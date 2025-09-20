<?php
/**
 * Add EPIS Supervisor Name Column Migration
 * Adds epis_supervisor_name column to users table for storing supervisor information
 */

try {
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=epic_hub', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "Connected to database successfully.\n";
    
    // Check if column already exists
    $result = $pdo->query("SHOW COLUMNS FROM epic_users LIKE 'epis_supervisor_name'");
    if ($result->rowCount() > 0) {
        echo "❌ Column 'epis_supervisor_name' already exists.\n";
        exit(1);
    }
    
    // Add epis_supervisor_name column
    $sql = "ALTER TABLE `epic_users` ADD COLUMN `epis_supervisor_name` VARCHAR(100) NULL COMMENT 'EPIS Supervisor name/identifier' AFTER `epis_supervisor_id`";
    
    $pdo->exec($sql);
    echo "✅ Successfully added 'epis_supervisor_name' column to epic_users table.\n";
    
    // Verify the column was added
    $result = $pdo->query("SHOW COLUMNS FROM epic_users LIKE 'epis_supervisor_name'");
    if ($result->rowCount() > 0) {
        echo "✅ Column verification successful.\n";
        
        // Show column details
        $column = $result->fetch();
        echo "Column details:\n";
        echo "- Field: " . $column['Field'] . "\n";
        echo "- Type: " . $column['Type'] . "\n";
        echo "- Null: " . $column['Null'] . "\n";
        echo "- Default: " . ($column['Default'] ?? 'NULL') . "\n";
        echo "- Comment: " . ($column['Comment'] ?? 'None') . "\n";
    } else {
        echo "❌ Column verification failed.\n";
        exit(1);
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>