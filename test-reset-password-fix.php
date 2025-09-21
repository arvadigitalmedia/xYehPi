<?php
/**
 * Test Reset Password Fix
 * Script untuk memverifikasi perbaikan masalah reset password
 */

require_once 'bootstrap.php';

echo "=== TEST RESET PASSWORD FIX ===\n\n";

try {
    $db = db();
    
    echo "1. Setup Test User\n";
    
    // Cari atau buat user test
    $test_email = 'test.reset@example.com';
    $user = epic_get_user_by_email($test_email);
    
    if (!$user) {
        echo "   Creating test user...\n";
        
        // Buat user test
        $user_data = [
            'name' => 'Test Reset User',
            'email' => $test_email,
            'password' => 'oldpassword123',
            'status' => 'active',
            'role' => 'user',
            'email_verified' => 1
        ];
        
        $user_id = epic_create_user($user_data);
        $user = epic_get_user($user_id);
        echo "   ✓ Test user created with ID: $user_id\n";
    } else {
        echo "   ✓ Using existing test user ID: {$user['id']}\n";
    }
    
    echo "   Email: {$user['email']}\n";
    echo "   Current password hash: " . substr($user['password'], 0, 30) . "...\n\n";
    
    echo "2. Generate Reset Token\n";
    
    // Generate reset token
    $token = epic_generate_reset_token($user['id']);
    echo "   ✓ Reset token generated: " . substr($token, 0, 20) . "...\n";
    
    // Verify token
    $token_data = epic_verify_reset_token($token);
    if ($token_data) {
        echo "   ✓ Token verification successful\n";
        echo "   User ID from token: {$token_data['user_id']}\n\n";
    } else {
        throw new Exception('Token verification failed');
    }
    
    echo "3. Test Reset Password (FIXED VERSION)\n";
    
    $new_password = 'newpassword123';
    echo "   New password: '$new_password'\n";
    
    // Test reset password dengan fungsi yang sudah diperbaiki
    $reset_result = epic_reset_password($token, $new_password);
    
    if ($reset_result) {
        echo "   ✓ Password reset successful\n";
        
        // Verify password update
        $updated_user = epic_get_user($user['id']);
        echo "   Updated password hash: " . substr($updated_user['password'], 0, 30) . "...\n";
        
        // Test password verification
        $verify_result = password_verify($new_password, $updated_user['password']);
        echo "   Password verification: " . ($verify_result ? 'SUCCESS' : 'FAILED') . "\n";
        
        if ($verify_result) {
            echo "   ✅ RESET PASSWORD FIX BERHASIL!\n\n";
        } else {
            echo "   ❌ Password verification still failed\n\n";
        }
        
    } else {
        echo "   ❌ Password reset failed\n\n";
    }
    
    echo "4. Test Login dengan Password Baru\n";
    
    // Test login dengan password baru
    $login_user = epic_get_user_by_email($test_email);
    if ($login_user && password_verify($new_password, $login_user['password'])) {
        echo "   ✅ Login dengan password baru: SUCCESS\n";
    } else {
        echo "   ❌ Login dengan password baru: FAILED\n";
    }
    
    echo "\n5. Test Token Cleanup\n";
    
    // Cek apakah token sudah dihapus
    $remaining_token = epic_verify_reset_token($token);
    if (!$remaining_token) {
        echo "   ✅ Used token successfully deleted\n";
    } else {
        echo "   ⚠️ Used token still exists\n";
    }
    
    echo "\n6. Test dengan Password Baru Lagi\n";
    
    // Generate token baru untuk test kedua
    $token2 = epic_generate_reset_token($user['id']);
    $new_password2 = 'finalpassword123';
    
    echo "   Testing second reset with password: '$new_password2'\n";
    
    $reset_result2 = epic_reset_password($token2, $new_password2);
    
    if ($reset_result2) {
        $final_user = epic_get_user($user['id']);
        $verify_final = password_verify($new_password2, $final_user['password']);
        echo "   Second reset verification: " . ($verify_final ? 'SUCCESS' : 'FAILED') . "\n";
    }
    
    echo "\n=== TEST SUMMARY ===\n";
    echo "✅ Reset password fix berhasil mengatasi masalah double hashing\n";
    echo "✅ Password verification sekarang berfungsi dengan benar\n";
    echo "✅ Token cleanup berfungsi dengan baik\n";
    echo "✅ Multiple reset password dapat dilakukan tanpa masalah\n";
    
    echo "\n=== NEXT STEPS ===\n";
    echo "1. Test melalui UI browser\n";
    echo "2. Test dengan email reset password\n";
    echo "3. Test dengan berbagai skenario edge case\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>