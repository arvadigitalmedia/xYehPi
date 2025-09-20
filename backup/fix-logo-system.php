<?php
/**
 * EPIC Hub Logo System Fix
 * Script untuk memperbaiki sistem upload logo dan favicon
 * 
 * Jalankan script ini sekali untuk memperbaiki masalah upload logo/favicon
 * Setelah berhasil, hapus file ini untuk keamanan
 */

require_once 'bootstrap.php';

// Security check - hanya admin yang bisa menjalankan
$user = epic_current_user();
if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
    die('❌ Access denied. Admin access required.');
}

echo "<!DOCTYPE html>";
echo "<html><head><title>EPIC Hub Logo System Fix</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5;}";
echo ".success{color:#28a745;}.error{color:#dc3545;}.info{color:#17a2b8;}.warning{color:#ffc107;}";
echo "pre{background:#fff;padding:15px;border-radius:5px;border-left:4px solid #007bff;}";
echo "</style></head><body>";

echo "<h1>🔧 EPIC Hub Logo System Fix</h1>";
echo "<p>Script ini akan memperbaiki sistem upload logo dan favicon.</p>";

$errors = [];
$success = [];

try {
    // Step 1: Check and create uploads/logos directory
    echo "<h3>📁 Step 1: Checking uploads directory</h3>";
    $logoDir = EPIC_ROOT . '/uploads/logos';
    
    if (!is_dir($logoDir)) {
        if (mkdir($logoDir, 0755, true)) {
            $success[] = "Directory uploads/logos created";
            echo "<p class='success'>✅ Directory uploads/logos created</p>";
        } else {
            $errors[] = "Failed to create uploads/logos directory";
            echo "<p class='error'>❌ Failed to create uploads/logos directory</p>";
        }
    } else {
        echo "<p class='success'>✅ Directory uploads/logos exists</p>";
    }
    
    if (is_writable($logoDir)) {
        echo "<p class='success'>✅ Directory is writable</p>";
    } else {
        $errors[] = "Directory uploads/logos is not writable";
        echo "<p class='error'>❌ Directory is not writable</p>";
        echo "<p class='warning'>⚠️ Please set directory permissions to 755 or 777</p>";
    }
    
    // Step 2: Check and create settings table
    echo "<h3>🗄️ Step 2: Checking settings table</h3>";
    $tableExists = false;
    try {
        $result = db()->select('SELECT 1 FROM ' . TABLE_SETTINGS . ' LIMIT 1');
        $tableExists = true;
        echo "<p class='success'>✅ Settings table exists</p>";
    } catch (Exception $e) {
        echo "<p class='info'>ℹ️ Settings table does not exist, creating...</p>";
    }
    
    if (!$tableExists) {
        // Create settings table
        $sql = "CREATE TABLE IF NOT EXISTS `settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `key` varchar(255) NOT NULL,
            `value` longtext DEFAULT NULL,
            `type` enum('string','number','boolean','json','text') NOT NULL DEFAULT 'string',
            `description` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `settings_key_unique` (`key`),
            KEY `settings_type_index` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        db()->query($sql);
        echo "<p class='success'>✅ Settings table created</p>";
        
        // Insert default settings
        $defaultSettings = [
            ['site_name', 'EPIC Hub', 'string', 'Website name'],
            ['site_description', 'Platform Affiliate Marketing Terdepan', 'string', 'Website description'],
            ['site_logo', '', 'string', 'Website logo filename'],
            ['site_favicon', '', 'string', 'Website favicon filename'],
            ['currency_symbol', 'Rp', 'string', 'Currency symbol'],
            ['timezone', 'Asia/Jakarta', 'string', 'Default timezone']
        ];
        
        foreach ($defaultSettings as $setting) {
            db()->query(
                "INSERT INTO settings (`key`, `value`, `type`, `description`) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `description` = VALUES(`description`)",
                $setting
            );
        }
        
        echo "<p class='success'>✅ Default settings inserted</p>";
        $success[] = "Settings table created and configured";
    }
    
    // Step 3: Verify upload function
    echo "<h3>⚙️ Step 3: Checking upload function</h3>";
    if (function_exists('epic_handle_logo_upload')) {
        echo "<p class='success'>✅ Upload function exists</p>";
    } else {
        $errors[] = "Upload function epic_handle_logo_upload not found";
        echo "<p class='error'>❌ Upload function not found</p>";
    }
    
    // Step 4: Test database connection and settings
    echo "<h3>🔗 Step 4: Testing database operations</h3>";
    try {
        // Test insert/update setting
        db()->query(
            "INSERT INTO " . TABLE_SETTINGS . " (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
            ['test_setting', 'test_value']
        );
        
        // Test retrieve setting
        $testSetting = db()->selectOne('SELECT * FROM ' . TABLE_SETTINGS . ' WHERE `key` = ?', ['test_setting']);
        
        if ($testSetting && $testSetting['value'] === 'test_value') {
            echo "<p class='success'>✅ Database operations working</p>";
            
            // Clean up test setting
            db()->query('DELETE FROM ' . TABLE_SETTINGS . ' WHERE `key` = ?', ['test_setting']);
        } else {
            $errors[] = "Database operations failed";
            echo "<p class='error'>❌ Database operations failed</p>";
        }
    } catch (Exception $e) {
        $errors[] = "Database error: " . $e->getMessage();
        echo "<p class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Step 5: Show current status
    echo "<h3>📊 Step 5: Current system status</h3>";
    $currentLogo = epic_setting('site_logo');
    $currentFavicon = epic_setting('site_favicon');
    
    echo "<p><strong>Current logo:</strong> " . ($currentLogo ?: '<em>Not set</em>') . "</p>";
    echo "<p><strong>Current favicon:</strong> " . ($currentFavicon ?: '<em>Not set</em>') . "</p>";
    
    // List uploaded files
    $files = array_diff(scandir($logoDir), ['.', '..']);
    if (!empty($files)) {
        echo "<p><strong>Files in uploads/logos:</strong></p>";
        echo "<ul>";
        foreach ($files as $file) {
            $filePath = $logoDir . '/' . $file;
            $fileSize = number_format(filesize($filePath));
            echo "<li>$file ($fileSize bytes)</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    $errors[] = "Fatal error: " . $e->getMessage();
    echo "<p class='error'>❌ Fatal error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Summary
echo "<h3>📋 Summary</h3>";
if (empty($errors)) {
    echo "<div style='background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:15px;border-radius:5px;'>";
    echo "<h4>🎉 Success! Logo system is now working properly.</h4>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Go to Admin → Settings → General</li>";
    echo "<li>Upload logo and favicon</li>";
    echo "<li>Settings will be saved correctly</li>";
    echo "</ul>";
    echo "<p><strong>⚠️ Important:</strong> Delete this file (fix-logo-system.php) after successful fix for security.</p>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:15px;border-radius:5px;'>";
    echo "<h4>❌ Issues found:</h4>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "<p>Please fix these issues and run the script again.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>EPIC Hub Logo System Fix v1.0 | " . date('Y-m-d H:i:s') . "</small></p>";
echo "</body></html>";
?>