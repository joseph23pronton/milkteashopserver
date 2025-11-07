<?php
require_once 'auth_check.php';
$mysqli = require __DIR__ . "/../database.php";

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_shift') {
        $employee_id = $_POST['employee_id'];
        $week_start = $_POST['week_start'];
        $day = $_POST['day'];
        $shift = $_POST['shift'];
        
        $check = $mysqli->prepare("SELECT id FROM schedules WHERE employee_id = ? AND week_start = ?");
        $check->bind_param("is", $employee_id, $week_start);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $stmt = $mysqli->prepare("UPDATE schedules SET {$day}_shift = ? WHERE employee_id = ? AND week_start = ?");
            $stmt->bind_param("sis", $shift, $employee_id, $week_start);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO schedules (employee_id, week_start, monday_shift, tuesday_shift, wednesday_shift, thursday_shift, friday_shift, saturday_shift, sunday_shift) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $off = 'OFF';
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $shifts = [];
            foreach ($days as $d) {
                $shifts[] = ($d === $day) ? $shift : $off;
            }
            $stmt->bind_param("issssssss", $employee_id, $week_start, $shifts[0], $shifts[1], $shifts[2], $shifts[3], $shifts[4], $shifts[5], $shifts[6]);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $mysqli->error]);
        }
        exit;
    }
}

$current_week = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d', strtotime('monday this week'));
$week_start = date('Y-m-d', strtotime('monday', strtotime($current_week)));
$week_end = date('Y-m-d', strtotime('sunday', strtotime($current_week)));

$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

$count_query = $mysqli->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin' AND is_archived = 0 AND employee_status = 'active'");
$total_employees = $count_query->fetch_assoc()['total'];
$total_pages = ceil($total_employees / $per_page);

$employees = $mysqli->query("SELECT id, CONCAT(fname, ' ', lname) as name, role FROM users WHERE role != 'admin' AND is_archived = 0 AND employee_status = 'active' ORDER BY fname, lname LIMIT $per_page OFFSET $offset");

$schedules_data = [];
$schedules_query = $mysqli->prepare("SELECT * FROM schedules WHERE week_start = ?");
$schedules_query->bind_param("s", $week_start);
$schedules_query->execute();
$schedules_result = $schedules_query->get_result();

