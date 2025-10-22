<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../logout.php");
    exit;
}

if ($_SESSION['role'] != 'hr') {
    header("Location: ../logout.php");
    exit;
}
?>