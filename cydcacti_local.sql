-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 22, 2026 at 11:28 PM
-- Server version: 10.11.16-MariaDB-cll-lve
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cydcacti_local`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_cluster_assignments`
--

CREATE TABLE `admin_cluster_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_user_id` bigint(20) UNSIGNED NOT NULL,
  `cluster_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_user_supervisions`
--

CREATE TABLE `admin_user_supervisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_user_id` bigint(20) UNSIGNED NOT NULL,
  `supervised_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `centers`
--

CREATE TABLE `centers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `center_id` varchar(255) NOT NULL,
  `center_name` varchar(255) NOT NULL,
  `cluster_name` varchar(255) DEFAULT NULL,
  `facilitator_name` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `center_notifications`
--

CREATE TABLE `center_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `center_id` varchar(255) NOT NULL,
  `participant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sent_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `event_key` varchar(255) NOT NULL,
  `due_date` date DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `is_manual` tinyint(1) NOT NULL DEFAULT 1,
  `sent_to_all_users` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `center_notification_reads`
--

CREATE TABLE `center_notification_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `center_notification_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `center_user_assignments`
--

CREATE TABLE `center_user_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `center_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `church_profiles`
--

CREATE TABLE `church_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `center_id` varchar(255) DEFAULT NULL,
  `created_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `church_name` varchar(255) DEFAULT NULL,
  `historical_background` longtext DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `photo_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photo_paths`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_09_125712_add_role_and_center_to_users_table', 1),
