<?php
require_once __DIR__ . '/../backend/bootstrap.php';

if (!function_exists('normalize_public_image_url_profile')) {
    function normalize_public_image_url_profile(?string $value): ?string {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $value)) {
            $parsed = parse_url($value);
            $path = $parsed['path'] ?? '';
            $host = $parsed['host'] ?? '';
            $appHost = parse_url(APP_URL, PHP_URL_HOST);
            if ($host && $appHost && strcasecmp($host, $appHost) === 0 && stripos($path, '/uploads/') === 0) {
                return rtrim(UPLOADS_BASE_URL, '/') . $path;
            }
            return $value;
        }
        if (stripos($value, '/uploads/') === 0) {
            return rtrim(UPLOADS_BASE_URL, '/') . $value;
        }
        if (stripos($value, 'uploads/') === 0) {
            return rtrim(UPLOADS_BASE_URL, '/') . '/' . $value;
        }
        if (preg_match('#^(userimg|profile|genimg)/#i', $value)) {
            return rtrim(UPLOADS_BASE_URL, '/') . '/uploads/' . $value;
        }
        return $value;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'search_users') {
    header('Content-Type: application/json; charset=utf-8');

    $query = trim((string)($_GET['q'] ?? ''));
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
    if ($limit < 1) $limit = 1;
    if ($limit > 12) $limit = 12;

    $users = [];
    if ($query !== '') {
        $q = '%' . strtolower($query) . '%';
        $stmt = $db->prepare(
            "SELECT u.id, u.first_name, u.last_name, u.image, u.public_profile_username, u.public_profile_token,
                    o.school, o.not_student
             FROM users u
             LEFT JOIN user_onboarding o ON o.user_id = u.id
             WHERE u.public_profile_enabled = 1
               AND (
                    LOWER(u.first_name) LIKE ?
                 OR LOWER(u.last_name) LIKE ?
                 OR LOWER(CONCAT(u.first_name, ' ', u.last_name)) LIKE ?
                 OR LOWER(COALESCE(u.public_profile_username, '')) LIKE ?
                 OR LOWER(COALESCE(o.school, '')) LIKE ?
               )
             ORDER BY u.reg_date DESC
             LIMIT ?"
        );
        $stmt->bind_param('sssssi', $q, $q, $q, $q, $q, $limit);
    } else {
        $stmt = $db->prepare(
            "SELECT u.id, u.first_name, u.last_name, u.image, u.public_profile_username, u.public_profile_token,
                    o.school, o.not_student
             FROM users u
             LEFT JOIN user_onboarding o ON o.user_id = u.id
             WHERE u.public_profile_enabled = 1
             ORDER BY RAND()
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();

    $userIds = array_values(array_map(static function ($u) {
        return (int)$u['id'];
    }, $users));

    $chatCounts = [];
    $imageCounts = [];
    $imageSamples = [];

    if (!empty($userIds)) {
        $in = implode(',', array_fill(0, count($userIds), '?'));
        $types = str_repeat('i', count($userIds));

        $stmt = $db->prepare(
            "SELECT user_id, COUNT(*) AS chat_count
             FROM conversations
             WHERE is_published = 1 AND user_id IN ($in)
             GROUP BY user_id"
        );
        $stmt->bind_param($types, ...$userIds);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $chatCounts[(int)$row['user_id']] = (int)$row['chat_count'];
        }
        $stmt->close();

        $stmt = $db->prepare(
            "SELECT user_id, COUNT(*) AS image_count
             FROM generated_images
             WHERE public = 1 AND user_id IN ($in)
             GROUP BY user_id"
        );
        $stmt->bind_param($types, ...$userIds);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $imageCounts[(int)$row['user_id']] = (int)$row['image_count'];
        }
        $stmt->close();

        $stmt = $db->prepare(
            "SELECT user_id, image_url
             FROM generated_images
             WHERE public = 1 AND user_id IN ($in)
             ORDER BY generated_at DESC"
        );
        $stmt->bind_param($types, ...$userIds);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $uid = (int)$row['user_id'];
            $imgValue = trim((string)($row['image_url'] ?? ''));
            if ($imgValue === '') {
                continue;
            }
            if (!isset($imageSamples[$uid])) {
                $imageSamples[$uid] = [];
            }
            if (count($imageSamples[$uid]) < 3) {
                $imageSamples[$uid][] = $imgValue;
            }
        }
        $stmt->close();
    }

    $payload = [];
    foreach ($users as $u) {
        $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
        if ($name === '') {
            $name = 'ApilageAI User';
        }
        $slug = !empty($u['public_profile_username']) ? $u['public_profile_username'] : $u['public_profile_token'];
        $profileUrl = APP_URL . '/' . $slug;
        $rawImage = trim((string)($u['image'] ?? ''));
        $hasImage = $rawImage !== '' && stripos($rawImage, 'array') === false;
        $profileImage = $hasImage ? user_image_url($rawImage) : '';

        $uid = (int)$u['id'];
        $images = [];
        if (!empty($imageSamples[$uid])) {
            foreach ($imageSamples[$uid] as $img) {
                $images[] = normalize_public_image_url_profile($img);
            }
        }

        $payload[] = [
            'id' => $uid,
            'name' => $name,
            'profile_url' => $profileUrl,
            'image_url' => $profileImage,
            'school' => $u['school'] ?? '',
            'not_student' => (int)($u['not_student'] ?? 0),
            'chat_count' => $chatCounts[$uid] ?? 0,
            'image_count' => $imageCounts[$uid] ?? 0,
            'images' => $images
        ];
    }

    echo json_encode(['success' => true, 'users' => $payload]);
    exit();
}

$slug = trim($_GET['u'] ?? '');
if ($slug === '') {
    http_response_code(404);
    echo 'Profile not found';
    exit();
}

$slug = preg_replace('/[^A-Za-z0-9_-]/', '', $slug);
if ($slug === '') {
    http_response_code(404);
    echo 'Profile not found';
    exit();
}

$stmt = $db->prepare(
    "SELECT u.id, u.first_name, u.last_name, u.image, u.public_profile_enabled, u.public_profile_username, u.public_profile_token,
            u.learning_streak_started_at, u.reg_date, o.school, o.not_student
     FROM users u
     LEFT JOIN user_onboarding o ON o.user_id = u.id
     WHERE u.public_profile_username = ? OR u.public_profile_token = ?
     LIMIT 1"
);
$stmt->bind_param('ss', $slug, $slug);
$stmt->execute();
$result = $stmt->get_result();
$userRow = $result->fetch_assoc();
$stmt->close();

if (!$userRow || (int)$userRow['public_profile_enabled'] !== 1) {
    http_response_code(404);
    echo 'Profile not found';
    exit();
}

$displayName = trim(($userRow['first_name'] ?? '') . ' ' . ($userRow['last_name'] ?? ''));
if ($displayName === '') {
    $displayName = 'ApilageAI User';
}

