<?php
require_once __DIR__ . '/../backend/bootstrap.php';

$isLoggedIn = $user->_logged_in;
$userId = $isLoggedIn ? (int)$user->_data['id'] : 0;
$userName = $isLoggedIn ? trim(($user->_data['first_name'] ?? '') . ' ' . ($user->_data['last_name'] ?? '')) : '';
if ($userName === '') {
    $userName = 'ApilageAI User';
}
$userImage = $isLoggedIn ? user_image_url($user->_data['image'] ?? '') : (APP_URL . '/assets/images/user.png');
$userProfileUrl = $isLoggedIn ? (function () use ($user) {
    $slug = '';
    if (!empty($user->_data['public_profile_username'])) {
        $slug = $user->_data['public_profile_username'];
    } elseif (!empty($user->_data['public_profile_token'])) {
        $slug = $user->_data['public_profile_token'];
    }
    return $slug ? APP_URL . '/' . $slug : '';
})() : '';
$userHandle = $userProfileUrl !== '' ? '@' . basename($userProfileUrl) : ($isLoggedIn ? '@apilageai' : '@guest');

function explore_collapse_whitespace(string $text): string {
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim((string)$text);
}

function explore_truncate_text(string $text, int $maxLen): string {
    $text = trim($text);
    if ($text === '' || $maxLen <= 0) return '';
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $maxLen) return $text;
        $sliceLen = max(0, $maxLen - 3);
        $slice = mb_substr($text, 0, $sliceLen, 'UTF-8');
        return rtrim($slice) . '...';
    }
    if (strlen($text) <= $maxLen) return $text;
    $sliceLen = max(0, $maxLen - 3);
    $slice = substr($text, 0, $sliceLen);
    return rtrim($slice) . '...';
}

function explore_build_upload_url(string $value): string {
    return (string)(uploads_url_from_db($value, 'userimg') ?? '');
}

function explore_build_post_snippet(string $body, array $images): string {
    $body = explore_collapse_whitespace($body);
    if ($body !== '') {
        return explore_truncate_text($body, 140);
    }
    if (!empty($images)) {
        return 'Shared a photo on Explore.';
    }
    return 'Shared a post on Explore.';
}

$focusPostId = isset($_GET['post']) ? (int)$_GET['post'] : 0;
$focusPost = null;
if ($focusPostId > 0) {
    $stmt = $db->prepare(
        "SELECT p.id, p.body, p.created_at,
                u.id AS user_id, u.first_name, u.last_name, u.image, u.public_profile_username, u.public_profile_token
         FROM public_posts p
         JOIN users u ON u.id = p.user_id
         WHERE p.id = ? AND p.status = 'active'
         LIMIT 1"
    );
    if ($stmt) {
        $stmt->bind_param('i', $focusPostId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            $focusPost = [
                'id' => (int)$row['id'],
                'body' => $row['body'] ?? '',
                'created_at' => $row['created_at'],
                'user' => [
                    'id' => (int)$row['user_id'],
                    'name' => trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')) ?: 'ApilageAI User',
                    'image_url' => user_image_url($row['image'] ?? ''),
                    'profile_url' => ''
                ],
                'images' => []
            ];

            $slug = '';
            if (!empty($row['public_profile_username'])) {
                $slug = $row['public_profile_username'];
            } elseif (!empty($row['public_profile_token'])) {
                $slug = $row['public_profile_token'];
            }
            if ($slug !== '') {
                $focusPost['user']['profile_url'] = APP_URL . '/' . $slug;
            }

            $stmt = $db->prepare(
                "SELECT image_filename
                 FROM public_post_images
                 WHERE post_id = ?
                 ORDER BY id ASC"
            );
            if ($stmt) {
                $stmt->bind_param('i', $focusPostId);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($imgRow = $res->fetch_assoc()) {
                    $focusPost['images'][] = explore_build_upload_url((string)$imgRow['image_filename']);
                }
                $stmt->close();
            }
        }
    }
}

$title = 'Explore | ApilageAI';
$metaTitle = $title;
$metaDescription = 'Discover questions, answers, and discussions on ApilageAI Explore.';
$metaImage = APP_URL . '/assets/images/icon.png';
$canonicalUrl = APP_URL . '/explore';
$ogType = 'website';
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'ApilageAI Explore',
    'description' => $metaDescription,
    'url' => $canonicalUrl
];

if ($focusPost) {
    $snippet = explore_build_post_snippet($focusPost['body'], $focusPost['images']);
    $metaTitle = $snippet !== '' ? explore_truncate_text($snippet, 70) . ' | ApilageAI Explore' : 'Explore Post | ApilageAI';
    $metaDescription = $snippet !== '' ? $snippet : $metaDescription;
    if (!empty($focusPost['images'])) {
        $metaImage = $focusPost['images'][0];
    } else {
        $metaImage = APP_URL . '/og/explore.php?post=' . $focusPostId;
    }
    $canonicalUrl = APP_URL . '/explore?post=' . $focusPostId;
    $ogType = 'article';
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'SocialMediaPosting',
        'headline' => explore_truncate_text($snippet ?: 'ApilageAI Explore Post', 110),
        'datePublished' => date('c', strtotime($focusPost['created_at'] ?? 'now')),
        'author' => [
            '@type' => 'Person',
            'name' => $focusPost['user']['name'] ?? 'ApilageAI User'
        ],
        'image' => [$metaImage],
        'url' => $canonicalUrl
    ];
}

