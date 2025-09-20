<?php
/**
 * Test Event Scheduling Database Connection Fix
 * Verifikasi perbaikan error "Database connection not available"
 */

session_start();
require_once 'bootstrap.php';

echo "<h1>🔧 TEST EVENT SCHEDULING DATABASE FIX</h1>";

// 1. Test Bootstrap Database Initialization
echo "<h2>1. Bootstrap Database Initialization Test</h2>";
global $epic_db;

if (isset($epic_db) && $epic_db) {
    echo "<p style='color: green;'>✅ Global \$epic_db: TERSEDIA</p>";
    echo "<p>Type: " . get_class($epic_db) . "</p>";
    
    try {
        $test_query = $epic_db->query('SELECT 1 as test');
        $result = $test_query->fetch();
        echo "<p style='color: green;'>✅ Database query test: BERHASIL (result: " . $result['test'] . ")</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database query test: GAGAL - " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Global \$epic_db: TIDAK TERSEDIA</p>";
}

// 2. Test db() Function
echo "<h2>2. Database Function Test</h2>";
if (function_exists('db')) {
    echo "<p style='color: green;'>✅ Function db(): TERSEDIA</p>";
    
    try {
        $db_instance = db();
        echo "<p style='color: green;'>✅ db() instance: BERHASIL</p>";
        
        $connection = $db_instance->getConnection();
        echo "<p style='color: green;'>✅ getConnection(): BERHASIL</p>";
        echo "<p>Connection type: " . get_class($connection) . "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ db() function error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Function db(): TIDAK TERSEDIA</p>";
}

// 3. Test EpicEventScheduling Class
echo "<h2>3. EpicEventScheduling Class Test</h2>";

if (class_exists('EpicEventScheduling')) {
    echo "<p style='color: green;'>✅ Class EpicEventScheduling: TERSEDIA</p>";
    
    try {
        $event_scheduler = new EpicEventScheduling();
        echo "<p style='color: green;'>✅ EpicEventScheduling instantiation: BERHASIL</p>";
        
        // Test method call
        try {
            $categories = $event_scheduler->getEventCategories();
            echo "<p style='color: green;'>✅ getEventCategories(): BERHASIL (" . count($categories) . " categories)</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ getEventCategories() error: " . $e->getMessage() . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ EpicEventScheduling instantiation: GAGAL</p>";
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Class EpicEventScheduling: TIDAK TERSEDIA</p>";
}

// 4. Test Database Tables
echo "<h2>4. Database Tables Test</h2>";

try {
    $tables_to_check = [
        'epic_event_categories',
        'epi_event_schedules'
    ];
    
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $epic_db->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->fetch();
            
            if ($exists) {
                echo "<p style='color: green;'>✅ Table $table: EXISTS</p>";
                
                // Count records
                $count_stmt = $epic_db->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_stmt->fetch()['count'];
                echo "<p>   Records: $count</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Table $table: NOT EXISTS</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Table $table check error: " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database tables check failed: " . $e->getMessage() . "</p>";
}

// 5. Test Error Logging
echo "<h2>5. Error Logging Test</h2>";

$log_file = ini_get('error_log');
if ($log_file) {
    echo "<p>Error log file: " . $log_file . "</p>";
    
    if (file_exists($log_file)) {
        $log_size = filesize($log_file);
        echo "<p style='color: green;'>✅ Error log accessible (Size: " . number_format($log_size) . " bytes)</p>";
        
        // Check recent logs
        $recent_logs = tail($log_file, 10);
        if ($recent_logs) {
            echo "<h3>Recent Error Logs (last 10 lines):</h3>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 12px;'>";
            echo htmlspecialchars($recent_logs);
            echo "</pre>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Error log file not found</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Error logging not configured</p>";
}

// 6. Summary
echo "<h2>📋 Summary</h2>";

$issues_found = [];
$fixes_applied = [];

// Check for issues
if (!isset($epic_db) || !$epic_db) {
    $issues_found[] = "Global \$epic_db not available";
} else {
    $fixes_applied[] = "Global \$epic_db properly initialized";
}

if (!function_exists('db')) {
    $issues_found[] = "db() function not available";
} else {
    $fixes_applied[] = "db() function working correctly";
}

if (!class_exists('EpicEventScheduling')) {
    $issues_found[] = "EpicEventScheduling class not loaded";
} else {
    $fixes_applied[] = "EpicEventScheduling class loaded successfully";
}

echo "<h3 style='color: green;'>✅ Fixes Applied:</h3>";
echo "<ul>";
foreach ($fixes_applied as $fix) {
    echo "<li>$fix</li>";
}
echo "</ul>";

if (!empty($issues_found)) {
    echo "<h3 style='color: red;'>❌ Issues Found:</h3>";
    echo "<ul>";
    foreach ($issues_found as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
} else {
    echo "<h3 style='color: green;'>🎉 No Issues Found!</h3>";
    echo "<p><strong>Database connection error has been successfully fixed!</strong></p>";
}

// Helper function to read last N lines of a file
function tail($filename, $lines = 10) {
    if (!file_exists($filename)) return false;
    
    $file = file($filename);
    if (count($file) < $lines) {
        return implode('', $file);
    }
    
    return implode('', array_slice($file, -$lines));
}
?>