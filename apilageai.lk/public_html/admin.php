<?php
/**
 * ApilageAI Admin Page
 *
 * Restricted to users with type 2 or 3 only.
 */

require_once __DIR__ . '/../backend/bootstrap.php';

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
// Admin query params
// ----------------------------
$userPage = max(1, (int) ($_GET["user_page"] ?? 1));
$userSort = (string) ($_GET["user_sort"] ?? "reg_desc");
$userSearch = trim((string) ($_GET["user_search"] ?? ""));
$txnSearch = trim((string) ($_GET["txn_search"] ?? ""));
$usersPerPage = 25;
$userOffset = ($userPage - 1) * $usersPerPage;

// ----------------------------
// Fetch admin data
// ----------------------------
$users = [];
$activeUsersMap = [];
$activeSessions = [];
$bugReports = [];
$transactions = [];
$freeUserDailyUsage = [];
$trialAbuseTracking = [];
$userGrowthLabels = [];
$userGrowthData = [];
$userGrowthSeries = [];
$adminUserPagination = [
    "page" => $userPage,
    "per_page" => $usersPerPage,
    "total" => 0,
    "total_pages" => 1,
];
$adminUserPageBase = "?";
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
    $hasSessions = table_exists($db, "sessions");

    // Total users (all)
    $result = $db->query("SELECT COUNT(*) AS total FROM users");
    if ($result) {
        $stats["total_users"] = (int) ($result->fetch_assoc()["total"] ?? 0);
    }

    // Build filters for users list
    $userFilters = [];
    $userFilterTypes = "";
    $userFilterParams = [];
    if ($userSearch !== "") {
        $userFilters[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
        $like = "%" . $userSearch . "%";
        $userFilterParams = [$like, $like, $like, $like];
        $userFilterTypes = "ssss";
    }
    $userWhere = $userFilters ? ("WHERE " . implode(" AND ", $userFilters)) : "";

    // Count filtered users for pagination
    $countSql = "SELECT COUNT(*) AS total FROM users u $userWhere";
    $userTotal = 0;
    if ($stmt = $db->prepare($countSql)) {
        if ($userFilterTypes !== "") {
            $stmt->bind_param($userFilterTypes, ...$userFilterParams);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
            $userTotal = (int) ($res->fetch_assoc()["total"] ?? 0);
        }
        $stmt->close();
    }

    $adminUserPagination["total"] = $userTotal;
    $adminUserPagination["total_pages"] = max(1, (int) ceil($userTotal / $usersPerPage));
    if ($userPage > $adminUserPagination["total_pages"]) {
        $userPage = $adminUserPagination["total_pages"];
        $adminUserPagination["page"] = $userPage;
    }
    $userOffset = ($userPage - 1) * $usersPerPage;

    // Build select + joins
    $onboardingJoin = $hasOnboarding ? "LEFT JOIN user_onboarding o ON o.user_id = u.id" : "";
    $lastLoginJoin = "";
    $lastLoginSelect = "NULL AS last_login";
    if ($hasSessions) {
        $lastLoginJoin = "LEFT JOIN (SELECT user_id, MAX(last_seen) AS last_seen FROM sessions GROUP BY user_id) ls ON ls.user_id = u.id";
        $lastLoginSelect = "ls.last_seen AS last_login";
    }

    $selectFields = "
        u.id, u.first_name, u.last_name, u.email, u.email_verified, u.phone, u.image, u.balance,
        u.type, u.reg_date, u.memory, u.subscription_status, u.onboard_complete, u.failed_login_attempts, u.locked_until,
        $lastLoginSelect
    ";
    if ($hasOnboarding) {
        $selectFields .= ",
        o.school, o.not_student, o.interests, o.preference, o.created_at AS onboarding_created_at, o.updated_at AS onboarding_updated_at
        ";
    }

    // Sorting
    $orderBy = "u.reg_date DESC";
    if ($userSort === "login_desc" && $hasSessions) {
        $orderBy = "ls.last_seen DESC, u.reg_date DESC";
    } elseif ($userSort === "login_asc" && $hasSessions) {
        $orderBy = "ls.last_seen ASC, u.reg_date ASC";
    } elseif ($userSort === "balance_desc") {
        $orderBy = "u.balance DESC, u.reg_date DESC";
    } elseif ($userSort === "balance_asc") {
        $orderBy = "u.balance ASC, u.reg_date DESC";
    }

    $sql = "SELECT $selectFields FROM users u $onboardingJoin $lastLoginJoin $userWhere ORDER BY $orderBy LIMIT ? OFFSET ?";
    if ($stmt = $db->prepare($sql)) {
        $types = $userFilterTypes . "ii";
        $params = $userFilterParams;
        $params[] = $usersPerPage;
        $params[] = $userOffset;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $users = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
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
            $path = trim($report["screenshot_path"] ?? "");
            $report["screenshot_url"] = $path !== '' ? (uploads_url_from_db($path, 'userimg') ?? '') : '';
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

    $txnFilters = [];
    $txnTypes = "";
    $txnParams = [];
    if ($txnSearch !== "") {
        $txnFilters[] = "(t.invoice_id LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
        $like = "%" . $txnSearch . "%";
        $txnParams = [$like, $like, $like, $like];
        $txnTypes = "ssss";
    }
    $txnWhere = $txnFilters ? ("WHERE " . implode(" AND ", $txnFilters)) : "";

    $txnSql = "
        SELECT t.invoice_id, t.user_id, t.amount, t.paid, t.created_at, t.updated_at,
               u.first_name, u.last_name, u.email
        FROM transactions t
        LEFT JOIN users u ON u.id = t.user_id
        $txnWhere
        ORDER BY t.created_at DESC
        LIMIT 10
    ";
    if ($stmt = $db->prepare($txnSql)) {
        if ($txnTypes !== "") {
            $stmt->bind_param($txnTypes, ...$txnParams);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
}

if (table_exists($db, "users")) {
    // Daily (last 30 days)
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

    // Weekly (last 12 weeks)
    $weeksToShow = 12;
    $byWeek = [];
    $result = $db->query("
        SELECT YEARWEEK(reg_date, 3) AS yw, COUNT(*) AS count
        FROM users
        WHERE reg_date >= DATE_SUB(CURDATE(), INTERVAL {$weeksToShow} WEEK)
        GROUP BY yw
        ORDER BY yw ASC
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $byWeek[$row["yw"]] = (int) $row["count"];
        }
    }
    $weeklyLabels = [];
    $weeklyData = [];
    $weekStart = new DateTimeImmutable("monday this week");
    for ($i = $weeksToShow - 1; $i >= 0; $i--) {
        $d = $weekStart->modify("-{$i} week");
        $isoYear = (int) $d->format("o");
        $isoWeek = (int) $d->format("W");
        $key = (int) sprintf("%d%02d", $isoYear, $isoWeek);
        $weeklyLabels[] = $d->format("M j");
        $weeklyData[] = $byWeek[$key] ?? 0;
    }

    // Monthly (last 12 months)
    $monthsToShow = 12;
    $byMonth = [];
    $result = $db->query("
        SELECT DATE_FORMAT(reg_date, '%Y-%m') AS ym, COUNT(*) AS count
        FROM users
        WHERE reg_date >= DATE_SUB(CURDATE(), INTERVAL {$monthsToShow} MONTH)
        GROUP BY ym
        ORDER BY ym ASC
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $byMonth[$row["ym"]] = (int) $row["count"];
        }
    }
    $monthlyLabels = [];
    $monthlyData = [];
    $monthStart = new DateTimeImmutable("first day of this month");
    for ($i = $monthsToShow - 1; $i >= 0; $i--) {
        $m = $monthStart->modify("-{$i} month");
        $key = $m->format("Y-m");
        $monthlyLabels[] = $m->format("M Y");
        $monthlyData[] = $byMonth[$key] ?? 0;
    }

    // Yearly (last 5 years)
    $yearsToShow = 5;
    $byYear = [];
    $result = $db->query("
        SELECT YEAR(reg_date) AS y, COUNT(*) AS count
        FROM users
        WHERE reg_date >= DATE_SUB(CURDATE(), INTERVAL {$yearsToShow} YEAR)
        GROUP BY y
        ORDER BY y ASC
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $byYear[$row["y"]] = (int) $row["count"];
        }
    }
    $yearlyLabels = [];
    $yearlyData = [];
    $currentYear = (int) date("Y");
    for ($i = $yearsToShow - 1; $i >= 0; $i--) {
        $year = $currentYear - $i;
        $yearlyLabels[] = (string) $year;
        $yearlyData[] = $byYear[$year] ?? 0;
    }

    $userGrowthSeries = [
        "daily" => ["labels" => $userGrowthLabels, "data" => $userGrowthData],
        "weekly" => ["labels" => $weeklyLabels, "data" => $weeklyData],
        "monthly" => ["labels" => $monthlyLabels, "data" => $monthlyData],
        "yearly" => ["labels" => $yearlyLabels, "data" => $yearlyData],
    ];
}

