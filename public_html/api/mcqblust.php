<?php
define('APILAGE_LOADED', true);
require_once __DIR__ . '/../../backend/config.php';
/**
 * ApilageAI MCQ Blast Configuration API
 * 
 * Returns Firebase and Gemini configuration for MCQ Blast game
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../../backend/bootstrap.php';

// Security headers
header('Content-Type: application/json');

// Allowed referrers
$allowedReferrers = [
    APP_URL,
    'https://www.apilageai.edu.lk',
];

// Get the Referer header
$referer = $_SERVER['HTTP_REFERER'] ?? '';

// Check if the referer matches allowed domains
$accessAllowed = false;
foreach ($allowedReferrers as $allowed) {
    if (strpos($referer, $allowed) === 0) {
        $accessAllowed = true;
        break;
    }
}

// Also allow if user is logged in
if ($user->_logged_in) {
    $accessAllowed = true;
}

// Deny access if not allowed
if (!$accessAllowed) {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

// Firebase Configuration (from config.php constants)
$firebaseConfig = [
    "apiKey" => FIREBASE_API_KEY,
    "authDomain" => FIREBASE_AUTH_DOMAIN,
    "databaseURL" => FIREBASE_DATABASE_URL,
    "projectId" => FIREBASE_PROJECT_ID,
    "storageBucket" => FIREBASE_STORAGE_BUCKET,
    "messagingSenderId" => FIREBASE_MESSAGING_SENDER_ID,
    "appId" => FIREBASE_APP_ID,
    "measurementId" => FIREBASE_MEASUREMENT_ID
];

// Return JSON
echo json_encode([
    "firebaseConfig" => $firebaseConfig,
    "geminiApiKey" => GEMINI_API_KEY_MCQBLUST
]);
