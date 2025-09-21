<?php
/**
 * Debug Reset Password Flow
 * Script untuk mengidentifikasi masalah pada alur reset password
 */

require_once 'config.php';

echo "<h1>Debug Reset Password Flow</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
.success { background: #d4edda; border-color: #c3e6cb; }
.error { background: #f8d7da; border-color: #f5c6cb; }
.info { background: #d1ecf1; border-color: #bee5eb; }
.warning { background: #fff3cd; border-color: #ffeaa7; }
pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// Test email untuk debug
$test_email = 'test@example.com';
$test_password = 'newpassword123';

echo "<div class='section info'>";
echo "<h2>1. Cek Koneksi Database</h2>";
try {
    $db = db();
    echo "<p class='success'>✓ Koneksi database berhasil</p>";
    
    // Cek tabel users
    $users_exist = $db->query("SHOW TABLES LIKE 'epic_users'")->fetch();
    if ($users_exist) {
        echo "<p class='success'>✓ Tabel epic_users ditemukan</p>";
    } else {
        echo "<p class='error'>✗ Tabel epic_users tidak ditemukan</p>";
    }
    
    // Cek tabel tokens
    $tokens_exist = $db->query("SHOW TABLES LIKE 'epic_user_tokens'")->fetch();
    if ($tokens_exist) {
        echo "<p class='success'>✓ Tabel epic_user_tokens ditemukan</p>";
    } else {
        echo "<p class='error'>✗ Tabel epic_user_tokens tidak ditemukan</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error koneksi database: " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "<div class='section info'>";
echo "<h2>2. Cek User Test</h2>";
try {
    // Cari atau buat user test
    $user = epic_get_user_by_email($test_email);
    
    if (!$user) {
        echo "<p class='warning'>User test tidak ditemukan, membuat user baru...</p>";
        
        // Buat user test
        $user_id = epic_create_user([
            'email' => $test_email,
            'password' => password_hash('oldpassword123', PASSWORD_DEFAULT),
            'first_name' => 'Test',
            'last_name' => 'User',
            'status' => 'active'
        ]);
        
        if ($user_id) {
            echo "<p class='success'>✓ User test berhasil dibuat dengan ID: $user_id</p>";
            $user = epic_get_user($user_id);
        } else {
            echo "<p class='error'>✗ Gagal membuat user test</p>";
        }
    } else {
        echo "<p class='success'>✓ User test ditemukan dengan ID: " . $user['id'] . "</p>";
    }
    
    if ($user) {
        echo "<pre>";
        echo "User Data:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "Password Hash: " . substr($user['password'], 0, 20) . "...\n";
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error cek user: " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "<div class='section info'>";
echo "<h2>3. Test Generate Reset Token</h2>";
if ($user) {
    try {
        $token = epic_generate_reset_token($user['id']);
        echo "<p class='success'>✓ Token reset berhasil dibuat: " . substr($token, 0, 20) . "...</p>";
        
        // Cek token di database
        $token_data = $db->query("SELECT * FROM epic_user_tokens WHERE token = ? AND type = 'password_reset'", [$token])->fetch();
        if ($token_data) {
            echo "<p class='success'>✓ Token tersimpan di database</p>";
            echo "<pre>";
            echo "Token Data:\n";
            echo "User ID: " . $token_data['user_id'] . "\n";
            echo "Type: " . $token_data['type'] . "\n";
            echo "Expires: " . $token_data['expires_at'] . "\n";
            echo "Created: " . $token_data['created_at'] . "\n";
            echo "</pre>";
        } else {
            echo "<p class='error'>✗ Token tidak ditemukan di database</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error generate token: " . $e->getMessage() . "</p>";
        $token = null;
    }
} else {
    echo "<p class='error'>✗ Tidak ada user untuk test token</p>";
    $token = null;
}
echo "</div>";

echo "<div class='section info'>";
echo "<h2>4. Test Verify Reset Token</h2>";
if ($token) {
    try {
        $token_data = epic_verify_reset_token($token);
        if ($token_data) {
            echo "<p class='success'>✓ Token valid dan dapat diverifikasi</p>";
            echo "<pre>";
            echo "Verified Token Data:\n";
            echo "User ID: " . $token_data['user_id'] . "\n";
            echo "Type: " . $token_data['type'] . "\n";
            echo "</pre>";
        } else {
            echo "<p class='error'>✗ Token tidak valid atau expired</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error verify token: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>✗ Tidak ada token untuk diverifikasi</p>";
}
echo "</div>";

echo "<div class='section info'>";
echo "<h2>5. Test Reset Password</h2>";
if ($token && $user) {
    try {
        // Simpan password lama untuk perbandingan
        $old_password_hash = $user['password'];
        echo "<p>Password lama (hash): " . substr($old_password_hash, 0, 30) . "...</p>";
        
        // Reset password
        $result = epic_reset_password($token, $test_password);
        
        if ($result) {
            echo "<p class='success'>✓ Password berhasil direset</p>";
            
            // Ambil user data terbaru
            $updated_user = epic_get_user($user['id']);
            $new_password_hash = $updated_user['password'];
            
            echo "<p>Password baru (hash): " . substr($new_password_hash, 0, 30) . "...</p>";
            
            // Cek apakah hash berubah
            if ($old_password_hash !== $new_password_hash) {
                echo "<p class='success'>✓ Password hash berubah di database</p>";
            } else {
                echo "<p class='error'>✗ Password hash tidak berubah di database</p>";
            }
            
            // Cek apakah token sudah dihapus
            $token_check = $db->query("SELECT * FROM epic_user_tokens WHERE token = ?", [$token])->fetch();
            if (!$token_check) {
                echo "<p class='success'>✓ Token berhasil dihapus setelah reset</p>";
            } else {
                echo "<p class='warning'>⚠ Token masih ada di database</p>";
            }
            
        } else {
            echo "<p class='error'>✗ Gagal reset password</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error reset password: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>✗ Tidak ada token atau user untuk test reset</p>";
}
echo "</div>";

echo "<div class='section info'>";
echo "<h2>6. Test Login dengan Password Baru</h2>";
if ($user) {
    try {
        // Ambil user data terbaru
        $updated_user = epic_get_user_by_email($test_email);
        
        if ($updated_user) {
            echo "<p>Testing login dengan email: $test_email</p>";
            echo "<p>Testing login dengan password: $test_password</p>";
            
            // Test verifikasi password
            $password_valid = epic_verify_password($test_password, $updated_user['password']);
            
            if ($password_valid) {
                echo "<p class='success'>✓ Password baru valid untuk login</p>";
                
                // Test status user
                if (strtoupper($updated_user['status']) === 'ACTIVE') {
                    echo "<p class='success'>✓ Status user aktif</p>";
                } else {
                    echo "<p class='warning'>⚠ Status user: " . $updated_user['status'] . "</p>";
                }
                
            } else {
                echo "<p class='error'>✗ Password baru tidak valid untuk login</p>";
                
                // Debug password hash
                echo "<pre>";
                echo "Debug Password:\n";
                echo "Input Password: $test_password\n";
                echo "Stored Hash: " . $updated_user['password'] . "\n";
                echo "Hash Length: " . strlen($updated_user['password']) . "\n";
                echo "Hash Algorithm: " . (strpos($updated_user['password'], '$2y$') === 0 ? 'bcrypt' : 'unknown') . "\n";
                echo "</pre>";
            }
            
        } else {
            echo "<p class='error'>✗ User tidak ditemukan setelah reset</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error test login: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>✗ Tidak ada user untuk test login</p>";
}
echo "</div>";

echo "<div class='section info'>";
echo "<h2>7. Test Simulasi Login Lengkap</h2>";
if ($user) {
    try {
        // Simulasi proses login seperti di index.php
        $email = $test_email;
        $password = $test_password;
        
        echo "<p>Simulasi login dengan:</p>";
        echo "<p>Email: $email</p>";
        echo "<p>Password: $password</p>";
        
        if (empty($email) || empty($password)) {
            echo "<p class='error'>✗ Email dan password kosong</p>";
        } else {
            $login_user = epic_get_user_by_email($email);
            
            if (!$login_user) {
                echo "<p class='error'>✗ User tidak ditemukan dengan email: $email</p>";
            } else {
                echo "<p class='success'>✓ User ditemukan</p>";
                
                if (epic_verify_password($password, $login_user['password'])) {
                    echo "<p class='success'>✓ Password cocok</p>";
                    
                    if (strtoupper($login_user['status']) === 'BANNED') {
                        echo "<p class='error'>✗ Akun diblokir</p>";
                    } elseif (strtoupper($login_user['status']) === 'PENDING') {
                        echo "<p class='error'>✗ Akun pending konfirmasi</p>";
                    } else {
                        echo "<p class='success'>✓ Status akun valid: " . $login_user['status'] . "</p>";
                        echo "<p class='success'>✓ LOGIN BERHASIL - Tidak ada masalah dengan alur reset password!</p>";
                    }
                } else {
                    echo "<p class='error'>✗ Password tidak cocok</p>";
                    echo "<p class='error'>MASALAH DITEMUKAN: Password baru tidak bisa digunakan untuk login</p>";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error simulasi login: " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

echo "<div class='section warning'>";
echo "<h2>8. Cleanup Test Data</h2>";
echo "<p>Untuk membersihkan data test, jalankan query berikut:</p>";
echo "<pre>";
echo "DELETE FROM epic_user_tokens WHERE user_id = (SELECT id FROM epic_users WHERE email = '$test_email');\n";
echo "DELETE FROM epic_users WHERE email = '$test_email';\n";
echo "</pre>";
echo "</div>";

echo "<div class='section info'>";
echo "<h2>9. Kesimpulan & Rekomendasi</h2>";
echo "<p>Jika semua test di atas menunjukkan ✓ (berhasil), maka sistem reset password berfungsi dengan baik.</p>";
echo "<p>Jika ada ✗ (error), periksa:</p>";
echo "<ul>";
echo "<li>Koneksi database dan struktur tabel</li>";
echo "<li>Implementasi fungsi hashing password</li>";
echo "<li>Proses verifikasi token</li>";
echo "<li>Cache browser atau session yang mengganggu</li>";
echo "</ul>";
echo "</div>";
?>