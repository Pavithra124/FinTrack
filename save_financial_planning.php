<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "myfinance_track");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$primary_salary = isset($_POST['primary_salary']) ? $_POST['primary_salary'] : null;
$additional_income = isset($_POST['additional_income']) ? $_POST['additional_income'] : 0;
$total_income = isset($_POST['total_income']) ? $_POST['total_income'] : null;

if ($primary_salary === null || $total_income === null) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required parameters"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO financial_planning (primary_salary, additional_income, total_income) VALUES (?, ?, ?)");
$stmt->bind_param("ddd", $primary_salary, $additional_income, $total_income);

if ($stmt->execute()) {
    echo json_encode(["message" => "Data saved successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to save data"]);
}

$stmt->close();
$conn->close();
?>
