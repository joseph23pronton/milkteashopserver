<?php
$screen = 'branch';
ob_start();
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Branch ID is required.");
}

$mysqli = include('database.php');

// Fetch branch details
$sql = "SELECT name, city FROM branches WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No branch found with the provided ID.");

}

$branch = $result->fetch_assoc();
$sql_earnings = "
    SELECT SUM(s.quantity * s.price) AS total_earnings
    FROM sales s
    WHERE s.branchID = ? 
    AND MONTH(s.sales_date) = MONTH(CURRENT_DATE) 
    AND YEAR(s.sales_date) = YEAR(CURRENT_DATE) 
";
$stmt_earnings = $mysqli->prepare($sql_earnings);
$stmt_earnings->bind_param('i', $_GET['id']);
$stmt_earnings->execute();
$earnings_result = $stmt_earnings->get_result();
$earnings_row = $earnings_result->fetch_assoc();
$total_earnings = $earnings_row['total_earnings'] ?: 0; // Default to 0 if no earnings found

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Branch Dashboard</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="/css/login.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include "backend/nav.php";
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'cashier') {
            header("Location: branch_index.php");
            exit();
        } ?>

        <div class="container-fluid">
            <h1 class="h3 mb-2 text-gray-800"><?= $branch['name'] ?> Inventory</h1>
            <p class="mb-4">Branch Inventory in <?= $branch['city'] ?></p>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <a href="sales.php?id=<?= $_GET['id'] ?>&b_id=<?= $_GET['id'] ?>" class="card-link">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Sales
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">₱<?= number_format($total_earnings, 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Inventory List</h6>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                    <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success mt-3">Restock Successfully</div>
                        <?php endif; ?>
                        <?php if (isset($_GET['failed'])): ?>
                            <div class="alert alert-success mt-3">Restock Failed: <?php echo $_GET['failed']?></div>
                        <?php endif; ?>
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ingredient Name</th>
                                    <th>Current Stock</th>
                                    <th>Last Restock</th>
                                    <th>Updated At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "
SELECT
    i.id AS ingredient_id,
    ih.id AS inv_id,
    ih.name AS ingredient_name,
    COALESCE(i.currentStock, 0) AS current_stock,
    i.lastRestock AS last_restock,
    i.updated_at
FROM ingredientsHeader ih
LEFT JOIN ingredients i ON i.ingredientsID = ih.id AND i.branchesID = ?
";

                                $stmt = $mysqli->prepare($sql);
                                $stmt->bind_param('i', $_GET['id']); // Bind branch_id to the query
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $formAction = ($_SESSION['role'] === 'admin') ? 'backend/process_restock.php' : 'backend/request_restock.php';

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td><?php echo $row['ingredient_name']; ?></td>
                                            <td><?php echo $row['current_stock']; ?></td>
                                            <td><?php echo isset($row['last_restock']) ? $row['last_restock'] : 'N/A'; ?></td>
                                            <td><?php echo isset($row['updated_at']) ? $row['updated_at'] : 'N/A'; ?></td>
                                            <td>
                                            <button class="btn btn-success" data-toggle="modal" data-target="#restockModal"
                                                data-ingredient-id="<?php echo $row['inv_id']; ?>"
                                                data-branch-id="<?php echo $_GET['id']; ?>"
                                                data-ingredient-name="<?php echo htmlspecialchars($row['ingredient_name']); ?>">
                                                Restock
                                            </button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No ingredients found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Restock Modal -->
    <div class="modal fade" id="restockModal" tabindex="-1" role="dialog" aria-labelledby="restockModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restockModalLabel">Restock Ingredient</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo $formAction; ?>" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="branch_id" name="branchID" value="">
                        <input type="hidden" id="ingredient_id" name="ingredientsID">
                        <div class="form-group">
                            <label for="ingredient_name">Ingredient Name</label>
                            <input type="text" id="ingredient_name" class="form-control" name="ingredientsName" readonly>
                        </div>
                        <div class="form-group">
                            <label for="restockAmount">Restock Quantity</label>
                            <input type="number" class="form-control" id="restockAmount" name="restockAmount"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Restock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

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
                    <a class="btn btn-primary" href="logout.php">Logout</a>
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
        $('#restockModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var ingredientId = button.data('ingredient-id');
            var branchId = button.data('branch-id');
            var ingredientName = button.data('ingredient-name');

            // Update the modal fields
            var modal = $(this);
            modal.find('#ingredient_id').val(ingredientId);
            modal.find('#branch_id').val(branchId);
            modal.find('#ingredient_name').val(ingredientName);
        });
    </script>

</body>
<?php
$stmt->close();
$mysqli->close();
ob_end_flush();
?>

</html>