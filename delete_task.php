<?php
session_start();
require_once 'log_helper.php';
define('SECURE_ACCESS', true);
require_once 'private/db.php'; // sesuaikan pathnya
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get task ID from the URL
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];
    writeLog("Task '$task_name' (ID: {$conn->insert_id}) dihapus oleh User ID: $user_id");
    // Delete the task query
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        // Task deleted successfully, redirect back to the previous page
        writeLog("Task '$task_name' (ID: {$conn->insert_id}) dihapus oleh User ID: $user_id");
        $previous_page = $_SERVER['HTTP_REFERER'] ?? 'index.php'; // Fallback to index.php if referer is not set
        header("Location: $previous_page");
        exit();
    } else {
        // Handle failure
        echo "Failed to delete task.";
    }
}
?>