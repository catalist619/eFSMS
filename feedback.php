<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Fetch user details from the session
$student_id = $_SESSION['user_id'];

// Check if the student has already submitted a request
$query_check_request = "SELECT id FROM Request WHERE student_id = ?";
$stmt_check_request = $conn->prepare($query_check_request);
$stmt_check_request->bind_param('i', $student_id);
$stmt_check_request->execute();
$stmt_check_request->store_result();
$request_exists = $stmt_check_request->num_rows > 0;
$stmt_check_request->close();


$email = $_SESSION['email'] ?? ''; // Use default empty string if not set
$phone_number = $_SESSION['phone_number'] ?? ''; // Use default empty string if not set

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $description = $_POST['description'];
    
    // Insert data into database
    $query = "INSERT INTO Opinion (student_id, email, phone_number, description) 
              VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('isss', $student_id, $email, $phone_number, $description);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Opinion submitted successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to submit opinion.';
            $_SESSION['message_type'] = 'error';
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = 'Database query failed.';
        $_SESSION['message_type'] = 'error';
    }

    $conn->close();

    header('Location: feedback.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opinion Form</title>

    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dash_style.css">
    <link rel="stylesheet" href="css/opinion_form.css">
    <style>

    .disabled-link {
    pointer-events: none;
    opacity: 0.6;
    cursor: not-allowed;
    color: #888; /* Optional: Change the color to indicate it's disabled */
    }
        /* Basic toast style */
        .toast {
            display: none;
            position: fixed;
            top: 20px; /* Position from the top */
            right: 20px; /* Position from the right */
            padding: 15px;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            z-index: 1000;
            transition: opacity 0.5s ease;
        }

        /* Show toast */
        .toast.show {
            display: block;
            opacity: 1;
        }

        /* Success toast */
        .toast.success {
            background-color: #4CAF50;
        }

        /* Error toast */
        .toast.error {
            background-color: #F44336;
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
        <a href="request_field.php" <?php echo $request_exists ? 'class="disabled-link"' : ''; ?>>
            <li class="sidebar-list-item">
                <span class="material-icons-outlined">bolt</span> Request Field
            </li>
        </a>
        <a href="feedback.php">
            <li class="sidebar-list-item">
                <span class="material-icons-outlined">swap_horiz</span> Feedback
            </li>
        </a>
    </ul>
</aside>
        <!-- End Sidebar -->

        <!-- Main -->
        <main class="main-container">
            <div class="container">
                <!-- Toast notification -->
                <?php
                if (isset($_SESSION['message'])) {
                    echo '<div id="toast" class="toast ' . $_SESSION['message_type'] . '">'
                        . $_SESSION['message'] .
                        '</div>';
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                }
                ?>

                <!-- Opinion Form -->
                <div class="container mt-4">
                    <div class="card opinion-form shadow-sm">
                        <div class="card-body">
                            <h2 class="card-title mb-4 text-center">Submit Your Opinion</h2>
                            <form action="feedback.php" method="post">
                                <div class="mb-3">
                                    <!-- <label for="description" class="form-label">Description</label> -->
                                    <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Submit Opinion</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End Main -->
            </div>
            <!-- Custom JS -->
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const toast = document.getElementById('toast');
                    
                    if (toast) {
                        // Display the toast message
                        toast.classList.add('show');
                        
                        // Hide the toast message after 3 seconds
                        setTimeout(() => {
                            toast.classList.remove('show');
                        }, 3000);
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
        </main>
    </div>
</body>
</html>
