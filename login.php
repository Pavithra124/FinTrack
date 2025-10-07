<?php
$host = 'localhost';
$dbname = 'myfinance_track';
$username = 'root';
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
?>
<?php
header('Content-Type: application/json');
session_start();

require 'db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

$email = $input['email'];
$password = $input['password'];

// Prepare and execute query to get user by email
$stmt = $pdo->prepare('SELECT id, name, email, password, failed_login_attempts, last_failed_login FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User not found
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// Optional: Check failed login attempts or lockout here if desired

// Assuming passwords are hashed with password_hash()
if (password_verify($password, $user['password'])) {
    // Password is correct
    // Reset failed login attempts (optional)
    $stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = 0 WHERE id = :id');
    $stmt->execute(['id' => $user['id']]);

    // Set session or generate token, here just returning success message
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);
} else {
    // Invalid password: increment failed login attempts (optional)
    $failedAttempts = $user['failed_login_attempts'] + 1;
    $stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = :failed_attempts, last_failed_login = NOW() WHERE id = :id');
    $stmt->execute(['failed_attempts' => $failedAttempts, 'id' => $user['id']]);

    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
}
?>
