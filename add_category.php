<?php
header('Content-Type: application/json');
require "dbconn.php";

$input = json_decode(file_get_contents("php://input"), true);

if (isset($input['user_id'], $input['name'])) {
    $user_id = intval($input['user_id']);
    $name = trim($input['name']);

    if ($user_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid user_id']);
        exit();
    }

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Category name cannot be empty']);
        exit();
    }

    if (strlen($name) > 50) {
        http_response_code(400);
        echo json_encode(['error' => 'Category name too long (max 50 chars)']);
        exit();
    }

    $checkStmt = $conn->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ?");
    if (!$checkStmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit();
    }

    $checkStmt->bind_param("is", $user_id, $name);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'Category name already exists for this user']);
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();

    $stmt = $conn->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("is", $user_id, $name);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Category added successfully',
            'category_id' => $stmt->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add category: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: user_id, name']);
}

$conn->close();
