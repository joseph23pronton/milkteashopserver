<?php
$screen = 'products';
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

    <title>Products</title>

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
        <?php include "backend/nav.php"; ?>


        <!-- Begin Page Content -->
        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3">Product Update Successfully</div>
            <?php endif; ?>
            <?php if (isset($_GET['del_success'])): ?>
                <div class="alert alert-danger mt-3">Product Deleted Successfully</div>
            <?php endif; ?>
            <!-- Page Heading -->
            <h1 class="h3 mb-2 text-gray-800">Products Management</h1>
            <p class="mb-4">Products Management For All Branches</p>


            <!-- DataTales Example -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Products List</h6>
                </div>
                <div class="card-body">
                    <a href="add_products.php" class='btn btn-success'>Add Products</a>
                    <a href="main_inventory.php" class='btn btn-success'>Ingredients</a>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Product Size</th>
                                    <th>Product Price</th>
                                    <th>Initial Price</th>
                                    <th>Ingredients</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $mysqli = include('database.php');

                                $sql = "
                                    SELECT 
                                        p.id AS product_id, 
                                        p.name AS product_name, 
                                        p.size as product_size,
                                        p.price, 
                                        p.initial_price,
                                        GROUP_CONCAT(CONCAT(ih.name, ' <b>(', pi.quantityRequired, ' ', ih.unit, ')</b>') SEPARATOR '<br>') AS ingredients
                                    FROM products p
                                    LEFT JOIN products_ingredient pi ON p.id = pi.productID
                                    LEFT JOIN ingredientsHeader ih ON pi.ingredientsID = ih.id
                                    GROUP BY p.id, p.name, p.price
                                    ORDER BY p.id
                                        ";

                                $result = $mysqli->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                                        <td>{$row['product_name']}</td>
                                                        <td>{$row['product_size']}</td>
                                                        <td>{$row['price']}</td>
                                                        <td>{$row['initial_price']}</td>
                                                        <td>{$row['ingredients']}</td>
                                                        <td>
                                                            <form method='POST' action='update_user.php'>
                                                                <input type='hidden' name='uid' value='{$row['product_id']}'>
                                                                <a href='add_products.php?id={$row['product_id']}' class='btn btn-success'>Edit Product</a>
                                                                <button type='button' class='btn btn-danger' data-toggle='modal' data-target='#confirmDeleteModal' data-id='{$row['product_id']}'>Delete Product</button>
                                                            </form>
                                                        </td>
                                                    </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No records found</td></tr>";
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
    <!-- Delete Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
        aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this inventory item?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
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
    <script>
        $('#confirmDeleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var invId = button.data('id');
            var deleteUrl = '/backend/delete.php?prod_id=' + invId;
            $('#confirmDeleteBtn').attr('href', deleteUrl);
        });
    </script>
</body>

</html>