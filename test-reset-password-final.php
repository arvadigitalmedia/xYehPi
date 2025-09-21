<?php
/**
 * Test Reset Password Final
 * Script untuk memverifikasi perbaikan alur reset password
 */

require_once 'config.php';

echo "<h1>Test Reset Password Final - Verifikasi Perbaikan</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
.success { background: #d4edda; border-color: #c3e6cb; }
.error { background: #f8d7da; border-color: #f5c6cb; }
.info { background: #d1ecf1; border-color: #bee5eb; }
.warning { background: #fff3cd; border-color: #ffeaa7; }
pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
.step { margin: 10px 0; padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff; }
</style>";

// Test dengan user real
$test_email = 'testuser@example.com';
$old_password = 'oldpassword123';
$new_password = 'newpassword456';

echo "<div class='section info'>";
echo "<h2>🔧 Test Scenario: Complete Reset Password Flow</h2>";
echo "<p><strong>Test Email:</strong> $test_email</p>";
echo "<p><strong>Old Password:</strong> $old_password</p>";
echo "<p><strong>New Password:</strong> $new_password</p>";
echo "</div>";

// Step 1: Setup test user
echo "<div class='section'>";
echo "<h3>Step 1: Setup Test User</h3>";

try {
    $db = db();
    
    // Hapus user test jika ada
    $db->query("DELETE FROM epic_user_tokens WHERE user_id IN (SELECT id FROM epic_users WHERE email = ?)", [$test_email]);
    $db->query("DELETE FROM epic_users WHERE email = ?", [$test_email]);
    
    // Buat user baru
    $user_id = epic_create_user([
        'email' => $test_email,
        'password' => password_hash($old_password, PASSWORD_DEFAULT),
        'first_name' => 'Test',
        'last_name' => 'User',
        'status' => 'active'
    ]);
    
    if ($user_id) {
        echo "<div class='step success'>✓ User test berhasil dibuat dengan ID: $user_id</div>";
        
        // Verify user can login with old password
        $user = epic_get_user_by_email($test_email);
        if ($user && epic_verify_password($old_password, $user['password'])) {
            echo "<div class='step success'>✓ User dapat login dengan password lama</div>";
        } else {
            echo "<div class='step error'>✗ User tidak dapat login dengan password lama</div>";
        }
    } else {
        echo "<div class='step error'>✗ Gagal membuat user test</div>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<div class='step error'>✗ Error setup user: " . $e->getMessage() . "</div>";
    exit;
}

echo "</div>";

// Step 2: Generate reset token
echo "<div class='section'>";
echo "<h3>Step 2: Generate Reset Token</h3>";

try {
    $token = epic_generate_reset_token($user_id);
    echo "<div class='step success'>✓ Token reset berhasil dibuat</div>";
    echo "<div class='step info'>Token: " . substr($token, 0, 20) . "...</div>";
    
    // Verify token
    $token_data = epic_verify_reset_token($token);
    if ($token_data) {
        echo "<div class='step success'>✓ Token dapat diverifikasi</div>";
    } else {
        echo "<div class='step error'>✗ Token tidak dapat diverifikasi</div>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<div class='step error'>✗ Error generate token: " . $e->getMessage() . "</div>";
    exit;
}

echo "</div>";

// Step 3: Reset password
echo "<div class='section'>";
echo "<h3>Step 3: Reset Password</h3>";

try {
    // Clear any session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
        if (isset($_SESSION['epic_user_id'])) {
            unset($_SESSION['epic_user_id']);
        }
    }
    
    echo "<div class='step info'>Session dibersihkan</div>";
    
    // Reset password
    $result = epic_reset_password($token, $new_password);
    
    if ($result) {
        echo "<div class='step success'>✓ Password berhasil direset</div>";
        
        // Verify token is deleted
        $token_check = epic_verify_reset_token($token);
        if (!$token_check) {
            echo "<div class='step success'>✓ Token berhasil dihapus setelah reset</div>";
        } else {
            echo "<div class='step warning'>⚠ Token masih ada setelah reset</div>";
        }
        
    } else {
        echo "<div class='step error'>✗ Gagal reset password</div>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<div class='step error'>✗ Error reset password: " . $e->getMessage() . "</div>";
    exit;
}

echo "</div>";

// Step 4: Test login with new password
echo "<div class='section'>";
echo "<h3>Step 4: Test Login dengan Password Baru</h3>";

try {
    // Get fresh user data
    $updated_user = epic_get_user_by_email($test_email);
    
    if (!$updated_user) {
        echo "<div class='step error'>✗ User tidak ditemukan setelah reset</div>";
        exit;
    }
    
    echo "<div class='step info'>User data ditemukan</div>";
    
    // Test old password (should fail)
    $old_login = epic_verify_password($old_password, $updated_user['password']);
    if (!$old_login) {
        echo "<div class='step success'>✓ Password lama tidak bisa digunakan (benar)</div>";
    } else {
        echo "<div class='step error'>✗ Password lama masih bisa digunakan (salah)</div>";
    }
    
    // Test new password (should work)
    $new_login = epic_verify_password($new_password, $updated_user['password']);
    if ($new_login) {
        echo "<div class='step success'>✓ Password baru dapat digunakan untuk login</div>";
    } else {
        echo "<div class='step error'>✗ Password baru tidak dapat digunakan untuk login</div>";
        
        // Debug info
        echo "<div class='step warning'>";
        echo "Debug Info:<br>";
        echo "Password input: $new_password<br>";
        echo "Hash stored: " . substr($updated_user['password'], 0, 30) . "...<br>";
        echo "Hash length: " . strlen($updated_user['password']) . "<br>";
        echo "</div>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<div class='step error'>✗ Error test login: " . $e->getMessage() . "</div>";
    exit;
}

echo "</div>";

// Step 5: Simulasi login lengkap
echo "<div class='section'>";
echo "<h3>Step 5: Simulasi Login Lengkap</h3>";

try {
    // Simulasi seperti di index.php
    $email = strtolower(trim($test_email));
    $password = $new_password;
    
    echo "<div class='step info'>Testing login dengan email: $email</div>";
    
    if (empty($email) || empty($password)) {
        echo "<div class='step error'>✗ Email atau password kosong</div>";
    } else {
        $login_user = epic_get_user_by_email($email);
        
        if (!$login_user) {
            echo "<div class='step error'>✗ User tidak ditemukan</div>";
        } elseif (!epic_verify_password($password, $login_user['password'])) {
            echo "<div class='step error'>✗ Password tidak cocok</div>";
        } else {
            if (strtoupper($login_user['status']) === 'BANNED') {
                echo "<div class='step error'>✗ Akun diblokir</div>";
            } elseif (strtoupper($login_user['status']) === 'PENDING') {
                echo "<div class='step error'>✗ Akun pending</div>";
            } else {
                echo "<div class='step success'>✓ LOGIN BERHASIL!</div>";
                echo "<div class='step success'>✓ Alur reset password berfungsi dengan sempurna</div>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<div class='step error'>✗ Error simulasi login: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Step 6: Cleanup
echo "<div class='section'>";
echo "<h3>Step 6: Cleanup Test Data</h3>";

try {
    $db->query("DELETE FROM epic_user_tokens WHERE user_id = ?", [$user_id]);
    $db->query("DELETE FROM epic_users WHERE id = ?", [$user_id]);
    echo "<div class='step success'>✓ Test data berhasil dibersihkan</div>";
    
} catch (Exception $e) {
    echo "<div class='step warning'>⚠ Error cleanup: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Summary
echo "<div class='section success'>";
echo "<h2>🎉 Ringkasan Test</h2>";
echo "<p><strong>Jika semua step menunjukkan ✓ (berhasil), maka:</strong></p>";
echo "<ul>";
echo "<li>✅ Sistem reset password berfungsi dengan baik</li>";
echo "<li>✅ Password baru tersimpan dengan benar di database</li>";
echo "<li>✅ Proses hashing bekerja sesuai standar keamanan</li>";
echo "<li>✅ Sistem validasi login membaca data terbaru</li>";
echo "<li>✅ Tidak ada masalah cache atau session</li>";
echo "</ul>";

echo "<h3>Perbaikan yang Telah Diterapkan:</h3>";
echo "<ul>";
echo "<li>🔧 Logging lengkap untuk debugging</li>";
echo "<li>🔧 Database transaction untuk konsistensi</li>";
echo "<li>🔧 Session cleanup untuk mencegah konflik</li>";
echo "<li>🔧 Email normalization (lowercase, trim)</li>";
echo "<li>🔧 Password verification setelah update</li>";
echo "<li>🔧 Error handling yang lebih baik</li>";
echo "</ul>";
echo "</div>";

echo "<div class='section info'>";
echo "<h2>📋 Langkah Selanjutnya</h2>";
echo "<ol>";
echo "<li>Test dengan user real di production</li>";
echo "<li>Monitor log error untuk masalah tersembunyi</li>";
echo "<li>Pastikan email reset password terkirim dengan benar</li>";
echo "<li>Test di berbagai browser untuk memastikan kompatibilitas</li>";
echo "</ol>";
echo "</div>";
?>