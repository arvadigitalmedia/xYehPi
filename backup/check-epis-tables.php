<?php
require_once 'bootstrap.php';

echo "=== Checking EPIS Tables ===\n";

try {
    $tables = db()->select('SHOW TABLES');
    
    echo "Tables containing 'epis':\n";
    foreach($tables as $table) {
        $name = array_values($table)[0];
        if(strpos($name, 'epis') !== false) {
            echo "- $name\n";
        }
    }
    
    echo "\nAll tables:\n";
    foreach($tables as $table) {
        $name = array_values($table)[0];
        echo "- $name\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>