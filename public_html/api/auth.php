<?php
/**
 * ApilageAI Authentication API
 * 
 * Handles login and registration with security headers
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../backend/bootstrap.php';

// Security headers
header('Content-Type: application/json');

// Validate action parameter
$action = $_GET["act"] ?? '';

if (empty($action)) {
    http_response_code(400);
    returnJSON(["e" => true, "m" => "Action required"]);
}

switch ($action) {
    case "login":
        // Validate required fields exist
        if (empty($_POST["e"]) || empty($_POST["p"])) {
            returnJSON(["e" => true, "m" => "Email and password required"]);
        }
        
        $user->sign_in([
            "email" => $_POST["e"],
            "password" => $_POST["p"],
            "captcha" => $_POST['g-recaptcha-response'] ?? ''
        ]);
        break;
        
    case "register":
        // Validate required fields exist
        $required = ['f', 'l', 'e', 't', 'p'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                returnJSON(["e" => true, "m" => "All fields are required"]);
            }
        }
        
        $user->sign_up([
            "firstName" => $_POST["f"],
            "lastName" => $_POST["l"],
            "email" => $_POST["e"],
            "phone" => $_POST["t"],
            "password" => $_POST["p"],
            "image" => $_FILES['i'] ?? null,
            "captcha" => $_POST['g-recaptcha-response'] ?? ''
        ]);
        break;

    case "reset-request":
        if (empty($_POST["email"])) {
            returnJSON(["e" => true, "m" => "Email is required"]);
        }
        $user->request_password_reset($_POST["email"]);
        break;

    case "magic":
        if (empty($_POST["email"])) {
            returnJSON(["e" => true, "m" => "Email is required"]);
        }
        $user->request_magic_login($_POST["email"], $_POST['captcha'] ?? '');
        break;
        
    default:
        http_response_code(404);
        returnJSON(["e" => true, "m" => "Invalid action"]);
}
