<?php
/**
 * EPIC Rate Limiter System
 * Protects registration and referral endpoints from abuse
 * 
 * @author TRAE AI Assistant
 * @version 1.0.0
 */

// Conditional type declaration for IDE support
if (!class_exists('Redis') && !extension_loaded('redis')) {
    /**
     * Mock Redis class for IDE type hinting when Redis extension is not available
     * This is only for IDE support and will not be used at runtime
     */
    class Redis {
        public function connect($host, $port) { return false; }
        public function auth($password) { return false; }
        public function get($key) { return false; }
        public function set($key, $value, $ttl = null) { return false; }
        public function incr($key) { return false; }
        public function expire($key, $ttl) { return false; }
        public function del($key) { return false; }
        public function close() { return false; }
        public function ping() { return false; }
        public function zRemRangeByScore($key, $min, $max) { return false; }
        public function zCard($key) { return false; }
        public function zRange($key, $start, $stop, $withscores = false) { return false; }
        public function zAdd($key, $score, $member) { return false; }
    }
}

class EpicRateLimiter {
    /** @var Redis|null Redis instance when available */
    private $redis = null;
    /** @var bool */
    private $use_redis = false;
    /** @var string */
    private $table_name = 'epi_rate_limits';
    
    public function __construct() {
        // Try to use Redis if available, fallback to database
        $this->use_redis = extension_loaded('redis') && defined('REDIS_HOST') && class_exists('Redis');
        
        if ($this->use_redis) {
            try {
                // Ensure Redis class exists before instantiation
                if (!class_exists('Redis')) {
                    throw new Exception('Redis class not available');
                }
                
                /** @var Redis $redis */
                $this->redis = new Redis();
                $connection_result = $this->redis->connect(REDIS_HOST, (int)REDIS_PORT);
                
                if (!$connection_result) {
                    throw new Exception('Failed to connect to Redis server');
                }
                
                if (defined('REDIS_PASSWORD') && !empty(REDIS_PASSWORD)) {
                    $auth_result = $this->redis->auth(REDIS_PASSWORD);
                    if (!$auth_result) {
                        throw new Exception('Redis authentication failed');
                    }
                }
                
                // Test Redis connection
                $this->redis->ping();
                
            } catch (Exception $e) {
                $this->use_redis = false;
                $this->redis = null;
                error_log("Redis connection failed, falling back to database: " . $e->getMessage());
            }
        }
        
        // Ensure rate limit table exists
        $this->createRateLimitTable();
    }
    
    /**
     * Check if action is rate limited
     * 
     * @param string $action Action type (register, referral_check, etc)
     * @param string $identifier IP address or user identifier
     * @param int $limit Maximum attempts
     * @param int $window Time window in seconds
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
     */
    public function checkLimit($action, $identifier, $limit = 5, $window = 300) {
        $key = "rate_limit:{$action}:{$identifier}";
        $current_time = time();
        
        if ($this->use_redis) {
            return $this->checkLimitRedis($key, $limit, $window, $current_time);
        } else {
            return $this->checkLimitDatabase($action, $identifier, $limit, $window, $current_time);
        }
    }
    
    /**
     * Redis-based rate limiting (preferred)
     */
    private function checkLimitRedis($key, $limit, $window, $current_time) {
        // Ensure Redis is available
        if ($this->redis === null) {
            throw new Exception('Redis connection is not available');
        }
        
        try {
            // Use sliding window with Redis sorted sets
            $this->redis->zRemRangeByScore($key, 0, $current_time - $window);
            $current_count = $this->redis->zCard($key);
            
            if ($current_count >= $limit) {
                $oldest_request = $this->redis->zRange($key, 0, 0, true);
                $reset_time = !empty($oldest_request) ? array_values($oldest_request)[0] + $window : $current_time + $window;
                
                return [
                    'allowed' => false,
                    'remaining' => 0,
                    'reset_time' => $reset_time,
                    'retry_after' => $reset_time - $current_time
                ];
            }
            
            // Add current request
            $this->redis->zAdd($key, $current_time, uniqid());
            $this->redis->expire($key, $window);
            
            return [
                'allowed' => true,
                'remaining' => $limit - $current_count - 1,
                'reset_time' => $current_time + $window,
                'retry_after' => 0
            ];
            
        } catch (Exception $e) {
            error_log("Redis rate limiting failed: " . $e->getMessage());
            // Fallback to database
            return $this->checkLimitDatabase(
                explode(':', $key)[1], 
                explode(':', $key)[2], 
                $limit, $window, $current_time
            );
        }
    }
    
