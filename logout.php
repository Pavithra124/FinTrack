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
// Start session and set header
session_start();
header('Content-Type: application/json');

// Clear all session variables
$_SESSION = [];

// Delete the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Return JSON response
echo json_encode([
    'success' => true,
    'message' => 'Logout successful'
]);
