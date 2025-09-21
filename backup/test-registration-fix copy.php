<?php
/**
 * Test Script - Registration Form Fix Verification
 * Menguji perbaikan form registrasi dengan berbagai skenario
 */

// Prevent direct access
if (!defined('EPIC_INIT')) {
    define('EPIC_INIT', true);
}

if (!defined('EPIC_LOADED')) {
    define('EPIC_LOADED', true);
}

require_once 'config/config.php';
require_once 'core/functions.php';
require_once 'core/csrf-protection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "=== TEST REGISTRASI FORM FIX ===\n\n";

// Test 1: Validasi Password Baru (6 karakter minimum)
echo "1. Test Validasi Password Baru:\n";
$test_passwords = [
    'abc123' => 'Valid (6 karakter, ada huruf dan angka)',
    'abcdef' => 'Invalid (tidak ada angka)',
    '123456' => 'Invalid (tidak ada huruf)',
    'abc12' => 'Invalid (kurang dari 6 karakter)',
    'Abc123!' => 'Valid (kuat dengan simbol)'
];

foreach ($test_passwords as $password => $expected) {
    $rules = epic_get_registration_validation_rules();
    $password_rule = $rules['password'];
    
    $is_valid = true;
    $error_msg = '';
    
    // Test length
    if (strlen($password) < $password_rule['min_length']) {
        $is_valid = false;
        $error_msg = $password_rule['min_length_message'];
    }
    
    // Test custom validation
    if ($is_valid && isset($password_rule['custom'])) {
        $custom_result = $password_rule['custom']($password);
        if ($custom_result !== true) {
            $is_valid = false;
            $error_msg = $custom_result;
        }
    }
    
    $status = $is_valid ? "✅ VALID" : "❌ INVALID";
    echo "   Password: '$password' -> $status";
    if (!$is_valid) {
        echo " ($error_msg)";
    }
    echo " | Expected: $expected\n";
}

echo "\n";

// Test 2: Validasi Form Data
echo "2. Test Validasi Form Data:\n";
$test_data = [
    'name' => 'John Doe',
    'email' => 'test' . time() . '@example.com',
    'password' => 'test123',
    'confirm_password' => 'test123',
    'referral_code' => 'IJUU9WA8'
];

// Simulate CSRF token
$_SESSION['csrf_token'] = 'test_token_' . time();
$test_data['csrf_token'] = $_SESSION['csrf_token'];

echo "   Testing data: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n";

try {
    $validation = epic_validate_registration_form($test_data);
    
    if ($validation['valid']) {
        echo "   ✅ Validasi BERHASIL\n";
        echo "   Data yang divalidasi:\n";
        foreach ($validation['data'] as $key => $value) {
            if ($key !== 'password' && $key !== 'confirm_password') {
                echo "      $key: $value\n";
            } else {
                echo "      $key: [HIDDEN]\n";
            }
        }
    } else {
        echo "   ❌ Validasi GAGAL\n";
        echo "   Errors:\n";
        foreach ($validation['errors'] as $field => $errors) {
            echo "      $field: " . implode(', ', $errors) . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Referral Code Processing
echo "3. Test Referral Code Processing:\n";
require_once 'core/enhanced-referral-handler.php';

$referral_codes = ['IJUU9WA8', 'INVALID123', ''];

foreach ($referral_codes as $code) {
    echo "   Testing referral code: '$code'\n";
    
    try {
        if (empty($code)) {
            echo "      ✅ Empty code - OK (optional)\n";
            continue;
        }
        
        $result = epic_enhanced_referral_processing($code);
        
        if ($result['success']) {
            echo "      ✅ Referral processing BERHASIL\n";
            echo "         Scenario: " . $result['scenario'] . "\n";
            echo "         Referrer: " . $result['referrer']['name'] . "\n";
            echo "         Auto Integration: " . ($result['auto_integration'] ? 'Yes' : 'No') . "\n";
            
            // Check EPIS Supervisor
            if (isset($result['epis_supervisor'])) {
                echo "         EPIS Supervisor: " . ($result['epis_supervisor'] ? 'Set' : 'NULL') . "\n";
            } else {
                echo "         EPIS Supervisor: Not applicable\n";
            }
        } else {
            echo "      ❌ Referral processing GAGAL: " . $result['error'] . "\n";
        }
    } catch (Exception $e) {
        echo "      ❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test 4: Database Connection
echo "4. Test Database Connection:\n";
try {
    $db = db()->getConnection();
    $stmt = $db->query("SELECT COUNT(*) as count FROM epic_users");
    $result = $stmt->fetch();
    echo "   ✅ Database connection OK\n";
    echo "   Total users in database: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "   ❌ Database connection ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: CSRF Protection
echo "5. Test CSRF Protection:\n";
try {
    // Test valid token
    $_SESSION['csrf_token'] = 'valid_token';
    $_POST['csrf_token'] = 'valid_token';
    
    if (epic_verify_csrf_request()) {
        echo "   ✅ CSRF validation BERHASIL\n";
    } else {
        echo "   ❌ CSRF validation GAGAL\n";
    }
    
    // Test invalid token
    $_POST['csrf_token'] = 'invalid_token';
    
    if (!epic_verify_csrf_request()) {
        echo "   ✅ CSRF protection BERHASIL (invalid token rejected)\n";
    } else {
        echo "   ❌ CSRF protection GAGAL (invalid token accepted)\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ CSRF test ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== TEST SELESAI ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
?>