    /**
     * Database-based rate limiting (fallback)
     */
    private function checkLimitDatabase($action, $identifier, $limit, $window, $current_time) {
        $window_start = $current_time - $window;
        
        // Clean old entries
        db()->query(
            "DELETE FROM {$this->table_name} WHERE created_at < ?",
            [date('Y-m-d H:i:s', $window_start)]
        );
        
        // Count current requests
        $current_count = db()->selectValue(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE action = ? AND identifier = ? AND created_at >= ?",
            [$action, $identifier, date('Y-m-d H:i:s', $window_start)]
        );
        
        if ($current_count >= $limit) {
            $oldest_request = db()->selectValue(
                "SELECT UNIX_TIMESTAMP(created_at) FROM {$this->table_name} 
                 WHERE action = ? AND identifier = ? 
                 ORDER BY created_at ASC LIMIT 1",
                [$action, $identifier]
            );
            
            $reset_time = $oldest_request ? $oldest_request + $window : $current_time + $window;
            
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_time' => $reset_time,
                'retry_after' => $reset_time - $current_time
            ];
        }
        
        // Add current request
        db()->insert($this->table_name, [
            'action' => $action,
            'identifier' => $identifier,
            'created_at' => date('Y-m-d H:i:s', $current_time)
        ]);
        
        return [
            'allowed' => true,
            'remaining' => $limit - $current_count - 1,
            'reset_time' => $current_time + $window,
            'retry_after' => 0
        ];
    }
    
    /**
     * Create rate limit table if not exists
     */
    private function createRateLimitTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(50) NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_action_identifier_time (action, identifier, created_at),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        db()->query($sql);
    }
    
    /**
     * Get client identifier (IP + User Agent hash)
     */
    public static function getClientIdentifier() {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Use first IP if multiple (proxy chain)
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        
        return $ip . ':' . substr(md5($user_agent), 0, 8);
    }
    
    /**
     * Send rate limit headers
     */
    public static function sendRateLimitHeaders($result) {
        header('X-RateLimit-Remaining: ' . $result['remaining']);
        header('X-RateLimit-Reset: ' . $result['reset_time']);
        
        if (!$result['allowed']) {
            header('Retry-After: ' . $result['retry_after']);
            http_response_code(429);
        }
    }
}

/**
 * Rate limiting middleware functions
 */

/**
 * Check registration rate limit
 */
function epic_check_registration_rate_limit() {
    $limiter = new EpicRateLimiter();
    $identifier = EpicRateLimiter::getClientIdentifier();
    
    // 3 registration attempts per 10 minutes
    $result = $limiter->checkLimit('registration', $identifier, 3, 600);
    
    EpicRateLimiter::sendRateLimitHeaders($result);
    
    if (!$result['allowed']) {
        $minutes = ceil($result['retry_after'] / 60);
        
        // Log rate limit hit
        epic_log_error('warning', 'Registration rate limit exceeded', [
            'identifier' => $identifier,
            'remaining' => $result['remaining'],
            'reset_time' => $result['reset_time'],
            'retry_after' => $result['retry_after']
        ]);
        
        // Log to monitoring system
        if (file_exists(EPIC_ROOT . '/core/monitoring.php')) {
            require_once EPIC_ROOT . '/core/monitoring.php';
            epic_log_registration_error('rate_limit', 'Rate limit exceeded', [
                'identifier' => $identifier,
                'remaining' => $result['remaining'],
                'reset_time' => $result['reset_time'],
                'retry_after' => $result['retry_after']
            ]);
        }
        
        throw new Exception("Too many registration attempts. Please try again in {$minutes} minutes.");
    }
    
    return $result;
}

