-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 12, 2026 at 06:06 AM
-- Server version: 10.5.29-MariaDB
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apilageai_lk`
--

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option` varchar(256) DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `score_change` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `created_at` datetime NOT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_canvas`
--

CREATE TABLE `conversation_canvas` (
  `conversation_id` bigint(20) NOT NULL,
  `data` longtext NOT NULL,
  `version` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','collaborator') NOT NULL DEFAULT 'collaborator',
  `added_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `permission_level` int(11) DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_published`
--

CREATE TABLE `conversation_published` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `publish_token` varchar(64) NOT NULL,
  `access_level` enum('read_only','read_write') NOT NULL DEFAULT 'read_only',
  `published_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_share_links`
--

CREATE TABLE `conversation_share_links` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `shared_by` int(11) NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `free_user_daily_usage`
--

CREATE TABLE `free_user_daily_usage` (
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `messages_used` int(11) DEFAULT 0,
  `image_uploads_used` int(11) DEFAULT 0,
  `file_uploads_used` int(11) DEFAULT 0,
  `image_generations_used` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `free_user_limits`
--

CREATE TABLE `free_user_limits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_uses` int(11) DEFAULT 0,
  `last_reset` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `mode` enum('single','multi') NOT NULL,
  `subject` varchar(128) DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `term` varchar(32) DEFAULT NULL,
  `focus` text DEFAULT NULL,
  `language` varchar(16) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gb_auth`
--

