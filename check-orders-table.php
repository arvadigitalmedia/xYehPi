<?php
/**
 * Check Orders Table Structure
 */

require_once __DIR__ . '/bootstrap.php';

try {
    echo "=== Checking Orders Table Structure ===\n";
    
    // Check if orders table exists
    $tables = db()->select("SHOW TABLES LIKE 'epic_orders'");
    if (empty($tables)) {
        echo "ERROR: epic_orders table does not exist\n";
        
        // Check for other possible table names
        $all_tables = db()->select("SHOW TABLES");
        echo "Available tables:\n";
        foreach ($all_tables as $table) {
            $table_name = array_values($table)[0];
            if (strpos($table_name, 'order') !== false) {
                echo "- $table_name\n";
            }
        }
        exit;
    }
    
    echo "Orders table exists. Checking structure...\n";
    
    // Get table structure
    $columns = db()->select("DESCRIBE epic_orders");
    echo "\nTable structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    // Check for amount-related columns
    echo "\nAmount-related columns:\n";
    foreach ($columns as $column) {
        if (strpos(strtolower($column['Field']), 'amount') !== false || 
            strpos(strtolower($column['Field']), 'price') !== false ||
            strpos(strtolower($column['Field']), 'total') !== false) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
    }
    
    // Test a simple query
    echo "\nTesting simple query...\n";
    $count = db()->selectValue("SELECT COUNT(*) FROM epic_orders");
    echo "Total orders: $count\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>