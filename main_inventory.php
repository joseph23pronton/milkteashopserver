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
    <title>Main Inventory</title>
    <!-- Custom fonts for this template -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
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
            <div class="alert alert-success mt-3">Inventory Item Add Successfully</div>
        <?php endif; ?>
        <?php if (isset($_GET['del_success'])): ?>
            <div class="alert alert-danger mt-3">Inventory Item Deleted Successfully</div>
        <?php endif; ?>
        <!-- Page Heading -->
        <h1 class="h3 mb-2 text-gray-800">Inventory Management</h1>
        <p class="mb-4">Main Inventory For All Branches</p>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Main Inventory List</h6>
            </div>
            <div class="card-body">
            <button type='button' class='btn btn-success editStockBTN' data-toggle='modal' data-target='#addInventoryModal' data-id='0'>Add Ingredients</button>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Inventory Name</th>
                                <th>Inventory Limit (Unit)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $mysqli = include('database.php');
                            $sql = "SELECT * FROM ingredientsHeader";
                            $result = $mysqli->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr> <td>{$row['name']}</td><td>{$row['ingredients_limit']} ({$row['unit']})</td>
                                        <td>
                                            <!-- Edit Stock Button -->
                                            <button type='button' class='btn btn-success editStockBTN' data-toggle='modal' data-target='#addInventoryModal' data-id='{$row['id']}'>Edit Stock Name</button>
                                            <!-- Delete Stock Button -->
                                            <button type='button' class='btn btn-danger' data-toggle='modal' data-target='#confirmDeleteModal' data-id='{$row['id']}'>Delete Stock</button>
                                        </td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No records found</td></tr>";
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

<!-- Modal for Add/Edit Inventory -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" role="dialog" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInventoryModalLabel">Add or Edit Ingredients</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="addInventoryModalFormContainer">
                    Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Confirm Deletion -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
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

<!-- JavaScript -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="js/demo/datatables-demo.js"></script>

<script>
$(document).ready(function() {
    // Open modal for editing stock
    $(document).on('click', '.editStockBTN', function(e) {
        e.preventDefault();

        var inventoryId = $(this).data('id'); // Get the inventory ID from the data-id attribute

        // Use AJAX to load the form for editing the inventory item
        $.ajax({
            url: 'add_inventory.php',  // Path to the PHP script to load content
            method: 'GET',
            data: { id: inventoryId }, // Pass the inventory ID to the PHP script
            success: function(response) {
                // Load the fetched form into the modal
                $('#addInventoryModalFormContainer').html(response);
            },
            error: function() {
                $('#addInventoryModalFormContainer').html('<p>Error loading data. Please try again later.</p>');
            }
        });
    });
});
</script>

<script>
    // Handle delete button click
    $('#confirmDeleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var invId = button.data('id'); // Extract info from data-* attributes

        // Set the action URL for the confirmation button
        var deleteUrl = '/backend/delete.php?inv_id=' + invId;
        $('#confirmDeleteBtn').attr('href', deleteUrl);
    });
</script>

</body>
</html>
