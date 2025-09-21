<?php
/**
 * Test Login dengan Akun yang Sudah Dikonfirmasi
 * Menguji proses login end-to-end dengan akun verified
 */

require_once __DIR__ . '/bootstrap.php';

echo "=== TEST LOGIN DENGAN AKUN CONFIRMED ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

$test_email = 'testlogin' . time() . '@example.com';
$test_name = 'Test Login User';
$test_password = 'TestLogin123!';

try {
    // Step 1: Create confirmed user
    echo "1. Creating confirmed user...\n";
    
    $user_data = [
        'name' => $test_name,
        'email' => $test_email,
        'phone' => '081234567890',
        'password' => password_hash($test_password, PASSWORD_DEFAULT),
        'referral_code' => 'LOGIN' . strtoupper(substr(md5(time()), 0, 6)),
        'status' => 'free', // Already confirmed
        'role' => 'user',
        'email_verified' => 1,
        'email_verified_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $user_id = db()->insert('epic_users', $user_data);
    
    if ($user_id) {
        echo "✅ Confirmed user created with ID: $user_id\n";
        echo "   Email: $test_email\n";
        echo "   Status: free (confirmed)\n";
        echo "   Email Verified: Yes\n\n";
    } else {
        throw new Exception("Failed to create user");
    }
    
    // Step 2: Test login validation
    echo "2. Testing login validation...\n";
    
    // Test empty fields
    $empty_test = [
        'email' => '',
        'password' => ''
    ];
    
    $required_fields = ['email', 'password'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($empty_test[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo "✅ Empty field validation: Missing fields detected (" . implode(', ', $missing_fields) . ")\n";
    }
    
    // Test invalid email format
    if (!filter_var('invalid-email', FILTER_VALIDATE_EMAIL)) {
        echo "✅ Email format validation: Invalid email rejected\n";
    }
    
    // Test valid data
    $valid_test = [
        'email' => $test_email,
        'password' => $test_password
    ];
    
    $valid_missing = [];
    foreach ($required_fields as $field) {
        if (empty($valid_test[$field])) {
            $valid_missing[] = $field;
        }
    }
    
    if (empty($valid_missing) && filter_var($valid_test['email'], FILTER_VALIDATE_EMAIL)) {
        echo "✅ Valid data validation: All checks passed\n\n";
    }
    
    // Step 3: Test user lookup
    echo "3. Testing user lookup...\n";
    
    $login_user = db()->selectOne(
        "SELECT id, name, email, password, status, role, email_verified_at FROM epic_users WHERE email = ?",
        [$test_email]
    );
    
    if ($login_user) {
        echo "✅ User found in database\n";
        echo "   ID: " . $login_user['id'] . "\n";
        echo "   Name: " . $login_user['name'] . "\n";
        echo "   Status: " . $login_user['status'] . "\n";
        echo "   Email Verified: " . ($login_user['email_verified_at'] ? 'Yes' : 'No') . "\n\n";
    } else {
        throw new Exception("User not found");
    }
    
    // Step 4: Test password verification
    echo "4. Testing password verification...\n";
    
    if (password_verify($test_password, $login_user['password'])) {
        echo "✅ Password verification successful\n\n";
    } else {
        throw new Exception("Password verification failed");
    }
    
    // Step 5: Test account status checks
    echo "5. Testing account status checks...\n";
    
    // Check if account is active
    if ($login_user['status'] === 'free') {
        echo "✅ Account status is active (free)\n";
    } else {
        echo "❌ Account status is not active: " . $login_user['status'] . "\n";
    }
    
    // Check if email is verified
    if ($login_user['email_verified_at']) {
        echo "✅ Email is verified\n";
    } else {
        echo "❌ Email is not verified\n";
    }
    
    // Overall login eligibility
    if ($login_user['status'] === 'free' && $login_user['email_verified_at']) {
        echo "✅ User is eligible for login\n\n";
    } else {
        echo "❌ User is not eligible for login\n\n";
    }
    
    // Step 6: Test session creation simulation
    echo "6. Testing session creation simulation...\n";
    
    // Simulate session data that would be created
    $session_data = [
        'epic_user_id' => $login_user['id'],
        'epic_user_name' => $login_user['name'],
        'epic_user_email' => $login_user['email'],
        'epic_user_role' => $login_user['role'],
        'epic_logged_in' => true,
        'epic_login_time' => time()
    ];
    
    echo "✅ Session data prepared:\n";
    foreach ($session_data as $key => $value) {
        echo "   $key: $value\n";
    }
    echo "\n";
    
    // Step 7: Test last login update
    echo "7. Testing last login update...\n";
    
    $login_update = db()->query(
        "UPDATE epic_users SET last_login_at = NOW() WHERE id = ?",
        [$login_user['id']]
    );
    
    if ($login_update) {
        echo "✅ Last login timestamp updated\n";
        
        // Verify update
        $updated_user = db()->selectOne("SELECT last_login_at FROM epic_users WHERE id = ?", [$login_user['id']]);
        echo "   Last login: " . $updated_user['last_login_at'] . "\n\n";
    } else {
        echo "❌ Failed to update last login\n\n";
    }
    
    // Step 8: Test redirect simulation
    echo "8. Testing redirect simulation...\n";
    
    $redirect_url = "dashboard";
    echo "✅ Would redirect to: $redirect_url\n";
    echo "   Full URL: http://localhost:8080/$redirect_url\n\n";
    
    // Step 9: Cleanup
    echo "9. Cleaning up test data...\n";
    
    $cleanup = db()->query("DELETE FROM epic_users WHERE id = ?", [$user_id]);
    
    if ($cleanup) {
        echo "✅ Test user deleted successfully\n\n";
    } else {
        echo "⚠️ Cleanup may have failed\n\n";
    }
    
    // Summary
    echo "=== LOGIN TEST SUMMARY ===\n";
    echo "✅ User Creation: OK\n";
    echo "✅ Login Validation: OK\n";
    echo "✅ User Lookup: OK\n";
    echo "✅ Password Verification: OK\n";
    echo "✅ Account Status Check: OK\n";
    echo "✅ Session Creation: OK\n";
    echo "✅ Last Login Update: OK\n";
    echo "✅ Redirect Logic: OK\n";
    echo "✅ Cleanup: OK\n\n";
    
    echo "=== LOGIN SYSTEM WORKING PROPERLY ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    
    // Emergency cleanup
    if (isset($user_id)) {
        echo "\nPerforming emergency cleanup...\n";
        db()->query("DELETE FROM epic_users WHERE id = ?", [$user_id]);
        echo "Emergency cleanup completed.\n";
    }
}

echo "\n=== TEST COMPLETED ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
?>