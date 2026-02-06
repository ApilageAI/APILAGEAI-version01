<?php
/**
 * ApilageAI Admin Page
 *
 * Restricted to users with type 2 or 3 only.
 */

require_once __DIR__ . "/backend/bootstrap.php";

$allowedTypes = ["2", "3"]; 
$currentType = isset($user->_data["type"]) ? (string) $user->_data["type"] : "";

if (!$user->_logged_in || !in_array($currentType, $allowedTypes, true)) {
    header("Location: " . APP_URL . "/404-error-page.html", true, 302);
    exit();
}

header("X-Robots-Tag: noindex, nofollow, noarchive");
$smarty->assign("noindex", true);

function table_exists(mysqli $db, string $table): bool {
    $stmt = $db->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1");
    $stmt->bind_param("s", $table);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

function column_exists(mysqli $db, string $table, string $column): bool {
    $stmt = $db->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1");
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

function ensure_bug_report_status_column(mysqli $db): void {
    if (!table_exists($db, "bug_reports")) {
        return;
    }
    if (!column_exists($db, "bug_reports", "status")) {
        try {
            $db->query("ALTER TABLE bug_reports ADD COLUMN status ENUM('open','fixed') NOT NULL DEFAULT 'open'");
        } catch (Exception $e) {
            // Ignore schema update failures to keep admin UI functional
        }
    }
}

ensure_bug_report_status_column($db);

// ----------------------------
// Fetch admin data
// ----------------------------
$users = [];
$activeUsersMap = [];
$activeSessions = [];
$bugReports = [];
$transactions = [];
$userGrowthLabels = [];
$userGrowthData = [];
$stats = [
    "total_users" => 0,
    "new_users_30" => 0,
    "active_users_now" => 0,
    "earnings_total" => 0.0,
    "earnings_paid" => 0.0,
    "earnings_pending" => 0.0,
];

if (table_exists($db, "users")) {
    $hasOnboarding = table_exists($db, "user_onboarding");
    if ($hasOnboarding) {
        $result = $db->query("
            SELECT 
                u.id, u.first_name, u.last_name, u.email, u.email_verified, u.phone, u.image, u.balance,
                u.type, u.reg_date, u.memory, u.subscription_status, u.onboard_complete, u.failed_login_attempts, u.locked_until,
                o.school, o.not_student, o.interests, o.preference, o.created_at AS onboarding_created_at, o.updated_at AS onboarding_updated_at
            FROM users u
            LEFT JOIN user_onboarding o ON o.user_id = u.id
            ORDER BY u.reg_date DESC
        ");
    } else {
        $result = $db->query("
            SELECT 
                id, first_name, last_name, email, email_verified, phone, image, balance,
                type, reg_date, memory, subscription_status, onboard_complete, failed_login_attempts, locked_until
            FROM users
            ORDER BY reg_date DESC
        ");
    }
    if ($result) {
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stats["total_users"] = count($users);
    }

    $result = $db->query("SELECT COUNT(*) AS total FROM users WHERE reg_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    if ($result) {
        $stats["new_users_30"] = (int) ($result->fetch_assoc()["total"] ?? 0);
    }
}

if (table_exists($db, "sessions") && table_exists($db, "users")) {
    $result = $db->query("
        SELECT s.user_id, s.token, s.ip, s.client, s.last_seen, s.start,
               u.first_name, u.last_name, u.email
        FROM sessions s
        JOIN users u ON u.id = s.user_id
        WHERE s.active = 1 AND s.last_seen >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ORDER BY s.last_seen DESC
    ");
    if ($result) {
        $activeSessions = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($activeSessions as $row) {
            $activeUsersMap[(int) $row["user_id"]] = true;
        }
        $stats["active_users_now"] = count($activeUsersMap);
    }
}

if (table_exists($db, "bug_reports")) {
    $result = $db->query("
        SELECT id, email, problem, screenshot_path, created_at, status
        FROM bug_reports
        ORDER BY created_at DESC
    ");
    if ($result) {
        $bugReports = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($bugReports as &$report) {
            if (!isset($report["status"]) || $report["status"] === null || $report["status"] === "") {
                $report["status"] = "open";
            }
        }
        unset($report);
    }
}

if (table_exists($db, "transactions")) {
    $result = $db->query("SELECT COALESCE(SUM(amount), 0) AS total FROM transactions");
    if ($result) {
        $stats["earnings_total"] = (float) ($result->fetch_assoc()["total"] ?? 0);
    }
    $result = $db->query("SELECT COALESCE(SUM(amount), 0) AS total FROM transactions WHERE paid = 1");
    if ($result) {
        $stats["earnings_paid"] = (float) ($result->fetch_assoc()["total"] ?? 0);
    }
    $result = $db->query("SELECT COALESCE(SUM(amount), 0) AS total FROM transactions WHERE paid = 0");
    if ($result) {
        $stats["earnings_pending"] = (float) ($result->fetch_assoc()["total"] ?? 0);
    }

    $result = $db->query("
        SELECT t.invoice_id, t.user_id, t.amount, t.paid, t.created_at, t.updated_at,
               u.first_name, u.last_name, u.email
        FROM transactions t
        LEFT JOIN users u ON u.id = t.user_id
        ORDER BY t.created_at DESC
        LIMIT 30
    ");
    if ($result) {
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
    }
}

if (table_exists($db, "users")) {
    $result = $db->query("
        SELECT DATE(reg_date) AS day, COUNT(*) AS count
        FROM users
        WHERE reg_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(reg_date)
        ORDER BY day ASC
    ");

    $byDay = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $byDay[$row["day"]] = (int) $row["count"];
        }
    }

    for ($i = 29; $i >= 0; $i--) {
        $day = date("Y-m-d", strtotime("-$i days"));
        $userGrowthLabels[] = $day;
        $userGrowthData[] = $byDay[$day] ?? 0;
    }
}

$smarty->assign("admin_users", $users);
$smarty->assign("admin_active_sessions", $activeSessions);
$smarty->assign("admin_active_users_map", $activeUsersMap);
$smarty->assign("admin_bug_reports", $bugReports);
$smarty->assign("admin_transactions", $transactions);
$smarty->assign("admin_user_growth_labels", $userGrowthLabels);
$smarty->assign("admin_user_growth_data", $userGrowthData);
$smarty->assign("admin_stats", $stats);

page_header("Apilage AI | Admin", "ApilageAI administration area.");
page_footer("admin");
