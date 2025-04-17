<?php
session_start();
require_once 'log_helper.php';
define('SECURE_ACCESS', true);
require_once 'private/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /To_do_list/login");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['act']) ? $_GET['act'] : '';

// Handle completing a task
if ($action === 'complete' && $task_id > 0) {
    // First verify the task belongs to the current user
    $stmt = $conn->prepare("
        SELECT t.id, p.id as project_id 
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE t.id = ? AND p.user_id = ?
    ");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: /To_do_list/?error=task_not_found");
        exit;
    }
    
    $task_data = $result->fetch_assoc();
    $project_id = $task_data['project_id'];
    
    // Update the task status to completed
    $update_stmt = $conn->prepare("UPDATE tasks SET is_completed = 2, completed_at = NOW() WHERE id = ?");
    $update_stmt->bind_param("i", $task_id);
    
    if ($update_stmt->execute()) {
        writeLog("Task ID: $task_id telah diselesaikan oleh User ID: $user_id");
        header("Location: /To_do_list/?project_id={$project_id}&completed=1");
    } else {
        writeLog("Gagal menyelesaikan Task ID: $task_id - Error: " . $update_stmt->error);
        header("Location: /To_do_list/?project_id={$project_id}&error=complete_failed");
    }
    exit;
}

// Redirect to dashboard if no valid action
header("Location: /To_do_list/");
exit;