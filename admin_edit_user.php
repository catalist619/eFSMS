<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$type = $_GET['type'];
$table = ($type === 'student') ? 'Student' : 'Staff';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_email = $_POST['email'];
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "UPDATE $table SET email = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_email, $new_password, $id);

    if ($stmt->execute()) {
        echo "Record updated successfully";
        header("Location: admin_dashboard.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // Fetch current user data
    $query_user = "SELECT email FROM $table WHERE id = ?";
    $stmt = $conn->prepare($query_user);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dash_style.css">
</head>

<body>
    <div class="grid-container">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <p>Welcome to the Admin Dashboard</p>
            </div>
            <div class="header-left">
                <span class="material-icons-outlined"><a href="./logout.php">logout</a></span>
            </div>
        </header>
        <!-- End Header -->

        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="sidebar-title">
                <div class="sidebar-brand">
                    <span class="material-icons-outlined">admin_panel_settings</span> Admin Panel
                </div>
            </div>

            <ul class="sidebar-list">
                <a href="admin_dashboard.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">dashboard</span> Dashboard
                    </li>
                </a>
                <a href="admin_student_request.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">description</span> Student Requests
                    </li>
                </a>
                <a href="admin_users.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">people</span> All Users
                    </li>
                </a>
                <a href="admin_staff_registration.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">people</span> Register Users
                    </li>
                </a>
                <a href="admin_feedback.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">feedback</span> Feedback
                    </li>
                </a>
            </ul>
        </aside>
        <!-- End Sidebar -->
<body>
    <div class="container">
        <h2>Edit User</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>
</html>
