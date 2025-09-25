<?php
include 'db_connection.php'; // Ensure this includes your DB connection code

if (isset($_GET['name'])) {
    $name = $_GET['name'];
    
    $sql = "SELECT id, size, price FROM products WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    $sizes = [];
    while ($row = $result->fetch_assoc()) {
        $sizes[$row['size']] = ['id' => $row['id'], 'price' => $row['price']];
    }

    echo json_encode($sizes);
} else {
    echo json_encode(['error' => 'Product name not provided']);
}
?>