(5, '2026_04_09_130855_create_otp_codes_table', 1),
(6, '2026_04_09_143925_create_centers_table', 1),
(7, '2026_04_09_173601_add_center_id_to_users_table', 1),
(8, '2026_04_09_180223_add_role_to_users_table', 1),
(9, '2026_04_10_062949_create_participants_table', 1),
(10, '2026_04_11_093230_add_photo_to_participants_table', 1),
(11, '2026_04_11_120315_update_participants_add_new_profile_fields', 1),
(12, '2026_04_11_122204_create_participant_sponsorships_table', 1),
(13, '2026_04_12_100000_create_center_notifications_table', 1),
(14, '2026_04_12_100100_create_center_notification_reads_table', 1),
(15, '2026_04_12_101500_add_profile_photo_to_users_table', 1),
(16, '2026_04_12_110000_add_job_title_to_users_table', 1),
(17, '2026_04_12_120000_add_sponsorship_summary_columns_to_participants_table', 1),
(18, '2026_04_12_130000_create_center_user_assignments_table', 1),
(19, '2026_04_14_120000_add_profile_enhancements_to_participants_and_sponsorships', 1),
(20, '2026_04_15_090000_add_primary_subject_scores_to_participants_table', 1),
(21, '2026_04_15_140000_add_secondary_and_university_results_to_participants_table', 1),
(22, '2026_04_15_170000_add_manual_sender_fields_to_center_notifications_table', 1),
(23, '2026_04_15_220000_add_project_name_to_users_table', 1),
(24, '2026_04_16_090000_add_created_by_user_id_to_participants_and_sponsorships', 1),
(25, '2026_04_19_090000_create_church_profiles_table', 1),
(26, '2026_04_21_090000_change_weight_and_height_to_string_on_participants_table', 2),
(27, '2026_04_22_090000_add_mission_and_vision_to_church_profiles_table', 3),
(28, '2026_04_22_091000_create_program_attendance_sessions_table', 3),
(29, '2026_04_22_091100_create_program_attendance_entries_table', 3),
(30, '2026_04_24_120000_create_participant_treatments_table', 4),
(31, '2026_04_24_170000_add_attendance_type_to_program_attendance_sessions_table', 5),
(32, '2026_04_24_180000_add_activity_fields_to_program_attendance_sessions_table', 6),
(33, '2026_04_24_190000_add_household_contact_fields_to_participants_table', 7),
(34, '2026_04_24_191000_add_contact_status_fields_to_participants_table', 8),
(35, '2026_04_24_192000_add_participant_needs_to_participants_table', 9),
(36, '2026_04_24_193000_add_activity_photo_gallery_fields_to_program_attendance_sessions_table', 10),
(37, '2026_04_25_090000_add_recipient_fields_to_center_notifications_table', 11),
(38, '2026_05_09_090000_add_account_approval_fields_to_users_table', 12),
(39, '2026_05_15_090000_add_admin_supervision_fields_and_table', 13),
(40, '2026_05_15_100000_add_cluster_name_to_users_and_create_admin_cluster_assignments_table', 14);

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `user_id`, `code`, `expires_at`, `is_used`, `created_at`, `updated_at`) VALUES
(32, 11, '349842', '2026-05-15 09:50:40', 1, '2026-05-15 06:50:22', '2026-05-15 06:50:40'),
(34, 11, '620648', '2026-05-19 15:13:45', 1, '2026-05-19 12:13:32', '2026-05-19 12:13:45'),
(36, 12, '217062', '2026-05-21 08:19:47', 1, '2026-05-21 07:19:04', '2026-05-21 07:19:47'),
(37, 12, '172002', '2026-05-21 08:21:23', 1, '2026-05-21 07:19:47', '2026-05-21 07:21:23'),
(38, 12, '806609', '2026-05-21 08:21:36', 1, '2026-05-21 07:21:23', '2026-05-21 07:21:36'),
(39, 12, '838276', '2026-05-21 08:23:11', 1, '2026-05-21 07:21:36', '2026-05-21 07:23:11'),
(40, 12, '659850', '2026-05-21 08:23:36', 1, '2026-05-21 07:23:11', '2026-05-21 07:23:36'),
(41, 12, '778977', '2026-05-21 08:39:40', 1, '2026-05-21 07:38:21', '2026-05-21 07:39:40'),
(42, 12, '475069', '2026-05-21 08:42:18', 1, '2026-05-21 07:41:13', '2026-05-21 07:42:18'),
(43, 12, '346850', '2026-05-21 09:21:53', 1, '2026-05-21 08:13:31', '2026-05-21 08:21:53'),
(44, 12, '178197', '2026-05-21 09:25:19', 1, '2026-05-21 08:21:53', '2026-05-21 08:25:19'),
(45, 12, '639424', '2026-05-21 09:27:49', 1, '2026-05-21 08:25:19', '2026-05-21 08:27:49'),
(46, 12, '831353', '2026-05-21 09:28:10', 1, '2026-05-21 08:27:49', '2026-05-21 08:28:10'),
(47, 14, '310585', '2026-05-21 10:47:16', 0, '2026-05-21 10:42:16', '2026-05-21 10:42:16'),
(48, 13, '671080', '2026-05-21 11:53:25', 1, '2026-05-21 10:52:50', '2026-05-21 10:53:25'),
(49, 12, '343693', '2026-05-21 12:08:12', 1, '2026-05-21 11:06:51', '2026-05-21 11:08:12'),
(50, 12, '990810', '2026-05-21 12:08:34', 1, '2026-05-21 11:08:12', '2026-05-21 11:08:34'),
(51, 12, '211201', '2026-05-21 17:50:54', 1, '2026-05-21 16:50:12', '2026-05-21 16:50:54'),
(52, 15, '428642', '2026-05-21 21:44:19', 1, '2026-05-21 20:43:37', '2026-05-21 20:44:19'),
(53, 12, '632709', '2026-05-22 05:14:09', 1, '2026-05-22 04:13:14', '2026-05-22 04:14:09'),
(54, 16, '467029', '2026-05-22 05:47:57', 1, '2026-05-22 04:46:44', '2026-05-22 04:47:57'),
(55, 17, '697903', '2026-05-22 06:39:05', 1, '2026-05-22 05:36:33', '2026-05-22 05:39:05'),
(56, 12, '902577', '2026-05-22 08:17:35', 1, '2026-05-22 07:16:52', '2026-05-22 07:17:35'),
(57, 18, '686067', '2026-05-22 09:00:52', 1, '2026-05-22 08:00:18', '2026-05-22 08:00:52'),
(58, 12, '404918', '2026-05-22 14:48:59', 1, '2026-05-22 13:46:38', '2026-05-22 13:48:59'),
(59, 12, '861293', '2026-05-22 22:00:42', 1, '2026-05-22 20:59:57', '2026-05-22 21:00:42'),
(60, 18, '599141', '2026-05-22 22:03:19', 1, '2026-05-22 21:02:42', '2026-05-22 21:03:19');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `center_id` varchar(255) NOT NULL,
  `created_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `local_participant_number` varchar(255) DEFAULT NULL,
  `local_participant_id` varchar(255) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `preferred_name` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `participant_status` varchar(255) NOT NULL DEFAULT 'Active',
  `sponsorship_status` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `photo_updated_at` timestamp NULL DEFAULT NULL,
  `next_photo_update_due_at` date DEFAULT NULL,
  `age_group` varchar(255) DEFAULT NULL,
  `household` varchar(255) DEFAULT NULL,
  `correspondence_language` varchar(255) DEFAULT NULL,
  `citizenship` varchar(255) DEFAULT NULL,
  `fcp_id` varchar(255) DEFAULT NULL,
  `cluster` varchar(255) DEFAULT NULL,
  `fcp_name` varchar(255) DEFAULT NULL,
  `partnership_facilitator` varchar(255) DEFAULT NULL,
  `community_name` varchar(255) DEFAULT NULL,
  `attending_location` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `school_level` varchar(255) DEFAULT NULL,
  `school_performance` varchar(255) DEFAULT NULL,
  `religious_affiliation` varchar(255) DEFAULT NULL,
  `weight` varchar(255) DEFAULT NULL,
  `height` varchar(255) DEFAULT NULL,
  `health_comments` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `national_office_community_name` varchar(255) DEFAULT NULL,
  `planned_completion_date` date DEFAULT NULL,
  `transition_date` date DEFAULT NULL,
  `gps_location` varchar(255) DEFAULT NULL,
  `things_i_like` text DEFAULT NULL,
  `favorite_activities` text DEFAULT NULL,
  `household_duties` text DEFAULT NULL,
  `favorite_subjects` text DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `grade_level` varchar(255) DEFAULT NULL,
  `course_of_study` varchar(255) DEFAULT NULL,
  `vocational_training` varchar(255) DEFAULT NULL,
  `bible_distributed_date` date DEFAULT NULL,
  `faith_confession_date` date DEFAULT NULL,
  `christian_activities` text DEFAULT NULL,
  `disabilities` text DEFAULT NULL,
  `chronic_illnesses` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `sponsored_by` varchar(255) DEFAULT NULL,
  `sponsorship_start_date` date DEFAULT NULL,
  `sponsorship_category` varchar(255) DEFAULT NULL,
  `physical_address` text DEFAULT NULL,
  `house_number` varchar(255) DEFAULT NULL,
  `region_city_street` varchar(255) DEFAULT NULL,
  `parent_guardian_name` varchar(255) DEFAULT NULL,
  `parent_guardian_occupation` varchar(255) DEFAULT NULL,
  `parent_guardian_phone` varchar(255) DEFAULT NULL,
  `caregiver_name` varchar(255) DEFAULT NULL,
  `father_status` varchar(255) DEFAULT NULL,
  `mother_status` varchar(255) DEFAULT NULL,
  `household_name` varchar(255) DEFAULT NULL,
  `household_phone` varchar(255) DEFAULT NULL,
  `household_relationship` varchar(255) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `current_class` varchar(255) DEFAULT NULL,
  `education_stage` varchar(255) DEFAULT NULL,
  `education_grade` varchar(255) DEFAULT NULL,
  `primary_score` decimal(5,2) DEFAULT NULL,
  `o_level_score` decimal(5,2) DEFAULT NULL,
  `a_level_score` decimal(5,2) DEFAULT NULL,
  `college_score` decimal(5,2) DEFAULT NULL,
  `university_gpa` decimal(4,2) DEFAULT NULL,
  `is_in_school` tinyint(1) NOT NULL DEFAULT 1,
  `not_in_school_reason` text DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `participant_needs` text DEFAULT NULL,
  `vision_for_tomorrow` text DEFAULT NULL,
  `planned_exit_type` varchar(255) DEFAULT NULL,
  `planned_exit_reason` text DEFAULT NULL,
  `unplanned_exit_lessons` text DEFAULT NULL,
  `treatment_date` date DEFAULT NULL,
  `tested_diseases` text DEFAULT NULL,
  `illness_type` varchar(255) DEFAULT NULL,
  `treatment_location` varchar(255) DEFAULT NULL,
  `treatment_cost` decimal(10,2) DEFAULT NULL,
  `general_assessment_social` text DEFAULT NULL,
  `general_assessment_physical` text DEFAULT NULL,
  `general_assessment_emotional` text DEFAULT NULL,
  `general_assessment_spiritual` text DEFAULT NULL,
  `baptism_status` varchar(255) DEFAULT NULL,
  `sponsor_type` varchar(255) DEFAULT NULL,
  `sponsorship_type` varchar(255) DEFAULT NULL,
  `sponsor_physical_address` text DEFAULT NULL,
  `sponsor_contact` varchar(255) DEFAULT NULL,
  `primary_kiswahili_score` decimal(5,2) DEFAULT NULL,
  `primary_english_score` decimal(5,2) DEFAULT NULL,
  `primary_mathematics_score` decimal(5,2) DEFAULT NULL,
  `primary_science_score` decimal(5,2) DEFAULT NULL,
  `primary_social_studies_score` decimal(5,2) DEFAULT NULL,
  `secondary_english_score` decimal(5,2) DEFAULT NULL,
  `secondary_mathematics_score` decimal(5,2) DEFAULT NULL,
  `secondary_biology_score` decimal(5,2) DEFAULT NULL,
  `secondary_chemistry_score` decimal(5,2) DEFAULT NULL,
  `secondary_physics_score` decimal(5,2) DEFAULT NULL,
  `secondary_average_score` decimal(5,2) DEFAULT NULL,
  `university_semester_one_gpa` decimal(4,2) DEFAULT NULL,
  `university_semester_two_gpa` decimal(4,2) DEFAULT NULL,
  `university_semester_three_gpa` decimal(4,2) DEFAULT NULL,
  `university_semester_four_gpa` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `center_id`, `created_by_user_id`, `local_participant_number`, `local_participant_id`, `account_name`, `preferred_name`, `gender`, `birthdate`, `participant_status`, `sponsorship_status`, `photo`, `photo_updated_at`, `next_photo_update_due_at`, `age_group`, `household`, `correspondence_language`, `citizenship`, `fcp_id`, `cluster`, `fcp_name`, `partnership_facilitator`, `community_name`, `attending_location`, `phone`, `email`, `address`, `school_level`, `school_performance`, `religious_affiliation`, `weight`, `height`, `health_comments`, `photo_path`, `created_at`, `updated_at`, `national_office_community_name`, `planned_completion_date`, `transition_date`, `gps_location`, `things_i_like`, `favorite_activities`, `household_duties`, `favorite_subjects`, `country`, `grade_level`, `course_of_study`, `vocational_training`, `bible_distributed_date`, `faith_confession_date`, `christian_activities`, `disabilities`, `chronic_illnesses`, `treatment`, `sponsored_by`, `sponsorship_start_date`, `sponsorship_category`, `physical_address`, `house_number`, `region_city_street`, `parent_guardian_name`, `parent_guardian_occupation`, `parent_guardian_phone`, `caregiver_name`, `father_status`, `mother_status`, `household_name`, `household_phone`, `household_relationship`, `school_name`, `current_class`, `education_stage`, `education_grade`, `primary_score`, `o_level_score`, `a_level_score`, `college_score`, `university_gpa`, `is_in_school`, `not_in_school_reason`, `hobbies`, `participant_needs`, `vision_for_tomorrow`, `planned_exit_type`, `planned_exit_reason`, `unplanned_exit_lessons`, `treatment_date`, `tested_diseases`, `illness_type`, `treatment_location`, `treatment_cost`, `general_assessment_social`, `general_assessment_physical`, `general_assessment_emotional`, `general_assessment_spiritual`, `baptism_status`, `sponsor_type`, `sponsorship_type`, `sponsor_physical_address`, `sponsor_contact`, `primary_kiswahili_score`, `primary_english_score`, `primary_mathematics_score`, `primary_science_score`, `primary_social_studies_score`, `secondary_english_score`, `secondary_mathematics_score`, `secondary_biology_score`, `secondary_chemistry_score`, `secondary_physics_score`, `secondary_average_score`, `university_semester_one_gpa`, `university_semester_two_gpa`, `university_semester_three_gpa`, `university_semester_four_gpa`) VALUES
(10, 'TZ0342', 18, 'TZ03425000', 'TZ0342003', 'KKKT KASULU', 'idriss mwala', 'Male', '2020-06-06', 'Active', 'Active', 'participants/miZYbBKdu8v8kwl0j0j2PWKawStTC2avD3tXELYH.jpg', '2026-05-22 08:54:33', '2027-11-22', NULL, NULL, NULL, NULL, NULL, 'KIGOMA KASULU', 'KKKT', 'EMMANUEL RUSSOTA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CHRISTIAN', '60', '6F', NULL, NULL, '2026-05-22 08:54:33', '2026-05-22 08:54:33', 'CIT', '2026-04-14', '2026-04-15', '-5.0242850, 32.8087260', 'UI', 'IU', 'IO', 'IO', 'TANZANIA', NULL, NULL, NULL, '2026-03-10', '2026-05-05', 'JK', 'NO', 'Cancer', NULL, 'KKKT', '2026-04-07', 'SCHOOL', 'kanyenye, tabora municipal', '26', NULL, '7655', 'ENGINEER', '0673746031', 'FATHER', 'Alive', 'Alive', '1', '0673746031', 'FATHER', 'UNYANYEMBE SECONDARY SCHOOL', 'FORM 2', 'Secondary', 'A', NULL, NULL, NULL, NULL, NULL, 1, NULL, 'UO', 'KJ', 'JK', 'By Age', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'JK', 'JK', 'JK', 'JK', 'Baptized', NULL, 'TYH', 'KASULU', '0673746031', NULL, NULL, NULL, NULL, NULL, 87.00, 98.00, 78.00, 77.98, 89.00, 86.00, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `participant_sponsorships`
--

CREATE TABLE `participant_sponsorships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `created_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `funding_type` varchar(255) DEFAULT NULL,
  `sponsorship_status` varchar(255) DEFAULT NULL,
  `sponsored_by` varchar(255) DEFAULT NULL,
  `sponsorship_start_date` date DEFAULT NULL,
  `sponsorship_category` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sponsor_name` varchar(255) DEFAULT NULL,
  `sponsor_type` varchar(255) DEFAULT NULL,
  `sponsorship_type` varchar(255) DEFAULT NULL,
  `sponsor_physical_address` text DEFAULT NULL,
  `sponsor_contact` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `participant_sponsorships`
