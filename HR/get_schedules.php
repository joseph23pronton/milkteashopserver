<?php
require_once 'auth_check.php';
$mysqli = require __DIR__ . "/../database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $date = $_POST['date'];
    
    $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
    $day_of_week = strtolower(date('l', strtotime($date)));
    
    $query = $mysqli->prepare("SELECT {$day_of_week}_shift as shift FROM schedules WHERE employee_id = ? AND week_start = ?");
    $query->bind_param("is", $employee_id, $week_start);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows > 0) {
        $schedule = $result->fetch_assoc();
        echo json_encode(['schedule' => $schedule['shift']]);
    } else {
        echo json_encode(['schedule' => null]);
    }
}
?>