$metaTitleEsc = htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8');
$metaDescEsc = htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8');
$metaImageEsc = htmlspecialchars($metaImage, ENT_QUOTES, 'UTF-8');
$canonicalEsc = htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8');
$ogTypeEsc = htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8');
$structuredJson = json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $metaTitleEsc; ?></title>
  <meta name="description" content="<?php echo $metaDescEsc; ?>">
  <link rel="canonical" href="<?php echo $canonicalEsc; ?>">
  <meta property="og:site_name" content="ApilageAI">
  <meta property="og:title" content="<?php echo $metaTitleEsc; ?>">
  <meta property="og:description" content="<?php echo $metaDescEsc; ?>">
  <meta property="og:image" content="<?php echo $metaImageEsc; ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:url" content="<?php echo $canonicalEsc; ?>">
  <meta property="og:type" content="<?php echo $ogTypeEsc; ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo $metaTitleEsc; ?>">
  <meta name="twitter:description" content="<?php echo $metaDescEsc; ?>">
  <meta name="twitter:image" content="<?php echo $metaImageEsc; ?>">
  <?php if (!empty($structuredJson)) { ?>
  <script type="application/ld+json"><?php echo $structuredJson; ?></script>
  <?php } ?>
  <meta name="robots" content="index,follow">
  <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/images/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <script>
    window.MathJax = {
      tex: {
        inlineMath: [['$', '$'], ['\\(', '\\)']],
        displayMath: [['$$', '$$'], ['\\[', '\\]']],
        processEscapes: true
      },
      svg: { fontCache: 'global' }
    };
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>
  <script>
    (function () {
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      const savedTheme = localStorage.getItem('theme');
      const theme = savedTheme || (prefersDark ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
    })();
  </script>
  <style>
    :root {
      color-scheme: dark;
      --x-bg: #1a1a1a;
      --x-surface: #000000;
      --x-card: #16181c;
      --x-border: #2f3336;
      --x-text: #e7e9ea;
      --x-muted: #71767b;
      --x-accent: #ff0606;
      --x-accent-hover: #9c1111;
      --x-accent-soft: rgba(255, 6, 6, 0.15);
      --x-success: #00ba7c;
      --primary-red: #e53e3e;
      --primary-red-hover: #c53030;
      --text-primary: var(--x-text);
      --text-secondary: var(--x-muted);
      --border-color: var(--x-border);
      --sidebar-bg: #1a1a1a;
      --gray-light: rgba(231, 233, 234, 0.1);
      --sidebar-hover: rgba(231, 233, 234, 0.08);
      --card-bg: var(--x-card);
      --container-bg: var(--x-card);
      --mention-ai: #ff4d4d;
      --mention-1: #1d9bf0;
      --mention-2: #00ba7c;
      --mention-3: #f59e0b;
      --mention-4: #8b5cf6;
      --mention-5: #ec4899;
    }
    [data-theme="light"] {
      color-scheme: light;
      --x-bg: #ffffff;
      --x-surface: #ffffff;
      --x-card: #f7f9f9;
      --x-border: #e6e6e6;
      --x-text: #0f1419;
      --x-muted: #536471;
      --x-accent: #1d9bf0;
      --x-accent-hover: #1a8cd8;
      --x-accent-soft: rgba(29, 155, 240, 0.15);
      --x-success: #00ba7c;
      --primary-red: #e53e3e;
      --primary-red-hover: #c53030;
      --text-primary: var(--x-text);
      --text-secondary: var(--x-muted);
      --border-color: var(--x-border);
      --sidebar-bg: #f9fafb;
      --gray-light: rgba(15, 20, 25, 0.06);
      --sidebar-hover: rgba(15, 20, 25, 0.06);
      --card-bg: var(--x-card);
      --container-bg: var(--x-card);
      --mention-ai: #e53e3e;
      --mention-1: #1d9bf0;
      --mention-2: #16a34a;
      --mention-3: #d97706;
      --mention-4: #7c3aed;
      --mention-5: #db2777;
    }
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: var(--x-bg);
      color: var(--x-text);
      overflow: hidden;
    }
    body.modal-open {
      overflow: hidden;
    }
    a {
      color: inherit;
      text-decoration: none;
    }

    .page-layout.x-layout {
      display: grid;
      grid-template-columns: var(--sidebar-width) minmax(0, 1fr) 340px;
      width: 100%;
      max-width: 100%;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      height: 100vh;
      height: 100dvh;
      overflow: hidden;
      align-items: stretch;
      --sidebar-width: 260px;
    }
    body.sidebar-collapsed .page-layout.x-layout {
      --sidebar-width: 70px;
    }

    .sidebar.app-sidebar {
      background: linear-gradient(180deg, var(--sidebar-bg) 0%, rgba(26, 26, 26, 0.98) 100%);
      border-right: 1px solid rgba(255, 255, 255, 0.06);
      box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
      padding: 0;
      gap: 0;
      width: var(--sidebar-width);
      min-width: var(--sidebar-width);
      position: sticky;
      top: 0;
      left: 0;
      height: 100vh;
      height: 100dvh;
      display: flex;
      flex-direction: column;
      flex: 0 0 var(--sidebar-width);
      overflow: hidden;
      z-index: 200;
    }
    [data-theme="light"] .sidebar.app-sidebar {
      background: linear-gradient(180deg, var(--sidebar-bg) 0%, rgba(249, 250, 251, 0.98) 100%);
      border-right: 1px solid rgba(0, 0, 0, 0.06);
      box-shadow: 4px 0 24px rgba(0, 0, 0, 0.03);
    }
    .sidebar.app-sidebar .sidebar-header {
      padding: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      min-height: 56px;
    }
    .sidebar.app-sidebar .sidebar-logo {
      width: 44px;
      height: 44px;
      object-fit: contain;
      border-radius: 12px;
      background: none;
    }
    .sidebar.app-sidebar .sidebar-backn {
      position: absolute;
      right: 8px;
      color: var(--text-primary);
      background: transparent;
      border: none;
      font-size: 1.1rem;
      cursor: pointer;
      width: 40px;
      height: 40px;
      display: none;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
    }
    .sidebar.app-sidebar .sidebar-backn:hover {
      background: var(--sidebar-hover);
    }
    .sidebar.app-sidebar .sidebar-items {
      padding: 16px 12px;
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex: 1;
      overflow-y: auto;
      overflow-x: hidden;
    }
    .sidebar.app-sidebar .sidebar-but {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      border: none;
      border-radius: 8px;
      background: transparent;
      color: var(--text-secondary);
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.15s ease;
      text-decoration: none;
    }
    .sidebar.app-sidebar .sidebar-but:hover {
      background: var(--gray-light);
      color: var(--text-primary);
    }
    .sidebar.app-sidebar .sidebar-but.active {
      background: rgba(239, 68, 68, 0.15);
      color: var(--primary-red);
      font-weight: 600;
    }
    .sidebar.app-sidebar .sidebar-but .sidebar-but-icon {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      color: inherit;
    }
    .sidebar.app-sidebar .sidebar-but .sidebar-but-icon img {
      width: 32px;
      height: 32px;
      display: block;
    }
    .sidebar.app-sidebar .sidebar-but.new-chat-btn {
      background: var(--primary-red);
      color: #ffffff;
      border: none;
      margin-bottom: 4px;
    }
    .sidebar.app-sidebar .sidebar-but.new-chat-btn:hover {
      background: var(--primary-red-hover);
    }
    .sidebar.app-sidebar .sidebar-footer {
      padding: 16px;
      padding-bottom: calc(16px + env(safe-area-inset-bottom));
      margin-top: auto;
      background: var(--sidebar-bg);
      display: flex;
      flex-direction: column;
      gap: 12px;
      border-top: 1px solid var(--border-color);
    }
    .sidebar.app-sidebar .sidebar-minimize-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      background: transparent;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      color: var(--text-secondary);
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
    }
    .sidebar.app-sidebar .sidebar-minimize-btn:hover {
      background: var(--sidebar-hover);
      color: var(--primary-red);
      border-color: var(--primary-red);
    }
    .sidebar.app-sidebar .sidebar-footer-userinfo {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 16px;
      cursor: pointer;
      background-color: rgba(255, 255, 255, 0.03);
      border-radius: 18px;
    }
    [data-theme="light"] .sidebar.app-sidebar .sidebar-footer-userinfo {
      background-color: #f9fafb;
    }
    .sidebar.app-sidebar .sidebar-footer-userinfo:hover {
      background: var(--gray-light);
    }
    .sidebar.app-sidebar .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      overflow: hidden;
      flex-shrink: 0;
      border: 1px solid var(--border-color);
      background: var(--container-bg);
    }
    .sidebar.app-sidebar .user-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .sidebar.app-sidebar .user-details {
      flex: 1;
      min-width: 0;
    }
    .sidebar.app-sidebar .user-name {
      font-weight: 600;
      font-size: 14px;
      color: var(--text-primary);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .sidebar.app-sidebar .user-credit-text {
      font-size: 12px;
      color: var(--text-secondary);
      font-weight: 400;
      margin-top: 2px;
    }
    body.sidebar-collapsed .sidebar.app-sidebar .sidebar-but-text,
    body.sidebar-collapsed .sidebar.app-sidebar .minimize-text,
    body.sidebar-collapsed .sidebar.app-sidebar .user-details {
      display: none;
    }
    body.sidebar-collapsed .sidebar.app-sidebar .sidebar-but {
      justify-content: center;
      padding: 10px;
    }
    body.sidebar-collapsed .sidebar.app-sidebar .sidebar-footer-userinfo {
      justify-content: center;
      padding: 12px;
    }
    body.sidebar-collapsed .sidebar.app-sidebar .sidebar-minimize-btn {
      justify-content: center;
      padding: 8px;
    }
    body.sidebar-collapsed .sidebar.app-sidebar .sidebar-items {
      padding: 16px 8px;
    }

    .explore-main {
      flex: 1;
      max-width: none;
      border-left: 1px solid var(--x-border);
      border-right: 1px solid var(--x-border);
      background: var(--x-bg);
      height: 100vh;
      height: 100dvh;
      overflow-y: auto;
      min-width: 0;
    }
    .profile-topbar {
      position: sticky;
      top: 0;
      z-index: 20;
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      background: var(--x-bg);
      border-bottom: 1px solid var(--x-border);
    }
    .topbar-btn {
      width: 36px;
      height: 36px;
      border-radius: 999px;
      border: none;
      background: transparent;
      color: var(--x-text);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    .topbar-btn:hover {
      background: rgba(231, 233, 234, 0.1);
    }
    [data-theme="light"] .topbar-btn:hover {
      background: rgba(15, 20, 25, 0.06);
    }
    .topbar-title {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    .topbar-name {
      font-size: 18px;
      font-weight: 700;
    }
    .topbar-meta {
      font-size: 12px;
      color: var(--x-muted);
      font-weight: 600;
    }

    .x-feed {
      display: flex;
      flex-direction: column;
    }
    .x-post-card {
      padding: 16px;
      border-bottom: 1px solid var(--x-border);
      background: var(--x-bg);
      position: relative;
    }
    .x-post-head {
      display: flex;
      gap: 12px;
    }
    .x-avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      object-fit: cover;
      border: 1px solid var(--x-border);
      background: var(--x-card);
      flex-shrink: 0;
    }
    .x-avatar-link {
      display: inline-flex;
      border-radius: 50%;
      overflow: hidden;
      flex-shrink: 0;
      line-height: 0;
    }
    .x-post-meta {
      flex: 1;
      min-width: 0;
    }
    .x-post-title {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      align-items: center;
      font-size: 14px;
      padding-right: 42px;
    }
    .x-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 2px 10px;
      border-radius: 999px;
      background: var(--x-card);
      border: 1px solid var(--x-border);
      color: var(--x-muted);
      font-size: 11px;
      font-weight: 700;
    }
    .x-name {
      font-weight: 700;
      color: var(--x-text);
    }
    .x-handle,
    .x-time {
      color: var(--x-muted);
      font-size: 13px;
    }
    .x-post-body {
      margin-top: 6px;
      font-size: 15px;
      line-height: 1.5;
      color: var(--x-text);
    }
    .mention {
      font-weight: 600;
    }
    .mention-ai {
      color: var(--mention-ai);
    }
    .mention-color-1 {
      color: var(--mention-1);
    }
    .mention-color-2 {
      color: var(--mention-2);
    }
    .mention-color-3 {
      color: var(--mention-3);
    }
    .mention-color-4 {
      color: var(--mention-4);
    }
    .mention-color-5 {
      color: var(--mention-5);
    }
    .mention-wrap {
      position: relative;
    }
    .mention-suggest {
      position: absolute;
      left: 0;
      right: 0;
      top: calc(100% + 6px);
      background: var(--x-card);
      border: 1px solid var(--x-border);
      border-radius: 14px;
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
      max-height: 240px;
      overflow-y: auto;
      z-index: 50;
      display: none;
    }
    .mention-suggest.active {
      display: block;
    }
    .mention-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      font-size: 13px;
      cursor: pointer;
      color: var(--x-text);
    }
    .mention-item:hover,
    .mention-item.active {
      background: var(--x-accent-soft);
    }
    .mention-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--mention-color);
      flex-shrink: 0;
    }
    .mention-label {
      font-weight: 600;
      color: var(--mention-color);
    }
    .mention-handle {
      color: var(--x-muted);
      font-size: 12px;
    }
    .x-post-media {
      margin-top: 10px;
      display: grid;
      gap: 8px;
    }
    .x-post-media.is-single {
      grid-template-columns: minmax(0, 1fr);
      grid-auto-rows: minmax(180px, 260px);
    }
    .x-post-media.is-multi {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      grid-auto-rows: 140px;
    }
    .x-post-media img {
      width: 100%;
      height: 100%;
      border-radius: 16px;
      border: 1px solid var(--x-border);
      object-fit: cover;
      aspect-ratio: 1 / 1;
      cursor: pointer;
    }
    .x-post-actions {
      margin-top: 12px;
      display: flex;
      gap: 18px;
      align-items: center;
      color: var(--x-muted);
      font-size: 13px;
    }
    .x-action {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: transparent;
      border: none;
      color: inherit;
      cursor: pointer;
      padding: 6px 10px;
      border-radius: 999px;
    }
    .x-action:hover {
      color: var(--x-accent);
      background: var(--x-accent-soft);
    }
    .item-menu {
      position: absolute;
      top: 12px;
      right: 12px;
      z-index: 5;
    }
    .item-menu-btn {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      border: 1px solid var(--x-border);
      background: var(--x-card);
      color: var(--x-muted);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    .item-menu-btn:hover {
      color: var(--x-accent);
      border-color: var(--x-accent);
      background: var(--x-accent-soft);
    }
    .item-menu-list {
      position: absolute;
      right: 0;
      top: calc(100% + 6px);
      background: var(--x-card);
      border: 1px solid var(--x-border);
      border-radius: 12px;
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
      padding: 6px;
      min-width: 160px;
      display: none;
    }
    .item-menu.open .item-menu-list {
      display: block;
    }
    .item-menu-item {
      width: 100%;
      border: none;
      background: transparent;
      color: var(--x-text);
      font-size: 13px;
      padding: 8px 10px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      text-align: left;
    }
    .item-menu-item:hover {
      background: var(--x-accent-soft);
      color: var(--x-accent);
    }
    .item-menu-item.delete {
      color: #ef4444;
    }
    .item-menu-item.delete:hover {
      background: rgba(239, 68, 68, 0.12);
    }

    .x-composer {
      display: flex;
      gap: 12px;
      padding: 16px;
      border-bottom: 1px solid var(--x-border);
      background: var(--x-bg);
    }
    .x-composer textarea {
      width: 100%;
      min-height: 110px;
      border: none;
      background: transparent;
      resize: vertical;
      color: var(--x-text);
      font-size: 16px;
      outline: none;
    }
    .x-composer-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-top: 12px;
      border-top: 1px solid var(--x-border);
      padding-top: 10px;
    }
    .x-composer-icons {
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--x-accent);
      font-size: 16px;
    }
    .x-composer-icons label {
      cursor: pointer;
    }
    .x-composer-icons input {
      display: none;
    }
    .upload-preview {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 10px;
    }
    .upload-preview.is-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, 72px);
      grid-auto-rows: 72px;
      justify-content: flex-start;
    }
    .upload-item {
      position: relative;
      width: 72px;
      height: 72px;
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid var(--x-border);
      background: var(--x-card);
    }
    .upload-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .upload-loader {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.35);
      opacity: 1;
      transition: opacity 0.2s ease;
    }
    .upload-item.is-preview-loaded .upload-loader {
      opacity: 0;
      pointer-events: none;
    }
    .upload-progress {
      position: absolute;
      inset: 8px;
      border-radius: 50%;
      border: 2px solid rgba(255, 255, 255, 0.15);
      border-top-color: var(--x-accent);
      animation: spin 0.9s linear infinite;
      display: none;
    }
    .upload-item.is-uploading .upload-progress {
      display: block;
    }
    .upload-size {
      position: absolute;
      bottom: 4px;
      right: 6px;
      padding: 2px 6px;
      border-radius: 999px;
      background: rgba(0, 0, 0, 0.6);
      color: #fff;
      font-size: 10px;
    }
    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }
    .x-post-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--x-accent);
      color: #ffffff;
      text-decoration: none;
      border-radius: 999px;
      padding: 8px 18px;
      font-weight: 700;
      border: none;
      cursor: pointer;
    }
    .x-post-btn:hover {
      background: var(--x-accent-hover);
    }
    .x-post-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    .x-post-btn.is-loading {
      gap: 8px;
    }
    .thread-form .form-actions button.is-loading {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .thread-form .form-actions button.is-loading .loader-spin,
    .x-post-btn.is-loading .loader-spin {
      width: 14px;
      height: 14px;
      border-width: 2px;
    }
    .x-helper {
      font-size: 12px;
      color: var(--x-muted);
    }
    .feed-loader {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 16px;
      color: var(--x-muted);
      font-size: 13px;
    }
    .loader-spin {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      border: 2px solid rgba(255, 255, 255, 0.2);
      border-top-color: var(--x-accent);
      animation: spin 0.9s linear infinite;
    }
    [data-theme="light"] .loader-spin {
      border-color: rgba(15, 20, 25, 0.15);
    }

    .x-thread {
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px solid var(--x-border);
    }
    .thread-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }
    .thread-item {
      display: flex;
      gap: 12px;
      position: relative;
    }
    .thread-item.has-replies .thread-avatar::after {
      content: '';
      position: absolute;
      left: 50%;
      top: 46px;
      width: 2px;
      height: calc(100% - 46px);
      background: var(--x-border);
      transform: translateX(-50%);
    }
    .thread-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      overflow: hidden;
      border: 1px solid var(--x-border);
      background: var(--x-card);
      flex-shrink: 0;
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .thread-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .thread-content {
      flex: 1;
      min-width: 0;
      position: relative;
    }
    .thread-header {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      align-items: center;
      font-size: 13px;
      padding-right: 42px;
    }
    .thread-name {
      font-weight: 700;
    }
    .thread-handle,
    .thread-time {
      color: var(--x-muted);
      font-size: 12px;
    }
    .thread-body {
      margin-top: 4px;
      font-size: 14px;
      line-height: 1.5;
      color: var(--x-text);
    }
    .x-post-body p,
    .thread-body p {
      margin: 0 0 0.6em;
    }
    .x-post-body p:last-child,
    .thread-body p:last-child {
      margin-bottom: 0;
    }
    .x-post-body ul,
    .x-post-body ol,
    .thread-body ul,
    .thread-body ol {
      margin: 0.4em 0 0.4em 1.2em;
      padding: 0;
    }
    .thread-media {
      margin-top: 8px;
      display: grid;
      gap: 6px;
    }
    .thread-media.is-single {
      grid-template-columns: minmax(0, 1fr);
      grid-auto-rows: minmax(140px, 200px);
    }
    .thread-media.is-multi {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      grid-auto-rows: 120px;
    }
    .thread-media img {
      width: 100%;
      height: 100%;
      border-radius: 12px;
      border: 1px solid var(--x-border);
      object-fit: cover;
      aspect-ratio: 1 / 1;
      cursor: pointer;
    }
    .thread-actions {
      display: flex;
      gap: 12px;
      align-items: center;
      margin-top: 6px;
      font-size: 12px;
      color: var(--x-muted);
    }
    .thread-actions button {
      background: none;
      border: none;
      color: inherit;
      cursor: pointer;
      padding: 4px 0;
      font-weight: 600;
    }
    .thread-actions button:hover {
      color: var(--x-accent);
    }
    .thread-replies {
      margin-top: 8px;
      margin-left: 12px;
      padding-left: 16px;
      border-left: 2px solid var(--x-border);
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .thread-item.is-ai .thread-content {
      background: rgba(239, 68, 68, 0.08);
      border-radius: 12px;
      padding: 10px 12px;
    }

    .thread-form {
      margin-top: 14px;
      padding-top: 12px;
      border-top: 1px dashed var(--x-border);
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .thread-form textarea {
      width: 100%;
      min-height: 70px;
      border: 1px solid var(--x-border);
      border-radius: 12px;
      padding: 10px;
      background: var(--x-card);
      color: var(--x-text);
      font-size: 13px;
      resize: vertical;
    }
    .thread-form .form-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .thread-upload {
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--x-accent);
    }
    .thread-file {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid var(--x-border);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: inherit;
    }
    .thread-file:hover {
      background: var(--x-accent-soft);
    }
    .thread-file input {
      display: none;
    }
    .thread-form .form-actions button {
      padding: 6px 12px;
      border-radius: 999px;
      border: none;
      background: var(--x-accent);
      color: #fff;
      font-weight: 600;
      cursor: pointer;
    }
    .thread-form .form-actions button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    .replying-indicator {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--x-muted);
      font-size: 12px;
    }
    .replying-indicator button {
      background: none;
      border: none;
      color: var(--x-accent);
      cursor: pointer;
      font-weight: 600;
      padding: 0;
    }

    .right-rail {
      height: 100vh;
      height: 100dvh;
      overflow-y: auto;
      flex: 0 0 340px;
      padding: 12px 16px;
      border-left: 1px solid var(--x-border);
      background: var(--x-bg);
    }
    .x-search {
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--x-card);
      border: 1px solid var(--x-border);
      border-radius: 999px;
      padding: 10px 14px;
      color: var(--x-muted);
      margin-bottom: 16px;
    }
    .x-search input {
      background: transparent;
      border: none;
      color: var(--x-text);
      width: 100%;
      outline: none;
      font-size: 14px;
    }
    .x-card {
      background: var(--x-card);
      border: 1px solid var(--x-border);
      border-radius: 16px;
      padding: 14px;
      margin-bottom: 16px;
    }
    .x-card-title {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 12px;
    }
    .x-card-item {
      display: flex;
      flex-direction: column;
      gap: 4px;
      padding: 8px 0;
      border-bottom: 1px solid var(--x-border);
    }
    .x-card-item:last-child {
      border-bottom: none;
    }
    .x-card-name {
      font-size: 14px;
      font-weight: 600;
    }
    .x-card-sub {
      font-size: 12px;
      color: var(--x-muted);
    }
    .x-card-images {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      grid-auto-rows: 120px;
      gap: 8px;
    }
    .x-card-images img {
      width: 100%;
      height: 100%;
      border-radius: 12px;
      border: 1px solid var(--x-border);
      object-fit: cover;
      aspect-ratio: 1 / 1;
      cursor: pointer;
    }

    .load-more {
      padding: 12px 16px;
      border: none;
      width: 100%;
      background: transparent;
      color: var(--x-accent);
      font-weight: 700;
      cursor: pointer;
      border-top: 1px solid var(--x-border);
    }
    .load-more:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    .empty-state {
      padding: 20px;
      text-align: center;
      color: var(--x-muted);
      border-bottom: 1px solid var(--x-border);
    }
    .status-text {
      font-size: 12px;
      color: var(--x-muted);
      margin-top: 8px;
    }

    .image-modal {
      position: fixed;
      inset: 0;
      z-index: 9999;
      display: none;
      padding: 24px;
    }
    .image-modal.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .image-modal-backdrop {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.85);
    }
    .image-modal-content {
      position: relative;
      z-index: 1;
      max-width: 90vw;
      max-height: 90vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .image-modal-content img {
      max-width: 90vw;
      max-height: 90vh;
      border-radius: 18px;
      border: 1px solid var(--x-border);
      object-fit: contain;
      background: var(--x-card);
    }
    .image-modal-loader {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.35);
      border-radius: 18px;
      z-index: 2;
      opacity: 0;
      transition: opacity 0.2s ease;
      pointer-events: none;
    }
    .image-modal.is-loading .image-modal-loader {
      opacity: 1;
    }
    .image-modal-close {
      position: absolute;
      top: -18px;
      right: -18px;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: none;
      background: var(--x-card);
      color: var(--x-text);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      z-index: 3;
    }

    .share-modal {
      position: fixed;
      inset: 0;
      z-index: 9998;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .share-modal.active {
      display: flex;
    }
    .share-modal-backdrop {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.75);
    }
    .share-modal-card {
      position: relative;
      z-index: 1;
      width: min(420px, 92vw);
      background: var(--x-card);
      border: 1px solid var(--x-border);
      border-radius: 18px;
      padding: 18px;
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.2);
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .share-modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .share-modal-title {
      font-size: 16px;
      font-weight: 700;
    }
    .share-modal-close {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid var(--x-border);
      background: var(--x-card);
      color: var(--x-text);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .share-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .share-btn {
      flex: 1 1 120px;
      border-radius: 12px;
      border: 1px solid var(--x-border);
      background: var(--x-bg);
      color: var(--x-text);
      padding: 10px 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      font-weight: 600;
    }
    .share-btn:hover {
      background: var(--x-accent-soft);
      color: var(--x-accent);
    }
    .share-link-row {
      display: flex;
      gap: 8px;
      align-items: center;
    }
    .share-link-row input {
      flex: 1;
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid var(--x-border);
      background: var(--x-bg);
      color: var(--x-text);
      font-size: 13px;
    }
    .share-link-row button {
      border-radius: 12px;
      border: none;
      background: var(--x-accent);
      color: #fff;
      padding: 10px 14px;
      font-weight: 600;
      cursor: pointer;
    }
    .share-link-row button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    .share-status {
      font-size: 12px;
      color: var(--x-muted);
      min-height: 16px;
    }
    .share-status[data-type="error"] {
      color: #ef4444;
    }
    .share-status[data-type="success"] {
      color: var(--x-success);
    }

    @media (max-width: 640px) {
      .x-post-media.is-single,
      .thread-media.is-single {
        grid-template-columns: minmax(0, 1fr);
      }
      .x-post-media.is-multi,
      .thread-media.is-multi {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 1200px) {
      .page-layout.x-layout {
        grid-template-columns: var(--sidebar-width) minmax(0, 1fr);
      }
      .right-rail {
        display: none;
      }
    }
    @media (max-width: 900px) {
      body {
        overflow: auto;
      }
      .page-layout.x-layout {
        display: block;
        grid-template-columns: none;
        height: auto;
        min-height: 100vh;
      }
      body.sidebar-collapsed .page-layout.x-layout {
        --sidebar-width: 0px;
      }
      .explore-main {
        height: auto;
        min-height: 100vh;
        overflow: visible;
        border-left: none;
        border-right: none;
      }
      .sidebar.app-sidebar {
        position: fixed;
        transform: translateX(-100%);
        transition: transform 0.2s ease;
      }
      body.sidebar-open .sidebar.app-sidebar {
        transform: translateX(0);
      }
      .sidebar.app-sidebar .sidebar-backn {
        display: inline-flex;
      }
    }
    .focus-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      background: var(--x-card);
      border: 1px solid var(--x-border);
      border-radius: 16px;
      padding: 12px 16px;
      margin-bottom: 18px;
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.12);
    }
    .focus-bar .focus-title {
      font-weight: 600;
      font-size: 0.98rem;
    }
    .focus-bar .focus-actions {
      display: flex;
      gap: 8px;
      align-items: center;
    }
    .focus-bar .focus-btn {
      border: 1px solid var(--x-border);
      background: transparent;
      color: var(--x-text);
      border-radius: 999px;
      padding: 6px 12px;
      font-size: 0.85rem;
      cursor: pointer;
    }
    .focus-bar .focus-btn.primary {
      background: var(--x-accent);
      border-color: var(--x-accent);
      color: #fff;
    }
    body.is-focus-mode .load-more,
    body.is-focus-mode #feedEnd {
      display: none !important;
    }
    body.is-focus-mode .x-composer {
      display: none;
    }
  </style>
