<?php
/**
 * EPIC Enhanced Error Logging System
 * Structured logging dengan monitoring dan alerting
 * 
 * @author TRAE AI Assistant
 * @version 1.0.0
 */

class EpicEnhancedLogger {
    private $log_dir;
    private $max_log_size = 10485760; // 10MB
    private $max_log_files = 5;
    private $request_id;
    
    public function __construct() {
        $this->log_dir = EPIC_ROOT . '/logs';
        $this->request_id = $this->generateRequestId();
        
        // Ensure log directory exists
        if (!is_dir($this->log_dir)) {
            mkdir($this->log_dir, 0755, true);
        }
        
        // Create .htaccess to protect logs
        $htaccess_file = $this->log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "Deny from all\n");
        }
    }
    
    /**
     * Log error dengan context dan structured format
     */
    public function logError($level, $message, $context = [], $exception = null) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => $this->request_id,
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $this->sanitizeContext($context),
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? 'cli',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI'
        ];
        
        // Add exception details if provided
        if ($exception instanceof Exception) {
            $log_entry['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $this->sanitizeStackTrace($exception->getTraceAsString())
            ];
        }
        
        // Add memory and performance metrics
        $log_entry['metrics'] = [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))
        ];
        
        $this->writeLog($level, $log_entry);
        
        // Send alerts for critical errors
        if (in_array($level, ['critical', 'emergency'])) {
            $this->sendAlert($log_entry);
        }
        
        // Store in database for monitoring dashboard
        $this->storeInDatabase($log_entry);
    }
    
    /**
     * Log security events
     */
    public function logSecurity($event_type, $message, $context = []) {
        $context['security_event'] = $event_type;
        $context['severity'] = 'high';
        
        $this->logError('warning', "[SECURITY] {$message}", $context);
        
        // Always send security alerts
        $this->sendSecurityAlert($event_type, $message, $context);
    }
    
    /**
     * Log performance issues
     */
    public function logPerformance($metric, $value, $threshold, $context = []) {
        if ($value > $threshold) {
            $context['metric'] = $metric;
            $context['value'] = $value;
            $context['threshold'] = $threshold;
            
            $this->logError('warning', "[PERFORMANCE] {$metric} exceeded threshold: {$value} > {$threshold}", $context);
        }
    }
    
    /**
     * Log business logic errors
     */
    public function logBusiness($operation, $message, $context = []) {
        $context['business_operation'] = $operation;
        $this->logError('info', "[BUSINESS] {$operation}: {$message}", $context);
    }
    
    /**
     * Write log to file
     */
    private function writeLog($level, $log_entry) {
        $log_file = $this->log_dir . '/' . date('Y-m-d') . '-' . $level . '.log';
        
        // Rotate log if too large
        if (file_exists($log_file) && filesize($log_file) > $this->max_log_size) {
            $this->rotateLog($log_file);
        }
        
        $log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Store log in database for monitoring
     */
    private function storeInDatabase($log_entry) {
        try {
            // Create logs table if not exists
            $this->createLogsTable();
            
            db()->insert('epi_error_logs', [
                'request_id' => $log_entry['request_id'],
                'level' => $log_entry['level'],
                'message' => $log_entry['message'],
                'context' => json_encode($log_entry['context']),
                'user_id' => $log_entry['user_id'],
                'ip_address' => $log_entry['ip'],
                'user_agent' => substr($log_entry['user_agent'], 0, 255),
                'url' => substr($log_entry['url'], 0, 255),
                'method' => $log_entry['method'],
                'memory_usage' => $log_entry['metrics']['memory_usage'],
                'execution_time' => $log_entry['metrics']['execution_time'],
                'created_at' => $log_entry['timestamp']
            ]);
        } catch (Exception $e) {
            // Fallback to file logging only
            error_log("Failed to store log in database: " . $e->getMessage());
        }
    }
    
    /**
     * Send alert for critical errors
     */
    private function sendAlert($log_entry) {
        // Implementation would depend on your alerting system
        // Could be email, Slack, SMS, etc.
        
        $alert_message = "CRITICAL ERROR DETECTED\n\n";
        $alert_message .= "Request ID: {$log_entry['request_id']}\n";
        $alert_message .= "Message: {$log_entry['message']}\n";
        $alert_message .= "URL: {$log_entry['url']}\n";
        $alert_message .= "User: " . ($log_entry['user_id'] ?? 'Guest') . "\n";
        $alert_message .= "Time: {$log_entry['timestamp']}\n";
        
        // Log to system error log as fallback
        error_log($alert_message);
        
        // TODO: Implement actual alerting (email, webhook, etc.)
    }
    
    /**
     * Send security alert
     */
    private function sendSecurityAlert($event_type, $message, $context) {
        $alert = [
            'type' => 'security_alert',
            'event_type' => $event_type,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => $this->request_id
        ];
        
        // Log to security log file
        $security_log = $this->log_dir . '/security-' . date('Y-m-d') . '.log';
        file_put_contents($security_log, json_encode($alert) . "\n", FILE_APPEND | LOCK_EX);
        
        // TODO: Send to security monitoring system
    }
    
    /**
     * Sanitize context data
     */
    private function sanitizeContext($context) {
        $sensitive_keys = ['password', 'token', 'secret', 'key', 'auth', 'session'];
        
        array_walk_recursive($context, function(&$value, $key) use ($sensitive_keys) {
            if (is_string($key) && $this->containsSensitiveData($key, $sensitive_keys)) {
                $value = '[REDACTED]';
            }
        });
        
        return $context;
    }
    
    /**
     * Check if key contains sensitive data
     */
    private function containsSensitiveData($key, $sensitive_keys) {
        $key_lower = strtolower($key);
        foreach ($sensitive_keys as $sensitive) {
            if (strpos($key_lower, $sensitive) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Sanitize stack trace
     */
    private function sanitizeStackTrace($trace) {
        // Remove sensitive information from stack trace
        $lines = explode("\n", $trace);
        $sanitized = [];
        
        foreach ($lines as $line) {
            // Remove absolute paths, keep relative
            $line = str_replace(EPIC_ROOT, '[ROOT]', $line);
            $sanitized[] = $line;
        }
        
        return implode("\n", array_slice($sanitized, 0, 10)); // Limit to 10 lines
    }
    
    /**
     * Generate unique request ID
     */
    private function generateRequestId() {
        return uniqid('req_', true);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Rotate log files
     */
    private function rotateLog($log_file) {
        $base_name = pathinfo($log_file, PATHINFO_FILENAME);
        $extension = pathinfo($log_file, PATHINFO_EXTENSION);
        $dir = dirname($log_file);
        
        // Move existing rotated logs
        for ($i = $this->max_log_files - 1; $i >= 1; $i--) {
            $old_file = "{$dir}/{$base_name}.{$i}.{$extension}";
            $new_file = "{$dir}/{$base_name}." . ($i + 1) . ".{$extension}";
            
            if (file_exists($old_file)) {
                if ($i == $this->max_log_files - 1) {
                    unlink($old_file); // Delete oldest
                } else {
                    rename($old_file, $new_file);
                }
            }
        }
        
        // Move current log to .1
        rename($log_file, "{$dir}/{$base_name}.1.{$extension}");
    }
    
    /**
     * Create logs table
     */
    private function createLogsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS epi_error_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_id VARCHAR(50) NOT NULL,
            level VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            context JSON,
            user_id INT NULL,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            url VARCHAR(255),
            method VARCHAR(10),
            memory_usage BIGINT,
            execution_time DECIMAL(10,6),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_level (level),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at),
            INDEX idx_request_id (request_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        db()->query($sql);
    }
}

/**
 * Global logging functions
 */

function epic_log_error($level, $message, $context = [], $exception = null) {
    static $logger = null;
    
    if ($logger === null) {
        $logger = new EpicEnhancedLogger();
    }
    
    $logger->logError($level, $message, $context, $exception);
}

function epic_log_security($event_type, $message, $context = []) {
    static $logger = null;
    
    if ($logger === null) {
        $logger = new EpicEnhancedLogger();
    }
    
    $logger->logSecurity($event_type, $message, $context);
}

function epic_log_performance($metric, $value, $threshold, $context = []) {
    static $logger = null;
    
    if ($logger === null) {
        $logger = new EpicEnhancedLogger();
    }
    
    $logger->logPerformance($metric, $value, $threshold, $context);
}

function epic_log_business($operation, $message, $context = []) {
    static $logger = null;
    
    if ($logger === null) {
        $logger = new EpicEnhancedLogger();
    }
    
    $logger->logBusiness($operation, $message, $context);
}