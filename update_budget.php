<?php
header('Content-Type: application/json');
require "dbconn.php";  // Your mysqli connection ($conn)

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Required fields: budget id and user_id to identify the record
$required_fields = ['id', 'user_id'];

foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required parameter: $field"]);
        exit();
    }
}

$id = intval($input['id']);
$user_id = intval($input['user_id']);

// Optional fields that can be updated
$fields = ['category', 'amount', 'start_date', 'end_date', 'status'];

// Prepare update parts
$update_fields = [];
$params = [];
$types = "";

// Validate and collect only fields which are present in the input
foreach ($fields as $field) {
    if (isset($input[$field])) {
        // Basic validation per field:
        if (($field == 'amount') && (!is_numeric($input[$field]) || floatval($input[$field]) < 0)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid amount: must be a positive number']);
            exit();
        }

        if (($field == 'start_date' || $field == 'end_date') && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "$field must be in YYYY-MM-DD format"]);
            exit();
        }

        // Append update clause and parameters
        $update_fields[] = "$field = ?";
        if ($field == 'amount') {
            $params[] = floatval($input[$field]);
            $types .= "d";
        } else {
            $params[] = $input[$field];
            $types .= "s";
        }
    }
}

if (empty($update_fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'No fields provided to update']);
    exit();
}

// Add id and user_id to params for WHERE clause
$params[] = $id;
$params[] = $user_id;
$types .= "ii";

$sql = "UPDATE budget SET " . implode(", ", $update_fields) . " WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
    exit();
}

// Bind params dynamically using call_user_func_array
// mysqli_stmt::bind_param requires references
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
            'message' => 'Budget updated successfully',
            'affected_rows' => $stmt->affected_rows
        ]);
    } else {
        // No rows updated - possibly wrong id/user or no change in data
        echo json_encode([
            'success' => false,
            'message' => 'No budget record updated. Either it does not exist or data is unchanged.'
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update budget: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
