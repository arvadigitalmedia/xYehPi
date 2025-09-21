<?php
require_once 'bootstrap.php';

// Create a test user with pending status
$test_email = 'test-confirm-' . time() . '@example.com';
$test_name = 'Test Confirm User';

// Insert test user
$user_id = db()->insert(db()->table('users'), [
    'name' => $test_name,
    'email' => $test_email,
    'password' => password_hash('password123', PASSWORD_DEFAULT),
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
]);

if ($user_id) {
    echo "Test user created: {$test_name} ({$test_email}) - ID: {$user_id}\n";
    
    // Create token
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
        echo "Test token created: {$token}\n";
        echo "Test URL: http://localhost:8080/confirm-email.php?token={$token}\n";
        echo "\nCopy URL di atas untuk test konfirmasi email!\n";
    } else {
        echo "Failed to create token\n";
    }
} else {
    echo "Failed to create test user\n";
}
?>