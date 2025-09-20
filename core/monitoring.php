<?php
/**
 * Registration Monitoring System
 * For EPIC Registration System
 */

if (!defined('EPIC_ROOT')) {
    die('Direct access not allowed');
}

/**
 * Initialize monitoring tables
 */
function epic_init_monitoring_tables() {
    $sql_metrics = "CREATE TABLE IF NOT EXISTS `epi_registration_metrics` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `date` date NOT NULL,
        `hour` tinyint(2) NOT NULL,
        `total_attempts` int(11) NOT NULL DEFAULT 0,
        `successful_registrations` int(11) NOT NULL DEFAULT 0,
        `failed_attempts` int(11) NOT NULL DEFAULT 0,
        `csrf_errors` int(11) NOT NULL DEFAULT 0,
        `validation_errors` int(11) NOT NULL DEFAULT 0,
        `rate_limit_hits` int(11) NOT NULL DEFAULT 0,
        `referral_errors` int(11) NOT NULL DEFAULT 0,
        `avg_processing_time` decimal(8,3) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_date_hour` (`date`, `hour`),
        KEY `idx_date` (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $sql_errors = "CREATE TABLE IF NOT EXISTS `epi_registration_errors` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `error_type` varchar(50) NOT NULL,
        `error_message` text,
        `error_data` json,
        `ip_address` varchar(45),
        `user_agent` varchar(500),
        `referrer` varchar(500),
        `form_data` json,
        `stack_trace` text,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_error_type` (`error_type`),
        KEY `idx_created_at` (`created_at`),
        KEY `idx_ip_address` (`ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $sql_performance = "CREATE TABLE IF NOT EXISTS `epi_performance_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `action` varchar(50) NOT NULL,
        `processing_time` decimal(8,3) NOT NULL,
        `memory_usage` int(11),
        `query_count` int(11),
        `user_id` int(11),
        `ip_address` varchar(45),
        `additional_data` json,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_action` (`action`),
        KEY `idx_created_at` (`created_at`),
        KEY `idx_processing_time` (`processing_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db()->query($sql_metrics);
    db()->query($sql_errors);
    db()->query($sql_performance);
}

/**
 * Record registration attempt
 */
function epic_record_registration_attempt($type = 'attempt', $processing_time = null, $error_data = null) {
    $date = date('Y-m-d');
    $hour = (int)date('H');
    
    // Get or create metrics record for current hour
    $sql = "INSERT INTO `epi_registration_metrics` 
            (`date`, `hour`, `total_attempts`, `successful_registrations`, `failed_attempts`, 
             `csrf_errors`, `validation_errors`, `rate_limit_hits`, `referral_errors`, `avg_processing_time`) 
            VALUES (?, ?, 1, 0, 0, 0, 0, 0, 0, ?)
            ON DUPLICATE KEY UPDATE 
            `total_attempts` = `total_attempts` + 1,
            `updated_at` = CURRENT_TIMESTAMP";
    
    $params = [$date, $hour, $processing_time];
    
    // Update specific counters based on type
    switch ($type) {
        case 'success':
            $sql = "INSERT INTO `epi_registration_metrics` 
                    (`date`, `hour`, `total_attempts`, `successful_registrations`, `avg_processing_time`) 
                    VALUES (?, ?, 1, 1, ?)
                    ON DUPLICATE KEY UPDATE 
                    `total_attempts` = `total_attempts` + 1,
                    `successful_registrations` = `successful_registrations` + 1,
                    `avg_processing_time` = ((`avg_processing_time` * (`total_attempts` - 1)) + ?) / `total_attempts`,
                    `updated_at` = CURRENT_TIMESTAMP";
            $params = [$date, $hour, $processing_time, $processing_time];
            break;
            
        case 'csrf_error':
            $sql = "INSERT INTO `epi_registration_metrics` 
                    (`date`, `hour`, `total_attempts`, `failed_attempts`, `csrf_errors`) 
                    VALUES (?, ?, 1, 1, 1)
                    ON DUPLICATE KEY UPDATE 
                    `total_attempts` = `total_attempts` + 1,
                    `failed_attempts` = `failed_attempts` + 1,
                    `csrf_errors` = `csrf_errors` + 1,
                    `updated_at` = CURRENT_TIMESTAMP";
            $params = [$date, $hour];
            break;
            
        case 'validation_error':
            $sql = "INSERT INTO `epi_registration_metrics` 
                    (`date`, `hour`, `total_attempts`, `failed_attempts`, `validation_errors`) 
                    VALUES (?, ?, 1, 1, 1)
                    ON DUPLICATE KEY UPDATE 
                    `total_attempts` = `total_attempts` + 1,
                    `failed_attempts` = `failed_attempts` + 1,
                    `validation_errors` = `validation_errors` + 1,
                    `updated_at` = CURRENT_TIMESTAMP";
            $params = [$date, $hour];
            break;
            
        case 'rate_limit':
            $sql = "INSERT INTO `epi_registration_metrics` 
                    (`date`, `hour`, `total_attempts`, `failed_attempts`, `rate_limit_hits`) 
                    VALUES (?, ?, 1, 1, 1)
                    ON DUPLICATE KEY UPDATE 
                    `total_attempts` = `total_attempts` + 1,
                    `failed_attempts` = `failed_attempts` + 1,
                    `rate_limit_hits` = `rate_limit_hits` + 1,
                    `updated_at` = CURRENT_TIMESTAMP";
            $params = [$date, $hour];
            break;
            
        case 'referral_error':
            $sql = "INSERT INTO `epi_registration_metrics` 
                    (`date`, `hour`, `total_attempts`, `failed_attempts`, `referral_errors`) 
                    VALUES (?, ?, 1, 1, 1)
                    ON DUPLICATE KEY UPDATE 
                    `total_attempts` = `total_attempts` + 1,
                    `failed_attempts` = `failed_attempts` + 1,
                    `referral_errors` = `referral_errors` + 1,
                    `updated_at` = CURRENT_TIMESTAMP";
            $params = [$date, $hour];
            break;
            
        default: // general failure
            $sql = "INSERT INTO `epi_registration_metrics` 
                    (`date`, `hour`, `total_attempts`, `failed_attempts`) 
                    VALUES (?, ?, 1, 1)
                    ON DUPLICATE KEY UPDATE 
                    `total_attempts` = `total_attempts` + 1,
                    `failed_attempts` = `failed_attempts` + 1,
                    `updated_at` = CURRENT_TIMESTAMP";
            $params = [$date, $hour];
            break;
    }
    
    try {
        db()->query($sql, $params);
    } catch (Exception $e) {
        error_log("Failed to record registration metrics: " . $e->getMessage());
    }
}

/**
 * Log registration error with details
 */
function epic_log_registration_error($error_type, $error_message, $error_data = null, $stack_trace = null) {
    $form_data = $_POST;
    
    // Remove sensitive data from form data
    if (isset($form_data['password'])) {
        $form_data['password'] = '[REDACTED]';
    }
    if (isset($form_data['confirm_password'])) {
        $form_data['confirm_password'] = '[REDACTED]';
    }
    
    $sql = "INSERT INTO `epi_registration_errors` 
            (`error_type`, `error_message`, `error_data`, `ip_address`, `user_agent`, `referrer`, `form_data`, `stack_trace`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $error_type,
        $error_message,
        json_encode($error_data),
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_REFERER'] ?? null,
        json_encode($form_data),
        $stack_trace
    ];
    
    try {
        db()->query($sql, $params);
        
        // Also record in metrics
        epic_record_registration_attempt($error_type);
    } catch (Exception $e) {
        error_log("Failed to log registration error: " . $e->getMessage());
    }
}

/**
 * Log performance metrics
 */
function epic_log_performance($action, $processing_time, $memory_usage = null, $query_count = null, $user_id = null, $additional_data = null) {
    $sql = "INSERT INTO `epi_performance_logs` 
            (`action`, `processing_time`, `memory_usage`, `query_count`, `user_id`, `ip_address`, `additional_data`) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $action,
        $processing_time,
        $memory_usage ?: memory_get_peak_usage(true),
        $query_count,
        $user_id,
        $_SERVER['REMOTE_ADDR'] ?? null,
        json_encode($additional_data)
    ];
    
    try {
        db()->query($sql, $params);
    } catch (Exception $e) {
        error_log("Failed to log performance metrics: " . $e->getMessage());
    }
}

