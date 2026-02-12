<?php
require_once __DIR__ . '/../backend/bootstrap.php';

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
$initialsSource = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $displayName);
$initialParts = array_values(array_filter(explode(' ', trim($initialsSource))));
$initials = 'AI';
$getSub = function ($str, $length) {
    if (function_exists('mb_substr')) {
        return mb_substr($str, 0, $length, 'UTF-8');
    }
    return substr($str, 0, $length);
};
if (!empty($initialParts)) {
    $first = $initialParts[0];
    $last = $initialParts[count($initialParts) - 1];
    if (count($initialParts) >= 2) {
        $initials = strtoupper($getSub($first, 1) . $getSub($last, 1));
    } else {
        $initials = strtoupper($getSub($first, 2));
    }
}
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
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
      --brand-red: #FF3B30;
      --brand-blue: #38BDF8;
      --brand-blueLight: #E0F2FE;
      --brand-dark: #172554;
      --brand-gray: #F8FAFC;
      --page-bg: #F8FAFC;
      --surface: #ffffff;
      --card-bg: #ffffff;
      --border-color: #172554;
      --text-primary: #172554;
      --text-muted: rgba(23, 37, 84, 0.7);
      --meta-bg: #E0F2FE;
      --badge-bg: #172554;
      --badge-text: #ffffff;
      --pattern-color: rgba(23, 37, 84, 0.08);
      --shadow-hard: 6px 6px 0px 0px var(--border-color);
      --shadow-hard-lg: 10px 10px 0px 0px var(--border-color);
    }
    [data-theme="dark"] {
      color-scheme: dark;
      --page-bg: #0b1120;
      --surface: #0f172a;
      --card-bg: #111827;
      --border-color: #334155;
      --text-primary: #e2e8f0;
      --text-muted: rgba(226, 232, 240, 0.7);
      --meta-bg: #0b1f3a;
      --badge-bg: #38BDF8;
      --badge-text: #0b1120;
      --pattern-color: rgba(148, 163, 184, 0.12);
      --shadow-hard: 6px 6px 0px 0px #0b1120;
      --shadow-hard-lg: 10px 10px 0px 0px #0b1120;
    }
    body {
      margin: 0;
      font-family: "Plus Jakarta Sans", "Segoe UI", Arial, sans-serif;
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
      gap: 12px;
      font-family: "Outfit", sans-serif;
      font-size: 20px;
      font-weight: 800;
      color: var(--text-primary);
    }
    .sidebar-header img {
      width: 36px;
      height: 36px;
      border-radius: 12px;
      border: 2px solid var(--border-color);
      background: #ffffff;
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
      border: 2px solid var(--border-color);
      border-radius: 999px;
      padding: 8px 12px;
      background: var(--card-bg);
      box-shadow: var(--shadow-hard);
    }
    [data-theme="dark"] .sidebar-search {
      box-shadow: none;
    }
    .sidebar-search input {
      border: none;
      background: transparent;
      width: 100%;
      font-size: 13px;
      color: var(--text-primary);
      outline: none;
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
      font-family: "Outfit", sans-serif;
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
      font-family: "Outfit", sans-serif;
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
      font-family: "Outfit", sans-serif;
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
      background: var(--surface);
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
      font-family: "Outfit", sans-serif;
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
    }
  </style>
</head>
<body>
  <div class="page-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="<?php echo APP_URL; ?>/assets/images/icon.png" alt="Apilageai logo">
        <span>Apilageai</span>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-title">Explore Users</div>
        <div class="sidebar-search" role="search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="search" id="exploreSearch" placeholder="Search users" aria-describedby="exploreStatus">
        </div>
        <div id="exploreStatus" class="search-status" role="status" aria-live="polite"></div>
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
                $avatarUrl = user_image_url($row['image'] ?? null);
                $avatarEscaped = htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8');
              ?>
              <a class="user-row" href="<?php echo $profileLinkEscaped; ?>" data-user-name="<?php echo $nameData; ?>">
                <img src="<?php echo $avatarEscaped; ?>" alt="<?php echo $nameEscaped; ?>">
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
                $avatarUrl = user_image_url($row['image'] ?? null);
                $avatarEscaped = htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8');
              ?>
              <a class="user-row" href="<?php echo $profileLinkEscaped; ?>">
                <img src="<?php echo $avatarEscaped; ?>" alt="<?php echo $nameEscaped; ?>">
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
      </div>
        </div>
      </div>
    </main>
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

    const exploreSearch = document.getElementById('exploreSearch');
    const exploreRows = Array.from(document.querySelectorAll('#exploreList .user-row'));
    const exploreStatus = document.getElementById('exploreStatus');

    function updateExploreStatus(visibleCount, query) {
      if (!exploreStatus) return;
      if (!query) {
        exploreStatus.textContent = `Showing ${visibleCount} profiles.`;
        return;
      }
      exploreStatus.textContent = `Found ${visibleCount} result${visibleCount === 1 ? '' : 's'} for "${query}".`;
    }

    if (exploreSearch && exploreRows.length) {
      updateExploreStatus(exploreRows.length, '');
      exploreSearch.addEventListener('input', (event) => {
        const query = event.target.value.trim().toLowerCase();
        let visibleCount = 0;
        exploreRows.forEach((row) => {
          const name = row.getAttribute('data-user-name') || '';
          const match = query === '' || name.includes(query);
          row.style.display = match ? '' : 'none';
          if (match) visibleCount += 1;
        });
        updateExploreStatus(visibleCount, query);
      });
    }
  </script>
</body>
</html>
