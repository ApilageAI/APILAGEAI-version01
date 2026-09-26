<?php
/**
 * ApilageAI Bootstrap
 * 
 * Enterprise-grade initialization with security hardening
 * 
 * @package ApilageAI
 */

// Define loaded constant for config protection
define('APILAGE_LOADED', true);

// ================================================================
// LOAD CONFIGURATION
// ================================================================
if (file_exists(__DIR__ . '/config.php')) {
    require __DIR__ . '/config.php';
} elseif (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
} elseif (file_exists(__DIR__ . '/config.example.php')) {
    require __DIR__ . '/config.example.php';
} else {
    http_response_code(500);
    exit('Configuration file missing. Please copy config.example.php to config.php and set your credentials.');
}

// ================================================================
// SECURITY HEADERS
// ================================================================
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
if (APP_ENV === 'production') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}
header("Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Capture any PHP output for JSON API responses so we can surface errors cleanly.
if (defined('APILAGE_EXPECTS_JSON') && APILAGE_EXPECTS_JSON && ob_get_level() === 0) {
    ob_start();
}

// ================================================================
// ERROR HANDLING
// ================================================================
if (APP_DEBUG) {
    ini_set("display_errors", true);
    error_reporting(E_ALL);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
} else {
    ini_set("display_errors", false);
    ini_set("log_errors", true);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('error_log', __DIR__ . '/error.log');
}

// ================================================================
// SIMPLE HTML ERROR RESPONSE (non-JSON)
// ================================================================
if (!function_exists('apilage_render_service_unavailable')) {
    function apilage_render_service_unavailable(string $message = 'Service temporarily unavailable. Please try again later.'): void {
        if (!headers_sent()) {
            http_response_code(503);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Service Unavailable</title><style>body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,sans-serif;background:#f8fafc;color:#0f172a;display:flex;min-height:100vh;align-items:center;justify-content:center}main{max-width:640px;padding:32px;border-radius:16px;background:#fff;box-shadow:0 20px 60px rgba(15,23,42,.12)}h1{margin:0 0 12px;font-size:22px}p{margin:0;color:#475569;line-height:1.6}</style></head><body><main><h1>We&rsquo;re working on it</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p></main></body></html>';
        exit;
    }
}

// ================================================================
// INPUT NORMALIZATION (basic sanitization)
// ================================================================
if (!function_exists('apilage_sanitize_scalar_input')) {
    function apilage_sanitize_scalar_input($value) {
        if (!is_string($value)) {
            return $value;
        }
        // Remove null bytes and control chars
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        return trim($value);
    }
}

if (!function_exists('apilage_sanitize_array_input')) {
    function apilage_sanitize_array_input($data) {
        if (!is_array($data)) {
            return apilage_sanitize_scalar_input($data);
        }
        $clean = [];
        foreach ($data as $key => $value) {
            $clean_key = apilage_sanitize_scalar_input((string) $key);
            $clean[$clean_key] = is_array($value) ? apilage_sanitize_array_input($value) : apilage_sanitize_scalar_input($value);
        }
        return $clean;
    }
}

$_GET = apilage_sanitize_array_input($_GET);
$_POST = apilage_sanitize_array_input($_POST);
$_COOKIE = apilage_sanitize_array_input($_COOKIE);

// ================================================================
// TIMEZONE
// ================================================================
date_default_timezone_set(APP_TIMEZONE);
$time = time();
$DateTime = new DateTime();
$date = $DateTime->format('Y-m-d H:i:s');

// ================================================================
// AUTOLOADER
// ================================================================
require __DIR__ . "/includes/libs/vendor/autoload.php";
require __DIR__ . '/functions.php';

// Ensure fatal errors still return JSON for API callers.
if (defined('APILAGE_EXPECTS_JSON') && APILAGE_EXPECTS_JSON) {
    register_shutdown_function(function () {
        if (!empty($GLOBALS['APILAGE_JSON_SENT'])) {
            return;
        }
        $error = error_get_last();
        if (!$error) {
            return;
        }
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
        if (!in_array($error['type'], $fatalTypes, true)) {
            return;
        }
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        $message = (defined('APP_DEBUG') && APP_DEBUG)
            ? ($error['message'] . ' in ' . $error['file'] . ':' . $error['line'])
            : 'Server error. Please try again later.';
        echo json_encode(["e" => true, "m" => $message, "fatal" => true]);
    });
}

// ================================================================
// SESSION SECURITY
// ================================================================
// Secure session configuration
ini_set('session.cookie_httponly', 1);
$cookieSecure = APP_ENV === 'production';
ini_set('session.cookie_secure', $cookieSecure ? 1 : 0);
$cookieSameSite = defined('COOKIE_SAMESITE') ? COOKIE_SAMESITE : 'Lax';
if (!in_array($cookieSameSite, ['Lax', 'Strict', 'None'], true)) {
    $cookieSameSite = 'Lax';
}
ini_set('session.cookie_samesite', $cookieSameSite);
if (defined('COOKIE_DOMAIN') && COOKIE_DOMAIN !== '') {
    ini_set('session.cookie_domain', COOKIE_DOMAIN);
}
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);

session_name("APILAGE_AI_SESSID");
session_start();

// Regenerate session ID periodically to prevent fixation
if (!isset($_SESSION['_created'])) {
    $_SESSION['_created'] = time();
} elseif (time() - $_SESSION['_created'] > 1800) {
    // Regenerate session ID every 30 minutes
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}

// ================================================================
// DATABASE CONNECTION
// ================================================================
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($db->connect_error) {
        error_log("Database connection failed: " . $db->connect_error);
        if (defined('APILAGE_EXPECTS_JSON') && APILAGE_EXPECTS_JSON) {
            $message = APP_DEBUG
                ? "Database connection failed: " . $db->connect_error
                : "Service temporarily unavailable. Please try again later.";
            returnJSON(["e" => true, "m" => $message]);
        }
        if (APP_DEBUG) {
            apilage_render_service_unavailable("Database connection failed: " . $db->connect_error);
        }
        apilage_render_service_unavailable();
    }
    
    $db->set_charset(DB_CHARSET);
    $db->query("SET time_zone = '+05:30'");
    $db->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
} catch (Exception $e) {
    error_log("Database exception: " . $e->getMessage());
    if (defined('APILAGE_EXPECTS_JSON') && APILAGE_EXPECTS_JSON) {
        $message = APP_DEBUG
            ? "Database exception: " . $e->getMessage()
            : "Service temporarily unavailable. Please try again later.";
        returnJSON(["e" => true, "m" => $message]);
    }
    if (APP_DEBUG) {
        apilage_render_service_unavailable("Database exception: " . $e->getMessage());
    }
    apilage_render_service_unavailable();
}

