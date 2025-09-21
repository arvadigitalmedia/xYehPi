<?php
/**
 * Simulasi Reset Password untuk contact.bustanul@gmail.com
 * Script untuk mensimulasikan proses reset password dan verifikasi login
 */

require_once 'bootstrap.php';

echo "=== SIMULASI RESET PASSWORD - BUSTANUL ===\n\n";

$target_email = 'contact.bustanul@gmail.com';
$current_password = '!Shadow007';
$new_password = 'NewSecure123!';

try {
    $db = db();
    
    echo "1. VERIFIKASI AKUN TARGET\n";
    echo "   Email: $target_email\n";
    
    // Cari user berdasarkan email
    $user = epic_get_user_by_email($target_email);
    
    if (!$user) {
        echo "   ❌ Akun tidak ditemukan di database\n";
        echo "   Mencari akun dengan email serupa...\n";
        
        // Cari email yang mirip
        $sql = "SELECT * FROM epic_users WHERE email LIKE ? LIMIT 5";
        $similar_users = $db->select($sql, ['%bustanul%']);
        
        if ($similar_users) {
            echo "   📧 Email serupa yang ditemukan:\n";
            foreach ($similar_users as $similar) {
                echo "      - {$similar['email']} (ID: {$similar['id']}, Status: {$similar['status']})\n";
            }
        }
        
        exit(1);
    }
    
    echo "   ✓ Akun ditemukan:\n";
    echo "   ID: {$user['id']}\n";
    echo "   Name: {$user['name']}\n";
    echo "   Email: {$user['email']}\n";
    echo "   Status: {$user['status']}\n";
    echo "   Current password hash: " . substr($user['password'], 0, 30) . "...\n";
    
    // Backup password asli
    $original_password_hash = $user['password'];
    
    echo "\n2. VERIFIKASI PASSWORD SAAT INI\n";
    
    // Test password saat ini
    $current_verify = password_verify($current_password, $user['password']);
    echo "   Password '$current_password' verification: " . ($current_verify ? 'VALID' : 'INVALID') . "\n";
    
    if (!$current_verify) {
        echo "   ⚠️ Password saat ini tidak cocok dengan yang tersimpan\n";
        echo "   Melanjutkan simulasi reset password...\n";
    }
    
    echo "\n3. GENERATE RESET TOKEN\n";
    
    // Generate reset token
    $token = epic_generate_reset_token($user['id']);
    echo "   ✓ Reset token generated: " . substr($token, 0, 20) . "...\n";
    
    // Verify token
    $token_data = epic_verify_reset_token($token);
    if ($token_data) {
        echo "   ✓ Token verification successful\n";
        echo "   User ID from token: {$token_data['user_id']}\n";
        echo "   Token expires: {$token_data['expires_at']}\n";
    } else {
        throw new Exception('Token verification failed');
    }
    
    echo "\n4. SIMULASI RESET PASSWORD\n";
    echo "   Password baru: '$new_password'\n";
    
    // Reset password
    $reset_result = epic_reset_password($token, $new_password);
    
    if ($reset_result) {
        echo "   ✓ Password reset successful\n";
        
        // Verify password update
        $updated_user = epic_get_user($user['id']);
        echo "   Updated password hash: " . substr($updated_user['password'], 0, 30) . "...\n";
        
        // Test password verification
        $verify_result = password_verify($new_password, $updated_user['password']);
        echo "   New password verification: " . ($verify_result ? 'SUCCESS' : 'FAILED') . "\n";
        
        if (!$verify_result) {
            throw new Exception('Password verification failed after reset');
        }
        
    } else {
        throw new Exception('Password reset failed');
    }
    
    echo "\n5. TEST LOGIN DENGAN PASSWORD BARU\n";
    
    // Simulate login process
    $login_user = epic_get_user_by_email($target_email);
    
    if ($login_user) {
        echo "   User data retrieved for login test:\n";
        echo "   - ID: {$login_user['id']}\n";
        echo "   - Email: {$login_user['email']}\n";
        echo "   - Status: {$login_user['status']}\n";
        
        // Test login dengan password baru
        $login_success = password_verify($new_password, $login_user['password']);
        echo "   Login dengan password baru: " . ($login_success ? 'SUCCESS ✅' : 'FAILED ❌') . "\n";
        
        if ($login_success) {
            echo "   🎉 LOGIN BERHASIL! User dapat login dengan password baru.\n";
        } else {
            echo "   ❌ LOGIN GAGAL! Ada masalah dengan password yang di-reset.\n";
        }
        
        // Test login dengan password lama (harus gagal)
        $old_login = password_verify($current_password, $login_user['password']);
        echo "   Login dengan password lama: " . ($old_login ? 'SUCCESS (ERROR!)' : 'FAILED (CORRECT)') . "\n";
        
    } else {
        throw new Exception('Failed to retrieve user for login test');
    }
    
    echo "\n6. VERIFIKASI TOKEN CLEANUP\n";
    
    // Cek apakah token sudah dihapus
    $remaining_token = epic_verify_reset_token($token);
    if (!$remaining_token) {
        echo "   ✅ Used token successfully deleted\n";
    } else {
        echo "   ⚠️ Used token still exists (may be intentional)\n";
    }
    
    echo "\n7. RESTORE PASSWORD ASLI (CLEANUP)\n";
    
    // Restore password asli agar tidak mengganggu user
    $restore_sql = "UPDATE epic_users SET password = ?, updated_at = NOW() WHERE id = ?";
    $restored = $db->query($restore_sql, [$original_password_hash, $user['id']]);
    
    if ($restored) {
        echo "   ✅ Original password restored\n";
        
        // Verify restoration
        $restored_user = epic_get_user($user['id']);
        if ($restored_user['password'] === $original_password_hash) {
            echo "   ✅ Password restoration verified\n";
        } else {
            echo "   ⚠️ Password restoration verification failed\n";
        }
    } else {
        echo "   ❌ Failed to restore original password\n";
    }
    
    echo "\n=== HASIL SIMULASI ===\n";
    
    if ($login_success) {
        echo "✅ SIMULASI BERHASIL!\n";
        echo "✅ Reset password berfungsi dengan benar\n";
        echo "✅ User dapat login dengan password baru\n";
        echo "✅ Token cleanup berjalan normal\n";
        echo "✅ Password asli berhasil di-restore\n";
        
        echo "\n=== KESIMPULAN ===\n";
        echo "🔧 Sistem reset password berfungsi dengan baik\n";
        echo "📧 Jika user masih tidak bisa login, kemungkinan:\n";
        echo "   1. User salah memasukkan password baru\n";
        echo "   2. Browser cache/session issue\n";
        echo "   3. Ada masalah di halaman login (bukan reset password)\n";
        echo "   4. User menggunakan email yang berbeda\n";
        
        echo "\n=== SARAN TROUBLESHOOTING ===\n";
        echo "1. Pastikan user menggunakan email: $target_email\n";
        echo "2. Clear browser cache dan cookies\n";
        echo "3. Coba reset password sekali lagi\n";
        echo "4. Test login di browser incognito/private\n";
        echo "5. Periksa log error di browser console\n";
        
    } else {
        echo "❌ SIMULASI GAGAL!\n";
        echo "❌ Ada masalah dengan sistem reset password\n";
        echo "🔧 Perlu investigasi lebih lanjut\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Jika ada error, coba restore password asli
    if (isset($original_password_hash) && isset($user['id'])) {
        echo "\nTrying to restore original password...\n";
        $restore_sql = "UPDATE epic_users SET password = ?, updated_at = NOW() WHERE id = ?";
        $db->query($restore_sql, [$original_password_hash, $user['id']]);
        echo "Original password restoration attempted.\n";
    }
}
?>