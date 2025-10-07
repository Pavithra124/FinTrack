<?php
header('Content-Type: application/json');
require "dbconn.php";  // Your mysqli connection ($conn)

// Get POSTed JSON data
$input = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: user_id']);
    exit();
}

$user_id = intval($input['user_id']);

try {
    // Prepare the SQL query to fetch notifications for the user
    // Optionally, order by newest first
    $stmt = $conn->prepare("SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $notifications = [];

    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
