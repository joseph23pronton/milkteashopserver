<?php
require_once 'auth_check.php';
$mysqli = require __DIR__ . "/../database.php";

date_default_timezone_set('Asia/Manila');

$success_msg = '';
$error_msg = '';

function calculateMonthlySSS($monthlySalary) {
    $sssTable = [
        [4250, 180], [4750, 202.50], [5250, 225], [5750, 247.50], [6250, 270],
        [6750, 292.50], [7250, 315], [7750, 337.50], [8250, 360], [8750, 382.50],
        [9250, 405], [9750, 427.50], [10250, 450], [10750, 472.50], [11250, 495],
        [11750, 517.50], [12250, 540], [12750, 562.50], [13250, 585], [13750, 607.50],
        [14250, 630], [14750, 652.50], [15250, 675], [15750, 697.50], [16250, 720],
        [16750, 742.50], [17250, 765], [17750, 787.50], [18250, 810], [18750, 832.50],
        [19250, 855], [19750, 877.50], [20250, 900], [20750, 922.50], [21250, 945],
        [21750, 967.50], [22250, 990], [22750, 1012.50], [23250, 1035], [23750, 1057.50],
        [24250, 1080], [24750, 1102.50], [25250, 1125], [25750, 1147.50], [26250, 1170],
        [26750, 1192.50], [27250, 1215], [27750, 1237.50], [28250, 1260], [28750, 1282.50],
        [29250, 1305], [29750, 1327.50], [30250, 1350], [30750, 1372.50], [31250, 1395],
        [31750, 1417.50], [32250, 1440], [32750, 1462.50], [33250, 1485], [33750, 1507.50],
        [34250, 1530], [34750, 1552.50], [35250, 1575], [35750, 1597.50]
    ];
    
    foreach ($sssTable as $bracket) {
        if ($monthlySalary <= $bracket[0]) {
            return $bracket[1];
        }
    }
    return 1597.50;
}

function calculateMonthlyPagIBIG($monthlySalary) {
    if ($monthlySalary <= 1500) {
        return $monthlySalary * 0.01;
    }
    return min($monthlySalary * 0.02, 200);
}

function calculateMonthlyPhilHealth($monthlySalary) {
    $salary = max(10000, min($monthlySalary, 100000));
    return ($salary * 0.05) / 2;
}

