<?php
/**
 * Check Table Structure Script
 */

require_once 'bootstrap.php';

echo "=== STRUKTUR TABEL EPIC_USERS ===\n";

try {
    $columns = db()->select("DESCRIBE epic_users");
    
    echo "Kolom yang tersedia:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Key']}\n";
    }
    
    echo "\n=== SAMPLE DATA ===\n";
    $sample = db()->selectOne("SELECT * FROM epic_users LIMIT 1");
    if ($sample) {
        echo "Sample record fields:\n";
        foreach (array_keys($sample) as $field) {
            echo "- $field\n";
        }
    } else {
        echo "No data in table\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}