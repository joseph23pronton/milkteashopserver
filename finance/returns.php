<?php
require_once 'db_connection.php';

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
    $stmt->bind_param("siiisdsi", $invoice, $purchase_order_id, $ingredient_id, $quantity_returned, $reason, $return_date, $refund_amount, $supplier_name, $branch_id);
    $stmt->execute();
    header("Location: returns.php?success=1");
    exit();
}

$returns_query = "SELECT pr.*, b.name as branch_name, ih.name as ingredient_name 
                  FROM product_returns pr
                  LEFT JOIN branches b ON pr.branch_id = b.id
                  LEFT JOIN ingredientsheader ih ON pr.ingredient_id = ih.id
                  ORDER BY pr.created_at DESC";
$returns = $mysqli->query($returns_query);

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
                        <h1 class="h3 mb-0 text-gray-800">Product Returns</h1>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addReturnModal">
                            <i class="fas fa-plus"></i> New Return
                        </button>
                    </div>

                    <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Return request created successfully!
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
                                            <th>Supplier</th>
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
                                            <td><?php echo $row['invoice_number']; ?></td>
                                            <td><?php echo $row['supplier_name']; ?></td>
                                            <td><?php echo $row['ingredient_name']; ?></td>
                                            <td><?php echo $row['quantity_returned']; ?></td>
                                            <td><?php echo $row['reason']; ?></td>
                                            <td>₱<?php echo number_format($row['refund_amount'], 2); ?></td>
                                            <td><?php echo $row['branch_name']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['return_date'])); ?></td>
                                            <td>
                                                <?php if($row['status'] == 'approved'): ?>
                                                    <span class="badge badge-success">Approved</span>
                                                <?php elseif($row['status'] == 'rejected'): ?>
                                                    <span class="badge badge-danger">Rejected</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="viewReturn(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($row['status'] == 'pending'): ?>
                                                <button class="btn btn-sm btn-success" onclick="approveReturn(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-check"></i>
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

    <div class="modal fade" id="addReturnModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">New Product Return</h5>
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
                                    <label>Purchase Order ID (Optional)</label>
                                    <input type="number" name="purchase_order_id" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ingredient</label>
                                    <select name="ingredient_id" class="form-control" required>
                                        <?php 
                                        $ingredients->data_seek(0);
                                        while($ing = $ingredients->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $ing['id']; ?>"><?php echo $ing['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quantity Returned</label>
                                    <input type="number" name="quantity_returned" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Refund Amount</label>
                                    <input type="number" step="0.01" name="refund_amount" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Reason for Return</label>
                            <select name="reason" class="form-control" required>
                                <option value="Damaged">Damaged Products</option>
                                <option value="Expired">Expired</option>
                                <option value="Wrong Item">Wrong Item Delivered</option>
                                <option value="Quality Issue">Quality Issue</option>
                                <option value="Overstocked">Overstocked</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Return Date</label>
                            <input type="date" name="return_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="add_return" class="btn btn-primary">Submit Return</button>
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
            $('#returnsTable').DataTable();
        });

        function viewReturn(id) {
            window.location.href = 'view_return.php?id=' + id;
        }

        function approveReturn(id) {
            if(confirm('Are you sure you want to approve this return?')) {
                window.location.href = 'approve_return.php?id=' + id;
            }
        }
    </script>
</body>
</html>