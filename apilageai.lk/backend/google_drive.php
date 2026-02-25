<?php
/**
 * Google Drive helper functions for Canvas integration.
 *
 * @package ApilageAI
 */

if (!defined('APILAGE_LOADED')) {
    http_response_code(403);
    exit('Access denied');
}

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

if (!defined('GOOGLE_DRIVE_REDIRECT_URI')) {
    define('GOOGLE_DRIVE_REDIRECT_URI', APP_URL . '/api/drive_canvas.php');
}

if (!defined('GOOGLE_DRIVE_FOLDER_NAME')) {
    define('GOOGLE_DRIVE_FOLDER_NAME', 'ApilageAI Canvas');
}

/**
 * Ensure the Google Drive token table exists.
 */
function apilage_drive_ensure_table(mysqli $db): bool
{
    $sql = "CREATE TABLE IF NOT EXISTS `google_drive_tokens` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `refresh_token` varchar(512) DEFAULT NULL,
        `access_token` text DEFAULT NULL,
        `expires_at` datetime DEFAULT NULL,
        `scope` varchar(512) DEFAULT NULL,
        `folder_id` varchar(128) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_google_drive_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    return (bool)$db->query($sql);
}

/**
 * Create a Google Client configured for Drive.
 */
function apilage_drive_client(): Google_Client
{
    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_DRIVE_REDIRECT_URI);
    $client->setAccessType('offline');
    $client->setPrompt('consent');
    $client->addScope(Google_Service_Drive::DRIVE_FILE);

    return $client;
}

/**
 * Fetch Drive token row for a user.
 */
function apilage_drive_get_token_row(mysqli $db, int $userId): ?array
{
    if (!apilage_drive_ensure_table($db)) {
        return null;
    }

    $stmt = $db->prepare("SELECT * FROM google_drive_tokens WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->num_rows ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row;
}

/**
 * Upsert Drive token row for a user.
 */
function apilage_drive_upsert_token_row(mysqli $db, int $userId, array $data): void
{
    apilage_drive_ensure_table($db);

    $refreshToken = $data['refresh_token'] ?? null;
    $accessToken = $data['access_token'] ?? null;
    $expiresAt = $data['expires_at'] ?? null;
    $scope = $data['scope'] ?? null;
    $folderId = $data['folder_id'] ?? null;

    $stmt = $db->prepare(
        "INSERT INTO google_drive_tokens (user_id, refresh_token, access_token, expires_at, scope, folder_id)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            refresh_token = COALESCE(VALUES(refresh_token), refresh_token),
            access_token = VALUES(access_token),
            expires_at = VALUES(expires_at),
            scope = COALESCE(VALUES(scope), scope),
            folder_id = COALESCE(VALUES(folder_id), folder_id)"
    );
    $stmt->bind_param("isssss", $userId, $refreshToken, $accessToken, $expiresAt, $scope, $folderId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Clear Drive token row for a user.
 */
function apilage_drive_clear_token_row(mysqli $db, int $userId): void
{
    if (!apilage_drive_ensure_table($db)) {
        return;
    }
    $stmt = $db->prepare("DELETE FROM google_drive_tokens WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Apply tokens to the client and refresh if needed.
 */
function apilage_drive_apply_token(mysqli $db, int $userId, Google_Client $client, array $tokenRow): bool
{
    $refreshToken = $tokenRow['refresh_token'] ?? null;
    if (!$refreshToken) {
        return false;
    }

    $accessToken = $tokenRow['access_token'] ?? null;
    $expiresAt = $tokenRow['expires_at'] ?? null;
    $expiresOk = false;
    if ($accessToken && $expiresAt) {
        $expiresOk = strtotime($expiresAt) > (time() + 60);
    }

    if ($accessToken && $expiresOk) {
        $client->setAccessToken(['access_token' => $accessToken]);
        return true;
    }

    $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
    if (isset($token['error'])) {
        return false;
    }

    $expiresAt = null;
    if (!empty($token['expires_in'])) {
        $expiresAt = date('Y-m-d H:i:s', time() + (int)$token['expires_in']);
    }

    apilage_drive_upsert_token_row($db, $userId, [
        'access_token' => $token['access_token'] ?? null,
        'expires_at' => $expiresAt,
        'scope' => $token['scope'] ?? ($tokenRow['scope'] ?? null),
    ]);

    $client->setAccessToken($token);
    return true;
}

/**
 * Get Drive service for a user.
 */
function apilage_drive_get_service(mysqli $db, int $userId): ?Google_Service_Drive
{
    $client = apilage_drive_client();
    $tokenRow = apilage_drive_get_token_row($db, $userId);
    if (!$tokenRow) {
        return null;
    }

    if (!apilage_drive_apply_token($db, $userId, $client, $tokenRow)) {
        return null;
    }

    return new Google_Service_Drive($client);
}

/**
 * Ensure canvas folder exists and return folder ID.
 */
function apilage_drive_get_or_create_folder(Google_Service_Drive $drive, ?string $folderId = null): string
{
    if ($folderId) {
        try {
            $drive->files->get($folderId, ['fields' => 'id']);
            return $folderId;
        } catch (Exception $e) {
            // Fall through to re-create if folder was deleted.
        }
    }

    $folderName = GOOGLE_DRIVE_FOLDER_NAME;
    $query = sprintf(
        "mimeType = 'application/vnd.google-apps.folder' and name = '%s' and trashed = false",
        addslashes($folderName)
    );

    $results = $drive->files->listFiles([
        'q' => $query,
        'fields' => 'files(id, name)',
        'pageSize' => 1,
    ]);

    $files = $results->getFiles();
    if (!empty($files)) {
        return $files[0]->getId();
    }

    $fileMetadata = new Google_Service_Drive_DriveFile([
        'name' => $folderName,
        'mimeType' => 'application/vnd.google-apps.folder',
    ]);

    $folder = $drive->files->create($fileMetadata, ['fields' => 'id']);
    return $folder->getId();
}
