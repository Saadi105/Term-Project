<?php
session_start();
include 'includes/db.php';

if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $user_id = $_SESSION['user_id'];

    $conn->query("INSERT INTO tasks (user_id, task) VALUES ('$user_id', '$task')");
    header("Location: dashboard.php");
}
?>
