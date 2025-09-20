<?php
/**
 * Test EPIS Table Check
 * Mengecek struktur dan data tabel epic_epis_accounts
 */

require_once 'bootstrap.php';

echo "=== CHECKING epic_epis_accounts TABLE ===\n";

try {
    // Check if table exists
    $tables = db()->select("SHOW TABLES LIKE 'epic_epis_accounts'");
    if (empty($tables)) {
        echo "❌ Table epic_epis_accounts NOT FOUND\n";
        
        // Check if table exists with double prefix
        $doublePrefix = db()->select("SHOW TABLES LIKE 'epic_epic_epis_accounts'");
        if (!empty($doublePrefix)) {
            echo "⚠️  Found table with DOUBLE PREFIX: epic_epic_epis_accounts\n";
        }
        
        exit(1);
    }
    echo "✅ Table epic_epis_accounts EXISTS\n";
    
    // Show table structure
    echo "\n=== TABLE STRUCTURE ===\n";
    $structure = db()->select("DESCRIBE epic_epis_accounts");
    printf("%-25s %-20s %-10s %-10s\n", "Field", "Type", "Null", "Key");
    echo str_repeat("-", 70) . "\n";
    foreach ($structure as $column) {
        printf("%-25s %-20s %-10s %-10s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Key']
        );
    }
    
    // Count records
    $count = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts");
    echo "\n=== RECORD COUNT ===\n";
    echo "Total records: $count\n";
    
    // Check max_epic_recruits values
    echo "\n=== MAX_EPIC_RECRUITS VALUES ===\n";
    $recruitValues = db()->select("SELECT DISTINCT max_epic_recruits, COUNT(*) as count FROM epic_epis_accounts GROUP BY max_epic_recruits ORDER BY max_epic_recruits");
    foreach ($recruitValues as $value) {
        echo "Value: {$value['max_epic_recruits']}, Count: {$value['count']}\n";
    }
    
    // Show sample data
    if ($count > 0) {
        echo "\n=== SAMPLE DATA (first 3 records) ===\n";
        $samples = db()->select("SELECT id, user_id, status, max_epic_recruits, created_at FROM epic_epis_accounts LIMIT 3");
        foreach ($samples as $sample) {
            echo "ID: {$sample['id']}, User: {$sample['user_id']}, Status: {$sample['status']}, Max Recruits: {$sample['max_epic_recruits']}, Created: {$sample['created_at']}\n";
        }
    }
    
    echo "\n✅ Table check completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}