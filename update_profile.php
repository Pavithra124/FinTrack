<?php
header('Content-Type: application/json');
require "dbconn.php";  // Your mysqli connection ($conn)

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Required parameter: user_id to identify the user
if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: user_id']);
    exit();
}

$user_id = intval($input['user_id']);

// Allowed fields to update (adjust to your actual columns)
$fields = ['email', 'name'];  // Remove username and phone

$update_fields = [];
$params = [];
$types = "";

// Validate and prepare update fields
foreach ($fields as $field) {
    if (isset($input[$field])) {
        if ($field === 'email' && !filter_var($input[$field], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            exit();
        }
        $update_fields[] = "$field = ?";
        $params[] = $input[$field];
        $types .= "s";  // assume string type
    }
}

if (empty($update_fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'No valid fields provided to update']);
    exit();
}

// Add user_id for WHERE clause
$params[] = $user_id;
$types .= "i";

$sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
    exit();
}

// Bind params dynamically using references
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
            'message' => 'Profile updated successfully',
            'affected_rows' => $stmt->affected_rows
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No profile updated. Either user does not exist or data is unchanged.'
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update profile: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
