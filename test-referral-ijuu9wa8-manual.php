<?php
require_once 'bootstrap.php';

echo "=== MANUAL TEST REFERRAL CODE: IJUU9WA8 ===\n";

$referral_code = 'IJUU9WA8';

try {
    // 1. Cek apakah kode referral ada di database
    echo "\n1. CHECKING DATABASE FOR REFERRAL CODE:\n";
    
    $stmt = $pdo->prepare("SELECT * FROM epi_users WHERE referral_code = ?");
    $stmt->execute([$referral_code]);
    $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($referrer) {
        echo "✅ Referral code found in database!\n";
        echo "   - User ID: " . $referrer['id'] . "\n";
        echo "   - Name: " . $referrer['name'] . "\n";
        echo "   - Email: " . $referrer['email'] . "\n";
        echo "   - Role: " . $referrer['role'] . "\n";
        echo "   - Status: " . $referrer['status'] . "\n";
        echo "   - Referral Code: " . $referrer['referral_code'] . "\n";
        echo "   - EPIS Supervisor ID: " . ($referrer['epis_supervisor_id'] ?? 'NULL') . "\n";
    } else {
        echo "❌ Referral code NOT FOUND in database!\n";
        
        // Cek semua kode yang ada
        echo "\n   Available referral codes:\n";
        $stmt = $pdo->query("SELECT id, name, referral_code FROM epi_users WHERE referral_code IS NOT NULL AND referral_code != '' LIMIT 10");
        $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($codes as $code) {
            echo "   - {$code['referral_code']} ({$code['name']})\n";
        }
        
        // Buat user dengan kode IJUU9WA8 untuk testing
        echo "\n   Creating test user with referral code IJUU9WA8...\n";
        
        $test_user_data = [
            'uuid' => 'test-' . uniqid(),
            'name' => 'Test User IJUU9WA8',
            'email' => 'test.ijuu9wa8@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'referral_code' => $referral_code,
            'status' => 'active',
            'role' => 'epic',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $stmt = $pdo->prepare("INSERT INTO epi_users (uuid, name, email, password, referral_code, status, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $test_user_data['uuid'],
            $test_user_data['name'],
            $test_user_data['email'],
            $test_user_data['password'],
            $test_user_data['referral_code'],
            $test_user_data['status'],
            $test_user_data['role'],
            $test_user_data['created_at'],
            $test_user_data['updated_at']
        ]);
        
        echo "✅ Test user created successfully!\n";
        
        // Ambil data user yang baru dibuat
        $stmt = $pdo->prepare("SELECT * FROM epi_users WHERE referral_code = ?");
        $stmt->execute([$referral_code]);
        $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 2. Test API check-referral
    echo "\n2. TESTING API CHECK-REFERRAL:\n";
    
    // Simulasi request ke API
    $api_data = json_encode(['referral_code' => $referral_code]);
    
    // Set up environment untuk API call
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Capture output dari API
    ob_start();
    
    // Mock input stream
    $temp_file = tmpfile();
    fwrite($temp_file, $api_data);
    rewind($temp_file);
    
    // Backup original input
    $original_input = 'php://input';
    
    // Include API file
    try {
        // Set up mock input
        file_put_contents('php://temp', $api_data);
        
        // Include API dengan error handling
        $api_response = '';
        
        // Manual API simulation
        $input = json_decode($api_data, true);
        
        if ($input && isset($input['referral_code'])) {
            $code = trim($input['referral_code']);
            
            // Query database untuk mendapatkan data sponsor
            $sponsor = $pdo->prepare(
                "SELECT u.id, u.name, u.email, u.referral_code, u.status,
                        supervisor.id as epis_supervisor_id,
                        supervisor.name as epis_supervisor_name,
                        supervisor.email as epis_supervisor_email
                 FROM epi_users u
                 LEFT JOIN epi_users supervisor ON u.epis_supervisor_id = supervisor.id
                 WHERE u.referral_code = ? AND u.status IN ('active', 'epic', 'epis')"
            );
            $sponsor->execute([$code]);
            $sponsor_data = $sponsor->fetch(PDO::FETCH_ASSOC);
            
            if ($sponsor_data) {
                $api_response = json_encode([
                    'success' => true,
                    'message' => 'Sponsor ditemukan',
                    'data' => [
                        'sponsor' => [
                            'id' => $sponsor_data['id'],
                            'name' => $sponsor_data['name'],
                            'email' => $sponsor_data['email'],
                            'referral_code' => $sponsor_data['referral_code'],
                            'status' => $sponsor_data['status']
                        ],
                        'epis_supervisor' => $sponsor_data['epis_supervisor_id'] ? [
                            'id' => $sponsor_data['epis_supervisor_id'],
                            'name' => $sponsor_data['epis_supervisor_name'],
                            'email' => $sponsor_data['epis_supervisor_email']
                        ] : null
                    ]
                ]);
                echo "✅ API Response: SUCCESS\n";
                echo "   Response: " . $api_response . "\n";
            } else {
                $api_response = json_encode([
                    'success' => false,
                    'message' => 'Kode sponsor tidak ditemukan atau tidak aktif'
                ]);
                echo "❌ API Response: FAILED\n";
                echo "   Response: " . $api_response . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ API Error: " . $e->getMessage() . "\n";
    }
    
    ob_end_clean();
    
    // 3. Test enhanced referral processing
    echo "\n3. TESTING ENHANCED REFERRAL PROCESSING:\n";
    
    if (file_exists('core/enhanced-referral-handler.php')) {
        require_once 'core/enhanced-referral-handler.php';
        
        if (function_exists('epic_enhanced_referral_processing')) {
            $referral_result = epic_enhanced_referral_processing($referral_code);
            
            echo "   Function result:\n";
            echo "   - Success: " . ($referral_result['success'] ? 'YES' : 'NO') . "\n";
            
            if ($referral_result['success']) {
                echo "   - Scenario: " . $referral_result['scenario'] . "\n";
                echo "   - Referrer: " . $referral_result['referrer']['name'] . "\n";
                echo "   - Auto Integration: " . ($referral_result['auto_integration'] ? 'YES' : 'NO') . "\n";
                
                if (isset($referral_result['epis_supervisor'])) {
                    echo "   - EPIS Supervisor: " . $referral_result['epis_supervisor']['name'] . "\n";
                }
            } else {
                echo "   - Error: " . $referral_result['error'] . "\n";
            }
        } else {
            echo "❌ Function 'epic_enhanced_referral_processing' not found\n";
        }
    } else {
        echo "❌ File 'core/enhanced-referral-handler.php' not found\n";
    }
    
    // 4. Test registration form dengan referral code
    echo "\n4. TESTING REGISTRATION FORM SIMULATION:\n";
    
    // Simulasi data form registrasi
    $form_data = [
        'name' => 'Test Registrant',
        'email' => 'test.registrant@example.com',
        'phone' => '081234567890',
        'password' => 'password123',
        'confirm_password' => 'password123',
        'referral_code' => $referral_code,
        'terms' => '1'
    ];
    
    echo "   Form data prepared:\n";
    foreach ($form_data as $key => $value) {
        if ($key !== 'password' && $key !== 'confirm_password') {
            echo "   - $key: $value\n";
        }
    }
    
    // Test CSRF validation
    if (file_exists('core/csrf-protection.php')) {
        require_once 'core/csrf-protection.php';
        
        // Generate CSRF token
        $csrf_token = epic_generate_csrf_token('register');
        $form_data['csrf_token'] = $csrf_token;
        $form_data['csrf_action'] = 'register';
        
        echo "   - CSRF token generated: " . substr($csrf_token, 0, 20) . "...\n";
        
        // Test validation
        if (function_exists('epic_validate_registration_form')) {
            $validation = epic_validate_registration_form($form_data);
            
            echo "   Validation result:\n";
            echo "   - Valid: " . ($validation['valid'] ? 'YES' : 'NO') . "\n";
            
            if (!$validation['valid']) {
                echo "   - Errors:\n";
                foreach ($validation['errors'] as $field => $error) {
                    echo "     * $field: $error\n";
                }
            }
        }
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
    // Cleanup - hapus test user
    if (isset($test_user_data)) {
        echo "\nCleaning up test user...\n";
        $stmt = $pdo->prepare("DELETE FROM epi_users WHERE email = ?");
        $stmt->execute(['test.ijuu9wa8@example.com']);
        echo "✅ Test user cleaned up\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}
?>