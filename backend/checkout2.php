<?php
session_start();
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input.']);
        exit();
    }

    $branchID = $_SESSION['branch_id'];
    $cashierID = $_SESSION['user_id'];
    $cashierName = $_SESSION['name'];
    $customerName = $data['customerName'] ?? 'Walk-in';
    $orderItems = $data['orderItems'] ?? [];

    if (empty($orderItems)) {
        echo json_encode(['status' => 'error', 'message' => 'Order items cannot be empty.']);
        exit();
    }

    $transactionNumber = 'TXN' . time() . rand(100, 999); // unique transaction number
    $invoiceNumber = 'INV' . date('YmdHis'); // invoice with datetime
    $receiptID = 'REC' . time(); 
    $salesDate = date('Y-m-d H:i:s');
    $mysqli->begin_transaction();

    try {
        foreach ($orderItems as $item) {
            $productID = $item['productID'];
            $productName = $item['product'];
            $size = $item['size'];
            $price = $item['price'];
            $initialPrice = $item['initial_price'];
            $quantity = $item['quantity'];
            $totalPrice = $price * $quantity;
        
            // INSERT???? YES OO INSERT
            $stmt = $mysqli->prepare("INSERT INTO sales (branchID, receiptID, productName, price, initial_price, quantity, totalPrice, sales_date, customerName, cashierID) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issddiissi", $branchID, $invoiceNumber, $productName, $price, $initialPrice, $quantity, $totalPrice, $salesDate, $customerName, $cashierID);
            $stmt->execute();
        
            // KUKUNIN AND MAGBABAWAS NA NG INGREDIENTS TO
            $ingredientQuery = "SELECT ingredientsID, quantityRequired FROM products_ingredient WHERE productID = ?";
            $ingredientStmt = $mysqli->prepare($ingredientQuery);
            $ingredientStmt->bind_param("i", $productID);
            $ingredientStmt->execute();
            $ingredientResult = $ingredientStmt->get_result();
        
            while ($ingredient = $ingredientResult->fetch_assoc()) {
                $ingredientID = $ingredient['ingredientsID'];
                $requiredQty = $ingredient['quantityRequired'] * $quantity; 
        
                // Update stock? OO UUPDATE TAYO STOCK PAR
                $updateQuery = "UPDATE ingredients 
                                SET currentStock = currentStock - ? 
                                WHERE ingredientsID = ? AND branchesID = ? AND currentStock >= ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                $updateStmt->bind_param("iiii", $requiredQty, $ingredientID, $branchID, $requiredQty);
                $updateStmt->execute();
        
                if ($updateStmt->affected_rows === 0) {
                    throw new Exception("Insufficient stock for ingredient ID: $ingredientID");
                }
            }
        }
        $stmtTrans = $mysqli->prepare("INSERT INTO transactions 
                        (branchID, cashierID, receiptID, transactionNumber, invoiceNumber, customerName, totalAmount, salesDate, orderItems) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtTrans->bind_param("iissssdss", 
                        $branchID, 
                        $cashierID, 
                        $receiptID, 
                        $transactionNumber, 
                        $invoiceNumber, 
                        $customerName, 
                        $totalAmount, 
                        $salesDate,
                        $orderItemsJson
                    );
                    $stmtTrans->execute();
        $mysqli->commit();
        echo json_encode([
                                'status' => 'success',
                                'receiptID' => $receiptID,
                                'transactionNumber' => $transactionNumber,
                                'invoiceNumber' => $invoiceNumber,
                                'orderItems' => $orderItems,
                                'salesDate' => $salesDate]);
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>