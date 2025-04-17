<?php
session_start();
require_once 'log_helper.php';
define('SECURE_ACCESS', true);
require_once 'private/db.php'; // sesuaikan pathnya

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['project_id'])) {
    $project_id = (int) $_GET['project_id'];
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'Unknown';

    // Ambil nama project untuk log
    $stmt = $conn->prepare("SELECT name FROM projects WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $project_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($project_name);

    if ($stmt->fetch()) {
        $stmt->close();

        // Lakukan penghapusan project
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $project_id, $user_id);
        if ($stmt->execute()) {
            writeLog("Project '{$project_name}' (ID: {$project_id}) dihapus oleh User ID: {$user_id}", [
                'id' => $user_id,
                'name' => $user_name
            ]);

            $previous_page = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: $previous_page");
            exit();
        } else {
            echo "Gagal menghapus project: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Project tidak ditemukan atau bukan milik Anda.";
        $stmt->close();
    }
}

$conn->close();
?>
