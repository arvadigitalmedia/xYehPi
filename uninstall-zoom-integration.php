<?php
/**
 * EPIC Hub - Uninstall Zoom Integration
 * Script untuk menghapus semua komponen Zoom Integration secara aman
 * 
 * PERINGATAN: Script ini akan menghapus:
 * - File core zoom integration
 * - Tabel database zoom
 * - Konfigurasi zoom di .env
 * - Direktori zoom themes
 * 
 * @package EPIC Hub
 * @version 1.0.0
 * @author Bustanu
 */

// Security check
$uninstall_key = $_GET['uninstall_key'] ?? '';
if ($uninstall_key !== 'epic_uninstall_zoom_2025') {
    die('❌ Akses ditolak. Gunakan URL: ?uninstall_key=epic_uninstall_zoom_2025');
}

// Prevent direct access in production
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') === false) {
    $confirm = $_GET['confirm'] ?? '';
    if ($confirm !== 'yes') {
        die('❌ Untuk keamanan, tambahkan &confirm=yes di URL untuk server production');
    }
}

session_start();
require_once 'bootstrap.php';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uninstall Zoom Integration - EPIC Hub</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; margin-bottom: 20px; }
        h2 { color: #495057; border-bottom: 2px solid #e9ecef; padding-bottom: 10px; }
        .alert { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; font-weight: 500; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .file-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .file-list ul { margin: 0; padding-left: 20px; }
        .progress { background: #e9ecef; border-radius: 5px; height: 20px; margin: 10px 0; }
        .progress-bar { background: #28a745; height: 100%; border-radius: 5px; transition: width 0.3s; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        .step.completed { border-left-color: #28a745; background: #d4edda; }
        .step.error { border-left-color: #dc3545; background: #f8d7da; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗑️ Uninstall Zoom Integration</h1>
        
        <div class="alert alert-danger">
            <strong>⚠️ PERINGATAN:</strong> Script ini akan menghapus semua komponen Zoom Integration secara permanen. 
            Pastikan Anda sudah backup data penting sebelum melanjutkan.
        </div>

        <?php
        $action = $_POST['action'] ?? 'show_info';
        
        if ($action === 'show_info') {
            // Show information about what will be removed
            ?>
            <h2>📋 Komponen yang Akan Dihapus</h2>
            
            <div class="step">
                <h3>1. File Core Zoom Integration</h3>
                <div class="file-list">
                    <ul>
                        <li><code>core/zoom-integration.php</code></li>
                        <li><code>install-zoom-integration.php</code></li>
                        <li><code>zoom-integration-schema.sql</code></li>
                    </ul>
                </div>
            </div>

            <div class="step">
                <h3>2. Tabel Database Zoom</h3>
                <div class="file-list">
                    <ul>
                        <li><code>epic_zoom_events</code></li>
                        <li><code>epic_zoom_settings</code></li>
                        <li><code>epic_event_registrations</code> (jika ada)</li>
                    </ul>
                </div>
            </div>

            <div class="step">
                <h3>3. Direktori Themes Zoom</h3>
                <div class="file-list">
                    <ul>
                        <li><code>themes/modern/zoom/</code> (direktori kosong)</li>
                    </ul>
                </div>
            </div>

            <div class="step">
                <h3>4. Konfigurasi Environment</h3>
                <div class="file-list">
                    <ul>
                        <li>Hapus variabel <code>ZOOM_API_KEY</code> dari .env</li>
                        <li>Hapus variabel <code>ZOOM_API_SECRET</code> dari .env</li>
                        <li>Hapus variabel <code>ZOOM_ACCOUNT_ID</code> dari .env</li>
                    </ul>
                </div>
            </div>

            <div class="step">
                <h3>5. Referensi Code</h3>
                <div class="file-list">
                    <ul>
                        <li>Hapus include zoom-integration dari bootstrap.php</li>
                        <li>Hapus referensi zoom dari admin.php</li>
                        <li>Update komentar di file terkait</li>
                    </ul>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="uninstall">
                <div class="alert alert-warning">
                    <strong>Konfirmasi:</strong> Saya memahami bahwa proses ini tidak dapat dibatalkan dan akan menghapus semua data Zoom Integration.
                </div>
                <button type="submit" class="btn btn-danger">🗑️ Mulai Uninstall</button>
                <a href="admin" class="btn btn-secondary">❌ Batal</a>
            </form>
            <?php
        } elseif ($action === 'uninstall') {
            // Perform uninstall
            ?>
            <h2>🔄 Proses Uninstall</h2>
            <div class="progress">
                <div class="progress-bar" style="width: 0%" id="progress"></div>
            </div>
            
            <?php
            $steps = [];
            $total_steps = 6;
            $current_step = 0;
            
            // Step 1: Backup database
            $current_step++;
            echo "<div class='step' id='step{$current_step}'>";
            echo "<h3>Step {$current_step}/{$total_steps}: Backup Database</h3>";
            try {
                $backup_file = 'backup/zoom-integration-backup-' . date('Y-m-d-H-i-s') . '.sql';
                if (!is_dir('backup')) {
                    mkdir('backup', 0755, true);
                }
                
                // Backup zoom tables if they exist
                $tables_to_backup = ['epic_zoom_events', 'epic_zoom_settings', 'epic_event_registrations'];
                $backup_content = "-- Zoom Integration Backup - " . date('Y-m-d H:i:s') . "\n\n";
                
                foreach ($tables_to_backup as $table) {
                    try {
                        $result = db()->query("SHOW TABLES LIKE '{$table}'");
                        if ($result->rowCount() > 0) {
                            // Get table structure
                            $create_result = db()->query("SHOW CREATE TABLE `{$table}`");
                            $create_row = $create_result->fetch(PDO::FETCH_ASSOC);
                            $backup_content .= "-- Structure for table {$table}\n";
                            $backup_content .= $create_row['Create Table'] . ";\n\n";
                            
                            // Get table data
                            $data_result = db()->query("SELECT * FROM `{$table}`");
                            if ($data_result->rowCount() > 0) {
                                $backup_content .= "-- Data for table {$table}\n";
                                while ($row = $data_result->fetch(PDO::FETCH_ASSOC)) {
                                    $values = array_map(function($value) {
                                        return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                                    }, $row);
                                    $backup_content .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                                }
                                $backup_content .= "\n";
                            }
                        }
                    } catch (Exception $e) {
                        // Table doesn't exist, skip
                    }
                }
                
                file_put_contents($backup_file, $backup_content);
                echo "<p style='color: green;'>✅ Backup database berhasil: {$backup_file}</p>";
                $steps[] = "Backup database: BERHASIL";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Backup database gagal: " . $e->getMessage() . "</p>";
                $steps[] = "Backup database: GAGAL - " . $e->getMessage();
            }
            echo "</div>";
            
            // Step 2: Drop database tables
            $current_step++;
            echo "<div class='step' id='step{$current_step}'>";
            echo "<h3>Step {$current_step}/{$total_steps}: Hapus Tabel Database</h3>";
            $tables_to_drop = ['epic_zoom_events', 'epic_zoom_settings', 'epic_event_registrations'];
            foreach ($tables_to_drop as $table) {
                try {
                    db()->exec("DROP TABLE IF EXISTS `{$table}`");
                    echo "<p style='color: green;'>✅ Tabel {$table} berhasil dihapus</p>";
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠️ Tabel {$table} tidak ditemukan atau sudah dihapus</p>";
                }
            }
            $steps[] = "Hapus tabel database: SELESAI";
            echo "</div>";
            
            // Step 3: Remove core files
            $current_step++;
            echo "<div class='step' id='step{$current_step}'>";
            echo "<h3>Step {$current_step}/{$total_steps}: Hapus File Core</h3>";
            $files_to_remove = [
                'core/zoom-integration.php',
                'install-zoom-integration.php',
                'zoom-integration-schema.sql'
            ];
            foreach ($files_to_remove as $file) {
                if (file_exists($file)) {
                    if (unlink($file)) {
                        echo "<p style='color: green;'>✅ File {$file} berhasil dihapus</p>";
                    } else {
                        echo "<p style='color: red;'>❌ Gagal menghapus file {$file}</p>";
                    }
                } else {
                    echo "<p style='color: orange;'>⚠️ File {$file} tidak ditemukan</p>";
                }
            }
            $steps[] = "Hapus file core: SELESAI";
            echo "</div>";
            
            // Step 4: Remove theme directories
            $current_step++;
            echo "<div class='step' id='step{$current_step}'>";
            echo "<h3>Step {$current_step}/{$total_steps}: Hapus Direktori Theme</h3>";
            $zoom_theme_dir = 'themes/modern/zoom';
            if (is_dir($zoom_theme_dir)) {
                if (rmdir($zoom_theme_dir)) {
                    echo "<p style='color: green;'>✅ Direktori {$zoom_theme_dir} berhasil dihapus</p>";
                } else {
                    echo "<p style='color: red;'>❌ Gagal menghapus direktori {$zoom_theme_dir}</p>";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Direktori {$zoom_theme_dir} tidak ditemukan</p>";
            }
            $steps[] = "Hapus direktori theme: SELESAI";
            echo "</div>";
            
            // Step 5: Clean environment file
            $current_step++;
            echo "<div class='step' id='step{$current_step}'>";
            echo "<h3>Step {$current_step}/{$total_steps}: Bersihkan File Environment</h3>";
            $env_file = '.env';
            if (file_exists($env_file)) {
                $env_content = file_get_contents($env_file);
                $env_lines = explode("\n", $env_content);
                $cleaned_lines = [];
                $zoom_section = false;
                
                foreach ($env_lines as $line) {
                    $line = trim($line);
                    if (strpos($line, '# Zoom Integration') !== false) {
                        $zoom_section = true;
                        continue;
                    }
                    if ($zoom_section && (empty($line) || strpos($line, '#') === 0)) {
                        if (strpos($line, '#') === 0 && strpos($line, 'Zoom') === false) {
                            $zoom_section = false;
                        } else {
                            continue;
                        }
                    }
                    if (strpos($line, 'ZOOM_') === 0) {
                        continue;
                    }
                    $cleaned_lines[] = $line;
                }
                
                $cleaned_content = implode("\n", $cleaned_lines);
                if (file_put_contents($env_file, $cleaned_content)) {
                    echo "<p style='color: green;'>✅ File .env berhasil dibersihkan dari konfigurasi Zoom</p>";
                } else {
                    echo "<p style='color: red;'>❌ Gagal membersihkan file .env</p>";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ File .env tidak ditemukan</p>";
            }
            $steps[] = "Bersihkan environment: SELESAI";
            echo "</div>";
            
            // Step 6: Clean code references
            $current_step++;
            echo "<div class='step' id='step{$current_step}'>";
            echo "<h3>Step {$current_step}/{$total_steps}: Bersihkan Referensi Code</h3>";
            
            // Clean bootstrap.php
            $bootstrap_file = 'bootstrap.php';
            if (file_exists($bootstrap_file)) {
                $content = file_get_contents($bootstrap_file);
                $content = preg_replace('/\/\/ Zoom Integration.*?\n/', "// Zoom Integration removed\n", $content);
                $content = str_replace("require_once 'core/zoom-integration.php';", "// Zoom Integration removed", $content);
                file_put_contents($bootstrap_file, $content);
                echo "<p style='color: green;'>✅ bootstrap.php dibersihkan</p>";
            }
            
            // Clean admin.php
            $admin_file = 'core/admin.php';
            if (file_exists($admin_file)) {
                $content = file_get_contents($admin_file);
                $content = preg_replace('/\/\/ Zoom integration.*?\n/', "// Zoom integration removed\n", $content);
                file_put_contents($admin_file, $content);
                echo "<p style='color: green;'>✅ core/admin.php dibersihkan</p>";
            }
            
            $steps[] = "Bersihkan referensi code: SELESAI";
            echo "</div>";
            
            // Final summary
            echo "<div class='step completed'>";
            echo "<h2>✅ Uninstall Selesai!</h2>";
            echo "<div class='alert alert-success'>";
            echo "<strong>Zoom Integration berhasil dihapus dari sistem.</strong><br><br>";
            echo "<strong>Ringkasan:</strong><br>";
            foreach ($steps as $step) {
                echo "• " . $step . "<br>";
            }
            echo "</div>";
            
            echo "<h3>🔧 Langkah Selanjutnya:</h3>";
            echo "<ol>";
            echo "<li>Hapus file uninstall ini untuk keamanan: <code>uninstall-zoom-integration.php</code></li>";
            echo "<li>Restart web server jika diperlukan</li>";
            echo "<li>Periksa error log untuk memastikan tidak ada error</li>";
            echo "<li>Test fungsionalitas website</li>";
            echo "</ol>";
            
            echo "<h3>📁 File Backup:</h3>";
            echo "<p>Backup database disimpan di: <code>backup/zoom-integration-backup-*.sql</code></p>";
            echo "<p>Jika perlu restore, import file backup tersebut ke database.</p>";
            
            echo "<a href='admin' class='btn btn-secondary'>🏠 Kembali ke Admin</a>";
            echo "</div>";
            
            // JavaScript for progress bar
            echo "<script>";
            echo "let progress = 0;";
            echo "const totalSteps = {$total_steps};";
            echo "const progressBar = document.getElementById('progress');";
            echo "const interval = setInterval(() => {";
            echo "  progress += 100 / totalSteps;";
            echo "  progressBar.style.width = Math.min(progress, 100) + '%';";
            echo "  if (progress >= 100) clearInterval(interval);";
            echo "}, 500);";
            echo "</script>";
        }
        ?>
    </div>
</body>
</html>