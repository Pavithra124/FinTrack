<?php
header('Content-Type: application/json');
require "dbconn.php";  // MySQLi connection ($conn)

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: user_id']);
    exit();
}

$user_id = intval($input['user_id']);

// Optional: filter by status (e.g., active), category, or date range
$status_filter = isset($input['status']) ? $input['status'] : null;
$category_filter = isset($input['category']) ? $input['category'] : null;

try {
    // Prepare base query for budgets
    $sql = "SELECT id, category, amount, start_date, end_date, status FROM budget WHERE user_id = ?";
    $types = "i";
    $params = [$user_id];

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

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $budgets = [];
    $total_budget_amount = 0;
    $total_expense_amount = 0;

    while ($row = $result->fetch_assoc()) {
        $budget_id = $row['id'];
        $category = $row['category'];
        $budget_amount = floatval($row['amount']);
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $status = $row['status'];

        $total_budget_amount += $budget_amount;

        // Calculate total expenses for this budget's category and date range
        $expense_stmt = $conn->prepare(
            "SELECT IFNULL(SUM(amount),0) as total_expense FROM cash_flow
             WHERE user_id = ? AND type = 'expense' AND category = ? AND date BETWEEN ? AND ?"
        );
        if (!$expense_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $expense_stmt->bind_param("isss", $user_id, $category, $start_date, $end_date);
        $expense_stmt->execute();
        $expense_result = $expense_stmt->get_result();
        $expense_row = $expense_result->fetch_assoc();
        $expense_amount = floatval($expense_row['total_expense']);
        $total_expense_amount += $expense_amount;

        $budgets[] = [
            'budget_id' => $budget_id,
            'category' => $category,
            'budget_amount' => number_format($budget_amount, 2, '.', ''),
            'expense_amount' => number_format($expense_amount, 2, '.', ''),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => $status
        ];

        $expense_stmt->close();
    }

    echo json_encode([
        'success' => true,
        'total_budget_amount' => number_format($total_budget_amount, 2, '.', ''),
        'total_expense_amount' => number_format($total_expense_amount, 2, '.', ''),
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
