<?php
require_once 'bootstrap.php';

echo "Checking database structure...\n\n";

try {
    $db = db();
    
    // Show all tables
    echo "Available tables:\n";
    $tables = $db->select("SHOW TABLES");
    foreach ($tables as $table) {
        $table_name = array_values($table)[0];
        echo "- $table_name\n";
    }
    
    // Look for user-related tables
    echo "\nLooking for user-related tables:\n";
    $user_tables = $db->select("SHOW TABLES LIKE '%user%'");
    foreach ($user_tables as $table) {
        $table_name = array_values($table)[0];
        echo "- $table_name\n";
        
        // Show structure
        echo "  Structure:\n";
        $columns = $db->select("DESCRIBE $table_name");
        foreach ($columns as $col) {
            echo "    " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        echo "\n";
    }
    
    // Look for referral-related tables
    echo "\nLooking for referral-related tables:\n";
    $referral_tables = $db->select("SHOW TABLES LIKE '%referral%'");
    foreach ($referral_tables as $table) {
        $table_name = array_values($table)[0];
        echo "- $table_name\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>