<?php
/**
 * Test Sederhana Email Konfirmasi dengan Mailketing
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'bootstrap.php';
    
    echo "<h1>Test Sederhana Email Konfirmasi</h1>";
    echo "<hr>";
    
    // Test data
    $test_email = 'testmailketing@bisnisemasperak.com';
    $test_name = 'Test User Mailketing';
    $token = bin2hex(random_bytes(16)); // Token sederhana
    
    echo "<h2>Data Test:</h2>";
    echo "<p><strong>Email:</strong> $test_email</p>";
    echo "<p><strong>Nama:</strong> $test_name</p>";
    echo "<p><strong>Token:</strong> $token</p>";
    
    // Test Mailketing status
    echo "<h2>Status Mailketing:</h2>";
    if (function_exists('epic_get_mailketing_status')) {
        $status = epic_get_mailketing_status();
        echo "<pre>";
        print_r($status);
        echo "</pre>";
        
        if ($status['enabled'] && $status['configured']) {
            echo "<p style='color: green;'>✅ Mailketing siap digunakan</p>";
        } else {
            echo "<p style='color: red;'>❌ Mailketing belum dikonfigurasi dengan benar</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Fungsi epic_get_mailketing_status tidak ditemukan</p>";
    }
    
    // Test kirim email
    echo "<h2>Test Kirim Email:</h2>";
    
    $confirmation_url = "http://localhost:8080/confirm-email.php?token=" . $token;
    $subject = "Test Email Konfirmasi - Bisnisemasperak.com";
    $message = "
    <h2>Test Email Konfirmasi</h2>
    <p>Halo $test_name,</p>
    <p>Ini adalah test email konfirmasi.</p>
    <p><a href='$confirmation_url'>Klik untuk konfirmasi</a></p>
    <p>Link: $confirmation_url</p>
    <p>Waktu: " . date('Y-m-d H:i:s') . "</p>
    ";
    
    if (function_exists('epic_send_email')) {
        echo "<p>Mengirim email ke $test_email...</p>";
        
        $result = epic_send_email(
            $test_email,
            $subject,
            $message,
            'Admin Test',
            'admin@bisnisemasperak.com'
        );
        
        if ($result) {
            echo "<p style='color: green;'>✅ Email berhasil dikirim!</p>";
        } else {
            echo "<p style='color: red;'>❌ Gagal mengirim email</p>";
        }
        
        echo "<p><strong>Link Konfirmasi:</strong> <a href='$confirmation_url' target='_blank'>$confirmation_url</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Fungsi epic_send_email tidak ditemukan</p>";
    }
    
    echo "<hr>";
    echo "<p><em>Test selesai pada: " . date('Y-m-d H:i:s') . "</em></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>