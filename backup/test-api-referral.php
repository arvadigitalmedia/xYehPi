<?php
define('EPIC_LOADED', true);
require_once 'config/database.php';
require_once 'core/functions.php';

echo "=== TEST API REFERRAL ===\n\n";

$referral_code = 'IJUU9WA8';

// 1. Test fungsi epic_get_referrer_info
echo "1. Test fungsi epic_get_referrer_info:\n";
$referrer_info = epic_get_referrer_info($referral_code);

if ($referrer_info) {
    echo "   ✓ Referrer ditemukan:\n";
    echo "   - ID: {$referrer_info['id']}\n";
    echo "   - Nama: {$referrer_info['name']}\n";
    echo "   - Email: {$referrer_info['email']}\n";
    echo "   - Kode Referral: {$referrer_info['referral_code']}\n";
    echo "   - EPIS Supervisor ID: " . ($referrer_info['epis_supervisor_id'] ?: 'NULL') . "\n";
    echo "   - EPIS Supervisor Name: " . ($referrer_info['epis_supervisor_name'] ?: 'NULL') . "\n";
    echo "   - EPIS Code: " . ($referrer_info['epis_code'] ?: 'NULL') . "\n";
    echo "   - Territory: " . ($referrer_info['territory_name'] ?: 'NULL') . "\n";
    echo "   - Status: {$referrer_info['status']}\n";
    echo "   - Role: {$referrer_info['role']}\n\n";
} else {
    echo "   ✗ Referrer tidak ditemukan\n\n";
}

// 2. Test API endpoint via cURL
echo "2. Test API endpoint via cURL:\n";
$api_url = "http://localhost:8080/api/check-referral.php";
$post_data = json_encode(['referral_code' => $referral_code]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "   URL: $api_url\n";
echo "   HTTP Code: $http_code\n";

if ($curl_error) {
    echo "   cURL Error: $curl_error\n";
} else {
    echo "   Response: $response\n";
    
    // Try to decode JSON
    $json_data = json_decode($response, true);
    if ($json_data) {
        echo "   JSON Valid: ✓\n";
        echo "   Data:\n";
        foreach ($json_data as $key => $value) {
            echo "     - $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    } else {
        echo "   JSON Valid: ✗\n";
    }
}

echo "\n=== SELESAI ===\n";
?>