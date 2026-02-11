<?php
require_once __DIR__ . '/../backend/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = defined('ALLOWED_ORIGINS') ? ALLOWED_ORIGINS : [APP_URL];
$allowOrigin = APP_URL;
if ($origin && in_array($origin, $allowedOrigins, true)) {
    $allowOrigin = $origin;
}
header('Access-Control-Allow-Origin: ' . $allowOrigin);
header('Vary: Origin');
header('Access-Control-Allow-Credentials: true');

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

if (!$user->_logged_in) {
    jsonResponse(["success" => false, "error" => "Unauthorized"]);
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 25;
$offset = ($page - 1) * $per_page;

try {
    $stmt = $db->prepare("
        SELECT g.id, g.image_url, g.prompt, g.author_name, g.likes, g.unlikes, g.generated_at,
               u.first_name, u.last_name
        FROM generated_images g
        JOIN users u ON u.id = g.user_id
        WHERE g.public = 1
        ORDER BY g.likes DESC, g.generated_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $per_page, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    $images = [];

    while ($row = $res->fetch_assoc()) {
        $images[] = [
            "id" => (int)$row["id"],
            "image_url" => normalize_upload_url($row["image_url"]),
            "prompt" => $row["prompt"],
            "author_name" => $row["author_name"],
            "likes" => (int)$row["likes"],
            "unlikes" => (int)$row["unlikes"],
            "generated_at" => $row["generated_at"],
            "user_name" => trim($row["first_name"] . " " . $row["last_name"])
        ];
    }

    jsonResponse(["success" => true, "images" => $images]);
} catch (Exception $e) {
    jsonResponse(["success" => false, "error" => $e->getMessage()]);
}
