<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "myfinance_track");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, category, date, amount, description, tags FROM receipts ORDER BY date DESC LIMIT 10";
$result = $conn->query($sql);

$receipts = [];
while ($row = $result->fetch_assoc()) {
    $receipts[] = $row;
}

echo json_encode(["receipts" => $receipts]);
$conn->close();
?>
