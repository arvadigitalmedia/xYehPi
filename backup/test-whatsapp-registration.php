<?php
/**
 * Test Script untuk Notifikasi WhatsApp Registrasi
 * Simulasi registrasi user baru dengan sponsor untuk test notifikasi
 */

// Include core files
require_once 'config/database.php';
require_once 'core/functions.php';
require_once 'core/starsender-notifications.php';
require_once 'core/starsender-triggers.php';

// Test data
$test_user_data = [
    'id' => 999,
    'name' => 'Test User Baru',
    'email' => 'testuser@example.com',
    'phone' => '081234567890',
    'account_type' => 'free',
    'referral_code' => 'TEST123',
    'created_at' => date('Y-m-d H:i:s')
];

$test_sponsor_data = [
    'id' => 888,
    'name' => 'Test Sponsor',
    'email' => 'sponsor@example.com',
    'phone' => '081987654321',
    'account_type' => 'epic',
    'referral_code' => 'SPONSOR456'
];

echo "<h2>Test Notifikasi WhatsApp Registrasi</h2>";

// Cek apakah Starsender diaktifkan
$starsender_enabled = epic_setting('starsender_enabled', '0');
echo "<p><strong>Status Starsender:</strong> " . ($starsender_enabled == '1' ? '✅ Aktif' : '❌ Tidak Aktif') . "</p>";

if ($starsender_enabled != '1') {
    echo "<p style='color: red;'>⚠️ Starsender belum diaktifkan. Silakan aktifkan di Admin > Settings > WhatsApp Notification</p>";
    exit;
}

// Cek API Key
$api_key = epic_setting('starsender_api_key', '');
echo "<p><strong>API Key:</strong> " . (empty($api_key) ? '❌ Belum diset' : '✅ Sudah diset') . "</p>";

if (empty($api_key)) {
    echo "<p style='color: red;'>⚠️ API Key Starsender belum diset. Silakan set di Admin > Settings > WhatsApp Notification</p>";
    exit;
}

echo "<hr>";

// Test 1: Notifikasi ke User Baru
echo "<h3>Test 1: Notifikasi ke User Baru</h3>";
$user_message = epic_setting('starsender_registration_free_message', '');
if (!empty($user_message)) {
    $processed_message = epic_process_starsender_shortcodes($user_message, $test_user_data, $test_sponsor_data);
    echo "<p><strong>Pesan untuk User:</strong></p>";
    echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . nl2br(htmlspecialchars($processed_message)) . "</div>";
    
    // Simulasi pengiriman (tanpa benar-benar mengirim)
    echo "<p><strong>Target:</strong> " . epic_format_phone_starsender($test_user_data['phone']) . "</p>";
    echo "<p style='color: green;'>✅ Pesan siap dikirim ke user</p>";
} else {
    echo "<p style='color: red;'>❌ Template pesan user belum diset</p>";
}

echo "<hr>";

// Test 2: Notifikasi ke Sponsor
echo "<h3>Test 2: Notifikasi ke Sponsor</h3>";
$sponsor_message = epic_setting('starsender_registration_referral_message', '');
if (!empty($sponsor_message)) {
    $processed_message = epic_process_starsender_shortcodes($sponsor_message, $test_user_data, $test_sponsor_data);
    echo "<p><strong>Pesan untuk Sponsor:</strong></p>";
    echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . nl2br(htmlspecialchars($processed_message)) . "</div>";
    
    echo "<p><strong>Target:</strong> " . epic_format_phone_starsender($test_sponsor_data['phone']) . "</p>";
    echo "<p style='color: green;'>✅ Pesan siap dikirim ke sponsor</p>";
} else {
    echo "<p style='color: red;'>❌ Template pesan sponsor belum diset</p>";
}

echo "<hr>";

// Test 3: Simulasi Trigger Function
echo "<h3>Test 3: Simulasi Trigger Function</h3>";
echo "<p>Menjalankan fungsi <code>epic_starsender_on_registration()</code>...</p>";

try {
    // Simulasi trigger (dengan dry run)
    echo "<div style='background: #e8f4fd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Simulasi Trigger:</strong><br>";
    echo "- User: " . $test_user_data['name'] . " (" . $test_user_data['phone'] . ")<br>";
    echo "- Sponsor: " . $test_sponsor_data['name'] . " (" . $test_sponsor_data['phone'] . ")<br>";
    echo "- Level: " . $test_user_data['account_type'] . "<br>";
    echo "</div>";
    
    // Panggil fungsi trigger (tapi jangan benar-benar kirim)
    // epic_starsender_on_registration($test_user_data, $test_sponsor_data);
    
    echo "<p style='color: green;'>✅ Trigger function siap dijalankan</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 4: Shortcodes Available
echo "<h3>Test 4: Shortcodes Tersedia</h3>";
$shortcodes = epic_get_starsender_shortcodes();
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;'>";

foreach ($shortcodes as $category => $codes) {
    echo "<div style='background: #f9f9f9; padding: 10px; border-radius: 5px;'>";
    echo "<h4>" . ucfirst($category) . " Shortcodes</h4>";
    foreach ($codes as $code => $description) {
        echo "<code style='background: #e0e0e0; padding: 2px 4px; border-radius: 3px;'>" . $code . "</code> - " . $description . "<br>";
    }
    echo "</div>";
}

echo "</div>";

echo "<hr>";

// Test 5: Konfigurasi Lengkap
echo "<h3>Test 5: Status Konfigurasi</h3>";
$config_status = [
    'Starsender Enabled' => epic_setting('starsender_enabled', '0') == '1' ? '✅' : '❌',
    'API Key Set' => !empty(epic_setting('starsender_api_key', '')) ? '✅' : '❌',
    'User Free Message' => !empty(epic_setting('starsender_registration_free_message', '')) ? '✅' : '❌',
    'User Epic Message' => !empty(epic_setting('starsender_registration_epic_message', '')) ? '✅' : '❌',
    'User Epis Message' => !empty(epic_setting('starsender_registration_epis_message', '')) ? '✅' : '❌',
    'Sponsor Message' => !empty(epic_setting('starsender_registration_referral_message', '')) ? '✅' : '❌',
    'Test Phone Set' => !empty(epic_setting('starsender_test_phone', '')) ? '✅' : '❌'
];

echo "<table style='border-collapse: collapse; width: 100%;'>";
foreach ($config_status as $item => $status) {
    echo "<tr style='border-bottom: 1px solid #ddd;'>";
    echo "<td style='padding: 8px;'>" . $item . "</td>";
    echo "<td style='padding: 8px; text-align: center;'>" . $status . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><strong>Catatan:</strong> Script ini hanya untuk testing dan simulasi. Tidak ada pesan WhatsApp yang benar-benar dikirim.</p>";
echo "<p><strong>Untuk test pengiriman nyata:</strong> Gunakan fitur 'Test Koneksi' di halaman Admin > Settings > WhatsApp Notification</p>";

?>