<?php
/**
 * EPIC Hub Setup Script
 * Automated setup for EPIC Hub installation
 */

// Prevent running if already configured
if (file_exists(__DIR__ . '/.env') || (file_exists(__DIR__ . '/config/config.php') && filesize(__DIR__ . '/config/config.php') > 1000)) {
    die('EPIC Hub appears to be already configured. If you need to reconfigure, please delete .env file or config/config.php first.');
}

$step = $_GET['step'] ?? 1;
$error = null;
$success = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            $result = setup_environment();
            if ($result['success']) {
                $step = 2;
            } else {
                $error = $result['error'];
            }
            break;
            
        case 2:
            $result = setup_database();
            if ($result['success']) {
                $step = 3;
            } else {
                $error = $result['error'];
            }
            break;
            
        case 3:
            $result = setup_admin();
            if ($result['success']) {
                $step = 4;
            } else {
                $error = $result['error'];
            }
            break;
    }
}

/**
 * Setup environment configuration
 */
function setup_environment() {
    $app_url = rtrim($_POST['app_url'] ?? '', '/');
    $app_env = $_POST['app_env'] ?? 'production';
    $app_debug = $_POST['app_debug'] ?? 'false';
    $secret_key = $_POST['secret_key'] ?? bin2hex(random_bytes(32));
    
    if (empty($app_url)) {
        return ['success' => false, 'error' => 'Application URL is required.'];
    }
    
    // Create .env file
    $env_content = create_env_content([
        'APP_URL' => $app_url,
        'APP_ENV' => $app_env,
        'APP_DEBUG' => $app_debug,
        'APP_SECRET' => $secret_key
    ]);
    
    if (!file_put_contents(__DIR__ . '/.env', $env_content)) {
        return ['success' => false, 'error' => 'Could not create .env file. Check permissions.'];
    }
    
    return ['success' => true];
}

/**
 * Setup database configuration
 */
