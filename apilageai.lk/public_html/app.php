<?php
/**
 * ApilageAI App Main Page
 * 
 * Main application entry point
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../backend/bootstrap.php';

// Require authentication (allow guest mode)
$is_guest = (!$user->_logged_in && !empty($_SESSION['guest_mode']));
if (!$user->_logged_in && !$is_guest) {
    header("Location: " . APP_URL . "/auth/login");
    exit();
}

$title = "";
$view = $_GET["view"] ?? '';
$sub_view = $_GET["sub_view"] ?? '';

switch ($view) {
    case "":
        // Handle payment redirect parameters
        if (isset($_GET['uid']) || isset($_GET['statusIndicator'])) {
            header("Location: " . APP_URL . "/app");
            exit();
        }
        $title = " | Playground";
        break;
        
    case "chat":
        // Shared chats may not be owned by the current user
        // The Node backend (Socket.IO / APIs) remains the source of truth for access control
        if (!empty($sub_view) && is_numeric($sub_view)) {
            $smarty->assign("old_chat", "true");
            if (isset($_GET['share']) && !is_empty($_GET['share'])) {
                $smarty->assign('share_token', trim((string)$_GET['share']));
            }
        } else {
            http_response_code(404);
            exit();
        }
        break;
        
    default:
        http_response_code(404);
        exit();
}

$smarty->assign("view", $view);
$smarty->assign("sub_view", $sub_view);
$smarty->assign("is_guest", $is_guest);
$smarty->assign("conversations", $is_guest ? [] : $user->get_conversations());

page_header("Apilage AI $title");
page_footer("app");
