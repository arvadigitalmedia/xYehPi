<?php
/**
 * EPIC Hub Bootstrap File
 * Initialize the EPIC Hub application
 */

// Prevent direct access
if (!defined('EPIC_INIT')) {
    define('EPIC_INIT', true);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Define constants
define('EPIC_VERSION', '2.0.0');
define('EPIC_ROOT', __DIR__);
define('EPIC_PATH', __DIR__); // Alias for compatibility
define('EPIC_LOADED', true); // Security constant
define('EPIC_CONFIG_DIR', EPIC_ROOT . '/config');
define('EPIC_CORE_DIR', EPIC_ROOT . '/core');
define('EPIC_THEME_DIR', EPIC_ROOT . '/themes');
define('EPIC_UPLOAD_DIR', EPIC_ROOT . '/uploads');
define('EPIC_CACHE_DIR', EPIC_ROOT . '/cache');

// Debug mode - check environment
$current_domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (strpos($current_domain, 'localhost') !== false || 
    strpos($current_domain, '127.0.0.1') !== false ||
    strpos($current_domain, '.local') !== false) {
    define('EPIC_DEBUG', true);
} else {
    define('EPIC_DEBUG', false);
}

// Load .env file if exists
if (file_exists(EPIC_ROOT . '/.env')) {
    $env_lines = file(EPIC_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key) && !defined($key)) {
                define($key, $value);
            }
        }
    }
}

// Define MAILKETING constants with fallbacks
if (!defined('MAILKETING_FROM_NAME')) {
    define('MAILKETING_FROM_NAME', defined('EPIC_MAIL_FROM_NAME') ? EPIC_MAIL_FROM_NAME : 'EPIC Hub');
}
if (!defined('MAILKETING_FROM_EMAIL')) {
    define('MAILKETING_FROM_EMAIL', defined('EPIC_MAIL_FROM_EMAIL') ? EPIC_MAIL_FROM_EMAIL : 'noreply@epichub.local');
}

// Define REDIS constants with fallbacks
if (!defined('REDIS_HOST')) {
    define('REDIS_HOST', '127.0.0.1');
}
if (!defined('REDIS_PORT')) {
    define('REDIS_PORT', 6379);
}
if (!defined('REDIS_PASSWORD')) {
    define('REDIS_PASSWORD', '');
}

// Load configuration
if (file_exists(EPIC_CONFIG_DIR . '/config.php')) {
    require_once EPIC_CONFIG_DIR . '/config.php';
} else {
    die('Configuration file not found. Please run the installer.');
}

// Load database configuration FIRST
if (file_exists(EPIC_CONFIG_DIR . '/database.php')) {
    require_once EPIC_CONFIG_DIR . '/database.php';
} else {
    die('Database configuration file not found. Please run the installer.');
}

// Ensure $epic_db is available globally
global $epic_db;
if (!isset($epic_db) || !$epic_db) {
    try {
        $epic_db = db()->getConnection();
    } catch (Exception $e) {
        error_log('Bootstrap: Failed to initialize database connection: ' . $e->getMessage());
        // Continue loading but log the error
    }
}

// Load core functions
require_once EPIC_CORE_DIR . '/functions.php';

// Load CSRF protection (required for security)
if (file_exists(EPIC_CORE_DIR . '/csrf-protection.php')) {
    require_once EPIC_CORE_DIR . '/csrf-protection.php';
}

// Load function aliases for backward compatibility (AFTER core functions)
if (file_exists(EPIC_CORE_DIR . '/function-aliases.php')) {
    require_once EPIC_CORE_DIR . '/function-aliases.php';
}

