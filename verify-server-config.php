<?php
/**
 * Server Configuration Verification Script
 * Verifies database connection and environment configuration for production server
 */

// Allow direct access for this verification script
define('EPIC_LOADED', true);
define('EPIC_INIT', true);

// Start session
session_start();

echo "<h1>Server Configuration Verification</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}</style>\n";

// 1. Check if .env file exists and load it
echo "<h2>1. Environment Configuration</h2>\n";
if (file_exists(__DIR__ . '/.env')) {
    echo "<p class='success'>✅ .env file found</p>\n";
    
    // Load .env manually
    $env_lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env_vars = [];
    foreach ($env_lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                $env_vars[$key] = $value;
                if (!defined($key)) {
                    define($key, $value);
                }
            }
        }
    }
    
    echo "<p class='info'>📋 Environment variables loaded:</p>\n";
    echo "<ul>\n";
    foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'SITE_URL', 'ENVIRONMENT'] as $key) {
        $value = $env_vars[$key] ?? 'Not set';
        if ($key === 'DB_PASS') $value = '***hidden***';
        echo "<li><strong>$key:</strong> $value</li>\n";
    }
    echo "</ul>\n";
} else {
    echo "<p class='error'>❌ .env file not found</p>\n";
    echo "<p class='warning'>⚠️ Using default configuration</p>\n";
}

// 2. Test database connection
echo "<h2>2. Database Connection Test</h2>\n";

try {
    // Load database configuration
    require_once __DIR__ . '/config/database.php';
    
    echo "<p class='info'>📋 Database Configuration:</p>\n";
    echo "<ul>\n";
    echo "<li><strong>Host:</strong> " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "</li>\n";
    echo "<li><strong>Database:</strong> " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "</li>\n";
    echo "<li><strong>User:</strong> " . (defined('DB_USER') ? DB_USER : 'Not defined') . "</li>\n";
    echo "<li><strong>Prefix:</strong> " . (defined('DB_PREFIX') ? DB_PREFIX : 'Not defined') . "</li>\n";
    echo "</ul>\n";
    
    // Test connection
    $db = db();
    $connection = $db->getConnection();
    
    if ($connection) {
        echo "<p class='success'>✅ Database connection successful</p>\n";
        
        // Test a simple query
        $stmt = $connection->query("SELECT 1 as test");
        if ($stmt) {
            echo "<p class='success'>✅ Database query test successful</p>\n";
        } else {
            echo "<p class='error'>❌ Database query test failed</p>\n";
        }
        
        // Check if epic_users table exists
        $stmt = $connection->prepare("SHOW TABLES LIKE 'epic_users'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✅ epic_users table exists</p>\n";
            
            // Count users
            $stmt = $connection->query("SELECT COUNT(*) as count FROM epic_users");
            $result = $stmt->fetch();
            echo "<p class='info'>📊 Total users: " . $result['count'] . "</p>\n";
        } else {
            echo "<p class='warning'>⚠️ epic_users table not found</p>\n";
        }
        
    } else {
        echo "<p class='error'>❌ Database connection failed</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// 3. Integration Test
echo "<h2>3. Integration Test</h2>\n";
echo "<p class='info'>ℹ️ Zoom Integration has been removed from the system</p>\n";

// 4. Test URL Configuration
echo "<h2>4. URL Configuration Test</h2>\n";

try {
    require_once __DIR__ . '/core/functions.php';
    
    $base_url = epic_url();
    echo "<p class='info'>🌐 Base URL: " . htmlspecialchars($base_url) . "</p>\n";
    
    if (strpos($base_url, 'localhost') !== false) {
        echo "<p class='warning'>⚠️ URL still contains 'localhost' - may need to update SITE_URL in .env</p>\n";
    } else {
        echo "<p class='success'>✅ URL configuration looks good for production</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ URL configuration error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// 5. Server Environment Check
echo "<h2>5. Server Environment</h2>\n";

echo "<p class='info'>📋 Server Information:</p>\n";
echo "<ul>\n";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>\n";
echo "<li><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>\n";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</li>\n";
echo "<li><strong>Script Path:</strong> " . __DIR__ . "</li>\n";
echo "<li><strong>Current Time:</strong> " . date('Y-m-d H:i:s T') . "</li>\n";
echo "</ul>\n";

// 6. Recommendations
echo "<h2>6. Recommendations</h2>\n";

if (defined('SITE_URL') && strpos(SITE_URL, 'localhost') !== false) {
    echo "<p class='warning'>⚠️ Update SITE_URL in .env file to your actual domain</p>\n";
}

if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    echo "<p class='warning'>⚠️ Set ENVIRONMENT=production in .env file</p>\n";
}

if (defined('DEBUG_MODE') && DEBUG_MODE === 'true') {
    echo "<p class='warning'>⚠️ Set DEBUG_MODE=false in .env file for production</p>\n";
}

echo "<p class='info'>✅ Verification completed at " . date('Y-m-d H:i:s T') . "</p>\n";
?>