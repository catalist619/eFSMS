<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Fetch Student Request information
$query_student_request = "SELECT 
                            s.first_name, s.middle_name, s.surname, 
                            r.start_field, r.end_field, r.upload_request_letter 
                        FROM 
                            Request r 
                            JOIN Student s ON r.student_id = s.id";

$result_student_request = $conn->query($query_student_request);

// Fetch Feedback information
$query_feedback = "SELECT 
                        s.first_name, s.middle_name, s.surname, 
                        o.email, o.phone_number, o.description 
                    FROM 
                        Opinion o 
                        JOIN Student s ON o.student_id = s.id";

$result_feedback = $conn->query($query_feedback);

// Fetch Student Users
$query_students = "SELECT first_name, middle_name, surname, email, phone_number, privilege FROM Student";
$result_students = $conn->query($query_students);

// Fetch Staff Users
$query_staff = "SELECT first_name, middle_name, surname, email, privilege FROM Staff WHERE privilege = 'staff'";
$result_staff = $conn->query($query_staff);
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
                <p>Welcome to the Staff Dashboard</p>
            </div>
            <div class="header-right">
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
                    <span class="material-icons-outlined">admin_panel_settings</span> Staff Panel
                </div>
            </div>

            <ul class="sidebar-list">
                <a href="staff_dashboard.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">dashboard</span> Dashboard
                    </li>
                </a>
                <!-- <a href="student_request.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">description</span> Student Requests
                    </li>
                </a> -->
                <a href="staff_users.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">people</span> Users
                    </li>
                </a>
                <a href="staff_feedback.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">feedback</span> Feedback
                    </li>
                </a>
                <a href="staff_resetpass.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">password</span> Reset Password
                    </li>
                </a>
            </ul>
        </aside>
        <!-- End Sidebar -->

        <!-- Main -->
        <main class="main-container">
            <div class="container">

                <!-- Student Users Table -->
                <div id="students" class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Student Users</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_student = $result_students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row_student['first_name'] . ' ' . $row_student['middle_name'] . ' ' . $row_student['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($row_student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row_student['phone_number']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Staff Users Table -->
                <div id="staff" class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Staff Users</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_staff = $result_staff->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row_staff['first_name'] . ' ' . $row_staff['middle_name'] . ' ' . $row_staff['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($row_staff['email']); ?></td>
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
