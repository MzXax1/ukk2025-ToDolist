<?php
session_start();
require_once 'log_helper.php';
define('SECURE_ACCESS', true);
require_once 'private/db.php'; // sesuaikan pathnya


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

/**
 * Clean input data to prevent XSS and other attacks.
 *
 * @param string $data The input data to clean.
 * @return string The cleaned data.
 */
function cleanInput($data)
{
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Ambil nama user dari database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc(); 

// Initialize variables
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM projects WHERE user_id = ?");
$stmt->bind_param("i", $user_id); // "i" berarti integer
$stmt->execute();
$resultProjects = $stmt->get_result(); // 

// Handle adding a project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_name'])) {
    $project_name = cleanInput($_POST['project_name']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO projects (name, user_id) VALUES (?, ?)");
    $stmt->bind_param("si", $project_name, $user_id);
    if ($stmt->execute()) {
        writeLog("Project '$project_name' (ID: {$conn->insert_id}) ditambahkan oleh User ID: $user_id");
    } else {
        writeLog("Gagal menambahkan proyek: " . $stmt->error);
    }

    header("Location: index.php?project_id=" . $conn->insert_id);
    exit;
}

// Handle editing a project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_project_id']) && isset($_POST['new_project_name'])) {
    $edit_project_id = (int) $_POST['edit_project_id'];
    $new_project_name = cleanInput($_POST['new_project_name']);

    $stmt = $conn->prepare("UPDATE projects SET name = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $new_project_name, $edit_project_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        writeLog("Project '$project_name' (ID: {$conn->insert_id}) diedit oleh User ID: $user_id");
    } else {
        writeLog("Gagal menambahkan proyek: " . $stmt->error);
    }

    header("Location: index.php?project_id=" . $conn->insert_id);
    exit;
}

// Handle adding a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_name']) && isset($_POST['project_id'])) {
    $task_name = cleanInput($_POST['task_name']);
    $status = $_POST['status']; // Assuming status is 'Pending' or 'In Progress'
    $priority = $_POST['priority']; // Ensure priority is passed as Low, Medium, High
    $project_id = (int) $_POST['project_id'];
    $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : NULL;

    writeLog("User ID: {$_SESSION['user_id']} mencoba menambahkan task: '$task_name' ke project ID: $project_id dengan due date: $due_date");

    // Validate due date, if it's in the past
    if ($due_date && strtotime($due_date) < time()) {
        writeLog("Task '$task_name' berhasil ditambahkan dengan ID: {$conn->insert_id}");
        echo json_encode(['success' => false, 'message' => 'Due date cannot be in the past.']);
        exit;
    }

    // Insert into database (assume status is either 0 or 1 for pending/in progress)
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO tasks (name, is_completed, project_id, due_date, user_id, priority) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisis", $task_name, $status, $project_id, $due_date, $user_id, $priority);

    if ($stmt->execute()) {
        writeLog("Task '$task_name' berhasil ditambahkan dengan ID: {$conn->insert_id}");
        echo json_encode(['success' => true, 'task_id' => $conn->insert_id, 'task_name' => $task_name, 'status' => $status, 'priority' => $priority]);
    } else {
        writeLog("Gagal menambahkan task: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to add task: ' . $stmt->error]);
    }
    exit;
}

// Ambil user_id dari sesi login
$user_id = $_SESSION['user_id'];
$project_id = isset($_GET['project_id']) && is_numeric($_GET['project_id']) ? (int) $_GET['project_id'] : 0;

// Query universal untuk fetch tasks
$priority = isset($_GET['priority']) ? $_GET['priority'] : null;

