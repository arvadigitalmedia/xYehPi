<?php
/**
 * Test Script untuk API Check Referral
 * Untuk testing manual endpoint /api/check-referral.php
 */

// Include bootstrap untuk akses database
require_once 'bootstrap.php';

// Test cases
$test_cases = [
    'ADMIN001' => 'Kode admin default',
    'TEST123' => 'Kode test yang mungkin ada',
    'INVALID' => 'Kode yang tidak ada',
    '' => 'Kode kosong'
];

echo "<h2>Test API Check Referral</h2>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-case { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    .code { background-color: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; }
</style>\n";

foreach ($test_cases as $code => $description) {
    echo "<div class='test-case'>\n";
    echo "<h3>Test: {$description}</h3>\n";
    echo "<p><strong>Kode:</strong> " . ($code ?: '(kosong)') . "</p>\n";
    
    // Simulate API call
    $url = "http://localhost:8000/api/check-referral.php";
    $data = ['referral_code' => $code];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        echo "<div class='error'>";
        echo "<p><strong>Error:</strong> Tidak dapat mengakses API</p>";
        echo "</div>";
    } else {
        $response = json_decode($result, true);
        
        if ($response) {
            if ($response['success']) {
                echo "<div class='success'>";
                echo "<p><strong>Status:</strong> Berhasil ✅</p>";
                
                if (isset($response['data']['sponsor'])) {
                    $sponsor = $response['data']['sponsor'];
                    echo "<p><strong>Sponsor:</strong> {$sponsor['name']} ({$sponsor['email']})</p>";
                }
                
                if (isset($response['data']['epis_supervisor'])) {
                    $supervisor = $response['data']['epis_supervisor'];
                    echo "<p><strong>EPIS Supervisor:</strong> {$supervisor['name']} ({$supervisor['email']})</p>";
                } else {
                    echo "<p><strong>EPIS Supervisor:</strong> Tidak ada</p>";
                }
                
                echo "</div>";
            } else {
                echo "<div class='error'>";
                echo "<p><strong>Status:</strong> Gagal ❌</p>";
                echo "<p><strong>Error:</strong> {$response['error']}</p>";
                echo "</div>";
            }
            
            echo "<div class='code'>";
            echo "<strong>Response JSON:</strong><br>";
            echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<p><strong>Error:</strong> Response bukan JSON valid</p>";
            echo "<p><strong>Raw Response:</strong> " . htmlspecialchars($result) . "</p>";
            echo "</div>";
        }
    }
    
    echo "</div>\n";
}

// Test database connection
echo "<div class='test-case'>\n";
echo "<h3>Test Database Connection</h3>\n";

try {
    $users = db()->select("SELECT referral_code, name, email, status FROM " . db()->table('users') . " LIMIT 5");
    
    echo "<div class='success'>";
    echo "<p><strong>Status:</strong> Database OK ✅</p>";
    echo "<p><strong>Sample Users:</strong></p>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Referral Code</th><th>Name</th><th>Email</th><th>Status</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['referral_code']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p><strong>Status:</strong> Database Error ❌</p>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>\n";

echo "<p><em>Test selesai pada: " . date('Y-m-d H:i:s') . "</em></p>";
?>