<?php
/**
 * Fix Referral Mapping untuk User contact.bustanul@gmail.com
 * Memperbaiki mapping kode ADMIN001 ke Admin Official
 */

// Include required files
if (!defined('EPIC_LOADED')) define('EPIC_LOADED', true);
require_once 'config/config.php';
require_once 'bootstrap.php';

echo "<h1>Fix Referral Mapping - ADMIN001 ke Admin Official</h1>";

try {
    // 1. Cek user target
    echo "<h3>1. Cek User Target</h3>";
    $target_user = db()->selectOne(
        "SELECT * FROM " . db()->table('users') . " WHERE email = ?",
        ['contact.bustanul@gmail.com']
    );
    
    if (!$target_user) {
        echo "<strong style='color: red;'>❌ User contact.bustanul@gmail.com tidak ditemukan!</strong><br>";
        exit;
    }
    
    echo "<strong>✅ User ditemukan:</strong><br>";
    echo "- ID: {$target_user['id']}<br>";
    echo "- Name: {$target_user['name']}<br>";
    echo "- Email: {$target_user['email']}<br>";
    echo "- Current sponsor_id: " . ($target_user['sponsor_id'] ?? 'NULL') . "<br>";
    echo "- Current epis_supervisor_id: " . ($target_user['epis_supervisor_id'] ?? 'NULL') . "<br>";
    
    // 2. Cek sponsor dengan kode ADMIN001
    echo "<h3>2. Cek Sponsor ADMIN001</h3>";
    $admin_sponsor = db()->selectOne(
        "SELECT * FROM " . db()->table('users') . " WHERE referral_code = ?",
        ['ADMIN001']
    );
    
    if (!$admin_sponsor) {
        echo "<strong style='color: red;'>❌ Sponsor dengan kode ADMIN001 tidak ditemukan!</strong><br>";
        
        // Cari admin dengan nama yang mengandung "Admin" atau "Official"
        $admin_users = db()->select(
            "SELECT * FROM " . db()->table('users') . " WHERE (name LIKE '%Admin%' OR name LIKE '%Official%' OR role IN ('admin', 'super_admin')) AND referral_code IS NOT NULL"
        );
        
        if (!empty($admin_users)) {
            echo "<strong>Admin users yang tersedia:</strong><br>";
            foreach ($admin_users as $admin) {
                echo "- ID: {$admin['id']}, Name: {$admin['name']}, Code: {$admin['referral_code']}, Role: {$admin['role']}<br>";
            }
            
            // Gunakan admin pertama atau buat kode ADMIN001
            $selected_admin = $admin_users[0];
            
            // Update referral code menjadi ADMIN001 jika belum ada
            if ($selected_admin['referral_code'] !== 'ADMIN001') {
                echo "<br><strong>🔧 Mengupdate referral code admin menjadi ADMIN001...</strong><br>";
                $update_result = db()->update(
                    db()->table('users'),
                    ['referral_code' => 'ADMIN001'],
                    'id = ?',
                    [$selected_admin['id']]
                );
                
                if ($update_result) {
                    echo "✅ Referral code berhasil diupdate ke ADMIN001<br>";
                    $admin_sponsor = db()->selectOne(
                        "SELECT * FROM " . db()->table('users') . " WHERE id = ?",
                        [$selected_admin['id']]
                    );
                } else {
                    echo "❌ Gagal mengupdate referral code<br>";
                    exit;
                }
            }
        } else {
            echo "<strong style='color: red;'>❌ Tidak ada admin user yang ditemukan!</strong><br>";
            exit;
        }
    }
    
    if ($admin_sponsor) {
        echo "<strong>✅ Sponsor ADMIN001 ditemukan:</strong><br>";
        echo "- ID: {$admin_sponsor['id']}<br>";
        echo "- Name: {$admin_sponsor['name']}<br>";
        echo "- Email: {$admin_sponsor['email']}<br>";
        echo "- Referral Code: {$admin_sponsor['referral_code']}<br>";
        echo "- Role: {$admin_sponsor['role']}<br>";
    }
    
    // 3. Update sponsor_id pada user target
    echo "<h3>3. Update Sponsor ID</h3>";
    if ($target_user['sponsor_id'] != $admin_sponsor['id']) {
        echo "<strong>🔧 Mengupdate sponsor_id user...</strong><br>";
        $update_sponsor = db()->update(
            db()->table('users'),
            ['sponsor_id' => $admin_sponsor['id']],
            'id = ?',
            [$target_user['id']]
        );
        
        if ($update_sponsor) {
            echo "✅ sponsor_id berhasil diupdate ke {$admin_sponsor['id']}<br>";
        } else {
            echo "❌ Gagal mengupdate sponsor_id<br>";
        }
    } else {
        echo "✅ sponsor_id sudah benar: {$admin_sponsor['id']}<br>";
    }
    
    // 4. Cek/buat record di tabel sponsors
    echo "<h3>4. Cek/Buat Record di Tabel Sponsors</h3>";
    try {
        $sponsor_record = db()->selectOne(
            "SELECT * FROM " . db()->table('sponsors') . " WHERE user_id = ?",
            [$target_user['id']]
        );
        
        if (!$sponsor_record) {
            echo "<strong>🔧 Membuat record baru di tabel sponsors...</strong><br>";
            
            $sponsor_data = [
                'user_id' => $target_user['id'],
                'sponsor_id' => $admin_sponsor['id'],
                'sponsor_code' => $admin_sponsor['referral_code'],
                'level' => 1,
                'network_path' => (string)$target_user['id'],
                'commission_rate' => 10.00,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $insert_result = db()->insert(db()->table('sponsors'), $sponsor_data);
            
            if ($insert_result) {
                echo "✅ Record sponsors berhasil dibuat<br>";
            } else {
                echo "❌ Gagal membuat record sponsors<br>";
            }
        } else {
            echo "<strong>✅ Record sponsors sudah ada:</strong><br>";
            echo "- User ID: {$sponsor_record['user_id']}<br>";
            echo "- Sponsor ID: {$sponsor_record['sponsor_id']}<br>";
            echo "- Sponsor Code: {$sponsor_record['sponsor_code']}<br>";
            
            // Update jika sponsor_id berbeda
            if ($sponsor_record['sponsor_id'] != $admin_sponsor['id']) {
                echo "<strong>🔧 Mengupdate record sponsors...</strong><br>";
                $update_sponsor_record = db()->update(
                    db()->table('sponsors'),
                    [
                        'sponsor_id' => $admin_sponsor['id'],
                        'sponsor_code' => $admin_sponsor['referral_code'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    'user_id = ?',
                    [$target_user['id']]
                );
                
                if ($update_sponsor_record) {
                    echo "✅ Record sponsors berhasil diupdate<br>";
                } else {
                    echo "❌ Gagal mengupdate record sponsors<br>";
                }
            }
        }
    } catch (Exception $e) {
        echo "<strong style='color: orange;'>⚠️ Tabel sponsors tidak ada atau error: " . $e->getMessage() . "</strong><br>";
        echo "Sistem akan menggunakan sponsor_id di tabel users saja.<br>";
    }
    
    // 5. Test query yang digunakan di member.php
    echo "<h3>5. Test Query Member List</h3>";
    $test_member = db()->selectOne(
        "SELECT u.*, 
                 supervisor.name as supervisor_name,
                 supervisor.referral_code as supervisor_code,
                 sponsor.name as sponsor_name,
                 sponsor.referral_code as sponsor_code
          FROM " . db()->table('users') . " u
          LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
          LEFT JOIN " . db()->table('sponsors') . " es ON u.id = es.user_id
          LEFT JOIN " . db()->table('users') . " sponsor ON es.sponsor_id = sponsor.id
          WHERE u.email = ?",
        ['contact.bustanul@gmail.com']
    );
    
    if ($test_member) {
        echo "<strong>✅ Test query berhasil:</strong><br>";
        echo "- User: {$test_member['name']}<br>";
        echo "- Sponsor Name: " . ($test_member['sponsor_name'] ?? 'NULL') . "<br>";
        echo "- Sponsor Code: " . ($test_member['sponsor_code'] ?? 'NULL') . "<br>";
        echo "- Supervisor Name: " . ($test_member['supervisor_name'] ?? 'NULL') . "<br>";
        
        if (empty($test_member['sponsor_name'])) {
            echo "<strong style='color: orange;'>⚠️ sponsor_name masih NULL!</strong><br>";
            
            // Coba query alternatif langsung dari users table
            echo "<strong>🔧 Mencoba query alternatif...</strong><br>";
            $alt_query = db()->selectOne(
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
            
            if ($alt_query && !empty($alt_query['sponsor_name'])) {
                echo "<strong>✅ Query alternatif berhasil:</strong><br>";
                echo "- Sponsor Name: {$alt_query['sponsor_name']}<br>";
                echo "- Sponsor Code: {$alt_query['sponsor_code']}<br>";
                
                echo "<br><strong>🔧 Perlu update query di member.php untuk menggunakan sponsor_id langsung!</strong><br>";
            }
        } else {
            echo "<strong style='color: green;'>✅ Sponsor berhasil ditampilkan!</strong><br>";
        }
    } else {
        echo "<strong style='color: red;'>❌ Test query gagal!</strong><br>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Proses Selesai</h3>";
    echo "<p><strong>Langkah selanjutnya:</strong></p>";
    echo "<ul>";
    echo "<li>Buka halaman admin member list untuk melihat hasilnya</li>";
    echo "<li>Jika masih belum muncul, perlu update query di member.php</li>";
    echo "<li>Pastikan user contact.bustanul@gmail.com sudah memiliki sponsor 'Admin Official'</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>❌ Error: " . $e->getMessage() . "</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>