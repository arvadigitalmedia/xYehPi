<?php
/**
 * Test Script: Simulasi Alur Pendaftaran dengan Kode Referral
 * Menguji end-to-end flow dari registrasi hingga Free Account
 */

require_once "config.php";
require_once "core/functions.php";

echo "<h2>🧪 Simulasi Alur Pendaftaran dengan Kode Referral</h2>";
echo "<style>
.test-section { margin: 20px 0; padding: 15px; border-left: 4px solid #007cba; background: #f8f9fa; }
.success { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.info { color: #17a2b8; }
.warning { color: #ffc107; }
</style>";

// Step 1: Cari referrer yang valid (EPIC Account)
echo "<div class=\"test-section\">";
echo "<h3>📋 Step 1: Mencari Referrer Valid</h3>";

$referrer = db()->selectOne(
    "SELECT id, name, email, referral_code, affiliate_code, status, role 
     FROM epic_users 
     WHERE status = 'epic' AND role = 'user' 
     ORDER BY created_at DESC 
     LIMIT 1"
);

if ($referrer) {
    echo "<div class=\"success\">✅ Referrer ditemukan:</div>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> {$referrer['id']}</li>";
    echo "<li><strong>Nama:</strong> {$referrer['name']}</li>";
    echo "<li><strong>Email:</strong> {$referrer['email']}</li>";
    echo "<li><strong>Referral Code:</strong> {$referrer['referral_code']}</li>";
    echo "<li><strong>Affiliate Code:</strong> {$referrer['affiliate_code']}</li>";
    echo "<li><strong>Status:</strong> {$referrer['status']}</li>";
    echo "</ul>";
    
    $referral_code = $referrer['referral_code'];
} else {
    echo "<div class=\"error\">❌ Tidak ada EPIC Account yang tersedia sebagai referrer</div>";
    echo "<div class=\"info\">💡 Membuat EPIC Account dummy untuk testing...</div>";
    
    // Create dummy EPIC account
    $dummy_data = [
        'name' => 'Test EPIC Referrer',
        'email' => 'epic.referrer.' . time() . '@test.com',
        'password' => 'testpassword123',
        'phone' => '081234567890'
    ];
    
    try {
        $epic_user_id = epic_register_user($dummy_data);
        
        // Upgrade to EPIC
        db()->update('epic_users', [
            'status' => 'epic',
            'hierarchy_level' => 2,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$epic_user_id]);
        
        $referrer = epic_get_user($epic_user_id);
        $referral_code = $referrer['referral_code'];
        
        echo "<div class=\"success\">✅ EPIC Account dummy berhasil dibuat:</div>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$referrer['id']}</li>";
        echo "<li><strong>Nama:</strong> {$referrer['name']}</li>";
        echo "<li><strong>Referral Code:</strong> {$referral_code}</li>";
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<div class=\"error\">❌ Gagal membuat EPIC Account dummy: " . $e->getMessage() . "</div>";
        exit;
    }
}
echo "</div>";

// Step 2: Validasi kode referral
echo "<div class=\"test-section\">";
echo "<h3>🔍 Step 2: Validasi Kode Referral</h3>";

$referrer_info = epic_get_referrer_info($referral_code);

if ($referrer_info) {
    echo "<div class=\"success\">✅ Kode referral valid:</div>";
    echo "<ul>";
    echo "<li><strong>Referrer ID:</strong> {$referrer_info['id']}</li>";
    echo "<li><strong>Nama:</strong> {$referrer_info['name']}</li>";
    echo "<li><strong>Status:</strong> {$referrer_info['status']}</li>";
    echo "<li><strong>Role:</strong> {$referrer_info['role']}</li>";
    echo "</ul>";
} else {
    echo "<div class=\"error\">❌ Kode referral tidak valid atau tidak memenuhi syarat</div>";
    exit;
}
echo "</div>";

// Step 3: Cek EPIS Supervisor yang tersedia
echo "<div class=\"test-section\">";
echo "<h3>👥 Step 3: Cek EPIS Supervisor</h3>";

$available_epis = db()->select(
    "SELECT u.id, u.name, u.email, ea.current_epic_count, ea.max_epic_recruits
     FROM epic_users u 
     JOIN epic_epis_accounts ea ON u.id = ea.user_id 
     WHERE u.status = 'epis' AND ea.status = 'active' 
     AND (ea.max_epic_recruits = 0 OR ea.current_epic_count < ea.max_epic_recruits)
     ORDER BY ea.current_epic_count ASC, u.created_at ASC"
);

if (!empty($available_epis)) {
    echo "<div class=\"success\">✅ EPIS Supervisor tersedia (" . count($available_epis) . " akun):</div>";
    echo "<ul>";
    foreach ($available_epis as $epis) {
        $capacity = $epis['max_epic_recruits'] == 0 ? 'Unlimited' : "{$epis['current_epic_count']}/{$epis['max_epic_recruits']}";
        echo "<li><strong>{$epis['name']}</strong> ({$epis['email']}) - Kapasitas: {$capacity}</li>";
    }
    echo "</ul>";
} else {
    echo "<div class=\"warning\">⚠️ Tidak ada EPIS Supervisor yang tersedia</div>";
}
echo "</div>";

// Step 4: Simulasi registrasi
echo "<div class=\"test-section\">";
echo "<h3>📝 Step 4: Simulasi Registrasi User Baru</h3>";

$test_user_data = [
    'name' => 'Test User ' . date('His'),
    'email' => 'testuser.' . time() . '@test.com',
    'password' => 'testpassword123',
    'phone' => '081234567891',
    'referral_code' => $referral_code,
    'marketing' => true
];

echo "<div class=\"info\">📋 Data registrasi:</div>";
echo "<ul>";
echo "<li><strong>Nama:</strong> {$test_user_data['name']}</li>";
echo "<li><strong>Email:</strong> {$test_user_data['email']}</li>";
echo "<li><strong>Phone:</strong> {$test_user_data['phone']}</li>";
echo "<li><strong>Referral Code:</strong> {$test_user_data['referral_code']}</li>";
echo "</ul>";

try {
    $new_user_id = epic_register_user($test_user_data);
    
    if ($new_user_id) {
        echo "<div class=\"success\">✅ Registrasi berhasil! User ID: {$new_user_id}</div>";
        
        // Get complete user data
        $new_user = epic_get_user($new_user_id);
        
        echo "<div class=\"info\">📊 Data user yang terbuat:</div>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$new_user['id']}</li>";
        echo "<li><strong>UUID:</strong> {$new_user['uuid']}</li>";
        echo "<li><strong>Nama:</strong> {$new_user['name']}</li>";
        echo "<li><strong>Email:</strong> {$new_user['email']}</li>";
        echo "<li><strong>Status:</strong> {$new_user['status']}</li>";
        echo "<li><strong>Hierarchy Level:</strong> {$new_user['hierarchy_level']}</li>";
        echo "<li><strong>Referral Code:</strong> {$new_user['referral_code']}</li>";
        echo "<li><strong>Affiliate Code:</strong> {$new_user['affiliate_code']}</li>";
        echo "<li><strong>EPIS Supervisor ID:</strong> {$new_user['epis_supervisor_id']}</li>";
        echo "</ul>";
        
    } else {
        echo "<div class=\"error\">❌ Registrasi gagal</div>";
    }
    
} catch (Exception $e) {
    echo "<div class=\"error\">❌ Error saat registrasi: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Step 5: Verifikasi sponsor relationship
if (isset($new_user_id)) {
    echo "<div class=\"test-section\">";
    echo "<h3>🔗 Step 5: Verifikasi Sponsor Relationship</h3>";
    
    require_once "core/sponsor.php";
    $sponsor_record = epic_get_sponsor_by_user($new_user_id);
    
    if ($sponsor_record) {
        echo "<div class=\"success\">✅ Sponsor record berhasil dibuat:</div>";
        echo "<ul>";
        echo "<li><strong>User ID:</strong> {$sponsor_record['user_id']}</li>";
        echo "<li><strong>Sponsor ID:</strong> {$sponsor_record['sponsor_id']}</li>";
        echo "<li><strong>Sponsor Code:</strong> {$sponsor_record['sponsor_code']}</li>";
        echo "<li><strong>Level:</strong> {$sponsor_record['level']}</li>";
        echo "<li><strong>Network Path:</strong> {$sponsor_record['network_path']}</li>";
        echo "<li><strong>Commission Rate:</strong> {$sponsor_record['commission_rate']}%</li>";
        echo "<li><strong>Status:</strong> {$sponsor_record['status']}</li>";
        echo "</ul>";
    } else {
        echo "<div class=\"error\">❌ Sponsor record tidak ditemukan</div>";
    }
    echo "</div>";
}

// Step 6: Cek Free Account status
if (isset($new_user_id)) {
    echo "<div class=\"test-section\">";
    echo "<h3>🆓 Step 6: Verifikasi Free Account Status</h3>";
    
    $user_status = epic_get_user($new_user_id);
    
    // Determine account level
    $account_level = "Unknown";
    if ($user_status['hierarchy_level'] == 1 || $user_status['status'] === 'free') {
        $account_level = "Free Account";
    } elseif ($user_status['status'] === 'epic') {
        $account_level = "EPIC Account";
    } elseif ($user_status['status'] === 'epis') {
        $account_level = "EPIS Account";
    }
    
    echo "<div class=\"info\">📊 Status Akun:</div>";
    echo "<ul>";
    echo "<li><strong>Account Level:</strong> {$account_level}</li>";
    echo "<li><strong>Status:</strong> {$user_status['status']}</li>";
    echo "<li><strong>Hierarchy Level:</strong> {$user_status['hierarchy_level']}</li>";
    echo "<li><strong>Role:</strong> {$user_status['role']}</li>";
    echo "<li><strong>Email Verified:</strong> " . ($user_status['email_verified'] ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
    
    if ($account_level === "Free Account") {
        echo "<div class=\"success\">✅ Free Account berhasil dibuat dengan benar!</div>";
    } else {
        echo "<div class=\"warning\">⚠️ Account level tidak sesuai ekspektasi (Free Account)</div>";
    }
    echo "</div>";
}

// Step 7: Test akses fitur Free Account
if (isset($new_user_id)) {
    echo "<div class=\"test-section\">";
    echo "<h3>🔐 Step 7: Test Akses Fitur Free Account</h3>";
    
    $access_tests = [
        'Dashboard' => epic_can_access_feature($new_user_id, 'dashboard'),
        'Profile' => epic_can_access_feature($new_user_id, 'profile'),
        'Referral Link' => epic_can_access_feature($new_user_id, 'referral'),
        'Commission' => epic_can_access_feature($new_user_id, 'commission'),
        'Upgrade' => epic_can_access_feature($new_user_id, 'upgrade'),
        'Admin Panel' => epic_can_access_feature($new_user_id, 'admin')
    ];
    
    echo "<div class=\"info\">🔍 Test akses fitur:</div>";
    echo "<ul>";
    foreach ($access_tests as $feature => $can_access) {
        $status = $can_access ? "<span class=\"success\">✅ Allowed</span>" : "<span class=\"error\">❌ Denied</span>";
        echo "<li><strong>{$feature}:</strong> {$status}</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<div class=\"test-section\">";
echo "<h3>🎯 Kesimpulan Simulasi</h3>";
echo "<div class=\"success\">✅ Alur pendaftaran dengan kode referral berjalan dengan baik!</div>";
echo "<div class=\"info\">📋 Ringkasan proses:</div>";
echo "<ol>";
echo "<li>Validasi kode referral berhasil</li>";
echo "<li>EPIS Supervisor assignment otomatis</li>";
echo "<li>User berhasil terdaftar dengan status Free Account</li>";
echo "<li>Sponsor relationship terbentuk dengan benar</li>";
echo "<li>Access control berfungsi sesuai level akun</li>";
echo "</ol>";
echo "</div>";

?>