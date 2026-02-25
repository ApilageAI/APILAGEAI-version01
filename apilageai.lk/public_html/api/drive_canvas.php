<?php
/**
 * Google Drive Canvas API
 *
 * Handles OAuth connection, listing, loading, and saving canvas files.
 *
 * @package ApilageAI
 */

require_once __DIR__ . '/../../backend/bootstrap.php';
require_once __DIR__ . '/../../backend/google_drive.php';

// OAuth connect / callback flow (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$user->_logged_in || !empty($_SESSION['guest_mode'])) {
        header("Location: " . APP_URL . "/auth/login");
        exit;
    }

    $client = apilage_drive_client();

    if (isset($_GET['connect'])) {
        $state = bin2hex(random_bytes(16));
        $client->setState($state);
        $_SESSION['google_drive_oauth_state'] = $state;
        $_SESSION['google_drive_oauth_user_id'] = (int)$user->_data['id'];

        $authUrl = $client->createAuthUrl();
        header("Location: " . $authUrl);
        exit;
    }

    if (isset($_GET['code'])) {
        $expectedState = $_SESSION['google_drive_oauth_state'] ?? '';
        $state = $_GET['state'] ?? '';
        if (!$expectedState || $state !== $expectedState) {
            unset($_SESSION['google_drive_oauth_state'], $_SESSION['google_drive_oauth_user_id']);
            header("Location: " . APP_URL . "/app?drive=state_mismatch");
            exit;
        }

        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            unset($_SESSION['google_drive_oauth_state'], $_SESSION['google_drive_oauth_user_id']);
            header("Location: " . APP_URL . "/app?drive=auth_failed");
            exit;
        }

        $userId = (int)($_SESSION['google_drive_oauth_user_id'] ?? ($user->_data['id'] ?? 0));
        $expiresAt = null;
        if (!empty($token['expires_in'])) {
            $expiresAt = date('Y-m-d H:i:s', time() + (int)$token['expires_in']);
        }

        apilage_drive_upsert_token_row($db, $userId, [
            'refresh_token' => $token['refresh_token'] ?? null,
            'access_token' => $token['access_token'] ?? null,
            'expires_at' => $expiresAt,
            'scope' => $token['scope'] ?? null,
        ]);

        unset($_SESSION['google_drive_oauth_state'], $_SESSION['google_drive_oauth_user_id']);
        header("Location: " . APP_URL . "/app?drive=connected");
        exit;
    }

    http_response_code(400);
    echo "Invalid request";
    exit;
}

// API JSON responses (POST)
header('Content-Type: application/json');

if (!$user->_logged_in || !empty($_SESSION['guest_mode'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? '';
$userId = (int)$user->_data['id'];

if (!$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Action is required']);
    exit;
}

switch ($action) {
    case 'status':
        $tokenRow = apilage_drive_get_token_row($db, $userId);
        $connected = !empty($tokenRow['refresh_token']);

        $email = null;
        $stmt = $db->prepare("SELECT google_email FROM google_auth WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $email = $result->fetch_assoc()['google_email'];
        }
        $stmt->close();

        echo json_encode([
            'success' => true,
            'connected' => $connected,
            'email' => $email,
        ]);
        exit;

    case 'disconnect':
        apilage_drive_clear_token_row($db, $userId);
        echo json_encode(['success' => true]);
        exit;

    case 'list':
        $drive = apilage_drive_get_service($db, $userId);
        if (!$drive) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Drive not connected']);
            exit;
        }

        $tokenRow = apilage_drive_get_token_row($db, $userId);
        $folderId = apilage_drive_get_or_create_folder($drive, $tokenRow['folder_id'] ?? null);
        if ($folderId && ($tokenRow['folder_id'] ?? null) !== $folderId) {
            apilage_drive_upsert_token_row($db, $userId, ['folder_id' => $folderId]);
        }

        $query = sprintf("'%s' in parents and trashed = false", addslashes($folderId));
        $results = $drive->files->listFiles([
            'q' => $query,
            'pageSize' => 100,
            'orderBy' => 'modifiedTime desc',
            'fields' => 'files(id, name, modifiedTime, mimeType)',
        ]);

        $files = [];
        foreach ($results->getFiles() as $file) {
            $files[] = [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'modifiedTime' => $file->getModifiedTime(),
                'mimeType' => $file->getMimeType(),
            ];
        }

        echo json_encode(['success' => true, 'files' => $files]);
        exit;

    case 'get':
        $fileId = $input['fileId'] ?? '';
        if (!$fileId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'fileId is required']);
            exit;
        }

        $drive = apilage_drive_get_service($db, $userId);
        if (!$drive) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Drive not connected']);
            exit;
        }

        try {
            $file = $drive->files->get($fileId, ['fields' => 'id, name, modifiedTime']);
            $response = $drive->files->get($fileId, ['alt' => 'media']);
            $content = $response->getBody()->getContents();

            echo json_encode([
                'success' => true,
                'file' => [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'modifiedTime' => $file->getModifiedTime(),
                ],
                'content' => $content,
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to load file']);
            exit;
        }

    case 'create':
        $name = trim((string)($input['name'] ?? 'Untitled Canvas'));
        if ($name === '') {
            $name = 'Untitled Canvas';
        }
        $content = $input['content'] ?? null;
        $contentString = is_string($content) ? $content : json_encode($content ?? [], JSON_UNESCAPED_UNICODE);

        $drive = apilage_drive_get_service($db, $userId);
        if (!$drive) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Drive not connected']);
            exit;
        }

        $tokenRow = apilage_drive_get_token_row($db, $userId);
        $folderId = apilage_drive_get_or_create_folder($drive, $tokenRow['folder_id'] ?? null);
        if ($folderId && ($tokenRow['folder_id'] ?? null) !== $folderId) {
            apilage_drive_upsert_token_row($db, $userId, ['folder_id' => $folderId]);
        }

        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $name,
            'parents' => [$folderId],
            'mimeType' => 'application/json',
        ]);

        try {
            $file = $drive->files->create($fileMetadata, [
                'data' => $contentString,
                'mimeType' => 'application/json',
                'uploadType' => 'multipart',
                'fields' => 'id, name, modifiedTime',
            ]);

            echo json_encode([
                'success' => true,
                'file' => [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'modifiedTime' => $file->getModifiedTime(),
                ],
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create file']);
            exit;
        }

    case 'save':
        $fileId = $input['fileId'] ?? '';
        if (!$fileId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'fileId is required']);
            exit;
        }

        $content = $input['content'] ?? null;
        $contentString = is_string($content) ? $content : json_encode($content ?? [], JSON_UNESCAPED_UNICODE);

        $drive = apilage_drive_get_service($db, $userId);
        if (!$drive) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Drive not connected']);
            exit;
        }

        try {
            $file = $drive->files->update($fileId, new Google_Service_Drive_DriveFile(), [
                'data' => $contentString,
                'mimeType' => 'application/json',
                'uploadType' => 'media',
                'fields' => 'id, modifiedTime',
            ]);

            echo json_encode([
                'success' => true,
                'file' => [
                    'id' => $file->getId(),
                    'modifiedTime' => $file->getModifiedTime(),
                ],
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save file']);
            exit;
        }

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
}
