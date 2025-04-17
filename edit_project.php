<?php
session_start();
require_once 'log_helper.php';
define('SECURE_ACCESS', true);
require_once 'private/db.php'; // sesuaikan pathnya


// Example: edit_project.php
if (isset($_GET['id'])) {
    $project_id = (int)$_GET['id'];
    // Fetch project details using $project_id
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $_POST['project_id'];
    $projectName = $_POST['project_name'];
    $userId = $_SESSION['user_id']; // Ambil user ID dari sesi

    // Validate input
    if (!empty($projectId) && !empty($projectName) && !empty($userId)) {
        // Prepare and execute the update query
        $stmt = $conn->prepare("UPDATE projects SET name = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $projectName, $projectId, $userId);

        if ($stmt->execute()) {
            // Log perubahan proyek
            writeLog("Project '$projectName' (ID: $projectId) diperbarui oleh User ID: $userId");
            header("Location: index.php?project_id=$projectId");
            exit();
        } else {
            echo "Error updating project: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid input.";
    }
}

$conn->close();
?>
