<?php
/**
 * CSRF Protection System
 * Enhanced security for form submissions
 */

// Prevent direct access
if (!defined('EPIC_ROOT')) {
    die('Direct access not permitted');
}

/**
 * Generate CSRF token for a specific action
 * @param string $action Action name (default: 'default')
 * @return string Generated token
 */
if (!function_exists('epic_generate_csrf_token')) {
function epic_generate_csrf_token($action = 'default') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Initialize CSRF tokens array if not exists
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $timestamp = time();
    
    // Store token with metadata
    $_SESSION['csrf_tokens'][$action] = [
        'token' => $token,
        'timestamp' => $timestamp,
        'used' => false
    ];
    
    return $token;
}
}

/**
 * Verify CSRF token
 */
if (!function_exists('epic_verify_csrf_token')) {
function epic_verify_csrf_token($token, $action = 'default', $single_use = true) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if tokens exist
    if (!isset($_SESSION['csrf_tokens'][$action])) {
        return false;
    }
    
    $stored_token = $_SESSION['csrf_tokens'][$action];
    
    // Check if token was already used (for single-use tokens)
    if ($single_use && $stored_token['used']) {
        return false;
    }
    
    // Verify token
    if (!hash_equals($stored_token['token'], $token)) {
        return false;
    }
    
    // Check token expiration (default: 1 hour)
    $token_lifetime = 3600; // Default 1 hour
    if (function_exists('epic_setting')) {
        $token_lifetime = epic_setting('csrf_token_lifetime', 3600);
    }
    
    if ((time() - $stored_token['timestamp']) > $token_lifetime) {
        unset($_SESSION['csrf_tokens'][$action]);
        return false;
    }
    
    // Mark token as used if single-use
    if ($single_use) {
        $_SESSION['csrf_tokens'][$action]['used'] = true;
    }
    
    return true;
}
}

/**
 * Clean up expired CSRF tokens
 */
if (!function_exists('epic_cleanup_csrf_tokens')) {
function epic_cleanup_csrf_tokens() {
    if (!isset($_SESSION['csrf_tokens'])) {
        return;
    }
    
    $token_lifetime = epic_setting('csrf_token_lifetime', 3600);
    $current_time = time();
    
    foreach ($_SESSION['csrf_tokens'] as $action => $token_data) {
        if (($current_time - $token_data['timestamp']) > $token_lifetime) {
            unset($_SESSION['csrf_tokens'][$action]);
        }
    }
}
}

/**
 * Generate CSRF hidden input field
 */
if (!function_exists('epic_csrf_field')) {
function epic_csrf_field($action = 'default') {
    $token = epic_generate_csrf_token($action);
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">' .
           '<input type="hidden" name="csrf_action" value="' . htmlspecialchars($action) . '">';
}
}

/**
 * Verify CSRF token from request
 */
if (!function_exists('epic_verify_csrf_request')) {
function epic_verify_csrf_request($single_use = true) {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    $action = $_POST['csrf_action'] ?? $_GET['csrf_action'] ?? 'default';
    
    if (empty($token)) {
        epic_csrf_error('CSRF token missing');
        return false;
    }
    
    if (!epic_verify_csrf_token($token, $action, $single_use)) {
        epic_csrf_error('CSRF token invalid or expired');
        return false;
    }
    
    return true;
}
}

/**
 * Handle CSRF error
 */
if (!function_exists('epic_csrf_error')) {
function epic_csrf_error($message) {
    // Log CSRF attack attempt
    $log_data = [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message
    ];
    
    error_log('CSRF Attack Attempt: ' . json_encode($log_data));
    
    // Store in database if available
    try {
         if (function_exists('db') && db()) {
             $stmt = db()->getConnection()->prepare("INSERT INTO epic_epi_error_logs (error_type, error_message, context, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
             $stmt->execute([
                 'csrf_violation',
                 $message,
                 json_encode($log_data),
                 $log_data['ip'],
                 $log_data['user_agent']
             ]);
         }
     } catch (Exception $e) {
         error_log('Failed to log CSRF error to database: ' . $e->getMessage());
     }
    
    // Handle response based on request type
    if (epic_is_ajax_request()) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'CSRF token validation failed',
            'code' => 'CSRF_ERROR'
        ]);
        exit;
    } else {
        // Redirect to error page or show error
        header('HTTP/1.1 403 Forbidden');
        echo '<h1>Security Error</h1><p>CSRF token validation failed. Please try again.</p>';
        exit;
    }
}
}

