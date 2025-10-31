<?php
require 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $id = $_POST['id'];
    $content = $_POST['content'];

    $sql = "UPDATE thoughts SET content=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $content, $id);

    if ($stmt->execute()) {
        header("Location: thought.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $id = $_GET['id'];

    $sql = "SELECT * FROM thoughts WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $thought = $result->fetch_assoc();

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Thought</title>
</head>
<body>
    <form action="update_t.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($thought['id']); ?>">
        <label for="content">Content:</label>
        <textarea id="content" name="content" required><?php echo htmlspecialchars($thought['content']); ?></textarea>
        <button type="submit">Update Thought</button>
    </form>
    <a href="thought.php">Back to Thoughts</a>
</body>
</html>
