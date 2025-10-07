<?php
header('Content-Type: application/json');
require "dbconn.php";

// Decode POST JSON
$input = json_decode(file_get_contents("php://input"), true);

// Check for required fields
if (!isset($input['user_id'], $input['name'], $input['target_amount'], $input['deadline'], $input['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: user_id, name, target_amount, deadline, status']);
    exit();
}

// Assign and sanitize
$user_id = intval($input['user_id']);
$name = $input['name'];
$target_amount = floatval($input['target_amount']);
$deadline = $input['deadline'];
$status = $input['status'];

// Optional: Accept saved_amount; if not set, default to zero.
$saved_amount = isset($input['saved_amount']) ? floatval($input['saved_amount']) : 0;

// Validate amounts
if ($target_amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'target_amount must be greater than 0']);
    exit();
}
if ($saved_amount < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'saved_amount cannot be negative']);
    exit();
}

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
    http_response_code(400);
    echo json_encode(['error' => 'deadline must be YYYY-MM-DD']);
    exit();
}

// INSERT INTO goals table
$stmt = $conn->prepare("INSERT INTO goals (user_id, name, target_amount, saved_amount, deadline, status) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit();
}
$stmt->bind_param("isddss", $user_id, $name, $target_amount, $saved_amount, $deadline, $status);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Goal added successfully',
        'goal_id' => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to add goal: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
