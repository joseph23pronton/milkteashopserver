<?php
$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['id'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $branch_id = $_POST['branch_id'];
    $role = trim($_POST['role']);
    $password = $_POST['password'];
    $password_change = 0;

    // Validate role
    $allowed_roles = ['admin', 'cashier', 'encoder'];
    if (!in_array($role, $allowed_roles)) {
        header("Location: /employee.php?failed=Invalid role");
        exit();
    }

    // Prepare the SQL update statement
    if (!empty($password)) {
        // If password is provided, include it in the update
        $sql = "UPDATE users SET fname = ?, lname = ?, email = ?, branch_assignment = ?, role = ?, password_hash = ?, password_changed = ? WHERE id = ?";
    } else {
        // If no password is provided
        $sql = "UPDATE users SET fname = ?, lname = ?, email = ?, branch_assignment = ?, role = ? WHERE id = ?";
    }

    // Prepare the statement
    $stmt = $mysqli->prepare($sql);

    // Check if the preparation was successful
    if ($stmt === false) {
        die("Error preparing statement: " . $mysqli->error);
    }

    // Bind parameters based on whether a new password is provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param(
            "ssssssii", 
            $fname, $lname, $email, $branch_id, $role, $hashed_password, $password_change, $employee_id
        );
    } else {
        $stmt->bind_param(
            "sssssi", 
            $fname, $lname, $email, $branch_id, $role, $employee_id
        );
    }

    // Execute the statement and check for errors
    if ($stmt->execute()) {
        header("Location: /employee.php?success=true");
        exit(); // Ensure that no further code is executed after the redirect
    } else {
        header("Location: /employee.php?failed=" . $stmt->error);
    }

    $stmt->close();
}

$mysqli->close();
?>
