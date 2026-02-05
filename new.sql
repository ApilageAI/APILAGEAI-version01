-- Magic login tokens for email login links
CREATE TABLE IF NOT EXISTS `magic_login_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `purpose` varchar(32) NOT NULL DEFAULT 'login',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token_hash` (`token_hash`),
  KEY `idx_user` (`user_id`),
  KEY `idx_purpose` (`purpose`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add purpose column if the table already exists
ALTER TABLE `magic_login_tokens`
  ADD COLUMN IF NOT EXISTS `purpose` varchar(32) NOT NULL DEFAULT 'login',
  ADD INDEX IF NOT EXISTS `idx_purpose` (`purpose`);
