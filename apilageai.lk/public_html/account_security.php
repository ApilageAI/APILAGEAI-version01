<?php
require_once __DIR__ . '/../backend/bootstrap.php';

page_header(
    'Account Security | ApilageAI',
    'Report security issues, reset your password, and review sign-in activity for your ApilageAI account.'
);

$isLoggedIn = $user->_logged_in;
$userEmail = $isLoggedIn ? (string) ($user->_data['email'] ?? '') : '';
$userName = $isLoggedIn ? trim((string) ($user->_data['first_name'] ?? '') . ' ' . (string) ($user->_data['last_name'] ?? '')) : '';
$currentToken = $isLoggedIn ? (string) ($user->_data['token'] ?? '') : '';
$securitySessions = [];
$hasSessionsTable = false;

if ($isLoggedIn && isset($db) && $db instanceof mysqli) {
    $stmt = $db->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1");
    if ($stmt) {
        $tableName = 'sessions';
        $stmt->bind_param('s', $tableName);
        $stmt->execute();
        $result = $stmt->get_result();
        $hasSessionsTable = $result->num_rows > 0;
        $stmt->close();
    }

    if ($hasSessionsTable) {
        $viewerId = (int) ($user->_data['id'] ?? 0);
        if ($viewerId > 0) {
            $stmt = $db->prepare(
                "SELECT token, ip, client, start, last_seen, active
                 FROM sessions
                 WHERE user_id = ? AND active = '1'
                 ORDER BY last_seen DESC
                 LIMIT 8"
            );
            if ($stmt) {
                $stmt->bind_param('i', $viewerId);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $lastSeen = $row['last_seen'] ?? null;
                    $start = $row['start'] ?? null;
                    $lastSeenLabel = $lastSeen ? date('M d, Y H:i', strtotime($lastSeen)) : 'Unknown';
                    $startLabel = $start ? date('M d, Y H:i', strtotime($start)) : 'Unknown';
                    $token = (string) ($row['token'] ?? '');
                    $securitySessions[] = [
                        'client' => (string) ($row['client'] ?? ''),
                        'ip' => (string) ($row['ip'] ?? ''),
                        'last_seen' => $lastSeenLabel,
                        'started' => $startLabel,
                        'is_current' => $currentToken !== '' && $token !== '' && hash_equals($currentToken, $token)
                    ];
                }
                $stmt->close();
            }
        }
    }
}

$smarty->assign('security_is_logged_in', $isLoggedIn);
$smarty->assign('security_user_email', $userEmail);
$smarty->assign('security_user_name', $userName);
$smarty->assign('security_sessions', $securitySessions);
$smarty->assign('security_has_sessions', !empty($securitySessions));
$smarty->assign('security_sessions_available', $hasSessionsTable);

page_footer('account_security');
?>
