<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// Check if the student has already submitted a request
$query_check_request = "SELECT id FROM Request WHERE student_id = ?";
$stmt_check_request = $conn->prepare($query_check_request);
$stmt_check_request->bind_param('i', $student_id);
$stmt_check_request->execute();
$stmt_check_request->store_result();
$request_exists = $stmt_check_request->num_rows > 0;
$stmt_check_request->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate form data
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    } else {
        // Fetch current password from database
        $query_password = "SELECT password FROM Student WHERE id = ?";
        $stmt_password = $conn->prepare($query_password);
        $stmt_password->bind_param('i', $student_id);
        $stmt_password->execute();
        $stmt_password->bind_result($hashed_password);
        $stmt_password->fetch();
        $stmt_password->close();

        // Verify current password
        if (!password_verify($current_password, $hashed_password)) {
            $errors[] = "Current password is incorrect.";
        }

        // Check if new password matches confirmation
        if ($new_password !== $confirm_password) {
            $errors[] = "New password and confirm password do not match.";
        }

        // Update password in database if no errors
        if (empty($errors)) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query_update_password = "UPDATE Student SET password = ? WHERE id = ?";
            $stmt_update_password = $conn->prepare($query_update_password);
            $stmt_update_password->bind_param('si', $new_hashed_password, $student_id);
            if ($stmt_update_password->execute()) {
                $success = "Password updated successfully.";
            } else {
                $errors[] = "Failed to update password. Please try again.";
            }
            $stmt_update_password->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dash_style.css">

    <style>
                        /* General styling for header */
                        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8f9fa;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-left p {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 500;
            color: #343a40;
        }

        .header-right {
            display: flex;
            align-items: center;
            position: relative;
        }

        /* Profile Menu Icon */
        .profile-menu {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .profile-menu .material-icons-outlined {
            font-size: 36px;
            color: #343a40;
            transition: color 0.3s ease;
        }

        .profile-menu:hover .material-icons-outlined {
            color: #007bff;
        }

        /* Profile Dropdown Menu */
        .profile-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            background-color: #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 10px;
            width: 200px;
            z-index: 100;
            display: none; /* Initially hidden */
        }

        .profile-dropdown a {
            display: flex;
            align-items: center;
            color: #343a40;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .profile-dropdown a:hover {
            background-color: #f1f1f1;
        }

        .profile-dropdown a span {
            margin-right: 10px;
        }

        /* Table Styling */
        .table-striped {
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="grid-container">
         <!-- Header -->
         <header class="header">
            <div class="header-left">
                <p>Welcome to the Student Dashboard</p>
            </div>
            <div class="header-right">
                <div class="profile-menu" id="profile-icon">
                    <span class="material-icons-outlined">account_circle</span>
                </div>
                <div class="profile-dropdown" id="profile-dropdown">
                    <a href="student_resetpass.php">
                        <span class="material-icons-outlined">password</span> Reset Password
                    </a>
                    <a href="logout.php">
                        <span class="material-icons-outlined">logout</span> Logout
                    </a>
                </div>
            </div>
        </header>
        <!-- End Header -->

<!-- Sidebar -->
<aside id="sidebar">
    <div class="sidebar-title">
        <div class="sidebar-brand">
            <span class="material-icons-outlined">edit_calendar</span> eFSMS
        </div>
    </div>

    <ul class="sidebar-list">
        <a href="student_dashboard.php">
            <li class="sidebar-list-item">
                <span class="material-icons-outlined">dashboard</span> Dashboard
            </li>
        </a>
        <!-- Disable the 'Request Field' link if the student has already submitted a request -->
        <li class="sidebar-list-item <?php echo $request_exists ? 'disabled-link' : ''; ?>">
            <?php if ($request_exists): ?>
                <span class="material-icons-outlined">bolt</span> Request Field
            <?php else: ?>
                <a href="request_field.php"><span class="material-icons-outlined">bolt</span> Request Field</a>
            <?php endif; ?>
        </li>
        <a href="feedback.php">
            <li class="sidebar-list-item">
                <span class="material-icons-outlined">swap_horiz</span> Feedback
            </li>
        </a>
    </ul>
</aside>
<!-- End Sidebar -->

    <div class="container">
        <div class="card mt-5">
            <div class="card-body">
                <h5 class="card-title">Reset Password</h5>

                <!-- Display Errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Display Success Message -->
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Reset Password Form -->
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom JS -->
    <script src="js/bootstrap.min.js"></script>

                <!-- JavaScript for Profile Dropdown -->
                <script>
        document.addEventListener('DOMContentLoaded', function() {
            var profileIcon = document.getElementById('profile-icon');
            var profileDropdown = document.getElementById('profile-dropdown');

            profileIcon.addEventListener('click', function() {
                profileDropdown.style.display = (profileDropdown.style.display === 'none' || profileDropdown.style.display === '') 
                    ? 'block' 
                    : 'none';
            });

            document.addEventListener('click', function(event) {
                if (!profileIcon.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileDropdown.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
