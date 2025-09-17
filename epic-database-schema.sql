-- =====================================================
-- EPIC Hub Database Schema
-- Modern Affiliate Marketing Platform
-- Migration from SimpleAff Plus (sa_*) to EPIC Hub (epic_*)
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- 1. USERS MANAGEMENT
-- =====================================================

-- Users table (replaces sa_member)
CREATE TABLE `epic_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NULL,
  `avatar` varchar(255) NULL,
  `referral_code` varchar(20) NULL,
  `status` enum('pending','free','epic','suspended','banned') NOT NULL DEFAULT 'pending',
  `role` enum('user','super_admin') NOT NULL DEFAULT 'user',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `email_confirmation_token` varchar(6) NULL,
  `password_reset_token` varchar(100) NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `additional_data` json NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_users_uuid_unique` (`uuid`),
  UNIQUE KEY `epic_users_email_unique` (`email`),
  UNIQUE KEY `epic_users_referral_code_unique` (`referral_code`),
  KEY `epic_users_status_index` (`status`),
  KEY `epic_users_role_index` (`role`),
  KEY `epic_users_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User tokens table (for password reset, email verification, etc.)
CREATE TABLE `epic_user_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` enum('password_reset','email_verification','api_token') NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_user_tokens_token_unique` (`token`),
  KEY `epic_user_tokens_user_id_index` (`user_id`),
  KEY `epic_user_tokens_type_index` (`type`),
  KEY `epic_user_tokens_expires_at_index` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User profiles table (for sponsor/affiliate profiles)
CREATE TABLE `epic_user_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(100) NULL,
  `experience` varchar(50) NULL,
  `bio` text NULL,
  `phone` varchar(20) NULL,
  `avatar` varchar(255) NULL,
  `social_links` json NULL,
  `website` varchar(255) NULL,
  `location` varchar(100) NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_user_profiles_user_id_unique` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Landing page configurations
CREATE TABLE `epic_landing_configs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `template_name` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `custom_settings` json NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_landing_configs_user_template_unique` (`user_id`, `template_name`),
  KEY `epic_landing_configs_user_id_index` (`user_id`),
  KEY `epic_landing_configs_template_name_index` (`template_name`),
  FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Landing page visits tracking
