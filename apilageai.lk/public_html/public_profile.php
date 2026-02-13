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

$badgeRegistry = [
    'verified' => [
        'title' => 'Verified user',
        'icon' => [
            'webp' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/2705/512.webp',
            'gif' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/2705/512.gif',
            'alt' => '✅'
        ]
    ],
    'developer' => [
        'title' => 'Official Developer of ApilageAI app',
        'icon' => [
            'webp' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f4a1/512.webp',
            'gif' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f4a1/512.gif',
            'alt' => '💡'
        ]
    ],
    'streak_7' => [
        'title' => '7-day streak',
        'icon' => [
            'webp' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f948/512.webp',
            'gif' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f948/512.gif',
            'alt' => '🥈'
        ]
    ],
    'streak_30' => [
        'title' => '30-day streak',
        'icon' => [
            'webp' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f947/512.webp',
            'gif' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f947/512.gif',
            'alt' => '🥇'
        ]
    ],
    'streak_100' => [
        'title' => '100-day streak',
        'icon' => [
            'webp' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f48e/512.webp',
            'gif' => 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f48e/512.gif',
            'alt' => '💎'
        ]
    ]
];

// Add user IDs to assign manual badges.
$manualBadgeUsers = [
    'verified' => [1197],
    'developer' => [1197]
];
$manualBadgeLookup = [];
foreach ($manualBadgeUsers as $badgeKey => $ids) {
    $cleanIds = array_values(array_unique(array_map('intval', $ids)));
    $manualBadgeUsers[$badgeKey] = $cleanIds;
    $manualBadgeLookup[$badgeKey] = array_fill_keys($cleanIds, true);
}

function fetch_max_streak_days_for_users($db, array $userIds): array {
    $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds), function ($id) {
        return $id > 0;
    })));

    if (empty($userIds)) {
        return [];
    }

    $in = implode(',', array_fill(0, count($userIds), '?'));
    $types = str_repeat('i', count($userIds));

    $stmt = $db->prepare("
        SELECT streaks.user_id, MAX(streaks.day_count) AS max_day_count
        FROM (
            SELECT s.user_id, s.id, COUNT(e.id) AS day_count
            FROM learning_streaks s
            LEFT JOIN learning_streak_entries e ON e.streak_id = s.id
            WHERE s.user_id IN ($in)
            GROUP BY s.id
        ) AS streaks
        GROUP BY streaks.user_id
    ");
    $stmt->bind_param($types, ...$userIds);
    $stmt->execute();
    $res = $stmt->get_result();

    $maxDays = [];
    while ($row = $res->fetch_assoc()) {
        $maxDays[(int)$row['user_id']] = (int)$row['max_day_count'];
    }
    $stmt->close();

    return $maxDays;
}

function compute_user_badge_keys($userId, array $manualBadgeLookup, array $streakMaxDaysByUser): array {
    $userId = (int)$userId;
    $badges = [];

    if ($userId > 0) {
        if (!empty($manualBadgeLookup['verified'][$userId])) {
            $badges[] = 'verified';
        }
        if (!empty($manualBadgeLookup['developer'][$userId])) {
            $badges[] = 'developer';
        }
    }

    $maxDays = (int)($streakMaxDaysByUser[$userId] ?? 0);
    if ($maxDays >= 7) {
        $badges[] = 'streak_7';
    }
    if ($maxDays >= 30) {
        $badges[] = 'streak_30';
    }
    if ($maxDays >= 100) {
        $badges[] = 'streak_100';
    }

    return $badges;
}

