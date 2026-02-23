<?php
require_once __DIR__ . "/youtube_helper.php";
require_once __DIR__ . "/image_helper.php";

class User
{
    public $_logged_in = false;
    public $_data = [];

    private $_cookie_user_id = "APILAGE_AI_LK_USER_ID";
    private $_cookie_user_token = "APILAGE_AI_LK_TOKEN";
    
    // Rate limiting storage
    private static $rateLimitStore = [];

    public function __construct()
    {
        global $db, $date;

        if (
            isset($_COOKIE[$this->_cookie_user_id]) &&
            isset($_COOKIE[$this->_cookie_user_token])
        ) {
            // Validate cookie values are properly formatted
            if (!is_numeric($_COOKIE[$this->_cookie_user_id]) || 
                !preg_match('/^[a-f0-9]{64}$/i', $_COOKIE[$this->_cookie_user_token])) {
                $this->unset_cookies();
                return;
            }

            $stmt = $db->prepare(
                "SELECT users.*, sessions.token, sessions.ip, sessions.client 
                 FROM users 
                 JOIN sessions ON users.id = sessions.user_id 
                 WHERE users.id = ? AND sessions.token = ? AND sessions.active = '1' 
                 LIMIT 1"
            );
            $stmt->bind_param(
                "is",
                $_COOKIE[$this->_cookie_user_id],
                $_COOKIE[$this->_cookie_user_token]
            );
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $sessionData = $result->fetch_assoc();
                
                // Session hijacking protection - verify IP and user agent
                $currentIp = get_user_ip();
                $currentClient = $_SERVER["HTTP_USER_AGENT"] ?? '';
                
                // Allow some flexibility for IP changes (mobile networks)
                // but be strict on user agent changes
                if ($sessionData['client'] !== $currentClient) {
                    $stmt->close();
                    $this->unset_cookies();
                    error_log("Session hijacking attempt detected for user " . $_COOKIE[$this->_cookie_user_id]);
                    return;
                }
                
                $this->_data = $sessionData;
                $this->_logged_in = true;
                $stmt->close();

                // Update last seen with IP verification
                $stmt = $db->prepare(
                    "UPDATE sessions SET last_seen = ?, ip = ? WHERE user_id = ? AND token = ?"
                );
                $stmt->bind_param(
                    "ssis",
                    $date,
                    $currentIp,
                    $_COOKIE[$this->_cookie_user_id],
                    $_COOKIE[$this->_cookie_user_token]
                );
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt->close();
                $this->unset_cookies();
            }
        }
    }

    function get_unique_media_prefix()
    {
        return "ApilageAI_t_" . time() . "_r_" . bin2hex(random_bytes(8)) . "_tk_" . get_hash_token();
    }

    private function generate_verification_token()
    {
        return bin2hex(random_bytes(32));
    }

    private function generate_public_profile_token()
    {
        return bin2hex(random_bytes(16));
    }

    private function get_unique_public_profile_token()
    {
        global $db;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $token = $this->generate_public_profile_token();
            $stmt = $db->prepare(
                "SELECT id FROM users WHERE public_profile_token = ? OR public_profile_username = ? LIMIT 1"
            );
            $stmt->bind_param("ss", $token, $token);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if (!$exists) {
                return $token;
            }
        }

        return $this->generate_public_profile_token();
    }

    private function send_verification_email($email, $firstName, $token)
    {
        // Sanitize inputs for email
        $firstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
        $verificationLink = APP_URL . "/auth/verify-email?token=" . urlencode($token);

        $body = get_email_template("email_verification", [
            "name" => $firstName,
            "verification_link" => $verificationLink,
        ]);

        return _email($email, "Verify Your Email - Apilage AI", $body);
    }

    private function link_google_auth_by_email($userId, $email)
    {
        global $db;

        if (empty($userId) || empty($email)) {
            return;
        }

        $email = strtolower(trim($email));

        $stmt = $db->prepare(
            "UPDATE google_auth SET user_id = ? WHERE google_email = ? AND user_id <> ?"
        );
        $stmt->bind_param("isi", $userId, $email, $userId);
        $stmt->execute();
        $stmt->close();
    }

    private function link_facebook_auth_by_email($userId, $email)
    {
        global $db;

        if (empty($userId) || empty($email)) {
            return;
        }

        $email = strtolower(trim($email));

        $stmt = $db->prepare(
            "UPDATE facebook_auth SET user_id = ? WHERE facebook_email = ? AND user_id <> ?"
        );
        $stmt->bind_param("isi", $userId, $email, $userId);
        $stmt->execute();
        $stmt->close();
    }

    // Rate limiting helper
    private function check_rate_limit($key, $max_attempts = 5, $time_window = 900)
    {
        $current_time = time();
        
        if (!isset(self::$rateLimitStore[$key])) {
            self::$rateLimitStore[$key] = ['count' => 1, 'first_attempt' => $current_time];
            return true;
        }
        
        $data = self::$rateLimitStore[$key];
        
        if ($current_time - $data['first_attempt'] > $time_window) {
            self::$rateLimitStore[$key] = ['count' => 1, 'first_attempt' => $current_time];
            return true;
        }
        
        if ($data['count'] >= $max_attempts) {
            return false;
        }
        
        self::$rateLimitStore[$key]['count']++;
        return true;
    }

    private function http_get_json($url, $params = [], $timeout = 8)
    {
        $endpoint = $url;
        if (!empty($params)) {
            $query = http_build_query($params);
            $endpoint .= (strpos($url, '?') === false ? '?' : '&') . $query;
        }

        $response = null;
        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_TIMEOUT => $timeout,
            ]);
            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new Exception("HTTP request failed: " . $curlError);
            }
            if ($httpCode >= 400) {
                throw new Exception("HTTP request returned status " . $httpCode);
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'timeout' => $timeout,
                ],
            ]);
            $response = @file_get_contents($endpoint, false, $context);
            if ($response === false) {
                throw new Exception("HTTP request failed");
            }
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new Exception("Invalid JSON response");
        }
        if (isset($data['error'])) {
            $message = 'API error';
            if (is_array($data['error'])) {
                $message = $data['error']['message'] ?? $message;
            } elseif (is_string($data['error'])) {
                $message = $data['error'];
            }
            throw new Exception($message);
        }

        return $data;
    }

    private function base64_url_decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    private function parse_facebook_signed_request(string $signedRequest, string $secret): ?array
    {
        $parts = explode('.', $signedRequest, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$encodedSig, $payload] = $parts;
        $sig = $this->base64_url_decode($encodedSig);
        $data = json_decode($this->base64_url_decode($payload), true);

        if (!is_array($data)) {
            return null;
        }
        if (empty($data['algorithm']) || strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            return null;
        }

        $expectedSig = hash_hmac('sha256', $payload, $secret, true);
        if (!hash_equals($expectedSig, $sig)) {
            return null;
        }

        return $data;
    }

    private function delete_user_account_by_id(int $userId): bool
    {
        global $db;

        if ($userId <= 0) {
            return false;
        }

        $table_exists = function (string $table) use ($db): bool {
            $stmt = $db->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1");
            $stmt->bind_param("s", $table);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();
            return $exists;
        };

        $column_exists = function (string $table, string $column) use ($db): bool {
            $stmt = $db->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1");
            $stmt->bind_param("ss", $table, $column);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();
            return $exists;
        };

        $delete_by_user_id = function (string $table, string $column) use ($db, $userId, $table_exists, $column_exists): void {
            if (!$table_exists($table) || !$column_exists($table, $column)) {
                return;
            }
            $stmt = $db->prepare("DELETE FROM `$table` WHERE `$column` = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
        };

        $delete_by_ids = function (string $table, string $column, array $ids) use ($db, $table_exists, $column_exists): void {
            if (!$table_exists($table) || !$column_exists($table, $column) || empty($ids)) {
                return;
            }
            $safeIds = array_map("intval", $ids);
            $in = implode(",", $safeIds);
            $db->query("DELETE FROM `$table` WHERE `$column` IN ($in)");
        };

        $db->begin_transaction();
        try {
            // Conversations + messages
            $conversationIds = [];
            if ($table_exists("conversations") && $column_exists("conversations", "user_id")) {
                $idColumn = $column_exists("conversations", "conversation_id") ? "conversation_id" : "id";
                if ($column_exists("conversations", $idColumn)) {
                    $stmt = $db->prepare("SELECT `$idColumn` AS cid FROM conversations WHERE user_id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($row = $res->fetch_assoc()) {
                        $conversationIds[] = (int) $row["cid"];
                    }
                    $stmt->close();
                }
            }

            if (!empty($conversationIds)) {
                $delete_by_ids("conversation_canvas", "conversation_id", $conversationIds);
                $delete_by_ids("conversation_published", "conversation_id", $conversationIds);
                $delete_by_ids("conversation_share_links", "conversation_id", $conversationIds);
                $delete_by_ids("messages", "conversation_id", $conversationIds);
                $delete_by_ids("conversation_participants", "conversation_id", $conversationIds);
            }

            $delete_by_user_id("conversations", "user_id");
            $delete_by_user_id("messages", "user_id");

            // Games + answers
            $gameIds = [];
            if ($table_exists("games") && $column_exists("games", "host_id")) {
                $idColumn = $column_exists("games", "id") ? "id" : "game_id";
                if ($column_exists("games", $idColumn)) {
                    $stmt = $db->prepare("SELECT `$idColumn` AS gid FROM games WHERE host_id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($row = $res->fetch_assoc()) {
                        $gameIds[] = (int) $row["gid"];
                    }
                    $stmt->close();
                }
            }

            if (!empty($gameIds)) {
                $delete_by_ids("questions", "game_id", $gameIds);
                $delete_by_ids("answers", "game_id", $gameIds);
            }

            $delete_by_user_id("answers", "user_id");
            $delete_by_user_id("games", "host_id");

            // Images + reactions
            $imageIds = [];
            if ($table_exists("generated_images") && $column_exists("generated_images", "user_id")) {
                $idColumn = $column_exists("generated_images", "id") ? "id" : "image_id";
                if ($column_exists("generated_images", $idColumn)) {
                    $stmt = $db->prepare("SELECT `$idColumn` AS iid FROM generated_images WHERE user_id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($row = $res->fetch_assoc()) {
                        $imageIds[] = (int) $row["iid"];
                    }
                    $stmt->close();
                }
            }

            if (!empty($imageIds)) {
                $delete_by_ids("image_reactions", "image_id", $imageIds);
            }

            $delete_by_user_id("generated_images", "user_id");
            $delete_by_user_id("image_reactions", "user_id");

            // Auth + sessions
            $delete_by_user_id("sessions", "user_id");
            $delete_by_user_id("google_auth", "user_id");
            $delete_by_user_id("facebook_auth", "user_id");
            $delete_by_user_id("gb_auth", "user_id");
            $delete_by_user_id("magic_login_tokens", "user_id");

            // Usage + billing
            $delete_by_user_id("usage_logs", "user_id");
            $delete_by_user_id("thinking_usage_logs", "user_id");
            $delete_by_user_id("transactions", "user_id");
            $delete_by_user_id("free_user_daily_usage", "user_id");
            $delete_by_user_id("free_user_limits", "user_id");
            $delete_by_user_id("trial_abuse_tracking", "user_id");

            // Notifications & onboarding
            $delete_by_user_id("notific", "user_id");
            $delete_by_user_id("user_onboarding", "user_id");

            if ($table_exists("users") && $column_exists("users", "id")) {
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            error_log("Facebook deauthorize delete failed: " . $e->getMessage());
            return false;
        }
    }

    public function sign_in($data = [])
    {
        global $db;

        if ($this->_logged_in) {
            returnJSON(["e" => true, "m" => "You're already logged in"]);
        }

        // Rate limiting by IP
        $ip = get_user_ip();
        if (!$this->check_rate_limit("signin_ip_" . $ip, 5, 900)) {
            returnJSON(["e" => true, "m" => "Too many login attempts. Please try again in 15 minutes."]);
        }

        $fields = [
            "email" => ["Email", FILTER_VALIDATE_EMAIL, "valid email"],
            "password" => ["Password"],
        ];

        $error = validateFields($data, $fields);

        if ($error) {
            returnJSON(["e" => true, "m" => $error]);
        }

        if (empty($data["captcha"]) || !captchaVerify($data["captcha"])) {
            returnJSON(["e" => true, "m" => "Captcha verification failed. Please try again."]);
        }

        // Additional rate limiting by email
        if (!$this->check_rate_limit("signin_email_" . strtolower($data["email"]), 3, 300)) {
            returnJSON(["e" => true, "m" => "Too many login attempts for this account. Please try again later."]);
        }

        $stmt = $db->prepare(
            "SELECT password, id, email_verified, first_name, failed_login_attempts, locked_until 
             FROM users WHERE email = ? LIMIT 1"
        );
        $email = strtolower(trim($data["email"]));
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            // Check if account is locked
            if ($row['locked_until'] && strtotime($row['locked_until']) > time()) {
                $stmt->close();
                returnJSON([
                    "e" => true, 
                    "m" => "Account temporarily locked due to multiple failed login attempts. Please try again later or reset your password."
                ]);
            }

            if (password_verify($data["password"], $row["password"])) {
                // Check if email is verified
                if ($row["email_verified"] == 0) {
                    $stmt->close();
                    returnJSON([
                        "e" => true,
                        "m" => "Please verify your email address before signing in. Check your inbox for the verification link.",
                        "resend" => true,
                        "email" => $data["email"],
                    ]);
                }

                // Reset failed login attempts on successful login
                $resetStmt = $db->prepare(
                    "UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?"
                );
                $resetStmt->bind_param("i", $row["id"]);
                $resetStmt->execute();
                $resetStmt->close();

                $this->link_google_auth_by_email((int)$row["id"], $email);
                $this->link_facebook_auth_by_email((int)$row["id"], $email);
                $this->create_session($row["id"]);
                $stmt->close();
                returnJSON(["e" => false]);
            } else {
                // Increment failed login attempts
                $failedAttempts = ($row['failed_login_attempts'] ?? 0) + 1;
                $lockedUntil = null;
                
                // Lock account after 5 failed attempts for 30 minutes
                if ($failedAttempts >= 5) {
                    $lockedUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                }
                
                $updateStmt = $db->prepare(
                    "UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?"
                );
                $updateStmt->bind_param("isi", $failedAttempts, $lockedUntil, $row["id"]);
                $updateStmt->execute();
                $updateStmt->close();
                
                $stmt->close();
                
                if ($lockedUntil) {
                    returnJSON([
                        "e" => true,
                        "m" => "Too many failed login attempts. Your account has been temporarily locked for 30 minutes."
                    ]);
                } else {
                    returnJSON([
                        "e" => true,
                        "m" => "Password doesn’t match. " . (5 - $failedAttempts) . " attempts remaining before account lock. You can reset it if needed."
                    ]);
                }
            }
        } else {
            $stmt->close();
            returnJSON(["e" => true, "m" => "Account not found. Please check your email or sign up."]);
        }
    }

    public function sign_up($data = [])
    {
        global $db, $date;

        if ($this->_logged_in) {
            returnJSON(["e" => true, "m" => "You're already logged in"]);
        }

        // Rate limiting by IP
        $ip = get_user_ip();
        if (!$this->check_rate_limit("signup_ip_" . $ip, 3, 3600)) {
            returnJSON(["e" => true, "m" => "Too many registration attempts. Please try again later."]);
        }

        // Normalize inputs before validation
        $data["email"] = strtolower(trim($data["email"] ?? ''));
        $data["phone"] = preg_replace('/\D+/', '', $data["phone"] ?? '');

        $fields = [
            "firstName" => ["First Name", "/^[a-zA-Z\s]{2,50}$/", "2-50 letters only"],
            "lastName" => ["Last Name", "/^[a-zA-Z\s]{2,50}$/", "2-50 letters only"],
            "email" => ["Email", FILTER_VALIDATE_EMAIL, "valid email"],
            "phone" => ["Phone", "/^\d{10,15}$/", "10-15 digits only"],
            "password" => [
                "Password",
                '/^.{5,}$/',
                "at least 5 characters long",
            ],
        ];

        $error = validateFields($data, $fields);

        if ($error) {
            returnJSON(["e" => true, "m" => $error]);
        }

        if (empty($data["captcha"]) || !captchaVerify($data["captcha"])) {
            returnJSON(["e" => true, "m" => "Captcha verification failed. Please try again."]);
        }

        // Check for disposable email
        if (isDisposableEmail($data["email"])) {
            returnJSON([
                "e" => true,
                "m" => "Disposable email addresses are not allowed. Please use a permanent email address (like Gmail, Outlook, etc.)."
            ]);
        }

        checkExists(
            $db,
            "users",
            "email",
            $data["email"],
            "Email is already being used",
            true
        );

        // Sanitize inputs
        $data["firstName"] = trim($data["firstName"]);
        $data["lastName"] = trim($data["lastName"]);
        if (!isset($data["image"]) || !is_array($data["image"])) {
            $data["image"] = ["tmp_name" => null, "size" => 0];
        } elseif (!array_key_exists("tmp_name", $data["image"])) {
            $data["image"]["tmp_name"] = null;
        }
        if (!array_key_exists("size", $data["image"])) {
            $data["image"]["size"] = 0;
        }
        
        // Handle image upload securely
        $imageName = null;
        if (is_empty($data["image"]["tmp_name"]) && !is_empty($_SESSION["gb_auth"]["picture"] ?? '')) {
            $imagePrefix = $this->get_unique_media_prefix();
            // save_picture_from_url returns filename for database storage
            $imageName = save_picture_from_url(
                $_SESSION["gb_auth"]["picture"],
                $imagePrefix,
                "low",
                "profile"
            );
        } elseif (!is_empty($data["image"]["tmp_name"] ?? '')) {
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $data["image"]["tmp_name"]);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                returnJSON(["e" => true, "m" => "Invalid image file type"]);
            }
            
            // Check file size (max 5MB)
            if ($data["image"]["size"] > 5242880) {
                returnJSON(["e" => true, "m" => "Image file too large (max 5MB)"]);
            }
            
            require_once __DIR__ . "/class-image.php";
            $image = new Image($data["image"]["tmp_name"]);
            $image_name = $this->get_unique_media_prefix() . $image->_img_ext;
            $dir = __DIR__ . "/../public_html/uploads/profile/";
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $path = $dir . $image_name;
            $image->save($path, "low");
            // Store just the filename for consistency
            $imageName = $image_name;
        }

        // Generate verification token
        $verificationToken = $this->generate_verification_token();
        $tokenExpires = date("Y-m-d H:i:s", strtotime("+24 hours"));

        $data["password"] = _password_hash($data["password"]);
        $publicProfileToken = $this->get_unique_public_profile_token();
        $stmt = $db->prepare(
            "INSERT INTO users (first_name, last_name, email, phone, image, password, email_verified, verification_token, verification_token_expires, reg_date, failed_login_attempts, public_profile_token, public_profile_enabled) 
             VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, 0, ?, 1)"
        );
        $stmt->bind_param(
            "ssssisssss",
            $data["firstName"],
            $data["lastName"],
            $data["email"],
            $data["phone"],
            $imageName,
            $data["password"],
            $verificationToken,
            $tokenExpires,
            $date,
            $publicProfileToken
        );
        $stmt->execute();
        $userId = $db->insert_id;
        $stmt->close();

        if (!is_empty($_SESSION["gb_auth"] ?? '')) {
            if (!is_empty($_SESSION["gb_auth"]["user_id"] ?? '')) {
                $stmt = $db->prepare(
                    "INSERT INTO gb_auth (user_id, auth) VALUES (?, ?)"
                );
                $stmt->bind_param(
                    "is",
                    $userId,
                    $_SESSION["gb_auth"]["user_id"]
                );
                $stmt->execute();
                $stmt->close();
            }
            unset($_SESSION["gb_auth"]);
        }

        // Send verification email
        $emailSent = $this->send_verification_email(
            $data["email"],
            $data["firstName"],
            $verificationToken
        );

        if (!$emailSent) {
            error_log("Failed to send verification email to: " . $data["email"]);
        }

        returnJSON([
            "e" => false,
            "m" => "Your account is created. Check your inbox to continue to the app.",
            "verify_required" => true,
        ]);
    }

    public function verify_email($token)
    {
        global $db, $date;

        if (empty($token) || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
            return ["e" => true, "m" => "Invalid verification token"];
        }

        $stmt = $db->prepare(
            "SELECT id, first_name, email, email_verified, verification_token_expires 
             FROM users WHERE verification_token = ? LIMIT 1"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ["e" => true, "m" => "Invalid verification token"];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user["email_verified"] == 1) {
            $this->create_session((int)$user["id"]);
            return [
                "e" => false,
                "m" => "Email already verified. Redirecting to your account.",
                "already_verified" => true,
                "auto_login" => true,
            ];
        }

        if (strtotime($user["verification_token_expires"]) < time()) {
            return [
                "e" => true,
                "m" => "Verification link has expired. Please request a new one.",
                "expired" => true,
            ];
        }

        $stmt = $db->prepare(
            "UPDATE users SET email_verified = 1, verification_token = NULL, verification_token_expires = NULL 
             WHERE id = ?"
        );
        $stmt->bind_param("i", $user["id"]);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            $body = get_email_template("welcome", [
                "name" => htmlspecialchars($user["first_name"], ENT_QUOTES, 'UTF-8'),
            ]);
            _email($user["email"], "Welcome to Apilage AI!", $body);

            $this->create_session((int)$user["id"]);
            return [
                "e" => false,
                "m" => "Email verified successfully! Redirecting to your account.",
                "auto_login" => true,
            ];
        } else {
            return [
                "e" => true,
                "m" => "Failed to verify email. Please try again.",
            ];
        }
    }

    public function resend_verification_email($email)
    {
        global $db;

        if (empty($email)) {
            returnJSON(["e" => true, "m" => "Email is required"]);
        }

        // Rate limiting
        $ip = get_user_ip();
        if (!$this->check_rate_limit("resend_verify_" . $ip, 3, 3600)) {
            returnJSON(["e" => true, "m" => "Too many requests. Please try again later."]);
        }

        $email = strtolower(trim($email));

        $stmt = $db->prepare(
            "SELECT id, first_name, email_verified FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            // Generic message to prevent email enumeration
            returnJSON(["e" => false, "m" => "If the email exists, a verification link has been sent."]);
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user["email_verified"] == 1) {
            returnJSON(["e" => true, "m" => "Email is already verified"]);
        }

        $verificationToken = $this->generate_verification_token();
        $tokenExpires = date("Y-m-d H:i:s", strtotime("+24 hours"));

        $stmt = $db->prepare(
            "UPDATE users SET verification_token = ?, verification_token_expires = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $verificationToken, $tokenExpires, $user["id"]);
        $stmt->execute();
        $stmt->close();

        $emailSent = $this->send_verification_email(
            $email,
            $user["first_name"],
            $verificationToken
        );

        if ($emailSent) {
            returnJSON([
                "e" => false,
                "m" => "Verification email sent! Please check your inbox.",
            ]);
        } else {
            returnJSON([
                "e" => true,
                "m" => "Failed to send verification email. Please try again later.",
            ]);
        }
    }

    public function google_sign_in()
    {
        global $db, $date;

        $linkUserId = null;
        if ($this->_logged_in) {
            if (!isset($_GET["code"])) {
                $_SESSION["link_google_user_id"] = $this->_data['id'];
            } else {
                $linkUserId = isset($_SESSION["link_google_user_id"]) ? (int)$_SESSION["link_google_user_id"] : null;
            }
        }

        $client = new Google_Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $client->addScope("email");
        $client->addScope("profile");

        if (isset($_GET["code"])) {
            try {
                $token = $client->fetchAccessTokenWithAuthCode($_GET["code"]);

                if (isset($token["error"])) {
                    throw new Exception($token["error_description"]);
                }

                $client->setAccessToken($token);
                $oauth = new Google_Service_Oauth2($client);
                $userInfo = $oauth->userinfo->get();

                $googleId = $userInfo->id;
                $email = strtolower(trim($userInfo->email));
                $firstName = trim($userInfo->givenName);
                $lastName = trim($userInfo->familyName);
                $picture = $userInfo->picture;

                // Check for disposable email
                if (isDisposableEmail($email)) {
                    error_log("Attempted Google Sign-In with disposable email: $email");
                    // Clear output buffers and redirect
                    if (ob_get_level() > 0) ob_end_clean();
                    header("Location: " . APP_URL . "/auth/login?error=disposable_email_not_allowed");
                    exit();
                }

                $stmt = $db->prepare(
                    "SELECT user_id FROM google_auth WHERE google_id = ? LIMIT 1"
                );
                $stmt->bind_param("s", $googleId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($linkUserId) {
                    if ($result->num_rows === 1) {
                        $row = $result->fetch_assoc();
                        $stmt->close();
                        if ((int)$row["user_id"] !== (int)$linkUserId) {
                            unset($_SESSION["link_google_user_id"]);
                            if (ob_get_level() > 0) ob_end_clean();
                            header("Location: " . APP_URL . "/app?error=google_already_linked");
                            exit();
                        }
                        $this->maybe_update_user_image_from_url($linkUserId, $picture);
                        unset($_SESSION["link_google_user_id"]);
                        if (ob_get_level() > 0) ob_end_clean();
                        header("Location: " . APP_URL . "/app");
                        exit();
                    }
                    $stmt->close();

                    $stmt = $db->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
                    $stmt->bind_param("i", $linkUserId);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $db->prepare(
                        "INSERT INTO google_auth (user_id, google_id, google_email) VALUES (?, ?, ?)"
                    );
                    $stmt->bind_param("iss", $linkUserId, $googleId, $email);
                    $stmt->execute();
                    $stmt->close();

                    $this->maybe_update_user_image_from_url($linkUserId, $picture);
                    unset($_SESSION["link_google_user_id"]);
                    if (ob_get_level() > 0) ob_end_clean();
                    header("Location: " . APP_URL . "/app");
                    exit();
                }

                if ($result->num_rows === 1) {
                    $row = $result->fetch_assoc();
                    $stmt->close();
                    $this->maybe_update_user_image_from_url((int)$row["user_id"], $picture);
                    $this->create_session($row["user_id"]);
                    if (ob_get_level() > 0) ob_end_clean();
                    header("Location: " . APP_URL . "/app");
                    exit();
                } else {
                    $stmt->close();

                    $stmt = $db->prepare(
                        "SELECT id FROM users WHERE email = ? LIMIT 1"
                    );
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $row = $result->fetch_assoc();
                        $userId = $row["id"];
                        $stmt->close();

                        $this->link_google_auth_by_email((int)$userId, $email);

                        $stmt = $db->prepare(
                            "UPDATE users SET email_verified = 1 WHERE id = ?"
                        );
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $stmt->close();

                        $stmt = $db->prepare(
                            "INSERT INTO google_auth (user_id, google_id, google_email) VALUES (?, ?, ?)"
                        );
                        $stmt->bind_param("iss", $userId, $googleId, $email);
                        $stmt->execute();
                        $stmt->close();

                        $this->maybe_update_user_image_from_url((int)$userId, $picture);
                        $this->create_session($userId);
                        if (ob_get_level() > 0) ob_end_clean();
                        header("Location: " . APP_URL . "/app");
                        exit();
                    } else {
                        $stmt->close();

                        $imageName = null;
                        if ($picture) {
                            $imagePrefix = $this->get_unique_media_prefix();
                            // Save profile picture to profile folder - returns filename
                            $imageName = save_picture_from_url(
                                $picture,
                                $imagePrefix,
                                "low",
                                "profile"
                            );
                        }

                        $randomPassword = _password_hash(bin2hex(random_bytes(32)));
                        $phone = '';

                        $publicProfileToken = $this->get_unique_public_profile_token();
                        $stmt = $db->prepare(
                            "INSERT INTO users (first_name, last_name, email, phone, image, password, email_verified, reg_date, failed_login_attempts, public_profile_token, public_profile_enabled) 
                             VALUES (?, ?, ?, ?, ?, ?, 1, ?, 0, ?, 1)"
                        );
                        $stmt->bind_param(
                            "ssssssss",
                            $firstName,
                            $lastName,
                            $email,
                            $phone,
                            $imageName,
                            $randomPassword,
                            $date,
                            $publicProfileToken
                        );
                        $stmt->execute();
                        $userId = $db->insert_id;
                        $stmt->close();

                        $stmt = $db->prepare(
                            "INSERT INTO google_auth (user_id, google_id, google_email) VALUES (?, ?, ?)"
                        );
                        $stmt->bind_param("iss", $userId, $googleId, $email);
                        $stmt->execute();
                        $stmt->close();

                        $this->create_session($userId);
                        if (ob_get_level() > 0) ob_end_clean();
                        header("Location: " . APP_URL . "/app");
                        exit();
                    }
                }
            } catch (Exception $e) {
                error_log("Google Sign-In Error: " . $e->getMessage());
                if (ob_get_level() > 0) ob_end_clean();
                header(
                    "Location: " . APP_URL . "/auth/login?error=google_auth_failed"
                );
                exit();
            }
        } else {
            $authUrl = $client->createAuthUrl();
            if (ob_get_level() > 0) ob_end_clean();
            header("Location: " . filter_var($authUrl, FILTER_SANITIZE_URL));
            exit();
        }
    }

    public function facebook_sign_in()
    {
        global $db, $date;

        $linkUserId = null;
        if ($this->_logged_in) {
            if (!isset($_GET["code"]) && empty($_GET["error"])) {
                $_SESSION["link_facebook_user_id"] = $this->_data['id'];
            } else {
                $linkUserId = isset($_SESSION["link_facebook_user_id"]) ? (int)$_SESSION["link_facebook_user_id"] : null;
            }
        }

        $appId = defined('FACEBOOK_APP_ID') ? FACEBOOK_APP_ID : '';
        $appSecret = defined('FACEBOOK_APP_SECRET') ? FACEBOOK_APP_SECRET : '';
        $redirectUri = (defined('FACEBOOK_REDIRECT_URI') && FACEBOOK_REDIRECT_URI)
            ? FACEBOOK_REDIRECT_URI
            : (APP_URL . '/auth/facebook-callback');

        if (empty($appId) || empty($appSecret)) {
            error_log("Facebook OAuth not configured");
            if (ob_get_level() > 0) ob_end_clean();
            header("Location: " . APP_URL . "/auth/login?error=facebook_auth_failed");
            exit();
        }

        if (!isset($_GET["code"])) {
            if (!empty($_GET["error"])) {
                $err = $_GET["error_description"] ?? $_GET["error_reason"] ?? $_GET["error"];
                error_log("Facebook OAuth error: " . $err);
                if (ob_get_level() > 0) ob_end_clean();
                header("Location: " . APP_URL . "/auth/login?error=facebook_auth_failed");
                exit();
            }

            $state = bin2hex(random_bytes(16));
            $_SESSION["facebook_oauth_state"] = $state;

            $authUrl = 'https://www.facebook.com/v20.0/dialog/oauth?' . http_build_query([
                'client_id' => $appId,
                'redirect_uri' => $redirectUri,
                'state' => $state,
                'scope' => 'email,public_profile',
                'response_type' => 'code',
            ]);
            if (ob_get_level() > 0) ob_end_clean();
            header("Location: " . filter_var($authUrl, FILTER_SANITIZE_URL));
            exit();
        }

        $state = $_GET["state"] ?? '';
        $expectedState = $_SESSION["facebook_oauth_state"] ?? '';
        unset($_SESSION["facebook_oauth_state"]);

        if (empty($state) || empty($expectedState) || !hash_equals($expectedState, $state)) {
            error_log("Facebook OAuth state mismatch");
            if (ob_get_level() > 0) ob_end_clean();
            header("Location: " . APP_URL . "/auth/login?error=facebook_auth_failed");
            exit();
        }

        try {
            $tokenData = $this->http_get_json('https://graph.facebook.com/v20.0/oauth/access_token', [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
                'code' => $_GET["code"],
            ]);
            $accessToken = $tokenData["access_token"] ?? '';

            if (empty($accessToken)) {
                throw new Exception("Facebook access token missing");
            }

            $profileData = $this->http_get_json('https://graph.facebook.com/me', [
                'fields' => 'id,first_name,last_name,email,picture.type(large)',
                'access_token' => $accessToken,
            ]);

            $facebookId = $profileData["id"] ?? '';
            $email = strtolower(trim((string)($profileData["email"] ?? '')));
            $firstName = trim((string)($profileData["first_name"] ?? ''));
            $lastName = trim((string)($profileData["last_name"] ?? ''));
            $picture = null;
            if (!empty($profileData['picture']['data']['url'])) {
                $picture = $profileData['picture']['data']['url'];
            }

            if (empty($facebookId)) {
                throw new Exception("Facebook user id missing");
            }

            if (empty($email)) {
                if (ob_get_level() > 0) ob_end_clean();
                header("Location: " . APP_URL . "/auth/login?error=facebook_email_required");
                exit();
            }

            if (isDisposableEmail($email)) {
                error_log("Attempted Facebook Sign-In with disposable email: $email");
                if (ob_get_level() > 0) ob_end_clean();
                header("Location: " . APP_URL . "/auth/login?error=disposable_email_not_allowed");
                exit();
            }

            $stmt = $db->prepare(
                "SELECT user_id FROM facebook_auth WHERE facebook_id = ? LIMIT 1"
            );
            $stmt->bind_param("s", $facebookId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($linkUserId) {
                if ($result->num_rows === 1) {
                    $row = $result->fetch_assoc();
                    $stmt->close();
                    if ((int)$row["user_id"] !== (int)$linkUserId) {
                        unset($_SESSION["link_facebook_user_id"]);
                        if (ob_get_level() > 0) ob_end_clean();
                        header("Location: " . APP_URL . "/app?error=facebook_already_linked");
                        exit();
                    }
                    $this->maybe_update_user_image_from_url($linkUserId, $picture);
                    unset($_SESSION["link_facebook_user_id"]);
                    if (ob_get_level() > 0) ob_end_clean();
                    header("Location: " . APP_URL . "/app");
                    exit();
                }
                $stmt->close();

                $stmt = $db->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
                $stmt->bind_param("i", $linkUserId);
                $stmt->execute();
                $stmt->close();

                $stmt = $db->prepare(
                    "INSERT INTO facebook_auth (user_id, facebook_id, facebook_email) VALUES (?, ?, ?)"
                );
                $stmt->bind_param("iss", $linkUserId, $facebookId, $email);
                $stmt->execute();
                $stmt->close();

                $this->maybe_update_user_image_from_url($linkUserId, $picture);
                unset($_SESSION["link_facebook_user_id"]);
                if (ob_get_level() > 0) ob_end_clean();
                header("Location: " . APP_URL . "/app");
                exit();
            }

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $stmt->close();
                $this->maybe_update_user_image_from_url((int)$row["user_id"], $picture);
                $this->create_session($row["user_id"]);
                if (ob_get_level() > 0) ob_end_clean();
                header("Location: " . APP_URL . "/app");
                exit();
            }

            $stmt->close();

            $stmt = $db->prepare(
                "SELECT id FROM users WHERE email = ? LIMIT 1"
            );
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $userId = $row["id"];
                $stmt->close();

                $this->link_facebook_auth_by_email((int)$userId, $email);

                $stmt = $db->prepare(
                    "UPDATE users SET email_verified = 1 WHERE id = ?"
                );
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();

                $stmt = $db->prepare(
                    "INSERT INTO facebook_auth (user_id, facebook_id, facebook_email) VALUES (?, ?, ?)"
                );
                $stmt->bind_param("iss", $userId, $facebookId, $email);
                $stmt->execute();
                $stmt->close();

                $this->maybe_update_user_image_from_url((int)$userId, $picture);
                $this->create_session($userId);
                if (ob_get_level() > 0) ob_end_clean();
                header("Location: " . APP_URL . "/app");
                exit();
            } else {
                $stmt->close();

                $imageName = null;
                if ($picture) {
                    $imagePrefix = $this->get_unique_media_prefix();
                    // Save profile picture to profile folder - returns filename
                    $imageName = save_picture_from_url(
                        $picture,
                        $imagePrefix,
                        "low",
                        "profile"
                    );
                }

                $randomPassword = _password_hash(bin2hex(random_bytes(32)));
                $phone = '';

                $publicProfileToken = $this->get_unique_public_profile_token();
                $stmt = $db->prepare(
                    "INSERT INTO users (first_name, last_name, email, phone, image, password, email_verified, reg_date, failed_login_attempts, public_profile_token, public_profile_enabled) 
                     VALUES (?, ?, ?, ?, ?, ?, 1, ?, 0, ?, 1)"
                );
                $stmt->bind_param(
                    "ssssssss",
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $imageName,
                    $randomPassword,
                    $date,
                    $publicProfileToken
                );
                $stmt->execute();
                $userId = $db->insert_id;
                $stmt->close();

                $stmt = $db->prepare(
                    "INSERT INTO facebook_auth (user_id, facebook_id, facebook_email) VALUES (?, ?, ?)"
                );
                $stmt->bind_param("iss", $userId, $facebookId, $email);
                $stmt->execute();
                $stmt->close();

                $this->create_session($userId);
                if (ob_get_level() > 0) ob_end_clean();
                header("Location: " . APP_URL . "/app");
                exit();
            }
        } catch (Exception $e) {
            error_log("Facebook Sign-In Error: " . $e->getMessage());
            if (ob_get_level() > 0) ob_end_clean();
            header("Location: " . APP_URL . "/auth/login?error=facebook_auth_failed");
            exit();
        }
    }

    public function facebook_deauthorize()
    {
        global $db;

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);
            header('Content-Type: text/plain; charset=utf-8');
            echo "Method not allowed";
            exit();
        }

        $signedRequest = $_POST["signed_request"] ?? "";
        if ($signedRequest === "") {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo "Missing signed_request";
            exit();
        }

        $appSecret = defined('FACEBOOK_APP_SECRET') ? FACEBOOK_APP_SECRET : '';
        if ($appSecret === "") {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo "Facebook not configured";
            exit();
        }

        $data = $this->parse_facebook_signed_request($signedRequest, $appSecret);
        if (!$data || empty($data["user_id"])) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo "Invalid signed_request";
            exit();
        }

        $facebookId = (string) $data["user_id"];
        $stmt = $db->prepare("SELECT user_id FROM facebook_auth WHERE facebook_id = ? LIMIT 1");
        $stmt->bind_param("s", $facebookId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!empty($row["user_id"])) {
            $this->delete_user_account_by_id((int) $row["user_id"]);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["success" => true]);
        exit();
    }

    function g_register($auth_key)
    {
        global $db, $smarty;
        require_once __DIR__ . "/includes/libs/globbook_auth.php";

        if ($this->_logged_in) {
            returnJSON(["e" => true, "m" => "You're already logged in"]);
        }

        try {
            $GlobbookAuth = new GlobbookAuthAPI(
                GLOBBOOK_APP_ID,
                GLOBBOOK_APP_SECRET
            );
            $GlobbookAuth->authenticate($auth_key);
            $profile = $GlobbookAuth->getUserInfo();

            $stmt = $db->prepare(
                "SELECT user_id FROM gb_auth WHERE auth = ? LIMIT 1"
            );
            $stmt->bind_param("s", $profile->user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $val = $result->fetch_assoc();
                $stmt->close();
                $this->create_session($val["user_id"]);
                header("Location: " . APP_URL . "/app");
                exit();
            } else {
                $stmt->close();
                $_SESSION["gb_auth"] = [
                    "user_id" => $profile->user_id,
                    "picture" => filter_var($profile->picture, FILTER_SANITIZE_URL),
                ];
                $smarty->assign("view", "register");
                $smarty->assign("profile", $profile);
                page_header("Apilage AI | Register");
                page_footer("register");
                exit();
            }
        } catch (Exception $e) {
            unset($_SESSION["gb_auth"]);
            error_log("Globbook auth failed: " . $e->getMessage());
            echo "Authentication failed";
        }
    }

    public function create_session(int $userId)
    {
        global $db, $date;

        $session_token = bin2hex(random_bytes(32)); // Use secure random token
        $expire = time() + 31536000;
        $ip = get_user_ip();
        $client = $_SERVER["HTTP_USER_AGENT"] ?? 'Unknown';

        // Limit active sessions per user
        $stmt = $db->prepare(
            "SELECT COUNT(*) as session_count FROM sessions WHERE user_id = ? AND active = '1'"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // If more than 5 active sessions, deactivate oldest
        if ($row['session_count'] >= 5) {
            $stmt = $db->prepare(
                "UPDATE sessions SET active = '0' 
                 WHERE user_id = ? AND active = '1' 
                 ORDER BY last_seen ASC LIMIT 1"
            );
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $db->prepare(
            "INSERT INTO sessions (user_id, token, ip, client, start, last_seen, active) 
             VALUES (?, ?, ?, ?, ?, ?, '1')"
        );
        $stmt->bind_param(
            "isssss",
            $userId,
            $session_token,
            $ip,
            $client,
            $date,
            $date
        );
        $stmt->execute();
        $stmt->close();

        $authCookieSameSite = 'Lax';
        setSecureCookie($this->_cookie_user_id, $userId, $expire, "/", null, $authCookieSameSite);
        setSecureCookie($this->_cookie_user_token, $session_token, $expire, "/", null, $authCookieSameSite);

        $stmt = $db->prepare(
            "SELECT first_name, last_name, email FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $body = get_email_template("new_device_signin", [
                "name" => htmlspecialchars("{$user["first_name"]} {$user["last_name"]}", ENT_QUOTES, 'UTF-8'),
                "device" => user_agent_array($client),
                "ip" => $ip,
            ]);
            _email($user["email"], "New Device Sign-in - Apilage AI", $body);
        }
        $stmt->close();
    }

    public function sign_out()
    {
        global $db;

        if (!isset($_COOKIE[$this->_cookie_user_id]) || !isset($_COOKIE[$this->_cookie_user_token])) {
            $this->unset_cookies();
            return;
        }

        $stmt = $db->prepare(
            "UPDATE sessions SET active = '0' WHERE user_id = ? AND token = ?"
        );
        $stmt->bind_param(
            "is",
            $_COOKIE[$this->_cookie_user_id],
            $_COOKIE[$this->_cookie_user_token]
        );
        $stmt->execute();
        $stmt->close();

        $this->unset_cookies();
    }

    private function generate_reset_token()
    {
        return bin2hex(random_bytes(32));
    }

    private function invalidate_magic_tokens($userId, $purpose)
    {
        global $db;
        $stmt = $db->prepare("UPDATE magic_login_tokens SET used_at = NOW() WHERE user_id = ? AND purpose = ? AND used_at IS NULL");
        $stmt->bind_param("is", $userId, $purpose);
        $stmt->execute();
        $stmt->close();
    }

    private function create_magic_token_record($userId, $purpose, $expiresMinutes)
    {
        global $db;
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', strtotime('+' . (int)$expiresMinutes . ' minutes'));
        $ip = get_user_ip();
        $client = $_SERVER["HTTP_USER_AGENT"] ?? 'Unknown';

        $stmt = $db->prepare(
            "INSERT INTO magic_login_tokens (user_id, token_hash, purpose, expires_at, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssss", $userId, $tokenHash, $purpose, $expires, $ip, $client);
        $stmt->execute();
        $stmt->close();

        return $token;
    }

    private function maybe_update_user_image_from_url($userId, $pictureUrl)
    {
        global $db;

        if (empty($userId) || empty($pictureUrl)) {
            return;
        }

        $stmt = $db->prepare("SELECT image FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!empty($row['image'])) {
            $current = trim((string)$row['image']);
            if ($current !== '') {
                $lower = strtolower($current);
                $isLocal = false;
                if (strpos($lower, '/uploads/') !== false || strpos($lower, 'uploads/') !== false || preg_match('#\\.[a-z0-9]{2,5}$#i', $lower)) {
                    if (strpos($lower, 'http://') === 0 || strpos($lower, 'https://') === 0) {
                        $host = parse_url($lower, PHP_URL_HOST);
                        $appHost = parse_url(APP_URL, PHP_URL_HOST);
                        $uploadsHost = parse_url(UPLOADS_BASE_URL, PHP_URL_HOST);
                        if ($host && ($host === $appHost || $host === $uploadsHost)) {
                            $isLocal = true;
                        }
                    } else {
                        $isLocal = true;
                    }
                }
                if ($isLocal) {
                    return;
                }
            }
        }

        try {
            $imagePrefix = $this->get_unique_media_prefix();
            // Save profile picture to profile folder (filename only)
            $imageName = save_picture_from_url($pictureUrl, $imagePrefix, "low", "profile");
            if (!empty($imageName)) {
                $stmt = $db->prepare("UPDATE users SET image = ? WHERE id = ?");
                $stmt->bind_param("si", $imageName, $userId);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $e) {
            error_log("Failed to save Google profile image: " . $e->getMessage());
        }
    }

    private function send_password_reset_email($email, $firstName, $token)
    {
        $firstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
        $resetLink = APP_URL . "/auth/reset-password?token=" . urlencode($token);

        $body = get_email_template("password_reset", [
            "name" => $firstName,
            "reset_link" => $resetLink
        ]);

        return _email($email, "Password Reset Request - Apilage AI", $body);
    }

    private function send_magic_login_email($email, $firstName, $token)
    {
        $firstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
        $loginLink = APP_URL . "/auth/magic-login?token=" . urlencode($token);

        $body = get_email_template("magic_login", [
            "name" => $firstName,
            "login_link" => $loginLink
        ]);

        return _email($email, "Your ApilageAI Login Link", $body);
    }

    public function request_password_reset($email)
    {
        global $db;

        // Rate limiting
        $ip = get_user_ip();
        if (!$this->check_rate_limit("reset_password_" . $ip, 3, 3600)) {
            returnJSON(["e" => true, "m" => "Too many password reset requests. Please try again later."]);
        }

        if (empty($email)) {
            returnJSON(["e" => true, "m" => "Email is required"]);
        }

        $email = strtolower(trim($email));

        $stmt = $db->prepare("SELECT id, first_name FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            // Generic message to prevent email enumeration
            returnJSON(["e" => false, "m" => "If the email exists, password reset instructions have been sent."]);
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        $this->invalidate_magic_tokens((int)$user['id'], 'password_reset');
        $token = $this->create_magic_token_record((int)$user['id'], 'password_reset', 5);

        $send = $this->send_password_reset_email($email, $user['first_name'], $token);

        if ($send) {
            returnJSON(["e" => false, "m" => "Password reset instructions sent to your email"]);
        } else {
            error_log("Failed to send password reset email to: " . $email);
            returnJSON(["e" => true, "m" => "Failed to send reset email. Please try again later."]);
        }
    }

    public function request_password_reset_for_user($userId, $email)
    {
        global $db;

        $ip = get_user_ip();
        if (!$this->check_rate_limit("reset_password_" . $ip, 3, 3600)) {
            return ["e" => true, "m" => "Too many password reset requests. Please try again later."];
        }

        $email = strtolower(trim($email));
        if (empty($email)) {
            return ["e" => true, "m" => "Email is required"];
        }

        $stmt = $db->prepare("SELECT id, first_name, email FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            return ["e" => true, "m" => "User not found"];
        }
        $userRow = $result->fetch_assoc();
        $stmt->close();

        if (strtolower(trim($userRow['email'])) !== $email) {
            return ["e" => true, "m" => "Email does not match your account"];
        }

        $this->invalidate_magic_tokens((int)$userRow['id'], 'password_reset');
        $token = $this->create_magic_token_record((int)$userRow['id'], 'password_reset', 5);

        $send = $this->send_password_reset_email($email, $userRow['first_name'], $token);
        if ($send) {
            return ["e" => false, "m" => "Password reset instructions sent to your email"];
        }

        error_log("Failed to send password reset email to: " . $email);
        return ["e" => true, "m" => "Failed to send reset email. Please try again later."];
    }

    public function verify_reset_token($token)
    {
        global $db;

        if (empty($token) || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
            return ["e" => true, "m" => "Invalid reset token"];
        }

        $tokenHash = hash('sha256', $token);
        $stmt = $db->prepare(
            "SELECT t.user_id, t.expires_at, t.used_at
             FROM magic_login_tokens t
             WHERE t.token_hash = ? AND t.purpose = 'password_reset' LIMIT 1"
        );
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ["e" => true, "m" => "Invalid reset token"];
        }

        $row = $result->fetch_assoc();
        $stmt->close();

        if (!empty($row['used_at'])) {
            return ["e" => true, "m" => "Reset link already used"];
        }

        if (strtotime($row['expires_at']) < time()) {
            return ["e" => true, "m" => "Reset token expired"];
        }

        return ["e" => false, "user_id" => (int)$row['user_id']];
    }

    public function reset_password($token, $newPassword)
    {
        global $db;

        // Validate password strength (min 5 characters)
        if (!preg_match('/^.{5,}$/', $newPassword)) {
            return ["e" => true, "m" => "Password must be at least 5 characters."];
        }

        $verification = $this->verify_reset_token($token);

        if ($verification['e']) {
            return $verification;
        }

        $userId = $verification['user_id'];
        $hashedPassword = _password_hash($newPassword);

        $stmt = $db->prepare(
            "UPDATE users SET password = ?, failed_login_attempts = 0, locked_until = NULL WHERE id = ?"
        );
        $stmt->bind_param("si", $hashedPassword, $userId);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            $tokenHash = hash('sha256', $token);
            $stmt = $db->prepare("UPDATE magic_login_tokens SET used_at = NOW() WHERE token_hash = ? AND purpose = 'password_reset'");
            $stmt->bind_param("s", $tokenHash);
            $stmt->execute();
            $stmt->close();

            // Invalidate all sessions for security
            $stmt = $db->prepare("UPDATE sessions SET active = '0' WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            return ["e" => false, "m" => "Password reset successful. Please sign in with your new password."];
        } else {
            return ["e" => true, "m" => "Failed to reset password"];
        }
    }

    public function request_magic_login($email, $captcha)
    {
        global $db;

        $ip = get_user_ip();
        if (!$this->check_rate_limit("magic_login_" . $ip, 3, 600)) {
            returnJSON(["e" => true, "m" => "Too many requests. Please try again later."]);
        }

        $email = strtolower(trim($email));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            returnJSON(["e" => true, "m" => "Valid email is required"]);
        }

        if (empty($captcha) || !captchaVerify($captcha)) {
            returnJSON(["e" => true, "m" => "Captcha verification failed. Please try again."]);
        }

        $stmt = $db->prepare("SELECT id, first_name, email_verified FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            returnJSON(["e" => true, "m" => "Email not found"]);
        }
        $user = $result->fetch_assoc();
        $stmt->close();

        if ((int)$user['email_verified'] !== 1) {
            returnJSON(["e" => true, "m" => "Please verify your email before requesting a login link."]);
        }

        $this->invalidate_magic_tokens((int)$user['id'], 'login');
        $token = $this->create_magic_token_record((int)$user['id'], 'login', 5);

        $send = $this->send_magic_login_email($email, $user['first_name'], $token);
        if ($send) {
            returnJSON(["e" => false, "m" => "Login link sent to your email"]);
        }

        error_log("Failed to send magic login email to: " . $email);
        returnJSON(["e" => true, "m" => "Failed to send login link. Please try again later."]);
    }

    public function login_with_magic_token($token)
    {
        global $db;

        if (empty($token) || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
            return ["e" => true, "m" => "Invalid login token"];
        }

        $tokenHash = hash('sha256', $token);
        $stmt = $db->prepare(
            "SELECT t.user_id, t.expires_at, t.used_at, u.email_verified
             FROM magic_login_tokens t
             JOIN users u ON u.id = t.user_id
             WHERE t.token_hash = ? AND t.purpose = 'login' LIMIT 1"
        );
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            return ["e" => true, "m" => "Invalid login token"];
        }
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row['used_at']) {
            return ["e" => true, "m" => "Login link already used"];
        }
        if (strtotime($row['expires_at']) < time()) {
            return ["e" => true, "m" => "Login link expired"];
        }
        if ((int)$row['email_verified'] !== 1) {
            return ["e" => true, "m" => "Email not verified"];
        }

        $stmt = $db->prepare("UPDATE magic_login_tokens SET used_at = NOW() WHERE token_hash = ? AND purpose = 'login'");
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $stmt->close();

        $this->create_session((int)$row['user_id']);
        return ["e" => false];
    }

    public function unset_cookies()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        unset($_COOKIE[$this->_cookie_user_id]);
        unset($_COOKIE[$this->_cookie_user_token]);
        $authCookieSameSite = 'Lax';
        setSecureCookie($this->_cookie_user_id, "", -1, "/", null, $authCookieSameSite);
        setSecureCookie($this->_cookie_user_token, "", -1, "/", null, $authCookieSameSite);
    }

    public function conversation_exists($id): bool
    {
        global $db;

        if (!is_numeric($id)) {
            return false;
        }

        $stmt = $db->prepare(
            "SELECT 1 FROM conversations WHERE user_id = ? AND conversation_id = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $this->_data["user_id"], $id);
        $stmt->execute();
        $result = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        
        return $result;
    }

    public function get_conversations(): array
    {
        global $db;

        $stmt = $db->prepare(
            "SELECT conversation_id, title FROM conversations WHERE user_id = ? ORDER BY conversation_id DESC"
        );
        $stmt->bind_param("i", $this->_data["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversations = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $conversations;
    }
}
?>
