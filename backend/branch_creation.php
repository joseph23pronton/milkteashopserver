<?php
function createBranch($branch_name, $branch_city) {
    // Sanitize and format branch names for any table names
    $branch_name_cleaned = strtolower(preg_replace('/\s+/', '_', $branch_name));
    $branch_city_cleaned = strtolower(preg_replace('/\s+/', '_', $branch_city));
    $branch_id = rand(0000,1000);
    // Connect to database
    $mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

    // Insert branch data into the `branches` table
    $sql_branch_insertion = "INSERT INTO branches (id, name, city) 
                             VALUES (?, ?, ?)";

    // Prepare and bind the insert statement for the `branches` table
    $stmt = $mysqli->prepare($sql_branch_insertion);
    $stmt->bind_param("iss", $branch_id, $branch_name, $branch_city);

    // Execute and check if branch creation is successful
    if ($stmt->execute() === TRUE) {
       
        // Get the branch ID of the newly created branch
        $branch_id = $mysqli->insert_id;
        header("Location: branches.php");
    } else {
        echo "Error inserting branch data: " . $stmt->error . "<br>";
        $stmt->close();
        $mysqli->close();
        return;
    }

    // Close the prepared statement and MySQL connection
    $stmt->close();
    $mysqli->close();

    // Redirect to branches page after successful creation
    header("Location: /branches.php");
}

// Check if data is posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_name = $_POST['branch_name'] ?? '';
    $branch_city = $_POST['branch_city'] ?? '';

    if ($branch_name && $branch_city) {
        createBranch($branch_name, $branch_city);
    } else {
        echo "Please provide both branch name and branch city.";
    }
}
?>
