<?php
session_start();
include 'conn.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $staff_id = $_SESSION['user_id'];
    $description = $_POST['description'];
    $status = 'Pending'; // Initially set the status to 'Pending'
    
    // Handle file upload
    $upload_document = '';
    if (isset($_FILES['upload_document']) && $_FILES['upload_document']['error'] == 0) {
        $target_dir = "uploads/";
        $upload_document = basename($_FILES["upload_document"]["name"]);
        $target_file = $target_dir . $upload_document;
        
        // Move uploaded file to the target directory
        if (!move_uploaded_file($_FILES["upload_document"]["tmp_name"], $target_file)) {
            die("Sorry, there was an error uploading your file.");
        }
    }

    // Insert into the Feedback table
    $stmt = $conn->prepare("INSERT INTO Feedback (student_id, staff_id, field_id, upload_document, description, status) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Assuming you want to insert a specific field_id (e.g., 1)
    $field_id = 1; // Replace with appropriate field ID logic
    
    $stmt->bind_param("iissss", $student_id, $staff_id, $field_id, $upload_document, $description, $status);

    if ($stmt->execute()) {
        header("Location: admin_student_request.php?message=Feedback successfully added.");
    } else {
        header("Location: admin_student_request.php?error=Failed to add feedback.");
    }
    
    $stmt->close();
    $conn->close();
}
?>