/**
 * Check if request is AJAX
 */
if (!function_exists('epic_is_ajax_request')) {
function epic_is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
}

/**
 * CSRF middleware for forms
 */
if (!function_exists('epic_csrf_middleware')) {
function epic_csrf_middleware($action = 'default') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!epic_verify_csrf_request()) {
            return false;
        }
    }
    return true;
}
}

/**
 * Enhanced input sanitization
 */
if (!function_exists('epic_sanitize_input')) {
function epic_sanitize_input($input, $type = 'string') {
    if (is_array($input)) {
        return array_map(function($item) use ($type) {
            return epic_sanitize_input($item, $type);
        }, $input);
    }
    
    // Remove null bytes
    $input = str_replace("\0", '', $input);
    
    switch ($type) {
        case 'email':
            return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            
        case 'url':
            return filter_var(trim($input), FILTER_SANITIZE_URL);
            
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
        case 'html':
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
            
        case 'sql':
            // For SQL, use prepared statements instead
            return trim($input);
            
        case 'filename':
            // Remove dangerous characters from filenames
            $input = preg_replace('/[^a-zA-Z0-9._-]/', '', $input);
            return trim($input, '.');
            
        default: // string
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
}

/**
 * Validate and sanitize form data
 */
if (!function_exists('epic_validate_form_data')) {
function epic_validate_form_data($data, $rules) {
    global $epic_validation_data;
    $epic_validation_data = $data; // Make data available globally for custom validators
    
    $validated = [];
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';
        $field_errors = [];
        
        // Required validation
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $field_errors[] = $rule['required_message'] ?? "Field {$field} is required";
            continue;
        }
        
        // Skip other validations if field is empty and not required
        if (empty($value) && (!isset($rule['required']) || !$rule['required'])) {
            $validated[$field] = '';
            continue;
        }
        
        // Type validation and sanitization
        $type = $rule['type'] ?? 'string';
        $sanitized_value = epic_sanitize_input($value, $type);
        
        // Length validation
        if (isset($rule['min_length']) && strlen($sanitized_value) < $rule['min_length']) {
            $field_errors[] = $rule['min_length_message'] ?? "Field {$field} must be at least {$rule['min_length']} characters";
        }
        
        if (isset($rule['max_length']) && strlen($sanitized_value) > $rule['max_length']) {
            $field_errors[] = $rule['max_length_message'] ?? "Field {$field} must not exceed {$rule['max_length']} characters";
        }
        
        // Pattern validation
        if (isset($rule['pattern']) && !preg_match($rule['pattern'], $sanitized_value)) {
            $field_errors[] = $rule['pattern_message'] ?? "Field {$field} format is invalid";
        }
        
        // Custom validation
        if (isset($rule['custom']) && is_callable($rule['custom'])) {
            $custom_result = $rule['custom']($sanitized_value);
            if ($custom_result !== true) {
                $field_errors[] = is_string($custom_result) ? $custom_result : "Field {$field} validation failed";
            }
        }
        
        if (empty($field_errors)) {
            $validated[$field] = $sanitized_value;
        } else {
            $errors[$field] = $field_errors;
        }
    }
    
    return [
        'valid' => empty($errors),
        'data' => $validated,
        'errors' => $errors
    ];
}
}

/**
 * Registration form validation rules
 */
