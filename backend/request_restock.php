<?php
session_start();
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Get user details from session
$userID = $_SESSION['user_id'];
$userName = $_SESSION['name']; // Assuming the user's name is stored in session

// Get branchID and ingredientsID from GET parameters
$branchID = $_SESSION['branch_id'] ?? null;
$ingredientsID = $_POST['ingredientsID'] ?? null;
$restockAmount = $_POST['restockAmount'];
$ingredientsName = $_POST['ingredientsName'];

if (!isset($_POST['restockAmount'])) {
    echo "restockAmount is missing!";
}
if (!isset($_POST['ingredientsName'])) {
    echo "ingredientsName is missing!";
}

if (!$branchID || !$ingredientsID) {
    echo json_encode(['status' => 'error', 'message' => 'Branch ID and Ingredients ID are required.']);
    exit();
}


    // Read the restock amount from the request

    if (!$restockAmount || $restockAmount <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid restock amount.']);
        exit();
    }

    $sql = "INSERT INTO restockOrder (name, branchID, ingredientsID, ingredientsName, restock_amount, is_accepted, is_confirmed, requested_by)
        VALUES (?, ?, ?, ?, ?, 0, 0, ?)";

$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("siisis", $userName, $branchID, $ingredientsID, $ingredientsName, $restockAmount, $userID);

    if ($stmt->execute()) {
        header("Location: /restock_inventory.php?id=$branchID&success=true");
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
}

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>