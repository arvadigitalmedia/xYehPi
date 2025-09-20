<?php
/**
 * Test fungsi epic_confirm_email_token
 */

// Include core functions
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/email-confirmation.php';

echo "<h1>Test Fungsi epic_confirm_email_token</h1>";

// Test dengan token yang ada
$token = '03ab701f56ba3f03fc3ea4a38efcca046dd1527413ce2d57de78ee69d4354387';

echo "<p>Testing token: $token</p>";

try {
    // Cek apakah fungsi ada
    if (function_exists('epic_confirm_email_token')) {
        echo "<p>✓ Fungsi epic_confirm_email_token ditemukan</p>";
        
        // Test fungsi
        $result = epic_confirm_email_token($token);
        
        echo "<h3>Hasil:</h3>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        
    } else {
        echo "<p>✗ Fungsi epic_confirm_email_token tidak ditemukan</p>";
    }
    
    // Cek data di database
    echo "<h3>Data di Database:</h3>";
    
    $confirmation = db()->selectOne(
        "SELECT ec.*, u.id as user_id, u.name, u.email, u.email_verified_at 
         FROM epic_email_confirmations ec 
         JOIN epic_users u ON ec.user_id = u.id 
         WHERE ec.token = ?",
        [$token]
    );
    
    if ($confirmation) {
        echo "<p>✓ Token ditemukan di database</p>";
        echo "<pre>";
        print_r($confirmation);
        echo "</pre>";
    } else {
        echo "<p>✗ Token tidak ditemukan di database</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>