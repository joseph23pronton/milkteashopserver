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
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
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
    <?php include "backend/nav.php"; ?>

    <div class="container-fluid">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <strong>Success!</strong> Inventory item added successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['archived'])): ?>
            <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                <strong>Archived!</strong> Inventory item has been archived successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['restored'])): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <strong>Restored!</strong> Inventory item has been restored successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Inventory Management</h1>
                <p class="mb-0 text-gray-600">Main inventory for all branches</p>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="header-actions">
                    <h6 class="m-0 font-weight-bold text-primary">Main Inventory List</h6>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-success btn-sm editStockBTN" data-toggle="modal" data-target="#addInventoryModal" data-id="0">
                            <i class="fas fa-plus"></i> Add Ingredient
                        </button>
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
                                <th>Inventory Name</th>
                                <th>Inventory Limit (Unit)</th>
                                <th>Price Per Unit <strong>₱</strong></th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $mysqli = include('database.php');
                            $sql = "SELECT *, COALESCE(is_archived, 0) as is_archived FROM ingredientsHeader ORDER BY is_archived ASC, id DESC";
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
                                            <td>{$row['name']}</td>
                                            <td>" . number_format($row['ingredients_limit'], 2) . " ({$row['unit']})</td>
                                            <td>{$row['price_per_unit']} <strong>₱</strong></td>
                                            <td>{$statusBadge}</td>
                                            <td>
                                                <div class='btn-group-action'>
                                                    <button type='button' class='btn btn-sm btn-primary editStockBTN' data-toggle='modal' data-target='#addInventoryModal' data-id='{$row['id']}'><i class='fas fa-edit'></i> Edit</button>
                                                    {$actionButton}
                                                </div>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>No records found</td></tr>";
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
                Are you sure you want to archive this inventory item?
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
                Are you sure you want to restore this inventory item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a id="confirmRestoreBtn" href="#" class="btn btn-success">Restore</a>
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="js/demo/datatables-demo.js"></script>

<script>
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

    $(document).ready(function() {
        $('tbody tr[data-archived="1"]').hide();

        $(document).on('click', '.editStockBTN', function(e) {
            e.preventDefault();
            var inventoryId = $(this).data('id');
            $.ajax({
                url: 'add_inventory.php',
                method: 'GET',
                data: { id: inventoryId },
                success: function(response) {
                    $('#addInventoryModalFormContainer').html(response);
                },
                error: function() {
                    $('#addInventoryModalFormContainer').html('<p>Error loading data. Please try again later.</p>');
                }
            });
        });
    });

    $('#confirmArchiveModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var invId = button.data('id');
        var archiveUrl = '/backend/archive.php?inv_id=' + invId;
        $('#confirmArchiveBtn').attr('href', archiveUrl);
    });

    $('#confirmRestoreModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var invId = button.data('id');
        var restoreUrl = '/backend/restore.php?inv_id=' + invId;
        $('#confirmRestoreBtn').attr('href', restoreUrl);
    });
</script>

</body>
</html>