<?php
/**
 * Test Mailketing Integration
 * File untuk testing integrasi API Mailketing
 */

require_once 'bootstrap.php';

// Set content type
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Test Integrasi Mailketing API</h1>";
echo "<hr>";

// 1. Test konfigurasi
echo "<h2>1. Status Konfigurasi</h2>";
$status = epic_get_mailketing_status();
echo "<pre>";
print_r($status);
echo "</pre>";

if (!$status['enabled']) {
    echo "<p style='color: red;'>❌ Mailketing tidak diaktifkan</p>";
    exit;
}

if (!$status['configured']) {
    echo "<p style='color: red;'>❌ Konfigurasi tidak lengkap. Missing: " . implode(', ', $status['missing_configs']) . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Konfigurasi lengkap dan aktif</p>";

// 2. Test koneksi API
echo "<h2>2. Test Koneksi API</h2>";
$connection_test = epic_test_mailketing_connection();
echo "<pre>";
print_r($connection_test);
echo "</pre>";

if ($connection_test['success']) {
    echo "<p style='color: green;'>✅ Koneksi API berhasil</p>";
} else {
    echo "<p style='color: red;'>❌ Koneksi API gagal: " . $connection_test['error'] . "</p>";
}

// 3. Test email konfirmasi
echo "<h2>3. Test Email Konfirmasi</h2>";
$test_email = 'test@bisnisemasperak.com'; // Ganti dengan email test yang valid

$confirmation_subject = 'Test Email Konfirmasi - ' . date('Y-m-d H:i:s');
$confirmation_message = '
<html>
<head>
    <title>Test Email Konfirmasi</title>
</head>
<body>
    <h2>Test Email Konfirmasi</h2>
    <p>Halo,</p>
    <p>Ini adalah test email konfirmasi untuk memverifikasi integrasi Mailketing API.</p>
    <p><strong>Waktu Test:</strong> ' . date('Y-m-d H:i:s') . '</p>
    <p><a href="http://localhost:8080/confirm-email.php?token=test123" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Konfirmasi Email</a></p>
    <p>Terima kasih,<br>Tim Bisnisemasperak.com</p>
</body>
</html>';

$conf_result = epic_send_email($test_email, $confirmation_subject, $confirmation_message);
echo "<p>Result: " . ($conf_result ? 'SUCCESS' : 'FAILED') . "</p>";

// 4. Test email reset password
echo "<h2>4. Test Email Reset Password</h2>";

$reset_subject = 'Test Reset Password - ' . date('Y-m-d H:i:s');
$reset_message = '
<html>
<head>
    <title>Test Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <p>Halo,</p>
    <p>Anda telah meminta reset password untuk akun Anda.</p>
    <p><strong>Waktu Test:</strong> ' . date('Y-m-d H:i:s') . '</p>
    <p><a href="http://localhost:8080/reset-password.php?token=reset123" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
    <p>Link ini akan kedaluwarsa dalam 24 jam.</p>
    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
    <p>Terima kasih,<br>Tim Bisnisemasperak.com</p>
</body>
</html>';

$reset_result = epic_send_email($test_email, $reset_subject, $reset_message);
echo "<p>Result: " . ($reset_result ? 'SUCCESS' : 'FAILED') . "</p>";

// 5. Test direct Mailketing API
echo "<h2>5. Test Direct Mailketing API</h2>";

$direct_result = epic_send_email_mailketing(
    $test_email,
    'Test Direct API - ' . date('Y-m-d H:i:s'),
    '<h1>Test Direct API</h1><p>Ini adalah test langsung ke Mailketing API tanpa fallback.</p><p>Waktu: ' . date('Y-m-d H:i:s') . '</p>',
    'Test Direct',
    'test@bisnisemasperak.com'
);

echo "<pre>";
print_r($direct_result);
echo "</pre>";

// 6. Summary
echo "<h2>6. Summary Test</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Test</th><th>Status</th></tr>";
echo "<tr><td>Konfigurasi</td><td>" . ($status['enabled'] && $status['configured'] ? '✅ OK' : '❌ FAILED') . "</td></tr>";
echo "<tr><td>Koneksi API</td><td>" . ($connection_test['success'] ? '✅ OK' : '❌ FAILED') . "</td></tr>";
echo "<tr><td>Email Konfirmasi</td><td>" . ($conf_result ? '✅ OK' : '❌ FAILED') . "</td></tr>";
echo "<tr><td>Email Reset Password</td><td>" . ($reset_result ? '✅ OK' : '❌ FAILED') . "</td></tr>";
echo "<tr><td>Direct API</td><td>" . ($direct_result['success'] ? '✅ OK' : '❌ FAILED') . "</td></tr>";
echo "</table>";

echo "<hr>";
echo "<p><strong>Catatan:</strong> Cek email di " . $test_email . " untuk memverifikasi pengiriman.</p>";
echo "<p><strong>Waktu Test:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>