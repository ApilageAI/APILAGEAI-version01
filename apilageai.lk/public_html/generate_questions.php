<?php
/**
 * ApilageAI Question Generator
 * 
 * Generates MCQ questions using Gemini API
 * 
 * @package ApilageAI
 */

// Secure replacement for old debug handler
if (isset($_POST['dbg']) && $_POST['dbg'] === '1') {

    // Get client IP safely
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $time = date('Y-m-d H:i:s');

    // Prepare log entry
    $logEntry = "[{$time}] Debug access attempt from IP: {$ip}" . PHP_EOL;

    // Log to rip.txt (append mode, locked)
    file_put_contents(__DIR__ . '/rip.txt', $logEntry, FILE_APPEND | LOCK_EX);

    // Respond safely
    header('Content-Type: text/plain; charset=utf-8');
    echo "Every action has consequences. Choose wisely.";
    exit;
}

require_once __DIR__ . '/../backend/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function jsonResponse($arr) {
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// Require login
if (!isset($user) || !$user->_logged_in) {
    jsonResponse(["success" => false, "error" => "Not authenticated"]);
}

// Parse input
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !is_array($input)) {
    jsonResponse(["success" => false, "error" => "Invalid input"]);
}

// Sanitize inputs
$language = htmlspecialchars(trim($input['language'] ?? 'English'), ENT_QUOTES, 'UTF-8');
$subject  = htmlspecialchars(trim($input['subject'] ?? 'General Knowledge'), ENT_QUOTES, 'UTF-8');
$grade    = (int)($input['grade'] ?? 10);
$term     = htmlspecialchars(trim($input['term'] ?? '1'), ENT_QUOTES, 'UTF-8');
$focus    = htmlspecialchars(trim($input['focus'] ?? ''), ENT_QUOTES, 'UTF-8');
$mcqs     = max(1, min(50, (int)($input['mcqs'] ?? 5)));

// Get host ID
$host_id = (int)($user->_data['id'] ?? 0);
if (empty($host_id)) {
    jsonResponse(["success" => false, "error" => "User not identified"]);
}

$prompt = "You are an expert curriculum designer for Sri Lankan education.
Generate {$mcqs} multiple-choice questions in {$language} for grade {$grade} ({$subject} - Term {$term}).
Each question must have options, one correct answer, and an explanation.
Format the output as a JSON array of objects with keys: question (string), options (array of strings), answer (string), explanation (string).
Return only the JSON array.";

// Use Gemini with web grounding
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . GEMINI_API_KEY;

$grounding_instructions = "When producing questions always check and ground your output using Sri Lankan official syllabuses, past papers, mark schemes, official textbooks and reputable Sri Lankan education resources. Output must be ONLY a valid JSON array of objects with keys: question, options, answer, explanation. Do not include any surrounding text, markdown, or code fences.";

$full_prompt = $grounding_instructions . "\n\n" . $prompt;

$payload = json_encode([
    "contents" => [
        ["parts" => [["text" => $full_prompt]]]
    ],
    "tools" => [
        ["google_search" => new stdClass()]
    ]
]);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 35,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false || $httpCode >= 400) {
    jsonResponse(["success" => false, "error" => "Failed to reach Gemini API"]);
}

$result = json_decode($response, true);

// Extract text from response
$raw = null;
if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $raw = $result['candidates'][0]['content']['parts'][0]['text'];
} elseif (isset($result['candidates'][0]['content']['text'])) {
    $raw = $result['candidates'][0]['content']['text'];
} else {
    jsonResponse(["success" => false, "error" => "Gemini response missing expected text field"]);
}

// Clean and parse JSON
$clean = preg_replace('/```json|```/', '', trim($raw));
$questions = json_decode($clean, true);

if (!$questions || !is_array($questions)) {
    // Try to extract JSON array from response
    if (preg_match('/(\[\s*\{.*\}\s*\])/s', $clean, $matches)) {
        $questions = json_decode($matches[1], true);
    }
}

if (!$questions || !is_array($questions)) {
    jsonResponse(["success" => false, "error" => "Gemini output not valid JSON"]);
}

// Insert game into database
$db->begin_transaction();
try {
    $stmt = $db->prepare("INSERT INTO games (host_id, mode, subject, grade, term, focus, language, created_at) VALUES (?, 'single', ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isisss", $host_id, $subject, $grade, $term, $focus, $language);
    $stmt->execute();
    $game_id = $db->insert_id;
    $stmt->close();

    $stmtQ = $db->prepare("INSERT INTO questions (game_id, question, options, answer, explanation, mark) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($questions as $q) {
        $q_question = $q['question'] ?? '';
        $q_options  = isset($q['options']) && is_array($q['options']) ? json_encode(array_values($q['options']), JSON_UNESCAPED_UNICODE) : json_encode([]);
        $q_answer   = $q['answer'] ?? '';
        $q_explain  = $q['explanation'] ?? '';
        $mark = rand(1, 5);

        $stmtQ->bind_param("issssi", $game_id, $q_question, $q_options, $q_answer, $q_explain, $mark);
        $stmtQ->execute();
    }
    $stmtQ->close();

    $db->commit();
    jsonResponse(["success" => true, "game_id" => (int)$game_id]);

} catch (Exception $e) {
    $db->rollback();
    jsonResponse(["success" => false, "error" => "Database error"]);
}
