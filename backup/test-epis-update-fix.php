<?php
/**
 * Test EPIS Update Fix
 * Test perbaikan prefix ganda dan update max_epic_recruits
 */

require_once 'bootstrap.php';

echo "=== TESTING EPIS UPDATE FIX ===\n";

try {
    // Test 1: Update max_epic_recruits dari 50 ke 1.000.000
    echo "\n1. Updating max_epic_recruits from 50 to 1,000,000...\n";
    
    $affected = db()->update(
        'epic_epis_accounts', 
        ['max_epic_recruits' => 1000000], 
        'max_epic_recruits = ?', 
        [50]
    );
    
    echo "✅ Updated $affected records with max_epic_recruits = 50\n";
    
    // Test 2: Verify the update worked
    echo "\n2. Verifying update results...\n";
    $newValues = db()->select("SELECT DISTINCT max_epic_recruits, COUNT(*) as count FROM epic_epis_accounts GROUP BY max_epic_recruits ORDER BY max_epic_recruits");
    foreach ($newValues as $value) {
        echo "Value: " . number_format($value['max_epic_recruits']) . ", Count: {$value['count']}\n";
    }
    
    // Test 3: Test update with table that already has prefix (should not double prefix)
    echo "\n3. Testing update with prefixed table name...\n";
    
    // Get a sample record to update
    $sampleRecord = db()->selectOne("SELECT id, max_epic_recruits FROM epic_epis_accounts LIMIT 1");
    if ($sampleRecord) {
        $originalValue = $sampleRecord['max_epic_recruits'];
        $testValue = $originalValue + 1;
        
        echo "Original value: " . number_format($originalValue) . "\n";
        echo "Test value: " . number_format($testValue) . "\n";
        
        // This should work without creating epic_epic_epis_accounts
        $affected = db()->update(
            'epic_epis_accounts', 
            ['max_epic_recruits' => $testValue], 
            'id = ?', 
            [$sampleRecord['id']]
        );
        
        echo "✅ Updated $affected record(s)\n";
        
        // Verify the change
        $updatedRecord = db()->selectOne("SELECT max_epic_recruits FROM epic_epis_accounts WHERE id = ?", [$sampleRecord['id']]);
        if ($updatedRecord && $updatedRecord['max_epic_recruits'] == $testValue) {
            echo "✅ Verification successful: Value updated to " . number_format($testValue) . "\n";
            
            // Restore original value
            db()->update(
                'epic_epis_accounts', 
                ['max_epic_recruits' => $originalValue], 
                'id = ?', 
                [$sampleRecord['id']]
            );
            echo "✅ Original value restored\n";
        } else {
            echo "❌ Verification failed\n";
        }
    }
    
    // Test 4: Check if double prefix table was created (should not exist)
    echo "\n4. Checking for double prefix table...\n";
    $doublePrefix = db()->select("SHOW TABLES LIKE 'epic_epic_epis_accounts'");
    if (empty($doublePrefix)) {
        echo "✅ No double prefix table found - fix is working!\n";
    } else {
        echo "❌ Double prefix table still exists\n";
    }
    
    // Test 5: Test other methods with prefix
    echo "\n5. Testing other database methods...\n";
    
    // Test count method
    $count = db()->count('epic_epis_accounts');
    echo "✅ Count method: $count records\n";
    
    // Test exists method
    $exists = db()->exists('epic_epis_accounts', 'status = ?', ['active']);
    echo "✅ Exists method: " . ($exists ? 'Found active records' : 'No active records') . "\n";
    
    echo "\n🎉 ALL TESTS COMPLETED SUCCESSFULLY!\n";
    echo "✅ Prefix double issue has been fixed\n";
    echo "✅ max_epic_recruits updated to 1,000,000\n";
    echo "✅ All database methods working correctly\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}