<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your Cart</title>
    <style>
        :root {
            --primary-color: #3498db;
            --primary-hover: #2980b9;
            --danger-color: #e74c3c;
            --danger-hover: #c0392b;
            --light-gray: #f1f1f1;
            --medium-gray: #ddd;
            --dark-gray: #555;
            --white: #fff;
            --shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--medium-gray);
            color: #333;
        }

        header {
            background-color: var(--light-gray);
            border-bottom: 1px solid #ccc;
        }

        /* Shared grid columns for header & items */
        .header-row, .cart-item {
            display: grid;
            grid-template-columns: 2.5fr 1.7fr 1.5fr 1.5fr 1fr;
            gap: 10px;
            align-items: center;
            text-align: center;
        }

        /* Header styling */
        .header-row {
            font-weight: bold;
            padding: 10px;
            font-size: clamp(12px, 2vw, 14px);
        }

        /* Item styling */
        .cart-item {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 10px;
            padding: 10px;
            font-size: clamp(12px, 2.5vw, 14px);
        }

        /* Align specific columns */
        .header-desc, .item-name { text-align: left; }
        .header-total, .item-total, .header-remove, .item-remove { text-align: right; }

        .item-name { font-weight: bold; }
        .item-total { font-weight: bold; }

        /* Footer bar */
        .footer-bar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: var(--white);
            border-top: 1px solid #ccc;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
        }

        .cart-container {
            padding: 15px;
            padding-bottom: 100px;
        }

        .empty-cart-message {
            text-align: center;
            color: var(--dark-gray);
            padding: 40px 20px;
        }

        /* Quantity controls */
        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .quantity-controls button {
            background-color: var(--light-gray);
            border: 1px solid #ccc;
            cursor: pointer;
            font-weight: bold;
            width: 28px; height: 28px;
            font-size: 1.1em;
            line-height: 1;
            transition: background-color 0.2s;
        }
        .quantity-controls button:hover { background-color: #ccc; }
        .quantity-controls .qty-minus { border-radius: 4px 0 0 4px; }
        .quantity-controls .qty-plus { border-radius: 0 4px 4px 0; }
        .quantity-controls input {
            width: 35px;
            text-align: center;
            border: 1px solid #ccc;
            border-left: none;
            border-right: none;
            height: 28px;
            font-size: 0.9em;
            -moz-appearance: textfield;
        }
        .quantity-controls input::-webkit-outer-spin-button,
        .quantity-controls input::-webkit-inner-spin-button {
            -webkit-appearance: none; margin: 0;
        }

        /* Buttons */
        .btn {
            padding: 10px 18px;
            font-size: 1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }
        .btn:active { transform: scale(0.98); }
        .btn-primary { background-color: var(--primary-color); color: var(--white); }
        .btn-primary:hover { background-color: var(--primary-hover); }
        .btn-danger { background-color: var(--danger-color); color: white; padding: 6px 10px; font-size: 0.8em; }
        .btn-danger:hover { background-color: var(--danger-hover); }

        /* Modal */
        #confirm-modal {
            display: none; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.6);
            justify-content: center; align-items: center; z-index: 9999;
        }
        .modal-content {
            background: var(--white); padding: 25px; border-radius: 10px;
            text-align: center; width: 90%; max-width: 320px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .modal-buttons { margin-top: 20px; display: flex; justify-content: center; gap: 10px; }
        .modal-buttons button { padding: 10px 20px; cursor: pointer; border-radius: 5px; border: none; font-weight: bold; }
        .modal-buttons .confirm { background-color: var(--danger-color); color: var(--white); }
        .modal-buttons .cancel { background-color: var(--medium-gray); }
    </style>
</head>
<body>
    <header>
        <div class="header-row">
            <span class="header-desc">DESCRIPTION</span>
            <span class="header-qty">QTY</span>
            <span class="header-price">UNIT PRICE</span>
            <span class="header-total">TOTAL</span>
            <span class="header-remove"></span>
        </div>
    </header>

    <div class="cart-container" id="cart-container"></div>

    <div class="footer-bar">
        <button class="btn btn-primary" onclick="goBack()">Back to Scanner</button>
        <div><strong>Subtotal: <span id="subtotal">₱0.00</span></strong></div>
    </div>

    <div id="confirm-modal">
        <div class="modal-content">
            <p><strong>Are you sure?</strong></p>
            <p style="font-size: 0.9em; color: #555;">This will remove the item from your cart.</p>
            <div class="modal-buttons">
                <button class="cancel" onclick="cancelRemove()">Cancel</button>
                <button class="confirm" onclick="confirmRemove()">Remove</button>
            </div>
        </div>
    </div>

    <script>
        let cart = JSON.parse(localStorage.getItem("cart") || "[]");
        let itemToRemoveIndex = null;

        const cartContainer = document.getElementById("cart-container");
        const subtotalEl = document.getElementById("subtotal");
        const modal = document.getElementById("confirm-modal");

        function renderCart() {
            cartContainer.innerHTML = "";
            let subtotal = 0;

            if (cart.length === 0) {
                cartContainer.innerHTML = '<p class="empty-cart-message">Your cart is empty. 🛒</p>';
                subtotalEl.textContent = '₱0.00';
                return;
            }

            cart.forEach((item, index) => {
                const itemQty = parseInt(item.qty, 10) || 1;
                const itemPrice = parseFloat(item.price) || 0;
                const total = itemQty * itemPrice;
                subtotal += total;

                const itemDiv = document.createElement("div");
                itemDiv.className = "cart-item";
                itemDiv.dataset.index = index;

                itemDiv.innerHTML = `
                    <div class="item-name">${item.name}</div>
                    <div class="item-qty">
                        <div class="quantity-controls">
                            <button class="qty-minus" data-action="minus" aria-label="Decrease quantity">-</button>
                            <input type="number" class="qty-input" value="${itemQty}" min="1" aria-label="Quantity">
                            <button class="qty-plus" data-action="plus" aria-label="Increase quantity">+</button>
                        </div>
                    </div>
                    <div class="item-price">₱${itemPrice.toFixed(2)}</div>
                    <div class="item-total">₱${total.toFixed(2)}</div>
                    <div class="item-remove">
                        <button class="btn btn-danger" data-action="remove">Remove</button>
                    </div>
                `;
                cartContainer.appendChild(itemDiv);
            });

            subtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
        }

        function updateQty(index, newQty) {
            const qty = Math.max(1, parseInt(newQty, 10));
            if (cart[index] && !isNaN(qty)) {
                cart[index].qty = qty;
                localStorage.setItem("cart", JSON.stringify(cart));
                renderCart();
            }
        }

        cartContainer.addEventListener('click', (event) => {
            const target = event.target;
            const itemDiv = target.closest('.cart-item');
            if (!itemDiv) return;

            const index = parseInt(itemDiv.dataset.index, 10);
            const action = target.dataset.action;

            if (action === 'plus') {
                updateQty(index, cart[index].qty + 1);
            } else if (action === 'minus') {
                updateQty(index, cart[index].qty - 1);
            } else if (action === 'remove') {
                promptRemove(index);
            }
        });

        cartContainer.addEventListener('change', (event) => {
            const target = event.target;
            if (target.classList.contains('qty-input')) {
                const itemDiv = target.closest('.cart-item');
                const index = parseInt(itemDiv.dataset.index, 10);
                updateQty(index, target.value);
            }
        });

        function promptRemove(index) {
            itemToRemoveIndex = index;
            modal.style.display = "flex";
        }

        function cancelRemove() {
            itemToRemoveIndex = null;
            modal.style.display = "none";
        }

        function confirmRemove() {
            if (itemToRemoveIndex !== null) {
                cart.splice(itemToRemoveIndex, 1);
                localStorage.setItem("cart", JSON.stringify(cart));
                renderCart();
            }
            cancelRemove();
        }

        function goBack() {
            window.location.href = "index.html";
        }

        document.addEventListener('DOMContentLoaded', renderCart);
    </script>
</body>
</html>
