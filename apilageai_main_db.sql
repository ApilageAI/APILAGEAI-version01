-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Feb 05, 2026 at 03:16 PM
-- Server version: 5.7.44
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apilageai_main_db`
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
  `selected_option` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT '0',
  `score_change` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bug_reports`
--

CREATE TABLE `bug_reports` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Anonymous',
  `problem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `screenshot_path` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bug_reports`
--

INSERT INTO `bug_reports` (`id`, `email`, `problem`, `screenshot_path`, `created_at`) VALUES
(1, 'dineth@apilageai.lk', 'The app isn\'t loading ??', 'upload/bugs/20260204_223037_bd24a24a.jpg', '2026-02-04 22:30:37');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `is_published` tinyint(1) DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `user_id`, `title`, `created_at`, `is_published`, `published_at`) VALUES
(31, 7, 'New Chat', '2026-02-05 12:04:29', 0, NULL),
(32, 2, 'Initial Contact', '2026-02-05 19:05:10', 0, NULL),
(33, 2, 'Hey', '2026-02-05 19:10:06', 0, NULL),
(34, 2, 'Linear Equation Graph', '2026-02-05 19:12:43', 0, NULL),
(35, 2, 'Graphing Linear Equation', '2026-02-05 19:18:25', 0, NULL),
(36, 2, 'Untitled Conversation', '2026-02-05 19:28:00', 0, NULL),
(37, 2, 'Hey', '2026-02-05 20:06:00', 0, NULL),
(38, 2, 'Untitled Conversation', '2026-02-05 20:10:34', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `conversation_canvas`
--

CREATE TABLE `conversation_canvas` (
  `conversation_id` bigint(20) NOT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int(11) NOT NULL DEFAULT '0',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversation_canvas`
--

INSERT INTO `conversation_canvas` (`conversation_id`, `data`, `version`, `updated_at`) VALUES
(36, '{\"strokes\":[],\"texts\":[],\"doc_html\":\"Hey teach me pure maths\"}', 11, '2026-02-05 19:29:24');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','collaborator') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collaborator',
  `added_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `permission_level` int(11) DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversation_participants`
--

INSERT INTO `conversation_participants` (`id`, `conversation_id`, `user_id`, `role`, `added_by`, `created_at`, `permission_level`) VALUES
(31, 31, 7, 'owner', 7, '2026-02-05 12:04:29', 2),
(32, 32, 2, 'owner', 2, '2026-02-05 19:05:10', 2),
(33, 33, 2, 'owner', 2, '2026-02-05 19:10:06', 2),
(34, 34, 2, 'owner', 2, '2026-02-05 19:12:43', 2),
(35, 35, 2, 'owner', 2, '2026-02-05 19:18:25', 2),
(36, 36, 2, 'owner', 2, '2026-02-05 19:28:00', 2),
(37, 37, 2, 'owner', 2, '2026-02-05 20:06:00', 2),
(38, 38, 2, 'owner', 2, '2026-02-05 20:10:34', 2);

-- --------------------------------------------------------

--
-- Table structure for table `conversation_published`
--

CREATE TABLE `conversation_published` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `publish_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_level` enum('read_only','read_write') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'read_only',
  `published_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_share_links`
--

CREATE TABLE `conversation_share_links` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shared_by` int(11) NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `free_user_daily_usage`
--

CREATE TABLE `free_user_daily_usage` (
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `window_id` tinyint(4) NOT NULL DEFAULT '0',
  `messages_used` int(11) DEFAULT '0',
  `image_uploads_used` int(11) DEFAULT '0',
  `file_uploads_used` int(11) DEFAULT '0',
  `image_generations_used` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `free_user_daily_usage`
--

INSERT INTO `free_user_daily_usage` (`user_id`, `date`, `messages_used`, `image_uploads_used`, `file_uploads_used`, `image_generations_used`) VALUES
(2, '2026-02-05', 0, 0, 0, 0),
(4, '2026-02-05', 0, 0, 0, 0),
(7, '2026-02-05', 2, 0, 0, 0),
(10, '2026-02-05', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `free_user_limits`
--

CREATE TABLE `free_user_limits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_uses` int(11) DEFAULT '0',
  `last_reset` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `mode` enum('single','multi') COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `term` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `focus` text COLLATE utf8mb4_unicode_ci,
  `language` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gb_auth`
--

CREATE TABLE `gb_auth` (
  `user_id` int(11) NOT NULL,
  `auth` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `generated_images`
--

CREATE TABLE `generated_images` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_url` varchar(512) NOT NULL,
  `prompt` text,
  `author_name` varchar(128) DEFAULT NULL,
  `public` tinyint(1) DEFAULT '0',
  `likes` int(11) DEFAULT '0',
  `unlikes` int(11) DEFAULT '0',
  `generated_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `generated_images`
--

INSERT INTO `generated_images` (`id`, `user_id`, `image_url`, `prompt`, `author_name`, `public`, `likes`, `unlikes`, `generated_at`) VALUES
(1, 2, 'http://localhost:8888/uploads/genimg/1770302452509-380724405.png', 'create image of red cat', 'Dineth Dilshan', 1, 0, 0, '2026-02-05 20:10:52');

-- --------------------------------------------------------

--
-- Table structure for table `google_auth`
--

CREATE TABLE `google_auth` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `google_auth`
--

INSERT INTO `google_auth` (`id`, `user_id`, `google_id`, `google_email`, `created_at`) VALUES
(2, 2, '104379396416505066737', 'infodinethdil@gmail.com', '2026-02-05 13:16:14'),
(4, 11, '110404356075202332270', 'dinethgunawardana2007@gmail.com', '2026-02-05 14:00:43');

-- --------------------------------------------------------

--
-- Table structure for table `image_reactions`
--

CREATE TABLE `image_reactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL,
  `reaction` enum('like','unlike') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `magic_login_tokens`
--

CREATE TABLE `magic_login_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'login',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `magic_login_tokens`
--

INSERT INTO `magic_login_tokens` (`id`, `user_id`, `token_hash`, `purpose`, `expires_at`, `used_at`, `ip`, `user_agent`, `created_at`) VALUES
(1, 7, '3cb57e7c80e23e845a242e23f9e12c14851de968261c99400e55d4c861e3b2f8', 'password_reset', '2026-02-05 12:24:56', '2026-02-05 12:20:32', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:19:56'),
(2, 7, '2b7949209cb13f97eb83dca8dde0af3c6741b85eae68c7f96417a3faf3ea6679', 'password_reset', '2026-02-05 17:26:25', NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 17:21:25');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `used_model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'APILAGEAI',
  `created_at` datetime NOT NULL,
  `text` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `thinking_text` longtext COLLATE utf8mb4_unicode_ci,
  `thinking_tokens` int(11) DEFAULT '0',
  `attach` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Attachment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `conversation_id`, `user_id`, `type`, `used_model`, `created_at`, `text`, `thinking_text`, `thinking_tokens`, `attach`) VALUES
(103, 31, 7, '1', 'APILAGEAI', '2026-02-05 12:04:29', 'hi', NULL, 0, ''),
(104, 31, 7, '2', 'super', '2026-02-05 12:04:29', 'Hi there! How can I help you today? 😊', NULL, 0, ''),
(105, 31, 7, '1', 'APILAGEAI', '2026-02-05 12:04:34', 'wassup', NULL, 0, ''),
(106, 31, 7, '2', 'super', '2026-02-05 12:04:34', 'Not much, just chilling and getting ready for any questions you might have! What\'s up with you? Anything interesting happening at Mahanama, or maybe a cool math problem you\'re stuck on? 😉', NULL, 0, ''),
(107, 32, 2, '1', 'APILAGEAI', '2026-02-05 19:05:10', 'hi', NULL, 0, ''),
(108, 32, 2, '2', 'free', '2026-02-05 19:05:10', 'සුභ දවසක්! මට ඔයාට මොනවා කරන්නද පුළුවන්? 😊\n', NULL, 0, ''),
(109, 33, 2, '1', 'APILAGEAI', '2026-02-05 19:10:06', 'hey', NULL, 0, ''),
(110, 33, 2, '2', 'free', '2026-02-05 19:10:06', 'Server is busy right now. Please try again shortly.', NULL, 0, ''),
(111, 33, 2, '1', 'APILAGEAI', '2026-02-05 19:10:13', 'hey', NULL, 0, ''),
(112, 33, 2, '2', 'pro', '2026-02-05 19:10:13', 'Hi there! How can I help you today?', NULL, 0, ''),
(113, 33, 2, '1', 'APILAGEAI', '2026-02-05 19:11:11', 'wassup', NULL, 0, ''),
(114, 33, 2, '2', 'pro', '2026-02-05 19:11:11', 'ApilageAI was founded by Dineth Gunawardana and Thisath Damiru in 2024', NULL, 0, ''),
(115, 34, 2, '1', 'APILAGEAI', '2026-02-05 19:12:43', 'graph y=5x+6', NULL, 0, ''),
(116, 34, 2, '2', 'pro', '2026-02-05 19:12:43', '%%y=5x+6%%\n%%m=5%%\n%%c=6%%\n\nThis is a linear equation in the form $y = mx + c$, where $m$ is the gradient (slope) and $c$ is the y-intercept. In this case, the gradient is 5 and the y-intercept is 6.', NULL, 0, ''),
(117, 35, 2, '1', 'APILAGEAI', '2026-02-05 19:18:25', 'graph y=5x+8', NULL, 0, ''),
(118, 35, 2, '2', 'pro', '2026-02-05 19:18:25', '%%y=5x+8%%\n%%x=-2%%\n%%x=2%%', NULL, 0, ''),
(119, 36, 2, '1', 'APILAGEAI', '2026-02-05 19:28:00', 'whats on my canves?', NULL, 0, ''),
(120, 36, 2, '2', 'pro', '2026-02-05 19:28:00', 'Sorry, I can\'t see what\'s on your canvas. I\'m a text-based AI and don\'t have access to external documents or your screen.\n\nIf you\'d like to tell me about your canvas document, I\'m happy to help with that!', NULL, 0, ''),
(121, 36, 2, '1', 'APILAGEAI', '2026-02-05 19:29:23', 'whats on my canves?', NULL, 0, ''),
(122, 36, 2, '2', 'pro', '2026-02-05 19:29:23', 'Sure thing! Let\'s dive into pure maths. What topic are you interested in learning about today? We could cover anything from algebra and calculus to geometry and trigonometry. Just let me know what you\'d like to focus on, or if you have a specific question in mind!', NULL, 0, ''),
(123, 37, 2, '1', 'APILAGEAI', '2026-02-05 20:06:00', 'hey', NULL, 0, ''),
(124, 37, 2, '2', 'pro', '2026-02-05 20:06:00', 'Hello! How can I help you today with your canvas document? Are you working on a math problem, a science concept, or something else? Let me know what you\'d like to do!', NULL, 0, ''),
(125, 37, 2, '1', 'APILAGEAI', '2026-02-05 20:06:04', 'wassup', NULL, 0, ''),
(126, 37, 2, '2', 'pro', '2026-02-05 20:06:04', 'Not much, just here to help you out with anything you need for Mahanama College Colombo. Whether it\'s maths, science, or ICT, or even just some study guidance, I\'m ready to assist. What\'s on your mind today?', NULL, 0, ''),
(127, 37, 2, '1', 'APILAGEAI', '2026-02-05 20:06:13', 'how is your life?', NULL, 0, ''),
(128, 37, 2, '2', 'pro', '2026-02-05 20:06:13', '> ApilageAI was founded by Dineth Gunawardana and Thisath Damiru in 2024', NULL, 0, ''),
(129, 38, 2, '1', 'APILAGEAI', '2026-02-05 20:10:34', 'create image of red cat', NULL, 0, ''),
(130, 38, 2, '2', 'pro', '2026-02-05 20:10:34', 'Yes I can generate images based on text prompts Just ask me to create one or upload an image and tell me what you want\n\n<img src=\"http://localhost:8888/uploads/genimg/1770302452509-380724405.png\" alt=\"Generated image\" style=\"max-width:100%;height:auto;\"/>', NULL, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `notific`
--

CREATE TABLE `notific` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `mark` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `user_id` int(11) NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `client` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_seen` datetime NOT NULL,
  `start` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`user_id`, `token`, `ip`, `client`, `last_seen`, `start`, `active`) VALUES
(2, '08f57e8a70d23571abb4a316e91bdaf2c0fa02a4dc962f507c7a0686f8b7004f', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:45:12', '2026-02-05 18:45:12', 1),
(2, '2ff8b4f376867b5ae56edcbee6ade55ccaa4723419bb65f1c97d005937f8883d', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:40:35', '2026-02-05 18:40:35', 0),
(7, '324c3b3adacf0ba0c8592869d0df039ca40fb87f9de18e74e89c64c26307bf1a', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 17:55:36', '2026-02-05 12:21:10', 0),
(2, '38440f317864a063bcf80a27ded67fcb2ae8ab62d72fa333abeed28dc357f8dc', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 20:14:38', '2026-02-05 18:59:41', 0),
(2, '3ab2b890759265317b7d576df1a42c9b4661e3e41287beffa99fd82a6da02297', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:38:20', '2026-02-05 18:38:20', 0),
(2, '3d0b8598391e4378e61edd0a6f11ac05198b168711f7c67e25ae4c743f7f3560', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Safari/605.1.15', '2026-02-05 18:39:04', '2026-02-05 18:39:04', 0),
(7, '3d3ff6bc4985e8c6fb731b744f70072b4eabf5ec71d4ca5811471f4919431d72', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:45:03', '2026-02-05 18:42:16', 0),
(10, '422caaf7646d5ecd8ef064aba47fd847a0a3743de59ca1a1d43422fe40aee1b5', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:58:58', '2026-02-05 18:58:58', 1),
(4, '43c38321256e28533fd3059eabb2c644010db26da90281ae648ed26abe6a3d73', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:50:20', '2026-02-05 11:49:20', 0),
(2, '446435ac560878e50b98ed4a3f80359c6f9abccdd6e7e8603974b990ad20c70c', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:38:29', '2026-02-05 18:38:29', 0),
(2, '58edb69006b6ecac527388728c2bc5288eb17ef697e436d9ed8465e0900cbde1', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 20:19:08', '2026-02-05 20:19:08', 1),
(2, '5a50add34425a2d830a814e02d16eacd2c04953d0cf4cd86cba135926f975237', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 20:45:54', '2026-02-05 20:20:45', 1),
(2, '63ef0114f150a6c3845049f71eeccdc5db564a656e376482c6cf565da47f17be', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:34:58', '2026-02-05 18:34:58', 0),
(2, '65eaa408e39dd104a3197730275ef9dd62caa3f4209b4d22e75dfb5584768fac', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:05:33', '2026-02-05 11:05:16', 0),
(7, '67fc6492ebee9ded469ac73b68679a5ea67a2d62843a79b8b8917a134bb16fb5', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:59:16', '2026-02-05 11:58:38', 0),
(7, '6d8ba4cd08b729aee917db9bc5e224b7fd678e202289a23288a11481c2d0d718', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:01:23', '2026-02-05 11:59:51', 0),
(2, '7061e9407394592c007f0ee18e35db9270fd0bd46884e88ba4369592e0042ecf', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 10:01:31', '2026-02-05 09:52:45', 0),
(10, '8084f7852986e10eb5dbeec528e8f8338887ed1c1b1e3eeda4727077e4521058', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:58:10', '2026-02-05 18:55:51', 1),
(2, '85d62371c347ae9394b32714a693ea0ca69f43ba00002c6fdf4511cf57293fef', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:34:32', '2026-02-05 18:34:32', 0),
(11, '90582813f5c8a91f56bd4894a6537426bcf7e4dfe4f5d9e566630a5af301b14c', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Safari/605.1.15', '2026-02-05 19:31:03', '2026-02-05 19:30:38', 1),
(2, 'afb98b1b9fab3c5e5ce0b4df3edf1d21ccff668c34044b8af9619b07bcabce6e', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 10:32:53', '2026-02-05 10:32:20', 0),
(2, 'afbede3e4cce20a3f968ae1a114a24edc7bb35a3b651a0503169e9b9f3af1a0f', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 20:17:23', '2026-02-05 20:17:23', 1),
(2, 'b203af12a9d40b50a041317ef04ea7e035d319754fbd4b531680e586b7bc1b99', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:34:14', '2026-02-05 18:34:14', 0),
(2, 'b39acacba4dd02cc5dc6a9acc08faa08ad2cccea13976105c6094901a8e32fcb', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:34:06', '2026-02-05 18:34:06', 0),
(2, 'd053f59ff1f7530e892ff90d2455f43ddab87ffa2fdd5d9eb50d1d11fe6e952a', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 20:14:48', '2026-02-05 20:14:48', 1),
(2, 'd21de619a25ed1c6b0f9a98946605d38ecbd455438f9cdfac4e1317352727b98', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:41:58', '2026-02-05 18:41:58', 0),
(10, 'd8808bc174c96dbbbb496fcb181baa411265f5c2eb1b4902dd4493646bf1e82b', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:55:45', '2026-02-05 18:46:10', 0),
(7, 'ddf9e217796067bcf0a1c20de155484d33a953785cc5c2215e0380ac8575b64d', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:06:00', '2026-02-05 12:03:57', 0),
(2, 'e267d7fa8e68a01167f6af629dcb7e22cf4388f134027b638a3034e13cf71988', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 18:35:12', '2026-02-05 18:35:12', 0),
(7, 'faf926d397d2b4fed41d9d956031dfeb0c72b9f205d51411d614e18a7a81c1de', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:03:24', '2026-02-05 12:01:56', 0);

-- --------------------------------------------------------

--
-- Table structure for table `thinking_usage_logs`
--

CREATE TABLE `thinking_usage_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `thinking_tokens` int(11) DEFAULT '0',
  `thinking_budget` int(11) DEFAULT '0',
  `thinking_cost_lkr` decimal(10,4) DEFAULT '0.0000',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `invoice_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(12) NOT NULL,
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `payable_uid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_indicator` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trial_abuse_tracking`
--

CREATE TABLE `trial_abuse_tracking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_fingerprint` varchar(255) DEFAULT NULL,
  `trial_start_date` date DEFAULT NULL,
  `trial_end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `usage_logs`
--

INSERT INTO `usage_logs` (`id`, `user_id`, `model_used`, `input_tokens`, `output_tokens`, `input_cost_lkr`, `output_cost_lkr`, `total_cost_lkr`, `profit_added_lkr`, `total_final_cost_lkr`, `balance_before`, `balance_after`, `created_at`) VALUES
(1, 2, 'gemini-2.0-flash', 2, 11, 0.0001, 0.0013, 0.0014, 0.0007, 0.0021, 10000.00, 10000.00, '2026-02-05 13:35:13'),
(2, 2, 'gemini-2.5-flash-lite', 2, 11, 0.0001, 0.0013, 0.0014, 0.0007, 0.0021, 10000.00, 10000.00, '2026-02-05 13:40:17'),
(3, 2, 'gemini-2.5-flash-lite', 2, 15, 0.0001, 0.0018, 0.0019, 0.0009, 0.0028, 10000.00, 10000.00, '2026-02-05 13:41:16'),
(4, 2, 'gemini-2.5-flash-lite', 3, 52, 0.0001, 0.0063, 0.0064, 0.0032, 0.0096, 10000.00, 9999.99, '2026-02-05 13:42:46'),
(5, 2, 'gemini-2.5-flash-lite', 3, 4, 0.0001, 0.0005, 0.0006, 0.0003, 0.0009, 10000.00, 10000.00, '2026-02-05 13:48:30'),
(6, 2, 'gemini-2.5-flash-lite', 6, 51, 0.0002, 0.0062, 0.0064, 0.0032, 0.0096, 10000.00, 9999.99, '2026-02-05 13:58:05'),
(7, 2, 'gemini-2.5-flash-lite', 6, 63, 0.0002, 0.0077, 0.0078, 0.0039, 0.0118, 10000.00, 9999.99, '2026-02-05 13:59:27'),
(8, 2, 'gemini-2.5-flash-lite', 2, 43, 0.0001, 0.0052, 0.0053, 0.0026, 0.0079, 10000.00, 9999.99, '2026-02-05 14:36:03'),
(9, 2, 'gemini-2.5-flash-lite', 2, 50, 0.0001, 0.0061, 0.0061, 0.0031, 0.0092, 10000.00, 9999.99, '2026-02-05 14:36:08'),
(10, 2, 'gemini-2.5-flash-lite', 6, 16, 0.0002, 0.0019, 0.0021, 0.0011, 0.0032, 10000.00, 10000.00, '2026-02-05 14:36:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Email verification status',
  `verification_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email verification token',
  `verification_token_expires` datetime DEFAULT NULL COMMENT 'Token expiration time',
  `reset_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image` mediumtext COLLATE utf8mb4_unicode_ci,
  `balance` int(11) NOT NULL DEFAULT '0',
  `password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `reg_date` datetime NOT NULL COMMENT 'Registered Date and Time',
  `memory` text CHARACTER SET utf8mb4 COMMENT 'Stores up to 10 memory points for the user\r\n',
  `subscription_status` tinyint(1) NOT NULL DEFAULT '0',
  `onboard_complete` tinyint(1) NOT NULL DEFAULT '0',
  `failed_login_attempts` int(11) NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `email_verified`, `verification_token`, `verification_token_expires`, `reset_token`, `reset_token_expires`, `phone`, `image`, `balance`, `password`, `type`, `reg_date`, `memory`, `subscription_status`, `onboard_complete`, `failed_login_attempts`, `locked_until`) VALUES
(2, 'Dineth', 'Dilshan', 'infodinethdil@gmail.com', 1, NULL, NULL, NULL, NULL, '0701840527', '/uploads/profile/user_10_1770298068.jpeg', 9995, '$2y$10$B4G67djClPw2vl0MUSbNmO8obQNZn2vdHf9Cpv3L2WBTPlXh1fXD.', '1', '2026-02-05 18:46:10', '*   User asks questions related to graphing equations.\n*   User interacts with visual \'canvas\' interfaces.\n*   User requests image generation.', 0, 1, 0, NULL),
(11, 'Dineth', 'Gunawardana', 'dinethgunawardana2007@gmail.com', 1, NULL, NULL, NULL, NULL, '', 'ApilageAI_t_1770300041_r_1d7f7fb1f13e91f3_tk_c255e5406991bff1dfccec89c2fb1c38.jpg', 0, '$2y$10$dvrl0BRLnWWD4GbvirPaQ.5ft3/Osz5oFhwOnRJLuVE6rZx7BHcW2', '1', '2026-02-05 19:30:38', NULL, 0, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_onboarding`
--

CREATE TABLE `user_onboarding` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `school` varchar(256) DEFAULT NULL,
  `not_student` tinyint(1) DEFAULT '0',
  `interests` text,
  `preference` enum('friendly','educational','explanatory','concise') DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_onboarding`
--

INSERT INTO `user_onboarding` (`id`, `user_id`, `school`, `not_student`, `interests`, `preference`, `created_at`, `updated_at`) VALUES
(5, 7, 'Mahanama college ', 0, 'maths,life,science', 'friendly', '2026-02-05 11:59:00', '2026-02-05 11:59:00'),
(6, 10, 'Mahanama college ', 0, 'life,maths,art', 'friendly', '2026-02-05 18:46:33', '2026-02-05 18:46:33'),
(7, 2, 'Mahanama College Colombo', 0, '[\"science\",\"life\",\"maths\"]', 'educational', '2026-02-05 19:11:30', '2026-02-05 19:11:30'),
(8, 11, 'Kalutara Vidyalaya', 0, 'science,life,maths', 'friendly', '2026-02-05 19:31:03', '2026-02-05 19:31:03');

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
-- Indexes for table `bug_reports`
--
ALTER TABLE `bug_reports`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `idx_published_by` (`published_by`),
  ADD KEY `idx_conversation_id` (`conversation_id`);

--
-- Indexes for table `conversation_share_links`
--
ALTER TABLE `conversation_share_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_shared_by` (`shared_by`),
  ADD KEY `idx_conversation_target` (`conversation_id`,`target_user_id`);

--
-- Indexes for table `free_user_daily_usage`
--
ALTER TABLE `free_user_daily_usage`
  ADD PRIMARY KEY (`user_id`,`date`,`window_id`);

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
-- Indexes for table `magic_login_tokens`
--
ALTER TABLE `magic_login_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_token_hash` (`token_hash`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_purpose` (`purpose`),
  ADD KEY `idx_expires` (`expires_at`);

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
  ADD UNIQUE KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `trial_abuse_tracking`
--
ALTER TABLE `trial_abuse_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_date` (`ip_address`,`trial_end_date`),
  ADD KEY `idx_device_date` (`device_fingerprint`,`trial_end_date`),
  ADD KEY `idx_user_date` (`user_id`,`trial_end_date`);

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
  ADD KEY `idx_reset_token` (`reset_token`),
  ADD KEY `idx_locked_until` (`locked_until`);

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
-- AUTO_INCREMENT for table `bug_reports`
--
ALTER TABLE `bug_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `google_auth`
--
ALTER TABLE `google_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `image_reactions`
--
ALTER TABLE `image_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `magic_login_tokens`
--
ALTER TABLE `magic_login_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

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
-- AUTO_INCREMENT for table `trial_abuse_tracking`
--
ALTER TABLE `trial_abuse_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usage_logs`
--
ALTER TABLE `usage_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
