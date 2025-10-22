<?php
require_once 'auth_check.php';
$mysqli = require __DIR__ . "/../database.php";

date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'mark') {
        $stmt = $mysqli->prepare("INSERT INTO attendance (employee_id, attendance_date, time_in, time_out, status, late_minutes, notes) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE time_in=VALUES(time_in), time_out=VALUES(time_out), status=VALUES(status), late_minutes=VALUES(late_minutes), notes=VALUES(notes)");
        $stmt->bind_param("issssss", $_POST['employee_id'], $_POST['attendance_date'], $_POST['time_in'], $_POST['time_out'], $_POST['status'], $_POST['late_minutes'], $_POST['notes']);
        $stmt->execute();
        header("Location: attendance.php?success=marked");
        exit;
    } elseif ($_POST['action'] === 'bulk_mark') {
        $date = $_POST['bulk_date'];
        $status = $_POST['bulk_status'];
        
        $employees = $mysqli->query("SELECT id FROM users WHERE role IN ('cashier', 'encoder', 'hr') AND is_archived = 0 AND employee_status = 'active'");
        
        while ($emp = $employees->fetch_assoc()) {
            $stmt = $mysqli->prepare("INSERT INTO attendance (employee_id, attendance_date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status=VALUES(status)");
            $stmt->bind_param("iss", $emp['id'], $date, $status);
            $stmt->execute();
        }
        
        header("Location: attendance.php?success=bulk_marked");
        exit;
    } elseif ($_POST['action'] === 'manual_time_in') {
        $user_id = $_SESSION['user_id'];
        $today = date('Y-m-d');
        $current_time = date('H:i:s');
        
        $work_start = '08:00:00';
        $late_minutes = 0;
        $status = 'present';
        
        if ($current_time > $work_start) {
            $diff = strtotime($current_time) - strtotime($work_start);
            $late_minutes = floor($diff / 60);
            $status = 'late';
        }
        
        $stmt = $mysqli->prepare("INSERT INTO attendance (employee_id, attendance_date, time_in, status, late_minutes, time_out) VALUES (?, ?, ?, ?, ?, NULL) ON DUPLICATE KEY UPDATE time_in = VALUES(time_in), status = VALUES(status), late_minutes = VALUES(late_minutes), time_out = NULL");
        $stmt->bind_param("isssi", $user_id, $today, $current_time, $status, $late_minutes);
        $stmt->execute();
        
        header("Location: attendance.php?success=time_in");
        exit;
    } elseif ($_POST['action'] === 'manual_time_out') {
        $user_id = $_SESSION['user_id'];
        $today = date('Y-m-d');
        $current_time = date('H:i:s');
        
        $stmt = $mysqli->prepare("UPDATE attendance SET time_out = ? WHERE employee_id = ? AND attendance_date = ? AND time_out IS NULL");
        $stmt->bind_param("sis", $current_time, $user_id, $today);
        $stmt->execute();
        
        header("Location: attendance.php?success=time_out");
        exit;
    }
}

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$attendance = $mysqli->query("SELECT a.*, u.fname, u.lname, u.role, d.name as dept_name FROM attendance a JOIN users u ON a.employee_id = u.id LEFT JOIN departments d ON u.department_id = d.id WHERE DATE(a.attendance_date) = '$selected_date' AND u.role IN ('cashier', 'encoder', 'hr') ORDER BY u.fname ASC");

$employees = $mysqli->query("SELECT u.*, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.role IN ('cashier', 'encoder', 'hr') AND u.is_archived = 0 AND u.employee_status = 'active' ORDER BY u.fname ASC");

$current_user_attendance = null;
if ($_SESSION['role'] != 'admin') {
    $user_id = $_SESSION['user_id'];
    $current_date = date('Y-m-d');
    $check_att = $mysqli->query("SELECT * FROM attendance WHERE employee_id = $user_id AND attendance_date = '$current_date'");
    if ($check_att->num_rows > 0) {
        $current_user_attendance = $check_att->fetch_assoc();
    }
}

