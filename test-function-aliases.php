<?php
/**
 * Test Script untuk Function Aliases
 * Memverifikasi semua fungsi yang diperbaiki bekerja dengan benar
 */

require_once __DIR__ . '/bootstrap.php';

// Set headers untuk output yang bersih
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Function Aliases - EPIC Hub</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-result { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        h1 { color: #333; text-align: center; }
        h2 { color: #666; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .function-test { margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Test Function Aliases - EPIC Hub</h1>
        <p><strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?></p>
        
        <h2>📋 Ringkasan Test</h2>
        
        <?php
        $tests = [];
        $total_tests = 0;
        $passed_tests = 0;
        
        // Test 1: epic_get_db()
        echo '<div class="function-test">';
        echo '<h3>1. Test epic_get_db()</h3>';
        $total_tests++;
        
        try {
            if (function_exists('epic_get_db')) {
                $db = epic_get_db();
                if ($db && method_exists($db, 'selectValue')) {
                    $result = $db->selectValue('SELECT 1');
                    if ($result == 1) {
                        echo '<div class="test-result success">✅ epic_get_db() bekerja dengan benar</div>';
                        $passed_tests++;
                        $tests['epic_get_db'] = 'PASS';
                    } else {
                        echo '<div class="test-result error">❌ epic_get_db() tidak mengembalikan database yang valid</div>';
                        $tests['epic_get_db'] = 'FAIL';
                    }
                } else {
                    echo '<div class="test-result error">❌ epic_get_db() tidak mengembalikan objek database yang valid</div>';
                    $tests['epic_get_db'] = 'FAIL';
                }
            } else {
                echo '<div class="test-result error">❌ Fungsi epic_get_db() tidak ditemukan</div>';
                $tests['epic_get_db'] = 'FAIL';
            }
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $tests['epic_get_db'] = 'FAIL';
        }
        echo '</div>';
        
        // Test 2: epic_404()
        echo '<div class="function-test">';
        echo '<h3>2. Test epic_404()</h3>';
        $total_tests++;
        
        try {
            if (function_exists('epic_404')) {
                echo '<div class="test-result success">✅ Fungsi epic_404() tersedia</div>';
                echo '<div class="test-result info">ℹ️ Fungsi ini akan menampilkan halaman 404 jika dipanggil</div>';
                $passed_tests++;
                $tests['epic_404'] = 'PASS';
            } else {
                echo '<div class="test-result error">❌ Fungsi epic_404() tidak ditemukan</div>';
                $tests['epic_404'] = 'FAIL';
            }
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $tests['epic_404'] = 'FAIL';
        }
        echo '</div>';
        
        // Test 3: epic_csrf_token_field()
        echo '<div class="function-test">';
        echo '<h3>3. Test epic_csrf_token_field()</h3>';
        $total_tests++;
        
        try {
            if (function_exists('epic_csrf_token_field')) {
                $csrf_field = epic_csrf_token_field('test');
                if (!empty($csrf_field) && strpos($csrf_field, 'csrf_token') !== false) {
                    echo '<div class="test-result success">✅ epic_csrf_token_field() bekerja dengan benar</div>';
                    echo '<div class="test-result info">📝 Output: <pre>' . htmlspecialchars($csrf_field) . '</pre></div>';
                    $passed_tests++;
                    $tests['epic_csrf_token_field'] = 'PASS';
                } else {
                    echo '<div class="test-result error">❌ epic_csrf_token_field() tidak menghasilkan field yang valid</div>';
                    $tests['epic_csrf_token_field'] = 'FAIL';
                }
            } else {
                echo '<div class="test-result error">❌ Fungsi epic_csrf_token_field() tidak ditemukan</div>';
                $tests['epic_csrf_token_field'] = 'FAIL';
            }
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $tests['epic_csrf_token_field'] = 'FAIL';
        }
        echo '</div>';
        
        // Test 4: epic_csrf_token()
        echo '<div class="function-test">';
        echo '<h3>4. Test epic_csrf_token()</h3>';
        $total_tests++;
        
        try {
            if (function_exists('epic_csrf_token')) {
                $token = epic_csrf_token('test');
                if (!empty($token) && strlen($token) >= 32) {
                    echo '<div class="test-result success">✅ epic_csrf_token() bekerja dengan benar</div>';
                    echo '<div class="test-result info">🔑 Token: ' . substr($token, 0, 16) . '... (truncated)</div>';
                    $passed_tests++;
                    $tests['epic_csrf_token'] = 'PASS';
                } else {
                    echo '<div class="test-result error">❌ epic_csrf_token() tidak menghasilkan token yang valid</div>';
                    $tests['epic_csrf_token'] = 'FAIL';
                }
            } else {
                echo '<div class="test-result error">❌ Fungsi epic_csrf_token() tidak ditemukan</div>';
                $tests['epic_csrf_token'] = 'FAIL';
            }
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $tests['epic_csrf_token'] = 'FAIL';
        }
        echo '</div>';
        
        // Test 5: epic_sanitize()
        echo '<div class="function-test">';
        echo '<h3>5. Test epic_sanitize()</h3>';
        $total_tests++;
        
        try {
            if (function_exists('epic_sanitize')) {
                $test_input = '<script>alert("xss")</script>Test Input';
                $sanitized = epic_sanitize($test_input);
                if ($sanitized !== $test_input && !strpos($sanitized, '<script>')) {
                    echo '<div class="test-result success">✅ epic_sanitize() bekerja dengan benar</div>';
                    echo '<div class="test-result info">🧹 Input: ' . htmlspecialchars($test_input) . '</div>';
                    echo '<div class="test-result info">🧹 Output: ' . htmlspecialchars($sanitized) . '</div>';
                    $passed_tests++;
                    $tests['epic_sanitize'] = 'PASS';
                } else {
                    echo '<div class="test-result error">❌ epic_sanitize() tidak melakukan sanitasi dengan benar</div>';
                    $tests['epic_sanitize'] = 'FAIL';
                }
            } else {
                echo '<div class="test-result error">❌ Fungsi epic_sanitize() tidak ditemukan</div>';
                $tests['epic_sanitize'] = 'FAIL';
            }
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $tests['epic_sanitize'] = 'FAIL';
        }
        echo '</div>';
        
        // Test 6: epic_error()
        echo '<div class="function-test">';
        echo '<h3>6. Test epic_error()</h3>';
        $total_tests++;
        
        try {
            if (function_exists('epic_error')) {
                echo '<div class="test-result success">✅ Fungsi epic_error() tersedia</div>';
                echo '<div class="test-result info">ℹ️ Fungsi ini akan menampilkan halaman error jika dipanggil</div>';
                $passed_tests++;
                $tests['epic_error'] = 'PASS';
            } else {
                echo '<div class="test-result error">❌ Fungsi epic_error() tidak ditemukan</div>';
                $tests['epic_error'] = 'FAIL';
            }
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $tests['epic_error'] = 'FAIL';
        }
        echo '</div>';
        
        // Test 7: epic_log()
        echo '<div class="function-test">';
        echo '<h3>7. Test epic_log()</h3>';
        $total_tests++;
        
        try {
            if (function_exists('epic_log')) {
                $log_id = epic_log('info', 'Test log message from function aliases test');
                if (!empty($log_id)) {
                    echo '<div class="test-result success">✅ epic_log() bekerja dengan benar</div>';
                    echo '<div class="test-result info">📝 Log ID: ' . htmlspecialchars($log_id) . '</div>';
                    $passed_tests++;
                    $tests['epic_log'] = 'PASS';
                } else {
                    echo '<div class="test-result error">❌ epic_log() tidak mengembalikan log ID</div>';
                    $tests['epic_log'] = 'FAIL';
                }
            } else {
                echo '<div class="test-result error">❌ Fungsi epic_log() tidak ditemukan</div>';
                $tests['epic_log'] = 'FAIL';
            }
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $tests['epic_log'] = 'FAIL';
        }
        echo '</div>';
        
        // Ringkasan hasil
        echo '<h2>📊 Ringkasan Hasil Test</h2>';
        
        $success_rate = ($passed_tests / $total_tests) * 100;
        
        if ($success_rate == 100) {
            echo '<div class="test-result success">';
            echo '<h3>🎉 SEMUA TEST BERHASIL!</h3>';
            echo "<p>✅ {$passed_tests}/{$total_tests} fungsi bekerja dengan benar ({$success_rate}%)</p>";
            echo '</div>';
        } elseif ($success_rate >= 80) {
            echo '<div class="test-result warning">';
            echo '<h3>⚠️ SEBAGIAN BESAR TEST BERHASIL</h3>';
            echo "<p>✅ {$passed_tests}/{$total_tests} fungsi bekerja dengan benar ({$success_rate}%)</p>";
            echo '</div>';
        } else {
            echo '<div class="test-result error">';
            echo '<h3>❌ BANYAK TEST GAGAL</h3>';
            echo "<p>❌ {$passed_tests}/{$total_tests} fungsi bekerja dengan benar ({$success_rate}%)</p>";
            echo '</div>';
        }
        
        echo '<h3>📋 Detail Hasil:</h3>';
        echo '<ul>';
        foreach ($tests as $function => $result) {
            $icon = $result === 'PASS' ? '✅' : '❌';
            echo "<li>{$icon} <strong>{$function}():</strong> {$result}</li>";
        }
        echo '</ul>';
        
        // Informasi sistem
        echo '<h2>🔧 Informasi Sistem</h2>';
        echo '<div class="test-result info">';
        echo '<ul>';
        echo '<li><strong>PHP Version:</strong> ' . PHP_VERSION . '</li>';
        echo '<li><strong>EPIC Version:</strong> ' . (defined('EPIC_VERSION') ? EPIC_VERSION : 'Unknown') . '</li>';
        echo '<li><strong>Session Status:</strong> ' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . '</li>';
        echo '<li><strong>Function Aliases File:</strong> ' . (file_exists(EPIC_CORE_DIR . '/function-aliases.php') ? 'Loaded' : 'Not Found') . '</li>';
        echo '<li><strong>CSRF Protection:</strong> ' . (file_exists(EPIC_CORE_DIR . '/csrf-protection.php') ? 'Loaded' : 'Not Found') . '</li>';
        echo '</ul>';
        echo '</div>';
        
        ?>
        
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <h3>🔗 Navigasi</h3>
            <p>
                <a href="<?= epic_url() ?>" style="color: #007bff; text-decoration: none;">← Kembali ke Beranda</a> |
                <a href="<?= epic_url('admin') ?>" style="color: #007bff; text-decoration: none;">Admin Dashboard</a> |
                <a href="javascript:location.reload()" style="color: #007bff; text-decoration: none;">🔄 Refresh Test</a>
            </p>
        </div>
    </div>
</body>
</html>