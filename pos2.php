<?php
session_start();
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must log in to access this page.";
    exit();
}

// Fetch products with size 'Medium'
$query = "SELECT id, name, size, price, initial_price FROM products WHERE size = 'Medium'";
$result = $mysqli->query($query);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Point of Sale</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .product-card { cursor: pointer; transition: transform 0.2s; }
        .product-card:hover { transform: scale(1.05); }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Point of Sale</h1>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card" onclick="addToOrder(<?= htmlspecialchars(json_encode($product)) ?>)">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text">Size: <?= htmlspecialchars($product['size']) ?></p>
                        <p class="card-text">Price: ₱<?= htmlspecialchars($product['price']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h3>Order Summary</h3>
    <ul id="orderList" class="list-group mb-3"></ul>
    <p><strong>Total Price:</strong> ₱<span id="totalPrice">0</span></p>
    <button class="btn btn-primary" onclick="submitOrder()">Checkout</button>
</div>

<script>
    let orderItems = [];
    let totalPrice = 0;

    // Add product to order list
    function addToOrder(product) {
        const existingItem = orderItems.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            product.quantity = 1;
            orderItems.push(product);
        }
        updateOrderList();
    }

    // Update order list UI
    function updateOrderList() {
        const orderList = document.getElementById('orderList');
        orderList.innerHTML = '';
        totalPrice = 0;

        orderItems.forEach(item => {
            const itemTotal = item.price * item.quantity;
            totalPrice += itemTotal;

            const listItem = document.createElement('li');
            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            listItem.innerHTML = `
                ${item.name} (x${item.quantity}) - ₱${itemTotal.toFixed(2)}
                <button class="btn btn-danger btn-sm" onclick="removeItem(${item.id})">Remove</button>
            `;
            orderList.appendChild(listItem);
        });

        document.getElementById('totalPrice').textContent = totalPrice.toFixed(2);
    }

    // Remove item from order
    function removeItem(id) {
        orderItems = orderItems.filter(item => item.id !== id);
        updateOrderList();
    }

    // Submit order to checkout.php
    async function submitOrder() {
        if (orderItems.length === 0) {
            alert("No items in the order.");
            return;
        }

        const customerName = prompt("Enter customer name (or leave blank for 'Walk-in')") || "Walk-in";

        const data = {
            customerName: customerName,
            orderItems: orderItems.map(item => ({
                product: item.name,
                size: item.size,
                price: item.price,
                quantity: item.quantity,
                initial_price: item.initial_price // Include initial_price
            }))
        };

        try {
            const response = await fetch('backend/checkout2.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.status === 'success') {
                alert(`Order completed! Receipt ID: ${result.receiptID}`);
                orderItems = [];
                updateOrderList();
            } else {
                alert(`Error: ${result.message}`);
            }
        } catch (error) {
            console.error("Error:", error);
            alert("An error occurred while processing the order.");
        }
    }
</script>
</body>
</html>