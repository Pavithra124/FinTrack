<?php
header('Content-Type: application/json');
require "dbconn.php";  // your mysqli connection ($conn)

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validate required parameters
if (!isset($input['user_id'], $input['notification_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: user_id and notification_id']);
    exit();
}

$user_id = intval($input['user_id']);
$notification_id = intval($input['notification_id']);

try {
    // Check if notification exists and belongs to user
    $checkStmt = $conn->prepare("SELECT id FROM notifications WHERE id = ? AND user_id = ?");
    if (!$checkStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $checkStmt->bind_param("ii", $notification_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Notification not found or does not belong to this user']);
        exit();
    }
    $checkStmt->close();

    // Mark the notification as read (dismiss)
    $updateStmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    if (!$updateStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $updateStmt->bind_param("ii", $notification_id, $user_id);

    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification dismissed (marked as read) successfully.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to dismiss notification: ' . $updateStmt->error]);
    }

    $updateStmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
