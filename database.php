<?php

$host = "localhost";
$dbname = "milkteashop2";
$username = "root";
$password = "DREAMTEAM";

$mysqli = new mysqli(hostname: $host,
                     username: $username,
                     password: $password,
                     database: $dbname);
                     
if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;