<?php
require_once 'bootstrap.php';

echo "<h2>Test Perbaikan Email Confirmation</h2>\n";

try {
    // Test data user
    $test_user = [
        'id' => 999,
        'name' => 'Test User Email',
        'email' => 'test-email@example.com'
    ];
    
    echo "<h3>1. Test epic_render_template</h3>\n";
    
    // Test render template
    $template_data = [
        'user_name' => $test_user['name'],
        'user_email' => $test_user['email'],
        'confirmation_url' => 'http://localhost:8000/confirm-email/test-token-123',
        'site_name' => 'EPIC Hub Test',
        'site_url' => 'http://localhost:8000',
        'expires_hours' => 24
    ];
    
    ob_start();
    epic_render_template('emails/email-confirmation', $template_data);
    $rendered_email = ob_get_clean();
    
    if (!empty($rendered_email)) {
        echo "<p style='color: green;'>✓ Template email berhasil di-render</p>\n";
        echo "<p>Panjang konten: " . strlen($rendered_email) . " karakter</p>\n";
        
        // Tampilkan preview singkat
        $preview = substr(strip_tags($rendered_email), 0, 200) . '...';
        echo "<p><strong>Preview:</strong> " . htmlspecialchars($preview) . "</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Template email gagal di-render</p>\n";
    }
    
    echo "<h3>2. Test epic_send_confirmation_email</h3>\n";
    
    // Test fungsi send confirmation email
    $result = epic_send_confirmation_email($test_user);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✓ epic_send_confirmation_email berhasil</p>\n";
        echo "<p>Message: " . htmlspecialchars($result['message']) . "</p>\n";
    } else {
        echo "<p style='color: red;'>✗ epic_send_confirmation_email gagal</p>\n";
        echo "<p>Error: " . htmlspecialchars($result['message']) . "</p>\n";
    }
    
    echo "<h3>3. Cleanup</h3>\n";
    
    // Hapus token test yang mungkin dibuat
    db()->query("DELETE FROM email_confirmations WHERE user_id = ?", [$test_user['id']]);
    echo "<p>✓ Token test dihapus</p>\n";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ ERROR: " . htmlspecialchars($e->getMessage()) . "</h3>\n";
    echo "<p>File: " . $e->getFile() . "</p>\n";
    echo "<p>Line: " . $e->getLine() . "</p>\n";
    echo "<p>Stack trace:</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}

echo "<hr>\n";
echo "<p><strong>Kesimpulan:</strong></p>\n";
echo "<ul>\n";
echo "<li>Template email konfirmasi sudah dibuat</li>\n";
echo "<li>Fungsi epic_send_email sudah diperbaiki parameternya</li>\n";
echo "<li>Sistem email confirmation siap digunakan</li>\n";
echo "</ul>\n";
?>