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
if (!verify_csrf_token($csrf)) {
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
