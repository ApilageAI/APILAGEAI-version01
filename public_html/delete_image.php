<?php
/**
 * ApilageAI Delete Image API
 * 
 * Deletes user's generated images
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../backend/bootstrap.php';

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

// Only allow POST or DELETE
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE', 'GET'])) {
    http_response_code(405);
    jsonResponse(["success" => false, "error" => "Method not allowed"]);
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$user_id = (int)$user->_data['id'];

if (!$id) {
    jsonResponse(["success" => false, "error" => "Invalid ID"]);
}

try {
    // Get image URL to delete from disk (verify ownership)
    $stmt = $db->prepare("SELECT image_url FROM generated_images WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$res) {
        jsonResponse(["success" => false, "error" => "Not found or unauthorized"]);
    }

    // Delete file from disk
    $imagePath = str_replace(APP_URL . "/", __DIR__ . "/", $res['image_url']);
    if (file_exists($imagePath) && is_file($imagePath)) {
        unlink($imagePath);
    }

    // Delete DB record
    $stmt = $db->prepare("DELETE FROM generated_images WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();

    jsonResponse(["success" => true]);
} catch (Exception $e) {
    error_log("Delete image error: " . $e->getMessage());
    jsonResponse(["success" => false, "error" => "Failed to delete image"]);
}