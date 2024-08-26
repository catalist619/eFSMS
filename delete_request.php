<?php
session_start();
include 'conn.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['privilege'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ensure the student_id is provided via GET
if (isset($_GET['student_id'])) {
    $student_id = $conn->real_escape_string($_GET['student_id']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete the request from the Request table
        $query_delete_request = "DELETE FROM Request WHERE student_id = ?";
        $stmt = $conn->prepare($query_delete_request);
        $stmt->bind_param("i", $student_id);

        if (!$stmt->execute()) {
            throw new Exception("Error deleting request: " . $stmt->error);
        }

        // Optionally, you might want to delete associated feedback or any other related records
        // $query_delete_feedback = "DELETE FROM Feedback WHERE student_id = ?";
        // $stmt_feedback = $conn->prepare($query_delete_feedback);
        // $stmt_feedback->bind_param("i", $student_id);
        // if (!$stmt_feedback->execute()) {
        //     throw new Exception("Error deleting feedback: " . $stmt_feedback->error);
        // }

        // Commit transaction
        $conn->commit();

        // Redirect back with success message
        header("Location: student_request.php?message=Request+deleted+successfully");
        exit();
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();

        // Redirect back with error message
        header("Location: student_request.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirect back with error if student_id is not provided
    header("Location: student_request.php?error=Invalid+request");
    exit();
}
?>
