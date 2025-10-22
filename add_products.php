<?php
ob_start();
$mysqli = include('database.php');

$productId = isset($_GET['id']) ? intval($_GET['id']) : null;
$productData = null;
$ingredientRows = '';

if ($productId) {
    // Fetch product details
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $productData = $stmt->get_result()->fetch_assoc();

    // Fetch product ingredients
    $ingredientQuery = "
        SELECT ingredientsID, quantityRequired
        FROM products_ingredient
        WHERE productID = ?
    ";
    $ingredientStmt = $mysqli->prepare($ingredientQuery);
    $ingredientStmt->bind_param('i', $productId);
    $ingredientStmt->execute();
    $ingredientResult = $ingredientStmt->get_result();

    while ($ingredientRow = $ingredientResult->fetch_assoc()) {
        $ingredientRows .= renderIngredientRow($mysqli, $ingredientRow['ingredientsID'], $ingredientRow['quantityRequired']);
    }

    $ingredientStmt->close();
    $stmt->close();
}

function renderIngredientRow($mysqli, $inventoryId = null, $quantityRequired = null) {
    $options = getIngredientOptions($mysqli, $inventoryId);
    return "
        <div class='ingredient-row'>
            <div class='form-group'>
                <label for='inventory_id'>Ingredient</label>
                <select class='form-control' name='inventory_id[]' required>
                    {$options}
                </select>
            </div>
            <div class='form-group'>
                <label for='quantity_required'>Quantity Required</label>
                <input type='number' step='0.01' class='form-control' name='quantity_required[]' value='{$quantityRequired}' required>
                <button type='button' class='btn btn-danger remove-ingredient'>Remove Ingredient</button>
            </div>
        </div>
    ";
}

function getIngredientOptions($mysqli, $selectedId = null) {
    $query = "SELECT id, name, unit FROM ingredientsHeader";
    $result = $mysqli->query($query);
    $options = '';
    while ($row = $result->fetch_assoc()) {
        $selected = ($selectedId == $row['id']) ? 'selected' : '';
        $options .= "<option value='{$row['id']}' {$selected}>{$row['name']} ({$row['unit']}) </option>";
    }
    return $options;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?= $productId ? 'Edit Product' : 'Add Product' ?></title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
          rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include "backend/nav.php" ?>

        <!-- Begin Page Content -->
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800"><?= $productId ? 'Edit Product' : 'Add Product' ?></h1>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= $productId ? 'Edit Product' : 'Add Product' ?></h6>
                        </div>
                        <div class="card-body">
                            <form action="backend/add_products_method.php" method="post" enctype="multipart/form-data">
                            <?php if (isset($productData['id'])): ?>
                                <input type="hidden" name="product_id" value="<?= $productData['id']; $productData['size'] = $productData['size'] ?? 'Medium'; // Default to Medium if not set?>">

                            <?php endif; ?>
                            
                            <input type="hidden" name="is_active" value="1" <?= isset($productData['is_active']) && $productData['is_active'] == 1 ? 'checked' : '' ?>> 
                                <!-- Product Info -->
                                <div class="form-group">
                                    <label for="product_name">Product Name</label>
                                    <input type="text" class="form-control" name="product_name" value="<?= $productData['name'] ?? '' ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="product_image">Product Image</label>
                                    
                                    <!-- Display current image if it exists -->
                                    <?php if (!empty($productData['image'])): ?>
                                        <div>
                                            <img src="<?= htmlspecialchars($productData['image']) ?>" alt="Product Image" style="max-width: 150px; max-height: 150px; margin-bottom: 10px;">
                                            <p>Current Image: <?= htmlspecialchars($productData['image']) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <!-- File input for uploading a new image -->
                                    <input 
                                        type="file" 
                                        class="form-control" 
                                        name="image"
                                        accept=".jpg, .png" 
                                        <?= empty($productData['id']) ? 'required' : '' ?>
                                    >
                                </div>
                                <div class="form-group">
                                    <label for="price">Product Size (! Large Size will automatically be added once the product is created)</label>
                                    <select name="size" class="form-control" id="size" required <?= !isset($productData['size']) ? 'disabled' : '' ?>>
                                        <option value="Medium" <?= (!isset($productData['size']) || $productData['size'] === 'Medium') ? 'selected' : '' ?>>Medium</option>
                                        <option value="Large" <?= (isset($productData['size']) && $productData['size'] === 'Large') ? 'selected' : '' ?>>Large</option>
                                    </select>

                                    <?php if (!isset($productData['size'])): ?>
                                        <!-- Hidden input to send 'Medium' value when the select is disabled -->
                                        <input type="hidden" name="size" value="Medium">
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="price">Product Price</label>
                                    <input type="number" class="form-control" name="price" value="<?= $productData['price'] ?? '' ?>"  onkeydown="javascript: return event.keyCode == 69 ? false : true" required>
                                </div>
                                <div class="form-group">
                                    <label for="price">Initial Price</label>
                                    <input type="number" class="form-control" name="initial_price" value="<?= $productData['initial_price'] ?? '' ?>" onkeydown="javascript: return event.keyCode == 69 ? false : true" required>
                                </div>

                                <!-- Ingredients -->
                                <div id="ingredients-section">
                                    <?= $ingredientRows ?: renderIngredientRow($mysqli); ?>
                                </div>
                                <button type="button" class="btn btn-secondary" id="add-ingredient">Add Another Ingredient</button>
                                <button type="submit" class="btn btn-primary"><?= $productId ? 'Update Product' : 'Save Product' ?></button>
                                <?php if ($productId): ?>
                                    <input type="hidden" name="product_id" value="<?= $productId ?>"  onkeydown="javascript: return event.keyCode == 69 ? false : true">
                                <?php endif; ob_end_flush();?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <script>
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('add-ingredient').addEventListener('click', function () {
        const section = document.getElementById('ingredients-section');
        const options = `<?= getIngredientOptions($mysqli); ?>`; 
        const ingredientRow = `
            <div class="ingredient-row form-group">
                <label for="inventory_id">Ingredient</label>
                <select class="form-control" name="inventory_id[]" required>
                    ${options}
                </select>
                <label for="quantity_required">Quantity Required</label>
                <input type="number" step="0.01" class="form-control" name="quantity_required[]" required>
                <button type="button" class="btn btn-danger remove-ingredient">Remove Ingredient</button>
            </div>
        `;
        section.insertAdjacentHTML('beforeend', ingredientRow);
    });

    document.getElementById('ingredients-section').addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-ingredient')) {
            const row = e.target.closest('.ingredient-row');
            if (row) {
                row.remove(); 
            }
        }
    });
});
    </script>

</body>
</html>