CREATE TABLE `epic_landing_visits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sponsor_id` bigint(20) UNSIGNED NOT NULL,
  `template_name` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NULL,
  `referrer` varchar(500) NULL,
  `visited_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_landing_visits_sponsor_id_index` (`sponsor_id`),
  KEY `epic_landing_visits_template_name_index` (`template_name`),
  KEY `epic_landing_visits_visited_at_index` (`visited_at`),
  KEY `epic_landing_visits_ip_address_index` (`ip_address`),
  FOREIGN KEY (`sponsor_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Advanced landing pages management system
CREATE TABLE `epic_landing_pages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `page_title` varchar(200) NOT NULL,
  `page_description` text NULL,
  `page_image` varchar(255) NULL,
  `page_slug` varchar(100) NOT NULL,
  `landing_url` text NOT NULL,
  `method` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=iframe, 2=inject, 3=redirect',
  `find_replace_data` json NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_landing_pages_slug_unique` (`page_slug`),
  KEY `epic_landing_pages_user_id_index` (`user_id`),
  KEY `epic_landing_pages_is_active_index` (`is_active`),
  KEY `epic_landing_pages_method_index` (`method`),
  FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Landing page visits tracking
CREATE TABLE `epic_landing_page_visits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `landing_page_id` bigint(20) UNSIGNED NOT NULL,
  `sponsor_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NULL,
  `referrer_url` varchar(500) NULL,
  `visited_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_landing_page_visits_landing_page_id_index` (`landing_page_id`),
  KEY `epic_landing_page_visits_sponsor_id_index` (`sponsor_id`),
  KEY `epic_landing_page_visits_visited_at_index` (`visited_at`),
  KEY `epic_landing_page_visits_ip_address_index` (`ip_address`),
  FOREIGN KEY (`landing_page_id`) REFERENCES `epic_landing_pages` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sponsor_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonials table
CREATE TABLE `epic_testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) UNSIGNED NULL,
  `user_id` bigint(20) UNSIGNED NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NULL,
  `customer_role` varchar(100) NULL,
  `customer_avatar` varchar(255) NULL,
  `content` text NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_testimonials_product_id_index` (`product_id`),
  KEY `epic_testimonials_user_id_index` (`user_id`),
  KEY `epic_testimonials_is_approved_index` (`is_approved`),
  KEY `epic_testimonials_rating_index` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Referrals table (replaces sa_sponsor - simplified)
CREATE TABLE `epic_referrals` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `referrer_id` bigint(20) UNSIGNED NULL,
  `referral_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `total_earnings` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_referrals` int(11) NOT NULL DEFAULT 0,
  `total_sales` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_referrals_user_id_unique` (`user_id`),
  KEY `epic_referrals_referrer_id_foreign` (`referrer_id`),
  KEY `epic_referrals_status_index` (`status`),
  CONSTRAINT `epic_referrals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_referrals_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. PRODUCT MANAGEMENT
-- =====================================================

-- Products table (replaces sa_page)
CREATE TABLE `epic_products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text NULL,
  `short_description` varchar(500) NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `commission_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `commission_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image` varchar(255) NULL,
  `gallery` json NULL,
  `landing_page_type` enum('iframe','redirect','internal') NOT NULL DEFAULT 'iframe',
  `landing_page_url` varchar(500) NULL,
  `download_file` varchar(255) NULL,
  `content` longtext NULL,
  `status` enum('draft','active','inactive','archived') NOT NULL DEFAULT 'draft',
  `featured` boolean NOT NULL DEFAULT FALSE,
  `sales_count` int(11) NOT NULL DEFAULT 0,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(200) NULL,
  `seo_description` varchar(500) NULL,
  `seo_keywords` varchar(500) NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_products_uuid_unique` (`uuid`),
  UNIQUE KEY `epic_products_slug_unique` (`slug`),
  KEY `epic_products_status_index` (`status`),
  KEY `epic_products_featured_index` (`featured`),
  KEY `epic_products_price_index` (`price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. ORDER MANAGEMENT
-- =====================================================

-- Orders table (replaces sa_order)
CREATE TABLE `epic_orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `referrer_id` bigint(20) UNSIGNED NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `unique_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `commission_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','cancelled','refunded','expired') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) NULL,
  `payment_reference` varchar(100) NULL,
  `payment_data` json NULL,
  `staff_id` bigint(20) UNSIGNED NULL,
  `notes` text NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_orders_uuid_unique` (`uuid`),
  UNIQUE KEY `epic_orders_order_number_unique` (`order_number`),
  KEY `epic_orders_user_id_foreign` (`user_id`),
  KEY `epic_orders_referrer_id_foreign` (`referrer_id`),
  KEY `epic_orders_product_id_foreign` (`product_id`),
  KEY `epic_orders_staff_id_foreign` (`staff_id`),
  KEY `epic_orders_status_index` (`status`),
  KEY `epic_orders_created_at_index` (`created_at`),
  CONSTRAINT `epic_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_orders_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `epic_orders_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `epic_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_orders_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. FINANCIAL TRACKING
-- =====================================================

-- Transactions table (replaces sa_laporan)
CREATE TABLE `epic_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `referrer_id` bigint(20) UNSIGNED NULL,
  `type` enum('sale','commission','withdrawal','refund','bonus','penalty') NOT NULL,
  `amount_in` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount_out` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_before` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_after` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','completed','cancelled','failed') NOT NULL DEFAULT 'pending',
  `description` varchar(500) NULL,
  `reference` varchar(100) NULL,
  `metadata` json NULL,
  `processed_by` bigint(20) UNSIGNED NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_transactions_order_id_foreign` (`order_id`),
  KEY `epic_transactions_user_id_foreign` (`user_id`),
  KEY `epic_transactions_referrer_id_foreign` (`referrer_id`),
  KEY `epic_transactions_processed_by_foreign` (`processed_by`),
  KEY `epic_transactions_type_index` (`type`),
  KEY `epic_transactions_status_index` (`status`),
  KEY `epic_transactions_created_at_index` (`created_at`),
  CONSTRAINT `epic_transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `epic_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `epic_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_transactions_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `epic_transactions_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. AFFILIATE LINK TRACKING
-- =====================================================

-- Affiliate Links table (new feature)
CREATE TABLE `epic_affiliate_links` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `link_code` varchar(50) NOT NULL,
  `original_url` varchar(500) NOT NULL,
  `custom_parameters` json NULL,
  `click_count` int(11) NOT NULL DEFAULT 0,
  `conversion_count` int(11) NOT NULL DEFAULT 0,
  `conversion_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `last_clicked_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_affiliate_links_link_code_unique` (`link_code`),
  KEY `epic_affiliate_links_user_id_foreign` (`user_id`),
  KEY `epic_affiliate_links_product_id_foreign` (`product_id`),
  KEY `epic_affiliate_links_status_index` (`status`),
  CONSTRAINT `epic_affiliate_links_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_affiliate_links_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `epic_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link Clicks table (analytics)
CREATE TABLE `epic_link_clicks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_link_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NULL,
  `referrer` varchar(500) NULL,
  `country` varchar(2) NULL,
  `city` varchar(100) NULL,
  `device_type` enum('desktop','mobile','tablet','bot') NULL,
  `browser` varchar(50) NULL,
  `os` varchar(50) NULL,
  `converted` boolean NOT NULL DEFAULT FALSE,
  `conversion_value` decimal(15,2) NULL,
  `session_id` varchar(100) NULL,
  `clicked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_link_clicks_affiliate_link_id_foreign` (`affiliate_link_id`),
  KEY `epic_link_clicks_clicked_at_index` (`clicked_at`),
  KEY `epic_link_clicks_converted_index` (`converted`),
  KEY `epic_link_clicks_ip_address_index` (`ip_address`),
  KEY `idx_link_converted_clicked` (`affiliate_link_id`, `converted`, `clicked_at`),
  CONSTRAINT `epic_link_clicks_affiliate_link_id_foreign` FOREIGN KEY (`affiliate_link_id`) REFERENCES `epic_affiliate_links` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. CONTENT MANAGEMENT
-- =====================================================

-- Categories table (replaces sa_kategori)
CREATE TABLE `epic_categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) UNSIGNED NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text NULL,
  `image` varchar(255) NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `seo_title` varchar(200) NULL,
  `seo_description` varchar(500) NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_categories_slug_unique` (`slug`),
  KEY `epic_categories_parent_id_foreign` (`parent_id`),
  KEY `epic_categories_status_index` (`status`),
  KEY `epic_categories_sort_order_index` (`sort_order`),
  CONSTRAINT `epic_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `epic_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles table (replaces sa_artikel)
CREATE TABLE `epic_articles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `category_id` bigint(20) UNSIGNED NULL,
  `product_id` bigint(20) UNSIGNED NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `excerpt` text NULL,
  `content` longtext NULL,
  `featured_image` varchar(255) NULL,
  `gallery` json NULL,
  `status` enum('draft','published','private','archived') NOT NULL DEFAULT 'draft',
  `visibility` enum('public','members','premium') NOT NULL DEFAULT 'public',
  `view_count` int(11) NOT NULL DEFAULT 0,
  `reading_time` int(11) NULL,
  `seo_title` varchar(200) NULL,
  `seo_description` varchar(500) NULL,
  `seo_keywords` varchar(500) NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_articles_uuid_unique` (`uuid`),
  UNIQUE KEY `epic_articles_slug_unique` (`slug`),
  KEY `epic_articles_category_id_foreign` (`category_id`),
  KEY `epic_articles_product_id_foreign` (`product_id`),
  KEY `epic_articles_author_id_foreign` (`author_id`),
  KEY `epic_articles_status_index` (`status`),
  KEY `epic_articles_visibility_index` (`visibility`),
  KEY `epic_articles_published_at_index` (`published_at`),
  CONSTRAINT `epic_articles_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `epic_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `epic_articles_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `epic_products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `epic_articles_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. SYSTEM CONFIGURATION
