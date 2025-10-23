<?php

$host = "192.168.100.66";
$dbname = "milkteashop2";
$username = "ganbaruby23";
$password = "mp3music";


$mysqli = new mysqli(hostname: $host,
                     username: $username,
                     password: $password,
                     database: $dbname);
                     
if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;