/**
 * Get registration success rate for a date range
 */
if (!function_exists('epic_get_registration_success_rate')) {
function epic_get_registration_success_rate($start_date = null, $end_date = null) {
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-7 days'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d');
    }
    
    $sql = "SELECT 
                DATE(`date`) as date,
                SUM(`total_attempts`) as total_attempts,
                SUM(`successful_registrations`) as successful_registrations,
                SUM(`failed_attempts`) as failed_attempts,
                SUM(`csrf_errors`) as csrf_errors,
                SUM(`validation_errors`) as validation_errors,
                SUM(`rate_limit_hits`) as rate_limit_hits,
                SUM(`referral_errors`) as referral_errors,
                AVG(`avg_processing_time`) as avg_processing_time,
                ROUND((SUM(`successful_registrations`) / SUM(`total_attempts`) * 100), 2) as success_rate
            FROM `epi_registration_metrics` 
            WHERE `date` BETWEEN ? AND ?
            GROUP BY DATE(`date`)
            ORDER BY `date` DESC";
    
    try {
        return db()->select($sql, [$start_date, $end_date]);
    } catch (Exception $e) {
        error_log("Failed to get registration success rate: " . $e->getMessage());
        return [];
    }
}
}

/**
 * Get error patterns and trends
 */
function epic_get_error_patterns($start_date = null, $end_date = null, $limit = 50) {
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-7 days'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d 23:59:59');
    }
    
    $sql = "SELECT 
                `error_type`,
                COUNT(*) as error_count,
                COUNT(DISTINCT `ip_address`) as unique_ips,
                MAX(`created_at`) as last_occurrence,
                GROUP_CONCAT(DISTINCT `error_message` SEPARATOR '; ') as sample_messages
            FROM `epi_registration_errors` 
            WHERE `created_at` BETWEEN ? AND ?
            GROUP BY `error_type`
            ORDER BY `error_count` DESC
            LIMIT ?";
    
    try {
        return db()->select($sql, [$start_date, $end_date]);
    } catch (Exception $e) {
        error_log("Failed to get error patterns: " . $e->getMessage());
        return [];
    }
}

