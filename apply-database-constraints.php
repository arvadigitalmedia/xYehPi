<?php
/**
 * Apply Database Constraints - Quick Fix Checklist
 * Menerapkan unique constraints dan indexes untuk performa dan integritas data
 */

require_once 'bootstrap.php';

echo "=== APPLYING DATABASE CONSTRAINTS ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = db();
    
    // Backup reminder
    echo "⚠️  REMINDER: Pastikan sudah backup database!\n";
    echo "   mysqldump -u username -p database_name > backup_" . date('Ymd_His') . ".sql\n\n";
    
    echo "Applying constraints and indexes...\n\n";
    
    // 1. Add unique constraint for email (jika belum ada)
    echo "1. Adding unique constraint for email...\n";
    try {
        $db->query("ALTER TABLE epic_users ADD UNIQUE KEY unique_email (email)");
        echo "   ✅ Unique constraint for email added\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ℹ️  Unique constraint for email already exists\n";
        } else {
            throw $e;
        }
    }
    
    // 2. Add unique constraint for referral_code (jika belum ada)
    echo "2. Adding unique constraint for referral_code...\n";
    try {
        $db->query("ALTER TABLE epic_users ADD UNIQUE KEY unique_referral_code (referral_code)");
        echo "   ✅ Unique constraint for referral_code added\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ℹ️  Unique constraint for referral_code already exists\n";
        } else {
            throw $e;
        }
    }
    
    // 3. Add index for status (jika belum ada)
    echo "3. Adding index for status...\n";
    try {
        $db->query("ALTER TABLE epic_users ADD INDEX idx_status (status)");
        echo "   ✅ Index for status added\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ℹ️  Index for status already exists\n";
        } else {
            throw $e;
        }
    }
    
    // 4. Add index for created_at (jika belum ada)
    echo "4. Adding index for created_at...\n";
    try {
        $db->query("ALTER TABLE epic_users ADD INDEX idx_created_at (created_at)");
        echo "   ✅ Index for created_at added\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ℹ️  Index for created_at already exists\n";
        } else {
            throw $e;
        }
    }
    
    // 5. Add composite index for epic_epis_accounts (jika belum ada)
    echo "5. Adding composite index for epic_epis_accounts...\n";
    try {
        $db->query("ALTER TABLE epic_epis_accounts ADD INDEX idx_status_count (status, current_epic_count)");
        echo "   ✅ Composite index for status and current_epic_count added\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ℹ️  Composite index already exists\n";
        } else {
            throw $e;
        }
    }
    
    // 6. Add index for user_id in epic_epis_accounts (jika belum ada)
    echo "6. Adding index for user_id in epic_epis_accounts...\n";
    try {
        $db->query("ALTER TABLE epic_epis_accounts ADD INDEX idx_user_id (user_id)");
        echo "   ✅ Index for user_id added\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ℹ️  Index for user_id already exists\n";
        } else {
            throw $e;
        }
    }
    
    // Verify indexes
    echo "\n--- VERIFICATION ---\n";
    echo "epic_users indexes:\n";
    $indexes = $db->select("SHOW INDEX FROM epic_users");
    foreach ($indexes as $index) {
        echo "  - {$index['Key_name']} on {$index['Column_name']} (unique: " . ($index['Non_unique'] ? 'no' : 'yes') . ")\n";
    }
    
    echo "\nepic_epis_accounts indexes:\n";
    $indexes = $db->select("SHOW INDEX FROM epic_epis_accounts");
    foreach ($indexes as $index) {
        echo "  - {$index['Key_name']} on {$index['Column_name']} (unique: " . ($index['Non_unique'] ? 'no' : 'yes') . ")\n";
    }
    
    echo "\n✅ DATABASE CONSTRAINTS APPLIED SUCCESSFULLY!\n";
    echo "\nNext steps:\n";
    echo "- Test duplicate email prevention\n";
    echo "- Test duplicate referral code prevention\n";
    echo "- Monitor query performance\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nRollback commands if needed:\n";
    echo "ALTER TABLE epic_users DROP INDEX unique_email;\n";
    echo "ALTER TABLE epic_users DROP INDEX unique_referral_code;\n";
    echo "ALTER TABLE epic_users DROP INDEX idx_status;\n";
    echo "ALTER TABLE epic_users DROP INDEX idx_created_at;\n";
    echo "ALTER TABLE epic_epis_accounts DROP INDEX idx_status_count;\n";
    echo "ALTER TABLE epic_epis_accounts DROP INDEX idx_user_id;\n";
    exit(1);
}
?>