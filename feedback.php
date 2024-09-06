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
    </style>
</head>
<body>
    <div class="grid-container">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <p>Welcome to your eFSMS</p>
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
        <a href="student_resetpass.php">
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
        </main>
    </div>
</body>
</html>
