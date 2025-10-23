<?php
session_start();
include_once '../database.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

if (!isset($_POST['restock_id']) || !isset($_POST['invoice_number'])) {
    die("Invalid request");
}

$restock_id = (int)$_POST['restock_id'];
$invoice_number = $_POST['invoice_number'];

// Fetch restock order details
$query = "SELECT r.*, b.name as branch_name, b.city as branch_city, 
                 ih.name as ingredient_name, ih.unit as ingredient_unit, ih.price_per_unit,
                 CONCAT(u.fname, ' ', u.lname) as requested_by_name
          FROM restockOrder r
          LEFT JOIN branches b ON r.branchID = b.id
          LEFT JOIN ingredientsHeader ih ON r.ingredientsID = ih.id
          LEFT JOIN users u ON r.requested_by = u.id
          WHERE r.id = ?";

$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die("SQL Error: " . $mysqli->error . "<br>Query: " . htmlspecialchars($query));
}
$stmt->bind_param("i", $restock_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found");
}

$order = $result->fetch_assoc();
$total_cost = $order['restock_amount'] * $order['price_per_unit'];
$request_date = isset($order['created_at']) ? date('F j, Y \a\t g:i A', strtotime($order['created_at'])) : date('F j, Y \a\t g:i A');
?>

<div class="receipt-container">


    <!-- Receipt Header -->
    <div class="receipt-header">
        <h2><i class="fas fa-store"></i> Milktea Shop</h2>
        <h4>Restock Receipt</h4>
        <hr>
    </div>

    <!-- Order Information -->
    <div class="receipt-details">
        <div class="row">
            <div class="col-md-6">
                <strong>Invoice Number:</strong><br>
                <code style="font-size: 16px; color: #007bff;"><?= htmlspecialchars($invoice_number) ?></code>
            </div>
            <div class="col-md-6 text-right">
                <strong>Date Issued:</strong><br>
                <?= $request_date ?>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <strong>Branch:</strong><br>
                <?= htmlspecialchars($order['branch_name']) ?><br>
                <small class="text-muted"><?= htmlspecialchars($order['branch_city']) ?></small>
            </div>
            <div class="col-md-6 text-right">
                <strong>Requested By:</strong><br>
                <?= htmlspecialchars($order['requested_by_name']) ?>
            </div>
        </div>
    </div>

    <!-- Order Details Table -->
    <table class="receipt-table table table-bordered">
        <thead class="thead-light">
            <tr>
                <th>Item Description</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($order['ingredient_name']) ?></strong><br>
                    <small class="text-muted">Unit: <?= htmlspecialchars($order['ingredient_unit']) ?></small>
                </td>
                <td class="text-center">
                    <?= (int)$order['restock_amount'] ?> <?= htmlspecialchars($order['ingredient_unit']) ?>
                </td>
                <td class="text-right">
                    ₱<?= number_format($order['price_per_unit'], 2) ?>
                </td>
                <td class="text-right">
                    <strong>₱<?= number_format($total_cost, 2) ?></strong>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="receipt-total mt-4">
        <div class="row">
            <div class="col-md-8"></div>
            <div class="col-md-4">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Total Amount:</strong></td>
                        <td class="text-right"><strong>₱<?= number_format($total_cost, 2) ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Information -->
    <div class="receipt-status mt-4">
        <?php if ($order['is_confirmed'] == 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-clock"></i> <strong>Status:</strong> Pending Confirmation
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <strong>Status:</strong> Confirmed
                <?php if (isset($order['confirmed_at']) && $order['confirmed_at']): ?>
                    <br><small>Confirmed on: <?= date('F j, Y \a\t g:i A', strtotime($order['confirmed_at'])) ?></small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer Notes -->
    <div class="receipt-footer mt-4 text-center text-muted">
        <hr>
        <p><small>This is a computer-generated receipt. No signature required.</small></p>
        <p><small>Generated on: <?= date('F j, Y \a\t g:i A') ?></small></p>
    </div>
</div>

<style>
@media print {
    .receipt-container { margin: 0 !important; }
}

.receipt-container {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
}

.receipt-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background-color: #f8f9fc;
    border-radius: 8px;
}

.receipt-table {
    margin-top: 20px;
}

.receipt-table th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.receipt-total table {
    background-color: #f8f9fc;
}

.alert {
    border-radius: 8px;
}

code {
    background-color: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
}


</style>

<script>
// Hide action buttons when printing
function printReceipt() {
    window.print();
}
</script>

<?php
$stmt->close();
$mysqli->close();
?>