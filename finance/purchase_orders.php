<?php
require_once 'db_connection.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_purchase'])) {
    $supplier_name = $_POST['supplier_name'];
    $ingredient_id = $_POST['ingredient_id'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $total_amount = $quantity * $unit_price;
    $payment_status = $_POST['payment_status'];
    $amount_paid = $_POST['amount_paid'];
    $branch_id = $_POST['branch_id'];
    $purchase_date = $_POST['purchase_date'];
    $notes = $_POST['notes'];
    
    $invoice = 'PO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $sql = "INSERT INTO purchase_orders (invoice_number, supplier_name, ingredient_id, quantity, unit_price, total_amount, payment_status, amount_paid, balance, branch_id, purchase_date, notes, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($sql);
    $balance = $total_amount - $amount_paid;
    $stmt->bind_param("ssiiddsdiss", $invoice, $supplier_name, $ingredient_id, $quantity, $unit_price, $total_amount, $payment_status, $amount_paid, $balance, $branch_id, $purchase_date, $notes);
    $stmt->execute();
    header("Location: purchase_orders.php?success=1");
    exit();
}

$purchase_orders_query = "SELECT po.*, b.name as branch_name, ih.name as ingredient_name 
                          FROM purchase_orders po
                          LEFT JOIN branches b ON po.branch_id = b.id
                          LEFT JOIN ingredientsheader ih ON po.ingredient_id = ih.id
                          ORDER BY po.created_at DESC";
$purchase_orders = $mysqli->query($purchase_orders_query);

$ingredients_query = "SELECT * FROM ingredientsheader WHERE is_archived = 0";
$ingredients = $mysqli->query($ingredients_query);

$branches_query = "SELECT * FROM branches";
$branches = $mysqli->query($branches_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Finance Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        #wrapper {
            display: flex;
        }
        #wrapper #content-wrapper {
            overflow-x: hidden;
            width: 100%;
            background-color: #f8f9fc;
        }
        .sidebar {
            width: 14rem !important;
            min-height: 100vh;
        }
        .sidebar .nav-item .nav-link {
            display: block;
            width: 100%;
            text-align: left;
            padding: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar .nav-item .nav-link span {
            font-size: 0.85rem;
            display: inline;
        }
        .sidebar .nav-item .nav-link i {
            font-size: 0.85rem;
            margin-right: 0.25rem;
        }
        .sidebar .nav-item .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-item.active .nav-link {
            color: #fff;
            font-weight: 700;
        }
        .sidebar-dark {
            background-color: #1cc88a;
        }
        .bg-gradient-success {
            background-color: #1cc88a;
            background-image: linear-gradient(180deg, #1cc88a 10%, #13855c 100%);
            background-size: cover;
        }
        .sidebar .sidebar-brand {
            height: 4.375rem;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            z-index: 1;
            color: #fff;
        }
        .sidebar .sidebar-brand-icon {
            font-size: 2rem;
        }
        .sidebar .sidebar-brand-text {
            display: block;
        }
        .sidebar .sidebar-heading {
            text-align: center;
            padding: 0 1rem;
            font-weight: 800;
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
        }
        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 0 1rem 1rem;
        }
        .sidebar #sidebarToggle {
            width: 2.5rem;
            height: 2.5rem;
            text-align: center;
            margin-bottom: 1rem;
            cursor: pointer;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .sidebar #sidebarToggle::after {
            font-weight: 900;
            content: '\f104';
            font-family: 'Font Awesome 5 Free';
            margin-right: 0.1rem;
            color: rgba(255, 255, 255, 0.5);
        }
        .sidebar #sidebarToggle:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }
        .topbar {
            height: 4.375rem;
        }
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        .text-primary {
            color: #4e73df !important;
        }
        .text-success {
            color: #1cc88a !important;
        }
        .text-info {
            color: #36b9cc !important;
        }
        .text-warning {
            color: #f6c23e !important;
        }
    </style>
</head> 
<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                </nav>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Purchase Orders</h1>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addPurchaseModal">
                            <i class="fas fa-plus"></i> New Purchase Order
                        </button>
                    </div>

                    <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Purchase order created successfully!
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Purchase Orders List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="purchaseTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Supplier</th>
                                            <th>Ingredient</th>
                                            <th>Quantity</th>
                                            <th>Total Amount</th>
                                            <th>Amount Paid</th>
                                            <th>Balance</th>
                                            <th>Status</th>
                                            <th>Branch</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $purchase_orders->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['invoice_number']; ?></td>
                                            <td><?php echo $row['supplier_name']; ?></td>
                                            <td><?php echo $row['ingredient_name']; ?></td>
                                            <td><?php echo $row['quantity']; ?></td>
                                            <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                            <td>₱<?php echo number_format($row['amount_paid'], 2); ?></td>
                                            <td>₱<?php echo number_format($row['balance'], 2); ?></td>
                                            <td>
                                                <?php if($row['payment_status'] == 'paid'): ?>
                                                    <span class="badge badge-success">Paid</span>
                                                <?php elseif($row['payment_status'] == 'partial'): ?>
                                                    <span class="badge badge-warning">Partial</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $row['branch_name']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['purchase_date'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="viewPO(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($row['balance'] > 0): ?>
                                                <button class="btn btn-sm btn-success" onclick="addPayment(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPurchaseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">New Purchase Order</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier Name</label>
                                    <input type="text" name="supplier_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Branch</label>
                                    <select name="branch_id" class="form-control" required>
                                        <?php 
                                        $branches->data_seek(0);
                                        while($branch = $branches->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $branch['id']; ?>"><?php echo $branch['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ingredient</label>
                                    <select name="ingredient_id" class="form-control" required>
                                        <?php while($ing = $ingredients->fetch_assoc()): ?>
                                        <option value="<?php echo $ing['id']; ?>"><?php echo $ing['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" name="quantity" class="form-control" id="quantity" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Unit Price</label>
                                    <input type="number" step="0.01" name="unit_price" class="form-control" id="unit_price" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Amount</label>
                                    <input type="text" class="form-control" id="total_amount" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select name="payment_status" class="form-control" required>
                                        <option value="pending">Pending</option>
                                        <option value="partial">Partial Payment</option>
                                        <option value="paid">Fully Paid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount Paid</label>
                                    <input type="number" step="0.01" name="amount_paid" class="form-control" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="add_purchase" class="btn btn-primary">Create Purchase Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#purchaseTable').DataTable();
            
            $('#quantity, #unit_price').on('input', function() {
                var qty = parseFloat($('#quantity').val()) || 0;
                var price = parseFloat($('#unit_price').val()) || 0;
                $('#total_amount').val((qty * price).toFixed(2));
            });
        });

        function viewPO(id) {
            window.location.href = 'view_purchase.php?id=' + id;
        }

        function addPayment(id) {
            window.location.href = 'add_payment.php?id=' + id;
        }
    </script>
</body>
</html>