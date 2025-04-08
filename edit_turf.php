<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "registration";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$turf_id = isset($_GET['id']) ? $_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Fetch turf details
$turf_query = "SELECT * FROM turf WHERE turf_id = ? AND owner_id = ?";
$stmt = $conn->prepare($turf_query);
$stmt->bind_param("ii", $turf_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$turf = $result->fetch_assoc();

if (!$turf) {
    header("Location: owner.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $morning_rate = floatval($_POST['morning_rate']);
    $afternoon_rate = floatval($_POST['afternoon_rate']);
    $evening_rate = floatval($_POST['evening_rate']);

    if (empty($name) || empty($location) || 
        $morning_rate <= 0 || $afternoon_rate <= 0 || $evening_rate <= 0) {
        $error_message = "Please fill all fields with valid values.";
    } else {
        $update_query = "UPDATE turf SET name = ?, location = ?, 
                        morning_rate = ?, afternoon_rate = ?, evening_rate = ? 
                        WHERE turf_id = ? AND owner_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssdddii", $name, $location, $morning_rate, 
                         $afternoon_rate, $evening_rate, $turf_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            header("Location: owner.php");
            exit();
        } else {
            $error_message = "Error updating turf details.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Turf - SOCCER-11</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e9 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            animation: gradientBG 15s ease infinite;
            background-size: 400% 400%;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .edit-form-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 600px;
            transform: translateY(20px);
            animation: slideUp 0.6s ease forwards;
            opacity: 0;
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #2E7D32;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .form-header h2:after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            bottom: -5px;
            left: 0;
            background: #4CAF50;
            animation: lineWidth 0.6s ease forwards 0.6s;
        }

        @keyframes lineWidth {
            to { width: 100%; }
        }

        .form-group {
            margin-bottom: 25px;
            opacity: 0;
            transform: translateX(-20px);
            animation: slideIn 0.5s ease forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.3s; }
        .form-group:nth-child(2) { animation-delay: 0.4s; }
        .form-group:nth-child(3) { animation-delay: 0.5s; }
        .form-group:nth-child(4) { animation-delay: 0.6s; }
        .form-group:nth-child(5) { animation-delay: 0.7s; }
        .form-group:nth-child(6) { animation-delay: 0.8s; }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #E0E0E0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            background: white;
        }

        .form-group:hover label {
            color: #4CAF50;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards 0.7s;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn:after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .btn:hover:after {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
        }

        .btn-secondary {
            background: #E0E0E0;
            color: #333;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideDown 0.5s ease forwards;
            transform: translateY(-20px);
            opacity: 0;
        }

        @keyframes slideDown {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background-color: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #A5D6A7;
        }

        .alert-error {
            background-color: #FFEBEE;
            color: #C62828;
            border: 1px solid #FFCDD2;
        }
    </style>
</head>
<body>
    <div class="edit-form-container">
        <div class="form-header">
            <h2>Edit Turf Details</h2>
            <p>Update your turf information below</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Turf Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($turf['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($turf['location']); ?>" required>
            </div>

            <div class="form-group">
                <label for="morning_rate">Morning Rate (6:01 AM - 10:00 AM) (₹)</label>
                <input type="number" id="morning_rate" name="morning_rate" 
                       value="<?php echo htmlspecialchars($turf['morning_rate']); ?>" 
                       required min="0" step="0.01">
            </div>

            <div class="form-group">
                <label for="afternoon_rate">Afternoon Rate (10:01 AM - 4:00 PM) (₹)</label>
                <input type="number" id="afternoon_rate" name="afternoon_rate" 
                       value="<?php echo htmlspecialchars($turf['afternoon_rate']); ?>" 
                       required min="0" step="0.01">
            </div>

            <div class="form-group">
                <label for="evening_rate">Evening Rate (4:01 PM - 11:00 PM) (₹)</label>
                <input type="number" id="evening_rate" name="evening_rate" 
                       value="<?php echo htmlspecialchars($turf['evening_rate']); ?>" 
                       required min="0" step="0.01">
            </div>

            <div class="button-group">
                <a href="owner.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Turf</button>
            </div>
        </form>
    </div>
</body>
</html> 