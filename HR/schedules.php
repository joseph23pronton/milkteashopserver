<?php
require_once 'auth_check.php';
$mysqli = require __DIR__ . "/../database.php";

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save_schedule') {
            $employee_id = $_POST['employee_id'];
            $week_start = $_POST['week_start'];
            $monday = $_POST['monday_shift'];
            $tuesday = $_POST['tuesday_shift'];
            $wednesday = $_POST['wednesday_shift'];
            $thursday = $_POST['thursday_shift'];
            $friday = $_POST['friday_shift'];
            $saturday = $_POST['saturday_shift'];
            $sunday = $_POST['sunday_shift'];
            
            $check = $mysqli->prepare("SELECT id FROM schedules WHERE employee_id = ? AND week_start = ?");
            $check->bind_param("is", $employee_id, $week_start);
            $check->execute();
            $result = $check->get_result();
            
            if ($result->num_rows > 0) {
                $stmt = $mysqli->prepare("UPDATE schedules SET monday_shift = ?, tuesday_shift = ?, wednesday_shift = ?, thursday_shift = ?, friday_shift = ?, saturday_shift = ?, sunday_shift = ? WHERE employee_id = ? AND week_start = ?");
                $stmt->bind_param("sssssssis", $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday, $employee_id, $week_start);
            } else {
                $stmt = $mysqli->prepare("INSERT INTO schedules (employee_id, week_start, monday_shift, tuesday_shift, wednesday_shift, thursday_shift, friday_shift, saturday_shift, sunday_shift) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssssss", $employee_id, $week_start, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday);
            }
            
            if ($stmt->execute()) {
                $success_msg = "Schedule saved successfully!";
            } else {
                $error_msg = "Error saving schedule.";
            }
        } elseif ($_POST['action'] === 'delete_schedule') {
            $schedule_id = $_POST['schedule_id'];
            $stmt = $mysqli->prepare("DELETE FROM schedules WHERE id = ?");
            $stmt->bind_param("i", $schedule_id);
            if ($stmt->execute()) {
                $success_msg = "Schedule deleted successfully!";
            } else {
                $error_msg = "Error deleting schedule.";
            }
        }
    }
}

$current_week = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d', strtotime('monday this week'));
$week_start = date('Y-m-d', strtotime('monday', strtotime($current_week)));
$week_end = date('Y-m-d', strtotime('sunday', strtotime($current_week)));

$employees = $mysqli->query("SELECT id, CONCAT(fname, ' ', lname) as name, role FROM users WHERE role IN ('cashier', 'encoder', 'hr') AND is_archived = 0 ORDER BY fname, lname");

