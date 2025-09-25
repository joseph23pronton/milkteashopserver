<?php
ob_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['branchID'], $_POST['ingredientsID'], $_POST['restockAmount'])) {
    $mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

    $branch_id = (int)$_POST['branchID'];
    $ingredient_id = (int)$_POST['ingredientsID'];
    $restock_quantity = (int)$_POST['restockAmount'];

    $current_date = date('Y-m-d');
    // Check if a record exists with the given ingredient and branch
    $check_sql = "
        SELECT id 
        FROM ingredients 
        WHERE ingredientsID = ? AND branchesID = ?
    ";
    $check_stmt = $mysqli->prepare($check_sql);

    if (!$check_stmt) {
        die("SQL Error: " . $mysqli->error);
    }

    // Bind parameters and execute the statement
    $check_stmt->bind_param('ii', $ingredient_id, $branch_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Record exists, update it
        $row = $result->fetch_assoc();
        $ingredient_record_id = $row['id'];

        $update_sql = "
            UPDATE ingredients 
            SET currentStock = currentStock + ?, lastRestock = ?, updated_at = ? 
            WHERE id = ?
        ";
        $update_stmt = $mysqli->prepare($update_sql);

        if (!$update_stmt) {
            die("SQL Error: " . $mysqli->error);
        }

        $update_stmt->bind_param('issi', $restock_quantity, $current_date, $current_date, $ingredient_record_id);
        $update_stmt->execute();

    } else {
        // Record doesn't exist, create a new one
        $insert_sql = "
            INSERT INTO ingredients (id, ingredientsID, branchesID, currentStock, lastRestock, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $insert_stmt = $mysqli->prepare($insert_sql);

        if (!$insert_stmt) {
            die("SQL Error: " . $mysqli->error);
        }

        // Generate a unique ID (adjust based on your table schema if needed)
        $new_id = rand(1000, 9999);

        $insert_stmt->bind_param('iiiiss', $new_id, $ingredient_id, $branch_id, $restock_quantity, $current_date, $current_date);
        $insert_stmt->execute();
    }
    header("Location: /view_branch.php?id=$branch_id&success=true");
    // Redirect to the branch details page or inventory overview
    exit;

} else {

    // Redirect to the branch details page or inventory overview
    header("Location: /view_branch.php?id=$branch_id&failed=true");
}
ob_end_flush();
?>