<?php
/**
 * ApilageAI Side Q&A Generator API
 * 
 * Uses Gemini API to generate study questions
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

$data = json_decode(file_get_contents("php://input"), true);
$prompt = trim($data["prompt"] ?? "");
$context = trim($data["context"] ?? "");

if (empty($prompt) || empty($context)) {
    http_response_code(400);
    echo json_encode(["error" => "Prompt and context are required"]);
    exit;
}

// Sanitize inputs
$prompt = mb_substr($prompt, 0, 1000);
$context = mb_substr($context, 0, 5000);

$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                [
                    "text" => "Generate 3-5 study questions based on this content: $context. $prompt and give answers for the generated contents without explaining, just answers only."
                ]
            ]
        ]
    ]
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . GEMINI_API_KEY_SIDEQA;

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
    $text = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'No result.';
    echo json_encode(["result" => trim($text)]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to generate content"]);
}