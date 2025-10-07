<?php
header('Content-Type: application/json');
require "dbconn.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: id']);
    exit();
}

$id = intval($input['id']);

$stmt = $conn->prepare("DELETE FROM receipts WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => $conn->error]);
    exit();
}
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Receipt deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete receipt: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
