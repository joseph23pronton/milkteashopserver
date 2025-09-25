<?php
ob_start();
session_start();

if (!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
  }
$conn = include('database.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOVE , TEA ♥</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>

/* CSS TO */
/* CSS TO */
/* CSS TO */

body {
    font-family: 'Poppins', sans-serif;
    background-image: url('Milktea/LOVETEABG.png');
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
        background-color: #b6adad;
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
            padding: 20px;
            background-color: #FFF;
            border-radius: 10px;
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


        .order-list div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.order-list span {
    margin: 0 5px;
    cursor: pointer; 
}

.order-list span:hover {
    text-decoration: underline; 
}

        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .order-summary button {
            background-color: #43a546;
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
            background-color: #08440b;
        }

        .order-summary button.cancel-btn {
            background-color: #b63a3a;
            margin-left: 10px;
            flex: 1;
            font-size: 18px;
        }

        .order-summary button.cancel-btn:hover {
           
            background-color: #851a08;

        }

        .order-summary .button-container {
            display: flex;
            gap: 0px;
            justify-content: space-between;
            margin-top: 1px;
        }

        .total-price {
    font-weight: bold; 
    font-size: 20px; 
    color: #388E3C; 
    margin: 50px 0; 
    padding: 10px; 
    
}

        /*SIZE , ADD ONS MODAL*/

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
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

          .complete-btn {
    background-color: #4CAF50; 
    color: #FFF; 
    border: none;
    padding: 15px 30px; 
    border-radius: 5px; 
    cursor: pointer;
    font-weight: bold;
    font-size: 18px; 
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.complete-btn:hover {
    background-color: #388E3C; 
    transform: scale(1.05); 
}

.payment-button {
    background-color: #4CAF50; 
    color: #FFF; 
    border: none; 
    padding: 10px 20px; 
    border-radius: 5px; 
    cursor: pointer; 
    font-weight: bold; 
    font-size: 18px; 
    transition: background-color 0.3s ease, transform 0.3s ease; 
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
}

.payment-button:hover {
    background-color: #388E3C; 
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

    
.logout-btn {
            background-color: #964B00;            ;
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

        .modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    display: none; /* Hidden by default */
    justify-content: center;
    align-items: center;
}

.modal.active {
    display: flex; /* Show modal when active */
}

.modal-content {
    background: #FFF;
    padding: 25px;
    border-radius: 20px;
    width: 400px;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.close-btn {
    background-color: #FF5252; /* Red background */
    color: #FFF; /* White text */
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 
    0.3s ease;
}

.close-btn:hover {
    background-color: #d34550; /* Darker red on hover */
}

    </style>

    

<!--HTML TO-->
<!--HTML TO-->
<!--HTML TO-->
</head>
<body>
    
    <!--ETO PRE SA LOGO SA LEFT SIDE YUNG OPEN SPACE-->
    <div class="branding">
        <!--LAGYAN MO NLNG TO PRE TINANGGAL KO YUNG LOGO MASYADO MALAKI-->
    </div>

    <div class="main-content">
        <!-- GRID SA MILKTEA-->
         <!-- DITO MO INSERT YUNG MGA PRODUCTS GALING DATA BASE-->
        <div class="product-grid">
            <?php
                // Fetch all products with "Medium" size
                $sql = "SELECT id, name, price AS medium_price, 
                (SELECT price FROM products p2 WHERE p2.name = p1.name AND p2.size = 'Large') AS large_price, 
                image 
                FROM products p1 
                WHERE size = 'Medium' AND is_active = 1";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
$productName = htmlspecialchars($row['name']);
$mediumPrice = (int)$row['medium_price'];
$largePrice = (int)$row['large_price'] ?? 0;
$imagePath = htmlspecialchars($row['image']);

echo "
<div class='product' style='background-color: #f8f8f8;' 
onclick=\"openSizeModal('$productName', $mediumPrice, $largePrice, {$row['id']})\">
<img src='$imagePath' alt='$productName'>
$productName
</div>";
}
} else {
echo "<p>No products found.</p>";
}

            ?>
        </div>

          <!-- ORDER SUMMARY -->
          <div class="order-summary">
            <h4>ORDER SUMMARY</h4>
            <div class="order-list" id="orderList"></div>
            <p class="total-price">Total Price: ₱<span id="totalPrice">0</span></p>
            <div class="button-container">
                <button onclick="checkout()">Checkout</button>
                <button class="cancel-btn" onclick="cancelOrder()">Cancel</button>
            </div>
            <a class="logout-btn" href="backend/signout.php">Logout</a>
        </div>

    <div class="modal" id="addonsModal">
        <div class="addons-modal-content">
            <h5>SELECT ADD-ONS</h5>
            <label>
                <input type="checkbox" id="crushedOreo" value="10"> Crushed Oreos (+ ₱10)
            </label><br>
            <label>
                <input type="checkbox" id="extraPearl" value="10"> Extra Pearl (+ ₱10)
            </label><br>
            <h5>SELECT SIZE</h5>
            <label>
                <input type="radio" name="size" value="medium" checked> Medium
            </label><br>
            <label>
                <input type="radio" name="size" value="large"> Large (+₱10)
            </label><br>
            <button class="close-btn" onclick="closeModal()">Cancel</button>
            <button onclick="addToOrderWithAddons()">Add to Order</button>
            <button onclick="addToOrderWithoutAddons()">Add Without Add-Ons</button> 
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

     <!-- PAYMENT MODAL -->
<div class="modal" id="paymentModal">
    <div class="modal-content">
        <h5>Receive Payment</h5>
        <label for="amountReceived">Amount Received:</label>
        <input type="number" id="amountReceived" placeholder="Enter amount" />
        <p>Change: ₱<span id="changeAmount">0</span></p>
        <div class="button-container">
            <button class="payment-button" onclick="processPayment()">Confirm Payment</button>
            <button class="close-btn" onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>


<div class="modal" id="errorModal">
        <div class="modal-content">
            <h5>Error</h5>
            <p id="errorMessage">An error occurred.</p>
            <button class="close-btn" onclick="closeErrorModal()">Close</button>
        </div>
    </div>

<!-- VALIDATION MODAL -->
<div class="modal" id="validationModal">
    <div class="modal-content">
        <h5>Error</h5>
        <p id="validationMessage"></p>
        <button class="close-btn" onclick="closeValidationModal()">Close</button>
    </div>
</div>

    <!-- RESIBO (wag na galawin goods na yan pogi na) -->
<div class="modal" id="receiptModal">
    <div class="receipt-modal-content">
        <h5 class="modal-title">ORDER RECEIPT</h5>
        <hr class="divider">
        <div class="shop-info">LOVE, TEA ♥ - Your Favorite Tea Shop</div>
        
        <div class="order-info">
            <div class="order-id">Transaction Number: <span id="transactionNumber"></span></div>
            <div class="order-id">Invoice Number: <span id="invoiceNumber"></span></div>
            <div class="date-time">Date: <span id="orderDate"></span></div>
        </div>
        <hr class="divider">
        
        <div id="receiptDetails" class="receipt-details"></div>
        
        <div class="receipt-summary">
            <div class="total">
                <span>Total:</span> <span id="receiptTotal">0</span>
            </div>
            <p>Received: <span id="receipt-received">₱0.00</span></p>
            <p>Change: <span id="receipt-change">₱0.00</span></p>
        </div>

        <hr class="divider">

        <div class="button-container">
            <button class="close-btn" onclick="completeOrder()">Close</button>
        </div>
        
    </div>
</div>

    



<!--JAVA SCRIPT TO-->
<!--JAVA SCRIPT TO-->
<!--JAVA SCRIPT TO-->
<script>
        
// ADD PRODUCT w/ ADD ONS
let order = [];
let totalPrice = 0;
let productName = '';
let productPrice = 0;
let selectedProduct = {}; 

function closeErrorModal() {
    document.getElementById('errorModal').classList.remove('active'); // Hide the modal
}
// MODAL NG SIZE , ADD ONS
function openSizeModal(name, mediumPrice, largePrice, productID) {
    console.log("Product ID:", productID); 
    
    selectedProduct = {
        id: productID,
        name: name, // Use 'name' passed to the function
        mediumPrice: mediumPrice,
        largePrice: largePrice
    };
    productName = name;
    productPrice = mediumPrice; // Set the base price for the product
    document.getElementById('addonsModal').classList.add('active');

}

// SELECTED PRODUCTS w/ADD ONS
function addToOrderWithAddons() {
    let price = 0; 
    let addons = [];
    let selectedSize = document.querySelector('input[name="size"]:checked').value;

    if (selectedSize === 'medium') {
        price = 70; 
    } else if (selectedSize === 'large') {
        price = 80; 
    }

    if (document.getElementById('crushedOreo').checked) {
        price += 10; 
        addons.push('Crushed Oreos');
    }
    if (document.getElementById('extraPearl').checked) {
        price += 10; 
        addons.push('Extra Pearl');
    }

    order.push({ 
        productID: selectedProduct.id, // Use global product ID
        product: selectedProduct.name, 
        size: selectedSize, 
        price: price, 
        addons: addons, 
        quantity: 1 
    });
    totalPrice += price;
    updateOrderList();
    closeModal();
}

// W/OUT ADD ONS
function addToOrderWithoutAddons() {
    let price = 0; 
    let selectedSize = document.querySelector('input[name="size"]:checked').value;

    if (selectedSize === 'medium') {
        price = 70; 
    } else if (selectedSize === 'large') {
        price = 80; 
    }

    order.push({ 
        productID: selectedProduct.id, 
        product: selectedProduct.name, 
        size: selectedSize, 
        price: price, 
        addons: [], 
        quantity: 1 
    });
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

    // Create an object to group items by product name, size, and add-ons
    const groupedOrder = {};

    order.forEach((item, index) => {
        const key = `${item.product} (${item.size})`;
        const addonsKey = item.addons.length > 0 ? ` (Add-ons: ${item.addons.join(', ')})` : '';

        if (groupedOrder[key + addonsKey]) {
            groupedOrder[key + addonsKey].quantity += item.quantity; // Increment quantity
            groupedOrder[key + addonsKey].price += item.price; // Add to total price
            groupedOrder[key + addonsKey].index.push(index); // Store the index for removal
        } else {
            groupedOrder[key + addonsKey] = { quantity: item.quantity, price: item.price, index: [index] }; // Initialize if it doesn't exist
        }
    });

    // Display the grouped items in the order list
    for (const [key, value] of Object.entries(groupedOrder)) {
        let itemDiv = document.createElement('div');
        itemDiv.style.display = 'flex';
        itemDiv.style.alignItems = 'center';
        itemDiv.style.justifyContent = 'space-between';
        itemDiv.style.marginBottom = '10px';

        itemDiv.innerHTML = `
            <span>${key} ${value.quantity > 1 ? value.quantity + 'x' : ''} - ₱${value.price}</span>
            <div>
                <span style="cursor: pointer; color: blue;" onclick="decreaseQuantity('${key}', ${value.index[0]})">-</span>
                <span>${value.quantity}</span>
                <span style="cursor: pointer; color: blue;" onclick="increaseQuantity('${key}', ${value.index[0]})">+</span>
                <span style="cursor: pointer; color: red;" onclick="removeItem('${key}', ${value.index[0]})">Remove</span>
            </div>
        `;
        orderList.appendChild(itemDiv);
    }

    document.getElementById('totalPrice').textContent = totalPrice;
}

function increaseQuantity(key, index) {
    const item = order[index];
    item.quantity += 1; // Increase quantity
    totalPrice += item.price; // Update total price
    updateOrderList(); // Refresh the order list
}

function decreaseQuantity(key, index) {
    const item = order[index];
    if (item.quantity > 1) {
        item.quantity -= 1; // Decrease 
        totalPrice -= item.price; // Update 
        updateOrderList(); // Refresh  order list
    } else {
        removeItem(key, index); // Remove item if quantity is 1
    }
}


function removeItem(key, index) {
    const item = order[index];
    totalPrice -= item.price * item.quantity; // Update total price
    order.splice(index, 1); // Remove item from order
    updateOrderList(); // Refresh the order list
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
    let receiptDetails = document.getElementById('receiptDetails');
    receiptDetails.innerHTML = '';

    // Group order items
    const groupedOrder = {};
    totalPrice = 0; // Reset total price

    order.forEach(item => {
        const key = `${item.product} (${item.size})`;
        const addonsKey = item.addons.length > 0 ? ` (Add-ons: ${item.addons.join(', ')})` : '';

        if (groupedOrder[key + addonsKey]) {
            groupedOrder[key + addonsKey].quantity += 1;
            groupedOrder[key + addonsKey].price += item.price;
        } else {
            groupedOrder[key + addonsKey] = { 
                product: item.product,
                size: item.size,
                addons: item.addons,
                price: item.price, 
                quantity: item.quantity
            };
        }
    });

    // Display the order summary and calculate total
    for (const [key, value] of Object.entries(groupedOrder)) {
        totalPrice += value.price;
        let itemSummary = document.createElement('div');
        itemSummary.innerHTML = `${key} ${value.quantity > 1 ? value.quantity + 'x' : ''} - ₱${value.price.toFixed(2)}`;
        receiptDetails.appendChild(itemSummary);
    }

    // Display total price
    document.getElementById('receiptTotal').textContent = `₱${totalPrice.toFixed(2)}`;

    // Open the payment modal
    document.getElementById('paymentModal').classList.add('active');
    closeModal();
}

function processPayment() {
    const amountReceivedInput = document.getElementById('amountReceived'); // Ensure this is a valid input
    const amountReceived = parseFloat(amountReceivedInput.value.trim()); // Use `.trim()` to prevent unwanted spaces

    // Validate input
    if (isNaN(amountReceived) || amountReceived <= 0) {
        showValidationModal("Please enter a valid amount received.");
        return;
    }

    if (amountReceived < totalPrice) {
        showValidationModal("Amount received is less than the total price!");
        return;
    }

    const change = amountReceived - totalPrice;

    // Prepare order details for backend
    const customerNameElement = document.getElementById('customerName');
    const customerName = customerNameElement && customerNameElement.value.trim() !== '' 
        ? customerNameElement.value 
        : 'Walk-in';

        const orderItems = order.map(item => ({
            productID: item.productID,
            product: item.product,
            size: item.size,
            price: item.price,
            initial_price: item.price / item.quantity || item.price,
            quantity: item.quantity
        }));

    // Send order to checkout2.php

    function showErrorModal(message) {
    document.getElementById('errorMessage').textContent = message; // Set the error message
    document.getElementById('errorModal').classList.add('active'); // Show the modal
}



    fetch('backend/checkout2.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            customerName: customerName,
            orderItems: orderItems
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // server generated na this
            const orderID = data.receiptID; 
            const orderDate = data.salesDate;
            const transactionNumber = data.transactionNumber;
            const invoiceNumber = data.invoiceNumber;

            // retrieve data galing json
            document.getElementById('transactionNumber').textContent = transactionNumber;
            document.getElementById('invoiceNumber').textContent = invoiceNumber;
            document.getElementById('orderDate').textContent = orderDate;

            document.getElementById('receipt-received').textContent = `₱${amountReceived.toFixed(2)}`;
            document.getElementById('receipt-change').textContent = `₱${change.toFixed(2)}`;


            document.getElementById('receiptModal').classList.add('active');
            closeModal();
        } else {
            showErrorModal(`Error saving order: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal("Error saving order: Unable to connect to the server.");
;
    });

    // Keep the input editable after processing
    amountReceivedInput.value = ''; // Optionally clear the input
}

// Function to show the validation modal
function showValidationModal(message) {
    document.getElementById('validationMessage').textContent = message;
    document.getElementById('validationModal').classList.add('active');
}

// Function to close the validation modal
function closeValidationModal() {
    document.getElementById('validationModal').classList.remove('active');
}
            
            
function completeOrder() {
    // Reset the order and total price
    order = [];
    totalPrice = 0;
    updateOrderList();

    // Close the receipt modal
    closeModal();

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