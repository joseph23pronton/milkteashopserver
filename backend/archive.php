<?php
$mysqli = include('../database.php');

if (isset($_GET['prod_id'])) {
    $prod_id = intval($_GET['prod_id']);
    $sql = "UPDATE products SET is_archived = 1 WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $prod_id);
    
    if ($stmt->execute()) {
        header("Location: ../products.php?archived=1");
    } else {
        header("Location: ../products.php?error=1");
    }
    $stmt->close();
}

if (isset($_GET['inv_id'])) {
    $inv_id = intval($_GET['inv_id']);
    $sql = "UPDATE ingredientsHeader SET is_archived = 1 WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $inv_id);
    
    if ($stmt->execute()) {
        header("Location: ../main_inventory.php?archived=1");
    } else {
        header("Location: ../main_inventory.php?error=1");
    }
    $stmt->close();
}

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $sql = "UPDATE users SET is_archived = 1 WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        header("Location: ../employee.php?archived=1");
    } else {
        header("Location: ../employee.php?error=1");
    }
    $stmt->close();
}

$mysqli->close();
?>