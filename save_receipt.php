<?php
// save_receipt.php

$host = 'localhost';
$dbname = 'myfinance_track';
$username = 'root';
$password = "";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category = $_POST['category'];
$date = $_POST['date'];
$amount = $_POST['amount'];
$description = $_POST['description'];
$tags = $_POST['tags'];

$sql = "INSERT INTO receipts (category, date, amount, description, tags)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdss", $category, $date, $amount, $description, $tags);

if ($stmt->execute()) {
    echo "Receipt saved successfully";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
