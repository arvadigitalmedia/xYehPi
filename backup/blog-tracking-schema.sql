-- =====================================================
-- EPIC Hub Blog Tracking Enhancement
-- Additional fields and tables for blog referral tracking
-- =====================================================

-- Add blog tracking fields to epic_landing_visits table
ALTER TABLE `epic_landing_visits` 
ADD COLUMN `article_id` bigint(20) UNSIGNED NULL AFTER `template_name`,
ADD COLUMN `article_slug` varchar(200) NULL AFTER `article_id`,
ADD INDEX `epic_landing_visits_article_id_index` (`article_id`),
ADD INDEX `epic_landing_visits_article_slug_index` (`article_slug`);

-- Add foreign key constraint for article_id
ALTER TABLE `epic_landing_visits`
ADD CONSTRAINT `epic_landing_visits_article_id_foreign` 
FOREIGN KEY (`article_id`) REFERENCES `epic_articles` (`id`) ON DELETE SET NULL;

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
  `avg_time_on_page` int(11) NOT NULL DEFAULT 0, -- in seconds
  `bounce_rate` decimal(5,2) NOT NULL DEFAULT 0.00, -- percentage
  `social_shares` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_blog_article_stats_article_date_unique` (`article_id`, `date`),
  KEY `epic_blog_article_stats_article_id_index` (`article_id`),
  KEY `epic_blog_article_stats_date_index` (`date`),
  CONSTRAINT `epic_blog_article_stats_article_id_foreign` 
  FOREIGN KEY (`article_id`) REFERENCES `epic_articles` (`id`) ON DELETE CASCADE
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
  KEY `epic_blog_referral_tracking_converted_at_index` (`converted_at`),
  CONSTRAINT `epic_blog_referral_tracking_article_id_foreign` 
  FOREIGN KEY (`article_id`) REFERENCES `epic_articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_blog_referral_tracking_user_id_foreign` 
  FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_blog_referral_tracking_referrer_id_foreign` 
  FOREIGN KEY (`referrer_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `epic_blog_referral_tracking_visit_id_foreign` 
  FOREIGN KEY (`visit_id`) REFERENCES `epic_landing_visits` (`id`) ON DELETE SET NULL
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
  KEY `epic_blog_social_shares_shared_at_index` (`shared_at`),
  CONSTRAINT `epic_blog_social_shares_article_id_foreign` 
  FOREIGN KEY (`article_id`) REFERENCES `epic_articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_blog_social_shares_shared_by_user_id_foreign` 
  FOREIGN KEY (`shared_by_user_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
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

-- Insert sample data for testing (optional)
-- This will be commented out in production
/*
INSERT INTO `epic_blog_article_stats` (`article_id`, `date`, `views`, `unique_views`, `referrals_generated`, `sales_generated`, `revenue_generated`) VALUES
(1, CURDATE(), 150, 120, 5, 2, 500000.00),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 200, 180, 8, 3, 750000.00),
(2, CURDATE(), 300, 250, 12, 5, 1250000.00),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 180, 150, 6, 2, 500000.00);

INSERT INTO `epic_blog_social_shares` (`article_id`, `platform`, `shared_at`) VALUES
(1, 'facebook', NOW()),
(1, 'twitter', NOW()),
(1, 'whatsapp', NOW()),
(2, 'facebook', NOW()),
(2, 'linkedin', NOW());
*/

-- Create stored procedure for updating article stats
DELIMITER //

CREATE PROCEDURE UpdateArticleStats(
    IN p_article_id BIGINT,
    IN p_date DATE,
    IN p_views INT DEFAULT 1,
    IN p_unique_views INT DEFAULT 1,
    IN p_referrals INT DEFAULT 0,
    IN p_sales INT DEFAULT 0,
    IN p_revenue DECIMAL(15,2) DEFAULT 0.00,
    IN p_time_on_page INT DEFAULT 0,
    IN p_bounce_rate DECIMAL(5,2) DEFAULT 0.00,
    IN p_social_shares INT DEFAULT 0
)
BEGIN
    INSERT INTO epic_blog_article_stats (
        article_id, date, views, unique_views, referrals_generated, 
        sales_generated, revenue_generated, avg_time_on_page, 
        bounce_rate, social_shares
    ) VALUES (
        p_article_id, p_date, p_views, p_unique_views, p_referrals,
        p_sales, p_revenue, p_time_on_page, p_bounce_rate, p_social_shares
    )
    ON DUPLICATE KEY UPDATE
        views = views + p_views,
        unique_views = unique_views + p_unique_views,
        referrals_generated = referrals_generated + p_referrals,
        sales_generated = sales_generated + p_sales,
        revenue_generated = revenue_generated + p_revenue,
        avg_time_on_page = (avg_time_on_page + p_time_on_page) / 2,
        bounce_rate = (bounce_rate + p_bounce_rate) / 2,
        social_shares = social_shares + p_social_shares,
        updated_at = CURRENT_TIMESTAMP;
END //

DELIMITER ;

-- Create function to get article conversion rate
DELIMITER //

CREATE FUNCTION GetArticleConversionRate(p_article_id BIGINT)
RETURNS DECIMAL(5,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total_views INT DEFAULT 0;
    DECLARE total_conversions INT DEFAULT 0;
    DECLARE conversion_rate DECIMAL(5,2) DEFAULT 0.00;
    
    SELECT COALESCE(view_count, 0) INTO total_views 
    FROM epic_articles 
    WHERE id = p_article_id;
    
    SELECT COALESCE(COUNT(*), 0) INTO total_conversions 
    FROM epic_blog_referral_tracking 
    WHERE article_id = p_article_id;
    
    IF total_views > 0 THEN
        SET conversion_rate = (total_conversions / total_views) * 100;
    END IF;
    
    RETURN conversion_rate;
END //

DELIMITER ;

-- Add triggers for automatic tracking
DELIMITER //

-- Trigger to update article view count when stats are updated
CREATE TRIGGER update_article_view_count
AFTER INSERT ON epic_blog_article_stats
FOR EACH ROW
BEGIN
    UPDATE epic_articles 
    SET view_count = (
        SELECT COALESCE(SUM(views), 0) 
        FROM epic_blog_article_stats 
        WHERE article_id = NEW.article_id
    )
    WHERE id = NEW.article_id;
END //

-- Trigger to track referrals when new users register from blog
CREATE TRIGGER track_blog_referral_registration
AFTER INSERT ON epic_users
FOR EACH ROW
BEGIN
    DECLARE v_article_id BIGINT DEFAULT NULL;
    DECLARE v_referrer_id BIGINT DEFAULT NULL;
    
    -- Check if user came from a blog article (this would be set in session)
    -- This is a simplified version - in practice, you'd check session data
    
    IF v_article_id IS NOT NULL THEN
        INSERT INTO epic_blog_referral_tracking (
            article_id, user_id, referrer_id, conversion_type, conversion_value
        ) VALUES (
            v_article_id, NEW.id, v_referrer_id, 'registration', 0.00
        );
        
        -- Update daily stats
        CALL UpdateArticleStats(v_article_id, CURDATE(), 0, 0, 1, 0, 0.00, 0, 0.00, 0);
    END IF;
END //

DELIMITER ;

-- Create indexes for better query performance
CREATE INDEX idx_blog_stats_article_date ON epic_blog_article_stats(article_id, date);
CREATE INDEX idx_blog_referral_conversion ON epic_blog_referral_tracking(article_id, conversion_type, converted_at);
CREATE INDEX idx_blog_social_platform_date ON epic_blog_social_shares(platform, shared_at);

-- Add comments to tables for documentation
ALTER TABLE epic_blog_article_stats COMMENT = 'Daily statistics for blog articles including views, referrals, and revenue';
ALTER TABLE epic_blog_referral_tracking COMMENT = 'Detailed tracking of conversions generated from blog articles';
ALTER TABLE epic_blog_social_shares COMMENT = 'Tracking of social media shares for blog articles';

COMMIT;

-- =====================================================
-- USAGE EXAMPLES
-- =====================================================

/*
-- Example 1: Track a page view
CALL UpdateArticleStats(1, CURDATE(), 1, 1, 0, 0, 0.00, 120, 0.00, 0);

-- Example 2: Track a referral conversion
INSERT INTO epic_blog_referral_tracking (article_id, user_id, conversion_type, conversion_value)
VALUES (1, 123, 'purchase', 250000.00);

-- Example 3: Track a social share
INSERT INTO epic_blog_social_shares (article_id, platform, shared_by_user_id)
VALUES (1, 'facebook', 123);

-- Example 4: Get article performance summary
SELECT * FROM epic_blog_analytics_summary WHERE id = 1;

-- Example 5: Get conversion rate for an article
SELECT GetArticleConversionRate(1) as conversion_rate;

-- Example 6: Get top performing articles by referrals
SELECT title, total_referrals, total_revenue 
FROM epic_blog_analytics_summary 
WHERE status = 'published' 
ORDER BY total_referrals DESC 
LIMIT 10;

-- Example 7: Get monthly blog performance
SELECT 
    DATE_FORMAT(date, '%Y-%m') as month,
    SUM(views) as total_views,
    SUM(referrals_generated) as total_referrals,
    SUM(revenue_generated) as total_revenue
FROM epic_blog_article_stats 
WHERE date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(date, '%Y-%m')
ORDER BY month DESC;
*/