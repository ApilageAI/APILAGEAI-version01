<?php
/**
 * ApilageAI Whiteboard API
 *
 * Creates/loads a Whiteboard Team board code per conversation and stores
 * optional snapshots (shapes + image) on our servers.
 *
 * POST JSON body:
 * {
 *   "action": "get" | "save",
 *   "conversation_id": 123,
 *   "shapes": [...],
 *   "image": "data:image/png;base64,..."
 * }
 */

define('APILAGE_EXPECTS_JSON', true);
require_once __DIR__ . '/../../backend/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    returnJSON(['e' => true, 'm' => 'Method not allowed']);
}

if (!$user->_logged_in) {
    http_response_code(401);
    returnJSON(['e' => true, 'm' => 'Authentication required']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    returnJSON(['e' => true, 'm' => 'Invalid JSON body']);
}

$action = strtolower(trim((string)($input['action'] ?? '')));
$conversationId = (int)($input['conversation_id'] ?? 0);

if (!$action || !$conversationId) {
    http_response_code(400);
    returnJSON(['e' => true, 'm' => 'action and conversation_id are required']);
}

function user_can_access_conversation($db, int $conversationId, int $userId): bool {
    $stmt = $db->prepare(
        'SELECT c.conversation_id
         FROM conversations c
         LEFT JOIN conversation_participants p
           ON p.conversation_id = c.conversation_id AND p.user_id = ?
         WHERE c.conversation_id = ? AND (c.user_id = ? OR p.user_id IS NOT NULL)
         LIMIT 1'
    );
    $stmt->execute([$userId, $conversationId, $userId]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
}

if (!user_can_access_conversation($db, $conversationId, (int)$user->_data['id'])) {
    http_response_code(403);
    returnJSON(['e' => true, 'm' => 'No access to this conversation']);
}

function load_canvas_record($db, int $conversationId): array {
    $stmt = $db->prepare('SELECT data FROM conversation_canvas WHERE conversation_id = ? LIMIT 1');
    $stmt->execute([$conversationId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return [null, []];
    $data = json_decode($row['data'] ?? '', true);
    return [$row['data'], is_array($data) ? $data : []];
}

function save_canvas_record($db, int $conversationId, array $payload): void {
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $stmt = $db->prepare('SELECT conversation_id FROM conversation_canvas WHERE conversation_id = ? LIMIT 1');
    $stmt->execute([$conversationId]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt = $db->prepare('UPDATE conversation_canvas SET data = ? WHERE conversation_id = ?');
        $stmt->execute([$json, $conversationId]);
    } else {
        $stmt = $db->prepare('INSERT INTO conversation_canvas (conversation_id, data) VALUES (?, ?)');
        $stmt->execute([$conversationId, $json]);
    }
}

function ensure_board_code($db, int $conversationId): array {
    [$raw, $data] = load_canvas_record($db, $conversationId);
    $whiteboard = is_array($data['whiteboard'] ?? null) ? $data['whiteboard'] : [];
    $boardCode = (string)($whiteboard['board_code'] ?? '');

    if (!$boardCode) {
        $boardCode = 'apil-' . $conversationId . '-' . bin2hex(random_bytes(8));
        $whiteboard['board_code'] = $boardCode;
    }

    $whiteboard['updated_at'] = date('c');
    $data['whiteboard'] = $whiteboard;

    save_canvas_record($db, $conversationId, $data);
    return [$boardCode, $data];
}

try {
    if ($action === 'get') {
        [$code] = ensure_board_code($db, $conversationId);
        returnJSON(['success' => true, 'board_code' => $code]);
    }

    if ($action === 'save') {
        $shapes = is_array($input['shapes'] ?? null) ? array_slice($input['shapes'], 0, 2000) : [];
        $image = (string)($input['image'] ?? '');
        if (strlen($image) > 1500000) {
            $image = '';
        }

        [$code, $data] = ensure_board_code($db, $conversationId);
        $data['snapshot'] = [
            'shapes' => $shapes,
            'image' => $image,
            'saved_at' => date('c'),
        ];
        save_canvas_record($db, $conversationId, $data);

        returnJSON(['success' => true, 'board_code' => $code]);
    }

    http_response_code(400);
    returnJSON(['e' => true, 'm' => 'Unknown action']);
} catch (Throwable $e) {
    http_response_code(500);
    returnJSON(['e' => true, 'm' => 'Server error']);
}