CREATE TABLE `gb_auth` (
  `user_id` int(11) NOT NULL,
  `auth` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `generated_images`
--

CREATE TABLE `generated_images` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_url` varchar(512) NOT NULL,
  `prompt` text DEFAULT NULL,
  `author_name` varchar(128) DEFAULT NULL,
  `public` tinyint(1) DEFAULT 0,
  `likes` int(11) DEFAULT 0,
  `unlikes` int(11) DEFAULT 0,
  `generated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `google_auth`
--

CREATE TABLE `google_auth` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `google_id` varchar(255) NOT NULL,
  `google_email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `image_reactions`
--

CREATE TABLE `image_reactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL,
  `reaction` enum('like','unlike') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('1','2','3') NOT NULL DEFAULT '1',
  `used_model` varchar(255) NOT NULL DEFAULT 'APILAGEAI',
  `created_at` datetime NOT NULL,
  `text` longtext NOT NULL,
  `thinking_text` longtext DEFAULT NULL,
  `thinking_tokens` int(11) DEFAULT 0,
  `attach` longtext DEFAULT NULL COMMENT 'Attachment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notific`
--

CREATE TABLE `notific` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `options` longtext NOT NULL,
  `answer` varchar(256) NOT NULL,
  `explanation` text DEFAULT NULL,
  `mark` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `ip` mediumtext NOT NULL,
  `client` mediumtext NOT NULL,
  `last_seen` datetime NOT NULL,
  `start` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thinking_usage_logs`
--

CREATE TABLE `thinking_usage_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `thinking_tokens` int(11) DEFAULT 0,
  `thinking_budget` int(11) DEFAULT 0,
  `thinking_cost_lkr` decimal(10,4) DEFAULT 0.0000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `invoice_id` tinytext NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(12) NOT NULL,
  `paid` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `payable_uid` varchar(100) DEFAULT NULL,
  `status_indicator` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usage_logs`
--

CREATE TABLE `usage_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `model_used` varchar(64) DEFAULT NULL,
  `input_tokens` int(11) DEFAULT NULL,
  `output_tokens` int(11) DEFAULT NULL,
  `input_cost_lkr` decimal(10,4) DEFAULT NULL,
  `output_cost_lkr` decimal(10,4) DEFAULT NULL,
  `total_cost_lkr` decimal(10,4) DEFAULT NULL,
  `profit_added_lkr` decimal(10,4) DEFAULT NULL,
  `total_final_cost_lkr` decimal(10,4) DEFAULT NULL,
  `balance_before` decimal(10,2) DEFAULT NULL,
  `balance_after` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(256) NOT NULL,
  `last_name` varchar(256) NOT NULL,
  `email` text NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Email verification status',
  `verification_token` varchar(64) DEFAULT NULL COMMENT 'Email verification token',
  `verification_token_expires` datetime DEFAULT NULL COMMENT 'Token expiration time',
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `phone` int(11) NOT NULL,
  `image` mediumtext DEFAULT NULL,
  `balance` int(11) NOT NULL DEFAULT 0,
  `password` varchar(64) NOT NULL,
  `type` enum('1','2','3') NOT NULL DEFAULT '1',
  `reg_date` datetime NOT NULL COMMENT 'Registered Date and Time',
  `memory` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Stores up to 10 memory points for the user\r\n',
  `subscription_status` tinyint(1) NOT NULL DEFAULT 0,
  `onboard_complete` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_balance_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    -- Only fire if the balance actually increased
    IF NEW.balance > OLD.balance THEN
        INSERT INTO notific (user_id, message, is_read, created_at)
        VALUES (
            NEW.id,
            CONCAT('Your balance is updated to ', NEW.balance),
            0,
            NOW()
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_onboarding`
--

CREATE TABLE `user_onboarding` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `school` varchar(256) DEFAULT NULL,
  `not_student` tinyint(1) DEFAULT 0,
  `interests` text DEFAULT NULL,
  `preference` enum('friendly','educational','explanatory','concise') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `conversations_ibfk_1` (`user_id`),
  ADD KEY `idx_published` (`is_published`,`created_at`);

--
-- Indexes for table `conversation_canvas`
--
ALTER TABLE `conversation_canvas`
  ADD PRIMARY KEY (`conversation_id`);

--
-- Indexes for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_conversation_user` (`conversation_id`,`user_id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_added_by` (`added_by`);

--
-- Indexes for table `conversation_published`
--
ALTER TABLE `conversation_published`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `publish_token` (`publish_token`),
  ADD KEY `idx_conversation_id` (`conversation_id`),
  ADD KEY `idx_published_by` (`published_by`);

--
-- Indexes for table `conversation_share_links`
--
ALTER TABLE `conversation_share_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_conversation_target` (`conversation_id`, `target_user_id`),
  ADD KEY `idx_shared_by` (`shared_by`);

--
-- Indexes for table `free_user_daily_usage`
--
ALTER TABLE `free_user_daily_usage`
  ADD PRIMARY KEY (`user_id`,`date`);

--
-- Indexes for table `free_user_limits`
--
ALTER TABLE `free_user_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user` (`user_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gb_auth`
--
ALTER TABLE `gb_auth`
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `generated_images`
--
ALTER TABLE `generated_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_genimg_user` (`user_id`),
  ADD KEY `idx_genimg_public` (`public`);

--
-- Indexes for table `google_auth`
--
ALTER TABLE `google_auth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `image_reactions`
--
ALTER TABLE `image_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reaction` (`user_id`,`image_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `idx_messages_user_id` (`user_id`);
ALTER TABLE `messages` ADD FULLTEXT KEY `ft_thinking` (`thinking_text`);

--
-- Indexes for table `notific`
--
ALTER TABLE `notific`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`token`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `thinking_usage_logs`
--
ALTER TABLE `thinking_usage_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD UNIQUE KEY `invoice_id` (`invoice_id`) USING HASH;

--
-- Indexes for table `usage_logs`
--
ALTER TABLE `usage_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usage_user` (`user_id`),
  ADD KEY `idx_usage_model` (`model_used`),
  ADD KEY `idx_usage_date` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_verification_token` (`verification_token`),
  ADD KEY `idx_email` (`email`(255)),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indexes for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_published`
--
ALTER TABLE `conversation_published`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_share_links`
--
ALTER TABLE `conversation_share_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `free_user_limits`
--
ALTER TABLE `free_user_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `generated_images`
--
ALTER TABLE `generated_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `google_auth`
--
ALTER TABLE `google_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `image_reactions`
--
ALTER TABLE `image_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notific`
--
ALTER TABLE `notific`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `thinking_usage_logs`
--
ALTER TABLE `thinking_usage_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usage_logs`
--
ALTER TABLE `usage_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `fk_cp_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cp_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_published`
--
ALTER TABLE `conversation_published`
  ADD CONSTRAINT `fk_conv_pub_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conv_pub_user` FOREIGN KEY (`published_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_share_links`
--
ALTER TABLE `conversation_share_links`
  ADD CONSTRAINT `fk_csl_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_csl_shared_by` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `free_user_limits`
--
ALTER TABLE `free_user_limits`
  ADD CONSTRAINT `free_user_limits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gb_auth`
--
ALTER TABLE `gb_auth`
  ADD CONSTRAINT `gb_auth_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `generated_images`
--
ALTER TABLE `generated_images`
  ADD CONSTRAINT `generated_images_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `google_auth`
--
ALTER TABLE `google_auth`
  ADD CONSTRAINT `google_auth_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `notific`
--
ALTER TABLE `notific`
  ADD CONSTRAINT `fk_notific_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `thinking_usage_logs`
--
ALTER TABLE `thinking_usage_logs`
  ADD CONSTRAINT `thinking_usage_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usage_logs`
--
ALTER TABLE `usage_logs`
  ADD CONSTRAINT `usage_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  ADD CONSTRAINT `fk_user_onboarding_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
