<?php
/**
 * Test WhatsApp Settings Page - Verifikasi error CSRF sudah teratasi
 */

// Include bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Test WhatsApp Settings Page</h1>";

// Test 1: Cek apakah fungsi CSRF tersedia
echo "<h2>1. Verifikasi Fungsi CSRF</h2>";
if (function_exists('epic_verify_csrf_token')) {
    echo "<p>✅ Fungsi epic_verify_csrf_token tersedia</p>";
} else {
    echo "<p>❌ Fungsi epic_verify_csrf_token TIDAK tersedia</p>";
    exit;
}

// Test 2: Simulasi akses halaman WhatsApp settings
echo "<h2>2. Test Akses Halaman WhatsApp Settings</h2>";

try {
    // Set up admin session (simulasi login admin)
    $_SESSION['epic_user_id'] = 1;
    $_SESSION['epic_user_role'] = 'admin';
    
    // Simulasi GET request ke halaman WhatsApp settings
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    echo "<p>Mencoba memuat halaman WhatsApp settings...</p>";
    
    // Capture output dari halaman
    ob_start();
    
    // Include file yang bermasalah
    include __DIR__ . '/themes/modern/admin/settings-whatsapp-notification.php';
    
    $output = ob_get_clean();
    
    echo "<p>✅ Halaman berhasil dimuat tanpa error!</p>";
    echo "<p>Panjang output: " . strlen($output) . " karakter</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error saat memuat halaman: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p>❌ Fatal error saat memuat halaman: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}

// Test 3: Simulasi POST request (submit form)
echo "<h2>3. Test Submit Form WhatsApp Settings</h2>";

try {
    // Generate CSRF token
    $csrf_token = epic_generate_csrf_token('whatsapp_settings');
    
    // Simulasi POST data
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'csrf_token' => $csrf_token,
        'starsender_enabled' => '1',
        'starsender_api_key' => 'test_api_key_12345',
        'test_phone_number' => '081234567890'
    ];
    
    echo "<p>Mencoba submit form dengan CSRF token...</p>";
    
    // Test verifikasi CSRF
    $csrf_valid = epic_verify_csrf_token($_POST['csrf_token'], 'whatsapp_settings', false);
    echo "<p>CSRF Token valid: " . ($csrf_valid ? '✅ Ya' : '❌ Tidak') . "</p>";
    
    if ($csrf_valid) {
        echo "<p>✅ Form dapat disubmit tanpa error CSRF!</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error saat test submit: " . $e->getMessage() . "</p>";
}

echo "<h2>Kesimpulan</h2>";
echo "<p>Jika test di atas menunjukkan ✅, maka error 'Call to undefined function epic_verify_csrf_token()' pada halaman WhatsApp notification sudah teratasi.</p>";
echo "<p><strong>Silakan coba akses halaman admin WhatsApp settings melalui browser untuk konfirmasi final.</strong></p>";
?>