if (!function_exists('epic_get_registration_validation_rules')) {
function epic_get_registration_validation_rules() {
    return [
        'name' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 2,
            'max_length' => 100,
            'pattern' => '/^[a-zA-Z\s\-\.\']+$/',
            'required_message' => 'Nama lengkap wajib diisi',
            'min_length_message' => 'Nama minimal 2 karakter',
            'max_length_message' => 'Nama maksimal 100 karakter',
            'pattern_message' => 'Nama hanya boleh berisi huruf, spasi, tanda hubung, titik, dan apostrof'
        ],
        'email' => [
            'required' => true,
            'type' => 'email',
            'max_length' => 255,
            'pattern' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
            'required_message' => 'Email wajib diisi',
            'max_length_message' => 'Email maksimal 255 karakter',
            'pattern_message' => 'Format email tidak valid',
            'custom' => function($email) {
                // Check if email already exists
                try {
                     $stmt = db()->getConnection()->prepare("SELECT id FROM epic_users WHERE email = ?");
                     $stmt->execute([$email]);
                     if ($stmt->fetch()) {
                         return 'Email sudah terdaftar';
                     }
                 } catch (Exception $e) {
                     error_log('Email validation error: ' . $e->getMessage());
                 }
                return true;
            }
        ],
        'password' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 8,
            'max_length' => 255,
            'required_message' => 'Password wajib diisi',
            'min_length_message' => 'Password minimal 8 karakter',
            'max_length_message' => 'Password maksimal 255 karakter',
            'custom' => function($password) {
                // Password strength validation
                if (!preg_match('/[A-Z]/', $password)) {
                    return 'Password harus mengandung minimal 1 huruf besar';
                }
                if (!preg_match('/[a-z]/', $password)) {
                    return 'Password harus mengandung minimal 1 huruf kecil';
                }
                if (!preg_match('/[0-9]/', $password)) {
                    return 'Password harus mengandung minimal 1 angka';
                }
                return true;
            }
        ],
        'confirm_password' => [
            'required' => true,
            'type' => 'string',
            'required_message' => 'Konfirmasi password wajib diisi',
            'custom' => function($confirm_password) {
                // Get password from the same data context
                global $epic_validation_data;
                $password = $epic_validation_data['password'] ?? ($_POST['password'] ?? '');
                if ($confirm_password !== $password) {
                    return 'Konfirmasi password tidak cocok';
                }
                return true;
            }
        ],
        'referral_code' => [
            'required' => false,
            'type' => 'string',
            'max_length' => 20,
            'pattern' => '/^[A-Za-z0-9]+$/',
            'max_length_message' => 'Kode referral maksimal 20 karakter',
            'pattern_message' => 'Kode referral hanya boleh berisi huruf dan angka',
            'custom' => function($referral_code) {
                if (empty($referral_code)) {
                    return true; // Optional field
                }
                
                // Normalize referral code (case-insensitive, trim)
                $referral_code = strtoupper(trim($referral_code));
                
                try {
                     $stmt = db()->getConnection()->prepare("SELECT id FROM epic_users WHERE UPPER(referral_code) = ? AND status != 'banned'");
                     $stmt->execute([$referral_code]);
                     if (!$stmt->fetch()) {
                         return 'Kode referral tidak valid atau tidak aktif';
                     }
                 } catch (Exception $e) {
                     error_log('Referral validation error: ' . $e->getMessage());
                     return 'Gagal memvalidasi kode referral';
                 }
                return true;
            }
        ]
    ];
}
}

/**
 * Validate registration form with CSRF protection
 */
if (!function_exists('epic_validate_registration_form')) {
function epic_validate_registration_form($data) {
    // CSRF protection
    if (!epic_verify_csrf_request()) {
        return [
            'valid' => false,
            'data' => [],
            'errors' => ['csrf' => ['CSRF token validation failed']]
        ];
    }
    
    // Validate form data
    $rules = epic_get_registration_validation_rules();
    return epic_validate_form_data($data, $rules);
}
}

/**
 * Create CSRF tokens table for database storage (optional)
 */
if (!function_exists('epic_create_csrf_tokens_table')) {
function epic_create_csrf_tokens_table() {
    $sql = "CREATE TABLE IF NOT EXISTS `epi_csrf_tokens` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `token` varchar(64) NOT NULL,
        `action` varchar(50) NOT NULL DEFAULT 'default',
        `user_id` bigint(20) unsigned DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `expires_at` timestamp NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `used` tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_token_action` (`token`, `action`),
        KEY `idx_expires_at` (`expires_at`),
        KEY `idx_user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db()->getConnection()->exec($sql);
}
}
?>