-- =====================================================

-- Settings table (replaces sa_setting)
CREATE TABLE `epic_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` longtext NULL,
  `type` enum('string','integer','boolean','json','array','file') NOT NULL DEFAULT 'string',
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `description` varchar(500) NULL,
  `is_public` boolean NOT NULL DEFAULT FALSE,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_settings_key_unique` (`key`),
  KEY `epic_settings_group_index` (`group`),
  KEY `epic_settings_is_public_index` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Form Fields table (replaces sa_form)
CREATE TABLE `epic_form_fields` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `label` varchar(200) NOT NULL,
  `type` enum('text','email','password','number','textarea','select','checkbox','radio','file','date','datetime') NOT NULL,
  `placeholder` varchar(200) NULL,
  `description` text NULL,
  `options` json NULL,
  `validation_rules` json NULL,
  `is_required` boolean NOT NULL DEFAULT FALSE,
  `show_in_profile` boolean NOT NULL DEFAULT FALSE,
  `show_in_registration` boolean NOT NULL DEFAULT FALSE,
  `show_in_network` boolean NOT NULL DEFAULT FALSE,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_form_fields_name_unique` (`name`),
  KEY `epic_form_fields_status_index` (`status`),
  KEY `epic_form_fields_sort_order_index` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. ANALYTICS & TRACKING
