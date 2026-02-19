<?php
define('APILAGE_EXPECTS_JSON', true);
require_once __DIR__ . '/../../backend/bootstrap.php';
require_once __DIR__ . '/developer_runtime.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    $origin = devapi_get_header('Origin');
    devapi_send_cors($origin);
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    returnJSON(["e" => true, "m" => "Method not allowed", "code" => "METHOD_NOT_ALLOWED"]);
}

$origin = devapi_get_header('Origin');
$referer = devapi_get_header('Referer');
$userAgent = devapi_get_header('User-Agent');
devapi_send_cors($origin);

$payload = devapi_get_json_body();
if (function_exists('apilage_sanitize_array_input')) {
    $payload = apilage_sanitize_array_input($payload);
}

$prompt = trim((string)($payload['prompt'] ?? ''));
if ($prompt === '') {
    http_response_code(400);
    returnJSON(["e" => true, "m" => "Prompt is required", "code" => "PROMPT_REQUIRED"]);
}
if (strlen($prompt) > 20000) {
    http_response_code(400);
    returnJSON(["e" => true, "m" => "Prompt too long", "code" => "PROMPT_TOO_LONG"]);
}

$modelToken = strtolower(trim((string)($payload['model'] ?? 'free')));
$modelMap = devapi_model_map();
if (!isset($modelMap[$modelToken])) {
    $modelToken = 'free';
}
$modelId = $modelMap[$modelToken];

$systemText = trim((string)($payload['system'] ?? ''));
if (strlen($systemText) > 4000) {
    http_response_code(400);
    returnJSON(["e" => true, "m" => "System text too long", "code" => "SYSTEM_TOO_LONG"]);
}

$apiKey = devapi_get_api_key();
if ($apiKey === '') {
    http_response_code(401);
    returnJSON(["e" => true, "m" => "API key required", "code" => "API_KEY_REQUIRED"]);
}

$keyRecord = devapi_fetch_key($db, $apiKey);
if (!$keyRecord) {
    http_response_code(401);
    returnJSON(["e" => true, "m" => "Invalid API key", "code" => "INVALID_API_KEY"]);
}
if ($keyRecord['status'] !== 'active') {
    http_response_code(403);
    returnJSON(["e" => true, "m" => "API key disabled", "code" => "API_KEY_DISABLED"]);
}

$domains = devapi_fetch_domains($db, (int)$keyRecord['id']);
if ($origin !== '' && !devapi_is_origin_allowed($origin, $domains)) {
    http_response_code(403);
    returnJSON(["e" => true, "m" => "Origin not allowed", "code" => "ORIGIN_NOT_ALLOWED"]);
}

$leakReason = devapi_detect_leak($origin, $referer, $userAgent);
if ($leakReason !== '') {
    $detail = json_encode([
        'reason' => $leakReason,
        'origin' => $origin,
        'referer' => $referer,
        'userAgent' => $userAgent,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
    devapi_flag_leak($db, (int)$keyRecord['id'], (int)$keyRecord['user_id'], $detail ?: $leakReason);
    http_response_code(403);
    returnJSON(["e" => true, "m" => "Potential API key leak detected. Key disabled.", "code" => "KEY_LEAK_DETECTED"]);
}

$systemInstruction = devapi_build_system_instruction(
    devapi_default_system_prompt(),
    (string)($keyRecord['system_prompt'] ?? ''),
    $systemText
);

$temperature = isset($payload['temperature']) ? (float)$payload['temperature'] : 0.7;
$temperature = max(0, min(2, $temperature));
$maxOutputTokens = isset($payload['max_tokens']) ? (int)$payload['max_tokens'] : 1024;
$maxOutputTokens = max(64, min(4096, $maxOutputTokens));

$estimatedInputTokens = devapi_estimate_tokens($prompt . "\n" . $systemInstruction);
$estimatedTotalTokens = $estimatedInputTokens + $maxOutputTokens;
$limitCheck = devapi_check_rate_limits($db, (int)$keyRecord['id'], $modelToken, $estimatedTotalTokens);
if (!$limitCheck['ok']) {
    http_response_code(429);
    returnJSON(["e" => true, "m" => $limitCheck['message'], "code" => $limitCheck['code']]);
}

$balance = devapi_get_balance($db, (int)$keyRecord['user_id']);
if ($balance <= 0) {
    http_response_code(402);
    returnJSON(["e" => true, "m" => "Insufficient balance", "code" => "INSUFFICIENT_BALANCE"]);
}

$result = devapi_call_gemini($modelId, $prompt, $systemInstruction, $temperature, $maxOutputTokens);
if (!$result['ok']) {
    http_response_code(500);
    returnJSON(["e" => true, "m" => "Failed to generate response", "code" => "GENERATION_FAILED"]);
}

$responseText = $result['text'];
$usage = $result['usage'] ?? [];
$inputTokens = max((int)($usage['promptTokenCount'] ?? 0), $estimatedInputTokens);
$outputTokens = max((int)($usage['candidatesTokenCount'] ?? 0), devapi_estimate_tokens($responseText));

$pricing = devapi_pricing();
$modelPricing = $pricing[$modelToken] ?? $pricing['free'];
$inputCost = ($inputTokens / 1000000) * $modelPricing['input'];
$outputCost = ($outputTokens / 1000000) * $modelPricing['output'];
$totalCost = $inputCost + $outputCost;

devapi_log_usage($db, [
    'api_key_id' => (int)$keyRecord['id'],
    'user_id' => (int)$keyRecord['user_id'],
    'model_token' => $modelToken,
    'input_tokens' => $inputTokens,
    'output_tokens' => $outputTokens,
    'total_tokens' => $inputTokens + $outputTokens,
    'input_cost_lkr' => $inputCost,
    'output_cost_lkr' => $outputCost,
    'total_cost_lkr' => $totalCost,
    'origin' => $origin,
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
]);

devapi_apply_charge($db, (int)$keyRecord['user_id'], $totalCost);

returnJSON([
    "e" => false,
    "model" => $modelToken,
    "text" => $responseText,
    "usage" => [
        "input_tokens" => $inputTokens,
        "output_tokens" => $outputTokens,
        "total_tokens" => $inputTokens + $outputTokens,
        "input_cost_lkr" => $inputCost,
        "output_cost_lkr" => $outputCost,
        "total_cost_lkr" => $totalCost,
    ]
]);
