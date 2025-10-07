<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "dbconn.php"; // mysqli connection ($conn)

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Required fields validation
if (empty($input['user_id']) || !isset($input['primary_salary']) || !isset($input['additional_income']) ||
    !isset($input['amount']) || empty($input['month_year']) || !isset($input['budget'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields: user_id, primary_salary, additional_income, amount, month_year, budget"]);
    exit;
}

$user_id = intval($input['user_id']);
$primary_salary = floatval($input['primary_salary']);
$additional_income = floatval($input['additional_income']);
$amount = floatval($input['amount']);
$month_year = $input['month_year'];
$budget = floatval($input['budget']);

// Validate month_year format YYYY-MM
if (!preg_match('/^\d{4}-\d{2}$/', $month_year)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid month_year format. Expected YYYY-MM"]);
    exit;
}

// Check user exists for foreign key integrity
$user_check = $conn->prepare("SELECT id FROM users WHERE id = ?");
$user_check->bind_param("i", $user_id);
$user_check->execute();
$user_check->store_result();
if ($user_check->num_rows === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid user_id. No such user exists."]);
    $user_check->close();
    $conn->close();
    exit;
}
$user_check->close();

// Insert query
$sql = "INSERT INTO budgets (user_id, primary_salary, additional_income, amount, month_year, budget) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["error" => "Database prepare failed: " . $conn->error]);
    $conn->close();
    exit;
}

// Bind parameters with error check
if (!$stmt->bind_param("idddsd", $user_id, $primary_salary, $additional_income, $amount, $month_year, $budget)) {
    http_response_code(500);
    echo json_encode(["error" => "Binding parameters failed: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Budget and income data added successfully",
        "insert_id" => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Database insert failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