-- =====================================================

-- Analytics table (replaces sa_visitor)
CREATE TABLE `epic_analytics` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NULL,
  `session_id` varchar(100) NOT NULL,
  `page_url` varchar(500) NOT NULL,
  `page_title` varchar(200) NULL,
  `referrer` varchar(500) NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NULL,
  `country` varchar(2) NULL,
  `city` varchar(100) NULL,
  `device_type` enum('desktop','mobile','tablet','bot') NULL,
  `browser` varchar(50) NULL,
  `os` varchar(50) NULL,
  `duration` int(11) NULL,
  `bounce` boolean NOT NULL DEFAULT TRUE,
  `conversion` boolean NOT NULL DEFAULT FALSE,
  `conversion_value` decimal(15,2) NULL,
  `visited_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_analytics_user_id_foreign` (`user_id`),
  KEY `epic_analytics_session_id_index` (`session_id`),
  KEY `epic_analytics_visited_at_index` (`visited_at`),
  KEY `epic_analytics_conversion_index` (`conversion`),
  KEY `epic_analytics_ip_address_index` (`ip_address`),
  KEY `idx_date_conversion` (`visited_at`, `conversion`),
  CONSTRAINT `epic_analytics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. NOTIFICATIONS & COMMUNICATIONS
-- =====================================================

-- Notifications table (new feature)
CREATE TABLE `epic_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `data` json NULL,
  `channels` json NOT NULL, -- ['email', 'whatsapp', 'dashboard']
  `status` enum('pending','sent','failed','read') NOT NULL DEFAULT 'pending',
  `read_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_notifications_user_id_foreign` (`user_id`),
  KEY `epic_notifications_type_index` (`type`),
  KEY `epic_notifications_status_index` (`status`),
  KEY `epic_notifications_created_at_index` (`created_at`),
  CONSTRAINT `epic_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. AUDIT & LOGGING
-- =====================================================

-- Activity Log table (new feature)
CREATE TABLE `epic_activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NULL,
  `action` varchar(100) NOT NULL,
  `model_type` varchar(100) NULL,
  `model_id` bigint(20) UNSIGNED NULL,
  `description` text NULL,
  `old_values` json NULL,
  `new_values` json NULL,
  `ip_address` varchar(45) NULL,
  `user_agent` text NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_activity_log_user_id_foreign` (`user_id`),
  KEY `epic_activity_log_action_index` (`action`),
  KEY `epic_activity_log_model_index` (`model_type`, `model_id`),
  KEY `epic_activity_log_created_at_index` (`created_at`),
  CONSTRAINT `epic_activity_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- =====================================================

-- Composite indexes for common queries
ALTER TABLE `epic_orders` ADD INDEX `idx_user_status_created` (`user_id`, `status`, `created_at`);
-- Note: epic_transactions composite index created dynamically to avoid conflicts
ALTER TABLE `epic_affiliate_links` ADD INDEX `idx_user_product_status` (`user_id`, `product_id`, `status`);
-- ALTER TABLE `epic_link_clicks` ADD INDEX `idx_link_converted_clicked` (`affiliate_link_id`, `converted`, `clicked_at`); -- Removed: duplicate index
ALTER TABLE `epic_articles` ADD INDEX `idx_status_visibility_published` (`status`, `visibility`, `published_at`);
-- ALTER TABLE `epic_analytics` ADD INDEX `idx_date_conversion` (`visited_at`, `conversion`); -- Removed: duplicate index

