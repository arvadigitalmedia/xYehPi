<?php
/**
 * Test Halaman Confirm Email
 * Membuat user dan token untuk test halaman confirm-email.php
 */

require_once __DIR__ . '/bootstrap.php';

echo "=== TEST HALAMAN CONFIRM EMAIL ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

$test_email = 'testpage' . time() . '@example.com';
$test_name = 'Test Page User';

try {
    // Step 1: Create test user
    echo "1. Creating test user for page test...\n";
    
    $user_data = [
        'name' => $test_name,
        'email' => $test_email,
        'phone' => '081234567890',
        'password' => password_hash('TestPassword123!', PASSWORD_DEFAULT),
        'referral_code' => 'PAGE' . strtoupper(substr(md5(time()), 0, 6)),
        'status' => 'pending',
        'role' => 'user',
        'email_verified' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $user_id = db()->insert('epic_users', $user_data);
    
    if ($user_id) {
        echo "✅ User created with ID: $user_id\n";
        echo "   Email: $test_email\n\n";
    } else {
        throw new Exception("Failed to create user");
    }
    
    // Step 2: Generate token
    echo "2. Generating token for page test...\n";
    
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $token_data = [
        'user_id' => $user_id,
        'token' => $token,
        'type' => 'email_verification',
        'expires_at' => $expires_at,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $token_id = db()->insert('epic_user_tokens', $token_data);
    
    if ($token_id) {
        echo "✅ Token generated successfully\n";
        echo "   Token: $token\n\n";
    } else {
        throw new Exception("Failed to generate token");
    }
    
    // Step 3: Generate test URLs
    echo "3. Generated test URLs:\n";
    echo "   Confirm Email Page: http://localhost:8080/confirm-email?token=$token\n";
    echo "   Direct PHP File: http://localhost:8080/confirm-email.php?token=$token\n\n";
    
    echo "=== INSTRUCTIONS ===\n";
    echo "1. Copy salah satu URL di atas\n";
    echo "2. Buka di browser untuk test halaman konfirmasi\n";
    echo "3. Jalankan cleanup script setelah selesai test\n\n";
    
    echo "User ID untuk cleanup: $user_id\n";
    echo "Token ID untuk cleanup: $token_id\n\n";
    
    // Create cleanup script
    $cleanup_script = "<?php
require_once __DIR__ . '/bootstrap.php';

echo \"Cleaning up test data...\\n\";
\$cleanup1 = db()->query(\"DELETE FROM epic_user_tokens WHERE id = ?\", [$token_id]);
\$cleanup2 = db()->query(\"DELETE FROM epic_users WHERE id = ?\", [$user_id]);

if (\$cleanup1 && \$cleanup2) {
    echo \"✅ Cleanup successful\\n\";
} else {
    echo \"❌ Cleanup failed\\n\";
}
?>";
    
    file_put_contents(__DIR__ . '/cleanup-test-page.php', $cleanup_script);
    echo "Cleanup script created: cleanup-test-page.php\n";
    echo "Run: php cleanup-test-page.php (after testing)\n\n";
    
    echo "=== READY FOR MANUAL TESTING ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    
    // Emergency cleanup
    if (isset($user_id)) {
        echo "\nPerforming emergency cleanup...\n";
        db()->query("DELETE FROM epic_user_tokens WHERE user_id = ?", [$user_id]);
        db()->query("DELETE FROM epic_users WHERE id = ?", [$user_id]);
        echo "Emergency cleanup completed.\n";
    }
}

echo "\n=== SCRIPT COMPLETED ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
?>