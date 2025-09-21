<?php
/**
 * Test CSRF Token Fix untuk WhatsApp Notification Settings
 */

// Start session
session_start();

// Include bootstrap
require_once 'bootstrap.php';

echo "<h2>Test CSRF Token Fix - WhatsApp Notification</h2>";

// Test 1: Generate CSRF Token
echo "<h3>1. Test Generate CSRF Token</h3>";
$token1 = epic_csrf_token();
echo "Token generated: " . substr($token1, 0, 16) . "...<br>";

// Test 2: Generate dengan action spesifik
echo "<h3>2. Test Generate dengan Action</h3>";
$token2 = epic_csrf_token('whatsapp_settings');
echo "Token untuk whatsapp_settings: " . substr($token2, 0, 16) . "...<br>";

// Test 3: Verify Token
echo "<h3>3. Test Verify CSRF Token</h3>";
if (function_exists('epic_verify_csrf_token')) {
    $verify_result = epic_verify_csrf_token($token1, 'default', false);
    echo "Verifikasi token default: " . ($verify_result ? "BERHASIL" : "GAGAL") . "<br>";
    
    $verify_result2 = epic_verify_csrf_token($token2, 'whatsapp_settings', false);
    echo "Verifikasi token whatsapp_settings: " . ($verify_result2 ? "BERHASIL" : "GAGAL") . "<br>";
} else {
    echo "Fungsi epic_verify_csrf_token tidak tersedia<br>";
}

// Test 4: Simulasi Form Submit
echo "<h3>4. Simulasi Form Submit WhatsApp Settings</h3>";
$_POST['csrf_token'] = epic_csrf_token();
$_POST['starsender_api_key'] = 'test_api_key';
$_POST['starsender_device_id'] = 'test_device';

echo "POST data disiapkan:<br>";
echo "- csrf_token: " . substr($_POST['csrf_token'], 0, 16) . "...<br>";
echo "- starsender_api_key: " . $_POST['starsender_api_key'] . "<br>";
echo "- starsender_device_id: " . $_POST['starsender_device_id'] . "<br>";

// Test verifikasi seperti di controller
if (function_exists('epic_verify_csrf_token')) {
    try {
        $csrf_valid = epic_verify_csrf_token($_POST['csrf_token'], 'default', false);
        echo "Verifikasi CSRF untuk form submit: " . ($csrf_valid ? "BERHASIL" : "GAGAL") . "<br>";
        
        if (!$csrf_valid) {
            echo "<span style='color: red;'>ERROR: Invalid CSRF token</span><br>";
        } else {
            echo "<span style='color: green;'>SUCCESS: CSRF token valid</span><br>";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>EXCEPTION: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "Fungsi epic_verify_csrf_token tidak tersedia<br>";
}

// Test 5: Check Session Data
echo "<h3>5. Check Session CSRF Data</h3>";
echo "Session csrf_tokens:<br>";
if (isset($_SESSION['csrf_tokens'])) {
    foreach ($_SESSION['csrf_tokens'] as $action => $data) {
        echo "- Action: $action<br>";
        echo "  Token: " . substr($data['token'], 0, 16) . "...<br>";
        echo "  Timestamp: " . date('Y-m-d H:i:s', $data['timestamp']) . "<br>";
        echo "  Used: " . ($data['used'] ? 'Yes' : 'No') . "<br>";
    }
} else {
    echo "Tidak ada csrf_tokens di session<br>";
}

echo "<hr>";
echo "<p><strong>Test selesai!</strong> Jika semua test BERHASIL, maka perbaikan CSRF token sudah benar.</p>";
?>