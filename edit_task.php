<?php
session_start();
include 'includes/header.php';
include 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get task data
$task = $conn->query("SELECT * FROM tasks WHERE id='$task_id' AND user_id='$user_id'")->fetch_assoc();

if (!$task) {
    header("Location: dashboard.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task'])) {
    $task_name = $conn->real_escape_string($_POST['task_name']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $status = $conn->real_escape_string($_POST['status']);
    $due_date = !empty($_POST['due_date']) ? "'" . $conn->real_escape_string($_POST['due_date']) . "'" : "NULL";
    
    $update_query = "UPDATE tasks SET 
        task='$task_name', 
        priority='$priority', 
        status='$status', 
        due_date=$due_date 
        WHERE id='$task_id' AND user_id='$user_id'";
    
    if ($conn->query($update_query)) {
        $_SESSION['success_message'] = "Task updated successfully!";
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Error updating task: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task | To-Do List App</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Flatpickr for date input -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<style>
    /* Edit Task Page Styles */
    .edit-task-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin: 20px 0;
    }

    .edit-task-container h2 {
        color: #333;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .task-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .task-form .form-group {
        margin-bottom: 20px;
    }

    .task-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }

    .task-form input[type="text"],
    .task-form select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .task-form input:focus,
    .task-form select:focus {
        border-color: #4a90e2;
        outline: none;
    }

    .form-row {
        display: flex;
        gap: 20px;
    }

    .form-row .form-group {
        flex: 1;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
    }

    .btn-secondary {
        background: #f5f5f5;
        color: #333;
        border: none;
        padding: 12px 25px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
    }

    .btn-primary {
        background: #4a90e2;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary:hover {
        background: #3a7bc8;
    }

    .alert {
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background: #ffebee;
        color: #c62828;
        border-left: 4px solid #c62828;
    }

    /* Date Picker Customization */
    .flatpickr-input {
        background: #fff !important;
    }

    /* Dark Mode Styles */
    body.dark-mode .edit-task-container {
        background: #2d2d2d;
        color: #eee;
    }

    body.dark-mode .edit-task-container h2 {
        color: #eee;
    }

    body.dark-mode .task-form input,
    body.dark-mode .task-form select {
        background: #333;
        border-color: #444;
        color: #eee;
    }

    body.dark-mode .btn-secondary {
        background: #444;
        color: #eee;
    }

    body.dark-mode .btn-secondary:hover {
        background: #555;
    }
</style>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>To-Do App</h3>
            <p>Manage your tasks</p>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php"><i class="fas fa-tasks"></i> <span>Tasks</span></a>
            </li>
            <li>
                <a href="calendar.php"><i class="fas fa-calendar-alt"></i> <span>Calendar</span></a>
            </li>
            <li>
                <a href="profile.php"><i class="fas fa-user"></i> <span>Profile</span></a>
            </li>
            <li>
                <!-- <a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a> -->
            </li>
            <li>
                <a href="javascript:void(0)" onclick="confirmLogout()" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> 
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>
            <div class="user-info">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=random" alt="User">
                <div class="user-details">
                    <h4><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></h4>
                    <p>Member</p>
                </div>
            </div>
        </div>

        <!-- Edit Task Container -->
        <div class="edit-task-container">
            <h2><i class="fas fa-edit"></i> Edit Task</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form action="edit_task.php?id=<?= $task_id ?>" method="POST" class="task-form">
                <div class="form-group">
                    <label for="task_name">Task Name</label>
                    <input type="text" id="task_name" name="task_name" value="<?= htmlspecialchars($task['task']) ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" required>
                            <option value="low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="due_date">Due Date (optional)</label>
                    <input type="text" id="due_date" name="due_date" class="datepicker" 
                           value="<?= $task['due_date'] ? date('Y-m-d H:i', strtotime($task['due_date'])) : '' ?>">
                </div>
                
                <div class="form-actions">
                    <a href="dashboard.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" name="update_task" class="btn-primary">
                        <i class="fas fa-save"></i> Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Initialize date picker
        flatpickr(".datepicker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today"
        });

        // Dark Mode Toggle (same as dashboard)
        const darkModeToggle = document.createElement('button');
        darkModeToggle.className = 'dark-mode-toggle';
        darkModeToggle.id = 'darkModeToggle';
        darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        document.body.appendChild(darkModeToggle);

        const body = document.body;
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });

        // Logout confirmation (same as dashboard)
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: "Are you sure you want to logout?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Logging Out',
                        html: 'Please wait...',
                        timer: 1500,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        willClose: () => {
                            window.location.href = 'logout.php';
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>