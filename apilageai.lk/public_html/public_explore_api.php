<?php
define('APILAGE_EXPECTS_JSON', true);
require_once __DIR__ . '/../backend/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

const MAX_WORDS = 1000;
const MAX_IMAGE_BYTES = 62914560; // 60MB
const ALLOWED_IMAGE_MIMES = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];

function json_response(array $payload) {
    returnJSON($payload);
}

function sanitize_text($value, $maxLen = 12000) {
    $value = apilage_sanitize_scalar_input((string)$value);
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLen, 'UTF-8');
    }
    return substr($value, 0, $maxLen);
}

function count_words($text): int {
    $text = trim((string)$text);
    if ($text === '') return 0;
    $parts = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    return is_array($parts) ? count($parts) : 0;
}

function validate_image_upload(string $path): string {
    if (!is_file($path)) {
        return 'Image could not be found.';
    }
    $size = filesize($path);
    if ($size === false || $size > MAX_IMAGE_BYTES) {
        return 'Each image must be 60MB or less.';
    }
    $mime = '';
    if (function_exists('mime_content_type')) {
        $detected = mime_content_type($path);
        if (is_string($detected)) {
            $mime = strtolower($detected);
        }
    }
    if ($mime === '') {
        $mime = 'application/octet-stream';
    }
    if (!in_array($mime, ALLOWED_IMAGE_MIMES, true)) {
        return 'Only PNG, GIF, or JPEG images are allowed.';
    }
    return '';
}

function build_profile_url(array $row): string {
    $slug = '';
    if (!empty($row['public_profile_username'])) {
        $slug = $row['public_profile_username'];
    } elseif (!empty($row['public_profile_token'])) {
        $slug = $row['public_profile_token'];
    }
    if ($slug === '') {
        return '';
    }
    return APP_URL . '/' . $slug;
}

function build_user_name(array $row): string {
    $name = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
    return $name !== '' ? $name : 'ApilageAI User';
}

function build_upload_url(string $value): string {
    $value = trim($value);
    if ($value === '') return '';
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }
    if (stripos($value, '/uploads/') === 0) {
        return rtrim(UPLOADS_BASE_URL, '/') . $value;
    }
    if (stripos($value, 'uploads/') === 0) {
        return rtrim(UPLOADS_BASE_URL, '/') . '/' . $value;
    }
    if (stripos($value, 'userimg/') === 0) {
        return rtrim(UPLOADS_BASE_URL, '/') . '/uploads/' . $value;
    }
    return rtrim(UPLOADS_BASE_URL, '/') . '/uploads/userimg/' . $value;
}

function get_upload_file_path(string $filename): ?string {
    $safe = basename($filename);
    if ($safe === '' || $safe !== $filename) {
        return null;
    }
    $base = realpath(__DIR__ . '/uploads/userimg');
    if (!$base) return null;
    $path = $base . '/' . $safe;
    if (!is_file($path)) return null;
    $real = realpath($path);
    if (!$real || strpos($real, $base) !== 0) {
        return null;
    }
    return $real;
}

function is_user_owned_upload(string $filename, int $userId): bool {
    $safe = basename($filename);
    if ($safe === '' || $safe !== $filename) return false;
    $metaPath = dirname(__DIR__) . '/doc_text/userimg_meta/' . $safe . '.json';
    if (!is_readable($metaPath)) return false;
    $metaRaw = file_get_contents($metaPath);
    if ($metaRaw === false) return false;
    $meta = json_decode($metaRaw, true);
    if (!is_array($meta)) return false;
    return (int)($meta['user_id'] ?? 0) === (int)$userId;
}

function fetch_post_image_paths(mysqli $db, int $postId): array {
    $paths = [];
    $stmt = $db->prepare("SELECT image_filename FROM public_post_images WHERE post_id = ? ORDER BY id ASC");
    if (!$stmt) return $paths;
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $path = get_upload_file_path((string)$row['image_filename']);
        if ($path) $paths[] = $path;
    }
    $stmt->close();
    return $paths;
}

function fetch_comment_image_paths(mysqli $db, int $commentId): array {
    $paths = [];
    $stmt = $db->prepare("SELECT image_filename FROM public_comment_images WHERE comment_id = ? ORDER BY id ASC");
    if (!$stmt) return $paths;
    $stmt->bind_param('i', $commentId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $path = get_upload_file_path((string)$row['image_filename']);
        if ($path) $paths[] = $path;
    }
    $stmt->close();
    return $paths;
}

function merge_image_paths(array ...$lists): array {
    $merged = [];
    foreach ($lists as $list) {
        foreach ($list as $path) {
            if (!in_array($path, $merged, true)) {
                $merged[] = $path;
            }
        }
    }
    return $merged;
}

function parse_json_from_text(string $text): ?array {
    $text = trim($text);
    if ($text === '') return null;
    $decoded = json_decode($text, true);
    if (is_array($decoded)) return $decoded;
    if (preg_match('/\{.*\}/s', $text, $matches)) {
        $decoded = json_decode($matches[0], true);
        if (is_array($decoded)) return $decoded;
    }
    return null;
}

