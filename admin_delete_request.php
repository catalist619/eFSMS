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
        // Check if the request exists before deletion
        $query_check_request = "SELECT id FROM Request WHERE student_id = ?";
        $stmt_check = $conn->prepare($query_check_request);
        $stmt_check->bind_param("i", $student_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        $request_exists = $stmt_check->num_rows > 0;
        $stmt_check->close();

        if (!$request_exists) {
            throw new Exception("Request not found for the given student ID.");
        }

        // Delete the request from the Request table
        $query_delete_request = "DELETE FROM Request WHERE student_id = ?";
        $stmt = $conn->prepare($query_delete_request);
        $stmt->bind_param("i", $student_id);

        if (!$stmt->execute()) {
            throw new Exception("Error deleting request: " . $stmt->error);
        }

        // Increase the field chance by 1
        $query_increase_chance = "UPDATE FieldChance SET available_chance = available_chance + 1 WHERE id = 1";
        $stmt_increase_chance = $conn->prepare($query_increase_chance);

        if (!$stmt_increase_chance->execute()) {
            throw new Exception("Error increasing field chance: " . $stmt_increase_chance->error);
        }

        // Commit transaction
        $conn->commit();

        // Redirect back with success message
        header("Location: admin_student_request.php?message=Request+deleted+successfully");
        exit();
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();

        // Redirect back with error message
        header("Location: admin_student_request.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirect back with error if student_id is not provided
    header("Location: admin_student_request.php?error=Invalid+request");
    exit();
}
?>