$status_badges = [
    'present' => 'badge-success',
    'late' => 'badge-warning',
    'absent' => 'badge-danger',
    'on_leave' => 'badge-info'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Attendance Monitoring - LoveTea HR</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
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
                    <?php if ($_SESSION['role'] != 'admin'): ?>
                    <div class="row mb-4">
                        <div class="col-lg-12">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Your Attendance Today</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo date('l, F j, Y'); ?></div>
                                        </div>
                                        <?php if ($current_user_attendance): ?>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="mb-2">
                                                    <span class="badge <?php echo $status_badges[$current_user_attendance['status']]; ?> badge-lg">
                                                        <?php echo strtoupper(str_replace('_', ' ', $current_user_attendance['status'])); ?>
                                                    </span>
                                                </div>
                                                <div class="text-xs text-muted">
                                                    <strong>Time In:</strong> <?php echo date('h:i A', strtotime($current_user_attendance['time_in'])); ?><br>
                                                    <strong>Time Out:</strong> <?php echo $current_user_attendance['time_out'] ? date('h:i A', strtotime($current_user_attendance['time_out'])) : 'Not yet'; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <?php if ($current_user_attendance['time_out']): ?>
                                                <button class="btn btn-secondary" disabled>
                                                    <i class="fas fa-check-circle"></i> Completed
                                                </button>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="manual_time_out">
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fas fa-clock"></i> Time Out Now
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="col-md-8 text-right">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="manual_time_in">
                                                <button type="submit" class="btn btn-success btn-lg">
                                                    <i class="fas fa-clock"></i> Time In Now
                                                </button>
                                            </form>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Attendance Monitoring</h1>
                        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'hr'): ?>
                        <div>
                            <button class="btn btn-success mr-2" data-toggle="modal" data-target="#markAttendanceModal">
                                <i class="fas fa-plus"></i> Mark Attendance
                            </button>
                            <button class="btn btn-info" data-toggle="modal" data-target="#bulkMarkModal">
                                <i class="fas fa-users"></i> Bulk Mark
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php 
                        if ($_GET['success'] == 'marked') echo 'Attendance marked successfully!';
                        elseif ($_GET['success'] == 'bulk_marked') echo 'Bulk attendance marked successfully!';
                        elseif ($_GET['success'] == 'time_in') echo '✅ Time In recorded successfully!';
                        elseif ($_GET['success'] == 'time_out') echo '✅ Time Out recorded successfully!';
                        ?>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 font-weight-bold text-success">Attendance for: <?php echo date('F d, Y', strtotime($selected_date)); ?></h6>
                                </div>
                                <div class="col-auto">
                                    <form method="GET" class="form-inline">
                                        <input type="date" name="date" class="form-control form-control-sm" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Department</th>
                                            <th>Time In</th>
                                            <th>Time Out</th>
                                            <th>Status</th>
                                            <th>Late (mins)</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $existing_ids = [];
                                        while ($att = $attendance->fetch_assoc()): 
                                            $existing_ids[] = $att['employee_id'];
                                        ?>
                                        <tr>
                                            <td><?php echo $att['employee_id']; ?></td>
                                            <td><?php echo htmlspecialchars($att['fname'] . ' ' . $att['lname']); ?></td>
                                            <td><span class="badge badge-secondary"><?php echo strtoupper($att['role']); ?></span></td>
                                            <td><?php echo htmlspecialchars($att['dept_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo $att['time_in'] ? date('h:i A', strtotime($att['time_in'])) : '-'; ?></td>
                                            <td><?php echo $att['time_out'] ? date('h:i A', strtotime($att['time_out'])) : '-'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $status_badges[$att['status']]; ?>">
                                                    <?php echo strtoupper(str_replace('_', ' ', $att['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $att['late_minutes'] > 0 ? $att['late_minutes'] : '-'; ?></td>
                                            <td><?php echo htmlspecialchars($att['notes'] ?? '-'); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                        
                                        <?php
                                        $employees->data_seek(0);
                                        while ($emp = $employees->fetch_assoc()):
                                            if (!in_array($emp['id'], $existing_ids)):
                                        ?>
                                        <tr class="table-light">
                                            <td><?php echo $emp['id']; ?></td>
                                            <td><?php echo htmlspecialchars($emp['fname'] . ' ' . $emp['lname']); ?></td>
                                            <td><span class="badge badge-secondary"><?php echo strtoupper($emp['role']); ?></span></td>
                                            <td><?php echo htmlspecialchars($emp['dept_name'] ?? 'N/A'); ?></td>
                                            <td colspan="5" class="text-center text-muted">No attendance record</td>
                                        </tr>
                                        <?php 
                                            endif;
                                        endwhile; 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>
    </div>

    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'hr'): ?>
    <div class="modal fade" id="markAttendanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="mark">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Mark Attendance</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                <?php 
                                $employees->data_seek(0);
                                while ($emp = $employees->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo htmlspecialchars($emp['fname'] . ' ' . $emp['lname']) . ' - ' . strtoupper($emp['role']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="attendance_date" class="form-control" value="<?php echo $selected_date; ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Time In</label>
                                    <input type="time" name="time_in" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Time Out</label>
                                    <input type="time" name="time_out" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="present">Present</option>
                                        <option value="late">Late</option>
                                        <option value="absent">Absent</option>
                                        <option value="on_leave">On Leave</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Late Minutes</label>
                                    <input type="number" name="late_minutes" class="form-control" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Mark Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkMarkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="bulk_mark">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Bulk Mark Attendance</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small>This will mark attendance for all active employees</small>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="bulk_date" class="form-control" value="<?php echo $selected_date; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="bulk_status" class="form-control" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="on_leave">On Leave</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Bulk Mark</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
            $('#dataTable').DataTable();
        });
    </script>
</body>
</html>