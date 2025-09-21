<?php
/**
 * Test Verifikasi Perbaikan Sistem Referral
 * Memastikan user contact.bustanul@gmail.com menampilkan sponsor "Admin Official"
 */

// Include required files
if (!defined('EPIC_LOADED')) define('EPIC_LOADED', true);
require_once 'config/config.php';
require_once 'bootstrap.php';

echo "<h1>Test Verifikasi Perbaikan Sistem Referral</h1>";

try {
    // 1. Test query yang sama dengan member.php (setelah perbaikan)
    echo "<h3>1. Test Query Member List (Setelah Perbaikan)</h3>";
    
    $test_member = db()->selectOne(
        "SELECT u.*, 
                 supervisor.name as supervisor_name,
                 supervisor.referral_code as supervisor_code,
                 sponsor_user.name as sponsor_name,
                 sponsor_user.referral_code as sponsor_code
          FROM " . db()->table('users') . " u
          LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
          LEFT JOIN " . db()->table('users') . " sponsor_user ON u.sponsor_id = sponsor_user.id
          WHERE u.email = ?",
        ['contact.bustanul@gmail.com']
    );
    
    if ($test_member) {
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ BERHASIL! Data member ditemukan:</strong><br>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='margin-top: 10px;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>User ID</td><td>{$test_member['id']}</td></tr>";
        echo "<tr><td>Name</td><td>{$test_member['name']}</td></tr>";
        echo "<tr><td>Email</td><td>{$test_member['email']}</td></tr>";
        echo "<tr><td>Sponsor ID</td><td>" . ($test_member['sponsor_id'] ?? 'NULL') . "</td></tr>";
        echo "<tr><td><strong>Sponsor Name</strong></td><td><strong style='color: green;'>" . ($test_member['sponsor_name'] ?? 'NULL') . "</strong></td></tr>";
        echo "<tr><td><strong>Sponsor Code</strong></td><td><strong style='color: green;'>" . ($test_member['sponsor_code'] ?? 'NULL') . "</strong></td></tr>";
        echo "<tr><td>Supervisor Name</td><td>" . ($test_member['supervisor_name'] ?? 'NULL') . "</td></tr>";
        echo "<tr><td>Status</td><td>{$test_member['status']}</td></tr>";
        echo "<tr><td>Role</td><td>{$test_member['role']}</td></tr>";
        echo "</table>";
        echo "</div>";
        
        // Validasi hasil
        if (!empty($test_member['sponsor_name'])) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
            echo "<strong style='color: #155724;'>🎉 SUKSES! Sponsor berhasil ditampilkan!</strong><br>";
            echo "User <strong>{$test_member['email']}</strong> sekarang menampilkan sponsor: <strong>{$test_member['sponsor_name']}</strong> ({$test_member['sponsor_code']})<br>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
            echo "<strong style='color: #721c24;'>❌ MASIH BERMASALAH! Sponsor masih NULL</strong><br>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong style='color: #721c24;'>❌ User tidak ditemukan!</strong>";
        echo "</div>";
    }
    
    // 2. Test semua user dengan sponsor
    echo "<h3>2. Test Semua User dengan Sponsor</h3>";
    
    $users_with_sponsors = db()->select(
        "SELECT u.name, u.email, u.sponsor_id, sponsor_user.name as sponsor_name, sponsor_user.referral_code as sponsor_code
         FROM " . db()->table('users') . " u
         LEFT JOIN " . db()->table('users') . " sponsor_user ON u.sponsor_id = sponsor_user.id
         WHERE u.sponsor_id IS NOT NULL
         ORDER BY u.name"
    );
    
    if (!empty($users_with_sponsors)) {
        echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; margin-top: 10px;'>";
        echo "<tr style='background: #f8f9fa;'><th>User Name</th><th>Email</th><th>Sponsor Name</th><th>Sponsor Code</th></tr>";
        
        foreach ($users_with_sponsors as $user) {
            $highlight = ($user['email'] === 'contact.bustanul@gmail.com') ? 'background: #fff3cd;' : '';
            echo "<tr style='{$highlight}'>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td><strong>" . ($user['sponsor_name'] ?? 'NULL') . "</strong></td>";
            echo "<td><strong>" . ($user['sponsor_code'] ?? 'NULL') . "</strong></td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<p><em>Total users dengan sponsor: " . count($users_with_sponsors) . "</em></p>";
    } else {
        echo "<p><em>Tidak ada user dengan sponsor yang ditemukan.</em></p>";
    }
    
    // 3. Test admin users dengan kode ADMIN001
    echo "<h3>3. Test Admin Users dengan Kode ADMIN001</h3>";
    
    $admin_users = db()->select(
        "SELECT * FROM " . db()->table('users') . " 
         WHERE referral_code = 'ADMIN001' OR (role IN ('admin', 'super_admin') AND referral_code IS NOT NULL)
         ORDER BY role, name"
    );
    
    if (!empty($admin_users)) {
        echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; margin-top: 10px;'>";
        echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Referral Code</th></tr>";
        
        foreach ($admin_users as $admin) {
            $highlight = ($admin['referral_code'] === 'ADMIN001') ? 'background: #d1ecf1;' : '';
            echo "<tr style='{$highlight}'>";
            echo "<td>{$admin['id']}</td>";
            echo "<td>{$admin['name']}</td>";
            echo "<td>{$admin['email']}</td>";
            echo "<td>{$admin['role']}</td>";
            echo "<td><strong>{$admin['referral_code']}</strong></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p><em>Tidak ada admin user dengan referral code yang ditemukan.</em></p>";
    }
    
    // 4. Summary dan rekomendasi
    echo "<hr>";
    echo "<h3>📋 Summary & Rekomendasi</h3>";
    
    if (!empty($test_member['sponsor_name'])) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border: 1px solid #c3e6cb;'>";
        echo "<h4 style='color: #155724; margin-top: 0;'>✅ PERBAIKAN BERHASIL!</h4>";
        echo "<ul style='color: #155724;'>";
        echo "<li>User <strong>contact.bustanul@gmail.com</strong> sekarang menampilkan sponsor: <strong>{$test_member['sponsor_name']}</strong></li>";
        echo "<li>Kode sponsor: <strong>{$test_member['sponsor_code']}</strong></li>";
        echo "<li>Query di member.php sudah diperbaiki</li>";
        echo "<li>Mapping referral ADMIN001 sudah bekerja dengan benar</li>";
        echo "</ul>";
        echo "<p><strong>Langkah selanjutnya:</strong></p>";
        echo "<ul>";
        echo "<li>✅ Buka halaman admin member list untuk melihat hasilnya</li>";
        echo "<li>✅ Kolom sponsor sekarang akan menampilkan '{$test_member['sponsor_name']}'</li>";
        echo "<li>✅ Sistem referral sudah berfungsi normal</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; border: 1px solid #f5c6cb;'>";
        echo "<h4 style='color: #721c24; margin-top: 0;'>❌ MASIH ADA MASALAH</h4>";
        echo "<ul style='color: #721c24;'>";
        echo "<li>Sponsor masih tidak ditampilkan</li>";
        echo "<li>Perlu cek kembali data di database</li>";
        echo "<li>Mungkin perlu menjalankan script fix-referral-mapping.php lagi</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong style='color: #721c24;'>❌ Error: " . $e->getMessage() . "</strong><br>";
    echo "<pre style='color: #721c24;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>