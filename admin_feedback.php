<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if ($delete_id > 0) {
        $delete_query = "DELETE FROM Opinion WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        if ($stmt) {
            $stmt->bind_param('i', $delete_id);
            if ($stmt->execute()) {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error_message = "Error deleting record: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    } else {
        $error_message = "Invalid ID.";
    }
}

// Fetch Feedback information
$query_feedback = "SELECT 
                        s.first_name, s.middle_name, s.surname, 
                        o.id, o.email, o.phone_number, o.description 
                    FROM 
                        Opinion o 
                        JOIN Student s ON o.student_id = s.id";

$result_feedback = $conn->query($query_feedback);

// Check for query errors
if (!$result_feedback) {
    die("Error executing query: " . $conn->error);
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
                <!-- <span class="material-icons-outlined">notifications</span>
                <span class="material-icons-outlined">email</span>
                <span class="material-icons-outlined">account_circle</span> -->
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

        <!-- Main -->
        <main class="main-container">
            <div class="container">
                <!-- Feedback Table -->
                <div id="feedback" class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Feedback</h5>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_feedback = $result_feedback->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row_feedback['first_name'] . ' ' . $row_feedback['middle_name'] . ' ' . $row_feedback['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($row_feedback['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row_feedback['phone_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row_feedback['description']); ?></td>
                                        <td>
                                            <a href="?delete_id=<?php echo htmlspecialchars($row_feedback['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this feedback?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php $conn->close(); ?>
        </main>
    </div>

    <!-- Custom JS -->
    <script src="../js/script.js"></script>
</body>

</html>
