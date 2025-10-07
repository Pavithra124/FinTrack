<?php
header('Content-Type: application/json');
require "dbconn.php";  // Your mysqli connection ($conn)

$input = json_decode(file_get_contents("php://input"), true);

// Required fields
$required_fields = ['merchant_name', 'category', 'amount'];

foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing or empty required parameter: $field"]);
        exit();
    }
}

$merchant_name = $input['merchant_name'];
$category = $input['category'];
$amount = floatval($input['amount']);

// Validate amount
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Amount must be greater than zero']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO receipts (merchant_name, category, amount) VALUES (?, ?, ?)");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ssd", $merchant_name, $category, $amount);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Receipt added successfully',
        'receipt_id' => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to add receipt: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
