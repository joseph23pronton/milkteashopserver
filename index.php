<?php
$screen = 'dashboard';
ob_start();

$mysqli = include('database.php');
$sql = "SELECT 
            MONTH(sales_date) AS month, 
            YEAR(sales_date) AS year, 
            SUM(quantity * price) AS total_earnings,
            SUM(quantity * initial_price) AS total_revenue
        FROM sales
        GROUP BY YEAR(sales_date), MONTH(sales_date)
        ORDER BY YEAR(sales_date), MONTH(sales_date)"; // Orders by year and month
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$months = [];
$earnings = [];
$revenues = []; // Add revenue data

while ($row = $result->fetch_assoc()) {
    $month_name = date("F", mktime(0, 0, 0, $row['month'], 10)); // Get the month name from the number
    $months[] = $month_name . ' ' . $row['year'];  // Store month and year
    $earnings[] = $row['total_earnings']; // Store the total earnings
    $revenues[] = $row['total_revenue']; // Store the total revenue
}

// Corrected SQL query
$sql_earnings = "
    SELECT SUM(s.quantity * s.price) AS total_earnings,
    SUM(s.quantity * initial_price) AS total_revenue
    FROM sales s
    WHERE MONTH(s.sales_date) = MONTH(CURRENT_DATE) 
    AND YEAR(s.sales_date) = YEAR(CURRENT_DATE)
";

$stmt_earnings = $mysqli->prepare($sql_earnings);
if ($stmt_earnings) {
    $stmt_earnings->execute();
    $earnings_result = $stmt_earnings->get_result();
    $earnings_row = $earnings_result->fetch_assoc();
    $total_earnings = $earnings_row['total_earnings'] ?: 0; // Default to 0 if no earnings found
    $total_revenue = $earnings_row['total_revenue'] ?: 0;
    $total_revenue = $total_earnings - $total_revenue;
    $stmt_earnings->close();
} else {
    $total_earnings = 0; // Default to 0 if statement preparation fails
    error_log("Failed to prepare SQL statement: " . $mysqli->error);
}

$stocks_sql = "SELECT COUNT(*) AS stock_count from restockOrder WHERE is_accepted = 0 AND is_confirmed = 0";
$stmt_stocks = $mysqli->prepare($stocks_sql);
if ($stmt_stocks) {
    $stmt_stocks->execute();
    $stock_result = $stmt_stocks->get_result();
    $stock_row = $stock_result->fetch_assoc();
    $totalStock = $stock_row['stock_count'] ?: 0;
    $stmt_stocks->close();
}

// Close the database connection
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Dashboard - Milktea Shop</title>

    <!-- Custom fonts for this template-->
    <script src="https://kit.fontawesome.com/ed626e6e0f.js" crossorigin="anonymous"></script>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>



<body id="page-top">
    <style>
        .toastCloseButton{
            padding: 5;
        }
    </style>
       <!-- Toast Notification Container -->
       <div class="toast-container position-fixed top-0 end-0 p-3 mt-5" style="left: 80%;z-index: 1050;">
    <div id="stockToast" class="toast text-bg-warning" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="toast-header">
            <strong class="me-auto">Low Stock Alert </strong>
            <button type="button" class="btn-close toastCloseButton"  data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include "backend/nav.php";
        ob_end_flush(); ?>
 
        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard (Overall Of All Branches)</h1>

            </div>
            <?php if (isset($_GET['welcome'])): ?>
                <div class="alert alert-success mt-3">Password Updated Successfully! Welcome Aboard</div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Revenue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ₱<?= number_format($total_revenue, 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-money-bill-trend-up fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Sales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        P<?= number_format($total_earnings, 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Stocks (Requested by the Encoder)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">⚠️<?= $totalStock ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-cart-flatbed fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <!-- Area Chart -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <!-- Card Header - Dropdown -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Earnings Overview</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>

                            </div>
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="myBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                // Fetch restock orders with branch name
                $query = "SELECT r.id, r.name, r.ingredientsName, r.restock_amount, r.requested_by, r.is_accepted, 
                 b.name as branchName 
          FROM restockOrder AS r
          LEFT JOIN branches AS b ON r.branchID = b.id WHERE is_confirmed = 0";

                $result = $mysqli->query($query);
                ?>

                <div class="col-xl-4 col-lg-3">
                    <div class="card shadow mb-4">
                        <!-- Card Header -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Restock Orders</h6>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="restockTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Quantity</th>
                                            <th>Requested By</th>
                                            <th>Branch Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['ingredientsName']) ?></td>
                                                    <td><?= (int) $row['restock_amount'] ?></td>
                                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                                    <td><?= htmlspecialchars($row['branchName']) ?></td>
                                                    <td>
                                                        <?php if ($row['is_accepted'] == 0): ?>
                                                            <!-- Accept Button -->
                                                            <form method="POST" action="backend/accept_restock.php">
                                                                <input type="hidden" name="restock_id" value="<?= $row['id'] ?>">
                                                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <!-- Disabled Button -->
                                                            <button class="btn btn-secondary btn-sm" disabled>Waiting to Confirm</button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No restock orders found.</td>
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

    </div>
    <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

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
                    <a class="btn btn-primary" href="backend/signout.php">Logout</a>
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
    <script src="vendor/chart.js/Chart.min.js"></script>


<!-- jQuery (required for AJAX) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle (for Toasts) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Pass PHP data to JavaScript
var months = <?php echo json_encode($months); ?>;
var earnings = <?php echo json_encode($earnings); ?>;
var revenues = <?php echo json_encode($revenues); ?>;

// Log data to console for debugging
console.log("Months:", months);
console.log("Earnings:", earnings);
console.log("Revenues:", revenues);

// Initialize the bar chart
var ctx = document.getElementById("myBarChart").getContext("2d");
var myBarChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: months, // X-axis labels (Months and Years)
    datasets: [
      {
        label: "Earnings",
        data: earnings,
        backgroundColor: "rgba(78, 115, 223, 0.8)", // Blue color
        borderColor: "rgba(78, 115, 223, 1)", // Blue border
        borderWidth: 1
      },
      {
        label: "Revenue",
        data: revenues,
        backgroundColor: "rgba(28, 200, 138, 0.8)", // Green color
        borderColor: "rgba(28, 200, 138, 1)", // Green border
        borderWidth: 1
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: {
        stacked: false
      },
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 1000 // Adjust step size for readability
        }
      }
    },
    plugins: {
      tooltip: {
        mode: "index", // Show data for all bars when hovered
        intersect: false
      }
    }
  }
});
</script>
<script>
    
    $(document).ready(function() {
        // Check stock status
        $.ajax({
            url: 'backend/checkStock.php', // Path to your checkStock.php
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'low_stock') {
                    // Format the message with all low stock items
                    let message = response.notifications.map(item => 
                        `⚠️ ${item.productName} (Branch: ${item.branchName}) - ${item.stockQuantity} left`
                    ).join("<br>");
                    
                    // Update toast body and show the toast
                    $('#toastMessage').html(message);
                    const stockToast = new bootstrap.Toast(document.getElementById('stockToast'));
                    stockToast.show();
                }
            },
            error: function(err) {
                console.error("Error fetching low stock data:", err);
            }
        });
    });
</script>
</body>

</html>