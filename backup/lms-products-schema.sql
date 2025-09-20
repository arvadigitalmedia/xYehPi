-- =====================================================
-- LMS PRODUCTS ENHANCEMENT SCHEMA
-- Extends epic_products for comprehensive LMS functionality
-- =====================================================

-- Add LMS-specific columns to epic_products
ALTER TABLE `epic_products` 
ADD COLUMN `type` enum('physical','digital','course','tools','masterclass') NOT NULL DEFAULT 'digital' AFTER `name`,
ADD COLUMN `duration` varchar(50) NULL AFTER `short_description`,
ADD COLUMN `difficulty_level` enum('beginner','intermediate','advanced','expert') NULL AFTER `duration`,
ADD COLUMN `total_modules` int(11) NOT NULL DEFAULT 0 AFTER `difficulty_level`,
ADD COLUMN `estimated_hours` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `total_modules`,
ADD COLUMN `certificate_enabled` boolean NOT NULL DEFAULT FALSE AFTER `estimated_hours`,
ADD COLUMN `access_level` json NULL AFTER `certificate_enabled`,
ADD COLUMN `prerequisites` json NULL AFTER `access_level`,
ADD COLUMN `learning_objectives` json NULL AFTER `prerequisites`,
ADD COLUMN `instructor_id` bigint(20) UNSIGNED NULL AFTER `learning_objectives`,
ADD COLUMN `category_id` bigint(20) UNSIGNED NULL AFTER `instructor_id`,
ADD COLUMN `tags` json NULL AFTER `category_id`,
ADD COLUMN `rating` decimal(3,2) NOT NULL DEFAULT 0.00 AFTER `tags`,
ADD COLUMN `total_reviews` int(11) NOT NULL DEFAULT 0 AFTER `rating`,
ADD COLUMN `enrollment_count` int(11) NOT NULL DEFAULT 0 AFTER `total_reviews`;

-- Product Categories
CREATE TABLE `epic_product_categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text NULL,
  `icon` varchar(100) NULL,
  `color` varchar(7) NULL DEFAULT '#3B82F6',
  `parent_id` bigint(20) UNSIGNED NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_product_categories_uuid_unique` (`uuid`),
  UNIQUE KEY `epic_product_categories_slug_unique` (`slug`),
  KEY `epic_product_categories_parent_id_foreign` (`parent_id`),
  KEY `epic_product_categories_status_index` (`status`),
  CONSTRAINT `epic_product_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `epic_product_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Modules (for courses)
CREATE TABLE `epic_product_modules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NULL,
  `content` longtext NULL,
  `content_type` enum('text','video','audio','pdf','quiz','assignment') NOT NULL DEFAULT 'text',
  `video_url` varchar(500) NULL,
  `video_duration` int(11) NULL COMMENT 'Duration in seconds',
  `file_url` varchar(500) NULL,
  `file_size` bigint(20) NULL COMMENT 'File size in bytes',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_preview` boolean NOT NULL DEFAULT FALSE,
  `estimated_duration` int(11) NOT NULL DEFAULT 0 COMMENT 'Estimated completion time in minutes',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_product_modules_uuid_unique` (`uuid`),
  KEY `epic_product_modules_product_id_foreign` (`product_id`),
  KEY `epic_product_modules_status_index` (`status`),
  KEY `epic_product_modules_sort_order_index` (`sort_order`),
  CONSTRAINT `epic_product_modules_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `epic_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Progress Tracking
CREATE TABLE `epic_user_progress` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `module_id` bigint(20) UNSIGNED NULL,
  `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `time_spent` int(11) NOT NULL DEFAULT 0 COMMENT 'Time spent in seconds',
  `status` enum('not_started','in_progress','completed','paused') NOT NULL DEFAULT 'not_started',
  `completed_at` timestamp NULL,
  `last_accessed_at` timestamp NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_user_progress_uuid_unique` (`uuid`),
  UNIQUE KEY `epic_user_progress_unique` (`user_id`, `product_id`, `module_id`),
  KEY `epic_user_progress_user_id_foreign` (`user_id`),
  KEY `epic_user_progress_product_id_foreign` (`product_id`),
  KEY `epic_user_progress_module_id_foreign` (`module_id`),
  KEY `epic_user_progress_status_index` (`status`),
  CONSTRAINT `epic_user_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_user_progress_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `epic_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_user_progress_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `epic_product_modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Certificates
