<?php

$screen = 'dashboard';
ob_start();
$mysqli = include('database.php');

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
    <link href="/css/login.css" rel="stylesheet">

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
        ob_end_flush(); 
        $branch_id = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['branch_id']) ? $_SESSION['branch_id'] : null);
$sql = "SELECT name, city FROM branches WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $branch_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No branch found with the provided ID.");

}

        $sql_earnings = "
    SELECT 
        SUM(s.quantity * s.price) AS total_earnings,
        SUM(s.quantity * s.initial_price) AS total_revenue
    FROM sales s
    WHERE MONTH(s.sales_date) = MONTH(CURRENT_DATE) 
    AND YEAR(s.sales_date) = YEAR(CURRENT_DATE)
    AND s.branchID = ?
";

$stmt_earnings = $mysqli->prepare($sql_earnings);
$branch = $result->fetch_assoc();

if ($stmt_earnings) {
    $stmt_earnings->bind_param("i", $branch_id);
    $stmt_earnings->execute();
    $earnings_result = $stmt_earnings->get_result();
    $earnings_row = $earnings_result->fetch_assoc();

    $total_earnings = $earnings_row['total_earnings'] ?: 0; 
    $total_revenue = $earnings_row['total_revenue'] ?: 0;

    $profit = $total_earnings - $total_revenue;

    $stmt_earnings->close();
} else {
    // If statement preparation fails
    $total_earnings = 0;
    $total_revenue = 0;
    $profit = 0;
    error_log("Failed to prepare SQL statement: " . $mysqli->error);
}


$stocks_sql = "SELECT COUNT(*) AS stock_count from restockOrder WHERE branchID = ? AND is_confirmed = 0";
$stmt_stocks = $mysqli->prepare($stocks_sql);
if ($stmt_stocks) {
    $stmt_stocks->bind_param("i", $branch_id);
    $stmt_stocks->execute();
    $stock_result = $stmt_stocks->get_result();
    $stock_row = $stock_result->fetch_assoc();
    $totalStock = $stock_row['stock_count'] ?: 0;
    $stmt_stocks->close();
}

$sql = "SELECT 
            MONTH(sales_date) AS month, 
            YEAR(sales_date) AS year, 
            SUM(quantity * price) AS total_earnings,
            SUM(quantity * initial_price) AS total_revenue
        FROM sales WHERE branchID = ?
        GROUP BY YEAR(sales_date), MONTH(sales_date)
        ORDER BY YEAR(sales_date), MONTH(sales_date) "; // Orders by year and month
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $branch_id);
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



?>
 
        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?= $branch['name'] ?>  - <?= $branch['city'] ?> Dashboard</h1>

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

                <div class="col-xl-3 col-md-6 mb-4">
                <a href="sales.php?id=<?= $branch_id; ?>" class="card-link">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Sales
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">₱<?= number_format($total_earnings, 2) ?></div>
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
                    <a href="view_branch.php?id=<?= $branch_id; ?>" class="card-link">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Stocks (Low Stock Notifications)
                                        </div>
                                        <!-- Total low stocks displayed -->
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="lowStockCount">Loading...</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-cart-flatbed fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>                                                  
                        </div>
                    </a>
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
                   
                    if (isset($branch_id)) {
                        // Prepare the SQL query to fetch restock orders for the specific branch with price information and invoice numbers
                        $query = "SELECT r.id, r.ingredientsName, r.restock_amount, r.requested_by, r.is_accepted, r.branchID, r.ingredientsID, r.created_at,
                                        b.name as branchName,
                                        ih.price_per_unit,
                                        ih.unit as ingredient_unit,
                                        (r.restock_amount * COALESCE(ih.price_per_unit, 0)) AS total_cost,
                                        COALESCE(r.invoice_number, CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(r.id, 4, '0'))) AS invoice_number
                                FROM restockOrder AS r
                                LEFT JOIN branches AS b ON r.branchID = b.id 
                                LEFT JOIN ingredientsHeader ih ON r.ingredientsID = ih.id
                                WHERE r.branchID = ? AND r.is_confirmed = 0";
                        
                        // Prepare the statement
                        $stmt = $mysqli->prepare($query);
                        
                        if ($stmt) {
                            // Bind the branch_id parameter to the query
                            $stmt->bind_param("i", $branch_id);
                            
                            // Execute the query
                            $stmt->execute();
                            
                            // Get the result
                            $result = $stmt->get_result();
                        } else {
                            echo "Failed to prepare the query: " . $mysqli->error;
                            exit();
                        }
                    } else {
                        echo "Branch ID is not set.";
                        exit();
                    }
                    ?>