-- =====================================================
-- TRIGGERS FOR AUTOMATIC CALCULATIONS
-- =====================================================

-- Note: Triggers have been removed to ensure installation compatibility
-- Database will function normally without triggers
-- Automatic calculations can be handled by application logic if needed

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- =====================================================
-- INITIAL DATA SEEDING
-- =====================================================

-- Default admin user
INSERT INTO `epic_users` (`uuid`, `name`, `email`, `password`, `referral_code`, `status`, `role`) 
VALUES 
('550e8400-e29b-41d4-a716-446655440000', 'Administrator', 'admin@epichub.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN001', 'epic', 'super_admin');

-- Default settings
INSERT INTO `epic_settings` (`key`, `value`, `type`, `group`, `description`) VALUES
('site_name', 'EPIC Hub', 'string', 'general', 'Website name'),
('site_description', 'Modern Affiliate Marketing Platform', 'string', 'general', 'Website description'),
('site_logo', '', 'file', 'general', 'Website logo'),
('site_favicon', '', 'file', 'general', 'Website favicon'),
('default_commission_rate', '10.00', 'string', 'affiliate', 'Default commission rate percentage'),
('currency', 'IDR', 'string', 'general', 'Default currency'),
('timezone', 'Asia/Jakarta', 'string', 'general', 'Default timezone'),
('email_from_name', 'EPIC Hub', 'string', 'email', 'Email sender name'),
('email_from_address', 'noreply@epichub.local', 'string', 'email', 'Email sender address');

-- Default form fields
INSERT INTO `epic_form_fields` (`name`, `label`, `type`, `is_required`, `show_in_registration`, `show_in_profile`, `sort_order`) VALUES
('name', 'Full Name', 'text', 1, 1, 1, 1),
('email', 'Email Address', 'email', 1, 1, 1, 2),
('phone', 'Phone Number', 'text', 0, 1, 1, 3),
('password', 'Password', 'password', 1, 1, 0, 4);

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- User statistics view (simplified for better compatibility)
CREATE VIEW `epic_user_stats` AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.status,
    COALESCE(r.total_referrals, 0) as total_referrals,
    COALESCE(r.total_sales, 0) as total_sales,
    COALESCE(r.total_earnings, 0) as total_earnings,
    (
        SELECT COUNT(*) 
        FROM `epic_orders` o 
        WHERE o.user_id = u.id AND o.status = 'paid'
    ) as total_orders,
    (
        SELECT COALESCE(SUM(amount), 0) 
        FROM `epic_orders` o 
        WHERE o.user_id = u.id AND o.status = 'paid'
    ) as total_spent
FROM `epic_users` u
LEFT JOIN `epic_referrals` r ON u.id = r.user_id;

-- Product performance view (simplified for better compatibility)
CREATE VIEW `epic_product_performance` AS
SELECT 
    p.id,
    p.name,
    p.price,
    p.sales_count,
    (
        SELECT COALESCE(SUM(amount), 0) 
        FROM `epic_orders` o 
        WHERE o.product_id = p.id AND o.status = 'paid'
    ) as total_revenue,
    (
        SELECT COALESCE(SUM(commission_amount), 0) 
        FROM `epic_orders` o 
        WHERE o.product_id = p.id AND o.status = 'paid'
    ) as total_commission,
    (
        SELECT COALESCE(SUM(click_count), 0) 
        FROM `epic_affiliate_links` al 
        WHERE al.product_id = p.id
    ) as total_clicks,
    (
        SELECT COALESCE(SUM(conversion_count), 0) 
        FROM `epic_affiliate_links` al 
        WHERE al.product_id = p.id
    ) as total_conversions,
    CASE 
        WHEN (
            SELECT COALESCE(SUM(click_count), 0) 
            FROM `epic_affiliate_links` al 
            WHERE al.product_id = p.id
        ) > 0 
        THEN ROUND((
            (
                SELECT COALESCE(SUM(conversion_count), 0) 
                FROM `epic_affiliate_links` al 
                WHERE al.product_id = p.id
            ) / (
                SELECT SUM(click_count) 
                FROM `epic_affiliate_links` al 
                WHERE al.product_id = p.id
            )
        ) * 100, 2)
        ELSE 0 
    END as conversion_rate
FROM `epic_products` p;

-- =====================================================
-- END OF SCHEMA
-- =====================================================