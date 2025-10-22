<?php
require_once 'auth_check.php';
$mysqli = require __DIR__ . "/../database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $mysqli->prepare("INSERT INTO users (id, fname, lname, email, password_hash, role, branch_assignment, department_id, hourly_rate, phone, address, hire_date, employee_status, password_changed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 0)");
        $id = rand(10000, 99999);
        $default_password = password_hash('LoveTea123', PASSWORD_DEFAULT);
        $stmt->bind_param("isssssiddss", $id, $_POST['fname'], $_POST['lname'], $_POST['email'], $default_password, $_POST['role'], $_POST['branch_assignment'], $_POST['department_id'], $_POST['hourly_rate'], $_POST['phone'], $_POST['address'], $_POST['hire_date']);
        $stmt->execute();
        header("Location: employees.php?success=added");
        exit;
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $mysqli->prepare("UPDATE users SET branch_assignment=?, department_id=?, hire_date=? WHERE id=?");
        $stmt->bind_param("iisi", $_POST['branch_assignment'], $_POST['department_id'], $_POST['hire_date'], $_POST['id']);
        $stmt->execute();
        header("Location: employees.php?success=updated");
        exit;
    }
}

$employees = $mysqli->query("SELECT u.*, d.name as dept_name, b.name as branch_name FROM users u LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN branches b ON u.branch_assignment = b.id WHERE u.role IN ('cashier', 'encoder', 'hr') AND u.is_archived = 0 AND u.employee_status = 'active' ORDER BY u.id DESC");
$departments = $mysqli->query("SELECT * FROM departments");
$branches = $mysqli->query("SELECT * FROM branches");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Employee Management - LoveTea HR</title>
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Employee Management</h1>
                        <button class="btn btn-success" data-toggle="modal" data-target="#addEmployeeModal">
                            <i class="fas fa-plus"></i> Add Employee
                        </button>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php 
                        if ($_GET['success'] == 'added') echo 'Employee added successfully!';
                        elseif ($_GET['success'] == 'updated') echo 'Employee updated successfully!';
                        ?>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">Active Employees</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Department</th>
                                            <th>Branch</th>
                                            <th>Hourly Rate</th>
                                            <th>Hire Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($emp = $employees->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $emp['id']; ?></td>
                                            <td><?php echo htmlspecialchars($emp['fname'] . ' ' . $emp['lname']); ?></td>
                                            <td><?php echo htmlspecialchars($emp['email']); ?></td>
                                            <td><span class="badge badge-success"><?php echo strtoupper($emp['role']); ?></span></td>
                                            <td><?php echo htmlspecialchars($emp['dept_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($emp['branch_name'] ?? 'N/A'); ?></td>
                                            <td>₱<?php echo number_format($emp['hourly_rate'], 2); ?></td>
                                            <td><?php echo $emp['hire_date'] ? date('M d, Y', strtotime($emp['hire_date'])) : 'N/A'; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info edit-btn" 
                                                    data-id="<?php echo $emp['id']; ?>" 
                                                    data-fname="<?php echo htmlspecialchars($emp['fname']); ?>" 
                                                    data-lname="<?php echo htmlspecialchars($emp['lname']); ?>" 
                                                    data-email="<?php echo htmlspecialchars($emp['email']); ?>" 
                                                    data-role="<?php echo $emp['role']; ?>" 
                                                    data-branch="<?php echo $emp['branch_assignment']; ?>" 
                                                    data-dept="<?php echo $emp['department_id']; ?>" 
                                                    data-hire="<?php echo $emp['hire_date']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
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

    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Add New Employee</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="fname" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="lname" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="role" class="form-control" required>
                                        <option value="cashier">Cashier</option>
                                        <option value="encoder">Encoder</option>
                                        <option value="hr">HR</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Department</label>
                                    <select name="department_id" class="form-control" required>
                                        <?php 
                                        $departments->data_seek(0);
                                        while ($dept = $departments->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Branch</label>
                                    <select name="branch_assignment" class="form-control" required>
                                        <?php 
                                        $branches->data_seek(0);
                                        while ($branch = $branches->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hourly Rate (₱)</label>
                                    <input type="number" step="0.01" name="hourly_rate" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Hire Date</label>
                            <input type="date" name="hire_date" class="form-control" required>
                        </div>
                        <div class="alert alert-info">
                            <small>Default password will be: <strong>LoveTea123</strong></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Edit Employee Assignment</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle"></i> You can only update Department, Branch, and Hire Date</small>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">First Name</label>
                                <p class="font-weight-bold" id="view_fname"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Last Name</label>
                                <p class="font-weight-bold" id="view_lname"></p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Email</label>
                            <p class="font-weight-bold" id="view_email"></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Role</label>
                            <p><span class="badge badge-success" id="view_role"></span></p>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="edit_dept" class="form-control" required>
                                <?php 
                                $departments->data_seek(0);
                                while ($dept = $departments->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Branch <span class="text-danger">*</span></label>
                            <select name="branch_assignment" id="edit_branch" class="form-control" required>
                                <?php 
                                $branches->data_seek(0);
                                while ($branch = $branches->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Hire Date <span class="text-danger">*</span></label>
                            <input type="date" name="hire_date" id="edit_hire" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Update Assignment</button>
                    </div>
                </form>
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
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();
            
            $('.edit-btn').click(function() {
                $('#edit_id').val($(this).data('id'));
                $('#view_fname').text($(this).data('fname'));
                $('#view_lname').text($(this).data('lname'));
                $('#view_email').text($(this).data('email'));
                $('#view_role').text($(this).data('role').toUpperCase());
                $('#edit_branch').val($(this).data('branch'));
                $('#edit_dept').val($(this).data('dept'));
                $('#edit_hire').val($(this).data('hire'));
                $('#editEmployeeModal').modal('show');
            });
        });
    </script>
</body>
</html>