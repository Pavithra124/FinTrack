<?php
header('Content-Type: application/json');
require "dbconn.php";  // your mysqli connection ($conn)

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Check required parameters
if (!isset($input['user_id'], $input['goal_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: user_id and goal_id']);
    exit();
}

$user_id = intval($input['user_id']);
$goal_id = intval($input['goal_id']);

try {
    // Check that the goal exists and belongs to user
    $checkStmt = $conn->prepare("SELECT id FROM goals WHERE id = ? AND user_id = ?");
    if (!$checkStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $checkStmt->bind_param("ii", $goal_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Goal not found or does not belong to this user']);
        exit();
    }
    $checkStmt->close();

    // Proceed to delete
    $deleteStmt = $conn->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
    if (!$deleteStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $deleteStmt->bind_param("ii", $goal_id, $user_id);

    if ($deleteStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Goal deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete goal: ' . $deleteStmt->error]);
    }

    $deleteStmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
