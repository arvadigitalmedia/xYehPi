<?php
/**
 * EPIC Fix EPIS 1 Counter Mismatch
 * Memperbaiki ketidaksesuaian counter EPIS 1 (Admin Official)
 * 
 * @author TRAE AI Assistant
 * @version 1.0.0
 */

require_once 'bootstrap.php';

echo "=== FIX EPIS 1 COUNTER MISMATCH ===\n";
echo "Memperbaiki ketidaksesuaian counter EPIS 1 (Admin Official)\n\n";

try {
    // Start transaction
    db()->beginTransaction();
    
    // 1. Cek status EPIS 1 saat ini
    echo "1. Mengecek status EPIS 1 saat ini...\n";
    
    $epis_1 = db()->selectOne(
        "SELECT * FROM epic_epis_accounts WHERE id = 1"
    );
    
    if (!$epis_1) {
        throw new Exception("EPIS 1 tidak ditemukan!");
    }
    
    echo "   EPIS 1 Status:\n";
    echo "   - Name: {$epis_1['name']}\n";
    echo "   - Email: {$epis_1['email']}\n";
    echo "   - Status: {$epis_1['status']}\n";
    echo "   - Current Counter: {$epis_1['counter']}\n";
    
    // 2. Hitung actual members di jaringan EPIS 1
    echo "\n2. Menghitung actual members di jaringan EPIS 1...\n";
    
    $actual_count = db()->selectValue(
        "SELECT COUNT(*) FROM users WHERE epis_supervisor_id = 1"
    );
    
    echo "   Actual members count: {$actual_count}\n";
    echo "   Counter difference: " . ($epis_1['counter'] - $actual_count) . "\n";
    
    // 3. Cek apakah ada orphaned records atau duplicate
    echo "\n3. Mengecek orphaned records dan duplicates...\n";
    
    $orphaned_users = db()->select(
        "SELECT u.id, u.name, u.email, u.epis_supervisor_id 
         FROM users u 
         LEFT JOIN epic_epis_accounts e ON u.epis_supervisor_id = e.id 
         WHERE u.epis_supervisor_id IS NOT NULL AND e.id IS NULL"
    );
    
    if (!empty($orphaned_users)) {
        echo "   Found " . count($orphaned_users) . " orphaned user records:\n";
        foreach ($orphaned_users as $user) {
            echo "   - User {$user['id']}: {$user['name']} (EPIS: {$user['epis_supervisor_id']})\n";
        }
    } else {
        echo "   No orphaned user records found.\n";
    }
    
    // 4. Cek duplicate EPIS assignments
    $duplicate_assignments = db()->select(
        "SELECT epis_supervisor_id, COUNT(*) as count 
         FROM users 
         WHERE epis_supervisor_id IS NOT NULL 
         GROUP BY epis_supervisor_id 
         HAVING COUNT(*) != (
             SELECT counter FROM epic_epis_accounts 
             WHERE id = epis_supervisor_id
         )"
    );
    
    if (!empty($duplicate_assignments)) {
        echo "   Found mismatched EPIS assignments:\n";
        foreach ($duplicate_assignments as $assignment) {
            $epis_counter = db()->selectValue(
                "SELECT counter FROM epic_epis_accounts WHERE id = ?",
                [$assignment['epis_supervisor_id']]
            );
            echo "   - EPIS {$assignment['epis_supervisor_id']}: Counter={$epis_counter}, Actual={$assignment['count']}\n";
        }
    }
    
    // 5. Perbaiki EPIS 1 counter
    echo "\n4. Memperbaiki EPIS 1 counter...\n";
    
    $old_counter = $epis_1['counter'];
    
    // Update counter to match actual count
    db()->update('epic_epis_accounts', [
        'counter' => $actual_count,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [1]);
    
    echo "   ✓ Updated EPIS 1 counter: {$old_counter} → {$actual_count}\n";
    
    // 6. Log the fix
    echo "\n5. Logging perbaikan...\n";
    
    // Create activity log entry
    db()->insert('epic_activity_logs', [
        'user_id' => null,
        'action' => 'epis_counter_fix',
        'description' => "Fixed EPIS 1 counter mismatch: {$old_counter} → {$actual_count}",
        'metadata' => json_encode([
            'epis_id' => 1,
            'old_counter' => $old_counter,
            'new_counter' => $actual_count,
            'difference' => $old_counter - $actual_count,
            'actual_members' => $actual_count,
            'fix_type' => 'manual_correction',
            'script_version' => '1.0.0'
        ]),
        'ip_address' => 'system',
        'user_agent' => 'fix-script',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    echo "   ✓ Activity logged\n";
    
    // 7. Verifikasi perbaikan
    echo "\n6. Verifikasi perbaikan...\n";
    
    $updated_epis = db()->selectOne(
        "SELECT * FROM epic_epis_accounts WHERE id = 1"
    );
    
    $verification_count = db()->selectValue(
        "SELECT COUNT(*) FROM users WHERE epis_supervisor_id = 1"
    );
    
    echo "   EPIS 1 After Fix:\n";
    echo "   - Counter: {$updated_epis['counter']}\n";
    echo "   - Actual Members: {$verification_count}\n";
    echo "   - Match Status: " . ($updated_epis['counter'] == $verification_count ? "✓ MATCHED" : "✗ STILL MISMATCHED") . "\n";
    
    if ($updated_epis['counter'] != $verification_count) {
        throw new Exception("Verification failed! Counter still doesn't match actual count.");
    }
    
    // 8. Commit transaction
    db()->commit();
    
    echo "\n=== PERBAIKAN BERHASIL ===\n";
    echo "EPIS 1 counter telah diperbaiki dari {$old_counter} menjadi {$actual_count}\n";
    echo "Semua data telah terverifikasi dan konsisten.\n";
    
    // 9. Recommendations
    echo "\n=== REKOMENDASI ===\n";
    echo "1. Monitor EPIS counter secara berkala dengan script monitoring\n";
    echo "2. Implementasikan atomic counter updates di semua operasi EPIS\n";
    echo "3. Setup alerting untuk deteksi mismatch otomatis\n";
    echo "4. Review dan audit kode yang mengupdate EPIS counter\n";
    
} catch (Exception $e) {
    // Rollback on error
    if (db()->inTransaction()) {
        db()->rollback();
    }
    
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes made.\n";
    exit(1);
}

echo "\nScript completed successfully.\n";