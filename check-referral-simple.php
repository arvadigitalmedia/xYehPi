<?php
require_once 'bootstrap.php';

$referral_code = 'IJUU9WA8';

echo "Checking referral code: $referral_code\n\n";

try {
    // Gunakan fungsi database EPIC Hub
    $db = db();
    
    // Cek di database menggunakan tabel yang benar
    $user = $db->selectOne("SELECT * FROM epic_users WHERE referral_code = ?", [$referral_code]);

    if ($user) {
        echo "FOUND:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Name: " . $user['name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Referral Code: " . $user['referral_code'] . "\n";
        echo "EPIS Supervisor ID: " . $user['epis_supervisor_id'] . "\n";
        echo "Registration Source: " . $user['registration_source'] . "\n";
        echo "Can Recruit EPIC: " . ($user['can_recruit_epic'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "NOT FOUND\n\n";
        
        // Show available codes
        echo "Available referral codes:\n";
        $codes = $db->select("SELECT referral_code, name, status, role FROM epic_users WHERE referral_code IS NOT NULL LIMIT 10");
        foreach ($codes as $row) {
            echo "- " . $row['referral_code'] . " (" . $row['name'] . " - " . $row['status'] . "/" . $row['role'] . ")\n";
        }
    }
    
    // Test API endpoint
    echo "\n\nTesting API endpoint...\n";
    
    $api_data = json_encode(['referral_code' => $referral_code]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/check-referral.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $api_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    echo "Response: $response\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>