function gemini_generate_text(string $prompt, int $maxTokens = 256): string {
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
        return '';
    }
    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.4,
            'maxOutputTokens' => $maxTokens
        ]
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . GEMINI_API_KEY;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 25
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return '';
    }

    $json = json_decode($response, true);
    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    return trim((string)$text);
}

function gemini_generate_content(string $prompt, array $imagePaths = [], int $maxTokens = 256): string {
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
        return '';
    }
    $parts = [
        ['text' => $prompt]
    ];
    foreach ($imagePaths as $path) {
        if (!is_file($path)) continue;
        $mime = 'image/jpeg';
        if (function_exists('mime_content_type')) {
            $detected = mime_content_type($path);
            if (is_string($detected) && $detected !== '') {
                $mime = $detected;
            }
        }
        $raw = file_get_contents($path);
        if ($raw === false) continue;
        $parts[] = [
            'inline_data' => [
                'mime_type' => $mime,
                'data' => base64_encode($raw)
            ]
        ];
    }

    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => $parts
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.4,
            'maxOutputTokens' => $maxTokens
        ]
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . GEMINI_API_KEY;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 25
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return '';
    }

    $json = json_decode($response, true);
    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    return trim((string)$text);
}

function gemini_moderate_image(string $filePath): array {
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
        return ['allowed' => false, 'recognizable' => false, 'reason' => 'Image moderation unavailable'];
    }

    $mime = 'image/jpeg';
    if (function_exists('mime_content_type')) {
        $detected = mime_content_type($filePath);
        if (is_string($detected) && $detected !== '') {
            $mime = $detected;
        }
    }

    $raw = file_get_contents($filePath);
    if ($raw === false) {
        return ['allowed' => false, 'recognizable' => false, 'reason' => 'Image could not be read'];
    }

    $prompt = 'You are a strict content safety checker for a public Q&A feed. '
        . 'Decide if the image is safe for public posting. '
        . 'Unsafe includes nudity, sexual content, graphic violence, hate symbols, self-harm, illegal activity, or minors in sexual context. '
        . 'Also mark recognizable=false if the image is too blurry, blank, or unclear to identify. '
        . 'Return ONLY JSON like {"allowed":true|false,"recognizable":true|false,"reason":"short reason"}.';

    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => [
                        'mime_type' => $mime,
                        'data' => base64_encode($raw)
                    ]]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0,
            'maxOutputTokens' => 120
        ]
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . GEMINI_API_KEY;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 25
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return ['allowed' => false, 'recognizable' => false, 'reason' => 'Image moderation failed'];
    }

    $json = json_decode($response, true);
    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $parsed = parse_json_from_text((string)$text);

    if (!is_array($parsed)) {
        return ['allowed' => false, 'recognizable' => false, 'reason' => 'Image not recognized'];
    }

    return [
        'allowed' => (bool)($parsed['allowed'] ?? false),
        'recognizable' => (bool)($parsed['recognizable'] ?? false),
        'reason' => trim((string)($parsed['reason'] ?? ''))
    ];
}

function build_ai_name(): string {
    return 'ApilageAI';
}

function build_ai_avatar(): string {
    return APP_URL . '/assets/images/icon.png';
}

function collapse_whitespace(string $text): string {
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim((string)$text);
}

function truncate_text(string $text, int $maxLen): string {
    $text = trim($text);
    if ($text === '' || $maxLen <= 0) return '';
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $maxLen) return $text;
        $sliceLen = max(0, $maxLen - 3);
        $slice = mb_substr($text, 0, $sliceLen, 'UTF-8');
        return rtrim($slice) . '...';
    }
    if (strlen($text) <= $maxLen) return $text;
    $sliceLen = max(0, $maxLen - 3);
    $slice = substr($text, 0, $sliceLen);
    return rtrim($slice) . '...';
}

function build_comment_snippet(string $body, array $images): string {
    $body = collapse_whitespace($body);
    if ($body !== '') {
        return truncate_text($body, 120);
    }
    if (!empty($images)) {
        return 'sent a photo';
    }
    return '';
}

function build_notification_message(string $actorName, string $action, string $body, array $images): string {
    $actorName = trim($actorName);
    if ($actorName === '') {
        $actorName = 'Someone';
    }
    $snippet = build_comment_snippet($body, $images);
    $message = $actorName . ' ' . $action;
    if ($snippet !== '') {
        $message .= ': "' . $snippet . '"';
    }
    return truncate_text($message, 255);
}

