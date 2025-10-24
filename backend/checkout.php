<?php
// Assuming session and database connection are already set up
session_start();
date_default_timezone_set('Asia/Manila');
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read and decode raw JSON input
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input: ' . json_last_error_msg()]);
        exit();
    }

    // Retrieve necessary data
    $branchID = $_SESSION['branch_id'];
    $cashierID = $_SESSION['user_id'];

    $customerName = isset($data['customerName']) ? trim($data['customerName']) : 'Walk-in';
    $orderItems = $data['orderItems'] ?? [];

    if (empty($orderItems)) {
        echo json_encode(['status' => 'error', 'message' => 'Order items cannot be empty.']);
        exit();
    }

    $receiptID = 'REC' . time(); // Unique receipt ID using timestamp
    $salesDate = date('Y-m-d H:i:s');

    // Start transaction
    $mysqli->begin_transaction();

    try {
        foreach ($orderItems as $item) {
            $productName = $item['product'];
            $size = $item['size'];
            $quantity = 1;
            $price = $item['price'];
            $totalPrice = $price * $quantity;

            // Insert sale record
            $stmt = $mysqli->prepare("INSERT INTO sales (branchID, receiptID, productName, price, quantity, totalPrice, sales_date, customerName, cashierID) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issiiissi", $branchID, $receiptID, $id, $price, $quantity, $totalPrice, $salesDate, $customerName, $cashierID);
            $stmt->execute();

            // Reduce ingredients based on product requirements
            $ingredientQuery = "SELECT ingredientsID, quantityRequired FROM products_ingredient WHERE productID = ?";
            $ingredientStmt = $mysqli->prepare($ingredientQuery);
            $ingredientStmt->bind_param("i", $id);
            $ingredientStmt->execute();
            $ingredientResult = $ingredientStmt->get_result();

            while ($ingredient = $ingredientResult->fetch_assoc()) {
                $ingredientID = $ingredient['ingredientsID'];
                $requiredQty = $ingredient['quantityRequired'] * $quantity; // Calculate total required

                // Update ingredients table
                $updateQuery = "UPDATE ingredients 
                                SET currentStock = currentStock - ? 
                                WHERE ingredientsID = ? AND branchesID = ? AND currentStock >= ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                $updateStmt->bind_param("iiii", $requiredQty, $ingredientID, $branchID, $requiredQty);
                $updateStmt->execute();

                if ($updateStmt->affected_rows === 0) {
                    throw new Exception("Insufficient stock or mismatch for ingredient ID: $requiredQty, $ingredientID, $branchID, $requiredQty");
                }
            }
        }

        // Commit transaction
        $mysqli->commit();

        // Calculate total price
        $totalPrice = array_reduce($orderItems, function ($carry, $item) {
            return $carry + ($item['price'] * 1);
        }, 0);

        // Return receipt details
        $response = [
            'status' => 'success',
            'receiptID' => $receiptID,
            'salesDate' => $salesDate,
            'customerName' => $customerName,
            'orderItems' => $orderItems,
            'totalPrice' => $totalPrice
        ];
        echo json_encode($response);

    } catch (Exception $e) {
        // Rollback transaction in case of error
        $mysqli->rollback();
        error_log('Error: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>