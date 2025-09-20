<?php
/**
 * Debug Script: Check Referral Code 03KIPMLQ
 */

require_once 'bootstrap.php';

echo "<h2>🔍 Debug Kode Referral: 03KIPMLQ</h2>";

$referral_code = '03KIPMLQ';

echo "<h3>1. Cek di Tabel epic_users</h3>";

// Check by referral_code
$user_by_referral = db()->selectOne(
    "SELECT id, name, email, referral_code, affiliate_code, status, role FROM epic_users WHERE referral_code = ?",
    [$referral_code]
);

echo "<strong>Cek berdasarkan referral_code:</strong><br>";
if ($user_by_referral) {
    echo "<pre>" . print_r($user_by_referral, true) . "</pre>";
} else {
    echo "❌ Tidak ditemukan di kolom referral_code<br>";
}

// Check by affiliate_code
$user_by_affiliate = db()->selectOne(
    "SELECT id, name, email, referral_code, affiliate_code, status, role FROM epic_users WHERE affiliate_code = ?",
    [$referral_code]
);

echo "<strong>Cek berdasarkan affiliate_code:</strong><br>";
if ($user_by_affiliate) {
    echo "<pre>" . print_r($user_by_affiliate, true) . "</pre>";
} else {
    echo "❌ Tidak ditemukan di kolom affiliate_code<br>";
}

echo "<h3>2. Test Fungsi epic_get_referrer_info</h3>";
$referrer_info = epic_get_referrer_info($referral_code);

if ($referrer_info) {
    echo "✅ Fungsi epic_get_referrer_info berhasil:<br>";
    echo "<pre>" . print_r($referrer_info, true) . "</pre>";
} else {
    echo "❌ Fungsi epic_get_referrer_info gagal<br>";
}

echo "<h3>3. Cek Setting epic_account_only</h3>";
$epic_account_only = epic_setting('epic_account_only', '1');
echo "Setting epic_account_only: " . ($epic_account_only == '1' ? 'Aktif (Hanya EPIC Account)' : 'Nonaktif') . "<br>";

echo "<h3>4. Cek Semua User dengan Status Premium</h3>";
$premium_users = db()->select(
    "SELECT id, name, email, referral_code, affiliate_code, status, role FROM epic_users WHERE status = 'premium' LIMIT 10"
);

if ($premium_users) {
    echo "✅ User dengan status premium:<br>";
    foreach ($premium_users as $user) {
        echo "- ID: {$user['id']}, Name: {$user['name']}, Referral: {$user['referral_code']}, Affiliate: {$user['affiliate_code']}<br>";
    }
} else {
    echo "❌ Tidak ada user dengan status premium<br>";
}

echo "<h3>5. Cek Semua Kode Referral yang Mirip</h3>";
$similar_codes = db()->select(
    "SELECT id, name, email, referral_code, affiliate_code, status, role FROM epic_users WHERE referral_code LIKE '%03KIP%' OR affiliate_code LIKE '%03KIP%'"
);

if ($similar_codes) {
    echo "✅ Kode referral yang mirip:<br>";
    foreach ($similar_codes as $user) {
        echo "- ID: {$user['id']}, Name: {$user['name']}, Referral: {$user['referral_code']}, Affiliate: {$user['affiliate_code']}, Status: {$user['status']}<br>";
    }
} else {
    echo "❌ Tidak ada kode referral yang mirip<br>";
}

echo "<h3>6. Cek EPIS Supervisor untuk User dengan Kode Referral</h3>";
if ($user_by_referral && !empty($user_by_referral['epis_supervisor_id'])) {
    $epis_supervisor = db()->selectOne(
        "SELECT id, name, email, status FROM epic_users WHERE id = ?",
        [$user_by_referral['epis_supervisor_id']]
    );
    
    if ($epis_supervisor) {
        echo "✅ EPIS Supervisor ditemukan:<br>";
        echo "<pre>" . print_r($epis_supervisor, true) . "</pre>";
        
        // Check EPIS account
        $epis_account = db()->selectOne(
            "SELECT * FROM epic_epis_accounts WHERE user_id = ?",
            [$user_by_referral['epis_supervisor_id']]
        );
        
        if ($epis_account) {
            echo "✅ EPIS Account data:<br>";
            echo "<pre>" . print_r($epis_account, true) . "</pre>";
        } else {
            echo "❌ EPIS Account data tidak ditemukan<br>";
        }
    } else {
        echo "❌ EPIS Supervisor tidak ditemukan<br>";
    }
} else {
    echo "❌ User tidak memiliki EPIS supervisor<br>";
}

echo "<h3>7. Test Fungsi epic_handle_referral_epis_tracking</h3>";
require_once 'referral-epis-handler.php';
$epis_tracking = epic_handle_referral_epis_tracking($referral_code);

if ($epis_tracking) {
    echo "✅ EPIS tracking berhasil:<br>";
    echo "<pre>" . print_r($epis_tracking, true) . "</pre>";
} else {
    echo "❌ EPIS tracking gagal<br>";
}

echo "<h3>8. Test API Check Referral</h3>";
echo "<script>
async function testReferralAPI() {
    try {
        const response = await fetch('/test-bisnisemasperak/api/check-referral.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                referral_code: '03KIPMLQ'
            })
        });
        
        const result = await response.json();
        document.getElementById('api-result').innerHTML = '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
    } catch (error) {
        document.getElementById('api-result').innerHTML = 'Error: ' + error.message;
    }
}

// Auto test on load
testReferralAPI();
</script>";

echo "<div id='api-result'>Loading API test...</div>";
?>