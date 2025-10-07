<?php
header("Content-Type: application/json");

$host = "localhost";
$db = "myfinance_track";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Only accept POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    // Check for missing fields
    if (!$name || !$email || !$password) {
        echo json_encode(["status" => "error", "message" => "Missing name, email, or password"]);
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already exists"]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Hash the password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User registered successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to register user"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$conn->close();
?>
