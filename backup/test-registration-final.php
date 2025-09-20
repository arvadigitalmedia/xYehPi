<?php
// Test Registration Final
define('EPIC_ROOT', __DIR__);
require_once EPIC_ROOT . '/bootstrap.php';

echo "<h1>Test Registrasi Final</h1>";

// Simulate POST data
$_POST = [
    'name' => 'Test User Final',
    'email' => 'testfinal@example.com',
    'password' => 'TestPass123',
    'confirm_password' => 'TestPass123',
    'referral_code' => '03KIPMLQ',
    'terms' => '1'
];

// Add CSRF token
require_once EPIC_ROOT . '/core/csrf-protection.php';
$csrf_token = epic_generate_csrf_token('register');
$_POST['csrf_token'] = $csrf_token;
$_POST['csrf_action'] = 'register';

echo "<h2>Data POST:</h2>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// Set REQUEST_METHOD
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "<h2>Testing Registrasi:</h2>";

try {
    // Call registration function directly
    require_once EPIC_ROOT . '/index.php';
    
    // Capture output
    ob_start();
    epic_route_register();
    $output = ob_get_clean();
    
    echo "<h3>Output:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Check if user was created
    $user = epic_get_user_by_email($_POST['email']);
    if ($user) {
        echo "<h3>✅ User berhasil dibuat:</h3>";
        echo "<pre>" . print_r($user, true) . "</pre>";
    } else {
        echo "<h3>❌ User tidak ditemukan</h3>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>