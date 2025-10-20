<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mysqli = require __DIR__ . "/database.php";

    $sql = sprintf(
        "SELECT users.* FROM users WHERE email = '%s'",
        $mysqli->real_escape_string($_POST["email"])
    );

    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();

    if ($user) {
        if ($user["is_archived"] == 1) {
            header("Location: login.php?failed=archived");
            exit;
        }

        if (password_verify($_POST["password"], $user["password_hash"])) {
            session_regenerate_id();

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["name"] = $user["fname"] . " " . $user["lname"];
            $_SESSION["branch_id"] = $user["branch_assignment"];
            $_SESSION["password_changed"] = $user["password_changed"];

            if ($user["password_changed"] == 0) {
                header("Location: change_password.php");
                exit;
            }

            if ($_SESSION["role"] == "cashier") {
                header("Location: branch_index.php");
            } elseif ($_SESSION["role"] == "encoder") {
                header("Location: branch_index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            header("Location: login.php?failed=true");
            exit;
        }
    } else {
        header("Location: login.php?failed=true");
        exit;
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

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">

</head>

<body class="LoveTeaBG">

<div class="container marginTop">
    <div class="row">
        <div class="col-lg-6">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                </div>
                                <form class="user" method="POST">
                                        <?php if (isset($_GET['failed']) && $_GET['failed'] == 'archived'): ?>
                                            <div class="alert alert-warning mt-3">
                                            Your account has been archived. Please contact administrator.
                                        </div>
                                        <?php elseif (isset($_GET['failed'])): ?>
                                            <div class="alert alert-danger mt-3">
                                            Invalid email or password. Please try again.
                                        </div>
                                        <?php endif; ?>
                                    <div class="form-group">
                                        <input type="email" class="form-control form-control-user"
                                            id="exampleInputEmail" aria-describedby="emailHelp"
                                            placeholder="Enter Email Address..." name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user"
                                            id="exampleInputPassword" placeholder="Password" name="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-user btn-block">
                                        Login
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>