if (table_exists($db, "free_user_daily_usage")) {
    $result = $db->query("
        SELECT f.user_id, f.date, f.window_id,
               f.messages_used, f.image_uploads_used, f.file_uploads_used, f.image_generations_used,
               u.first_name, u.last_name, u.email
        FROM free_user_daily_usage f
        LEFT JOIN users u ON u.id = f.user_id
        ORDER BY f.date DESC, f.user_id ASC, f.window_id DESC
        LIMIT 100
    ");
    if ($result) {
        $freeUserDailyUsage = $result->fetch_all(MYSQLI_ASSOC);
    }
}

if (table_exists($db, "trial_abuse_tracking")) {
    $result = $db->query("
        SELECT t.id, t.user_id, t.ip_address, t.device_fingerprint, t.trial_start_date, t.trial_end_date, t.created_at,
               u.first_name, u.last_name, u.email
        FROM trial_abuse_tracking t
        LEFT JOIN users u ON u.id = t.user_id
        ORDER BY t.created_at DESC
        LIMIT 100
    ");
    if ($result) {
        $trialAbuseTracking = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Build page base for pagination links
$userQuery = [];
if ($userSearch !== "") {
    $userQuery["user_search"] = $userSearch;
}
if ($userSort !== "") {
    $userQuery["user_sort"] = $userSort;
}
if ($txnSearch !== "") {
    $userQuery["txn_search"] = $txnSearch;
}
$adminUserPageBase = $userQuery ? ("?" . http_build_query($userQuery) . "&") : "?";

$smarty->assign("admin_users", $users);
$smarty->assign("admin_active_sessions", $activeSessions);
$smarty->assign("admin_active_users_map", $activeUsersMap);
$smarty->assign("admin_bug_reports", $bugReports);
$smarty->assign("admin_transactions", $transactions);
$smarty->assign("admin_user_growth_labels", $userGrowthLabels);
$smarty->assign("admin_user_growth_data", $userGrowthData);
$smarty->assign("admin_user_growth_series", $userGrowthSeries);
$smarty->assign("admin_user_pagination", $adminUserPagination);
$smarty->assign("admin_user_page_base", $adminUserPageBase);
$smarty->assign("admin_user_search", $userSearch);
$smarty->assign("admin_user_sort", $userSort);
$smarty->assign("admin_txn_search", $txnSearch);
$smarty->assign("admin_free_user_daily_usage", $freeUserDailyUsage);
$smarty->assign("admin_trial_abuse_tracking", $trialAbuseTracking);
$smarty->assign("admin_stats", $stats);

page_header("Apilage AI | Admin", "ApilageAI administration area.");
page_footer("admin");
