<?php
/**
 * ApilageAI Configuration
 * 
 * SECURITY NOTICE:
 * - NEVER commit this file to version control
 * - Use different values for development/staging/production
 * - Rotate credentials regularly
 * 
 * @package ApilageAI
 */

// Prevent direct access
if (!defined('APILAGE_LOADED')) {
    http_response_code(403);
    exit('Access denied');
}

// ================================================================
// DATABASE CONFIGURATION
// ================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'apilageai_main_db');
define('DB_PASS', 'XG3nxPBa5HH6v5Gqs7WT');  // CHANGE THIS!
define('DB_NAME', 'apilageai_main_db');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// ================================================================
// SMTP CONFIGURATION
// ================================================================
define('SMTP_HOST', 'mail.apilageai.lk');
define('SMTP_PORT', 587);
define('SMTP_USER', 'no-reply@apilageai.lk');
define('SMTP_PASS', 'fe9qVkw2Hgery6tC2bqU');  // CHANGE THIS!
define('SMTP_FROM_EMAIL', 'no-reply@apilageai.lk');
define('SMTP_FROM_NAME', 'ApilageAI');
define('SMTP_ENCRYPTION', 'tls');

// ================================================================
// GOOGLE OAUTH CONFIGURATION
// ================================================================
define('GOOGLE_CLIENT_ID', '940750836912-998mgc8pgddlq8h36bnp0gdoaro8a7i0.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');  // CHANGE THIS!
define('GOOGLE_REDIRECT_URI', 'https://apilageai.lk/auth/google-callback');

// ================================================================
// FACEBOOK OAUTH CONFIGURATION
// ================================================================
define('FACEBOOK_APP_ID', '874468478910770');  // CHANGE THIS!
define('FACEBOOK_APP_SECRET', 'a9f431ec60c2caf7675aa2054874e2e7');  // CHANGE THIS!
define('FACEBOOK_REDIRECT_URI', 'https://apilageai.lk/auth/facebook-callback');

// ================================================================
// RECAPTCHA CONFIGURATION
// ================================================================
define('RECAPTCHA_SITE_KEY', '0x4AAAAAACYx8UDWjBj0tpkL');
define('RECAPTCHA_SECRET_KEY', '0x4AAAAAACYx8d1vbSkxAcNPxbWmyxEN-Gc');  // CHANGE THIS!

// ================================================================
// GLOBBOOK AUTH CONFIGURATION
// ================================================================
define('GLOBBOOK_APP_ID', '56532326578385');
define('GLOBBOOK_APP_SECRET', '17f928533a1bc1a580320080cdba8067');  // CHANGE THIS!

// ================================================================
// SECURITY SETTINGS
// ================================================================
define('SESSION_LIFETIME', 31536000);  // 1 year
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 1800);  // 30 minutes
define('MAX_SESSIONS_PER_USER', 5);

// Rate limiting
define('RATE_LIMIT_LOGIN_ATTEMPTS', 5);
define('RATE_LIMIT_LOGIN_WINDOW', 900);  // 15 minutes
define('RATE_LIMIT_SIGNUP_ATTEMPTS', 3);
define('RATE_LIMIT_SIGNUP_WINDOW', 3600);  // 1 hour

// ================================================================
// APPLICATION SETTINGS
// ================================================================
define('APP_URL', 'https://apilageai.lk');
define('NODE_API_BASE', 'https://socket.apilageai.lk');
define('UPLOADS_BASE_URL', 'https://socket.apilageai.lk');  // File uploads served from Node.js server
define('ALLOWED_ORIGINS', ['https://apilageai.lk']);
define('COOKIE_DOMAIN', '.apilageai.lk');
define('COOKIE_SAMESITE', 'Strict');
define('APP_NAME', 'ApilageAI');
define('APP_ENV', 'production');  // 'development' or 'production'
define('APP_DEBUG', false);
define('APP_TIMEZONE', 'Asia/Colombo');

// File uploads
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);  // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_DIR', __DIR__ . '/../public_html/uploads/');

// ================================================================
// GEMINI API CONFIGURATION
// ================================================================
define('GEMINI_API_KEY', 'AIzaSyDNzEKIIS7GUIHpZBXjDlPbURZGtZYx7KE');  // CHANGE THIS!
define('GEMINI_API_KEY_QUIZ', 'AIzaSyDNzEKIIS7GUIHpZBXjDlPbURZGtZYx7KE');  // CHANGE THIS!
define('GEMINI_API_KEY_SIDEQA', 'AIzaSyDNzEKIIS7GUIHpZBXjDlPbURZGtZYx7KE');  // CHANGE THIS!
define('GEMINI_API_KEY_MCQBLUST', 'AIzaSyDNzEKIIS7GUIHpZBXjDlPbURZGtZYx7KE');  // CHANGE THIS!

// ================================================================
// OPENAI API CONFIGURATION
// ================================================================
define('OPENAI_API_KEY', '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');  // CHANGE THIS!

// ================================================================
// PAYABLE PAYMENT GATEWAY
// ================================================================
define('PAYABLE_SANDBOX', false);  // Set to true for testing
define('PAYABLE_MERCHANT_KEY', 'B669783789CC996D');  // CHANGE THIS!
define('PAYABLE_MERCHANT_TOKEN', '84CAE8510FBAD220D63EE313DEF2D1A3');  // CHANGE THIS!

// ================================================================
// PAYPAL PAYMENT GATEWAY (SANDBOX)
// ================================================================
define('PAYPAL_SANDBOX', false);
define('PAYPAL_CLIENT_ID', 'AS_Jcs8rojCyLm5a1zDFbQ_pRMeH3d2kKtA1Iv6orOjpieqjkLv1jR-RunCZOnsIHby_8rKGqbOM2VVR');
define('PAYPAL_SECRET', 'EPlD-B_VxLk6Tvx8nPWTbZCm_pPkUvG5DKRY-5CWicP4aX_sN0NsQ3VHYgqJCckBz6ic-8L_HMWAKKoa');
define('PAYPAL_LKR_TO_USD_RATE', 0.0032);

// ================================================================
// ENCRYPTION KEYS
// ================================================================
// Generate with: bin2hex(random_bytes(32))
define('APP_KEY', 'globbook_social_media_network_2026_ioejwiou32irojwoeirjojwer');
