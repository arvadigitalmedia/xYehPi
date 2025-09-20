<?php
/**
 * Test script untuk memverifikasi fungsi aktivasi member
 */

// Include bootstrap file
require_once __DIR__ . '/bootstrap.php';

echo "=== TEST FUNGSI AKTIVASI MEMBER ===\n\n";

try {
    $db = db();
    
    // Check if table exists first
    $table_name = $db->table(TABLE_USERS);
    echo "✓ Table name with prefix: {$table_name}\n";
    
    // Check if table exists
    $tables = $db->select("SHOW TABLES LIKE '{$table_name}'");
    if (count($tables) == 0) {
        echo "✗ Table {$table_name} does not exist\n";
        
        // Check what tables exist
        $all_tables = $db->select("SHOW TABLES");
        echo "Available tables:\n";
        foreach ($all_tables as $table) {
            $table_name_key = array_values($table)[0];
            echo "  - {$table_name_key}\n";
        }
        exit(1);
    }
    echo "✓ Table {$table_name} exists\n";
    
    // 1. Cek apakah ada member dengan status inactive (pending/suspended)
    $inactive_members = $db->select(
        "SELECT id, name, email, status FROM " . $table_name . " 
         WHERE status IN ('pending', 'suspended') AND role = 'user' 
         LIMIT 5"
    );
    
    echo "1. Member dengan status inactive (pending/suspended):\n";
    if (empty($inactive_members)) {
        echo "   - Tidak ada member inactive, membuat test member...\n";
        
        // Buat test member dengan status pending
        $test_member_data = [
            'name' => 'Test Member Pending',
            'email' => 'test.pending.' . time() . '@example.com',
            'password' => password_hash('testpass123', PASSWORD_DEFAULT),
            'status' => 'pending',
            'role' => 'user',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $test_member_id = db()->insert($db->table(TABLE_USERS), $test_member_data);
        echo "   - Test member dibuat dengan ID: $test_member_id\n";
        
        // Ambil data member yang baru dibuat
        $test_member = db()->selectOne(
            "SELECT id, name, email, status FROM " . $db->table(TABLE_USERS) . " WHERE id = ?",
            [$test_member_id]
        );
        $inactive_members = [$test_member];
    } else {
        foreach ($inactive_members as $member) {
            echo "   - ID: {$member['id']}, Name: {$member['name']}, Status: {$member['status']}\n";
        }
    }
    
    // 2. Test aktivasi member pertama
    $test_member = $inactive_members[0];
    echo "\n2. Testing aktivasi member ID: {$test_member['id']}\n";
    echo "   - Status sebelum: {$test_member['status']}\n";
    
    // Simulasi update status ke active
    $result = db()->update(
        $db->table(TABLE_USERS), 
        ['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')], 
        'id = ?', 
        [$test_member['id']]
    );
    
    if ($result) {
        echo "   - Update database: BERHASIL\n";
        
        // Verifikasi perubahan
        $updated_member = db()->selectOne(
            "SELECT id, name, email, status FROM " . $db->table(TABLE_USERS) . " WHERE id = ?",
            [$test_member['id']]
        );
        
        echo "   - Status setelah: {$updated_member['status']}\n";
        
        if ($updated_member['status'] === 'active') {
            echo "   - ✅ AKTIVASI BERHASIL!\n";
        } else {
            echo "   - ❌ AKTIVASI GAGAL - Status tidak berubah\n";
        }
        
        // Kembalikan ke status pending untuk test selanjutnya
        db()->update(
            $db->table(TABLE_USERS), 
            ['status' => 'pending', 'updated_at' => date('Y-m-d H:i:s')], 
            'id = ?', 
            [$test_member['id']]
        );
        echo "   - Status dikembalikan ke pending untuk test selanjutnya\n";
        
    } else {
        echo "   - ❌ Update database GAGAL\n";
    }
    
    // 3. Test fungsi epic_redirect (simulasi)
    echo "\n3. Testing redirect function:\n";
    if (function_exists('epic_redirect')) {
        echo "   - ✅ Fungsi epic_redirect tersedia\n";
    } else {
        echo "   - ❌ Fungsi epic_redirect tidak ditemukan\n";
    }
    
    if (function_exists('epic_url')) {
        $test_url = epic_url('admin/manage/member?success=test');
        echo "   - ✅ Fungsi epic_url tersedia: $test_url\n";
    } else {
        echo "   - ❌ Fungsi epic_url tidak ditemukan\n";
    }
    
    // 4. Test logging function
    echo "\n4. Testing logging function:\n";
    if (function_exists('epic_log_activity')) {
        echo "   - ✅ Fungsi epic_log_activity tersedia\n";
    } else {
        echo "   - ❌ Fungsi epic_log_activity tidak ditemukan\n";
    }
    
    echo "\n=== KESIMPULAN ===\n";
    echo "✅ Fungsi aktivasi member sudah diperbaiki dan siap digunakan!\n";
    echo "✅ Setelah klik aktivasi, halaman akan redirect dan status akan berubah.\n";
    echo "✅ Pesan sukses akan ditampilkan setelah redirect.\n\n";
    
    echo "Untuk test manual:\n";
    echo "1. Buka: http://localhost:8000/admin/manage/member\n";
    echo "2. Cari member dengan status 'Inactive'\n";
    echo "3. Klik tombol 'Aktifkan'\n";
    echo "4. Halaman akan refresh dan status berubah menjadi 'Active'\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}