<?php
header('Content-Type: application/json');
require "dbconn.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: user_id']);
    exit();
}

$user_id = intval($input['user_id']);
$type = isset($input['type']) ? $input['type'] : null;
$start_date = isset($input['start_date']) ? $input['start_date'] : null;
$end_date = isset($input['end_date']) ? $input['end_date'] : null;

$sql = "SELECT id, type, category, amount, date, description FROM cash_flow WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($type) {
    $sql .= " AND type = ?";
    $params[] = $type;
    $types .= "s";
}
if ($start_date) {
    $sql .= " AND date >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if ($end_date) {
    $sql .= " AND date <= ?";
    $params[] = $end_date;
    $types .= "s";
}
$sql .= " ORDER BY date DESC";

// Prepare statement with dynamic parameters
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();
$cash_flows = [];
while ($row = $result->fetch_assoc()) {
    $cash_flows[] = $row;
}
echo json_encode([
    'success' => true,
    'cash_flows' => $cash_flows
]);

$stmt->close();
$conn->close();
