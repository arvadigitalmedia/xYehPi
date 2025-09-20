<?php
/**
 * EPIC Hub - Verify Zoom Integration Uninstall
 * Script untuk memverifikasi bahwa Zoom Integration sudah terhapus sempurna
 * 
 * @package EPIC Hub
 * @version 1.0.0
 * @author Bustanu
 */

// Security check
$verify_key = $_GET['verify_key'] ?? '';
if ($verify_key !== 'epic_verify_uninstall_2025') {
    die('❌ Akses ditolak. Gunakan URL: ?verify_key=epic_verify_uninstall_2025');
}

session_start();
require_once 'bootstrap.php';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Zoom Uninstall - EPIC Hub</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #28a745; margin-bottom: 20px; }
        h2 { color: #495057; border-bottom: 2px solid #e9ecef; padding-bottom: 10px; }
        .check-item { margin: 15px 0; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; background: #f8f9fa; }
        .check-item.success { border-left-color: #28a745; background: #d4edda; }
        .check-item.warning { border-left-color: #ffc107; background: #fff3cd; }
        .check-item.error { border-left-color: #dc3545; background: #f8d7da; }
        .status { font-weight: bold; }
        .status.success { color: #28a745; }
        .status.warning { color: #856404; }
        .status.error { color: #721c24; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; font-weight: 500; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .summary { background: #e9ecef; padding: 20px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verifikasi Uninstall Zoom Integration</h1>
        
        <?php
        $checks = [];
        $total_checks = 0;
        $passed_checks = 0;
        
        // Check 1: Database tables
        echo "<h2>1. Verifikasi Database</h2>";
        $zoom_tables = ['epic_zoom_events', 'epic_zoom_settings', 'epic_event_registrations'];
        foreach ($zoom_tables as $table) {
            $total_checks++;
            try {
                $result = db()->query("SHOW TABLES LIKE '{$table}'");
                if ($result->rowCount() == 0) {
                    echo "<div class='check-item success'>";
                    echo "<span class='status success'>✅ BERHASIL</span> - Tabel <code>{$table}</code> sudah terhapus";
                    echo "</div>";
                    $passed_checks++;
                    $checks[] = "Tabel {$table}: TERHAPUS ✅";
                } else {
                    echo "<div class='check-item error'>";
                    echo "<span class='status error'>❌ GAGAL</span> - Tabel <code>{$table}</code> masih ada";
                    echo "</div>";
                    $checks[] = "Tabel {$table}: MASIH ADA ❌";
                }
            } catch (Exception $e) {
                echo "<div class='check-item success'>";
                echo "<span class='status success'>✅ BERHASIL</span> - Tabel <code>{$table}</code> tidak ditemukan (sudah terhapus)";
                echo "</div>";
                $passed_checks++;
                $checks[] = "Tabel {$table}: TIDAK DITEMUKAN ✅";
            }
        }
        
        // Check 2: Core files
        echo "<h2>2. Verifikasi File Core</h2>";
        $zoom_files = [
            'core/zoom-integration.php',
            'install-zoom-integration.php',
            'zoom-integration-schema.sql'
        ];
        foreach ($zoom_files as $file) {
            $total_checks++;
            if (!file_exists($file)) {
                echo "<div class='check-item success'>";
                echo "<span class='status success'>✅ BERHASIL</span> - File <code>{$file}</code> sudah terhapus";
                echo "</div>";
                $passed_checks++;
                $checks[] = "File {$file}: TERHAPUS ✅";
            } else {
                echo "<div class='check-item error'>";
                echo "<span class='status error'>❌ GAGAL</span> - File <code>{$file}</code> masih ada";
                echo "</div>";
                $checks[] = "File {$file}: MASIH ADA ❌";
            }
        }
        
        // Check 3: Theme directories
        echo "<h2>3. Verifikasi Direktori Theme</h2>";
        $zoom_dirs = ['themes/modern/zoom'];
        foreach ($zoom_dirs as $dir) {
            $total_checks++;
            if (!is_dir($dir)) {
                echo "<div class='check-item success'>";
                echo "<span class='status success'>✅ BERHASIL</span> - Direktori <code>{$dir}</code> sudah terhapus";
                echo "</div>";
                $passed_checks++;
                $checks[] = "Direktori {$dir}: TERHAPUS ✅";
            } else {
                echo "<div class='check-item warning'>";
                echo "<span class='status warning'>⚠️ PERINGATAN</span> - Direktori <code>{$dir}</code> masih ada";
                echo "</div>";
                $checks[] = "Direktori {$dir}: MASIH ADA ⚠️";
            }
        }
        
        // Check 4: Environment variables
        echo "<h2>4. Verifikasi Environment Variables</h2>";
        $env_file = '.env';
        if (file_exists($env_file)) {
            $env_content = file_get_contents($env_file);
            $zoom_vars = ['ZOOM_API_KEY', 'ZOOM_API_SECRET', 'ZOOM_ACCOUNT_ID'];
            foreach ($zoom_vars as $var) {
                $total_checks++;
                if (strpos($env_content, $var) === false) {
                    echo "<div class='check-item success'>";
                    echo "<span class='status success'>✅ BERHASIL</span> - Variabel <code>{$var}</code> sudah terhapus dari .env";
                    echo "</div>";
                    $passed_checks++;
                    $checks[] = "Env {$var}: TERHAPUS ✅";
                } else {
                    echo "<div class='check-item error'>";
                    echo "<span class='status error'>❌ GAGAL</span> - Variabel <code>{$var}</code> masih ada di .env";
                    echo "</div>";
                    $checks[] = "Env {$var}: MASIH ADA ❌";
                }
            }
        } else {
            echo "<div class='check-item warning'>";
            echo "<span class='status warning'>⚠️ PERINGATAN</span> - File .env tidak ditemukan";
            echo "</div>";
        }
        
        // Check 5: Code references
        echo "<h2>5. Verifikasi Referensi Code</h2>";
        $files_to_check = ['bootstrap.php', 'core/admin.php'];
        foreach ($files_to_check as $file) {
            if (file_exists($file)) {
                $total_checks++;
                $content = file_get_contents($file);
                if (strpos($content, 'zoom-integration.php') === false && 
                    strpos($content, 'ZoomIntegration') === false) {
                    echo "<div class='check-item success'>";
                    echo "<span class='status success'>✅ BERHASIL</span> - File <code>{$file}</code> sudah dibersihkan dari referensi Zoom";
                    echo "</div>";
                    $passed_checks++;
                    $checks[] = "Referensi di {$file}: DIBERSIHKAN ✅";
                } else {
                    echo "<div class='check-item warning'>";
                    echo "<span class='status warning'>⚠️ PERINGATAN</span> - File <code>{$file}</code> masih mengandung referensi Zoom";
                    echo "</div>";
                    $checks[] = "Referensi di {$file}: MASIH ADA ⚠️";
                }
            }
        }
        
        // Check 6: Backup files
        echo "<h2>6. Verifikasi File Backup</h2>";
        $backup_dir = 'backup';
        if (is_dir($backup_dir)) {
            $backup_files = glob($backup_dir . '/zoom-integration-backup-*.sql');
            if (!empty($backup_files)) {
                echo "<div class='check-item success'>";
                echo "<span class='status success'>✅ BERHASIL</span> - File backup ditemukan: " . count($backup_files) . " file";
                foreach ($backup_files as $backup_file) {
                    echo "<br>• <code>" . basename($backup_file) . "</code>";
                }
                echo "</div>";
                $checks[] = "File backup: TERSEDIA ✅ (" . count($backup_files) . " file)";
            } else {
                echo "<div class='check-item warning'>";
                echo "<span class='status warning'>⚠️ PERINGATAN</span> - File backup tidak ditemukan";
                echo "</div>";
                $checks[] = "File backup: TIDAK DITEMUKAN ⚠️";
            }
        }
        
        // Summary
        $success_rate = round(($passed_checks / $total_checks) * 100, 1);
        echo "<div class='summary'>";
        echo "<h2>📊 Ringkasan Verifikasi</h2>";
        echo "<p><strong>Total Checks:</strong> {$total_checks}</p>";
        echo "<p><strong>Passed:</strong> {$passed_checks}</p>";
        echo "<p><strong>Success Rate:</strong> {$success_rate}%</p>";
        
        if ($success_rate >= 90) {
            echo "<div class='check-item success'>";
            echo "<span class='status success'>🎉 EXCELLENT</span> - Zoom Integration berhasil dihapus dengan sempurna!";
            echo "</div>";
        } elseif ($success_rate >= 70) {
            echo "<div class='check-item warning'>";
            echo "<span class='status warning'>⚠️ GOOD</span> - Sebagian besar komponen sudah terhapus, ada beberapa yang perlu dibersihkan manual.";
            echo "</div>";
        } else {
            echo "<div class='check-item error'>";
            echo "<span class='status error'>❌ NEEDS ATTENTION</span> - Masih banyak komponen Zoom yang belum terhapus.";
            echo "</div>";
        }
        
        echo "<h3>Detail Checks:</h3>";
        echo "<ul>";
        foreach ($checks as $check) {
            echo "<li>{$check}</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        // Recommendations
        echo "<h2>💡 Rekomendasi</h2>";
        if ($success_rate < 100) {
            echo "<div class='check-item warning'>";
            echo "<h3>Langkah Manual yang Diperlukan:</h3>";
            echo "<ol>";
            if (strpos(implode(' ', $checks), 'MASIH ADA') !== false) {
                echo "<li>Hapus file/direktori yang masih tersisa secara manual</li>";
                echo "<li>Bersihkan referensi code yang masih ada</li>";
            }
            echo "<li>Restart web server untuk memastikan perubahan diterapkan</li>";
            echo "<li>Periksa error log untuk memastikan tidak ada error terkait Zoom</li>";
            echo "<li>Test fungsionalitas website secara menyeluruh</li>";
            echo "</ol>";
            echo "</div>";
        } else {
            echo "<div class='check-item success'>";
            echo "<h3>Uninstall Sempurna! 🎉</h3>";
            echo "<p>Semua komponen Zoom Integration sudah terhapus dengan sempurna.</p>";
            echo "<p><strong>Langkah terakhir:</strong></p>";
            echo "<ol>";
            echo "<li>Hapus file verifikasi ini: <code>verify-zoom-uninstall.php</code></li>";
            echo "<li>Hapus file uninstall: <code>uninstall-zoom-integration.php</code></li>";
            echo "<li>Test website untuk memastikan semua berfungsi normal</li>";
            echo "</ol>";
            echo "</div>";
        }
        
        echo "<div style='margin-top: 30px;'>";
        echo "<a href='admin' class='btn btn-primary'>🏠 Kembali ke Admin</a>";
        if ($success_rate < 100) {
            echo "<a href='uninstall-zoom-integration.php?uninstall_key=epic_uninstall_zoom_2025' class='btn btn-danger'>🔄 Jalankan Uninstall Lagi</a>";
        }
        echo "</div>";
        ?>
    </div>
</body>
</html>