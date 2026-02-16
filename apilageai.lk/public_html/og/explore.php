<?php
require_once __DIR__ . '/../../backend/bootstrap.php';

$postId = isset($_GET['post']) ? (int)$_GET['post'] : 0;
$fallback = APP_URL . '/assets/images/icon.png';

function output_fallback(string $url): void {
    header('Location: ' . $url, true, 302);
    exit();
}

if (!function_exists('imagecreatetruecolor')) {
    output_fallback($fallback);
}

if ($postId <= 0) {
    output_fallback($fallback);
}

$stmt = $db->prepare(
    "SELECT p.body, p.created_at, u.first_name, u.last_name
     FROM public_posts p
     JOIN users u ON u.id = p.user_id
     WHERE p.id = ? AND p.status = 'active'
     LIMIT 1"
);
if (!$stmt) {
    output_fallback($fallback);
}
$stmt->bind_param('i', $postId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$row) {
    output_fallback($fallback);
}

$body = trim((string)($row['body'] ?? ''));
$author = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
if ($author === '') {
    $author = 'ApilageAI User';
}

if ($body === '') {
    $body = 'Explore post';
}

function wrap_text_lines(string $text, int $maxChars, int $maxLines): array {
    $text = preg_replace('/\s+/u', ' ', trim($text));
    $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $lines = [];
    $current = '';
    $strlen = function (string $value): int {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    };
    $substr = function (string $value, int $start, int $length): string {
        return function_exists('mb_substr') ? mb_substr($value, $start, $length, 'UTF-8') : substr($value, $start, $length);
    };
    foreach ($words as $word) {
        $test = $current === '' ? $word : $current . ' ' . $word;
        if ($strlen($test) <= $maxChars) {
            $current = $test;
            continue;
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        $current = $word;
        if (count($lines) >= $maxLines) break;
    }
    if (count($lines) < $maxLines && $current !== '') {
        $lines[] = $current;
    }
    if (count($lines) > $maxLines) {
        $lines = array_slice($lines, 0, $maxLines);
    }
    if (count($lines) === $maxLines) {
        $last = $lines[$maxLines - 1];
        if ($strlen($last) > $maxChars - 3) {
            $last = $substr($last, 0, max(0, $maxChars - 3));
        }
        $lines[$maxLines - 1] = rtrim($last) . '...';
    }
    return $lines;
}

$width = 1200;
$height = 630;
$margin = 60;

$image = imagecreatetruecolor($width, $height);
$bg = imagecolorallocate($image, 18, 22, 30);
$accent = imagecolorallocate($image, 255, 6, 6);
$white = imagecolorallocate($image, 240, 243, 248);
$muted = imagecolorallocate($image, 153, 160, 174);
imagefilledrectangle($image, 0, 0, $width, $height, $bg);
imagefilledrectangle($image, 0, 0, $width, 10, $accent);

$font = 5;
$fontW = imagefontwidth($font);
$fontH = imagefontheight($font);

imagestring($image, $font, $margin, 50, 'ApilageAI Explore', $white);
imagestring($image, $font, $margin, 80, 'Question', $muted);

$maxChars = (int)(($width - ($margin * 2)) / $fontW);
$lines = wrap_text_lines($body, max(18, $maxChars), 8);

$y = 140;
foreach ($lines as $line) {
    imagestring($image, $font, $margin, $y, $line, $white);
    $y += $fontH + 8;
}

imagestring($image, $font, $margin, $height - 70, 'Posted by ' . $author, $muted);

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
imagepng($image);
imagedestroy($image);
exit();
