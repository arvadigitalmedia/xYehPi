<?php
/**
 * EPIC Hub Admin Routing Helper
 * Helper functions untuk menangani routing dan error handling secara konsisten
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Validate admin access with proper error handling
 * 
 * @param string $required_role Required role (admin, super_admin)
 * @param string $redirect_path Path to redirect after login
 * @return array User data if valid
 */
function epic_validate_admin_access($required_role = 'admin', $redirect_path = 'admin') {
    // Check if user is logged in
    $user = epic_current_user();
    
    if (!$user) {
        // Redirect to login with return path
        $login_url = epic_url('login?redirect=' . urlencode($redirect_path));
        epic_redirect($login_url);
        exit;
    }
    
    // Check role permission
    $allowed_roles = [];
    switch ($required_role) {
        case 'super_admin':
            $allowed_roles = ['super_admin'];
            break;
        case 'admin':
            $allowed_roles = ['admin', 'super_admin'];
            break;
        default:
            $allowed_roles = ['admin', 'super_admin'];
    }
    
    if (!in_array($user['role'], $allowed_roles)) {
        // Handle 403 error properly
        epic_handle_403_error();
        exit;
    }
    
    return $user;
}

/**
 * Handle 403 error with fallback options
 */
function epic_handle_403_error() {
    http_response_code(403);
    
    // Try to use proper 403 function
    if (function_exists('epic_route_403')) {
        epic_route_403();
        return;
    }
    
    // Try to include 403 error page
    $error_403_path = __DIR__ . '/../error/403.php';
    if (file_exists($error_403_path)) {
        include $error_403_path;
        return;
    }
    
    // Fallback to simple error message
    echo '<!DOCTYPE html>';
    echo '<html><head><title>403 - Access Forbidden</title></head><body>';
    echo '<h1>403 - Access Forbidden</h1>';
    echo '<p>You do not have permission to access this page.</p>';
    echo '<p><a href="' . epic_url('admin') . '">Back to Admin Dashboard</a></p>';
    echo '</body></html>';
}

/**
 * Validate database connection and required functions
 * 
 * @return bool True if all requirements are met
 */
function epic_validate_system_requirements() {
    $errors = [];
    
    // Check if EPIC_INIT is defined
    if (!defined('EPIC_INIT')) {
        $errors[] = 'EPIC_INIT constant not defined';
    }
    
    // Check if database function exists
    if (!function_exists('db')) {
        $errors[] = 'Database function not available';
    }
    
    // Check if essential functions exist
    $required_functions = [
        'epic_current_user',
        'epic_url',
        'epic_redirect'
    ];
    
    foreach ($required_functions as $func) {
        if (!function_exists($func)) {
            $errors[] = "Required function '{$func}' not available";
        }
    }
    
    // Test database connection
    try {
        $db = db();
        $db->selectValue("SELECT 1");
    } catch (Exception $e) {
        $errors[] = "Database connection failed: " . $e->getMessage();
    }
    
    if (!empty($errors)) {
        epic_handle_system_error($errors);
        return false;
    }
    
    return true;
}

/**
 * Handle system errors with detailed information
 * 
 * @param array $errors Array of error messages
 */
function epic_handle_system_error($errors) {
    http_response_code(500);
    
    echo '<!DOCTYPE html>';
    echo '<html><head><title>500 - System Error</title></head><body>';
    echo '<h1>500 - Internal Server Error</h1>';
    echo '<p>The system encountered the following errors:</p>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '<p>Please contact the system administrator.</p>';
    echo '</body></html>';
}

/**
 * Safe database query with error handling
 * 
 * @param string $query SQL query
 * @param array $params Query parameters
 * @param string $method Database method (select, selectOne, selectValue)
 * @return mixed Query result or empty array/null on error
 */
function epic_safe_db_query($query, $params = [], $method = 'select') {
    try {
        if (!function_exists('db')) {
            throw new Exception('Database function not available');
        }
        
        $db = db();
        
        switch ($method) {
            case 'select':
                return $db->select($query, $params) ?: [];
            case 'selectOne':
                return $db->selectOne($query, $params) ?: null;
            case 'selectValue':
                return $db->selectValue($query, $params) ?: 0;
            default:
                return $db->select($query, $params) ?: [];
        }
    } catch (Exception $e) {
        // Log error if logging is available
        if (function_exists('error_log')) {
            error_log('Database query error: ' . $e->getMessage() . ' Query: ' . $query);
        }
        
        // Return safe defaults
        switch ($method) {
            case 'select':
                return [];
            case 'selectOne':
                return null;
            case 'selectValue':
                return 0;
            default:
                return [];
        }
    }
}

/**
 * Initialize admin page with all necessary checks
 * 
 * @param string $required_role Required role for access
 * @param string $redirect_path Redirect path for login
 * @return array User data and system status
 */
function epic_init_admin_page($required_role = 'admin', $redirect_path = 'admin') {
    // Validate system requirements
    if (!epic_validate_system_requirements()) {
        exit;
    }
    
    // Validate admin access
    $user = epic_validate_admin_access($required_role, $redirect_path);
    
    return [
        'user' => $user,
        'system_ok' => true
    ];
}
?>