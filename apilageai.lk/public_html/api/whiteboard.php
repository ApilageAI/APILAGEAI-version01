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
    if (!$stmt) return false;
    $stmt->bind_param('iii', $userId, $conversationId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $hasRow = $result && $result->num_rows > 0;
    $stmt->close();
    return $hasRow;
}

if (!user_can_access_conversation($db, $conversationId, (int)$user->_data['id'])) {
    http_response_code(403);
    returnJSON(['e' => true, 'm' => 'No access to this conversation']);
}

function ensure_canvas_table($db): void {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        $ok = $db->query(
            "CREATE TABLE IF NOT EXISTS conversation_canvas (
                conversation_id BIGINT NOT NULL PRIMARY KEY,
                data LONGTEXT NOT NULL,
                version INT NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        if (!$ok) {
            throw new Exception('Failed to ensure conversation_canvas table');
        }
    } catch (Throwable $e) {
        throw $e;
    }
}

function load_canvas_record($db, int $conversationId): array {
    ensure_canvas_table($db);
    $stmt = $db->prepare('SELECT data FROM conversation_canvas WHERE conversation_id = ? LIMIT 1');
    if (!$stmt) return [null, []];
    $stmt->bind_param('i', $conversationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    if (!$row) return [null, []];
    $data = json_decode($row['data'] ?? '', true);
    return [$row['data'], is_array($data) ? $data : []];
}

function save_canvas_record($db, int $conversationId, array $payload): void {
    ensure_canvas_table($db);
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        $json = '{}';
    }
    $stmt = $db->prepare('SELECT conversation_id FROM conversation_canvas WHERE conversation_id = ? LIMIT 1');
    if (!$stmt) {
        throw new Exception('Failed to prepare canvas lookup');
    }
    $stmt->bind_param('i', $conversationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result && $result->num_rows > 0;
    $stmt->close();

    if ($exists) {
        $stmt = $db->prepare('UPDATE conversation_canvas SET data = ? WHERE conversation_id = ?');
        if (!$stmt) {
            throw new Exception('Failed to prepare canvas update');
        }
        $stmt->bind_param('si', $json, $conversationId);
        $stmt->execute();
        $stmt->close();
        return;
    }

    $stmt = $db->prepare('INSERT INTO conversation_canvas (conversation_id, data) VALUES (?, ?)');
    if (!$stmt) {
        throw new Exception('Failed to prepare canvas insert');
    }
    $stmt->bind_param('is', $conversationId, $json);
    $stmt->execute();
    $stmt->close();
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
    // Re-read to guard against race conditions on first insert.
    [, $fresh] = load_canvas_record($db, $conversationId);
    if (is_array($fresh['whiteboard'] ?? null) && !empty($fresh['whiteboard']['board_code'])) {
        $boardCode = (string)$fresh['whiteboard']['board_code'];
        $data = $fresh;
    }
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
    error_log('whiteboard.php error: ' . $e->getMessage());
    $message = (defined('APP_DEBUG') && APP_DEBUG)
        ? 'Server error: ' . $e->getMessage()
        : 'Server error. Please try again later.';
    http_response_code(500);
    returnJSON(['e' => true, 'm' => $message]);
}
