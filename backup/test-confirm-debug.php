<?php
/**
 * Debug konfirmasi email dengan logging detail
 */

// Include core functions
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/email-confirmation.php';

echo "<h1>Debug Konfirmasi Email</h1>";

$token = '03ab701f56ba3f03fc3ea4a38efcca046dd1527413ce2d57de78ee69d4354387';

echo "<p>Testing token: $token</p>";

try {
    // Test koneksi database
    echo "<h3>1. Test Koneksi Database</h3>";
    $db = db();
    echo "<p>✓ Koneksi database OK</p>";
    
    // Cari token
    echo "<h3>2. Cari Token</h3>";
    $stored_token = $db->selectOne("SELECT * FROM epic_user_tokens WHERE token = ? AND type = 'email_verification' AND expires_at > NOW() AND used_at IS NULL", [$token]);
    
    if (!$stored_token) {
        echo "<p>✗ Token tidak ditemukan atau expired</p>";
        exit;
    }
    
    echo "<p>✓ Token ditemukan:</p>";
    echo "<pre>";
    print_r($stored_token);
    echo "</pre>";
    
    // Cari user
    echo "<h3>3. Cari User</h3>";
    $user = $db->selectOne("SELECT * FROM epic_users WHERE id = ?", [$stored_token['user_id']]);
    
    if (!$user) {
        echo "<p>✗ User tidak ditemukan</p>";
        exit;
    }
    
    echo "<p>✓ User ditemukan:</p>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    
    // Update user status
    echo "<h3>4. Update User Status</h3>";
    $sql1 = "UPDATE epic_users SET status = 'free', email_verified_at = NOW() WHERE id = ?";
    echo "<p>SQL: $sql1</p>";
    echo "<p>Parameter: " . $user['id'] . "</p>";
    
    $result1 = $db->query($sql1, [$user['id']]);
    echo "<p>Result: " . ($result1 ? 'berhasil' : 'gagal') . "</p>";
    
    if ($result1) {
        echo "<p>Affected rows: " . $db->getConnection()->rowCount() . "</p>";
    }
    
    // Update token status
    echo "<h3>5. Update Token Status</h3>";
    $sql2 = "UPDATE epic_user_tokens SET used_at = NOW() WHERE id = ?";
    echo "<p>SQL: $sql2</p>";
    echo "<p>Parameter: " . $stored_token['id'] . "</p>";
    
    $result2 = $db->query($sql2, [$stored_token['id']]);
    echo "<p>Result: " . ($result2 ? 'berhasil' : 'gagal') . "</p>";
    
    if ($result2) {
        echo "<p>Affected rows: " . $db->getConnection()->rowCount() . "</p>";
    }
    
    // Verifikasi perubahan
    echo "<h3>6. Verifikasi Perubahan</h3>";
    
    $updated_user = $db->selectOne("SELECT id, name, email, status, email_verified_at FROM epic_users WHERE id = ?", [$user['id']]);
    echo "<p>Updated user:</p>";
    echo "<pre>";
    print_r($updated_user);
    echo "</pre>";
    
    $updated_token = $db->selectOne("SELECT id, user_id, type, expires_at, used_at FROM epic_user_tokens WHERE id = ?", [$stored_token['id']]);
    echo "<p>Updated token:</p>";
    echo "<pre>";
    print_r($updated_token);
    echo "</pre>";
    
    echo "<h3>✓ Konfirmasi Email Berhasil!</h3>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>