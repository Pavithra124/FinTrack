<?php
header('Content-Type: application/json');
require "dbconn.php";  // Your mysqli connection ($conn)

// Decode JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($input['user_id'], $input['year'], $input['month'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: user_id, year, and month']);
    exit();
}

$user_id = intval($input['user_id']);
$year = intval($input['year']);
$month = intval($input['month']);

if ($month < 1 || $month > 12) {
    http_response_code(400);
    echo json_encode(['error' => 'Month must be between 1 and 12']);
    exit();
}

// Format the month to two digits
$month_padded = str_pad($month, 2, '0', STR_PAD_LEFT);

// Calculate first and last day of the month
$start_date = "$year-$month_padded-01";
$end_date = date("Y-m-t", strtotime($start_date));  // Last day of the month

try {
    // Prepare total income query
    $stmtIncome = $conn->prepare("SELECT IFNULL(SUM(amount),0) as total_income FROM cash_flow WHERE user_id = ? AND type = 'income' AND date BETWEEN ? AND ?");
    if (!$stmtIncome) {
        throw new Exception("Prepare income query failed: " . $conn->error);
    }
    $stmtIncome->bind_param("iss", $user_id, $start_date, $end_date);
    $stmtIncome->execute();
    $incomeResult = $stmtIncome->get_result();
    $incomeRow = $incomeResult->fetch_assoc();
    $total_income = floatval($incomeRow['total_income']);
    $stmtIncome->close();

    // Prepare total expenses query
    $stmtExpense = $conn->prepare("SELECT IFNULL(SUM(amount),0) as total_expense FROM cash_flow WHERE user_id = ? AND type = 'expense' AND date BETWEEN ? AND ?");
    if (!$stmtExpense) {
        throw new Exception("Prepare expense query failed: " . $conn->error);
    }
    $stmtExpense->bind_param("iss", $user_id, $start_date, $end_date);
    $stmtExpense->execute();
    $expenseResult = $stmtExpense->get_result();
    $expenseRow = $expenseResult->fetch_assoc();
    $total_expense = floatval($expenseRow['total_expense']);
    $stmtExpense->close();

    // Calculate net savings
    $net_savings = $total_income - $total_expense;

    // Optionally: Breakdown by category for income and expense
    // Income by category
    $stmtIncCat = $conn->prepare("SELECT category, IFNULL(SUM(amount),0) as total FROM cash_flow WHERE user_id = ? AND type = 'income' AND date BETWEEN ? AND ? GROUP BY category");
    $stmtIncCat->bind_param("iss", $user_id, $start_date, $end_date);
    $stmtIncCat->execute();
    $incCatResult = $stmtIncCat->get_result();
    $income_categories = [];
    while ($row = $incCatResult->fetch_assoc()) {
        $income_categories[] = $row;
    }
    $stmtIncCat->close();

    // Expense by category
    $stmtExpCat = $conn->prepare("SELECT category, IFNULL(SUM(amount),0) as total FROM cash_flow WHERE user_id = ? AND type = 'expense' AND date BETWEEN ? AND ? GROUP BY category");
    $stmtExpCat->bind_param("iss", $user_id, $start_date, $end_date);
    $stmtExpCat->execute();
    $expCatResult = $stmtExpCat->get_result();
    $expense_categories = [];
    while ($row = $expCatResult->fetch_assoc()) {
        $expense_categories[] = $row;
    }
    $stmtExpCat->close();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'year' => $year,
        'month' => $month,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'total_income' => number_format($total_income, 2, '.', ''),
        'total_expense' => number_format($total_expense, 2, '.', ''),
        'net_savings' => number_format($net_savings, 2, '.', ''),
        'income_by_category' => $income_categories,
        'expense_by_category' => $expense_categories
    ]);

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