/**
 * Check referral validation rate limit
 */
function epic_check_referral_rate_limit() {
    $limiter = new EpicRateLimiter();
    $identifier = EpicRateLimiter::getClientIdentifier();
    
    // 10 referral checks per 5 minutes
    $result = $limiter->checkLimit('referral_check', $identifier, 10, 300);
    
    EpicRateLimiter::sendRateLimitHeaders($result);
    
    if (!$result['allowed']) {
        $seconds = $result['retry_after'];
        
        // Log rate limit hit
        epic_log_error('warning', 'Referral rate limit exceeded', [
            'identifier' => $identifier,
            'remaining' => $result['remaining'],
            'reset_time' => $result['reset_time'],
            'retry_after' => $result['retry_after']
        ]);
        
        // Log to monitoring system
        if (file_exists(EPIC_ROOT . '/core/monitoring.php')) {
            require_once EPIC_ROOT . '/core/monitoring.php';
            epic_log_registration_error('rate_limit', 'Referral rate limit exceeded', [
                'identifier' => $identifier,
                'remaining' => $result['remaining'],
                'reset_time' => $result['reset_time'],
                'retry_after' => $result['retry_after']
            ]);
        }
        
        throw new Exception("Too many referral validation attempts. Please try again in {$seconds} seconds.");
    }
    
    return $result;
}

/**
 * Check general API rate limit
 */
function epic_check_api_rate_limit($action = 'api', $limit = 60, $window = 60) {
    $limiter = new EpicRateLimiter();
    $identifier = EpicRateLimiter::getClientIdentifier();
    
    $result = $limiter->checkLimit($action, $identifier, $limit, $window);
    
    EpicRateLimiter::sendRateLimitHeaders($result);
    
    if (!$result['allowed']) {
        // Log rate limit hit
        epic_log_error('warning', 'API rate limit exceeded', [
            'action' => $action,
            'identifier' => $identifier,
            'limit' => $limit,
            'window' => $window,
            'remaining' => $result['remaining'],
            'retry_after' => $result['retry_after']
        ]);
        
        // Log to monitoring system
        if (file_exists(EPIC_ROOT . '/core/monitoring.php')) {
            require_once EPIC_ROOT . '/core/monitoring.php';
            epic_log_registration_error('rate_limit', 'API rate limit exceeded', [
                'action' => $action,
                'identifier' => $identifier,
                'limit' => $limit,
                'window' => $window,
                'remaining' => $result['remaining'],
                'retry_after' => $result['retry_after']
            ]);
        }
        
        throw new Exception("Rate limit exceeded. Please try again later.");
    }
    
    return $result;
}

/**
 * Enhanced registration rate limit with multiple layers
 */
