<?php
// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "registration";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turf_name = $_POST['turf_name'];
    $location = $_POST['location'];
    $hourly_rate = $_POST['hourly_rate'];
    $owner_id = $_POST['owner_id'];

    $sql_insert = "INSERT INTO turf (name, location, hourly_rate, owner_id) 
                   VALUES ('$turf_name', '$location', '$hourly_rate', '$owner_id')";
    
    if ($conn->query($sql_insert) === TRUE) {
        echo "<script>alert('Turf added successfully!'); window.location.href='turfs.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

$sql = "SELECT turf.turf_id, turf.name AS turf_name, turf.location, turf.hourly_rate, 
               users.name AS owner_name, users.email AS owner_email, users.phone AS owner_phone
        FROM turf
        INNER JOIN users ON turf.owner_id = users.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turf Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color:rgb(54, 122, 25);
            --secondary-color:rgb(51, 105, 24);
            --background-color: #f5f6fa;
            --text-color: #2c3e50;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: white;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            border-radius: 10px;
            position: relative;
        }

        .back-button {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background-color: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: var(--secondary-color);
        }

        h2 {
            text-align: center;
            color: var(--text-color);
            margin: 0;
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto 30px;
            padding: 25px;
            background: white;
            box-shadow: var(--shadow);
            border-radius: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        button:hover {
            background: var(--secondary-color);
        }

        .table-container {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .empty-message {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .form-container {
                padding: 15px;
            }

            th, td {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="javascript:history.back()" class="back-button">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2>Turf Management</h2>
        </div>

        <div class="form-container">
            <h3>Add New Turf</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Turf Name:</label>
                    <input type="text" name="turf_name" required placeholder="Enter turf name">
                </div>

                <div class="form-group">
                    <label>Location:</label>
                    <input type="text" name="location" required placeholder="Enter location">
                </div>

                <div class="form-group">
                    <label>Hourly Rate:</label>
                    <input type="number" name="hourly_rate" step="0.01" required placeholder="Enter hourly rate">
                </div>

                <div class="form-group">
                    <label>Owner:</label>
                    <select name="owner_id" required>
                        <option value="">Select Owner</option>
                        <?php
                        $owner_query = "SELECT id, name FROM users WHERE role='owner'";
                        $owners = $conn->query($owner_query);
                        while ($owner = $owners->fetch_assoc()) {
                            echo "<option value='" . $owner['id'] . "'>" . $owner['name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit">Add Turf</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <tr>
                    <th>Turf ID</th>
                    <th>Turf Name</th>
                    <th>Location</th>
                    <th>Hourly Rate</th>
                    <th>Owner Name</th>
                    <th>Owner Email</th>
                    <th>Owner Phone</th>
                </tr>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["turf_id"] . "</td>
                                <td>" . $row["turf_name"] . "</td>
                                <td>" . $row["location"] . "</td>
                                <td>₹" . $row["hourly_rate"] . "</td>
                                <td>" . $row["owner_name"] . "</td>
                                <td>" . $row["owner_email"] . "</td>
                                <td>" . $row["owner_phone"] . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='empty-message'>No turfs found!</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>