// Load additional core files if they exist
if (file_exists(EPIC_CORE_DIR . '/auth.php')) {
    require_once EPIC_CORE_DIR . '/auth.php';
}
if (file_exists(EPIC_CORE_DIR . '/router.php')) {
    require_once EPIC_CORE_DIR . '/router.php';
}
if (file_exists(EPIC_CORE_DIR . '/template.php')) {
    require_once EPIC_CORE_DIR . '/template.php';
}
if (file_exists(EPIC_CORE_DIR . '/admin.php')) {
    require_once EPIC_CORE_DIR . '/admin.php';
}
if (file_exists(EPIC_CORE_DIR . '/dashboard.php')) {
    require_once EPIC_CORE_DIR . '/dashboard.php';
}
if (file_exists(EPIC_CORE_DIR . '/landing.php')) {
    require_once EPIC_CORE_DIR . '/landing.php';
}
if (file_exists(EPIC_CORE_DIR . '/autoresponder.php')) {
    require_once EPIC_CORE_DIR . '/autoresponder.php';
}
if (file_exists(EPIC_CORE_DIR . '/starsender-notifications.php')) {
    require_once EPIC_CORE_DIR . '/starsender-notifications.php';
}
if (file_exists(EPIC_CORE_DIR . '/starsender-triggers.php')) {
    require_once EPIC_CORE_DIR . '/starsender-triggers.php';
}
if (file_exists(EPIC_CORE_DIR . '/epis-functions.php')) {
    require_once EPIC_CORE_DIR . '/epis-functions.php';
}
if (file_exists(EPIC_CORE_DIR . '/monitoring.php')) {
    require_once EPIC_CORE_DIR . '/monitoring.php';
}

// Load Mailketing Integration
if (file_exists(EPIC_CORE_DIR . '/mailketing.php')) {
    require_once EPIC_CORE_DIR . '/mailketing.php';
}

// Zoom Integration removed

// Load Event Scheduling if available (AFTER database is initialized)
if (file_exists(EPIC_CORE_DIR . '/event-scheduling.php')) {
    try {
        require_once EPIC_CORE_DIR . '/event-scheduling.php';
    } catch (Exception $e) {
        error_log('Bootstrap: Failed to load event-scheduling.php: ' . $e->getMessage());
        // Continue loading but log the error
    }
}

// Initialize application
class EpicApp {
    private static $instance = null;
    private $settings = [];
    private $user = null;
    
