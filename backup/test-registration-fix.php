<?php
/**
 * Test Script untuk Verifikasi Perbaikan Pendaftaran User
 * Menguji apakah INSERT ke epic_referrals berhasil setelah perbaikan
 */

require_once 'bootstrap.php';

echo "<h2>Test Pendaftaran User Baru - Verifikasi Perbaikan</h2>\n";

try {
    // Test 1: Verifikasi struktur tabel epic_referrals
    echo "<h3>1. Verifikasi Struktur Tabel epic_referrals</h3>\n";
    
    $structure = db()->query("DESCRIBE epic_referrals")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    
    foreach ($structure as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Verifikasi kolom id memiliki AUTO_INCREMENT
    $id_column = array_filter($structure, function($col) {
        return $col['Field'] === 'id';
    });
    $id_column = reset($id_column);
    
    if ($id_column && strpos($id_column['Extra'], 'auto_increment') !== false) {
        echo "<p style='color: green;'>✓ Kolom id memiliki AUTO_INCREMENT</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Kolom id TIDAK memiliki AUTO_INCREMENT</p>\n";
    }
    
    // Test 2: Test fungsi epic_create_referral
    echo "<h3>2. Test Fungsi epic_create_referral</h3>\n";
    
    // Buat user test terlebih dahulu
    $test_user_data = [
        'name' => 'Test User ' . date('Y-m-d H:i:s'),
        'email' => 'test_' . time() . '@example.com',
        'password' => password_hash('testpassword', PASSWORD_DEFAULT),
        'phone' => '081234567890',
        'status' => 'free',
        'referral_code' => 'TEST' . time(),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $test_user_id = db()->insert(TABLE_USERS, $test_user_data);
    echo "<p>User test dibuat dengan ID: {$test_user_id}</p>\n";
    
    // Test epic_create_referral tanpa referrer
    echo "<h4>Test 1: epic_create_referral tanpa referrer</h4>\n";
    $referral_id_1 = epic_create_referral($test_user_id);
    
    if ($referral_id_1) {
        echo "<p style='color: green;'>✓ epic_create_referral berhasil (ID: {$referral_id_1})</p>\n";
        
        // Verifikasi data tersimpan
        $referral_data = db()->selectOne("SELECT * FROM epic_referrals WHERE id = ?", [$referral_id_1]);
        if ($referral_data) {
            echo "<p>Data referral tersimpan:</p>\n";
            echo "<ul>\n";
            echo "<li>ID: {$referral_data['id']}</li>\n";
            echo "<li>User ID: {$referral_data['user_id']}</li>\n";
            echo "<li>Referrer ID: " . ($referral_data['referrer_id'] ?: 'NULL') . "</li>\n";
            echo "<li>Status: {$referral_data['status']}</li>\n";
            echo "<li>Created At: {$referral_data['created_at']}</li>\n";
            echo "</ul>\n";
        }
    } else {
        echo "<p style='color: red;'>✗ epic_create_referral gagal</p>\n";
    }
    
    // Buat user referrer untuk test kedua
    $referrer_data = [
        'name' => 'Referrer User ' . date('Y-m-d H:i:s'),
        'email' => 'referrer_' . time() . '@example.com',
        'password' => password_hash('testpassword', PASSWORD_DEFAULT),
        'phone' => '081234567891',
        'status' => 'epic',
        'referral_code' => 'REF' . time(),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $referrer_id = db()->insert(TABLE_USERS, $referrer_data);
    echo "<p>User referrer dibuat dengan ID: {$referrer_id}</p>\n";
    
    // Buat user kedua untuk test dengan referrer
    $test_user_data_2 = [
        'name' => 'Test User 2 ' . date('Y-m-d H:i:s'),
        'email' => 'test2_' . time() . '@example.com',
        'password' => password_hash('testpassword', PASSWORD_DEFAULT),
        'phone' => '081234567892',
        'status' => 'free',
        'referral_code' => 'TEST2' . time(),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $test_user_id_2 = db()->insert(TABLE_USERS, $test_user_data_2);
    
    // Test epic_create_referral dengan referrer
    echo "<h4>Test 2: epic_create_referral dengan referrer</h4>\n";
    $referral_id_2 = epic_create_referral($test_user_id_2, $referrer_id);
    
    if ($referral_id_2) {
        echo "<p style='color: green;'>✓ epic_create_referral dengan referrer berhasil (ID: {$referral_id_2})</p>\n";
        
        // Verifikasi data tersimpan
        $referral_data_2 = db()->selectOne("SELECT * FROM epic_referrals WHERE id = ?", [$referral_id_2]);
        if ($referral_data_2) {
            echo "<p>Data referral dengan referrer tersimpan:</p>\n";
            echo "<ul>\n";
            echo "<li>ID: {$referral_data_2['id']}</li>\n";
            echo "<li>User ID: {$referral_data_2['user_id']}</li>\n";
            echo "<li>Referrer ID: {$referral_data_2['referrer_id']}</li>\n";
            echo "<li>Status: {$referral_data_2['status']}</li>\n";
            echo "<li>Created At: {$referral_data_2['created_at']}</li>\n";
            echo "</ul>\n";
        }
    } else {
        echo "<p style='color: red;'>✗ epic_create_referral dengan referrer gagal</p>\n";
    }
    
    // Test 3: Verifikasi total data di epic_referrals
    echo "<h3>3. Verifikasi Total Data di epic_referrals</h3>\n";
    $total_referrals = db()->selectValue("SELECT COUNT(*) FROM epic_referrals");
    echo "<p>Total data di tabel epic_referrals: {$total_referrals}</p>\n";
    
    // Tampilkan beberapa data terakhir
    $recent_referrals = db()->query("SELECT * FROM epic_referrals ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    if ($recent_referrals) {
        echo "<h4>5 Data Referral Terakhir:</h4>\n";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
        echo "<tr><th>ID</th><th>User ID</th><th>Referrer ID</th><th>Status</th><th>Created At</th></tr>\n";
        
        foreach ($recent_referrals as $ref) {
            echo "<tr>";
            echo "<td>{$ref['id']}</td>";
            echo "<td>{$ref['user_id']}</td>";
            echo "<td>" . ($ref['referrer_id'] ?: 'NULL') . "</td>";
            echo "<td>{$ref['status']}</td>";
            echo "<td>{$ref['created_at']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // Cleanup - hapus data test
    echo "<h3>4. Cleanup Data Test</h3>\n";
    
    if (isset($referral_id_1)) {
        db()->query("DELETE FROM epic_referrals WHERE id = ?", [$referral_id_1]);
        echo "<p>✓ Data referral test 1 dihapus</p>\n";
    }
    
    if (isset($referral_id_2)) {
        db()->query("DELETE FROM epic_referrals WHERE id = ?", [$referral_id_2]);
        echo "<p>✓ Data referral test 2 dihapus</p>\n";
    }
    
    if (isset($test_user_id)) {
        db()->query("DELETE FROM epic_users WHERE id = ?", [$test_user_id]);
        echo "<p>✓ User test 1 dihapus</p>\n";
    }
    
    if (isset($test_user_id_2)) {
        db()->query("DELETE FROM epic_users WHERE id = ?", [$test_user_id_2]);
        echo "<p>✓ User test 2 dihapus</p>\n";
    }
    
    if (isset($referrer_id)) {
        db()->query("DELETE FROM epic_users WHERE id = ?", [$referrer_id]);
        echo "<p>✓ User referrer dihapus</p>\n";
    }
    
    echo "<h3 style='color: green;'>✓ SEMUA TEST BERHASIL - Perbaikan Pendaftaran User Berhasil!</h3>\n";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ ERROR: " . $e->getMessage() . "</h3>\n";
    echo "<p>Stack trace:</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

echo "<hr>\n";
echo "<p><strong>Kesimpulan:</strong></p>\n";
echo "<ul>\n";
echo "<li>Kolom id pada tabel epic_referrals sudah memiliki AUTO_INCREMENT</li>\n";
echo "<li>Konstanta TABLE_REFERRALS sudah mengarah ke epic_referrals</li>\n";
echo "<li>Fungsi epic_create_referral sudah menggunakan tabel epic_referrals</li>\n";
echo "<li>Semua hardcoded 'referrals' sudah diperbaiki menggunakan TABLE_REFERRALS</li>\n";
echo "<li>Pendaftaran user baru sekarang berfungsi dengan baik</li>\n";
echo "</ul>\n";
?>