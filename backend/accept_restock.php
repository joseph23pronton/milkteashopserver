<?php
session_start();
require_once '../finance/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restock_amount = (int)$_POST['restockAmount'];
    $branch_id = (int)$_POST['branchID'];
    $ingredients_id = (int)$_POST['ingredientsID'];
    $restock_id = (int)$_POST['restock_id'];
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php?error=unauthorized");
        exit;
    }
    
    $mysqli->begin_transaction();
    
    try {
        // Check if order exists
        $order_query = "SELECT * FROM restockorder WHERE id = ? AND is_confirmed = 0";
        $order_stmt = $mysqli->prepare($order_query);
        
        if (!$order_stmt) {
            throw new Exception("Prepare failed (order_query): " . $mysqli->error);
        }
        
        $order_stmt->bind_param('i', $restock_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        
        if ($order_result->num_rows === 0) {
            throw new Exception("Order not found or already confirmed");
        }
        
        $order = $order_result->fetch_assoc();
        
        // Generate invoice number if needed
        if (empty($order['invoice_number'])) {
            $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad($restock_id, 4, '0', STR_PAD_LEFT);
            
            $update_invoice_query = "UPDATE restockorder SET invoice_number = ? WHERE id = ?";
            $update_invoice_stmt = $mysqli->prepare($update_invoice_query);
            
            if (!$update_invoice_stmt) {
                throw new Exception("Prepare failed (update_invoice): " . $mysqli->error);
            }
            
            $update_invoice_stmt->bind_param('si', $invoice_number, $restock_id);
            $update_invoice_stmt->execute();
        }
        
        // Determine redirect location
        $redirect_to = '../index.php';
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'finance/supplies.php') !== false) {
            $redirect_to = '../finance/supplies.php';
        }
        
        // Update restock order status (without confirmed_by since it's not in your table)
        $update_sql = "UPDATE restockorder SET is_accepted = 1, is_confirmed = 1, confirmed_at = NOW() WHERE id = ?";
        $update_stmt = $mysqli->prepare($update_sql);
        
        if (!$update_stmt) {
            throw new Exception("Prepare failed (update_restock): " . $mysqli->error);
        }
        
        $update_stmt->bind_param('i', $restock_id);
        $update_stmt->execute();
        
        // Check if ingredient exists for this branch
        $check_query = "SELECT currentStock FROM ingredients WHERE ingredientsID = ? AND branchesID = ?";
        $check_stmt = $mysqli->prepare($check_query);
        
        if (!$check_stmt) {
            throw new Exception("Prepare failed (check_ingredient): " . $mysqli->error);
        }
        
        $check_stmt->bind_param('ii', $ingredients_id, $branch_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing ingredient stock
            $row = $check_result->fetch_assoc();
            $new_stock = $row['currentStock'] + $restock_amount;
            
            $update_ingredient_query = "UPDATE ingredients SET currentStock = ?, lastRestock = CURDATE(), updated_at = NOW() WHERE ingredientsID = ? AND branchesID = ?";
            $update_ingredient_stmt = $mysqli->prepare($update_ingredient_query);
            
            if (!$update_ingredient_stmt) {
                throw new Exception("Prepare failed (update_ingredient): " . $mysqli->error);
            }
            
            $update_ingredient_stmt->bind_param('iii', $new_stock, $ingredients_id, $branch_id);
            $update_ingredient_stmt->execute();
        } else {
            // Insert new ingredient record
            $insert_ingredient_query = "INSERT INTO ingredients (ingredientsID, branchesID, currentStock, lastRestock, created_at, updated_at) VALUES (?, ?, ?, CURDATE(), NOW(), NOW())";
            $insert_ingredient_stmt = $mysqli->prepare($insert_ingredient_query);
            
            if (!$insert_ingredient_stmt) {
                throw new Exception("Prepare failed (insert_ingredient): " . $mysqli->error);
            }
            
            $insert_ingredient_stmt->bind_param('iii', $ingredients_id, $branch_id, $restock_amount);
            $insert_ingredient_stmt->execute();
        }
        
        $mysqli->commit();
        
        header("Location: " . $redirect_to . "?success=1");
        exit;
        
    } catch (Exception $e) {
        $mysqli->rollback();
        
        $redirect_to = '../index.php';
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'finance/supplies.php') !== false) {
            $redirect_to = '../finance/supplies.php';
        }
        
        header("Location: " . $redirect_to . "?error=" . urlencode($e->getMessage()));
        exit;
    }
    
} else {
    header("Location: ../index.php?error=invalid_request");
    exit;
}
?>