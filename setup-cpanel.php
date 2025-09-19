<?php
/**
 * EPIC Hub cPanel Setup Script
 * Script khusus untuk setup di hosting cPanel
 */

// Prevent direct access in production
$current_domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_localhost = (strpos($current_domain, 'localhost') !== false || 
                strpos($current_domain, '127.0.0.1') !== false ||
                strpos($current_domain, '.local') !== false);

if (!$is_localhost) {
    // Hanya izinkan akses jika ada parameter khusus
    if (!isset($_GET['setup_key']) || $_GET['setup_key'] !== 'epic_setup_2025') {
        die('Access denied. Setup key required.');
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPIC Hub - cPanel Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .container { background: #f8f9fa; padding: 30px; border-radius: 10px; }
        .header { text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info-box { background: #e3f2fd; padding: 20px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 EPIC Hub - cPanel Setup</h1>
            <p>Konfigurasi database dan environment untuk hosting cPanel</p>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validasi input
                $db_host = $_POST['db_host'] ?? 'localhost';
                $db_name = $_POST['db_name'] ?? '';
                $db_user = $_POST['db_user'] ?? '';
                $db_pass = $_POST['db_pass'] ?? '';
                $site_url = $_POST['site_url'] ?? '';

                if (empty($db_name) || empty($db_user) || empty($site_url)) {
                    throw new Exception('Semua field wajib harus diisi');
                }

                // Test koneksi database
                $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
                $pdo = new PDO($dsn, $db_user, $db_pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);

                echo '<div class="alert alert-success">✅ Koneksi database berhasil!</div>';

                // Update config.php
                $config_content = file_get_contents(__DIR__ . '/config/config.php');
                
                // Replace database configuration
                $config_content = preg_replace(
                    '/if \(!defined\(\'DB_NAME\'\)\) define\(\'DB_NAME\', \'[^\']*\'\);/',
                    "if (!defined('DB_NAME')) define('DB_NAME', '{$db_name}');",
                    $config_content
                );
                $config_content = preg_replace(
                    '/if \(!defined\(\'DB_USER\'\)\) define\(\'DB_USER\', \'[^\']*\'\);/',
                    "if (!defined('DB_USER')) define('DB_USER', '{$db_user}');",
                    $config_content
                );
                $config_content = preg_replace(
                    '/if \(!defined\(\'DB_PASS\'\)\) define\(\'DB_PASS\', \'[^\']*\'\);/',
                    "if (!defined('DB_PASS')) define('DB_PASS', '{$db_pass}');",
                    $config_content
                );
                $config_content = preg_replace(
                    '/if \(!defined\(\'SITE_URL\'\)\) define\(\'SITE_URL\', \'[^\']*\'\);/',
                    "if (!defined('SITE_URL')) define('SITE_URL', '{$site_url}');",
                    $config_content
                );

                if (file_put_contents(__DIR__ . '/config/config.php', $config_content)) {
                    echo '<div class="alert alert-success">✅ Konfigurasi berhasil disimpan!</div>';
                } else {
                    throw new Exception('Gagal menyimpan konfigurasi');
                }

                // Test Zoom Integration
                require_once __DIR__ . '/bootstrap.php';
                require_once __DIR__ . '/core/zoom-integration.php';

                $zoom = new EpicZoomIntegration();
                echo '<div class="alert alert-success">✅ Zoom Integration berhasil diinisialisasi!</div>';

                echo '<div class="info-box">';
                echo '<h3>🎉 Setup Berhasil!</h3>';
                echo '<p>Konfigurasi EPIC Hub untuk cPanel telah selesai. Langkah selanjutnya:</p>';
                echo '<ol>';
                echo '<li>Hapus file setup-cpanel.php untuk keamanan</li>';
                echo '<li>Akses halaman admin: <a href="' . $site_url . '/admin">' . $site_url . '/admin</a></li>';
                echo '<li>Setup Zoom API credentials di halaman admin</li>';
                echo '</ol>';
                echo '</div>';

            } catch (Exception $e) {
                echo '<div class="alert alert-danger">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="db_host">Database Host:</label>
                <input type="text" id="db_host" name="db_host" value="localhost" required>
            </div>

            <div class="form-group">
                <label for="db_name">Database Name:</label>
                <input type="text" id="db_name" name="db_name" placeholder="bustanu1_ujicoba" required>
                <small>Nama database yang dibuat di cPanel</small>
            </div>

            <div class="form-group">
                <label for="db_user">Database Username:</label>
                <input type="text" id="db_user" name="db_user" placeholder="bustanu1_ujicoba" required>
                <small>Username database dari cPanel</small>
            </div>

            <div class="form-group">
                <label for="db_pass">Database Password:</label>
                <input type="password" id="db_pass" name="db_pass" required>
                <small>Password database dari cPanel</small>
            </div>

            <div class="form-group">
                <label for="site_url">Site URL:</label>
                <input type="url" id="site_url" name="site_url" placeholder="https://bisnisemasperak.com" required>
                <small>URL lengkap website Anda</small>
            </div>

            <button type="submit" class="btn">🚀 Setup Database & Konfigurasi</button>
        </form>

        <div class="info-box">
            <h3>📋 Informasi cPanel Database</h3>
            <p>Untuk mendapatkan informasi database cPanel:</p>
            <ol>
                <li>Login ke cPanel hosting Anda</li>
                <li>Cari menu "MySQL Databases"</li>
                <li>Catat nama database, username, dan password</li>
                <li>Pastikan user database memiliki akses penuh ke database</li>
            </ol>
        </div>
    </div>
</body>
</html>