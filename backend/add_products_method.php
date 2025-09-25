<?php
// Start output buffering
ob_start();
print_r($_POST);
// Include the database connection
$mysqli = include($_SERVER['DOCUMENT_ROOT'] . '/database.php');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start a database transaction
    $mysqli->begin_transaction();

    try {
        // Retrieve and sanitize product data
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
        $productName = $mysqli->real_escape_string($_POST['product_name']);
        $price = floatval($_POST['price']);
        $size = $_POST['size'];
        $initial_price = floatval($_POST['initial_price']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Handle image upload
        $imagePath = null;
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/product_images/';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Validate image type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Invalid image type. Allowed types: JPEG, PNG, GIF.');
            }

            // Generate a unique file name
            $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('product_', true) . '.' . $fileExt;
            $imagePath = '/uploads/product_images/' . $newFileName;

            // Move the uploaded file
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFileName)) {
                throw new Exception('Failed to upload the image.');
            }

            // If updating, delete the old image
            if ($productId) {
                $stmt = $mysqli->prepare("SELECT image FROM products WHERE id = ?");
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . $row['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $stmt->close();
            }
        }

        // Insert or update product
        if ($productId) {
            // Update existing product
            $stmt = $mysqli->prepare("
                UPDATE products
                SET name = ?, price = ?, initial_price= ?, is_active = ?, image = COALESCE(?, image)
                WHERE id = ?
            ");
            $stmt->bind_param('sddssi', $productName, $price, $initial_price, $isActive, $imagePath, $productId);
        } else {
            // Insert new product
            $stmt = $mysqli->prepare("
                INSERT INTO products (name, size, price, initial_price, is_active, image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param('ssddds', $productName, $size, $price, $initial_price, $isActive, $imagePath);

            // Insert new product with different size
            $stmt2 = $mysqli->prepare("
                INSERT INTO products (name, size, price, initial_price, is_active, image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $size2 = 'Large';
            $initial_price2 = 0;
            $stmt2->bind_param('ssddds', $productName, $size2, $price, $initial_price2, $isActive, $imagePath);
            $stmt2->execute();
            
        }
        $stmt->execute();

        if (!$productId) {
            $productId = $stmt->insert_id;
        }
        $stmt->close();
        // Handle ingredients
        $inventoryIds = $_POST['inventory_id'];
        $quantities = $_POST['quantity_required'];

        // Delete existing ingredients if updating
        if ($productId) {
            $stmt = $mysqli->prepare("DELETE FROM products_ingredient WHERE productID = ?");
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $stmt->close();
        }

        // Insert new ingredients
        $stmt = $mysqli->prepare("
            INSERT INTO products_ingredient (productID, ingredientsID, quantityRequired)
            VALUES (?, ?, ?)
        ");
        foreach ($inventoryIds as $index => $inventoryId) {
            $quantity = floatval($quantities[$index]);
            $stmt->bind_param('iid', $productId, $inventoryId, $quantity);
            $stmt->execute();
        }
        $stmt->close();

        // Commit the transaction
        $mysqli->commit();

        // Redirect on success
        header('Location: /products.php?success=true');
        exit;
    } catch (Exception $e) {
        // Roll back the transaction on error
        $mysqli->rollback();
        echo 'Failed to save product: ' . $e->getMessage();
    }
}

// Close database connection
$mysqli->close();