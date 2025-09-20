<?php
/**
 * Test Enhanced Referral Scenarios
 * Menguji semua skenario referral: EPIC→EPIS, EPIS→EPIS, link vs kode manual
 */

define('EPIC_LOADED', true);
define('EPIC_INIT', true);
require_once 'config/config.php';
require_once 'bootstrap.php';
require_once EPIC_ROOT . '/core/enhanced-referral-handler.php';

// CSS untuk styling
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #fafafa; }
.success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
.error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
.warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
.info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
.scenario { background: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 10px 0; }
h1 { color: #333; text-align: center; }
h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
h3 { color: #28a745; }
ul { margin: 10px 0; padding-left: 20px; }
li { margin: 5px 0; }
.code { background: #f8f9fa; border: 1px solid #e9ecef; padding: 10px; border-radius: 4px; font-family: monospace; }
</style>";

echo "<div class=\"container\">";
echo "<h1>🚀 Test Enhanced Referral Scenarios - EPIC Hub</h1>";
echo "<p><strong>Testing Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Test data preparation
$test_codes = [
    'epic_with_epis' => 'ADMIN001',  // EPIC dengan EPIS supervisor
    'epis_direct' => 'EPIS001',      // EPIS account langsung
    'epic_standalone' => 'EPIC001'   // EPIC tanpa EPIS supervisor
];

echo "<div class=\"info\">📋 <strong>Skenario yang akan ditest:</strong><br>";
echo "1. <strong>EPIC → EPIS:</strong> EPIC Account mereferalkan → auto-integrate ke EPIS Supervisor<br>";
echo "2. <strong>EPIS Direct:</strong> EPIS Account mereferalkan → langsung assign ke EPIS tersebut<br>";
echo "3. <strong>EPIC Standalone:</strong> EPIC Account tanpa EPIS supervisor<br>";
echo "4. <strong>Invalid Code:</strong> Kode referral tidak valid</div>";

// Scenario 1: EPIC → EPIS Auto-Integration
echo "<div class=\"test-section\">";
echo "<h2>🎯 Scenario 1: EPIC Account → EPIS Supervisor Auto-Integration</h2>";

$epic_code = $test_codes['epic_with_epis'];
echo "<div class=\"scenario\">";
echo "<h3>Testing dengan kode: {$epic_code}</h3>";

$result1 = epic_enhanced_referral_processing($epic_code);

if ($result1['success']) {
    echo "<div class=\"success\">✅ <strong>Berhasil:</strong> {$result1['message']}</div>";
    echo "<div class=\"info\">";
    echo "<strong>Detail Scenario:</strong> {$result1['scenario']}<br>";
    echo "<strong>Referrer:</strong> {$result1['referrer']['name']} ({$result1['referrer']['email']})<br>";
    
    if (isset($result1['epis_supervisor'])) {
        echo "<strong>EPIS Supervisor:</strong> {$result1['epis_supervisor']['name']} ({$result1['epis_supervisor']['email']})<br>";
    }
    
    echo "<strong>Auto Integration:</strong> " . ($result1['auto_integration'] ? 'Ya' : 'Tidak') . "<br>";
    
    if (isset($result1['assignment_data'])) {
        echo "<strong>Assignment Type:</strong> {$result1['assignment_data']['recruitment_type']}<br>";
    }
    echo "</div>";
} else {
    echo "<div class=\"error\">❌ <strong>Gagal:</strong> {$result1['message']}</div>";
}
echo "</div>";
echo "</div>";

// Scenario 2: EPIS Direct Referral
echo "<div class=\"test-section\">";
echo "<h2>👑 Scenario 2: EPIS Account Direct Referral</h2>";

// Cari EPIS account yang aktif
$epis_accounts = db()->select(
    "SELECT u.*, ea.epis_code, ea.max_epic_recruits, ea.current_epic_count 
     FROM epic_users u 
     JOIN epic_epis_accounts ea ON u.id = ea.user_id 
     WHERE u.status = 'epis' AND ea.status = 'active' 
     LIMIT 1"
);

if ($epis_accounts) {
    $epis_account = $epis_accounts[0];
    $epis_code = $epis_account['affiliate_code'] ?? $epis_account['referral_code'];
    
    echo "<div class=\"scenario\">";
    echo "<h3>Testing dengan EPIS: {$epis_account['name']} (Kode: {$epis_code})</h3>";
    
    $result2 = epic_enhanced_referral_processing($epis_code);
    
    if ($result2['success']) {
        echo "<div class=\"success\">✅ <strong>Berhasil:</strong> {$result2['message']}</div>";
        echo "<div class=\"info\">";
        echo "<strong>Detail Scenario:</strong> {$result2['scenario']}<br>";
        echo "<strong>EPIS Account:</strong> {$result2['referrer']['name']} ({$result2['referrer']['email']})<br>";
        echo "<strong>Auto Integration:</strong> " . ($result2['auto_integration'] ? 'Ya' : 'Tidak') . "<br>";
        
        if (isset($result2['epis_account'])) {
            $capacity = $result2['epis_account']['max_epic_recruits'] == 0 ? 'Unlimited' : 
                       "{$result2['epis_account']['current_epic_count']}/{$result2['epis_account']['max_epic_recruits']}";
            echo "<strong>Kapasitas EPIS:</strong> {$capacity}<br>";
        }
        echo "</div>";
    } else {
        echo "<div class=\"error\">❌ <strong>Gagal:</strong> {$result2['message']}</div>";
    }
    echo "</div>";
} else {
    echo "<div class=\"warning\">⚠️ Tidak ada EPIS account aktif untuk testing</div>";
}
echo "</div>";

// Scenario 3: EPIC Standalone
echo "<div class=\"test-section\">";
echo "<h2>⭐ Scenario 3: EPIC Account Standalone (Tanpa EPIS Supervisor)</h2>";

// Cari EPIC account tanpa EPIS supervisor
$epic_standalone = db()->selectOne(
    "SELECT * FROM epic_users 
     WHERE status = 'epic' AND (epis_supervisor_id IS NULL OR epis_supervisor_id = 0) 
     LIMIT 1"
);

if ($epic_standalone) {
    $epic_standalone_code = $epic_standalone['affiliate_code'] ?? $epic_standalone['referral_code'];
    
    echo "<div class=\"scenario\">";
    echo "<h3>Testing dengan EPIC: {$epic_standalone['name']} (Kode: {$epic_standalone_code})</h3>";
    
    $result3 = epic_enhanced_referral_processing($epic_standalone_code);
    
    if ($result3['success']) {
        echo "<div class=\"success\">✅ <strong>Berhasil:</strong> {$result3['message']}</div>";
        echo "<div class=\"info\">";
        echo "<strong>Detail Scenario:</strong> {$result3['scenario']}<br>";
        echo "<strong>EPIC Account:</strong> {$result3['referrer']['name']} ({$result3['referrer']['email']})<br>";
        echo "<strong>Auto Integration:</strong> " . ($result3['auto_integration'] ? 'Ya' : 'Tidak') . "<br>";
        echo "<strong>EPIS Supervisor:</strong> " . (isset($result3['epis_supervisor']) ? $result3['epis_supervisor']['name'] : 'Tidak ada') . "<br>";
        echo "</div>";
    } else {
        echo "<div class=\"error\">❌ <strong>Gagal:</strong> {$result3['message']}</div>";
    }
    echo "</div>";
} else {
    echo "<div class=\"warning\">⚠️ Tidak ada EPIC account standalone untuk testing</div>";
}
echo "</div>";

// Scenario 4: Invalid Referral Code
echo "<div class=\"test-section\">";
echo "<h2>❌ Scenario 4: Invalid Referral Code</h2>";

$invalid_codes = ['INVALID123', 'NOTFOUND', ''];

foreach ($invalid_codes as $invalid_code) {
    echo "<div class=\"scenario\">";
    echo "<h3>Testing dengan kode invalid: '{$invalid_code}'</h3>";
    
    $result4 = epic_enhanced_referral_processing($invalid_code);
    
    if (!$result4['success']) {
        echo "<div class=\"error\">✅ <strong>Expected Failure:</strong> {$result4['message']}</div>";
        echo "<div class=\"info\"><strong>Scenario:</strong> {$result4['scenario']}</div>";
    } else {
        echo "<div class=\"warning\">⚠️ <strong>Unexpected Success:</strong> Seharusnya gagal untuk kode invalid</div>";
    }
    echo "</div>";
}
echo "</div>";

// Test Cookie Functionality
echo "<div class=\"test-section\">";
echo "<h2>🍪 Test Cookie Tracking Functionality</h2>";

echo "<div class=\"scenario\">";
echo "<h3>Testing Cookie Set/Get untuk Referral Tracking</h3>";

// Test set cookie
$test_referral_code = $test_codes['epic_with_epis'];
$referrer = epic_get_user_by_affiliate_code($test_referral_code) ?? epic_get_user_by_referral_code($test_referral_code);

if ($referrer) {
    // Set cookie dengan EPIS info
    $epis_info = null;
    if ($referrer['status'] === 'epic' && !empty($referrer['epis_supervisor_id'])) {
        $epis_supervisor = epic_get_user($referrer['epis_supervisor_id']);
        $epis_account = epic_get_epis_account($referrer['epis_supervisor_id']);
        
        if ($epis_supervisor && $epis_account) {
            $epis_info = [
                'supervisor' => $epis_supervisor,
                'account' => $epis_account
            ];
        }
    }
    
    $cookie_data = epic_set_referral_epis_cookie($test_referral_code, $referrer['name'], $epis_info);
    
    echo "<div class=\"success\">✅ Cookie berhasil di-set</div>";
    echo "<div class=\"info\">";
    echo "<strong>Cookie Data:</strong><br>";
    echo "<div class=\"code\">" . json_encode($cookie_data, JSON_PRETTY_PRINT) . "</div>";
    echo "</div>";
    
    // Test get cookie
    $retrieved_cookie = epic_get_referral_epis_tracking();
    
    if ($retrieved_cookie) {
        echo "<div class=\"success\">✅ Cookie berhasil di-retrieve</div>";
        echo "<div class=\"info\">";
        echo "<strong>Retrieved Cookie Data:</strong><br>";
        echo "<div class=\"code\">" . json_encode($retrieved_cookie, JSON_PRETTY_PRINT) . "</div>";
        echo "</div>";
    } else {
        echo "<div class=\"error\">❌ Gagal retrieve cookie</div>";
    }
} else {
    echo "<div class=\"error\">❌ Referrer tidak ditemukan untuk testing cookie</div>";
}
echo "</div>";
echo "</div>";

// Summary
echo "<div class=\"test-section\">";
echo "<h2>📊 Test Summary</h2>";

$total_tests = 4;
$passed_tests = 0;

if (isset($result1) && $result1['success']) $passed_tests++;
if (isset($result2) && $result2['success']) $passed_tests++;
if (isset($result3) && $result3['success']) $passed_tests++;
if (isset($result4) && !$result4['success']) $passed_tests++; // Invalid should fail

echo "<div class=\"info\">";
echo "<strong>Total Tests:</strong> {$total_tests}<br>";
echo "<strong>Passed:</strong> {$passed_tests}<br>";
echo "<strong>Success Rate:</strong> " . round(($passed_tests / $total_tests) * 100, 2) . "%<br>";
echo "</div>";

if ($passed_tests === $total_tests) {
    echo "<div class=\"success\">🎉 <strong>Semua test berhasil!</strong> Enhanced referral system siap digunakan.</div>";
} else {
    echo "<div class=\"warning\">⚠️ <strong>Beberapa test gagal.</strong> Perlu review implementasi.</div>";
}

echo "<div class=\"info\">";
echo "<strong>Next Steps:</strong><br>";
echo "1. Update template registrasi untuk menampilkan info referral yang enhanced<br>";
echo "2. Test registrasi end-to-end dengan berbagai skenario<br>";
echo "3. Monitor log aktivitas untuk memastikan tracking berfungsi<br>";
echo "4. Update dokumentasi sistem referral<br>";
echo "</div>";

echo "</div>";

echo "</div>";
?>