<?php
header('Content-Type: application/json');
require "dbconn.php";

// Read raw JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Check if user_id is provided
if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: user_id']);
    exit();
}

$user_id = intval($input['user_id']);

// Query to fetch categories
$stmt = $conn->prepare("SELECT id, name FROM categories WHERE user_id = ? OR user_id IS NULL");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode([
    'success' => true,
    'categories' => $categories
]);

$stmt->close();
$conn->close();
