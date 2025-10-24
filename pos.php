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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LOVE, TEA ♥</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #4C763B 100%); min-height: 100vh; }
        .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .product-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12); }
        .modal-backdrop { backdrop-filter: blur(8px); background: rgba(0, 0, 0, 0.6); }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .gradient-text { background: linear-gradient(135deg, 59AC77 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    </style>
</head>
<body class="p-2 md:p-3">
    <div class="max-w-[1400px] mx-auto mb-2 flex justify-start">
    <?php
    // check if the session role exists and is "sales"
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'sales') {
        $link = "sales_index.php";
        $text = "Go Back to Sales";
    } else {
        $link = "branch_index.php";
        $text = "Go Back to Branch";
    }
    ?>
    <a href="<?= htmlspecialchars($link) ?>" class="bg-gradient-to-r from-green-500 to-green-600 text-white py-1 px-3 text-xs rounded-lg shadow hover:from-green-600 hover:to-white-700 transition-all">
        <?= htmlspecialchars($text) ?>
    </a>
</div>
    <div class="max-w-[1400px] mx-auto">
        <div class="text-center mb-3">
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-0.5">LOVE, TEA ♥</h1>
            <p class="text-white text-xs">Your Favorite Tea Shop</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="lg:col-span-2 glass-effect rounded-xl p-3 shadow-xl">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Menu</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2">
                    <?php
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
                            $largePrice = (int)($row['large_price'] ?? 0);
                            $imagePath = htmlspecialchars($row['image']);

                            echo "
                            <div class='product-card bg-white rounded-lg p-2 cursor-pointer shadow hover:shadow-lg'
                                onclick=\"openSizeModal('$productName', $mediumPrice, $largePrice, {$row['id']})\">
                                <div class='aspect-square mb-1.5 overflow-hidden rounded-md bg-gradient-to-br from-purple-100 to-pink-100'>
                                    <img src='$imagePath' alt='$productName' class='w-full h-full object-cover'>
                                </div>
                                <h3 class='font-semibold text-gray-800 text-center text-xs leading-tight'>{$productName}</h3>
                                <p class='text-purple-600 text-center text-[10px] mt-0.5'>From ₱{$mediumPrice}</p>
                            </div>";
                        }
                    } else {
                        echo "<p class='col-span-full text-center text-gray-500'>No products found.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="glass-effect rounded-xl p-3 shadow-xl flex flex-col h-fit lg:sticky lg:top-3">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Order Summary</h2>
                <div id="orderList" class="flex-1 mb-2 space-y-1.5 max-h-[240px] overflow-y-auto"></div>
                <div class="border-t pt-2">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-base font-bold text-gray-800">Total:</span>
                        <span class="text-xl font-bold text-green-600">₱<span id="totalPrice">0</span></span>
                    </div>
                    <button onclick="checkout()" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-2 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 transition-all shadow mb-1.5 text-xs">
                        Checkout
                    </button>
                    <button onclick="cancelOrder()" class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-1.5 rounded-lg font-semibold hover:from-red-600 hover:to-red-700 transition-all shadow mb-1.5 text-xs">
                        Cancel Order
                    </button>
                    <a href="logout.php" class="block w-full bg-gradient-to-r from-amber-700 to-amber-800 text-white py-1.5 rounded-lg font-semibold hover:from-amber-800 hover:to-amber-900 transition-all shadow text-center text-xs">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="fixed inset-0 modal-backdrop hidden items-center justify-center z-50" id="addonsModal">
        <div class="glass-effect rounded-xl p-5 max-w-md w-full mx-4 shadow-2xl animate-fade-in">
            <h3 class="text-lg font-bold gradient-text mb-3">Customize Your Order</h3>
            <div class="mb-3">
                <h4 class="font-semibold text-gray-700 mb-1.5 text-xs">Select Size</h4>
                <div class="space-y-1.5">
                    <label class="flex items-center p-1.5 bg-white rounded-lg cursor-pointer hover:bg-purple-50 transition-colors">
                        <input type="radio" name="size" value="medium" checked class="w-3.5 h-3.5 text-purple-600" />
                        <span class="ml-2 font-medium text-gray-700 text-xs">Medium</span>
                    </label>
                    <label class="flex items-center p-1.5 bg-white rounded-lg cursor-pointer hover:bg-purple-50 transition-colors">
                        <input type="radio" name="size" value="large" class="w-3.5 h-3.5 text-purple-600" />
                        <span class="ml-2 font-medium text-gray-700 text-xs">Large (+₱10)</span>
                    </label>
                </div>
            </div>
            <div class="mb-3">
                <h4 class="font-semibold text-gray-700 mb-1.5 text-xs">Add-ons</h4>
                <div class="space-y-1.5">
                    <label class="flex items-center p-1.5 bg-white rounded-lg cursor-pointer hover:bg-purple-50 transition-colors">
                        <input type="checkbox" id="crushedOreo" value="10" class="w-3.5 h-3.5 text-purple-600 rounded" />
                        <span class="ml-2 font-medium text-gray-700 text-xs">Crushed Oreos (+₱10)</span>
                    </label>
                    <label class="flex items-center p-1.5 bg-white rounded-lg cursor-pointer hover:bg-purple-50 transition-colors">
                        <input type="checkbox" id="extraPearl" value="10" class="w-3.5 h-3.5 text-purple-600 rounded" />
                        <span class="ml-2 font-medium text-gray-700 text-xs">Extra Pearl (+₱10)</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="addToOrderWithAddons()" class="flex-1 bg-gradient-to-r from-purple-600 to-purple-700 text-white py-2 rounded-lg font-semibold hover:from-purple-700 hover:to-purple-800 transition-all shadow text-xs">
                    Add to Order
                </button>
                <button onclick="closeModal()" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all text-xs">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    <div class="fixed inset-0 modal-backdrop hidden items-center justify-center z-50" id="checkoutModal">
        <div class="glass-effect rounded-xl p-5 max-w-md w-full mx-4 shadow-2xl animate-fade-in">
            <h3 class="text-lg font-bold gradient-text mb-3">Confirm Order</h3>
            <div id="orderSummaryList" class="mb-3 space-y-1.5 max-h-[200px] overflow-y-auto"></div>
            <div class="border-t pt-2.5 mb-3">
                <div class="flex justify-between items-center">
                    <span class="text-base font-bold text-gray-800">Total:</span>
                    <span class="text-lg font-bold text-green-600">₱<span id="checkoutTotal">0</span></span>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="confirmOrder()" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white py-2 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 transition-all shadow text-xs">
                    Proceed
                </button>
                <button onclick="closeModal()" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all text-xs">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    <div class="fixed inset-0 modal-backdrop hidden items-center justify-center z-50" id="paymentModal">
        <div class="glass-effect rounded-xl p-5 max-w-md w-full mx-4 shadow-2xl animate-fade-in">
            <h3 class="text-lg font-bold gradient-text mb-3">Receive Payment</h3>
            <div class="mb-3">
                <label class="block text-gray-700 font-semibold mb-1.5 text-xs">Amount Received:</label>
                <input type="number" id="amountReceived" placeholder="Enter amount" class="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:outline-none text-sm" />
            </div>
            <div class="bg-purple-50 p-2.5 rounded-lg mb-3"></div>
            <div class="flex gap-2">
                <button onclick="processPayment()" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white py-2 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 transition-all shadow text-xs">
                    Confirm Payment
                </button>
                <button onclick="closeModal()" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-all text-xs">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    <div class="fixed inset-0 modal-backdrop hidden items-center justify-center z-50" id="errorModal">
        <div class="glass-effect rounded-xl p-5 max-w-md w-full mx-4 shadow-2xl animate-fade-in">
            <h3 class="text-lg font-bold text-red-600 mb-2.5">Error</h3>
            <p id="errorMessage" class="text-gray-700 mb-3 text-xs">An error occurred.</p>
            <button onclick="closeErrorModal()" class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-2 rounded-lg font-semibold hover:from-red-600 hover:to-red-700 transition-all shadow text-xs">
                Close
            </button>
        </div>
    </div>
    <div class="fixed inset-0 modal-backdrop hidden items-center justify-center z-50" id="validationModal">
        <div class="glass-effect rounded-xl p-5 max-w-md w-full mx-4 shadow-2xl animate-fade-in">
            <h3 class="text-lg font-bold text-red-600 mb-2.5">Error</h3>
            <p id="validationMessage" class="text-gray-700 mb-3 text-xs"></p>
            <button onclick="closeValidationModal()" class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-2 rounded-lg font-semibold hover:from-red-600 hover:to-red-700 transition-all shadow text-xs">
                Close
            </button>
        </div>
    </div>
    <div class="fixed inset-0 modal-backdrop hidden items-center justify-center z-50" id="receiptModal">
        <div class="glass-effect rounded-xl p-5 max-w-md w-full mx-4 shadow-2xl animate-fade-in">
            <h3 class="text-xl font-bold gradient-text mb-0.5 text-center">ORDER RECEIPT</h3>
            <p class="text-center text-gray-600 mb-3 text-xs">LOVE, TEA ♥ - Your Favorite Tea Shop</p>
            <div class="bg-purple-50 rounded-lg p-2.5 mb-3 space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-600">Transaction #:</span>
                    <span id="transactionNumber" class="font-semibold text-gray-800"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Invoice #:</span>
                    <span id="invoiceNumber" class="font-semibold text-gray-800"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Date:</span>
                    <span id="orderDate" class="font-semibold text-gray-800"></span>
                </div>
            </div>
            <div id="receiptDetails" class="mb-3 space-y-1.5 max-h-[140px] overflow-y-auto text-xs"></div>
            <div class="border-t pt-2.5 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-base font-bold text-gray-800">Total:</span>
                    <span id="receiptTotal" class="text-lg font-bold gradient-text">₱0</span>
                </div>
                <div class="bg-green-50 rounded-lg p-2.5 space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Received:</span>
                        <span id="receipt-received" class="font-semibold text-gray-800">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Change:</span>
                        <span id="receipt-change" class="font-semibold text-green-600">₱0.00</span>
                    </div>
                </div>
            </div>
            <button onclick="completeOrder()" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-2 rounded-lg font-semibold hover:from-purple-700 hover:to-purple-800 transition-all shadow mt-3 text-xs">
                Complete Order
            </button>
        </div>
    </div>
<script>
let order = [];
let totalPrice = 0;
let productName = '';
let productPrice = 0;
let selectedProduct = {}; 

function closeErrorModal() {
    document.getElementById('errorModal').classList.add('hidden');
    document.getElementById('errorModal').classList.remove('flex');
}
function openSizeModal(name, mediumPrice, largePrice, productID) {
    selectedProduct = {
        id: productID,
        name: name,
        mediumPrice: mediumPrice,
        largePrice: largePrice
    };
    productName = name;
    productPrice = mediumPrice;
    document.getElementById('addonsModal').classList.remove('hidden');
    document.getElementById('addonsModal').classList.add('flex');
}
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
        productID: selectedProduct.id,
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
function closeModal(specificModal) {
    if (specificModal) {
        document.getElementById(specificModal).classList.add('hidden');
        document.getElementById(specificModal).classList.remove('flex');
    } else {
        document.querySelectorAll('.modal-backdrop.flex').forEach(modal => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    }
}
function updateOrderList() {
    const orderList = document.getElementById('orderList');
    orderList.innerHTML = '';
    const groupedOrder = {};
    order.forEach((item, index) => {
        const key = `${item.product} (${item.size})`;
        const addonsKey = item.addons.length > 0 ? ` (Add-ons: ${item.addons.join(', ')})` : '';
        if (groupedOrder[key + addonsKey]) {
            groupedOrder[key + addonsKey].quantity += item.quantity;
            groupedOrder[key + addonsKey].price += item.price;
            groupedOrder[key + addonsKey].index.push(index);
        } else {
            groupedOrder[key + addonsKey] = { quantity: item.quantity, price: item.price, index: [index] };
        }
    });
    for (const [key, value] of Object.entries(groupedOrder)) {
        let itemDiv = document.createElement('div');
        itemDiv.className = 'bg-white rounded-lg p-2 shadow-sm';
        itemDiv.innerHTML = `
            <div class="flex justify-between items-center mb-1">
                <span class="font-medium text-gray-800 text-[11px] leading-tight">${key}</span>
                <span class="font-bold text-purple-600 text-xs">₱${value.price}</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <button onclick="decreaseQuantity('${key}', ${value.index[0]})" class="w-5 h-5 bg-gray-200 rounded text-[10px] font-bold text-gray-700 hover:bg-gray-300 transition-colors">-</button>
                    <span class="font-semibold text-gray-800 text-xs">${value.quantity}</span>
                    <button onclick="increaseQuantity('${key}', ${value.index[0]})" class="w-5 h-5 bg-purple-600 rounded text-[10px] font-bold text-white hover:bg-purple-700 transition-colors">+</button>
                </div>
                <button onclick="removeItem('${key}', ${value.index[0]})" class="text-red-500 hover:text-red-700 font-semibold text-[10px]">Remove</button>
            </div>
        `;
        orderList.appendChild(itemDiv);
    }
    document.getElementById('totalPrice').textContent = totalPrice;
}
function increaseQuantity(key, index) {
    const item = order[index];
    item.quantity += 1;
    totalPrice += item.price;
    updateOrderList();
}
function decreaseQuantity(key, index) {
    const item = order[index];
    if (item.quantity > 1) {
        item.quantity -= 1;
        totalPrice -= item.price;
        updateOrderList();
    } else {
        removeItem(key, index);
    }
}
function removeItem(key, index) {
    const item = order[index];
    totalPrice -= item.price * item.quantity;
    order.splice(index, 1);
    updateOrderList();
}
function checkout() {
    if (order.length === 0) {
        showValidationModal("Your order is empty! Please add items to your order before checking out.");
        return;
    }
    const orderSummaryList = document.getElementById('orderSummaryList');
    orderSummaryList.innerHTML = '';
    order.forEach(item => {
        let itemSummary = document.createElement('div');
        itemSummary.className = 'flex justify-between text-gray-700 text-xs';
        let addonsText = item.addons.length > 0 ? ` (Add-ons: ${item.addons.join(', ')})` : '';
        itemSummary.innerHTML = `<span>${item.product} (${item.size})${addonsText}</span><span class="font-semibold">₱${item.price}</span>`;
        orderSummaryList.appendChild(itemSummary);
    });
    document.getElementById('checkoutTotal').textContent = totalPrice;
    document.getElementById('checkoutModal').classList.remove('hidden');
    document.getElementById('checkoutModal').classList.add('flex');
}
function confirmOrder() {
    let receiptDetails = document.getElementById('receiptDetails');
    receiptDetails.innerHTML = '';
    const groupedOrder = {};
    totalPrice = 0;
    order.forEach(item => {
        const key = `${item.product} (${item.size})`;
        const addonsKey = item.addons.length > 0 ? ` (Add-ons: ${item.addons.join(', ')})` : '';
        if (groupedOrder[key + addonsKey]) {
            groupedOrder[key + addonsKey].quantity += item.quantity;
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
    for (const [key, value] of Object.entries(groupedOrder)) {
        totalPrice += value.price;
        let itemSummary = document.createElement('div');
        itemSummary.className = 'flex justify-between text-gray-700';
        itemSummary.innerHTML = `<span>${key} ${value.quantity > 1 ? value.quantity + 'x' : ''}</span><span class="font-semibold">₱${value.price.toFixed(2)}</span>`;
        receiptDetails.appendChild(itemSummary);
    }
    document.getElementById('receiptTotal').textContent = `₱${totalPrice.toFixed(2)}`;
    closeModal('checkoutModal');
    document.getElementById('paymentModal').classList.remove('hidden');
    document.getElementById('paymentModal').classList.add('flex');
}
function processPayment() {
    const amountReceivedInput = document.getElementById('amountReceived');
    const amountReceived = parseFloat(amountReceivedInput.value.trim());
    if (isNaN(amountReceived) || amountReceived <= 0) {
        showValidationModal("Please enter a valid amount received.");
        return;
    }
    if (amountReceived < totalPrice) {
        showValidationModal("Amount received is less than the total price!");
        return;
    }
    const change = amountReceived - totalPrice;
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

    function showErrorModal(message) {
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('errorModal').classList.remove('hidden');
        document.getElementById('errorModal').classList.add('flex');
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
            const orderID = data.receiptID; 
            const orderDate = data.salesDate;
            const transactionNumber = data.transactionNumber;
            const invoiceNumber = data.invoiceNumber;
            document.getElementById('transactionNumber').textContent = transactionNumber;
            document.getElementById('invoiceNumber').textContent = invoiceNumber;
            document.getElementById('orderDate').textContent = orderDate;
            document.getElementById('receipt-received').textContent = `₱${amountReceived.toFixed(2)}`;
            document.getElementById('receipt-change').textContent = `₱${change.toFixed(2)}`;
            closeModal('paymentModal');
            document.getElementById('receiptModal').classList.remove('hidden');
            document.getElementById('receiptModal').classList.add('flex');
        } else {
            showErrorModal(`Error saving order: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal("Error saving order: Unable to connect to the server.");
    });
    amountReceivedInput.value = '';
}
function showValidationModal(message) {
    document.getElementById('validationMessage').textContent = message;
    document.getElementById('validationModal').classList.remove('hidden');
    document.getElementById('validationModal').classList.add('flex');
}
function closeValidationModal() {
    document.getElementById('validationModal').classList.add('hidden');
    document.getElementById('validationModal').classList.remove('flex');
}
function completeOrder() {
    order = [];
    totalPrice = 0;
    updateOrderList();
    closeModal();
}
function cancelOrder() {
    order = [];
    totalPrice = 0;
    updateOrderList();        
}
</script>
</body>
</html>