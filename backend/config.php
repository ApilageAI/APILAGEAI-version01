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
// ENV LOADER (shared .env with Node)
// ================================================================
function load_env_file($path) {
    if (!is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) return;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        // Strip surrounding quotes
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

function env_value($key, $default = null) {
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

// Prefer project root .env, fallback to node/.env
$envCandidates = [
    __DIR__ . '/../.env',
    __DIR__ . '/../node/.env',
];
foreach ($envCandidates as $candidate) {
    load_env_file($candidate);
}

// ================================================================
// DATABASE CONFIGURATION
// ================================================================
define('DB_HOST', env_value('DB_HOST', '127.0.0.1'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASSWORD', 'root'));
define('DB_NAME', env_value('DB_NAME', 'apilageai_main_db'));
define('DB_PORT', (int) env_value('DB_PORT', 3306));
define('DB_CHARSET', env_value('DB_CHARSET', 'utf8mb4'));

// ================================================================
// SMTP CONFIGURATION
// ================================================================
define('SMTP_HOST', env_value('SMTP_HOST', 'mail.apilageai.lk'));
define('SMTP_PORT', (int) env_value('SMTP_PORT', 587));
define('SMTP_USER', env_value('SMTP_USER', 'no-reply@apilageai.lk'));
define('SMTP_PASS', env_value('SMTP_PASS', ''));
define('SMTP_FROM_EMAIL', env_value('SMTP_FROM_EMAIL', 'no-reply@apilageai.lk'));
define('SMTP_FROM_NAME', env_value('SMTP_FROM_NAME', 'ApilageAI'));
define('SMTP_ENCRYPTION', env_value('SMTP_ENCRYPTION', 'tls'));

// ================================================================
// GOOGLE OAUTH CONFIGURATION
// ================================================================
define('GOOGLE_CLIENT_ID', env_value('GOOGLE_CLIENT_ID', '940750836912-998mgc8pgddlq8h36bnp0gdoaro8a7i0.apps.googleusercontent.com'));
define('GOOGLE_CLIENT_SECRET', env_value('GOOGLE_CLIENT_SECRET', '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'));
define('GOOGLE_REDIRECT_URI', env_value('GOOGLE_REDIRECT_URI', env_value('APP_URL', 'http://localhost:8888') . '/auth/google-callback'));

// ================================================================
// RECAPTCHA CONFIGURATION
// ================================================================
define('RECAPTCHA_SITE_KEY', env_value('RECAPTCHA_SITE_KEY', ''));
define('RECAPTCHA_SECRET_KEY', env_value('RECAPTCHA_SECRET_KEY', ''));

// ================================================================
// GLOBBOOK AUTH CONFIGURATION
// ================================================================
define('GLOBBOOK_APP_ID', env_value('GLOBBOOK_APP_ID', ''));
define('GLOBBOOK_APP_SECRET', env_value('GLOBBOOK_APP_SECRET', ''));

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
define('APP_URL', rtrim(env_value('APP_URL', 'http://localhost:8888'), '/'));
define('NODE_API_BASE', rtrim(env_value('NODE_API_BASE', APP_URL), '/'));
define('ALLOWED_ORIGINS', array_values(array_filter(array_map('trim', explode(',', env_value('ALLOWED_ORIGINS', APP_URL))))));
define('COOKIE_DOMAIN', env_value('COOKIE_DOMAIN', ''));
define('COOKIE_SAMESITE', env_value('COOKIE_SAME_SITE', 'Lax'));
define('APP_NAME', env_value('APP_NAME', 'ApilageAI'));
define('APP_ENV', env_value('APP_ENV', env_value('NODE_ENV', 'development')));  // 'development' or 'production'
define('APP_DEBUG', filter_var(env_value('APP_DEBUG', APP_ENV === 'production' ? 'false' : 'true'), FILTER_VALIDATE_BOOLEAN));
define('APP_TIMEZONE', env_value('APP_TIMEZONE', 'Asia/Colombo'));

// File uploads
define('UPLOAD_MAX_SIZE', (int) env_value('UPLOAD_MAX_SIZE', 5 * 1024 * 1024));  // 5MB
define('UPLOAD_ALLOWED_TYPES', explode(',', env_value('UPLOAD_ALLOWED_TYPES', 'image/jpeg,image/png,image/gif,image/webp')));
define('UPLOAD_DIR', env_value('UPLOAD_DIR', __DIR__ . '/../public_html/uploads/'));

// ================================================================
// GEMINI API CONFIGURATION
// ================================================================
define('GEMINI_API_KEY', env_value('GEMINI_API_KEY', ''));
define('GEMINI_API_KEY_QUIZ', env_value('GEMINI_API_KEY_QUIZ', env_value('GEMINI_API_KEY', '')));
define('GEMINI_API_KEY_SIDEQA', env_value('GEMINI_API_KEY_SIDEQA', env_value('GEMINI_API_KEY', '')));
define('GEMINI_API_KEY_MCQBLUST', env_value('GEMINI_API_KEY_MCQBLUST', env_value('GEMINI_API_KEY', '')));

// ================================================================
// OPENAI API CONFIGURATION
// ================================================================
define('OPENAI_API_KEY', env_value('OPENAI_API_KEY', ''));

// ================================================================
// FIREBASE CONFIGURATION
// ================================================================
define('FIREBASE_API_KEY', env_value('FIREBASE_API_KEY', ''));
define('FIREBASE_AUTH_DOMAIN', env_value('FIREBASE_AUTH_DOMAIN', ''));
define('FIREBASE_DATABASE_URL', env_value('FIREBASE_DATABASE_URL', ''));
define('FIREBASE_PROJECT_ID', env_value('FIREBASE_PROJECT_ID', ''));
define('FIREBASE_STORAGE_BUCKET', env_value('FIREBASE_STORAGE_BUCKET', ''));
define('FIREBASE_MESSAGING_SENDER_ID', env_value('FIREBASE_MESSAGING_SENDER_ID', ''));
define('FIREBASE_APP_ID', env_value('FIREBASE_APP_ID', ''));
define('FIREBASE_MEASUREMENT_ID', env_value('FIREBASE_MEASUREMENT_ID', ''));

// ================================================================
// PAYABLE PAYMENT GATEWAY
// ================================================================
define('PAYABLE_SANDBOX', filter_var(env_value('PAYABLE_SANDBOX', 'false'), FILTER_VALIDATE_BOOLEAN));  // Set to true for testing
define('PAYABLE_MERCHANT_KEY', env_value('PAYABLE_MERCHANT_KEY', ''));
define('PAYABLE_MERCHANT_TOKEN', env_value('PAYABLE_MERCHANT_TOKEN', ''));

// ================================================================
// ENCRYPTION KEYS
// ================================================================
// Generate with: bin2hex(random_bytes(32))
define('APP_KEY', env_value('APP_KEY', ''));