function insert_notification(mysqli $db, int $recipientId, string $message): void {
    if ($recipientId <= 0) return;
    $message = trim($message);
    if ($message === '') return;
    $stmt = $db->prepare("INSERT INTO notific (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
    if (!$stmt) return;
    $stmt->bind_param('is', $recipientId, $message);
    $stmt->execute();
    $stmt->close();
}

function get_item_owner(mysqli $db, string $itemType, int $itemId): ?int {
    if ($itemId <= 0) return null;
    switch ($itemType) {
        case 'post': {
            $stmt = $db->prepare("SELECT user_id FROM public_posts WHERE id = ? LIMIT 1");
            if (!$stmt) return null;
            $stmt->bind_param('i', $itemId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ? (int)($row['user_id'] ?? 0) : null;
        }
        case 'comment': {
            $stmt = $db->prepare("SELECT user_id, author_type FROM public_post_comments WHERE id = ? LIMIT 1");
            if (!$stmt) return null;
            $stmt->bind_param('i', $itemId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row) return null;
            if (($row['author_type'] ?? '') === 'ai') return null;
            return (int)($row['user_id'] ?? 0);
        }
        case 'chat': {
            $stmt = $db->prepare("SELECT user_id FROM conversations WHERE conversation_id = ? LIMIT 1");
            if (!$stmt) return null;
            $stmt->bind_param('i', $itemId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ? (int)($row['user_id'] ?? 0) : null;
        }
        case 'image': {
            $stmt = $db->prepare("SELECT user_id FROM generated_images WHERE id = ? LIMIT 1");
            if (!$stmt) return null;
            $stmt->bind_param('i', $itemId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ? (int)($row['user_id'] ?? 0) : null;
        }
        default:
            return null;
    }
}

function collect_comment_descendants(array $map, int $rootId): array {
    $stack = [$rootId];
    $collected = [];
    while (!empty($stack)) {
        $current = array_pop($stack);
        if (isset($collected[$current])) continue;
        $collected[$current] = true;
        if (!empty($map[$current])) {
            foreach ($map[$current] as $childId) {
                if (!isset($collected[$childId])) {
                    $stack[] = $childId;
                }
            }
        }
    }
    return array_map('intval', array_keys($collected));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$action = apilage_sanitize_scalar_input((string)$action);

if ($action === 'list_chats') {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(20, max(1, (int)($_GET['per_page'] ?? 10)));
    $offset = ($page - 1) * $perPage;

    $stmt = $db->prepare(
        "SELECT c.conversation_id, c.title,
                COALESCE(c.published_at, cp.created_at, c.created_at) AS published_at,
                u.id AS user_id, u.first_name, u.last_name, u.image, u.public_profile_username, u.public_profile_token,
                COUNT(m.message_id) AS message_count
         FROM conversations c
         JOIN users u ON u.id = c.user_id
         LEFT JOIN conversation_published cp
           ON cp.conversation_id = c.conversation_id AND cp.published_by = c.user_id
         LEFT JOIN messages m
           ON m.conversation_id = c.conversation_id AND m.type != '3'
         WHERE c.is_published = 1
         GROUP BY c.conversation_id
         ORDER BY published_at DESC, message_count DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $res = $stmt->get_result();

    $chats = [];
    while ($row = $res->fetch_assoc()) {
        $title = trim((string)($row['title'] ?? ''));
        if ($title === '') $title = 'Untitled Chat';
        $chats[] = [
            'conversation_id' => (int)$row['conversation_id'],
            'title' => $title,
            'published_at' => $row['published_at'],
            'message_count' => (int)$row['message_count'],
            'user' => [
                'id' => (int)$row['user_id'],
                'name' => build_user_name($row),
                'image_url' => user_image_url($row['image'] ?? ''),
                'profile_url' => build_profile_url($row)
            ]
        ];
    }
    $stmt->close();

    json_response([
        'success' => true,
        'chats' => $chats,
        'page' => $page,
        'per_page' => $perPage,
        'has_more' => count($chats) === $perPage
    ]);
}

if ($action === 'list_posts') {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(20, max(1, (int)($_GET['per_page'] ?? 10)));
    $offset = ($page - 1) * $perPage;

    $stmt = $db->prepare(
        "SELECT p.id, p.body, p.created_at,
                u.id AS user_id, u.first_name, u.last_name, u.image, u.public_profile_username, u.public_profile_token
         FROM public_posts p
         JOIN users u ON u.id = p.user_id
         WHERE p.status = 'active'
         ORDER BY p.created_at DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $res = $stmt->get_result();

    $posts = [];
    $postIds = [];
    while ($row = $res->fetch_assoc()) {
        $postId = (int)$row['id'];
        $postIds[] = $postId;
        $posts[$postId] = [
            'id' => $postId,
            'body' => $row['body'] ?? '',
            'created_at' => $row['created_at'],
            'images' => [],
            'comment_count' => 0,
            'user' => [
                'id' => (int)$row['user_id'],
                'name' => build_user_name($row),
                'image_url' => user_image_url($row['image'] ?? ''),
                'profile_url' => build_profile_url($row)
            ]
        ];
    }
    $stmt->close();

    if (!empty($postIds)) {
        $in = implode(',', array_fill(0, count($postIds), '?'));
        $types = str_repeat('i', count($postIds));

        $stmt = $db->prepare(
            "SELECT post_id, image_filename
             FROM public_post_images
             WHERE post_id IN ($in)
             ORDER BY id ASC"
        );
        $stmt->bind_param($types, ...$postIds);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['post_id'];
            if (!isset($posts[$pid])) continue;
            $posts[$pid]['images'][] = build_upload_url((string)$row['image_filename']);
        }
        $stmt->close();

        $stmt = $db->prepare(
            "SELECT post_id, COUNT(*) AS comment_count
             FROM public_post_comments
             WHERE post_id IN ($in)
             GROUP BY post_id"
        );
        $stmt->bind_param($types, ...$postIds);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['post_id'];
            if (!isset($posts[$pid])) continue;
            $posts[$pid]['comment_count'] = (int)$row['comment_count'];
        }
        $stmt->close();
    }

    json_response([
        'success' => true,
        'posts' => array_values($posts),
        'page' => $page,
        'per_page' => $perPage,
        'has_more' => count($posts) === $perPage
    ]);
}

if ($action === 'get_post') {
    $postId = (int)($_GET['post_id'] ?? 0);
    if ($postId <= 0) {
        json_response(['success' => false, 'message' => 'Invalid post id']);
    }

    $stmt = $db->prepare(
        "SELECT p.id, p.body, p.created_at,
                u.id AS user_id, u.first_name, u.last_name, u.image, u.public_profile_username, u.public_profile_token
         FROM public_posts p
         JOIN users u ON u.id = p.user_id
         WHERE p.id = ? AND p.status = 'active'
         LIMIT 1"
    );
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        json_response(['success' => false, 'message' => 'Post not found']);
    }

    $post = [
        'id' => (int)$row['id'],
        'body' => $row['body'] ?? '',
        'created_at' => $row['created_at'],
        'images' => [],
        'comment_count' => 0,
        'user' => [
            'id' => (int)$row['user_id'],
            'name' => build_user_name($row),
            'image_url' => user_image_url($row['image'] ?? ''),
            'profile_url' => build_profile_url($row)
        ]
    ];

    $stmt = $db->prepare(
        "SELECT image_filename
         FROM public_post_images
         WHERE post_id = ?
         ORDER BY id ASC"
    );
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($imgRow = $res->fetch_assoc()) {
        $post['images'][] = build_upload_url((string)$imgRow['image_filename']);
    }
    $stmt->close();

    $stmt = $db->prepare(
        "SELECT COUNT(*) AS comment_count
         FROM public_post_comments
         WHERE post_id = ?"
    );
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $countRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($countRow) {
        $post['comment_count'] = (int)($countRow['comment_count'] ?? 0);
    }

    json_response([
        'success' => true,
        'post' => $post
    ]);
}

if ($action === 'list_comments') {
    $postId = (int)($_GET['post_id'] ?? 0);
    if ($postId <= 0) {
        json_response(['success' => false, 'message' => 'Invalid post id']);
    }

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;

    $stmt = $db->prepare(
        "SELECT c.id, c.post_id, c.user_id, c.author_type, c.parent_id, c.body, c.created_at,
                u.first_name, u.last_name, u.image, u.public_profile_username, u.public_profile_token
         FROM public_post_comments c
         LEFT JOIN users u ON u.id = c.user_id
         WHERE c.post_id = ?
         ORDER BY c.created_at ASC
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param('iii', $postId, $perPage, $offset);
    $stmt->execute();
    $res = $stmt->get_result();

    $comments = [];
    $commentIds = [];
    while ($row = $res->fetch_assoc()) {
        $cid = (int)$row['id'];
        $commentIds[] = $cid;
        $authorType = $row['author_type'] ?? 'user';
        $isAi = $authorType === 'ai';
        $comments[$cid] = [
            'id' => $cid,
            'post_id' => (int)$row['post_id'],
            'parent_id' => $row['parent_id'] !== null ? (int)$row['parent_id'] : null,
            'author_type' => $authorType,
            'body' => $row['body'] ?? '',
            'created_at' => $row['created_at'],
            'images' => [],
            'user' => $isAi ? [
                'id' => 0,
                'name' => build_ai_name(),
                'image_url' => build_ai_avatar(),
                'profile_url' => ''
            ] : [
                'id' => (int)$row['user_id'],
                'name' => build_user_name($row),
                'image_url' => user_image_url($row['image'] ?? ''),
                'profile_url' => build_profile_url($row)
            ]
        ];
    }
    $stmt->close();

    if (!empty($commentIds)) {
        $in = implode(',', array_fill(0, count($commentIds), '?'));
        $types = str_repeat('i', count($commentIds));
        $stmt = $db->prepare(
            "SELECT comment_id, image_filename
             FROM public_comment_images
             WHERE comment_id IN ($in)
             ORDER BY id ASC"
        );
        $stmt->bind_param($types, ...$commentIds);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $cid = (int)$row['comment_id'];
            if (!isset($comments[$cid])) continue;
            $comments[$cid]['images'][] = build_upload_url((string)$row['image_filename']);
        }
        $stmt->close();
    }

    json_response([
        'success' => true,
        'comments' => array_values($comments),
        'page' => $page,
        'per_page' => $perPage,
        'has_more' => count($comments) === $perPage
    ]);
}

if ($action === 'create_post') {
    if (!$user->_logged_in) {
        json_response(['success' => false, 'message' => 'Authentication required', 'code' => 'AUTH_REQUIRED']);
    }

    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $body = sanitize_text($data['body'] ?? '', 12000);
    $images = $data['images'] ?? [];
    if (!is_array($images)) $images = [];

    $userId = (int)$user->_data['id'];
    $balance = (int)($user->_data['balance'] ?? 0);

    if ($body === '' && empty($images)) {
        json_response(['success' => false, 'message' => 'Post cannot be empty']);
    }
    if ($body !== '' && count_words($body) > MAX_WORDS) {
        json_response(['success' => false, 'message' => 'Post exceeds 1000 word limit.']);
    }

    if ($balance <= 0) {
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $stmt = $db->prepare(
            "SELECT COUNT(*) AS cnt FROM public_posts WHERE user_id = ? AND created_at BETWEEN ? AND ?"
        );
        $stmt->bind_param('iss', $userId, $todayStart, $todayEnd);
        $stmt->execute();
        $cntRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $count = (int)($cntRow['cnt'] ?? 0);
        if ($count >= 4) {
            json_response(['success' => false, 'message' => 'Daily post limit reached (4 posts per day).', 'code' => 'POST_LIMIT']);
        }
    }

    $images = array_values(array_filter(array_map('trim', $images), function ($val) {
        return $val !== '';
    }));
    $images = array_slice(array_unique($images), 0, 4);

    $imagePaths = [];
    foreach ($images as $filename) {
        if (!is_user_owned_upload($filename, $userId)) {
            json_response(['success' => false, 'message' => 'One or more images are not owned by you.']);
        }
        $path = get_upload_file_path($filename);
        if (!$path) {
            json_response(['success' => false, 'message' => 'One or more images could not be found.']);
        }
        $validation = validate_image_upload($path);
        if ($validation !== '') {
            json_response(['success' => false, 'message' => $validation]);
        }
        $moderation = gemini_moderate_image($path);
        if (!$moderation['allowed'] || !$moderation['recognizable']) {
            $reason = $moderation['reason'] !== '' ? $moderation['reason'] : 'Image not allowed for public posting.';
            json_response(['success' => false, 'message' => $reason]);
        }
        $imagePaths[] = $path;
    }

    $stmt = $db->prepare(
        "INSERT INTO public_posts (user_id, body, status) VALUES (?, ?, 'active')"
    );
    $stmt->bind_param('is', $userId, $body);
    $stmt->execute();
    $postId = (int)$stmt->insert_id;
    $stmt->close();

    if (!empty($images)) {
        $stmt = $db->prepare(
            "INSERT INTO public_post_images (post_id, image_filename) VALUES (?, ?)"
        );
        foreach ($images as $filename) {
            $stmt->bind_param('is', $postId, $filename);
            $stmt->execute();
        }
        $stmt->close();
    }

    $aiReply = '';
    $questionText = $body !== '' ? $body : 'User shared images without text.';
    $prompt = "You are ApilageAI large language model made by apilageai ,You are not google or chatgpt. Provide a helpful reply to this public question in 2-5 sentences. "
        . "Be concise and friendly. If the question is unclear, ask one clarifying question. "
        . "Use any attached images as context if provided.\n\n"
        . "Question: \"" . $questionText . "\"";
    $aiReply = gemini_generate_content($prompt, $imagePaths, 220);
    if ($aiReply === '') {
        $aiReply = 'Thanks for sharing. I am unable to respond right now. Please try again soon.';
    }

    $stmt = $db->prepare(
        "INSERT INTO public_post_comments (post_id, user_id, author_type, parent_id, body) VALUES (?, NULL, 'ai', NULL, ?)"
    );
    $stmt->bind_param('is', $postId, $aiReply);
    $stmt->execute();
    $aiCommentId = (int)$stmt->insert_id;
    $stmt->close();

    json_response([
        'success' => true,
        'post' => [
            'id' => $postId,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
            'images' => array_map('build_upload_url', $images),
            'comment_count' => 1,
            'user' => [
                'id' => $userId,
                'name' => build_user_name($user->_data),
                'image_url' => user_image_url($user->_data['image'] ?? ''),
                'profile_url' => build_profile_url($user->_data)
            ]
        ],
        'ai_comment' => [
            'id' => $aiCommentId,
            'post_id' => $postId,
            'parent_id' => null,
            'author_type' => 'ai',
            'body' => $aiReply,
            'created_at' => date('Y-m-d H:i:s'),
            'images' => [],
            'user' => [
                'id' => 0,
                'name' => build_ai_name(),
                'image_url' => build_ai_avatar(),
                'profile_url' => ''
            ]
        ]
    ]);
}

if ($action === 'create_comment') {
    if (!$user->_logged_in) {
        json_response(['success' => false, 'message' => 'Authentication required', 'code' => 'AUTH_REQUIRED']);
    }

    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $postId = (int)($data['post_id'] ?? 0);
    if ($postId <= 0) {
        json_response(['success' => false, 'message' => 'Invalid post id']);
    }

    $stmt = $db->prepare("SELECT id, user_id, body FROM public_posts WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $postRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$postRow) {
        json_response(['success' => false, 'message' => 'Post not found']);
    }

    $body = sanitize_text($data['body'] ?? '', 12000);
    $images = $data['images'] ?? [];
    if (!is_array($images)) $images = [];

    $images = array_values(array_filter(array_map('trim', $images), function ($val) {
        return $val !== '';
    }));
    $images = array_slice(array_unique($images), 0, 4);

    if ($body === '' && empty($images)) {
        json_response(['success' => false, 'message' => 'Reply cannot be empty']);
    }
    if ($body !== '' && count_words($body) > MAX_WORDS) {
        json_response(['success' => false, 'message' => 'Reply exceeds 1000 word limit.']);
    }

    $userId = (int)$user->_data['id'];

    $imagePaths = [];
    foreach ($images as $filename) {
        if (!is_user_owned_upload($filename, $userId)) {
            json_response(['success' => false, 'message' => 'One or more images are not owned by you.']);
        }
        $path = get_upload_file_path($filename);
        if (!$path) {
            json_response(['success' => false, 'message' => 'One or more images could not be found.']);
        }
        $validation = validate_image_upload($path);
        if ($validation !== '') {
            json_response(['success' => false, 'message' => $validation]);
        }
        $moderation = gemini_moderate_image($path);
        if (!$moderation['allowed'] || !$moderation['recognizable']) {
            $reason = $moderation['reason'] !== '' ? $moderation['reason'] : 'Image not allowed for public posting.';
            json_response(['success' => false, 'message' => $reason]);
        }
        $imagePaths[] = $path;
    }

    $parentId = isset($data['parent_id']) ? (int)$data['parent_id'] : null;
    $replyToId = isset($data['reply_to_id']) ? (int)$data['reply_to_id'] : 0;
    $targetCommentId = $replyToId > 0 ? $replyToId : ($parentId ?? 0);
    $replyingToAi = false;
    if ($targetCommentId > 0) {
        $stmt = $db->prepare("SELECT id, parent_id, author_type, body FROM public_post_comments WHERE id = ? AND post_id = ? LIMIT 1");
        $stmt->bind_param('ii', $targetCommentId, $postId);
        $stmt->execute();
        $targetRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($targetRow) {
            $replyingToAi = ($targetRow['author_type'] ?? '') === 'ai';
            $parentId = $targetRow['parent_id'] ? (int)$targetRow['parent_id'] : (int)$targetRow['id'];
        } else {
            $parentId = null;
        }
    } else {
        $parentId = null;
    }

    if ($parentId === null) {
        $stmt = $db->prepare(
            "INSERT INTO public_post_comments (post_id, user_id, author_type, parent_id, body) VALUES (?, ?, 'user', NULL, ?)"
        );
        $stmt->bind_param('iis', $postId, $userId, $body);
    } else {
        $stmt = $db->prepare(
            "INSERT INTO public_post_comments (post_id, user_id, author_type, parent_id, body) VALUES (?, ?, 'user', ?, ?)"
        );
        $stmt->bind_param('iiis', $postId, $userId, $parentId, $body);
    }
    $stmt->execute();
    $commentId = (int)$stmt->insert_id;
    $stmt->close();

    if (!empty($images)) {
        $stmt = $db->prepare(
            "INSERT INTO public_comment_images (comment_id, image_filename) VALUES (?, ?)"
        );
        foreach ($images as $filename) {
            $stmt->bind_param('is', $commentId, $filename);
            $stmt->execute();
        }
        $stmt->close();
    }

    $commentPayload = [
        'id' => $commentId,
        'post_id' => $postId,
        'parent_id' => $parentId,
        'author_type' => 'user',
        'body' => $body,
        'created_at' => date('Y-m-d H:i:s'),
        'images' => array_map('build_upload_url', $images),
        'user' => [
            'id' => $userId,
            'name' => build_user_name($user->_data),
            'image_url' => user_image_url($user->_data['image'] ?? ''),
            'profile_url' => build_profile_url($user->_data)
        ]
    ];

    $actorName = build_user_name($user->_data);
    $postOwnerId = (int)($postRow['user_id'] ?? 0);
    if ($targetCommentId > 0) {
        $commentOwnerId = get_item_owner($db, 'comment', $targetCommentId);
        if ($commentOwnerId && $commentOwnerId !== $userId) {
            $message = build_notification_message($actorName, 'replied to your comment', $body, $images);
            insert_notification($db, (int)$commentOwnerId, $message);
        }
    } elseif ($postOwnerId > 0 && $postOwnerId !== $userId) {
        $message = build_notification_message($actorName, 'commented on your post', $body, $images);
        insert_notification($db, $postOwnerId, $message);
    }

    $aiComment = null;
    $mentionTriggered = (bool)preg_match('/(^|\s)@apilageai(\b|\s)/i', $body);
    $aiRequested = $mentionTriggered || $replyingToAi;

    $cooldownActive = false;
    if ($aiRequested) {
        $cooldownSeconds = $replyingToAi ? 0 : 45;
        if ($cooldownSeconds > 0) {
            $stmt = $db->prepare(
                "SELECT created_at FROM public_post_comments WHERE post_id = ? AND author_type = 'ai' ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->bind_param('i', $postId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row && !empty($row['created_at'])) {
                $last = strtotime($row['created_at']);
                if ($last && (time() - $last) < $cooldownSeconds) {
                    $cooldownActive = true;
                }
            }
        }

        if (!$cooldownActive) {
            $stmt = $db->prepare(
                "SELECT c.body, c.author_type, c.created_at, u.first_name, u.last_name
                 FROM public_post_comments c
                 LEFT JOIN users u ON u.id = c.user_id
                 WHERE c.post_id = ?
                 ORDER BY c.created_at ASC"
            );
            $stmt->bind_param('i', $postId);
            $stmt->execute();
            $res = $stmt->get_result();

            $lines = [];
            while ($row = $res->fetch_assoc()) {
                $author = ($row['author_type'] === 'ai') ? build_ai_name() : build_user_name($row);
                $text = trim((string)($row['body'] ?? ''));
                if ($text === '') continue;
                $lines[] = $author . ': ' . $text;
            }
            $stmt->close();

            $context = "Post: " . trim((string)$postRow['body']) . "\n";
            if (!empty($lines)) {
                $context .= "Comments:\n" . implode("\n", $lines);
            }
            if (function_exists('mb_substr')) {
                $context = mb_substr($context, 0, 4000, 'UTF-8');
            } else {
                $context = substr($context, 0, 4000);
            }

            $latestText = $body !== '' ? $body : 'User shared images without text.';
            $prompt = "You are ApilageAI large language model made by apilageai ,You are not google or chatgpt replying to a public comment thread. You provide helpful, concise, and friendly replies to users problems related to education, learning, and knowledge sharing,"
                . "Read the conversation and answer the latest question clearly and briefly. "
                . "If clarification is needed, ask one short follow-up. "
                . "Use any attached images as context if provided. "
                . "Return only the reply text.\n\n"
                . $context . "\n\nLatest comment: \"" . $latestText . "\"";

            $contextImages = merge_image_paths(
                $imagePaths,
                $targetCommentId > 0 ? fetch_comment_image_paths($db, $targetCommentId) : [],
                fetch_post_image_paths($db, $postId)
            );
            if (count($contextImages) > 4) {
                $contextImages = array_slice($contextImages, 0, 4);
            }
            $aiText = gemini_generate_content($prompt, $contextImages, 220);
            if ($aiText === '') {
                $aiText = 'I am unable to respond right now. Please try again soon.';
            }

            $aiParentId = $parentId !== null ? (int)$parentId : (int)$commentId;
            $stmt = $db->prepare(
                "INSERT INTO public_post_comments (post_id, user_id, author_type, parent_id, body) VALUES (?, NULL, 'ai', ?, ?)"
            );
            $stmt->bind_param('iis', $postId, $aiParentId, $aiText);
            $stmt->execute();
            $aiId = (int)$stmt->insert_id;
            $stmt->close();

            $aiComment = [
                'id' => $aiId,
                'post_id' => $postId,
                'parent_id' => $aiParentId,
                'author_type' => 'ai',
                'body' => $aiText,
                'created_at' => date('Y-m-d H:i:s'),
                'images' => [],
                'user' => [
                    'id' => 0,
                    'name' => build_ai_name(),
                    'image_url' => build_ai_avatar(),
                    'profile_url' => ''
                ]
            ];
        }
    }

    json_response([
        'success' => true,
        'comment' => $commentPayload,
        'ai_comment' => $aiComment,
        'ai_cooldown' => $cooldownActive
    ]);
}

if ($action === 'report_item') {
    if (!$user->_logged_in) {
        json_response(['success' => false, 'message' => 'Authentication required', 'code' => 'AUTH_REQUIRED']);
    }

    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $itemType = apilage_sanitize_scalar_input((string)($data['item_type'] ?? ''));
    $itemId = (int)($data['item_id'] ?? 0);
    $reason = sanitize_text($data['reason'] ?? '', 500);

    $allowedTypes = ['post', 'comment', 'chat', 'image'];
    if (!in_array($itemType, $allowedTypes, true) || $itemId <= 0) {
        json_response(['success' => false, 'message' => 'Invalid report request']);
    }

    $ownerId = get_item_owner($db, $itemType, $itemId);
    if ($itemType === 'comment' && $ownerId === null) {
        $stmt = $db->prepare("SELECT id FROM public_post_comments WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$exists) {
            json_response(['success' => false, 'message' => 'Item not found']);
        }
    } elseif ($ownerId === null) {
        json_response(['success' => false, 'message' => 'Item not found']);
    }

    $reporterId = (int)$user->_data['id'];
    $reportedUserId = $ownerId ? (int)$ownerId : null;
    if ($reportedUserId === null) {
        $stmt = $db->prepare(
            "INSERT INTO public_reports (reporter_user_id, item_type, item_id, reported_user_id, reason)
             VALUES (?, ?, ?, NULL, ?)"
        );
        $stmt->bind_param('isis', $reporterId, $itemType, $itemId, $reason);
    } else {
        $stmt = $db->prepare(
            "INSERT INTO public_reports (reporter_user_id, item_type, item_id, reported_user_id, reason)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isiis', $reporterId, $itemType, $itemId, $reportedUserId, $reason);
    }
    $stmt->execute();
    $stmt->close();

    json_response(['success' => true]);
}

if ($action === 'delete_item') {
    if (!$user->_logged_in) {
        json_response(['success' => false, 'message' => 'Authentication required', 'code' => 'AUTH_REQUIRED']);
    }

    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $itemType = apilage_sanitize_scalar_input((string)($data['item_type'] ?? ''));
    $itemId = (int)($data['item_id'] ?? 0);
    $allowedTypes = ['post', 'comment', 'chat', 'image'];
    if (!in_array($itemType, $allowedTypes, true) || $itemId <= 0) {
        json_response(['success' => false, 'message' => 'Invalid delete request']);
    }

    $userId = (int)$user->_data['id'];

    if ($itemType === 'post') {
        $stmt = $db->prepare("SELECT user_id FROM public_posts WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row || (int)$row['user_id'] !== $userId) {
            json_response(['success' => false, 'message' => 'Not allowed']);
        }

        $commentIds = [];
        $stmt = $db->prepare("SELECT id FROM public_post_comments WHERE post_id = ?");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $commentIds[] = (int)$row['id'];
        }
        $stmt->close();

        if (!empty($commentIds)) {
            $in = implode(',', array_fill(0, count($commentIds), '?'));
            $types = str_repeat('i', count($commentIds));
            $stmt = $db->prepare("DELETE FROM public_comment_images WHERE comment_id IN ($in)");
            $stmt->bind_param($types, ...$commentIds);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare("DELETE FROM public_post_comments WHERE id IN ($in)");
            $stmt->bind_param($types, ...$commentIds);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $db->prepare("DELETE FROM public_post_images WHERE post_id = ?");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("DELETE FROM public_posts WHERE id = ?");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $stmt->close();

        json_response(['success' => true, 'deleted_count' => count($commentIds) + 1]);
    }

    if ($itemType === 'comment') {
        $stmt = $db->prepare(
            "SELECT id, post_id, user_id, author_type, parent_id
             FROM public_post_comments
             WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $target = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$target || ($target['author_type'] ?? '') !== 'user' || (int)$target['user_id'] !== $userId) {
            json_response(['success' => false, 'message' => 'Not allowed']);
        }

        $deleteIds = [$itemId];
        $parentId = $target['parent_id'] !== null ? (int)$target['parent_id'] : null;
        $postId = (int)($target['post_id'] ?? 0);

        if ($parentId === null && $postId > 0) {
            $stmt = $db->prepare("SELECT id, parent_id FROM public_post_comments WHERE post_id = ?");
            $stmt->bind_param('i', $postId);
            $stmt->execute();
            $res = $stmt->get_result();
            $childrenMap = [];
            while ($row = $res->fetch_assoc()) {
                $cid = (int)$row['id'];
                $pid = $row['parent_id'] !== null ? (int)$row['parent_id'] : 0;
                if (!isset($childrenMap[$pid])) {
                    $childrenMap[$pid] = [];
                }
                $childrenMap[$pid][] = $cid;
            }
            $stmt->close();
            $deleteIds = collect_comment_descendants($childrenMap, $itemId);
        }

        if (!empty($deleteIds)) {
            $in = implode(',', array_fill(0, count($deleteIds), '?'));
            $types = str_repeat('i', count($deleteIds));
            $stmt = $db->prepare("DELETE FROM public_comment_images WHERE comment_id IN ($in)");
            $stmt->bind_param($types, ...$deleteIds);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare("DELETE FROM public_post_comments WHERE id IN ($in)");
            $stmt->bind_param($types, ...$deleteIds);
            $stmt->execute();
            $stmt->close();
        }

        json_response(['success' => true, 'deleted_count' => count($deleteIds), 'parent_id' => $parentId]);
    }

    if ($itemType === 'chat') {
        $stmt = $db->prepare("SELECT user_id FROM conversations WHERE conversation_id = ? LIMIT 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row || (int)$row['user_id'] !== $userId) {
            json_response(['success' => false, 'message' => 'Not allowed']);
        }

        $stmt = $db->prepare("UPDATE conversations SET is_published = 0 WHERE conversation_id = ? AND user_id = ?");
        $stmt->bind_param('ii', $itemId, $userId);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("DELETE FROM conversation_published WHERE conversation_id = ? AND published_by = ?");
        $stmt->bind_param('ii', $itemId, $userId);
        $stmt->execute();
        $stmt->close();

        json_response(['success' => true]);
    }

    if ($itemType === 'image') {
        $stmt = $db->prepare("SELECT user_id FROM generated_images WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row || (int)$row['user_id'] !== $userId) {
            json_response(['success' => false, 'message' => 'Not allowed']);
        }

        $stmt = $db->prepare("UPDATE generated_images SET public = 0 WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $itemId, $userId);
        $stmt->execute();
        $stmt->close();

        json_response(['success' => true]);
    }
}

json_response(['success' => false, 'message' => 'Invalid action']);
