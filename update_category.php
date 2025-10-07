<?php
header('Content-Type: application/json');
require "dbconn.php";  // Your mysqli connection ($conn)

// Decode JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Required fields to identify the category
$required_fields = ['category_id', 'user_id'];

foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required parameter: $field"]);
        exit();
    }
}

$category_id = intval($input['category_id']);
$user_id = intval($input['user_id']);

// Optional fields to update
$fields = ['name', 'description']; // assuming your categories table has these fields; adjust as needed

$update_fields = [];
$params = [];
$types = "";

// Collect fields that are provided in input for update
foreach ($fields as $field) {
    if (isset($input[$field])) {
        $update_fields[] = "$field = ?";
        $params[] = $input[$field];
        $types .= "s";  // assuming string fields; change if needed
    }
}

if (empty($update_fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'No fields provided to update']);
    exit();
}

// Add category_id and user_id for WHERE clause
$params[] = $category_id;
$params[] = $user_id;
$types .= "ii";

$sql = "UPDATE categories SET " . implode(", ", $update_fields) . " WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
    exit();
}

// Bind parameters dynamically (must pass references for bind_param)
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
            'message' => 'Category updated successfully',
            'affected_rows' => $stmt->affected_rows
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No category updated. Either it does not exist or data is unchanged.'
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update category: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
