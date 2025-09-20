<?php
/**
 * EPIC Hub Installation Script
 * Modern installation process for EPIC Hub
 */

// Prevent running if already installed
if (file_exists(__DIR__ . '/config/config.php')) {
    $config_content = file_get_contents(__DIR__ . '/config/config.php');
    if (strpos($config_content, 'your-secret-key-here-change-this-in-production') === false) {
        die('EPIC Hub is already installed. If you need to reinstall, please delete the config file first.');
    }
}

// Start session
session_start();

// Set error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$step = $_GET['step'] ?? 1;
$error = null;
$success = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2:
            $result = handle_requirements_check();
            if ($result['success']) {
                $step = 3;
            } else {
                $error = $result['error'];
            }
            break;
            
        case 3:
            $result = handle_database_setup();
            if ($result['success']) {
                $step = 4;
            } else {
                $error = $result['error'];
            }
            break;
            
        case 4:
            $result = handle_admin_setup();
            if ($result['success']) {
                $step = 5;
            } else {
                $error = $result['error'];
            }
            break;
    }
}

/**
 * Handle requirements check
 */
function handle_requirements_check() {
    $requirements = check_requirements();
    
    foreach ($requirements as $req) {
        if (!$req['status']) {
            return ['success' => false, 'error' => 'System requirements not met: ' . $req['name']];
        }
    }
    
    return ['success' => true];
}

/**
 * Handle database setup
 */
