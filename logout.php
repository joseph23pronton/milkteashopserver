<?php
session_start();
date_default_timezone_set('Asia/Manila');

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $mysqli = require __DIR__ . "/database.php";
    
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    if ($role == 'cashier' || $role == 'encoder' || $role == 'hr') {
        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');
        
        $update_time_out = $mysqli->prepare("UPDATE attendance SET time_out = ? WHERE employee_id = ? AND attendance_date = ?");
        $update_time_out->bind_param("sis", $current_time, $user_id, $current_date);
        $update_time_out->execute();
    }
}

$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$login_url = $base_path . '/login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }
        
        .logout-container {
            text-align: center;
            color: white;
        }
        
        .spinner {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border: 8px solid rgba(255, 255, 255, 0.3);
            border-top: 8px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            animation: fadeIn 0.5s ease-in;
        }
        
        p {
            font-size: 16px;
            opacity: 0.9;
            animation: fadeIn 0.8s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .icon {
            font-size: 50px;
            margin-bottom: 20px;
            animation: bounce 1s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="spinner"></div>
        <h2>Logging Out</h2>
        <p>Please wait...</p>
    </div>
    
    <script>
        setTimeout(function() {
            window.location.href = '<?php echo $login_url; ?>';
        }, 1500);
    </script>
</body>
</html>
<?php
session_unset();
session_destroy();
?>