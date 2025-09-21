<?php
echo "=== TEST API REFERRAL SIMPLE ===\n\n";

$referral_code = 'IJUU9WA8';

// Test API endpoint via cURL
echo "Test API endpoint via cURL:\n";
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

echo "URL: $api_url\n";
echo "POST Data: $post_data\n";
echo "HTTP Code: $http_code\n";

if ($curl_error) {
    echo "cURL Error: $curl_error\n";
} else {
    echo "Response: $response\n";
    
    // Try to decode JSON
    $json_data = json_decode($response, true);
    if ($json_data) {
        echo "JSON Valid: ✓\n";
        echo "Data:\n";
        foreach ($json_data as $key => $value) {
            echo "  - $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    } else {
        echo "JSON Valid: ✗\n";
    }
}

echo "\n=== SELESAI ===\n";
?>