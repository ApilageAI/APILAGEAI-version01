<?php
/**
 * ApilageAI Configuration Template
 * 
 * SECURITY NOTICE:
 * - Copy this file to config.php
 * - NEVER commit config.php to version control
 * - Keep production credentials in environment variables or in your private server config
 * 
 * @package ApilageAI
 */

// Prevent direct access
if (!defined('APILAGE_LOADED')) {
    http_response_code(403);
    exit('Access denied');
}

// ================================================================
// APPLICATION SETTINGS
// ================================================================
define('APP_NAME', getenv('APP_NAME') ?: 'ApilageAI');
define('APP_ENV', getenv('APP_ENV') ?: 'production');  // 'development' or 'production'
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', getenv('APP_URL') ?: 'https://apilageai.lk');
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Asia/Colombo');

// Node Socket Server
define('NODE_API_BASE', getenv('NODE_API_BASE') ?: 'https://socket.apilageai.lk');
define('UPLOADS_BASE_URL', getenv('UPLOADS_BASE_URL') ?: 'https://socket.apilageai.lk');
define('ALLOWED_ORIGINS', explode(',', getenv('ALLOWED_ORIGINS') ?: 'https://apilageai.lk'));
define('COOKIE_DOMAIN', getenv('COOKIE_DOMAIN') ?: '.apilageai.lk');
define('COOKIE_SAMESITE', getenv('COOKIE_SAMESITE') ?: 'Strict');

// ================================================================
// DATABASE CONFIGURATION
// ================================================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'db_user');
define('DB_PASS', getenv('DB_PASS') ?: 'db_password_here');
define('DB_NAME', getenv('DB_NAME') ?: 'apilageai_main_db');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// ================================================================
// SMTP CONFIGURATION
// ================================================================
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'mail.apilageai.lk');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
define('SMTP_USER', getenv('SMTP_USER') ?: 'no-reply@apilageai.lk');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'your_smtp_password');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'no-reply@apilageai.lk');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'ApilageAI');
define('SMTP_ENCRYPTION', getenv('SMTP_ENCRYPTION') ?: 'tls');

// ================================================================
// GOOGLE OAUTH CONFIGURATION
// ================================================================
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'your_google_client_id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'your_google_client_secret');
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: 'https://apilageai.lk/auth/google-callback');

// ================================================================
// FACEBOOK OAUTH CONFIGURATION
// ================================================================
define('FACEBOOK_APP_ID', getenv('FACEBOOK_APP_ID') ?: 'your_facebook_app_id');
define('FACEBOOK_APP_SECRET', getenv('FACEBOOK_APP_SECRET') ?: 'your_facebook_app_secret');
define('FACEBOOK_REDIRECT_URI', getenv('FACEBOOK_REDIRECT_URI') ?: 'https://apilageai.lk/auth/facebook-callback');

// ================================================================
// CLOUDFLARE TURNSTILE / RECAPTCHA CONFIGURATION
// ================================================================
define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: 'your_turnstile_site_key');
define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: 'your_turnstile_secret_key');

// ================================================================
// GLOBBOOK AUTH CONFIGURATION
// ================================================================
define('GLOBBOOK_APP_ID', getenv('GLOBBOOK_APP_ID') ?: 'your_globbook_app_id');
define('GLOBBOOK_APP_SECRET', getenv('GLOBBOOK_APP_SECRET') ?: 'your_globbook_app_secret');

// ================================================================
// WHITEBOARD SERVICE
// ================================================================
define('WHITEBOARD_TEAM_CLIENT_ID', getenv('WHITEBOARD_TEAM_CLIENT_ID') ?: 'your_whiteboard_client_id');
define('WHITEBOARD_TEAM_CLIENT_SECRET', getenv('WHITEBOARD_TEAM_CLIENT_SECRET') ?: 'your_whiteboard_client_secret');

// ================================================================
// AI SERVICES
// ================================================================
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'your_gemini_api_key');
define('GEMINI_API_KEY_QUIZ', getenv('GEMINI_API_KEY_QUIZ') ?: (getenv('GEMINI_API_KEY') ?: 'your_gemini_api_key'));
define('GEMINI_API_KEY_SIDEQA', getenv('GEMINI_API_KEY_SIDEQA') ?: (getenv('GEMINI_API_KEY') ?: 'your_gemini_api_key'));
define('GEMINI_API_KEY_MCQBLUST', getenv('GEMINI_API_KEY_MCQBLUST') ?: (getenv('GEMINI_API_KEY') ?: 'your_gemini_api_key'));
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: 'your_openai_api_key');
define('YOUTUBE_API_KEY', getenv('YOUTUBE_API_KEY') ?: 'your_youtube_api_key');

// ================================================================
// PAYMENT GATEWAYS
// ================================================================
// Payable
define('PAYABLE_SANDBOX', filter_var(getenv('PAYABLE_SANDBOX') ?: false, FILTER_VALIDATE_BOOLEAN));
define('PAYABLE_MERCHANT_KEY', getenv('PAYABLE_MERCHANT_KEY') ?: 'your_payable_merchant_key');
define('PAYABLE_MERCHANT_TOKEN', getenv('PAYABLE_MERCHANT_TOKEN') ?: 'your_payable_merchant_token');

// PayPal
define('PAYPAL_SANDBOX', filter_var(getenv('PAYPAL_SANDBOX') ?: false, FILTER_VALIDATE_BOOLEAN));
define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: 'your_paypal_client_id');
define('PAYPAL_SECRET', getenv('PAYPAL_SECRET') ?: 'your_paypal_secret');
define('PAYPAL_LKR_TO_USD_RATE', (float)(getenv('PAYPAL_LKR_TO_USD_RATE') ?: 0.0032));

// ================================================================
// SECURITY & ENCRYPTION
// ================================================================
define('APP_KEY', getenv('APP_KEY') ?: 'generate_with_bin2hex_random_bytes_32');
define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: 31536000));  // 1 year
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 1800);  // 30 minutes
define('MAX_SESSIONS_PER_USER', 5);

// Rate limiting
define('RATE_LIMIT_LOGIN_ATTEMPTS', 5);
define('RATE_LIMIT_LOGIN_WINDOW', 900);  // 15 minutes
define('RATE_LIMIT_SIGNUP_ATTEMPTS', 3);
define('RATE_LIMIT_SIGNUP_WINDOW', 3600);  // 1 hour

// File uploads
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);  // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_DIR', __DIR__ . '/../public_html/uploads/');
