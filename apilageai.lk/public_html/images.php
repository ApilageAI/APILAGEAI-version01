<?php
/**
 * ApilageAI User Images API
 * 
 * Returns user's generated images
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../backend/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function jsonResponse($arr) {
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_upload_url(?string $value): ?string {
    if ($value === null) {
        return null;
    }
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $value)) {
        $parsed = parse_url($value);
        $path = $parsed['path'] ?? '';
        $host = $parsed['host'] ?? '';
        $appHost = parse_url(APP_URL, PHP_URL_HOST);
        if ($host && $appHost && strcasecmp($host, $appHost) === 0 && stripos($path, '/uploads/') === 0) {
            return rtrim(UPLOADS_BASE_URL, '/') . $path;
        }
        return $value;
    }
    if (stripos($value, '/uploads/') === 0) {
        return rtrim(UPLOADS_BASE_URL, '/') . $value;
    }
    if (stripos($value, 'uploads/') === 0) {
        return rtrim(UPLOADS_BASE_URL, '/') . '/' . $value;
    }
    if (preg_match('#^(userimg|profile|genimg)/#i', $value)) {
        return rtrim(UPLOADS_BASE_URL, '/') . '/uploads/' . $value;
    }
    return $value;
}

// Require authentication
if (!$user->_logged_in) {
    http_response_code(401);
    jsonResponse(["success" => false, "error" => "Unauthorized"]);
}

$user_id = (int)$user->_data['id'];
$page = max(1, min(100, (int)($_GET['page'] ?? 1)));  // Limit to 100 pages
$per_page = 25;
$offset = ($page - 1) * $per_page;

try {
    $stmt = $db->prepare("
        SELECT id, image_url, prompt, author_name, public, likes, unlikes, generated_at
        FROM generated_images
        WHERE user_id = ?
        ORDER BY generated_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("iii", $user_id, $per_page, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    $images = [];

    while ($row = $res->fetch_assoc()) {
        $images[] = [
            "id" => (int)$row["id"],
            "image_url" => normalize_upload_url($row["image_url"]),
            "prompt" => $row["prompt"],
            "author_name" => $row["author_name"],
            "public" => (int)$row["public"],
            "likes" => (int)$row["likes"],
            "unlikes" => (int)$row["unlikes"],
            "generated_at" => $row["generated_at"]
        ];
    }

    jsonResponse(["success" => true, "images" => $images, "page" => $page]);
} catch (Exception $e) {
    error_log("Images API error: " . $e->getMessage());
    jsonResponse(["success" => false, "error" => "Failed to fetch images"]);
}
