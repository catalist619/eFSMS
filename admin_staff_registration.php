<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $privilege = $_POST['privilege']; // Get privilege from the form

    // Determine which table to insert into based on privilege
    if ($privilege === 'student') {
        $sql = "INSERT INTO Student (first_name, middle_name, surname, email, phone_number, password, privilege)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $first_name, $middle_name, $surname, $email, $_POST['phone_number'], $password, $privilege);
    } else if ($privilege === 'staff' || $privilege === 'admin') {
        $sql = "INSERT INTO Staff (first_name, middle_name, surname, email, password, privilege)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt->prepare($sql);
        $stmt->bind_param("ssssss", $first_name, $middle_name, $surname, $email, $password, $privilege);
    }

    if ($stmt->execute()) {
        echo "New record created successfully";
        header("Location: admin_staff_registration.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
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
    <style>
        .registration-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .registration-form h2 {
            background-color: #343a40;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .hidden {
            display: none;
        }
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
    <script>
        
    </script>
</head>
<body>

<div class="grid-container">
         <!-- Header -->
         <header class="header">
            <div class="header-left">
                <p>Welcome to the Admin Dashboard</p>
            </div>
            <div class="header-right">
                <div class="profile-menu" id="profile-icon">
                    <span class="material-icons-outlined">account_circle</span>
                </div>
                <div class="profile-dropdown" id="profile-dropdown">
                    <a href="admin_resetpass.php">
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

    <div class="container">
        <div class="registration-form">
            <h2>Register Users</h2>
            <form id="registrationForm" action="admin_staff_registration.php" method="POST">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name">
                </div>
                <div class="form-group">
                    <label for="surname">Surname</label>
                    <input type="text" class="form-control" id="surname" name="surname" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group" id="phoneNumberGroup">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="privilege">Privilege</label>
                    <select class="form-control" id="privilege" name="privilege" required>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Register</button><br><br>
                <p>Have an account already? <a href="login.php">Log in</a></p>
            </form>
        </div>
    </div>
    <script src="js/jquery-3.7.1.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to hide the phone number field when Staff or Admin is selected
        document.getElementById('privilege').addEventListener('change', function() {
            var phoneNumberGroup = document.getElementById('phoneNumberGroup');
            if (this.value === 'staff' || this.value === 'admin') {
                phoneNumberGroup.classList.add('hidden');
            } else {
                phoneNumberGroup.classList.remove('hidden');
            }
        });

        // Initial check when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            var privilege = document.getElementById('privilege').value;
            var phoneNumberGroup = document.getElementById('phoneNumberGroup');
            if (privilege === 'staff' || privilege === 'admin') {
                phoneNumberGroup.classList.add('hidden');
            }
        });
    </script>
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
<script src="js/script.js"></script>
</html>
