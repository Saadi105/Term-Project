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

// Get tasks with due dates
$tasks = [];
$result = $conn->query("SELECT id, task, due_date, priority, status FROM tasks WHERE user_id='$user_id' AND due_date IS NOT NULL");
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | To-Do List App</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        .fc-event {
            cursor: pointer;
            font-size: 0.85em;
            padding: 2px 4px;
        }
        .priority-high { border-left: 3px solid #dc3545; }
        .priority-medium { border-left: 3px solid #ffc107; }
        .priority-low { border-left: 3px solid #28a745; }
        .completed-task { opacity: 0.7; text-decoration: line-through; }
        #calendar {
            max-width: 1100px;
            margin: 30px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
    </style>
</head>
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
            <li class="active">
                <a href="calendar.php"><i class="fas fa-calendar-alt"></i> <span>Calendar</span></a>
            </li>
            <li>
                <a href="profile.php"><i class="fas fa-user"></i> <span>Profile</span></a>
            </li>
            <li>
                <a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a>
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
                <input type="text" placeholder="Search tasks..." id="calendarSearch">
            </div>
            <div class="user-info">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=random" alt="User">
                <div class="user-details">
                    <h4><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></h4>
                    <p>Member</p>
                </div>
            </div>
        </div>

        <!-- Calendar Container -->
        <div class="calendar-container">
            <h2><i class="fas fa-calendar-alt"></i> Task Calendar</h2>
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Task Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalTitle">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Task:</strong> <span id="modalTaskName"></span></p>
                    <p><strong>Due Date:</strong> <span id="modalDueDate"></span></p>
                    <p><strong>Priority:</strong> <span id="modalPriority"></span></p>
                    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="editTaskLink" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Task
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap JS (for modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize calendar
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                events: [
                    <?php foreach ($tasks as $task): ?>
                    {
                        id: '<?= $task['id'] ?>',
                        title: '<?= addslashes($task['task']) ?>',
                        start: '<?= $task['due_date'] ?>',
                        className: 'priority-<?= $task['priority'] ?> <?= $task['status'] === 'completed' ? 'completed-task' : '' ?>',
                        extendedProps: {
                            priority: '<?= $task['priority'] ?>',
                            status: '<?= $task['status'] ?>'
                        }
                    },
                    <?php endforeach; ?>
                ],
                eventClick: function(info) {
                    // Show task details in modal
                    document.getElementById('modalTaskName').textContent = info.event.title;
                    document.getElementById('modalDueDate').textContent = info.event.start.toLocaleString();
                    document.getElementById('modalPriority').textContent = info.event.extendedProps.priority;
                    document.getElementById('modalStatus').textContent = info.event.extendedProps.status;
                    document.getElementById('editTaskLink').href = 'edit_task.php?id=' + info.event.id;
                    
                    // Show modal
                    var modal = new bootstrap.Modal(document.getElementById('taskModal'));
                    modal.show();
                },
                eventDidMount: function(info) {
                    // Add tooltip
                    info.el.title = info.event.title;
                }
            });
            calendar.render();

            // Search functionality
            document.getElementById('calendarSearch').addEventListener('input', function() {
                var searchTerm = this.value.toLowerCase();
                calendar.getEvents().forEach(function(event) {
                    var eventTitle = event.title.toLowerCase();
                    if (eventTitle.includes(searchTerm)) {
                        event.setProp('display', 'auto');
                    } else {
                        event.setProp('display', 'none');
                    }
                });
            });

            // Dark mode toggle (same as dashboard)
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
            window.confirmLogout = function() {
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
            };
        });
    </script>
</body>
</html>