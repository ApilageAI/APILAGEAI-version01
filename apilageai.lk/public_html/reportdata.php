<?php
require_once __DIR__ . '/../backend/bootstrap.php';

// Ensure JSON-only output
ini_set('display_errors', '0');
error_reporting(0);

// Prevent any output before JSON response
ob_start();

// Clear any previous output
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

header("Content-Type: application/json; charset=utf-8");

$responseSent = false;

register_shutdown_function(function () use (&$responseSent) {
    if ($responseSent) {
        return;
    }
    $error = error_get_last();
    if (!$error) {
        return;
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server error. Please try again."
    ], JSON_UNESCAPED_SLASHES);
});

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ob_end_clean();
    http_response_code(405);
    die(json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]));
}

function respond(bool $status, string $message, array $extra = [], int $code = 200): void
{
    global $responseSent;
    $responseSent = true;
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($code);
    die(json_encode(array_merge([
        "success" => $status,
        "message" => $message
    ], $extra), JSON_UNESCAPED_SLASHES));
}

$email = trim($_POST["email"] ?? "");
$problem = trim($_POST["problem"] ?? "");
$screenshotPath = null;

if ($problem === "" || str_word_count($problem) < 4) {
    respond(false, "Please describe the problem with at least 4 words.", [], 400);
}

if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, "Please provide a valid email address.", [], 400);
}

// Get database connection
if (!isset($db) || !($db instanceof mysqli)) {
    respond(false, "Database connection failed.", [], 500);
}

if (isset($_FILES["screenshot"]) && $_FILES["screenshot"]["error"] !== UPLOAD_ERR_NO_FILE) {
    $fileError = $_FILES["screenshot"]["error"];

    if ($fileError !== UPLOAD_ERR_OK) {
        respond(false, "There was an error uploading the screenshot.", ["errorCode" => $fileError], 400);
    }

    if ($_FILES["screenshot"]["size"] > 10 * 1024 * 1024) {
        respond(false, "File size must be under 10MB.", [], 400);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES["screenshot"]["tmp_name"]);
    finfo_close($finfo);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/gif" => "gif",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mimeType])) {
        respond(false, "Only image uploads are allowed.", [], 400);
    }

    $uploadDir = __DIR__ . "/uploads/userimg";
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        respond(false, "Unable to prepare storage directory.", [], 500);
    }

    $filename = "bug_" . date("Ymd_His") . "_" . bin2hex(random_bytes(4)) . "." . $allowed[$mimeType];
    $targetPath = $uploadDir . "/" . $filename;

    if (!move_uploaded_file($_FILES["screenshot"]["tmp_name"], $targetPath)) {
        respond(false, "Unable to save the screenshot.", [], 500);
    }

    $screenshotPath = rtrim(UPLOADS_BASE_URL, "/") . "/uploads/userimg/" . $filename;
}

// Insert into database
$emailValue = $email === "" ? "Anonymous" : $email;

$statement = $db->prepare(
    "INSERT INTO bug_reports (email, problem, screenshot_path, created_at)
     VALUES (?, ?, ?, NOW())"
);

if (!$statement) {
    respond(false, "Failed to prepare statement.", ["error" => $db->error], 500);
}

$statement->bind_param("sss", $emailValue, $problem, $screenshotPath);

if (!$statement->execute()) {
    respond(false, "Failed to save report.", ["error" => $statement->error], 500);
}

respond(true, "Bug report submitted successfully!", [
    "report_id" => (int) $db->insert_id,
    "screenshot_path" => $screenshotPath
]);
