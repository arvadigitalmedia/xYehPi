<?php
define('EPIC_DIRECT_ACCESS', true);
require_once 'bootstrap.php';

echo "<h2>Daftar Tabel di Database</h2>\n";

try {
    $tables = db()->select('SHOW TABLES');
    
    echo "<ul>\n";
    foreach($tables as $table) {
        $table_name = array_values($table)[0];
        echo "<li>{$table_name}</li>\n";
    }
    echo "</ul>\n";
    
    // Cek tabel EPIS khusus
    echo "<h3>Tabel EPIS yang ada:</h3>\n";
    foreach($tables as $table) {
        $table_name = array_values($table)[0];
        if (strpos($table_name, 'epis') !== false) {
            echo "<p>✅ {$table_name}</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>\n";
}
?>