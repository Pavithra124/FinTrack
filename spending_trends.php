<?php
header('Content-Type: application/json');
require "dbconn.php";  // Make sure this sets up $conn as a MySQLi connection

$input = json_decode(file_get_contents("php://input"), true);

// Validate required user_id
if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: user_id']);
    exit();
}

$user_id = intval($input['user_id']);

// Optional filters
$start_date = isset($input['start_date']) ? $input['start_date'] : null;
$end_date = isset($input['end_date']) ? $input['end_date'] : null;

// Validate date formats if set
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
    // Build SQL dynamically
    $sql = "SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total_spent
            FROM cash_flow
            WHERE user_id = ? AND type = 'expense'";
    $params = [$user_id];
    $types = "i";

    if ($start_date && $end_date) {
        $sql .= " AND date BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    } elseif ($start_date) {
        $sql .= " AND date >= ?";
        $params[] = $start_date;
        $types .= "s";
    } elseif ($end_date) {
        $sql .= " AND date <= ?";
        $params[] = $end_date;
        $types .= "s";
    }

    $sql .= " GROUP BY month ORDER BY month ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $result = $stmt->get_result();
    $spending_trends = [];
    while ($row = $result->fetch_assoc()) {
        $spending_trends[] = [
            'month' => $row['month'],
            'total_spent' => number_format((float)$row['total_spent'], 2, '.', '')
        ];
    }

    echo json_encode([
        'success' => true,
        'spending_trends' => $spending_trends
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
