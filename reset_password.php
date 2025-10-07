<?php
header('Content-Type: application/json');
require "dbconn.php";  // Your mysqli connection ($conn)

$input = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($input['token'], $input['new_password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: token and new_password']);
    exit();
}

$token = $input['token'];
$new_password = $input['new_password'];

// Validate new_password length (example: min 6 chars)
if (strlen($new_password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters long']);
    exit();
}

try {
    // Check if token exists and is not expired
    $stmt = $conn->prepare("SELECT id, reset_token_expiry FROM users WHERE reset_token = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or expired reset token']);
        exit();
    }

    $user = $result->fetch_assoc();

    // Check token expiry
    $expiry = $user['reset_token_expiry'];
    if (strtotime($expiry) < time()) {
        http_response_code(400);
        echo json_encode(['error' => 'Reset token has expired']);
        exit();
    }

    $user_id = $user['id'];

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update user's password and clear the reset token and expiry
    $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
    if (!$updateStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $updateStmt->bind_param("si", $hashed_password, $user_id);

    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Password has been reset successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update password: ' . $updateStmt->error]);
    }

    $updateStmt->close();
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
