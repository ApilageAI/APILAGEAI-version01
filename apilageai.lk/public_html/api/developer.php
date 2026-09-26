<?php
/**
 * ApilageAI Developer API Management
 *
 * Manage API keys, domains, and usage.
 *
 * @package ApilageAI
 */

define('APILAGE_EXPECTS_JSON', true);
require_once __DIR__ . '/../../backend/bootstrap.php';

header('Content-Type: application/json');

if (!$user->_logged_in) {
    http_response_code(401);
    returnJSON(["e" => true, "m" => "Authentication required"]);
}

// Allow JSON payloads
$rawPayload = file_get_contents('php://input');
if (!empty($rawPayload)) {
    $jsonPayload = json_decode($rawPayload, true);
    if (is_array($jsonPayload)) {
        $_POST = array_merge($_POST, $jsonPayload);
    }
}
if (function_exists('apilage_sanitize_array_input')) {
    $_POST = apilage_sanitize_array_input($_POST);
}

$action = $_GET["act"] ?? '';
if (empty($action)) {
    http_response_code(400);
    returnJSON(["e" => true, "m" => "Action required"]);
}

function bind_params($stmt, $types, $values) {
    $refs = [];
    foreach ($values as $key => $value) {
        $refs[$key] = &$values[$key];
    }
    $stmt->bind_param($types, ...$refs);
}

function normalize_domain($domain) {
    $domain = strtolower(trim((string) $domain));
    if ($domain === '') return '';
    $domain = preg_replace('/\s+/', '', $domain);
    if (strpos($domain, '://') !== false) {
        $parts = parse_url($domain);
        if (!$parts || empty($parts['host']) || empty($parts['scheme'])) return '';
        $origin = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }
        return $origin;
    }
    $domain = preg_replace('#/.*$#', '', $domain);
    return $domain;
}

function parse_domains($input) {
    $domains = [];
    if (is_array($input)) {
        $items = $input;
    } else {
        $items = preg_split('/[\r\n,]+/', (string) $input);
    }
    foreach ($items as $item) {
        $normalized = normalize_domain($item);
        if ($normalized !== '') {
            $domains[$normalized] = true;
        }
    }
    return array_keys($domains);
}

