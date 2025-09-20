<?php
/**
 * Debug Register URL Parameter - Test $_GET parameter
 */
define('EPIC_LOADED', true);
define('EPIC_INIT', true);
require_once 'bootstrap.php';

echo "<h2>🔍 Debug Register URL Parameter</h2>";

// Simulate $_GET parameter
$_GET['ref'] = '03KIPMLQ';

echo "<h3>1. Simulasi $_GET parameter</h3>";
echo "GET ref: " . ($_GET['ref'] ?? 'kosong') . "<br>";

echo "<h3>2. Logika dari template register.php</h3>";

// Get referral tracking from cookies/session or URL parameter
$tracking = epic_get_referral_tracking();
$referral_code = $_GET['ref'] ?? $_POST['referral_code'] ?? ($tracking ? $tracking['code'] : '');

echo "Tracking: " . ($tracking ? print_r($tracking, true) : 'kosong') . "<br>";
echo "Referral code final: $referral_code<br>";

$referrer_info = null;
$require_referral = epic_setting('require_referral', '0') == '1';
$show_referral_input = epic_setting('show_referral_input', '1') == '1';

// Get referrer information if referral code exists
if ($referral_code) {
    echo "<h3>3. Memanggil epic_get_referrer_info</h3>";
    $referrer = epic_get_referrer_info($referral_code);
    if ($referrer) {
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
        
        echo "✅ referrer_info terbentuk dengan data EPIS<br>";
    } else {
        echo "❌ Referrer tidak ditemukan<br>";
    }
}

echo "<h3>4. Kondisi untuk menampilkan card referral</h3>";
echo "referrer_info: " . ($referrer_info ? "SET" : "NULL") . "<br>";

if ($referrer_info) {
    echo "<h3>5. ✅ CARD REFERRAL AKAN DITAMPILKAN</h3>";
    echo "Nama Referrer: " . $referrer_info['name'] . "<br>";
    echo "Kode Referral: " . $referrer_info['code'] . "<br>";
    echo "EPIS Supervisor: " . ($referrer_info['epis_supervisor_name'] ?? 'Tidak ada') . "<br>";
    echo "EPIS Code: " . ($referrer_info['epis_code'] ?? 'N/A') . "<br>";
    echo "Territory: " . ($referrer_info['territory_name'] ?? 'N/A') . "<br>";
} else {
    echo "<h3>5. ❌ CARD REFERRAL TIDAK AKAN DITAMPILKAN</h3>";
}

echo "<h3>6. HTML Output untuk Card Referral</h3>";
if ($referrer_info) {
    echo "<div style='background: #1a1a1a; color: white; padding: 20px; border-radius: 8px; margin: 10px 0;'>";
    echo "<h4>✅ Referral Terdeteksi</h4>";
    echo "<p>Anda akan terdaftar sebagai referral dari:</p>";
    echo "<p><strong>" . htmlspecialchars($referrer_info['name']) . "</strong></p>";
    echo "<p>Kode Referral: " . htmlspecialchars($referrer_info['code']) . "</p>";
    echo "<h5>Data EPIS Account</h5>";
    echo "<p>Nama EPIS Account: " . htmlspecialchars($referrer_info['epis_supervisor_name'] ?? 'Tidak ada EPIS Supervisor') . "</p>";
    echo "<p>ID EPIS Account: " . htmlspecialchars($referrer_info['epis_code'] ?? 'N/A') . "</p>";
    echo "<p>Territory: " . htmlspecialchars($referrer_info['territory_name'] ?? 'N/A') . "</p>";
    echo "</div>";
} else {
    echo "<p>❌ Tidak ada card referral yang ditampilkan</p>";
}
?>