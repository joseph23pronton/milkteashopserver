<?php
require_once __DIR__ . '/../finance/db_connection.php';

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "UPDATE expenses SET is_archived = 1, archived_at = NOW() WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        header("Location: ../finance/expenses.php?success=archived");
    } else {
        header("Location: ../finance/expenses.php?error=Failed to archive expense");
    }
    exit();
} else {
    header("Location: ../finance/expenses.php?error=Invalid request");
    exit();
}
?>