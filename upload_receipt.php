<?php
header('Content-Type: application/json');

// ======================================================================
// 1. DATABASE CONFIGURATION
// ======================================================================
$host = 'localhost';
$dbname = 'myfinance_track';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// ======================================================================
// 2. GEMINI API CONFIGURATION
// ======================================================================
$geminiApiKey = 'AIzaSyAZB9HEuyw4CfxmUs6XVutNYUgQBqXZUdM'; // Your Gemini API key
$geminiModel = 'gemini-1.5-flash-latest'; // A valid model name

// Define the prompt you want to use. You can change this text easily.
$geminiPromptTemplate = "Analyze this receipt image to extract the following information:
1. Store name
2. Date of purchase (format: YYYY-MM-DD)
3. Total amount
4. List of items purchased with their prices and quantities.

Provide the response in JSON format.";

// ======================================================================
// 3. FILE UPLOAD AND GEMINI API INTERACTION LOGIC
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt'])) {
    // 3.1. Validate and move file (using temporary file path)
    $tempFilePath = $_FILES['receipt']['tmp_name'];
    $fileType = $_FILES['receipt']['type'];

    // Use a more reliable way to get the mime type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tempFilePath);
    finfo_close($finfo);

    // Validate the determined mime type
    $allowedTypes = ['image/jpeg', 'image/png'];
    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Only JPEG and PNG allowed. Detected: ' . $mimeType]);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = basename($_FILES['receipt']['name']);
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $newFileName = uniqid('receipt_', true) . '.' . strtolower($extension);
    $targetFile = $uploadDir . $newFileName;
    $publicPath = 'uploads/' . $newFileName;

    if (move_uploaded_file($tempFilePath, $targetFile)) {
        try {
            // 3.2. Store file path in the database
            $stmt = $pdo->prepare("INSERT INTO receipts (file_path, uploaded_at) VALUES (:file_path, NOW())");
            $stmt->execute(['file_path' => $publicPath]);
            $receiptId = $pdo->lastInsertId();

            // 3.3. Call a function to handle the Gemini API requests
            $geminiResult = processImageWithGemini($targetFile, $mimeType, $geminiApiKey, $geminiModel, $geminiPromptTemplate);

            // 3.4. Handle the response and send it back to the client
            if ($geminiResult['status'] === 'success') {
                echo json_encode([
                    'status' => 'success',
                    'receipt_id' => $receiptId,
                    'file' => $publicPath,
                    'gemini_file_uri' => $geminiResult['file_uri'],
                    'message' => 'Upload successful, saved to database, and processed by Gemini!',
                    'gemini_response' => $geminiResult['response']
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'receipt_id' => $receiptId,
                    'file' => $publicPath,
                    'gemini_file_uri' => $geminiResult['file_uri'],
                    'message' => 'Upload successful but Gemini API call failed.',
                    'gemini_error' => $geminiResult['error']
                ]);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Upload failed.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No receipt file provided. Use POST with a file named "receipt".']);
}

// ======================================================================
// 4. GEMINI API FUNCTION
// ======================================================================
/**
 * Processes an image with the Gemini API to get a response based on a prompt.
 *
 * @param string $filePath The path to the local image file.
 * @param string $mimeType The MIME type of the file (e.g., 'image/jpeg').
 * @param string $apiKey The Gemini API key.
 * @param string $model The model name (e.g., 'gemini-1.5-flash').
 * @param string $promptTemplate The template for the prompt.
 * @return array An array containing the status, the response or error, and the file URI.
 */
function processImageWithGemini($filePath, $mimeType, $apiKey, $model, $promptTemplate) {
    // 4.1. Upload the file to Gemini's Files API
    $uploadUrl = "https://generativelanguage.googleapis.com/upload/v1beta/files?key=$apiKey";
    $chUpload = curl_init();
    curl_setopt($chUpload, CURLOPT_URL, $uploadUrl);
    curl_setopt($chUpload, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chUpload, CURLOPT_POST, true);
    curl_setopt($chUpload, CURLOPT_HTTPHEADER, [
        'X-Goog-Api-Key: ' . $apiKey
    ]);
    $fileData = [
        'file' => new CURLFile($filePath)
    ];
    curl_setopt($chUpload, CURLOPT_POSTFIELDS, $fileData);

    $uploadResponse = curl_exec($chUpload);
    $uploadError = curl_error($chUpload);
    $uploadInfo = curl_getinfo($chUpload);
    curl_close($chUpload);

    if ($uploadError) {
        return ['status' => 'error', 'error' => 'cURL error during file upload: ' . $uploadError, 'file_uri' => null];
    }

    if ($uploadInfo['http_code'] !== 200) {
        return ['status' => 'error', 'error' => 'File upload to Gemini failed with HTTP code ' . $uploadInfo['http_code'] . ': ' . $uploadResponse, 'file_uri' => null];
    }
    
    $fileData = json_decode($uploadResponse, true);
    // Check if the JSON was decoded successfully and has the 'uri' key
    if (!isset($fileData['file']['uri'])) {
        return ['status' => 'error', 'error' => 'Failed to parse file URI from Gemini upload response.', 'file_uri' => null];
    }

    $fileUri = $fileData['file']['uri'];

    // 4.2. Prepare the prompt with the file URI
    $prompt = $promptTemplate;

    $geminiApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=$apiKey";
    
    $requestBody = json_encode([
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt],
                    ['fileData' => [
                        'mimeType' => $mimeType,
                        'fileUri' => $fileUri,
                    ]]
                ]
            ]
        ]
    ]);

    // 4.3. Call the Gemini model with the prompt and file URI
    $chGemini = curl_init();
    curl_setopt($chGemini, CURLOPT_URL, $geminiApiUrl);
    curl_setopt($chGemini, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chGemini, CURLOPT_POST, true);
    curl_setopt($chGemini, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($chGemini, CURLOPT_POSTFIELDS, $requestBody);
    
    $geminiResponse = curl_exec($chGemini);
    $geminiError = curl_error($chGemini);
    curl_close($chGemini);

    if ($geminiError) {
        return ['status' => 'error', 'error' => 'Gemini content generation failed: ' . $geminiError, 'file_uri' => $fileUri];
    }

    $geminiResponseData = json_decode($geminiResponse, true);
    return ['status' => 'success', 'response' => $geminiResponseData, 'file_uri' => $fileUri];
}
?>