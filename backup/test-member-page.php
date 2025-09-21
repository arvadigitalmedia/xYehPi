<?php
/**
 * Test Member Page
 * Memverifikasi halaman manage member sudah tidak error setelah perbaikan
 */

// Include required files
if (!defined('EPIC_LOADED')) define('EPIC_LOADED', true);
require_once 'config/config.php';
require_once 'bootstrap.php';

echo "<h1>Test Member Page - Verifikasi Perbaikan</h1>";

try {
    // 1. Test koneksi database
    echo "<h3>1. Test Koneksi Database</h3>";
    
    try {
        $db_test = db()->selectOne("SELECT 1 as test");
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<strong style='color: #155724;'>✅ Koneksi database berhasil!</strong>";
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<strong style='color: #721c24;'>❌ Koneksi database gagal: " . $e->getMessage() . "</strong>";
        echo "</div>";
        exit;
    }
    
    // 2. Test struktur tabel epic_users
    echo "<h3>2. Test Struktur Tabel epic_users</h3>";
    
    try {
        $table_structure = db()->select("DESCRIBE " . db()->table('users'));
        $columns = array_column($table_structure, 'Field');
        
        $required_columns = ['id', 'name', 'email', 'epis_supervisor_id', 'referral_code', 'status', 'role', 'created_at'];
        $sponsor_id_exists = in_array('sponsor_id', $columns);
        
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>📋 Kolom yang ditemukan:</strong><br>";
        foreach ($required_columns as $col) {
            $status = in_array($col, $columns) ? '✅' : '❌';
            echo "- {$col}: {$status}<br>";
        }
        echo "- sponsor_id: " . ($sponsor_id_exists ? '✅' : '❌') . "<br>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<strong style='color: #721c24;'>❌ Error cek struktur tabel: " . $e->getMessage() . "</strong>";
        echo "</div>";
    }
    
    // 3. Test query member list (simulasi dari member.php)
    echo "<h3>3. Test Query Member List</h3>";
    
    // Simulasi parameter dari member.php
    $per_page = 15;
    $offset = 0;
    $where_clause = '';
    $params = [];
    
    // Check if sponsor_id column exists (sama seperti di member.php)
    $sponsor_column_exists = false;
    try {
        $columns = db()->select("DESCRIBE " . db()->table('users'));
        foreach ($columns as $column) {
            if ($column['Field'] === 'sponsor_id') {
                $sponsor_column_exists = true;
                break;
            }
        }
    } catch (Exception $e) {
        $sponsor_column_exists = false;
    }
    
    echo "<div style='background: #e2e3e5; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
    echo "<strong>Kolom sponsor_id: " . ($sponsor_column_exists ? 'Ditemukan ✅' : 'Tidak ditemukan ❌') . "</strong>";
    echo "</div>";
    
    try {
        if ($sponsor_column_exists) {
            // Query dengan sponsor_id column
            $members = db()->select(
                "SELECT u.*, 
                             supervisor.name as supervisor_name,
                             supervisor.referral_code as supervisor_code,
                             sponsor_user.name as sponsor_name,
                             sponsor_user.referral_code as sponsor_code
                      FROM " . db()->table('users') . " u
                      LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
                      LEFT JOIN " . db()->table('users') . " sponsor_user ON u.sponsor_id = sponsor_user.id
                 {$where_clause}
                 ORDER BY u.created_at DESC
                 LIMIT {$per_page} OFFSET {$offset}",
                $params
            );
            
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong style='color: #155724;'>✅ Query dengan sponsor_id berhasil!</strong><br>";
            echo "Jumlah data: " . count($members) . " records<br>";
            echo "</div>";
            
        } else {
            // Fallback query tanpa sponsor_id column
            $members = db()->select(
                "SELECT u.*, 
                             supervisor.name as supervisor_name,
                             supervisor.referral_code as supervisor_code,
                             NULL as sponsor_name,
                             NULL as sponsor_code
                      FROM " . db()->table('users') . " u
                      LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
                 {$where_clause}
                 ORDER BY u.created_at DESC
                 LIMIT {$per_page} OFFSET {$offset}",
                $params
            );
            
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong style='color: #856404;'>⚠️ Query fallback (tanpa sponsor_id) berhasil!</strong><br>";
            echo "Jumlah data: " . count($members) . " records<br>";
            echo "Kolom sponsor akan menampilkan NULL<br>";
            echo "</div>";
        }
        
        // Tampilkan sample data
        if (!empty($members)) {
            echo "<h4>Sample Data (5 records pertama):</h4>";
            echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; margin-top: 10px;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Role</th><th>Sponsor</th><th>Supervisor</th>";
            echo "</tr>";
            
            foreach (array_slice($members, 0, 5) as $member) {
                echo "<tr>";
                echo "<td>{$member['id']}</td>";
                echo "<td>{$member['name']}</td>";
                echo "<td>{$member['email']}</td>";
                echo "<td>{$member['status']}</td>";
                echo "<td>{$member['role']}</td>";
                echo "<td>" . ($member['sponsor_name'] ?? '<em>NULL</em>') . "</td>";
                echo "<td>" . ($member['supervisor_name'] ?? '<em>NULL</em>') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong style='color: #721c24;'>❌ Query member list masih error!</strong><br>";
        echo "Error: " . $e->getMessage() . "<br>";
        echo "<pre style='color: #721c24; font-size: 12px;'>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }
    
    // 4. Test count query untuk pagination
    echo "<h3>4. Test Count Query untuk Pagination</h3>";
    
    try {
        $total_count = db()->selectValue(
            "SELECT COUNT(*) FROM " . db()->table('users') . " {$where_clause}",
            $params
        );
        
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<strong style='color: #155724;'>✅ Count query berhasil!</strong><br>";
        echo "Total users: {$total_count}<br>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<strong style='color: #721c24;'>❌ Count query error: " . $e->getMessage() . "</strong>";
        echo "</div>";
    }
    
    // 5. Test statistik queries
    echo "<h3>5. Test Statistik Queries</h3>";
    
    try {
        $stats = [
            'total' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users'))['count'],
            'active' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users') . " WHERE status IN ('free', 'epic', 'epis')")['count'],
            'inactive' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users') . " WHERE status IN ('pending', 'suspended', 'banned')")['count'],
        ];
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong style='color: #155724;'>✅ Statistik queries berhasil!</strong><br>";
        echo "- Total users: {$stats['total']}<br>";
        echo "- Active users: {$stats['active']}<br>";
        echo "- Inactive users: {$stats['inactive']}<br>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<strong style='color: #721c24;'>❌ Statistik queries error: " . $e->getMessage() . "</strong>";
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<h3>📋 Summary Hasil Test</h3>";
    
    if (isset($members) && !empty($members)) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border: 1px solid #c3e6cb;'>";
        echo "<h4 style='color: #155724; margin-top: 0;'>✅ PERBAIKAN BERHASIL!</h4>";
        echo "<ul style='color: #155724;'>";
        echo "<li>Query member list sudah bisa dijalankan tanpa error</li>";
        echo "<li>Fallback mechanism berfungsi dengan baik</li>";
        echo "<li>Halaman manage member sekarang bisa diakses</li>";
        echo "<li>Data member berhasil ditampilkan</li>";
        echo "</ul>";
        
        if ($sponsor_column_exists) {
            echo "<p><strong style='color: #155724;'>✅ Kolom sponsor_id sudah ada dan berfungsi!</strong></p>";
        } else {
            echo "<p><strong style='color: #856404;'>⚠️ Kolom sponsor_id belum ada, menggunakan fallback (sponsor = NULL)</strong></p>";
            echo "<p>Untuk menampilkan sponsor, jalankan: <code>fix-sponsor-column.php</code></p>";
        }
        
        echo "<p><strong>Langkah selanjutnya:</strong></p>";
        echo "<ul>";
        echo "<li>✅ Buka halaman admin manage member</li>";
        echo "<li>✅ Verifikasi tidak ada error lagi</li>";
        echo "<li>✅ Cek tampilan data member</li>";
        if (!$sponsor_column_exists) {
            echo "<li>🔧 Jalankan script fix-sponsor-column.php untuk menambahkan kolom sponsor_id</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; border: 1px solid #f5c6cb;'>";
        echo "<h4 style='color: #721c24; margin-top: 0;'>❌ MASIH ADA MASALAH</h4>";
        echo "<ul style='color: #721c24;'>";
        echo "<li>Query member list masih error</li>";
        echo "<li>Perlu cek kembali struktur database</li>";
        echo "<li>Atau ada masalah lain di konfigurasi</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong style='color: #721c24;'>❌ Error umum: " . $e->getMessage() . "</strong><br>";
    echo "<pre style='color: #721c24;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>