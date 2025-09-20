<?php
/**
 * EPIC Hub Event Scheduling Installer
 * Script untuk menginstall database schema dan data awal event scheduling
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

// Set header untuk output yang bersih
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🚀 Event Scheduling System Installer</h1>";
echo "<hr>";

// Check admin access
if (!epic_is_admin()) {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; border-radius: 5px;'>";
    echo "❌ <strong>Access Denied</strong><br>";
    echo "You must be logged in as an administrator to run this installer.";
    echo "</div>";
    echo "<p><a href='" . epic_url('login') . "'>Login as Admin</a></p>";
    exit;
}

echo "<h2>📋 Installation Progress</h2>";

try {
    global $epic_db;
    
    if (!$epic_db) {
        throw new Exception('Database connection not available');
    }
    
    echo "✅ Database connection: <strong>OK</strong><br>";
    
    // Read SQL file
    $sql_file = __DIR__ . '/event-scheduling-schema.sql';
    if (!file_exists($sql_file)) {
        throw new Exception('SQL schema file not found: ' . $sql_file);
    }
    
    $sql_content = file_get_contents($sql_file);
    if (!$sql_content) {
        throw new Exception('Failed to read SQL schema file');
    }
    
    echo "✅ SQL schema file loaded: <strong>" . basename($sql_file) . "</strong><br>";
    
    // Split SQL into individual statements
    $sql_statements = array_filter(
        array_map('trim', 
            preg_split('/;\s*$/m', $sql_content)
        ),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*(--|#)/', $stmt);
        }
    );
    
    echo "📊 Found <strong>" . count($sql_statements) . "</strong> SQL statements to execute<br><br>";
    
    $success_count = 0;
    $error_count = 0;
    
    // Execute each SQL statement
    foreach ($sql_statements as $index => $sql) {
        $sql = trim($sql);
        if (empty($sql)) continue;
        
        try {
            // Skip DELIMITER statements (not supported in PDO)
            if (stripos($sql, 'DELIMITER') === 0) {
                echo "⏭️ Skipped DELIMITER statement<br>";
                continue;
            }
            
            $stmt = $epic_db->prepare($sql);
            $result = $stmt->execute();
            
            if ($result) {
                $success_count++;
                
                // Determine statement type for better logging
                $stmt_type = 'UNKNOWN';
                if (stripos($sql, 'CREATE TABLE') === 0) {
                    preg_match('/CREATE TABLE\s+`?([^`\s]+)`?/i', $sql, $matches);
                    $stmt_type = 'CREATE TABLE ' . ($matches[1] ?? '');
                } elseif (stripos($sql, 'INSERT INTO') === 0) {
                    preg_match('/INSERT INTO\s+`?([^`\s]+)`?/i', $sql, $matches);
                    $stmt_type = 'INSERT INTO ' . ($matches[1] ?? '');
                } elseif (stripos($sql, 'CREATE INDEX') === 0) {
                    preg_match('/CREATE INDEX\s+`?([^`\s]+)`?/i', $sql, $matches);
                    $stmt_type = 'CREATE INDEX ' . ($matches[1] ?? '');
                } elseif (stripos($sql, 'CREATE VIEW') === 0) {
                    preg_match('/CREATE VIEW\s+`?([^`\s]+)`?/i', $sql, $matches);
                    $stmt_type = 'CREATE VIEW ' . ($matches[1] ?? '');
                } elseif (stripos($sql, 'CREATE TRIGGER') === 0) {
                    preg_match('/CREATE TRIGGER\s+`?([^`\s]+)`?/i', $sql, $matches);
                    $stmt_type = 'CREATE TRIGGER ' . ($matches[1] ?? '');
                } elseif (stripos($sql, 'ALTER TABLE') === 0) {
                    preg_match('/ALTER TABLE\s+`?([^`\s]+)`?/i', $sql, $matches);
                    $stmt_type = 'ALTER TABLE ' . ($matches[1] ?? '');
                }
                
                echo "✅ <strong>" . ($index + 1) . ".</strong> " . $stmt_type . "<br>";
            } else {
                $error_count++;
                echo "❌ <strong>" . ($index + 1) . ".</strong> Failed to execute statement<br>";
            }
        } catch (Exception $e) {
            $error_count++;
            $error_msg = $e->getMessage();
            
            // Check if it's a "table already exists" error (not critical)
            if (stripos($error_msg, 'already exists') !== false) {
                echo "⚠️ <strong>" . ($index + 1) . ".</strong> Table/Index already exists (skipped)<br>";
            } else {
                echo "❌ <strong>" . ($index + 1) . ".</strong> Error: " . htmlspecialchars($error_msg) . "<br>";
            }
        }
    }
    
    echo "<br><h2>📊 Installation Summary</h2>";
    echo "<ul>";
    echo "<li>✅ Successful operations: <strong>$success_count</strong></li>";
    echo "<li>❌ Failed operations: <strong>$error_count</strong></li>";
    echo "<li>📋 Total statements: <strong>" . count($sql_statements) . "</strong></li>";
    echo "</ul>";
    
    if ($error_count == 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>🎉 Installation Completed Successfully!</h3>";
        echo "<p>Event Scheduling system has been installed successfully. You can now:</p>";
        echo "<ul>";
        echo "<li>✅ Access the Event Scheduling management page</li>";
        echo "<li>✅ Create and manage event categories</li>";
        echo "<li>✅ Schedule events for different access levels</li>";
        echo "<li>✅ Manage event registrations</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>⚠️ Installation Completed with Warnings</h3>";
        echo "<p>Some operations failed, but the system should still be functional. Please check the errors above.</p>";
        echo "</div>";
    }
    
    // Verify installation
    echo "<h2>🔍 Installation Verification</h2>";
    
    $tables_to_check = [
        'epi_event_schedule_categories',
        'epi_event_schedules', 
        'epi_event_schedule_registrations'
    ];
    
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $epic_db->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'];
            echo "✅ Table <strong>$table</strong>: $count records<br>";
        } catch (Exception $e) {
            echo "❌ Table <strong>$table</strong>: " . $e->getMessage() . "<br>";
        }
    }
    
    // Check if view exists
    try {
        $stmt = $epic_db->query("SELECT COUNT(*) as count FROM epi_event_schedules_with_categories LIMIT 1");
        echo "✅ View <strong>epi_event_schedules_with_categories</strong>: Available<br>";
    } catch (Exception $e) {
        echo "❌ View <strong>epi_event_schedules_with_categories</strong>: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Installation Failed</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>🔗 Quick Links</h2>";
echo "<p>";
echo "<a href='" . epic_url('admin/event-scheduling') . "' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📅 Event Scheduling</a>";
echo "<a href='" . epic_url('admin') . "' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🏠 Admin Dashboard</a>";
echo "<a href='" . epic_url('admin/event-scheduling-add') . "' style='display: inline-block; padding: 10px 20px; background: #ffc107; color: black; text-decoration: none; border-radius: 5px;'>➕ Add New Event</a>";
echo "</p>";

echo "<p><strong>Installation completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>