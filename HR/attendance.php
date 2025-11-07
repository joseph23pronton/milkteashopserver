<?php
require_once 'auth_check.php';
$mysqli = require __DIR__ . "/../database.php";

date_default_timezone_set('Asia/Manila');


function getScheduledTime($mysqli, $employee_id, $date) {
    $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
    $day_of_week = strtolower(date('l', strtotime($date)));
    
    $query = $mysqli->prepare("SELECT {$day_of_week}_shift as shift FROM schedules WHERE employee_id = ? AND week_start = ?");
    $query->bind_param("is", $employee_id, $week_start);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows > 0) {
        $schedule = $result->fetch_assoc();
        return $schedule['shift'];
    }
    return null;
}

// Function to parse shift time and get start time
function getShiftStartTime($shift) {
    if (empty($shift) || $shift === 'OFF') {
        return null;
    }
    
    // Parse "9:00 AM - 5:00 PM" format
    $parts = explode(' - ', $shift);
    if (count($parts) >= 1) {
        return date('H:i:s', strtotime($parts[0]));
    }
    return null;
}

// Function to calculate late minutes
function calculateLateMinutes($time_in, $scheduled_start) {
    if (empty($scheduled_start)) {
        return 0;
    }
    
    $time_in_timestamp = strtotime($time_in);
    $scheduled_timestamp = strtotime($scheduled_start);
    
    if ($time_in_timestamp > $scheduled_timestamp) {
        return floor(($time_in_timestamp - $scheduled_timestamp) / 60);
    }
    return 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'mark') {
        $employee_id = $_POST['employee_id'];
        $attendance_date = $_POST['attendance_date'];
        $time_in = $_POST['time_in'];
        $time_out = $_POST['time_out'];
        $status = $_POST['status'];
        $notes = $_POST['notes'];
        
        // Get scheduled shift
        $scheduled_shift = getScheduledTime($mysqli, $employee_id, $attendance_date);
        $scheduled_start = getShiftStartTime($scheduled_shift);
        
        // Calculate late minutes if time in is provided
        $late_minutes = 0;
        if (!empty($time_in) && $scheduled_start) {
            $late_minutes = calculateLateMinutes($time_in, $scheduled_start);
            if ($late_minutes > 0 && $status === 'present') {
                $status = 'late';
            }
        }
        
        $stmt = $mysqli->prepare("INSERT INTO attendance (employee_id, attendance_date, time_in, time_out, status, late_minutes, notes, scheduled_shift) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE time_in=VALUES(time_in), time_out=VALUES(time_out), status=VALUES(status), late_minutes=VALUES(late_minutes), notes=VALUES(notes), scheduled_shift=VALUES(scheduled_shift)");
        $stmt->bind_param("issssiss", $employee_id, $attendance_date, $time_in, $time_out, $status, $late_minutes, $notes, $scheduled_shift);
        $stmt->execute();
        header("Location: attendance.php?success=marked");
        exit;
    } elseif ($_POST['action'] === 'bulk_mark') {
        $date = $_POST['bulk_date'];
        $status = $_POST['bulk_status'];
        
        // Fixed: Include all relevant roles for bulk marking
        $employees = $mysqli->query("SELECT id FROM users WHERE role IN ('cashier', 'encoder', 'hr', 'inventory', 'finance', 'sales', 'production') AND is_archived = 0 AND employee_status = 'active'");
        
        while ($emp = $employees->fetch_assoc()) {
            $scheduled_shift = getScheduledTime($mysqli, $emp['id'], $date);
            
            $stmt = $mysqli->prepare("INSERT INTO attendance (employee_id, attendance_date, status, scheduled_shift) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status=VALUES(status), scheduled_shift=VALUES(scheduled_shift)");
            $stmt->bind_param("isss", $emp['id'], $date, $status, $scheduled_shift);
            $stmt->execute();
        }
        
        header("Location: attendance.php?success=bulk_marked");
        exit;
    } elseif ($_POST['action'] === 'manual_time_in') {
        $user_id = $_SESSION['user_id'];
        $today = date('Y-m-d');
        $current_time = date('H:i:s');
        
        // Get employee's scheduled shift for today
        $scheduled_shift = getScheduledTime($mysqli, $user_id, $today);
        
        if (!$scheduled_shift || $scheduled_shift === 'OFF') {
            header("Location: attendance.php?error=no_schedule");
            exit;
        }
        
        $scheduled_start = getShiftStartTime($scheduled_shift);
        $late_minutes = calculateLateMinutes($current_time, $scheduled_start);
        
        $status = 'present';
        if ($late_minutes > 0) {
            $status = 'late';
        }
        
        $stmt = $mysqli->prepare("INSERT INTO attendance (employee_id, attendance_date, time_in, status, late_minutes, time_out, scheduled_shift) VALUES (?, ?, ?, ?, ?, NULL, ?) ON DUPLICATE KEY UPDATE time_in = VALUES(time_in), status = VALUES(status), late_minutes = VALUES(late_minutes), time_out = NULL, scheduled_shift = VALUES(scheduled_shift)");
        $stmt->bind_param("isssis", $user_id, $today, $current_time, $status, $late_minutes, $scheduled_shift);
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

// Fixed: Include all relevant roles in the attendance display query
$attendance = $mysqli->query("SELECT a.*, u.fname, u.lname, u.role, d.name as dept_name FROM attendance a JOIN users u ON a.employee_id = u.id LEFT JOIN departments d ON u.department_id = d.id WHERE DATE(a.attendance_date) = '$selected_date' AND u.role IN ('cashier', 'encoder', 'hr', 'inventory', 'finance', 'sales', 'production') ORDER BY u.fname ASC");

// Fixed: Include all relevant roles in the employees query (for unrecorded attendance and modals)
$employees = $mysqli->query("SELECT u.*, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.role IN ('cashier', 'encoder', 'hr', 'inventory', 'finance', 'sales', 'production') AND u.is_archived = 0 AND u.employee_status = 'active' ORDER BY u.fname ASC");

$current_user_attendance = null;
$current_user_schedule = null;
// The logic for displaying individual user attendance is based on $_SESSION['role'] != 'admin'
// and fetching by $_SESSION['user_id'], which is correct as it's specific to the logged-in user.
if ($_SESSION['role'] != 'admin') {
    $user_id = $_SESSION['user_id'];
    $current_date = date('Y-m-d');
    $check_att = $mysqli->query("SELECT * FROM attendance WHERE employee_id = $user_id AND attendance_date = '$current_date'");
    if ($check_att->num_rows > 0) {
        $current_user_attendance = $check_att->fetch_assoc();
    }
    $current_user_schedule = getScheduledTime($mysqli, $user_id, $current_date);
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
                                <i class="fas fa-user fa-fw text-success"></i>
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
                                        <div class="col-md-3">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Your Attendance Today</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo date('l, F j, Y'); ?></div>
                                            <?php if ($current_user_schedule && $current_user_schedule !== 'OFF'): ?>
                                            <div class="mt-2">
                                                <span class="badge badge-info"><i class="fas fa-clock"></i> Scheduled: <?php echo $current_user_schedule; ?></span>
                                            </div>
                                            <?php elseif ($current_user_schedule === 'OFF'): ?>
                                            <div class="mt-2">
                                                <span class="badge badge-secondary"><i class="fas fa-calendar-times"></i> Day Off</span>
                                            </div>
                                            <?php else: ?>
                                            <div class="mt-2">
                                                <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> No Schedule</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($current_user_attendance): ?>
                                        <div class="col-md-5">
                                            <div class="text-center">
                                                <div class="mb-2">
                                                    <span class="badge <?php echo $status_badges[$current_user_attendance['status']]; ?> badge-lg" style="font-size: 1rem; padding: 8px 15px;">
                                                        <?php echo strtoupper(str_replace('_', ' ', $current_user_attendance['status'])); ?>
                                                    </span>
                                                </div>
                                                <div class="text-sm">
                                                    <strong>Time In:</strong> <?php echo date('h:i A', strtotime($current_user_attendance['time_in'])); ?>
                                                    <?php if ($current_user_attendance['late_minutes'] > 0): ?>
                                                        <span class="text-warning"><i class="fas fa-exclamation-circle"></i> (<?php echo $current_user_attendance['late_minutes']; ?> mins late)</span>
                                                    <?php endif; ?>
                                                    <br>
                                                    <strong>Time Out:</strong> <?php echo $current_user_attendance['time_out'] ? date('h:i A', strtotime($current_user_attendance['time_out'])) : '<span class="text-muted">Not yet</span>'; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <?php if ($current_user_attendance['time_out']): ?>
                                                <button class="btn btn-secondary btn-lg" disabled>
                                                    <i class="fas fa-check-circle"></i> Attendance Completed
                                                </button>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="manual_time_out">
                                                    <button type="submit" class="btn btn-danger btn-lg">
                                                        <i class="fas fa-sign-out-alt"></i> Time Out Now
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="col-md-9 text-right">
                                            <?php if ($current_user_schedule && $current_user_schedule !== 'OFF'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="manual_time_in">
                                                    <button type="submit" class="btn btn-success btn-lg">
                                                        <i class="fas fa-sign-in-alt"></i> Time In Now
                                                    </button>
                                                </form>
                                            <?php elseif ($current_user_schedule === 'OFF'): ?>
                                                <div class="alert alert-secondary mb-0">
                                                    <i class="fas fa-info-circle"></i> You are scheduled OFF today
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning mb-0">
                                                    <i class="fas fa-exclamation-triangle"></i> No schedule found. Please contact HR.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-clipboard-check text-success"></i> Attendance Monitoring</h1>
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
                        if ($_GET['success'] == 'marked') echo '✅ Attendance marked successfully!';
                        elseif ($_GET['success'] == 'bulk_marked') echo '✅ Bulk attendance marked successfully!';
                        elseif ($_GET['success'] == 'time_in') echo '✅ Time In recorded successfully!';
                        elseif ($_GET['success'] == 'time_out') echo '✅ Time Out recorded successfully!';
                        ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php 
                        if ($_GET['error'] == 'no_schedule') echo '❌ You have no schedule for today. Please contact HR.';
                        ?>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-calendar-day"></i> Attendance for: <?php echo date('l, F d, Y', strtotime($selected_date)); ?>
                                    </h6>
                                </div>
                                <div class="col-auto">
                                    <form method="GET" class="form-inline">
                                        <label class="mr-2">Select Date:</label>
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
                                            <th>Scheduled Shift</th>
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
                                            <td>
                                                <?php if ($att['scheduled_shift'] && $att['scheduled_shift'] !== 'OFF'): ?>
                                                    <span class="badge badge-info"><?php echo htmlspecialchars($att['scheduled_shift']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">OFF</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $att['time_in'] ? date('h:i A', strtotime($att['time_in'])) : '-'; ?></td>
                                            <td><?php echo $att['time_out'] ? date('h:i A', strtotime($att['time_out'])) : '-'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $status_badges[$att['status']]; ?>">
                                                    <?php echo strtoupper(str_replace('_', ' ', $att['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($att['late_minutes'] > 0): ?>
                                                    <span class="text-warning font-weight-bold"><?php echo $att['late_minutes']; ?> mins</span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($att['notes'] ?? '-'); ?></td>
                                        </tr>
                                        
                                        <?php endwhile; ?>
                                        
                                        <?php
                                        // Reset pointer for $employees query to use it again
                                        $employees->data_seek(0);
                                        while ($emp = $employees->fetch_assoc()):
                                            if (!in_array($emp['id'], $existing_ids)):
                                                $emp_schedule = getScheduledTime($mysqli, $emp['id'], $selected_date);
                                        ?>
                                        <tr class="table-light">
                                            <td><?php echo $emp['id']; ?></td>
                                            <td><?php echo htmlspecialchars($emp['fname'] . ' ' . $emp['lname']); ?></td>
                                            <td><span class="badge badge-secondary"><?php echo strtoupper($emp['role']); ?></span></td>
                                            <td><?php echo htmlspecialchars($emp['dept_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($emp_schedule && $emp_schedule !== 'OFF'): ?>
                                                    <span class="badge badge-info"><?php echo htmlspecialchars($emp_schedule); ?></span>
                                                <?php elseif ($emp_schedule === 'OFF'): ?>
                                                    <span class="text-muted">OFF</span>
                                                <?php else: ?>
                                                    <span class="text-warning">No Schedule</span>
                                                <?php endif; ?>
                                            </td>
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
                            <label>Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" id="employee_select" class="form-control" required onchange="loadSchedule()">
                                <option value="">Select Employee</option>
                                <?php 
                                // Fixed: Ensure this dropdown also includes all roles
                                $employees_for_modal = $mysqli->query("SELECT u.id, u.fname, u.lname, u.role FROM users u WHERE u.role IN ('cashier', 'encoder', 'hr', 'inventory', 'finance', 'sales', 'production') AND u.is_archived = 0 AND u.employee_status = 'active' ORDER BY u.fname ASC");
                                $employees_for_modal->data_seek(0); // Reset pointer if already used
                                while ($emp = $employees_for_modal->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo htmlspecialchars($emp['fname'] . ' ' . $emp['lname']) . ' - ' . strtoupper($emp['role']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="<?php echo $selected_date; ?>" required onchange="loadSchedule()">
                        </div>
                        <div id="schedule_info" class="alert alert-info" style="display:none;">
                            <strong>Scheduled Shift:</strong> <span id="schedule_text"></span>
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="present">Present</option>
                                        <option value="late">Late</option>
                                        <option value="absent">Absent</option>
                                        <option value="on_leave">On Leave</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <small><i class="fas fa-info-circle"></i> Late minutes will be automatically calculated based on scheduled shift</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Mark Attendance</button>
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
                            <small><i class="fas fa-info-circle"></i> This will mark attendance for all active employees based on their schedules</small>
                        </div>
                        <div class="form-group">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="bulk_date" class="form-control" value="<?php echo $selected_date; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="bulk_status" class="form-control" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="on_leave">On Leave</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info"><i class="fas fa-users"></i> Bulk Mark</button>
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
            $('#dataTable').DataTable({
                "order": [[ 0, "asc" ]]
            });
        });

        function loadSchedule() {
            const employeeId = $('#employee_select').val();
            const date = $('#attendance_date').val();
            
            if (employeeId && date) {
                $.ajax({
                    url: 'HR/get_schedule.php', // Assuming this path is correct for your schedule fetching
                    method: 'POST',
                    data: { employee_id: employeeId, date: date },
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.schedule && data.schedule !== 'OFF') {
                            $('#schedule_text').text(data.schedule);
                            $('#schedule_info').show();
                        } else if (data.schedule === 'OFF') {
                            $('#schedule_text').text('Day OFF');
                            $('#schedule_info').show();
                        } else {
                            $('#schedule_info').hide();
                        }
                    },
                    error: function() {
                        // Handle error, e.g., show a message or log it
                        console.error('Error fetching schedule');
                        $('#schedule_info').hide();
                    }
                });
            }
        }
    </script>
</body>
</html>