--

INSERT INTO `participant_sponsorships` (`id`, `participant_id`, `created_by_user_id`, `funding_type`, `sponsorship_status`, `sponsored_by`, `sponsorship_start_date`, `sponsorship_category`, `created_at`, `updated_at`, `sponsor_name`, `sponsor_type`, `sponsorship_type`, `sponsor_physical_address`, `sponsor_contact`) VALUES
(10, 10, 18, NULL, 'Active', 'YUHJ', '2026-04-07', 'SCHOOL', '2026-05-22 08:54:33', '2026-05-22 08:54:33', 'KKKT', NULL, 'TYH', 'KASULU', '0673746031');

-- --------------------------------------------------------

--
-- Table structure for table `participant_treatments`
--

CREATE TABLE `participant_treatments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `center_id` varchar(255) NOT NULL,
  `treatment` text DEFAULT NULL,
  `treatment_date` date DEFAULT NULL,
  `tested_diseases` text DEFAULT NULL,
  `illness_type` varchar(255) DEFAULT NULL,
  `treatment_location` varchar(255) DEFAULT NULL,
  `treatment_cost` decimal(12,2) DEFAULT NULL,
  `health_comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('idrissmwala11@gmail.com', '$2y$12$uOgY.YHL1JLGkENHQ5Rp/Ogx4oiTiAg2wu2.fMTqg9QUEMdlrOHT.', '2026-05-21 08:28:56');

