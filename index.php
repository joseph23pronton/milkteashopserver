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
        ORDER BY YEAR(sales_date), MONTH(sales_date)";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$months = [];
$earnings = [];
$revenues = [];

while ($row = $result->fetch_assoc()) {
    $month_name = date("F", mktime(0, 0, 0, $row['month'], 10));
    $months[] = $month_name . ' ' . $row['year'];
    $earnings[] = $row['total_earnings'];
    $revenues[] = $row['total_revenue'];
}

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
    $total_earnings = $earnings_row['total_earnings'] ?: 0;
    $total_revenue = $earnings_row['total_revenue']?: 0;
    $total_revenue = $total_earnings - $total_revenue;
    $stmt_earnings->close();
} else {
    $total_earnings = 0;
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

$approved_stocks_sql = "SELECT COUNT(*) AS approved_count from restockOrder WHERE is_accepted = 1 AND is_confirmed = 1";
$stmt_approved = $mysqli->prepare($approved_stocks_sql);
if ($stmt_approved) {
    $stmt_approved->execute();
    $approved_result = $stmt_approved->get_result();
    $approved_row = $approved_result->fetch_assoc();
    $approvedStock = $approved_row['approved_count'] ?: 0;
    $stmt_approved->close();
}

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

    <script src="https://kit.fontawesome.com/ed626e6e0f.js" crossorigin="anonymous"></script>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>



<body id="page-top">
    <style>
        .toastCloseButton{
            padding: 5;
        }
    </style>
       <div class="toast-container position-fixed top-0 end-0 p-3 mt-5" style="left: 80%;z-index: 1050;">
    <div id="stockToast" class="toast text-bg-warning" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="toast-header">
            <strong class="me-auto">Low Stock Alert </strong>
            <button type="button" class="btn-close toastCloseButton"  data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

    <div id="wrapper">

        <?php include "backend/nav.php";
        ob_end_flush(); ?>
 
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard (Overall Of All Branches)</h1>

            </div>
            <?php if (isset($_GET['welcome'])): ?>
                <div class="alert alert-success mt-3">Password Updated Successfully! Welcome Aboard</div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
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

                <div class="col-xl-3 col-md-6 mb-4">
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
                                    <i class="fas fa-peso-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Stocks</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">⚠️<?= $totalStock ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-cart-flatbed fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Approved Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">✓<?= $approvedStock ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Earnings Overview</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="myBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $query = "SELECT r.id, r.name, r.ingredientsName, r.restock_amount, r.requested_by, r.is_accepted, r.is_confirmed,
                 b.name as branchName 
          FROM restockOrder AS r
          LEFT JOIN branches AS b ON r.branchID = b.id WHERE is_confirmed = 0";

                $result = $mysqli->query($query);
                ?>

                <div class="col-xl-4 col-lg-3">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Restock Orders</h6>
                        </div>

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
                                                            <form method="POST" action="backend/approve_order.php">
                                                                <input type="hidden" name="restock_id" value="<?= $row['id'] ?>">
                                                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                                            </form>
                                                        <?php elseif ($row['is_accepted'] == 1 && $row['is_confirmed'] == 0): ?>
                                                            <button class="btn btn-info btn-sm" disabled>Waiting Finance Approval</button>
                                                        <?php else: ?>
                                                            <button class="btn btn-primary btn-sm" disabled>Approved</button>
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
                    <a class="btn btn-primary" href="backend/signout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

    <script src="vendor/chart.js/Chart.min.js"></script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var months = <?php echo json_encode($months); ?>;
var earnings = <?php echo json_encode($earnings); ?>;
var revenues = <?php echo json_encode($revenues); ?>;

console.log("Months:", months);
console.log("Earnings:", earnings);
console.log("Revenues:", revenues);

var ctx = document.getElementById("myBarChart").getContext("2d");
var myBarChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: months,
    datasets: [
      {
        label: "Earnings",
        data: earnings,
        backgroundColor: "rgba(78, 115, 223, 0.8)",
        borderColor: "rgba(78, 115, 223, 1)",
        borderWidth: 1
      },
      {
        label: "Revenue",
        data: revenues,
        backgroundColor: "rgba(28, 200, 138, 0.8)",
        borderColor: "rgba(28, 200, 138, 1)",
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
          stepSize: 1000
        }
      }
    },
    plugins: {
      tooltip: {
        mode: "index",
        intersect: false
      }
    }
  }
});
</script>
<script>
    
    $(document).ready(function() {
        $.ajax({
            url: 'backend/checkStock.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'low_stock') {
                    let message = response.notifications.map(item => 
                        `⚠️ ${item.productName} (Branch: ${item.branchName}) - ${item.stockQuantity} left`
                    ).join("<br>");
                    
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