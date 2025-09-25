<?php
$screen = 'branch';
ob_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Branch ID is required.");
}
$mysqli = include('database.php');
$branch_id = $_GET['id'];

$sql = "SELECT name, city FROM branches WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $branch_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No branch found with the provided ID.");
}

$branch = $result->fetch_assoc();
$branch_name = $branch['name'];
$branch_city = $branch['city'];

$table_name = strtolower($branch_name . '_' . $branch_city . '_inventory');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Restocking</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
    <?php 
include "backend/nav.php";

if (isset($_GET['inv_id'])) {
    $inventory_id = $_GET['inv_id'];

    // Update the SQL query to match the new database structure
    $sql = "
        SELECT 
            ih.id AS ingredient_id,
            ih.name AS stock_name,
            IFNULL(i.currentStock, 0) AS stock_qty,
            i.stockLimit AS initial_inventory,
            i.currentStock,
            i.lastRestock,
            i.updated_at AS created_at
        FROM 
            ingredientsHeader ih
        LEFT JOIN 
            ingredients i ON i.ingredientsID = ih.id
        WHERE 
            ih.id = ?
    ";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $inventory_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stock_id = isset($row['ingredient_id']) ? $row['ingredient_id'] : 'N/A';
            $stock_name = isset($row['stock_name']) ? $row['stock_name'] : 'N/A';
            $stock_qty = isset($row['stock_qty']) ? $row['stock_qty'] : 'N/A';
            $initial_inventory = isset($row['initial_inventory']) ? $row['initial_inventory'] : 'N/A';
            $current_stock = isset($row['currentStock']) ? $row['currentStock'] : 'N/A';
            $last_restock = isset($row['lastRestock']) ? $row['lastRestock'] : 'N/A';
            $created_at = isset($row['created_at']) ? $row['created_at'] : 'N/A';

        }
    } else {
        echo "<p>No records found for this inventory ID.</p>";
    }
} else {
    echo "<p>Invalid inventory ID.</p>";
    exit;
}
ob_end_flush();
?>

        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Restocking</h1>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Restock <u><?php echo $stock_name; ?></u></h6>
                        </div>
                        <div class="card-body">


                        <form action="backend/process_restock.php" method="post">
    <input type="hidden" name="branch_id" value="<?php echo $_GET['id']; ?>">
    <input type="hidden" name="ingredient_id" value="<?php echo $_GET['inv_id']; ?>">
    <div class="form-group">
        <label for="restock_quantity">Restock Quantity</label>
        <input type="number" class="form-control" name="restock_quantity" required>
    </div>
    <button type="submit" class="btn btn-primary">Restock</button>
</form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>

</html>