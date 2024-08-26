<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $registration_no = $_POST['registration_no'];
    $university_name = $_POST['university_name'];
    $course = $_POST['course'];
    $year_of_study = $_POST['year_of_study'];
    $start_field = $_POST['start_field'];
    $end_field = $_POST['end_field'];
    $area_specialization = $_POST['area_specialization'];
    
    // Handle file upload
    $upload_request_letter = $_FILES['upload_request_letter']['name'];
    $upload_dir = 'uploads/';
    $upload_file = $upload_dir . basename($upload_request_letter);
    $upload_success = move_uploaded_file($_FILES['upload_request_letter']['tmp_name'], $upload_file);

    if (!$upload_success) {
        $_SESSION['message'] = 'File upload failed.';
        $_SESSION['message_type'] = 'error';
        header('Location: request_field.php');
        exit();
    }

    // Check available field chances
    $chance_query = "SELECT available_chance FROM FieldChance LIMIT 1"; // Assuming there's only one row
    $chance_result = $conn->query($chance_query);
    
    if ($chance_result && $chance_row = $chance_result->fetch_assoc()) {
        if ($chance_row['available_chance'] > 0) {
            // Decrease the field chance by 1
            $update_chance_query = "UPDATE FieldChance SET available_chance = available_chance - 1";
            if ($conn->query($update_chance_query)) {
                // Insert data into Request table
                $student_id = $_SESSION['user_id']; // Get the logged-in user ID
                $status = 'Pending'; // Default status

                $query = "INSERT INTO Request (student_id, registration_no, university_name, course, year_of_study, start_field, end_field, area_specialization, upload_request_letter, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if ($stmt = $conn->prepare($query)) {
                    $stmt->bind_param('isssssssss', $student_id, $registration_no, $university_name, $course, $year_of_study, $start_field, $end_field, $area_specialization, $upload_request_letter, $status);

                    if ($stmt->execute()) {
                        $_SESSION['message'] = 'Request submitted successfully!';
                        $_SESSION['message_type'] = 'success';
                    } else {
                        $_SESSION['message'] = 'Failed to submit request.';
                        $_SESSION['message_type'] = 'error';
                    }
                    $stmt->close();
                } else {
                    $_SESSION['message'] = 'Database query failed.';
                    $_SESSION['message_type'] = 'error';
                }
            } else {
                $_SESSION['message'] = 'Failed to update field chance.';
                $_SESSION['message_type'] = 'error';
            }
        } else {
            $_SESSION['message'] = 'No available chances left.';
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = 'Failed to fetch field chances.';
        $_SESSION['message_type'] = 'error';
    }

    mysqli_close($conn);

    header('Location: student_dashboard.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Request Field</title>

    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dash_style.css">
    <link rel="stylesheet" href="css/request_field.css">
    <style>
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
                <a href="request_field.php">
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

                <!-- Request Field Form -->
                <div class="container mt-4">
                    <div class="card registration-form shadow-sm">
                        <div class="card-body">
                            <h2 class="card-title mb-4 text-center">Request Field</h2>
                            <form action="request_field.php" method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="registration_no" class="form-label">Registration Number</label>
                                    <input type="text" class="form-control" id="registration_no" name="registration_no" required>
                                </div>
                                <div class="mb-3">
                                    <label for="university_name" class="form-label">University Name</label>
                                    <input type="text" class="form-control" id="university_name" name="university_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="course" class="form-label">Course</label>
                                    <input type="text" class="form-control" id="course" name="course" required>
                                </div>
                                <div class="mb-3">
                                    <label for="year_of_study" class="form-label">Year of Study</label>
                                    <input type="number" class="form-control" id="year_of_study" name="year_of_study" required>
                                </div>
                                <div class="mb-3">
                                    <label for="start_field" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_field" name="start_field" required>
                                </div>
                                <div class="mb-3">
                                    <label for="end_field" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_field" name="end_field" required>
                                </div>
                                <div class="mb-3">
                                    <label for="area_specialization" class="form-label">Area of Specialization</label>
                                    <select class="form-select" id="area_specialization" name="area_specialization" required>
                                        <option value="" disabled selected>Select your specialization</option>
                                        <option value="Computer Networking">Computer Networking</option>
                                        <option value="Database">Database</option>
                                        <option value="Cyber Security">Cyber Security</option>
                                        <option value="Programming">Programming</option>
                                        <option value="Software Development">Software Development</option>
                                        <option value="System Analysis and Design">System Analysis and Design</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="upload_request_letter" class="form-label">Upload Request Letter</label>
                                    <input type="file" class="form-control" id="upload_request_letter" name="upload_request_letter" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Submit Request</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End Main -->
            </div>
            <?php mysqli_close($conn); ?>
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
