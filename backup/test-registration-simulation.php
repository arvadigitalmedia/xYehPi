<?php
/**
 * Test Script - Simulasi Registrasi Pengguna
 * Menguji alur pendaftaran end-to-end
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli') {
    die('Script ini hanya bisa dijalankan via CLI');
}

// Include EPIC initialization
require_once __DIR__ . '/bootstrap.php';

echo "=== SIMULASI ALUR REGISTRASI PENGGUNA ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Test data untuk registrasi
$test_users = [
    [
        'name' => 'Test User Simulasi',
        'email' => 'testuser' . time() . '@example.com',
        'phone' => '081234567890',
        'password' => 'TestPassword123!',
        'confirm_password' => 'TestPassword123!',
        'terms' => '1',
        'marketing' => '1'
    ]
];

foreach ($test_users as $index => $user_data) {
    echo "--- Test User #" . ($index + 1) . " ---\n";
    echo "Email: " . $user_data['email'] . "\n";
    echo "Name: " . $user_data['name'] . "\n";
    echo "Phone: " . $user_data['phone'] . "\n\n";
    
    // Step 1: Test form validation
    echo "1. Testing Form Validation...\n";
    
    // Check required fields
    $required_fields = ['name', 'email', 'password', 'confirm_password', 'terms'];
    $validation_errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($user_data[$field])) {
            $validation_errors[] = "Field '$field' is required";
        }
    }
    
    // Email validation
    if (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
        $validation_errors[] = "Invalid email format";
    }
    
    // Password validation
    if (strlen($user_data['password']) < 8) {
        $validation_errors[] = "Password must be at least 8 characters";
    }
    
    // Password confirmation
    if ($user_data['password'] !== $user_data['confirm_password']) {
        $validation_errors[] = "Password confirmation does not match";
    }
    
    if (!empty($validation_errors)) {
        echo "❌ Validation failed:\n";
        foreach ($validation_errors as $error) {
            echo "   - $error\n";
        }
        continue;
    }
    
    echo "✅ Form validation passed\n\n";
    
    // Step 2: Check if email already exists
    echo "2. Checking Email Uniqueness...\n";
    
    try {
        $existing_user = db()->selectOne(
            "SELECT id, email FROM epic_users WHERE email = ?",
            [$user_data['email']]
        );
        
        if ($existing_user) {
            echo "❌ Email already exists: " . $existing_user['email'] . "\n";
            continue;
        }
        
        echo "✅ Email is unique\n\n";
        
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "\n";
        continue;
    }
    
    // Step 3: Simulate registration process
    echo "3. Simulating Registration Process...\n";
    
    try {
        // Generate referral code
        $referral_code = 'TEST' . strtoupper(substr(md5($user_data['email'] . time()), 0, 6));
        
        // Hash password
        $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);
        
        // Generate email confirmation token
        $confirmation_token = sprintf('%06d', mt_rand(100000, 999999));
        
        // Insert user into database
        $user_id = db()->insert('epic_users', [
            'name' => $user_data['name'],
            'email' => $user_data['email'],
            'phone' => $user_data['phone'],
            'password' => $password_hash,
            'referral_code' => $referral_code,
            'status' => 'pending',
            'role' => 'user',
            'email_verified' => 0,
            'email_confirmation_token' => $confirmation_token,
            'marketing_consent' => isset($user_data['marketing']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($user_id) {
            echo "✅ User created successfully with ID: $user_id\n";
            echo "   Referral Code: $referral_code\n";
            echo "   Confirmation Token: " . substr($confirmation_token, 0, 16) . "...\n\n";
            
            // Step 4: Test email confirmation process
            echo "4. Testing Email Confirmation...\n";
            
            // Simulate email confirmation URL
            $confirmation_url = "http://localhost:8080/email-confirmation?token=" . $confirmation_token;
            echo "   Confirmation URL: $confirmation_url\n";
            
            // Check if token is valid
            $user_for_confirmation = db()->selectOne(
                "SELECT id, email, email_confirmation_token, email_verified FROM epic_users WHERE id = ?",
                [$user_id]
            );
            
            if ($user_for_confirmation && $user_for_confirmation['email_confirmation_token'] === $confirmation_token) {
                echo "✅ Confirmation token is valid\n";
                
                // Simulate email confirmation
                 $confirmed = db()->update('epic_users', [
                     'email_verified' => 1,
                     'email_confirmation_token' => null,
                     'status' => 'active',
                     'updated_at' => date('Y-m-d H:i:s')
                 ], 'id = ?', [$user_id]);
                
                if ($confirmed) {
                    echo "✅ Email confirmed successfully\n\n";
                    
                    // Step 5: Test login process
                    echo "5. Testing Login Process...\n";
                    
                    // Verify user credentials
                    $login_user = db()->selectOne(
                        "SELECT id, email, password, status, email_verified FROM epic_users WHERE email = ?",
                        [$user_data['email']]
                    );
                    
                    if ($login_user) {
                        // Check password
                        if (password_verify($user_data['password'], $login_user['password'])) {
                            echo "✅ Password verification successful\n";
                            
                            // Check account status
                            if ($login_user['status'] === 'active' && $login_user['email_verified'] == 1) {
                                echo "✅ Account is active and email verified\n";
                                echo "✅ Login simulation successful\n\n";
                                
                                // Step 6: Cleanup test data
                                echo "6. Cleaning up test data...\n";
                                
                                $deleted = db()->delete('epic_users', 'id = ?', [$user_id]);
                                if ($deleted) {
                                    echo "✅ Test user deleted successfully\n";
                                } else {
                                    echo "⚠️  Warning: Could not delete test user (ID: $user_id)\n";
                                }
                                
                                echo "\n=== SIMULASI BERHASIL ===\n";
                                echo "✅ Registrasi: OK\n";
                                echo "✅ Email Confirmation: OK\n";
                                echo "✅ Login: OK\n";
                                echo "✅ Cleanup: OK\n\n";
                                
                            } else {
                                echo "❌ Account not active or email not verified\n";
                                echo "   Status: " . $login_user['status'] . "\n";
                                echo "   Email Verified: " . ($login_user['email_verified'] ? 'Yes' : 'No') . "\n";
                            }
                        } else {
                            echo "❌ Password verification failed\n";
                        }
                    } else {
                        echo "❌ User not found for login\n";
                    }
                } else {
                    echo "❌ Failed to confirm email\n";
                }
            } else {
                echo "❌ Invalid confirmation token\n";
            }
            
        } else {
            echo "❌ Failed to create user\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Registration error: " . $e->getMessage() . "\n";
        
        // Try to cleanup if user was created
        if (isset($user_id) && $user_id) {
            try {
                db()->delete('epic_users', 'id = ?', [$user_id]);
                echo "   Cleanup: Test user deleted\n";
            } catch (Exception $cleanup_error) {
                echo "   Cleanup error: " . $cleanup_error->getMessage() . "\n";
            }
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "=== SIMULASI SELESAI ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";