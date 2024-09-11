<?php
session_start();
include 'conn.php'; // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$errors = [];
$upload_dir = 'uploads/'; // Directory to store uploaded files

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $registration_no = $_POST['registration_no'];
    $university_name = $_POST['university_name'];
    $course = $_POST['course'];
    $year_of_study = $_POST['year_of_study'];
    $start_field = $_POST['start_field'];
    $end_field = $_POST['end_field'];
    $area_specialization = $_POST['area_specialization'];

    // Handle file upload
    if (isset($_FILES['upload_request_letter']) && $_FILES['upload_request_letter']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['upload_request_letter']['tmp_name'];
        $file_name = $_FILES['upload_request_letter']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file extension
        if ($file_ext != 'pdf') {
            $errors[] = 'Only PDF files are allowed.';
        } else {
            // Generate a unique filename
            $new_file_name = uniqid('request_', true) . '.' . $file_ext;
            $file_destination = $upload_dir . $new_file_name;

            // Move the uploaded file to the destination
            if (!move_uploaded_file($file_tmp, $file_destination)) {
                $errors[] = 'Failed to move uploaded file.';
            }
        }
    } else {
        $errors[] = 'No file uploaded or there was an upload error.';
    }

    // If there are no errors, proceed to insert data into the database
    if (empty($errors)) {
        $student_id = $_SESSION['user_id']; // Get student ID from session
        $status = 'Pending'; // Default status

        $conn->begin_transaction(); // Start transaction

        try {
            // Insert request data
            $sql = "INSERT INTO Request (student_id, registration_no, university_name, course, year_of_study, start_field, end_field, area_specialization, upload_request_letter, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "isssssssss",
                $student_id,
                $registration_no,
                $university_name,
                $course,
                $year_of_study,
                $start_field,
                $end_field,
                $area_specialization,
                $new_file_name, // Use the new filename here
                $status
            );

            if (!$stmt->execute()) {
                throw new Exception('Failed to submit request.');
            }

            // Decrease the available chance by 1
            $sql_chance = "UPDATE FieldChance SET available_chance = available_chance - 1 ORDER BY id DESC LIMIT 1";
            if (!$conn->query($sql_chance)) {
                throw new Exception('Failed to update available chance.');
            }

            $conn->commit(); // Commit transaction

            $_SESSION['message'] = 'Request submitted successfully!';
            $_SESSION['message_type'] = 'success';

        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on error
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }

        $stmt->close();
        $conn->close();

        header("Location: student_dashboard.php"); // Redirect to the same page to show the message
        exit();
    } else {
        // If there are errors, store them in session and redirect to the same page
        $_SESSION['errors'] = $errors;
        header("Location: request_field.php");
        exit();
    }
}

// Include this PHP block at the top of your PHP file
$date_today = date('Y-m-d'); // Today's date
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                                    <input type="date" class="form-control" id="start_field" name="start_field" value="<?php echo $date_today; ?>" min="<?php echo $date_today; ?>" required>
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
            </div>

            <!-- Custom JS -->
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const startField = document.getElementById('start_field');
                    const endField = document.getElementById('end_field');

                    // Set the minimum end date to be one day after the selected start date
                    startField.addEventListener('change', function () {
                        const startDate = new Date(startField.value);
                        const endDate = new Date(startDate);
                        endDate.setDate(startDate.getDate() + 1);
                        endField.value = endDate.toISOString().split('T')[0];
                        endField.min = endDate.toISOString().split('T')[0];
                    });

                    // Initialize end date based on today's date
                    const today = new Date();
                    const nextDay = new Date(today);
                    nextDay.setDate(today.getDate() + 1);
                    endField.min = nextDay.toISOString().split('T')[0];
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
        <!-- End Main -->
    </div>
</body>
</html>
