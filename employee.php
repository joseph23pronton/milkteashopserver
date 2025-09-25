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

    <!-- Custom fonts for this template -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
    <?php include"backend/nav.php"; ?>


                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Employees Management</h1>
                    <p class="mb-4">Employees Assignment for Every Branches of Milktea Shop</p>
                    

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
                        </div>
                        <div class="card-body">
                        <a href="#" class='btn btn-success' data-toggle="modal" data-target="#addEmployeeModal">Add New Employee</a>
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success mt-3">Employee Updated Successfully</div>
                        <?php endif; ?>
                        <?php if (isset($_GET['failed'])): ?>
                            <div class="alert alert-success mt-3">Employee Update Failed: <?php echo $_GET['failed']?></div>
                        <?php endif; ?>
                        <?php if (isset($_GET['del_success'])): ?>
                            <div class="alert alert-danger mt-3">Employee Deleted Successfully</div>
                        <?php endif; ?>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Branch Assignment</th>
                                            <th>Role</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                            $mysqli = include('database.php');

                            $sql = "SELECT CONCAT(users.fname, ' ', users.lname) as fullname, 
                                    users.id, users.email, users.branch_assignment, users.role,
                                    branches.name as branch_name 
                                    FROM users 
                                    LEFT JOIN branches ON users.branch_assignment = branches.id";
                            
                            $result = $mysqli->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . htmlspecialchars($row['fullname']) . "</td>
                                            <td>" . htmlspecialchars($row['email']) . "</td>
                                            <td>" . htmlspecialchars($row['branch_name']) . "</td>
                                            <td>" . $row['role'] . "</td>
                                            <td>
                                                <a href='#' class='btn btn-success edit-employee-btn' data-id='" . htmlspecialchars($row['id']) . "'>Edit Employee</a>
                                                <a href='backend/delete.php?user_id=" . htmlspecialchars($row['id']) . "' class='btn btn-danger'>Delete Employee</a>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No records found</td></tr>";
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
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include"add_employee.php";?>
<!-- Modal -->
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
                <!-- Form will be dynamically loaded here -->
                <div id="editEmployeeFormContainer">
                    Loading...
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Logout Modal-->
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

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script>
$(document).ready(function() {
    $('.edit-employee-btn').on('click', function(e) {
        e.preventDefault();

        var employeeId = $(this).data('id');
        $('#editEmployeeModal').modal('show');

        // Fetch data via AJAX
        $.ajax({
            url: 'edit_employee.php',
            method: 'GET',
            data: { id: employeeId },
            success: function(response) {
                // Load the fetched content into the modal
                $('#editEmployeeFormContainer').html(response);
            },
            error: function() {
                $('#editEmployeeFormContainer').html('<p>Error loading data. Please try again later.</p>');
            }
        });
    });
});
</script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>

</body>

</html>