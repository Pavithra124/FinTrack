<?php
header('Content-Type: application/json');
require "dbconn.php";  // Database connection ($conn)

// Get user_id from request (safe check)
$input = json_decode(file_get_contents("php://input"), true);
$user_id = (is_array($input) && isset($input['user_id'])) ? intval($input['user_id']) : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing user_id"]);
    exit;
}

$response = [];
$response['user_id'] = $user_id; // include user_id in response

// Fetch total income
$income_sql = "SELECT IFNULL(SUM(amount), 0) AS total_income 
               FROM cash_flow 
               WHERE user_id = ? AND type = 'income'";
$stmt = $conn->prepare($income_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$income_result = $stmt->get_result()->fetch_assoc();
$response['total_income'] = $income_result['total_income'];

// Fetch total expenses
$expense_sql = "SELECT IFNULL(SUM(amount), 0) AS total_expenses 
                FROM cash_flow 
                WHERE user_id = ? AND type = 'expense'";
$stmt = $conn->prepare($expense_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$expense_result = $stmt->get_result()->fetch_assoc();
$response['total_expenses'] = $expense_result['total_expenses'];

// Fetch recent expenses (last 5)
$recent_sql = "SELECT id, category, amount, date AS expense_date
               FROM cash_flow 
               WHERE user_id = ? AND type = 'expense'
               ORDER BY date DESC 
               LIMIT 5";
$stmt = $conn->prepare($recent_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_result = $stmt->get_result();

$recent_expenses = [];
while ($row = $recent_result->fetch_assoc()) {
    $recent_expenses[] = $row;
}
$response['recent_expenses'] = $recent_expenses;

// Send final JSON response
echo json_encode($response);

$conn->close();
?>