function handle_database_setup() {
    $host = $_POST['db_host'] ?? 'localhost';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $password = $_POST['db_password'] ?? '';
    $site_url = rtrim($_POST['site_url'] ?? '', '/');
    
    if (empty($name) || empty($user) || empty($site_url)) {
        return ['success' => false, 'error' => 'Please fill in all required fields.'];
    }
    
    try {
        // Test database connection
        $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$name}`");
        
        // Create database schema
        $schema_file = __DIR__ . '/epic-database-schema.sql';
        if (!file_exists($schema_file)) {
            return ['success' => false, 'error' => 'Database schema file not found.'];
        }
        
        $sql = file_get_contents($schema_file);
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^(--|#)/', $statement)) {
                $pdo->exec($statement);
            }
        }
        
        // Create configuration file
        $config_content = create_config_file($host, $name, $user, $password, $site_url);
        
        if (!file_put_contents(__DIR__ . '/config/config.php', $config_content)) {
            return ['success' => false, 'error' => 'Could not write configuration file.'];
        }
        
        // Store database connection for next step
        $_SESSION['install_db'] = [
            'host' => $host,
            'name' => $name,
            'user' => $user,
            'password' => $password
        ];
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Handle admin setup
 */
function handle_admin_setup() {
    $name = $_POST['admin_name'] ?? '';
    $email = $_POST['admin_email'] ?? '';
    $password = $_POST['admin_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        return ['success' => false, 'error' => 'Please fill in all fields.'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Please enter a valid email address.'];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'error' => 'Password must be at least 8 characters long.'];
    }
    
    if ($password !== $confirm_password) {
        return ['success' => false, 'error' => 'Passwords do not match.'];
    }
    
    try {
        // Connect to database
        $db_config = $_SESSION['install_db'];
        $pdo = new PDO(
            "mysql:host={$db_config['host']};dbname={$db_config['name']};charset=utf8mb4",
            $db_config['user'],
            $db_config['password']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Generate UUID and referral code
        $uuid = generate_uuid();
        $referral_code = 'ADMIN001';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Create admin user
        $stmt = $pdo->prepare(
            "INSERT INTO epic_users (uuid, name, email, password, referral_code, status, role, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, 'active', 'super_admin', NOW(), NOW())"
        );
        
        $stmt->execute([$uuid, $name, $email, $hashed_password, $referral_code]);
        $admin_id = $pdo->lastInsertId();
        
        // Create referral record
        $stmt = $pdo->prepare(
            "INSERT INTO epic_referrals (user_id, referrer_id, status, created_at, updated_at) 
             VALUES (?, NULL, 'active', NOW(), NOW())"
        );
        $stmt->execute([$admin_id]);
        
        // Insert default settings
        $default_settings = [
            ['site_name', 'EPIC Hub', 'string', 'general', 'Website name'],
            ['site_description', 'Modern Affiliate Marketing Platform', 'string', 'general', 'Website description'],
            ['currency', 'IDR', 'string', 'general', 'Default currency'],
            ['timezone', 'Asia/Jakarta', 'string', 'general', 'Default timezone'],
            ['default_commission_rate', '10.00', 'string', 'affiliate', 'Default commission rate percentage'],
            ['email_from_name', 'EPIC Hub', 'string', 'email', 'Email sender name'],
            ['email_from_address', $email, 'string', 'email', 'Email sender address'],
            ['theme', 'modern', 'string', 'appearance', 'Active theme']
        ];
        
        $stmt = $pdo->prepare(
            "INSERT INTO epic_settings (`key`, `value`, `type`, `group`, `description`) VALUES (?, ?, ?, ?, ?)"
        );
        
        foreach ($default_settings as $setting) {
            $stmt->execute($setting);
        }
        
        // Create sample product
        $product_uuid = generate_uuid();
        $stmt = $pdo->prepare(
            "INSERT INTO epic_products (uuid, name, slug, description, short_description, price, commission_type, commission_value, status, created_at, updated_at)
             VALUES (?, 'Sample Product', 'sample-product', 'This is a sample product for testing purposes.', 'Sample product for testing', 100000.00, 'percentage', 10.00, 'active', NOW(), NOW())"
        );
        $stmt->execute([$product_uuid]);
        
        // Clean up session
        unset($_SESSION['install_db']);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Setup error: ' . $e->getMessage()];
    }
}

/**
 * Check system requirements
 */
function check_requirements() {
    return [
        [
            'name' => 'PHP Version (>= 7.4)',
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
        ],
        [
            'name' => 'PDO Extension',
            'status' => extension_loaded('pdo')
        ],
        [
            'name' => 'PDO MySQL Extension',
            'status' => extension_loaded('pdo_mysql')
        ],
        [
            'name' => 'JSON Extension',
            'status' => extension_loaded('json')
        ],
        [
            'name' => 'cURL Extension',
            'status' => extension_loaded('curl')
        ],
        [
            'name' => 'OpenSSL Extension',
            'status' => extension_loaded('openssl')
        ],
        [
            'name' => 'Config Directory Writable',
            'status' => is_writable(__DIR__ . '/config')
        ],
        [
            'name' => 'Uploads Directory Writable',
            'status' => is_dir(__DIR__ . '/uploads') && is_writable(__DIR__ . '/uploads')
        ]
    ];
}

/**
 * Create configuration file content
 */
function create_config_file($host, $name, $user, $password, $site_url) {
    $secret = bin2hex(random_bytes(32));
    
    return "<?php
/**
 * EPIC Hub Configuration File
 * Generated during installation
 */

if (!defined('EPIC_INIT')) {
    define('EPIC_INIT', true);
}

// Website URL (with trailing slash)
\$weburl = '{$site_url}/';

// Database Configuration
\$dbhost = '{$host}';
\$dbname = '{$name}';
\$dbuser = '{$user}';
\$dbpassword = '{$password}';

// Security
define('SECRET', '{$secret}');
define('EPIC_DEBUG', false);

// Application Settings
define('EPIC_TIMEZONE', 'Asia/Jakarta');
define('EPIC_LOCALE', 'id_ID');
define('EPIC_CURRENCY', 'IDR');

// File Upload Settings
define('EPIC_MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('EPIC_ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,zip');

// Session Settings
define('EPIC_SESSION_LIFETIME', 3600 * 24 * 7); // 7 days
define('EPIC_SESSION_NAME', 'epic_session');

// Cache Settings
define('EPIC_CACHE_ENABLED', true);
define('EPIC_CACHE_LIFETIME', 3600); // 1 hour

// Email Settings
define('EPIC_MAIL_FROM_NAME', 'EPIC Hub');
define('EPIC_MAIL_FROM_EMAIL', 'noreply@epichub.local');
define('EPIC_MAIL_SMTP_HOST', 'localhost');
define('EPIC_MAIL_SMTP_PORT', 587);
define('EPIC_MAIL_SMTP_USERNAME', '');
define('EPIC_MAIL_SMTP_PASSWORD', '');
define('EPIC_MAIL_SMTP_ENCRYPTION', 'tls');

// API Settings
define('EPIC_API_ENABLED', true);
define('EPIC_API_RATE_LIMIT', 100);
define('EPIC_API_VERSION', 'v1');

// Security Settings
define('EPIC_CSRF_ENABLED', true);
define('EPIC_PASSWORD_MIN_LENGTH', 8);
define('EPIC_LOGIN_ATTEMPTS_LIMIT', 5);
define('EPIC_LOGIN_LOCKOUT_TIME', 900);

// Affiliate Settings
define('EPIC_DEFAULT_COMMISSION_RATE', 10.00);
define('EPIC_MIN_WITHDRAWAL_AMOUNT', 100000);
define('EPIC_REFERRAL_CODE_LENGTH', 8);

// Environment Detection
\$current_domain = \$_SERVER['HTTP_HOST'] ?? 'localhost';

if (strpos(\$current_domain, 'localhost') !== false || 
    strpos(\$current_domain, '127.0.0.1') !== false ||
    strpos(\$current_domain, '.local') !== false) {
    define('EPIC_ENVIRONMENT', 'development');
    define('EPIC_DEBUG', true);
} else {
    define('EPIC_ENVIRONMENT', 'production');
    define('EPIC_DEBUG', false);
}

// Feature Flags
define('EPIC_FEATURE_REGISTRATION', true);
define('EPIC_FEATURE_REFERRALS', true);
define('EPIC_FEATURE_COMMISSIONS', true);
define('EPIC_FEATURE_ANALYTICS', true);
define('EPIC_FEATURE_NOTIFICATIONS', true);
define('EPIC_FEATURE_API', true);
define('EPIC_FEATURE_BLOG', true);

// Set timezone
date_default_timezone_set(EPIC_TIMEZONE);

?>";
}

/**
 * Generate UUID v4
 */
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPIC Hub Installation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .content {
            padding: 40px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            position: relative;
        }
        
        .step.active {
            background: #667eea;
            color: white;
        }
        
        .step.completed {
            background: #10b981;
            color: white;
        }
        
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            width: 20px;
            height: 2px;
            background: #e2e8f0;
            transform: translateY(-50%);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        
        .requirements {
            list-style: none;
        }
        
        .requirements li {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status.pass {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .status.fail {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .success-message {
            text-align: center;
            padding: 40px 20px;
        }
        
        .success-message h2 {
            color: #16a34a;
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        .success-message p {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .btn-secondary {
            background: #6b7280;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EPIC Hub</h1>
            <p>Modern Affiliate Marketing Platform</p>
        </div>
        
        <div class="content">
            <div class="step-indicator">
                <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">1</div>
                <div class="step <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>">2</div>
                <div class="step <?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' ?>">3</div>
                <div class="step <?= $step >= 4 ? ($step > 4 ? 'completed' : 'active') : '' ?>">4</div>
                <div class="step <?= $step >= 5 ? 'active' : '' ?>">5</div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <h2>Welcome to EPIC Hub Installation</h2>
                <p>This installer will guide you through setting up your EPIC Hub platform. Please make sure you have:</p>
                <ul style="margin: 20px 0; padding-left: 20px;">
                    <li>MySQL database credentials</li>
                    <li>Write permissions on the config directory</li>
                    <li>PHP 7.4 or higher</li>
                </ul>
                <form method="post" action="?step=2">
                    <button type="submit" class="btn">Start Installation</button>
                </form>
                
            <?php elseif ($step == 2): ?>
                <h2>System Requirements</h2>
                <p>Checking your server configuration...</p>
                
                <ul class="requirements">
                    <?php foreach (check_requirements() as $req): ?>
                        <li>
                            <span><?= $req['name'] ?></span>
                            <span class="status <?= $req['status'] ? 'pass' : 'fail' ?>">
                                <?= $req['status'] ? 'PASS' : 'FAIL' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <form method="post" action="?step=2">
                    <button type="submit" class="btn">Continue</button>
                </form>
                
            <?php elseif ($step == 3): ?>
                <h2>Database Configuration</h2>
                <p>Please enter your database connection details:</p>
                
                <form method="post" action="?step=3">
                    <div class="form-group">
                        <label for="site_url">Site URL *</label>
                        <input type="url" id="site_url" name="site_url" required 
                               value="<?= htmlspecialchars($_POST['site_url'] ?? 'http://localhost/epichub') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" 
                               value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Database Name *</label>
                        <input type="text" id="db_name" name="db_name" required 
                               value="<?= htmlspecialchars($_POST['db_name'] ?? 'epic_hub') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="db_user">Database Username *</label>
                        <input type="text" id="db_user" name="db_user" required 
                               value="<?= htmlspecialchars($_POST['db_user'] ?? 'root') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="db_password">Database Password</label>
                        <input type="password" id="db_password" name="db_password" 
                               value="<?= htmlspecialchars($_POST['db_password'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn">Setup Database</button>
                </form>
                
            <?php elseif ($step == 4): ?>
                <h2>Admin Account</h2>
                <p>Create your administrator account:</p>
                
                <form method="post" action="?step=4">
                    <div class="form-group">
                        <label for="admin_name">Full Name *</label>
                        <input type="text" id="admin_name" name="admin_name" required 
                               value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">Email Address *</label>
                        <input type="email" id="admin_email" name="admin_email" required 
                               value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">Password *</label>
                        <input type="password" id="admin_password" name="admin_password" required 
                               minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               minlength="8">
                    </div>
                    
                    <button type="submit" class="btn">Create Admin Account</button>
                </form>
                
            <?php elseif ($step == 5): ?>
                <div class="success-message">
                    <h2>🎉 Installation Complete!</h2>
                    <p>EPIC Hub has been successfully installed and configured.</p>
                    
                    <a href="index.php" class="btn">Go to Homepage</a>
                    <a href="admin" class="btn btn-secondary">Go to Admin Panel</a>
                    
                    <div style="margin-top: 30px; padding: 20px; background: #f8fafc; border-radius: 8px; text-align: left;">
                        <h3>Important Security Notes:</h3>
                        <ul style="margin-top: 10px; padding-left: 20px;">
                            <li>Delete or rename this install.php file</li>
                            <li>Set proper file permissions (644 for files, 755 for directories)</li>
                            <li>Configure SSL/HTTPS for production use</li>
                            <li>Regularly backup your database</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>