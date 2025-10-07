<?php
header('Content-Type: application/json');
require "dbconn.php";  // Your mysqli connection ($conn)

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validate required 'user_id' parameter
if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: user_id']);
    exit();
}

$user_id = intval($input['user_id']);

// Optional filters for status, category, date range
$status_filter = isset($input['status']) ? $input['status'] : null;
$category_filter = isset($input['category']) ? $input['category'] : null;
$start_date = isset($input['start_date']) ? $input['start_date'] : null;
$end_date = isset($input['end_date']) ? $input['end_date'] : null;

// Validate date formats if provided
$date_pattern = '/^\d{4}-\d{2}-\d{2}$/';
if ($start_date && !preg_match($date_pattern, $start_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'start_date must be in YYYY-MM-DD format']);
    exit();
}
if ($end_date && !preg_match($date_pattern, $end_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'end_date must be in YYYY-MM-DD format']);
    exit();
}

try {
    $sql = "SELECT id, category, amount, start_date, end_date, status, created_at FROM budget WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";

    if ($status_filter) {
        $sql .= " AND status = ?";
        $types .= "s";
        $params[] = $status_filter;
    }
    if ($category_filter) {
        $sql .= " AND category = ?";
        $types .= "s";
        $params[] = $category_filter;
    }
    if ($start_date) {
        $sql .= " AND start_date >= ?";
        $types .= "s";
        $params[] = $start_date;
    }
    if ($end_date) {
        $sql .= " AND end_date <= ?";
        $types .= "s";
        $params[] = $end_date;
    }

    $sql .= " ORDER BY start_date DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // bind parameters dynamically
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $result = $stmt->get_result();
    $budgets = [];

    while ($row = $result->fetch_assoc()) {
        $budgets[] = $row;
    }

    echo json_encode([
        'success' => true,
        'budgets' => $budgets
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
