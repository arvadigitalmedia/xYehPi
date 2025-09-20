<?php
/**
 * Test Script untuk Sistem Konfirmasi Email
 * Simulasi proses registrasi dan konfirmasi email
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/email-confirmation.php';

// Fungsi untuk output dengan format
function test_output($message, $status = 'info') {
    $colors = [
        'success' => '#28a745',
        'error' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8'
    ];
    
    echo "<div style='padding: 10px; margin: 5px 0; background: " . $colors[$status] . "; color: white; border-radius: 5px;'>";
    echo "<strong>" . strtoupper($status) . ":</strong> " . $message;
    echo "</div>";
}

echo "<h1>Test Sistem Konfirmasi Email</h1>";

try {
    // 1. Test Database Connection
    test_output("Testing koneksi database...");
    $database = db();
    if (!$database) {
        throw new Exception("Koneksi database gagal");
    }
    test_output("Koneksi database berhasil", 'success');

    // 2. Test User Data
    test_output("Testing data user...");
    $user = db()->selectOne("SELECT id, name, email, status FROM epic_users WHERE email = ? LIMIT 1", ['test@example.com']);
    
    if ($user) {
        test_output("Test user ditemukan: " . $user['name'] . " (" . $user['email'] . ") - Status: " . $user['status'], 'success');
    } else {
        test_output("Test user tidak ditemukan, membuat user baru...", 'warning');
        
        // Create test user dengan SQL langsung
        $result = db()->query("INSERT INTO epic_users (id, uuid, name, email, password, status) VALUES (?, ?, ?, ?, ?, ?)", [
            9, // ID manual karena tidak ada AUTO_INCREMENT
            uniqid('user_', true),
            'Test User',
            'test@example.com',
            password_hash('password123', PASSWORD_DEFAULT),
            'pending'
        ]);
        
        if ($result) {
            test_output("Test user berhasil dibuat", 'success');
        } else {
            test_output("Gagal membuat test user", 'error');
        }
    }

    // 3. Test Token Generation
    test_output("Testing token generation...");
    $user = db()->selectOne("SELECT id, name, email FROM epic_users WHERE email = ?", ['test@example.com']);
    
    if ($user) {
        // Generate token using the correct system
        $token = epic_generate_confirmation_token($user['id']);
        
        test_output("Token berhasil dibuat: " . substr($token, 0, 20) . "...", 'success');
        
        // Validate token
        $stored_token = db()->selectOne("SELECT * FROM epic_email_confirmations WHERE token = ? AND expires_at > NOW()", [$token]);
        if ($stored_token) {
            test_output("Token validation berhasil", 'success');
        } else {
            test_output("Token validation gagal", 'error');
        }
    } else {
        test_output("User tidak ditemukan untuk testing token", 'error');
    }

    // 4. Test Email System
    test_output("Testing email system...");
    
    // Check if email templates exist
    $confirmation_template = __DIR__ . '/templates/emails/email-confirmation.php';
    $welcome_template = __DIR__ . '/templates/emails/welcome.php';
    
    if (file_exists($confirmation_template)) {
        test_output("Email confirmation template ditemukan", 'success');
    } else {
        test_output("Email confirmation template tidak ditemukan", 'warning');
    }
    
    if (file_exists($welcome_template)) {
        test_output("Welcome email template ditemukan", 'success');
    } else {
        test_output("Welcome email template tidak ditemukan", 'warning');
    }
    
    // Test email content generation
    if (isset($user) && isset($token)) {
        $confirmation_url = "http://localhost:8080/confirm-email/" . $token;
        test_output("URL konfirmasi: " . $confirmation_url, 'info');
    }

    // 5. Test Confirmation URL
    test_output("Testing confirmation URL generation...");
    if (isset($token)) {
        $confirmation_url = "http://localhost:8080/confirm-email/" . $token;
        test_output("Confirmation URL: " . $confirmation_url, 'info');
        test_output("Token akan expired pada: " . $expires_at, 'info');
    }

    // 6. Test Database Cleanup
    test_output("Testing database cleanup...");
    $expired_tokens = db()->select("SELECT COUNT(*) as count FROM epic_user_tokens WHERE expires_at < NOW()");
    if ($expired_tokens && $expired_tokens[0]['count'] > 0) {
        test_output("Ditemukan " . $expired_tokens[0]['count'] . " token yang expired", 'warning');
    } else {
        test_output("Tidak ada token yang expired", 'success');
    }

} catch (Exception $e) {
    test_output("Error: " . $e->getMessage(), 'error');
}

echo "<h2>Test Selesai</h2>";
echo "<p><a href='/register'>Ke Halaman Registrasi</a> | <a href='/email-confirmed'>Ke Halaman Konfirmasi Email</a></p>";
?>