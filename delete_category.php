<?php
header('Content-Type: application/json');
require "dbconn.php";  // your mysqli connection ($conn)

$input = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($input['user_id'], $input['category_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: user_id and category_id']);
    exit();
}

$user_id = intval($input['user_id']);
$category_id = intval($input['category_id']);

try {
    // First check if the category exists and belongs to the user
    $checkStmt = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
    if (!$checkStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $checkStmt->bind_param("ii", $category_id, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Category not found or does not belong to this user']);
        exit();
    }

    $checkStmt->close();

    // Delete the category
    $deleteStmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    if (!$deleteStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $deleteStmt->bind_param("ii", $category_id, $user_id);

    if ($deleteStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete category: ' . $deleteStmt->error]);
    }

    $deleteStmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
