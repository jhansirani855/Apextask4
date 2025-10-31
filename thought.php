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

// Get logged-in user's role
$sql_user_role = "SELECT role FROM users WHERE username=?";
$stmt_user_role = $conn->prepare($sql_user_role);
$stmt_user_role->bind_param("s", $_SESSION['username']);
$stmt_user_role->execute();
$result_user_role = $stmt_user_role->get_result();
$user_role = $result_user_role->fetch_assoc()['role'];
$stmt_user_role->close();

// Pagination setup
$limit = 3; // 3 thoughts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search setup
$search = isset($_GET['search']) ? "%".$_GET['search']."%" : "%";

// Count total thoughts (for pagination)
$stmt_total = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE current_thought LIKE ?");
$stmt_total->bind_param("s", $search);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_posts = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $limit);
$stmt_total->close();

// Fetch thoughts for current page with search
$sql_thoughts = "SELECT id, username, current_thought, thought_timestamp, role, profile_pic 
                 FROM users 
                 WHERE current_thought LIKE ? 
                 ORDER BY FIELD(role, 'admin', 'user') DESC, thought_timestamp DESC
                 LIMIT ?, ?";
$stmt = $conn->prepare($sql_thoughts);
$stmt->bind_param("sii", $search, $start, $limit);
$stmt->execute();
$result_thoughts = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Thoughts</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
body { font-family: 'Poppins', sans-serif; background:#f0f2f5; color:#333; }
.container { margin-top: 30px; }
.card { margin-bottom: 20px; }
.admin-tag { display:inline-block; background:#e74c3c; color:#fff; padding:2px 6px; font-size:12px; margin-left:10px; border-radius:4px; }
.pagination { justify-content:center; }
</style>
</head>
<body>
<div class="container">

    <h1 class="text-center mb-4">View Thoughts</h1>

    <!-- Search form -->
    <form method="GET" action="thought.php" class="form-inline mb-4 justify-content-center">
        <input type="text" name="search" class="form-control mr-2" placeholder="Search thoughts" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- Thoughts list -->
    <?php if ($result_thoughts->num_rows > 0): ?>
        <?php while ($row = $result_thoughts->fetch_assoc()): ?>
            <?php 
                $thought_timestamp = new DateTime($row['thought_timestamp']);
                $formatted_date = $thought_timestamp->format('F j, Y \a\t g:i A');
            ?>
            <div class="card">
                <div class="card-body d-flex">
                    <img src="<?php echo !empty($row['profile_pic']) ? htmlspecialchars($row['profile_pic']) : 'images/default-profile.png'; ?>" alt="Profile Picture" class="rounded-circle mr-3" width="80" height="80">
                    <div class="flex-grow-1">
                        <h5>
                            <?php echo htmlspecialchars($row['username']); ?>
                            <?php if ($row['role'] === 'admin'): ?>
                                <span class="admin-tag">Admin</span>
                            <?php endif; ?>
                        </h5>
                        <p><?php echo htmlspecialchars($row['current_thought']); ?></p>
                        <small class="text-muted">Posted on: <?php echo htmlspecialchars($formatted_date); ?></small>
                    </div>
                    <?php if ($user_role === 'admin'): ?>
                        <form action="delete_t.php" method="post" class="ml-3">
                            <input type="hidden" name="thought_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-link text-danger p-0"><i class="fas fa-trash"></i></button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- Pagination links -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="thought.php?page=<?php echo $i; ?><?php if(isset($_GET['search'])) echo '&search='.urlencode($_GET['search']); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php else: ?>
        <p class="text-center">No thoughts to display.</p>
    <?php endif; ?>

    <div class="text-center mt-3">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

</div>
</body>
</html>
