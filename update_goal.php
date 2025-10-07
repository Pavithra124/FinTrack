<?php
header('Content-Type: application/json');
require "dbconn.php"; // Your mysqli connection ($conn)

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Required to identify which goal to update
if (!isset($input['goal_id'], $input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: goal_id and user_id']);
    exit();
}

$goal_id = intval($input['goal_id']);
$user_id = intval($input['user_id']);

// Fields that can be updated
$fields = ['name', 'target_amount', 'saved_amount', 'deadline', 'status'];

$update_fields = [];
$params = [];
$types = "";

// Validate and prepare update fields
foreach ($fields as $field) {
    if (isset($input[$field])) {
        // Validation per field
        if (in_array($field, ['target_amount', 'saved_amount'])) {
            if (!is_numeric($input[$field]) || floatval($input[$field]) < 0) {
                http_response_code(400);
                echo json_encode(['error' => "$field must be a non-negative number"]);
                exit();
            }
            $params[] = floatval($input[$field]);
            $types .= "d";
        } else if ($field === 'deadline') {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "deadline must be in YYYY-MM-DD format"]);
                exit();
            }
            $params[] = $input[$field];
            $types .= "s";
        } else {
            $params[] = $input[$field];
            $types .= "s";
        }
        $update_fields[] = "$field = ?";
    }
}

if (empty($update_fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'No valid fields provided to update']);
    exit();
}

// Add goal_id and user_id for WHERE clause
$params[] = $goal_id;
$params[] = $user_id;
$types .= "ii";

$sql = "UPDATE goals SET " . implode(", ", $update_fields) . " WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
    exit();
}

// bind_param requires references
$tmp = [];
foreach ($params as $key => $value) {
    $tmp[$key] = &$params[$key];
}
array_unshift($tmp, $types);
call_user_func_array([$stmt, 'bind_param'], $tmp);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Goal updated successfully',
            'affected_rows' => $stmt->affected_rows
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No goal record updated. It may not exist or the data is unchanged.'
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update goal: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
