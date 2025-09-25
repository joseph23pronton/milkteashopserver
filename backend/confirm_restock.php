<?php
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock_id'])) {
    // Get POST parameters
    $restock_id = (int) $_POST['restock_id'];
    $branch_id = (int) $_POST['branchID'];
    $ingredient_id = (int) $_POST['ingredientsID'];
    $restock_quantity = (int) $_POST['restockAmount'];
    $current_date = date('Y-m-d H:i:s');

    // Start transaction
    $mysqli->begin_transaction();

    try {
        // Step 1: Check if the ingredient record exists for the given branch
        $check_sql = "
            SELECT id 
            FROM ingredients 
            WHERE ingredientsID = ? AND branchesID = ?
        ";
        $check_stmt = $mysqli->prepare($check_sql);
        if (!$check_stmt) {
            throw new Exception("SQL Error: " . $mysqli->error);
        }
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
                throw new Exception("SQL Error: " . $mysqli->error);
            }
            $update_stmt->bind_param('issi', $restock_quantity, $current_date, $current_date, $ingredient_record_id);
            $update_stmt->execute();
        } else {
            $new_id = rand(1000, 9999);
            // Record doesn't exist, create a new one
            $insert_sql = "
                INSERT INTO ingredients (id, ingredientsID, branchesID, currentStock, lastRestock, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?)
            ";
            $insert_stmt = $mysqli->prepare($insert_sql);
            if (!$insert_stmt) {
                throw new Exception("SQL Error: " . $mysqli->error);
            }
            $insert_stmt->bind_param('iiiiss', $new_id, $ingredient_id, $branch_id, $restock_quantity, $current_date, $current_date);
            $insert_stmt->execute();
        }

        // Step 2: Update restocOrder table to mark as confirmed
        $update_restock_sql = "
            UPDATE restockOrder 
            SET is_confirmed = 1 
            WHERE id = ?
        ";
        $update_restock_stmt = $mysqli->prepare($update_restock_sql);
        if (!$update_restock_stmt) {
            throw new Exception("SQL Error: " . $mysqli->error);
        }
        $update_restock_stmt->bind_param('i', $restock_id);
        $update_restock_stmt->execute();

        // Commit transaction
        $mysqli->commit();

        // Redirect to success page
        header("Location: /branch_index.php?id=$branch_id&success=true");
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();
        echo "Error processing restock: " . $e->getMessage();
    }

    // Close connection
    $mysqli->close();
} else {
    echo "Invalid request.";
}
?>