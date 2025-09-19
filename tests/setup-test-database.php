<?php
/**
 * Setup Test Database
 * Creates required tables for testing
 */

require_once dirname(__DIR__) . '/bootstrap.php';

echo "=== Setting up Test Database ===\n\n";

try {
    $pdo = db()->getConnection();
    
    // Create rate limits table
    echo "Creating rate limits table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `epic_epi_rate_limits` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `action` varchar(50) NOT NULL,
        `identifier` varchar(255) NOT NULL,
        `attempts` int(11) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_action_identifier` (`action`, `identifier`),
        KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✓ Rate limits table created\n";
    
    // Create monitoring tables
    echo "Creating monitoring tables...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `epic_epi_registration_metrics` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `date` date NOT NULL,
        `total_attempts` int(11) NOT NULL DEFAULT 0,
        `successful_registrations` int(11) NOT NULL DEFAULT 0,
        `failed_registrations` int(11) NOT NULL DEFAULT 0,
        `csrf_failures` int(11) NOT NULL DEFAULT 0,
        `rate_limit_hits` int(11) NOT NULL DEFAULT 0,
        `validation_errors` int(11) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_date` (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✓ Registration metrics table created\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `epic_epi_error_logs` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `error_type` varchar(50) NOT NULL,
        `error_code` varchar(20) DEFAULT NULL,
        `error_message` text NOT NULL,
        `context` json DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_error_type` (`error_type`),
        KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✓ Error logs table created\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `epic_epi_performance_logs` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `operation` varchar(100) NOT NULL,
        `execution_time` decimal(10,4) NOT NULL,
        `memory_usage` bigint(20) DEFAULT NULL,
        `context` json DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_operation` (`operation`),
        KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✓ Performance logs table created\n";
    
    // Check if users table exists
    echo "Checking users table...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'epic_users'");
    if ($stmt->rowCount() == 0) {
        echo "Creating users table...\n";
        $sql = "CREATE TABLE IF NOT EXISTS `epic_users` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `status` enum('pending','free','epic','epis','suspended','banned') NOT NULL DEFAULT 'pending',
            `referral_code` varchar(20) DEFAULT NULL,
            `referred_by` bigint(20) unsigned DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`),
            UNIQUE KEY `referral_code` (`referral_code`),
            KEY `idx_status` (`status`),
            KEY `idx_referred_by` (`referred_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        echo "✓ Users table created\n";
    } else {
        echo "✓ Users table already exists\n";
    }
    
    echo "\n=== Database setup completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "✗ Error setting up database: " . $e->getMessage() . "\n";
    exit(1);
}
?>