<?php if ($_SESSION['role'] != 'cashier'): ?>
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
                                                    <th>Invoice No.</th>
                                                    <th>Quantity</th>
                                                    <th>Price/Unit</th>
                                                    <th>Total Cost</th>
                                                    <th>Requested By</th>
                                                    <th>Branch Name</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($result->num_rows > 0): ?>
                                                    <?php while ($row = $result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($row['ingredientsName']) ?></td>
                                                            <td>
                                                                <code class="text-primary"><?= htmlspecialchars($row['invoice_number']) ?></code>
                                                            </td>
                                                            <td><?= (int) $row['restock_amount'] ?> <?= htmlspecialchars($row['ingredient_unit'] ?? '') ?></td>
                                                            <td>
                                                                <?php if ($row['price_per_unit'] > 0): ?>
                                                                    ₱<?= number_format($row['price_per_unit'], 2) ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Not set</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <strong>
                                                                    <?php if ($row['total_cost'] > 0): ?>
                                                                        ₱<?= number_format($row['total_cost'], 2) ?>
                                                                    <?php else: ?>
                                                                        ₱0.00
                                                                    <?php endif; ?>
                                                                </strong>
                                                            </td>
                                                            <td><?= htmlspecialchars($row['requested_by']) ?></td>
                                                            <td><?= htmlspecialchars($row['branchName']) ?></td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <!-- Receipt Button -->
                                                                    <button class="btn btn-info btn-sm" 
                                                                            onclick="viewReceipt(<?= $row['id'] ?>, '<?= htmlspecialchars($row['invoice_number']) ?>')"
                                                                            title="View Receipt">
                                                                        <i class="fas fa-receipt"></i>
                                                                    </button>
                                                                    
                                                                    <?php if ($_SESSION['role'] == 'admin' || $row['is_accepted'] == 1): ?>
                                                                        <!-- Confirm Button -->
                                                                        <form method="POST" action="<?php echo ($_SESSION['role'] == 'admin') ? 'backend/accept_restock.php' : 'backend/confirm_restock.php'; ?>" style="display: inline;">
                                                                            <input type="hidden" name="restockAmount" value="<?= $row['restock_amount'] ?>">
                                                                            <input type="hidden" name="branchID" value="<?= $row['branchID'] ?>">
                                                                            <input type="hidden" name="ingredientsID" value="<?= $row['ingredientsID'] ?>">
                                                                            <input type="hidden" name="restock_id" value="<?= $row['id'] ?>">
                                                                            <button type="submit" class="btn btn-success btn-sm" title="Confirm Order">
                                                                                <i class="fas fa-check"></i>
                                                                            </button>
                                                                        </form>
                                                                    <?php else: ?>
                                                                        <!-- Disabled Button -->
                                                                        <button class="btn btn-secondary btn-sm" disabled title="Waiting For Manager">
                                                                            <i class="fas fa-clock"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center">No restock orders found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                        
                                        <?php
                                        // Calculate total cost for all pending restock orders
                                        if (isset($branch_id)) {
                                            $total_query = "SELECT SUM(r.restock_amount * COALESCE(ih.price_per_unit, 0)) AS total_restock_cost
                                                            FROM restockOrder r
                                                            LEFT JOIN ingredientsHeader ih ON r.ingredientsID = ih.id
                                                            WHERE r.branchID = ? AND r.is_confirmed = 0";
                                            $total_stmt = $mysqli->prepare($total_query);
                                            if ($total_stmt) {
                                                $total_stmt->bind_param("i", $branch_id);
                                                $total_stmt->execute();
                                                $total_result = $total_stmt->get_result();
                                                $total_row = $total_result->fetch_assoc();
                                                $total_restock_cost = $total_row['total_restock_cost'] ?: 0;
                                                
                                                if ($total_restock_cost > 0) {
                                                    echo '<div class="mt-3 text-right">';
                                                    echo '<strong>Total Estimated Cost: ₱' . number_format($total_restock_cost, 2) . '</strong>';
                                                    echo '</div>';
                                                }
                                                $total_stmt->close();
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
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
                    <a class="btn btn-primary" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">
                        <i class="fas fa-receipt"></i> Restock Receipt
                    </h5>
                    <button type="button" class="close" onclick="$('#receiptModal').modal('hide');" aria-label="Close">
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
                    <button type="button" class="btn btn-secondary" onclick="$('#receiptModal').modal('hide');">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printReceipt()">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
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

// For Low Stock Alert
document.addEventListener("DOMContentLoaded", function () {
    fetch('backend/checkStock.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'low_stock') {
                // Update the card with the total number of low-stock items
                const lowStockCount = data.notifications.length;
                const lowStockDiv = document.getElementById("lowStockCount");
                lowStockDiv.textContent = `⚠️ ${lowStockCount}`;
                
                // Add a tooltip to display low-stock items
                const tooltipContent = data.notifications
                    .map(item => `${item.productName} (${item.stockQuantity}) - ${item.branchName}`)
                    .join('\n');
                lowStockDiv.setAttribute("title", tooltipContent);
            } else {
                // No low-stock items
                document.getElementById("lowStockCount").textContent = "✅ All stocks are sufficient";
            }
        })
        .catch(error => console.error('Error fetching low stock data:', error));
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

<script>
// Receipt viewing functionality
function viewReceipt(restockId, invoiceNumber) {
    // Show the modal
    $('#receiptModal').modal('show');
    
    // Load receipt content via AJAX
    $.ajax({
        url: 'backend/generate_receipt.php',
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

// Print receipt function
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
    
    // Optional: Close modal after printing (ask user first)
    // Uncomment this if you want to auto-close modal after printing:
    // setTimeout(function() {
    //     $('#receiptModal').modal('hide');
    // }, 2000);
}
</script>

<script>
// Additional modal controls for receipt modal
$(document).ready(function() {
    // Close receipt modal when clicking outside
    $('#receiptModal').on('click', function(e) {
        if (e.target === this) {
            $(this).modal('hide');
        }
    });
    
    // Close modal with Escape key
    $(document).on('keyup', function(e) {
        if (e.keyCode === 27) { // Escape key
            $('#receiptModal').modal('hide');
        }
    });
    
    // Ensure modal closes properly when hidden
    $('#receiptModal').on('hidden.bs.modal', function() {
        // Clear content when modal is closed
        $('#receiptContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
    });
});
</script>

</body>

</html>