<?php
/**
 * ApilageAI Profile Edit API
 * 
 * Handles user profile and preferences updates
 * 
 * @package ApilageAI
 */

require_once __DIR__ . "/../backend/bootstrap.php";
header('Content-Type: application/json');

// Require authentication
if (!$user->_logged_in) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = (int)$user->_data['id'];
$action = $_REQUEST['action'] ?? null;

// ----------- LOAD ACTION -----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'load') {
    // Fetch user general info
    $stmt = $db->prepare("SELECT first_name, last_name, email, phone, memory FROM users WHERE id=?");
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

    $interests = $prefData['interests'] ? json_decode($prefData['interests'], true) : [];
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

    echo json_encode([
        'success' => true,
        'user' => $userData,
        'school' => $school,
        'not_student' => $not_student,
        'interests' => $interests,
        'preference' => $preference,
        'billing' => $billing
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

    $image_url = null;
    $photoUploading = false;
    
    if (!empty($_FILES['profilePhoto']['name'])) {
        $photoUploading = true;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
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
            mkdir($uploadDir, 0777, true);
        }

        $fileExt = strtolower(pathinfo($_FILES['profilePhoto']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($fileExt, $allowedExts)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file extension']);
            exit();
        }
        
        $fileName = "user_" . $user_id . "_" . time() . "." . $fileExt;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profilePhoto']['tmp_name'], $filePath)) {
            $image_url = "/uploads/profile/" . $fileName;
        }
    }

    if ($image_url) {
        $stmt = $db->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, image=? WHERE id=?");
        $stmt->bind_param("sssssi", $first, $last, $email, $phone, $image_url, $user_id);
    } else {
        $stmt = $db->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=? WHERE id=?");
        $stmt->bind_param("ssssi", $first, $last, $email, $phone, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'photoUploading' => $photoUploading]);
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

// ----------- CLEAR MEMORY -----------
if ($action === 'clearmemory' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("UPDATE users SET memory=NULL WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
    exit();
}

// ----------- INVALID REQUEST -----------
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit();