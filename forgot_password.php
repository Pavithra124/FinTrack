<?php
header('Content-Type: application/json');
require "dbconn.php"; // your mysqli connection ($conn)

// Get POSTed JSON
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['email']) || empty($input['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameter: email']);
    exit();
}

$email = $input['email'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit();
}

try {
    // Check if user exists with this email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // For security, don't reveal if email is not found, just send generic message
        echo json_encode([
            'success' => true,
            'message' => 'If this email is registered, a password reset link will be sent.'
        ]);
        exit();
    }

    $user = $result->fetch_assoc();
    $user_id = $user['id'];

    // Generate a secure random token (e.g., 32 bytes hex)
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

    // Save token and expiry into DB
    $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
    if (!$updateStmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    $updateStmt->bind_param("ssi", $token, $expiry, $user_id);
    $updateStmt->execute();

    // TODO: Send an email to user with reset link e.g.
    // https://yourdomain.com/reset_password.php?token=$token
    // Email sending depends on your setup; not included here.

    echo json_encode([
        'success' => true,
        'message' => 'If this email is registered, a password reset link will be sent.'
    ]);

    $updateStmt->close();
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
