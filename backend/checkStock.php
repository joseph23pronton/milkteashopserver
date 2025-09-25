<?php
header('Content-Type: application/json'); // Ensure response is JSON
session_start(); // Start session to access $_SESSION variables

function checkLowStock($threshold = 300) {
    // Connect to your database
    $mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php"; // Adjust to your database connection path

    // Base SQL query
    $query = "SELECT i.id AS productID, h.name AS productName, h.ingredients_limit, i.currentStock, b.name AS branchName
              FROM ingredients AS i 
              INNER JOIN ingredientsHeader AS h ON i.ingredientsID = h.id
              INNER JOIN branches AS b ON i.branchesID = b.id
              WHERE i.currentStock <= h.ingredients_limit";

    // If the role is not 'admin', filter by branch ID
    if ($_SESSION['role'] !== 'admin') {
        $query .= " AND i.branchesID = ?";
    }

    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        die(json_encode(['error' => 'SQL Error: ' . $mysqli->error]));
    }

    // Bind branch ID if necessary
    if ($_SESSION['role'] !== 'admin') {
        $stmt->bind_param("i", $_SESSION['branch_id']);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];

    // Fetch matching rows (low-stock items)
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'productName' => $row['productName'],
            'stockQuantity' => $row['currentStock'],
            'branchName' => $row['branchName']
        ];
    }

    // Clean up
    $stmt->close();
    $mysqli->close();

    return $notifications; // Return all notifications as an array
}

// Generate response for AJAX
$lowStockNotifications = checkLowStock();
if (!empty($lowStockNotifications)) {
    echo json_encode([
        'status' => 'low_stock',
        'notifications' => $lowStockNotifications
    ]);
} else {
    echo json_encode(['status' => 'ok']);
}
?>