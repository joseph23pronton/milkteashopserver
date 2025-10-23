<?php
require_once 'db_connection.php';

$restockorders_query = "SELECT r.*, b.name as branch_name, ih.name as ingredient_name, ih.price_per_unit, ih.unit as ingredient_unit,
                        (r.restock_amount * COALESCE(ih.price_per_unit, 0)) AS total_cost,
                        COALESCE(r.invoice_number, CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(r.id, 4, '0'))) AS invoice_number
                        FROM restockorder r
                        LEFT JOIN branches b ON r.branchID = b.id
                        LEFT JOIN ingredientsheader ih ON r.ingredientsID = ih.id
                        ORDER BY r.created_at DESC";
$restockorders = $mysqli->query($restockorders_query);

$pending_query = "SELECT COUNT(*) as count FROM restockorder WHERE is_confirmed = 0";
$pending_count = $mysqli->query($pending_query)->fetch_assoc()['count'];

$confirmed_query = "SELECT COUNT(*) as count FROM restockorder WHERE is_confirmed = 1";
$confirmed_count = $mysqli->query($confirmed_query)->fetch_assoc()['count'];

$total_pending_query = "SELECT SUM(r.restock_amount * COALESCE(ih.price_per_unit, 0)) AS total_pending_cost
                        FROM restockorder r
                        LEFT JOIN ingredientsheader ih ON r.ingredientsID = ih.id
                        WHERE r.is_confirmed = 0";
$total_pending_result = $mysqli->query($total_pending_query);
$total_pending_row = $total_pending_result->fetch_assoc();
$total_pending_cost = $total_pending_row['total_pending_cost'] ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Finance Dashboard - Supplies Management</title>
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
        .btn-group .btn {
            margin-right: 2px;
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
                        <h1 class="h3 mb-0 text-gray-800">Supplies Management</h1>
                    </div>

                    <?php if(isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Order approved successfully!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error: <?php echo htmlspecialchars($_GET['error']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Orders</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Confirmed Orders</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $confirmed_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Pending Cost</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱<?php echo number_format($total_pending_cost, 2); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-peso-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Restock Orders</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="suppliesTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Ingredient</th>
                                            <th>Branch</th>
                                            <th>Amount</th>
                                            <th>Price/Unit</th>
                                            <th>Total Cost</th>
                                            <th>Requested By</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $restockorders->fetch_assoc()): ?>
                                        <tr>
                                            <td><code class="text-primary"><?php echo htmlspecialchars($row['invoice_number']); ?></code></td>
                                            <td><?php echo htmlspecialchars($row['ingredient_name']); ?></td>
                                            <td><?php echo $row['branch_name'] ? htmlspecialchars($row['branch_name']) : '<span class="text-muted">N/A</span>'; ?></td>
                                            <td><?php echo (int)$row['restock_amount']; ?> <?php echo htmlspecialchars($row['ingredient_unit'] ?? ''); ?></td>
                                            <td>
                                                <?php if($row['price_per_unit'] > 0): ?>
                                                    ₱<?php echo number_format($row['price_per_unit'], 2); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Not set</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong>
                                                    <?php if($row['total_cost'] > 0): ?>
                                                        ₱<?php echo number_format($row['total_cost'], 2); ?>
                                                    <?php else: ?>
                                                        ₱0.00
                                                    <?php endif; ?>
                                                </strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['requested_by']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <?php if($row['is_confirmed']): ?>
                                                    <span class="badge badge-success">Confirmed</span>
                                                <?php elseif($row['is_accepted']): ?>
                                                    <span class="badge badge-info">Accepted</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-info btn-sm" 
                                                            onclick="viewReceipt(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['invoice_number']); ?>')"
                                                            title="View Receipt">
                                                        <i class="fas fa-receipt"></i>
                                                    </button>
                                                    
                                                    <?php if(!$row['is_confirmed']): ?>
                                                        <form method="POST" action="../backend/accept_restock.php" style="display: inline;">
                                                            <input type="hidden" name="restockAmount" value="<?php echo $row['restock_amount']; ?>">
                                                            <input type="hidden" name="branchID" value="<?php echo $row['branchID']; ?>">
                                                            <input type="hidden" name="ingredientsID" value="<?php echo $row['ingredientsID']; ?>">
                                                            <input type="hidden" name="restock_id" value="<?php echo $row['id']; ?>">
                                                            <button type="submit" class="btn btn-success btn-sm" title="Approve Order" 
                                                                    onclick="return confirm('Approve this restock order for ₱<?php echo number_format($row['total_cost'], 2); ?>?')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary btn-sm" disabled title="Already Confirmed">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
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

    <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">
                        <i class="fas fa-receipt"></i> Restock Receipt
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="receiptContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printReceipt()">
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
            $('#suppliesTable').DataTable({
                "order": [[7, "desc"]],
                "pageLength": 25
            });
        });

        function viewReceipt(restockId, invoiceNumber) {
            $('#receiptModal').modal('show');
            
            $.ajax({
                url: '../backend/generate_receipt.php',
                method: 'POST',
                data: {
                    restock_id: restockId,
                    invoice_number: invoiceNumber
                },
                success: function(response) {
                    $('#receiptContent').html(response);
                },
                error: function() {
                    $('#receiptContent').html('<div class="alert alert-danger">Error loading receipt. Please try again.</div>');
                }
            });
        }

        function printReceipt() {
            const printContent = document.getElementById('receiptContent').innerHTML;
            const printWindow = window.open('', '', 'height=600,width=800');
            
            printWindow.document.write('<html><head><title>Restock Receipt</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
            printWindow.document.write('.receipt-header { text-align: center; margin-bottom: 20px; }');
            printWindow.document.write('.receipt-details { margin-bottom: 20px; }');
            printWindow.document.write('.receipt-table { width: 100%; border-collapse: collapse; }');
            printWindow.document.write('.receipt-table th, .receipt-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
            printWindow.document.write('.receipt-table th { background-color: #f2f2f2; }');
            printWindow.document.write('.receipt-total { text-align: right; font-weight: bold; font-size: 18px; }');
            printWindow.document.write('@media print { .no-print { display: none; } }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            
            printWindow.document.close();
            printWindow.print();
        }

        $('#receiptModal').on('hidden.bs.modal', function() {
            $('#receiptContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
        });
    </script>
</body>
</html>