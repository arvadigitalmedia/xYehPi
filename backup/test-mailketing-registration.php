<?php
/**
 * Test Registrasi dengan Mailketing Email Confirmation - Versi Sederhana
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'bootstrap.php';
} catch (Exception $e) {
    die("Error loading bootstrap: " . $e->getMessage());
}

// Set content type
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Test Registrasi dengan Mailketing Email</h1>";
echo "<hr>";

// Simulasi data registrasi
$test_data = [
    'name' => 'Test User Mailketing',
    'email' => 'testmailketing@bisnisemasperak.com',
    'password' => 'testpassword123',
    'referral_code' => '03KIPMLQ'
];

echo "<h2>Data Test Registrasi:</h2>";
echo "<pre>";
print_r($test_data);
echo "</pre>";

try {
    // Test koneksi database
    $db = db();
    echo "<p style='color: green;'>✅ Koneksi database berhasil</p>";
    
    // Cek dan hapus user lama jika ada
    $stmt = $db->prepare("SELECT id FROM epic_users WHERE email = ?");
    $stmt->execute([$test_data['email']]);
    $existing_user = $stmt->fetch();
    
    if ($existing_user) {
        echo "<p style='color: orange;'>⚠️ Email sudah terdaftar, akan hapus data lama...</p>";
        
        // Hapus token lama
        $stmt = $db->prepare("DELETE FROM epic_user_tokens WHERE user_id = ?");
        $stmt->execute([$existing_user['id']]);
        
        // Hapus user lama
        $stmt = $db->prepare("DELETE FROM epic_users WHERE id = ?");
        $stmt->execute([$existing_user['id']]);
        
        echo "<p style='color: green;'>✅ Data lama berhasil dihapus</p>";
    }
    
    // Buat user baru
    $password_hash = password_hash($test_data['password'], PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO epic_users (name, email, password, status, role, referral_code, created_at) 
        VALUES (?, ?, ?, 'pending', 'user', ?, NOW())
    ");
    
    $result = $stmt->execute([
        $test_data['name'],
        $test_data['email'],
        $password_hash,
        $test_data['referral_code']
    ]);
    
    if (!$result) {
        throw new Exception("Gagal membuat user: " . implode(', ', $stmt->errorInfo()));
    }
    
    $user_id = $db->lastInsertId();
    echo "<p style='color: green;'>✅ User berhasil dibuat dengan ID: $user_id</p>";
    
    // Generate token konfirmasi
    $token = bin2hex(random_bytes(32));
    
    $stmt = $db->prepare("
        INSERT INTO epic_user_tokens (user_id, token, type, expires_at, created_at) 
        VALUES (?, ?, 'email_confirmation', DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
    ");
    
    $result = $stmt->execute([$user_id, $token]);
    
    if (!$result) {
        throw new Exception("Gagal membuat token: " . implode(', ', $stmt->errorInfo()));
    }
    
    echo "<p style='color: green;'>✅ Token konfirmasi berhasil dibuat</p>";
    
    // Test Mailketing status
    echo "<h2>Status Mailketing:</h2>";
    if (function_exists('epic_get_mailketing_status')) {
        $mailketing_status = epic_get_mailketing_status();
        echo "<pre>";
        print_r($mailketing_status);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Fungsi epic_get_mailketing_status tidak ditemukan</p>";
    }
    
    // Kirim email konfirmasi
    $confirmation_url = "http://localhost:8080/confirm-email.php?token=" . $token;
    $subject = "Konfirmasi Email Anda - Bisnisemasperak.com";
    $message = "
    <h2>Selamat Datang!</h2>
    <p>Halo " . htmlspecialchars($test_data['name']) . ",</p>
    <p>Silakan klik link berikut untuk mengkonfirmasi email Anda:</p>
    <p><a href='$confirmation_url'>Konfirmasi Email</a></p>
    <p>Link: $confirmation_url</p>
    <p>Token akan kedaluwarsa dalam 24 jam.</p>
    ";
    
    echo "<h2>Mengirim Email Konfirmasi...</h2>";
    
    if (function_exists('epic_send_email')) {
        $email_result = epic_send_email(
            $test_data['email'],
            $subject,
            $message,
            'Admin Bisnisemasperak.com',
            'email@bisnisemasperak.com'
        );
        
        if ($email_result) {
            echo "<p style='color: green;'>✅ Email konfirmasi berhasil dikirim via Mailketing!</p>";
        } else {
            echo "<p style='color: red;'>❌ Gagal mengirim email konfirmasi</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Fungsi epic_send_email tidak ditemukan</p>";
    }
    
    // Summary
    echo "<h2>Summary Test:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Item</th><th>Status</th><th>Detail</th></tr>";
    echo "<tr><td>Database Connection</td><td>✅ SUCCESS</td><td>Connected</td></tr>";
    echo "<tr><td>User Creation</td><td>✅ SUCCESS</td><td>ID: $user_id</td></tr>";
    echo "<tr><td>Token Generation</td><td>✅ SUCCESS</td><td>Generated</td></tr>";
    echo "<tr><td>Email Sending</td><td>" . (isset($email_result) && $email_result ? '✅ SUCCESS' : '❌ FAILED') . "</td><td>Via Mailketing API</td></tr>";
    echo "</table>";
    
    echo "<hr>";
    echo "<p><strong>Link Konfirmasi:</strong> <a href='$confirmation_url' target='_blank'>$confirmation_url</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>