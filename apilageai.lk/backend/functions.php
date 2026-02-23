<?php
/**
 * get_hash_number
 * 
 * @return string
 */
function get_hash_number() {
    return time()*rand(1, 99999);
}

/**
 * Check if a value is empty (including whitespace and non-breaking spaces) or an empty array.
 *
 * @param mixed $value The value to check.
 * @return bool True if the value is empty, false otherwise.
 */
function is_empty(mixed $value): bool {
    if (is_null($value)) {
        return true;
    }

    if (is_string($value)) {
        // Replace non-breaking spaces with regular spaces and trim
        return trim(str_replace("\xc2\xa0", ' ', $value)) === '';
    }

    if (is_array($value)) {
        return empty($value);
    }

    return false;
}

// Upload helpers
function uploads_extract_path(string $value): string {
    $value = trim($value);
    if ($value === '') return '';
    if (preg_match('#^https?://#i', $value)) {
        $parsed = parse_url($value);
        $value = (string)($parsed['path'] ?? '');
    }
    $value = preg_replace('/[?#].*$/', '', $value);
    $value = str_replace('\\', '/', $value);
    return $value;
}

function uploads_detect_subfolder(string $value, string $default = 'userimg'): string {
    $path = uploads_extract_path($value);
    $path = ltrim($path, '/');
    if (preg_match('#^uploads/([a-z0-9_-]+)/#i', $path, $m)) {
        return strtolower($m[1]);
    }
    if (preg_match('#^([a-z0-9_-]+)/#i', $path, $m)) {
        return strtolower($m[1]);
    }
    return strtolower($default);
}

function uploads_extract_filename(string $value): string {
    $path = uploads_extract_path($value);
    if ($path === '') return '';
    $path = ltrim($path, '/');
    if (preg_match('#^uploads/[^/]+/(.+)$#i', $path, $m)) {
        $path = $m[1];
    } elseif (preg_match('#^[^/]+/(.+)$#i', $path, $m)) {
        $path = $m[1];
    }
    $filename = basename($path);
    if ($filename === '.' || $filename === '..') return '';
    return $filename;
}

function uploads_url_from_db(?string $value, string $defaultFolder = 'userimg', ?string $baseOverride = null): ?string {
    if ($value === null) return null;
    $raw = trim((string)$value);
    if ($raw === '') return '';

    if (preg_match('#^https?://#i', $raw)) {
        $host = parse_url($raw, PHP_URL_HOST);
        $path = parse_url($raw, PHP_URL_PATH) ?? '';
        $appHost = parse_url(APP_URL, PHP_URL_HOST);
        $uploadsHost = parse_url(UPLOADS_BASE_URL, PHP_URL_HOST);
        if ($host && $host !== $appHost && $host !== $uploadsHost) {
            return $raw;
        }
        $raw = $path ?: $raw;
    }

    $folder = uploads_detect_subfolder($raw, $defaultFolder);
    $filename = uploads_extract_filename($raw);
    if ($filename === '') return '';
    $base = $baseOverride ?: (($folder === 'profile') ? APP_URL : UPLOADS_BASE_URL);
    return rtrim($base, '/') . '/uploads/' . $folder . '/' . $filename;
}

function uploads_path_from_db(?string $value, string $defaultFolder = 'userimg'): ?string {
    $raw = trim((string)($value ?? ''));
    if ($raw === '') return null;
    $filename = uploads_extract_filename($raw);
    if ($filename === '') return null;
    $folder = uploads_detect_subfolder($raw, $defaultFolder);
    $base = realpath(__DIR__ . '/../public_html/uploads/' . $folder);
    if (!$base) return null;
    $path = $base . '/' . $filename;
    $real = realpath($path);
    if (!$real || strpos($real, $base) !== 0) return null;
    return $real;
}