/**
 * Get performance metrics
 */
function epic_get_performance_metrics($action = null, $start_date = null, $end_date = null) {
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-7 days'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d 23:59:59');
    }
    
    $where_clause = "`created_at` BETWEEN ? AND ?";
    $params = [$start_date, $end_date];
    
    if ($action) {
        $where_clause .= " AND `action` = ?";
        $params[] = $action;
    }
    
    $sql = "SELECT 
                `action`,
                COUNT(*) as total_requests,
                AVG(`processing_time`) as avg_processing_time,
                MIN(`processing_time`) as min_processing_time,
                MAX(`processing_time`) as max_processing_time,
                AVG(`memory_usage`) as avg_memory_usage,
                MAX(`memory_usage`) as max_memory_usage
            FROM `epi_performance_logs` 
            WHERE {$where_clause}
            GROUP BY `action`
            ORDER BY `avg_processing_time` DESC";
    
    try {
        return db()->select($sql, $params);
    } catch (Exception $e) {
        error_log("Failed to get performance metrics: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate monitoring dashboard data
 */
function epic_get_monitoring_dashboard($days = 7) {
    $start_date = date('Y-m-d', strtotime("-{$days} days"));
    $end_date = date('Y-m-d');
    
    return [
        'success_rate' => epic_get_registration_success_rate($start_date, $end_date),
        'error_patterns' => epic_get_error_patterns($start_date, $end_date),
        'performance' => epic_get_performance_metrics(null, $start_date, $end_date),
        'summary' => epic_get_registration_summary($start_date, $end_date)
    ];
}

/**
 * Get registration summary
 */
function epic_get_registration_summary($start_date, $end_date) {
    $sql = "SELECT 
                SUM(`total_attempts`) as total_attempts,
                SUM(`successful_registrations`) as successful_registrations,
                SUM(`failed_attempts`) as failed_attempts,
                ROUND((SUM(`successful_registrations`) / SUM(`total_attempts`) * 100), 2) as overall_success_rate,
                AVG(`avg_processing_time`) as avg_processing_time
            FROM `epi_registration_metrics` 
            WHERE `date` BETWEEN ? AND ?";
    
    try {
        return db()->selectOne($sql, [$start_date, $end_date]);
    } catch (Exception $e) {
        error_log("Failed to get registration summary: " . $e->getMessage());
        return null;
    }
}

/**
 * Clean old monitoring data
 */
function epic_cleanup_monitoring_data($days_to_keep = 90) {
    $cutoff_date = date('Y-m-d', strtotime("-{$days_to_keep} days"));
    
    try {
        // Clean old metrics (keep aggregated daily data longer)
        $sql1 = "DELETE FROM `epi_registration_metrics` WHERE `date` < ?";
        db()->query($sql1, [date('Y-m-d', strtotime("-{$days_to_keep} days"))]);
        
        // Clean old error logs (keep for shorter period)
        $sql2 = "DELETE FROM `epi_registration_errors` WHERE `created_at` < ?";
        db()->query($sql2, [date('Y-m-d', strtotime('-30 days'))]);
        
        // Clean old performance logs
        $sql3 = "DELETE FROM `epi_performance_logs` WHERE `created_at` < ?";
        db()->query($sql3, [date('Y-m-d', strtotime('-30 days'))]);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to cleanup monitoring data: " . $e->getMessage());
        return false;
    }
}

/**
 * Record registration metrics
 */
function epic_record_registration_metrics($type, $processing_time = null) {
    $date = date('Y-m-d');
    $hour = date('H');
    
    try {
        // Get or create metrics record for this hour
        $existing = db()->selectOne(
            "SELECT * FROM `epi_registration_metrics` WHERE `date` = ? AND `hour` = ?",
            [$date, $hour]
        );
        
        if ($existing) {
            // Update existing record
            $sql = "UPDATE `epi_registration_metrics` SET 
                `total_attempts` = `total_attempts` + 1,
                " . ($type === 'success' ? "`successful_registrations` = `successful_registrations` + 1," : "`failed_attempts` = `failed_attempts` + 1,") . "
                `avg_processing_time` = " . ($processing_time ? "?" : "`avg_processing_time`") . ",
                `updated_at` = NOW()
                WHERE `id` = ?";
            
            $params = $processing_time ? [$processing_time, $existing['id']] : [$existing['id']];
            db()->query($sql, $params);
        } else {
            // Create new record
            $sql = "INSERT INTO `epi_registration_metrics` 
                (`date`, `hour`, `total_attempts`, `successful_registrations`, `failed_attempts`, `avg_processing_time`) 
                VALUES (?, ?, 1, ?, ?, ?)";
            
            $params = [
                $date, 
                $hour, 
                $type === 'success' ? 1 : 0,
                $type === 'success' ? 0 : 1,
                $processing_time
            ];
            db()->query($sql, $params);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to record registration metrics: " . $e->getMessage());
        return false;
    }
}

/**
 * Record registration error
 */
function epic_record_registration_error($error_type, $error_message, $error_data = null) {
    try {
        $sql = "INSERT INTO `epi_registration_errors` 
            (`error_type`, `error_message`, `error_data`, `ip_address`, `user_agent`) 
            VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $error_type,
            $error_message,
            $error_data ? json_encode($error_data) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        db()->query($sql, $params);
        return true;
    } catch (Exception $e) {
        error_log("Failed to record registration error: " . $e->getMessage());
        return false;
    }
}

/**
 * Record performance metrics
 */
function epic_record_performance($action, $processing_time, $memory_usage = null, $query_count = null) {
    try {
        $sql = "INSERT INTO `epi_performance_logs` 
            (`action`, `processing_time`, `memory_usage`, `query_count`, `ip_address`) 
            VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $action,
            $processing_time,
            $memory_usage,
            $query_count,
            $_SERVER['REMOTE_ADDR'] ?? null
        ];
        
        db()->query($sql, $params);
        return true;
    } catch (Exception $e) {
        error_log("Failed to record performance: " . $e->getMessage());
        return false;
    }
}

/**
 * Initialize monitoring system
 */
function epic_init_monitoring() {
    epic_init_monitoring_tables();
    
    // Set up cleanup cron job (if not already set)
    if (!epic_setting('monitoring_cleanup_scheduled', '0')) {
        // This would typically be set up as a cron job
        // For now, we'll just mark it as initialized
        epic_set_setting('monitoring_cleanup_scheduled', '1');
    }
}

// Initialize monitoring on first load
if (!epic_setting('monitoring_initialized', '0')) {
    epic_init_monitoring();
    epic_set_setting('monitoring_initialized', '1');
}