switch ($action) {
    case "list":
        $userId = (int) $user->_data['id'];
        // Send pending leak alerts once
        $alertStmt = $db->prepare(
            "SELECT a.id, a.alert_type, a.detail, a.detected_at, k.name, k.api_key_prefix
             FROM developer_api_key_alerts a
             JOIN developer_api_keys k ON k.id = a.api_key_id
             WHERE k.user_id = ? AND a.notified = 0
             ORDER BY a.detected_at DESC"
        );
        $alertStmt->bind_param("i", $userId);
        $alertStmt->execute();
        $alertResult = $alertStmt->get_result();
        $pendingAlerts = $alertResult->fetch_all(MYSQLI_ASSOC);
        $alertStmt->close();

        if (!empty($pendingAlerts)) {
            $email = $user->_data['email'] ?? '';
            $firstName = $user->_data['first_name'] ?? 'Developer';
            foreach ($pendingAlerts as $alert) {
                $detail = $alert['detail'] ?? '';
                $body = get_email_template("developer_api_key_leak", [
                    "name" => $firstName,
                    "key_name" => $alert['name'],
                    "key_prefix" => $alert['api_key_prefix'],
                    "detected_at" => $alert['detected_at'],
                    "detail" => $detail,
                ]);
                if (!empty($email)) {
                    _email($email, "Security Alert: ApilageAI API Key Leak Detected", $body);
                }
            }

            $alertIds = array_map(fn($row) => (int) $row['id'], $pendingAlerts);
            if (!empty($alertIds)) {
                $placeholders = implode(',', array_fill(0, count($alertIds), '?'));
                $types = str_repeat('i', count($alertIds));
                $stmt = $db->prepare("UPDATE developer_api_key_alerts SET notified = 1 WHERE id IN ($placeholders)");
                bind_params($stmt, $types, $alertIds);
                $stmt->execute();
                $stmt->close();
            }
        }
        $stmt = $db->prepare(
            "SELECT k.id, k.name, k.api_key_prefix, k.status, k.created_at, k.last_used_at, k.system_prompt,
                    COUNT(u.id) AS requests_30d,
                    COALESCE(SUM(u.total_tokens), 0) AS tokens_30d,
                    COALESCE(SUM(u.total_cost_lkr), 0) AS cost_30d
             FROM developer_api_keys k
             LEFT JOIN developer_api_usage u
               ON u.api_key_id = k.id AND u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             WHERE k.user_id = ?
             GROUP BY k.id
             ORDER BY k.created_at DESC"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $keys = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $domainMap = [];
        $keyIds = array_map(fn($row) => (int) $row['id'], $keys);
        if (!empty($keyIds)) {
            $placeholders = implode(',', array_fill(0, count($keyIds), '?'));
            $types = str_repeat('i', count($keyIds));
            $stmt = $db->prepare("SELECT api_key_id, domain FROM developer_api_domains WHERE api_key_id IN ($placeholders)");
            bind_params($stmt, $types, $keyIds);
            $stmt->execute();
            $domainsResult = $stmt->get_result();
            while ($row = $domainsResult->fetch_assoc()) {
                $kid = (int) $row['api_key_id'];
                if (!isset($domainMap[$kid])) $domainMap[$kid] = [];
                $domainMap[$kid][] = $row['domain'];
            }
            $stmt->close();
        }

        $payload = [];
        foreach ($keys as $row) {
            $kid = (int) $row['id'];
            $payload[] = [
                "id" => $kid,
                "name" => $row['name'],
                "prefix" => $row['api_key_prefix'],
                "status" => $row['status'],
                "created_at" => $row['created_at'],
                "last_used_at" => $row['last_used_at'],
                "system_prompt" => $row['system_prompt'] ?? '',
                "domains" => $domainMap[$kid] ?? [],
                "usage" => [
                    "requests_30d" => (int) $row['requests_30d'],
                    "tokens_30d" => (int) $row['tokens_30d'],
                    "cost_30d" => (float) $row['cost_30d'],
                ],
            ];
        }

        returnJSON([
            "e" => false,
            "keys" => $payload,
            "balance" => (float)($user->_data['balance'] ?? 0),
        ]);
        break;

    case "create":
        $userId = (int) $user->_data['id'];
        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM developer_api_keys WHERE user_id = ?");
        $countStmt->bind_param("i", $userId);
        $countStmt->execute();
        $countRow = $countStmt->get_result()->fetch_assoc();
        $countStmt->close();
        if (!empty($countRow) && (int) $countRow['total'] >= 10) {
            returnJSON(["e" => true, "m" => "You can create up to 10 API keys."]);
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            returnJSON(["e" => true, "m" => "Key name required"]);
        }
        if (strlen($name) > 80) {
            $name = substr($name, 0, 80);
        }

        $apiKey = '';
        $prefix = '';
        $hash = '';
        $keyId = 0;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $apiKey = 'apk_' . bin2hex(random_bytes(24));
            $prefix = substr($apiKey, 0, 8);
            $hash = hash('sha256', $apiKey);
            try {
                $stmt = $db->prepare(
                    "INSERT INTO developer_api_keys (user_id, name, api_key_hash, api_key_prefix, status, created_at)
                     VALUES (?, ?, ?, ?, 'active', NOW())"
                );
                $stmt->bind_param("isss", $userId, $name, $hash, $prefix);
                $stmt->execute();
                $keyId = $stmt->insert_id;
                $stmt->close();
                break;
            } catch (Exception $e) {
                // Retry on duplicate key hash
                continue;
            }
        }

        if (!$keyId) {
            http_response_code(500);
            returnJSON(["e" => true, "m" => "Failed to create API key"]);
        }

        $defaults = ['localhost', '127.0.0.1'];
        $host = parse_url(APP_URL, PHP_URL_HOST);
        if (!empty($host)) {
            $defaults[] = $host;
        }
        $defaults = array_values(array_unique($defaults));
        $stmt = $db->prepare("INSERT INTO developer_api_domains (api_key_id, domain) VALUES (?, ?)");
        foreach ($defaults as $domain) {
            $normalized = normalize_domain($domain);
            if ($normalized === '') continue;
            $stmt->bind_param("is", $keyId, $normalized);
            $stmt->execute();
        }
        $stmt->close();

        $email = $user->_data['email'] ?? '';
        $firstName = $user->_data['first_name'] ?? 'Developer';
        if (!empty($email)) {
            $body = get_email_template("developer_api_key_created", [
                "name" => $firstName,
                "key_name" => $name,
                "key_prefix" => $prefix,
                "created_at" => date('Y-m-d H:i:s'),
            ]);
            _email($email, "Your ApilageAI API Key Was Created", $body);
        }

        returnJSON([
            "e" => false,
            "key" => $apiKey,
            "id" => $keyId,
            "prefix" => $prefix,
            "domains" => $defaults
        ]);
        break;

    case "toggle":
        $userId = (int) $user->_data['id'];
        $keyId = (int) ($_POST['key_id'] ?? 0);
        $status = strtolower(trim((string) ($_POST['status'] ?? '')));
        if (!$keyId || !in_array($status, ['active', 'disabled'], true)) {
            returnJSON(["e" => true, "m" => "Invalid request"]);
        }
        $stmt = $db->prepare("UPDATE developer_api_keys SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $keyId, $userId);
        $stmt->execute();
        $stmt->close();
        returnJSON(["e" => false, "status" => $status]);
        break;

    case "delete":
        $userId = (int) $user->_data['id'];
        $keyId = (int) ($_POST['key_id'] ?? 0);
        if (!$keyId) {
            returnJSON(["e" => true, "m" => "Invalid request"]);
        }
        $stmt = $db->prepare("SELECT name, api_key_prefix FROM developer_api_keys WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $keyId, $userId);
        $stmt->execute();
        $keyInfo = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (empty($keyInfo)) {
            returnJSON(["e" => true, "m" => "Key not found"]);
        }
        $stmt = $db->prepare("DELETE FROM developer_api_keys WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $keyId, $userId);
        $stmt->execute();
        $stmt->close();

        $email = $user->_data['email'] ?? '';
        $firstName = $user->_data['first_name'] ?? 'Developer';
        if (!empty($email)) {
            $body = get_email_template("developer_api_key_deleted", [
                "name" => $firstName,
                "key_name" => $keyInfo['name'],
                "key_prefix" => $keyInfo['api_key_prefix'],
                "deleted_at" => date('Y-m-d H:i:s'),
            ]);
            _email($email, "Your ApilageAI API Key Was Deleted", $body);
        }
        returnJSON(["e" => false]);
        break;

    case "update_domains":
        $userId = (int) $user->_data['id'];
        $keyId = (int) ($_POST['key_id'] ?? 0);
        $domains = parse_domains($_POST['domains'] ?? '');
        if (!$keyId || empty($domains)) {
            returnJSON(["e" => true, "m" => "At least one domain is required"]);
        }
        if (count($domains) > 5) {
            returnJSON(["e" => true, "m" => "Maximum 5 domains allowed"]);
        }

        $stmt = $db->prepare("SELECT id FROM developer_api_keys WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $keyId, $userId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$exists) {
            returnJSON(["e" => true, "m" => "Key not found"]);
        }

        $db->begin_transaction();
        try {
            $stmt = $db->prepare("DELETE FROM developer_api_domains WHERE api_key_id = ?");
            $stmt->bind_param("i", $keyId);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare("INSERT INTO developer_api_domains (api_key_id, domain) VALUES (?, ?)");
            foreach ($domains as $domain) {
                $stmt->bind_param("is", $keyId, $domain);
                $stmt->execute();
            }
            $stmt->close();
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            returnJSON(["e" => true, "m" => "Failed to update domains"]);
        }

        returnJSON(["e" => false, "domains" => $domains]);
        break;

    case "update_system":
        $userId = (int) $user->_data['id'];
        $keyId = (int) ($_POST['key_id'] ?? 0);
        $system = trim((string) ($_POST['system'] ?? ''));
        if (!$keyId) {
            returnJSON(["e" => true, "m" => "Invalid request"]);
        }
        if (strlen($system) > 4000) {
            $system = substr($system, 0, 4000);
        }
        $stmt = $db->prepare("UPDATE developer_api_keys SET system_prompt = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $system, $keyId, $userId);
        $stmt->execute();
        $stmt->close();
        returnJSON(["e" => false]);
        break;

    default:
        http_response_code(404);
        returnJSON(["e" => true, "m" => "Invalid action"]);
}