/**
 * Build a safe, absolute URL for a user profile image.
 *
 * @param mixed $image Stored image value (filename, relative path, or absolute URL).
 * @param string|null $fallback Optional fallback URL.
 * @return string
 */
function user_image_url(mixed $image, ?string $fallback = null): string {
    $fallbackUrl = $fallback ?: (APP_URL . '/assets/images/user.png');
    if (is_empty($image)) {
        return $fallbackUrl;
    }

    $image = trim((string) $image);
    if ($image === '') {
        return $fallbackUrl;
    }

    // If already a full URL, return as-is (profile images should keep APP_URL)
    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }
    // If starts with / but not /uploads (e.g., /assets/...), use APP_URL
    if (strpos($image, '/') === 0 && stripos($image, '/uploads/') !== 0) {
        return APP_URL . $image;
    }

    $resolved = uploads_url_from_db($image, 'profile', APP_URL);
    return $resolved !== '' ? $resolved : $fallbackUrl;
}

/**
 * get_hash_token
 * 
 * @return string
 */
function get_hash_token() {
    return md5(get_hash_number());
}

/**
 * setSecureCookie
 * 
 * @return void
 */
function setSecureCookie($name, $value, $expire, $path, $domain = null, $sameSiteOverride = null) {
    $secure = APP_ENV === 'production';
    $cookieDomain = $domain;
    if ($cookieDomain === null && $secure) {
        $cookieDomain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
        if ($cookieDomain === '') {
            $cookieDomain = parse_url(APP_URL, PHP_URL_HOST) ?: '';
        }
    }
    $sameSite = $sameSiteOverride !== null ? $sameSiteOverride : (defined('COOKIE_SAMESITE') ? COOKIE_SAMESITE : 'Lax');
    $validSameSite = in_array($sameSite, ['Lax', 'Strict', 'None'], true) ? $sameSite : 'Lax';
    $options = [
        'expires'  => $expire,
        'path'     => $path,
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => $validSameSite
    ];
    if (!empty($cookieDomain)) {
        $options['domain'] = $cookieDomain;
    }
    setcookie($name, $value, $options);
}

/**
 * getDateForDayOfWeek
 * 
 * @param string $d
 * @return string
 */
function get_date_by_digits($d) {
    return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$d - 1];
}

/**
 * page_header
 * 
 * @param string $title
 * @param string $description
 * @return void
 */
function page_header($title, $description = "Discover an advanced AI model designed to assist Sri Lankan citizens in studying, daily routines, language practice, school exams, and more—uniquely trained to embrace and reflect Sri Lankan culture.", $image = '') {
    global $smarty;
    
    if($image == '') {
        $image = APP_URL . '/assets/images/logo.png';
    }
    $smarty->assign('page_title', $title);
    $smarty->assign('page_description', $description);
    $smarty->assign('page_image', $image);
}

/**
 * checkExists
 * 
 * @param string $db
 * @param string $table
 * @param integer $id
 * @param string $message
 * @return void
 */
function checkExists($db, $table, $column, $value, $message, $bool = false) {
    $stmt = $db->prepare("SELECT 1 FROM $table WHERE $column = ? LIMIT 1");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    if ($exists === $bool) {
        returnJSON(["e" => true, "m" => $message]);
    }
}

/**
 * page_footer
 * 
 * @param string $page
 * @return void
 */
function page_footer($page) {
    global $smarty;
    $smarty->assign('page', $page);
    $smarty->display("$page.tpl");
}

/**
 * _password_hash
 * 
 * @param string $password
 * @return string
 */
function _password_hash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * get_user_ip
 * 
 * @return string
 */
function get_user_ip() {
    $ip = null;
    
    // Check for forwarded IP (behind proxy/load balancer)
    // Only trust X-Forwarded-For if from known proxy
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Take the first IP from the list (original client)
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    // Validate IP address format
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    return trim($ip);
}

/**
 * returnJSON
 * @param string $jsonData
 * @return string
 */
