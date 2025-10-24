<?php
require_once 'db_connection.php';

if(!isset($_GET['id'])) {
    header('Location: salary_records.php');
    exit();
}

$payroll_id = $_GET['id'];

$payroll_query = "SELECT p.*, u.fname, u.lname, u.email, u.phone, d.name as department, u.address
                  FROM payroll p
                  LEFT JOIN users u ON p.employee_id = u.id
                  LEFT JOIN departments d ON u.department_id = d.id
                  WHERE p.id = ?";
$stmt = $mysqli->prepare($payroll_query);
$stmt->bind_param("i", $payroll_id);
$stmt->execute();
$result = $stmt->get_result();
$payroll = $result->fetch_assoc();

if(!$payroll) {
    header('Location: salary_records.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Payslip - <?php echo $payroll['fname'] . ' ' . $payroll['lname']; ?></title>
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
        .payslip-header {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
        }
        .payslip-body {
            background: white;
            padding: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e3e6f0;
        }
        .info-label {
            font-weight: 600;
            color: #5a5c69;
        }
        .info-value {
            color: #858796;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #4e73df;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4e73df;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        .amount-row.total {
            border-top: 2px solid #4e73df;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: 700;
            color: #1cc88a;
        }
        @media print {
            .sidebar, .topbar, .no-print {
                display: none !important;
            }
            #wrapper #content-wrapper {
                width: 100%;
            }
            .payslip-header {
                background: #1cc88a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
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
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Finance Admin</span>
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
                        <h1 class="h3 mb-0 text-gray-800">Payslip Details</h1>
                        <div>
                            <button class="btn btn-primary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Payslip
                            </button>
                            <a href="salary_records.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="card shadow mb-4">
                                <div class="payslip-header">
                                    <div class="text-center">
                                        <h2 class="mb-0">PAYSLIP</h2>
                                        <p class="mb-0">Pay Period: <?php echo date('M d, Y', strtotime($payroll['pay_period_start'])); ?> - <?php echo date('M d, Y', strtotime($payroll['pay_period_end'])); ?></p>
                                    </div>
                                </div>
                                <div class="payslip-body">
                                    <div class="section-title">Employee Information</div>
                                    <div class="info-row">
                                        <span class="info-label">Employee Name:</span>
                                        <span class="info-value"><?php echo $payroll['fname'] . ' ' . $payroll['lname']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Employee ID:</span>
                                        <span class="info-value"><?php echo $payroll['employee_id']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Department:</span>
                                        <span class="info-value"><?php echo $payroll['department']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Email:</span>
                                        <span class="info-value"><?php echo $payroll['email']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Payslip ID:</span>
                                        <span class="info-value">PAY-<?php echo $payroll['id']; ?></span>
                                    </div>

                                    <div class="section-title">Earnings</div>
                                    <div class="amount-row">
                                        <span class="info-label">Total Hours Worked:</span>
                                        <span class="info-value"><?php echo number_format($payroll['total_hours'], 2); ?> hrs</span>
                                    </div>
                                    <div class="amount-row">
                                        <span class="info-label">Gross Pay:</span>
                                        <span class="info-value">₱<?php echo number_format($payroll['gross_pay'], 2); ?></span>
                                    </div>

                                    <div class="section-title">Deductions</div>
                                    <div class="amount-row">
                                        <span class="info-label">Late Deductions:</span>
                                        <span class="info-value">₱<?php echo number_format($payroll['late_deductions'], 2); ?></span>
                                    </div>
                                    <div class="amount-row">
                                        <span class="info-label">Absence Deductions:</span>
                                        <span class="info-value">₱<?php echo number_format($payroll['absence_deductions'], 2); ?></span>
                                    </div>
                                    <div class="amount-row">
                                        <span class="info-label">Tax Deductions:</span>
                                        <span class="info-value">₱<?php echo number_format($payroll['tax_deductions'], 2); ?></span>
                                    </div>
                                    <div class="amount-row">
                                        <span class="info-label">Total Deductions:</span>
                                        <span class="info-value">₱<?php echo number_format($payroll['late_deductions'] + $payroll['absence_deductions'] + $payroll['tax_deductions'], 2); ?></span>
                                    </div>

                                    <div class="amount-row total">
                                        <span>Net Pay:</span>
                                        <span>₱<?php echo number_format($payroll['net_pay'], 2); ?></span>
                                    </div>

                                    <div class="section-title">Payment Information</div>
                                    <div class="info-row">
                                        <span class="info-label">Status:</span>
                                        <span>
                                            <?php if($payroll['status'] == 'paid'): ?>
                                                <span class="badge badge-success">Paid</span>
                                            <?php elseif($payroll['status'] == 'approved'): ?>
                                                <span class="badge badge-info">Approved</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Generated Date:</span>
                                        <span class="info-value"><?php echo date('M d, Y H:i', strtotime($payroll['created_at'])); ?></span>
                                    </div>

                                    <div class="mt-5 pt-4 text-center" style="border-top: 1px solid #e3e6f0;">
                                        <p class="text-muted mb-0">This is a computer-generated payslip. No signature required.</p>
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
</body>
</html>