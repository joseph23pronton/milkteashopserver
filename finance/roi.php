<?php
require_once 'db_connection.php';

$total_revenue_query = "SELECT SUM(totalPrice) as total FROM sales";
$total_revenue = $mysqli->query($total_revenue_query)->fetch_assoc()['total'] ?? 0;

$total_expenses_query = "SELECT SUM(amount) as total FROM expenses";
$total_expenses_result = $mysqli->query($total_expenses_query);
$total_expenses = $total_expenses_result ? $total_expenses_result->fetch_assoc()['total'] ?? 0 : 0;

$total_payroll_query = "SELECT SUM(net_pay) as total FROM payroll WHERE status = 'paid'";
$total_payroll = $mysqli->query($total_payroll_query)->fetch_assoc()['total'] ?? 0;

$ingredient_costs_query = "SELECT SUM(total_amount) as total FROM purchase_orders";
$ingredient_costs_result = $mysqli->query($ingredient_costs_query);
$ingredient_costs = $ingredient_costs_result ? $ingredient_costs_result->fetch_assoc()['total'] ?? 0 : 0;

$total_investment = $total_expenses + $total_payroll + $ingredient_costs;
$net_profit = $total_revenue - $total_investment;
$roi_percentage = $total_investment > 0 ? (($net_profit / $total_investment) * 100) : 0;

$monthly_roi_query = "SELECT 
    DATE_FORMAT(s.sales_date, '%Y-%m') as month,
    SUM(s.totalPrice) as revenue,
    COALESCE(SUM(e.amount), 0) as expenses,
    COALESCE(SUM(p.net_pay), 0) as payroll
FROM sales s
LEFT JOIN expenses e ON DATE_FORMAT(e.expense_date, '%Y-%m') = DATE_FORMAT(s.sales_date, '%Y-%m')
LEFT JOIN payroll p ON DATE_FORMAT(p.pay_period_start, '%Y-%m') = DATE_FORMAT(s.sales_date, '%Y-%m') AND p.status = 'paid'
GROUP BY month
ORDER BY month DESC
LIMIT 12";
$monthly_roi = $mysqli->query($monthly_roi_query);

$branch_performance_query = "SELECT 
    b.name as branch_name,
    SUM(s.totalPrice) as revenue,
    COUNT(s.id) as transactions
FROM sales s
LEFT JOIN branches b ON s.branchID = b.id
GROUP BY b.id";
$branch_performance = $mysqli->query($branch_performance_query);
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
                        <h1 class="h3 mb-0 text-gray-800">ROI Analysis</h1>
                    </div>

                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱<?php echo number_format($total_revenue, 2); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-peso-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Investment</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱<?php echo number_format($total_investment, 2); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-coins fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Net Profit</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱<?php echo number_format($net_profit, 2); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">ROI</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($roi_percentage, 2); ?>%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Monthly ROI Trend</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="roiChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Cost Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="costChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Branch Performance</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Branch</th>
                                                    <th>Revenue</th>
                                                    <th>Transactions</th>
                                                    <th>Avg Transaction</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($row = $branch_performance->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $row['branch_name']; ?></td>
                                                    <td>₱<?php echo number_format($row['revenue'], 2); ?></td>
                                                    <td><?php echo $row['transactions']; ?></td>
                                                    <td>₱<?php echo number_format($row['revenue'] / $row['transactions'], 2); ?></td>
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
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        var ctx1 = document.getElementById('roiChart').getContext('2d');
        var roiChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    $monthly_roi->data_seek(0);
                    while($row = $monthly_roi->fetch_assoc()): 
                        echo "'" . date('M Y', strtotime($row['month'] . '-01')) . "',";
                    endwhile;
                    ?>
                ],
                datasets: [{
                    label: 'Revenue',
                    data: [
                        <?php 
                        $monthly_roi->data_seek(0);
                        while($row = $monthly_roi->fetch_assoc()): 
                            echo $row['revenue'] . ",";
                        endwhile;
                        ?>
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }, {
                    label: 'Expenses',
                    data: [
                        <?php 
                        $monthly_roi->data_seek(0);
                        while($row = $monthly_roi->fetch_assoc()): 
                            echo ($row['expenses'] + $row['payroll']) . ",";
                        endwhile;
                        ?>
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                }]
            }
        });

        var ctx2 = document.getElementById('costChart').getContext('2d');
        var costChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Operating Expenses', 'Payroll', 'Raw Materials'],
                datasets: [{
                    data: [
                        <?php echo $total_expenses; ?>,
                        <?php echo $total_payroll; ?>,
                        <?php echo $ingredient_costs; ?>
                    ],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ]
                }]
            }
        });
    </script>
</body>
</html>