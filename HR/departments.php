<?php
require_once 'auth_check.php';
$mysqli = require __DIR__ . "/../database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $mysqli->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $_POST['name'], $_POST['description']);
        $stmt->execute();
        header("Location: departments.php?success=added");
        exit;
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $mysqli->prepare("UPDATE departments SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $_POST['name'], $_POST['description'], $_POST['id']);
        $stmt->execute();
        header("Location: departments.php?success=updated");
        exit;
    } elseif ($_POST['action'] === 'delete') {
        $check = $mysqli->prepare("SELECT COUNT(*) as count FROM users WHERE department_id=? AND is_archived = 0 AND employee_status = 'active'");
        $check->bind_param("i", $_POST['id']);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            header("Location: departments.php?error=has_employees");
            exit;
        }
        
        $stmt = $mysqli->prepare("DELETE FROM departments WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        header("Location: departments.php?success=deleted");
        exit;
    }
}

$departments = $mysqli->query("SELECT d.*, COUNT(u.id) as employee_count FROM departments d LEFT JOIN users u ON d.id = u.department_id AND u.is_archived = 0 AND u.employee_status = 'active' GROUP BY d.id ORDER BY d.name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Department Management - LoveTea HR</title>
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
                        <h1 class="h3 mb-0 text-gray-800">Department Management</h1>
                        <button class="btn btn-success" data-toggle="modal" data-target="#addDepartmentModal">
                            <i class="fas fa-plus"></i> Add Department
                        </button>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php 
                        if ($_GET['success'] == 'added') echo 'Department added successfully!';
                        elseif ($_GET['success'] == 'updated') echo 'Department updated successfully!';
                        elseif ($_GET['success'] == 'deleted') echo 'Department deleted successfully!';
                        ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php 
                        if ($_GET['error'] == 'has_employees') echo 'Cannot delete department with active employees!';
                        ?>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">Departments</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Department Name</th>
                                            <th>Description</th>
                                            <th>Employee Count</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($dept = $departments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $dept['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($dept['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($dept['description'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge badge-success"><?php echo $dept['employee_count']; ?> employees</span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($dept['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info edit-btn" data-id="<?php echo $dept['id']; ?>" data-name="<?php echo htmlspecialchars($dept['name']); ?>" data-description="<?php echo htmlspecialchars($dept['description']); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($dept['employee_count'] == 0): ?>
                                                <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $dept['id']; ?>" data-name="<?php echo htmlspecialchars($dept['name']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
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
    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Add New Department</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Department Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Edit Department</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Department Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form method="POST" id="deleteForm">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <!-- Logout Modal -->
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

    <!-- JavaScript -->
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
                $('#edit_name').val($(this).data('name'));
                $('#edit_description').val($(this).data('description'));
                $('#editDepartmentModal').modal('show');
            });

            $('.delete-btn').click(function() {
                if (confirm('Are you sure you want to delete ' + $(this).data('name') + ' department?')) {
                    $('#delete_id').val($(this).data('id'));
                    $('#deleteForm').submit();
                }
            });
        });
    </script>
</body>
</html>