<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if (isset($_POST['restock_id'])) {
    $restock_id = (int) $_POST['restock_id'];
    $restock_amount = (int) $_POST['restockAmount'];
    $branch_id = (int) $_POST['branchID'];
    $ingredients_id = (int) $_POST['ingredientsID'];

    // Start transaction
    $mysqli->begin_transaction();
    
    try {
        // Get current restock order details
        $order_query = "SELECT * FROM restockOrder WHERE id = ? AND is_confirmed = 0";
        $order_stmt = $mysqli->prepare($order_query);
        $order_stmt->bind_param('i', $restock_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        
        if ($order_result->num_rows === 0) {
            throw new Exception("Order not found or already confirmed");
        }
        
        $order = $order_result->fetch_assoc();
        
        // Generate invoice number if not exists
        if (empty($order['invoice_number'])) {
            $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad($restock_id, 4, '0', STR_PAD_LEFT);
            
            // Update order with invoice number
            $update_invoice_query = "UPDATE restockOrder SET invoice_number = ? WHERE id = ?";
            $update_invoice_stmt = $mysqli->prepare($update_invoice_query);
            $update_invoice_stmt->bind_param('si', $invoice_number, $restock_id);
            $update_invoice_stmt->execute();
        }
        
        // Update restock order status (your original logic)
        $update_sql = "UPDATE restockOrder SET is_accepted = 1, confirmed_at = NOW(), confirmed_by = ? WHERE id = ?";
        $update_stmt = $mysqli->prepare($update_sql);
        $update_stmt->bind_param('ii', $_SESSION['user_id'], $restock_id);
        $update_stmt->execute();
        
        // Update or insert into ingredients table (enhanced version)
        $check_query = "SELECT currentStock FROM ingredients WHERE ingredientsID = ? AND branchesID = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param('ii', $ingredients_id, $branch_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing record
            $row = $check_result->fetch_assoc();
            $new_stock = $row['currentStock'] + $restock_amount;
            
            $update_ingredient_query = "UPDATE ingredients SET currentStock = ?, lastRestock = CURDATE(), updated_at = NOW() WHERE ingredientsID = ? AND branchesID = ?";
            $update_ingredient_stmt = $mysqli->prepare($update_ingredient_query);
            $update_ingredient_stmt->bind_param('iii', $new_stock, $ingredients_id, $branch_id);
            $update_ingredient_stmt->execute();
        } else {
            // Insert new record
            $insert_ingredient_query = "INSERT INTO ingredients (ingredientsID, branchesID, currentStock, lastRestock, created_at, updated_at) VALUES (?, ?, ?, CURDATE(), NOW(), NOW())";
            $insert_ingredient_stmt = $mysqli->prepare($insert_ingredient_query);
            $insert_ingredient_stmt->bind_param('iii', $ingredients_id, $branch_id, $restock_amount);
            $insert_ingredient_stmt->execute();
        }
        
        // Commit transaction
        $mysqli->commit();
        
        // Redirect back with success message (your original redirect)
        header("Location: ../index.php?success=restock_confirmed");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction
        $mysqli->rollback();
        
        // Redirect with error message
        header("Location: ../index.php?error=" . urlencode($e->getMessage()));
        exit;
    }

} else {
    // If restock_id is not set, redirect with an error
    header("Location: ../index.php?error=invalid_request");
    exit;
}
?>