$profileSlug = !empty($userRow['public_profile_username']) ? $userRow['public_profile_username'] : $userRow['public_profile_token'];
$profileUrl = APP_URL . '/' . $profileSlug;
$rawImage = trim((string)($userRow['image'] ?? ''));
$hasCustomImage = $rawImage !== '' && stripos($rawImage, 'array') === false;
$imageUrl = $hasCustomImage ? user_image_url($rawImage) : '';
$streakStarted = $userRow['learning_streak_started_at'] ?? null;
$memberSinceRaw = $userRow['reg_date'] ?? null;
$memberSince = $memberSinceRaw ? date('M d, Y', strtotime($memberSinceRaw)) : 'Unknown';

$schoolText = 'School not set';
if ((int)($userRow['not_student'] ?? 0) === 1) {
    $schoolText = 'Not a student';
} elseif (!empty($userRow['school'])) {
    $schoolText = $userRow['school'];
}

$coverGradients = [
    'linear-gradient(135deg, #38BDF8 0%, #FF3B30 100%)',
    'linear-gradient(135deg, #0EA5E9 0%, #9333EA 100%)',
    'linear-gradient(135deg, #22C55E 0%, #16A34A 100%)',
    'linear-gradient(135deg, #F97316 0%, #F43F5E 100%)',
    'linear-gradient(135deg, #6366F1 0%, #38BDF8 100%)',
    'linear-gradient(135deg, #FACC15 0%, #F97316 100%)',
    'linear-gradient(135deg, #14B8A6 0%, #0EA5E9 100%)'
];
$coverIndex = (int)(sprintf('%u', crc32($profileSlug)) % count($coverGradients));
$coverStyle = $coverGradients[$coverIndex];

$publishedChats = [];
$chatStmt = $db->prepare(
    "SELECT c.conversation_id, c.title,
            COALESCE(c.published_at, cp.created_at, c.created_at) AS published_at
     FROM conversations c
     LEFT JOIN conversation_published cp
        ON cp.conversation_id = c.conversation_id AND cp.published_by = c.user_id
     WHERE c.user_id = ? AND c.is_published = 1
     ORDER BY published_at DESC
     LIMIT 12"
);
$chatStmt->bind_param('i', $userRow['id']);
$chatStmt->execute();
$chatResult = $chatStmt->get_result();
while ($row = $chatResult->fetch_assoc()) {
    $publishedChats[] = $row;
}
$chatStmt->close();

$publishedImages = [];
$imageStmt = $db->prepare(
    "SELECT id, image_url, prompt, generated_at
     FROM generated_images
     WHERE public = 1 AND user_id = ?
     ORDER BY generated_at DESC
     LIMIT 9"
);
$imageStmt->bind_param('i', $userRow['id']);
$imageStmt->execute();
$imageResult = $imageStmt->get_result();
while ($row = $imageResult->fetch_assoc()) {
    $publishedImages[] = $row;
}
$imageStmt->close();

$exploreUsers = [];
$exploreStmt = $db->prepare(
    "SELECT id, first_name, last_name, image, public_profile_username, public_profile_token
     FROM users
     WHERE public_profile_enabled = 1 AND id <> ?
     ORDER BY RAND()
     LIMIT 8"
);
$exploreStmt->bind_param('i', $userRow['id']);
$exploreStmt->execute();
$exploreResult = $exploreStmt->get_result();
while ($row = $exploreResult->fetch_assoc()) {
    $exploreUsers[] = $row;
}
$exploreStmt->close();

$similarUsers = [];
$schoolRaw = trim((string)($userRow['school'] ?? ''));
if ($schoolRaw !== '' && (int)($userRow['not_student'] ?? 0) !== 1) {
    $tokens = preg_split('/[^A-Za-z0-9]+/', strtolower($schoolRaw));
    $tokens = array_values(array_unique(array_filter($tokens, function ($t) {
        return strlen($t) >= 3;
    })));

    if (empty($tokens) && $schoolRaw !== '') {
        $tokens = [strtolower($schoolRaw)];
    }

    if (!empty($tokens)) {
        $likeClauses = [];
        $types = 'i';
        $params = [$userRow['id']];
        foreach ($tokens as $token) {
            $likeClauses[] = "LOWER(o.school) LIKE ?";
            $types .= 's';
            $params[] = '%' . $token . '%';
        }
        $sql = "
            SELECT u.id, u.first_name, u.last_name, u.image, u.public_profile_username, u.public_profile_token, o.school
            FROM users u
            JOIN user_onboarding o ON o.user_id = u.id
            WHERE u.public_profile_enabled = 1
              AND u.id <> ?
              AND o.not_student = 0
              AND o.school IS NOT NULL
              AND o.school <> ''
              AND (" . implode(' OR ', $likeClauses) . ")
            ORDER BY RAND()
            LIMIT 6
        ";
        $simStmt = $db->prepare($sql);
        $simStmt->bind_param($types, ...$params);
        $simStmt->execute();
        $simResult = $simStmt->get_result();
        while ($row = $simResult->fetch_assoc()) {
            $similarUsers[] = $row;
        }
        $simStmt->close();
    }
}

$headerSent = headers_sent();
if (!$headerSent) {
    header('Content-Type: text/html; charset=utf-8');
}

