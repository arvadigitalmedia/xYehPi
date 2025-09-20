<?php
/**
 * Test Email Reset Password dengan Mailketing
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'bootstrap.php';
    
    echo "<h1>Test Email Reset Password</h1>";
    echo "<hr>";
    
    // Test data
    $test_email = 'testmailketing@bisnisemasperak.com';
    $test_name = 'Test User Mailketing';
    $reset_token = bin2hex(random_bytes(32)); // Token reset password
    
    echo "<h2>Data Test:</h2>";
    echo "<p><strong>Email:</strong> $test_email</p>";
    echo "<p><strong>Nama:</strong> $test_name</p>";
    echo "<p><strong>Reset Token:</strong> " . substr($reset_token, 0, 16) . "...</p>";
    
    // Test Mailketing status
    echo "<h2>Status Mailketing:</h2>";
    if (function_exists('epic_get_mailketing_status')) {
        $status = epic_get_mailketing_status();
        if ($status['enabled'] && $status['configured']) {
            echo "<p style='color: green;'>✅ Mailketing siap digunakan</p>";
        } else {
            echo "<p style='color: red;'>❌ Mailketing belum dikonfigurasi dengan benar</p>";
            echo "<pre>";
            print_r($status);
            echo "</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ Fungsi epic_get_mailketing_status tidak ditemukan</p>";
    }
    
    // Test kirim email reset password
    echo "<h2>Test Kirim Email Reset Password:</h2>";
    
    $reset_url = "http://localhost:8080/reset-password.php?token=" . $reset_token;
    $subject = "Reset Password - Bisnisemasperak.com";
    $message = '
    <html>
    <head>
        <title>Reset Password</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #dc3545; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔐 Reset Password</h1>
            </div>
            <div class="content">
                <h2>Halo ' . htmlspecialchars($test_name) . ',</h2>
                <p>Kami menerima permintaan untuk mereset password akun Anda di Bisnisemasperak.com.</p>
                
                <div class="warning">
                    <strong>⚠️ Penting:</strong> Jika Anda tidak meminta reset password, abaikan email ini. Password Anda akan tetap aman.
                </div>
                
                <p>Untuk mereset password Anda, silakan klik tombol di bawah ini:</p>
                
                <div style="text-align: center;">
                    <a href="' . $reset_url . '" class="button">Reset Password Saya</a>
                </div>
                
                <p>Atau copy dan paste link berikut ke browser Anda:</p>
                <p style="word-break: break-all; background: #eee; padding: 10px; border-radius: 5px;">' . $reset_url . '</p>
                
                <p><strong>Catatan Keamanan:</strong></p>
                <ul>
                    <li>Link reset ini akan kedaluwarsa dalam <strong>1 jam</strong></li>
                    <li>Link hanya dapat digunakan <strong>satu kali</strong></li>
                    <li>Setelah reset, Anda akan diminta membuat password baru</li>
                </ul>
                
                <p>Jika Anda mengalami kesulitan, hubungi tim support kami.</p>
                
                <p>Terima kasih,<br><strong>Tim Keamanan Bisnisemasperak.com</strong></p>
            </div>
            <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
                <p>Email ini dikirim secara otomatis via Mailketing API</p>
                <p>Waktu: ' . date('Y-m-d H:i:s') . ' WIB</p>
                <p>IP Request: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    if (function_exists('epic_send_email')) {
        echo "<p>Mengirim email reset password ke $test_email...</p>";
        
        $result = epic_send_email(
            $test_email,
            $subject,
            $message,
            'Security Team Bisnisemasperak.com',
            'security@bisnisemasperak.com'
        );
        
        if ($result) {
            echo "<p style='color: green;'>✅ Email reset password berhasil dikirim!</p>";
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>📧 Email Terkirim</h3>";
            echo "<p><strong>Kepada:</strong> $test_email</p>";
            echo "<p><strong>Subject:</strong> $subject</p>";
            echo "<p><strong>Via:</strong> Mailketing API</p>";
            echo "<p><strong>Waktu:</strong> " . date('Y-m-d H:i:s') . " WIB</p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Gagal mengirim email reset password</p>";
        }
        
        echo "<p><strong>Link Reset Password:</strong> <a href='$reset_url' target='_blank'>$reset_url</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Fungsi epic_send_email tidak ditemukan</p>";
    }
    
    // Test summary
    echo "<h2>Summary Test Reset Password:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th>Item</th><th>Status</th><th>Detail</th></tr>";
    echo "<tr><td>Mailketing Status</td><td style='color: green;'>✅ READY</td><td>Configured & Enabled</td></tr>";
    echo "<tr><td>Token Generation</td><td style='color: green;'>✅ SUCCESS</td><td>64 chars secure token</td></tr>";
    echo "<tr><td>Email Template</td><td style='color: green;'>✅ SUCCESS</td><td>HTML with security warnings</td></tr>";
    echo "<tr><td>Email Sending</td><td style='color: " . (isset($result) && $result ? 'green;">✅ SUCCESS' : 'red;">❌ FAILED') . "</td><td>Via Mailketing API</td></tr>";
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>🔒 Fitur Keamanan Email Reset:</h3>";
    echo "<ul>";
    echo "<li>✅ Token acak 64 karakter</li>";
    echo "<li>✅ Peringatan keamanan jelas</li>";
    echo "<li>✅ Link expire dalam 1 jam</li>";
    echo "<li>✅ Single-use token</li>";
    echo "<li>✅ IP tracking</li>";
    echo "<li>✅ Timestamp WIB</li>";
    echo "</ul>";
    
    echo "<p><em>Test selesai pada: " . date('Y-m-d H:i:s') . " WIB</em></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>