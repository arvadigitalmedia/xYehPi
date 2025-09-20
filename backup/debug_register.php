<?php
define('EPIC_LOADED', true);
define('EPIC_INIT', true);
require_once 'config/config.php';
require_once 'bootstrap.php';

echo "Debug Register Page Logic...\n\n";

// Simulate GET parameter
$_GET['ref'] = '03KIPMLQ';

// Get referral tracking from cookies/session or URL parameter
$tracking = epic_get_referral_tracking();
$referral_code = $_GET['ref'] ?? $_POST['referral_code'] ?? ($tracking ? $tracking['code'] : '');
$referrer_info = null;

echo "Referral code: " . ($referral_code ?: 'EMPTY') . "\n";

// Get referrer information if referral code exists
if ($referral_code) {
    echo "Getting referrer info for code: $referral_code\n";
    $referrer = epic_get_referrer_info($referral_code);
    if ($referrer) {
        echo "Referrer found, creating referrer_info array...\n";
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
        
        echo "Referrer info created successfully:\n";
        print_r($referrer_info);
    } else {
        echo "No referrer found for code: $referral_code\n";
    }
} else {
    echo "No referral code provided\n";
}

echo "\nFinal referrer_info status: " . ($referrer_info ? 'SET' : 'NULL') . "\n";
?>