$title = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . ' | Apilage AI';
$displayNameEscaped = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
$profileUrlEscaped = htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8');
$imageUrlEscaped = htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8');
$streakEscaped = $streakStarted ? htmlspecialchars($streakStarted, ENT_QUOTES, 'UTF-8') : '';
$memberSinceEscaped = htmlspecialchars($memberSince, ENT_QUOTES, 'UTF-8');
$schoolEscaped = htmlspecialchars($schoolText, ENT_QUOTES, 'UTF-8');
$coverStyleEscaped = htmlspecialchars($coverStyle, ENT_QUOTES, 'UTF-8');
$shareImage = $imageUrl !== '' ? $imageUrl : (APP_URL . '/assets/images/logo.png');
$shareImageEscaped = htmlspecialchars($shareImage, ENT_QUOTES, 'UTF-8');
$getSub = function ($str, $length) {
    if (function_exists('mb_substr')) {
        return mb_substr($str, 0, $length, 'UTF-8');
    }
    return substr($str, 0, $length);
};
$getInitials = function ($fullName) use ($getSub) {
    $source = preg_replace('/[^\p{L}\p{N}]+/u', ' ', (string)$fullName);
    $parts = array_values(array_filter(explode(' ', trim($source))));
    if (empty($parts)) {
        return 'AI';
    }
    $first = $parts[0];
    $last = $parts[count($parts) - 1];
    if (count($parts) >= 2) {
        return strtoupper($getSub($first, 1) . $getSub($last, 1));
    }
    return strtoupper($getSub($first, 2));
};
$initials = $getInitials($displayName);
$initialsEscaped = htmlspecialchars($initials, ENT_QUOTES, 'UTF-8');
$shareText = rawurlencode("Check out {$displayName} on ApilageAI");
$shareUrlEncoded = rawurlencode($profileUrl);
$twitterShare = "https://twitter.com/intent/tweet?text={$shareText}&url={$shareUrlEncoded}";
$facebookShare = "https://www.facebook.com/sharer/sharer.php?u={$shareUrlEncoded}";
$whatsappShare = "https://wa.me/?text={$shareText}%20{$shareUrlEncoded}";
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $title; ?></title>
  <meta name="robots" content="index,follow">
  <meta property="og:title" content="<?php echo $title; ?>">
  <meta property="og:description" content="View <?php echo $displayNameEscaped; ?> on Apilageai">
  <meta property="og:image" content="<?php echo $shareImageEscaped; ?>">
  <meta property="og:url" content="<?php echo $profileUrlEscaped; ?>">
  <meta property="og:type" content="profile">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo $title; ?>">
  <meta name="twitter:description" content="View <?php echo $displayNameEscaped; ?> on Apilageai">
  <meta name="twitter:image" content="<?php echo $shareImageEscaped; ?>">
  <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/images/icon.png">
  <link rel="shortcut icon" type="image/png" href="<?php echo APP_URL; ?>/assets/images/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=STIX+Two+Text:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <script>
    (function () {
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      const savedTheme = localStorage.getItem('theme');
      const theme = savedTheme || (prefersDark ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
      if (theme === 'dark') {
        document.documentElement.classList.add('dark');
      }
    })();
  </script>
  <style>
    :root {
      color-scheme: light;
      --brand-red: #e53e3e;
      --brand-blue: #3b82f6;
      --brand-blueLight: #eff6ff;
      --brand-dark: #1a202c;
      --brand-gray: #f9fafb;
      --page-bg: #ffffff;
      --surface: #f9fafb;
      --card-bg: #ffffff;
      --border-color: #d1d5db;
      --text-primary: #1a202c;
      --text-muted: #4a5568;
      --meta-bg: #eff6ff;
      --badge-bg: #e53e3e;
      --badge-text: #ffffff;
      --pattern-color: rgba(15, 23, 42, 0.05);
      --shadow-hard: 0 8px 24px rgba(15, 23, 42, 0.08);
      --shadow-hard-lg: 0 18px 38px rgba(15, 23, 42, 0.12);
    }
    [data-theme="dark"] {
      color-scheme: dark;
      --page-bg: #1a1a1a;
      --surface: #1a1a1a;
      --card-bg: #1e1e1e;
      --border-color: #444444;
      --text-primary: #f7fafc;
      --text-muted: #a0aec0;
      --meta-bg: #1a2a3a;
      --badge-bg: #e53e3e;
      --badge-text: #ffffff;
      --pattern-color: rgba(255, 255, 255, 0.06);
      --shadow-hard: 0 10px 26px rgba(0, 0, 0, 0.35);
      --shadow-hard-lg: 0 18px 40px rgba(0, 0, 0, 0.45);
    }
    body {
      margin: 0;
      font-family: "Inter", "Segoe UI", Arial, sans-serif;
      background: var(--page-bg);
      color: var(--text-primary);
      min-height: 100vh;
      display: block;
      padding: 0;
      box-sizing: border-box;
    }
    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background-image: radial-gradient(var(--pattern-color) 1px, transparent 1px);
      background-size: 26px 26px;
      opacity: 0.5;
      pointer-events: none;
    }
    .page-layout {
      position: relative;
      display: grid;
      grid-template-columns: minmax(240px, 300px) minmax(0, 1fr);
      min-height: 100vh;
    }
    .sidebar {
      border-right: 2px solid var(--border-color);
      background: var(--surface);
      padding: 24px 20px;
      display: flex;
      flex-direction: column;
      gap: 24px;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
      box-shadow: var(--shadow-hard);
      z-index: 2;
    }
    [data-theme="dark"] .sidebar {
      box-shadow: none;
    }
    .sidebar-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      font-family: "Inter", sans-serif;
      font-size: 20px;
      font-weight: 800;
      color: var(--text-primary);
    }
    .sidebar-brand {
      display: inline-flex;
      align-items: center;
      gap: 12px;
    }
    .sidebar-header img {
      width: 36px;
      height: 36px;
      border-radius: 12px;
      border: 2px solid var(--border-color);
      background: #ffffff;
    }
    .sidebar-toggle,
    .sidebar-show {
      border: 2px solid var(--border-color);
      background: var(--card-bg);
      color: var(--text-primary);
      width: 36px;
      height: 36px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: var(--shadow-hard);
      transition: transform 0.15s ease;
    }
    [data-theme="dark"] .sidebar-toggle,
    [data-theme="dark"] .sidebar-show {
      box-shadow: none;
    }
    .sidebar-toggle:hover,
    .sidebar-show:hover {
      transform: translateY(-2px);
    }
    .sidebar-show {
      position: fixed;
      top: 18px;
      left: 18px;
      z-index: 60;
      display: none;
    }
    .sidebar-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .sidebar-actions a {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 999px;
      border: 2px solid var(--border-color);
      background: var(--card-bg);
      text-decoration: none;
      color: var(--text-primary);
      font-size: 12px;
      font-weight: 600;
      box-shadow: var(--shadow-hard);
      transition: transform 0.15s ease;
    }
    [data-theme="dark"] .sidebar-actions a {
      box-shadow: none;
    }
    .sidebar-actions a:hover {
      transform: translateY(-2px);
    }
    .sidebar-section {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .sidebar-title {
      font-size: 13px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--text-muted);
    }
    .sidebar-search {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
      border: 2px solid var(--border-color);
      border-radius: 999px;
      padding: 10px 14px;
      background: var(--card-bg);
      box-shadow: var(--shadow-hard);
      cursor: pointer;
      text-align: left;
      font-family: inherit;
      color: var(--text-primary);
      transition: transform 0.15s ease;
    }
    [data-theme="dark"] .sidebar-search {
      box-shadow: none;
    }
    .sidebar-search:hover {
      transform: translateY(-2px);
    }
    .sidebar-search span {
      font-size: 13px;
      font-weight: 600;
      color: var(--text-muted);
    }
    .user-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .user-row {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 10px;
      border-radius: 14px;
      border: 2px solid var(--border-color);
      text-decoration: none;
      color: var(--text-primary);
      background: var(--card-bg);
      box-shadow: var(--shadow-hard);
      transition: transform 0.15s ease;
    }
    [data-theme="dark"] .user-row {
      box-shadow: none;
    }
    .user-row:hover {
      transform: translateY(-2px);
    }
    .user-row img {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--border-color);
      background: #ffffff;
    }
    .user-avatar-fallback {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 2px solid var(--border-color);
      background: var(--meta-bg);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 700;
      color: var(--text-primary);
      letter-spacing: 0.04em;
    }
    .user-row .user-name {
      font-size: 14px;
      font-weight: 700;
    }
    .user-row .user-sub {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 600;
    }
    .sidebar-empty {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-muted);
      padding: 10px 12px;
      border-radius: 12px;
      border: 2px dashed var(--text-muted);
      background: var(--surface);
    }
    .profile-shell {
      position: relative;
      width: 100%;
      min-height: 100vh;
      background: var(--page-bg);
      border-radius: 0;
      border: none;
      box-shadow: none;
      overflow: hidden;
      z-index: 1;
    }
    .cover {
      height: 280px;
      background: <?php echo $coverStyleEscaped; ?>;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .cover::after {
      content: "";
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(255,255,255,0.2) 1px, transparent 1px);
      background-size: 18px 18px;
      opacity: 0.6;
      pointer-events: none;
    }
    .cover-title {
      font-family: "STIX Two Text", serif;
      font-size: 32px;
      letter-spacing: 0.4em;
      text-transform: uppercase;
      color: #ffffff;
      text-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
      z-index: 1;
    }
    .profile-body {
      padding: 0;
    }
    .content-inner {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 36px 48px;
    }
    .avatar-wrap {
      margin-top: -90px;
      display: flex;
      justify-content: center;
      position: relative;
      z-index: 3;
      min-height: 160px;
    }
    .profile-avatar {
      width: 160px;
      height: 160px;
      border-radius: 50%;
      object-fit: cover;
      border: 6px solid #ffffff;
      box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
      background: #ffffff;
      position: relative;
      z-index: 6;
    }
    .avatar-fallback {
      position: absolute;
      width: 160px;
      height: 160px;
      border-radius: 50%;
      background: var(--meta-bg);
      border: 6px solid #ffffff;
      box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: "STIX Two Text", serif;
      font-weight: 800;
      font-size: 40px;
      color: var(--text-primary);
      letter-spacing: 0.06em;
      z-index: 5;
    }
    [data-theme="dark"] .profile-avatar {
      border-color: var(--surface);
      background: var(--surface);
    }
    [data-theme="dark"] .avatar-fallback {
      border-color: var(--surface);
      background: var(--meta-bg);
      color: var(--text-primary);
    }
    .profile-name {
      font-family: "STIX Two Text", serif;
      font-size: 30px;
      font-weight: 800;
      margin: 18px 0 6px;
      text-align: center;
    }
    .profile-link {
      font-size: 14px;
      color: var(--text-muted);
      margin-bottom: 18px;
      word-break: break-all;
      text-align: center;
    }
    .profile-link a {
      color: var(--brand-red);
      text-decoration: none;
      font-weight: 600;
    }
    .meta-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin: 20px 0 28px;
    }
    .meta-card {
      border: 2px solid var(--border-color);
      border-radius: 16px;
      padding: 14px 16px;
      box-shadow: var(--shadow-hard);
      background: var(--meta-bg);
      display: flex;
      gap: 10px;
      align-items: center;
    }
    [data-theme="dark"] .meta-card {
      box-shadow: none;
    }
    .meta-card i {
      font-size: 18px;
      color: var(--text-primary);
    }
    .meta-label {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
    }
    .meta-value {
      font-size: 14px;
      font-weight: 700;
      color: var(--text-primary);
    }
    .share-row {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
      margin-bottom: 28px;
    }
    .share-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border: 2px solid var(--border-color);
      border-radius: 999px;
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 700;
      background: var(--card-bg);
      color: var(--text-primary);
      text-decoration: none;
      box-shadow: var(--shadow-hard);
      transition: transform 0.15s ease;
    }
    [data-theme="dark"] .share-btn {
      box-shadow: none;
    }
    .share-btn:hover {
      transform: translateY(-2px);
    }
    .share-btn.copy {
      background: var(--brand-red);
      color: #ffffff;
      border-color: var(--border-color);
    }
    .section-title {
      font-family: "STIX Two Text", serif;
      font-size: 22px;
      font-weight: 800;
      margin: 0 0 16px;
    }
    .search-row {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 18px;
    }
    .search-label {
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
    }
    .search-input-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
      border: 2px solid var(--border-color);
      border-radius: 999px;
      padding: 10px 14px;
      background: var(--surface);
      box-shadow: var(--shadow-hard);
    }
    [data-theme="dark"] .search-input-wrap {
      box-shadow: none;
    }
    .search-input-wrap i {
      color: var(--text-muted);
      font-size: 14px;
    }
    .search-input {
      border: none;
      outline: none;
      font-size: 14px;
      background: transparent;
      width: 100%;
      color: var(--text-primary);
      font-family: inherit;
    }
    .search-status {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 600;
    }
    .chat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
    }
    .chat-card {
      display: block;
      border: 2px solid var(--border-color);
      border-radius: 18px;
      padding: 16px;
      background: var(--card-bg);
      text-decoration: none;
      color: var(--text-primary);
      box-shadow: var(--shadow-hard);
      transition: transform 0.15s ease;
    }
    [data-theme="dark"] .chat-card {
      box-shadow: none;
    }
    .chat-card:hover {
      transform: translateY(-3px);
    }
    .image-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 200px));
      gap: 16px;
      justify-content: center;
    }
    .image-card {
      border: 2px solid var(--border-color);
      border-radius: 18px;
      background: var(--card-bg);
      box-shadow: var(--shadow-hard);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      gap: 0;
    }
    [data-theme="dark"] .image-card {
      box-shadow: none;
    }
    .image-card img {
      width: 100%;
      aspect-ratio: 1 / 1;
      object-fit: cover;
      display: block;
      background: var(--surface);
    }
    .image-caption {
      padding: 10px 12px 12px;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-muted);
    }
    .chat-title {
      font-weight: 700;
      font-size: 15px;
      margin-bottom: 6px;
    }
    .chat-date {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 600;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--badge-bg);
      color: var(--badge-text);
      padding: 8px 14px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
    }
    .badge-row {
      display: flex;
      justify-content: center;
      margin-bottom: 22px;
    }
    .empty-state {
      border: 2px dashed var(--text-muted);
      border-radius: 16px;
      padding: 20px;
      text-align: center;
      font-weight: 600;
      color: var(--text-muted);
      background: var(--surface);
    }
    .mobile-search {
      display: none;
      justify-content: center;
      margin: 18px 0 8px;
    }
    .user-search-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.55);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      z-index: 999;
    }
    .user-search-backdrop[hidden] {
      display: none;
    }
    [data-theme="dark"] .user-search-backdrop {
      background: rgba(0, 0, 0, 0.6);
    }
    .user-search-modal {
      width: min(960px, 96vw);
      max-height: 90vh;
      overflow: auto;
      background: var(--card-bg);
      border: 2px solid var(--border-color);
      border-radius: 22px;
      box-shadow: var(--shadow-hard-lg);
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .user-search-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 12px;
    }
    .user-search-header h2 {
      margin: 0;
      font-family: "STIX Two Text", serif;
      font-size: 22px;
      font-weight: 700;
    }
    .user-search-header p {
      margin: 4px 0 0;
      font-size: 13px;
      color: var(--text-muted);
    }
    .user-search-close {
      border: 2px solid var(--border-color);
      background: var(--card-bg);
      color: var(--text-primary);
      width: 36px;
      height: 36px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: var(--shadow-hard);
    }
    [data-theme="dark"] .user-search-close {
      box-shadow: none;
    }
    .user-search-input-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
      border: 2px solid var(--border-color);
      border-radius: 999px;
      padding: 10px 14px;
      background: var(--card-bg);
      box-shadow: var(--shadow-hard);
    }
    [data-theme="dark"] .user-search-input-wrap {
      box-shadow: none;
    }
    .user-search-input-wrap i {
      color: var(--text-muted);
    }
    .user-search-input {
      flex: 1;
      border: none;
      background: transparent;
      font-size: 14px;
      color: var(--text-primary);
      outline: none;
      font-family: inherit;
    }
    .user-search-clear {
      border: none;
      background: transparent;
      color: var(--text-muted);
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
    }
    .user-search-results {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }
    .user-search-card {
      border: 2px solid var(--border-color);
      border-radius: 18px;
      padding: 16px;
      background: var(--card-bg);
      text-decoration: none;
      color: var(--text-primary);
      box-shadow: var(--shadow-hard);
      display: flex;
      flex-direction: column;
      gap: 12px;
      transition: transform 0.15s ease;
    }
    [data-theme="dark"] .user-search-card {
      box-shadow: none;
    }
    .user-search-card:hover {
      transform: translateY(-2px);
    }
    .user-search-main {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .user-search-avatar {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      border: 2px solid var(--border-color);
      background: var(--meta-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: var(--text-primary);
      overflow: hidden;
      flex-shrink: 0;
    }
    .user-search-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .user-search-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .user-search-name {
      font-size: 16px;
      font-weight: 700;
    }
    .user-search-meta {
      font-size: 13px;
      color: var(--text-muted);
      font-weight: 600;
    }
    .user-search-stats {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 600;
    }
    .user-search-images {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 8px;
    }
    .user-search-images img {
      width: 100%;
      aspect-ratio: 1 / 1;
      object-fit: cover;
      border-radius: 12px;
      border: 2px solid var(--border-color);
      background: var(--surface);
    }
    .user-search-images-empty {
      grid-column: 1 / -1;
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 600;
      padding: 10px 0;
    }
    body.modal-open {
      overflow: hidden;
    }
    .mobile-dock {
      position: fixed;
      left: 50%;
      bottom: 18px;
      transform: translateX(-50%);
      background: var(--card-bg);
      border: 2px solid var(--border-color);
      border-radius: 999px;
      padding: 10px 14px;
      display: none;
      align-items: center;
      gap: 12px;
      box-shadow: var(--shadow-hard-lg);
      z-index: 50;
    }
    [data-theme="dark"] .mobile-dock {
      box-shadow: none;
    }
    .dock-item {
      display: inline-flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      border: none;
      background: transparent;
      color: var(--text-primary);
      text-decoration: none;
      font-size: 10px;
      font-weight: 700;
      cursor: pointer;
      min-width: 58px;
    }
    .dock-item i {
      font-size: 16px;
    }
    @media (max-width: 900px) {
      .page-layout {
        grid-template-columns: 1fr;
      }
      body {
        padding-bottom: 96px;
      }
      .sidebar {
        display: none;
      }
      .content-inner {
        padding: 0 20px 36px;
      }
      .cover {
        height: 230px;
      }
      .mobile-search {
        display: none;
      }
      .mobile-dock {
        display: flex;
      }
      .sidebar-show {
        display: none !important;
      }
    }
    body.sidebar-hidden .page-layout {
      grid-template-columns: 1fr;
    }
    body.sidebar-hidden .sidebar {
      display: none;
    }
    body.sidebar-hidden .sidebar-show {
      display: inline-flex;
    }
    @media (max-width: 640px) {
      .page-layout {
        grid-template-columns: 1fr;
      }
      .sidebar {
        position: relative;
        height: auto;
        border-right: none;
        border-bottom: 2px solid var(--border-color);
      }
      .content-inner {
        padding: 0 20px 28px;
      }
      .cover-title {
        font-size: 22px;
        letter-spacing: 0.3em;
      }
      .profile-avatar {
        width: 130px;
        height: 130px;
      }
      .avatar-fallback {
        width: 130px;
        height: 130px;
        font-size: 32px;
      }
      .avatar-wrap {
        min-height: 130px;
      }
      .profile-name {
        font-size: 24px;
      }
      .user-search-backdrop {
        padding: 0;
      }
      .user-search-modal {
        width: 100%;
        height: 100%;
        max-height: none;
        border-radius: 0;
      }
    }
  </style>
</head>
<body>
  <div class="page-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-brand">
          <img src="<?php echo APP_URL; ?>/assets/images/icon.png" alt="Apilageai logo">
          <span>Apilageai</span>
        </div>
        <button class="sidebar-toggle" type="button" id="sidebarToggle" aria-label="Hide sidebar" aria-pressed="false">
          <i class="fa-solid fa-angles-left"></i>
        </button>
      </div>
      <div class="sidebar-actions">
        <a href="<?php echo APP_URL; ?>/app" aria-label="Open chat app">
          <i class="fa-solid fa-comments"></i>
          <span>Chat</span>
        </a>
        <a href="<?php echo APP_URL; ?>/images" aria-label="Open image gallery">
          <i class="fa-solid fa-image"></i>
          <span>Images</span>
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-title">Explore Users</div>
        <button class="sidebar-search" type="button" id="openUserSearch" data-open-user-search aria-haspopup="dialog" aria-controls="userSearchModal">
          <i class="fa-solid fa-magnifying-glass"></i>
          <span>Search users</span>
        </button>
        <div id="exploreStatus" class="search-status" role="status" aria-live="polite">Search all public profiles.</div>
        <div class="user-list" id="exploreList">
          <?php if (!empty($exploreUsers)): ?>
            <?php foreach ($exploreUsers as $row): ?>
              <?php
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                if ($name === '') {
                    $name = 'ApilageAI User';
                }
                $nameEscaped = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                $nameData = htmlspecialchars(strtolower($name), ENT_QUOTES, 'UTF-8');
                $slugValue = !empty($row['public_profile_username']) ? $row['public_profile_username'] : $row['public_profile_token'];
                $profileLink = APP_URL . '/' . $slugValue;
                $profileLinkEscaped = htmlspecialchars($profileLink, ENT_QUOTES, 'UTF-8');
                $rawAvatar = trim((string)($row['image'] ?? ''));
                $hasAvatar = $rawAvatar !== '' && stripos($rawAvatar, 'array') === false;
                $avatarUrl = $hasAvatar ? user_image_url($rawAvatar) : '';
                $avatarEscaped = htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8');
                $avatarInitials = htmlspecialchars($getInitials($name), ENT_QUOTES, 'UTF-8');
              ?>
              <a class="user-row" href="<?php echo $profileLinkEscaped; ?>" data-user-name="<?php echo $nameData; ?>">
                <?php if ($avatarEscaped !== ''): ?>
                  <img src="<?php echo $avatarEscaped; ?>" alt="<?php echo $nameEscaped; ?>">
                <?php else: ?>
                  <div class="user-avatar-fallback" aria-hidden="true"><?php echo $avatarInitials; ?></div>
                <?php endif; ?>
                <div>
                  <div class="user-name"><?php echo $nameEscaped; ?></div>
                  <div class="user-sub">View profile</div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="sidebar-empty">No public profiles yet.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-title">Similar Schools</div>
        <div class="user-list">
          <?php if (!empty($similarUsers)): ?>
            <?php foreach ($similarUsers as $row): ?>
              <?php
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                if ($name === '') {
                    $name = 'ApilageAI User';
                }
                $nameEscaped = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                $schoolRow = trim((string)($row['school'] ?? ''));
                $schoolRowEscaped = htmlspecialchars($schoolRow !== '' ? $schoolRow : 'School not set', ENT_QUOTES, 'UTF-8');
                $slugValue = !empty($row['public_profile_username']) ? $row['public_profile_username'] : $row['public_profile_token'];
                $profileLink = APP_URL . '/' . $slugValue;
                $profileLinkEscaped = htmlspecialchars($profileLink, ENT_QUOTES, 'UTF-8');
                $rawAvatar = trim((string)($row['image'] ?? ''));
                $hasAvatar = $rawAvatar !== '' && stripos($rawAvatar, 'array') === false;
                $avatarUrl = $hasAvatar ? user_image_url($rawAvatar) : '';
                $avatarEscaped = htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8');
                $avatarInitials = htmlspecialchars($getInitials($name), ENT_QUOTES, 'UTF-8');
              ?>
              <a class="user-row" href="<?php echo $profileLinkEscaped; ?>">
                <?php if ($avatarEscaped !== ''): ?>
                  <img src="<?php echo $avatarEscaped; ?>" alt="<?php echo $nameEscaped; ?>">
                <?php else: ?>
                  <div class="user-avatar-fallback" aria-hidden="true"><?php echo $avatarInitials; ?></div>
                <?php endif; ?>
                <div>
                  <div class="user-name"><?php echo $nameEscaped; ?></div>
                  <div class="user-sub"><?php echo $schoolRowEscaped; ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="sidebar-empty">
              <?php echo ($schoolRaw === '' || (int)($userRow['not_student'] ?? 0) === 1) ? 'No similar school matches.' : 'No similar school matches yet.'; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </aside>

    <main class="profile-shell">
      <div class="cover">
        <div class="cover-title">Apilageai</div>
      </div>
      <div class="profile-body">
        <div class="content-inner">
          <div class="avatar-wrap">
          <div class="avatar-fallback" aria-hidden="true"><?php echo $initialsEscaped; ?></div>
          <?php if ($imageUrlEscaped !== ''): ?>
            <img class="profile-avatar" src="<?php echo $imageUrlEscaped; ?>" alt="<?php echo $displayNameEscaped; ?>" onerror="this.style.display='none';">
          <?php endif; ?>
          </div>
        <h1 class="profile-name"><?php echo $displayNameEscaped; ?></h1>
        <div class="profile-link">
          <a href="<?php echo $profileUrlEscaped; ?>"><?php echo $profileUrlEscaped; ?></a>
        </div>
        <div class="mobile-search">
          <button class="share-btn" type="button" data-open-user-search aria-haspopup="dialog" aria-controls="userSearchModal">
            <i class="fa-solid fa-magnifying-glass"></i> Search users
          </button>
        </div>

        <div class="meta-grid">
          <div class="meta-card">
            <i class="fa-solid fa-school"></i>
            <div>
              <div class="meta-label">School</div>
              <div class="meta-value"><?php echo $schoolEscaped; ?></div>
            </div>
          </div>
          <div class="meta-card">
            <i class="fa-solid fa-calendar-days"></i>
            <div>
              <div class="meta-label">Member Since</div>
              <div class="meta-value"><?php echo $memberSinceEscaped; ?></div>
            </div>
          </div>
        </div>

        <div class="badge-row">
          <?php if ($streakStarted): ?>
            <div class="badge">Learning streak started on <?php echo $streakEscaped; ?></div>
          <?php else: ?>
            <div class="badge">Learning streak not started</div>
          <?php endif; ?>
        </div>

        <div class="share-row">
          <a class="share-btn" href="<?php echo htmlspecialchars($twitterShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
            <i class="fa-brands fa-x-twitter"></i> Share
          </a>
          <a class="share-btn" href="<?php echo htmlspecialchars($facebookShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
            <i class="fa-brands fa-facebook"></i> Share
          </a>
          <a class="share-btn" href="<?php echo htmlspecialchars($whatsappShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
            <i class="fa-brands fa-whatsapp"></i> Share
          </a>
          <button class="share-btn copy" type="button" id="copyProfileLink">
            <i class="fa-solid fa-link"></i> Copy Link
          </button>
        </div>

        <section>
          <h2 class="section-title">Published Chats</h2>
          <?php if (!empty($publishedChats)): ?>
            <div class="search-row" role="search">
              <label class="search-label" for="chatSearch">Search Published Chats</label>
              <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input class="search-input" id="chatSearch" type="search" placeholder="Search chats by title" aria-describedby="chatSearchStatus">
              </div>
              <div id="chatSearchStatus" class="search-status" role="status" aria-live="polite"></div>
            </div>
            <div class="chat-grid" id="chatGrid">
              <?php foreach ($publishedChats as $chat): ?>
                <?php
                  $chatTitle = trim((string)($chat['title'] ?? ''));
                  if ($chatTitle === '') {
                      $chatTitle = 'Untitled Chat';
                  }
                  $chatTitleEscaped = htmlspecialchars($chatTitle, ENT_QUOTES, 'UTF-8');
                  $chatTitleData = htmlspecialchars(strtolower($chatTitle), ENT_QUOTES, 'UTF-8');
                  $chatDate = $chat['published_at'] ?? $chat['created_at'] ?? null;
                  $chatDateLabel = $chatDate ? date('M d, Y', strtotime($chatDate)) : 'Date unknown';
                  $chatDateEscaped = htmlspecialchars($chatDateLabel, ENT_QUOTES, 'UTF-8');
                  $chatUrl = APP_URL . '/app/chat/' . (int)$chat['conversation_id'];
                  $chatUrlEscaped = htmlspecialchars($chatUrl, ENT_QUOTES, 'UTF-8');
                ?>
                <a class="chat-card" data-chat-title="<?php echo $chatTitleData; ?>" href="<?php echo $chatUrlEscaped; ?>">
                  <div class="chat-title"><?php echo $chatTitleEscaped; ?></div>
                  <div class="chat-date">Published on <?php echo $chatDateEscaped; ?></div>
                </a>
              <?php endforeach; ?>
            </div>
            <div id="chatEmptyState" class="empty-state" hidden>No chats match your search.</div>
          <?php else: ?>
            <div class="empty-state">No published chats yet.</div>
          <?php endif; ?>
        </section>

        <section style="margin-top: 28px;">
          <h2 class="section-title">Published Images</h2>
          <?php if (!empty($publishedImages)): ?>
            <div class="image-grid">
              <?php foreach ($publishedImages as $img): ?>
                <?php
                  $imgUrlRaw = trim((string)($img['image_url'] ?? ''));
                  $imgUrl = normalize_public_image_url_profile($imgUrlRaw);
                  $imgUrlEscaped = htmlspecialchars($imgUrl ?? '', ENT_QUOTES, 'UTF-8');
                  $prompt = trim((string)($img['prompt'] ?? ''));
                  if ($prompt === '') {
                      $prompt = 'Published image';
                  }
                  if (function_exists('mb_strlen')) {
                      if (mb_strlen($prompt, 'UTF-8') > 80) {
                          $prompt = mb_substr($prompt, 0, 77, 'UTF-8') . '...';
                      }
                  } elseif (strlen($prompt) > 80) {
                      $prompt = substr($prompt, 0, 77) . '...';
                  }
                  $promptEscaped = htmlspecialchars($prompt, ENT_QUOTES, 'UTF-8');
                ?>
                <?php if ($imgUrlEscaped !== ''): ?>
                  <div class="image-card">
                    <img src="<?php echo $imgUrlEscaped; ?>" alt="<?php echo $promptEscaped; ?>">
                    <div class="image-caption"><?php echo $promptEscaped; ?></div>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="empty-state">No published images yet.</div>
          <?php endif; ?>
        </section>
      </div>
        </div>
      </div>
    </main>
  </div>

  <button class="sidebar-show" type="button" id="sidebarShow" aria-label="Show sidebar">
    <i class="fa-solid fa-angles-right"></i>
  </button>

  <nav class="mobile-dock" aria-label="Quick actions">
    <a class="dock-item" href="<?php echo APP_URL; ?>/app" aria-label="Open chat app">
      <i class="fa-solid fa-comments"></i>
      <span>Chat</span>
    </a>
    <a class="dock-item" href="<?php echo APP_URL; ?>/images" aria-label="Open image gallery">
      <i class="fa-solid fa-image"></i>
      <span>Images</span>
    </a>
    <button class="dock-item" type="button" data-open-user-search aria-haspopup="dialog" aria-controls="userSearchModal">
      <i class="fa-solid fa-magnifying-glass"></i>
      <span>Search</span>
    </button>
  </nav>

  <div class="user-search-backdrop" id="userSearchModal" hidden aria-hidden="true">
    <div class="user-search-modal" role="dialog" aria-modal="true" aria-labelledby="userSearchTitle" aria-describedby="userSearchDesc">
      <div class="user-search-header">
        <div>
          <h2 id="userSearchTitle">Search users</h2>
          <p id="userSearchDesc">Find public profiles and preview their published chats and images.</p>
        </div>
        <button class="user-search-close" type="button" id="userSearchClose" aria-label="Close search">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="user-search-input-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input class="user-search-input" id="userSearchInput" type="search" placeholder="Search by name, username, or school">
        <button class="user-search-clear" type="button" id="userSearchClear">Clear</button>
      </div>
      <div id="userSearchStatus" class="search-status" role="status" aria-live="polite"></div>
      <div id="userSearchResults" class="user-search-results" role="list"></div>
      <div id="userSearchEmpty" class="empty-state" hidden>No public profiles match your search.</div>
    </div>
  </div>

  <script>
    const copyBtn = document.getElementById('copyProfileLink');
    if (copyBtn) {
      copyBtn.addEventListener('click', async () => {
        const url = "<?php echo $profileUrlEscaped; ?>";
        try {
          if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(url);
          } else {
            const tempInput = document.createElement('input');
            tempInput.value = url;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            tempInput.remove();
          }
          copyBtn.textContent = 'Link Copied!';
          setTimeout(() => { copyBtn.innerHTML = '<i class="fa-solid fa-link"></i> Copy Link'; }, 1600);
        } catch (e) {
          copyBtn.textContent = 'Copy Failed';
          setTimeout(() => { copyBtn.innerHTML = '<i class="fa-solid fa-link"></i> Copy Link'; }, 1600);
        }
      });
    }

    const chatSearch = document.getElementById('chatSearch');
    const chatCards = Array.from(document.querySelectorAll('.chat-card'));
    const chatStatus = document.getElementById('chatSearchStatus');
    const chatEmpty = document.getElementById('chatEmptyState');

    function updateChatStatus(visibleCount, query) {
      if (!chatStatus) return;
      if (!query) {
        chatStatus.textContent = `Showing ${visibleCount} published chats.`;
        return;
      }
      chatStatus.textContent = `Found ${visibleCount} result${visibleCount === 1 ? '' : 's'} for "${query}".`;
    }

    if (chatSearch && chatCards.length) {
      updateChatStatus(chatCards.length, '');
      chatSearch.addEventListener('input', (event) => {
        const query = event.target.value.trim().toLowerCase();
        let visibleCount = 0;
        chatCards.forEach((card) => {
          const title = card.getAttribute('data-chat-title') || '';
          const match = query === '' || title.includes(query);
          card.style.display = match ? '' : 'none';
          if (match) visibleCount += 1;
        });
        if (chatEmpty) {
          chatEmpty.hidden = visibleCount !== 0;
        }
        updateChatStatus(visibleCount, query);
      });
    }

    const searchEndpoint = "<?php echo APP_URL; ?>/public_profile.php?action=search_users";
    const searchModal = document.getElementById('userSearchModal');
    const searchInput = document.getElementById('userSearchInput');
    const searchResults = document.getElementById('userSearchResults');
    const searchStatus = document.getElementById('userSearchStatus');
    const searchEmpty = document.getElementById('userSearchEmpty');
    const searchClose = document.getElementById('userSearchClose');
    const searchClear = document.getElementById('userSearchClear');
    const openSearchButtons = Array.from(document.querySelectorAll('[data-open-user-search]'));
    let searchTimer = null;
    let activeSearchId = 0;
    let lastFocused = null;
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarShow = document.getElementById('sidebarShow');

    if (searchModal) {
      searchModal.hidden = true;
      searchModal.setAttribute('aria-hidden', 'true');
    }
    if (sidebarShow) {
      sidebarShow.hidden = false;
    }

    function setSidebarHidden(hidden) {
      document.body.classList.toggle('sidebar-hidden', hidden);
      if (sidebarToggle) {
        sidebarToggle.setAttribute('aria-pressed', hidden ? 'true' : 'false');
        sidebarToggle.setAttribute('aria-label', hidden ? 'Show sidebar' : 'Hide sidebar');
      }
    }

    function initialsFromName(name) {
      if (!name) return 'AI';
      const parts = name.trim().split(/\s+/).filter(Boolean);
      if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
      }
      if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
      }
      return 'AI';
    }

    function setSearchStatus(message) {
      if (!searchStatus) return;
      searchStatus.textContent = message;
    }

    function renderSearchResults(users, query) {
      if (!searchResults) return;
      searchResults.innerHTML = '';
      if (!Array.isArray(users) || users.length === 0) {
        if (searchEmpty) {
          searchEmpty.hidden = false;
          searchEmpty.textContent = query ? 'No public profiles match your search.' : 'No public profiles yet.';
        }
        setSearchStatus(query ? `No results for "${query}".` : 'No public profiles yet.');
        return;
      }

      if (searchEmpty) {
        searchEmpty.hidden = true;
      }
      setSearchStatus(query ? `Found ${users.length} result${users.length === 1 ? '' : 's'} for "${query}".` : `Showing ${users.length} suggested users.`);

      users.forEach((user) => {
        const card = document.createElement('a');
        card.className = 'user-search-card';
        card.href = user.profile_url || '#';
        card.setAttribute('role', 'listitem');

        const main = document.createElement('div');
        main.className = 'user-search-main';

        const avatar = document.createElement('div');
        avatar.className = 'user-search-avatar';
        if (user.image_url) {
          const img = document.createElement('img');
          img.src = user.image_url;
          img.alt = `${user.name || 'User'} profile picture`;
          avatar.appendChild(img);
        } else {
          avatar.textContent = initialsFromName(user.name || '');
        }

        const info = document.createElement('div');
        info.className = 'user-search-info';

        const name = document.createElement('div');
        name.className = 'user-search-name';
        name.textContent = user.name || 'ApilageAI User';

        const meta = document.createElement('div');
        meta.className = 'user-search-meta';
        if (Number(user.not_student) === 1) {
          meta.textContent = 'Not a student';
        } else if (user.school) {
          meta.textContent = user.school;
        } else {
          meta.textContent = 'School not set';
        }

        const stats = document.createElement('div');
        stats.className = 'user-search-stats';
        const chatCount = Number(user.chat_count) || 0;
        const imageCount = Number(user.image_count) || 0;
        stats.textContent = `${chatCount} chat${chatCount === 1 ? '' : 's'} · ${imageCount} image${imageCount === 1 ? '' : 's'}`;

        info.append(name, meta, stats);
        main.append(avatar, info);
        card.appendChild(main);

        const imagesWrap = document.createElement('div');
        imagesWrap.className = 'user-search-images';
        const images = Array.isArray(user.images) ? user.images.slice(0, 3) : [];
        if (images.length) {
          images.forEach((imgUrl) => {
            const img = document.createElement('img');
            img.src = imgUrl;
            img.alt = 'Published image';
            imagesWrap.appendChild(img);
          });
        } else {
          const empty = document.createElement('div');
          empty.className = 'user-search-images-empty';
          empty.textContent = 'No public images yet.';
          imagesWrap.appendChild(empty);
        }

        card.appendChild(imagesWrap);
        searchResults.appendChild(card);
      });
    }

    async function runUserSearch(query) {
      if (!searchResults) return;
      const requestId = ++activeSearchId;
      if (searchEmpty) {
        searchEmpty.hidden = true;
      }
      setSearchStatus(query ? `Searching for "${query}"...` : 'Loading suggestions...');

      try {
        const url = `${searchEndpoint}&q=${encodeURIComponent(query)}&limit=12`;
        const response = await fetch(url, { credentials: 'same-origin' });
        const payload = await response.json();
        if (requestId !== activeSearchId) return;
        const users = Array.isArray(payload.users) ? payload.users : [];
        renderSearchResults(users, query);
      } catch (error) {
        if (requestId !== activeSearchId) return;
        if (searchResults) {
          searchResults.innerHTML = '';
        }
        if (searchEmpty) {
          searchEmpty.hidden = false;
          searchEmpty.textContent = 'Unable to load users right now.';
        }
        setSearchStatus('Search failed. Please try again.');
      }
    }

    function openSearchModal() {
      if (!searchModal) return;
      lastFocused = document.activeElement;
      searchModal.hidden = false;
      searchModal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      setTimeout(() => {
        if (searchInput) {
          searchInput.focus();
          runUserSearch(searchInput.value.trim());
        } else {
          runUserSearch('');
        }
      }, 0);
    }

    function closeSearchModal() {
      if (!searchModal) return;
      searchModal.hidden = true;
      searchModal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('modal-open');
      if (lastFocused && typeof lastFocused.focus === 'function') {
        lastFocused.focus();
      }
    }

    if (openSearchButtons.length) {
      openSearchButtons.forEach((btn) => {
        btn.addEventListener('click', (event) => {
          event.preventDefault();
          openSearchModal();
        });
      });
    }

    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', () => {
        setSidebarHidden(!document.body.classList.contains('sidebar-hidden'));
      });
    }
    if (sidebarShow) {
      sidebarShow.addEventListener('click', () => {
        setSidebarHidden(false);
      });
    }

    if (searchClose) {
      searchClose.addEventListener('click', closeSearchModal);
    }

    if (searchModal) {
      searchModal.addEventListener('click', (event) => {
        if (event.target === searchModal) {
          closeSearchModal();
        }
      });
    }

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && searchModal && !searchModal.hidden) {
        closeSearchModal();
      }
    });

    if (searchInput) {
      searchInput.addEventListener('input', (event) => {
        const query = event.target.value.trim();
        if (searchTimer) {
          window.clearTimeout(searchTimer);
        }
        searchTimer = window.setTimeout(() => runUserSearch(query), 250);
      });
    }

    if (searchClear) {
      searchClear.addEventListener('click', () => {
        if (!searchInput) return;
        searchInput.value = '';
        searchInput.focus();
        runUserSearch('');
      });
    }
  </script>
</body>
</html>
