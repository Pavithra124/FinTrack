<?php
header('Content-Type: application/json');
require "dbconn.php";  // your mysqli connection ($conn)

// Get POSTed JSON
$input = json_decode(file_get_contents("php://input"), true);

// Validate inputs
$required_fields = ['user_id', 'type', 'category', 'amount', 'date'];

foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required parameter: $field"]);
        exit();
    }
}

$user_id = intval($input['user_id']);
$category = $input['category'];
$amount = floatval($input['amount']);
$date = $input['date'];
$description = isset($input['description']) ? $input['description'] : '';

// Validate type
if (!in_array($type, ['income', 'expense'])) {
    http_response_code(400);
    echo json_encode(['error' => "Invalid type. Must be 'income' or 'expense'"]);
    exit();
}

// Validate amount
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => "Amount must be greater than zero"]);
    exit();
}

// Validate date (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => "Date must be in YYYY-MM-DD format"]);
    exit();
}

// Insert into cash_flow table
$stmt = $conn->prepare("INSERT INTO cash_flow (user_id, type, category, amount, date, description) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("issdss", $user_id, $type, $category, $amount, $date, $description);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Cash flow entry added successfully',
        'cash_flow_id' => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to add cash flow: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
