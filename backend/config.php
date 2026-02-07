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
define('DB_PASS', 'h4ZNUDySX7QA2aE7g8EQ');  // CHANGE THIS!
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
define('GOOGLE_CLIENT_ID', '902160013451-vfbr84rg6kaut15jc38im0fl79fskukj.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');  // CHANGE THIS!
define('GOOGLE_REDIRECT_URI', 'https://apilageai.lk/auth/google-callback');

// ================================================================
// RECAPTCHA CONFIGURATION
// ================================================================
define('RECAPTCHA_SITE_KEY', '6Lf1xggrAAAAAN7d3fDQeFl9nP1jT7k2e_Q-vB8p');
define('RECAPTCHA_SECRET_KEY', '6Lf1xggrAAAAALkPyuadZpMV45bAABQ83pbwC0g6');  // CHANGE THIS!

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
define('NODE_API_BASE', 'https://apilageai.lk');
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
define('GEMINI_API_KEY', 'AIzaSyC4WmGhiqRYCX2MJojKX19mJQVFCx09QZc');  // CHANGE THIS!
define('GEMINI_API_KEY_QUIZ', 'AIzaSyC4WmGhiqRYCX2MJojKX19mJQVFCx09QZc');  // CHANGE THIS!
define('GEMINI_API_KEY_SIDEQA', 'AIzaSyC4WmGhiqRYCX2MJojKX19mJQVFCx09QZc');  // CHANGE THIS!
define('GEMINI_API_KEY_MCQBLUST', 'AIzaSyC4WmGhiqRYCX2MJojKX19mJQVFCx09QZc');  // CHANGE THIS!

// ================================================================
// OPENAI API CONFIGURATION
// ================================================================
define('OPENAI_API_KEY', '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');  // CHANGE THIS!

// ================================================================
// FIREBASE CONFIGURATION
// ================================================================
define('FIREBASE_API_KEY', 'AIzaSyBY5gsQusKZ95Os3KoWvjauEMxGI8fBw3c');
define('FIREBASE_AUTH_DOMAIN', 'apilage-ai.firebaseapp.com');
define('FIREBASE_DATABASE_URL', 'https://apilage-ai-default-rtdb.firebaseio.com');
define('FIREBASE_PROJECT_ID', 'apilage-ai');
define('FIREBASE_STORAGE_BUCKET', 'apilage-ai.firebasestorage.app');
define('FIREBASE_MESSAGING_SENDER_ID', '902160013451');
define('FIREBASE_APP_ID', '1:902160013451:web:498911915681b72ce25c8e');
define('FIREBASE_MEASUREMENT_ID', 'G-N7SRT0LHJV');

// ================================================================
// PAYABLE PAYMENT GATEWAY
// ================================================================
define('PAYABLE_SANDBOX', false);  // Set to true for testing
define('PAYABLE_MERCHANT_KEY', 'B669783789CC996D');  // CHANGE THIS!
define('PAYABLE_MERCHANT_TOKEN', '84CAE8510FBAD220D63EE313DEF2D1A3');  // CHANGE THIS!

// ================================================================
// ENCRYPTION KEYS
// ================================================================
// Generate with: bin2hex(random_bytes(32))
define('APP_KEY', 'globbook_social_media_network_2026_ioejwiou32irojwoeirjojwer');
