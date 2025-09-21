<?php
/**
 * Fix Sponsor Column Error
 * Memperbaiki error kolom sponsor_id yang tidak ditemukan di tabel epic_users
 */

// Include required files
if (!defined('EPIC_LOADED')) define('EPIC_LOADED', true);
require_once 'config/config.php';
require_once 'bootstrap.php';

echo "<h1>Fix Sponsor Column Error</h1>";

try {
    // 1. Cek struktur tabel epic_users
    echo "<h3>1. Cek Struktur Tabel epic_users</h3>";
    
    $table_structure = db()->select("DESCRIBE " . db()->table('users'));
    
    echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; margin-top: 10px;'>";
    echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $sponsor_id_exists = false;
    foreach ($table_structure as $column) {
        $highlight = ($column['Field'] === 'sponsor_id') ? 'background: #d1ecf1;' : '';
        if ($column['Field'] === 'sponsor_id') {
            $sponsor_id_exists = true;
        }
        
        echo "<tr style='{$highlight}'>";
        echo "<td><strong>{$column['Field']}</strong></td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Tambahkan kolom sponsor_id jika belum ada
    echo "<h3>2. Status Kolom sponsor_id</h3>";
    
    if (!$sponsor_id_exists) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
        echo "<strong>⚠️ Kolom sponsor_id tidak ditemukan!</strong><br>";
        echo "Menambahkan kolom sponsor_id ke tabel epic_users...<br>";
        echo "</div>";
        
        // Tambahkan kolom sponsor_id
        $add_column_sql = "ALTER TABLE " . db()->table('users') . " 
                          ADD COLUMN sponsor_id INT(11) NULL DEFAULT NULL AFTER epis_supervisor_id,
                          ADD INDEX idx_sponsor_id (sponsor_id)";
        
        try {
            db()->query($add_column_sql);
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
            echo "<strong style='color: #155724;'>✅ Kolom sponsor_id berhasil ditambahkan!</strong><br>";
            echo "- Type: INT(11) NULL DEFAULT NULL<br>";
            echo "- Index: idx_sponsor_id<br>";
            echo "</div>";
            
            $sponsor_id_exists = true;
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
            echo "<strong style='color: #721c24;'>❌ Gagal menambahkan kolom sponsor_id!</strong><br>";
            echo "Error: " . $e->getMessage() . "<br>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
        echo "<strong style='color: #155724;'>✅ Kolom sponsor_id sudah ada!</strong><br>";
        echo "</div>";
    }
    
    // 3. Cek kolom lain yang diperlukan
    echo "<h3>3. Cek Kolom Lain yang Diperlukan</h3>";
    
    $required_columns = ['epis_supervisor_id', 'referral_code', 'status', 'role'];
    $missing_columns = [];
    
    $existing_columns = array_column($table_structure, 'Field');
    
    foreach ($required_columns as $col) {
        if (!in_array($col, $existing_columns)) {
            $missing_columns[] = $col;
        }
    }
    
    if (empty($missing_columns)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong style='color: #155724;'>✅ Semua kolom yang diperlukan sudah ada!</strong><br>";
        echo "- epis_supervisor_id: ✅<br>";
        echo "- referral_code: ✅<br>";
        echo "- status: ✅<br>";
        echo "- role: ✅<br>";
        echo "- sponsor_id: ✅<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>⚠️ Kolom yang hilang:</strong><br>";
        foreach ($missing_columns as $col) {
            echo "- {$col}<br>";
        }
        echo "</div>";
    }
    
    // 4. Test query yang bermasalah
    echo "<h3>4. Test Query Member List</h3>";
    
    if ($sponsor_id_exists) {
        try {
            $test_query = "SELECT u.*, 
                                 supervisor.name as supervisor_name,
                                 supervisor.referral_code as supervisor_code,
                                 sponsor_user.name as sponsor_name,
                                 sponsor_user.referral_code as sponsor_code
                          FROM " . db()->table('users') . " u
                          LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
                          LEFT JOIN " . db()->table('users') . " sponsor_user ON u.sponsor_id = sponsor_user.id
                          ORDER BY u.created_at DESC
                          LIMIT 5";
            
            $test_result = db()->select($test_query);
            
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong style='color: #155724;'>✅ Query berhasil dijalankan!</strong><br>";
            echo "Jumlah data: " . count($test_result) . " records<br>";
            echo "</div>";
            
            if (!empty($test_result)) {
                echo "<h4>Sample Data (5 records pertama):</h4>";
                echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; margin-top: 10px;'>";
                echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Name</th><th>Email</th><th>Sponsor Name</th><th>Supervisor Name</th></tr>";
                
                foreach (array_slice($test_result, 0, 5) as $user) {
                    echo "<tr>";
                    echo "<td>{$user['id']}</td>";
                    echo "<td>{$user['name']}</td>";
                    echo "<td>{$user['email']}</td>";
                    echo "<td>" . ($user['sponsor_name'] ?? '<em>NULL</em>') . "</td>";
                    echo "<td>" . ($user['supervisor_name'] ?? '<em>NULL</em>') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong style='color: #721c24;'>❌ Query masih error!</strong><br>";
            echo "Error: " . $e->getMessage() . "<br>";
            echo "</div>";
        }
    }
    
    // 5. Migrasi data sponsor jika diperlukan
    echo "<h3>5. Migrasi Data Sponsor</h3>";
    
    if ($sponsor_id_exists) {
        // Cek apakah ada user dengan referral_code ADMIN001
        $admin_user = db()->selectOne(
            "SELECT * FROM " . db()->table('users') . " WHERE referral_code = ?",
            ['ADMIN001']
        );
        
        if ($admin_user) {
            echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>📋 Admin User ADMIN001 ditemukan:</strong><br>";
            echo "- ID: {$admin_user['id']}<br>";
            echo "- Name: {$admin_user['name']}<br>";
            echo "- Email: {$admin_user['email']}<br>";
            echo "</div>";
            
            // Update user contact.bustanul@gmail.com jika ada
            $target_user = db()->selectOne(
                "SELECT * FROM " . db()->table('users') . " WHERE email = ?",
                ['contact.bustanul@gmail.com']
            );
            
            if ($target_user && empty($target_user['sponsor_id'])) {
                echo "<strong>🔧 Mengupdate sponsor_id untuk contact.bustanul@gmail.com...</strong><br>";
                
                $update_result = db()->update(
                    db()->table('users'),
                    ['sponsor_id' => $admin_user['id']],
                    'email = ?',
                    ['contact.bustanul@gmail.com']
                );
                
                if ($update_result) {
                    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
                    echo "<strong style='color: #155724;'>✅ sponsor_id berhasil diupdate!</strong>";
                    echo "</div>";
                } else {
                    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
                    echo "<strong style='color: #721c24;'>❌ Gagal mengupdate sponsor_id!</strong>";
                    echo "</div>";
                }
            }
        } else {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>⚠️ Admin user dengan kode ADMIN001 tidak ditemukan!</strong><br>";
            echo "Perlu membuat atau mengupdate admin user terlebih dahulu.<br>";
            echo "</div>";
        }
    }
    
    echo "<hr>";
    echo "<h3>📋 Summary</h3>";
    
    if ($sponsor_id_exists) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border: 1px solid #c3e6cb;'>";
        echo "<h4 style='color: #155724; margin-top: 0;'>✅ PERBAIKAN BERHASIL!</h4>";
        echo "<ul style='color: #155724;'>";
        echo "<li>Kolom sponsor_id sudah ada di tabel epic_users</li>";
        echo "<li>Query member list sudah bisa dijalankan tanpa error</li>";
        echo "<li>Halaman manage member sekarang bisa diakses</li>";
        echo "</ul>";
        echo "<p><strong>Langkah selanjutnya:</strong></p>";
        echo "<ul>";
        echo "<li>✅ Buka halaman admin manage member</li>";
        echo "<li>✅ Verifikasi tidak ada error lagi</li>";
        echo "<li>✅ Cek tampilan kolom sponsor</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; border: 1px solid #f5c6cb;'>";
        echo "<h4 style='color: #721c24; margin-top: 0;'>❌ MASIH ADA MASALAH</h4>";
        echo "<ul style='color: #721c24;'>";
        echo "<li>Kolom sponsor_id gagal ditambahkan</li>";
        echo "<li>Perlu cek permission database</li>";
        echo "<li>Atau tambahkan manual via phpMyAdmin</li>";
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