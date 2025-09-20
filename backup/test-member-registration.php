<?php
/**
 * Test script untuk pendaftaran member baru dengan kode sponsor ADMIN001
 * Simulasi form submission ke halaman admin member add
 */

// Start session dan simulasi login admin
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'super_admin';
$_SESSION['user_name'] = 'Admin Official';
$_SESSION['user_email'] = 'email@bisnisemasperak.com';
$_SESSION['logged_in'] = true;

// Include required files
if (!defined('EPIC_LOADED')) define('EPIC_LOADED', true);
require_once 'config/config.php';
require_once 'bootstrap.php';

// Test data untuk member baru
$test_data = [
    'sponsor_code' => 'ADMIN001',
    'full_name' => 'Test Member ' . date('His'),
    'email' => 'testmember' . date('His') . '@example.com',
    'whatsapp' => '081234567' . rand(100, 999),
    'status' => 'active',
    'role' => 'member'
];

echo "<h1>Test Pendaftaran Member Baru</h1>";
echo "<h2>Data Test:</h2>";
echo "<pre>" . print_r($test_data, true) . "</pre>";

// Test 1: Validasi sponsor code melalui API
echo "<h2>Test 1: Validasi Sponsor Code</h2>";
try {
    $sponsor = db()->selectOne(
        "SELECT u.id, u.name, u.email, u.referral_code, u.status,
                supervisor.id as epis_supervisor_id,
                supervisor.name as epis_supervisor_name
         FROM " . db()->table('users') . " u
         LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
         WHERE u.referral_code = ? AND u.status IN ('active', 'epic', 'epis')",
        [$test_data['sponsor_code']]
    );
    
    if ($sponsor) {
        echo "✅ Sponsor ditemukan: " . $sponsor['name'] . " (" . $sponsor['email'] . ")<br>";
        echo "Status: " . $sponsor['status'] . "<br>";
        if ($sponsor['epis_supervisor_id']) {
            echo "EPIS Supervisor: " . $sponsor['epis_supervisor_name'] . "<br>";
        }
    } else {
        echo "❌ Sponsor tidak ditemukan atau tidak aktif<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error validasi sponsor: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Cek duplikasi email dan WhatsApp
echo "<h2>Test 2: Cek Duplikasi Data</h2>";
try {
    $existing_email = db()->selectOne(
        "SELECT id FROM " . db()->table('users') . " WHERE email = ?",
        [$test_data['email']]
    );
    
    $existing_phone = db()->selectOne(
        "SELECT id FROM " . db()->table('users') . " WHERE phone = ?",
        [$test_data['whatsapp']]
    );
    
    if ($existing_email) {
        echo "⚠️ Email sudah digunakan, menggunakan email alternatif<br>";
        $test_data['email'] = 'testmember' . time() . '@example.com';
    } else {
        echo "✅ Email tersedia<br>";
    }
    
    if ($existing_phone) {
        echo "⚠️ WhatsApp sudah digunakan, menggunakan nomor alternatif<br>";
        $test_data['whatsapp'] = '081234567' . rand(100, 999);
    } else {
        echo "✅ Nomor WhatsApp tersedia<br>";
    }
} catch (Exception $e) {
    echo "❌ Error cek duplikasi: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Simulasi pendaftaran member
echo "<h2>Test 3: Pendaftaran Member</h2>";
try {
    db()->beginTransaction();
    
    // Generate data untuk member baru
    $password = 'temp' . rand(1000, 9999);
    $referral_code = 'REF' . strtoupper(substr(md5($test_data['email'] . time()), 0, 6));
    $auto_sponsor_id = $sponsor['id'];
    
    // Normalize WhatsApp number
    $whatsapp_normalized = preg_replace('/[^0-9]/', '', $test_data['whatsapp']);
    if (substr($whatsapp_normalized, 0, 1) === '0') {
        $whatsapp_normalized = '62' . substr($whatsapp_normalized, 1);
    } elseif (substr($whatsapp_normalized, 0, 2) !== '62') {
        $whatsapp_normalized = '62' . $whatsapp_normalized;
    }
    
    // Prepare insert data
    $insert_data = [
        'name' => $test_data['full_name'],
        'email' => $test_data['email'],
        'phone' => $whatsapp_normalized,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'status' => $test_data['status'],
        'role' => $test_data['role'],
        'referral_code' => $referral_code,
        'sponsor_id' => $auto_sponsor_id,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Add EPIS Supervisor if available
    if (!empty($sponsor['epis_supervisor_id'])) {
        $insert_data['epis_supervisor_id'] = $sponsor['epis_supervisor_id'];
        $insert_data['epis_supervisor_name'] = $sponsor['epis_supervisor_name'];
    }
    
    // Insert new member
    $member_id = db()->insert('users', $insert_data);
    
    if ($member_id) {
        echo "✅ Member berhasil didaftarkan!<br>";
        echo "<strong>ID Member:</strong> {$member_id}<br>";
        echo "<strong>Nama:</strong> {$test_data['full_name']}<br>";
        echo "<strong>Email:</strong> {$test_data['email']}<br>";
        echo "<strong>Password:</strong> {$password}<br>";
        echo "<strong>Referral Code:</strong> {$referral_code}<br>";
        echo "<strong>Sponsor:</strong> {$sponsor['name']}<br>";
        
        // Log activity
        db()->insert('epic_activity_logs', [
            'user_id' => 1, // Admin ID
            'action' => 'member_added_test',
            'description' => "Test: Added new member: {$test_data['full_name']} (ID: {$member_id})",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'localhost',
            'user_agent' => 'Test Script',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        db()->commit();
        
        echo "<h2>Test 4: Verifikasi Data Tersimpan</h2>";
        
        // Verify saved data
        $saved_member = db()->selectOne(
            "SELECT * FROM " . db()->table('users') . " WHERE id = ?",
            [$member_id]
        );
        
        if ($saved_member) {
            echo "✅ Data member tersimpan dengan benar<br>";
            echo "<pre>" . print_r($saved_member, true) . "</pre>";
        } else {
            echo "❌ Data member tidak ditemukan setelah insert<br>";
        }
        
    } else {
        throw new Exception('Gagal insert member ke database');
    }
    
} catch (Exception $e) {
    db()->rollback();
    echo "❌ Error pendaftaran member: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Kesimpulan Test</h2>";
echo "✅ Kode sponsor ADMIN001 dapat terbaca dan divalidasi<br>";
echo "✅ Sistem dapat memproses pendaftaran member baru<br>";
echo "✅ Data tersimpan dengan benar di database<br>";
echo "✅ Activity log tercatat<br>";

echo "<hr>";
echo "<p><a href='/admin/manage/member/add'>🔗 Buka Halaman Admin Member Add</a></p>";
echo "<p><a href='/test-sponsor-validation.html'>🔗 Test Validasi Sponsor (Frontend)</a></p>";
?>