function setup_database() {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_password = $_POST['db_password'] ?? '';
    
    if (empty($db_name) || empty($db_user)) {
        return ['success' => false, 'error' => 'Database name and username are required.'];
    }
    
    try {
        // Test database connection
        $pdo = new PDO("mysql:host={$db_host};charset=utf8mb4", $db_user, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$db_name}`");
        
        // Run database schema
        $schema_file = __DIR__ . '/epic-database-schema.sql';
        if (file_exists($schema_file)) {
            $sql = file_get_contents($schema_file);
            $statements = explode(';', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^(--|#)/', $statement)) {
                    $pdo->exec($statement);
                }
            }
        }
        
        // Update .env with database settings
        update_env_file([
            'DB_HOST' => $db_host,
            'DB_NAME' => $db_name,
            'DB_USERNAME' => $db_user,
            'DB_PASSWORD' => $db_password
        ]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Setup admin account
 */
function setup_admin() {
    $name = $_POST['admin_name'] ?? '';
    $email = $_POST['admin_email'] ?? '';
    $password = $_POST['admin_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        return ['success' => false, 'error' => 'All admin fields are required.'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Please enter a valid email address.'];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'error' => 'Password must be at least 8 characters long.'];
    }
    
    try {
        // Load environment
        load_env();
        
        // Connect to database
        $pdo = new PDO(
            "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD']
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
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Setup error: ' . $e->getMessage()];
    }
}

/**
 * Create .env file content
 */
function create_env_content($config) {
    $template = file_get_contents(__DIR__ . '/.env.example');
    
    foreach ($config as $key => $value) {
        $template = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $template);
    }
    
    return $template;
}

/**
 * Update .env file
 */
function update_env_file($config) {
    $env_file = __DIR__ . '/.env';
    $content = file_get_contents($env_file);
    
    foreach ($config as $key => $value) {
        $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
    }
    
    file_put_contents($env_file, $content);
}

/**
 * Load environment variables
 */
function load_env() {
    $env_file = __DIR__ . '/.env';
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
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
    <title>EPIC Hub Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">EPIC Hub Setup</h1>
                <p class="text-gray-600">Configure your EPIC Hub installation</p>
            </div>
            
            <!-- Progress Steps -->
            <div class="flex justify-center mb-8">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full <?= $step >= 1 ? 'bg-blue-500 text-white' : 'bg-gray-300' ?> flex items-center justify-center text-sm font-semibold">1</div>
                        <span class="ml-2 text-sm <?= $step >= 1 ? 'text-blue-600' : 'text-gray-500' ?>">Environment</span>
                    </div>
                    <div class="w-8 h-0.5 <?= $step >= 2 ? 'bg-blue-500' : 'bg-gray-300' ?>"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full <?= $step >= 2 ? 'bg-blue-500 text-white' : 'bg-gray-300' ?> flex items-center justify-center text-sm font-semibold">2</div>
                        <span class="ml-2 text-sm <?= $step >= 2 ? 'text-blue-600' : 'text-gray-500' ?>">Database</span>
                    </div>
                    <div class="w-8 h-0.5 <?= $step >= 3 ? 'bg-blue-500' : 'bg-gray-300' ?>"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full <?= $step >= 3 ? 'bg-blue-500 text-white' : 'bg-gray-300' ?> flex items-center justify-center text-sm font-semibold">3</div>
                        <span class="ml-2 text-sm <?= $step >= 3 ? 'text-blue-600' : 'text-gray-500' ?>">Admin</span>
                    </div>
                    <div class="w-8 h-0.5 <?= $step >= 4 ? 'bg-blue-500' : 'bg-gray-300' ?>"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full <?= $step >= 4 ? 'bg-green-500 text-white' : 'bg-gray-300' ?> flex items-center justify-center text-sm font-semibold">✓</div>
                        <span class="ml-2 text-sm <?= $step >= 4 ? 'text-green-600' : 'text-gray-500' ?>">Complete</span>
                    </div>
                </div>
            </div>
            
            <!-- Setup Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($step == 1): ?>
                    <h2 class="text-2xl font-semibold mb-6">Environment Configuration</h2>
                    <form method="post" action="?step=1">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Application URL *</label>
                            <input type="url" name="app_url" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="<?= htmlspecialchars($_POST['app_url'] ?? 'http://localhost/epichub') ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
                            <select name="app_env" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="production" <?= ($_POST['app_env'] ?? 'production') === 'production' ? 'selected' : '' ?>>Production</option>
                                <option value="development" <?= ($_POST['app_env'] ?? '') === 'development' ? 'selected' : '' ?>>Development</option>
                            </select>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Debug Mode</label>
                            <select name="app_debug" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="false" <?= ($_POST['app_debug'] ?? 'false') === 'false' ? 'selected' : '' ?>>Disabled (Recommended)</option>
                                <option value="true" <?= ($_POST['app_debug'] ?? '') === 'true' ? 'selected' : '' ?>>Enabled</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Continue to Database Setup
                        </button>
                    </form>
                    
                <?php elseif ($step == 2): ?>
                    <h2 class="text-2xl font-semibold mb-6">Database Configuration</h2>
                    <form method="post" action="?step=2">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Host</label>
                            <input type="text" name="db_host" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Name *</label>
                            <input type="text" name="db_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="<?= htmlspecialchars($_POST['db_name'] ?? 'epic_hub') ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Username *</label>
                            <input type="text" name="db_user" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="<?= htmlspecialchars($_POST['db_user'] ?? 'root') ?>">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Password</label>
                            <input type="password" name="db_password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="<?= htmlspecialchars($_POST['db_password'] ?? '') ?>">
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Setup Database
                        </button>
                    </form>
                    
                <?php elseif ($step == 3): ?>
                    <h2 class="text-2xl font-semibold mb-6">Admin Account</h2>
                    <form method="post" action="?step=3">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="admin_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" name="admin_email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                            <input type="password" name="admin_password" required minlength="8" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Minimum 8 characters</p>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Create Admin Account
                        </button>
                    </form>
                    
                <?php elseif ($step == 4): ?>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        
                        <h2 class="text-2xl font-semibold text-green-600 mb-4">Setup Complete!</h2>
                        <p class="text-gray-600 mb-6">EPIC Hub has been successfully configured and is ready to use.</p>
                        
                        <div class="space-y-3">
                            <a href="index.php" class="block w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 text-center">
                                Go to Website
                            </a>
                            <a href="admin" class="block w-full bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 text-center">
                                Go to Admin Panel
                            </a>
                        </div>
                        
                        <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-md text-left">
                            <h3 class="font-semibold text-yellow-800 mb-2">Important Security Notes:</h3>
                            <ul class="text-sm text-yellow-700 space-y-1">
                                <li>• Delete or rename this setup.php file</li>
                                <li>• Set proper file permissions (644 for files, 755 for directories)</li>
                                <li>• Configure SSL/HTTPS for production use</li>
                                <li>• Regularly backup your database</li>
                                <li>• Keep your system updated</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>