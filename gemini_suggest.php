<?php
/**
 * ApilageAI Gemini Suggestions API
 * 
 * Generates conversation suggestions using Gemini API
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/backend/bootstrap.php';

// Security headers
header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['suggestions' => []]);
    exit;
}

// Require authentication
if (!$user->_logged_in) {
    http_response_code(401);
    echo json_encode(['suggestions' => [], 'error' => 'Authentication required']);
    exit;
}

// Read request payload
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['conversation_context'])) {
    echo json_encode(['suggestions' => []]);
    exit;
}

$messages = mb_substr($data['conversation_context'], 0, 2000);  // Limit context
$max_suggestions = min(10, max(1, (int)($data['max_suggestions'] ?? 5)));

// Build Gemini request
$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => "Suggest $max_suggestions possible next user messages based on this conversation:\n$messages\n\nReturn only the suggestions as a JSON array of strings."]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 150
    ]
];

// Send request to Gemini
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . GEMINI_API_KEY;
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$suggestions = [];
if ($httpcode === 200 && $response) {
    $json = json_decode($response, true);
    if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
        $text = $json['candidates'][0]['content']['parts'][0]['text'];
        // Try to parse as JSON array
        $parsed = json_decode($text, true);
        if (is_array($parsed)) {
            $suggestions = $parsed;
        }
    }
}

echo json_encode(['suggestions' => $suggestions]);