function returnJSON($jsonData) {
  if (defined('APILAGE_EXPECTS_JSON') && APILAGE_EXPECTS_JSON) {
    $buffer = '';
    if (ob_get_level() > 0) {
      $buffer = trim(ob_get_contents());
      while (ob_get_level() > 0) {
        ob_end_clean();
      }
    }
    if (!empty($buffer) && defined('APP_DEBUG') && APP_DEBUG) {
      if (!is_array($jsonData)) {
        $jsonData = ['data' => $jsonData];
      }
      $jsonData['_debug_output'] = $buffer;
    }
  }
  $GLOBALS['APILAGE_JSON_SENT'] = true;
  $jsonString = json_encode($jsonData);
  //$compressedData = gzencode($jsonString, 8);
  //header('Content-Encoding: gzip');
  header('Content-Type: application/json');
  exit($jsonString);
}

/**
 * captchaVerify
 * @param string $token
 * @return boolean
 */
function captchaVerify($token){
    if (empty($token)) {
        return false;
    }

    if (!defined('RECAPTCHA_SECRET_KEY') || empty(RECAPTCHA_SECRET_KEY)) {
        return false;
    }

    $endpoint = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $payload = http_build_query([
        'secret'   => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => get_user_ip(),
    ]);

    try {
        $response = null;
        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST            => true,
                CURLOPT_POSTFIELDS      => $payload,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CONNECTTIMEOUT  => 5,
                CURLOPT_TIMEOUT         => 8,
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $payload,
                    'timeout' => 8,
                ],
            ]);
            $response = @file_get_contents($endpoint, false, $context);
        }

        if (!$response) {
            return false;
        }

        $data = json_decode($response, true);
        return !empty($data['success']);
    } catch (Throwable $e) {
        error_log('Turnstile verify error: ' . $e->getMessage());
        return false;
    }
}

/**
 * validateFields
 * @param array $data
 * @param array $fields
 * @return string
 */
function validateFields($data, $fields) {
    foreach ($fields as $key => $rule) {
        $name = $rule[0] ?? 'Field';
        $pattern = $rule[1] ?? null;
        $message = $rule[2] ?? 'a valid value';
        if (empty($data[$key])) {
            return "$name is required.";
        } elseif ($pattern === FILTER_VALIDATE_EMAIL && !filter_var($data[$key], FILTER_VALIDATE_EMAIL)) {
            return "$name must be a valid email.";
        } elseif ($pattern === 'date') {
            $d = DateTime::createFromFormat('Y-m-d', $data[$key]);
            if (!$d || $d->format('Y-m-d') !== $data[$key]) {
                return "$name must be a valid date in YYYY-MM-DD format.";
            }
        } elseif (is_string($pattern) && !preg_match($pattern, $data[$key])) {
            return "$name must contain $message.";
        } elseif ($pattern === 'base64' && !preg_match('/^[A-Za-z0-9+\/=]+$/', $data[$key])) {
            return "$name must be a valid Base64 encoding.";
        }
    }

    return null;
}

/**
 * _email
 * 
 * @param string $email
 * @param string $subject
 * @param string $body
 * @param array $attachments
 * @return boolean
 */
function _email($email, $subject, $body, $attachments = []) {
    global $system;
    
    /* SMTP */
    $mail = new PHPMailer\PHPMailer\PHPMailer;
    $mail->CharSet = "UTF-8";
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->Port = SMTP_PORT;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    // Prevent long hangs when SMTP is unreachable (common in local dev)
    $mail->Timeout = 5;
    $mail->SMTPConnectTimeout = 5;
    $mail->SMTPKeepAlive = false;

    $mail->XMailer = "ApilageAI";
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($email);

    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body = $body;
    if(!empty($attachments)){
        foreach ($attachments['name'] as $key => $name) {
            $mail->addAttachment($attachments["tmp_name"][$key], $name);
        }
    }

    if(!$mail->send()) {
        error_log($mail->ErrorInfo);
        return false;
    }

    return true;
}

