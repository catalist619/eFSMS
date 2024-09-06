<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'admin') {
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
    <style>
        .chance-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 100px;
            height: 100px;
            background-color: #343a40;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 1em;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 50px;
        }

        .chance-number {
            font-size: 1.5em;
        }
    </style>
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
                <a href="admin_resetpass.php">
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
                <div class="chance-indicator">
                    Available chance
                    <div class="chance-number"><?php echo $available_chance; ?></div>
                </div>

                <!-- Student Request Table -->
                <div id="student_requests" class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Student Requests</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Start Field</th>
                                    <th>End Field</th>
                                    <th>Document</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_request = $result_student_request->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row_request['first_name'] . ' ' . $row_request['middle_name'] . ' ' . $row_request['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($row_request['start_field']); ?></td>
                                        <td><?php echo htmlspecialchars($row_request['end_field']); ?></td>
                                        <td><?php echo htmlspecialchars($row_request['upload_request_letter']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
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
        </main>
        <!-- End Main here-->
    </div>
</body>
</html>
