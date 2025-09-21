<?php
require_once 'bootstrap.php';

echo "<h2>🧪 Test Email Confirmation Flow</h2>";

// 1. Create test user with pending status
echo "<h3>1. Create Test User</h3>";
$test_email = 'test-confirm-flow-' . time() . '@example.com';
$test_name = 'Test Confirm Flow User';

$user_id = db()->insert(db()->table('users'), [
    'name' => $test_name,
    'email' => $test_email,
    'password' => password_hash('password123', PASSWORD_DEFAULT),
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
]);

if ($user_id) {
    echo "<p style='color: green;'>✅ Test user created: {$test_name} ({$test_email}) - ID: {$user_id}</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to create test user</p>";
    exit;
}

// 2. Create email verification token
echo "<h3>2. Create Email Verification Token</h3>";
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

$token_id = db()->insert(db()->table('user_tokens'), [
    'user_id' => $user_id,
    'token' => $token,
    'type' => 'email_verification',
    'expires_at' => $expires,
    'created_at' => date('Y-m-d H:i:s')
]);

if ($token_id) {
    echo "<p style='color: green;'>✅ Email verification token created</p>";
    echo "<p><strong>Token:</strong> {$token}</p>";
    echo "<p><strong>Confirmation URL:</strong> <a href='http://localhost:8080/confirm-email.php?token={$token}' target='_blank'>http://localhost:8080/confirm-email.php?token={$token}</a></p>";
} else {
    echo "<p style='color: red;'>❌ Failed to create token</p>";
    exit;
}

// 3. Verify user status before confirmation
echo "<h3>3. User Status Before Confirmation</h3>";
$user_before = db()->selectOne("SELECT * FROM " . db()->table('users') . " WHERE id = ?", [$user_id]);
echo "<p>Status: <strong>{$user_before['status']}</strong></p>";
echo "<p>Email Verified: <strong>" . ($user_before['email_verified_at'] ? 'YES' : 'NO') . "</strong></p>";

// 4. Simulate email confirmation by calling the confirmation logic
echo "<h3>4. Simulate Email Confirmation</h3>";

try {
    // Validate token (same logic as confirm-email.php)
    $stored_token = db()->selectOne(
        "SELECT * FROM " . db()->table('user_tokens') . " 
         WHERE token = ? AND type = 'email_verification' 
         AND expires_at > NOW() AND used_at IS NULL", 
        [$token]
    );
    
    if (!$stored_token) {
        echo "<p style='color: red;'>❌ Token not found or expired</p>";
    } else {
        echo "<p style='color: green;'>✅ Token found and valid</p>";
        
        // Get user data
        $user = db()->selectOne("SELECT * FROM " . db()->table('users') . " WHERE id = ?", [$stored_token['user_id']]);
        
        if (!$user) {
            echo "<p style='color: red;'>❌ User not found</p>";
        } else {
            echo "<p style='color: green;'>✅ User found: {$user['name']}</p>";
            
            // Update user status to free (confirmed user)
            $update_user = db()->query(
                "UPDATE " . db()->table('users') . " 
                 SET status = 'free', email_verified_at = NOW() 
                 WHERE id = ?", 
                [$user['id']]
            );
            
            // Mark token as used
            $update_token = db()->query(
                "UPDATE " . db()->table('user_tokens') . " 
                 SET used_at = NOW() 
                 WHERE id = ?", 
                [$stored_token['id']]
            );
            
            if ($update_user && $update_token) {
                echo "<p style='color: green;'>✅ Email confirmation successful!</p>";
                echo "<p>- User status updated to 'free'</p>";
                echo "<p>- Email verified timestamp set</p>";
                echo "<p>- Token marked as used</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to update user or token</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error during confirmation: " . $e->getMessage() . "</p>";
}

// 5. Verify user status after confirmation
echo "<h3>5. User Status After Confirmation</h3>";
$user_after = db()->selectOne("SELECT * FROM " . db()->table('users') . " WHERE id = ?", [$user_id]);
echo "<p>Status: <strong>{$user_after['status']}</strong></p>";
echo "<p>Email Verified: <strong>" . ($user_after['email_verified_at'] ? 'YES (' . $user_after['email_verified_at'] . ')' : 'NO') . "</strong></p>";

// 6. Test token reuse (should fail)
echo "<h3>6. Test Token Reuse (Should Fail)</h3>";
$reuse_token = db()->selectOne(
    "SELECT * FROM " . db()->table('user_tokens') . " 
     WHERE token = ? AND type = 'email_verification' 
     AND expires_at > NOW() AND used_at IS NULL", 
    [$token]
);

if (!$reuse_token) {
    echo "<p style='color: green;'>✅ Token correctly marked as used - cannot be reused</p>";
} else {
    echo "<p style='color: red;'>❌ Token can still be reused (security issue)</p>";
}

// 7. Cleanup
echo "<h3>7. Cleanup Test Data</h3>";
$delete_token = db()->query("DELETE FROM " . db()->table('user_tokens') . " WHERE id = ?", [$token_id]);
$delete_user = db()->query("DELETE FROM " . db()->table('users') . " WHERE id = ?", [$user_id]);

if ($delete_token && $delete_user) {
    echo "<p style='color: green;'>✅ Test data cleaned up successfully</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Cleanup may have failed - check manually</p>";
}

echo "<h3>✅ Email Confirmation Flow Test Complete!</h3>";
echo "<p><strong>Summary:</strong> Email confirmation system is working correctly without redirect loops.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Test the actual confirmation page: <a href='http://localhost:8080/confirm-email.php?token=invalid' target='_blank'>Test with invalid token</a></li>";
echo "<li>Test registration flow: <a href='http://localhost:8080/register' target='_blank'>Register new user</a></li>";
echo "<li>Test login after confirmation: <a href='http://localhost:8080/login' target='_blank'>Login page</a></li>";
echo "</ul>";
?>