/**
 * get_email_template
 * 
 * @param string $template_name
 * @param string $template_subject
 * @param array $template_variables
 * @return array
 */
function get_email_template($template_name, $template_variables = []) {
    global $smarty;
    if($template_variables) {
        foreach ($template_variables as $key => $value) {
            $smarty->assign($key, $value);
        }
    }
    $body = $smarty->fetch("emails/".$template_name.".html");
    return $body;
}

/**
 * user_agent_array
 * 
 * @return array
 */
function user_agent_array($agent) {
    $Browser = new foroco\BrowserDetection();
    $agent = $Browser->getAll($agent);

    return [
        'browser' => $agent['browser_title'],
        'platform' => $agent['os_title']
    ];
}

/**
 * save_picture_from_url
 * 
 * @param string $file File URL or path
 * @param string $prefix Image name prefix
 * @param string $img_quality Image quality ('low', 'medium', 'high')
 * @param string $subfolder Subfolder name within uploads (default: 'userimg', can be 'profile')
 * @return string Just the filename (e.g., 'user_123_timestamp.jpg')
 */
function save_picture_from_url($file, $prefix, $img_quality = 'medium', $subfolder = 'userimg') {
    // init image & prepare image name & path
    require_once(__DIR__.'/class-image.php');

    $image = new Image($file);
    $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$prefix);
    if ($safePrefix === '') {
        $safePrefix = bin2hex(random_bytes(8));
    }
    $image_name = $safePrefix.$image->_img_ext;
    // Sanitize subfolder to prevent directory traversal
    $subfolder = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$subfolder);
    if ($subfolder === '') {
        $subfolder = 'userimg';
    }
    $dir = __DIR__.'/../public_html/uploads/'.$subfolder;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $path = $dir.'/'.$image_name;

    /* save the new image */
    $image->save($path, $img_quality);
    // Return just filename for database storage
    return $image_name;
}

/**
 * Check if an email is from a disposable email provider
 * 
 * @param string $email Email address to check
 * @return bool True if disposable, false otherwise
 */
function isDisposableEmail($email) {
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $email = strtolower(trim($email));
    $domain = substr(strrchr($email, "@"), 1);
    
    // 1. Local common disposable domains check (Fast fallback)
    $commonDisposable = [
        'temp-mail.org', '10minutemail.com', 'throwawaymail.com', 'mailinator.com',
        'guerrillamail.com', 'sharklasers.com', 'dispostable.com', 'tempmail.net',
        'yopmail.com', 'getnada.com', 'tempmail.dev', 'temp-mail.io', 'minuteinbox.com'
    ];
    
    if (in_array($domain, $commonDisposable)) {
        return true;
    }
    
    // 2. Try the primary API (debounce.io)
    $url = "https://disposable.debounce.io/?email=" . urlencode($domain); // Checking domain is often more reliable
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false, // Set to false temporarily to check if SSL is the blocker
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_USERAGENT => 'ApilageAI/1.1'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    if ($httpCode === 200 && !empty($response)) {
        $data = json_decode($response, true);
        if (isset($data['disposable'])) {
            $isDisposable = ($data['disposable'] === 'true' || $data['disposable'] === true);
            if ($isDisposable) return true;
        }
    } else {
        error_log("Disposable API primary failed: $email. HTTP: $httpCode, Error: $error");
    }

    // 3. Secondary check: Kickbox Disposable API (No API key needed for basic check)
    $url2 = "https://open.kickbox.com/v1/disposable/" . urlencode($domain);
    $ch2 = curl_init($url2);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'ApilageAI/1.1'
    ]);
    
    $response2 = curl_exec($ch2);
    $data2 = json_decode($response2, true);
    
    if (isset($data2['disposable']) && $data2['disposable'] === true) {
        return true;
    }
    
    return false;
}
?>
