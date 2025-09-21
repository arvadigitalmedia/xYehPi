<?php
/**
 * Function Aliases untuk Backward Compatibility
 * Mengatasi error undefined function tanpa merusak sistem existing
 * 
 * @version 1.0.0
 * @author EPIC Hub Team
 */

if (!defined('EPIC_ROOT')) {
    die('Direct access not allowed');
}

/**
 * Database Function Aliases
 */

// Alias untuk epic_get_db() -> db()
if (!function_exists('epic_get_db')) {
    function epic_get_db() {
        if (function_exists('db')) {
            return db();
        }
        
        // Fallback jika db() tidak tersedia
        global $epic_db;
        if (isset($epic_db)) {
            return $epic_db;
        }
        
        // Last resort - load database config
        if (file_exists(EPIC_ROOT . '/config/database.php')) {
            require_once EPIC_ROOT . '/config/database.php';
            return db();
        }
        
        throw new Exception('Database connection not available');
    }
}

/**
 * Routing Function Aliases
 */

// Alias untuk epic_404() -> epic_route_404()
if (!function_exists('epic_404')) {
    function epic_404() {
        if (function_exists('epic_route_404')) {
            epic_route_404();
            return;
        }
        
        // Fallback 404 response
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p>The requested page could not be found.</p>';
        if (function_exists('epic_url')) {
            echo '<p><a href="' . epic_url() . '">Return to Home</a></p>';
        }
        exit;
    }
}

// Alias untuk epic_403() -> epic_route_403()
if (!function_exists('epic_403')) {
    function epic_403() {
        if (function_exists('epic_route_403')) {
            epic_route_403();
            return;
        }
        
        // Fallback 403 response
        http_response_code(403);
        echo '<h1>403 - Access Forbidden</h1>';
        echo '<p>You do not have permission to access this page.</p>';
        if (function_exists('epic_url')) {
            echo '<p><a href="' . epic_url() . '">Return to Home</a></p>';
        }
        exit;
    }
}

/**
 * CSRF Function Aliases
 */

// Alias untuk epic_csrf_token_field() -> epic_csrf_field()
if (!function_exists('epic_csrf_token_field')) {
    function epic_csrf_token_field($action = 'default') {
        if (function_exists('epic_csrf_field')) {
            return epic_csrf_field($action);
        }
        
        // Fallback CSRF field generation
        if (function_exists('epic_generate_csrf_token')) {
            $token = epic_generate_csrf_token($action);
            return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">' .
                   '<input type="hidden" name="csrf_action" value="' . htmlspecialchars($action) . '">';
        }
        
        // Last resort - basic token
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }
}

// Alias untuk epic_csrf_token() -> epic_generate_csrf_token()
if (!function_exists('epic_csrf_token')) {
    function epic_csrf_token($action = 'default') {
        if (function_exists('epic_generate_csrf_token')) {
            return epic_generate_csrf_token($action);
        }
        
        // Fallback token generation
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}

/**
 * Utility Function Aliases
 */

// Alias untuk epic_sanitize() -> epic_sanitize_input()
if (!function_exists('epic_sanitize')) {
    function epic_sanitize($input, $type = 'string') {
        if (function_exists('epic_sanitize_input')) {
            return epic_sanitize_input($input, $type);
        }
        
        // Fallback sanitization
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return epic_sanitize($item, $type);
            }, $input);
        }
        
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
}

/**
 * Error Handling Function Aliases
 */

// Alias untuk epic_error() -> epic_handle_error()
if (!function_exists('epic_error')) {
    function epic_error($message, $code = 500) {
        if (function_exists('epic_handle_error')) {
            $exception = new Exception($message, $code);
            epic_handle_error($exception);
            return;
        }
        
        // Fallback error handling
        http_response_code($code);
        echo '<h1>Error ' . $code . '</h1>';
        echo '<p>' . htmlspecialchars($message) . '</p>';
        exit;
    }
}

/**
 * Logging Function Aliases
 */

// Alias untuk epic_log() -> epic_log_error()
if (!function_exists('epic_log')) {
    function epic_log($level, $message, $context = []) {
        if (function_exists('epic_log_error')) {
            return epic_log_error($level, $message, $context);
        }
        
        // Fallback logging
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context
        ];
        
        error_log(json_encode($log_entry));
        return uniqid();
    }
}

/**
 * Auto-load aliases when this file is included
 */
function epic_load_function_aliases() {
    // This function ensures all aliases are loaded
    // Called automatically when file is included
    return true;
}

// Auto-execute
epic_load_function_aliases();

?>