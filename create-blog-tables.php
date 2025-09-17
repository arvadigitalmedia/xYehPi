<?php
/**
 * Create Blog Tables Script
 */

require_once __DIR__ . '/bootstrap.php';

echo "Creating Blog Tables...\n";

// Create blog_article_stats table
try {
    $sql = "CREATE TABLE `epic_blog_article_stats` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db()->query($sql);
    echo "✓ Created epic_blog_article_stats table\n";
} catch(Exception $e) {
    echo "✗ Error creating epic_blog_article_stats: " . $e->getMessage() . "\n";
}

// Create blog_referral_tracking table
try {
    $sql = "CREATE TABLE `epic_blog_referral_tracking` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db()->query($sql);
    echo "✓ Created epic_blog_referral_tracking table\n";
} catch(Exception $e) {
    echo "✗ Error creating epic_blog_referral_tracking: " . $e->getMessage() . "\n";
}

// Create blog_social_shares table
try {
    $sql = "CREATE TABLE `epic_blog_social_shares` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db()->query($sql);
    echo "✓ Created epic_blog_social_shares table\n";
} catch(Exception $e) {
    echo "✗ Error creating epic_blog_social_shares: " . $e->getMessage() . "\n";
}

// Add indexes to epic_articles table
try {
    db()->query("ALTER TABLE `epic_articles` ADD INDEX `epic_articles_view_count_index` (`view_count`)");
    echo "✓ Added view_count index to epic_articles\n";
} catch(Exception $e) {
    echo "✗ Error adding view_count index: " . $e->getMessage() . "\n";
}

try {
    db()->query("ALTER TABLE `epic_articles` ADD INDEX `epic_articles_reading_time_index` (`reading_time`)");
    echo "✓ Added reading_time index to epic_articles\n";
} catch(Exception $e) {
    echo "✗ Error adding reading_time index: " . $e->getMessage() . "\n";
}

try {
    db()->query("ALTER TABLE `epic_articles` ADD INDEX `epic_articles_author_status_index` (`author_id`, `status`)");
    echo "✓ Added author_status index to epic_articles\n";
} catch(Exception $e) {
    echo "✗ Error adding author_status index: " . $e->getMessage() . "\n";
}

// Add fields to epic_landing_visits table
try {
    db()->query("ALTER TABLE `epic_landing_visits` ADD COLUMN `article_id` bigint(20) UNSIGNED NULL AFTER `template_name`");
    echo "✓ Added article_id column to epic_landing_visits\n";
} catch(Exception $e) {
    echo "✗ Error adding article_id column: " . $e->getMessage() . "\n";
}

try {
    db()->query("ALTER TABLE `epic_landing_visits` ADD COLUMN `article_slug` varchar(200) NULL AFTER `article_id`");
    echo "✓ Added article_slug column to epic_landing_visits\n";
} catch(Exception $e) {
    echo "✗ Error adding article_slug column: " . $e->getMessage() . "\n";
}

// Create view for analytics
try {
    $sql = "CREATE VIEW `epic_blog_analytics_summary` AS
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
    GROUP BY a.id, a.title, a.slug, a.status, a.view_count, a.published_at, a.created_at, u.name, c.name";
    
    db()->query($sql);
    echo "✓ Created epic_blog_analytics_summary view\n";
} catch(Exception $e) {
    echo "✗ Error creating view: " . $e->getMessage() . "\n";
}

// Insert sample data
try {
    db()->query("INSERT INTO `epic_blog_article_stats` (`article_id`, `date`, `views`, `unique_views`, `referrals_generated`, `sales_generated`, `revenue_generated`) VALUES (1, CURDATE(), 150, 120, 5, 2, 500000.00)");
    echo "✓ Inserted sample article stats\n";
} catch(Exception $e) {
    echo "✗ Error inserting sample data: " . $e->getMessage() . "\n";
}

echo "\nTesting tables...\n";
$tables = ['epic_blog_article_stats', 'epic_blog_referral_tracking', 'epic_blog_social_shares'];
foreach($tables as $table) {
    try {
        $exists = db()->selectValue("SHOW TABLES LIKE '{$table}'");
        echo $exists ? "✓ {$table} exists\n" : "✗ {$table} missing\n";
    } catch(Exception $e) {
        echo "✗ {$table} error: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Blog tables creation completed!\n";
echo "\nNext steps:\n";
echo "1. Visit: http://localhost/epichub/admin/blog\n";
echo "2. Create test articles\n";
echo "3. Test blog at: http://localhost/epichub/blog\n";
?>