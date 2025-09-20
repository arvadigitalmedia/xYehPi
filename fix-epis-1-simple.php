<?php
/**
 * EPIC Fix EPIS 1 Counter Mismatch - Simple Version
 * Memperbaiki ketidaksesuaian counter EPIS 1 (Admin Official)
 * 
 * @author TRAE AI Assistant
 * @version 1.0.0
 */

require_once 'bootstrap.php';

echo "=== FIX EPIS 1 COUNTER MISMATCH (SIMPLE) ===\n";
echo "Memperbaiki ketidaksesuaian counter EPIS 1 (Admin Official)\n\n";

try {
    // 1. Cek status EPIS 1 saat ini
    echo "1. Mengecek status EPIS 1 saat ini...\n";
    
    $epis_1 = db()->selectOne(
        "SELECT ea.*, u.name, u.email, u.status 
         FROM epic_epis_accounts ea 
         LEFT JOIN epic_users u ON ea.user_id = u.id 
         WHERE ea.id = 1"
    );
    
    if (!$epis_1) {
        throw new Exception("EPIS 1 tidak ditemukan!");
    }
    
    echo "   EPIS 1 Status:\n";
    echo "   - Name: " . ($epis_1['name'] ?? 'N/A') . "\n";
    echo "   - Email: " . ($epis_1['email'] ?? 'N/A') . "\n";
    echo "   - Status: " . ($epis_1['status'] ?? 'N/A') . "\n";
    echo "   - Current Counter: " . ($epis_1['current_epic_count'] ?? '0') . "\n";
    
    // 2. Hitung actual members di jaringan EPIS 1
    echo "\n2. Menghitung actual members di jaringan EPIS 1...\n";
    
    $actual_count = db()->selectValue(
        "SELECT COUNT(*) FROM epic_users WHERE epis_supervisor_id = 1"
    ) ?: 0;
    
    echo "   Actual members count: {$actual_count}\n";
    echo "   Counter difference: " . (($epis_1['counter'] ?? 0) - $actual_count) . "\n";
    
    // 3. Perbaiki EPIS 1 counter
    echo "\n3. Memperbaiki EPIS 1 counter...\n";
    
    $old_counter = $epis_1['current_epic_count'] ?? 0;
    
    // Update counter to match actual count
    $result = db()->update('epic_epis_accounts', [
        'current_epic_count' => $actual_count,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [1]);
    
    if ($result) {
        echo "   ✓ Updated EPIS 1 counter: {$old_counter} → {$actual_count}\n";
    } else {
        throw new Exception("Failed to update EPIS 1 counter");
    }
    
    // 4. Verifikasi perbaikan
    echo "\n4. Verifikasi perbaikan...\n";
    
    $updated_epis = db()->selectOne(
        "SELECT * FROM epic_epis_accounts WHERE id = 1"
    );
    
    $verification_count = db()->selectValue(
        "SELECT COUNT(*) FROM epic_users WHERE epis_supervisor_id = 1"
    ) ?: 0;
    
    echo "   EPIS 1 After Fix:\n";
    echo "   - Counter: " . ($updated_epis['current_epic_count'] ?? '0') . "\n";
    echo "   - Actual Members: {$verification_count}\n";
    echo "   - Match Status: " . (($updated_epis['current_epic_count'] ?? 0) == $verification_count ? "✓ MATCHED" : "✗ STILL MISMATCHED") . "\n";
    
    if (($updated_epis['current_epic_count'] ?? 0) != $verification_count) {
        throw new Exception("Verification failed! Counter still doesn't match actual count.");
    }
    
    // 5. Log the fix (optional)
    echo "\n5. Logging perbaikan...\n";
    
    try {
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
    } catch (Exception $log_error) {
        echo "   ⚠ Warning: Could not log activity: " . $log_error->getMessage() . "\n";
        echo "   (This is not critical - the fix was still successful)\n";
    }
    
    echo "\n=== PERBAIKAN BERHASIL ===\n";
    echo "EPIS 1 counter telah diperbaiki dari {$old_counter} menjadi {$actual_count}\n";
    echo "Semua data telah terverifikasi dan konsisten.\n";
    
    // 6. Recommendations
    echo "\n=== REKOMENDASI ===\n";
    echo "1. Monitor EPIS counter secara berkala dengan script monitoring\n";
    echo "2. Implementasikan atomic counter updates di semua operasi EPIS\n";
    echo "3. Setup alerting untuk deteksi mismatch otomatis\n";
    echo "4. Review dan audit kode yang mengupdate EPIS counter\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "No changes made or changes may be incomplete.\n";
    exit(1);
}

echo "\nScript completed successfully.\n";