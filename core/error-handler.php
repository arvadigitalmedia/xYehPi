<?php
/**
 * Enhanced Error Handling and Logging System
 * For EPIC Registration System
 */

if (!defined('EPIC_ROOT')) {
    die('Direct access not allowed');
}

/**
 * Enhanced error logging with context and correlation ID
 */
function epic_log_error($level, $message, $context = [], $correlation_id = null) {
    if (!$correlation_id) {
        $correlation_id = epic_generate_correlation_id();
    }
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => strtoupper($level),
        'correlation_id' => $correlation_id,
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
    ];
    
    // Mask sensitive data
    $log_entry = epic_mask_sensitive_data($log_entry);
    
    // Log to file
    $log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    error_log($log_line, 3, EPIC_ROOT . '/logs/epic-errors.log');
    
    // Log to database for critical errors
    if (in_array($level, ['error', 'critical'])) {
        epic_log_error_to_db($log_entry);
    }
    
    return $correlation_id;
}

/**
 * Generate unique correlation ID for request tracking
 */
function epic_generate_correlation_id() {
    return uniqid('epic_', true) . '_' . substr(md5(microtime(true)), 0, 8);
}

/**
 * Mask sensitive data in logs
 */
function epic_mask_sensitive_data($data) {
    $sensitive_keys = ['password', 'token', 'secret', 'key', 'auth', 'credential'];
    
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $key_lower = strtolower($key);
                foreach ($sensitive_keys as $sensitive) {
                    if (strpos($key_lower, $sensitive) !== false) {
                        $data[$key] = '***MASKED***';
                        break;
                    }
                }
            }
            
            if (is_array($value) || is_object($value)) {
                $data[$key] = epic_mask_sensitive_data($value);
            }
        }
    }
    
    return $data;
}

/**
 * Log error to database for monitoring
 */
function epic_log_error_to_db($log_entry) {
    try {
        // Create error logs table if not exists
        epic_create_error_logs_table();
        
        db()->insert('epi_error_logs', [
            'correlation_id' => $log_entry['correlation_id'],
            'level' => $log_entry['level'],
            'message' => $log_entry['message'],
            'context' => json_encode($log_entry['context']),
            'ip_address' => $log_entry['ip'],
            'user_agent' => substr($log_entry['user_agent'], 0, 500),
            'request_uri' => $log_entry['request_uri'],
            'created_at' => $log_entry['timestamp']
        ]);
    } catch (Exception $e) {
        // Fallback to file logging if DB fails
        error_log("Failed to log to database: " . $e->getMessage());
    }
}

/**
 * Create error logs table
 */
