<?php
/**
 * Create Test User for Member Area Testing
 * Script untuk membuat user testing dengan akses penuh
 */

require_once __DIR__ . '/bootstrap.php';

try {
    // Test user data
    $test_user = [
        'name' => 'Test Member User',
        'email' => 'testmember@epichub.com',
        'password' => 'testmember123',
        'phone' => '+6281234567890',
        'status' => 'active',
        'role' => 'user'
    ];
    
    // Check if user already exists
    $existing_user = epic_get_user_by_email($test_user['email']);
    if ($existing_user) {
        echo "User already exists with ID: " . $existing_user['id'] . "\n";
        echo "Email: " . $existing_user['email'] . "\n";
        echo "Name: " . $existing_user['name'] . "\n";
        echo "Status: " . $existing_user['status'] . "\n";
        echo "Role: " . $existing_user['role'] . "\n";
        echo "\n=== LOGIN CREDENTIALS ===\n";
        echo "Email: testmember@epichub.com\n";
        echo "Password: testmember123\n";
        echo "Login URL: http://localhost:8000/login\n";
        exit;
    }
    
    // Generate UUID and referral code
    $uuid = epic_generate_uuid();
    $referral_code = epic_generate_referral_code();
    
    // Hash password
    $hashed_password = password_hash($test_user['password'], PASSWORD_DEFAULT);
    
    // Prepare user data for database
    $user_data = [
        'uuid' => $uuid,
        'name' => $test_user['name'],
        'email' => $test_user['email'],
        'password' => $hashed_password,
        'phone' => $test_user['phone'],
        'referral_code' => $referral_code,
        'status' => $test_user['status'],
        'role' => $test_user['role'],
        'email_verified_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Insert user into database
    $user_id = db()->insert('users', $user_data);
    
    if ($user_id) {
        echo "✅ Test user created successfully!\n";
        echo "User ID: " . $user_id . "\n";
        echo "UUID: " . $uuid . "\n";
        echo "Referral Code: " . $referral_code . "\n";
        echo "\n=== LOGIN CREDENTIALS ===\n";
        echo "Email: " . $test_user['email'] . "\n";
        echo "Password: " . $test_user['password'] . "\n";
        echo "\n=== ACCESS URLS ===\n";
        echo "Login: http://localhost:8000/login\n";
        echo "Member Area: http://localhost:8000/dashboard/member\n";
        echo "\n=== USER DETAILS ===\n";
        echo "Name: " . $test_user['name'] . "\n";
        echo "Status: " . $test_user['status'] . " (Free Account)\n";
        echo "Role: " . $test_user['role'] . "\n";
        echo "Phone: " . $test_user['phone'] . "\n";
        echo "Email Verified: Yes\n";
        echo "\n=== TESTING FEATURES ===\n";
        echo "✅ Home Dashboard\n";
        echo "✅ Edit Profil\n";
        echo "❌ Prospek (Premium Only - akan melihat upgrade CTA)\n";
        echo "❌ Bonus Cash (Premium Only - akan melihat upgrade CTA)\n";
        echo "✅ Akses Produk (Free products only)\n";
        echo "✅ History Order\n";
        echo "\n=== UPGRADE TO PREMIUM ===\n";
        echo "Untuk testing EPIC Account features, ubah status user menjadi 'premium' di database:\n";
        echo "UPDATE epic_users SET status = 'premium' WHERE id = " . $user_id . ";\n";
        
        // Create some sample data for testing
        echo "\n=== CREATING SAMPLE DATA ===\n";
        
        // Add some profile completion data
        $profile_data = [
            'user_id' => $user_id,
            'bio' => 'Saya adalah test user untuk keperluan pengujian Member Area EPIC Hub.',
            'address' => 'Jakarta, Indonesia',
            'website' => 'https://epichub.com',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Check if user_profiles table exists
        $tables = db()->select("SHOW TABLES LIKE 'epic_user_profiles'");
        if (!empty($tables)) {
            $profile_id = db()->insert('user_profiles', $profile_data);
            if ($profile_id) {
                echo "✅ Sample profile data created\n";
            }
        }
        
        echo "\n🎉 Test user setup completed! You can now login and test the Member Area.\n";
        
    } else {
        echo "❌ Failed to create test user\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating test user: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>