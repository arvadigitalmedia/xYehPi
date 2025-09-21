<?php
/**
 * Test Profile Update Debug
 * Untuk mengidentifikasi masalah query UPDATE pada edit profil
 */

require_once 'bootstrap.php';

echo "=== PROFILE UPDATE DEBUG TEST ===\n\n";

try {
    // Test 1: Cek prefix tabel
    echo "1. Testing table prefix...\n";
    $db = db();
    echo "   - DB Prefix: " . $db->getPrefix() . "\n";
    echo "   - Table 'epic_users' akan menjadi: " . $db->table('epic_users') . "\n";
    echo "   - Table 'users' akan menjadi: " . $db->table('users') . "\n\n";
    
    // Test 2: Cek apakah tabel epic_users ada
    echo "2. Testing table existence...\n";
    try {
        $tables = $db->select("SHOW TABLES LIKE 'epic_users'");
        if (!empty($tables)) {
            echo "   ✓ Tabel 'epic_users' ditemukan\n";
        } else {
            echo "   ✗ Tabel 'epic_users' tidak ditemukan\n";
        }
        
        $tables_with_prefix = $db->select("SHOW TABLES LIKE 'epic_epic_users'");
        if (!empty($tables_with_prefix)) {
            echo "   ⚠️  Tabel 'epic_epic_users' juga ditemukan (double prefix!)\n";
        } else {
            echo "   ✓ Tidak ada tabel dengan double prefix\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Error checking tables: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 3: Cek struktur kolom tabel epic_users
    echo "3. Testing table structure...\n";
    try {
        $columns = $db->select("DESCRIBE epic_users");
        echo "   ✓ Kolom yang tersedia di tabel epic_users:\n";
        foreach ($columns as $col) {
            echo "     - {$col['Field']} ({$col['Type']})\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Error describing table: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 4: Cari user test untuk update
    echo "4. Finding test user...\n";
    try {
        $test_user = $db->selectOne("SELECT id, name, email, phone, address FROM epic_users WHERE phone = '85860437327' LIMIT 1");
        if ($test_user) {
            echo "   ✓ User dengan nomor 85860437327 ditemukan:\n";
            echo "     - ID: {$test_user['id']}\n";
            echo "     - Name: {$test_user['name']}\n";
            echo "     - Email: {$test_user['email']}\n";
            echo "     - Phone: {$test_user['phone']}\n";
            echo "     - Address: " . ($test_user['address'] ?? 'NULL') . "\n";
        } else {
            echo "   ⚠️  User dengan nomor 85860437327 tidak ditemukan\n";
            // Cari user lain untuk test
            $test_user = $db->selectOne("SELECT id, name, email, phone, address FROM epic_users LIMIT 1");
            if ($test_user) {
                echo "   ✓ Menggunakan user lain untuk test:\n";
                echo "     - ID: {$test_user['id']}\n";
                echo "     - Name: {$test_user['name']}\n";
                echo "     - Phone: " . ($test_user['phone'] ?? 'NULL') . "\n";
            }
        }
    } catch (Exception $e) {
        echo "   ✗ Error finding user: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 5: Test query UPDATE dengan cara yang benar
    if (isset($test_user) && $test_user) {
        echo "5. Testing UPDATE query...\n";
        
        // Backup data asli
        $original_phone = $test_user['phone'];
        $original_address = $test_user['address'];
        
        try {
            // Test 1: Update menggunakan nama tabel tanpa prefix (BENAR)
            echo "   Testing update with table name 'users'...\n";
            $update_data = [
                'phone' => '6285860437327',
                'address' => 'Test Address Update',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $affected = $db->update('users', $update_data, 'id = ?', [$test_user['id']]);
            echo "     ✓ Update berhasil, {$affected} row affected\n";
            
            // Verifikasi perubahan
            $updated_user = $db->selectOne("SELECT phone, address FROM epic_users WHERE id = ?", [$test_user['id']]);
            echo "     ✓ Phone updated to: {$updated_user['phone']}\n";
            echo "     ✓ Address updated to: {$updated_user['address']}\n";
            
        } catch (Exception $e) {
            echo "     ✗ Update failed: " . $e->getMessage() . "\n";
            
            // Test 2: Coba dengan nama tabel lengkap
            echo "   Testing update with full table name 'epic_users'...\n";
            try {
                // Gunakan query langsung untuk menghindari double prefix
                $sql = "UPDATE epic_users SET phone = ?, address = ?, updated_at = ? WHERE id = ?";
                $stmt = $db->query($sql, ['6285860437327', 'Test Address Direct', date('Y-m-d H:i:s'), $test_user['id']]);
                echo "     ✓ Direct query berhasil\n";
                
                $updated_user = $db->selectOne("SELECT phone, address FROM epic_users WHERE id = ?", [$test_user['id']]);
                echo "     ✓ Phone updated to: {$updated_user['phone']}\n";
                echo "     ✓ Address updated to: {$updated_user['address']}\n";
                
            } catch (Exception $e2) {
                echo "     ✗ Direct query failed: " . $e2->getMessage() . "\n";
            }
        }
        
        // Kembalikan data asli
        try {
            $restore_data = [
                'phone' => $original_phone,
                'address' => $original_address,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $db->update('users', $restore_data, 'id = ?', [$test_user['id']]);
            echo "   ✓ Data dikembalikan ke kondisi semula\n";
        } catch (Exception $e) {
            echo "   ⚠️  Gagal mengembalikan data: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== DEBUG TEST COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
?>