<?php
require_once 'db_connection.php';
date_default_timezone_set('Asia/Manila');

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_return'])) {
    $purchase_order_id = $_POST['purchase_order_id'];
    $ingredient_id = $_POST['ingredient_id'];
    $quantity_returned = $_POST['quantity_returned'];
    $reason = $_POST['reason'];
    $return_date = $_POST['return_date'];
    $refund_amount = $_POST['refund_amount'];
    $supplier_name = $_POST['supplier_name'];
    $branch_id = $_POST['branch_id'];
    
    $invoice = 'RET-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $sql = "INSERT INTO product_returns (invoice_number, purchase_order_id, ingredient_id, quantity_returned, reason, return_date, refund_amount, supplier_name, branch_id, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    
    $stmt->bind_param("siiissdsi", $invoice, $purchase_order_id, $ingredient_id, $quantity_returned, $reason, $return_date, $refund_amount, $supplier_name, $branch_id);
    
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    header("Location: returns.php?success=1");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_return'])) {
    $return_id = $_POST['return_id'];
    
    $return_query = "SELECT * FROM product_returns WHERE id = ?";
    $stmt = $mysqli->prepare($return_query);
    $stmt->bind_param("i", $return_id);
    $stmt->execute();
    $return_data = $stmt->get_result()->fetch_assoc();
    
    if($return_data && $return_data['status'] == 'pending') {
        $mysqli->begin_transaction();
        
        try {
            $update_status = "UPDATE product_returns SET status = 'approved' WHERE id = ?";
            $stmt = $mysqli->prepare($update_status);
            $stmt->bind_param("i", $return_id);
            $stmt->execute();
            
            $insert_sales = "INSERT INTO sales (receiptID, price, quantity, totalPrice, sales_date) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($insert_sales);
            
            $price = floatval($return_data['refund_amount']);
            $quantity = -1;
            $totalPrice = floatval($return_data['refund_amount']) * -1;
            
            $stmt->bind_param("sdids", $return_data['invoice_number'], $price, $quantity, $totalPrice, $return_data['return_date']);
            $stmt->execute();
            
            $mysqli->commit();
            header("Location: returns.php?approved=1");
            exit();
        } catch (Exception $e) {
            $mysqli->rollback();
            die("Transaction failed: " . $e->getMessage());
        }
    }
}

$returns_query = "SELECT pr.*, b.name as branch_name, ih.name as ingredient_name, ih.unit as ingredient_unit
                  FROM product_returns pr
                  LEFT JOIN branches b ON pr.branch_id = b.id
                  LEFT JOIN ingredientsheader ih ON pr.ingredient_id = ih.id
                  ORDER BY pr.created_at DESC";
$returns = $mysqli->query($returns_query);

