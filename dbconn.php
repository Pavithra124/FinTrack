<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "myfinance_track";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}
?>
