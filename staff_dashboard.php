<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in and is a staff member
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Fetch available field chance
$query_field_chance = "SELECT available_chance FROM FieldChance ORDER BY id DESC LIMIT 1";
$result_field_chance = $conn->query($query_field_chance);
$available_chance = 0; // Initialize variable to store available chance

if ($result_field_chance->num_rows > 0) {
    $row_chance = $result_field_chance->fetch_assoc();
    $available_chance = $row_chance['available_chance'];
}

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
    <title>Staff Dashboard</title>

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

        /* Indicator */
        .chance-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #343a40;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 50px;
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
                <p>Welcome to the Staff Dashboard</p>
            </div>
            <div class="header-right">
                <div class="profile-menu" id="profile-icon">
                    <span class="material-icons-outlined">account_circle</span>
                </div>
                <div class="profile-dropdown" id="profile-dropdown">
                    <a href="staff_resetpass.php">
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
                    <span class="material-icons-outlined">admin_panel_settings</span> Staff Panel
                </div>
            </div>

            <ul class="sidebar-list">
                <a href="staff_dashboard.php">
                    <li class="sidebar-list-item">
                        <span class="material-icons-outlined">dashboard</span> Dashboard
                    </li>
                </a>
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
            </ul>
        </aside>
        <!-- End Sidebar -->

        <!-- Main Content -->
        <main class="main-container">
            <div class="container">
                <div class="chance-indicator">
                    <?php echo $available_chance; ?>
                </div>

                <!-- Feedback Table -->
                <div id="feedback" class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Feedback</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_feedback = $result_feedback->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row_feedback['first_name'] . ' ' . $row_feedback['middle_name'] . ' ' . $row_feedback['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($row_feedback['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row_feedback['phone_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row_feedback['description']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

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
                                    <th>Privilege</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_student = $result_students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row_student['first_name'] . ' ' . $row_student['middle_name'] . ' ' . $row_student['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($row_student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row_student['phone_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row_student['privilege']); ?></td>
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
                                    <th>Privilege</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_staff = $result_staff->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row_staff['first_name'] . ' ' . $row_staff['middle_name'] . ' ' . $row_staff['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($row_staff['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row_staff['privilege']); ?></td>
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
