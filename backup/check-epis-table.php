<?php
require_once 'bootstrap.php';

echo "=== Checking EPIS Table Structure ===\n";

try {
    // Check if table exists
    $tables = db()->select("SHOW TABLES LIKE 'epic_epis_accounts'");
    
    if (empty($tables)) {
        echo "✗ Table 'epic_epis_accounts' does not exist\n";
        
        // Check for similar tables
        echo "\nChecking for similar tables:\n";
        $all_tables = db()->select("SHOW TABLES");
        foreach ($all_tables as $table) {
            $table_name = array_values($table)[0];
            if (strpos($table_name, 'epis') !== false || strpos($table_name, 'epic') !== false) {
                echo "- $table_name\n";
            }
        }
    } else {
        echo "✓ Table 'epic_epis_accounts' exists\n";
        
        // Show table structure
        echo "\nTable structure:\n";
        $columns = db()->select("DESCRIBE epic_epis_accounts");
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Key']}\n";
        }
        
        // Show sample data
        echo "\nSample data (first 3 rows):\n";
        $sample = db()->select("SELECT * FROM epic_epis_accounts LIMIT 3");
        if (empty($sample)) {
            echo "No data found\n";
        } else {
            foreach ($sample as $row) {
                print_r($row);
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>