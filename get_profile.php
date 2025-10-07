<?php
header('Content-Type: application/json');
require "dbconn.php";  // Your mysqli connection ($conn)

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validate required parameter: user_id
if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: user_id']);
    exit();
}

$user_id = intval($input['user_id']);

try {
    $stmt = $conn->prepare("SELECT id,  email, phone, created_at FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit();
    }

    $profile = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'profile' => $profile
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
