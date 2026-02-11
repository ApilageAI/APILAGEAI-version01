<?php
/**
 * ApilageAI Mind Map Generator API
 * 
 * Uses Gemini API for mind map generation
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../../backend/bootstrap.php';

// Security headers
header('Content-Type: application/json');

// Require authentication
if (!$user->_logged_in) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// API Configuration
$API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . GEMINI_API_KEY;

// Read input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action']) || empty($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Action is required']);
    exit;
}

$action = $input['action'];
$prompt = '';

// Build prompt based on action
switch ($action) {
    case 'generate':
        if (!isset($input['text']) || empty($input['text'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Text is required for generation']);
            exit;
        }
        $text = htmlspecialchars($input['text'], ENT_QUOTES, 'UTF-8');
        $prompt = "From the following text, generate a complete, multi-level mind map structure as a valid, nested JSON object. Respond in the same language as the input text (e.g., if the text is in Sinhala, the output topics and notes must be in Sinhala).
        - The root object must have \"id\", \"topic\", \"note\", and an array of \"children\".
        - Each child object must also have \"id\", \"topic\", \"note\", and \"children\" (which can be an empty array).
        - Create a meaningful hierarchy with sub-nodes and sub-nodes of sub-nodes where appropriate.
        - IDs must be short, unique strings.
        - The \"topic\" should be a concise summary (1-4 words).
        - The \"note\" should be a one-sentence summary.
        - Do not output any text other than the single, complete JSON object.
        Text: \"{$text}\"";
        break;

    case 'develop':
        if (!isset($input['instruction']) || empty($input['instruction'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Instruction is required for development']);
            exit;
        }
        if (!isset($input['currentMap']) || empty($input['currentMap'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Current map is required for development']);
            exit;
        }
        $instruction = htmlspecialchars($input['instruction'], ENT_QUOTES, 'UTF-8');
        $currentMap = json_encode($input['currentMap'], JSON_PRETTY_PRINT);
        $prompt = "Based on the following instruction, develop the existing mind map JSON structure. You can add new nodes, sub-nodes, or modify existing ones. Return only the complete, updated JSON object. Do not add any other text.
        Instruction: \"{$instruction}\"
        Existing Mind Map:
        {$currentMap}";
        break;

    case 'addSubnodes':
        if (!isset($input['topic']) || empty($input['topic'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Topic is required for adding sub-nodes']);
            exit;
        }
        $topic = htmlspecialchars($input['topic'], ENT_QUOTES, 'UTF-8');
        $prompt = "Generate 2 to 3 sub-nodes for the topic \"{$topic}\". Return a valid JSON array of objects, where each object has \"id\", \"topic\", and \"note\". Make the IDs unique strings. Do not return any other text.";
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
}

// Prepare request body
$requestBody = json_encode([
    "contents" => [
        ["parts" => [["text" => $prompt]]]
    ]
]);

// Send request to Gemini API
$ch = curl_init($API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $requestBody,
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Connection failed']);
    exit;
}

// Parse and extract text
$responseData = json_decode($response, true);

if ($httpCode === 200 && isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $textContent = $responseData['candidates'][0]['content']['parts'][0]['text'];
    
    echo json_encode([
        'success' => true,
        'data' => $textContent,
        'timestamp' => time(),
        'request_id' => bin2hex(random_bytes(16))
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Processing failed',
        'timestamp' => time()
    ]);
}
