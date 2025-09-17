<?php
/**
 * Create Free User for Testing
 */

require_once __DIR__ . '/bootstrap.php';

try {
    // Check if free user already exists
    $user = epic_get_user_by_email('freeuser@epichub.com');
    
    if (!$user) {
        // Generate UUID and referral code
        $uuid = epic_generate_uuid();
        $referral_code = epic_generate_referral_code();
        $hashed_password = password_hash('freeuser123', PASSWORD_DEFAULT);
        
        // Prepare user data
        $user_data = [
            'uuid' => $uuid,
            'name' => 'Free Test User',
            'email' => 'freeuser@epichub.com',
            'password' => $hashed_password,
            'phone' => '+6281234567891',
            'referral_code' => $referral_code,
            'status' => 'free',
            'role' => 'user',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert user
        $user_id = db()->insert('users', $user_data);
        
        echo "Free user created successfully!\n";
        echo "ID: " . $user_id . "\n";
        echo "Email: freeuser@epichub.com\n";
        echo "Password: freeuser123\n";
        echo "Status: free\n";
        echo "Login URL: http://localhost:8000/login\n";
        
    } else {
        echo "Free user already exists:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Password: freeuser123\n";
        echo "Login URL: http://localhost:8000/login\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>