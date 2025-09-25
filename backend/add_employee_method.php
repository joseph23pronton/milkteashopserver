<?php

if (empty($_POST["fname"])) {
    die("First Name is required");
}

if (empty($_POST["lname"])) {
    die("Last Name is required");
}

if ( ! filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required");
}

if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

$employeeID = rand(10000, 20000);

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

$sql = "INSERT INTO users (id, fname, lname, email, password_hash, role, branch_assignment)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
        
$stmt = $mysqli->stmt_init();

if ( ! $stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

$stmt->bind_param("isssssi",
                  $employeeID,
                  $_POST["fname"],
                  $_POST["lname"],
                  $_POST["email"],
                  $password_hash,
                  $_POST["role"],
                  $_POST['branch_id']);
                  
if ($stmt->execute()) {

    header("Location: /employee.php");
    exit;
    
} else {
    
    if ($mysqli->errno === 1062) {
        header("Location: /employee.php?failed=true");
        
    } else {
        header("Location: /employee.php?failed=true");
    }
}