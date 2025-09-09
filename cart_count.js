let subtotal = 0;
let cartCount = 0;
let cart = JSON.parse(localStorage.getItem("cart")) || [];
let currentProduct = null;

// Recalculate subtotal and count on load
function initializeCartSummary() {
  subtotal = 0;
  cartCount = 0;

  cart.forEach((item) => {
    subtotal += item.total;
    cartCount += item.qty;
  });

  document.getElementById("subtotal").innerText = `₱${subtotal.toFixed(2)}`;
  document.getElementById("cart-count").innerText = cartCount;
}

function goToCart() {
  localStorage.setItem("cart", JSON.stringify(cart));
  window.location.href = "cart.php";
}

function updateSubtotal(amount) {
  subtotal += amount;
  document.getElementById("subtotal").innerText = `₱${subtotal.toFixed(2)}`;
}

function updateCartCount(qty = 1) {
  cartCount += qty;
  document.getElementById("cart-count").innerText = cartCount;
}

function showPopup(product) {
  currentProduct = product;
  document.getElementById("popup-description").innerText = product.name;
  document.getElementById("popup-price").innerText = `₱${parseFloat(
    product.price
  ).toFixed(2)}`;
  document.getElementById("popup-qty").value = 1;
  document.getElementById("popup").style.display = "block";
}

function closePopup() {
  currentProduct = null;
  document.getElementById("popup").style.display = "none";
}

function addToCart() {
  const qty = parseInt(document.getElementById("popup-qty").value);
  const total = qty * parseFloat(currentProduct.price);

  cart.push({ ...currentProduct, qty, total });
  localStorage.setItem("cart", JSON.stringify(cart));

  updateSubtotal(total);
  updateCartCount(qty);
  closePopup();
  setTimeout(startScanner, 500); // resume scanning
}

// Initialize on load
window.addEventListener("DOMContentLoaded", () => {
  initializeCartSummary();
});
