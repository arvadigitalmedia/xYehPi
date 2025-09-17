-- =====================================================
-- EPIC Hub Blog Tracking Enhancement (Simplified)
-- Basic tables for blog referral tracking without complex procedures
-- =====================================================

-- Add blog tracking fields to epic_landing_visits table
ALTER TABLE `epic_landing_visits` 
ADD COLUMN `article_id` bigint(20) UNSIGNED NULL AFTER `template_name`,
ADD COLUMN `article_slug` varchar(200) NULL AFTER `article_id`,
ADD INDEX `epic_landing_visits_article_id_index` (`article_id`),
ADD INDEX `epic_landing_visits_article_slug_index` (`article_slug`);

-- Add foreign key constraint for article_id (if epic_articles table exists)
-- ALTER TABLE `epic_landing_visits`
-- ADD CONSTRAINT `epic_landing_visits_article_id_foreign` 
-- FOREIGN KEY (`article_id`) REFERENCES `epic_articles` (`id`) ON DELETE SET NULL;

-- Create blog_article_stats table for detailed analytics
CREATE TABLE `epic_blog_article_stats` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `unique_views` int(11) NOT NULL DEFAULT 0,
  `referrals_generated` int(11) NOT NULL DEFAULT 0,
  `sales_generated` int(11) NOT NULL DEFAULT 0,
  `revenue_generated` decimal(15,2) NOT NULL DEFAULT 0.00,
  `avg_time_on_page` int(11) NOT NULL DEFAULT 0,
  `bounce_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `social_shares` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_blog_article_stats_article_date_unique` (`article_id`, `date`),
  KEY `epic_blog_article_stats_article_id_index` (`article_id`),
  KEY `epic_blog_article_stats_date_index` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create blog_referral_tracking table for detailed referral tracking
CREATE TABLE `epic_blog_referral_tracking` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `referrer_id` bigint(20) UNSIGNED NULL,
  `visit_id` bigint(20) UNSIGNED NULL,
  `conversion_type` enum('registration','purchase','subscription') NOT NULL,
  `conversion_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `utm_source` varchar(100) NULL,
  `utm_medium` varchar(100) NULL,
  `utm_campaign` varchar(100) NULL,
  `utm_content` varchar(100) NULL,
  `utm_term` varchar(100) NULL,
  `ip_address` varchar(45) NULL,
  `user_agent` text NULL,
  `referrer_url` varchar(500) NULL,
  `landing_page_url` varchar(500) NULL,
  `converted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_blog_referral_tracking_article_id_index` (`article_id`),
  KEY `epic_blog_referral_tracking_user_id_index` (`user_id`),
  KEY `epic_blog_referral_tracking_referrer_id_index` (`referrer_id`),
  KEY `epic_blog_referral_tracking_visit_id_index` (`visit_id`),
  KEY `epic_blog_referral_tracking_conversion_type_index` (`conversion_type`),
  KEY `epic_blog_referral_tracking_converted_at_index` (`converted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create blog_social_shares table for social media tracking
CREATE TABLE `epic_blog_social_shares` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `platform` enum('facebook','twitter','linkedin','whatsapp','telegram','email','copy_link') NOT NULL,
  `shared_by_user_id` bigint(20) UNSIGNED NULL,
  `ip_address` varchar(45) NULL,
  `user_agent` text NULL,
  `referrer_url` varchar(500) NULL,
  `shared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_blog_social_shares_article_id_index` (`article_id`),
  KEY `epic_blog_social_shares_platform_index` (`platform`),
  KEY `epic_blog_social_shares_shared_by_user_id_index` (`shared_by_user_id`),
  KEY `epic_blog_social_shares_shared_at_index` (`shared_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance on existing tables
ALTER TABLE `epic_articles` 
ADD INDEX `epic_articles_view_count_index` (`view_count`),
ADD INDEX `epic_articles_reading_time_index` (`reading_time`),
ADD INDEX `epic_articles_author_status_index` (`author_id`, `status`);

-- Create view for blog analytics summary
CREATE VIEW `epic_blog_analytics_summary` AS
SELECT 
    a.id,
    a.title,
    a.slug,
    a.status,
    a.view_count,
    a.published_at,
    a.created_at,
    u.name as author_name,
    c.name as category_name,
    COALESCE(SUM(bas.referrals_generated), 0) as total_referrals,
    COALESCE(SUM(bas.sales_generated), 0) as total_sales,
    COALESCE(SUM(bas.revenue_generated), 0) as total_revenue,
    COALESCE(COUNT(DISTINCT bss.id), 0) as total_social_shares,
    COALESCE(AVG(bas.avg_time_on_page), 0) as avg_time_on_page,
    COALESCE(AVG(bas.bounce_rate), 0) as avg_bounce_rate
FROM epic_articles a
LEFT JOIN epic_users u ON a.author_id = u.id
LEFT JOIN epic_categories c ON a.category_id = c.id
LEFT JOIN epic_blog_article_stats bas ON a.id = bas.article_id
LEFT JOIN epic_blog_social_shares bss ON a.id = bss.article_id
GROUP BY a.id, a.title, a.slug, a.status, a.view_count, a.published_at, a.created_at, u.name, c.name;

-- Create indexes for better query performance
CREATE INDEX idx_blog_stats_article_date ON epic_blog_article_stats(article_id, date);
CREATE INDEX idx_blog_referral_conversion ON epic_blog_referral_tracking(article_id, conversion_type, converted_at);
CREATE INDEX idx_blog_social_platform_date ON epic_blog_social_shares(platform, shared_at);

-- Add comments to tables for documentation
ALTER TABLE epic_blog_article_stats COMMENT = 'Daily statistics for blog articles including views, referrals, and revenue';
ALTER TABLE epic_blog_referral_tracking COMMENT = 'Detailed tracking of conversions generated from blog articles';
ALTER TABLE epic_blog_social_shares COMMENT = 'Tracking of social media shares for blog articles';

-- Insert some sample data for testing
INSERT INTO `epic_blog_article_stats` (`article_id`, `date`, `views`, `unique_views`, `referrals_generated`, `sales_generated`, `revenue_generated`) VALUES
(1, CURDATE(), 150, 120, 5, 2, 500000.00),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 200, 180, 8, 3, 750000.00);

INSERT INTO `epic_blog_social_shares` (`article_id`, `platform`, `shared_at`) VALUES
(1, 'facebook', NOW()),
(1, 'twitter', NOW()),
(1, 'whatsapp', NOW());