function epic_check_enhanced_registration_rate_limit($email = null) {
    $limiter = new EpicRateLimiter();
    $ip = EpicRateLimiter::getClientIdentifier();
    
    // Layer 1: IP-based rate limiting (5 attempts per hour)
    $ip_result = $limiter->checkLimit('registration_ip', $ip, 5, 3600);
    
    if (!$ip_result['allowed']) {
        $minutes = ceil($ip_result['retry_after'] / 60);
        
        epic_log_error('warning', 'IP registration rate limit exceeded', [
            'ip' => $ip,
            'remaining' => $ip_result['remaining'],
            'retry_after' => $ip_result['retry_after']
        ]);
        
        throw new Exception("Too many registration attempts from this IP. Please try again in {$minutes} minutes.");
    }
    
    // Layer 2: Email-based rate limiting (3 attempts per hour)
    if ($email) {
        $email_hash = md5(strtolower(trim($email)));
        $email_result = $limiter->checkLimit('registration_email', $email_hash, 3, 3600);
        
        if (!$email_result['allowed']) {
            $minutes = ceil($email_result['retry_after'] / 60);
            
            epic_log_error('warning', 'Email registration rate limit exceeded', [
                'email_hash' => $email_hash,
                'remaining' => $email_result['remaining'],
                'retry_after' => $email_result['retry_after']
            ]);
            
            throw new Exception("Too many registration attempts with this email. Please try again in {$minutes} minutes.");
        }
    }
    
    // Layer 3: Device fingerprint rate limiting (10 attempts per day)
    $device_fingerprint = epic_get_device_fingerprint();
    if ($device_fingerprint) {
        $device_result = $limiter->checkLimit('registration_device', $device_fingerprint, 10, 86400);
        
        if (!$device_result['allowed']) {
            $hours = ceil($device_result['retry_after'] / 3600);
            
            epic_log_error('warning', 'Device registration rate limit exceeded', [
                'device_fingerprint' => substr($device_fingerprint, 0, 8) . '...',
                'remaining' => $device_result['remaining'],
                'retry_after' => $device_result['retry_after']
            ]);
            
            throw new Exception("Too many registration attempts from this device. Please try again in {$hours} hours.");
        }
    }
    
    // Send most restrictive headers
    $most_restrictive = $ip_result;
    if ($email && isset($email_result) && $email_result['remaining'] < $most_restrictive['remaining']) {
        $most_restrictive = $email_result;
    }
    if ($device_fingerprint && isset($device_result) && $device_result['remaining'] < $most_restrictive['remaining']) {
        $most_restrictive = $device_result;
    }
    
    EpicRateLimiter::sendRateLimitHeaders($most_restrictive);
    
    return $most_restrictive;
}

/**
 * Generate device fingerprint based on browser characteristics
 */
function epic_get_device_fingerprint() {
    $components = [
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
        $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
        $_SERVER['HTTP_ACCEPT'] ?? '',
        // Don't include screen resolution or timezone as they can change
    ];
    
    $fingerprint = implode('|', $components);
    return hash('sha256', $fingerprint);
}

/**
 * Enhanced API rate limiting with burst protection
 */
function epic_check_enhanced_api_rate_limit($action = 'api', $limit = 60, $window = 60, $burst_limit = 10, $burst_window = 10) {
    $limiter = new EpicRateLimiter();
    $identifier = EpicRateLimiter::getClientIdentifier();
    
    // Check burst limit first (short window, low limit)
    $burst_result = $limiter->checkLimit("{$action}_burst", $identifier, $burst_limit, $burst_window);
    
    if (!$burst_result['allowed']) {
        epic_log_error('warning', 'API burst rate limit exceeded', [
            'action' => $action,
            'identifier' => $identifier,
            'burst_limit' => $burst_limit,
            'burst_window' => $burst_window
        ]);
        
        throw new Exception("Too many requests. Please slow down.");
    }
    
    // Check normal rate limit (longer window, higher limit)
    $normal_result = $limiter->checkLimit($action, $identifier, $limit, $window);
    
    if (!$normal_result['allowed']) {
        $minutes = ceil($normal_result['retry_after'] / 60);
        
        epic_log_error('warning', 'API rate limit exceeded', [
            'action' => $action,
            'identifier' => $identifier,
            'limit' => $limit,
            'window' => $window
        ]);
        
        throw new Exception("Rate limit exceeded. Please try again in {$minutes} minutes.");
    }
    
    // Send headers for the most restrictive limit
    $most_restrictive = ($burst_result['remaining'] < $normal_result['remaining']) ? $burst_result : $normal_result;
    EpicRateLimiter::sendRateLimitHeaders($most_restrictive);
    
    return $most_restrictive;
}