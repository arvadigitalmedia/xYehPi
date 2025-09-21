<?php
// Debug file untuk test reset password
require_once 'config/database.php';
require_once 'core/functions.php';

echo "<h2>Debug Reset Password</h2>";

$token = $_GET['token'] ?? '07811c6e0aae1721ece27c63369a869871c5298ed733c4911366c678915d65fd';

echo "<p>Token: " . htmlspecialchars($token) . "</p>";

try {
    echo "<h3>1. Test Database Connection</h3>";
    $db = db();
    echo "✓ Database connected<br>";
    
    echo "<h3>2. Test Constants</h3>";
    echo "TABLE_USERS: " . TABLE_USERS . "<br>";
    echo "TABLE_USER_TOKENS: " . TABLE_USER_TOKENS . "<br>";
    
    echo "<h3>3. Test epic_verify_reset_token Function</h3>";
    $token_data = epic_verify_reset_token($token);
    if ($token_data) {
        echo "✓ Token valid<br>";
        echo "User ID: " . $token_data['user_id'] . "<br>";
        echo "Expires: " . $token_data['expired_at'] . "<br>";
    } else {
        echo "✗ Token invalid or expired<br>";
    }
    
    echo "<h3>4. Test User Exists</h3>";
    if ($token_data) {
        $user = db()->selectOne("SELECT * FROM " . TABLE_USERS . " WHERE id = ?", [$token_data['user_id']]);
        if ($user) {
            echo "✓ User found: " . $user['name'] . " (" . $user['email'] . ")<br>";
        } else {
            echo "✗ User not found<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}
?>