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

// Get user data
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Check if new password is provided
    $password_update = '';
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = "Passwords don't match!";
        } else {
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $password_update = ", password='$hashed_password'";
        }
    }
    
    if (!isset($error)) {
        $update_query = "UPDATE users SET username='$username', email='$email' $password_update WHERE id='$user_id'";
        if ($conn->query($update_query)) {
            $_SESSION['username'] = $username;
            $success = "Profile updated successfully!";
            // Refresh user data
            $user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | To-Do List App</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<style>
    /* Profile Page Styles */
    .profile-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin: 20px 0;
    }

    .profile-container h2 {
        color: #333;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .profile-form .form-group {
        margin-bottom: 20px;
    }

    .profile-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }

    .profile-form input[type="text"],
    .profile-form input[type="email"],
    .profile-form input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .profile-form input:focus {
        border-color: #4a90e2;
        outline: none;
    }

    .profile-stats {
        margin-top: 40px;
        border-top: 1px solid #eee;
        padding-top: 30px;
    }

    .profile-stats h3 {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #444;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .stat-card {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card i {
        font-size: 24px;
        color: #4a90e2;
        margin-bottom: 10px;
    }

    .stat-card span {
        display: block;
        color: #777;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .stat-card strong {
        font-size: 20px;
        color: #333;
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

    .alert-success {
        background: #e8f5e9;
        color: #2e7d32;
        border-left: 4px solid #2e7d32;
    }

    /* Dark Mode Styles */
    body.dark-mode .profile-container,
    body.dark-mode .stat-card {
        background: #2d2d2d;
        color: #eee;
    }

    body.dark-mode .profile-container h2,
    body.dark-mode .stat-card strong {
        color: #eee;
    }

    body.dark-mode .profile-form input {
        background: #333;
        border-color: #444;
        color: #eee;
    }

    body.dark-mode .stat-card span {
        color: #bbb;
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
            <li class="active">
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
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" alt="User">
                <div class="user-details">
                    <h4><?= htmlspecialchars($user['username']) ?></h4>
                    <p>Member</p>
                </div>
            </div>
        </div>

        <!-- Profile Container -->
        <div class="profile-container">
            <h2><i class="fas fa-user"></i> My Profile</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form action="profile.php" method="POST" class="profile-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password (leave blank to keep unchanged)</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                
                <button type="submit" name="update_profile" class="btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
            
            <div class="profile-stats">
                <h3><i class="fas fa-chart-bar"></i> Account Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Member since</span>
                        <strong>
                            <?php 
                            if (isset($user['created_at']) && !empty($user['created_at'])) {
                                echo date('M d, Y', strtotime($user['created_at']));
                            } else {
                                // Either use a default value or show "Not available"
                                echo 'Not available'; 
                                
                                // Or if you want to use the current date as fallback:
                                // echo date('M d, Y');
                            }
                            ?>
                        </strong>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-tasks"></i>
                        <span>Total Tasks</span>
                        <strong><?= $conn->query("SELECT COUNT(*) FROM tasks WHERE user_id='$user_id'")->fetch_row()[0] ?></strong>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <span>Completed Tasks</span>
                        <strong><?= $conn->query("SELECT COUNT(*) FROM tasks WHERE user_id='$user_id' AND status='completed'")->fetch_row()[0] ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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