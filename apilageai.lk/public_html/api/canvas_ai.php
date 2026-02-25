<?php
/**
 * ApilageAI Canvas AI API
 *
 * Reads the current canvas state (screenshot + open PDF text) and uses Gemini
 * to generate text blocks, sticky notes, or drawing suggestions that are then
 * placed directly onto the collaborative Fabric.js canvas by the frontend.
 *
 * POST JSON body:
 *   {
 *     "conversation_id": 123,
 *     "prompt":          "Summarise the key points as 3 sticky notes",
 *     "canvas_doc_text": "...text extracted from open Drive PDF...",  // optional
 *     "canvas_image":    "data:image/png;base64,...",                // optional
 *     "board_shapes":    [...],                                      // optional
 *     "board_image":     "data:image/png;base64,..."                 // optional
 *   }
 *
 * Response:
 *   {
 *     "success": true,
 *     "message": "I've added 3 sticky notes…",
 *     "elements": [
 *       { "type": "sticky", "x": 0.2, "y": 0.3, "text": "…", "bg": "#ffe082" },
 *       { "type": "text",   "x": 0.5, "y": 0.6, "text": "…", "color": "#1a1a2e", "size": 16 }
 *     ]
 *   }
 *
 * @package ApilageAI
 */
require_once __DIR__ . '/../../backend/bootstrap.php';
header('Content-Type: application/json');
// ── Auth & method ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}
if (!$user->_logged_in) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}
// ── Input ─────────────────────────────────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}
$conversationId = (int)($input['conversation_id'] ?? 0);
$prompt = trim((string)($input['prompt'] ?? ''));
$docText = mb_substr(trim((string)($input['canvas_doc_text'] ?? '')), 0, 8000);
$canvasImage = (string)($input['canvas_image'] ?? ''); // may be empty
$boardShapes = $input['board_shapes'] ?? null;
$boardImage = (string)($input['board_image'] ?? '');
$mode = trim((string)($input['mode'] ?? 'write')); // write | suggest
if (!$conversationId || !$prompt) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'conversation_id and prompt are required']);
    exit;
}
// ── Build Gemini prompt ───────────────────────────────────────────────────────
$systemInstructions = <<<SYSTEM
You are an AI assistant embedded inside a collaborative whiteboard.
Your job is to help users add content to the canvas based on their request.
Rules:
1. Always respond with ONLY a valid JSON object — no markdown, no extra text.
2. The JSON must have these keys:
   - "message": a short friendly explanation of what you did (max 100 chars)
   - "elements": an array of canvas element objects to place on the canvas
Element types you can return:
  sticky note: { "type": "sticky", "x": 0.0-1.0, "y": 0.0-1.0, "w": 0.0-1.0, "h": 0.0-1.0,
                  "text": "...", "bg": "#ffe082", "color": "#1a1a2e", "font_size": 14 }
  text block:  { "type": "text", "x": 0.0-1.0, "y": 0.0-1.0,
                  "text": "...", "color": "#1a1a2e", "font_size": 16, "bold": false }
  heading:     { "type": "text", "x": 0.0-1.0, "y": 0.0-1.0,
                  "text": "...", "color": "#0d47a1", "font_size": 24, "bold": true }
  rectangle:   { "type": "shape", "shape": "rect", "x": 0.0-1.0, "y": 0.0-1.0,
                  "w": 0.0-1.0, "h": 0.0-1.0,
                  "fill": "#e3f2fd", "stroke": "#1565c0", "stroke_width": 2 }
  ellipse:     { "type": "shape", "shape": "ellipse", "x": 0.0-1.0, "y": 0.0-1.0,
                  "w": 0.0-1.0, "h": 0.0-1.0,
                  "fill": "#fce4ec", "stroke": "#c62828", "stroke_width": 2 }
