<?php 
session_start();
if (!isset($_SESSION['user_id'])){
  header("Location: login.php");
  exit();
}
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

// Fetch all products grouped by name
$sql = "SELECT id, name, size, price, image FROM products";
$result = $mysqli->query($sql);

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[$row['name']][] = [
            'id' => $row['id'],
            'size' => $row['size'], // Make sure this field exists in your database
            'price' => $row['price'],
            'image' => $row['image']
        ];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOVE , TEA ♥</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="css/pos3.css" rel="stylesheet">

<style>

body {
    font-family: 'Poppins', sans-serif;
    background-image: url('../uploads/bg.png');
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    width: 100vw;
}

        .branding {
            font-size: 60px; 
            color: #4CAF50;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: 5px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            display: flex;
            gap: 30px;
            padding: 40px;
            background-color: #e4ece4;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 items per row */
            gap: 20px;
            flex: 3;
            padding: 20px;
            justify-items: center;
        }

        .product {
            text-align: center;
            padding: 15px;
            border-radius: 15px;
            font-weight: bold;
            cursor: pointer;
            height: 280px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product img {
            width: 200px;
            height: 200px;
            object-fit: contain;
            margin-bottom: 10px;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .product:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            
        
        }

        .product img:hover {
            transform: scale(1.1);
            background-color: #FFFFFF;
        }

        /* MODAL NG ADD ONS*/
.addons-modal-content {
    background: #FFF;
    padding: 25px;
    border-radius: 20px;
    width: 450px;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.addons-modal-content h5 {
    font-weight: bold;
    margin-bottom: 15px;
    color: #388E3C;
}

.addons-modal-content label {
    display: block;
    margin: 10px 0;
    font-size: 18px;
    color: #333;
}

.addons-modal-content button {
    margin: 10px;
    padding: 10px 5px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.addons-modal-content .close-btn {
    background-color: #FF5252;
    color: #FFF;
}

.addons-modal-content .close-btn:hover {
    background-color: #d34550;
    transform: scale(1.05);
}

.addons-modal-content button:hover {
    background-color: #4CAF50;
    color: #FFF;
}

        .order-summary {
            flex: 1;
            padding: 25px;
            background-color: #FFF;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }


        .order-summary h4 {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 28px;
            color: #388E3C;
        }

        .order-summary .order-list {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            color: #333;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .order-summary button {
            background-color: #4CAF50;
            color: #FFF;
            border: none;
            padding: 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            width: 100%;
            font-size: 20px;
            transition: background-color 0.3s ease;
        }

        .order-summary button:hover {
            background-color: #388E3C;
        }

        .cancel-btn {
            background-color: #FF5252;
            margin-left: 10px;
            flex: 1;
            font-size: 18px;
        }

        .order-summary .button-container {
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }

        /*SIZE , ADD ONS MODAL*/

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #FFF;
            padding: 25px;
            border-radius: 20px;
            width: 450px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .modal-content button {
            margin: 10px;
            padding: 10px 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        

        .modal-content .close-btn {
            background-color: #eb3838;
            color: #FFF;
            margin-top: 15px;
    }

        

        .modal-content .close-btn {
            background-color: #FF5252;
            color: #FFF;
            width: 340px; 
            height: 50px;
            margin: 8px auto;
            border-radius: 10px;
            text-align: center;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .modal-content .close-btn:hover {
            
            background-color: #d34550; 
            transform: scale(1.05);
          }


         

         /* RESIBO ( POP UP ) */
         .receipt-modal-content {
    background: #FFF;
    padding: 30px;
    border-radius: 15px;
    width: 400px;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    font-family: 'Courier New', monospace;
}

.modal-title {
    font-size: 28px;
    color: #388E3C;
    margin-bottom: 15px;
}

.divider {
    border-top: 1px solid #ccc;
    margin: 15px 0;
}

.shop-info {
    font-size: 18px;
    color: #4CAF50;
    margin-bottom: 10px;
}

.order-info {
    font-size: 16px;
    color: #333;
    margin-bottom: 15px;
}

.receipt-details {
    text-align: left;
    margin: 15px 0;
    font-size: 16px;
    color: #333;
}

.total {
    font-weight: bold;
    font-size: 20px;
    color: #388E3C;
    margin: 10px 0;
}

.close-btn {
    background-color: #f02d2d;
    color: #FFF;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.close-btn:hover {
    background-color: #f85252;
}

    
</style>

<!--HTML TO-->
<!--HTML TO-->
<!--HTML TO-->
</head>
<body>


    
    <!--ETO PRE SA LOGO SA LEFT SIDE YUNG OPEN SPACE-->
    <div class="branding">
        <!--LAGYAN MO NLNG TO PRE TINANGGAL KO YUNG SA PIC MASYADO MALAKI-->
    </div>

    <div class="main-content">
        <!-- GRID SA MILKTEA-->
        <div class="product-grid">
        <!-- Taken By Javascript sa baba -->
            </div>

        </div>

          <!-- ORDER SUMMARY -->
          <div class="order-summary">
            <h4>ORDER SUMMARY</h4>
            <div class="order-list" id="orderList"></div>
            <div class="button-container">
                <button onclick="checkout()">Checkout</button>
                <button class="cancel-btn" onclick="cancelOrder()">Cancel</button>
            </div>
            <p>Total Price: ₱<span id="totalPrice">0</span></p>
        </div>
    </div>

    <div class="modal" id="addonsModal">
        <div class="addons-modal-content">
            <h5>SELECT SIZE</h5>
            <label>
                <input type="radio" name="size" value="medium" checked> Medium
            </label><br>
            <label>
                <input type="radio" name="size" value="large"> Large (+₱10)
            </label><br>
            <button class="close-btn" onclick="closeModal()">Cancel</button>
            <button onclick="addToOrderWithoutAddons()">Add to Order</button>
        </div>
    </div>

    <!-- CHECK OUT CONFI Modal -->
    <div class="modal" id="checkoutModal">
        <div class="modal-content">
            <h5>Confirm Order</h5>
            <div id="orderSummaryList"></div>
            <p class="total">Total: ₱<span id="checkoutTotal">0</span></p>
            <button onclick="confirmOrder()">Proceed</button>
            <button class="close-btn" onclick="closeModal()">Cancel</button>
        </div>
    </div>

    <!-- RESIBO (wag na galawin goods na yan pogi na) -->
    <div class="modal" id="receiptModal">
        <div class="receipt-modal-content">
            <h5 class="modal-title">ORDER RECEIPT</h5>
            <hr class="divider">
            <div class="shop-info">LOVE, TEA ♥ - Your Favorite Tea Shop</div>
            
            <div class="order-info">
                <div class="order-id">Order ID: <span id="orderID"></span></div>
                <div class="date-time">Date: <span id="orderDate"></span></div>
            </div>
            <hr class="divider">
            
            <div id="receiptDetails" class="receipt-details"></div>
            
            <div class="total">
                <span>Total:</span> ₱<span id="receiptTotal">0</span>
            </div>
            
            <hr class="divider">
            
            <button class="close-btn" onclick="closeModal()">Close</button>
        </div>
    </div>

    




<!--JAVA SCRIPT TO-->
<!--JAVA SCRIPT TO-->
<!--JAVA SCRIPT TO-->
<script>
            const productsData = <?php echo json_encode($products); ?>;
            const productGrid = document.querySelector('.product-grid');

// Render products dynamically
Object.keys(productsData).forEach(productName => {
    const productDetails = productsData[productName].find(p => p.size === 'Medium'); // Default to Medium size for grid
    if (!productDetails) {
        console.warn(`No medium size found for product: ${productName}`);
        return; // Skip this product if no Medium size is found
    }
    
    const imageSrc = productDetails.image ? productDetails.image : 'path/to/default-image.jpg'; // Use default image if undefined

    const productDiv = document.createElement('div');
    productDiv.className = 'product';
    productDiv.style.backgroundColor = '#4CAF50'; // Example styling
    productDiv.setAttribute('onclick', `openSizeModal('${productName}')`);

    // Add product image and name
    productDiv.innerHTML = `
        <img src="${imageSrc}" alt="${productName}">
        ${productName} - ₱${productDetails.price}
    `;

    productGrid.appendChild(productDiv);
});
// ADD PRODUCT w/ ADD ONS
let order = [];
let totalPrice = 0;
let productName = '';
let productPrice = 0;

// MODAL NG SIZE , ADD ONS
function openSizeModal(name) {
    if (!productsData[name]) {
        console.error(`No product found with the name: ${name}`);
        return;
    }

    const productSizes = productsData[name];
    const mediumSize = productSizes.find(p => p.size === 'Medium');
    const largeSize = productSizes.find(p => p.size === 'Large');

    if (!mediumSize || !largeSize) {
        console.error(`Sizes not properly configured for product: ${name}`);
        return;
    }

    // Update modal inputs with the fetched data
    const mediumInput = document.querySelector('input[name="size"][value="medium"]');
    mediumInput.dataset.id = mediumSize.id;
    mediumInput.dataset.price = mediumSize.price;

    const largeInput = document.querySelector('input[name="size"][value="large"]');
    largeInput.dataset.id = largeSize.id;
    largeInput.dataset.price = largeSize.price;

    // Show the modal
    document.getElementById('addonsModal').classList.add('active');
}
console.log("Products Data:", productsData);

// SELECTED PRODUCTS w/ADD ONS
function addToOrderWithAddons() {
    let addons = [];
    let selectedSize = document.querySelector('input[name="size"]:checked');
    let size = selectedSize.value;
    let price = parseFloat(selectedSize.dataset.price);
    let id = parseInt(selectedSize.dataset.id);

    // Handle add-ons
    if (document.getElementById('crushedOreo').checked) {
        price += 10;
        addons.push('Crushed Oreos');
    }
    if (document.getElementById('extraPearl').checked) {
        price += 10;
        addons.push('Extra Pearl');
    }

    // Add to order
    order.push({ product: name, size: size, id: id, price: price, addons: addons });
    totalPrice += price;
    updateOrderList();
    closeModal();
}
// w/out ADD ONS
function addToOrderWithoutAddons() {
    let price = 0; 
    let selectedSize = document.querySelector('input[name="size"]:checked').value;

    
    if (selectedSize === 'medium') {
        price = 70; 
    } else if (selectedSize === 'large') {
        price = 80; 
    }

    // check out w/out ADD ONS
    order.push({ product: productName, size: selectedSize, price: price, addons: [] });
    totalPrice += price;
    updateOrderList();
    closeModal();
}

// Close 
function closeModal() {
    document.querySelector('.modal.active').classList.remove('active');
}

// UPDATE LIST / SUMMARY NANG ORDERS
function updateOrderList() {
    const orderList = document.getElementById('orderList');
    orderList.innerHTML = '';
    order.forEach(item => {
        let itemDiv = document.createElement('div');
        let addonsText = item.addons.length > 0 ? ` (Add-ons: ${item.addons.join(', ')})` : '';
        itemDiv.innerHTML = `${item.productname} (${item.size}) - ₱${item.price}${addonsText}`;
        orderList.appendChild(itemDiv);
    });
    document.getElementById('totalPrice').textContent = totalPrice;
}

     // CONFIRMATION
        function checkout() {
        console.log("Checkout function called"); 
    if (order.length === 0) {
        alert("Your order is empty! Please add items to your order before checking out.");
        return;
    }

    const orderSummaryList = document.getElementById('orderSummaryList');
    orderSummaryList.innerHTML = '';
    order.forEach(item => {
        let itemSummary = document.createElement('div');
        let addonsText = item.addons.length > 0 ? ` (Add-ons: ${item.addons.join(', ')})` : '';
        itemSummary.innerHTML = `${item.product} (${item.size}) - ₱${item.price}${addonsText}`;
        orderSummaryList.appendChild(itemSummary);
    });
    document.getElementById('checkoutTotal').textContent = totalPrice;

    
    document.getElementById('checkoutModal').classList.add('active');
}

function confirmOrder() {
    if (order.length === 0) {
        alert("Your order is empty!");
        return;
    }

    // Prepare order data
    const customerName = prompt("Enter customer name:", "Walk-in") || "Walk-in";
    const payload = {
        customerName: customerName,
        orderItems: order
    };
    console.log("Sending order data:", payload);

    // Send data to checkout.php
    fetch('backend/checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(response => response.text()) // Fetch raw response first
        .then(text => {
            console.log("Raw server response:", text); // Log raw response
            try {
                const data = JSON.parse(text); // Parse JSON
                if (data.status === 'success') {
                    // Populate receipt details
                    document.getElementById('orderID').textContent = data.receiptID;
                    document.getElementById('orderDate').textContent = data.salesDate;
                    document.getElementById('receiptDetails').innerHTML = '';

                    data.orderItems.forEach(item => {
                        let addonsText = item.addons && item.addons.length > 0 ? ` (Add-ons: ${item.addons.join(', ')})` : '';
                        let receiptItem = document.createElement('div');
                        receiptItem.innerHTML = `${item.product} (${item.size}) - ₱${item.price} x ${item.quantity}${addonsText}`;
                        document.getElementById('receiptDetails').appendChild(receiptItem);
                    });

                    document.getElementById('receiptTotal').textContent = data.totalPrice;

                    // Show receipt modal
                    document.getElementById('receiptModal').classList.add('active');

                    // Clear the order
                    order = [];
                    totalPrice = 0;
                    updateOrderList();
                } else {
                    alert("Error: " + data.message);
                }
            } catch (e) {
                console.error("Failed to parse JSON:", e);
                alert("An error occurred while processing the order.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while processing the order.");
        });

    // Close the checkout modal
    document.getElementById('checkoutModal').classList.remove('active');
}
        // CANCEL ORDER
        function cancelOrder() {
            order = [];
            totalPrice = 0;
            updateOrderList();
        }
    </script>  
</body>
</html>