// Query untuk project tertentu
if ($project_id > 0) {
    // Query untuk project tertentu yang hanya diambil oleh user yang login
    $sqlTasks = "SELECT tasks.*, projects.name as project_name 
             FROM tasks 
             JOIN projects ON tasks.project_id = projects.id 
             WHERE tasks.project_id = ? 
               AND projects.user_id = ? 
               AND tasks.user_id = ?";

    if ($priority) {
        $sqlTasks .= " AND tasks.priority = ?";
        $stmtTasks = $conn->prepare($sqlTasks);
        $stmtTasks->bind_param("iiis", $project_id, $user_id, $user_id, $priority);
    } else {
        $stmtTasks = $conn->prepare($sqlTasks);
        $stmtTasks->bind_param("iii", $project_id, $user_id, $user_id);
    }
} else {
    // Tampilkan semua task dari user yang login
    $sqlTasks = "SELECT tasks.*, projects.name as project_name 
                 FROM tasks 
                 JOIN projects ON tasks.project_id = projects.id 
                 WHERE tasks.user_id = ? AND projects.user_id = ?";

    if ($priority) {
        $sqlTasks .= " AND tasks.priority = ?";
        $stmtTasks = $conn->prepare($sqlTasks);
        $stmtTasks->bind_param("sis", $user_id, $user_id, $priority);
    } else {
        $stmtTasks = $conn->prepare($sqlTasks);
        $stmtTasks->bind_param("ii", $user_id, $user_id);
    }

    $stmtTasks->execute();
    $resultTasks = $stmtTasks->get_result();
}


// Ambil nilai dari GET
$user_id = $_SESSION['user_id'] ?? 0;

$priority = $_GET['priority'] ?? '';
$priority = trim($priority);
$search = $_GET['search'] ?? '';
$search = trim($search);
$project_id = isset($_GET['project_id']) ? (int) $_GET['project_id'] : 0;

// Mulai bangun query dengan JOIN ke projects dan filter user_id
$sql = "SELECT tasks.* FROM tasks 
        JOIN projects ON tasks.project_id = projects.id 
        WHERE projects.user_id = ?";
$params = [$user_id];
$types = "i";

// Filter by project jika project_id tidak 0
if ($project_id !== 0) {
    $sql .= " AND tasks.project_id = ?";
    $params[] = $project_id;
    $types .= "i";
}

// Filter by priority jika ada nilai
if ($priority !== '') {
    $sql .= " AND tasks.priority = ?";
    $params[] = $priority;
    $types .= "s";
}

