<?php
/**
 * Debug Password Hashing Issue
 * Script untuk mengidentifikasi masalah double hashing pada reset password
 */

require_once 'bootstrap.php';

echo "=== DEBUG PASSWORD HASHING ISSUE ===\n\n";

try {
    // Test password
    $test_password = 'testpassword123';
    
    echo "1. Test Password Hashing Logic\n";
    echo "   Original password: '$test_password'\n";
    
    // Simulasi hashing di epic_reset_password
    $hashed_once = password_hash($test_password, PASSWORD_DEFAULT);
    echo "   Hashed once (in epic_reset_password): " . substr($hashed_once, 0, 30) . "...\n";
    
    // Simulasi hashing di epic_update_user
    $hashed_twice = password_hash($hashed_once, PASSWORD_DEFAULT);
    echo "   Hashed twice (in epic_update_user): " . substr($hashed_twice, 0, 30) . "...\n\n";
    
    echo "2. Test Password Verification\n";
    
    // Test verifikasi dengan hash sekali
    $verify_once = password_verify($test_password, $hashed_once);
    echo "   Verify original vs hashed_once: " . ($verify_once ? 'TRUE' : 'FALSE') . "\n";
    
    // Test verifikasi dengan hash dua kali (ini yang menyebabkan masalah)
    $verify_twice = password_verify($test_password, $hashed_twice);
    echo "   Verify original vs hashed_twice: " . ($verify_twice ? 'TRUE' : 'FALSE') . "\n";
    
    // Test verifikasi hash vs hash (yang terjadi di sistem)
    $verify_hash_vs_hash = password_verify($hashed_once, $hashed_twice);
    echo "   Verify hashed_once vs hashed_twice: " . ($verify_hash_vs_hash ? 'TRUE' : 'FALSE') . "\n\n";
    
    echo "3. Analisis Masalah\n";
    if (!$verify_twice) {
        echo "   ❌ MASALAH DITEMUKAN: Double hashing menyebabkan verifikasi gagal\n";
        echo "   📝 Penyebab: epic_update_user() melakukan hashing lagi pada password yang sudah di-hash\n";
        echo "   🔧 Solusi: Modifikasi epic_reset_password() untuk bypass hashing di epic_update_user()\n\n";
    }
    
    echo "4. Test dengan User Real\n";
    
    // Ambil user pertama untuk testing
    $user = db()->selectOne("SELECT id, email, password FROM epic_users ORDER BY id ASC LIMIT 1");
    
    if ($user) {
        echo "   User ID: {$user['id']}\n";
        echo "   Email: {$user['email']}\n";
        echo "   Current password hash: " . substr($user['password'], 0, 30) . "...\n";
        
        // Backup password lama
        $old_password = $user['password'];
        
        // Test update password dengan epic_update_user (akan double hash)
        echo "\n   Testing epic_update_user with new password...\n";
        
        $new_test_password = 'newtest123';
        $result = epic_update_user($user['id'], ['password' => $new_test_password]);
        
        if ($result) {
            // Ambil password yang baru di-update
            $updated_user = epic_get_user($user['id']);
            echo "   Updated password hash: " . substr($updated_user['password'], 0, 30) . "...\n";
            
            // Test verifikasi
            $verify_result = password_verify($new_test_password, $updated_user['password']);
            echo "   Verification result: " . ($verify_result ? 'SUCCESS' : 'FAILED') . "\n";
            
            if (!$verify_result) {
                echo "   ❌ KONFIRMASI: epic_update_user() menyebabkan double hashing\n";
            }
            
            // Restore password lama
            db()->query("UPDATE epic_users SET password = ? WHERE id = ?", [$old_password, $user['id']]);
            echo "   ✓ Password restored to original\n";
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Masalah: Double hashing di epic_update_user() menyebabkan reset password gagal\n";
    echo "Solusi: Modifikasi epic_reset_password() untuk update password langsung tanpa melalui epic_update_user()\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>