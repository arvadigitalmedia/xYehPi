<?php
/**
 * EPIC Hub Hosting Debug Tool
 * Tool untuk debug masalah database dan Zoom integration di hosting cPanel
 */

// Security check - hanya izinkan akses dengan parameter khusus
if (!isset($_GET['debug_key']) || $_GET['debug_key'] !== 'epic_debug_2025') {
    die('Access denied. Debug key required.');
}

// Set header untuk output yang bersih
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPIC Hub - Hosting Debug</title>
    <style>
        body { font-family: 'Courier New', monospace; max-width: 1200px; margin: 20px auto; padding: 20px; background: #1a1a1a; color: #00ff00; }
        .container { background: #000; padding: 20px; border-radius: 10px; border: 1px solid #333; }
        .header { text-align: center; margin-bottom: 30px; color: #00ffff; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #333; border-radius: 5px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffff00; }
        .info { color: #00ffff; }
        .code { background: #222; padding: 10px; border-radius: 5px; margin: 10px 0; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #333; }
        .btn { background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 3px; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 EPIC Hub - Hosting Debug Tool</h1>
            <p>Diagnostic tool untuk troubleshooting masalah hosting cPanel</p>
            <p><small>Timestamp: <?= date('Y-m-d H:i:s T') ?></small></p>
        </div>

        <?php
        echo "<div class='section'>";
        echo "<h2>📊 1. Environment Information</h2>";
        
        // Server info
        echo "<div class='code'>";
        echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
        echo "PHP Version: " . PHP_VERSION . "<br>";
        echo "Host: " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "<br>";
        echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";
        echo "Script Path: " . __FILE__ . "<br>";
        echo "Current User: " . (function_exists('get_current_user') ? get_current_user() : 'Unknown') . "<br>";
        echo "</div>";
        echo "</div>";

        // Test 1: File System Check
        echo "<div class='section'>";
        echo "<h2>📁 2. File System Check</h2>";
        
        $files_to_check = [
            'bootstrap.php',
            'config/config.php',
            'config/database.php',
            'core/functions.php',
            'core/zoom-integration.php'
        ];
        
        foreach ($files_to_check as $file) {
            $path = __DIR__ . '/' . $file;
            if (file_exists($path)) {
                echo "<span class='success'>✅ {$file}</span> - Size: " . filesize($path) . " bytes<br>";
            } else {
                echo "<span class='error'>❌ {$file}</span> - File not found<br>";
            }
        }
        echo "</div>";

        // Test 2: Configuration Check
        echo "<div class='section'>";
        echo "<h2>⚙️ 3. Configuration Check</h2>";
        
        try {
            if (file_exists(__DIR__ . '/config/config.php')) {
                require_once __DIR__ . '/config/config.php';
                echo "<span class='success'>✅ Config loaded</span><br>";
                
                echo "<div class='code'>";
                echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>";
                echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "<br>";
                echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "<br>";
                echo "DB_PASS: " . (defined('DB_PASS') ? (DB_PASS ? '[SET]' : '[EMPTY]') : 'Not defined') . "<br>";
                echo "SITE_URL: " . (defined('SITE_URL') ? SITE_URL : 'Not defined') . "<br>";
                echo "</div>";
            } else {
                echo "<span class='error'>❌ Config file not found</span><br>";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Config error: " . $e->getMessage() . "</span><br>";
        }
        echo "</div>";

        // Test 3: Database Connection
        echo "<div class='section'>";
        echo "<h2>🗄️ 4. Database Connection Test</h2>";
        
        try {
            if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 10
                ]);
                
                echo "<span class='success'>✅ Database connection successful</span><br>";
                
                // Test query
                $stmt = $pdo->query("SELECT VERSION() as version, NOW() as current_time");
                $result = $stmt->fetch();
                
                echo "<div class='code'>";
                echo "MySQL Version: " . $result['version'] . "<br>";
                echo "Server Time: " . $result['current_time'] . "<br>";
                echo "</div>";
                
                // Check tables
                echo "<h3>📋 Database Tables:</h3>";
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $zoom_tables = ['epic_event_categories', 'epic_zoom_events', 'epic_event_registrations', 'epic_zoom_settings'];
                
                echo "<table>";
                echo "<tr><th>Table Name</th><th>Status</th><th>Rows</th></tr>";
                
                foreach ($zoom_tables as $table) {
                    if (in_array($table, $tables)) {
                        try {
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
                            $count = $stmt->fetch()['count'];
                            echo "<tr><td>{$table}</td><td class='success'>✅ Exists</td><td>{$count}</td></tr>";
                        } catch (Exception $e) {
                            echo "<tr><td>{$table}</td><td class='warning'>⚠️ Error</td><td>{$e->getMessage()}</td></tr>";
                        }
                    } else {
                        echo "<tr><td>{$table}</td><td class='error'>❌ Missing</td><td>-</td></tr>";
                    }
                }
                echo "</table>";
                
            } else {
                echo "<span class='error'>❌ Database configuration incomplete</span><br>";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Database connection failed: " . $e->getMessage() . "</span><br>";
        }
        echo "</div>";

        // Test 4: Bootstrap Test
        echo "<div class='section'>";
        echo "<h2>🚀 5. Bootstrap Test</h2>";
        
        try {
            require_once __DIR__ . '/bootstrap.php';
            echo "<span class='success'>✅ Bootstrap loaded successfully</span><br>";
            
            // Test global variables
            echo "<div class='code'>";
            echo "EPIC_LOADED: " . (defined('EPIC_LOADED') ? 'true' : 'false') . "<br>";
            echo "EPIC_VERSION: " . (defined('EPIC_VERSION') ? EPIC_VERSION : 'Not defined') . "<br>";
            echo "Global \$epic_db: " . (isset($GLOBALS['epic_db']) ? 'Available' : 'Not available') . "<br>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ Bootstrap error: " . $e->getMessage() . "</span><br>";
        }
        echo "</div>";

        // Test 5: Zoom Integration Test
        echo "<div class='section'>";
        echo "<h2>🎥 6. Zoom Integration Test</h2>";
        
        try {
            if (file_exists(__DIR__ . '/core/zoom-integration.php')) {
                require_once __DIR__ . '/core/zoom-integration.php';
                echo "<span class='success'>✅ Zoom integration file loaded</span><br>";
                
                $zoom = new EpicZoomIntegration();
                echo "<span class='success'>✅ Zoom integration class instantiated</span><br>";
                
                // Test methods
                if (method_exists($zoom, 'getEventCategories')) {
                    try {
                        $categories = $zoom->getEventCategories();
                        echo "<span class='success'>✅ getEventCategories() works</span> - Found " . count($categories) . " categories<br>";
                    } catch (Exception $e) {
                        echo "<span class='warning'>⚠️ getEventCategories() error: " . $e->getMessage() . "</span><br>";
                    }
                }
                
            } else {
                echo "<span class='error'>❌ Zoom integration file not found</span><br>";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Zoom integration error: " . $e->getMessage() . "</span><br>";
        }
        echo "</div>";

        // Test 6: Error Log Check
        echo "<div class='section'>";
        echo "<h2>📝 7. Recent Error Logs</h2>";
        
        $log_file = ini_get('error_log');
        if ($log_file && file_exists($log_file)) {
            echo "<span class='info'>📄 Log file: {$log_file}</span><br>";
            
            $lines = file($log_file);
            $recent_lines = array_slice($lines, -20); // Last 20 lines
            
            echo "<div class='code'>";
            foreach ($recent_lines as $line) {
                if (strpos($line, 'Zoom') !== false || strpos($line, 'Database') !== false) {
                    echo "<span class='warning'>" . htmlspecialchars($line) . "</span>";
                } else {
                    echo htmlspecialchars($line);
                }
            }
            echo "</div>";
        } else {
            echo "<span class='warning'>⚠️ Error log file not accessible</span><br>";
        }
        echo "</div>";

        // Recommendations
        echo "<div class='section'>";
        echo "<h2>💡 8. Recommendations</h2>";
        echo "<ul>";
        echo "<li>Jika database connection gagal, periksa kredensial di config/config.php</li>";
        echo "<li>Jika tabel missing, jalankan install-zoom-integration.php</li>";
        echo "<li>Jika masih error, gunakan setup-cpanel.php untuk konfigurasi ulang</li>";
        echo "<li>Hapus file debug ini setelah selesai troubleshooting</li>";
        echo "</ul>";
        echo "</div>";
        ?>

        <div class="section">
            <h2>🔧 Quick Actions</h2>
            <a href="install-zoom-integration.php" class="btn">Install Zoom Tables</a>
            <a href="setup-cpanel.php?setup_key=epic_setup_2025" class="btn">Setup cPanel Config</a>
            <button onclick="location.reload()" class="btn">Refresh Debug</button>
        </div>
    </div>
</body>
</html>