<?php
header('Content-Type: application/json');
require "dbconn.php";  // Make sure this file initializes your $conn as mysqli connection

// Get the POSTed JSON data
$input = json_decode(file_get_contents("php://input"), true);

// Check if user_id is provided
if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: user_id']);
    exit();
}

$user_id = intval($input['user_id']);

try {
    // Prepare the SQL query
    $stmt = $conn->prepare("SELECT id, name, target_amount, saved_amount, deadline, status FROM goals WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $goals = [];

    while ($row = $result->fetch_assoc()) {
        $goals[] = $row;
    }

    echo json_encode([
        'success' => true,
        'goals' => $goals
    ]);

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
