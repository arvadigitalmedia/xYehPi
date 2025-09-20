<?php
/**
 * Server Configuration Fix Script
 * Updates .env file for production server deployment
 */

// Allow direct access for this fix script
define('EPIC_LOADED', true);

echo "<h1>Server Configuration Fix</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}</style>\n";

// Get server information
$server_name = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
$protocol = $is_https ? 'https' : 'http';
$detected_url = $protocol . '://' . $server_name;

echo "<p class='info'>🔍 Detected server URL: <strong>$detected_url</strong></p>\n";

// Check if this is a form submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_config') {
    
    $site_url = trim($_POST['site_url'] ?? '');
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');
    
    if (empty($site_url) || empty($db_name) || empty($db_user)) {
        echo "<p class='error'>❌ Please fill in all required fields</p>\n";
    } else {
        
        // Create new .env content
        $env_content = "# EPIC Hub Environment Configuration\n";
        $env_content .= "# Production Configuration for Server\n\n";
        
        $env_content .= "# Environment\n";
        $env_content .= "ENVIRONMENT=production\n";
        $env_content .= "DEBUG_MODE=false\n\n";
        
        $env_content .= "# Database Configuration (Server)\n";
        $env_content .= "DB_HOST=$db_host\n";
        $env_content .= "DB_NAME=$db_name\n";
        $env_content .= "DB_USER=$db_user\n";
        $env_content .= "DB_PASS=$db_pass\n";
        $env_content .= "DB_PREFIX=epic_\n\n";
        
        $env_content .= "# Site Configuration\n";
        $env_content .= "SITE_URL=$site_url\n";
        $env_content .= "SITE_NAME=EPI Hub - Bisnis Emas Perak Indonesia\n";
        $env_content .= "SITE_DESCRIPTION=Modern Support System Platform\n\n";
        
        $env_content .= "# Security Keys (Generate new untuk production)\n";
        $env_content .= "ENCRYPTION_KEY=" . bin2hex(random_bytes(16)) . "\n";
        $env_content .= "JWT_SECRET=" . bin2hex(random_bytes(32)) . "\n";
        $env_content .= "SECURITY_SALT=" . bin2hex(random_bytes(16)) . "\n";
        $env_content .= "SESSION_SECRET=" . bin2hex(random_bytes(32)) . "\n\n";
        
        $env_content .= "# Email Configuration (cPanel)\n";
        $env_content .= "SMTP_HOST=mail." . parse_url($site_url, PHP_URL_HOST) . "\n";
        $env_content .= "SMTP_PORT=587\n";
        $env_content .= "SMTP_USER=noreply@" . parse_url($site_url, PHP_URL_HOST) . "\n";
        $env_content .= "SMTP_PASS=YOUR_EMAIL_PASSWORD\n";
        $env_content .= "SMTP_FROM=noreply@" . parse_url($site_url, PHP_URL_HOST) . "\n";
        $env_content .= "SMTP_FROM_NAME=EPI Hub\n\n";
        
        $env_content .= "# Mailketing API Configuration\n";
        $env_content .= "MAILKETING_ENABLED=true\n";
        $env_content .= "MAILKETING_API_URL=https://api.mailketing.co.id/api/v1/send\n";
        $env_content .= "MAILKETING_API_TOKEN=277b5a7d945847177b5c67dfe91838ba\n";
        $env_content .= "MAILKETING_FROM_NAME=Admin " . parse_url($site_url, PHP_URL_HOST) . "\n";
        $env_content .= "MAILKETING_FROM_EMAIL=email@" . parse_url($site_url, PHP_URL_HOST) . "\n\n";
        
        // Zoom Integration removed
        
        $env_content .= "# Feature Flags\n";
        $env_content .= "EPIC_FEATURE_REGISTRATION=true\n";
        $env_content .= "EPIC_FEATURE_REFERRALS=true\n";
        $env_content .= "EPIC_FEATURE_COMMISSIONS=true\n";
        $env_content .= "EPIC_FEATURE_ANALYTICS=true\n";
        $env_content .= "EPIC_FEATURE_API=true\n";
        $env_content .= "EPIC_FEATURE_BLOG=true\n\n";
        
        $env_content .= "# File Upload\n";
        $env_content .= "MAX_UPLOAD_SIZE=5242880\n";
        $env_content .= "UPLOAD_PATH=uploads/\n";
        
        // Backup existing .env
        if (file_exists(__DIR__ . '/.env')) {
            $backup_name = '.env.backup.' . date('Y-m-d-H-i-s');
            copy(__DIR__ . '/.env', __DIR__ . '/' . $backup_name);
            echo "<p class='info'>📋 Existing .env backed up as: $backup_name</p>\n";
        }
        
        // Write new .env file
        if (file_put_contents(__DIR__ . '/.env', $env_content)) {
            echo "<p class='success'>✅ .env file updated successfully!</p>\n";
            echo "<p class='info'>🔄 Please test the configuration using <a href='verify-server-config.php'>verify-server-config.php</a></p>\n";
            
            // Test database connection
            echo "<h3>Testing Database Connection...</h3>\n";
            try {
                $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
                $pdo = new PDO($dsn, $db_user, $db_pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                
                $stmt = $pdo->query("SELECT 1");
                if ($stmt) {
                    echo "<p class='success'>✅ Database connection test successful!</p>\n";
                } else {
                    echo "<p class='error'>❌ Database connection test failed</p>\n";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Database connection error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
            }
            
        } else {
            echo "<p class='error'>❌ Failed to write .env file. Check file permissions.</p>\n";
        }
    }
    
} else {
    
    // Load current .env if exists
    $current_env = [];
    if (file_exists(__DIR__ . '/.env')) {
        $env_lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($env_lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $current_env[trim($key)] = trim($value);
            }
        }
    }
    
    // Show configuration form
    echo "<h2>Update Server Configuration</h2>\n";
    echo "<form method='post'>\n";
    echo "<input type='hidden' name='action' value='update_config'>\n";
    
    echo "<table style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Site URL:</strong></td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><input type='url' name='site_url' value='" . ($current_env['SITE_URL'] ?? $detected_url) . "' style='width: 100%; padding: 5px;' required></td></tr>\n";
    
    echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Database Host:</strong></td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><input type='text' name='db_host' value='" . ($current_env['DB_HOST'] ?? 'localhost') . "' style='width: 100%; padding: 5px;' required></td></tr>\n";
    
    echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Database Name:</strong></td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><input type='text' name='db_name' value='" . ($current_env['DB_NAME'] ?? 'bustanu1_ujicoba') . "' style='width: 100%; padding: 5px;' required></td></tr>\n";
    
    echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Database User:</strong></td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><input type='text' name='db_user' value='" . ($current_env['DB_USER'] ?? 'bustanu1_ujicoba') . "' style='width: 100%; padding: 5px;' required></td></tr>\n";
    
    echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Database Password:</strong></td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><input type='password' name='db_pass' value='" . ($current_env['DB_PASS'] ?? '') . "' style='width: 100%; padding: 5px;' placeholder='Enter database password'></td></tr>\n";
    
    echo "</table>\n";
    
    echo "<br><button type='submit' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Update Configuration</button>\n";
    echo "</form>\n";
    
    echo "<h3>Current Configuration:</h3>\n";
    if (!empty($current_env)) {
        echo "<ul>\n";
        foreach (['SITE_URL', 'DB_HOST', 'DB_NAME', 'DB_USER', 'ENVIRONMENT', 'DEBUG_MODE'] as $key) {
            $value = $current_env[$key] ?? 'Not set';
            if ($key === 'DB_PASS') $value = '***hidden***';
            echo "<li><strong>$key:</strong> $value</li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p class='warning'>⚠️ No .env file found</p>\n";
    }
}

echo "<hr>\n";
echo "<p><a href='verify-server-config.php'>🔍 Verify Configuration</a> | <a href='index.php'>🏠 Go to Homepage</a></p>\n";
?>