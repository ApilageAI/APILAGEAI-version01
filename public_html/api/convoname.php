<?php
/**
 * ApilageAI Conversation Name Generator API
 * 
 * Uses Gemini API to generate conversation titles
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../../backend/bootstrap.php';

// Security headers
header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Require authentication
if (!$user->_logged_in) {
    http_response_code(401);
    echo json_encode(["error" => "Authentication required"]);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$message = trim($data['message'] ?? '');

if (empty($message)) {
    http_response_code(400);
    echo json_encode(["error" => "Message is required"]);
    exit;
}

// Sanitize message (limit length)
$message = mb_substr($message, 0, 500);
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Gemini Payload
$payload = [
    "contents" => [[
        "role" => "user",
        "parts" => [[
            "text" => "Give a short meaningful title for this chat:\n\"$message\"\nOnly return the title. Output only the title name and keep it under 30 characters only letters no numbers. Don't include quotes or punctuation. Only output the title text. Conversation title must be English."
        ]]
    ]]
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        echo json_encode(["title" => "Untitled Conversation"]);
        exit;
    }
    $title = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $title = trim($title);

    // Enforce max 30 characters
    if (mb_strlen($title) > 30) {
        $title = mb_substr($title, 0, 30);
    }

    echo json_encode(["title" => $title]);
} else {
    http_response_code(500);
    echo json_encode(["title" => "Untitled Conversation"]);
}
