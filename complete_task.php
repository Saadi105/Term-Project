<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Task ID missing']);
    exit;
}

$task_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Update the task status
$stmt = $conn->prepare("UPDATE tasks SET status='completed' WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $task_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$stmt->close();
$conn->close();
?>