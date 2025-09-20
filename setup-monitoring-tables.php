<?php
/**
 * Setup Monitoring Tables for EPIS Counter System
 * Membuat tabel yang dibutuhkan untuk sistem monitoring EPIS
 */

require_once 'bootstrap.php';

echo "=== SETUP MONITORING TABLES ===\n";

try {
    $db = db();
    
    // 1. Create epic_system_alerts table
    echo "1. Creating epic_system_alerts table...\n";
    
    $sql_alerts = "CREATE TABLE IF NOT EXISTS `epic_system_alerts` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `alert_type` varchar(50) NOT NULL,
        `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
        `title` varchar(255) NOT NULL,
        `message` text NOT NULL,
        `metadata` json NULL,
        `status` enum('active','acknowledged','resolved') NOT NULL DEFAULT 'active',
        `acknowledged_by` bigint(20) UNSIGNED NULL,
        `acknowledged_at` timestamp NULL DEFAULT NULL,
        `resolved_by` bigint(20) UNSIGNED NULL,
        `resolved_at` timestamp NULL DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `epic_system_alerts_alert_type_index` (`alert_type`),
        KEY `epic_system_alerts_severity_index` (`severity`),
        KEY `epic_system_alerts_status_index` (`status`),
        KEY `epic_system_alerts_created_at_index` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($sql_alerts);
    echo "   ✓ epic_system_alerts table created\n";
    
    // 2. Create epic_monitoring_logs table
    echo "2. Creating epic_monitoring_logs table...\n";
    
    $sql_logs = "CREATE TABLE IF NOT EXISTS `epic_monitoring_logs` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `monitor_type` varchar(50) NOT NULL,
        `check_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `status` enum('success','warning','error') NOT NULL DEFAULT 'success',
        `summary` json NULL,
        `details` json NULL,
        `execution_time_ms` int(11) NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `epic_monitoring_logs_monitor_type_index` (`monitor_type`),
        KEY `epic_monitoring_logs_status_index` (`status`),
        KEY `epic_monitoring_logs_check_timestamp_index` (`check_timestamp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($sql_logs);
    echo "   ✓ epic_monitoring_logs table created\n";
    
    // 3. Create epic_counter_history table for tracking changes
    echo "3. Creating epic_counter_history table...\n";
    
    $sql_history = "CREATE TABLE IF NOT EXISTS `epic_counter_history` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `epis_id` bigint(20) UNSIGNED NOT NULL,
        `old_count` int(11) NOT NULL,
        `new_count` int(11) NOT NULL,
        `change_reason` varchar(100) NOT NULL,
        `changed_by` varchar(50) NOT NULL DEFAULT 'system',
        `metadata` json NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `epic_counter_history_epis_id_index` (`epis_id`),
        KEY `epic_counter_history_created_at_index` (`created_at`),
        CONSTRAINT `epic_counter_history_epis_id_foreign` 
            FOREIGN KEY (`epis_id`) REFERENCES `epic_epis_accounts` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($sql_history);
    echo "   ✓ epic_counter_history table created\n";
    
    // 4. Test insert sample data
    echo "4. Testing table functionality...\n";
    
    // Test alert
    $alert_id = $db->insert('epic_system_alerts', [
        'alert_type' => 'test',
        'severity' => 'low',
        'title' => 'Test Alert',
        'message' => 'This is a test alert for monitoring system setup',
        'metadata' => json_encode(['test' => true]),
        'status' => 'resolved',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($alert_id) {
        echo "   ✓ Test alert created (ID: {$alert_id})\n";
        
        // Clean up test data
        $db->delete('epic_system_alerts', 'id = ?', [$alert_id]);
        echo "   ✓ Test alert cleaned up\n";
    }
    
    // Test monitoring log
    $log_id = $db->insert('epic_monitoring_logs', [
        'monitor_type' => 'test',
        'status' => 'success',
        'summary' => json_encode(['test' => 'passed']),
        'execution_time_ms' => 100,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($log_id) {
        echo "   ✓ Test monitoring log created (ID: {$log_id})\n";
        
        // Clean up test data
        $db->delete('epic_monitoring_logs', 'id = ?', [$log_id]);
        echo "   ✓ Test monitoring log cleaned up\n";
    }
    
    echo "\n=== SETUP COMPLETED SUCCESSFULLY ===\n";
    echo "Monitoring tables are ready for use.\n";
    echo "\nNext steps:\n";
    echo "1. Setup cron job: */15 * * * * php " . __DIR__ . "/core/epis-counter-monitor.php --schedule\n";
    echo "2. Test monitoring: php " . __DIR__ . "/core/epis-counter-monitor.php\n";
    echo "3. Check alerts in admin dashboard\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Setup failed. Please check database connection and permissions.\n";
    exit(1);
}
?>