if (!$returns) {
    die("Query failed: " . $mysqli->error);
}

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
    <title>Finance Dashboard - Returns</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
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
        .border-left-danger {
            border-left: 0.25rem solid #e74a3b !important;
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
        .text-danger {
            color: #e74a3b !important;
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
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Finance Admin</span>
                                <i class="fas fa-user-circle fa-2x"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="../logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Product Returns</h1>
                    </div>

                    <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Return request created successfully!
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($_GET['approved'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Return approved successfully! Refund amount has been added to sales.
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Product Returns List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="returnsTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Ingredient</th>
                                            <th>Quantity</th>
                                            <th>Reason</th>
                                            <th>Refund Amount</th>
                                            <th>Branch</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $returns->fetch_assoc()): ?>
                                        <tr>
                                            <td><code class="text-danger"><?php echo htmlspecialchars($row['invoice_number']); ?></code></td>
                                            <td><?php echo htmlspecialchars($row['ingredient_name']); ?></td>
                                            <td><?php echo (int)$row['quantity_returned']; ?></td>
                                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                            <td><strong>₱<?php echo number_format($row['refund_amount'], 2); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['return_date'])); ?></td>
                                            <td>
                                                <?php if($row['status'] == 'approved'): ?>
                                                    <span class="badge badge-success">Approved</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="viewReturnReceipt(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['invoice_number']); ?>', '<?php echo htmlspecialchars($row['ingredient_name']); ?>', <?php echo $row['quantity_returned']; ?>, '<?php echo htmlspecialchars($row['ingredient_unit'] ?? ''); ?>', <?php echo $row['refund_amount']; ?>, '<?php echo htmlspecialchars($row['reason']); ?>', '<?php echo date('M d, Y', strtotime($row['return_date'])); ?>', '<?php echo htmlspecialchars($row['branch_name']); ?>', '<?php echo htmlspecialchars($row['supplier_name']); ?>')">
                                                    <i class="fas fa-receipt"></i>
                                                </button>
                                                <?php if($row['status'] == 'pending'): ?>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to approve this return? The refund amount will be added to sales.');">
                                                    <input type="hidden" name="return_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="approve_return" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
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

    <div class="modal fade" id="returnReceiptModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-receipt"></i> Return Receipt
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="returnReceiptContent">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printReturnReceipt()">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#returnsTable').DataTable({
                order: [[7, 'desc']],
                pageLength: 25
            });
        });

        function viewReturnReceipt(returnId, invoiceNumber, ingredientName, quantity, unit, refundAmount, reason, returnDate, branchName, supplierName) {
            const receiptHTML = `
                <div class="receipt-header text-center mb-4">
                    <h4 class="font-weight-bold text-danger">PRODUCT RETURN RECEIPT</h4>
                    <p class="mb-0"><strong>Invoice Number:</strong> ${invoiceNumber}</p>
                    <p class="mb-0"><strong>Date:</strong> ${returnDate}</p>
                </div>
                
                <div class="receipt-details mb-3">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Branch:</strong></td>
                            <td>${branchName}</td>
                        </tr>
                        <tr>
                            <td><strong>Supplier:</strong></td>
                            <td>${supplierName}</td>
                        </tr>
                    </table>
                </div>

                <table class="receipt-table table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Ingredient</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${ingredientName}</td>
                            <td>${quantity} ${unit}</td>
                            <td>${reason}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="receipt-total mt-4">
                    <h5 class="text-right">
                        <strong>Total Refund Amount: ₱${parseFloat(refundAmount).toFixed(2)}</strong>
                    </h5>
                </div>

                <div class="text-center mt-4">
                    <p class="mb-0"><em>This is a system-generated return receipt.</em></p>
                    <p class="mb-0 text-muted small">Generated on <?php echo date('F d, Y h:i A'); ?></p>
                </div>
            `;
            
            $('#returnReceiptContent').html(receiptHTML);
            $('#returnReceiptModal').modal('show');
        }

        function printReturnReceipt() {
            const printContent = document.getElementById('returnReceiptContent').innerHTML;
            const printWindow = window.open('', '', 'height=600,width=800');
            
            printWindow.document.write('<html><head><title>Return Receipt</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
            printWindow.document.write('.receipt-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #e74a3b; padding-bottom: 10px; }');
            printWindow.document.write('.receipt-details { margin-bottom: 20px; }');
            printWindow.document.write('.receipt-table { width: 100%; border-collapse: collapse; margin: 20px 0; }');
            printWindow.document.write('.receipt-table th, .receipt-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }');
            printWindow.document.write('.receipt-table th { background-color: #f8f9fa; font-weight: bold; }');
            printWindow.document.write('.receipt-total { text-align: right; font-weight: bold; font-size: 18px; margin-top: 20px; padding-top: 10px; border-top: 2px solid #333; }');
            printWindow.document.write('table.table-borderless td { padding: 5px; border: none; }');
            printWindow.document.write('@media print { .no-print { display: none; } }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            
            printWindow.document.close();
            printWindow.print();
        }

        $('#returnReceiptModal').on('hidden.bs.modal', function() {
            $('#returnReceiptContent').html('');
        });
    </script>
</body>
</html>