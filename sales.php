<?php
$screen = 'branch';
ob_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Branch ID is required.");
}
$mysqli = include('database.php');
$branch_id = $_GET['id'];

$sql = "SELECT name, city FROM branches WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $branch_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No branch found with the provided ID.");
}

$branch = $result->fetch_assoc();
$branch_name = $branch['name'];
$branch_city = $branch['city'];

$table_name = strtolower('name');

$filter_type = $_GET['filter'] ?? 'all';
$selected_date = $_GET['date'] ?? date('Y-m-d');

$date_condition = "1=1";
$filter_label = "All Time";
$table_date_condition = "1=1";
$bind_value = null;
$table_bind_value = null;

switch($filter_type) {
    case 'day':
        $date_condition = "DATE(s.sales_date) = ?";
        $table_date_condition = "DATE(s.sales_date) = ?";
        $filter_label = "Daily";
        $bind_value = $selected_date;
        $table_bind_value = $selected_date;
        break;
    case 'month':
        $month = date('Y-m', strtotime($selected_date));
        $date_condition = "DATE_FORMAT(s.sales_date, '%Y-%m') = ?";
        $table_date_condition = "DATE_FORMAT(s.sales_date, '%Y-%m') = ?";
        $filter_label = "Monthly";
        $bind_value = $month;
        $table_bind_value = $month;
        break;
    case 'year':
        $year = date('Y', strtotime($selected_date));
        $date_condition = "YEAR(s.sales_date) = ?";
        $table_date_condition = "YEAR(s.sales_date) = ?";
        $filter_label = "Yearly";
        $bind_value = $year;
        $table_bind_value = $year;
        break;
    case 'all':
        $date_condition = "1=1";
        $table_date_condition = "1=1";
        $filter_label = "All Time";
        $bind_value = null;
        $table_bind_value = null;
        break;
}

$sql_earnings = "
    SELECT SUM(s.quantity * s.price) AS total_earnings
    FROM sales s
    WHERE s.branchID = ? AND $date_condition
";
$stmt_earnings = $mysqli->prepare($sql_earnings);
if ($bind_value !== null) {
    $stmt_earnings->bind_param('is', $branch_id, $bind_value);
} else {
    $stmt_earnings->bind_param('i', $branch_id);
}
$stmt_earnings->execute();
$earnings_result = $stmt_earnings->get_result();
$earnings_row = $earnings_result->fetch_assoc();
$total_earnings = $earnings_row['total_earnings'] ?: 0;

$sql_total_quantity = "
    SELECT SUM(s.quantity) AS total_quantity_sold
    FROM sales s
    WHERE s.branchID = ? AND $date_condition
";
$stmt_quantity = $mysqli->prepare($sql_total_quantity);
if ($bind_value !== null) {
    $stmt_quantity->bind_param('is', $branch_id, $bind_value);
} else {
    $stmt_quantity->bind_param('i', $branch_id);
}
$stmt_quantity->execute();
$quantity_result = $stmt_quantity->get_result();
$quantity_row = $quantity_result->fetch_assoc();
$total_quantity_sold = $quantity_row['total_quantity_sold'] ?: 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Branch Dashboard</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <style>
        .filter-container {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-btn {
            margin-right: 10px;
        }
    </style>

</head>

<body id="page-top">

    <div id="wrapper">
        <?php include "backend/nav.php"; ?>

        <div class="container-fluid">

            <h1 class="h3 mb-2 text-gray-800"><?= $branch_name ?> Sales</h1>
            <p class="mb-4">Sales in <?= $branch_name ?> </p>

            <div class="filter-container">
                <form method="GET" action="" class="form-inline">
                    <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
                    
                    <div class="form-group mr-3">
                        <label class="mr-2">Filter by:</label>
                        <select name="filter" class="form-control" id="filterType">
                            <option value="all" <?= $filter_type == 'all' ? 'selected' : '' ?>>All Sales</option>
                            <option value="day" <?= $filter_type == 'day' ? 'selected' : '' ?>>Day</option>
                            <option value="month" <?= $filter_type == 'month' ? 'selected' : '' ?>>Month</option>
                            <option value="year" <?= $filter_type == 'year' ? 'selected' : '' ?>>Year</option>
                        </select>
                    </div>

                    <div class="form-group mr-3" id="dateInputGroup">
                        <input type="date" name="date" class="form-control" value="<?= $selected_date ?>" id="dateInput">
                    </div>

                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </form>
            </div>

            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="restock_inventory.php?id=<?= $_GET['id'] ?>&b_id=<?= $_GET['id'] ?>" class="card-link">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Inventory
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">95%</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Earnings (<?= $filter_label ?>)
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ₱<?= number_format($total_earnings, 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-peso-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Quantity Sold (<?= $filter_label ?>)
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($total_quantity_sold) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sales</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Receipt ID</th>
                                    <th>Products</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                    <th>Sales Date</th>
                                    <th>Customer Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $where_clause = "s.branchID = ?";
                                $params = [$branch_id];
                                $types = "i";

                                if ($filter_type != 'all' && isset($_GET['date'])) {
                                    $where_clause .= " AND $table_date_condition";
                                    $params[] = $table_bind_value;
                                    $types .= "s";
                                }

                                $sql = "
                                    SELECT 
                                        s.receiptID,
                                        GROUP_CONCAT(s.productName ORDER BY s.productName) AS products,
                                        SUM(s.quantity) AS total_quantity,
                                        SUM(s.quantity * s.price) AS total_price,
                                        MAX(s.sales_date) AS sales_date,
                                        MAX(s.customerName) AS customer_name
                                    FROM 
                                        sales s
                                    WHERE 
                                        $where_clause
                                    GROUP BY 
                                        s.receiptID
                                    ORDER BY 
                                        sales_date DESC
                                ";

                                $stmt = $mysqli->prepare($sql);
                                $stmt->bind_param($types, ...$params);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                        <td>{$row['receiptID']}</td>
                                        <td>{$row['products']}</td>
                                        <td>{$row['total_quantity']}</td>
                                        <td>" . '₱' . "{$row['total_price']}</td>
                                        <td>{$row['sales_date']}</td>
                                        <td>{$row['customer_name']}</td>
                                    </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No records found for this branch.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script src="js/demo/datatables-demo.js"></script>

    <script>
        document.getElementById('filterType').addEventListener('change', function() {
            const dateInputGroup = document.getElementById('dateInputGroup');
            if (this.value === 'all') {
                dateInputGroup.style.display = 'none';
            } else {
                dateInputGroup.style.display = 'block';
            }
        });

        window.addEventListener('DOMContentLoaded', function() {
            const filterType = document.getElementById('filterType').value;
            const dateInputGroup = document.getElementById('dateInputGroup');
            if (filterType === 'all') {
                dateInputGroup.style.display = 'none';
            }
        });
    </script>

</body>
<?php
$stmt->close();
$mysqli->close();
ob_end_flush();
?>

</html>