-- --------------------------------------------------------

--
-- Table structure for table `program_attendance_entries`
--

CREATE TABLE `program_attendance_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `program_attendance_session_id` bigint(20) UNSIGNED NOT NULL,
  `participant_id` bigint(20) UNSIGNED NOT NULL,
  `is_present` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_attendance_sessions`
--

CREATE TABLE `program_attendance_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `center_id` varchar(255) DEFAULT NULL,
  `created_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `attendance_type` varchar(255) NOT NULL DEFAULT 'program',
  `attendance_date` date NOT NULL,
  `activity_name` varchar(255) DEFAULT NULL,
  `activity_photo_path` varchar(255) DEFAULT NULL,
  `activity_photo_caption` varchar(255) DEFAULT NULL,
  `activity_photo_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`activity_photo_paths`)),
  `activity_photo_captions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`activity_photo_captions`)),
  `instructor_name` varchar(255) DEFAULT NULL,
  `topic` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `present_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `absent_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program_attendance_sessions`
--

INSERT INTO `program_attendance_sessions` (`id`, `center_id`, `created_by_user_id`, `attendance_type`, `attendance_date`, `activity_name`, `activity_photo_path`, `activity_photo_caption`, `activity_photo_paths`, `activity_photo_captions`, `instructor_name`, `topic`, `comment`, `present_count`, `absent_count`, `created_at`, `updated_at`) VALUES
(1, 'Tz0342', NULL, 'program', '2026-05-15', NULL, NULL, NULL, NULL, NULL, 'IDRISS', 'computer hardware', 'WAMEELEWA SANA', 1, 0, '2026-05-15 02:09:11', '2026-05-15 02:09:11'),
(2, 'Tz0342', NULL, 'activity', '2026-05-15', 'FOOTBALL', 'activity-attendance/QuCb3yYaFVlzZDQDenthWI70LKn4LgXs1imfsgDz.png', 'RUNNING', '[\"activity-attendance\\/QuCb3yYaFVlzZDQDenthWI70LKn4LgXs1imfsgDz.png\"]', '[\"RUNNING\"]', 'IDRISS', 'computer hardware', 'WAMEELEWA SANA', 0, 1, '2026-05-15 02:10:38', '2026-05-15 02:10:38');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('lNQrWdfIA2etZmVemZxSPTKORmHMF9FTwyCjnJLM', 12, '196.249.103.32', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJES1RuY1RiQTNhZldaVGFXdFNnMTlMaUw4WDJCYjF5bEYzeDg1d1h0IiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEyLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cHM6XC9cL2xvY2Fsc3BvbnNvcnNoaXBwb3J0YWwub3IudHpcL21lZGlhXC9wdWJsaWNcL3Byb2ZpbGUtcGhvdG9zXC91c2VyLTEyLmpwZyIsInJvdXRlIjoibWVkaWEucHVibGljIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwib3RwX3ZlcmlmaWVkIjp0cnVlfQ==', 1779487289),
('Vk5b3I1gvcI8srvOMNJIDICYFKoQpo1rCLsMGsH1', 18, '196.249.103.32', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'eyJfdG9rZW4iOiJWMXFJbURKT2tiWUZPSXJ1bkFpc2U2aDVteUV3S0luWXUwRTcyVHI0IiwidXJsIjp7ImludGVuZGVkIjoiaHR0cHM6XC9cL2xvY2Fsc3BvbnNvcnNoaXBwb3J0YWwub3IudHpcL3BhcnRpY2lwYW50c1wvMTAifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9sb2NhbHNwb25zb3JzaGlwcG9ydGFsLm9yLnR6XC9wYXJ0aWNpcGFudHNcLzEwIiwicm91dGUiOiJwYXJ0aWNpcGFudHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxOCwib3RwX3ZlcmlmaWVkIjp0cnVlfQ==', 1779487450);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `project_name` varchar(255) NOT NULL DEFAULT 'compassion',
  `center_id` varchar(255) DEFAULT NULL,
  `cluster_name` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_onboarded_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `job_title`, `project_name`, `center_id`, `cluster_name`, `profile_photo`, `role`, `email`, `email_verified_at`, `approved_at`, `approved_by`, `admin_onboarded_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(11, 'RENATHA GAHAGI', 'PC', 'Moravian', 'TZ0562', 'kigoma kasulu', NULL, 'user', 'mstudentcentre@gmail.com', NULL, '2026-05-15 06:51:36', 8, NULL, '$2y$12$YpMuk6RRCO.UQXXEhgdkqeffpgdgA/VgnkieNizRaVKPoFIWVcBnG', 'imGQCF1IokHN95r0ptOaPO67q7SSnoEQMHyXYlGZwFjazj6Mpq6IbmMxFm6W', '2026-05-15 06:49:47', '2026-05-15 06:51:36'),
