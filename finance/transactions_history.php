<?php
require_once 'db_connection.php';

$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$filter_branch = isset($_GET['branch']) ? $_GET['branch'] : 'all';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$where_conditions = [];
if($filter_type != 'all') {
    $where_conditions[] = "type = '" . $mysqli->real_escape_string($filter_type) . "'";
}
if($filter_branch != 'all') {
    $where_conditions[] = "branch_id = " . intval($filter_branch);
}
if($date_from) {
    $where_conditions[] = "date >= '" . $mysqli->real_escape_string($date_from) . "'";
}
if($date_to) {
    $where_conditions[] = "date <= '" . $mysqli->real_escape_string($date_to) . "'";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$transactions_query = "SELECT * FROM (
    SELECT 
        'Sale' as type,
        receiptID as reference,
        totalPrice as amount,
        sales_date as date,
        branchID as branch_id,
        CONCAT(productName, ' - ', customerName) as description
    FROM sales
    UNION ALL
    SELECT 
        'Expense' as type,
        CONCAT('EXP-', id) as reference,
        amount,
        created_at as date,
        branch_id,
        description
    FROM expenses
    UNION ALL
    SELECT 
        'Payroll' as type,
        CONCAT('PAY-', id) as reference,
        net_pay as amount,
        created_at as date,
        NULL as branch_id,
        CONCAT('Employee ID: ', employee_id) as description
    FROM payroll WHERE status = 'paid'
    UNION ALL
    SELECT 
        'Purchase Order' as type,
        COALESCE(r.invoice_number, CONCAT('INV-', DATE_FORMAT(r.created_at, '%Y%m%d'), '-', LPAD(r.id, 4, '0'))) as reference,
        (r.restock_amount * COALESCE(ih.price_per_unit, 0)) as amount,
        r.created_at as date,
        r.branchID as branch_id,
        CONCAT(ih.name, ' (', r.restock_amount, ' ', ih.unit, ')') as description
    FROM restockOrder r
    LEFT JOIN ingredientsHeader ih ON r.ingredientsID = ih.id
    WHERE r.is_confirmed = 1
) as all_transactions
$where_clause
ORDER BY date DESC";

$transactions = $mysqli->query($transactions_query);

$branches_query = "SELECT * FROM branches";
$branches = $mysqli->query($branches_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Finance Dashboard - Transactions History</title>
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
                        <h1 class="h3 mb-0 text-gray-800">Transactions History</h1>
                       
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="form-inline">
                                <div class="form-group mr-3 mb-2">
                                    <label class="mr-2">Type:</label>
                                    <select name="type" class="form-control">
                                        <option value="all">All</option>
                                        <option value="Sale" <?php echo $filter_type == 'Sale' ? 'selected' : ''; ?>>Sales</option>
                                        <option value="Expense" <?php echo $filter_type == 'Expense' ? 'selected' : ''; ?>>Expenses</option>
                                        <option value="Payroll" <?php echo $filter_type == 'Payroll' ? 'selected' : ''; ?>>Payroll</option>
                                        <option value="Purchase Order" <?php echo $filter_type == 'Purchase Order' ? 'selected' : ''; ?>>Purchase Orders</option>
                                    </select>
                                </div>
                                <div class="form-group mr-3 mb-2">
                                    <label class="mr-2">Branch:</label>
                                    <select name="branch" class="form-control">
                                        <option value="all">All</option>
                                        <?php 
                                        if($branches && $branches->num_rows > 0) {
                                            while($branch = $branches->fetch_assoc()): ?>
                                        <option value="<?php echo $branch['id']; ?>" <?php echo $filter_branch == $branch['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($branch['name']); ?>
                                        </option>
                                        <?php endwhile; 
                                        } ?>
                                    </select>
                                </div>
                                <div class="form-group mr-3 mb-2">
                                    <label class="mr-2">From:</label>
                                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                                </div>
                                <div class="form-group mr-3 mb-2">
                                    <label class="mr-2">To:</label>
                                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="sales_history.php" class="btn btn-secondary mb-2 ml-2">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">All Transactions</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="transactionsTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Reference</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($transactions && $transactions->num_rows > 0):
                                            while($row = $transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php if($row['type'] == 'Sale'): ?>
                                                    <span class="badge badge-success">Sale</span>
                                                <?php elseif($row['type'] == 'Expense'): ?>
                                                    <span class="badge badge-danger">Expense</span>
                                                <?php elseif($row['type'] == 'Payroll'): ?>
                                                    <span class="badge badge-info">Payroll</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Purchase</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['reference']); ?></td>
                                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                                            <td>
                                                <?php if($row['type'] == 'Sale'): ?>
                                                    <span class="text-success">+₱<?php echo number_format($row['amount'], 2); ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger">-₱<?php echo number_format($row['amount'], 2); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-order="<?php echo strtotime($row['date']); ?>"><?php echo date('M d, Y H:i', strtotime($row['date'])); ?></td>
                                        </tr>
                                        <?php endwhile; 
                                        else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No transactions found</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
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
            $('#transactionsTable').DataTable({
                order: [[4, 'desc']],
                pageLength: 25
            });
        });
    </script>
</body>
</html>