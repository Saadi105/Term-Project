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

// Get tasks with priority ordering
$result = $conn->query("SELECT * FROM tasks WHERE user_id='$user_id' ORDER BY 
    CASE priority 
        WHEN 'high' THEN 1 
        WHEN 'medium' THEN 2 
        WHEN 'low' THEN 3 
        ELSE 4 
    END, created_at DESC");

// Get task statistics in a single query
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed
    FROM tasks WHERE user_id='$user_id'")->fetch_assoc();
$total_tasks = $stats['total'];
$completed_tasks = $stats['completed'];
$pending_tasks = $total_tasks - $completed_tasks;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | To-Do List App</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>To-Do App</h3>
            <p>Manage your tasks</p>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="dashboard.php"><i class="fas fa-tasks"></i> <span>Tasks</span></a></li>
            <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> <span>Calendar</span></a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> <span>Profile</span></a></li>
            <!-- <li><a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a></li> -->
            <li><a href="javascript:void(0)" onclick="confirmLogout()" class="logout-link"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search tasks...">
            </div>
            <div class="user-info">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=random" alt="User">
                <div class="user-details">
                    <h4><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></h4>
                    <p>Member</p>
                </div>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <?php
            $cards = [
                ['title' => 'Total Tasks', 'icon' => 'fas fa-tasks', 'value' => $total_tasks, 'text' => 'All your tasks'],
                ['title' => 'Completed', 'icon' => 'fas fa-check-circle', 'value' => $completed_tasks, 'text' => 'Tasks done'],
                ['title' => 'Pending', 'icon' => 'fas fa-clock', 'value' => $pending_tasks, 'text' => 'Tasks remaining']
            ];

            foreach ($cards as $card): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><?= $card['title'] ?></h3>
                        <i class="<?= $card['icon'] ?>"></i>
                    </div>
                    <div class="card-body">
                        <h2><?= $card['value'] ?></h2>
                        <p><?= $card['text'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Task Container -->
        <div class="task-container">
            <h2><i class="fas fa-clipboard-list"></i> Manage Your Tasks</h2>

            <!-- Task Form -->
            <form action="add_task.php" method="POST" class="task-input">
                <input type="text" name="task" placeholder="Enter your task..." required>
                <select name="priority" style="padding: 12px; border-radius: 8px; border: 1px solid #ddd;">
                    <option value="low">Low Priority</option>
                    <option value="medium">Medium Priority</option>
                    <option value="high">High Priority</option>
                </select>
                <button type="submit" name="add_task"><i class="fas fa-plus"></i> Add Task</button>
            </form>

            <!-- Task List -->
            <ul class="task-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="<?= $row['status'] == 'completed' ? 'completed' : '' ?> <?= $row['priority'] ?>-priority">
                        <div>
                            <span class="task-text">
                                <?= htmlspecialchars($row['task']) ?>
                                <span class="priority-badge priority-<?= $row['priority'] ?>">
                                    <?= ucfirst($row['priority']) ?> priority
                                </span>
                            </span>
                            <div class="task-meta">
                                <span><i class="far fa-calendar"></i> <?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                                <?php if ($row['due_date']): ?>
                                    <span><i class="far fa-clock"></i> Due: <?= date('M d, Y', strtotime($row['due_date'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <?php if ($row['status'] != 'completed'): ?>
                                <button class="complete-btn" onclick="completeTask(<?= $row['id'] ?>)">
                                    <i class="fas fa-check"></i> Complete
                                </button>
                            <?php else: ?>
                                <button class="complete-btn" disabled>
                                    <i class="fas fa-check"></i> Completed
                                </button>
                            <?php endif; ?>
                            <a href="edit_task.php?id=<?= $row['id'] ?>">
                                <button class="edit-btn"><i class="fas fa-edit"></i> Edit</button>
                            </a>
                            <button class="delete-btn" onclick="confirmDelete(<?= $row['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;

        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
            darkModeToggle.innerHTML = isDarkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        });

        // Confirmation Dialog Helper
        function showConfirmation(options, callback) {
            Swal.fire({
                title: options.title,
                text: options.text,
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: options.confirmColor || '#3085d6',
                cancelButtonColor: options.cancelColor || '#d33',
                confirmButtonText: options.confirmText || 'Confirm',
                allowOutsideClick: false,
                ...options
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        }

        // Logout Confirmation
        function confirmLogout() {
            showConfirmation({
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                confirmColor: '#3085d6',
                confirmText: 'Yes, logout!'
            }, () => {
                Swal.fire({
                    title: 'Logging Out',
                    html: 'Please wait...',
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: () => Swal.showLoading(),
                    willClose: () => window.location.href = 'logout.php'
                });
            });
        }

        // Complete Task Function
        function completeTask(taskId) {
            Swal.fire({
                title: 'Complete Task?',
                text: "Mark this task as completed?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, complete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('complete_task.php?id=' + taskId, {
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Completed!',
                                    'Your task has been marked as completed.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.error || 'There was a problem completing the task.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'There was a problem completing the task: ' + error.message,
                                'error'
                            );
                        });
                }
            });
        }

        // Delete Confirmation Function
        function confirmDelete(taskId) {
            Swal.fire({
                title: 'Delete Task?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading indicator
                    Swal.showLoading();

                    fetch('deletetask.php?id=' + taskId, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                // Get the actual response text for debugging
                                return response.text().then(text => {
                                    throw new Error(`Server responded with ${response.status}: ${text}`);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            Swal.fire(
                                'Deleted!',
                                'Your task has been deleted.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        })
                        .catch(error => {
                            console.error('Delete error:', error);
                            Swal.fire(
                                'Error!',
                                `Failed to delete task: ${error.message}`,
                                'error'
                            );
                        });
                }
            });
        }
        // Show success messages if needed
        <?php if (isset($_GET['completed']) && $_GET['completed'] == '1'): ?>
            Swal.fire('Task Completed!', 'Your task has been marked as completed.', 'success');
        <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
            Swal.fire('Deleted!', 'Your task has been deleted.', 'success');
        <?php endif; ?>
    </script>

    <style>
        .logout-link {
            color: #dc3545;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .logout-link:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .completed .task-text {
            text-decoration: line-through;
            color: #888;
        }

        .complete-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</body>

</html>