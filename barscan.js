document.addEventListener("DOMContentLoaded", () => {
  // --- Globals ---
  const codeReader = new ZXing.BrowserMultiFormatReader();
  let cart = JSON.parse(localStorage.getItem("cart") || "[]");
  let currentProduct = null;
  let isScanningPaused = false;

  // --- DOM Elements ---
  const videoElement = document.getElementById("scanner");
  const statusElement = document.getElementById("scan-status");
  const beepSound = document.getElementById("beep-sound");

  // =================================================================
  // SCANNER LOGIC
  // =================================================================

  function handleScanResult(result) {
    if (isScanningPaused) return;
    isScanningPaused = true; // Pause scanning to show the popup

    codeReader.reset();
    beepSound.play();

    // Fetch product details from the server
    fetch(`get-product.php?code=${encodeURIComponent(result.text)}`)
      .then((res) => res.json())
      .then((data) => {
        // *** THE FIX IS HERE ***
        // We must check for data.success AND data.product.
        // Then, we pass ONLY data.product to the showPopup function.
        if (data.success && data.product) {
          showPopup(data.product);
        } else {
          showNotFoundPopup();
        }
      })
      .catch((error) => {
        console.error("Fetch error:", error);
        showNotFoundPopup();
      });
  }

  function startScanner() {
    isScanningPaused = false;
    codeReader
      .listVideoInputDevices()
      .then((videoInputDevices) => {
        const rearCamera = videoInputDevices.find((d) =>
          d.label.toLowerCase().includes("back")
        );
        const deviceId = rearCamera
          ? rearCamera.deviceId
          : videoInputDevices[0].deviceId;

        codeReader.decodeFromVideoDevice(deviceId, "scanner", (result, err) => {
          if (result) {
            handleScanResult(result);
          }
          if (err && !(err instanceof ZXing.NotFoundException)) {
            console.error("ZXing Error:", err);
          }
        });
      })
      .catch((err) => console.error("Camera Error:", err));
  }

  // =================================================================
  // CART & POPUP LOGIC (Moved from index.html)
  // =================================================================

  function updateCartSummary() {
    let total = 0;
    let count = 0;
    cart.forEach((item) => {
      // Ensure qty and price are valid numbers before calculating
      const itemQty = parseInt(item.qty, 10) || 0;
      const itemPrice = parseFloat(item.price) || 0;

      total += itemQty * itemPrice;
      count += itemQty;
    });
    document.getElementById("cart-count").textContent = count;
    document.getElementById("subtotal").textContent = `₱${total.toFixed(2)}`;
  }

  window.goToCart = function () {
    localStorage.setItem("cart", JSON.stringify(cart));
    window.location.href = "cart.php";
  };

  function showPopup(product) {
    currentProduct = product; // Set the global product object
    document.getElementById("popup-description").textContent = product.name;
    document.getElementById("popup-price").textContent = `₱${parseFloat(
      product.price
    ).toFixed(2)}`;
    document.getElementById("popup-qty").value = 1;
    document.getElementById("popup").style.display = "flex";
  }

  window.closePopup = function () {
    currentProduct = null;
    document.getElementById("popup").style.display = "none";
    setTimeout(startScanner, 300); // Resume scanning
  };

  window.addToCart = function () {
    const qty = parseInt(document.getElementById("popup-qty").value, 10);
    if (qty > 0 && currentProduct) {
      // Find if item already exists (match by ID)
      const existingProductIndex = cart.findIndex(
        (item) => item.id === currentProduct.id
      );

      if (existingProductIndex > -1) {
        // Item exists, just update quantity
        cart[existingProductIndex].qty += qty;
      } else {
        // Item is new, add it to the cart
        cart.push({ ...currentProduct, qty: qty });
      }
      localStorage.setItem("cart", JSON.stringify(cart));
      updateCartSummary();
    }
    closePopup();
  };

  function showNotFoundPopup() {
    document.getElementById("not-found-popup").style.display = "flex";
  }

  window.closeNotFound = function () {
    document.getElementById("not-found-popup").style.display = "none";
    setTimeout(startScanner, 300); // Resume scanning
  };

  // --- Initializer ---
  updateCartSummary();
  startScanner();
});
