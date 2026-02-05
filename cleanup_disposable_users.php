<?php
/**
 * ApilageAI Disposable User Cleanup Script
 * 
 * This script identifies users with disposable email addresses,
 * deletes their data from the database, and removes their uploaded/generated files.
 * 
 * Usage: php cleanup_disposable_users.php [--dry-run]
 * 
 * @package ApilageAI
 */

// Define APILAGE_LOADED to allow including bootstrap
define('APILAGE_LOADED', true);

require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/../backend/functions.php';

$dryRun = false;

if ($dryRun) {
    echo "--- DRY RUN MODE: No data will be deleted ---\n";
}

echo "Fetching all users...\n";

$result = $db->query("SELECT id, email, first_name, last_name, image FROM users");
$users = $result->fetch_all(MYSQLI_ASSOC);

echo "Found " . count($users) . " users. Checking for disposable emails...\n";

$disposableCount = 0;

foreach ($users as $userRow) {
    $email = $userRow['email'];
    echo "Checking {$email}... ";
    
    if (isDisposableEmail($email)) {
        echo "[DISPOSABLE]\n";
        $disposableCount++;
        
        if (!$dryRun) {
            cleanupUserData($userRow['id'], $userRow['image']);
        } else {
            echo "  (Dry run) Would delete user ID {$userRow['id']} and all related data.\n";
        }
    } else {
        echo "[OK]\n";
    }
}

echo "\nDone. Found and processed $disposableCount disposable users.\n";

/**
 * Cleanup all data related to a user
 */
function cleanupUserData($userId, $profileImage) {
    global $db;
    
    echo "  Cleaning up user ID $userId...\n";
    
    // 1. Delete generated images from disk
    $stmt = $db->prepare("SELECT image_url FROM generated_images WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $path = __DIR__ . '/../../public_html/' . ltrim(parse_url($row['image_url'], PHP_URL_PATH), '/');
        if (file_exists($path) && is_file($path)) {
            unlink($path);
            echo "    Deleted image: " . basename($path) . "\n";
        }
    }
    $stmt->close();
    
    // 2. Delete profile image from disk
    if (!empty($profileImage)) {
        $path = __DIR__ . '/../../public_html/' . ltrim(parse_url($profileImage, PHP_URL_PATH), '/');
        if (file_exists($path) && is_file($path)) {
            unlink($path);
            echo "    Deleted profile photo: " . basename($path) . "\n";
        }
    }
    
    // 3. Delete from database (Order matters for foreign keys if any)
    $tables = [
        ['messages', 'user_id'],
        ['conversations', 'user_id'],
        ['generated_images', 'user_id'],
        ['image_reactions', 'user_id'],
        ['answers', 'user_id'],
        ['gb_auth', 'user_id'],
        ['google_auth', 'user_id'],
        ['notific', 'user_id'],
        ['sessions', 'user_id'],
        ['thinking_usage_logs', 'user_id'],
        ['user_onboarding', 'user_id'],
        ['transactions', 'user_id'],
        ['users', 'id']
    ];
    
    foreach ($tables as $table) {
        $tableName = $table[0];
        $columnName = $table[1];
        
        $sql = "DELETE FROM `$tableName` WHERE `$columnName` = ?";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
            echo "    Cleared table: $tableName\n";
        }
    }
    
    echo "  User $userId fully removed.\n";
}