(12, 'idriss mwala', 'System Administrator', 'Local Sponsorship Portal', NULL, NULL, 'profile-photos/user-12.jpg', 'official_admin', 'idrissmwala11@gmail.com', NULL, '2026-05-21 07:04:04', NULL, NULL, '$2y$12$xMXmuVdz45KHELz7e5pMSOOYnNPBhp7QOVysoxHGz9eMJ7yV5G3TO', 'pYh6tdXF5MM3k52CmVlGp3aWY7QrQW9iaosrWLDZ3agOm0ulS2OHFJU7gd8M', '2026-05-21 07:04:05', '2026-05-21 11:12:23'),
(13, 'Sibomana Jackob', 'Center coordinator', 'FPCT', 'TZ0832', 'Kigoma Northern', NULL, 'user', 'sjackob.tz0832@gmail.com', NULL, '2026-05-21 11:09:33', 12, NULL, '$2y$12$oDD9x3XXd7TIhyuBiIsobeaR.Ab6mxnxiHEOdG9uwIdv3FgXDyNS6', NULL, '2026-05-21 10:37:47', '2026-05-21 11:09:33'),
(14, 'Moravian Kasulu', 'Mratibu', 'Moravian Child and Youth Development Center', 'TZ0562', 'Kasulu', NULL, 'user', 'mstudentcentretz0562@gmail.com', NULL, '2026-05-21 11:10:12', 12, NULL, '$2y$12$JN1gevPx/LpLksrlH0Xelu.lWkmpyzWhmkDt.JM4nJSktO.A2Zu1y', NULL, '2026-05-21 10:39:17', '2026-05-21 11:10:12'),
(15, 'FILBERT DANIEL', 'Project Cordinator', 'ANGLICAN KIDYAMA', 'TZ0367', 'KASULU', NULL, 'user', 'fdaniel.tz0367@gmail.com', NULL, '2026-05-22 04:15:14', 12, NULL, '$2y$12$MaK115tkNeLwkp7H0n/vu.F52h82NZPFTUavRjPfvc2qGt1Nfm2iG', NULL, '2026-05-21 20:36:52', '2026-05-22 04:15:14'),
(16, 'AYOUB MATOKEO', 'CENTER COORDINATOR', 'PAGT', 'TZ0611', 'KASULU CLUSTER', NULL, 'user', 'amatokeo.tz0611@gmail.com', NULL, '2026-05-22 04:53:22', 12, NULL, '$2y$12$BObLCaL8IhoNUakJNdPeAeNVfGhPzukOPST7rQ9BOkDAiataKVngW', NULL, '2026-05-22 04:45:29', '2026-05-22 04:53:22'),
(17, 'OLIVER MBISE', 'Center Cordinator', 'KKKT Kasulu', 'TZ0272', 'Kasulu Cluster', NULL, 'user', 'ombise.tz272@gmail.com', NULL, '2026-05-22 07:18:09', 12, NULL, '$2y$12$vPncJwJ57vMrHatKwst8s.edMytpqfayIf1zgcmwEqeo9LY5j6j4u', NULL, '2026-05-22 05:34:50', '2026-05-22 07:18:09'),
(18, 'For Maintenance', 'PA', 'Anglican', 'TZ0342', 'KASULU', NULL, 'user', 'faithkanjanja@gmail.com', NULL, '2026-05-22 08:01:17', 12, NULL, '$2y$12$tnfNAWpn2SbFJneORH.8dOd6WBK0c09Ed2fRpL.0tZkYAWBHLNb7S', 'NrkU9PTBm2kMkFKeFZPZVt5C7YKKhrqM2Vft7CG7BErRSbIZVfzqIzXN2to4', '2026-05-22 07:59:40', '2026-05-22 13:50:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_cluster_assignments`
--
ALTER TABLE `admin_cluster_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_cluster_assignment_unique` (`admin_user_id`,`cluster_name`);

--
-- Indexes for table `admin_user_supervisions`
--
ALTER TABLE `admin_user_supervisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_supervision_unique` (`admin_user_id`,`supervised_user_id`),
  ADD KEY `admin_user_supervisions_supervised_user_id_foreign` (`supervised_user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `centers`
--
ALTER TABLE `centers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `centers_center_id_unique` (`center_id`);

--
-- Indexes for table `center_notifications`
--
ALTER TABLE `center_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `center_notifications_event_key_unique` (`event_key`),
  ADD KEY `center_notifications_participant_id_foreign` (`participant_id`),
  ADD KEY `center_notifications_center_id_index` (`center_id`),
  ADD KEY `center_notifications_type_index` (`type`),
  ADD KEY `center_notifications_sent_by_user_id_foreign` (`sent_by_user_id`),
  ADD KEY `center_notifications_target_user_id_foreign` (`target_user_id`);

--
-- Indexes for table `center_notification_reads`
--
ALTER TABLE `center_notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `center_notification_reads_center_notification_id_user_id_unique` (`center_notification_id`,`user_id`),
  ADD KEY `center_notification_reads_user_id_foreign` (`user_id`);

--
-- Indexes for table `center_user_assignments`
--
ALTER TABLE `center_user_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `center_user_assignments_user_id_center_id_unique` (`user_id`,`center_id`);

--
-- Indexes for table `church_profiles`
--
ALTER TABLE `church_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `church_profiles_created_by_user_id_foreign` (`created_by_user_id`),
  ADD KEY `church_profiles_center_id_index` (`center_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otp_codes_user_id_foreign` (`user_id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `participants_local_participant_id_unique` (`local_participant_id`),
  ADD KEY `participants_created_by_user_id_foreign` (`created_by_user_id`);

--
-- Indexes for table `participant_sponsorships`
--
ALTER TABLE `participant_sponsorships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `participant_sponsorships_participant_id_foreign` (`participant_id`),
  ADD KEY `participant_sponsorships_created_by_user_id_foreign` (`created_by_user_id`);

--
-- Indexes for table `participant_treatments`
--
ALTER TABLE `participant_treatments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `participant_treatments_participant_id_foreign` (`participant_id`),
  ADD KEY `participant_treatments_center_id_created_by_user_id_index` (`center_id`,`created_by_user_id`),
  ADD KEY `participant_treatments_center_id_index` (`center_id`),
  ADD KEY `participant_treatments_treatment_date_index` (`treatment_date`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `program_attendance_entries`
--
ALTER TABLE `program_attendance_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `program_attendance_session_participant_unique` (`program_attendance_session_id`,`participant_id`),
  ADD KEY `program_attendance_entries_participant_id_foreign` (`participant_id`);

--
-- Indexes for table `program_attendance_sessions`
--
ALTER TABLE `program_attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_attendance_sessions_created_by_user_id_foreign` (`created_by_user_id`),
  ADD KEY `program_attendance_sessions_center_id_index` (`center_id`),
  ADD KEY `program_attendance_sessions_attendance_date_index` (`attendance_date`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_cluster_assignments`
--
ALTER TABLE `admin_cluster_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_user_supervisions`
--
ALTER TABLE `admin_user_supervisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `centers`
--
ALTER TABLE `centers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `center_notifications`
--
ALTER TABLE `center_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `center_notification_reads`
--
ALTER TABLE `center_notification_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `center_user_assignments`
--
ALTER TABLE `center_user_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `church_profiles`
--
ALTER TABLE `church_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `participant_sponsorships`
--
ALTER TABLE `participant_sponsorships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `participant_treatments`
--
ALTER TABLE `participant_treatments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `program_attendance_entries`
--
ALTER TABLE `program_attendance_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `program_attendance_sessions`
--
ALTER TABLE `program_attendance_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_cluster_assignments`
--
ALTER TABLE `admin_cluster_assignments`
  ADD CONSTRAINT `admin_cluster_assignments_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_user_supervisions`
--
ALTER TABLE `admin_user_supervisions`
  ADD CONSTRAINT `admin_user_supervisions_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_user_supervisions_supervised_user_id_foreign` FOREIGN KEY (`supervised_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `center_notifications`
--
ALTER TABLE `center_notifications`
  ADD CONSTRAINT `center_notifications_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `center_notifications_sent_by_user_id_foreign` FOREIGN KEY (`sent_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `center_notifications_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `center_notification_reads`
--
ALTER TABLE `center_notification_reads`
  ADD CONSTRAINT `center_notification_reads_center_notification_id_foreign` FOREIGN KEY (`center_notification_id`) REFERENCES `center_notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `center_notification_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `center_user_assignments`
--
ALTER TABLE `center_user_assignments`
  ADD CONSTRAINT `center_user_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `church_profiles`
--
ALTER TABLE `church_profiles`
  ADD CONSTRAINT `church_profiles_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD CONSTRAINT `otp_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `participant_sponsorships`
--
ALTER TABLE `participant_sponsorships`
  ADD CONSTRAINT `participant_sponsorships_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participant_sponsorships_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `participant_treatments`
--
ALTER TABLE `participant_treatments`
  ADD CONSTRAINT `participant_treatments_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `program_attendance_entries`
--
ALTER TABLE `program_attendance_entries`
  ADD CONSTRAINT `program_attendance_entries_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `program_attendance_entries_program_attendance_session_id_foreign` FOREIGN KEY (`program_attendance_session_id`) REFERENCES `program_attendance_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `program_attendance_sessions`
--
ALTER TABLE `program_attendance_sessions`
  ADD CONSTRAINT `program_attendance_sessions_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
