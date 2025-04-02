<?php
session_start();
include 'includes/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not logged in', 401);
    }

    // Check if task ID is provided and valid
    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        throw new Exception('Invalid task ID', 400);
    }

    $task_id = (int)$_GET['id'];
    $user_id = (int)$_SESSION['user_id'];

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();

    // Check if any row was actually deleted
    if ($stmt->affected_rows === 0) {
        throw new Exception('Task not found or already deleted', 404);
    }

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Task deleted successfully'
    ]);

} catch (Exception $e) {
    // Error response
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>