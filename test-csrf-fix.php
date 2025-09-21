<?php
/**
 * Test CSRF Fix - Verifikasi fungsi CSRF tersedia
 */

// Include bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Test CSRF Fix</h1>";

// Test 1: Cek apakah fungsi CSRF tersedia
echo "<h2>1. Cek Fungsi CSRF</h2>";
$csrf_functions = [
    'epic_generate_csrf_token',
    'epic_verify_csrf_token',
    'epic_csrf_field'
];

foreach ($csrf_functions as $function) {
    $exists = function_exists($function);
    echo "<p>$function: " . ($exists ? '✅ Tersedia' : '❌ Tidak tersedia') . "</p>";
}

// Test 2: Generate CSRF token
echo "<h2>2. Test Generate CSRF Token</h2>";
try {
    if (function_exists('epic_generate_csrf_token')) {
        $token = epic_generate_csrf_token('test');
        echo "<p>✅ Token berhasil dibuat: " . substr($token, 0, 20) . "...</p>";
        
        // Test verify token
        if (function_exists('epic_verify_csrf_token')) {
            $valid = epic_verify_csrf_token($token, 'test', false);
            echo "<p>✅ Token valid: " . ($valid ? 'Ya' : 'Tidak') . "</p>";
        }
    } else {
        echo "<p>❌ Fungsi epic_generate_csrf_token tidak tersedia</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 3: Simulasi form WhatsApp settings
echo "<h2>3. Test Simulasi Form WhatsApp Settings</h2>";
try {
    // Simulasi POST data seperti di halaman WhatsApp settings
    $_POST['csrf_token'] = epic_generate_csrf_token('whatsapp_settings');
    $_POST['starsender_enabled'] = '1';
    $_POST['starsender_api_key'] = 'test_key';
    
    // Test verifikasi
    $csrf_valid = epic_verify_csrf_token($_POST['csrf_token'], 'whatsapp_settings', false);
    echo "<p>CSRF Token untuk WhatsApp settings: " . ($csrf_valid ? '✅ Valid' : '❌ Invalid') . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error simulasi: " . $e->getMessage() . "</p>";
}

// Test 4: Cek session
echo "<h2>4. Cek Session CSRF</h2>";
if (isset($_SESSION['csrf_tokens'])) {
    echo "<p>✅ Session CSRF tokens tersedia</p>";
    echo "<p>Jumlah tokens: " . count($_SESSION['csrf_tokens']) . "</p>";
} else {
    echo "<p>❌ Session CSRF tokens tidak tersedia</p>";
}

echo "<h2>Kesimpulan</h2>";
echo "<p>Jika semua test menunjukkan ✅, maka error 'Call to undefined function epic_verify_csrf_token()' sudah teratasi.</p>";
?>