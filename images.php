<?php
/**
 * ApilageAI User Images API
 * 
 * Returns user's generated images
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/backend/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function jsonResponse($arr) {
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
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
            "image_url" => $row["image_url"],
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