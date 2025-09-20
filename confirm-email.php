<?php
/**
 * Email Confirmation Token Handler
 * Handles email confirmation token validation
 */

// Include core functions
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/email-confirmation.php';

// Get token from URL
$token = $_GET['token'] ?? '';

echo "<h2>Debug Email Confirmation</h2>";
echo "<p>Token: " . htmlspecialchars($token) . "</p>";

if (empty($token)) {
    echo "<p>Error: Token kosong</p>";
    $_SESSION['epic_error'] = 'Token konfirmasi tidak valid';
    epic_redirect('register');
}

try {
    echo "<p>Mencari token di database...</p>";
    
    // Validate token
    $stored_token = db()->selectOne("SELECT * FROM epic_user_tokens WHERE token = ? AND type = 'email_verification' AND expires_at > NOW() AND used_at IS NULL", [$token]);
    
    if (!$stored_token) {
        echo "<p>Error: Token tidak ditemukan atau expired</p>";
        $_SESSION['epic_error'] = 'Token konfirmasi tidak valid atau sudah kedaluwarsa';
        epic_redirect('register');
    }
    
    echo "<p>Token ditemukan untuk user ID: " . $stored_token['user_id'] . "</p>";
    
    // Get user data
    $user = db()->selectOne("SELECT * FROM epic_users WHERE id = ?", [$stored_token['user_id']]);
    
    if (!$user) {
        echo "<p>Error: User tidak ditemukan</p>";
        $_SESSION['epic_error'] = 'User tidak ditemukan';
        epic_redirect('register');
    }
    
    echo "<p>User ditemukan: " . $user['name'] . " (" . $user['email'] . ")</p>";
    
    // Update user status to free (confirmed user)
    $result1 = db()->query("UPDATE epic_users SET status = 'free', email_verified_at = NOW() WHERE id = ?", [$user['id']]);
    echo "<p>Update user status: " . ($result1 ? 'berhasil' : 'gagal') . "</p>";
    
    // Mark token as used
    echo "<p>Mencoba update token ID: " . $stored_token['id'] . "</p>";
    $result2 = db()->query("UPDATE epic_user_tokens SET used_at = NOW() WHERE id = ?", [$stored_token['id']]);
    echo "<p>Update token status: " . ($result2 ? 'berhasil' : 'gagal') . "</p>";
    if (!$result2) {
        echo "<p>Error update token: " . db()->error() . "</p>";
    }
    
    // Set success session
    $_SESSION['epic_email_confirmed'] = true;
    $_SESSION['epic_success'] = 'Email berhasil dikonfirmasi! Silakan login untuk melanjutkan.';
    
    echo "<p>Konfirmasi berhasil! Redirect ke login...</p>";
    echo "<p><a href='login?confirmed=1'>Klik di sini jika tidak redirect otomatis</a></p>";
    
    // Redirect to login
    // epic_redirect('login?confirmed=1');
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
    error_log('Email confirmation error: ' . $e->getMessage());
    $_SESSION['epic_error'] = 'Terjadi kesalahan saat konfirmasi email';
    epic_redirect('register');
}
?>