<?php
/**
 * Debug Register Page - Test referrer_info formation
 */
define('EPIC_LOADED', true);
define('EPIC_INIT', true);
require_once 'bootstrap.php';

$referral_code = '03KIPMLQ';

echo "<h2>🔍 Debug Register Page untuk Kode: $referral_code</h2>";

// Simulate register page logic
$tracking = epic_get_referral_tracking();
$referrer_info = null;
$require_referral = epic_setting('require_referral', '0') == '1';
$show_referral_input = epic_setting('show_referral_input', '1') == '1';

echo "<h3>1. Tracking dari Cookie/Session</h3>";
echo "Tracking: " . ($tracking ? print_r($tracking, true) : 'kosong') . "<br>";

echo "<h3>2. Referral Code dari URL</h3>";
echo "Referral Code: $referral_code<br>";

// Get referrer information if referral code exists
if ($referral_code) {
    echo "<h3>3. Memanggil epic_get_referrer_info</h3>";
    $referrer = epic_get_referrer_info($referral_code);
    
    if ($referrer) {
        echo "✅ Referrer ditemukan:<br>";
        echo "<pre>" . print_r($referrer, true) . "</pre>";
        
        echo "<h3>4. Membentuk referrer_info array</h3>";
        $referrer_info = [
            'id' => $referrer['id'],
            'name' => $referrer['name'],
            'email' => $referrer['email'],
            'code' => $referral_code,
            'status' => $referrer['status'],
            'role' => $referrer['role'],
            'tracking_source' => $tracking ? $tracking['source'] : 'url',
            'tracking_time' => $tracking ? date('d/m/Y H:i', $tracking['timestamp']) : date('d/m/Y H:i'),
            // EPIS Supervisor data
            'epis_supervisor_id' => $referrer['epis_supervisor_id'] ?? null,
            'epis_supervisor_name' => $referrer['epis_supervisor_name'] ?? null,
            'epis_supervisor_email' => $referrer['epis_supervisor_email'] ?? null,
            'epis_code' => $referrer['epis_code'] ?? null,
            'territory_name' => $referrer['territory_name'] ?? null
        ];
        
        echo "✅ referrer_info terbentuk:<br>";
        echo "<pre>" . print_r($referrer_info, true) . "</pre>";
        
        echo "<h3>5. Cek Data EPIS</h3>";
        if (!empty($referrer_info['epis_supervisor_name'])) {
            echo "✅ EPIS Supervisor Name: " . $referrer_info['epis_supervisor_name'] . "<br>";
            echo "✅ EPIS Code: " . ($referrer_info['epis_code'] ?? 'N/A') . "<br>";
            echo "✅ Territory: " . ($referrer_info['territory_name'] ?? 'N/A') . "<br>";
        } else {
            echo "❌ Tidak ada data EPIS supervisor<br>";
        }
        
    } else {
        echo "❌ Referrer tidak ditemukan atau tidak memenuhi syarat<br>";
    }
} else {
    echo "❌ Tidak ada referral code<br>";
}

echo "<h3>6. Status Akhir</h3>";
echo "referrer_info: " . ($referrer_info ? "SET" : "NULL") . "<br>";
echo "require_referral: " . ($require_referral ? "YA" : "TIDAK") . "<br>";
echo "show_referral_input: " . ($show_referral_input ? "YA" : "TIDAK") . "<br>";
?>