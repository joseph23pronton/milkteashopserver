<?php
require_once 'db_connection.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $category = $_POST['category'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $branch_id = $_POST['branch_id'];
    $expense_date = $_POST['expense_date'];
    $payment_method = $_POST['payment_method'];
    
    $sql = "INSERT INTO expenses (category, description, amount, branch_id, expense_date, payment_method, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssdiss", $category, $description, $amount, $branch_id, $expense_date, $payment_method);
    $stmt->execute();
    header("Location: expenses.php?success=1");
    exit();
}

$show_archived = isset($_GET['show_archived']) && $_GET['show_archived'] == '1';

if ($show_archived) {
    $expenses_query = "SELECT e.*, b.name as branch_name 
                       FROM expenses e 
                       LEFT JOIN branches b ON e.branch_id = b.id 
                       WHERE e.is_archived = 1
                       ORDER BY e.expense_date DESC";
} else {
    $expenses_query = "SELECT e.*, b.name as branch_name 
                       FROM expenses e 
                       LEFT JOIN branches b ON e.branch_id = b.id 
                       WHERE e.is_archived = 0 OR e.is_archived IS NULL
                       ORDER BY e.expense_date DESC";
}

$expenses = $mysqli->query($expenses_query);

$branches_query = "SELECT * FROM branches";
$branches = $mysqli->query($branches_query);

if ($show_archived) {
    $total_expenses_query = "SELECT SUM(amount) as total FROM expenses WHERE is_archived = 1";
} else {
    $total_expenses_query = "SELECT SUM(amount) as total FROM expenses WHERE is_archived = 0 OR is_archived IS NULL";
}

$total_expenses_result = $mysqli->query($total_expenses_query);
$total_expenses = $total_expenses_result ? $total_expenses_result->fetch_assoc()['total'] ?? 0 : 0;

$active_count_query = "SELECT COUNT(*) as count FROM expenses WHERE is_archived = 0 OR is_archived IS NULL";
$active_count = $mysqli->query($active_count_query)->fetch_assoc()['count'];

$archived_count_query = "SELECT COUNT(*) as count FROM expenses WHERE is_archived = 1";
$archived_count = $mysqli->query($archived_count_query)->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Finance Dashboard - Expenses Management</title>
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
        .archive-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .archive-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #1cc88a;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
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
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
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
                        <h1 class="h3 mb-0 text-gray-800">Expenses Management</h1>
                        <div class="d-flex align-items-center">
                            <div class="mr-3 d-flex align-items-center">
                                <span class="mr-2 font-weight-bold text-gray-700">Show Archived</span>
                                <label class="archive-toggle">
                                    <input type="checkbox" id="archiveToggle" <?php echo $show_archived ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addExpenseModal">
                                <i class="fas fa-plus"></i> Add Expense
                            </button>
                        </div>
                    </div>

                    <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        if($_GET['success'] == '1') {
                            echo 'Expense added successfully!';
                        } elseif($_GET['success'] == 'archived') {
                            echo 'Expense archived successfully!';
                        } elseif($_GET['success'] == 'restored') {
                            echo 'Expense restored successfully!';
                        }
                        ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        Error: <?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                <?php echo $show_archived ? 'Archived Total' : 'Total Expenses'; ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱<?php echo number_format($total_expenses, 2); ?></div>
                                        </div>
                                        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
                                            <i class="fas fa-peso-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Expenses</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $active_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Archived Expenses</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $archived_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-archive fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <?php echo $show_archived ? 'Archived Expenses' : 'Active Expenses'; ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="expensesTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Branch</th>
                                            <th>Date</th>
                                            <th>Payment Method</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $expenses->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><span class="badge badge-info"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                                            <td><strong>₱<?php echo number_format($row['amount'], 2); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['expense_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                            <td>
                                                <?php if ($show_archived): ?>
                                                    <button class="btn btn-sm btn-success" onclick="restoreExpense(<?php echo $row['id']; ?>)" title="Restore">
                                                        <i class="fas fa-undo"></i> Restore
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-warning" onclick="archiveExpense(<?php echo $row['id']; ?>)" title="Archive">
                                                        <i class="fas fa-archive"></i> Archive
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

    <div class="modal fade" id="addExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Expense</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control" required>
                                <option value="Raw Materials">Raw Materials</option>
                                <option value="Electricity">Electricity</option>
                                <option value="Water">Water</option>
                                <option value="Rent">Rent</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Branch</label>
                            <select name="branch_id" class="form-control" required>
                                <?php 
                                $branches->data_seek(0);
                                while($branch = $branches->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Expense Date</label>
                            <input type="date" name="expense_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Check">Check</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="add_expense" class="btn btn-primary">Save Expense</button>
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
            $('#expensesTable').DataTable({
                "order": [[5, "desc"]],
                "pageLength": 25
            });
        });

        $('#archiveToggle').on('change', function() {
            if (this.checked) {
                window.location.href = 'expenses.php?show_archived=1';
            } else {
                window.location.href = 'expenses.php';
            }
        });

        function archiveExpense(id) {
            if(confirm('Are you sure you want to archive this expense?')) {
                window.location.href = '/backend/archive_expense.php?id=' + id;
            }
        }

        function restoreExpense(id) {
            if(confirm('Are you sure you want to restore this expense?')) {
                window.location.href = '/backend/restore_expense.php?id=' + id;
            }
        }
    </script>
</body>
</html>