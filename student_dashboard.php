<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Check if the student has already submitted a request
$query_check_request = "SELECT id FROM Request WHERE student_id = ?";
$stmt_check_request = $conn->prepare($query_check_request);
$stmt_check_request->bind_param('i', $student_id);
$stmt_check_request->execute();
$stmt_check_request->store_result();
$request_exists = $stmt_check_request->num_rows > 0;
$stmt_check_request->close();

// Fetch uploaded documents information
$query_uploaded = "SELECT 
                        s.first_name, s.middle_name, s.surname, 
                        r.id as request_id, r.registration_no, r.university_name, r.course, 
                        r.upload_request_letter 
                    FROM 
                        Request r 
                        JOIN Student s ON r.student_id = s.id 
                    WHERE 
                        s.id = ?";
$stmt_uploaded = $conn->prepare($query_uploaded);
$stmt_uploaded->bind_param('i', $student_id);
$stmt_uploaded->execute();
$result_uploaded = $stmt_uploaded->get_result();
$stmt_uploaded->close();

// Fetch feedback information
$query_feedback = "SELECT 
                        f.id as feedback_id, f.description, f.upload_document 
                    FROM 
                        Feedback f 
                    WHERE 
                        f.student_id = ?";
$stmt_feedback = $conn->prepare($query_feedback);
$stmt_feedback->bind_param('i', $student_id);
$stmt_feedback->execute();
$result_feedback = $stmt_feedback->get_result();
$stmt_feedback->close();

// Fetch remaining field chances
$query_field_chance = "SELECT available_chance FROM FieldChance LIMIT 1";
$stmt_field_chance = $conn->prepare($query_field_chance);
$stmt_field_chance->execute();
$result_field_chance = $stmt_field_chance->get_result();
$field_chance = $result_field_chance->fetch_assoc()['available_chance'];
$stmt_field_chance->close();

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_request_id'])) {
    $delete_request_id = $_POST['delete_request_id'];
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete the feedback associated with the request
        $query_delete_feedback = "DELETE FROM Feedback WHERE student_id = ? AND field_id IN (SELECT field_id FROM Request WHERE id = ?)";
        $stmt_delete_feedback = $conn->prepare($query_delete_feedback);
        $stmt_delete_feedback->bind_param('ii', $student_id, $delete_request_id);
        $stmt_delete_feedback->execute();
        $stmt_delete_feedback->close();

        // Delete the request from the database
        $query_delete_request = "DELETE FROM Request WHERE id = ? AND student_id = ?";
        $stmt_delete_request = $conn->prepare($query_delete_request);
        $stmt_delete_request->bind_param('ii', $delete_request_id, $student_id);
        $stmt_delete_request->execute();
        $stmt_delete_request->close();

        // Increase the field chance by 1
        $query_increase_chance = "UPDATE FieldChance SET available_chance = available_chance + 1 WHERE id = 1";
        $stmt_increase_chance = $conn->prepare($query_increase_chance);
        $stmt_increase_chance->execute();
        $stmt_increase_chance->close();

        // Commit transaction
        $conn->commit();

        $_SESSION['message'] = "Request and feedback deleted successfully.";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        // Rollback transaction if any query fails
        $conn->rollback();
        $_SESSION['message'] = "Failed to delete request and feedback.";
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: student_dashboard.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dash_style.css">
    <style>
        .disabled-link {
            pointer-events: none;
            opacity: 0.6;
            cursor: not-allowed;
            color: #888;
        }

        .field-chance {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #343a40;
            color: #ffffff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 50px;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }

        .toast.success {
            background-color: #28a745;
        }

        .toast.error {
            background-color: #dc3545;
        }

        .toast.show {
            display: block;
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

                <!-- Display Remaining Field Chances -->
                <div class="field-chance">
                    <?php echo htmlspecialchars($field_chance); ?>
                </div>

                <!-- Uploaded Documents Table -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Uploaded Documents</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Registration No</th>
                                    <th>University Name</th>
                                    <th>Course</th>
                                    <th>View</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($row_uploaded = $result_uploaded->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row_uploaded['first_name'] . ' ' . $row_uploaded['middle_name'] . ' ' . $row_uploaded['surname']); ?></td>
                                    <td><?php echo htmlspecialchars($row_uploaded['registration_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row_uploaded['university_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row_uploaded['course']); ?></td>
                                    <td>
                                        <a href="view.php?file=<?php echo urlencode($row_uploaded['upload_request_letter']); ?>" class="btn btn-primary btn-sm" target="_blank">View</a>
                                    </td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="delete_request_id" value="<?php echo $row_uploaded['request_id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Request Feedback Table -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Request Feedback</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Document Name</th>
                                    <th>Uploaded Document</th>
                                    <th>View</th>
                                    <th>Download</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($row_feedback = $result_feedback->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row_feedback['description']); ?></td>
                                        <td><a href="uploads/<?php echo htmlspecialchars($row_feedback['upload_document']); ?>" target="_blank">View Document</a></td>
                                        <td><a href="uploads/<?php echo htmlspecialchars($row_feedback['upload_document']); ?>" class="btn btn-primary btn-sm" target="_blank">View</a></td>
                                        <td><a href="uploads/<?php echo htmlspecialchars($row_feedback['upload_document']); ?>" class="btn btn-success btn-sm" download>Download</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php mysqli_close($conn); ?>
        </main>
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
</body>
</html>
