<?php
header('Content-Type: application/json');
require "dbconn.php";

try {
    // Removed merchant column
    $stmt = $conn->prepare("SELECT id, category, amount FROM receipts ORDER BY id DESC");
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $receipts = [];
    while ($row = $result->fetch_assoc()) {
        $receipts[] = $row;
    }

    echo json_encode([
        'success' => true,
        'receipts' => $receipts
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
