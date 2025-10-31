<?php
session_start();
require 'config.php'; // ✅ DB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Get and sanitize user input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please fill in both fields.";
    } else {
        // ✅ Prepared statement to prevent SQL Injection
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // ✅ Verify hashed password
            if (password_verify($password, $user['password'])) {
                // ✅ Start session
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // ✅ Regenerate session ID for security
                session_regenerate_id(true);

                // ✅ Redirect all roles to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Thought App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f3f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            width: 380px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #6A0DAD;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            font-size: 16px;
        }
        input:focus {
            border-color: #6A0DAD;
        }
        button {
            width: 100%;
            background-color: #6A0DAD;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #5a0aa8;
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
        .link {
            text-align: center;
            margin-top: 15px;
        }
        .link a {
            color: #6A0DAD;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <div class="link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
