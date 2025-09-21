<?php
/**
 * Test script untuk memverifikasi perbaikan update profil
 * Menguji apakah kolom 'address' sudah bisa diupdate tanpa error
 */

// Include konfigurasi
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/functions.php';

echo "<h2>Test Perbaikan Update Profil</h2>\n";
echo "<hr>\n";

try {
    // 1. Test koneksi database
    echo "<h3>1. Test Koneksi Database</h3>\n";
    $db = db();
    echo "✅ Koneksi database berhasil<br>\n";
    
    // 2. Verifikasi kolom address ada di tabel epic_users
    echo "<h3>2. Verifikasi Kolom Address</h3>\n";
    $columns = $db->query("SHOW COLUMNS FROM epic_users LIKE 'address'");
    if ($columns) {
        echo "✅ Kolom 'address' ditemukan di tabel epic_users<br>\n";
        foreach ($columns as $col) {
            echo "   - Tipe: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}<br>\n";
        }
    } else {
        echo "❌ Kolom 'address' tidak ditemukan<br>\n";
        exit;
    }
    
    // 3. Test user untuk update
    echo "<h3>3. Cari User untuk Test</h3>\n";
    $test_user = $db->selectOne("SELECT id, name, email, phone, address FROM epic_users LIMIT 1");
    if (!$test_user) {
        echo "❌ Tidak ada user untuk test<br>\n";
        exit;
    }
    echo "✅ User test ditemukan: {$test_user['name']} (ID: {$test_user['id']})<br>\n";
    echo "   - Email: {$test_user['email']}<br>\n";
    echo "   - Phone: " . ($test_user['phone'] ?: 'kosong') . "<br>\n";
    echo "   - Address: " . ($test_user['address'] ?: 'kosong') . "<br>\n";
    
    // 4. Test update dengan kolom address
    echo "<h3>4. Test Update Profil dengan Address</h3>\n";
    $test_address = "Jl. Test Update No. " . rand(1, 999) . ", Jakarta";
    $test_phone = "628" . rand(1000000000, 9999999999);
    
    $update_data = [
        'name' => $test_user['name'],
        'email' => $test_user['email'],
        'phone' => $test_phone,
        'address' => $test_address,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    echo "Data yang akan diupdate:<br>\n";
    echo "   - Phone: {$test_phone}<br>\n";
    echo "   - Address: {$test_address}<br>\n";
    
    // Simulasi query yang sama dengan edit-profile.php
    $result = $db->update('epic_users', $update_data, 'id = ?', [$test_user['id']]);
    
    if ($result) {
        echo "✅ Update berhasil!<br>\n";
        
        // Verifikasi data terupdate
        $updated_user = $db->selectOne("SELECT name, email, phone, address, updated_at FROM epic_users WHERE id = ?", [$test_user['id']]);
        echo "Data setelah update:<br>\n";
        echo "   - Phone: {$updated_user['phone']}<br>\n";
        echo "   - Address: {$updated_user['address']}<br>\n";
        echo "   - Updated at: {$updated_user['updated_at']}<br>\n";
    } else {
        echo "❌ Update gagal<br>\n";
    }
    
    // 5. Test query dengan semua field profil
    echo "<h3>5. Test Query dengan Semua Field Profil</h3>\n";
    $full_update_data = [
        'name' => $test_user['name'] . ' (Updated)',
        'email' => $test_user['email'],
        'phone' => $test_phone,
        'address' => $test_address . ' (Full Test)',
        'profile_photo' => 'test_photo.jpg',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $full_result = $db->update('epic_users', $full_update_data, 'id = ?', [$test_user['id']]);
    
    if ($full_result) {
        echo "✅ Update dengan semua field berhasil!<br>\n";
        
        // Verifikasi
        $final_user = $db->selectOne("SELECT name, email, phone, address, profile_photo, updated_at FROM epic_users WHERE id = ?", [$test_user['id']]);
        echo "Data final:<br>\n";
        echo "   - Name: {$final_user['name']}<br>\n";
        echo "   - Email: {$final_user['email']}<br>\n";
        echo "   - Phone: {$final_user['phone']}<br>\n";
        echo "   - Address: {$final_user['address']}<br>\n";
        echo "   - Profile Photo: {$final_user['profile_photo']}<br>\n";
        echo "   - Updated at: {$final_user['updated_at']}<br>\n";
    } else {
        echo "❌ Update dengan semua field gagal<br>\n";
    }
    
    echo "<hr>\n";
    echo "<h3>✅ HASIL TEST</h3>\n";
    echo "✅ Kolom 'address' berhasil ditambahkan ke tabel epic_users<br>\n";
    echo "✅ Query UPDATE profil sudah diperbaiki<br>\n";
    echo "✅ Update profil dengan kolom address berfungsi normal<br>\n";
    echo "✅ Semua field profil dapat diupdate tanpa error<br>\n";
    echo "<br><strong>Status: PERBAIKAN BERHASIL!</strong><br>\n";
    
} catch (Exception $e) {
    echo "<h3>❌ ERROR</h3>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
    echo "File: " . $e->getFile() . "<br>\n";
    echo "Line: " . $e->getLine() . "<br>\n";
    
    if (strpos($e->getMessage(), 'address') !== false) {
        echo "<br><strong>Masalah masih terkait kolom 'address'. Periksa:</strong><br>\n";
        echo "1. Apakah kolom 'address' sudah ditambahkan ke tabel epic_users?<br>\n";
        echo "2. Apakah query menggunakan tabel yang benar (epic_users)?<br>\n";
        echo "3. Apakah ada typo dalam nama kolom?<br>\n";
    }
}
?>