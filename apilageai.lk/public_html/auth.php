<?php
/**
 * ApilageAI Authentication Page
 * 
 * Handles login, register, password reset, and OAuth
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../backend/bootstrap.php';

$do = $_GET['do'] ?? '';

// Prevent authenticated users from viewing auth pages
$allowWhenLoggedIn = ["out", "google", "google-callback", "facebook", "facebook-callback", "facebook-deauthorize"];
if ($user->_logged_in && !in_array($do, $allowWhenLoggedIn, true)) {
    header("Location: " . APP_URL . "/app");
    exit;
}

// Login/Register pages
if ($do === "log" || $do === "reg") {
    if ($user->_logged_in) {
        header("Location: " . APP_URL . "/app");
        exit;
    }
    if ($do === "reg") {
        header("Location: " . APP_URL . "/auth/login?mode=register");
        exit;
    }
    $title = "Login";
    $page = "login";
}
// Sign out
elseif ($do === "out") {
    if (!$user->_logged_in) {
        header("Location: " . APP_URL . "/auth/login");
        exit;
    }
    $user->sign_out();
    header("Location: " . APP_URL . "/auth/login");
    exit;
}
// Password reset request
elseif ($do === "reset-request") {
    if ($user->_logged_in) {
        header("Location: " . APP_URL . "/app");
        exit;
    }
    $title = "Password Reset Request";
    $page = "password_reset_request";
}
// Password reset form
elseif ($do === "reset-password") {
    $token = $_GET['token'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = $_POST['password'] ?? '';
        $result = $user->reset_password($token, $newPassword);
        $smarty->assign('result', $result);
        $smarty->assign('reset_complete', empty($result['e']));
    } else {
        $result = $user->verify_reset_token($token);
        $smarty->assign('result', $result);
        $smarty->assign('reset_complete', false);
    }
    $title = "Reset Password";
    $page = "password_reset_form";
}
// Continue without account (guest mode)
elseif ($do === "guest") {
    if ($user->_logged_in) {
        $user->sign_out();
        // sign_out destroys the session; start a new one for guest mode
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
    $_SESSION['guest_mode'] = true;
    $_SESSION['guest_id'] = bin2hex(random_bytes(8));
    header("Location: " . APP_URL . "/app");
    exit;
}
// Magic login link
elseif ($do === "magic-login") {
    $token = $_GET['token'] ?? '';
    $result = $user->login_with_magic_token($token);
    if (!empty($result['e'])) {
        header("Location: " . APP_URL . "/auth/login?error=magic_expired");
        exit;
    }
    header("Location: " . APP_URL . "/app");
    exit;
}
// Google OAuth
elseif ($do === "google" || $do === "google-callback") {
    // This will handle its own exit() with header redirect
    $user->google_sign_in();
    // Should not reach here as google_sign_in() calls exit()
    exit;
}
// Facebook OAuth
elseif ($do === "facebook" || $do === "facebook-callback") {
    // This will handle its own exit() with header redirect
    $user->facebook_sign_in();
    // Should not reach here as facebook_sign_in() calls exit()
    exit;
}
// Facebook deauthorize callback
elseif ($do === "facebook-deauthorize") {
    $user->facebook_deauthorize();
    exit;
}
// Email verification
elseif ($do === "verify-email") {
    $token = $_GET['token'] ?? '';
    $result = $user->verify_email($token);
    if (empty($result['e'])) {
        header("Location: " . APP_URL . "/app");
        exit;
    }
    $smarty->assign('result', $result);
    $title = "Email Verification";
    $page = "email_verification";
}
// Resend verification email (AJAX)
elseif ($do === "resend-verification") {
    header('Content-Type: application/json');
    $email = $_POST['email'] ?? '';
    $user->resend_verification_email($email);
    exit;
}
// Invalid action
else {
    http_response_code(404);
    exit;
}

page_header("Apilage AI | $title");
page_footer($page);