Coordinates (x, y, w, h) are fractions of canvas size (0.0 = left/top, 1.0 = right/bottom).
Space elements sensibly so they do not overlap — spread them across the canvas.
Prefer warm, readable colours. Max 10 elements per response.SYSTEM;
// Build content parts for Gemini
$textPrompt = "User request: $prompt";
if ($docText) {
    $textPrompt .= "\n\nOpen document text:\n$docText";
}
$shapeText = '';
if (is_array($boardShapes)) {
    $shapeText = json_encode($boardShapes, JSON_UNESCAPED_SLASHES);
    $shapeText = mb_substr($shapeText, 0, 8000);
    if ($shapeText) {
        $textPrompt .= "\n\nCurrent whiteboard shapes (JSON):\n" . $shapeText;
    }
}
$boardImage = trim($boardImage);
if ($boardImage && !$canvasImage) {
    $canvasImage = $boardImage;
}
if ($canvasImage && !str_starts_with($canvasImage, 'data:image/')) {
    if (preg_match('/^[A-Za-z0-9+\\/=_-]+$/', $canvasImage)) {
        $canvasImage = 'data:image/png;base64,' . $canvasImage;
    }
}
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . GEMINI_API_KEY;
// Build parts array
$parts = [['text' => $textPrompt]];
// If a canvas screenshot was sent, include it as an inline image
if ($canvasImage && str_starts_with($canvasImage, 'data:image/')) {
    $commaPos = strpos($canvasImage, ',');
    if ($commaPos !== false) {
        $mimeMatch = [];
        preg_match('/data:([^;]+);base64/', $canvasImage, $mimeMatch);
        $mimeType = $mimeMatch[1] ?? 'image/png';
        $base64Data = substr($canvasImage, $commaPos + 1);
        // Limit to ~1MB decoded to keep request fast
        if (strlen($base64Data) <= 1400000) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $base64Data,
                ],
            ];
            $parts[] = ['text' => 'The image above is a screenshot of the current canvas.'];
        }
    }
}
$payload = [
    'system_instruction' => [
        'parts' => [['text' => $systemInstructions]],
    ],
    'contents' => [
        [
            'role' => 'user',
            'parts' => $parts,
        ],
    ],
    'generationConfig' => [
        'temperature' => 0.4,
        'maxOutputTokens' => 1200,
    ],
];
// ── Call Gemini ────────────────────────────────────────────────────────────────
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($httpCode !== 200 || !$response) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Gemini API request failed']);
    exit;
}
$geminiData = json_decode($response, true);
$rawText = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? '';
// Strip markdown code fences if present
$clean = preg_replace('/```json|```/', '', trim($rawText));
$parsed = json_decode($clean, true);
if (!is_array($parsed) || !isset($parsed['elements'])) {
    // Try to extract JSON from the text
    if (preg_match('/(\{.*\})/s', $clean, $m)) {
        $parsed = json_decode($m[1], true);
    }
}
if (!is_array($parsed)) {
    echo json_encode([
        'success' => false,
        'error' => 'AI returned an unexpected response format',
        'raw' => defined('APP_DEBUG') && APP_DEBUG ? $rawText : null,
    ]);
    exit;
}
// ── Sanitise & cap elements ───────────────────────────────────────────────────
$allowedTypes = ['sticky', 'text', 'shape'];
$allowedShapes = ['rect', 'ellipse'];
$elements = [];
foreach ((array)($parsed['elements'] ?? []) as $el) {
    if (!is_array($el))
        continue;
    $type = (string)($el['type'] ?? '');
    if (!in_array($type, $allowedTypes, true))
        continue;
    $safe = [
        'id' => bin2hex(random_bytes(8)),
        'type' => $type,
        'x' => max(0.0, min(1.0, (float)($el['x'] ?? 0.1))),
        'y' => max(0.0, min(1.0, (float)($el['y'] ?? 0.1))),
    ];
    if ($type === 'sticky') {
        $safe['w'] = max(0.05, min(0.5, (float)($el['w'] ?? 0.2)));
        $safe['h'] = max(0.05, min(0.4, (float)($el['h'] ?? 0.15)));
        $safe['text'] = mb_substr((string)($el['text'] ?? ''), 0, 500);
        $safe['bg'] = preg_match('/^#[0-9a-fA-F]{3,6}$/', $el['bg'] ?? '') ? $el['bg'] : '#ffe082';
        $safe['color'] = preg_match('/^#[0-9a-fA-F]{3,6}$/', $el['color'] ?? '') ? $el['color'] : '#1a1a2e';
        $safe['font_size'] = max(10, min(24, (int)($el['font_size'] ?? 13)));
    }
    elseif ($type === 'text') {
        $safe['text'] = mb_substr((string)($el['text'] ?? ''), 0, 1000);
        $safe['color'] = preg_match('/^#[0-9a-fA-F]{3,6}$/', $el['color'] ?? '') ? $el['color'] : '#1a1a2e';
        $safe['font_size'] = max(8, min(72, (int)($el['font_size'] ?? 16)));
        $safe['bold'] = !empty($el['bold']);
    }
    elseif ($type === 'shape') {
        $shape = (string)($el['shape'] ?? 'rect');
        $safe['shape'] = in_array($shape, $allowedShapes, true) ? $shape : 'rect';
        $safe['w'] = max(0.02, min(0.8, (float)($el['w'] ?? 0.2)));
        $safe['h'] = max(0.02, min(0.8, (float)($el['h'] ?? 0.15)));
        $safe['fill'] = preg_match('/^#[0-9a-fA-F]{3,6}$/', $el['fill'] ?? '') ? $el['fill'] : '#e3f2fd';
        $safe['stroke'] = preg_match('/^#[0-9a-fA-F]{3,6}$/', $el['stroke'] ?? '') ? $el['stroke'] : '#1565c0';
        $safe['stroke_width'] = max(1, min(8, (int)($el['stroke_width'] ?? 2)));
    }
    $elements[] = $safe;
    if (count($elements) >= 10)
        break;
}
echo json_encode([
    'success' => true,
    'message' => mb_substr((string)($parsed['message'] ?? 'Done!'), 0, 150),
    'elements' => $elements,
]);