function epic_create_error_logs_table() {
    $sql = "CREATE TABLE IF NOT EXISTS `epi_error_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `correlation_id` varchar(100) NOT NULL,
        `level` varchar(20) NOT NULL,
        `message` text NOT NULL,
        `context` longtext,
        `ip_address` varchar(45),
        `user_agent` varchar(500),
        `request_uri` varchar(500),
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_correlation_id` (`correlation_id`),
        KEY `idx_level` (`level`),
        KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db()->query($sql);
}

/**
 * Enhanced registration error handler
 */
function epic_handle_registration_error($error, $user_data = [], $step = 'unknown') {
    $correlation_id = epic_generate_correlation_id();
    
    $context = [
        'step' => $step,
        'user_email' => $user_data['email'] ?? 'unknown',
        'referral_code' => $user_data['referral_code'] ?? null,
        'epis_supervisor_id' => $user_data['epis_supervisor_id'] ?? null,
        'error_type' => get_class($error),
        'stack_trace' => $error->getTraceAsString()
    ];
    
    epic_log_error('error', 'Registration failed: ' . $error->getMessage(), $context, $correlation_id);
    
    // Update registration metrics
    epic_update_registration_metrics('error');
    
    // Log to monitoring system
    if (file_exists(EPIC_ROOT . '/core/monitoring.php')) {
        require_once EPIC_ROOT . '/core/monitoring.php';
        epic_log_registration_error('general_error', $error->getMessage(), $context, $error->getTraceAsString());
    }
    
    // Return user-friendly error message with correlation ID
    return [
        'success' => false,
        'message' => 'Registrasi gagal. Silakan coba lagi atau hubungi support.',
        'correlation_id' => $correlation_id,
        'technical_message' => $error->getMessage()
    ];
}

/**
 * Registration success logger
 */
function epic_log_registration_success($user_id, $user_data, $processing_time = null) {
    $correlation_id = epic_generate_correlation_id();
    
    $context = [
        'user_id' => $user_id,
        'user_email' => $user_data['email'] ?? 'unknown',
        'referral_code' => $user_data['referral_code'] ?? null,
        'epis_supervisor_id' => $user_data['epis_supervisor_id'] ?? null,
        'processing_time_ms' => $processing_time,
        'registration_source' => $user_data['registration_source'] ?? 'web'
    ];
    
    epic_log_error('info', 'Registration completed successfully', $context, $correlation_id);
    
    // Update registration metrics
    epic_update_registration_metrics('success');
    
    // Log to monitoring system
    if (file_exists(EPIC_ROOT . '/core/monitoring.php')) {
        require_once EPIC_ROOT . '/core/monitoring.php';
        epic_record_registration_attempt('success', $processing_time);
        epic_log_performance('registration', $processing_time, null, null, $user_id, $context);
    }
    
    return $correlation_id;
}

/**
 * Update registration metrics for monitoring
 */
function epic_update_registration_metrics($type) {
    try {
        $today = date('Y-m-d');
        $metric_key = "registration_{$type}_{$today}";
        
        // Use Redis if available, otherwise database
        if (class_exists('Redis') && epic_setting('redis_enabled', '0') === '1') {
            $redis = new Redis();
            $redis->connect(epic_setting('redis_host', '127.0.0.1'), epic_setting('redis_port', 6379));
            $redis->incr($metric_key);
            $redis->expire($metric_key, 86400 * 7); // Keep for 7 days
        } else {
            // Database fallback
            epic_create_metrics_table();
            
            $existing = db()->select('epi_metrics', 'value', 'metric_key = ? AND date = ?', [$metric_key, $today]);
            
            if ($existing) {
                db()->update('epi_metrics', [
                    'value' => $existing['value'] + 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'metric_key = ? AND date = ?', [$metric_key, $today]);
            } else {
                db()->insert('epi_metrics', [
                    'metric_key' => $metric_key,
                    'value' => 1,
                    'date' => $today,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    } catch (Exception $e) {
        error_log("Failed to update metrics: " . $e->getMessage());
    }
}

/**
 * Create metrics table
 */
function epic_create_metrics_table() {
    $sql = "CREATE TABLE IF NOT EXISTS `epi_metrics` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `metric_key` varchar(100) NOT NULL,
        `value` int(11) NOT NULL DEFAULT 0,
        `date` date NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_metric_date` (`metric_key`, `date`),
        KEY `idx_date` (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db()->query($sql);
}

/**
 * Get registration success rate for monitoring
 */
function epic_get_registration_success_rate($days = 7) {
    try {
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $end_date = date('Y-m-d');
        
        $success_count = 0;
        $error_count = 0;
        
        if (class_exists('Redis') && epic_setting('redis_enabled', '0') === '1') {
            $redis = new Redis();
            $redis->connect(epic_setting('redis_host', '127.0.0.1'), epic_setting('redis_port', 6379));
            
            for ($i = 0; $i < $days; $i++) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $success_count += (int)$redis->get("registration_success_{$date}") ?: 0;
                $error_count += (int)$redis->get("registration_error_{$date}") ?: 0;
            }
        } else {
            // Database fallback
            $success_result = db()->query("SELECT SUM(value) as total FROM epi_metrics WHERE metric_key LIKE 'registration_success_%' AND date BETWEEN ? AND ?", [$start_date, $end_date]);
            $error_result = db()->query("SELECT SUM(value) as total FROM epi_metrics WHERE metric_key LIKE 'registration_error_%' AND date BETWEEN ? AND ?", [$start_date, $end_date]);
            
            $success_count = $success_result[0]['total'] ?? 0;
            $error_count = $error_result[0]['total'] ?? 0;
        }
        
        $total = $success_count + $error_count;
        $success_rate = $total > 0 ? ($success_count / $total) * 100 : 0;
        
        return [
            'success_count' => $success_count,
            'error_count' => $error_count,
            'total_attempts' => $total,
            'success_rate' => round($success_rate, 2)
        ];
    } catch (Exception $e) {
        error_log("Failed to get registration success rate: " . $e->getMessage());
        return null;
    }
}

/**
 * Create logs directory if not exists
 */
function epic_ensure_logs_directory() {
    $logs_dir = EPIC_ROOT . '/logs';
    if (!is_dir($logs_dir)) {
        mkdir($logs_dir, 0755, true);
    }
    
    // Create .htaccess to protect logs
    $htaccess_file = $logs_dir . '/.htaccess';
    if (!file_exists($htaccess_file)) {
        file_put_contents($htaccess_file, "Deny from all\n");
    }
}

// Ensure logs directory exists
epic_ensure_logs_directory();