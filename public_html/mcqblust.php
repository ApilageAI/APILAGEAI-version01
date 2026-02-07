<?php
define('APILAGE_LOADED', true);
require_once __DIR__ . '/../backend/config.php';
header('Content-Type: application/json');

// Allowed referrers (your site and trusted services)
$allowedReferrers = [
    APP_URL,
    'https://apilageai.lk',
    'https://firebase.google.com',
    'https://www.googleapis.com'
];

// Get the Referer header
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// Check if the referer matches allowed domains
$accessAllowed = false;
foreach ($allowedReferrers as $allowed) {
    if (strpos($referer, $allowed) === 0) {
        $accessAllowed = true;
        break;
    }
}

// Deny access if not allowed
if (!$accessAllowed) {
    http_response_code(403); // Forbidden
    echo json_encode(["error" => "Access denied."]);
    exit;
}

// ------------------- Firebase Configuration -------------------
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

// ------------------- Gemini API Key -------------------
$geminiApiKey = GEMINI_API_KEY_MCQBLUST;

// ------------------- Return JSON -------------------
echo json_encode([
    "firebaseConfig" => $firebaseConfig,
    "geminiApiKey" => $geminiApiKey
]);
?>
