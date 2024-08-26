<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$type = $_GET['type'];
$table = ($type === 'student') ? 'Student' : 'Staff';

if ($type === 'student') {
    // Delete student-specific records if necessary
    $delete_query = "DELETE FROM Student WHERE id = ?";
} else {
    // Delete staff-specific records if necessary
    $delete_query = "DELETE FROM Staff WHERE id = ?";
}

$stmt = $conn->prepare($delete_query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "Record deleted successfully";
    header("Location: admin_users.php");
} else {
    echo "Error: " . $delete_query . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();
?>
