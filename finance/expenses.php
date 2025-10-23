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

$expenses_query = "SELECT e.*, b.name as branch_name 
                   FROM expenses e 
                   LEFT JOIN branches b ON e.branch_id = b.id 
                   ORDER BY e.expense_date DESC";
$expenses = $mysqli->query($expenses_query);

$branches_query = "SELECT * FROM branches";
$branches = $mysqli->query($branches_query);

$total_expenses_query = "SELECT SUM(amount) as total FROM expenses";
$total_expenses_result = $mysqli->query($total_expenses_query);
$total_expenses = $total_expenses_result ? $total_expenses_result->fetch_assoc()['total'] ?? 0 : 0;
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
                        <h1 class="h3 mb-0 text-gray-800">Expenses Management</h1>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addExpenseModal">
                            <i class="fas fa-plus"></i> Add Expense
                        </button>
                    </div>

                    <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Expense added successfully!
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Total Expenses: ₱<?php echo number_format($total_expenses, 2); ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Expenses List</h6>
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
                                            <td><span class="badge badge-info"><?php echo $row['category']; ?></span></td>
                                            <td><?php echo $row['description']; ?></td>
                                            <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                                            <td><?php echo $row['branch_name']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['expense_date'])); ?></td>
                                            <td><?php echo $row['payment_method']; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger" onclick="deleteExpense(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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
                                <option value="<?php echo $branch['id']; ?>"><?php echo $branch['name']; ?></option>
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
            $('#expensesTable').DataTable();
        });

        function deleteExpense(id) {
            if(confirm('Are you sure you want to delete this expense?')) {
                window.location.href = 'delete_expense.php?id=' + id;
            }
        }
    </script>
</body>
</html>