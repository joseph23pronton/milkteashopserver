<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Include database connection
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

// Function to check stock for a product
function checkStock($mysqli, $productID, $branchID, $orderQuantity) {
    $results = [];

    // Query to check stock for the product
    $sql = "SELECT 
                i.ingredientsName AS ingredientName,
                s.quantityRequired AS currentStock,
                (s.quantityRequired - ?) AS remainingStock
            FROM products_ingredient s
            JOIN ingredients i ON s.ingredientsID = i.ingredientsID
            WHERE s.productID = ? AND i.branchesID = ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iii", $orderQuantity, $productID, $branchID);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $status = $row['remainingStock'] < 0 ? 'Insufficient' : 'Sufficient';
        $results[] = [
            'ingredientName' => $row['ingredientName'],
            'currentStock' => $row['currentStock'],
            'requiredQuantity' => $orderQuantity,
            'remainingStock' => $row['remainingStock'],
            'status' => $status
        ];
    }

    $stmt->close();
    return $results;
}

// Fetch all products from the database
$sql = "SELECT * FROM products";
$result = $mysqli->query($sql);

// Initialize arrays for availability status
$availabilityStatus = [];
$products = [];
// Loop through all products
if ($result->num_rows > 0) {
    while ($product = $result->fetch_assoc()) {
        $products[] = $product;

        // Example branchID and orderQuantity (replace as needed)
        $branchID = $_SESSION['branch_id']; // Default branch ID
        $orderQuantity = 1; // Default order quantity

        // Check stock availability
        $stockStatus = checkStock($mysqli, $product['id'], $branchID, $orderQuantity);
        foreach ($stockStatus as $status) {
            if ($status['status'] === 'Insufficient') {
                $availabilityStatus[] = [
                    'productID' => $product['id'],
                    'ingredientName' => $status['ingredientName'],
                    'requiredQuantity' => $status['requiredQuantity'],
                    'currentStock' => $status['currentStock'],
                    'remainingStock' => $status['remainingStock']
                ];
            }
        }
    }
}

// Prepare the response
$response = [];

if (empty($availabilityStatus)) {
    $response = [
        'status' => 'success',
        'message' => 'All products are available',
        'products' => $products
    ];
} else {
    $response = [
        'status' => 'error',
        'message' => 'Some products have insufficient stock',
        'insufficientStock' => $availabilityStatus
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$mysqli->close();
?>