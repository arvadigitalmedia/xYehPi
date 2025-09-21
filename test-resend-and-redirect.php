<?php
/**
 * Test End-to-End: Fitur Kirim Ulang Email & Redirect Login
 * Menguji fitur baru konfirmasi email dengan tombol kirim ulang dan redirect
 */

// Prevent direct access
if (!defined('EPIC_INIT')) {
    define('EPIC_INIT', true);
}

if (!defined('EPIC_LOADED')) {
    define('EPIC_LOADED', true);
}

require_once 'config/config.php';
require_once 'core/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🧪 Test End-to-End: Fitur Kirim Ulang Email & Redirect Login</h1>";
echo "<hr>";

try {
    // Database connection - menggunakan epic_hub
    $pdo = new PDO(
        "mysql:host=localhost;dbname=epic_hub;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<h2>📋 Persiapan Test</h2>";
    
    // 1. Buat user test baru
    $test_email = 'test-resend-' . time() . '@example.com';
    $test_name = 'Test Resend User';
    $test_password = password_hash('test123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO epic_users (name, email, password, status, created_at) 
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$test_name, $test_email, $test_password]);
    $user_id = $pdo->lastInsertId();
    
    echo "✅ User test dibuat: ID $user_id, Email: $test_email<br>";
    
    // 2. Generate token konfirmasi
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $stmt = $pdo->prepare("
        INSERT INTO email_confirmations (user_id, token, expires_at, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $token, $expires_at]);
    
    echo "✅ Token konfirmasi dibuat: $token<br>";
    echo "✅ Expires: $expires_at<br>";
    
    echo "<h2>🔗 URL Test</h2>";
    
    // URL untuk test konfirmasi dengan token valid
    $confirm_url = "http://localhost:8080/confirm-email.php?token=$token";
    echo "<strong>1. Test Konfirmasi Valid:</strong><br>";
    echo "<a href='$confirm_url' target='_blank'>$confirm_url</a><br><br>";
    
    // URL untuk test konfirmasi dengan token invalid
    $invalid_url = "http://localhost:8080/confirm-email.php?token=invalid-token-test";
    echo "<strong>2. Test Token Invalid (untuk tombol kirim ulang):</strong><br>";
    echo "<a href='$invalid_url' target='_blank'>$invalid_url</a><br><br>";
    
    // URL halaman login untuk test redirect
    $login_url = "http://localhost:8080/index.php?confirmed=1";
    echo "<strong>3. Test Halaman Login dengan Pesan Sukses:</strong><br>";
    echo "<a href='$login_url' target='_blank'>$login_url</a><br><br>";
    
    echo "<h2>🧪 Skenario Test</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007acc; margin: 10px 0;'>";
    echo "<h3>Test Case 1: Konfirmasi Email Berhasil</h3>";
    echo "1. Klik URL konfirmasi valid di atas<br>";
    echo "2. Verifikasi pesan sukses muncul<br>";
    echo "3. Verifikasi redirect ke halaman login dengan parameter ?confirmed=1<br>";
    echo "4. Verifikasi pesan 'Email Anda telah berhasil dikonfirmasi!' muncul di halaman login<br>";
    echo "</div>";
    
    echo "<div style='background: #fff8f0; padding: 15px; border-left: 4px solid #ff8c00; margin: 10px 0;'>";
    echo "<h3>Test Case 2: Token Invalid - Tombol Kirim Ulang</h3>";
    echo "1. Klik URL token invalid di atas<br>";
    echo "2. Verifikasi pesan error muncul<br>";
    echo "3. Verifikasi tombol 'Kirim Ulang Email Konfirmasi' muncul<br>";
    echo "4. Masukkan email: <strong>$test_email</strong><br>";
    echo "5. Klik tombol 'Kirim Ulang'<br>";
    echo "6. Verifikasi pesan sukses kirim ulang muncul<br>";
    echo "</div>";
    
    echo "<h2>🔍 Verifikasi Database</h2>";
    
    // Cek status user sebelum konfirmasi
    $stmt = $pdo->prepare("SELECT id, name, email, status FROM epic_users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<strong>Status User Sebelum Konfirmasi:</strong><br>";
    echo "ID: {$user['id']}<br>";
    echo "Name: {$user['name']}<br>";
    echo "Email: {$user['email']}<br>";
    echo "Status: <span style='color: orange;'>{$user['status']}</span><br><br>";
    
    // Cek token yang tersedia
    $stmt = $pdo->prepare("
        SELECT token, expires_at, used_at, created_at 
        FROM email_confirmations 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<strong>Token Konfirmasi:</strong><br>";
    foreach ($tokens as $t) {
        $status = $t['used_at'] ? 'USED' : 'ACTIVE';
        $color = $t['used_at'] ? 'red' : 'green';
        echo "Token: " . substr($t['token'], 0, 16) . "...<br>";
        echo "Status: <span style='color: $color;'>$status</span><br>";
        echo "Expires: {$t['expires_at']}<br>";
        echo "Created: {$t['created_at']}<br>";
        if ($t['used_at']) {
            echo "Used: {$t['used_at']}<br>";
        }
        echo "<br>";
    }
    
    echo "<h2>📝 Instruksi Manual Test</h2>";
    echo "<ol>";
    echo "<li><strong>Test Konfirmasi Berhasil:</strong> Klik URL konfirmasi valid, pastikan redirect ke login dengan pesan sukses</li>";
    echo "<li><strong>Test Token Invalid:</strong> Klik URL token invalid, pastikan tombol kirim ulang muncul</li>";
    echo "<li><strong>Test Kirim Ulang:</strong> Gunakan tombol kirim ulang dengan email test, pastikan tidak ada error</li>";
    echo "<li><strong>Test Rate Limiting:</strong> Coba kirim ulang beberapa kali dalam 5 menit, pastikan rate limit bekerja</li>";
    echo "<li><strong>Test CSRF Protection:</strong> Pastikan form kirim ulang memiliki CSRF token</li>";
    echo "</ol>";
    
    echo "<h2>🧹 Cleanup</h2>";
    echo "<p>Setelah selesai test, jalankan script berikut untuk membersihkan data test:</p>";
    echo "<code>php cleanup-email-confirmation-tests.php</code>";
    
    echo "<hr>";
    echo "<h2>✅ Test Setup Selesai</h2>";
    echo "<p><strong>Status:</strong> READY FOR MANUAL TESTING</p>";
    echo "<p><strong>User Test ID:</strong> $user_id</p>";
    echo "<p><strong>Email Test:</strong> $test_email</p>";
    echo "<p><strong>Token:</strong> " . substr($token, 0, 16) . "...</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}
?>