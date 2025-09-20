<?php
/**
 * EPIC Hub Database Creator
 * Simple script to create the epic_hub database
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'epic_hub';

echo "<h1>EPIC Hub Database Creator</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;}</style>";

try {
    // Connect to MySQL without specifying database
    $pdo = new PDO("mysql:host={$host}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='success'>✓ Connected to MySQL server</div>";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$database}'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "<div class='success'>✓ Database '{$database}' already exists</div>";
    } else {
        // Create database
        $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<div class='success'>✓ Database '{$database}' created successfully</div>";
    }
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='success'>✓ Connected to database '{$database}'</div>";
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'epic_%'");
    $tables = $stmt->fetchAll();
    
    if (empty($tables)) {
        echo "<div style='margin:20px 0; padding:15px; background:#fff3cd; border:1px solid #ffeaa7; border-radius:5px;'>";
        echo "<h3>⚠️ Database is empty</h3>";
        echo "<p>The database exists but contains no tables. You need to:</p>";
        echo "<ol>";
        echo "<li>Run the SQL schema from <strong>epic-database-schema.sql</strong></li>";
        echo "<li>Or use the <a href='install.php'>installation wizard</a></li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div class='success'>✓ Found " . count($tables) . " EPIC Hub tables</div>";
        echo "<div style='margin:10px 0;'>";
        foreach ($tables as $table) {
            echo "<div style='margin-left:20px;'>• " . $table[0] . "</div>";
        }
        echo "</div>";
    }
    
    echo "<div style='margin-top:30px; padding:20px; background:#d4edda; border:1px solid #c3e6cb; border-radius:5px;'>";
    echo "<h3>✅ Database Setup Complete</h3>";
    echo "<p>Your database is ready. Next steps:</p>";
    echo "<ul>";
    echo "<li><a href='test-system.php'>Run system test</a> to verify everything works</li>";
    echo "<li><a href='install.php'>Run installation wizard</a> if tables are missing</li>";
    echo "<li><a href='index.php'>Access EPIC Hub</a> if already configured</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>✗ Database error: " . $e->getMessage() . "</div>";
    
    echo "<div style='margin-top:20px; padding:15px; background:#f8d7da; border:1px solid #f5c6cb; border-radius:5px;'>";
    echo "<h3>❌ Database Connection Failed</h3>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>XAMPP MySQL service is running</li>";
    echo "<li>Database credentials are correct</li>";
    echo "<li>MySQL server is accessible</li>";
    echo "</ul>";
    echo "<p><strong>Error details:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='margin-top:30px; padding:10px; background:#f8f9fa; border-radius:5px; font-size:12px; color:#666;'>";
echo "<strong>Note:</strong> Delete this file (create-database.php) after setup for security.";
echo "</div>";
?>