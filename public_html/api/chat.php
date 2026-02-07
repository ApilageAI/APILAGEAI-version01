<?php
/**
 * ApilageAI Chat API
 * 
 * Handles chat-related operations with authentication
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../../backend/bootstrap.php';

// Security headers
header('Content-Type: application/json');

// Require authentication
if (!$user->_logged_in) {
    http_response_code(401);
    returnJSON(["e" => true, "m" => "Authentication required"]);
}

// Validate action parameter
$action = $_GET["act"] ?? '';

if (empty($action)) {
    http_response_code(400);
    returnJSON(["e" => true, "m" => "Action required"]);
}

switch ($action) {
    case "new":
        $user->new_message(
            $_POST['m'] ?? '',
            $_POST['t'] ?? '',
            $_FILES['f'] ?? null,
            $_POST['i'] ?? ''
        );
        break;
        
    case "delete":
        if (empty($_POST['i'])) {
            returnJSON(["e" => true, "m" => "Conversation ID required"]);
        }
        $user->delete_conversation(['id' => $_POST['i']]);
        break;
        
    case "message":
        $user->new_message($_POST['m'] ?? '', $_POST['i'] ?? '');
        break;
        
    case "get":
        if (empty($_POST['i'])) {
            returnJSON(["e" => true, "m" => "Conversation ID required"]);
        }
        $messageIds = !empty($_POST['m_ids']) ? explode(',', $_POST['m_ids']) : [];
        $user->get_conversation($_POST['i'], true, $messageIds);
        break;
        
    default:
        http_response_code(404);
        returnJSON(["e" => true, "m" => "Invalid action"]);
}