CREATE TABLE `epic_user_certificates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `completion_percentage` decimal(5,2) NOT NULL DEFAULT 100.00,
  `issued_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL,
  `certificate_data` json NULL COMMENT 'Certificate template data',
  `status` enum('active','revoked','expired') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_user_certificates_uuid_unique` (`uuid`),
  UNIQUE KEY `epic_user_certificates_number_unique` (`certificate_number`),
  UNIQUE KEY `epic_user_certificates_unique` (`user_id`, `product_id`),
  KEY `epic_user_certificates_user_id_foreign` (`user_id`),
  KEY `epic_user_certificates_product_id_foreign` (`product_id`),
  KEY `epic_user_certificates_status_index` (`status`),
  CONSTRAINT `epic_user_certificates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_user_certificates_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `epic_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Reviews
CREATE TABLE `epic_product_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `title` varchar(200) NULL,
  `review` text NULL,
  `is_verified_purchase` boolean NOT NULL DEFAULT FALSE,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_product_reviews_uuid_unique` (`uuid`),
  UNIQUE KEY `epic_product_reviews_unique` (`user_id`, `product_id`),
  KEY `epic_product_reviews_user_id_foreign` (`user_id`),
  KEY `epic_product_reviews_product_id_foreign` (`product_id`),
  KEY `epic_product_reviews_rating_index` (`rating`),
  KEY `epic_product_reviews_status_index` (`status`),
  CONSTRAINT `epic_product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `epic_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints to epic_products
ALTER TABLE `epic_products`
ADD CONSTRAINT `epic_products_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `epic_products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `epic_product_categories` (`id`) ON DELETE SET NULL;

-- Insert default categories
INSERT INTO `epic_product_categories` (`uuid`, `name`, `slug`, `description`, `icon`, `color`, `sort_order`) VALUES
(UUID(), 'Digital Marketing', 'digital-marketing', 'Comprehensive digital marketing courses and tools', 'trending-up', '#3B82F6', 1),
(UUID(), 'SEO & Analytics', 'seo-analytics', 'Search engine optimization and web analytics', 'search', '#10B981', 2),
(UUID(), 'Social Media', 'social-media', 'Social media marketing strategies and tools', 'share-2', '#8B5CF6', 3),
(UUID(), 'Content Marketing', 'content-marketing', 'Content creation and marketing strategies', 'edit-3', '#F59E0B', 4),
(UUID(), 'Email Marketing', 'email-marketing', 'Email marketing automation and strategies', 'mail', '#EF4444', 5),
(UUID(), 'Tools & Templates', 'tools-templates', 'Marketing tools and ready-to-use templates', 'tool', '#6B7280', 6);

-- Insert sample LMS products
INSERT INTO `epic_products` (
  `uuid`, `name`, `type`, `slug`, `description`, `short_description`, `duration`, `difficulty_level`, 
  `total_modules`, `estimated_hours`, `certificate_enabled`, `access_level`, `learning_objectives`,
  `price`, `commission_type`, `commission_value`, `status`, `featured`, `category_id`
) VALUES
(
  UUID(), 
  'Digital Marketing Mastery', 
  'course',
  'digital-marketing-mastery',
  'Panduan lengkap digital marketing dari basic hingga advanced. Course ini mencakup strategi pemasaran digital terkini, tools yang digunakan profesional, dan case study dari brand-brand ternama. Anda akan mempelajari SEO, SEM, social media marketing, content marketing, email marketing, dan analytics.',
  'Panduan lengkap digital marketing dari basic hingga advanced',
  '8 jam',
  'intermediate',
  12,
  8.00,
  TRUE,
  JSON_ARRAY('free', 'epic', 'epis'),
  JSON_ARRAY(
    'Memahami fundamental digital marketing',
    'Menguasai strategi SEO dan SEM',
    'Membuat campaign social media yang efektif',
    'Mengoptimalkan conversion rate',
    'Menganalisis performa marketing dengan tools analytics'
  ),
  299000.00,
  'percentage',
  30.00,
  'active',
  TRUE,
  (SELECT id FROM epic_product_categories WHERE slug = 'digital-marketing' LIMIT 1)
),
(
  UUID(), 
  'Advanced SEO Strategies', 
  'course',
  'advanced-seo-strategies',
  'Teknik SEO terdepan untuk mendominasi search engine. Course ini fokus pada strategi SEO advanced yang digunakan oleh para expert. Anda akan mempelajari technical SEO, link building strategies, content optimization, dan tools SEO profesional.',
  'Teknik SEO terdepan untuk mendominasi search engine',
  '6 jam',
  'advanced',
  8,
  6.00,
  TRUE,
  JSON_ARRAY('epic', 'epis'),
  JSON_ARRAY(
    'Menguasai technical SEO advanced',
    'Membangun strategi link building yang efektif',
    'Optimasi content untuk search intent',
    'Menggunakan tools SEO profesional',
    'Menganalisis kompetitor dengan mendalam'
  ),
  199000.00,
  'percentage',
  35.00,
  'active',
  TRUE,
  (SELECT id FROM epic_product_categories WHERE slug = 'seo-analytics' LIMIT 1)
);

-- Insert sample modules for Digital Marketing Mastery
INSERT INTO `epic_product_modules` (
  `uuid`, `product_id`, `title`, `description`, `content_type`, `sort_order`, `estimated_duration`, `status`
) VALUES
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Introduction to Digital Marketing', 'Overview of digital marketing landscape and opportunities', 'video', 1, 45, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Understanding Your Target Audience', 'How to research and define your ideal customer', 'video', 2, 40, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'SEO Fundamentals', 'Basic search engine optimization techniques', 'video', 3, 50, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Content Marketing Strategy', 'Creating valuable content that converts', 'video', 4, 45, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Social Media Marketing', 'Leveraging social platforms for business growth', 'video', 5, 40, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Email Marketing Automation', 'Building and nurturing email lists', 'video', 6, 35, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Paid Advertising (PPC)', 'Google Ads and Facebook Ads strategies', 'video', 7, 50, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Analytics and Measurement', 'Tracking and optimizing your campaigns', 'video', 8, 40, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Conversion Rate Optimization', 'Improving your website conversion rates', 'video', 9, 35, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Marketing Automation Tools', 'Using tools to scale your marketing', 'video', 10, 30, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Case Studies & Best Practices', 'Real-world examples and success stories', 'video', 11, 25, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'digital-marketing-mastery'), 'Final Project & Certification', 'Apply your knowledge in a comprehensive project', 'assignment', 12, 60, 'published');

-- Insert sample modules for Advanced SEO Strategies
INSERT INTO `epic_product_modules` (
  `uuid`, `product_id`, `title`, `description`, `content_type`, `sort_order`, `estimated_duration`, `status`
) VALUES
(UUID(), (SELECT id FROM epic_products WHERE slug = 'advanced-seo-strategies'), 'Advanced Keyword Research', 'Finding high-value, low-competition keywords', 'video', 1, 50, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'advanced-seo-strategies'), 'Technical SEO Mastery', 'Site speed, crawlability, and indexation optimization', 'video', 2, 60, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'advanced-seo-strategies'), 'Content Optimization Strategies', 'Creating content that ranks and converts', 'video', 3, 45, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'advanced-seo-strategies'), 'Link Building at Scale', 'Advanced link building techniques and outreach', 'video', 4, 55, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'advanced-seo-strategies'), 'Local SEO Domination', 'Ranking in local search results', 'video', 5, 40, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'advanced-seo-strategies'), 'SEO Tools Mastery', 'Advanced usage of professional SEO tools', 'video', 6, 35, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'advanced-seo-strategies'), 'Competitor Analysis Deep Dive', 'Analyzing and outranking competitors', 'video', 7, 45, 'published'),
(UUID(), (SELECT id FROM epic_products WHERE slug = 'advanced-seo-strategies'), 'SEO Strategy Implementation', 'Putting it all together with a comprehensive strategy', 'assignment', 8, 50, 'published');