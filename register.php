<?php
require 'config.php'; // ✅ centralized DB connection

$errors = [];
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $address = trim($_POST['address']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // ✅ Server-side validation
    if (empty($phone) || empty($dob) || empty($address) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "Please fill in all fields.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Invalid phone number. Must be 10 digits.";
    }

    if (count($errors) === 0) {
        // ✅ Check if email or username already exists
        $stmt_check = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt_check->bind_param("ss", $email, $username);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $errors[] = "This email or username is already registered.";
        }
        $stmt_check->close();

        if (count($errors) === 0) {
            // ✅ Hash password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // ✅ Insert new user
            $stmt = $conn->prepare("INSERT INTO users (phone, dob, address, username, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $phone, $dob, $address, $username, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $successMessage = "Registration successful!";
                $_POST = []; // clear form data
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #6a11cb, #2575fc); /* 💜 Purple background */
            color: #fff;
        }
        .container {
            width: 100%;
            max-width: 800px;
            background-color: #ffffff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 1s ease-in-out;
            color: #333;
        }
        @keyframes fadeInUp {
            from {opacity: 0; transform: translateY(30px);}
            to {opacity: 1; transform: translateY(0);}
        }
        h2 {
            text-align: center;
            color: #6a11cb;
            margin-bottom: 25px;
            font-weight: 600;
        }
        form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .form-group { display: flex; flex-direction: column; }
        .form-group input, .form-group select {
            padding: 12px;
            border: 2px solid #d1c4e9;
            border-radius: 30px;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
            background-color: #f9f8ff;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 10px rgba(106, 17, 203, 0.3);
        }
        input[type="submit"] {
            grid-column: span 2;
            padding: 12px;
            background: linear-gradient(90deg, #6a11cb, #8e2de2);
            border: none;
            border-radius: 30px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        input[type="submit"]:hover {
            background: linear-gradient(90deg, #8e2de2, #6a11cb);
        }
        a {
            text-align: center;
            color: #6a11cb;
            text-decoration: none;
            grid-column: span 2;
            font-weight: 500;
        }
        a:hover { text-decoration: underline; }
        .error, .success {
            grid-column: span 2;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }
        .error { background: #f8d7da; color: #721c24; }
        .success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form id="registerForm" action="register.php" method="post">
            <?php if ($errors): ?>
                <div class="error"><?= implode('<br>', $errors); ?></div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div class="success"><?= $successMessage; ?></div>
            <?php endif; ?>

            <div class="form-group"><input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required></div>
            <div class="form-group"><input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required></div>
            <div class="form-group"><input type="password" name="password" placeholder="Password" required></div>
            <div class="form-group"><input type="password" name="confirm_password" placeholder="Confirm Password" required></div>
            <div class="form-group"><input type="tel" name="phone" placeholder="Phone Number (10 digits)" pattern="[0-9]{10}" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required></div>
            <div class="form-group"><input type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required></div>
            <div class="form-group"><input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required></div>
            <div class="form-group">
                <select name="role" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="user" <?= (($_POST['role'] ?? '') === 'user') ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <input type="submit" value="Register">
            <a href="login.php">Already have an account? Login here.</a>
        </form>
    </div>
</body>
</html>
