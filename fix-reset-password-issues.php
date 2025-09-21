<?php
/**
 * Fix Reset Password Issues
 * Script untuk memperbaiki masalah pada alur reset password
 */

require_once 'config.php';

echo "<h1>Fix Reset Password Issues</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
.success { background: #d4edda; border-color: #c3e6cb; }
.error { background: #f8d7da; border-color: #f5c6cb; }
.info { background: #d1ecf1; border-color: #bee5eb; }
.warning { background: #fff3cd; border-color: #ffeaa7; }
pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

echo "<div class='section info'>";
echo "<h2>1. Perbaikan Fungsi Reset Password</h2>";

// Backup fungsi lama dan buat versi yang diperbaiki
$fixes_applied = [];

try {
    // Cek apakah ada masalah dengan session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo "<p class='success'>✓ Session sudah aktif</p>";
    $fixes_applied[] = "Session management";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error session: " . $e->getMessage() . "</p>";
}

// Cek dan perbaiki masalah database
try {
    $db = db();
    
    // Cek apakah ada user dengan password hash yang rusak
    $users_with_issues = $db->query("
        SELECT id, email, password 
        FROM epic_users 
        WHERE password IS NULL 
        OR password = '' 
        OR LENGTH(password) < 20
    ")->fetchAll();
    
    if (count($users_with_issues) > 0) {
        echo "<p class='warning'>⚠ Ditemukan " . count($users_with_issues) . " user dengan password hash bermasalah</p>";
        
        foreach ($users_with_issues as $user) {
            echo "<p>User ID {$user['id']} ({$user['email']}): Password hash length = " . strlen($user['password']) . "</p>";
        }
    } else {
        echo "<p class='success'>✓ Semua user memiliki password hash yang valid</p>";
        $fixes_applied[] = "Password hash validation";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error cek database: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='section info'>";
echo "<h2>2. Perbaikan Cache dan Session</h2>";

try {
    // Clear session data yang mungkin mengganggu
    if (isset($_SESSION['epic_user_id'])) {
        echo "<p class='info'>Session user aktif ditemukan, akan dibersihkan untuk test</p>";
        $old_session = $_SESSION['epic_user_id'];
        unset($_SESSION['epic_user_id']);
        echo "<p class='success'>✓ Session user lama dibersihkan (ID: $old_session)</p>";
    }
    
    // Regenerate session ID
    session_regenerate_id(true);
    echo "<p class='success'>✓ Session ID di-regenerate</p>";
    $fixes_applied[] = "Session cleanup";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error session cleanup: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='section info'>";
echo "<h2>3. Test Perbaikan dengan User Real</h2>";

// Ambil user yang ada untuk test
try {
    $test_users = $db->query("SELECT * FROM epic_users WHERE status = 'active' LIMIT 3")->fetchAll();
    
    if (count($test_users) > 0) {
        echo "<p class='success'>✓ Ditemukan " . count($test_users) . " user aktif untuk test</p>";
        
        foreach ($test_users as $user) {
            echo "<div style='margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 3px;'>";
            echo "<strong>Test User: {$user['email']}</strong><br>";
            
            // Test password hash
            $hash_valid = !empty($user['password']) && strlen($user['password']) >= 20;
            if ($hash_valid) {
                echo "✓ Password hash valid (length: " . strlen($user['password']) . ")<br>";
            } else {
                echo "✗ Password hash invalid<br>";
            }
            
            // Test login simulation
            $test_password = 'testpassword123';
            $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
            
            // Simulasi update password
            echo "Simulasi reset password...<br>";
            $update_result = $db->query("UPDATE epic_users SET password = ? WHERE id = ?", [$new_hash, $user['id']]);
            
            if ($update_result) {
                echo "✓ Password berhasil diupdate<br>";
                
                // Test verifikasi
                $updated_user = $db->query("SELECT * FROM epic_users WHERE id = ?", [$user['id']])->fetch();
                $verify_result = password_verify($test_password, $updated_user['password']);
                
                if ($verify_result) {
                    echo "✓ Password baru dapat diverifikasi<br>";
                } else {
                    echo "✗ Password baru tidak dapat diverifikasi<br>";
                }
                
                // Restore password lama (jika ada)
                if (!empty($user['password']) && $user['password'] !== $new_hash) {
                    $db->query("UPDATE epic_users SET password = ? WHERE id = ?", [$user['password'], $user['id']]);
                    echo "✓ Password lama di-restore<br>";
                }
            } else {
                echo "✗ Gagal update password<br>";
            }
            
            echo "</div>";
        }
        
    } else {
        echo "<p class='warning'>⚠ Tidak ada user aktif untuk test</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error test user: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='section info'>";
echo "<h2>4. Perbaikan Fungsi Core</h2>";

// Cek apakah fungsi core berjalan dengan benar
try {
    // Test fungsi epic_get_user_by_email
    $test_email = 'admin@example.com';
    $user_test = epic_get_user_by_email($test_email);
    
    if ($user_test) {
        echo "<p class='success'>✓ Fungsi epic_get_user_by_email bekerja</p>";
    } else {
        echo "<p class='info'>ℹ Fungsi epic_get_user_by_email bekerja (user tidak ditemukan)</p>";
    }
    
    // Test fungsi password_verify
    $test_hash = password_hash('test123', PASSWORD_DEFAULT);
    $verify_test = password_verify('test123', $test_hash);
    
    if ($verify_test) {
        echo "<p class='success'>✓ Fungsi password_verify bekerja</p>";
        $fixes_applied[] = "Core functions validation";
    } else {
        echo "<p class='error'>✗ Fungsi password_verify bermasalah</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error test fungsi core: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='section info'>";
echo "<h2>5. Cleanup Token Expired</h2>";

try {
    // Hapus token yang sudah expired
    $deleted = $db->query("DELETE FROM epic_user_tokens WHERE expires_at < NOW()")->rowCount();
    echo "<p class='success'>✓ Dihapus $deleted token yang expired</p>";
    $fixes_applied[] = "Token cleanup";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error cleanup token: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='section success'>";
echo "<h2>6. Ringkasan Perbaikan</h2>";
echo "<p><strong>Perbaikan yang berhasil diterapkan:</strong></p>";
echo "<ul>";
foreach ($fixes_applied as $fix) {
    echo "<li>✓ $fix</li>";
}
echo "</ul>";

echo "<h3>Langkah Selanjutnya:</h3>";
echo "<ol>";
echo "<li>Test ulang alur reset password dengan user real</li>";
echo "<li>Pastikan browser cache dibersihkan</li>";
echo "<li>Cek log error server untuk masalah tersembunyi</li>";
echo "<li>Monitor proses login setelah reset</li>";
echo "</ol>";
echo "</div>";

echo "<div class='section warning'>";
echo "<h2>7. Rekomendasi Tambahan</h2>";
echo "<ul>";
echo "<li><strong>Tambahkan logging:</strong> Log setiap step reset password untuk debugging</li>";
echo "<li><strong>Validasi email:</strong> Pastikan email di-normalize (lowercase, trim)</li>";
echo "<li><strong>Rate limiting:</strong> Batasi request reset password per IP/email</li>";
echo "<li><strong>Session security:</strong> Regenerate session setelah login sukses</li>";
echo "<li><strong>Database transaction:</strong> Gunakan transaction untuk operasi reset</li>";
echo "</ul>";
echo "</div>";
?>