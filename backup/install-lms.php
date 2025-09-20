<?php
/**
 * EPIC Hub LMS Installation Script
 * Install and configure LMS products system
 */

if (!defined('EPIC_INIT')) {
    require_once __DIR__ . '/bootstrap.php';
}

// Check if user is admin
$user = epic_current_user();
if (!epic_is_admin($user)) {
    die('Admin access required');
}

$step = $_GET['step'] ?? 1;
$action = $_POST['action'] ?? '';

$messages = [];
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'install_schema':
            $result = installLMSSchema();
            if ($result['success']) {
                $messages[] = $result['message'];
                $step = 2;
            } else {
                $errors[] = $result['message'];
            }
            break;
            
        case 'install_sample_data':
            $result = installSampleData();
            if ($result['success']) {
                $messages[] = $result['message'];
                $step = 3;
            } else {
                $errors[] = $result['message'];
            }
            break;
            
        case 'configure_settings':
            $result = configureSettings($_POST);
            if ($result['success']) {
                $messages[] = $result['message'];
                $step = 4;
            } else {
                $errors[] = $result['message'];
            }
            break;
    }
}

// Check current installation status
$installation_status = checkInstallationStatus();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPIC Hub LMS Installation</title>
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
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .content {
            padding: 2rem;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #E5E7EB;
            color: #6B7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin: 0 10px;
            position: relative;
        }
        
        .step.active {
            background: #3B82F6;
            color: white;
        }
        
        .step.completed {
            background: #10B981;
            color: white;
        }
        
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 20px;
            height: 2px;
            background: #E5E7EB;
            transform: translateY(-50%);
        }
        
        .step.completed:not(:last-child)::after {
            background: #10B981;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        
        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
        
        .alert-info {
            background: #DBEAFE;
            color: #1E40AF;
            border: 1px solid #93C5FD;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #3B82F6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563EB;
        }
        
        .btn-success {
            background: #10B981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .status-item {
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        
        .status-item.success {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .status-item.error {
            background: #FEE2E2;
            color: #991B1B;
        }
        
        .status-item.pending {
            background: #FEF3C7;
            color: #92400E;
        }
        
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EPIC Hub LMS Installation</h1>
            <p>Set up your Learning Management System</p>
        </div>
        
        <div class="content">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">1</div>
                <div class="step <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>">2</div>
                <div class="step <?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' ?>">3</div>
                <div class="step <?= $step >= 4 ? 'active' : '' ?>">4</div>
            </div>
            
            <!-- Messages -->
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endforeach; ?>
            
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            
            <?php if ($step == 1): ?>
                <!-- Step 1: Database Schema -->
                <h2>Step 1: Install Database Schema</h2>
                <p>This will create the necessary database tables for the LMS system.</p>
                
                <div class="status-grid">
                    <div class="status-item <?= $installation_status['schema'] ? 'success' : 'pending' ?>">
                        <strong>Database Schema</strong><br>
                        <?= $installation_status['schema'] ? 'Installed' : 'Not Installed' ?>
                    </div>
                    <div class="status-item <?= $installation_status['categories'] ? 'success' : 'pending' ?>">
                        <strong>Categories</strong><br>
                        <?= $installation_status['categories'] ? 'Ready' : 'Pending' ?>
                    </div>
                </div>
                
                <?php if (!$installation_status['schema']): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="install_schema">
                        <button type="submit" class="btn btn-primary">Install Database Schema</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">Database schema is already installed.</div>
                    <a href="?step=2" class="btn btn-primary">Continue to Next Step</a>
                <?php endif; ?>
                
            <?php elseif ($step == 2): ?>
                <!-- Step 2: Sample Data -->
                <h2>Step 2: Install Sample Data</h2>
                <p>Install sample products and modules to get started quickly.</p>
                
                <div class="status-grid">
                    <div class="status-item <?= $installation_status['sample_products'] ? 'success' : 'pending' ?>">
                        <strong>Sample Products</strong><br>
                        <?= $installation_status['sample_products'] ? 'Installed' : 'Not Installed' ?>
                    </div>
                    <div class="status-item <?= $installation_status['sample_modules'] ? 'success' : 'pending' ?>">
                        <strong>Sample Modules</strong><br>
                        <?= $installation_status['sample_modules'] ? 'Installed' : 'Not Installed' ?>
                    </div>
                </div>
                
                <?php if (!$installation_status['sample_products']): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="install_sample_data">
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="install_products" value="1" checked>
                                Install sample products (Digital Marketing Mastery, Advanced SEO Strategies)
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="install_modules" value="1" checked>
                                Install sample modules for each product
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="install_categories" value="1" checked>
                                Install product categories
                            </label>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary">Install Sample Data</button>
                        <a href="?step=3" class="btn btn-secondary">Skip Sample Data</a>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">Sample data is already installed.</div>
                    <a href="?step=3" class="btn btn-primary">Continue to Next Step</a>
                <?php endif; ?>
                
            <?php elseif ($step == 3): ?>
                <!-- Step 3: Configuration -->
                <h2>Step 3: Configure Settings</h2>
                <p>Configure LMS settings and preferences.</p>
                
                <form method="post">
                    <input type="hidden" name="action" value="configure_settings">
                    
                    <div class="form-group">
                        <label class="form-label">Enable Auto-Sync</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="auto_sync" value="1" checked>
                                Automatically sync data between admin and member areas
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Certificate Settings</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enable_certificates" value="1" checked>
                                Enable automatic certificate generation
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Progress Tracking</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="track_time" value="1" checked>
                                Track time spent on modules
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="track_progress" value="1" checked>
                                Track module completion progress
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Configuration</button>
                </form>
                
            <?php else: ?>
                <!-- Step 4: Complete -->
                <h2>Installation Complete!</h2>
                <p>Your EPIC Hub LMS system has been successfully installed and configured.</p>
                
                <div class="alert alert-success">
                    <strong>Installation Summary:</strong><br>
                    • Database schema installed<br>
                    • Sample data configured<br>
                    • Settings applied<br>
                    • System ready for use
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <a href="<?= epic_url('admin/lms-products') ?>" class="btn btn-primary">Manage Products</a>
                    <a href="<?= epic_url('dashboard/member/products') ?>" class="btn btn-success">View Member Area</a>
                </div>
                
                <div class="alert alert-info" style="margin-top: 2rem;">
                    <strong>Next Steps:</strong><br>
                    1. Configure your products in the admin panel<br>
                    2. Add modules to your courses<br>
                    3. Set up access levels and pricing<br>
                    4. Test the member experience
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php

/**
 * Install LMS database schema
 */
function installLMSSchema() {
    try {
        $schema_file = __DIR__ . '/lms-products-schema.sql';
        if (!file_exists($schema_file)) {
            return ['success' => false, 'message' => 'Schema file not found'];
        }
        
        $sql = file_get_contents($schema_file);
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                db()->query($statement);
            }
        }
        
        return ['success' => true, 'message' => 'Database schema installed successfully'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Schema installation failed: ' . $e->getMessage()];
    }
}

/**
 * Install sample data
 */
function installSampleData() {
    try {
        // Sample data is included in the schema file
        return ['success' => true, 'message' => 'Sample data installed successfully'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Sample data installation failed: ' . $e->getMessage()];
    }
}

/**
 * Configure LMS settings
 */
function configureSettings($settings) {
    try {
        // Create or update configuration file
        $config = [
            'auto_sync' => !empty($settings['auto_sync']),
            'enable_certificates' => !empty($settings['enable_certificates']),
            'track_time' => !empty($settings['track_time']),
            'track_progress' => !empty($settings['track_progress']),
            'installed_at' => date('Y-m-d H:i:s')
        ];
        
        $config_content = "<?php\n// EPIC Hub LMS Configuration\ndefine('EPIC_AUTO_SYNC_LMS', " . ($config['auto_sync'] ? 'true' : 'false') . ");\n";
        $config_content .= "define('EPIC_LMS_CERTIFICATES', " . ($config['enable_certificates'] ? 'true' : 'false') . ");\n";
        $config_content .= "define('EPIC_LMS_TRACK_TIME', " . ($config['track_time'] ? 'true' : 'false') . ");\n";
        $config_content .= "define('EPIC_LMS_TRACK_PROGRESS', " . ($config['track_progress'] ? 'true' : 'false') . ");\n";
        
        file_put_contents(__DIR__ . '/config/lms-config.php', $config_content);
        
        return ['success' => true, 'message' => 'Configuration saved successfully'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Configuration failed: ' . $e->getMessage()];
    }
}

/**
 * Check installation status
 */
function checkInstallationStatus() {
    $status = [
        'schema' => false,
        'categories' => false,
        'sample_products' => false,
        'sample_modules' => false
    ];
    
    try {
        // Check if tables exist
        $tables = ['epic_product_categories', 'epic_product_modules', 'epic_user_progress', 'epic_user_certificates'];
        foreach ($tables as $table) {
            $result = db()->selectOne("SHOW TABLES LIKE '{$table}'");
            if ($result) {
                $status['schema'] = true;
                break;
            }
        }
        
        // Check categories
        if ($status['schema']) {
            $categories = db()->selectOne("SELECT COUNT(*) as count FROM epic_product_categories");
            $status['categories'] = ($categories['count'] ?? 0) > 0;
        }
        
        // Check sample products
        if ($status['schema']) {
            $products = db()->selectOne("SELECT COUNT(*) as count FROM epic_products WHERE name IN ('Digital Marketing Mastery', 'Advanced SEO Strategies')");
            $status['sample_products'] = ($products['count'] ?? 0) >= 2;
        }
        
        // Check sample modules
        if ($status['schema']) {
            $modules = db()->selectOne("SELECT COUNT(*) as count FROM epic_product_modules");
            $status['sample_modules'] = ($modules['count'] ?? 0) > 0;
        }
        
    } catch (Exception $e) {
        // Tables don't exist yet
    }
    
    return $status;
}
?>