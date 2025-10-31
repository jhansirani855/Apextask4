<?php
require 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch current user's role (PHP 5.2 compatible)
$sql_user_role = "SELECT role FROM users WHERE username=?";
$stmt_role = $conn->prepare($sql_user_role);
$stmt_role->bind_param("s", $_SESSION['username']);
$stmt_role->execute();
$stmt_role->bind_result($user_role);
$stmt_role->fetch();
$stmt_role->close();

// Fetch all users
$sql_all_users = "SELECT username, email, role FROM users ORDER BY role DESC, username ASC";
$result_all_users = $conn->query($sql_all_users);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #8D58BF; 
            color: #333;
        }
        header {
            background: #ffffff;
            padding: 20px 10%;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        h1, h2 {
            margin: 0;
            font-size: 32px;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 1400px;
            margin: 30px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #8D58BF;
            color: #fff;
            font-size: 18px;
        }
        td {
            background: #f9f9f9;
            font-size: 16px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: 1px solid #8D58BF;
            background-color: #fff;
            border-radius: 8px;
            font-size: 16px; 
            text-align: center;
            text-decoration: none;
            color: #8D58BF;
            margin-right: 10px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .btn:hover {
            background-color: #8D58BF;
            color: #fff;
        }

        table th:nth-child(1), table td:nth-child(1) {
            width: 30%;
        }
        table th:nth-child(2), table td:nth-child(2) {
            width: 50%;
        }
        table th:nth-child(3), table td:nth-child(3) {
            width: 20%;
        }

        tr:nth-child(even) td {
            background: #f2f2f2;
        }
    </style>
</head>
<body>
    <header>
        <h1>Manage Users</h1>
    </header>
    <div class="container">
        <h2>Admins</h2>
        <table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php
            $result_all_users->data_seek(0); 
            while ($row = $result_all_users->fetch_assoc()) {
                if ($row['role'] === 'admin') { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <?php if ($user_role === 'admin') { ?>
                                <a href="update.php?username=<?php echo urlencode($row['username']); ?>" class="btn">Edit</a>
                                <a href="delete.php?username=<?php echo urlencode($row['username']); ?>" class="btn">Delete</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php }
            }
            ?>
        </table>

        <h2>Users</h2>
        <table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php
            $result_all_users->data_seek(0); 
            while ($row = $result_all_users->fetch_assoc()) {
                if ($row['role'] !== 'admin') { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <?php if ($user_role === 'admin' || $row['username'] === $_SESSION['username']) { ?>
                                <a href="update.php?username=<?php echo urlencode($row['username']); ?>" class="btn">Edit</a>
                                <?php if ($user_role === 'admin') { ?>
                                    <a href="delete.php?username=<?php echo urlencode($row['username']); ?>" class="btn">Delete</a>
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php }
            }
            ?>
        </table>

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>
