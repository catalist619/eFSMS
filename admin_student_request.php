<?php
session_start();
include 'conn.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch Student Requests
$query_requests = "SELECT 
                        s.id as student_id, s.first_name, s.middle_name, s.surname, 
                        r.start_field, r.end_field, 
                        r.upload_request_letter 
                   FROM 
                        Request r 
                        JOIN Student s ON r.student_id = s.id";

$result_requests = $conn->query($query_requests);

if (!$result_requests) {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Requests</title>

    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dash_style.css">

    <style>
        /* Styles for the pop-up form */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            margin: 6px 0 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
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
                <li class="sidebar-list-item active">
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
    <div class="container mt-5">
        <h2 class="mb-4">Student Requests</h2>

        <!-- Success and Error Messages -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Start Field</th>
                            <th>End Field</th>
                            <th>Document</th>
                            <th>Download</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_request = $result_requests->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row_request['first_name'] . ' ' . $row_request['middle_name'] . ' ' . $row_request['surname']); ?></td>
                                <td><?php echo htmlspecialchars($row_request['start_field']); ?></td>
                                <td><?php echo htmlspecialchars($row_request['end_field']); ?></td>
                                <td><a href="uploads/<?php echo htmlspecialchars($row_request['upload_request_letter']); ?>" target="_blank">View Document</a></td>
                                <td><a href="uploads/<?php echo htmlspecialchars($row_request['upload_request_letter']); ?>" download>Download</a></td>
                                <td>
                                <button class="btn btn-primary" onclick="openModal(<?php echo $row_request['student_id']; ?>)">Approve</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRequest(<?php echo $row_request['student_id']; ?>, <?php echo $row_request['id']; ?>)">Delete</button>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End Main -->

    <!-- Modal (Pop-up Form) -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Upload Feedback</h2>
            <form action="approve_feedback.php" method="post" enctype="multipart/form-data">
                <input type="hidden" id="student_id" name="student_id" value="">

                <div class="form-group">
                    <label for="upload_document">Upload Document:</label>
                    <input type="file" class="form-control" id="upload_document" name="upload_document" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(studentId) {
            document.getElementById('student_id').value = studentId;
            document.getElementById('myModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('myModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('myModal')) {
                closeModal();
            }
        }

        function deleteRequest(studentId, requestId) {
        if (confirm('Are you sure you want to delete this request?')) {
        window.location.href = 'admin_delete_request.php?student_id=' + studentId + '&request_id=' + requestId;
    }
}

    </script>

</body>
</html>
