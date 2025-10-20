<?php
$screen = 'employee';
ob_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Employees</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <style>
        .archived-row {
            background-color: #f8f9fa;
            opacity: 0.7;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-group-action {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .alert {
            border-radius: 0.35rem;
            border-left: 4px solid;
        }
        .alert-success {
            border-left-color: #1cc88a;
        }
        .alert-danger {
            border-left-color: #e74a3b;
        }
        .alert-warning {
            border-left-color: #f6c23e;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .switch input {
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
            background-color: #4e73df;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #858796;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .switch-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #5a5c69;
        }
    </style>

</head>

<body id="page-top">

    <div id="wrapper">
    <?php include"backend/nav.php"; ?>


                <div class="container-fluid">

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <strong>Success!</strong> Employee updated successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['failed'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <strong>Error!</strong> Employee update failed: <?php echo htmlspecialchars($_GET['failed'])?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['del_success'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <strong>Deleted!</strong> Employee deleted successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['archived'])): ?>
                        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                            <strong>Archived!</strong> Employee has been archived successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['restored'])): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <strong>Restored!</strong> Employee has been restored successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">Employees Management</h1>
                            <p class="mb-0 text-gray-600">Employees Assignment for Every Branches of Milktea Shop</p>
                        </div>
                    </div>
                    

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="header-actions">
                                <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
                                <div class="ml-auto">
                                    <a href="#" class='btn btn-success btn-sm' data-toggle="modal" data-target="#addEmployeeModal">
                                        <i class="fas fa-plus"></i> Add New Employee
                                    </a>
                                    <label class="switch-label">
                                        <span id="viewLabel">Active</span>
                                        <label class="switch">
                                            <input type="checkbox" id="toggleArchive">
                                            <span class="slider"></span>
                                        </label>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Branch Assignment</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                            $mysqli = include('database.php');

                            $sql = "SELECT CONCAT(users.fname, ' ', users.lname) as fullname, 
                                    users.id, users.email, users.branch_assignment, users.role,
                                    branches.name as branch_name,
                                    COALESCE(users.is_archived, 0) as is_archived
                                    FROM users 
                                    LEFT JOIN branches ON users.branch_assignment = branches.id
                                    ORDER BY users.is_archived ASC, users.id DESC";
                            
                            $result = $mysqli->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $isArchived = $row['is_archived'];
                                    $rowClass = $isArchived ? 'archived-row' : '';
                                    $statusBadge = $isArchived ? '<span class="badge badge-secondary">Archived</span>' : '<span class="badge badge-success">Active</span>';
                                    $actionButton = $isArchived 
                                        ? "<button type='button' class='btn btn-sm btn-success' data-toggle='modal' data-target='#confirmRestoreModal' data-id='{$row['id']}'><i class='fas fa-undo'></i> Restore</button>"
                                        : "<button type='button' class='btn btn-sm btn-warning' data-toggle='modal' data-target='#confirmArchiveModal' data-id='{$row['id']}'><i class='fas fa-archive'></i> Archive</button>";
                                    
                                    echo "<tr class='{$rowClass}' data-archived='{$isArchived}'>
                                            <td>" . htmlspecialchars($row['fullname']) . "</td>
                                            <td>" . htmlspecialchars($row['email']) . "</td>
                                            <td>" . htmlspecialchars($row['branch_name']) . "</td>
                                            <td>" . htmlspecialchars($row['role']) . "</td>
                                            <td>{$statusBadge}</td>
                                            <td>
                                                <div class='btn-group-action'>
                                                    <a href='#' class='btn btn-sm btn-primary edit-employee-btn' data-id='" . htmlspecialchars($row['id']) . "'><i class='fas fa-edit'></i> Edit</a>
                                                    {$actionButton}
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No records found</td></tr>";
                            }
                            
                            $mysqli->close();
                            ob_end_flush();
                            ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include"add_employee.php";?>

    <div class="modal fade" id="editEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="editEmployeeFormContainer">
                        Loading...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
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
                    <a class="btn btn-primary" href="backend/signout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmArchiveModal" tabindex="-1" role="dialog" aria-labelledby="confirmArchiveModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmArchiveModalLabel">Confirm Archive</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to archive this employee?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="confirmArchiveBtn" href="#" class="btn btn-warning">Archive</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmRestoreModal" tabindex="-1" role="dialog" aria-labelledby="confirmRestoreModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmRestoreModalLabel">Confirm Restore</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to restore this employee?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="confirmRestoreBtn" href="#" class="btn btn-success">Restore</a>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script>
$(document).ready(function() {
    $('.edit-employee-btn').on('click', function(e) {
        e.preventDefault();

        var employeeId = $(this).data('id');
        $('#editEmployeeModal').modal('show');

        $.ajax({
            url: 'edit_employee.php',
            method: 'GET',
            data: { id: employeeId },
            success: function(response) {
                $('#editEmployeeFormContainer').html(response);
            },
            error: function() {
                $('#editEmployeeFormContainer').html('<p>Error loading data. Please try again later.</p>');
            }
        });
    });

    $('tbody tr[data-archived="1"]').hide();

    $('#toggleArchive').on('change', function() {
        if ($(this).is(':checked')) {
            $('tbody tr[data-archived="0"]').hide();
            $('tbody tr[data-archived="1"]').show();
            $('#viewLabel').text('Archived');
        } else {
            $('tbody tr[data-archived="0"]').show();
            $('tbody tr[data-archived="1"]').hide();
            $('#viewLabel').text('Active');
        }
    });

    $('#confirmArchiveModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var userId = button.data('id');
        var archiveUrl = 'backend/archive.php?user_id=' + userId;
        $('#confirmArchiveBtn').attr('href', archiveUrl);
    });

    $('#confirmRestoreModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var userId = button.data('id');
        var restoreUrl = 'backend/restore.php?user_id=' + userId;
        $('#confirmRestoreBtn').attr('href', restoreUrl);
    });
});
</script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script src="js/demo/datatables-demo.js"></script>

</body>

</html>