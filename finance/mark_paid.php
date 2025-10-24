<?php
session_start();
$conn = require $_SERVER['DOCUMENT_ROOT'] . "/database.php"; // adjust path if needed


    if (!isset($_GET['id'])) {
        echo json_encode(["status" => "error", "message" => "Missing salary ID"]);
        exit;
    }

    $salary_id = intval($_GET['id']);

    // Prepare update query
    $stmt = $conn->prepare("UPDATE payroll SET status = 'Paid' WHERE id = ?");
    $stmt->bind_param("i", $salary_id);

    if ($stmt->execute()) {
        header("Location: salary_records.php?success=1");
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update status."]);
    }

    $stmt->close();
    $conn->close();
?>
