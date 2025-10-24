<?php
$screen = 'branch';
ob_start();
date_default_timezone_set('Asia/Manila');
// Check if branch ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Branch ID is required.");
}
$mysqli = include('database.php');
$branch_id = $_GET['id'];

// Step 1: Get branch_name and branch_city from branches table
$sql = "SELECT name, city FROM branches WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $branch_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if branch exists
if ($result->num_rows === 0) {
    die("No branch found with the provided ID.");
}

$branch = $result->fetch_assoc();
$branch_name = $branch['name'];
$branch_city = $branch['city'];

// Step 2: Construct the inventory table name
$table_name = strtolower('name'); // Use lowercase for table names

// Step 3: Calculate total earnings for the current month
$sql_earnings = "
    SELECT SUM(s.quantity * s.price) AS total_earnings
    FROM sales s
    WHERE s.branchID = ? 
    AND MONTH(s.sales_date) = MONTH(CURRENT_DATE) 
    AND YEAR(s.sales_date) = YEAR(CURRENT_DATE) 
";
$stmt_earnings = $mysqli->prepare($sql_earnings);
$stmt_earnings->bind_param('i', $branch_id);
$stmt_earnings->execute();
$earnings_result = $stmt_earnings->get_result();
$earnings_row = $earnings_result->fetch_assoc();
$total_earnings = $earnings_row['total_earnings'] ?: 0; // Default to 0 if no earnings found

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

    <!-- Custom fonts for this template -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include "backend/nav.php"; ?>

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <h1 class="h3 mb-2 text-gray-800"><?= $branch_name ?> Sales</h1>
            <p class="mb-4">Sales in <?= $branch_name ?> </p>
            <div class="row">
                <!-- Inventory Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="view_branch.php?id=<?= $_GET['id'] ?>&b_id=<?= $_GET['id'] ?>" class="card-link">
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

                <!-- Earnings Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Earnings (Monthly)
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ₱<?= number_format($total_earnings, 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- DataTales Example -->
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
                                $sql = "
                            SELECT 
                                s.receiptID,
                                GROUP_CONCAT(s.productName ORDER BY s.productName) AS products,
                                SUM(s.quantity) AS total_quantity,
                                SUM(s.quantity * s.price) AS total_price,
                                MAX(s.sales_date) AS sales_date,  -- Use MAX or MIN to avoid errors
                                MAX(s.customerName) AS customer_name  -- Same here, choose an aggregate function
                            FROM 
                                sales s
                            WHERE 
                                s.branchID = ? 
                            GROUP BY 
                                s.receiptID
                            ORDER BY 
                                sales_date DESC
                        ";

                                $stmt = $mysqli->prepare($sql);
                                $stmt->bind_param('i', $_GET['id']);
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
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
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

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>

</body>
<?php
// Closing the connection after all operations are done
$stmt->close();
$mysqli->close(); // Close the MySQLi connection here
ob_end_flush();
?>

</html>