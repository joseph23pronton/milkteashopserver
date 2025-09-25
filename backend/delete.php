<?php
// Include database connection
$conn = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

// Check if either 'inv_id' or 'prod_id' is passed
if (isset($_GET['inv_id'])) {
    // If inv_id is passed, delete from main_inventory
    $inv_id = (int)$_GET['inv_id'];

    // Prepare delete statement for main_inventory
    $stmt = $conn->prepare("DELETE FROM ingredientsHeader WHERE id = ?");
    $stmt->bind_param("i", $inv_id);

    if ($stmt->execute()) {
        header("Location: /main_inventory.php?del_success=true");
    } else {
        echo "Failed to delete inventory item.";
    }
} elseif (isset($_GET['prod_id'])) {
    // If prod_id is passed, delete from products
    $prod_id = (int)$_GET['prod_id'];

    // Prepare delete statement for products
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $prod_id);

    if ($stmt->execute()) {
        header("Location: /products.php?del_success=true");
    } else {
        echo "Failed to delete product.";
    }
}elseif (isset($_GET['user_id'])) {
    // If prod_id is passed, delete from products
    $user_id = (int)$_GET['user_id'];

    // Prepare delete statement for products
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: /employee.php?del_success=true");
    } else {
        echo "Failed to delete Employee.";
    }
} elseif (isset($_GET['branch_id'])) {
    // If prod_id is passed, delete from products
    $branch_id = (int)$_GET['branch_id'];

    // Prepare delete statement for products
    $stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
    $stmt->bind_param("i", $branch_id);

    if ($stmt->execute()) {
        header("Location: /branch_index.php?del_success=true");
    } else {
        echo "Failed to delete Employee.";
    }
} else {
    echo "No valid ID provided.";
}

// Close database connection
$conn->close();
?>