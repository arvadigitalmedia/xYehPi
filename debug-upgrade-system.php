<?php
/**
 * DEBUGGING KOMPREHENSIF SISTEM UPGRADE
 * Script untuk menganalisis semua aspek sistem upgrade dan mengidentifikasi akar masalah
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'core/config.php';
require_once 'core/functions.php';

// Start session untuk testing
session_start();

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.info { color: blue; }
h2 { background: #f5f5f5; padding: 10px; margin: 0 -15px 15px -15px; }
pre { background: #f8f8f8; padding: 10px; border-radius: 3px; overflow-x: auto; }
.test-result { margin: 5px 0; padding: 5px; border-left: 3px solid #ccc; }
.test-result.pass { border-color: green; background: #f0fff0; }
.test-result.fail { border-color: red; background: #fff0f0; }
.test-result.warn { border-color: orange; background: #fff8f0; }
</style>";

echo "<h1>🔍 DEBUGGING KOMPREHENSIF SISTEM UPGRADE</h1>";

// =============================================================================
// 1. ANALISIS ENVIRONMENT & KONFIGURASI
// =============================================================================
echo "<div class='section'>";
echo "<h2>1. 🌐 Environment & Konfigurasi</h2>";

// PHP Version
echo "<div class='test-result " . (version_compare(PHP_VERSION, '7.4', '>=') ? 'pass' : 'fail') . "'>";
echo "PHP Version: " . PHP_VERSION . (version_compare(PHP_VERSION, '7.4', '>=') ? ' ✅' : ' ❌ (Minimal 7.4)');
echo "</div>";

// Session Status
echo "<div class='test-result " . (session_status() === PHP_SESSION_ACTIVE ? 'pass' : 'fail') . "'>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active ✅' : 'Inactive ❌');
echo "</div>";

// Error Reporting
echo "<div class='test-result info'>";
echo "Error Reporting: " . error_reporting() . " (Display Errors: " . ini_get('display_errors') . ")";
echo "</div>";

echo "</div>";

// =============================================================================
// 2. ANALISIS DATABASE & KONEKSI
// =============================================================================
echo "<div class='section'>";
echo "<h2>2. 🗄️ Database & Koneksi</h2>";

try {
    $db = db();
    echo "<div class='test-result pass'>Database Connection: ✅ Connected</div>";
    
    // Test query
    $count = $db->selectValue("SELECT COUNT(*) FROM epic_users");
    echo "<div class='test-result pass'>Users Table: ✅ {$count} records found</div>";
    
    // Check required columns
    $columns_result = $db->select("SHOW COLUMNS FROM epic_users");
    $columns = array_column($columns_result, 'Field');
    
    $required_columns = ['id', 'status', 'hierarchy_level', 'referral_code', 'epis_supervisor_id'];
    foreach ($required_columns as $col) {
        $exists = in_array($col, $columns);
        echo "<div class='test-result " . ($exists ? 'pass' : 'fail') . "'>";
        echo "Column '$col': " . ($exists ? '✅ Exists' : '❌ Missing');
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='test-result fail'>Database Error: ❌ " . $e->getMessage() . "</div>";
}

echo "</div>";

// =============================================================================
// 3. ANALISIS FUNGSI UPGRADE
// =============================================================================
echo "<div class='section'>";
echo "<h2>3. ⚙️ Fungsi Upgrade Backend</h2>";

// Check function existence
$functions = ['epic_safe_upgrade_to_epic', 'epic_validate_upgrade_eligibility', 'epic_get_upgrade_impact'];
foreach ($functions as $func) {
    $exists = function_exists($func);
    echo "<div class='test-result " . ($exists ? 'pass' : 'fail') . "'>";
    echo "Function '$func': " . ($exists ? '✅ Exists' : '❌ Missing');
    echo "</div>";
}

// Initialize variables
$test_user = null;

// Test dengan user sample
try {
    $test_user = $db->selectOne("SELECT * FROM epic_users WHERE status = 'free' AND hierarchy_level = 1 LIMIT 1");
    
    if ($test_user) {
        echo "<div class='test-result info'>Test User Found: ID {$test_user['id']} - {$test_user['name']}</div>";
        
        // Test eligibility
        if (function_exists('epic_validate_upgrade_eligibility')) {
            try {
                $eligibility = epic_validate_upgrade_eligibility($test_user['id']);
                echo "<div class='test-result " . ($eligibility['eligible'] ? 'pass' : 'warn') . "'>";
                echo "Eligibility Check: " . ($eligibility['eligible'] ? '✅ Eligible' : '⚠️ ' . $eligibility['reason']);
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='test-result fail'>Eligibility Error: ❌ " . $e->getMessage() . "</div>";
            }
        }
        
        // Test impact analysis
        if (function_exists('epic_get_upgrade_impact')) {
            try {
                $impact = epic_get_upgrade_impact($test_user['id']);
                echo "<div class='test-result pass'>Impact Analysis: ✅ " . json_encode($impact) . "</div>";
            } catch (Exception $e) {
                echo "<div class='test-result fail'>Impact Analysis Error: ❌ " . $e->getMessage() . "</div>";
            }
        }
    } else {
        echo "<div class='test-result warn'>No Free Account users found for testing ⚠️</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-result fail'>User Query Error: ❌ " . $e->getMessage() . "</div>";
}

echo "</div>";

// =============================================================================
// 4. ANALISIS ENDPOINT AJAX
// =============================================================================
echo "<div class='section'>";
echo "<h2>4. 🌐 Endpoint AJAX</h2>";

$endpoint_path = __DIR__ . '/api/admin/upgrade-member.php';
$endpoint_exists = file_exists($endpoint_path);

echo "<div class='test-result " . ($endpoint_exists ? 'pass' : 'fail') . "'>";
echo "Endpoint File: " . ($endpoint_exists ? '✅ Exists' : '❌ Missing') . " ($endpoint_path)";
echo "</div>";

if ($endpoint_exists) {
    // Check file permissions
    $readable = is_readable($endpoint_path);
    echo "<div class='test-result " . ($readable ? 'pass' : 'fail') . "'>";
    echo "File Readable: " . ($readable ? '✅ Yes' : '❌ No');
    echo "</div>";
    
    // Check file size
    $size = filesize($endpoint_path);
    echo "<div class='test-result " . ($size > 0 ? 'pass' : 'fail') . "'>";
    echo "File Size: " . ($size > 0 ? "✅ {$size} bytes" : '❌ Empty file');
    echo "</div>";
}

echo "</div>";

// =============================================================================
// 5. ANALISIS SESSION & PERMISSIONS
// =============================================================================
echo "<div class='section'>";
echo "<h2>5. 🔐 Session & Permissions</h2>";

echo "<div class='test-result info'>Current Session Data:</div>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Simulasi admin session untuk testing
if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
    echo "<div class='test-result warn'>⚠️ Admin session disimulasikan untuk testing</div>";
}

// Check admin permissions
$is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin']);
echo "<div class='test-result " . ($is_admin ? 'pass' : 'fail') . "'>";
echo "Admin Permission: " . ($is_admin ? '✅ Valid' : '❌ Invalid');
echo "</div>";

echo "</div>";

// =============================================================================
// 6. TEST SIMULASI AJAX REQUEST
// =============================================================================
echo "<div class='section'>";
echo "<h2>6. 🧪 Simulasi AJAX Request</h2>";

if ($endpoint_exists && $test_user) {
    // Backup original $_POST and $_SERVER
    $original_post = $_POST;
    $original_server = $_SERVER;
    
    // Simulasi POST request
    $_POST = ['member_id' => $test_user['id']];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    
    echo "<div class='test-result info'>Simulating AJAX POST to endpoint...</div>";
    echo "<div class='test-result info'>POST Data: " . json_encode($_POST) . "</div>";
    
    // Capture output
    ob_start();
    try {
        include $endpoint_path;
        $output = ob_get_contents();
    } catch (Exception $e) {
        $output = "Error: " . $e->getMessage();
    }
    ob_end_clean();
    
    // Restore original values
    $_POST = $original_post;
    $_SERVER = $original_server;
    
    echo "<div class='test-result info'>Endpoint Response:</div>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Try to decode as JSON
    $json_response = json_decode($output, true);
    if ($json_response !== null) {
        echo "<div class='test-result pass'>✅ Valid JSON Response</div>";
        echo "<div class='test-result info'>Parsed Response:</div>";
        echo "<pre>" . print_r($json_response, true) . "</pre>";
    } else {
        echo "<div class='test-result fail'>❌ Invalid JSON Response</div>";
    }
} else {
    echo "<div class='test-result warn'>⚠️ Cannot simulate - missing endpoint or test user</div>";
}

echo "</div>";

// =============================================================================
// 7. ANALISIS FILE FRONTEND
// =============================================================================
echo "<div class='section'>";
echo "<h2>7. 🎨 Frontend Files</h2>";

$frontend_file = __DIR__ . '/themes/modern/admin/content/member-content.php';
$frontend_exists = file_exists($frontend_file);

echo "<div class='test-result " . ($frontend_exists ? 'pass' : 'fail') . "'>";
echo "Frontend File: " . ($frontend_exists ? '✅ Exists' : '❌ Missing') . " (member-content.php)";
echo "</div>";

// Initialize variables
$has_upgrade_function = false;
$has_ajax = false;
$has_data_attr = false;
$has_update_function = false;

if ($frontend_exists) {
    $content = file_get_contents($frontend_file);
    
    // Check for upgradeAccount function
    $has_upgrade_function = strpos($content, 'function upgradeAccount') !== false;
    echo "<div class='test-result " . ($has_upgrade_function ? 'pass' : 'fail') . "'>";
    echo "upgradeAccount Function: " . ($has_upgrade_function ? '✅ Found' : '❌ Missing');
    echo "</div>";
    
    // Check for AJAX implementation
    $has_ajax = strpos($content, 'XMLHttpRequest') !== false || strpos($content, '$.ajax') !== false || strpos($content, 'fetch(') !== false;
    echo "<div class='test-result " . ($has_ajax ? 'pass' : 'fail') . "'>";
    echo "AJAX Implementation: " . ($has_ajax ? '✅ Found' : '❌ Missing');
    echo "</div>";
    
    // Check for data-member-id attribute
    $has_data_attr = strpos($content, 'data-member-id') !== false;
    echo "<div class='test-result " . ($has_data_attr ? 'pass' : 'fail') . "'>";
    echo "data-member-id Attribute: " . ($has_data_attr ? '✅ Found' : '❌ Missing');
    echo "</div>";
    
    // Check for updateMemberRow function
    $has_update_function = strpos($content, 'function updateMemberRow') !== false;
    echo "<div class='test-result " . ($has_update_function ? 'pass' : 'fail') . "'>";
    echo "updateMemberRow Function: " . ($has_update_function ? '✅ Found' : '❌ Missing');
    echo "</div>";
}

echo "</div>";

// =============================================================================
// 8. REKOMENDASI PERBAIKAN
// =============================================================================
echo "<div class='section'>";
echo "<h2>8. 💡 Rekomendasi Perbaikan</h2>";

$issues = [];
$recommendations = [];

// Collect issues and recommendations based on tests above
if (!$endpoint_exists) {
    $issues[] = "Endpoint AJAX tidak ditemukan";
    $recommendations[] = "Buat file api/admin/upgrade-member.php";
}

if (!function_exists('epic_safe_upgrade_to_epic')) {
    $issues[] = "Fungsi upgrade backend tidak tersedia";
    $recommendations[] = "Tambahkan fungsi upgrade di core/functions.php";
}

if (!$is_admin) {
    $issues[] = "Session admin tidak valid";
    $recommendations[] = "Pastikan user login sebagai admin";
}

if ($frontend_exists) {
    if (!$has_ajax) {
        $issues[] = "Implementasi AJAX tidak ditemukan di frontend";
        $recommendations[] = "Update fungsi upgradeAccount() untuk menggunakan AJAX";
    }
    if (!$has_upgrade_function) {
        $issues[] = "Fungsi upgradeAccount tidak ditemukan di frontend";
        $recommendations[] = "Tambahkan fungsi upgradeAccount() di member-content.php";
    }
}

if (empty($issues)) {
    echo "<div class='test-result pass'>✅ Tidak ada masalah kritis ditemukan</div>";
} else {
    echo "<div class='test-result fail'>❌ Masalah yang ditemukan:</div>";
    foreach ($issues as $issue) {
        echo "<div style='margin-left: 20px;'>• $issue</div>";
    }
    
    echo "<div class='test-result info'>💡 Rekomendasi perbaikan:</div>";
    foreach ($recommendations as $rec) {
        echo "<div style='margin-left: 20px;'>• $rec</div>";
    }
}

echo "</div>";

// =============================================================================
// 9. QUICK FIX ACTIONS
// =============================================================================
echo "<div class='section'>";
echo "<h2>9. 🚀 Quick Fix Actions</h2>";

echo "<div style='margin: 10px 0;'>";
echo "<a href='test-upgrade-ajax.php' target='_blank' style='background: #007cba; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>🧪 Test AJAX Endpoint</a>";
echo "<a href='themes/modern/admin/member.php' target='_blank' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>👥 Open Member Management</a>";
echo "<a href='debug-upgrade-system.php' target='_blank' style='background: #ffc107; color: black; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>🔄 Refresh Debug</a>";
echo "</div>";

echo "</div>";

echo "<div style='margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
echo "<strong>📋 Langkah Selanjutnya:</strong><br>";
echo "1. Periksa semua hasil test di atas<br>";
echo "2. Perbaiki masalah yang ditemukan berdasarkan rekomendasi<br>";
echo "3. Test ulang dengan mengklik tombol upgrade di Member Management<br>";
echo "4. Monitor console browser untuk error JavaScript<br>";
echo "5. Periksa network tab untuk request AJAX<br>";
echo "</div>";
?>