// Filter by search (nama task)
if ($search !== '') {
    $sql .= " AND tasks.name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// Tambahkan urutan sorting berdasarkan priority: High → Medium → Low
$sql .= " ORDER BY FIELD(tasks.priority, 'High', 'Medium', 'Low')";

// Siapkan dan jalankan query
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultTasks = $stmt->get_result();



// Ambil nama proyek jika project_id dipilih
$projectName = "All Projects";
if ($project_id > 0) {
    $stmtProject = $conn->prepare("SELECT name FROM projects WHERE id = ? AND user_id = ?");
    $stmtProject->bind_param("ii", $project_id, $user_id);
    $stmtProject->execute();
    $resultProject = $stmtProject->get_result();
    if ($resultProject->num_rows > 0) {
        $projectName = $resultProject->fetch_assoc()['name'];
    } else {
        $projectName = "Unknown Project";
    }
}

// Handle editing a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_POST['edit-status'])) {
    $task_id = (int) $_POST['task_id'];
    $status = (int) $_POST['edit-status']; // 2 for completed

    writeLog("User ID: {$_SESSION['user_id']} mencoba mengubah status task ID: $task_id menjadi: $status");

    if ($status === 2) {
        $stmt = $conn->prepare("UPDATE tasks SET is_completed = ?, completed_at = NOW() WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE tasks SET is_completed = ?, completed_at = NULL WHERE id = ?");
    }

    $stmt->bind_param("ii", $status, $task_id);

    if ($stmt->execute()) {
        writeLog("Task ID: $task_id berhasil diperbarui menjadi status: $status");
        echo json_encode(['success' => true, 'task_id' => $task_id, 'status' => $status]);
    } else {
        writeLog("Gagal memperbarui task ID: $task_id - Error: " . $stmt->error);
        error_log("Task update error: " . $stmt->error, 3, "php_error.log");
        echo json_encode(['success' => false, 'message' => 'Failed to update task']);
    }
    exit;
}
$nama_user = $user ? $user['name'] : 'Guest';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo App - Tasks for
        <?= htmlspecialchars($projectName); ?>
    </title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="vaficon.png" type="image/x-icon">
    <style>
        html, body {
            height: 100%;
            min-height: 100vh;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        .main-wrapper {
            flex: 1;
            display: flex;
            min-height: 100vh;
            /* height: 100vh; */
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
            overflow: hidden; /* Jangan scroll seluruh .content */
        }

        .task-list {
            flex: 1;
            overflow-y: auto;
            margin-top: 20px;
        }

        .task-actions-wrapper {
            display: flex;
            justify-content: flex-start; /* atau center kalau mau tengah */
            margin-top: 15px;
        }

        .add-task-btn {
            display: inline-block;
            width: auto;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar {
            width: 300px;
            background-color: #f4f4f4;
            padding: 20px;
        }
       
        /* Modal Styling */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1000;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            background-color: rgba(0, 0, 0, 0.6);
            /* Black with opacity */
            backdrop-filter: blur(4px);
            /* Blur effect */
        }

        .priority-filter-form {
            margin-bottom: 20px;
        }

        .priority-filter-form select {
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }


        .modal-content {
            background: #ffffff;
            /* White background */
            margin: 8% auto;
            /* Centered */
            padding: 20px;
            /* Padding inside the modal */
            border-radius: 12px;
            /* Rounded corners */
            width: 400px;
            /* Fixed width */
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
            /* Shadow effect */
        }

        .modal-header {
            background: #007bff;
            /* Blue background */
            color: white;
            /* White text */
            padding: 15px;
            /* Padding inside the header */
            font-size: 18px;
            /* Font size */
            border-top-left-radius: 12px;
            /* Rounded corners */
            border-top-right-radius: 12px;
            /* Rounded corners */
        }

        .modal-body input,
        .modal-body select {
            width: 100%;
            /* Full width */
            padding: 10px;
            /* Padding inside input */
            margin-bottom: 15px;
            /* Space below input */
            border-radius: 6px;
            /* Rounded corners */
            border: 1px solid #ccc;
            /* Light border */
            font-size: 14px;
            /* Font size */
        }

        .submit-btn {
            width: 100%;
            /* Full width */
            padding: 12px;
            /* Padding inside button */
            background: #28a745;
            /* Green background */
            color: white;
            /* White text */
            border: none;
            /* No border */
            border-radius: 6px;
            /* Rounded corners */
            font-size: 16px;
            /* Font size */
            cursor: pointer;
            /* Pointer cursor */
            transition: background-color 0.3s ease;
            /* Smooth transition */
        }

        .submit-btn:hover {
            background: #218838;
            /* Darker green on hover */
        }

        /* Logout Button */
        .logout-btn {
            width: 100%;
            /* Full width */
            padding: 12px;
            /* Padding inside button */
            background-color: #dc3545;
            /* Red background */
            color: white;
            /* White text */
            border: none;
            /* No border */
            border-radius: 6px;
            /* Rounded corners */
            font-size: 16px;
            /* Font size */
            cursor: pointer;
            /* Pointer cursor */
            transition: background-color 0.3s ease;
            /* Smooth transition */
            margin-top: 20px;
            /* Space above */
        }

        .logout-btn:hover {
            background-color: #c82333;
            /* Darker red on hover */
        }
        
       

        .completed-task {
            text-decoration: line-through;
            color: gray;
        }


        .task-item {
            display: flex;
            /* Flexbox for layout */
            justify-content: space-between;
            /* Space between items */
            padding: 12px;
            /* Padding inside item */
            margin-bottom: 10px;
            /* Space below item */
            border-radius: 8px;
            /* Rounded corners */
            background-color: #f9f9f9;
            /* Light background */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Shadow effect */
            align-items: center;
            /* Center items vertically */
        }

        .task-meta {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .task-priority {
            font-size: 13px;
            font-weight: 500;
            margin-right: 4px;   /* kasih jarak ke status */
            margin-left: -8px;   /* ini geser ke kiri */
        }

        .task-item .task-name {
            font-size: 16px;
            /* Font size */
            font-weight: bold;
            /* Bold text */
        }

        .task-item .task-status {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 6px;
            color: white;
            margin-top: 4px; /* Tambahkan ini */
        }

        .task-item .task-status.pending {
            background-color: #ffc107;
            /* Yellow for pending */
        }

        .task-item .task-status.in-progress {
            background-color: #007bff;
            /* Blue for in-progress */
        }

        .task-item .task-status.completed {
            background-color: #28a745;
            /* Green for completed */
        }

        .task-item button {
            background-color: #007bff;
            /* Blue background */
            color: white;
            /* White text */
            padding: 6px 12px;
            /* Padding inside button */
            border: none;
            /* No border */
            border-radius: 6px;
            /* Rounded corners */
            cursor: pointer;
            /* Pointer cursor */
            font-size: 14px;
            /* Font size */
            transition: background-color 0.3s ease;
            /* Smooth transition */
        }

        .task-item button:hover {
            background-color: #0056b3;
            /* Darker blue on hover */
        }

        .task-actions {
            display: flex;
            /* Flexbox for actions */
            gap: 8px;
            /* Space between buttons */
        }

        .logout-btn {
            width: auto;
            /* Set width to auto for a smaller button */
            padding: 6px 10px;
            /* Reduce padding */
            background-color: #dc3545;
            /* Red background */
            color: white;
            /* White text */
            border: none;
            /* No border */
            border-radius: 4px;
            /* Rounded corners */
            font-size: 14px;
            /* Smaller font size */
            cursor: pointer;
            /* Pointer cursor */
            transition: background-color 0.3s ease;
            /* Smooth transition */
            margin-top: 10px;
            /* Space above */
        }

        .logout-btn:hover {
            background-color: #c82333;
            /* Darker red on hover */
        }



        .add-task-btn:hover {
            background-color: #45a049;
            /* Darker green on hover */
        }

        /* Dropdown Menu Styling */
        .menu-actions {
            position: relative;
            /* Position relative to the project item */
            display: inline-block;
            /* Inline block for layout */
        }

        .menu-toggle {
            background: none;
            /* No background */
            border: none;
            /* No border */
            font-size: 16px;
            /* Font size */
            cursor: pointer;
            /* Pointer cursor */
            color: #007bff;
            /* Color for the toggle button */
        }

        .dropdown-menu {
            display: none;
            /* Hidden by default */
            position: absolute;
            /* Absolute positioning */
            top: 0;
            /* Align to the top of the project item */
            right: -120px;
            /* Position to the right of the project item */
            background-color: #ffffff;
            /* White background */
            border: 1px solid #ccc;
            /* Light border */
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            /* Shadow effect */
            z-index: 10;
            /* Sit on top */
            min-width: 120px;
            /* Minimum width */
            border-radius: 4px;
            /* Rounded corners */
            padding: 10px 0;
            /* Padding around the menu */
        }

        .dropdown-menu button {
            width: 100%;
            /* Full width */
            padding: 8px 10px;
            /* Padding inside button */
            border: none;
            /* No border */
            background: none;
            /* No background */
            text-align: left;
            /* Align text to the left */
            cursor: pointer;
            /* Pointer cursor */
            color: #333;
            /* Text color */
            transition: background-color 0.3s;
            /* Smooth transition */
        }

        .dropdown-menu button:hover {
            background-color: #f0f0f0;
            /* Light gray on hover */
        }

        /* Optional: Add a transition effect for the dropdown */
        .dropdown-menu.show {
            display: block;
            /* Show the dropdown */
            animation: fadeIn 0.2s;
            /* Fade-in effect */
        }

        .task-search-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .task-search-form input[type="text"] {
            flex: 1;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .task-search-form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <aside class="sidebar">
            <h2>Projects</h2>
            <h3>Welcome,
                <?php echo htmlspecialchars($nama_user); ?>!
            </h3>
            <form method="GET" class="task-search-form" style="margin-bottom: 20px; display: flex;">
                <div class="search-wrapper" style="position: relative; flex: 1;">
                    <input type="text" name="search" placeholder="🔍 Search tasks..."
                        class="search-input"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                        style="padding-right: 30px; width: 100%; box-sizing: border-box;">
                    
                    <?php if (!empty($_GET['search'])): ?>
                        <span class="reset-search" title="Reset search" onclick="resetSearch()" 
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: red; font-weight: bold;">
                            ✕
                        </span>
                    <?php endif; ?>
                </div>
                <button type="submit">
                    Search
                </button>
            </form>
            <ul class="menu" id="project-list">
                <li class="menu-item">
                    <div class="project-item">
                        <span class="project-button <?= $project_id === 0 ? 'active' : '' ?>"
                            onclick="loadAllProjects()">All Projects</span>
                    </div>
                </li>
                <?php while ($project = $resultProjects->fetch_assoc()) { ?>
                    <li class="menu-item">
                        <div class="project-item">
                            <span class="project-button <?= $project_id == $project['id'] ? 'active' : '' ?>"
                                onclick="loadProjectTasks(<?= $project['id']; ?>)">
                                <?= htmlspecialchars($project['name']); ?>
                            </span>
                            <div class="menu-actions">
                                <button class="menu-toggle" onclick="toggleDropdown(<?= $project['id']; ?>)">⋮</button>
                                <div class="dropdown-menu" id="dropdown-<?= $project['id']; ?>" style="display: none;">
                                    <button
                                        onclick="openEditProjectModal(<?= $project['id']; ?>, '<?= htmlspecialchars($project['name']); ?>')">Edit</button>
                                    <button onclick="confirmDeleteProject(<?= $project['id']; ?>)">Delete</button>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ul>   
            <button class="add-project-btn" onclick="openProjectModal()">+ Add Project</button>
            <!-- Logout Button -->
            <?php if (isset($_SESSION['user_id'])) { ?>
                <form method="POST" action="logout.php">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            <?php } ?>
        </aside>
        <main class="content">
            <h1>Tasks for
                <?= htmlspecialchars($projectName ?? "Unknown Project"); ?>
            </h1>
            <form method="GET" class="priority-filter-form">
                <label for="priority">Filter by Priority:</label>
                <select name="priority" id="priority" onchange="this.form.submit()">
                    <option value="">-- All --</option>
                    <option value="High" <?= ($_GET['priority'] ?? '') === 'High' ? 'selected' : '' ?>>High</option>
                    <option value="Medium" <?= ($_GET['priority'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= ($_GET['priority'] ?? '') === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>

                <input type="hidden" name="project_id" value="<?= (int) $project_id ?>">
            </form>
            <div class="task-actions-wrapper">
            <?php if ($project_id !== 0) { ?>
                <button class="add-task-btn" onclick="openTaskModal()">+ Add Task</button>
            <?php } ?>
            </div>
            <div class="task-list">
                    <?php if ($resultTasks && $resultTasks->num_rows > 0) { ?>
                        <?php while ($task = $resultTasks->fetch_assoc()) {
                            // Map status code to string and class
                            $status = '';
                            $statusClass = '';
                            switch ($task['is_completed']) {
                                case 0:
                                    $status = 'Pending';
                                    $statusClass = 'pending';
                                    break;
                                case 1:
                                    $status = 'In Progress';
                                    $statusClass = 'in-progress';
                                    break;
                                case 2:
                                    $status = 'Completed';
                                    $statusClass = 'completed';
                                    break;
                            }
                            ?>
                            <div class="task-item">
                                <div class="task-header">
                                    <div class="task-name <?= $task['is_completed'] == 2 ? 'completed-task' : ''; ?>">
                                        <a href="index.php?project_id=<?= $task['project_id']; ?>" style="text-decoration: none; color: inherit;">
                                            <?= htmlspecialchars($task['name']); ?>
                                        </a>
                                    </div>
                                    <div class="task-status <?= $statusClass; ?>">
                                        <?= htmlspecialchars($status); ?>
                                    </div>
                                </div>

                                <div class="task-meta">
                                    <span class="task-priority">Priority: <?= htmlspecialchars($task['priority']); ?></span>
                                    <span class="task-due-date">
                                        <?= $task['is_completed'] == 2 ? 'Completed On:' : 'Due Date:' ?>
                                        <?= htmlspecialchars(
                                            $task['is_completed'] == 2
                                            ? ($task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'Unknown')
                                            : ($task['due_date'] ? date('Y-m-d', strtotime($task['due_date'])) : 'Not Set')
                                        ); ?>
                                    </span>
                                </div>
                                <div class="task-actions">
                                    <?php if ($project_id !== 0): ?>
                                        <?php if ($task['is_completed'] == 0): ?>
                                            <!-- Task masih pending -->
                                            <button onclick="markTaskAsInProgress(<?= $task['id']; ?>)">Mark In Progress</button>
                                        <?php elseif ($task['is_completed'] == 1): ?>
                                            <!-- Task sedang dikerjakan -->
                                            <button onclick="markTaskAsCompleted(<?= $task['id']; ?>)">Mark as Completed</button>
                                        <?php endif; ?>
                                        
                                        <!-- Tombol Delete selalu muncul -->
                                        <button onclick="confirmDeleteTask(<?= (int) $task['id']; ?>, '<?= htmlspecialchars(addslashes($task['name'])); ?>')">Delete</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p>No tasks available</p>
                    <?php } ?>
                </div>
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                <script>
                Swal.fire({
                    title: 'Terhapus!',
                    text: 'Task berhasil dihapus.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                </script>
                <?php endif; ?>
        </main>
    </div>
    <!-- Edit Project Modal -->
    <div id="editProjectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditProjectModal()">&times;</span>
            <h2>Edit Project</h2>
            <form id="editProjectForm" method="POST" action="edit_project.php">
                <input type="hidden" name="project_id" id="editProjectId">
                <label for="projectName">Project Name:</label>
                <input type="text" name="project_name" id="editProjectName" required>
                <button type="submit" class="btn">Save Changes</button>
            </form>
        </div>
    </div>
    <!-- Modal for Adding Project -->
    <div id="project-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Project</h3>
                <span class="close-btn" onclick="closeProjectModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="add-project-form" method="POST">
                    <label for="project_name">Project Name:</label>
                    <input type="text" name="project_name" id="project_name" placeholder="Enter project name" required>
                    <button type="submit" class="submit-btn">Add Project</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal for adding task -->
    <div class="modal" id="task-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Task</h2>
                <button class="close-btn" onclick="closeTaskModal()">X</button>
            </div>
            <div class="modal-body">
                <input type="text" id="task-name" placeholder="Task Name" required>
                <select id="task-status" required>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                </select>
                <select id="task-priority" required>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
                <input type="date" id="task-due-date" required>
                <div class="modal-footer">
                    <button class="submit-btn" onclick="addTask()">Save Task</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for Editing Product -->
    <div id="edit-product-modal" class="modal">
        <div class="edit-product-modal-content">
            <div class="edit-product-modal-header">
                <h3>Edit Product</h3>
                <span class="close-btn" onclick="closeEditProductModal()">&times;</span>
            </div>
            <div class="edit-product-modal-body">
                <form id="edit-product-form" method="POST" onsubmit="event.preventDefault(); confirmEditProduct();">
                    <label for="edit-product_name">Product Name:</label>
                    <input type="text" name="edit-product_name" id="edit-product_name" required>

                    <label for="edit-product_price">Price:</label>
                    <input type="number" name="edit-product_price" id="edit-product_price" required>

                    <label for="edit-product_description">Description:</label>
                    <textarea name="edit-product_description" id="edit-product_description" required></textarea>

                    <input type="hidden" name="product_id" id="edit-product_id">

                    <div class="edit-product-modal-footer">
                        <button type="submit" class="edit-product-submit-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function openEditProductModal(productId, productName, productPrice, productDescription) {
            document.getElementById('edit-product_name').value = productName;
            document.getElementById('edit-product_price').value = productPrice;
            document.getElementById('edit-product_description').value = productDescription;
            document.getElementById('edit-product_id').value = productId;
            document.getElementById('edit-product-modal').style.display = 'block';
        }

        function closeEditProductModal() {
            document.getElementById('edit-product-modal').style.display = 'none';
        }

        function confirmEditProduct() {
            const productId = document.getElementById('edit-product_id').value;
            const productName = document.getElementById('edit-product_name').value;
            const productPrice = document.getElementById('edit-product_price').value;
            const productDescription = document.getElementById('edit-product_description').value;

            const formData = new FormData();
            formData.append('edit-product_name', productName);
            formData.append('edit-product_price', productPrice);
            formData.append('edit-product_description', productDescription);
            formData.append('product_id', productId);

            fetch('index.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json()).then(result => {
                if (result.success) {
                    alert('Product updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating product: ' + result.message);
                }
            });
        }
        function confirmDeleteProject(projectId) {
            if (confirm("Are you sure you want to delete this project?")) {
                window.location.href = `delete_project.php?project_id=${projectId}`;
            }
        }

        function openEditProjectModal(projectId, projectName) {
            document.getElementById('edit-project-id').value = projectId;
            document.getElementById('edit-project-name').value = projectName;
            document.getElementById('editProjectModal').style.display = 'block';
        }

        function closeEditProjectModal() {
            document.getElementById('editProjectModal').style.display = 'none';
        }

        function openTaskModal() {
            document.getElementById('task-modal').style.display = 'block';
        }

        function closeTaskModal() {
            document.getElementById('task-modal').style.display = 'none';
        }

        // Open the Edit Task Modal and populate the fields with current task data
        function openEditTaskModal(taskId, taskName, taskStatus, taskPriority, taskDueDate) {
            document.getElementById('edit-task_name').value = taskName;
            document.getElementById('edit-status').value = taskStatus;
            document.getElementById('edit-priority').value = taskPriority;
            document.getElementById('edit-due_date').value = taskDueDate;
            document.getElementById('edit-task_id').value = taskId;
            document.getElementById('edit-task-modal').style.display = 'block';
        }

        function closeEditTaskModal() {
            document.getElementById('edit-task-modal').style.display = 'none';
        }

        // Handle task deletion
        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                // Make a POST request to delete the task
                window.location.href = '../function/delete_task.php?task_id=' + taskId;
            }
        }

        function addTask() {
            const taskName = document.getElementById('task-name').value;
            const taskStatus = document.getElementById('task-status').value;
            const taskPriority = document.getElementById('task-priority').value;
            const taskDueDate = document.getElementById('task-due-date').value;
            const projectId = <?= $project_id ?>;  // Ensure this is dynamically passed from PHP

            // Validation to ensure task name is not empty
            if (taskName.trim() === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Task name cannot be empty.',
                });
                return;
            }

            // AJAX call to send the task data to the server
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Task added successfully!',
                        }).then(() => {
                            location.reload();  // Reload to update task list
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error adding task: ' + (response.message || 'Something went wrong!'),
                        });
                    }
                }
            };
            xhr.send(`task_name=${taskName}&status=${taskStatus}&priority=${taskPriority}&due_date=${taskDueDate}&project_id=${projectId}`);
        }

        // Confirm before editing the task with SweetAlert2
        function confirmEditTask() {
            const taskId = document.getElementById('edit-task_id').value;
            const taskName = document.getElementById('edit-task_name').value;
            const taskStatus = document.getElementById('edit-status').value;
            const taskPriority = document.getElementById('edit-priority').value;
            const taskDueDate = document.getElementById('edit-due_date').value;

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to edit this task?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save changes!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the edit form if confirmed
                    const formData = new FormData();
                    formData.append('edit-task_name', taskName);
                    formData.append('edit-status', taskStatus);
                    formData.append('edit-priority', taskPriority);
                    formData.append('edit-due_date', taskDueDate);
                    formData.append('task_id', taskId);

                    fetch('index.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.json()).then(result => {
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Task updated successfully!',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();  // Reload to update task list
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error updating task',
                                text: result.message || 'Something went wrong!',
                            });
                        }
                    });
                }
            });
        }

        // Open the Add Project Modal
        function openProjectModal() {
            document.getElementById('project-modal').style.display = 'block';
        }

        // Close the Add Project Modal
        function closeProjectModal() {
            document.getElementById('project-modal').style.display = 'none';
        }

        // Add project functionality
        function addProject() {
            const projectName = document.getElementById('project_name').value;

            // Validation to ensure project name is not empty
            if (!projectName.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Project name cannot be empty.',
                });
                return;
            }

            const data = new FormData();
            data.append('project_name', projectName);

            fetch('index.php', {
                method: 'POST',
                body: data
            }).then(response => response.json()).then(result => {
                if (result.success) {
                    window.location.href = 'index.php?project_id=' + result.project_id;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'Failed to add project',
                    });
                }
            });
        }

        function toggleDropdown(projectId) {
            const dropdown = document.getElementById(`dropdown-${projectId}`);
            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            } else {
                dropdown.style.display = "block";
            }
        }

        // Optional: Close dropdown when clicking outside
        document.addEventListener("click", function (event) {
            const dropdowns = document.querySelectorAll(".dropdown-menu");
            dropdowns.forEach((dropdown) => {
                if (!dropdown.contains(event.target) && !event.target.classList.contains("menu-toggle")) {
                    dropdown.style.display = "none";
                }
            });
        });

        function editProject(projectId, projectName) {
            document.getElementById('edit_project_id').value = projectId;
            document.getElementById('new_project_name').value = projectName;
            $('#editModal').modal('show');
        }

        function deleteProject(projectId) {
            if (confirm("Are you sure you want to delete this project?")) {
                document.getElementById('delete_project_id').value = projectId;
                document.getElementById('deleteForm').submit();
            }
        }

        function toggleDropdown(projectId) {
            var dropdown = document.getElementById('dropdown-' + projectId);
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
        function openEditProjectModal(projectId, projectName) {
            document.getElementById('editProjectId').value = projectId;
            document.getElementById('editProjectName').value = projectName;
            document.getElementById('editProjectModal').style.display = 'block';
        }

        function closeEditProjectModal() {
            document.getElementById('editProjectModal').style.display = 'none';
        }
        function confirmDeleteProject(projectId) {
            if (confirm("Are you sure you want to delete this project? This action cannot be undone.")) {
                // Redirect to the delete_project.php script with the project ID
                window.location.href = `delete_project.php?project_id=${projectId}`;
            }
        }

        function loadAllProjects() {
            window.location.href = 'index.php'; // Load all projects
        }

        function loadProjectTasks(projectId) {
            window.location.href = 'index.php?project_id=' + projectId; // Load specific project tasks
        }

        function markTaskAsCompleted(taskId) {
            if (confirm('Are you sure you want to mark this task as completed?')) {
                const formData = new FormData();
                formData.append('task_id', taskId);
                formData.append('edit-status', 2); // 2 for completed status

                fetch('index.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(result => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Task marked as completed!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error marking task',
                            text: result.message || 'Something went wrong!',
                        });
                    }
                });
            }
        }

        function markTaskAsInProgress(taskId) {
            if (confirm('Are you sure you want to mark this task as in progress?')) {
                const formData = new FormData();
                formData.append('task_id', taskId);
                formData.append('edit-status', 1); // 2 for completed status

                fetch('index.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(result => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Task marked as completed!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error marking task',
                            text: result.message || 'Something went wrong!',
                        });
                    }
                });
            }
        }
        

        function confirmDeleteTask(taskId, taskName) {
            Swal.fire({
                title: 'Hapus Task?',
                text: `Yakin ingin menghapus task "${taskName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect ke PHP delete handler
                    window.location.href = 'delete_task.php?task_id=' + taskId;
                }
            });
        }

        function resetSearch() {
        // Clear the search input
        document.querySelector('.search-input').value = '';
        
        // Redirect to the same page without search parameters
        window.location.href = 'index.php?project_id=<?= (int) $project_id ?>';
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>