    private function __construct() {
        $this->loadSettings();
        $this->loadCurrentUser();
        $this->setupErrorHandling();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadSettings() {
        try {
            // Check if database is available first
            if (function_exists('db') && function_exists('epic_get_all_settings')) {
                $this->settings = epic_get_all_settings();
            } else {
                throw new Exception('Database not available');
            }
        } catch (Exception $e) {
            // If settings table doesn't exist, use defaults
            $this->settings = [
                'site_name' => 'EPIC Hub',
                'site_description' => 'Modern Affiliate Marketing Platform',
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
                'default_commission_rate' => '10.00'
            ];
        }
    }
    
    private function loadCurrentUser() {
        if (epic_is_logged_in()) {
            $this->user = epic_current_user();
        }
    }
    
    private function setupErrorHandling() {
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }
    
    public function errorHandler($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error = [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->logError($error);
        
        if ($severity === E_ERROR || $severity === E_USER_ERROR) {
            $this->showErrorPage($error);
        }
        
        return true;
    }
    
    public function exceptionHandler($exception) {
        $error = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->logError($error);
        $this->showErrorPage($error);
    }
    
    private function logError($error) {
        $logFile = EPIC_ROOT . '/logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = '[' . $error['timestamp'] . '] ' . 
                   $error['message'] . ' in ' . 
                   $error['file'] . ' on line ' . 
                   $error['line'] . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    private function showErrorPage($error) {
        http_response_code(500);
        
        if (defined('EPIC_DEBUG') && EPIC_DEBUG) {
            echo '<h1>EPIC Hub Error</h1>';
            echo '<p><strong>Message:</strong> ' . htmlspecialchars($error['message']) . '</p>';
            echo '<p><strong>File:</strong> ' . htmlspecialchars($error['file']) . '</p>';
            echo '<p><strong>Line:</strong> ' . $error['line'] . '</p>';
            if (isset($error['trace'])) {
                echo '<h3>Stack Trace:</h3>';
                echo '<pre>' . htmlspecialchars($error['trace']) . '</pre>';
            }
        } else {
            echo '<h1>Something went wrong</h1>';
            echo '<p>We\'re sorry, but something went wrong. Please try again later.</p>';
        }
        
        exit;
    }
    
    public function getSetting($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }
    
    public function getUser() {
        return $this->user;
    }
    
    public function isInstalled() {
        try {
            return db()->selectValue("SELECT 1 FROM epic_users LIMIT 1") !== null;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function needsMigration() {
        try {
            // Check if old tables exist and new tables don't
            $oldExists = db()->selectValue("SHOW TABLES LIKE 'sa_member'") !== null;
            $newExists = db()->selectValue("SHOW TABLES LIKE 'epic_users'") !== null;
            
            return $oldExists && !$newExists;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Initialize the application
$epic = EpicApp::getInstance();

// Global helper functions
function epic() {
    return EpicApp::getInstance();
}

function epic_setting($key, $default = null) {
    return epic()->getSetting($key, $default);
}

function epic_user() {
    return epic()->getUser();
}

// Helper functions moved to core/functions.php to avoid duplication

function epic_csrf_token($action = 'default') {
    // Gunakan fungsi dari csrf-protection.php jika tersedia
    if (function_exists('epic_generate_csrf_token')) {
        return epic_generate_csrf_token($action);
    }
    
    // Fallback untuk kompatibilitas
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    
    if (!isset($_SESSION['csrf_tokens'][$action]) || 
        (time() - $_SESSION['csrf_tokens'][$action]['timestamp']) > 3600) {
        $_SESSION['csrf_tokens'][$action] = [
            'token' => bin2hex(random_bytes(32)),
            'timestamp' => time(),
            'used' => false
        ];
    }
    
    return $_SESSION['csrf_tokens'][$action]['token'];
}

// Legacy CSRF functions removed - using new implementation from csrf-protection.php


function epic_old($key, $default = '') {
    return $_SESSION['epic_old'][$key] ?? $default;
}

function epic_set_old($data) {
    $_SESSION['epic_old'] = $data;
}

function epic_clear_old() {
    unset($_SESSION['epic_old']);
}

/**
 * Get configuration value with dot notation support
 * @param string $key Configuration key (e.g., 'api.rate_limit')
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value
 */
function epic_config($key, $default = null) {
    // Handle dot notation (e.g., 'api.rate_limit')
    if (strpos($key, '.') !== false) {
        $parts = explode('.', $key);
        $config_key = strtoupper(implode('_', $parts));
        
        // Try to get from constants first
        if (defined($config_key)) {
            return constant($config_key);
        }
        
        // Try with EPIC_ prefix
        $epic_key = 'EPIC_' . $config_key;
        if (defined($epic_key)) {
            return constant($epic_key);
        }
        
        // Try to get from database settings
        try {
            $setting_key = str_replace('.', '_', $key);
            $value = epic_setting($setting_key, $default);
            if ($value !== $default) {
                return $value;
            }
        } catch (Exception $e) {
            // Ignore database errors during config lookup
        }
    } else {
        // Simple key lookup
        $config_key = strtoupper($key);
        
        // Try to get from constants first
        if (defined($config_key)) {
            return constant($config_key);
        }
        
        // Try with EPIC_ prefix
        $epic_key = 'EPIC_' . $config_key;
        if (defined($epic_key)) {
            return constant($epic_key);
        }
        
        // Try to get from database settings
        try {
            $value = epic_setting($key, $default);
            if ($value !== $default) {
                return $value;
            }
        } catch (Exception $e) {
            // Ignore database errors during config lookup
        }
    }
    
    return $default;
}

// Check if application needs installation or migration
if (!epic()->isInstalled()) {
    // Redirect to installer if not accessing installer pages
    if (!preg_match('/\/(install|migration)/', $_SERVER['REQUEST_URI'])) {
        epic_redirect(epic_url('install.php'));
    }
} elseif (epic()->needsMigration()) {
    // Redirect to migration if not accessing migration pages
    if (!preg_match('/\/(migration|install)/', $_SERVER['REQUEST_URI'])) {
        epic_redirect(epic_url('migration-script.php'));
    }
}

// Load theme functions if exists
$theme = epic_setting('theme', 'modern');
$theme_functions = EPIC_THEME_DIR . '/' . $theme . '/functions.php';
if (file_exists($theme_functions)) {
    require_once $theme_functions;
}

// Process referral tracking automatically on every page load
if (function_exists('epic_process_referral_tracking')) {
    epic_process_referral_tracking();
}

?>