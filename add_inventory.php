<?php
// Check if the 'id' is provided in the URL (for editing)
if (isset($_GET['id'])) {
    // Fetch inventory item data by ID
    $inventoryId = $_GET['id'];

    // Connect to the database
    $mysqli = include('database.php');

    // Query to get the inventory data
    $sql = "SELECT * FROM ingredientsHeader WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $inventoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a record was found
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $invID = $row['id'];
        $name = $row['name'];
        $ingredientsLimit = $row['ingredients_limit'];
        $ingredientsUnit = $row['unit'];
    } else {
        // Handle the case if no item is found (optional)
        $name = '';
        $ingredientsLimit = '';
        $ingredientsUnit = '';
    }

    // Close the database connection
    $mysqli->close();
} else {
    // If no ID is provided, leave the fields empty (for adding a new item)
    $name = '';
    $ingredientsLimit = '';
    $ingredientsUnit = '';
}
?>

<form action="backend/add_inventory_method.php" method="post">
    <!-- Hidden field for Item ID (used for editing) -->
    <input type="hidden" id="item_id" name="id" value="<?= $invID ?? '' ?>">

    <div class="form-group">
        <label for="item_name">Item Name</label>
        <input type="text" class="form-control" id="item_name" name="name" value="<?= htmlspecialchars($name) ?>" required>
    </div>
    <div class="form-group">
        <label for="item_limit">Item Limit</label>
        <input type="number" class="form-control" id="item_limit" name="ingredients_limit" value="<?= (int)$ingredientsLimit ?>" required>
    </div>
    <div class="form-group">
        <label for="item_unit">Item Unit</label>
        <select id="item_unit" name="ingredients_unit" class="form-control">
            <option value="g">Grams</option>
            <option value="pcs">Pieces</option>
            <option value="ml">MiliLiters</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>