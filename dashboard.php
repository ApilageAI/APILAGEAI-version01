<?php
/**
 * ApilageAI Images
 * 
 * Images gallery with usage stats and profile
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/backend/bootstrap.php';

// Require authentication
if (!$user->_logged_in) {
    header("Location: " . APP_URL . "/auth/login");
    exit;
}

$title = "";
$view = $_GET["view"] ?? '';
$user_id = (int)$user->_data['id'];

switch ($view) {
    case "":
        $title = " | Images";
        break;
        
    case "usage":
        $stmt = $db->prepare("
            SELECT 
                balance, 
                ROUND(IFNULL(COUNT(DISTINCT c.conversation_id) / GREATEST(DATEDIFF(CURDATE(), MIN(c.created_at)), 1), 0)) AS conversations_count, 
                COUNT(m.message_id) AS messages_count 
            FROM users u 
            LEFT JOIN conversations c ON c.user_id = u.id 
            LEFT JOIN messages m ON m.conversation_id = c.conversation_id 
            WHERE u.id = ? 
            GROUP BY u.id
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $val = $result->fetch_assoc();
        $stmt->close();

        $smarty->assign('current_balance', $val['balance'] ?? 0);
        $smarty->assign('messages_count', $val['messages_count'] ?? 0);
        $smarty->assign('conversations_count', $val['conversations_count'] ?? 0);

        $title = " | Images - Usage";
        break;
        
    case "profile":
        $title = " | Images - Profile";
        break;
        
    default:
        http_response_code(404);
        exit;
}

$smarty->assign("view", $view);

page_header("Apilage AI $title");
page_footer('dashboard');
