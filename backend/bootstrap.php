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
require __DIR__ . '/config.php';

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
// INPUT NORMALIZATION (basic sanitization)
// ================================================================
function sanitize_scalar_input($value) {
    if (!is_string($value)) {
        return $value;
    }
    // Remove null bytes and control chars
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    return trim($value);
}

function sanitize_array_input($data) {
    if (!is_array($data)) {
        return sanitize_scalar_input($data);
    }
    $clean = [];
    foreach ($data as $key => $value) {
        $clean_key = sanitize_scalar_input((string) $key);
        $clean[$clean_key] = is_array($value) ? sanitize_array_input($value) : sanitize_scalar_input($value);
    }
    return $clean;
}

$_GET = sanitize_array_input($_GET);
$_POST = sanitize_array_input($_POST);
$_COOKIE = sanitize_array_input($_COOKIE);

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

// ================================================================
// SESSION SECURITY
// ================================================================
// Secure session configuration
ini_set('session.cookie_httponly', 1);
$cookieSecure = APP_ENV === 'production';
ini_set('session.cookie_secure', $cookieSecure ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');
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
        if (APP_DEBUG) {
            die("Database connection failed: " . $db->connect_error);
        } else {
            die("Service temporarily unavailable. Please try again later.");
        }
    }
    
    $db->set_charset(DB_CHARSET);
    $db->query("SET time_zone = '+05:30'");
    $db->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
} catch (Exception $e) {
    error_log("Database exception: " . $e->getMessage());
    if (APP_DEBUG) {
        die("Database exception: " . $e->getMessage());
    }
    die("Service temporarily unavailable. Please try again later.");
}

// ================================================================
// SMARTY TEMPLATE ENGINE
// ================================================================
use Smarty\Smarty;
use Smarty\Filter\Output\TrimWhitespace;

$smarty = new Smarty();
$smarty->error_reporting = APP_DEBUG ? (E_ALL & ~E_NOTICE) : 0;
$smarty->setTemplateDir(__DIR__ . '/includes/smarty/templates/');
$smarty->setCompileDir(__DIR__ . '/includes/smarty/templates_compiled/');
$smarty->registerFilter('output', [new TrimWhitespace(), 'filter']);

// Register all PHP functions as Smarty modifiers (backward compatible)
foreach (array_merge(...array_values(get_defined_functions())) as $function) {
    $smarty->registerPlugin('modifier', $function, $function);
}

// HTML minification
function minify_html($tpl_output, \Smarty\Template $template) {
    return preg_replace('/\s+/', ' ', $tpl_output);
}
$smarty->registerFilter('output', 'minify_html');

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
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