// ================================================================
// SMARTY TEMPLATE ENGINE
// ================================================================
use Smarty\Smarty;
use Smarty\Filter\Output\TrimWhitespace;

$smarty = new Smarty();
$smarty->compile_check = true;
$smarty->force_compile = true;
$smarty->caching = false;
$smarty->error_reporting = APP_DEBUG ? (E_ALL & ~E_NOTICE) : 0;
$smarty->setTemplateDir(__DIR__ . '/includes/smarty/templates/');
$smarty->setCompileDir(__DIR__ . '/includes/smarty/templates_compiled/');
$smarty->registerFilter('output', [new TrimWhitespace(), 'filter']);

// Register all PHP functions as Smarty modifiers (backward compatible)
foreach (array_merge(...array_values(get_defined_functions())) as $function) {
    $smarty->registerPlugin('modifier', $function, $function);
}

// HTML minification
if (!function_exists('apilage_minify_html')) {
    function apilage_minify_html($tpl_output, \Smarty\Template $template) {
        return preg_replace('/\s+/', ' ', $tpl_output);
    }
}
$smarty->registerFilter('output', 'apilage_minify_html');

// ================================================================
// GLOBAL CONSTANTS
// ================================================================
$gcons = [
    'months' => ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    'days_of_week' => ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"]
];

// ================================================================
// USER INITIALIZATION
// ================================================================
require __DIR__ . "/user.php";
$user = new User();
$isGuest = !empty($_SESSION['guest_mode']);
if ($user->_logged_in && $isGuest) {
    unset($_SESSION['guest_mode'], $_SESSION['guest_id']);
    $isGuest = false;
}
$smarty->assign('user', $user);
$smarty->assign('gcons', $gcons);

// ================================================================
// CSRF TOKEN GENERATION
// ================================================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$smarty->assign('csrf_token', $_SESSION['csrf_token']);

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool
 */
if (!function_exists('apilage_verify_csrf_token')) {
    function apilage_verify_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