</head>
<body>
  <div class="page-layout x-layout">
    <aside class="sidebar app-sidebar" id="sidebar" aria-label="Main sidebar">
      <div class="sidebar-header">
        <img class="sidebar-logo" src="<?php echo APP_URL; ?>/assets/images/icon.png" alt="Apilageai logo">
        <button id="sidebarback" class="sidebar-backn" type="button" aria-label="Toggle sidebar">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </button>
      </div>

      <div class="sidebar-items">
        <a class="sidebar-but new-chat-btn" href="<?php echo APP_URL; ?>/app" title="New chat">
          <span class="sidebar-but-icon" aria-hidden="true">
            <picture>
              <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/270f_fe0f/512.webp" type="image/webp">
              <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/270f_fe0f/512.gif" alt="✏" width="32" height="32">
            </picture>
          </span>
          <span class="sidebar-but-text">New chat</span>
        </a>

        <a class="sidebar-but active" href="<?php echo APP_URL; ?>/explore" title="Explore" aria-current="page">
          <span class="sidebar-but-icon" aria-hidden="true">
            <picture>
              <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f30e/512.webp" type="image/webp">
              <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f30e/512.gif" alt="🌎" width="32" height="32">
            </picture>
          </span>
          <span class="sidebar-but-text">Explore</span>
        </a>
      </div>

      <div class="sidebar-footer">
        <button id="sidebarMinimize" class="sidebar-minimize-btn" type="button" aria-label="Minimize sidebar">
          <i class="fa fa-bullseye" aria-hidden="true"></i>
          <span class="minimize-text">Focused</span>
        </button>
        <a class="sidebar-footer-userinfo" id="sidebarUserInfo" href="<?php echo APP_URL; ?>/app?open=preferences">
          <div class="user-avatar">
            <img src="<?php echo htmlspecialchars($userImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?> Avatar">
          </div>
          <div class="user-details">
            <div class="user-name"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
        </a>
      </div>
    </aside>

    <main class="explore-main">
      <div class="profile-topbar">
        <button id="toggleSidebar" class="topbar-btn" type="button" aria-label="Toggle sidebar">
          <i class="fa fa-bars" aria-hidden="true"></i>
        </button>
      <div class="topbar-title">
        <div class="topbar-name">Explore</div>
        <div class="topbar-meta">Public chats and questions in one stream</div>
      </div>
      </div>

      <?php if ($isLoggedIn): ?>
        <div class="x-composer">
          <img class="x-avatar" src="<?php echo htmlspecialchars($userImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>">
          <div style="flex: 1; min-width: 0;">
            <div class="mention-wrap">
              <textarea id="postBody" name="post_body" placeholder="Ask a public question..." autocomplete="off" aria-label="Ask a public question"></textarea>
              <div class="mention-suggest" id="postMentionSuggest"></div>
            </div>
            <div class="upload-preview" id="postImagePreview"></div>
            <div class="x-composer-actions">
              <div>
                <div class="x-composer-icons">
                  <label title="Add images">
                    <input type="file" id="postImages" name="post_images[]" accept="image/png,image/jpeg,image/gif" multiple aria-label="Add images">
                    <i class="fa-regular fa-image"></i>
                  </label>
                  <span class="x-helper">PNG, GIF, JPG. Up to 4 images, 60MB each.</span>
                </div>
                <div class="status-text" id="postStatus"></div>
              </div>
              <button type="button" id="postSubmit" class="x-post-btn">Post</button>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="empty-state">Log in to post questions and reply to others.</div>
      <?php endif; ?>

      <section>
        <div class="x-feed" id="feedList"></div>
        <div id="feedEmpty" class="empty-state" hidden>No published items yet.</div>
        <div id="feedLoader" class="feed-loader" hidden>
          <span class="loader-spin" aria-hidden="true"></span>
          Loading...
        </div>
        <div id="feedEnd" class="empty-state" hidden>You're all caught up.</div>
        <button class="load-more" id="loadMoreFeed" type="button" hidden>Load more</button>
      </section>
    </main>

    <aside class="right-rail">
      <div class="x-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="exploreSearch" name="explore_search" placeholder="Search explore" autocomplete="off" aria-label="Search explore">
      </div>
      <div class="x-card">
        <div class="x-card-title">What's happening</div>
        <div id="trendList"></div>
      </div>
      <div class="x-card">
        <div class="x-card-title">Top questions</div>
        <div id="questionList"></div>
      </div>
    </aside>
  </div>

  <div class="image-modal" id="imageModal">
    <div class="image-modal-backdrop"></div>
    <div class="image-modal-content">
      <button class="image-modal-close" type="button" aria-label="Close">
        <i class="fa fa-times"></i>
      </button>
      <div class="image-modal-loader" id="imageModalLoader">
        <span class="loader-spin" aria-hidden="true"></span>
      </div>
      <img id="imageModalImg" src="" alt="Preview">
    </div>
  </div>

  <div class="share-modal" id="shareModal" aria-hidden="true">
    <div class="share-modal-backdrop"></div>
    <div class="share-modal-card" role="dialog" aria-modal="true" aria-label="Share post">
      <div class="share-modal-header">
        <div class="share-modal-title">Share</div>
        <button class="share-modal-close" type="button" data-share-close aria-label="Close share">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="share-actions">
        <button class="share-btn" type="button" data-share-whatsapp>
          <i class="fa-brands fa-whatsapp"></i>
          WhatsApp
        </button>
        <button class="share-btn" type="button" data-share-twitter>
          <i class="fa-brands fa-x-twitter"></i>
          Twitter
        </button>
      </div>
      <div class="share-link-row">
        <input type="text" id="shareLinkInput" name="share_link" readonly autocomplete="off" aria-label="Share link">
        <button type="button" id="shareCopyBtn">Copy link</button>
      </div>
      <div class="share-status" id="shareStatus" role="status"></div>
    </div>
  </div>

  <script>
    window.APP_BASE_URL = "<?php echo APP_URL; ?>";
    window.UPLOADS_BASE_URL = "<?php echo UPLOADS_BASE_URL; ?>";
    window.IS_GUEST = <?php echo $isLoggedIn ? 'false' : 'true'; ?>;
    window.userData = <?php echo $isLoggedIn ? json_encode([
        'id' => $userId,
        'name' => $userName,
        'image_url' => $userImage,
        'profile_url' => $userProfileUrl
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : 'null'; ?>;
  </script>
  <script>
    (function () {
      const sidebarMinimize = document.getElementById('sidebarMinimize');
      const sidebarBack = document.getElementById('sidebarback');
      const sidebarToggleBtn = document.getElementById('toggleSidebar');

      function toggleSidebarHidden() {
        if (window.innerWidth <= 900) {
          document.body.classList.remove('sidebar-open');
          return;
        }
        document.body.classList.toggle('sidebar-collapsed');
      }

      if (sidebarMinimize) sidebarMinimize.addEventListener('click', toggleSidebarHidden);
      if (sidebarBack) sidebarBack.addEventListener('click', () => {
        document.body.classList.remove('sidebar-open');
      });
      if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', () => {
          if (window.innerWidth <= 900) {
            document.body.classList.toggle('sidebar-open');
          } else {
            toggleSidebarHidden();
          }
        });
      }

      const apiBase = `${window.APP_BASE_URL}/public_explore_api.php`;

      const feedList = document.getElementById('feedList');
      const feedEmpty = document.getElementById('feedEmpty');
      const feedLoader = document.getElementById('feedLoader');
      const feedEnd = document.getElementById('feedEnd');
      const loadMoreFeed = document.getElementById('loadMoreFeed');
      const trendList = document.getElementById('trendList');
      const questionList = document.getElementById('questionList');

      const postBody = document.getElementById('postBody');
      const postImages = document.getElementById('postImages');
      const postSubmit = document.getElementById('postSubmit');
      const postStatus = document.getElementById('postStatus');
      const exploreSearch = document.getElementById('exploreSearch');
      const postImagePreview = document.getElementById('postImagePreview');

      const imageModal = document.getElementById('imageModal');
      const imageModalImg = document.getElementById('imageModalImg');
      const imageModalLoader = document.getElementById('imageModalLoader');
      const shareModal = document.getElementById('shareModal');
      const shareLinkInput = document.getElementById('shareLinkInput');
      const shareCopyBtn = document.getElementById('shareCopyBtn');
      const shareStatus = document.getElementById('shareStatus');

      let chatPage = 1;
      let postPage = 1;
      let feedLoading = false;
      let hasMoreChats = true;
      let hasMorePosts = true;
      const cachedChats = [];
      const cachedPosts = [];
      const seenChats = new Set();
      const seenPosts = new Set();
      const mentionPalette = ['mention-color-1', 'mention-color-2', 'mention-color-3', 'mention-color-4', 'mention-color-5'];
      const mentionIndex = new Map();
      const currentUserId = window.userData ? Number(window.userData.id) : 0;
      const notificationEndpoint = `${window.APP_BASE_URL}/notific.php?action=get`;
      const notificationStorageKey = currentUserId ? `explore_last_notif_${currentUserId}` : null;
      let notificationInitialized = false;
      let lastNotificationId = 0;
      let focusMode = false;
      let focusPostId = 0;
      let focusBar = null;
      if (notificationStorageKey) {
        try {
          lastNotificationId = Number(localStorage.getItem(notificationStorageKey) || 0);
        } catch (err) {
          lastNotificationId = 0;
        }
      }
      const shuffleEnabled = true;
      const shuffleSeed = (() => {
        try {
          if (window.crypto && window.crypto.getRandomValues) {
            const buf = new Uint32Array(2);
            window.crypto.getRandomValues(buf);
            return `${buf[0]}-${buf[1]}`;
          }
          return String(Date.now() + Math.random());
        } catch (err) {
          return String(Date.now() + Math.random());
        }
      })();
      const SHUFFLE_JITTER_MS = 6 * 60 * 60 * 1000;
      const MAX_FEED_ITEMS = 120;
      const MAX_WORDS = 1000;
      const MAX_FILE_BYTES = 60 * 1024 * 1024;
      const ALLOWED_TYPES = new Set(['image/png', 'image/jpeg', 'image/jpg', 'image/gif']);

      function storeLastNotificationId(id) {
        if (!notificationStorageKey) return;
        lastNotificationId = id;
        try {
          localStorage.setItem(notificationStorageKey, String(id));
        } catch (err) {
          // ignore storage errors
        }
      }

      function maybeRequestNotificationPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'default') {
          Notification.requestPermission().catch(() => {});
        }
      }

      function showBrowserNotification(item) {
        if (!('Notification' in window)) return;
        if (Notification.permission !== 'granted') return;
        const body = item && item.message ? item.message : 'You have a new notification.';
        const notif = new Notification('ApilageAI', {
          body,
          tag: `explore-notif-${item.id || Date.now()}`
        });
        notif.onclick = () => {
          try {
            window.focus();
          } catch (err) {
            // ignore
          }
          window.location.href = `${window.APP_BASE_URL}/explore`;
        };
      }

      async function pollNotifications() {
        if (!currentUserId) return;
        try {
          const res = await fetch(notificationEndpoint, { cache: 'no-cache' });
          if (!res.ok) return;
          const items = await res.json();
          if (!Array.isArray(items) || items.length === 0) {
            notificationInitialized = true;
            return;
          }
          const maxId = items.reduce((acc, item) => {
            const id = Number(item && item.id ? item.id : 0);
            return id > acc ? id : acc;
          }, lastNotificationId);
          if (!notificationInitialized) {
            storeLastNotificationId(maxId);
            notificationInitialized = true;
            return;
          }
          const newItems = items.filter((item) => Number(item && item.id ? item.id : 0) > lastNotificationId);
          if (newItems.length) {
            newItems.sort((a, b) => Number(a.id) - Number(b.id));
            newItems.slice(0, 3).forEach(showBrowserNotification);
            storeLastNotificationId(maxId);
          }
        } catch (err) {
          // ignore polling errors
        }
      }

      function escapeHtml(str) {
        return String(str ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');
      }

      function buildItemMenu(type, id, canDelete) {
        return `
          <div class="item-menu" data-item-type="${escapeHtml(type)}" data-item-id="${escapeHtml(String(id))}">
            <button class="item-menu-btn" type="button" aria-label="Open menu">
              <i class="fa-solid fa-ellipsis"></i>
            </button>
            <div class="item-menu-list" role="menu">
              <button class="item-menu-item" type="button" data-menu-action="report">
                <i class="fa-regular fa-flag"></i>
                Report
              </button>
              ${canDelete ? `
                <button class="item-menu-item delete" type="button" data-menu-action="delete">
                  <i class="fa-regular fa-trash-can"></i>
                  Delete
                </button>
              ` : ''}
            </div>
          </div>
        `;
      }

      function setButtonLoading(button, isLoading, label) {
        if (!button) return;
        if (isLoading) {
          button.dataset.originalText = button.textContent;
          button.innerHTML = `<span class="loader-spin" aria-hidden="true"></span>${escapeHtml(label || 'Loading...')}`;
          button.classList.add('is-loading');
          button.disabled = true;
          return;
        }
        const original = button.dataset.originalText || '';
        button.textContent = original || 'Submit';
        button.classList.remove('is-loading');
        button.disabled = false;
      }

      function hashHandle(handle) {
        let hash = 0;
        for (let i = 0; i < handle.length; i += 1) {
          hash = (hash << 5) - hash + handle.charCodeAt(i);
          hash |= 0;
        }
        return Math.abs(hash);
      }

      function itemShuffleKey(item) {
        if (!item) return 'unknown';
        if (item.type === 'chat') return `chat_${item.data?.conversation_id ?? ''}`;
        if (item.type === 'post') return `post_${item.data?.id ?? ''}`;
        return `${item.type || 'item'}_${item.time || 0}`;
      }

      function itemShuffleScore(item) {
        const key = itemShuffleKey(item);
        const hash = hashHandle(`${shuffleSeed}:${key}`);
        const ratio = (hash % 1000000) / 1000000;
        const jitter = (ratio - 0.5) * SHUFFLE_JITTER_MS;
        return (item.time || 0) + jitter;
      }

      function sortFeedItems(items) {
        if (!Array.isArray(items)) return [];
        if (!shuffleEnabled) {
          return items.sort((a, b) => b.time - a.time);
        }
        return items.sort((a, b) => itemShuffleScore(b) - itemShuffleScore(a));
      }

      function mentionClass(handle) {
        const normalized = handle.toLowerCase();
        if (normalized === 'apilageai') return 'mention-ai';
        const idx = hashHandle(normalized) % mentionPalette.length;
        return mentionPalette[idx];
      }

      function formatText(text) {
        const safe = escapeHtml(text).replace(/\n/g, '<br>');
        return safe.replace(/(^|\s)@([a-z0-9_.]+)/gi, (match, prefix, handle) => {
          const cls = mentionClass(handle);
          return `${prefix}<span class="mention ${cls}">@${handle}</span>`;
        });
      }

      let markedConfigured = false;
      const mathJaxQueue = new Set();
      let mathJaxScheduled = false;

      function configureMarked() {
        if (!window.marked || markedConfigured) return;
        if (typeof window.marked.setOptions === 'function') {
          window.marked.setOptions({ breaks: true, gfm: true, headerIds: false, mangle: false });
        }
        markedConfigured = true;
      }

      function queueMathTypeset(targets) {
        if (!window.MathJax || !window.MathJax.typesetPromise) return;
        const list = Array.isArray(targets) ? targets : [targets];
        list.forEach((el) => {
          if (el) mathJaxQueue.add(el);
        });
        if (mathJaxScheduled) return;
        mathJaxScheduled = true;
        requestAnimationFrame(() => {
          const items = Array.from(mathJaxQueue);
          mathJaxQueue.clear();
          mathJaxScheduled = false;
          if (items.length) {
            window.MathJax.typesetPromise(items);
          }
        });
      }

      function applyMentionHighlight(root) {
        if (!root) return;
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach((node) => {
          if (!node.nodeValue || !node.parentNode) return;
          const text = node.nodeValue;
          if (!text.includes('@')) return;
          const parentTag = node.parentNode.tagName;
          if (parentTag === 'CODE' || parentTag === 'PRE') return;

          const regex = /@([a-z0-9_.]+)/gi;
          let match;
          let lastIndex = 0;
          const fragment = document.createDocumentFragment();
          let changed = false;

          while ((match = regex.exec(text)) !== null) {
            const start = match.index;
            const beforeChar = start === 0 ? '' : text[start - 1];
            if (start !== 0 && !/\s/.test(beforeChar)) {
              continue;
            }
            fragment.appendChild(document.createTextNode(text.slice(lastIndex, start)));
            const span = document.createElement('span');
            span.className = `mention ${mentionClass(match[1])}`;
            span.textContent = `@${match[1]}`;
            fragment.appendChild(span);
            lastIndex = start + match[0].length;
            changed = true;
          }

          if (!changed) return;
          fragment.appendChild(document.createTextNode(text.slice(lastIndex)));
          node.parentNode.replaceChild(fragment, node);
        });
      }

      function formatAiTextIn(root, options = {}) {
        if (!root) return;
        const elements = Array.from(root.querySelectorAll('[data-format-ai="true"]'));
        if (!elements.length) return;
        const ready = !!(window.marked && window.DOMPurify);
        if (ready) configureMarked();

        elements.forEach((el) => {
          const raw = el.dataset.raw ?? el.textContent ?? '';
          const state = el.dataset.formatState || '';
          if (options.onlyFallback && state && state !== 'fallback' && !options.force) return;
          if (!options.force) {
            if (ready && state === 'rendered') return;
            if (!ready && state === 'fallback') return;
          }

          if (ready) {
            const html = window.DOMPurify.sanitize(window.marked.parse(raw || ''));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            applyMentionHighlight(wrapper);
            el.innerHTML = wrapper.innerHTML;
            el.dataset.formatState = 'rendered';
          } else {
            el.innerHTML = formatText(raw || '');
            el.dataset.formatState = 'fallback';
          }
        });

        if (ready) {
          queueMathTypeset(elements);
        }
      }

      function formatDate(raw) {
        if (!raw) return '';
        const parsed = new Date(raw.replace(' ', 'T'));
        if (Number.isNaN(parsed.getTime())) return raw;
        return parsed.toLocaleString();
      }

      function formatHandle(user) {
        if (!user || !user.profile_url) return '@guest';
        const parts = String(user.profile_url).split('/').filter(Boolean);
        const slug = parts[parts.length - 1] || '';
        return slug ? `@${slug}` : '@guest';
      }

      function getProfileUrl(user) {
        return user && user.profile_url ? String(user.profile_url) : '';
      }

      function wrapProfileLink(user, innerHtml, className) {
        const url = getProfileUrl(user);
        const cls = className ? ` class="${className}"` : '';
        if (url) {
          return `<a${cls} href="${escapeHtml(url)}">${innerHtml}</a>`;
        }
        return `<span${cls}>${innerHtml}</span>`;
      }

      function getHandleSlug(user) {
        if (!user || !user.profile_url) return '';
        const handle = formatHandle(user);
        return handle.replace('@', '');
      }

      function countWords(text) {
        const trimmed = String(text || '').trim();
        if (!trimmed) return 0;
        return trimmed.split(/\s+/).filter(Boolean).length;
      }

      function formatBytes(bytes) {
        const mb = bytes / (1024 * 1024);
        return `${mb.toFixed(mb >= 10 ? 0 : 1)}MB`;
      }

      function getQueryPostId() {
        const params = new URLSearchParams(window.location.search);
        const value = Number(params.get('post') || 0);
        return Number.isFinite(value) ? value : 0;
      }

      function setFocusMode(active) {
        focusMode = !!active;
        document.body.classList.toggle('is-focus-mode', focusMode);
        if (!focusMode && focusBar) {
          focusBar.remove();
          focusBar = null;
        }
      }

      function ensureFocusBar(postId) {
        if (!feedList || !feedList.parentNode) return;
        if (!focusBar) {
          focusBar = document.createElement('div');
          focusBar.className = 'focus-bar';
          focusBar.innerHTML = `
            <div class="focus-title">Focused post view</div>
            <div class="focus-actions">
              <button class="focus-btn" type="button" data-focus-action="back">Back to Explore</button>
              <button class="focus-btn primary" type="button" data-focus-action="copy">Copy link</button>
            </div>
          `;
          feedList.parentNode.insertBefore(focusBar, feedList);
          focusBar.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-focus-action]');
            if (!btn) return;
            const action = btn.dataset.focusAction;
            if (action === 'back') {
              exitPostFocus();
            } else if (action === 'copy') {
              const id = Number(focusBar?.dataset?.postId || 0);
              if (id) {
                const link = `${window.APP_BASE_URL}/explore?post=${id}`;
                navigator.clipboard.writeText(link);
              }
            }
          });
        }
        focusBar.dataset.postId = String(postId || '');
      }

      function resetFeedState() {
        chatPage = 1;
        postPage = 1;
        feedLoading = false;
        hasMoreChats = true;
        hasMorePosts = true;
        cachedChats.length = 0;
        cachedPosts.length = 0;
        seenChats.clear();
        seenPosts.clear();
      }

      function registerUser(user) {
        if (!user) return;
        const handle = getHandleSlug(user);
        if (!handle) return;
        if (mentionIndex.has(handle)) return;
        mentionIndex.set(handle, {
          handle,
          name: user.name || handle,
          className: mentionClass(handle)
        });
      }

      function getMentionList(query) {
        const q = String(query || '').toLowerCase();
        const items = [];
        items.push({
          handle: 'apilageai',
          name: 'ApilageAI',
          className: 'mention-ai'
        });
        mentionIndex.forEach((value) => {
          if (value.handle.toLowerCase() === 'apilageai') return;
          items.push(value);
        });
        let filtered = q
          ? items.filter((item) => item.handle.toLowerCase().startsWith(q))
          : items;
        if (q && !filtered.find((item) => item.handle.toLowerCase() === 'apilageai')) {
          filtered = [items[0], ...filtered];
        }
        const [first, ...rest] = filtered;
        const sortedRest = rest.sort((a, b) => a.handle.localeCompare(b.handle));
        return first ? [first, ...sortedRest] : sortedRest;
      }

      if (window.userData) {
        registerUser(window.userData);
      }

      function buildMentionItems(list, activeIndex) {
        return list.map((item, idx) => {
          const activeClass = idx === activeIndex ? 'active' : '';
          const colorVar = item.className === 'mention-ai'
            ? '--mention-ai'
            : `--${item.className.replace('mention-color-', 'mention-')}`;
          return `
            <div class="mention-item ${activeClass}" data-handle="${escapeHtml(item.handle)}" style="--mention-color: var(${colorVar})">
              <span class="mention-dot"></span>
              <span class="mention-label">${escapeHtml(item.name)}</span>
              <span class="mention-handle">@${escapeHtml(item.handle)}</span>
            </div>
          `;
        }).join('');
      }

      function setupMentionAutocomplete(textarea, suggestBox) {
        if (!textarea || !suggestBox) return;
        let activeIndex = 0;
        let currentList = [];
        let startIndex = null;

        function closeSuggest() {
          suggestBox.classList.remove('active');
          suggestBox.innerHTML = '';
          activeIndex = 0;
          currentList = [];
          startIndex = null;
        }

        function openSuggest(list) {
          currentList = list;
          activeIndex = 0;
          suggestBox.innerHTML = buildMentionItems(list, activeIndex);
          suggestBox.classList.add('active');
        }

        function updateActive() {
          const items = suggestBox.querySelectorAll('.mention-item');
          items.forEach((item, idx) => {
            item.classList.toggle('active', idx === activeIndex);
          });
        }

        function insertMention(handle) {
          if (startIndex === null) return;
          const value = textarea.value;
          const end = textarea.selectionStart;
          const before = value.slice(0, startIndex);
          const after = value.slice(end);
          const mentionText = `@${handle} `;
          textarea.value = `${before}${mentionText}${after}`;
          const cursor = (before + mentionText).length;
          textarea.setSelectionRange(cursor, cursor);
          closeSuggest();
          textarea.focus();
        }

        textarea.addEventListener('input', () => {
          const pos = textarea.selectionStart;
          const before = textarea.value.slice(0, pos);
          const match = /(^|\s)@([a-z0-9_.]*)$/i.exec(before);
          if (!match) {
            closeSuggest();
            return;
          }
          const query = match[2] || '';
          startIndex = match.index + match[1].length;
          const list = getMentionList(query).slice(0, 6);
          if (list.length === 0) {
            closeSuggest();
            return;
          }
          openSuggest(list);
        });

        textarea.addEventListener('keydown', (event) => {
          if (!suggestBox.classList.contains('active')) return;
          if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = (activeIndex + 1) % currentList.length;
            updateActive();
          } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = (activeIndex - 1 + currentList.length) % currentList.length;
            updateActive();
          } else if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault();
            const pick = currentList[activeIndex];
            if (pick) insertMention(pick.handle);
          } else if (event.key === 'Escape') {
            event.preventDefault();
            closeSuggest();
          }
        });

        suggestBox.addEventListener('click', (event) => {
          const item = event.target.closest('.mention-item');
          if (!item) return;
          insertMention(item.dataset.handle || '');
        });

        textarea.addEventListener('blur', () => {
          setTimeout(closeSuggest, 120);
        });
      }

      function ensureMention(textarea, handle) {
        if (!textarea || !handle) return;
        const mentionText = `@${handle}`;
        const value = textarea.value.trim();
        if (value.toLowerCase().includes(mentionText.toLowerCase())) return;
        textarea.value = value ? `${mentionText} ${value}` : `${mentionText} `;
      }

      function renderPreviews(files, container) {
        if (!container) return [];
        container.innerHTML = '';
        const valid = [];
        Array.from(files || []).forEach((file) => {
          if (!file || !ALLOWED_TYPES.has(file.type)) {
            return;
          }
          if (file.size > MAX_FILE_BYTES) {
            return;
          }
          valid.push(file);
        });
        const limited = valid.slice(0, 4);
        container.classList.toggle('is-grid', limited.length > 1);
        limited.forEach((file) => {
          const item = document.createElement('div');
          item.className = 'upload-item';
          const img = document.createElement('img');
          img.src = URL.createObjectURL(file);
          img.alt = file.name;
          const loader = document.createElement('div');
          loader.className = 'upload-loader';
          loader.innerHTML = '<span class="loader-spin" aria-hidden="true"></span>';
          img.addEventListener('load', () => item.classList.add('is-preview-loaded'));
          img.addEventListener('error', () => item.classList.add('is-preview-loaded'));
          const progress = document.createElement('div');
          progress.className = 'upload-progress';
          const size = document.createElement('div');
          size.className = 'upload-size';
          size.textContent = formatBytes(file.size);
          item.appendChild(img);
          item.appendChild(loader);
          item.appendChild(progress);
          item.appendChild(size);
          container.appendChild(item);
        });
        return limited;
      }

      function setPreviewUploading(container, isUploading) {
        if (!container) return;
        const items = container.querySelectorAll('.upload-item');
        items.forEach((item) => {
          item.classList.toggle('is-uploading', isUploading);
        });
      }

      function validateFiles(files) {
        const valid = [];
        const errors = [];
        Array.from(files || []).forEach((file) => {
          if (!file) return;
          if (!ALLOWED_TYPES.has(file.type)) {
            errors.push('Only PNG, GIF, or JPEG images are allowed.');
            return;
          }
          if (file.size > MAX_FILE_BYTES) {
            errors.push('Each image must be 60MB or less.');
            return;
          }
          valid.push(file);
        });
        return { valid, errors };
      }

      function getNodeApiBase() {
        const host = window.location.hostname;
        if (host === 'apilageai.lk') return 'https://socket.apilageai.lk';
        return window.location.origin;
      }

      function setImageModalLoading(isLoading) {
        if (!imageModal) return;
        imageModal.classList.toggle('is-loading', isLoading);
      }

      function openImageModal(src, altText) {
        if (!imageModal || !imageModalImg) return;
        setImageModalLoading(true);
        imageModalImg.src = '';
        imageModalImg.alt = altText || 'Preview';
        imageModal.classList.add('active');
        document.body.classList.add('modal-open');
        requestAnimationFrame(() => {
          imageModalImg.src = src;
        });
      }

      function closeImageModal() {
        if (!imageModal || !imageModalImg) return;
        imageModalImg.src = '';
        imageModal.classList.remove('active');
        document.body.classList.remove('modal-open');
        setImageModalLoading(false);
      }

      if (imageModalImg) {
        imageModalImg.addEventListener('load', () => setImageModalLoading(false));
        imageModalImg.addEventListener('error', () => setImageModalLoading(false));
      }

      function setShareStatus(message, type = '') {
        if (!shareStatus) return;
        shareStatus.textContent = message || '';
        shareStatus.dataset.type = type;
      }

      function getShareUrl() {
        if (shareModal && shareModal.dataset.url) {
          return shareModal.dataset.url;
        }
        return window.location.href;
      }

      function openShareModal(url) {
        if (!shareModal) return;
        const link = url || window.location.href;
        shareModal.dataset.url = link;
        if (shareLinkInput) shareLinkInput.value = link;
        shareModal.classList.add('active');
        shareModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        setShareStatus('');
      }

      function closeShareModal() {
        if (!shareModal) return;
        shareModal.classList.remove('active');
        shareModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        setShareStatus('');
      }

      if (imageModal) {
        imageModal.addEventListener('click', (event) => {
          if (event.target.closest('.image-modal-content') && !event.target.closest('.image-modal-close')) {
            return;
          }
          closeImageModal();
        });
      }
      if (shareModal) {
        shareModal.addEventListener('click', (event) => {
          if (event.target.closest('.share-modal-card') && !event.target.closest('[data-share-close]')) {
            return;
          }
          closeShareModal();
        });
      }
      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          closeImageModal();
          closeShareModal();
        }
      });

      if (shareCopyBtn) {
        shareCopyBtn.addEventListener('click', () => {
          const link = getShareUrl();
          navigator.clipboard.writeText(link).then(() => {
            setShareStatus('Link copied.', 'success');
            shareCopyBtn.textContent = 'Copied';
            setTimeout(() => {
              shareCopyBtn.textContent = 'Copy link';
              setShareStatus('');
            }, 1500);
          }).catch(() => {
            setShareStatus('Unable to copy link.', 'error');
          });
        });
      }
      if (shareModal) {
        shareModal.addEventListener('click', (event) => {
          const whatsappBtn = event.target.closest('[data-share-whatsapp]');
          if (whatsappBtn) {
            const link = getShareUrl();
            const url = `https://wa.me/?text=${encodeURIComponent(link)}`;
            window.open(url, '_blank', 'noopener');
            return;
          }
          const twitterBtn = event.target.closest('[data-share-twitter]');
          if (twitterBtn) {
            const link = getShareUrl();
            const url = `https://twitter.com/intent/tweet?url=${encodeURIComponent(link)}`;
            window.open(url, '_blank', 'noopener');
          }
        });
      }

      async function uploadImages(files, previewContainer) {
        if (!files || !files.length) return [];
        const { valid, errors } = validateFiles(files);
        if (errors.length) {
          alert(Array.from(new Set(errors)).join('\n'));
        }
        const limited = valid.slice(0, 4);
        if (!limited.length) return [];
        if (valid.length > 4) {
          alert('You can upload up to 4 images at a time.');
        }

        const form = new FormData();
        limited.forEach((file) => form.append('images', file));
        setPreviewUploading(previewContainer, true);

        return new Promise((resolve, reject) => {
          const xhr = new XMLHttpRequest();
          xhr.open('POST', `${getNodeApiBase()}/upload`, true);
          xhr.withCredentials = true;
          xhr.onload = () => {
            setPreviewUploading(previewContainer, false);
            if (xhr.status < 200 || xhr.status >= 300) {
              reject(new Error('Image upload failed'));
              return;
            }
            let data = {};
            try {
              data = JSON.parse(xhr.responseText);
            } catch (err) {
              reject(new Error('Image upload failed'));
              return;
            }
            if (Array.isArray(data.filenames)) {
              resolve(data.filenames);
              return;
            }
            if (data.filename) {
              resolve([data.filename]);
              return;
            }
            resolve([]);
          };
          xhr.onerror = () => {
            setPreviewUploading(previewContainer, false);
            reject(new Error('Image upload failed'));
          };
          xhr.send(form);
        });
      }

      function renderChatCard(chat) {
        const card = document.createElement('div');
        card.className = 'x-post-card';
        card.dataset.type = 'chat';
        const isOwner = currentUserId && Number(chat.user.id) === currentUserId;
        const menuHtml = buildItemMenu('chat', chat.conversation_id, isOwner);
        const avatarHtml = wrapProfileLink(
          chat.user,
          `<img class="x-avatar" src="${escapeHtml(chat.user.image_url)}" alt="${escapeHtml(chat.user.name)}">`,
          'x-avatar-link'
        );
        const nameHtml = wrapProfileLink(chat.user, escapeHtml(chat.user.name), 'x-name');
        const handleHtml = wrapProfileLink(chat.user, escapeHtml(formatHandle(chat.user)), 'x-handle');
        card.innerHTML = `
          ${menuHtml}
          <div class="x-post-head">
            ${avatarHtml}
            <div class="x-post-meta">
              <div class="x-post-title">
                ${nameHtml}
                ${handleHtml}
                <span class="x-time">- ${escapeHtml(formatDate(chat.published_at))}</span>
                <span class="x-badge"><i class="fa-solid fa-comment-dots"></i> Chat</span>
              </div>
              <div class="x-post-body" data-format-ai="true"></div>
            </div>
          </div>
          <div class="x-post-actions">
            <button class="x-action" type="button" data-open-chat="${chat.conversation_id}">
              <i class="fa-regular fa-message"></i>
              <span>${chat.message_count}</span>
            </button>
            <button class="x-action" type="button" data-open-chat="${chat.conversation_id}">
              <i class="fa-solid fa-arrow-up-right-from-square"></i>
              <span>Open</span>
            </button>
          </div>
        `;
        const chatBody = card.querySelector('.x-post-body');
        if (chatBody) {
          chatBody.dataset.raw = chat.title || '';
          chatBody.textContent = chat.title || '';
        }
        formatAiTextIn(card);
        return card;
      }

      function renderPostCard(post) {
        const wrapper = document.createElement('div');
        wrapper.className = 'x-post-card';
        wrapper.dataset.postId = post.id;
        wrapper.dataset.type = 'post';
        const images = Array.isArray(post.images) ? post.images : [];
        const mediaClass = images.length > 1 ? 'x-post-media is-multi' : 'x-post-media is-single';
        const imagesHtml = images.map((url) => `<img src="${escapeHtml(url)}" alt="Post image" data-image-preview="${escapeHtml(url)}">`).join('');
        const handle = formatHandle(post.user);
        const isOwner = currentUserId && Number(post.user.id) === currentUserId;
        const menuHtml = buildItemMenu('post', post.id, isOwner);
        const avatarHtml = wrapProfileLink(
          post.user,
          `<img class="x-avatar" src="${escapeHtml(post.user.image_url)}" alt="${escapeHtml(post.user.name)}">`,
          'x-avatar-link'
        );
        const nameHtml = wrapProfileLink(post.user, escapeHtml(post.user.name), 'x-name');
        const handleHtml = wrapProfileLink(post.user, escapeHtml(handle), 'x-handle');
        wrapper.innerHTML = `
          ${menuHtml}
          <div class="x-post-head">
            ${avatarHtml}
            <div class="x-post-meta">
              <div class="x-post-title">
                ${nameHtml}
                ${handleHtml}
                <span class="x-time">- ${escapeHtml(formatDate(post.created_at))}</span>
                <span class="x-badge"><i class="fa-regular fa-circle-question"></i> Question</span>
              </div>
              <div class="x-post-body" data-format-ai="true"></div>
              ${imagesHtml ? `<div class="${mediaClass}">${imagesHtml}</div>` : ''}
            </div>
          </div>
          <div class="x-post-actions">
            <button class="x-action" type="button" data-action="toggle-comments">
              <i class="fa-regular fa-comment"></i>
              <span class="reply-count">${post.comment_count}</span>
            </button>
            <button class="x-action" type="button" data-action="copy-link">
              <i class="fa-regular fa-share-from-square"></i>
              <span>Share</span>
            </button>
          </div>
          <div class="x-thread" hidden>
            <div class="thread-list"></div>
            <div class="thread-form">
              <div class="replying-indicator" hidden>
                Replying to <span class="replying-name"></span>
                <button type="button" class="reply-cancel">Cancel</button>
              </div>
              <div class="mention-wrap">
                <textarea name="reply_body" placeholder="Reply or mention @apilageai" autocomplete="off" aria-label="Reply or mention"></textarea>
                <div class="mention-suggest"></div>
              </div>
              <div class="upload-preview"></div>
              <div class="form-actions">
                <div class="thread-upload">
                  <label class="thread-file" title="Add images">
                    <input type="file" name="reply_images[]" accept="image/png,image/jpeg,image/gif" multiple aria-label="Attach images">
                    <i class="fa-regular fa-image"></i>
                  </label>
                  <span class="x-helper">PNG, GIF, JPG. Up to 4 images, 60MB. Mention @apilageai for AI reply.</span>
                </div>
                <button type="button" class="comment-submit">Reply</button>
              </div>
            </div>
          </div>
        `;
        const postBody = wrapper.querySelector('.x-post-body');
        if (postBody) {
          postBody.dataset.raw = post.body || '';
          postBody.textContent = post.body || '';
        }
        formatAiTextIn(wrapper);
        return wrapper;
      }

      function renderComment(comment) {
        const card = document.createElement('div');
        card.className = `thread-item ${comment.author_type === 'ai' ? 'is-ai' : ''}`;
        card.dataset.commentId = comment.id;
        card.dataset.parentId = comment.parent_id ? String(comment.parent_id) : '';
        card.dataset.authorType = comment.author_type;
        const hasProfile = !!(comment.user && comment.user.profile_url);
        const handleValue = comment.author_type === 'ai'
          ? 'apilageai'
          : (hasProfile ? formatHandle(comment.user).replace('@', '') : '');
        card.dataset.handle = handleValue;
        const handle = comment.author_type === 'ai'
          ? '@apilageai'
          : (hasProfile ? formatHandle(comment.user) : '@guest');
        const isOwner = comment.author_type === 'user' && currentUserId && Number(comment.user.id) === currentUserId;
        const menuHtml = buildItemMenu('comment', comment.id, isOwner);
        const avatarTagOpen = hasProfile && comment.author_type !== 'ai'
          ? `<a class="thread-avatar" href="${escapeHtml(comment.user.profile_url)}">`
          : '<div class="thread-avatar">';
        const avatarTagClose = hasProfile && comment.author_type !== 'ai' ? '</a>' : '</div>';
        const nameHtml = hasProfile && comment.author_type !== 'ai'
          ? `<a class="thread-name" href="${escapeHtml(comment.user.profile_url)}">${escapeHtml(comment.user.name)}</a>`
          : `<span class="thread-name">${escapeHtml(comment.user.name)}</span>`;
        const handleHtml = hasProfile && comment.author_type !== 'ai'
          ? `<a class="thread-handle" href="${escapeHtml(comment.user.profile_url)}">${escapeHtml(handle)}</a>`
          : `<span class="thread-handle">${escapeHtml(handle)}</span>`;
        const images = Array.isArray(comment.images) ? comment.images : [];
        const mediaClass = images.length > 1 ? 'thread-media is-multi' : 'thread-media is-single';
        const imagesHtml = images.map((url) => `<img src="${escapeHtml(url)}" alt="Comment image" data-image-preview="${escapeHtml(url)}">`).join('');
        card.innerHTML = `
          ${avatarTagOpen}
            <img src="${escapeHtml(comment.user.image_url)}" alt="${escapeHtml(comment.user.name)}">
          ${avatarTagClose}
          <div class="thread-content">
            ${menuHtml}
            <div class="thread-header">
              ${nameHtml}
              ${handleHtml}
              <span class="thread-time">- ${escapeHtml(formatDate(comment.created_at))}</span>
            </div>
            <div class="thread-body" data-format-ai="true"></div>
            ${imagesHtml ? `<div class="${mediaClass}">${imagesHtml}</div>` : ''}
            <div class="thread-actions">
              <button type="button" class="thread-reply-btn" data-comment-id="${comment.id}">Reply</button>
              <button type="button" class="thread-toggle" data-comment-id="${comment.id}" hidden>See replies</button>
            </div>
            <div class="thread-replies" hidden></div>
          </div>
        `;
        const bodyEl = card.querySelector('.thread-body');
        if (bodyEl) {
          bodyEl.dataset.raw = comment.body || '';
          bodyEl.textContent = comment.body || '';
        }
        formatAiTextIn(card);
        return card;
      }

      function initPostCard(card) {
        if (!card) return;
        const textarea = card.querySelector('.thread-form textarea');
        const suggest = card.querySelector('.thread-form .mention-suggest');
        setupMentionAutocomplete(textarea, suggest);
      }

      function updateReplyToggle(toggle, repliesWrap) {
        if (!toggle || !repliesWrap) return;
        const count = repliesWrap.childElementCount;
        if (!count) {
          toggle.hidden = true;
          return;
        }
        toggle.hidden = false;
        toggle.textContent = `${repliesWrap.hidden ? 'See' : 'Hide'} replies (${count})`;
      }

      function buildThread(comments, listEl, options = {}) {
        const expandAll = !!options.expandAll;
        const byParent = {};

        comments.forEach((comment) => {
          registerUser(comment.user);
          const parentId = comment.parent_id ? String(comment.parent_id) : '';
          const key = parentId || 'root';
          if (!byParent[key]) byParent[key] = [];
          byParent[key].push({ ...comment, parent_id: parentId || null });
        });

        function renderNode(comment) {
          const item = renderComment(comment);
          const replies = byParent[String(comment.id)] || [];
          const toggle = item.querySelector('.thread-toggle');
          const repliesWrap = item.querySelector('.thread-replies');

          if (replies.length) {
            item.classList.add('has-replies');
            replies.forEach((reply) => {
              repliesWrap.appendChild(renderNode(reply));
            });
            repliesWrap.hidden = !expandAll;
            updateReplyToggle(toggle, repliesWrap);
          }
          return item;
        }

        listEl.innerHTML = '';
        const roots = byParent.root || [];
        if (!roots.length) {
          const empty = document.createElement('div');
          empty.className = 'status-text';
          empty.textContent = 'No replies yet.';
          listEl.appendChild(empty);
          return;
        }
        roots.forEach((comment) => {
          listEl.appendChild(renderNode(comment));
        });
      }

      function setReplyTarget(card, commentEl) {
        const form = card.querySelector('.thread-form');
        if (!form) return;
        const indicator = form.querySelector('.replying-indicator');
        const nameEl = form.querySelector('.replying-name');
        const textarea = form.querySelector('textarea');
        const commentId = commentEl ? commentEl.dataset.commentId : '';
        form.dataset.replyToId = commentId || '';
        if (commentEl) {
          form.dataset.parentId = commentId || '';
        } else {
          form.dataset.parentId = '';
        }
        if (commentEl) {
          const name = commentEl.querySelector('.thread-name')?.textContent || 'user';
          if (nameEl) nameEl.textContent = name;
          if (indicator) indicator.hidden = false;
          if (commentId && textarea) {
            const handle = commentEl.dataset.handle || '';
            ensureMention(textarea, handle);
          }
        } else {
          if (indicator) indicator.hidden = true;
          if (nameEl) nameEl.textContent = '';
        }
      }

      function shuffleArray(list) {
        const arr = list.slice();
        for (let i = arr.length - 1; i > 0; i -= 1) {
          const j = Math.floor(Math.random() * (i + 1));
          [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
      }

      function getTotalLoaded() {
        return cachedChats.length + cachedPosts.length;
      }

      function updateLoadMoreVisibility() {
        const total = getTotalLoaded();
        const limitReached = total >= MAX_FEED_ITEMS;
        const canLoadMore = (hasMoreChats || hasMorePosts) && !limitReached;
        if (loadMoreFeed) {
          loadMoreFeed.hidden = !canLoadMore;
        }
        if (feedEnd) {
          feedEnd.hidden = true;
        }
        if (feedLoader && !feedLoading) {
          feedLoader.hidden = true;
        }
      }

      function updateRightRail() {
        if (trendList && cachedChats.length) {
          const ranked = cachedChats.map((chat) => ({
            chat,
            score: (chat.message_count * 3) + (new Date(chat.published_at).getTime() / 1e12)
          }));
          const top = shuffleArray(ranked.sort((a, b) => b.score - a.score).slice(0, 8)).slice(0, 4);
          trendList.innerHTML = top.map((row) => `
            <div class="x-card-item">
              <div class="x-card-name">${escapeHtml(row.chat.title)}</div>
              <div class="x-card-sub">${row.chat.message_count} replies - ${escapeHtml(formatHandle(row.chat.user))}</div>
            </div>
          `).join('');
        }

        if (questionList && cachedPosts.length) {
          const rankedPosts = cachedPosts.map((post) => ({
            post,
            score: (post.comment_count * 4) + (new Date(post.created_at).getTime() / 1e12)
          }));
          const topPosts = shuffleArray(rankedPosts.sort((a, b) => b.score - a.score).slice(0, 8)).slice(0, 4);
          questionList.innerHTML = topPosts.map((row) => `
            <div class="x-card-item">
              <div class="x-card-name">${escapeHtml(row.post.body.slice(0, 60))}${row.post.body.length > 60 ? '...' : ''}</div>
              <div class="x-card-sub">${row.post.comment_count} replies - ${escapeHtml(formatHandle(row.post.user))}</div>
            </div>
          `).join('');
        }

      }

      function closeAllMenus(except) {
        document.querySelectorAll('.item-menu.open').forEach((menu) => {
          if (except && menu === except) return;
          menu.classList.remove('open');
        });
      }

      async function submitReport(itemType, itemId) {
        if (window.IS_GUEST) {
          alert('Please log in to report.');
          return;
        }
        const reason = window.prompt('Report reason (optional):');
        if (reason === null) return;
        try {
          const res = await fetch(`${apiBase}?action=report_item`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_type: itemType, item_id: itemId, reason })
          });
          const data = await res.json();
          if (!data.success) {
            alert(data.message || 'Failed to report.');
            return;
          }
          alert('Report submitted.');
        } catch (err) {
          console.error(err);
          alert('Failed to report.');
        }
      }

      function updateReplyCount(card, delta) {
        const replyCountEl = card.querySelector('.reply-count');
        if (!replyCountEl) return;
        const current = parseInt(replyCountEl.textContent, 10) || 0;
        const next = Math.max(0, current + delta);
        replyCountEl.textContent = `${next}`;
        const cached = cachedPosts.find((post) => Number(post.id) === Number(card.dataset.postId));
        if (cached) {
          cached.comment_count = Math.max(0, (cached.comment_count || 0) + delta);
        }
      }

      function removeCommentFromUi(commentEl, deletedCount) {
        if (!commentEl) return;
        const card = commentEl.closest('.x-post-card');
        if (!card) return;
        const list = card.querySelector('.thread-list');
        const parentId = commentEl.dataset.parentId || '';
        const isRoot = !parentId;
        const countDelta = -Math.max(1, Number(deletedCount) || 1);

        if (isRoot) {
          commentEl.remove();
          if (list && !list.querySelector('.thread-item')) {
            const empty = document.createElement('div');
            empty.className = 'status-text';
            empty.textContent = 'No replies yet.';
            list.appendChild(empty);
          }
        } else {
          const parentEl = list ? list.querySelector(`[data-comment-id="${parentId}"]`) : null;
          commentEl.remove();
          if (parentEl) {
            const repliesWrap = parentEl.querySelector('.thread-replies');
            const toggle = parentEl.querySelector('.thread-toggle');
            const childCount = repliesWrap ? repliesWrap.childElementCount : 0;
            if (repliesWrap && childCount === 0) {
              repliesWrap.hidden = true;
              parentEl.classList.remove('has-replies');
            }
            if (toggle && repliesWrap) {
              updateReplyToggle(toggle, repliesWrap);
            }
          }
        }

        updateReplyCount(card, countDelta);
        updateRightRail();
      }

      async function submitDelete(itemType, itemId, targetEl) {
        if (window.IS_GUEST) {
          alert('Please log in to delete.');
          return;
        }
        if (!window.confirm('Delete this item?')) return;
        try {
          const res = await fetch(`${apiBase}?action=delete_item`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_type: itemType, item_id: itemId })
          });
          const data = await res.json();
          if (!data.success) {
            alert(data.message || 'Failed to delete.');
            return;
          }

          if (itemType === 'comment') {
            removeCommentFromUi(targetEl, data.deleted_count);
            return;
          }

          if (itemType === 'post' && targetEl) {
            const postId = Number(itemId);
            const index = cachedPosts.findIndex((post) => Number(post.id) === postId);
            if (index >= 0) cachedPosts.splice(index, 1);
            targetEl.remove();
            if (feedList && !feedList.children.length) {
              feedEmpty.hidden = false;
            }
            updateRightRail();
            return;
          }

          if (itemType === 'chat' && targetEl) {
            const chatId = Number(itemId);
            const index = cachedChats.findIndex((chat) => Number(chat.conversation_id) === chatId);
            if (index >= 0) cachedChats.splice(index, 1);
            targetEl.remove();
            if (feedList && !feedList.children.length) {
              feedEmpty.hidden = false;
            }
            updateRightRail();
          }
        } catch (err) {
          console.error(err);
          alert('Failed to delete.');
        }
      }

      async function fetchPost(postId) {
        try {
          const res = await fetch(`${apiBase}?action=get_post&post_id=${postId}`, { credentials: 'include' });
          const data = await res.json();
          if (data && data.success && data.post) return data.post;
        } catch (err) {
          console.error(err);
        }
        return null;
      }

      async function fetchAllComments(postId) {
        const all = [];
        let page = 1;
        let hasMore = true;
        const perPage = 50;
        const maxPages = 20;
        while (hasMore && page <= maxPages) {
          try {
            const res = await fetch(`${apiBase}?action=list_comments&post_id=${postId}&page=${page}&per_page=${perPage}`, { credentials: 'include' });
            const data = await res.json();
            if (!data || !data.success || !Array.isArray(data.comments)) {
              break;
            }
            all.push(...data.comments);
            hasMore = !!data.has_more;
            page += 1;
          } catch (err) {
            console.error(err);
            break;
          }
        }
        return all;
      }

      async function openPostFocus(postId, pushState = true) {
        if (!postId || !feedList) return;
        setFocusMode(true);
        focusPostId = Number(postId) || 0;
        if (pushState) {
          const url = `${window.APP_BASE_URL}/explore?post=${focusPostId}`;
          window.history.pushState({ post: focusPostId }, '', url);
        }
        ensureFocusBar(focusPostId);
        feedList.innerHTML = '';
        feedEmpty.hidden = true;
        if (feedLoader) feedLoader.hidden = false;
        if (loadMoreFeed) loadMoreFeed.hidden = true;
        if (feedEnd) feedEnd.hidden = true;
        try {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (err) {
          window.scrollTo(0, 0);
        }

        const post = await fetchPost(focusPostId);
        if (!post) {
          if (feedLoader) feedLoader.hidden = true;
          const empty = document.createElement('div');
          empty.className = 'empty-state';
          empty.textContent = 'Post not found.';
          feedList.appendChild(empty);
          return;
        }

        const node = renderPostCard(post);
        initPostCard(node);
        feedList.appendChild(node);

        const thread = node.querySelector('.x-thread');
        const list = node.querySelector('.thread-list');
        const form = node.querySelector('.thread-form');
        if (thread) thread.hidden = false;
        if (form) form.hidden = window.IS_GUEST;
        if (list) {
          list.dataset.loaded = '1';
          list.innerHTML = `
            <div class="feed-loader">
              <span class="loader-spin" aria-hidden="true"></span>
              Loading replies...
            </div>
          `;
          const comments = await fetchAllComments(focusPostId);
          buildThread(comments, list, { expandAll: true });
        }

        if (feedLoader) feedLoader.hidden = true;
        updateRightRail();
      }

      function exitPostFocus(pushState = true) {
        if (!focusMode) return;
        setFocusMode(false);
        focusPostId = 0;
        if (pushState) {
          const url = `${window.APP_BASE_URL}/explore`;
          window.history.pushState({}, '', url);
        }
        resetFeedState();
        if (feedList) feedList.innerHTML = '';
        if (feedLoader) feedLoader.hidden = true;
        if (feedEmpty) feedEmpty.hidden = true;
        loadFeed();
      }

      async function loadFeed() {
        if (focusMode) return;
        if (feedLoading) return;
        if (!hasMoreChats && !hasMorePosts) {
          updateLoadMoreVisibility();
          return;
        }
        if (getTotalLoaded() >= MAX_FEED_ITEMS) {
          hasMoreChats = false;
          hasMorePosts = false;
          updateLoadMoreVisibility();
          return;
        }
        feedLoading = true;
        if (feedLoader) feedLoader.hidden = false;
        const loadMoreLabel = loadMoreFeed ? loadMoreFeed.textContent : '';
        if (loadMoreFeed) {
          loadMoreFeed.disabled = true;
          loadMoreFeed.textContent = 'Loading...';
        }

        try {
          const [chatRes, postRes] = await Promise.all([
            hasMoreChats ? fetch(`${apiBase}?action=list_chats&page=${chatPage}`, { credentials: 'include' }) : null,
            hasMorePosts ? fetch(`${apiBase}?action=list_posts&page=${postPage}`, { credentials: 'include' }) : null
          ]);

          const chatData = chatRes ? await chatRes.json() : { chats: [], has_more: false };
          const postData = postRes ? await postRes.json() : { posts: [], has_more: false };

          if (focusMode) {
            return;
          }

          const newItems = [];

          if (chatData && chatData.success && Array.isArray(chatData.chats)) {
            chatData.chats.forEach((chat) => {
              if (seenChats.has(chat.conversation_id)) return;
              seenChats.add(chat.conversation_id);
              registerUser(chat.user);
              cachedChats.push(chat);
              newItems.push({
                type: 'chat',
                time: new Date(chat.published_at).getTime() || 0,
                data: chat
              });
            });
            hasMoreChats = !!chatData.has_more;
            chatPage += chatData.chats.length ? 1 : 0;
          } else {
            hasMoreChats = false;
          }

          if (postData && postData.success && Array.isArray(postData.posts)) {
            postData.posts.forEach((post) => {
              if (seenPosts.has(post.id)) return;
              seenPosts.add(post.id);
              registerUser(post.user);
              cachedPosts.push(post);
              newItems.push({
                type: 'post',
                time: new Date(post.created_at).getTime() || 0,
                data: post
              });
            });
            hasMorePosts = !!postData.has_more;
            postPage += postData.posts.length ? 1 : 0;
          } else {
            hasMorePosts = false;
          }

          if (newItems.length === 0 && feedList.children.length === 0) {
            feedEmpty.hidden = false;
          }

          sortFeedItems(newItems).forEach((item) => {
            const node = item.type === 'chat' ? renderChatCard(item.data) : renderPostCard(item.data);
            if (item.type === 'post') {
              initPostCard(node);
            }
            feedList.appendChild(node);
          });

          updateRightRail();
          updateLoadMoreVisibility();
        } catch (err) {
          console.error(err);
        } finally {
          feedLoading = false;
          if (feedLoader) feedLoader.hidden = true;
          if (loadMoreFeed) loadMoreFeed.disabled = false;
          if (loadMoreFeed) loadMoreFeed.textContent = loadMoreLabel || 'Load more';
          updateLoadMoreVisibility();
        }
      }

      async function loadComments(card) {
        const postId = card.dataset.postId;
        const list = card.querySelector('.thread-list');
        const thread = card.querySelector('.x-thread');
        const form = card.querySelector('.thread-form');
        if (thread) thread.hidden = false;
        if (form) form.hidden = window.IS_GUEST;
        if (list.dataset.loaded === '1') return;
        list.dataset.loaded = '1';
        if (list) {
          list.innerHTML = `
            <div class="feed-loader">
              <span class="loader-spin" aria-hidden="true"></span>
              Loading replies...
            </div>
          `;
        }

        let loaded = false;
        try {
          const res = await fetch(`${apiBase}?action=list_comments&post_id=${postId}`, { credentials: 'include' });
          const data = await res.json();
          if (data.success && Array.isArray(data.comments)) {
            buildThread(data.comments, list);
            loaded = true;
          }
        } catch (err) {
          console.error(err);
        }
        if (!loaded && list) {
          list.innerHTML = '<div class="status-text">No replies yet.</div>';
        }
      }

      async function handlePostSubmit() {
        if (window.IS_GUEST) {
          alert('Please log in to post.');
          return;
        }
        if (!postBody) return;
        const body = postBody.value.trim();
        const files = postImages?.files || [];
        const validFiles = validateFiles(files).valid;
        if (!body && !validFiles.length) {
          postStatus.textContent = 'Write a question or add an image.';
          return;
        }
        if (countWords(body) > MAX_WORDS) {
          postStatus.textContent = 'Post is too long. Maximum is 1000 words.';
          return;
        }
        postStatus.textContent = 'Posting...';
        setButtonLoading(postSubmit, true, 'Posting...');
        try {
          const uploaded = await uploadImages(files, postImagePreview);
          const res = await fetch(`${apiBase}?action=create_post`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body, images: uploaded })
          });
          const data = await res.json();
          if (!data.success) {
            postStatus.textContent = data.message || 'Failed to post.';
            return;
          }
          postStatus.textContent = 'Posted successfully.';
          postBody.value = '';
          if (postImages) postImages.value = '';
          if (postImagePreview) {
            postImagePreview.innerHTML = '';
            postImagePreview.classList.remove('is-grid');
          }
          if (data.post) {
            const node = renderPostCard(data.post);
            initPostCard(node);
            feedList.prepend(node);
            feedEmpty.hidden = true;
            cachedPosts.unshift(data.post);
          }
          if (data.ai_comment && data.post) {
            const card = feedList.querySelector(`[data-post-id="${data.post.id}"]`);
            if (card) {
              const list = card.querySelector('.thread-list');
              const thread = card.querySelector('.x-thread');
              if (thread) thread.hidden = false;
              buildThread([data.ai_comment], list);
              list.dataset.loaded = '1';
            }
          }
          updateRightRail();
        } catch (err) {
          console.error(err);
          postStatus.textContent = 'Failed to post. Please try again.';
        } finally {
          setButtonLoading(postSubmit, false, 'Post');
        }
      }

      async function handleCommentSubmit(button) {
        if (window.IS_GUEST) {
          alert('Please log in to reply.');
          return;
        }
        const card = button.closest('.x-post-card');
        if (!card) return;
        const postId = card.dataset.postId;
        const textarea = card.querySelector('.thread-form textarea');
        const fileInput = card.querySelector('.thread-form input[type="file"]');
        const form = card.querySelector('.thread-form');
        const body = textarea.value.trim();
        const files = fileInput?.files || [];
        const validFiles = validateFiles(files).valid;
        const parentIdRaw = form?.dataset.parentId || '';
        const parentId = parentIdRaw ? Number(parentIdRaw) : null;
        const replyToRaw = form?.dataset.replyToId || '';
        const replyToId = replyToRaw ? Number(replyToRaw) : null;
        if (!body && !validFiles.length) {
          alert('Reply cannot be empty.');
          return;
        }
        if (body && countWords(body) > MAX_WORDS) {
          alert('Reply is too long. Maximum is 1000 words.');
          return;
        }
        setButtonLoading(button, true, 'Replying...');
        try {
          const preview = form ? form.querySelector('.upload-preview') : null;
          const uploaded = await uploadImages(files, preview);
          const res = await fetch(`${apiBase}?action=create_comment`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: postId, body, images: uploaded, parent_id: parentId, reply_to_id: replyToId })
          });
          const data = await res.json();
          if (!data.success) {
            alert(data.message || 'Failed to reply.');
            return;
          }
          const list = card.querySelector('.thread-list');
          list.dataset.loaded = '1';

          if (parentId) {
            const parentEl = list.querySelector(`[data-comment-id="${parentId}"]`);
            if (parentEl) {
              const repliesWrap = parentEl.querySelector('.thread-replies');
              const toggle = parentEl.querySelector('.thread-toggle');
              if (repliesWrap) {
                repliesWrap.hidden = false;
                repliesWrap.appendChild(renderComment(data.comment));
              }
              if (toggle && repliesWrap) {
                updateReplyToggle(toggle, repliesWrap);
              }
              parentEl.classList.add('has-replies');
            } else {
              list.appendChild(renderComment(data.comment));
            }
          } else {
            const emptyText = list.querySelector('.status-text');
            if (emptyText) emptyText.remove();
            list.appendChild(renderComment(data.comment));
          }

          if (data.ai_comment) {
            const aiTargetId = data.ai_comment.parent_id || null;
            if (aiTargetId) {
              const aiParent = list.querySelector(`[data-comment-id="${aiTargetId}"]`);
              if (aiParent) {
                const repliesWrap = aiParent.querySelector('.thread-replies');
                const toggle = aiParent.querySelector('.thread-toggle');
                if (repliesWrap) {
                  repliesWrap.hidden = false;
                  repliesWrap.appendChild(renderComment(data.ai_comment));
                }
                if (toggle && repliesWrap) {
                  updateReplyToggle(toggle, repliesWrap);
                }
                aiParent.classList.add('has-replies');
              }
            } else {
              list.appendChild(renderComment(data.ai_comment));
            }
          } else if (data.ai_cooldown) {
            alert('AI is on cooldown for this thread.');
          }

          const replyCountEl = card.querySelector('.reply-count');
          if (replyCountEl) {
            const current = parseInt(replyCountEl.textContent, 10) || 0;
            const inc = data.ai_comment ? 2 : 1;
            replyCountEl.textContent = `${current + inc}`;
            const cached = cachedPosts.find((post) => Number(post.id) === Number(postId));
            if (cached) {
              cached.comment_count = (cached.comment_count || 0) + inc;
            }
          }

          setReplyTarget(card, null);
          textarea.value = '';
          if (fileInput) fileInput.value = '';
          if (preview) {
            preview.innerHTML = '';
            preview.classList.remove('is-grid');
          }
          updateRightRail();
        } catch (err) {
          console.error(err);
          alert('Failed to reply. Please try again.');
        } finally {
          setButtonLoading(button, false, 'Reply');
        }
      }

      document.addEventListener('change', (event) => {
        const fileInput = event.target.closest('.thread-form input[type="file"]');
        if (!fileInput) return;
        const form = fileInput.closest('.thread-form');
        const preview = form ? form.querySelector('.upload-preview') : null;
        const { valid, errors } = validateFiles(fileInput.files || []);
        if (errors.length) {
          alert(Array.from(new Set(errors)).join('\n'));
        }
        if (valid.length > 4) {
          alert('Only the first 4 images will be used.');
        }
        renderPreviews(valid, preview);
      });

      document.addEventListener('click', (event) => {
        const menuBtn = event.target.closest('.item-menu-btn');
        if (menuBtn) {
          event.preventDefault();
          event.stopPropagation();
          const menu = menuBtn.closest('.item-menu');
          if (menu) {
            const isOpen = menu.classList.contains('open');
            closeAllMenus(menu);
            menu.classList.toggle('open', !isOpen);
          }
          return;
        }

        const menuAction = event.target.closest('[data-menu-action]');
        if (menuAction) {
          event.preventDefault();
          event.stopPropagation();
          const menu = menuAction.closest('.item-menu');
          if (!menu) return;
          closeAllMenus();
          const itemType = menu.dataset.itemType || '';
          const itemId = Number(menu.dataset.itemId || 0);
          const action = menuAction.dataset.menuAction || '';
          if (action === 'report') {
            submitReport(itemType, itemId);
          } else if (action === 'delete') {
            const targetEl = itemType === 'comment'
              ? menu.closest('.thread-item')
              : menu.closest('.x-post-card');
            submitDelete(itemType, itemId, targetEl);
          }
          return;
        }

        if (!event.target.closest('.item-menu')) {
          closeAllMenus();
        }

        const toggleThread = event.target.closest('[data-action="toggle-comments"]');
        if (toggleThread) {
          const card = toggleThread.closest('.x-post-card');
          if (!card) return;
          const thread = card.querySelector('.x-thread');
          if (thread.hidden) {
            loadComments(card);
            thread.hidden = false;
          } else {
            thread.hidden = true;
          }
          return;
        }

        const replyToggle = event.target.closest('.thread-toggle');
        if (replyToggle) {
          const item = replyToggle.closest('.thread-item');
          if (!item) return;
          const replies = item.querySelector('.thread-replies');
          if (!replies) return;
          replies.hidden = !replies.hidden;
          updateReplyToggle(replyToggle, replies);
          return;
        }

        const replyBtn = event.target.closest('.thread-reply-btn');
        if (replyBtn) {
          const card = replyBtn.closest('.x-post-card');
          const commentEl = replyBtn.closest('.thread-item');
          if (card && commentEl) {
            setReplyTarget(card, commentEl);
            const textarea = card.querySelector('.thread-form textarea');
            if (textarea) textarea.focus();
          }
          return;
        }

        const cancelBtn = event.target.closest('.reply-cancel');
        if (cancelBtn) {
          const card = cancelBtn.closest('.x-post-card');
          if (card) setReplyTarget(card, null);
          return;
        }

        const submitBtn = event.target.closest('.comment-submit');
        if (submitBtn) {
          handleCommentSubmit(submitBtn);
          return;
        }

        const openChatBtn = event.target.closest('[data-open-chat]');
        if (openChatBtn) {
          const chatId = openChatBtn.dataset.openChat;
          if (chatId) {
            window.location.href = `${window.APP_BASE_URL}/app/chat/${chatId}`;
          }
        }

        const previewImg = event.target.closest('[data-image-preview]');
        if (previewImg) {
          openImageModal(previewImg.dataset.imagePreview || previewImg.src, previewImg.alt);
        }

        const postCard = event.target.closest('.x-post-card');
        if (postCard && postCard.dataset.type === 'post') {
          if (
            event.target.closest('.x-post-actions') ||
            event.target.closest('.thread-form') ||
            event.target.closest('.thread-actions') ||
            event.target.closest('.thread-replies') ||
            event.target.closest('.thread-item') ||
            event.target.closest('.item-menu') ||
            event.target.closest('[data-action]') ||
            event.target.closest('[data-open-chat]') ||
            event.target.closest('.x-avatar-link') ||
            event.target.closest('.x-name') ||
            event.target.closest('.x-handle')
          ) {
            return;
          }
          const postId = postCard.dataset.postId;
          if (postId) {
            openPostFocus(Number(postId));
            return;
          }
        }

        const shareBtn = event.target.closest('[data-action="copy-link"]');
        if (shareBtn) {
          const card = shareBtn.closest('.x-post-card');
          if (card) {
            const postId = card.dataset.postId;
            if (postId) {
              const link = `${window.APP_BASE_URL}/explore?post=${postId}`;
              openShareModal(link);
            }
          }
        }
      });

      if (postSubmit) {
        postSubmit.addEventListener('click', handlePostSubmit);
      }
      if (postImages) {
        postImages.addEventListener('change', () => {
          const { valid, errors } = validateFiles(postImages.files || []);
          if (errors.length) {
            postStatus.textContent = Array.from(new Set(errors)).join(' ');
          } else if (valid.length > 4) {
            postStatus.textContent = 'Only the first 4 images will be used.';
          } else {
            postStatus.textContent = '';
          }
          renderPreviews(valid, postImagePreview);
        });
      }
      if (postBody) {
        setupMentionAutocomplete(postBody, document.getElementById('postMentionSuggest'));
      }
      if (loadMoreFeed) loadMoreFeed.addEventListener('click', loadFeed);

      if (exploreSearch) {
        exploreSearch.addEventListener('input', (event) => {
          const query = String(event.target.value || '').toLowerCase().trim();
          const cards = feedList.querySelectorAll('.x-post-card');
          cards.forEach((card) => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(query) ? '' : 'none';
          });
        });
      }

      const refreshAiFormatting = () => {
        formatAiTextIn(document, { onlyFallback: true });
      };
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', refreshAiFormatting);
      } else {
        refreshAiFormatting();
      }

      if (currentUserId) {
        document.addEventListener('click', maybeRequestNotificationPermission, { once: true });
        pollNotifications();
        setInterval(pollNotifications, 30000);
      }

      const initialPostId = getQueryPostId();
      if (initialPostId) {
        openPostFocus(initialPostId, false);
      } else {
        loadFeed();
      }

      window.addEventListener('popstate', () => {
        const postId = getQueryPostId();
        if (postId) {
          openPostFocus(postId, false);
        } else if (focusMode) {
          exitPostFocus(false);
        }
      });

      setInterval(() => {
        if (cachedChats.length || cachedPosts.length) {
          updateRightRail();
        }
      }, 25000);
    })();
  </script>
</body>
</html>
