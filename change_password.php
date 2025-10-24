<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["password_changed"] == 1) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = $_POST["password"];
    $confirm_password = $_POST["password_confirmation"];

    if ($new_password === $confirm_password) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $mysqli = require __DIR__ . "/database.php";

        $sql = "UPDATE users SET password_hash = ?, password_changed = 1 WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("si", $password_hash, $_SESSION["user_id"]);
        $stmt->execute();

        $_SESSION["password_changed"] = 1; 
        header("Location: login.php?welcome=true");
        exit;
    } else {
        $error = "Passwords do not match!";
    }
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

    <title>Login</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">

</head>

<body class="LoveTeaBG">

<div class="container marginTop">
    <div class="row">
        <!-- Left Column for the Login Form -->
        <div class="col-lg-6">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Welcome Aboard</h1>
                                    <p>Welcome! Please Change Your Password Now To Continue</p>
                                </div>
                                <form class="user" method="POST">
                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user"
                                            id="exampleInputEmail" aria-describedby="emailHelp"
                                            placeholder="Enter New Password" name="password">
                                    </div>
                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user"
                                            id="exampleInputPassword" placeholder="Repeat New Password" name="password_confirmation">
                                    </div>
                                    <button type="submit" class="btn btn-success btn-user btn-block">
                                        Change Password
                                    </button>
                                </form>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>