function render_badges_html(array $badgeKeys, array $badgeRegistry): string {
    if (empty($badgeKeys)) {
        return '';
    }

    $html = '<span class="profile-badges" aria-label="Badges">';
    foreach ($badgeKeys as $badgeKey) {
        if (!isset($badgeRegistry[$badgeKey])) {
            continue;
        }
        $badge = $badgeRegistry[$badgeKey];
        $title = htmlspecialchars($badge['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $icon = $badge['icon'] ?? [];
        $webp = htmlspecialchars($icon['webp'] ?? '', ENT_QUOTES, 'UTF-8');
        $gif = htmlspecialchars($icon['gif'] ?? '', ENT_QUOTES, 'UTF-8');
        $alt = htmlspecialchars($icon['alt'] ?? '', ENT_QUOTES, 'UTF-8');

        $html .= '<span class="badge-icon" role="img" tabindex="0" data-tooltip="' . $title . '" aria-label="' . $title . '">';
        $html .= '<picture aria-hidden="true">';
        if ($webp !== '') {
            $html .= '<source srcset="' . $webp . '" type="image/webp">';
        }
        if ($gif !== '') {
            $html .= '<img src="' . $gif . '" alt="' . $alt . '" width="16" height="16" loading="lazy">';
        }
        $html .= '</picture></span>';
    }
    $html .= '</span>';

    return $html;
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

    $streakMaxDaysMap = fetch_max_streak_days_for_users($db, $userIds);

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
        $badgeKeys = compute_user_badge_keys($uid, $manualBadgeLookup, $streakMaxDaysMap);
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
            'images' => $images,
            'badges' => $badgeKeys
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

$activeStreak = null;
$streakStats = ['day_count' => 0, 'last_entry_date' => null];
$streakGraphData = [];
$streakGraphWindow = 14;
$streakGraphDays = $streakGraphWindow;
$streakGraphJson = '[]';

$activeStmt = $db->prepare(
    "SELECT id, name, started_at
     FROM learning_streaks
     WHERE user_id = ? AND status = 'active'
     ORDER BY started_at DESC
     LIMIT 1"
);
$activeStmt->bind_param('i', $userRow['id']);
$activeStmt->execute();
$activeStreak = $activeStmt->get_result()->fetch_assoc() ?: null;
$activeStmt->close();

if ($activeStreak) {
    $statsStmt = $db->prepare(
        "SELECT COUNT(*) AS day_count, MAX(entry_date) AS last_entry_date
         FROM learning_streak_entries
         WHERE streak_id = ?"
    );
    $statsStmt->bind_param('i', $activeStreak['id']);
    $statsStmt->execute();
    $statsRow = $statsStmt->get_result()->fetch_assoc();
    $statsStmt->close();

    $streakStats = [
        'day_count' => (int)($statsRow['day_count'] ?? 0),
        'last_entry_date' => $statsRow['last_entry_date'] ?? null
    ];

    $today = new DateTimeImmutable('today');
    $windowStart = $today->sub(new DateInterval('P' . ($streakGraphWindow - 1) . 'D'));
    $streakStart = !empty($activeStreak['started_at'])
        ? new DateTimeImmutable(substr($activeStreak['started_at'], 0, 10))
        : $windowStart;
    $startDate = $streakStart > $windowStart ? $streakStart : $windowStart;
    $streakGraphDays = (int)$today->diff($startDate)->days + 1;
    $startDateStr = $startDate->format('Y-m-d');
    $endDateStr = $today->format('Y-m-d');

    $entriesStmt = $db->prepare(
        "SELECT entry_date, hours, minutes, summary
         FROM learning_streak_entries
         WHERE streak_id = ? AND entry_date BETWEEN ? AND ?
         ORDER BY entry_date ASC"
    );
    $entriesStmt->bind_param('iss', $activeStreak['id'], $startDateStr, $endDateStr);
    $entriesStmt->execute();
    $entriesResult = $entriesStmt->get_result();

    $entryMap = [];
    while ($row = $entriesResult->fetch_assoc()) {
        $entryDate = $row['entry_date'] ?? null;
        if (!$entryDate) {
            continue;
        }
        $entryMap[$entryDate] = [
            'hours' => (int)($row['hours'] ?? 0),
            'minutes' => (int)($row['minutes'] ?? 0),
            'summary' => $row['summary'] ?? ''
        ];
    }
    $entriesStmt->close();

    for ($i = 0; $i < $streakGraphDays; $i++) {
        $day = $startDate->add(new DateInterval('P' . $i . 'D'));
        $dateKey = $day->format('Y-m-d');
        $entry = $entryMap[$dateKey] ?? ['hours' => 0, 'minutes' => 0, 'summary' => ''];
        $streakGraphData[] = [
            'date' => $dateKey,
            'hours' => (int)$entry['hours'],
            'minutes' => (int)$entry['minutes'],
            'summary' => (string)$entry['summary']
        ];
    }

    $streakGraphJson = json_encode(
        $streakGraphData,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
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

$badgeUserIds = [(int)$userRow['id']];
foreach ($exploreUsers as $row) {
    $badgeUserIds[] = (int)$row['id'];
}
foreach ($similarUsers as $row) {
    $badgeUserIds[] = (int)$row['id'];
}
$streakMaxDaysByUser = fetch_max_streak_days_for_users($db, $badgeUserIds);
$profileBadgeKeys = compute_user_badge_keys($userRow['id'], $manualBadgeLookup, $streakMaxDaysByUser);
$profileBadgesHtml = render_badges_html($profileBadgeKeys, $badgeRegistry);

$headerSent = headers_sent();
if (!$headerSent) {
    header('Content-Type: text/html; charset=utf-8');
}

$title = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . ' | Apilage AI';
$displayNameEscaped = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
$profileUrlEscaped = htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8');
$imageUrlEscaped = htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8');
$streakEscaped = $streakStarted ? htmlspecialchars($streakStarted, ENT_QUOTES, 'UTF-8') : '';
$hasActiveStreak = (bool)$activeStreak;
$streakName = $activeStreak['name'] ?? '';
$streakNameEscaped = htmlspecialchars($streakName, ENT_QUOTES, 'UTF-8');
$streakDayCount = (int)($streakStats['day_count'] ?? 0);
$streakDayLabel = $streakDayCount > 0 ? "Day {$streakDayCount}" : 'Just started';
$streakDayLabelEscaped = htmlspecialchars($streakDayLabel, ENT_QUOTES, 'UTF-8');
$streakDayTotalLabel = $streakDayCount . ' day' . ($streakDayCount === 1 ? '' : 's') . ' logged';
$streakDayTotalLabelEscaped = htmlspecialchars($streakDayTotalLabel, ENT_QUOTES, 'UTF-8');
$streakStartedLabel = $activeStreak && !empty($activeStreak['started_at'])
    ? date('M d, Y', strtotime($activeStreak['started_at']))
    : '';
$streakStartedLabelEscaped = htmlspecialchars($streakStartedLabel, ENT_QUOTES, 'UTF-8');
$streakLastEntryLabel = !empty($streakStats['last_entry_date'])
    ? date('M d, Y', strtotime($streakStats['last_entry_date']))
    : 'No check-in yet';
$streakLastEntryLabelEscaped = htmlspecialchars($streakLastEntryLabel, ENT_QUOTES, 'UTF-8');
$streakGraphDaysLabel = htmlspecialchars((string)$streakGraphDays, ENT_QUOTES, 'UTF-8');
$streakGraphRangeLabel = 'Last ' . $streakGraphDays . ' day' . ($streakGraphDays === 1 ? '' : 's');
$streakGraphRangeLabelEscaped = htmlspecialchars($streakGraphRangeLabel, ENT_QUOTES, 'UTF-8');
$memberSinceEscaped = htmlspecialchars($memberSince, ENT_QUOTES, 'UTF-8');
$schoolEscaped = htmlspecialchars($schoolText, ENT_QUOTES, 'UTF-8');
$coverStyleEscaped = htmlspecialchars($coverStyle, ENT_QUOTES, 'UTF-8');
$shareImage = $imageUrl !== '' ? $imageUrl : (APP_URL . '/assets/images/logo.png');
$shareImageEscaped = htmlspecialchars($shareImage, ENT_QUOTES, 'UTF-8');
$chatCount = count($publishedChats);
$imageCount = count($publishedImages);
$postCount = $chatCount + $imageCount;
$profileHandle = '@' . $profileSlug;
$profileHandleEscaped = htmlspecialchars($profileHandle, ENT_QUOTES, 'UTF-8');
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
$badgeRegistryClient = [];
foreach ($badgeRegistry as $badgeKey => $badge) {
    $badgeRegistryClient[$badgeKey] = [
        'title' => $badge['title'] ?? '',
        'icon' => $badge['icon'] ?? []
    ];
}
$badgeRegistryJson = json_encode(
    $badgeRegistryClient,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
      --primary-red: #e53e3e;
      --primary-red-hover: #c53030;
      --blue-50: #eff6ff;
      --blue-500: #3b82f6;
      --text-primary: #1a202c;
      --text-secondary: #4a5568;
      --bg-color: #ffffff;
      --sidebar-bg: #F9FAFB;
      --card-bg: #ffffff;
      --input-bg: #ffffff;
      --border-color: #ccc;
      --page-bg: var(--bg-color);
      --surface: var(--sidebar-bg);
      --text-muted: var(--text-secondary);
      --meta-bg: var(--blue-50);
      --badge-bg: var(--primary-red);
      --badge-text: #ffffff;
      --brand-red: var(--primary-red);
      --brand-blue: var(--blue-500);
      --brand-blueLight: var(--blue-50);
      --brand-dark: var(--text-primary);
      --brand-gray: var(--sidebar-bg);
      --pattern-color: transparent;
      --shadow-hard: none;
      --shadow-hard-lg: none;
    }
    [data-theme="dark"] {
      color-scheme: dark;
      --primary-red: #e53e3e;
      --primary-red-hover: #c53030;
      --blue-50: #1a2a3a;
      --blue-500: #3b82f6;
      --text-primary: #f7fafc;
      --text-secondary: #a0aec0;
      --bg-color: #1a1a1a;
      --sidebar-bg: #1a1a1a;
      --card-bg: #1e1e1e;
      --input-bg: #333;
      --border-color: #444444;
      --page-bg: var(--bg-color);
      --surface: var(--sidebar-bg);
      --text-muted: var(--text-secondary);
      --meta-bg: var(--blue-50);
      --badge-bg: var(--primary-red);
      --badge-text: #ffffff;
      --brand-red: var(--primary-red);
      --brand-blue: var(--blue-500);
      --brand-blueLight: var(--blue-50);
      --brand-dark: var(--text-primary);
      --brand-gray: var(--sidebar-bg);
      --pattern-color: transparent;
      --shadow-hard: none;
      --shadow-hard-lg: none;
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
      background: var(--surface);
      border-bottom: 1px solid var(--border-color);
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .cover-title {
      font-family: "Inter", "Segoe UI", Arial, sans-serif;
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--text-primary);
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
      border: 2px solid var(--border-color);
      background: var(--card-bg);
      position: relative;
      z-index: 6;
    }
    .avatar-fallback {
      position: absolute;
      width: 160px;
      height: 160px;
      border-radius: 50%;
      background: var(--meta-bg);
      border: 2px solid var(--border-color);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: "Inter", "Segoe UI", Arial, sans-serif;
      font-weight: 700;
      font-size: 40px;
      color: var(--text-primary);
      letter-spacing: 0.06em;
      z-index: 5;
    }
    [data-theme="dark"] .profile-avatar {
      border-color: var(--border-color);
      background: var(--card-bg);
    }
    [data-theme="dark"] .avatar-fallback {
      border-color: var(--border-color);
      background: var(--meta-bg);
      color: var(--text-primary);
    }
    .profile-name {
      font-family: "Inter", "Segoe UI", Arial, sans-serif;
      font-size: 30px;
      font-weight: 700;
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
      font-family: "Inter", "Segoe UI", Arial, sans-serif;
      font-size: 22px;
      font-weight: 700;
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
    .name-line {
      display: flex;
      align-items: center;
      gap: 4px;
      min-width: 0;
      max-width: 100%;
    }
    .name-text {
      min-width: 0;
      flex: 0 1 auto;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .profile-badges {
      display: inline-flex;
      align-items: center;
      gap: 2px;
      flex-shrink: 0;
    }
    .badge-icon {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 1em;
      height: 1em;
      cursor: pointer;
    }
    .badge-icon picture {
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .badge-icon img {
      width: 1em;
      height: 1em;
      display: block;
    }
    .badge-icon::after {
      content: attr(data-tooltip);
      position: absolute;
      left: 50%;
      bottom: calc(100% + 6px);
      transform: translateX(-50%) translateY(4px);
      background: rgba(15, 20, 25, 0.92);
      color: #ffffff;
      padding: 6px 8px;
      border-radius: 8px;
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.15s ease, transform 0.15s ease;
      z-index: 50;
    }
    .badge-icon::before {
      content: '';
      position: absolute;
      left: 50%;
      bottom: calc(100% + 2px);
      transform: translateX(-50%);
      border-width: 6px 6px 0 6px;
      border-style: solid;
      border-color: rgba(15, 20, 25, 0.92) transparent transparent transparent;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.15s ease;
      z-index: 49;
    }
    .badge-icon:hover::after,
    .badge-icon:focus::after,
    .badge-icon:hover::before,
    .badge-icon:focus::before {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }
    .badge-icon:focus {
      outline: none;
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
      font-family: "Inter", "Segoe UI", Arial, sans-serif;
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
      min-width: 0;
    }
    .user-search-name {
      font-size: 16px;
      font-weight: 700;
    }
    .user-search-meta {
      font-size: 13px;
      color: var(--text-muted);
      font-weight: 600;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
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

    /* X-like layout overrides */
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
      --x-success: #00ba7c;
      --page-bg: var(--x-bg);
      --surface: var(--x-surface);
      --card-bg: var(--x-card);
      --border-color: var(--x-border);
      --text-primary: var(--x-text);
      --text-muted: var(--x-muted);
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
      --x-success: #00ba7c;
      --page-bg: var(--x-bg);
      --surface: var(--x-surface);
      --card-bg: var(--x-card);
      --border-color: var(--x-border);
      --text-primary: var(--x-text);
      --text-muted: var(--x-muted);
    }
    body {
      background: var(--x-bg);
      color: var(--x-text);
    }
    .page-layout.x-layout {
      display: flex;
      justify-content: center;
      gap: 0;
      width: 100%;
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 12px;
      background: var(--x-bg);
    }
    .sidebar.x-nav {
      width: 250px;
      padding: 12px 12px 20px;
      border-right: 1px solid var(--x-border);
      background: var(--x-bg);
      box-shadow: none;
      gap: 16px;
    }
    .x-nav-inner {
      display: flex;
      flex-direction: column;
      gap: 16px;
      height: 100%;
    }
    .x-logo {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-size: 20px;
      font-weight: 700;
      color: var(--x-text);
      text-decoration: none;
      padding: 8px 10px;
      border-radius: 999px;
    }
    .x-logo img {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      border: 1px solid var(--x-border);
      background: var(--x-card);
    }
    .x-menu {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .x-menu-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 10px 12px;
      border-radius: 999px;
      color: var(--x-text);
      text-decoration: none;
      font-size: 16px;
      font-weight: 600;
      background: transparent;
      border: none;
      cursor: pointer;
      width: 100%;
      text-align: left;
    }
    .x-menu-item i {
      font-size: 18px;
      width: 22px;
      text-align: center;
    }
    .x-menu-item:hover {
      background: rgba(231, 233, 234, 0.1);
    }
    [data-theme="light"] .x-menu-item:hover {
      background: rgba(15, 20, 25, 0.06);
    }
    .x-post-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--x-accent);
      color: #ffffff;
      text-decoration: none;
      border-radius: 999px;
      padding: 12px 18px;
      font-weight: 700;
      margin-top: 4px;
    }
    .x-post-btn:hover {
      background: var(--x-accent-hover);
    }
    .x-post-mini {
      display: none;
      width: 48px;
      height: 48px;
      border-radius: 50%;
      align-items: center;
      justify-content: center;
      background: var(--x-accent);
      color: #ffffff;
      text-decoration: none;
      font-size: 18px;
      margin-top: 6px;
    }
    .x-post-mini:hover {
      background: var(--x-accent-hover);
    }
    .x-nav-user {
      margin-top: auto;
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 12px;
      border-radius: 999px;
      color: var(--x-text);
      cursor: pointer;
      text-decoration: none;
    }
    .x-nav-user:hover {
      background: rgba(231, 233, 234, 0.1);
    }
    [data-theme="light"] .x-nav-user:hover {
      background: rgba(15, 20, 25, 0.06);
    }
    .x-user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 1px solid var(--x-border);
      background: var(--x-card);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 700;
    }
    .x-user-fallback {
      background: var(--x-card);
      color: var(--x-text);
    }
    .x-user-meta {
      display: flex;
      flex-direction: column;
      gap: 2px;
      min-width: 0;
    }
    .x-user-name {
      font-size: 14px;
      font-weight: 700;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .x-user-handle {
      font-size: 12px;
      color: var(--x-muted);
    }
    .profile-shell.x-main {
      flex: 1;
      max-width: 720px;
      border-left: 1px solid var(--x-border);
      border-right: 1px solid var(--x-border);
      background: var(--x-bg);
      box-shadow: none;
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
    .topbar-search {
      display: none;
    }
    @media (max-width: 1200px) {
      .topbar-search {
        display: inline-flex;
      }
    }
    .topbar-title {
      display: flex;
      flex-direction: column;
      gap: 2px;
      min-width: 0;
    }
    .topbar-name {
      font-size: 16px;
      font-weight: 700;
    }
    .topbar-meta {
      font-size: 12px;
      color: var(--x-muted);
      font-weight: 600;
    }
    .profile-hero {
      display: flex;
      flex-direction: column;
      border-bottom: 1px solid var(--x-border);
    }
    .profile-cover {
      height: 200px;
      background: var(--x-card);
      border-bottom: 1px solid var(--x-border);
    }
    .profile-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 12px;
      padding: 0 16px;
      margin-top: -56px;
    }
    .profile-avatar-wrap {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 4px solid var(--x-bg);
      background: var(--x-bg);
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .profile-avatar {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
      border: 1px solid var(--x-border);
      background: var(--x-card);
    }
    .avatar-fallback {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: var(--x-card);
      border: 1px solid var(--x-border);
      font-size: 32px;
      font-weight: 700;
      color: var(--x-text);
      display: flex;
      align-items: center;
      justify-content: center;
      position: absolute;
      inset: 0;
    }
    .profile-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      padding-bottom: 10px;
    }
    .action-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid var(--x-border);
      background: transparent;
      color: var(--x-text);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
    }
    .action-icon:hover {
      background: rgba(231, 233, 234, 0.1);
    }
    [data-theme="light"] .action-icon:hover {
      background: rgba(15, 20, 25, 0.06);
    }
    .action-btn {
      border: 1px solid var(--x-border);
      background: transparent;
      color: var(--x-text);
      padding: 8px 14px;
      border-radius: 999px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
    }
    .action-btn:hover {
      background: rgba(231, 233, 234, 0.1);
    }
    [data-theme="light"] .action-btn:hover {
      background: rgba(15, 20, 25, 0.06);
    }
    .profile-details {
      padding: 12px 16px 16px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-width: 0;
    }
    .profile-name {
      margin: 0;
      font-size: 22px;
      font-weight: 800;
      text-align: left;
    }
    .profile-handle {
      color: var(--x-muted);
      font-size: 14px;
      word-break: normal;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 100%;
    }
    .profile-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      font-size: 13px;
      color: var(--x-muted);
    }
    .profile-meta span {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .profile-meta a {
      color: var(--x-accent);
      text-decoration: none;
      word-break: break-all;
    }
    .profile-stats {
      display: flex;
      gap: 16px;
      font-size: 13px;
      color: var(--x-muted);
    }
    .profile-stats strong {
      color: var(--x-text);
    }
    .profile-highlight {
      padding: 12px 16px 16px;
      border-bottom: 1px solid var(--x-border);
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .highlight-card {
      border: 1px solid var(--x-border);
      background: var(--x-card);
      border-radius: 16px;
      padding: 14px 16px;
      font-weight: 600;
      color: var(--x-text);
    }
    .streak-header-card,
    .streak-graph-card {
      border: 1px solid var(--x-border);
      background: var(--x-card);
      border-radius: 16px;
      padding: 14px 16px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .streak-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .streak-title-row {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }
    .streak-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(0, 186, 124, 0.18);
      color: var(--x-text);
      border-radius: 999px;
      padding: 4px 10px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }
    .streak-name {
      font-weight: 700;
      font-size: 14px;
      color: var(--x-text);
    }
    .streak-sub {
      font-size: 12px;
      color: var(--x-muted);
      font-weight: 600;
    }
    .streak-stat {
      display: flex;
      flex-direction: column;
      gap: 2px;
      font-size: 12px;
      color: var(--x-muted);
      font-weight: 600;
    }
    .streak-stat strong {
      color: var(--x-text);
      font-size: 14px;
    }
    .streak-graph-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-wrap: wrap;
    }
    .streak-graph-title {
      font-weight: 700;
      font-size: 15px;
    }
    .streak-graph-subtitle {
      font-size: 12px;
      color: var(--x-muted);
      font-weight: 600;
    }
    .streak-graph-total {
      font-size: 12px;
      color: var(--x-muted);
      font-weight: 600;
    }
    .streak-graph {
      display: grid;
      grid-template-columns: repeat(var(--streak-graph-days, 14), minmax(18px, 1fr));
      align-items: end;
      gap: 8px;
      height: 150px;
      padding-top: 6px;
      overflow-x: auto;
    }
    .streak-bar {
      border: none;
      background: transparent;
      padding: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      color: var(--x-muted);
    }
    .streak-bar-fill {
      width: 100%;
      max-width: 18px;
      border-radius: 999px;
      background: var(--x-accent);
      transition: height 0.2s ease, background 0.2s ease;
      min-height: 6px;
    }
    .streak-bar.is-zero .streak-bar-fill {
      background: rgba(29, 155, 240, 0.25);
    }
    .streak-bar.is-active .streak-bar-fill {
      background: var(--x-success);
    }
    .streak-bar-label {
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
    }
    .streak-summary {
      border-top: 1px dashed var(--x-border);
      padding-top: 12px;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .streak-summary-title {
      font-weight: 700;
      font-size: 14px;
    }
    .streak-summary-meta {
      font-size: 12px;
      color: var(--x-muted);
      font-weight: 600;
    }
    .streak-summary-text {
      margin: 0;
      font-size: 13px;
      color: var(--x-text);
      line-height: 1.5;
      white-space: pre-wrap;
    }
    @media (max-width: 640px) {
      .streak-graph {
        height: 120px;
        gap: 6px;
      }
    }
    .profile-tabs {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      border-bottom: 1px solid var(--x-border);
    }
    .profile-tab {
      background: transparent;
      border: none;
      color: var(--x-muted);
      font-weight: 700;
      padding: 14px 16px;
      cursor: pointer;
      text-align: center;
    }
    .profile-tab.is-active {
      color: var(--x-text);
      border-bottom: 3px solid var(--x-accent);
    }
    .tab-panel {
      padding: 12px 0 0;
    }
    .tab-panel:not(.is-active) {
      display: none;
    }
    .search-row {
      padding: 0 16px 8px;
    }
    .search-input-wrap {
      border: 1px solid var(--x-border);
      background: var(--x-card);
      box-shadow: none;
      border-radius: 999px;
    }
    .search-input {
      color: var(--x-text);
    }
    .search-status {
      color: var(--x-muted);
    }
    .chat-grid {
      display: flex;
      flex-direction: column;
    }
    .chat-card {
      border: none;
      border-bottom: 1px solid var(--x-border);
      border-radius: 0;
      padding: 14px 16px;
      background: transparent;
      box-shadow: none;
      transition: background 0.15s ease;
    }
    .chat-card:hover {
      transform: none;
      background: rgba(231, 233, 234, 0.06);
    }
    [data-theme="light"] .chat-card:hover {
      background: rgba(15, 20, 25, 0.04);
    }
    .chat-title {
      font-size: 15px;
      font-weight: 700;
    }
    .chat-date {
      font-size: 12px;
      color: var(--x-muted);
      font-weight: 600;
    }
    .image-grid {
      padding: 8px 16px 16px;
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .image-card {
      border: 1px solid var(--x-border);
      border-radius: 14px;
      box-shadow: none;
      background: var(--x-card);
    }
    .image-card img {
      background: var(--x-surface);
    }
    .image-caption {
      color: var(--x-muted);
    }
    .empty-state {
      border: 1px dashed var(--x-border);
      background: var(--x-card);
      color: var(--x-muted);
      margin: 12px 16px 16px;
    }
    .right-rail {
      width: 340px;
      padding: 12px 16px 20px;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
      background: var(--x-bg);
    }
    .x-search {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border: 1px solid var(--x-border);
      border-radius: 999px;
      background: var(--x-card);
      color: var(--x-muted);
      cursor: pointer;
      margin-bottom: 16px;
    }
    .x-search input {
      border: none;
      background: transparent;
      color: var(--x-muted);
      width: 100%;
      font-size: 14px;
      outline: none;
      cursor: pointer;
    }
    .x-card {
      border: 1px solid var(--x-border);
      background: var(--x-card);
      border-radius: 16px;
      padding: 14px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 16px;
    }
    .x-card-title {
      font-size: 16px;
      font-weight: 800;
    }
    .x-card-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }
    .x-card-user {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }
    .x-card-user > div {
      min-width: 0;
    }
    .x-card-user img,
    .x-card-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 1px solid var(--x-border);
      background: var(--x-card);
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 12px;
    }
    .x-card-name {
      font-size: 14px;
      font-weight: 700;
      color: var(--x-text);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .x-card-sub {
      font-size: 12px;
      color: var(--x-muted);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
    }
    .x-follow-btn {
      border: 1px solid var(--x-border);
      background: transparent;
      color: var(--x-text);
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      text-decoration: none;
      white-space: nowrap;
    }
    .x-follow-btn:hover {
      background: rgba(231, 233, 234, 0.1);
    }
    [data-theme="light"] .x-follow-btn:hover {
      background: rgba(15, 20, 25, 0.06);
    }
    .x-trend-item {
      display: flex;
      flex-direction: column;
      gap: 4px;
      text-decoration: none;
      color: var(--x-text);
      padding: 8px 0;
      border-top: 1px solid var(--x-border);
    }
    .x-trend-item:first-of-type {
      border-top: none;
    }
    .x-trend-meta {
      font-size: 12px;
      color: var(--x-muted);
    }
    .mobile-dock {
      position: fixed;
      left: 0;
      right: 0;
      bottom: 0;
      padding: 10px 24px;
      border-top: 1px solid var(--x-border);
      background: var(--x-bg);
      display: none;
      justify-content: space-between;
      z-index: 50;
    }
    .dock-item {
      color: var(--x-text);
      font-size: 18px;
      min-width: 44px;
    }
    @media (max-width: 1200px) {
      .right-rail {
        display: none;
      }
    }
    @media (max-width: 900px) {
      body {
        padding-bottom: 0;
      }
      .sidebar.x-nav {
        width: 72px;
        align-items: center;
        display: flex;
      }
      .x-nav-inner {
        align-items: center;
      }
      .x-menu {
        align-items: center;
      }
      .x-logo span,
      .x-menu-item span,
      .x-post-btn,
      .x-user-meta {
        display: none;
      }
      .x-post-mini {
        display: inline-flex;
      }
      .x-logo {
        justify-content: center;
        width: 44px;
        height: 44px;
        padding: 0;
      }
      .x-menu-item {
        justify-content: center;
        width: 44px;
        height: 44px;
        padding: 0;
        margin: 0;
      }
      .x-menu-item i {
        width: auto;
      }
    }
    @media (max-width: 700px) {
      .page-layout.x-layout {
        padding: 0;
      }
      .sidebar.x-nav {
        display: flex;
        width: 64px;
        padding: 10px 6px 16px;
      }
      .profile-shell.x-main {
        border-right: none;
      }
      .profile-header {
        flex-direction: column;
        align-items: flex-start;
      }
      .profile-actions {
        width: 100%;
        justify-content: flex-start;
      }
      .profile-cover {
        height: 160px;
      }
      .mobile-dock {
        display: none;
      }
    }

    /* App-style sidebar override */
    body {
      overflow: hidden;
    }
    .page-layout.x-layout {
      justify-content: flex-start;
      max-width: 100%;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      width: 100%;
      height: 100vh;
      overflow: hidden;
      align-items: stretch;
      --sidebar-width: 260px;
    }
    body.sidebar-collapsed .page-layout.x-layout {
      --sidebar-width: 70px;
    }
    .profile-shell.x-main {
      border-left: none;
      height: 100vh;
      overflow-y: auto;
      margin-left: 0;
      min-width: 0;
      max-width: 100%;
      width: 100%;
      flex: 1 1 0%;
    }
    .right-rail {
      height: 100vh;
      overflow: hidden;
      flex: 0 0 340px;
    }
    .sidebar.app-sidebar {
      background: linear-gradient(180deg, var(--sidebar-bg) 0%, rgba(249, 250, 251, 0.98) 100%);
      border-right: 1px solid rgba(0, 0, 0, 0.06);
      box-shadow: 4px 0 24px rgba(0, 0, 0, 0.03);
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
      transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1),
        width 0.25s cubic-bezier(0.4, 0, 0.2, 1),
        min-width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      overflow: hidden;
      overflow-x: hidden;
      z-index: 200;
    }
    [data-theme="dark"] .sidebar.app-sidebar {
      background: linear-gradient(180deg, var(--sidebar-bg) 0%, rgba(26, 26, 26, 0.98) 100%);
      border-right: 1px solid rgba(255, 255, 255, 0.06);
      box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
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
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      cursor: pointer;
      transition: all 0.15s ease;
      text-decoration: none;
      outline: none;
      min-width: 0;
      max-width: 100%;
    }
    .sidebar.app-sidebar .sidebar-but:hover {
      background: var(--gray-light);
      color: var(--text-primary);
    }
    .sidebar.app-sidebar .sidebar-but:active {
      background: var(--border-color);
    }
    .sidebar.app-sidebar .sidebar-but.active {
      background: var(--red-50);
      color: var(--primary-red);
      font-weight: 600;
    }
    .sidebar.app-sidebar .sidebar-but .sidebar-but-icon {
      width: 18px;
      height: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      color: inherit;
      transition: color 0.15s ease;
    }
    .sidebar.app-sidebar .sidebar-but:hover .sidebar-but-icon {
      color: var(--primary-red);
    }
    .sidebar.app-sidebar .sidebar-but-shortcut {
      margin-left: auto;
      font-size: 12px;
      padding: 4px 8px;
      border-radius: 999px;
      border: 1px solid var(--border-color);
      color: var(--text-secondary);
      background: var(--card-bg);
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
      transition: all 0.2s ease;
      font-size: 14px;
      font-weight: 500;
    }
    .sidebar.app-sidebar .sidebar-minimize-btn:hover {
      background: var(--sidebar-hover);
      color: var(--primary-red);
      border-color: var(--primary-red);
    }
    .sidebar.app-sidebar.hidden .sidebar-minimize-btn {
      justify-content: center;
    }
    .sidebar.app-sidebar.hidden .sidebar-minimize-btn .minimize-text {
      display: none;
    }
    .sidebar.app-sidebar .sidebar-footer-userinfo {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 16px;
      cursor: pointer;
      transition: background 0.15s ease;
      background-color: #f9fafb;
      border-radius: 18px;
    }
    .sidebar.app-sidebar .sidebar-footer-userinfo:hover {
      background: var(--gray-light);
    }
    [data-theme="dark"] .sidebar.app-sidebar .sidebar-footer-userinfo {
      background: var(--sidebar-bg);
    }
    [data-theme="dark"] .sidebar.app-sidebar .sidebar-footer-userinfo:hover {
      background: var(--sidebar-hover);
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
    .sidebar.app-sidebar .credit-bar-container {
      background: var(--border-color);
      height: 4px;
      border-radius: 4px;
      overflow: hidden;
      margin-top: 6px;
    }
    .sidebar.app-sidebar .credit-bar-fill {
      background: var(--primary-red);
      height: 100%;
      border-radius: 4px;
      transition: width 0.3s ease;
    }
    .sidebar.app-sidebar .sidebar-but.new-chat-btn {
      background: var(--primary-red);
      color: #ffffff;
      border: none;
      margin-bottom: 4px;
    }
    .sidebar.app-sidebar .sidebar-but.new-chat-btn .sidebar-but-icon,
    .sidebar.app-sidebar .sidebar-but.new-chat-btn i {
      color: #ffffff !important;
    }
    .sidebar.app-sidebar .sidebar-but.new-chat-btn:hover {
      background: var(--primary-red-hover);
    }
    .sidebar.app-sidebar .sidebar-but.new-chat-btn .sidebar-but-shortcut {
      background: rgba(255, 255, 255, 0.2);
      border-color: rgba(255, 255, 255, 0.3);
      color: #ffffff;
    }
    .sidebar-backn {
      color: var(--text-primary);
      background: transparent;
      border: none;
      font-size: 1.25rem;
      cursor: pointer;
      width: 40px;
      height: 40px;
      display: none;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: background 0.2s ease;
    }
    .sidebar-backn:hover {
      background: var(--sidebar-hover);
    }
    [data-theme="dark"] .sidebar-backn {
      color: #ffffff;
    }
    @media (min-width: 956px) {
      .sidebar.app-sidebar.hidden {
        width: 70px;
        min-width: 70px;
      }
      .sidebar.app-sidebar.hidden .sidebar-but-text,
      .sidebar.app-sidebar.hidden .sidebar-but-shortcut {
        display: none;
      }
      .sidebar.app-sidebar.hidden .sidebar-but {
        justify-content: center;
        padding: 10px;
        position: relative;
      }
      .sidebar.app-sidebar.hidden .sidebar-but:hover::after {
        content: attr(title);
        position: absolute;
        left: calc(100% + 12px);
        top: 50%;
        transform: translateY(-50%);
        padding: 8px 14px;
        background: rgba(0, 0, 0, 0.9);
        color: #ffffff;
        border-radius: 8px;
        white-space: nowrap;
        font-size: 14px;
        font-weight: 500;
        z-index: 10000;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        animation: tooltipFadeIn 0.2s ease;
      }
      .sidebar.app-sidebar.hidden .sidebar-but:hover::before {
        content: '';
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        margin-left: 6px;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 5px 6px 5px 0;
        border-color: transparent rgba(0, 0, 0, 0.9) transparent transparent;
        z-index: 10000;
        pointer-events: none;
      }
      @keyframes tooltipFadeIn {
        from {
          opacity: 0;
          transform: translateY(-50%) translateX(-5px);
        }
        to {
          opacity: 1;
          transform: translateY(-50%) translateX(0);
        }
      }
      .sidebar.app-sidebar.hidden .user-details {
        display: none;
      }
      .sidebar.app-sidebar.hidden .sidebar-footer-userinfo {
        justify-content: center;
        padding: 6px;
      }
    }
    @media (max-width: 1200px) {
      .page-layout.x-layout {
        --sidebar-width: 80px;
      }
      .sidebar.app-sidebar .sidebar-but-text,
      .sidebar.app-sidebar .sidebar-but-shortcut {
        display: none;
      }
      .sidebar.app-sidebar .sidebar-but {
        justify-content: center;
        padding: 10px;
      }
      .sidebar.app-sidebar .sidebar-footer-userinfo {
        justify-content: center;
        padding: 6px;
      }
      .sidebar.app-sidebar .user-details {
        display: none;
      }
      .sidebar.app-sidebar .sidebar-minimize-btn {
        justify-content: center;
      }
      .sidebar.app-sidebar .sidebar-minimize-btn .minimize-text {
        display: none;
      }
    }
    .sidebar-toggle-btn {
      display: none;
    }
    @media (max-width: 955px) {
      .page-layout.x-layout {
        --sidebar-width: 64px;
      }
      body.sidebar-collapsed .page-layout.x-layout {
        --sidebar-width: 0px;
      }
      .sidebar.app-sidebar.hidden {
        transform: translateX(-100%);
      }
      .sidebar-toggle-btn {
        display: inline-flex;
      }
    }
    @media (max-width: 768px) {
      .page-layout.x-layout {
        --sidebar-width: 56px;
        padding-left: var(--sidebar-width);
      }
      body.sidebar-collapsed .page-layout.x-layout {
        --sidebar-width: 0px;
      }
      .sidebar.app-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        height: 100dvh;
        transform: translateX(0);
        z-index: 400;
      }
      .sidebar.app-sidebar .sidebar-but-text,
      .sidebar.app-sidebar .sidebar-but-shortcut {
        display: none;
      }
      .sidebar.app-sidebar .sidebar-but {
        justify-content: center;
        padding: 10px;
      }
      .sidebar.app-sidebar .sidebar-footer-userinfo {
        justify-content: center;
        padding: 6px;
      }
      .sidebar.app-sidebar .user-details {
        display: none;
      }
      .sidebar.app-sidebar .sidebar-minimize-btn {
        display: none;
      }
      .sidebar-backn {
        display: none;
      }
      .sidebar-toggle-btn {
        display: inline-flex;
      }
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
              <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/270f_fe0f/512.gif" alt="✏" width="20" height="20">
            </picture>
          </span>
          <span class="sidebar-but-text">New chat</span>
        </a>

        <a class="sidebar-but" href="<?php echo APP_URL; ?>/app" title="Conversations">
          <span class="sidebar-but-icon" aria-hidden="true">💬</span>
          <span class="sidebar-but-text">Conversations</span>
        </a>

        <a class="sidebar-but" href="<?php echo APP_URL; ?>/app" title="Share with friends">
          <span class="sidebar-but-icon" aria-hidden="true">
            <picture>
              <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f680/512.webp" type="image/webp">
              <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f680/512.gif" alt="🚀" width="20" height="20">
            </picture>
          </span>
          <span class="sidebar-but-text">Share with friends</span>
        </a>

        <a class="sidebar-but" href="<?php echo APP_URL; ?>/app" title="Mind map">
          <span class="sidebar-but-icon" aria-hidden="true">🧠</span>
          <span class="sidebar-but-text">Mind map</span>
        </a>

        <a class="sidebar-but" href="<?php echo APP_URL; ?>/app" title="MCQ game">
          <span class="sidebar-but-icon" aria-hidden="true">🎮</span>
          <span class="sidebar-but-text">MCQ game</span>
        </a>

        <a class="sidebar-but" href="<?php echo APP_URL; ?>/images" title="Image Gallery">
          <span class="sidebar-but-icon" aria-hidden="true">
            <picture>
              <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f4f8/512.webp" type="image/webp">
              <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f4f8/512.gif" alt="📸" width="20" height="20">
            </picture>
          </span>
          <span class="sidebar-but-text">Image Gallery</span>
        </a>

        <a class="sidebar-but active" href="<?php echo $profileUrlEscaped; ?>" title="Public Profile" aria-current="page">
          <span class="sidebar-but-icon" aria-hidden="true">👤</span>
          <span class="sidebar-but-text">Public Profile</span>
        </a>
      </div>

      <div class="sidebar-footer">
        <button id="sidebarMinimize" class="sidebar-minimize-btn" type="button" aria-label="Minimize sidebar">
          <i class="fa fa-bullseye" aria-hidden="true"></i>
          <span class="minimize-text">Focused</span>
        </button>
        <div class="sidebar-footer-userinfo" id="sidebarUserInfo">
          <div class="user-avatar">
            <img
              src="<?php echo $imageUrlEscaped !== '' ? $imageUrlEscaped : (APP_URL . '/assets/images/user.png'); ?>"
              alt="<?php echo $displayNameEscaped; ?> Avatar"
              onerror="this.onerror=null;this.src='<?php echo APP_URL; ?>/assets/images/user.png';"
            />
          </div>
          <div class="user-details">
            <div class="user-name name-line">
              <span class="name-text"><?php echo $displayNameEscaped; ?></span>
              <?php echo $profileBadgesHtml; ?>
            </div>
            <div class="user-credit-text">Public profile</div>
            <div class="credit-bar-container">
              <div class="credit-bar-fill" style="width: 0%;"></div>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <main class="profile-shell x-main">
      <header class="profile-topbar">
        <button class="topbar-btn" type="button" onclick="history.back()" aria-label="Go back">
          <i class="fa-solid fa-arrow-left"></i>
        </button>
        <button id="toggleSidebar" class="topbar-btn sidebar-toggle-btn" type="button" aria-label="Toggle sidebar">
          <i class="fa-solid fa-bars"></i>
        </button>
        <button class="topbar-btn topbar-search" type="button" data-open-user-search aria-label="Search users">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <div class="topbar-title">
          <div class="topbar-name name-line">
            <span class="name-text"><?php echo $displayNameEscaped; ?></span>
            <?php echo $profileBadgesHtml; ?>
          </div>
          <div class="topbar-meta"><?php echo $postCount; ?> posts</div>
        </div>
      </header>

      <section class="profile-hero">
        <div class="profile-cover"></div>
        <div class="profile-header">
          <div class="profile-avatar-wrap">
            <div class="avatar-fallback" aria-hidden="true"><?php echo $initialsEscaped; ?></div>
            <?php if ($imageUrlEscaped !== ''): ?>
              <img class="profile-avatar" src="<?php echo $imageUrlEscaped; ?>" alt="<?php echo $displayNameEscaped; ?>" onerror="this.style.display='none';">
            <?php endif; ?>
          </div>
          <div class="profile-actions">
            <a class="action-icon" href="<?php echo htmlspecialchars($twitterShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Share on X">
              <i class="fa-brands fa-x-twitter"></i>
            </a>
            <a class="action-icon" href="<?php echo htmlspecialchars($facebookShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Share on Facebook">
              <i class="fa-brands fa-facebook"></i>
            </a>
            <a class="action-icon" href="<?php echo htmlspecialchars($whatsappShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp">
              <i class="fa-brands fa-whatsapp"></i>
            </a>
            <button class="action-btn" type="button" id="copyProfileLink">
              <i class="fa-solid fa-link"></i>
              Copy link
            </button>
          </div>
        </div>

        <div class="profile-details">
          <h1 class="profile-name name-line">
            <span class="name-text"><?php echo $displayNameEscaped; ?></span>
            <?php echo $profileBadgesHtml; ?>
          </h1>
          <div class="profile-handle"><?php echo $profileHandleEscaped; ?></div>
          <div class="profile-meta">
            <span><i class="fa-solid fa-school"></i><?php echo $schoolEscaped; ?></span>
            <span><i class="fa-solid fa-calendar-days"></i>Joined <?php echo $memberSinceEscaped; ?></span>
            <span><i class="fa-solid fa-link"></i><a href="<?php echo $profileUrlEscaped; ?>"><?php echo $profileUrlEscaped; ?></a></span>
          </div>
          <div class="profile-stats">
            <span><strong><?php echo $chatCount; ?></strong> Chats</span>
            <span><strong><?php echo $imageCount; ?></strong> Images</span>
            <span><strong><?php echo $postCount; ?></strong> Posts</span>
          </div>
        </div>
      </section>

      <section class="profile-highlight">
        <?php if ($hasActiveStreak): ?>
          <div class="streak-header-card">
            <div class="streak-header">
              <div>
                <div class="streak-title-row">
                  <span class="streak-badge"><i class="fa-solid fa-fire"></i> Active streak</span>
                  <?php if ($streakNameEscaped !== ''): ?>
                    <span class="streak-name"><?php echo $streakNameEscaped; ?></span>
                  <?php endif; ?>
                </div>
                <?php if ($streakStartedLabelEscaped !== ''): ?>
                  <div class="streak-sub"><?php echo $streakDayLabelEscaped; ?> · Started <?php echo $streakStartedLabelEscaped; ?></div>
                <?php else: ?>
                  <div class="streak-sub"><?php echo $streakDayLabelEscaped; ?></div>
                <?php endif; ?>
              </div>
              <div class="streak-stat">
                <span>Last check-in</span>
                <strong><?php echo $streakLastEntryLabelEscaped; ?></strong>
              </div>
            </div>
          </div>

          <div class="streak-graph-card">
            <div class="streak-graph-header">
              <div>
                <div class="streak-graph-title">Study Hours</div>
                <div class="streak-graph-subtitle"><?php echo $streakGraphRangeLabelEscaped; ?></div>
              </div>
              <div class="streak-graph-total"><?php echo $streakDayTotalLabelEscaped; ?></div>
            </div>
            <div class="streak-graph" id="streakGraph" role="list" aria-label="Study hours by day" style="--streak-graph-days: <?php echo $streakGraphDaysLabel; ?>"></div>
            <div class="streak-summary" id="streakSummaryPanel">
              <div class="streak-summary-title" id="streakSummaryTitle">Select a day</div>
              <div class="streak-summary-meta" id="streakSummaryMeta"></div>
              <p class="streak-summary-text" id="streakSummaryText">Click a bar to see what <?php echo $displayNameEscaped; ?> studied.</p>
            </div>
            <script id="streakGraphData" type="application/json"><?php echo $streakGraphJson; ?></script>
          </div>
        <?php else: ?>
          <div class="highlight-card">Learning streak not started</div>
        <?php endif; ?>
      </section>

      <div class="profile-tabs" role="tablist">
        <button class="profile-tab is-active" type="button" data-tab="chats" role="tab" aria-controls="tab-chats" aria-selected="true">Posts</button>
        <button class="profile-tab" type="button" data-tab="images" role="tab" aria-controls="tab-images" aria-selected="false">Media</button>
      </div>

      <section class="tab-panel is-active" id="tab-chats" role="tabpanel">
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

      <section class="tab-panel" id="tab-images" role="tabpanel">
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
    </main>

    <aside class="right-rail">
      <div class="x-search" data-open-user-search role="button" tabindex="0" aria-haspopup="dialog" aria-controls="userSearchModal">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search" aria-label="Search public profiles" readonly>
      </div>

      <div class="x-card">
        <div class="x-card-title">You might like</div>
        <?php if (!empty($exploreUsers)): ?>
          <?php $exploreShort = array_slice($exploreUsers, 0, 3); ?>
          <?php foreach ($exploreShort as $row): ?>
            <?php
              $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
              if ($name === '') {
                  $name = 'ApilageAI User';
              }
              $nameEscaped = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
              $slugValue = !empty($row['public_profile_username']) ? $row['public_profile_username'] : $row['public_profile_token'];
              $profileLink = APP_URL . '/' . $slugValue;
              $profileLinkEscaped = htmlspecialchars($profileLink, ENT_QUOTES, 'UTF-8');
              $handleValue = '@' . $slugValue;
              $handleEscaped = htmlspecialchars($handleValue, ENT_QUOTES, 'UTF-8');
              $rawAvatar = trim((string)($row['image'] ?? ''));
              $hasAvatar = $rawAvatar !== '' && stripos($rawAvatar, 'array') === false;
              $avatarUrl = $hasAvatar ? user_image_url($rawAvatar) : '';
              $avatarEscaped = htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8');
              $avatarInitials = htmlspecialchars($getInitials($name), ENT_QUOTES, 'UTF-8');
              $badgeKeys = compute_user_badge_keys($row['id'], $manualBadgeLookup, $streakMaxDaysByUser);
              $badgesHtml = render_badges_html($badgeKeys, $badgeRegistry);
            ?>
            <div class="x-card-item">
              <div class="x-card-user">
                <?php if ($avatarEscaped !== ''): ?>
                  <img src="<?php echo $avatarEscaped; ?>" alt="<?php echo $nameEscaped; ?>">
                <?php else: ?>
                  <div class="x-card-avatar"><?php echo $avatarInitials; ?></div>
                <?php endif; ?>
                <div>
                  <div class="x-card-name name-line">
                    <span class="name-text"><?php echo $nameEscaped; ?></span>
                    <?php echo $badgesHtml; ?>
                  </div>
                  <div class="x-card-sub"><?php echo $handleEscaped; ?></div>
                </div>
              </div>
              <a class="x-follow-btn" href="<?php echo $profileLinkEscaped; ?>">View</a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="x-card-sub">No public profiles yet.</div>
        <?php endif; ?>
      </div>

      <div class="x-card">
        <div class="x-card-title">Similar Schools</div>
        <?php if (!empty($similarUsers)): ?>
          <?php $similarShort = array_slice($similarUsers, 0, 3); ?>
          <?php foreach ($similarShort as $row): ?>
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
              $badgeKeys = compute_user_badge_keys($row['id'], $manualBadgeLookup, $streakMaxDaysByUser);
              $badgesHtml = render_badges_html($badgeKeys, $badgeRegistry);
            ?>
            <div class="x-card-item">
              <div class="x-card-user">
                <?php if ($avatarEscaped !== ''): ?>
                  <img src="<?php echo $avatarEscaped; ?>" alt="<?php echo $nameEscaped; ?>">
                <?php else: ?>
                  <div class="x-card-avatar"><?php echo $avatarInitials; ?></div>
                <?php endif; ?>
                <div>
                  <div class="x-card-name name-line">
                    <span class="name-text"><?php echo $nameEscaped; ?></span>
                    <?php echo $badgesHtml; ?>
                  </div>
                  <div class="x-card-sub"><?php echo $schoolRowEscaped; ?></div>
                </div>
              </div>
              <a class="x-follow-btn" href="<?php echo $profileLinkEscaped; ?>">View</a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="x-card-sub">
            <?php echo ($schoolRaw === '' || (int)($userRow['not_student'] ?? 0) === 1) ? 'No similar school matches.' : 'No similar school matches yet.'; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="x-card">
        <div class="x-card-title">What's happening</div>
        <?php if (!empty($publishedChats)): ?>
          <?php $chatShort = array_slice($publishedChats, 0, 3); ?>
          <?php foreach ($chatShort as $chat): ?>
            <?php
              $chatTitle = trim((string)($chat['title'] ?? ''));
              if ($chatTitle === '') {
                  $chatTitle = 'Untitled Chat';
              }
              $chatTitleEscaped = htmlspecialchars($chatTitle, ENT_QUOTES, 'UTF-8');
              $chatDate = $chat['published_at'] ?? $chat['created_at'] ?? null;
              $chatDateLabel = $chatDate ? date('M d, Y', strtotime($chatDate)) : 'Date unknown';
              $chatDateEscaped = htmlspecialchars($chatDateLabel, ENT_QUOTES, 'UTF-8');
              $chatUrl = APP_URL . '/app/chat/' . (int)$chat['conversation_id'];
              $chatUrlEscaped = htmlspecialchars($chatUrl, ENT_QUOTES, 'UTF-8');
            ?>
            <a class="x-trend-item" href="<?php echo $chatUrlEscaped; ?>">
              <div class="x-card-name"><?php echo $chatTitleEscaped; ?></div>
              <div class="x-trend-meta">Published <?php echo $chatDateEscaped; ?></div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="x-card-sub">No published chats yet.</div>
        <?php endif; ?>
      </div>
    </aside>
  </div>

  <nav class="mobile-dock" aria-label="Primary">
    <a class="dock-item" href="<?php echo APP_URL; ?>/app" aria-label="Home">
      <i class="fa-solid fa-house"></i>
    </a>
    <button class="dock-item" type="button" data-open-user-search aria-haspopup="dialog" aria-controls="userSearchModal" aria-label="Explore">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>
    <a class="dock-item" href="<?php echo APP_URL; ?>/images" aria-label="Images">
      <i class="fa-solid fa-image"></i>
    </a>
    <a class="dock-item" href="<?php echo $profileUrlEscaped; ?>" aria-label="Profile">
      <i class="fa-solid fa-user"></i>
    </a>
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
    const badgeRegistry = <?php echo $badgeRegistryJson ?: '{}'; ?>;

    function renderBadges(badgeKeys) {
      if (!Array.isArray(badgeKeys) || badgeKeys.length === 0) {
        return null;
      }
      const wrap = document.createElement('span');
      wrap.className = 'profile-badges';

      badgeKeys.forEach((badgeKey) => {
        const badge = badgeRegistry[badgeKey];
        if (!badge) return;
        const icon = badge.icon || {};
        const span = document.createElement('span');
        span.className = 'badge-icon';
        span.setAttribute('role', 'img');
        span.setAttribute('tabindex', '0');
        span.setAttribute('aria-label', badge.title || '');
        if (badge.title) {
          span.dataset.tooltip = badge.title;
        }

        const picture = document.createElement('picture');
        picture.setAttribute('aria-hidden', 'true');

        if (icon.webp) {
          const source = document.createElement('source');
          source.srcset = icon.webp;
          source.type = 'image/webp';
          picture.appendChild(source);
        }

        if (icon.gif) {
          const img = document.createElement('img');
          img.src = icon.gif;
          img.alt = icon.alt || '';
          img.width = 16;
          img.height = 16;
          img.loading = 'lazy';
          picture.appendChild(img);
        }

        span.appendChild(picture);
        wrap.appendChild(span);
      });

      return wrap.childNodes.length ? wrap : null;
    }

    const copyBtn = document.getElementById('copyProfileLink');
    if (copyBtn) {
      const originalCopyHtml = copyBtn.innerHTML;
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
          copyBtn.textContent = 'Copied';
          setTimeout(() => { copyBtn.innerHTML = originalCopyHtml; }, 1600);
        } catch (e) {
          copyBtn.textContent = 'Copy failed';
          setTimeout(() => { copyBtn.innerHTML = originalCopyHtml; }, 1600);
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

    const sidebarEl = document.getElementById('sidebar');
    const sidebarMinimize = document.getElementById('sidebarMinimize');
    const sidebarBack = document.getElementById('sidebarback');
    const sidebarToggleBtn = document.getElementById('toggleSidebar');

    const toggleSidebarHidden = () => {
      if (!sidebarEl) return;
      const isHidden = sidebarEl.classList.toggle('hidden');
      document.body.classList.toggle('sidebar-collapsed', isHidden);
    };

    if (sidebarEl) {
      document.body.classList.toggle('sidebar-collapsed', sidebarEl.classList.contains('hidden'));
    }

    if (sidebarMinimize) {
      sidebarMinimize.addEventListener('click', toggleSidebarHidden);
    }
    if (sidebarBack) {
      sidebarBack.addEventListener('click', toggleSidebarHidden);
    }
    if (sidebarToggleBtn) {
      sidebarToggleBtn.addEventListener('click', toggleSidebarHidden);
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
        name.className = 'user-search-name name-line';
        const nameText = document.createElement('span');
        nameText.className = 'name-text';
        nameText.textContent = user.name || 'ApilageAI User';
        name.appendChild(nameText);
        const badgesEl = renderBadges(user.badges);
        if (badgesEl) {
          name.appendChild(badgesEl);
        }

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
        btn.addEventListener('keydown', (event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openSearchModal();
          }
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

    const tabButtons = Array.from(document.querySelectorAll('.profile-tab'));
    const tabPanels = Array.from(document.querySelectorAll('.tab-panel'));
    if (tabButtons.length && tabPanels.length) {
      tabButtons.forEach((tab) => {
        tab.addEventListener('click', () => {
          const target = tab.getAttribute('data-tab');
          tabButtons.forEach((btn) => {
            const active = btn === tab;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
          });
          tabPanels.forEach((panel) => {
            const isMatch = panel.id === `tab-${target}`;
            panel.classList.toggle('is-active', isMatch);
          });
        });
      });
    }

    const streakDataEl = document.getElementById('streakGraphData');
    const streakGraph = document.getElementById('streakGraph');
    const streakSummaryTitle = document.getElementById('streakSummaryTitle');
    const streakSummaryMeta = document.getElementById('streakSummaryMeta');
    const streakSummaryText = document.getElementById('streakSummaryText');

    if (streakDataEl && streakGraph) {
      let streakData = [];
      try {
        streakData = JSON.parse(streakDataEl.textContent || '[]');
      } catch (err) {
        streakData = [];
      }

      if (Array.isArray(streakData) && streakData.length) {
        const clampNumber = (value, min, max) => {
          const num = Number(value);
          if (Number.isNaN(num)) return min;
          return Math.min(Math.max(num, min), max);
        };

        const sanitizeSummaryText = (text) => {
          let safe = String(text || '').trim();
          if (!safe) return '';
          try {
            safe = safe.replace(/[^\p{L}\p{N}\s]/gu, '');
          } catch (err) {
            safe = safe.replace(/[^A-Za-z0-9\s]/g, '');
          }
          safe = safe.replace(/\s+/g, ' ').trim();
          if (!safe) return '';
          const words = safe.split(' ').filter(Boolean).slice(0, 25);
          return words.join(' ');
        };

        const totals = streakData.map((item) => {
          const hours = clampNumber(item.hours, 0, 23);
          const minutes = clampNumber(item.minutes, 0, 59);
          return (hours * 60) + minutes;
        });
        const maxMinutes = Math.max(...totals, 1);

        const buttons = [];

        const formatDuration = (hours, minutes) => {
          const safeHours = clampNumber(hours, 0, 23);
          const safeMinutes = clampNumber(minutes, 0, 59);
          if (safeHours === 0 && safeMinutes === 0) {
            return 'No time logged';
          }
          if (safeHours > 0 && safeMinutes > 0) {
            return `${safeHours}h ${safeMinutes}m`;
          }
          if (safeHours > 0) {
            return `${safeHours}h`;
          }
          return `${safeMinutes}m`;
        };

        const formatDayLabel = (dateStr) => {
          const date = new Date(`${dateStr}T00:00:00`);
          if (Number.isNaN(date.getTime())) return '';
          return date.toLocaleDateString(undefined, { weekday: 'short' });
        };

        const formatFullDate = (dateStr) => {
          const date = new Date(`${dateStr}T00:00:00`);
          if (Number.isNaN(date.getTime())) return dateStr;
          return date.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
        };

        const updateSummary = (item, button) => {
          if (buttons.length) {
            buttons.forEach((btn) => btn.classList.remove('is-active'));
          }
          if (button) {
            button.classList.add('is-active');
          }
          if (streakSummaryTitle) {
            streakSummaryTitle.textContent = formatFullDate(item.date);
          }
          if (streakSummaryMeta) {
            streakSummaryMeta.textContent = formatDuration(item.hours, item.minutes);
          }
          if (streakSummaryText) {
            const summary = sanitizeSummaryText(item.summary || '');
            streakSummaryText.textContent = summary || 'No summary logged for this day.';
          }
        };

        streakData.forEach((item, index) => {
          const minutes = totals[index];
          const heightPercent = Math.max(6, Math.round((minutes / maxMinutes) * 100));
          const button = document.createElement('button');
          button.type = 'button';
          button.className = `streak-bar${minutes === 0 ? ' is-zero' : ''}`;
          button.setAttribute('role', 'listitem');
          button.setAttribute(
            'aria-label',
            `${formatFullDate(item.date)} · ${formatDuration(item.hours, item.minutes)}`
          );

          const fill = document.createElement('span');
          fill.className = 'streak-bar-fill';
          fill.style.height = `${heightPercent}%`;

          const label = document.createElement('span');
          label.className = 'streak-bar-label';
          label.textContent = formatDayLabel(item.date);

          button.appendChild(fill);
          button.appendChild(label);
          button.addEventListener('click', () => updateSummary(item, button));

          streakGraph.appendChild(button);
          buttons.push(button);
        });

        const defaultIndex = (() => {
          for (let i = totals.length - 1; i >= 0; i -= 1) {
            if (totals[i] > 0) return i;
          }
          return totals.length - 1;
        })();

        if (streakData[defaultIndex]) {
          updateSummary(streakData[defaultIndex], buttons[defaultIndex]);
        }
      }
    }
  </script>
</body>
</html>
