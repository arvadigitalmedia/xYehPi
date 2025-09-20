<?php
/**
 * Script untuk membuat data test EPIS Accounts
 * Untuk testing tampilan tabel
 */

// Bypass security check
define('EPIC_DIRECT_ACCESS', true);
require_once 'bootstrap.php';

echo "<h2>Membuat Data Test EPIS Accounts</h2>\n";

try {
    // 1. Buat user test untuk EPIS
    echo "<h3>1. Membuat User Test</h3>\n";
    
    // Gunakan user yang sudah ada saja (karena tabel epic_users tidak memiliki AUTO_INCREMENT)
    $existing_users = db()->select('SELECT id, name, email FROM epic_users ORDER BY id LIMIT 3');
    
    $user_ids = [];
    foreach ($existing_users as $user) {
        $user_ids[] = $user['id'];
        echo "<p>✅ Menggunakan user yang sudah ada: {$user['name']} (ID: {$user['id']})</p>\n";
    }
    
    if (empty($user_ids)) {
        echo "<p>❌ Tidak ada user yang tersedia. Silakan buat user terlebih dahulu.</p>\n";
        exit;
    }
    
    // 2. Buat EPIS Accounts
    echo "<h3>2. Membuat EPIS Accounts</h3>\n";
    
    // Generate EPIS codes yang unik
    $timestamp = time();
    $epis_accounts = [
        [
            'user_id' => $user_ids[0],
            'epis_code' => 'EPIS' . substr($timestamp, -6) . '01',
            'territory_name' => 'Jakarta Pusat Test',
            'territory_description' => 'Wilayah Jakarta Pusat dan sekitarnya (Test Data)',
            'max_epic_recruits' => 50,
            'status' => 'active'
        ]
    ];
    
    // Tambahkan EPIS accounts untuk user baru jika ada
    if (count($user_ids) > 1) {
        $epis_accounts[] = [
            'user_id' => $user_ids[1],
            'epis_code' => 'EPIS' . substr($timestamp, -6) . '02',
            'territory_name' => 'Bandung Utara Test',
            'territory_description' => 'Wilayah Bandung Utara dan Lembang (Test Data)',
            'max_epic_recruits' => 30,
            'status' => 'suspended'
        ];
    }
    
    if (count($user_ids) > 2) {
        $epis_accounts[] = [
            'user_id' => $user_ids[2],
            'epis_code' => 'EPIS' . substr($timestamp, -6) . '03',
            'territory_name' => 'Surabaya Timur Test',
            'territory_description' => 'Wilayah Surabaya Timur dan Sidoarjo (Test Data)',
            'max_epic_recruits' => 40,
            'status' => 'active'
        ];
    }
    
    $epis_ids = [];
    foreach ($epis_accounts as $epis_data) {
        // Cek apakah EPIS account sudah ada (gunakan nama tabel lengkap untuk query)
        $existing = db()->selectOne("SELECT id FROM epic_epis_accounts WHERE user_id = ?", [$epis_data['user_id']]);
        
        if ($existing) {
            $epis_ids[] = $existing['id'];
            echo "<p>✅ EPIS Account untuk User ID {$epis_data['user_id']} sudah ada (ID: {$existing['id']})</p>\n";
        } else {
            // Insert menggunakan nama tabel tanpa prefix (karena method insert menambahkan prefix)
            $epis_id = db()->insert('epis_accounts', $epis_data);
            $epis_ids[] = $epis_id;
            echo "<p>✅ EPIS Account {$epis_data['territory_name']} berhasil dibuat (ID: {$epis_id})</p>\n";
        }
    }
    
    // 3. Membuat Data Network (untuk total commissions)
    echo "<h3>3. Membuat Data Network Test</h3>\n";
    
    // Buat data network hanya jika ada EPIS account dan user yang cukup
    $network_data = [];
    if (!empty($epis_ids) && count($user_ids) > 1) {
        $network_data[] = [
            'epis_id' => $epis_ids[0],
            'epic_user_id' => $user_ids[1], // User 2 direkrut oleh EPIS 1
            'recruitment_type' => 'direct',
            'commission_rate' => 10.00,
            'status' => 'active',
            'notes' => 'Direct recruitment by EPIS - Test Data'
        ];
        
        // Tambahkan network data kedua jika ada user ketiga
        if (count($user_ids) > 2) {
            $network_data[] = [
                'epis_id' => $epis_ids[0],
                'epic_user_id' => $user_ids[2], // User 3 direkrut oleh EPIS 1
                'recruitment_type' => 'direct',
                'commission_rate' => 10.00,
                'status' => 'active',
                'notes' => 'Direct recruitment by EPIS - Test Data'
            ];
        }
    }
    
    foreach ($network_data as $network) {
        // Cek apakah data network sudah ada (gunakan nama tabel lengkap untuk query)
        $existing = db()->selectOne("SELECT id FROM epic_epis_networks WHERE epis_id = ? AND epic_user_id = ?", 
            [$network['epis_id'], $network['epic_user_id']]);
        
        if ($existing) {
            echo "<p>✅ Network data untuk EPIS {$network['epis_id']} → {$network['epic_user_id']} sudah ada</p>\n";
        } else {
            // Insert menggunakan nama tabel tanpa prefix (karena method insert menambahkan prefix)
            $network_id = db()->insert('epis_networks', $network);
            echo "<p>✅ Network data berhasil dibuat (ID: {$network_id}) - Rate: {$network['commission_rate']}%</p>\n";
        }
    }
    
    echo "<h3>✅ Data Test Berhasil Dibuat</h3>\n";
    echo "<p>Sekarang Anda dapat mengakses halaman EPIS Management untuk melihat tampilan tabel.</p>\n";
    echo "<p><a href='" . epic_url('admin/manage/epis') . "' target='_blank'>Buka Halaman EPIS Management</a></p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}
?>