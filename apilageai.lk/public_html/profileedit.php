<?php
/**
 * ApilageAI Profile Edit API
 * 
 * Handles user profile and preferences updates
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../backend/bootstrap.php';
header('Content-Type: application/json');

// Require authentication
if (!$user->_logged_in) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = (int)$user->_data['id'];
$action = $_REQUEST['action'] ?? null;

function fetch_active_learning_streak($db, $user_id) {
    $stmt = $db->prepare("SELECT id, name, status, started_at, ended_at, ended_reason FROM learning_streaks WHERE user_id=? AND status='active' ORDER BY started_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function fetch_learning_streak_stats($db, $streak_id) {
    $stmt = $db->prepare("SELECT COUNT(*) AS day_count, MAX(entry_date) AS last_entry_date FROM learning_streak_entries WHERE streak_id=?");
    $stmt->bind_param("i", $streak_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return [
        'day_count' => (int)($row['day_count'] ?? 0),
        'last_entry_date' => $row['last_entry_date'] ?? null
    ];
}

function evaluate_learning_streak_state($started_at, $last_entry_date) {
    $todayStr = date('Y-m-d');
    $todayTs = strtotime($todayStr);
    $startDate = $started_at ? substr($started_at, 0, 10) : $todayStr;
    $startTs = strtotime($startDate);

    if (empty($last_entry_date)) {
        if ($todayTs > $startTs) {
            return ['needs_check_in' => false, 'missed' => true];
        }
        return ['needs_check_in' => true, 'missed' => false];
    }

    $lastTs = strtotime($last_entry_date);
    if ($todayTs === $lastTs) {
        return ['needs_check_in' => false, 'missed' => false];
    }

    $nextTs = strtotime($last_entry_date . ' +1 day');
    if ($todayTs === $nextTs) {
        return ['needs_check_in' => true, 'missed' => false];
    }

    if ($todayTs > $nextTs) {
        return ['needs_check_in' => false, 'missed' => true];
    }

    return ['needs_check_in' => false, 'missed' => false];
}

function end_learning_streak($db, $user_id, $streak_id, $reason) {
    $stmt = $db->prepare("UPDATE learning_streaks SET status='ended', ended_at=NOW(), ended_reason=?, updated_at=NOW() WHERE id=? AND user_id=? AND status='active'");
    $stmt->bind_param("sii", $reason, $streak_id, $user_id);
    $stmt->execute();
    $success = $stmt->affected_rows > 0;
    $stmt->close();

    $stmt = $db->prepare("UPDATE users SET learning_streak_started_at = NULL WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    return $success;
}

function fetch_learning_streak_history($db, $user_id, $limit = 10) {
    $stmt = $db->prepare("
        SELECT s.id, s.name, s.status, s.started_at, s.ended_at, s.ended_reason,
               COUNT(e.id) AS day_count
        FROM learning_streaks s
        LEFT JOIN learning_streak_entries e ON e.streak_id = s.id
        WHERE s.user_id = ?
        GROUP BY s.id, s.name, s.status, s.started_at, s.ended_at, s.ended_reason
        ORDER BY s.started_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function fetch_learning_streak_max_days($db, $user_id) {
    $stmt = $db->prepare("
        SELECT MAX(day_count) AS max_day_count FROM (
            SELECT s.id, COUNT(e.id) AS day_count
            FROM learning_streaks s
            LEFT JOIN learning_streak_entries e ON e.streak_id = s.id
            WHERE s.user_id = ?
            GROUP BY s.id
        ) AS streak_days
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return isset($row['max_day_count']) ? (int)$row['max_day_count'] : 0;
}

function build_learning_streak_payload($db, $user_id, $history_limit = 10, $auto_end = true) {
    $active = fetch_active_learning_streak($db, $user_id);
    $needsCheckIn = false;
    $autoEnded = false;
    $dayCount = 0;
    $lastEntryDate = null;

    if ($active) {
        $stats = fetch_learning_streak_stats($db, $active['id']);
        $dayCount = $stats['day_count'];
        $lastEntryDate = $stats['last_entry_date'];
        $state = evaluate_learning_streak_state($active['started_at'], $lastEntryDate);
        $needsCheckIn = $state['needs_check_in'];

        if ($state['missed'] && $auto_end) {
            end_learning_streak($db, $user_id, $active['id'], 'missed');
            $active = null;
            $needsCheckIn = false;
            $autoEnded = true;
            $dayCount = 0;
            $lastEntryDate = null;
        }
    }

    $history = fetch_learning_streak_history($db, $user_id, $history_limit);
    $maxDays = fetch_learning_streak_max_days($db, $user_id);
    $currentDays = $active ? $dayCount : 0;

    $thresholds = [
        'silver' => 7,
        'gold' => 30,
        'diamond' => 100
    ];
    $badges = [];
    $progress = ['current_days' => $currentDays];

    foreach ($thresholds as $key => $target) {
        $earned = $maxDays >= $target;
        $currentForProgress = $earned ? $target : min($currentDays, $target);
        $percent = $target > 0 ? (int)round(($currentForProgress / $target) * 100) : 0;
        $badges[$key] = $earned;
        $progress[$key] = [
            'target' => $target,
            'remaining' => $earned ? 0 : max(0, $target - $currentDays),
            'percent' => $earned ? 100 : $percent,
            'earned' => $earned
        ];
    }

    return [
        'active' => (bool)$active,
        'active_streak' => $active ? [
            'id' => (int)$active['id'],
            'name' => $active['name'],
            'started_at' => $active['started_at'],
            'day_count' => $dayCount,
            'last_entry_date' => $lastEntryDate
        ] : null,
        'needs_check_in' => $needsCheckIn,
        'auto_ended' => $autoEnded,
        'history' => $history,
        'badges' => $badges,
        'progress' => $progress,
        'max_days' => $maxDays
    ];
}

// ----------- LOAD ACTION -----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'load') {
    // Fetch user general info
    $stmt = $db->prepare("SELECT first_name, last_name, email, phone, memory, public_profile_token, public_profile_username, public_profile_enabled, learning_streak_started_at FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch user preferences
    $stmt = $db->prepare("SELECT school, not_student, interests, preference FROM user_onboarding WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $prefData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$prefData) {
        $prefData = [];
    }

    $interests = !empty($prefData['interests']) ? json_decode($prefData['interests'], true) : [];
    $preference = $prefData['preference'] ?? '';
    $school = $prefData['school'] ?? '';
    $not_student = $prefData['not_student'] ?? 0;

    // Fetch billing history
    $stmt = $db->prepare("SELECT invoice_id, amount, created_at, status_indicator FROM transactions WHERE user_id=? AND status_indicator IS NOT NULL AND status_indicator <> '' ORDER BY created_at DESC LIMIT 50");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $billingResult = $stmt->get_result();
    $billing = [];
    while ($row = $billingResult->fetch_assoc()) {
        $billing[] = $row;
    }
    $stmt->close();

    // Check Google connection
    $stmt = $db->prepare("SELECT google_email FROM google_auth WHERE user_id=? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $googleResult = $stmt->get_result();
    $googleRow = $googleResult->fetch_assoc();
    $hasGoogleAuth = !empty($googleRow);
    $googleEmail = $googleRow['google_email'] ?? null;
    $stmt->close();

    $streakSummary = build_learning_streak_payload($db, $user_id);
    if (!empty($streakSummary['active']) && !empty($streakSummary['active_streak']['started_at'])) {
        $userData['learning_streak_started_at'] = $streakSummary['active_streak']['started_at'];
    } else {
        $userData['learning_streak_started_at'] = null;
    }

    echo json_encode([
        'success' => true,
        'user' => $userData,
        'school' => $school,
        'not_student' => $not_student,
        'interests' => $interests,
        'preference' => $preference,
        'billing' => $billing,
        'has_google_auth' => $hasGoogleAuth,
        'google_email' => $googleEmail,
        'public_profile_token' => $userData['public_profile_token'] ?? null,
        'public_profile_username' => $userData['public_profile_username'] ?? null,
        'public_profile_enabled' => isset($userData['public_profile_enabled']) ? (int)$userData['public_profile_enabled'] : 1,
        'learning_streak_started_at' => $userData['learning_streak_started_at'] ?? null,
        'learning_streak' => $streakSummary
    ]);
    exit();
}

// ----------- GENERAL SETTINGS UPDATE -----------
if ($action === 'general' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = htmlspecialchars(trim($_POST['firstName'] ?? ''), ENT_QUOTES, 'UTF-8');
    $last = htmlspecialchars(trim($_POST['lastName'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');

    if (empty($first) || empty($last) || !$email || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'All fields required']);
        exit();
    }

    $image_filename = null;
    $photoUploading = false;
    
    if (!empty($_FILES['profilePhoto']['name'])) {
        $photoUploading = true;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['profilePhoto']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid image file type']);
            exit();
        }
        
        // Check file size (max 5MB)
        if ($_FILES['profilePhoto']['size'] > 5242880) {
            echo json_encode(['success' => false, 'message' => 'Image file too large (max 5MB)']);
            exit();
        }
        
        $uploadDir = __DIR__ . "/uploads/profile/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExt = $mimeToExt[$mimeType] ?? '';
        if ($fileExt === '') {
            echo json_encode(['success' => false, 'message' => 'Invalid image file type']);
            exit();
        }

        $fileName = "user_" . $user_id . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $fileExt;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profilePhoto']['tmp_name'], $filePath)) {
            $image_filename = $fileName;
            error_log("Profile Photo Upload - File: " . $image_filename);
        }
    }

    if ($image_filename) {
        $stmt = $db->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, image=? WHERE id=?");
        $stmt->bind_param("sssssi", $first, $last, $email, $phone, $image_filename, $user_id);
    } else {
        $stmt = $db->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=? WHERE id=?");
        $stmt->bind_param("ssssi", $first, $last, $email, $phone, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'photoUploading' => $photoUploading,
            'image_url' => $image_filename ? user_image_url($image_filename) : null
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    exit();
}

// ----------- PREFERENCE SETTINGS UPDATE -----------
if ($action === 'preferences' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjects_raw = trim($_POST['subjects'] ?? '');
    $subjects = [];
    $aiTone = htmlspecialchars(trim($_POST['aiTone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $school = htmlspecialchars(trim($_POST['school'] ?? ''), ENT_QUOTES, 'UTF-8');
    $not_student = isset($_POST['not_student']) ? (int)$_POST['not_student'] : 0;

    if ($school && $not_student) {
        echo json_encode(['success' => false, 'message' => 'Select either School or Not a Student, not both.']);
        exit();
    }

    if (empty($subjects_raw) || empty($aiTone)) {
        echo json_encode(['success' => false, 'message' => 'All fields required']);
        exit();
    }

    if (strpos($subjects_raw, '[') === 0) {
        $decoded = json_decode($subjects_raw, true);
        if (is_array($decoded)) {
            $subjects = array_values(array_filter(array_map('trim', $decoded)));
        }
    } else {
        if (!preg_match('/^[\p{L}\s,]+$/u', $subjects_raw)) {
            echo json_encode(['success' => false, 'message' => 'Only text, spaces, and commas are allowed.']);
            exit();
        }

        $subjects = array_values(array_filter(array_map('trim', explode(',', $subjects_raw))));
    }

    $subjects = array_values(array_unique($subjects));

    if (count($subjects) === 0) {
        echo json_encode(['success' => false, 'message' => 'Enter at least one interested subject.']);
        exit();
    }

    if (count($subjects) > 5) {
        echo json_encode(['success' => false, 'message' => 'Maximum 5 values allowed.']);
        exit();
    }

    $interests = json_encode($subjects);

    $stmt = $db->prepare("
        INSERT INTO user_onboarding (user_id, school, not_student, interests, preference, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE 
            school = VALUES(school),
            not_student = VALUES(not_student),
            interests = VALUES(interests),
            preference = VALUES(preference),
            updated_at = NOW()
    ");
    $stmt->bind_param("isiss", $user_id, $school, $not_student, $interests, $aiTone);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update preferences']);
    }
    exit();
}

// ----------- PUBLIC PROFILE SETTINGS UPDATE -----------
if ($action === 'public_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameRaw = trim($_POST['username'] ?? '');
    $closeProfile = isset($_POST['close_public_profile']) ? (int)$_POST['close_public_profile'] : 0;
    $publicEnabled = $closeProfile ? 0 : 1;

    $username = $usernameRaw === '' ? null : strtolower($usernameRaw);

    if ($username !== null) {
        if (preg_match('/\s/', $usernameRaw)) {
            echo json_encode(['success' => false, 'message' => 'Username cannot contain spaces.']);
            exit();
        }
        if (!preg_match('/^[a-z0-9_-]{3,30}$/i', $usernameRaw)) {
            echo json_encode(['success' => false, 'message' => 'Use 3-30 characters: letters, numbers, underscores, or dashes.']);
            exit();
        }

        $reserved = [
            'app', 'auth', 'about', 'about-us', 'images', 'dashboard', 'pay', 'apilage-admin', 'admin',
            'privacypolicy', 'termsofservice', 'termsconditions', 'data-deletion', 'api', 'uploads',
            'assets', 'robots.txt', 'sitemap', 'public', 'public-profile'
        ];
        if (in_array($username, $reserved, true)) {
            echo json_encode(['success' => false, 'message' => 'That username is reserved.']);
            exit();
        }

        $stmt = $db->prepare(
            "SELECT id FROM users WHERE (public_profile_username = ? OR public_profile_token = ?) AND id <> ? LIMIT 1"
        );
        $stmt->bind_param("ssi", $username, $username, $user_id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($exists) {
            echo json_encode(['success' => false, 'message' => 'Username already taken.']);
            exit();
        }
    }

    if ($username === null) {
        $stmt = $db->prepare("UPDATE users SET public_profile_username = NULL, public_profile_enabled = ? WHERE id = ?");
        $stmt->bind_param("ii", $publicEnabled, $user_id);
    } else {
        $stmt = $db->prepare("UPDATE users SET public_profile_username = ?, public_profile_enabled = ? WHERE id = ?");
        $stmt->bind_param("sii", $username, $publicEnabled, $user_id);
    }

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to update public profile']);
        exit();
    }
    $stmt->close();

    $stmt = $db->prepare("SELECT public_profile_token, public_profile_username, public_profile_enabled, learning_streak_started_at FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $updated = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $streakSummary = build_learning_streak_payload($db, $user_id);
    $learningStreakStarted = $streakSummary['active_streak']['started_at'] ?? null;
    echo json_encode([
        'success' => true,
        'public_profile_token' => $updated['public_profile_token'] ?? null,
        'public_profile_username' => $updated['public_profile_username'] ?? null,
        'public_profile_enabled' => isset($updated['public_profile_enabled']) ? (int)$updated['public_profile_enabled'] : 1,
        'learning_streak_started_at' => $learningStreakStarted,
        'learning_streak' => $streakSummary
    ]);
    exit();
}

// ----------- START LEARNING STREAK -----------
if ($action === 'start_streak' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nameRaw = trim($_POST['streak_name'] ?? '');
    $nameRaw = preg_replace('/\s+/', ' ', $nameRaw);
    $name = trim(strip_tags($nameRaw));
    if ($name === '') {
        echo json_encode(['success' => false, 'message' => 'Please provide a streak name.']);
        exit();
    }
    $nameLength = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
    if ($nameLength > 80) {
        echo json_encode(['success' => false, 'message' => 'Streak name must be 80 characters or less.']);
        exit();
    }

    $existing = fetch_active_learning_streak($db, $user_id);
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'You already have an active streak.']);
        exit();
    }

    $stmt = $db->prepare("INSERT INTO learning_streaks (user_id, name, status, started_at, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW(), NOW())");
    $stmt->bind_param("is", $user_id, $name);
    $success = $stmt->execute();
    $stmt->close();

    if (!$success) {
        echo json_encode(['success' => false, 'message' => 'Failed to start learning streak']);
        exit();
    }

    $stmt = $db->prepare("UPDATE users SET learning_streak_started_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $streakSummary = build_learning_streak_payload($db, $user_id);
    echo json_encode([
        'success' => true,
        'learning_streak' => $streakSummary,
        'learning_streak_started_at' => $streakSummary['active_streak']['started_at'] ?? null
    ]);
    exit();
}

// ----------- END LEARNING STREAK -----------
if ($action === 'end_streak' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $active = fetch_active_learning_streak($db, $user_id);
    if (!$active) {
        echo json_encode(['success' => false, 'message' => 'No active streak to end.']);
        exit();
    }

    end_learning_streak($db, $user_id, $active['id'], 'manual');
    $streakSummary = build_learning_streak_payload($db, $user_id);
    echo json_encode([
        'success' => true,
        'learning_streak' => $streakSummary,
        'learning_streak_started_at' => null
    ]);
    exit();
}

// ----------- LOG LEARNING STREAK CHECK-IN -----------
if ($action === 'log_streak' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $hours = isset($_POST['hours']) ? (int)$_POST['hours'] : 0;
    $minutes = isset($_POST['minutes']) ? (int)$_POST['minutes'] : 0;
    if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
        echo json_encode(['success' => false, 'message' => 'Invalid time values.']);
        exit();
    }
    if ($hours === 0 && $minutes === 0) {
        echo json_encode(['success' => false, 'message' => 'Please enter at least 1 minute of study time.']);
        exit();
    }

    $summaryRaw = trim($_POST['summary'] ?? '');
    $summaryClean = trim(strip_tags($summaryRaw));
    if ($summaryClean === '') {
        echo json_encode(['success' => false, 'message' => 'Please add a short note about what you studied.']);
        exit();
    }
    $summary = function_exists('mb_substr') ? mb_substr($summaryClean, 0, 140) : substr($summaryClean, 0, 140);

    $active = fetch_active_learning_streak($db, $user_id);
    if (!$active) {
        echo json_encode(['success' => false, 'message' => 'No active streak found.']);
        exit();
    }

    $stats = fetch_learning_streak_stats($db, $active['id']);
    $state = evaluate_learning_streak_state($active['started_at'], $stats['last_entry_date']);
    if ($state['missed']) {
        end_learning_streak($db, $user_id, $active['id'], 'missed');
        echo json_encode(['success' => false, 'message' => 'Your streak ended because a day was missed.']);
        exit();
    }
    if (!$state['needs_check_in']) {
        echo json_encode(['success' => false, 'message' => 'Today has already been logged.']);
        exit();
    }

    $today = date('Y-m-d');
    $stmt = $db->prepare("INSERT INTO learning_streak_entries (streak_id, user_id, entry_date, hours, minutes, summary) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisiis", $active['id'], $user_id, $today, $hours, $minutes, $summary);
    $success = $stmt->execute();
    $errorCode = $stmt->errno;
    $stmt->close();

    if (!$success) {
        if ($errorCode === 1062) {
            echo json_encode(['success' => false, 'message' => 'Today has already been logged.']);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Failed to log streak check-in.']);
        exit();
    }

    $streakSummary = build_learning_streak_payload($db, $user_id);
    echo json_encode([
        'success' => true,
        'learning_streak' => $streakSummary
    ]);
    exit();
}

// ----------- LEARNING STREAK STATUS -----------
if ($action === 'streak_status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $streakSummary = build_learning_streak_payload($db, $user_id);
    echo json_encode([
        'success' => true,
        'learning_streak' => $streakSummary
    ]);
    exit();
}

// ----------- CLEAR MEMORY -----------
if ($action === 'clearmemory' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("UPDATE users SET memory=NULL WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
    exit();
}

// ----------- PASSWORD RESET REQUEST (LOGGED-IN USER) -----------
if ($action === 'request_password_reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $result = $user->request_password_reset_for_user($user_id, $email);
    echo json_encode([
        'success' => !$result['e'],
        'message' => $result['m']
    ]);
    exit();
}

// ----------- DELETE ACCOUNT -----------
if ($action === 'delete_account' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = $user->delete_user_account_by_id($user_id);
    if ($success) {
        $user->sign_out();
        echo json_encode(['success' => true]);
        exit();
    }
    echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
    exit();
}

// ----------- INVALID REQUEST -----------
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit();