function calculatePayrollDeductions($totalHours, $hourlyRate, $lateMinutes) {
    $hoursPerMonth = 160;
    $monthlySalary = $hourlyRate * $hoursPerMonth;
    
    $lateDeductions = ($lateMinutes / 60) * $hourlyRate;
    $grossPay = $totalHours * $hourlyRate;
    
    $monthlySSSContribution = calculateMonthlySSS($monthlySalary);
    $monthlyPagibigContribution = calculateMonthlyPagIBIG($monthlySalary);
    $monthlyPhilhealthContribution = calculateMonthlyPhilHealth($monthlySalary);
    
    $prorateRatio = $totalHours / $hoursPerMonth;
    
    $sssContribution = $monthlySSSContribution * $prorateRatio;
    $pagibigContribution = $monthlyPagibigContribution * $prorateRatio;
    $philhealthContribution = $monthlyPhilhealthContribution * $prorateRatio;
    
    $totalDeductions = $lateDeductions + $sssContribution + $pagibigContribution + $philhealthContribution;
    $netPay = $grossPay - $totalDeductions;
    
    return [
        'gross_pay' => $grossPay,
        'late_deductions' => $lateDeductions,
        'sss_contribution' => $sssContribution,
        'pagibig_contribution' => $pagibigContribution,
        'philhealth_contribution' => $philhealthContribution,
        'total_deductions' => $totalDeductions,
        'net_pay' => $netPay
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'generate_individual_payroll') {
            $employee_id = intval($_POST['employee_id']);
            $period_start = $_POST['period_start'];
            $period_end = $_POST['period_end'];
            
            $emp_query = $mysqli->query("SELECT u.id, u.fname, u.lname, u.role, u.hourly_rate FROM users u WHERE u.id = $employee_id AND u.role != 'admin' AND u.is_archived = 0 AND u.employee_status = 'active'");
            
            if ($emp = $emp_query->fetch_assoc()) {
                $hourly_rate = $emp['hourly_rate'];
                
                $attendance_query = "SELECT * FROM attendance WHERE employee_id = $employee_id AND attendance_date BETWEEN '$period_start' AND '$period_end' AND time_in IS NOT NULL";
                $attendance_records = $mysqli->query($attendance_query);
                
                $total_hours = 0;
                $total_late_minutes = 0;
                
                while ($att = $attendance_records->fetch_assoc()) {
                    if ($att['time_in']) {
                        $time_in = strtotime($att['time_in']);
                        $time_out = $att['time_out'] ? strtotime($att['time_out']) : strtotime($att['time_in']) + (8 * 3600);
                        
                        $hours_worked = ($time_out - $time_in) / 3600;
                        $hours_worked = max(0, min($hours_worked, 24));
                        
                        $total_hours += $hours_worked;
                        $total_late_minutes += ($att['late_minutes'] ?? 0);
                    }
                }
                
                if ($total_hours > 0) {
                    $payroll_data = calculatePayrollDeductions($total_hours, $hourly_rate, $total_late_minutes);
                    
                    $check_existing = $mysqli->query("SELECT id FROM payroll WHERE employee_id = $employee_id AND pay_period_start = '$period_start' AND pay_period_end = '$period_end'");
                    
                    if ($check_existing->num_rows > 0) {
                        $stmt = $mysqli->prepare("UPDATE payroll SET total_hours = ?, gross_pay = ?, late_deductions = ?, sss_contribution = ?, pagibig_contribution = ?, philhealth_contribution = ?, net_pay = ?, status = 'pending' WHERE employee_id = ? AND pay_period_start = ? AND pay_period_end = ?");
                        $stmt->bind_param("dddddddiss", 
                            $total_hours, 
                            $payroll_data['gross_pay'], 
                            $payroll_data['late_deductions'], 
                            $payroll_data['sss_contribution'],
                            $payroll_data['pagibig_contribution'],
                            $payroll_data['philhealth_contribution'],
                            $payroll_data['net_pay'], 
                            $employee_id, 
                            $period_start, 
                            $period_end
                        );
                    } else {
                        $stmt = $mysqli->prepare("INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, total_hours, gross_pay, late_deductions, sss_contribution, pagibig_contribution, philhealth_contribution, net_pay, status, is_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0)");
                        $stmt->bind_param("issdddddddd", 
                            $employee_id, 
                            $period_start, 
                            $period_end, 
                            $total_hours, 
                            $payroll_data['gross_pay'], 
                            $payroll_data['late_deductions'],
                            $payroll_data['sss_contribution'],
                            $payroll_data['pagibig_contribution'],
                            $payroll_data['philhealth_contribution'],
                            $payroll_data['net_pay']
                        );
                    }
                    $stmt->execute();
                    $success_msg = "Payroll generated successfully for " . htmlspecialchars($emp['fname'] . ' ' . $emp['lname']) . "!";
                } else {
                    $error_msg = "No attendance records found for this employee in the selected period.";
                }
            } else {
                $error_msg = "Employee not found or not active.";
            }
        } elseif ($_POST['action'] === 'generate_payroll') {
            $period_start = $_POST['period_start'];
            $period_end = $_POST['period_end'];
            
            $employees = $mysqli->query("SELECT u.id, u.fname, u.lname, u.role, u.hourly_rate FROM users u WHERE u.role != 'admin' AND u.is_archived = 0 AND u.employee_status = 'active'");
            
            $generated_count = 0;
            while ($emp = $employees->fetch_assoc()) {
                $employee_id = intval($emp['id']);
                $hourly_rate = floatval($emp['hourly_rate']);
                
                $attendance_query = "SELECT * FROM attendance WHERE employee_id = $employee_id AND attendance_date BETWEEN '$period_start' AND '$period_end' AND time_in IS NOT NULL";
                $attendance_records = $mysqli->query($attendance_query);
                
                $total_hours = 0.0;
                $total_late_minutes = 0;
                
                while ($att = $attendance_records->fetch_assoc()) {
                    if ($att['time_in']) {
                        $time_in = strtotime($att['time_in']);
                        $time_out = $att['time_out'] ? strtotime($att['time_out']) : strtotime($att['time_in']) + (8 * 3600);
                        
                        $hours_worked = ($time_out - $time_in) / 3600;
                        $hours_worked = max(0, min($hours_worked, 24));
                        
                        $total_hours += $hours_worked;
                        $total_late_minutes += ($att['late_minutes'] ?? 0);
                    }
                }
                
                if ($total_hours > 0) {
                    $payroll_data = calculatePayrollDeductions($total_hours, $hourly_rate, $total_late_minutes);
                    
                    $check_existing = $mysqli->query("SELECT id FROM payroll WHERE employee_id = $employee_id AND pay_period_start = '$period_start' AND pay_period_end = '$period_end'");
                    
                    if ($check_existing->num_rows > 0) {
                        $stmt = $mysqli->prepare("UPDATE payroll SET total_hours = ?, gross_pay = ?, late_deductions = ?, sss_contribution = ?, pagibig_contribution = ?, philhealth_contribution = ?, net_pay = ?, status = 'pending' WHERE employee_id = ? AND pay_period_start = ? AND pay_period_end = ?");
                        $stmt->bind_param("dddddddiss", 
                            $total_hours, 
                            $payroll_data['gross_pay'], 
                            $payroll_data['late_deductions'], 
                            $payroll_data['sss_contribution'],
                            $payroll_data['pagibig_contribution'],
                            $payroll_data['philhealth_contribution'],
                            $payroll_data['net_pay'], 
                            $employee_id, 
                            $period_start, 
                            $period_end
                        );
                    } else {
                        $stmt = $mysqli->prepare("INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, total_hours, gross_pay, late_deductions, sss_contribution, pagibig_contribution, philhealth_contribution, net_pay, status, is_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0)");
                        $stmt->bind_param("issdddddddd", 
                            $employee_id, 
                            $period_start, 
                            $period_end, 
                            $total_hours, 
                            $payroll_data['gross_pay'], 
                            $payroll_data['late_deductions'],
                            $payroll_data['sss_contribution'],
                            $payroll_data['pagibig_contribution'],
                            $payroll_data['philhealth_contribution'],
                            $payroll_data['net_pay']
                        );
                    }
                    $stmt->execute();
                    $generated_count++;
                }
            }
            
            $success_msg = "Payroll generated successfully for $generated_count employees!";
        } elseif ($_POST['action'] === 'update_status') {
            $payroll_id = $_POST['payroll_id'];
            $status = $_POST['status'];
            $stmt = $mysqli->prepare("UPDATE payroll SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $payroll_id);
            if ($stmt->execute()) {
                $success_msg = "Payroll status updated successfully!";
            } else {
                $error_msg = "Error updating status.";
            }
        } elseif ($_POST['action'] === 'archive_payroll') {
            $payroll_id = $_POST['payroll_id'];
            $stmt = $mysqli->prepare("UPDATE payroll SET is_archived = 1 WHERE id = ?");
            $stmt->bind_param("i", $payroll_id);
            if ($stmt->execute()) {
                $success_msg = "Payroll archived successfully!";
            } else {
                $error_msg = "Error archiving payroll.";
            }
        } elseif ($_POST['action'] === 'unarchive_payroll') {
            $payroll_id = $_POST['payroll_id'];
            $stmt = $mysqli->prepare("UPDATE payroll SET is_archived = 0 WHERE id = ?");
            $stmt->bind_param("i", $payroll_id);
            if ($stmt->execute()) {
                $success_msg = "Payroll unarchived successfully!";
            } else {
                $error_msg = "Error unarchiving payroll.";
            }
        }
    }
}

