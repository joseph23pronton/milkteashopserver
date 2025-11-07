<?php

require_once 'auth_check.php';
$mysqli = require __DIR__ . "/../database.php";

date_default_timezone_set('Asia/Manila');
$mysqli->query("SET time_zone = '+08:00'");

$total_employees = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE role IN ('cashier', 'encoder', 'hr', 'inventory', 'finance', 'sales', 'production') AND is_archived = 0")->fetch_assoc()['count'];

$total_departments = $mysqli->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'];

$dept_employees = $mysqli->query("SELECT d.name, COUNT(u.id) as count FROM departments d LEFT JOIN users u ON d.id = u.department_id AND u.is_archived = 0 AND u.role IN ('cashier', 'encoder', 'hr', 'inventory', 'finance', 'sales', 'production') GROUP BY d.id, d.name");

// FIXED: Count all attendance statuses (present, late, on_leave) - NOT just 'present'
$attendance_data = $mysqli->query("SELECT DATE(attendance_date) as date, 
    COUNT(*) as present_count, 
    (SELECT COUNT(*) FROM users WHERE role IN ('cashier', 'encoder', 'hr', 'inventory', 'finance', 'sales', 'production') AND is_archived = 0) as total_employees 
    FROM attendance 
    WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
    AND status IN ('present', 'late', 'on_leave') 
    GROUP BY DATE(attendance_date) 
    ORDER BY date ASC");

$dates = [];
$present = [];
$absent = [];
while ($row = $attendance_data->fetch_assoc()) {
    $dates[] = date('M d', strtotime($row['date']));
    $present[] = $row['present_count'];
    $absent[] = $row['total_employees'] - $row['present_count'];
}

// FIXED: Count all attendance today (present, late, on_leave) - NOT just 'present'
$today_attendance = $mysqli->query("SELECT COUNT(*) as count FROM attendance WHERE DATE(attendance_date) = CURDATE() AND status IN ('present', 'late', 'on_leave')")->fetch_assoc()['count'];
$attendance_rate = $total_employees > 0 ? round(($today_attendance / $total_employees) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>HR Dashboard - LoveTea</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'User'; ?></span>
                                <i class="fas fa-user-circle fa-2x text-success"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="../logout.php" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">HR Dashboard</h1>
                    </div>

                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Employees</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_employees; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Departments</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_departments; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-building fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Attendance</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_attendance; ?> / <?php echo $total_employees; ?></div>
                                            <div class="text-xs text-gray-600 mt-1">(<?php echo $attendance_rate; ?>% attendance rate)</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
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
                                    <h6 class="m-0 font-weight-bold text-success">Attendance Overview (Last 30 Days)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="attendanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success">Employees by Department</h6>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $dept_employees->data_seek(0);
                                    while ($dept = $dept_employees->fetch_assoc()): 
                                    ?>
                                    <div class="mb-3">
                                        <h4 class="small font-weight-bold"><?php echo htmlspecialchars($dept['name']); ?> <span class="float-right"><?php echo $dept['count']; ?> employees</span></h4>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $total_employees > 0 ? ($dept['count'] / $total_employees * 100) : 0; ?>%" aria-valuenow="<?php echo $dept['count']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $total_employees; ?>"></div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
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

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                    <a class="btn btn-success" href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script>
        var ctx = document.getElementById('attendanceChart').getContext('2d');
        var attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Present',
                    data: <?php echo json_encode($present); ?>,
                    backgroundColor: '#1cc88a',
                    borderColor: '#1cc88a',
                    borderWidth: 1
                }, {
                    label: 'Absent',
                    data: <?php echo json_encode($absent); ?>,
                    backgroundColor: '#e74a3b',
                    borderColor: '#e74a3b',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>