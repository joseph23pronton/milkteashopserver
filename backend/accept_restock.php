<?php
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

if (isset($_POST['restock_id'])) {
    $restock_id = (int) $_POST['restock_id'];

    $update_sql = "
        UPDATE restockOrder 
        SET is_accepted = 1 
        WHERE id = ?
    ";

    $update_stmt = $mysqli->prepare($update_sql);

    if (!$update_stmt) {
        die("SQL Error: " . $mysqli->error);
    }

    // Bind parameter and execute
    $update_stmt->bind_param('i', $restock_id);
    $update_stmt->execute();

    // Redirect back to the page with a success message
    header("Location: /index.php?success=true");
    exit;

} else {
    // If restock_id is not set, redirect with an error
    header("Location: /index.php?error=true");
    exit;
}
?>