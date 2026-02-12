<?php
require_once __DIR__ . '/../backend/bootstrap.php';

header("Content-Type: application/json; charset=utf-8");

function respond(bool $ok, string $message, array $extra = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(array_merge([
        "success" => $ok,
        "message" => $message,
    ], $extra));
    exit();
}

$allowedTypes = ["2", "3"];
$currentType = isset($user->_data["type"]) ? (string) $user->_data["type"] : "";
if (!$user->_logged_in || !in_array($currentType, $allowedTypes, true)) {
    respond(false, "Access denied", [], 403);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Method not allowed", [], 405);
}

$csrf = $_POST["csrf_token"] ?? "";
if (!apilage_verify_csrf_token($csrf)) {
    respond(false, "Invalid CSRF token", [], 403);
}

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
            // Ignore schema update failures to keep admin actions running
        }
    }
}

function delete_by_user_id(mysqli $db, string $table, string $column, int $userId): void {
    if (!table_exists($db, $table)) {
        return;
    }
    if (!column_exists($db, $table, $column)) {
        return;
    }
    $stmt = $db->prepare("DELETE FROM `$table` WHERE `$column` = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

function delete_by_ids(mysqli $db, string $table, string $column, array $ids): void {
    if (!table_exists($db, $table) || empty($ids)) {
        return;
    }
    $safeIds = array_map("intval", $ids);
    $in = implode(",", $safeIds);
    $db->query("DELETE FROM `$table` WHERE `$column` IN ($in)");
}

$action = $_POST["action"] ?? "";

switch ($action) {
    case "logout_user": {
        $userId = (int) ($_POST["user_id"] ?? 0);
        if ($userId <= 0) {
            respond(false, "Invalid user");
        }
        if (!table_exists($db, "sessions")) {
            respond(false, "Sessions table not found", [], 500);
        }
        $stmt = $db->prepare("UPDATE sessions SET active = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        respond(true, "User logged out");
        break;
    }

    case "delete_user": {
        $userId = (int) ($_POST["user_id"] ?? 0);
        if ($userId <= 0) {
            respond(false, "Invalid user");
        }
        if ($userId === (int) ($user->_data["id"] ?? 0)) {
            respond(false, "You cannot delete your own account.");
        }
        if (!table_exists($db, "users")) {
            respond(false, "Users table not found", [], 500);
        }

        $db->begin_transaction();
        try {
            $conversationIds = [];
            if (table_exists($db, "conversations")) {
                $stmt = $db->prepare("SELECT conversation_id FROM conversations WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $conversationIds[] = (int) $row["conversation_id"];
                }
                $stmt->close();
            }

            if (!empty($conversationIds)) {
                delete_by_ids($db, "conversation_canvas", "conversation_id", $conversationIds);
                delete_by_ids($db, "conversation_published", "conversation_id", $conversationIds);
                delete_by_ids($db, "conversation_share_links", "conversation_id", $conversationIds);
                delete_by_ids($db, "messages", "conversation_id", $conversationIds);
                delete_by_ids($db, "conversation_participants", "conversation_id", $conversationIds);
            }

            // Conversation-related user references
            delete_by_user_id($db, "conversation_participants", "user_id", $userId);
            delete_by_user_id($db, "conversation_participants", "added_by", $userId);
            delete_by_user_id($db, "conversation_published", "published_by", $userId);
            delete_by_user_id($db, "conversation_share_links", "shared_by", $userId);
            delete_by_user_id($db, "conversation_share_links", "target_user_id", $userId);

            if (table_exists($db, "conversations")) {
                $stmt = $db->prepare("DELETE FROM conversations WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
            }

            $gameIds = [];
            if (table_exists($db, "games")) {
                $stmt = $db->prepare("SELECT id FROM games WHERE host_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $gameIds[] = (int) $row["id"];
                }
                $stmt->close();
            }

            if (!empty($gameIds)) {
                delete_by_ids($db, "questions", "game_id", $gameIds);
                delete_by_ids($db, "answers", "game_id", $gameIds);
            }

            delete_by_user_id($db, "answers", "user_id", $userId);
            delete_by_user_id($db, "games", "host_id", $userId);

            // Images
            $imageIds = [];
            if (table_exists($db, "generated_images")) {
                $stmt = $db->prepare("SELECT id FROM generated_images WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $imageIds[] = (int) $row["id"];
                }
                $stmt->close();
            }
            if (!empty($imageIds)) {
                delete_by_ids($db, "image_reactions", "image_id", $imageIds);
            }

            delete_by_user_id($db, "generated_images", "user_id", $userId);
            delete_by_user_id($db, "image_reactions", "user_id", $userId);

            // Auth + sessions
            delete_by_user_id($db, "sessions", "user_id", $userId);
            delete_by_user_id($db, "google_auth", "user_id", $userId);
            delete_by_user_id($db, "gb_auth", "user_id", $userId);
            delete_by_user_id($db, "magic_login_tokens", "user_id", $userId);

            // Usage + billing
            delete_by_user_id($db, "usage_logs", "user_id", $userId);
            delete_by_user_id($db, "thinking_usage_logs", "user_id", $userId);
            delete_by_user_id($db, "transactions", "user_id", $userId);
            delete_by_user_id($db, "free_user_daily_usage", "user_id", $userId);
            delete_by_user_id($db, "free_user_limits", "user_id", $userId);
            delete_by_user_id($db, "trial_abuse_tracking", "user_id", $userId);

            // Notifications & onboarding
            delete_by_user_id($db, "notific", "user_id", $userId);
            delete_by_user_id($db, "user_onboarding", "user_id", $userId);

            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            $db->commit();
            respond(true, "User deleted");
        } catch (Exception $e) {
            $db->rollback();
            respond(false, "Failed to delete user", ["error" => $e->getMessage()], 500);
        }
        break;
    }

    case "send_notification": {
        $message = trim($_POST["message"] ?? "");
        $sendAll = (string)($_POST["send_all"] ?? "0") === "1";

        if ($message === "") {
            respond(false, "Message cannot be empty");
        }
        $messageLength = function_exists("mb_strlen") ? mb_strlen($message) : strlen($message);
        if ($messageLength > 255) {
            respond(false, "Message must be 255 characters or fewer");
        }
        if (!table_exists($db, "notific")) {
            respond(false, "Notifications table not found", [], 500);
        }

        if ($sendAll) {
            if (!table_exists($db, "users")) {
                respond(false, "Users table not found", [], 500);
            }
            $stmt = $db->prepare("INSERT INTO notific (user_id, message, is_read, created_at) SELECT id, ?, 0, NOW() FROM users");
            $stmt->bind_param("s", $message);
            $stmt->execute();
            $count = $stmt->affected_rows;
            $stmt->close();
            respond(true, "Notification sent to all users", ["count" => $count]);
        }

        $ids = $_POST["user_ids"] ?? [];
        if (!is_array($ids) || empty($ids)) {
            respond(false, "Select at least one user or choose Send to all");
        }
        $ids = array_values(array_unique(array_map("intval", $ids)));
        $stmt = $db->prepare("INSERT INTO notific (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
        $count = 0;
        foreach ($ids as $id) {
            if ($id <= 0) {
                continue;
            }
            $stmt->bind_param("is", $id, $message);
            $stmt->execute();
            $count++;
        }
        $stmt->close();
        respond(true, "Notification sent", ["count" => $count]);
        break;
    }

    case "update_user_profile": {
        $userId = (int) ($_POST["user_id"] ?? 0);
        if ($userId <= 0) {
            respond(false, "Invalid user");
        }
        if (!table_exists($db, "users")) {
            respond(false, "Users table not found", [], 500);
        }

        $firstName = trim((string) ($_POST["first_name"] ?? ""));
        $lastName = trim((string) ($_POST["last_name"] ?? ""));
        $email = trim((string) ($_POST["email"] ?? ""));
        $phone = trim((string) ($_POST["phone"] ?? ""));
        $image = trim((string) ($_POST["image"] ?? ""));
        $memory = trim((string) ($_POST["memory"] ?? ""));
        $balance = (int) ($_POST["balance"] ?? 0);
        $type = (string) ($_POST["type"] ?? "1");
        $subscription = (int) ($_POST["subscription_status"] ?? 0);
        $onboardComplete = (int) ($_POST["onboard_complete"] ?? 0);
        $emailVerified = (int) ($_POST["email_verified"] ?? 0);
        $failedLogins = (int) ($_POST["failed_login_attempts"] ?? 0);
        $lockedUntilRaw = trim((string) ($_POST["locked_until"] ?? ""));
        $lockedUntil = null;
        if ($lockedUntilRaw !== "") {
            $ts = strtotime($lockedUntilRaw);
            if ($ts === false) {
                respond(false, "Invalid locked_until value");
            }
            $lockedUntil = date("Y-m-d H:i:s", $ts);
        }

        if (!in_array($type, ["1", "2", "3"], true)) {
            respond(false, "Invalid user type");
        }

        $stmt = $db->prepare("
            UPDATE users
            SET first_name = ?, last_name = ?, email = ?, phone = ?, image = ?, balance = ?,
                type = ?, subscription_status = ?, onboard_complete = ?, email_verified = ?,
                failed_login_attempts = ?, locked_until = ?, memory = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "sssssisiiiissi",
            $firstName,
            $lastName,
            $email,
            $phone,
            $image,
            $balance,
            $type,
            $subscription,
            $onboardComplete,
            $emailVerified,
            $failedLogins,
            $lockedUntil,
            $memory,
            $userId
        );
        $stmt->execute();
        $stmt->close();

        if (table_exists($db, "user_onboarding")) {
            $school = trim((string) ($_POST["school"] ?? ""));
            $notStudent = (int) ($_POST["not_student"] ?? 0);
            $interests = trim((string) ($_POST["interests"] ?? ""));
            $preference = trim((string) ($_POST["preference"] ?? ""));
            if (!in_array($preference, ["friendly", "educational", "explanatory", "concise"], true)) {
                $preference = null;
            }

            $stmt = $db->prepare("SELECT id FROM user_onboarding WHERE user_id = ? LIMIT 1");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            $exists = $res && $res->num_rows > 0;
            $stmt->close();

            if ($exists) {
                $stmt = $db->prepare("
                    UPDATE user_onboarding
                    SET school = ?, not_student = ?, interests = ?, preference = ?
                    WHERE user_id = ?
                ");
                $stmt->bind_param("sissi", $school, $notStudent, $interests, $preference, $userId);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $db->prepare("
                    INSERT INTO user_onboarding (user_id, school, not_student, interests, preference)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("isiss", $userId, $school, $notStudent, $interests, $preference);
                $stmt->execute();
                $stmt->close();
            }
        }

        respond(true, "User profile updated");
        break;
    }

    case "update_free_user_daily_usage":
    case "update_free_user_limit": {
        $userId = (int) ($_POST["user_id"] ?? 0);
        $dateRaw = trim((string) ($_POST["date"] ?? ""));
        $windowId = (int) ($_POST["window_id"] ?? 0);
        $messagesUsed = (int) ($_POST["messages_used"] ?? 0);
        $imageUploadsUsed = (int) ($_POST["image_uploads_used"] ?? 0);
        $fileUploadsUsed = (int) ($_POST["file_uploads_used"] ?? 0);
        $imageGenerationsUsed = (int) ($_POST["image_generations_used"] ?? 0);

        if ($userId <= 0) {
            respond(false, "Invalid user");
        }
        if ($dateRaw === "") {
            respond(false, "Invalid date");
        }
        if ($windowId < 0) {
            respond(false, "Invalid window");
        }
        if (!table_exists($db, "free_user_daily_usage")) {
            respond(false, "Free user daily usage table not found", [], 500);
        }

        $ts = strtotime($dateRaw);
        if ($ts === false) {
            respond(false, "Invalid date");
        }
        $date = date("Y-m-d", $ts);

        $stmt = $db->prepare("
            UPDATE free_user_daily_usage
            SET messages_used = ?, image_uploads_used = ?, file_uploads_used = ?, image_generations_used = ?
            WHERE user_id = ? AND date = ? AND window_id = ?
        ");
        $stmt->bind_param(
            "iiiiisi",
            $messagesUsed,
            $imageUploadsUsed,
            $fileUploadsUsed,
            $imageGenerationsUsed,
            $userId,
            $date,
            $windowId
        );
        $stmt->execute();
        $stmt->close();
        respond(true, "Free user usage updated");
        break;
    }

    case "update_trial_abuse": {
        $id = (int) ($_POST["id"] ?? 0);
        $userId = (int) ($_POST["user_id"] ?? 0);
        $ipAddress = trim((string) ($_POST["ip_address"] ?? ""));
        $fingerprint = trim((string) ($_POST["device_fingerprint"] ?? ""));
        $trialStartRaw = trim((string) ($_POST["trial_start_date"] ?? ""));
        $trialEndRaw = trim((string) ($_POST["trial_end_date"] ?? ""));

        if ($id <= 0) {
            respond(false, "Invalid row");
        }
        if (!table_exists($db, "trial_abuse_tracking")) {
            respond(false, "Trial abuse table not found", [], 500);
        }

        $trialStart = null;
        if ($trialStartRaw !== "") {
            $ts = strtotime($trialStartRaw);
            if ($ts === false) {
                respond(false, "Invalid trial_start_date value");
            }
            $trialStart = date("Y-m-d", $ts);
        }

        $trialEnd = null;
        if ($trialEndRaw !== "") {
            $ts = strtotime($trialEndRaw);
            if ($ts === false) {
                respond(false, "Invalid trial_end_date value");
            }
            $trialEnd = date("Y-m-d", $ts);
        }

        $stmt = $db->prepare("
            UPDATE trial_abuse_tracking
            SET user_id = NULLIF(?, 0), ip_address = ?, device_fingerprint = ?, trial_start_date = ?, trial_end_date = ?
            WHERE id = ?
        ");
        $stmt->bind_param("issssi", $userId, $ipAddress, $fingerprint, $trialStart, $trialEnd, $id);
        $stmt->execute();
        $stmt->close();
        respond(true, "Trial abuse record updated");
        break;
    }

    case "update_bug_status": {
        ensure_bug_report_status_column($db);
        if (!table_exists($db, "bug_reports")) {
            respond(false, "Bug reports table not found", [], 500);
        }
        $bugId = (int) ($_POST["bug_id"] ?? 0);
        $status = (string) ($_POST["status"] ?? "");
        if ($bugId <= 0) {
            respond(false, "Invalid bug report");
        }
        if (!in_array($status, ["open", "fixed"], true)) {
            respond(false, "Invalid status");
        }
        $stmt = $db->prepare("UPDATE bug_reports SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $bugId);
        $stmt->execute();
        $stmt->close();
        respond(true, "Bug status updated");
        break;
    }

    case "delete_bug": {
        if (!table_exists($db, "bug_reports")) {
            respond(false, "Bug reports table not found", [], 500);
        }
        $bugId = (int) ($_POST["bug_id"] ?? 0);
        if ($bugId <= 0) {
            respond(false, "Invalid bug report");
        }
        $stmt = $db->prepare("DELETE FROM bug_reports WHERE id = ?");
        $stmt->bind_param("i", $bugId);
        $stmt->execute();
        $stmt->close();
        respond(true, "Bug report deleted");
        break;
    }

    default:
        respond(false, "Invalid action", [], 400);
}