$schedules_query = $mysqli->prepare("SELECT s.*, CONCAT(u.fname, ' ', u.lname) as employee_name, u.role FROM schedules s JOIN users u ON s.employee_id = u.id WHERE s.week_start = ? ORDER BY u.fname, u.lname");
$schedules_query->bind_param("s", $week_start);
$schedules_query->execute();
$schedules = $schedules_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Weekly Schedule - HR System</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
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
                        <h1 class="h3 mb-0 text-gray-800">Weekly Schedule</h1>
                        <button class="btn btn-success" data-toggle="modal" data-target="#addScheduleModal">
                            <i class="fas fa-plus"></i> Add Schedule
                        </button>
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

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-success">
                                Week of <?php echo date('M d', strtotime($week_start)); ?> - <?php echo date('M d, Y', strtotime($week_end)); ?>
                            </h6>
                            <div>
                                <a href="?week=<?php echo date('Y-m-d', strtotime('-7 days', strtotime($week_start))); ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                                <a href="?week=<?php echo date('Y-m-d', strtotime('+7 days', strtotime($week_start))); ?>" class="btn btn-sm btn-success">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="bg-success text-white">
                                            <th>Employee</th>
                                            <th>Role</th>
                                            <th>Monday</th>
                                            <th>Tuesday</th>
                                            <th>Wednesday</th>
                                            <th>Thursday</th>
                                            <th>Friday</th>
                                            <th>Saturday</th>
                                            <th>Sunday</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($schedules->num_rows > 0): ?>
                                            <?php while ($schedule = $schedules->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($schedule['employee_name']); ?></td>
                                                <td><span class="badge badge-success"><?php echo strtoupper($schedule['role']); ?></span></td>
                                                <td><?php echo htmlspecialchars($schedule['monday_shift']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['tuesday_shift']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['wednesday_shift']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['thursday_shift']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['friday_shift']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['saturday_shift']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['sunday_shift']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" onclick="editSchedule(<?php echo htmlspecialchars(json_encode($schedule)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteSchedule(<?php echo $schedule['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center">No schedules found for this week</td>
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

    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add/Edit Schedule</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="save_schedule">
                        <input type="hidden" name="week_start" value="<?php echo $week_start; ?>">
                        
                        <div class="form-group">
                            <label>Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                <?php 
                                $employees->data_seek(0);
                                while ($emp = $employees->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['name']); ?> (<?php echo $emp['role']; ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Monday</label>
                                    <select name="monday_shift" id="monday_shift" class="form-control">
                                        <option value="OFF">OFF</option>
                                        <option value="6:00 AM - 2:00 PM">6:00 AM - 2:00 PM</option>
                                        <option value="7:00 AM - 3:00 PM">7:00 AM - 3:00 PM</option>
                                        <option value="8:00 AM - 4:00 PM">8:00 AM - 4:00 PM</option>
                                        <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM</option>
                                        <option value="10:00 AM - 6:00 PM">10:00 AM - 6:00 PM</option>
                                        <option value="11:00 AM - 7:00 PM">11:00 AM - 7:00 PM</option>
                                        <option value="12:00 PM - 8:00 PM">12:00 PM - 8:00 PM</option>
                                        <option value="1:00 PM - 9:00 PM">1:00 PM - 9:00 PM</option>
                                        <option value="2:00 PM - 10:00 PM">2:00 PM - 10:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tuesday</label>
                                    <select name="tuesday_shift" id="tuesday_shift" class="form-control">
                                        <option value="OFF">OFF</option>
                                        <option value="6:00 AM - 2:00 PM">6:00 AM - 2:00 PM</option>
                                        <option value="7:00 AM - 3:00 PM">7:00 AM - 3:00 PM</option>
                                        <option value="8:00 AM - 4:00 PM">8:00 AM - 4:00 PM</option>
                                        <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM</option>
                                        <option value="10:00 AM - 6:00 PM">10:00 AM - 6:00 PM</option>
                                        <option value="11:00 AM - 7:00 PM">11:00 AM - 7:00 PM</option>
                                        <option value="12:00 PM - 8:00 PM">12:00 PM - 8:00 PM</option>
                                        <option value="1:00 PM - 9:00 PM">1:00 PM - 9:00 PM</option>
                                        <option value="2:00 PM - 10:00 PM">2:00 PM - 10:00 PM</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Wednesday</label>
                                    <select name="wednesday_shift" id="wednesday_shift" class="form-control">
                                        <option value="OFF">OFF</option>
                                        <option value="6:00 AM - 2:00 PM">6:00 AM - 2:00 PM</option>
                                        <option value="7:00 AM - 3:00 PM">7:00 AM - 3:00 PM</option>
                                        <option value="8:00 AM - 4:00 PM">8:00 AM - 4:00 PM</option>
                                        <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM</option>
                                        <option value="10:00 AM - 6:00 PM">10:00 AM - 6:00 PM</option>
                                        <option value="11:00 AM - 7:00 PM">11:00 AM - 7:00 PM</option>
                                        <option value="12:00 PM - 8:00 PM">12:00 PM - 8:00 PM</option>
                                        <option value="1:00 PM - 9:00 PM">1:00 PM - 9:00 PM</option>
                                        <option value="2:00 PM - 10:00 PM">2:00 PM - 10:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Thursday</label>
                                    <select name="thursday_shift" id="thursday_shift" class="form-control">
                                        <option value="OFF">OFF</option>
                                        <option value="6:00 AM - 2:00 PM">6:00 AM - 2:00 PM</option>
                                        <option value="7:00 AM - 3:00 PM">7:00 AM - 3:00 PM</option>
                                        <option value="8:00 AM - 4:00 PM">8:00 AM - 4:00 PM</option>
                                        <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM</option>
                                        <option value="10:00 AM - 6:00 PM">10:00 AM - 6:00 PM</option>
                                        <option value="11:00 AM - 7:00 PM">11:00 AM - 7:00 PM</option>
                                        <option value="12:00 PM - 8:00 PM">12:00 PM - 8:00 PM</option>
                                        <option value="1:00 PM - 9:00 PM">1:00 PM - 9:00 PM</option>
                                        <option value="2:00 PM - 10:00 PM">2:00 PM - 10:00 PM</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Friday</label>
                                    <select name="friday_shift" id="friday_shift" class="form-control">
                                        <option value="OFF">OFF</option>
                                        <option value="6:00 AM - 2:00 PM">6:00 AM - 2:00 PM</option>
                                        <option value="7:00 AM - 3:00 PM">7:00 AM - 3:00 PM</option>
                                        <option value="8:00 AM - 4:00 PM">8:00 AM - 4:00 PM</option>
                                        <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM</option>
                                        <option value="10:00 AM - 6:00 PM">10:00 AM - 6:00 PM</option>
                                        <option value="11:00 AM - 7:00 PM">11:00 AM - 7:00 PM</option>
                                        <option value="12:00 PM - 8:00 PM">12:00 PM - 8:00 PM</option>
                                        <option value="1:00 PM - 9:00 PM">1:00 PM - 9:00 PM</option>
                                        <option value="2:00 PM - 10:00 PM">2:00 PM - 10:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Saturday</label>
                                    <select name="saturday_shift" id="saturday_shift" class="form-control">
                                        <option value="OFF">OFF</option>
                                        <option value="6:00 AM - 2:00 PM">6:00 AM - 2:00 PM</option>
                                        <option value="7:00 AM - 3:00 PM">7:00 AM - 3:00 PM</option>
                                        <option value="8:00 AM - 4:00 PM">8:00 AM - 4:00 PM</option>
                                        <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM</option>
                                        <option value="10:00 AM - 6:00 PM">10:00 AM - 6:00 PM</option>
                                        <option value="11:00 AM - 7:00 PM">11:00 AM - 7:00 PM</option>
                                        <option value="12:00 PM - 8:00 PM">12:00 PM - 8:00 PM</option>
                                        <option value="1:00 PM - 9:00 PM">1:00 PM - 9:00 PM</option>
                                        <option value="2:00 PM - 10:00 PM">2:00 PM - 10:00 PM</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Sunday</label>
                            <select name="sunday_shift" id="sunday_shift" class="form-control">
                                <option value="OFF">OFF</option>
                                <option value="6:00 AM - 2:00 PM">6:00 AM - 2:00 PM</option>
                                <option value="7:00 AM - 3:00 PM">7:00 AM - 3:00 PM</option>
                                <option value="8:00 AM - 4:00 PM">8:00 AM - 4:00 PM</option>
                                <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM</option>
                                <option value="10:00 AM - 6:00 PM">10:00 AM - 6:00 PM</option>
                                <option value="11:00 AM - 7:00 PM">11:00 AM - 7:00 PM</option>
                                <option value="12:00 PM - 8:00 PM">12:00 PM - 8:00 PM</option>
                                <option value="1:00 PM - 9:00 PM">1:00 PM - 9:00 PM</option>
                                <option value="2:00 PM - 10:00 PM">2:00 PM - 10:00 PM</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete_schedule">
        <input type="hidden" name="schedule_id" id="delete_schedule_id">
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
    <script>
        function editSchedule(schedule) {
            document.getElementById('employee_id').value = schedule.employee_id;
            document.getElementById('monday_shift').value = schedule.monday_shift;
            document.getElementById('tuesday_shift').value = schedule.tuesday_shift;
            document.getElementById('wednesday_shift').value = schedule.wednesday_shift;
            document.getElementById('thursday_shift').value = schedule.thursday_shift;
            document.getElementById('friday_shift').value = schedule.friday_shift;
            document.getElementById('saturday_shift').value = schedule.saturday_shift;
            document.getElementById('sunday_shift').value = schedule.sunday_shift;
            $('#addScheduleModal').modal('show');
        }

        function deleteSchedule(id) {
            if (confirm('Are you sure you want to delete this schedule?')) {
                document.getElementById('delete_schedule_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>