<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

    // Start transaction
    $conn->begin_transaction();

    try {
        // Sanitize inputs
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;  // If id is provided, it's an update
        $name = $conn->real_escape_string($_POST['name']);
        $ingredients_limit = (int)$_POST['ingredients_limit']; // Cast to integer for safety
        $ingredients_unit = $conn->real_escape_string($_POST['ingredients_unit']);
        $price_per_unit = $conn->real_escape_string($_POST['price_per_unit']);

        if ($id) {
            // Update existing inventory
            $stmt = $conn->prepare("UPDATE ingredientsHeader SET name = ?, ingredients_limit = ?, unit = ?, price_per_unit = ? WHERE id = ?");
            $stmt->bind_param("sisdi", $name, $ingredients_limit, $ingredients_unit, $price_per_unit, $id);
        } else {
            // Insert new inventory
            $stmt = $conn->prepare("INSERT INTO ingredientsHeader (name, ingredients_limit, unit, price_per_unit) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sisd", $name, $ingredients_limit, $ingredients_unit, $price_per_unit);
        }

        // Execute the query
        $stmt->execute();

        // Check for errors in execution
        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows affected. Check your input or query.");
        }

        // Commit transaction
        $conn->commit();
        header("Location: /main_inventory.php?success=true");
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Failed to save inventory: " . $e->getMessage();
    }

    $conn->close();
}
?>