$show_archived = isset($_GET['archived']) && $_GET['archived'] == '1';
$archive_filter = $show_archived ? "p.is_archived = 1" : "(p.is_archived = 0 OR p.is_archived IS NULL)";

$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_query = "SELECT p.*, CONCAT(u.fname, ' ', u.lname) as employee_name, u.role, u.hourly_rate FROM payroll p JOIN users u ON p.employee_id = u.id WHERE $archive_filter";
if ($filter_status != 'all') {
    $filter_query .= " AND p.status = '$filter_status'";
}
$filter_query .= " ORDER BY p.pay_period_end DESC, u.fname, u.lname";
$payrolls = $mysqli->query($filter_query);

$employees = $mysqli->query("SELECT u.*, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.role != 'admin' AND u.is_archived = 0 AND u.employee_status = 'active' ORDER BY u.fname ASC");

$active_filter = "(is_archived = 0 OR is_archived IS NULL)";
$pending = $mysqli->query("SELECT COUNT(*) as count FROM payroll WHERE status = 'pending' AND $active_filter");
$approved = $mysqli->query("SELECT COUNT(*) as count FROM payroll WHERE status = 'approved' AND $active_filter");
$paid = $mysqli->query("SELECT COUNT(*) as count FROM payroll WHERE status = 'paid' AND $active_filter");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Payroll Management - LoveTea HR</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        .archive-toggle {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
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
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
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
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['name']; ?></span>
                                <i class="fas fa-user-circle fa-2x text-success"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Payroll Management</h1>
                        <div>
                            <button class="btn btn-success" data-toggle="modal" data-target="#generatePayrollModal">
                                <i class="fas fa-calculator"></i> Generate All Payroll
                            </button>
                            <button class="btn btn-info" data-toggle="modal" data-target="#generateIndividualPayrollModal">
                                <i class="fas fa-user-plus"></i> Generate Individual
                            </button>
                        </div>
                    </div>

                    <?php if ($success_msg): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success_msg; ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php endif; ?>

                    <?php if ($error_msg): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error_msg; ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Employees</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $employees->num_rows; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Payrolls</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $pending->fetch_assoc()['count']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Approved Payrolls</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $approved->fetch_assoc()['count']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Paid Payrolls</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $paid->fetch_assoc()['count']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-success">Payroll Records</h6>
                            <div class="d-flex align-items-center">
                                <div class="mr-3 d-flex align-items-center">
                                    <span class="mr-2 text-sm font-weight-bold"><?php echo $show_archived ? 'Archived' : 'Active'; ?></span>
                                    <label class="archive-toggle mb-0">
                                        <input type="checkbox" id="archiveToggle" <?php echo $show_archived ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div>
                                    <a href="?status=all<?php echo $show_archived ? '&archived=1' : ''; ?>" class="btn btn-sm <?php echo $filter_status == 'all' ? 'btn-success' : 'btn-outline-success'; ?>">All</a>
                                    <a href="?status=pending<?php echo $show_archived ? '&archived=1' : ''; ?>" class="btn btn-sm <?php echo $filter_status == 'pending' ? 'btn-success' : 'btn-outline-success'; ?>">Pending</a>
                                    <a href="?status=approved<?php echo $show_archived ? '&archived=1' : ''; ?>" class="btn btn-sm <?php echo $filter_status == 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">Approved</a>
                                    <a href="?status=paid<?php echo $show_archived ? '&archived=1' : ''; ?>" class="btn btn-sm <?php echo $filter_status == 'paid' ? 'btn-success' : 'btn-outline-success'; ?>">Paid</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%">
                                    <thead>
                                        <tr class="bg-success text-white">
                                            <th>Employee</th>
                                            <th>Role</th>
                                            <th>Pay Period</th>
                                            <th>Total Hours</th>
                                            <th>Hourly Rate</th>
                                            <th>Gross Pay</th>
                                            <th>Deductions</th>
                                            <th>Net Pay</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($payrolls->num_rows > 0): ?>
                                            <?php while ($payroll = $payrolls->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payroll['employee_name']); ?></td>
                                                <td><span class="badge badge-success"><?php echo strtoupper($payroll['role']); ?></span></td>
                                                <td>
                                                    <small>
                                                        <?php echo date('M d', strtotime($payroll['pay_period_start'])) . ' - ' . date('M d, Y', strtotime($payroll['pay_period_end'])); ?>
                                                    </small>
                                                </td>
                                                <td><?php echo number_format($payroll['total_hours'], 2); ?> hrs</td>
                                                <td>₱<?php echo number_format($payroll['hourly_rate'], 2); ?></td>
                                                <td>₱<?php echo number_format($payroll['gross_pay'], 2); ?></td>
                                                <td>
                                                    <small>
                                                        Late: ₱<?php echo number_format($payroll['late_deductions'], 2); ?><br>
                                                        SSS: ₱<?php echo number_format($payroll['sss_contribution'] ?? 0, 2); ?><br>
                                                        Pag-IBIG: ₱<?php echo number_format($payroll['pagibig_contribution'] ?? 0, 2); ?><br>
                                                        PhilHealth: ₱<?php echo number_format($payroll['philhealth_contribution'] ?? 0, 2); ?>
                                                    </small>
                                                </td>
                                                <td><strong>₱<?php echo number_format($payroll['net_pay'], 2); ?></strong></td>
                                                <td>
                                                    <?php
                                                    $badge_class = 'secondary';
                                                    if ($payroll['status'] == 'approved') $badge_class = 'info';
                                                    if ($payroll['status'] == 'paid') $badge_class = 'success';
                                                    if ($payroll['status'] == 'pending') $badge_class = 'warning';
                                                    ?>
                                                    <span class="badge badge-<?php echo $badge_class; ?>"><?php echo strtoupper($payroll['status']); ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info mb-1" onclick="viewPayroll(<?php echo htmlspecialchars(json_encode($payroll)); ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if (!$show_archived && $payroll['status'] != 'paid'): ?>
                                                    <div class="btn-group mb-1"><button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <?php if ($payroll['status'] == 'pending'): ?>
                                                            <a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $payroll['id']; ?>, 'approved')">Approve</a>
                                                            <?php endif; ?>
                                                            <?php if ($payroll['status'] == 'approved'): ?>
                                                            <a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $payroll['id']; ?>, 'paid')">Mark as Paid</a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($show_archived): ?>
                                                    <button class="btn btn-sm btn-warning mb-1" onclick="unarchivePayroll(<?php echo $payroll['id']; ?>)" title="Unarchive">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary mb-1" onclick="archivePayroll(<?php echo $payroll['id']; ?>)" title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center">No payroll records found</td>
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

    <div class="modal fade" id="generateIndividualPayrollModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="generate_individual_payroll">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Generate Individual Payroll</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Generate payroll for a specific employee based on their attendance records.
                        </div>
                        <div class="form-group">
                            <label>Select Employee</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">-- Choose Employee --</option>
                                <?php 
                                $employees->data_seek(0);
                                while ($emp = $employees->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo htmlspecialchars($emp['fname'] . ' ' . $emp['lname']); ?> - 
                                    <?php echo strtoupper($emp['role']); ?> 
                                    (<?php echo htmlspecialchars($emp['dept_name'] ?? 'N/A'); ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Pay Period Start Date</label>
                            <input type="date" name="period_start" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Pay Period End Date</label>
                            <input type="date" name="period_end" class="form-control" required>
                        </div>
                        <div class="alert alert-warning">
                            <small><strong>Note:</strong> Only attendance records within this period will be calculated.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-calculator"></i> Generate Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="generatePayrollModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="generate_payroll">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Generate Payroll for All Employees</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This will generate payroll for all employees based on their attendance records in the selected period.
                        </div>
                        <div class="form-group">
                            <label>Pay Period Start Date</label>
                            <input type="date" name="period_start" class="form-control" required>
                            <small class="form-text text-muted">Example: 10/15/2024</small>
                        </div>
                        <div class="form-group">
                            <label>Pay Period End Date</label>
                            <input type="date" name="period_end" class="form-control" required>
                            <small class="form-text text-muted">Example: 11/14/2024 (before next pay date)</small>
                        </div>
                        <div class="alert alert-warning">
                            <small><strong>Note:</strong> Only employees with attendance records in this period will be included.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-calculator"></i> Generate Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewPayrollModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Payroll Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Employee:</strong> <span id="view_employee"></span></p>
                            <p><strong>Role:</strong> <span id="view_role"></span></p>
                            <p><strong>Pay Period:</strong> <span id="view_period"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span id="view_status"></span></p>
                            <p><strong>Hourly Rate:</strong> ₱<span id="view_rate"></span></p>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Total Hours Worked</strong></td>
                                <td class="text-right"><span id="view_hours"></span> hours</td>
                            </tr>
                            <tr>
                                <td><strong>Gross Pay</strong></td>
                                <td class="text-right">₱<span id="view_gross"></span></td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>Deductions:</strong></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;&nbsp;Late Deduction</td>
                                <td class="text-right text-danger">- ₱<span id="view_late"></span></td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;&nbsp;SSS Contribution</td>
                                <td class="text-right text-danger">- ₱<span id="view_sss"></span></td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;&nbsp;Pag-IBIG Contribution</td>
                                <td class="text-right text-danger">- ₱<span id="view_pagibig"></span></td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;&nbsp;PhilHealth Contribution</td>
                                <td class="text-right text-danger">- ₱<span id="view_philhealth"></span></td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Net Pay</strong></td>
                                <td class="text-right"><strong>₱<span id="view_net"></span></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="statusForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="payroll_id" id="status_payroll_id">
        <input type="hidden" name="status" id="status_value">
    </form>

    <form id="archiveForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="archive_payroll">
        <input type="hidden" name="payroll_id" id="archive_payroll_id">
    </form>

    <form id="unarchiveForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="unarchive_payroll">
        <input type="hidden" name="payroll_id" id="unarchive_payroll_id">
    </form>

    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "order": [[ 2, "desc" ]]
            });

            $('#archiveToggle').change(function() {
                const currentStatus = new URLSearchParams(window.location.search).get('status') || 'all';
                if (this.checked) {
                    window.location.href = '?status=' + currentStatus + '&archived=1';
                } else {
                    window.location.href = '?status=' + currentStatus;
                }
            });
        });
        
        function viewPayroll(payroll) {
            document.getElementById('view_employee').textContent = payroll.employee_name;
            document.getElementById('view_role').textContent = payroll.role.toUpperCase();
            document.getElementById('view_period').textContent = formatDate(payroll.pay_period_start) + ' - ' + formatDate(payroll.pay_period_end);
            document.getElementById('view_status').innerHTML = '<span class="badge badge-' + getStatusBadge(payroll.status) + '">' + payroll.status.toUpperCase() + '</span>';
            document.getElementById('view_rate').textContent = parseFloat(payroll.hourly_rate).toFixed(2);
            document.getElementById('view_hours').textContent = parseFloat(payroll.total_hours).toFixed(2);
            document.getElementById('view_gross').textContent = parseFloat(payroll.gross_pay).toFixed(2);
            document.getElementById('view_late').textContent = parseFloat(payroll.late_deductions).toFixed(2);
            document.getElementById('view_sss').textContent = parseFloat(payroll.sss_contribution || 0).toFixed(2);
            document.getElementById('view_pagibig').textContent = parseFloat(payroll.pagibig_contribution || 0).toFixed(2);
            document.getElementById('view_philhealth').textContent = parseFloat(payroll.philhealth_contribution || 0).toFixed(2);
            document.getElementById('view_net').textContent = parseFloat(payroll.net_pay).toFixed(2);
            $('#viewPayrollModal').modal('show');
        }

        function updateStatus(id, status) {
            if (confirm('Are you sure you want to update this payroll status to ' + status + '?')) {
                document.getElementById('status_payroll_id').value = id;
                document.getElementById('status_value').value = status;
                document.getElementById('statusForm').submit();
            }
        }

        function archivePayroll(id) {
            if (confirm('Are you sure you want to archive this payroll record?')) {
                document.getElementById('archive_payroll_id').value = id;
                document.getElementById('archiveForm').submit();
            }
        }

        function unarchivePayroll(id) {
            if (confirm('Are you sure you want to unarchive this payroll record?')) {
                document.getElementById('unarchive_payroll_id').value = id;
                document.getElementById('unarchiveForm').submit();
            }
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function getStatusBadge(status) {
            if (status == 'approved') return 'info';
            if (status == 'paid') return 'success';
            if (status == 'pending') return 'warning';
            return 'secondary';
        }
    </script>
</body>
</html>