<?php
require_once '../finance/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    
    $update_query = "UPDATE restockorder SET is_confirmed = 1 WHERE id = ?";
    $stmt = $mysqli->prepare($update_query);
    
    if ($stmt === false) {
        die("Error preparing statement: " . $mysqli->error);
    }
    
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        header("Location: ../finance/purchase_orders.php?approved=1");
        exit();
    } else {
        error_log("Error executing statement: " . $stmt->error);
        $stmt->close();
        $mysqli->close();
        header("Location: ../finance/purchase_orders.php?error=1");
        exit();
    }
} else {
    header("Location: ../finance/purchase_orders.php");
    exit();
}
?>