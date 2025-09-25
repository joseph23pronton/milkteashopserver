<?php
$screen = 'branch';
ob_start();
// Check if branch ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Branch ID is required.");
}
$mysqli = include('database.php');

$branch_id = $_GET['id'];
// Step 1: Get branch_name and branch_city from branches table
$sql = "SELECT branch_name, branch_city FROM branches WHERE branch_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $branch_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if branch exists
if ($result->num_rows === 0) {
    die("No branch found with the provided ID.");
}

$branch = $result->fetch_assoc();
$branch_name = $branch['branch_name'];
$branch_city = $branch['branch_city'];

// Step 2: Construct the inventory table name
$table_name = strtolower($branch_name . '_' . $branch_city . '_inventory'); // Use lower case for table names

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags and styles omitted for brevity -->
</head>

<body id="page-top">
    <!-- Page Wrapper and other components omitted for brevity -->
    
    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800"><?= $branch_name ?> Inventory</h1>
        <p class="mb-4">Branch Inventory in <?= $branch_city ?></p>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Inventory List</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Stock Name</th>
                                <th>Stock Quantity</th>
                                <th>Last Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Step 3: Query the main_inventory and join with the branch inventory table
                            $sql = "
                                SELECT 
                                    m.id,
                                    m.name AS stock_name,
                                    IFNULL(bi.current_stock, 0) AS stock_qty,
                                    bi.last_restock AS created_at
                                FROM 
                                    main_inventory m
                                LEFT JOIN 
                                    `$table_name` bi ON m.id = bi.inv_id
                            ";

                            $stmt = $mysqli->prepare($sql);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>{$row['stock_name']}</td>
                                        <td>{$row['stock_qty']}</td>
                                        <td>{$row['created_at']}</td>
                                        <td>
                                            <a href='#' class='btn btn-success'>Restock</a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No records found for this branch.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts omitted for brevity -->

</body>
<?php
// Closing the connection after all operations are done
$stmt->close();
$mysqli->close();
ob_end_flush();
?>
</html>