while ($row = $schedules_result->fetch_assoc()) {
    $schedules_data[$row['employee_id']] = $row;
}
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
    <style>
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }
        .schedule-table th {
            background: #1cc88a;
            color: white;
            padding: 12px;
            text-align: center;
            font-weight: 600;
        }
        .schedule-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #e3e6f0;
        }
        .employee-name {
            font-weight: 600;
            text-align: left !important;
            background: #f8f9fc;
        }
        .shift-cell {
            cursor: pointer;
            transition: all 0.2s;
            min-width: 120px;
        }
        .shift-cell:hover {
            background: #eaecf4;
        }
        .shift-off {
            color: #858796;
            font-style: italic;
        }
        .shift-active {
            background: #d1f2eb;
            color: #1cc88a;
            font-weight: 600;
        }
        .employee-role {
            font-size: 0.75rem;
            color: #858796;
        }
        .total-row {
            background: #f8f9fc;
            font-weight: 600;
        }
        .pagination .page-link {
            color: #1cc88a;
        }
        .pagination .page-item.active .page-link {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        .pagination .page-link:hover {
            color: #17a673;
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calendar-week text-success"></i> Weekly Schedule Planning</h1>
                        <div>
                            <a href="?week=<?php echo date('Y-m-d', strtotime('-7 days', strtotime($week_start))); ?>&page=<?php echo $page; ?>" class="btn btn-success">
                                <i class="fas fa-chevron-left"></i> Previous Week
                            </a>
                            <a href="?week=<?php echo date('Y-m-d', strtotime('+7 days', strtotime($week_start))); ?>&page=<?php echo $page; ?>" class="btn btn-success">
                                Next Week <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-calendar"></i> Week of <?php echo date('M d', strtotime($week_start)); ?> - <?php echo date('M d, Y', strtotime($week_end)); ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="schedule-table table">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Employee</th>
                                            <th>Monday<br><small><?php echo date('M d', strtotime('monday', strtotime($week_start))); ?></small></th>
                                            <th>Tuesday<br><small><?php echo date('M d', strtotime('tuesday', strtotime($week_start))); ?></small></th>
                                            <th>Wednesday<br><small><?php echo date('M d', strtotime('wednesday', strtotime($week_start))); ?></small></th>
                                            <th>Thursday<br><small><?php echo date('M d', strtotime('thursday', strtotime($week_start))); ?></small></th>
                                            <th>Friday<br><small><?php echo date('M d', strtotime('friday', strtotime($week_start))); ?></small></th>
                                            <th>Saturday<br><small><?php echo date('M d', strtotime('saturday', strtotime($week_start))); ?></small></th>
                                            <th>Sunday<br><small><?php echo date('M d', strtotime('sunday', strtotime($week_start))); ?></small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $employees->data_seek(0);
                                        while ($emp = $employees->fetch_assoc()): 
                                            $schedule = isset($schedules_data[$emp['id']]) ? $schedules_data[$emp['id']] : null;
                                        ?>
                                        <tr>
                                            <td class="employee-name">
                                                <?php echo htmlspecialchars($emp['name']); ?>
                                                <br><span class="badge badge-success employee-role"><?php echo strtoupper($emp['role']); ?></span>
                                            </td>
                                            <?php 
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            foreach ($days as $day):
                                                $shift = $schedule ? $schedule[$day . '_shift'] : 'OFF';
                                                $isOff = ($shift === 'OFF' || empty($shift));
                                            ?>
                                            <td class="shift-cell <?php echo $isOff ? 'shift-off' : 'shift-active'; ?>" 
                                                onclick="editShift(<?php echo $emp['id']; ?>, '<?php echo $day; ?>', '<?php echo htmlspecialchars($shift); ?>')">
                                                <?php echo $isOff ? 'OFF' : htmlspecialchars($shift); ?>
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <p class="text-muted mb-0">
                                        Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $per_page, $total_employees); ?> of <?php echo $total_employees; ?> employees
                                    </p>
                                </div>
                                <nav>
                                    <ul class="pagination mb-0">
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?week=<?php echo $week_start; ?>&page=<?php echo $page - 1; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        
                                        <?php
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        
                                        if ($start_page > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="?week=' . $week_start . '&page=1">1</a></li>';
                                            if ($start_page > 2) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                        }
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++) {
                                            $active = ($i == $page) ? 'active' : '';
                                            echo '<li class="page-item ' . $active . '"><a class="page-link" href="?week=' . $week_start . '&page=' . $i . '">' . $i . '</a></li>';
                                        }
                                        
                                        if ($end_page < $total_pages) {
                                            if ($end_page < $total_pages - 1) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="?week=' . $week_start . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                        }
                                        ?>
                                        
                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?week=<?php echo $week_start; ?>&page=<?php echo $page + 1; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <p class="text-muted"><i class="fas fa-info-circle"></i> Click on any shift cell to edit the schedule for that employee on that day.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalTitle">Edit Shift</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_employee_id">
                    <input type="hidden" id="edit_day">
                    <input type="hidden" id="edit_week_start" value="<?php echo $week_start; ?>">
                    
                    <div class="form-group">
                        <label><strong>Shift Time</strong></label>
                        <select id="edit_shift" class="form-control form-control-lg">
                            <option value="OFF">OFF</option>
                            <option value="6:00 AM - 2:00 PM">6:00 AM - 2:00 PM (8 hours)</option>
                            <option value="7:00 AM - 3:00 PM">7:00 AM - 3:00 PM (8 hours)</option>
                            <option value="8:00 AM - 4:00 PM">8:00 AM - 4:00 PM (8 hours)</option>
                            <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM (8 hours)</option>
                            <option value="10:00 AM - 6:00 PM">10:00 AM - 6:00 PM (8 hours)</option>
                            <option value="11:00 AM - 7:00 PM">11:00 AM - 7:00 PM (8 hours)</option>
                            <option value="12:00 PM - 8:00 PM">12:00 PM - 8:00 PM (8 hours)</option>
                            <option value="1:00 PM - 9:00 PM">1:00 PM - 9:00 PM (8 hours)</option>
                            <option value="2:00 PM - 10:00 PM">2:00 PM - 10:00 PM (8 hours)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="saveShift()"><i class="fas fa-save"></i> Save Shift</button>
                </div>
            </div>
        </div>
    </div>

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
        function editShift(employeeId, day, currentShift) {
            $('#edit_employee_id').val(employeeId);
            $('#edit_day').val(day);
            $('#edit_shift').val(currentShift);
            $('#modalTitle').text('Edit ' + day.charAt(0).toUpperCase() + day.slice(1) + ' Shift');
            $('#editShiftModal').modal('show');
        }

        function saveShift() {
            const employeeId = $('#edit_employee_id').val();
            const day = $('#edit_day').val();
            const shift = $('#edit_shift').val();
            const weekStart = $('#edit_week_start').val();

            $.ajax({
                url: 'schedules.php',
                method: 'POST',
                data: {
                    action: 'update_shift',
                    employee_id: employeeId,
                    week_start: weekStart,
                    day: day,
                    shift: shift
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating shift: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Error updating shift. Please try again.');
                }
            });
        }
    </script>
</body>
</html>