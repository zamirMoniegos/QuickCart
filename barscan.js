<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Barcode Scanner</title>
  <script src="https://unpkg.com/@zxing/library@latest"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #f4f4f4;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 20px;
    }
    video {
      width: 100%;
      max-width: 400px;
      border: 3px solid #333;
      border-radius: 8px;
    }
    #resultBox {
      margin-top: 15px;
      padding: 15px;
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      display: none;
    }
    #resultBox h3 { margin: 0 0 10px; }
  </style>
</head>
<body>
  <h2>📷 Barcode Scanner</h2>
  <video id="videoElement"></video>
  <div id="resultBox"></div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const codeReader = new ZXing.BrowserMultiFormatReader();
      const videoElement = document.getElementById("videoElement");
      const resultBox = document.getElementById("resultBox");

      let scanning = false;

      function showProduct(product) {
        resultBox.style.display = "block";
        if (product.error) {
          resultBox.innerHTML = `<h3>❌ ${product.error}</h3>`;
        } else {
          resultBox.innerHTML = `
            <h3>${product.name}</h3>
            <p>Price: ₱${product.price}</p>
            <p>Stock: ${product.stock}</p>
          `;
        }
      }

      function startScanner() {
        if (scanning) return; // prevent double play
        scanning = true;

        codeReader.decodeFromVideoDevice(null, videoElement, (result, err) => {
          if (result) {
            console.log("Scanned:", result.text);

            // ✅ Call your PHP API here
            fetch(`https://YOUR-SERVER.COM/get-product.php?barcode=${result.text}`)
              .then(res => res.json())
              .then(data => showProduct(data))
              .catch(err => console.error("API error:", err));

            // Pause scanning after detection
            codeReader.reset();
            scanning = false;
          }
        });
      }

      startScanner();
    });
  </script>
</body>
</html>
