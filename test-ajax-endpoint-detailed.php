<?php
/**
 * TEST DETAIL ENDPOINT AJAX UPGRADE
 * Script khusus untuk menguji endpoint AJAX dengan berbagai skenario
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.info { color: blue; font-weight: bold; }
pre { background: #f8f8f8; padding: 10px; border-radius: 3px; overflow-x: auto; }
.response-box { background: #f0f8ff; padding: 10px; border-left: 4px solid #007cba; margin: 10px 0; }
</style>";

echo "<h1>🔬 TEST DETAIL ENDPOINT AJAX UPGRADE</h1>";

// =============================================================================
// 1. SETUP & INITIALIZATION
// =============================================================================
echo "<div class='test-section'>";
echo "<h2>1. 🚀 Setup & Initialization</h2>";

require_once 'core/config.php';
require_once 'core/functions.php';

session_start();

// Simulasi admin session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

echo "<span class='success'>✅ Core files loaded</span><br>";
echo "<span class='success'>✅ Session started</span><br>";
echo "<span class='success'>✅ Admin session simulated</span><br>";

echo "</div>";

// =============================================================================
// 2. ENDPOINT ACCESSIBILITY TEST
// =============================================================================
echo "<div class='test-section'>";
echo "<h2>2. 🌐 Endpoint Accessibility Test</h2>";

$endpoint_path = __DIR__ . '/api/admin/upgrade-member.php';
$endpoint_url = 'http://localhost:8080/api/admin/upgrade-member.php';

echo "Endpoint Path: <code>$endpoint_path</code><br>";
echo "Endpoint URL: <code>$endpoint_url</code><br><br>";

// File existence
if (file_exists($endpoint_path)) {
    echo "<span class='success'>✅ File exists</span><br>";
    
    // File permissions
    if (is_readable($endpoint_path)) {
        echo "<span class='success'>✅ File readable</span><br>";
    } else {
        echo "<span class='error'>❌ File not readable</span><br>";
    }
    
    // File size
    $size = filesize($endpoint_path);
    if ($size > 0) {
        echo "<span class='success'>✅ File size: {$size} bytes</span><br>";
    } else {
        echo "<span class='error'>❌ File is empty</span><br>";
    }
    
    // File content preview
    echo "<h3>📄 File Content Preview (first 500 chars):</h3>";
    echo "<pre>" . htmlspecialchars(substr(file_get_contents($endpoint_path), 0, 500)) . "...</pre>";
    
} else {
    echo "<span class='error'>❌ File does not exist</span><br>";
}

echo "</div>";

// =============================================================================
// 3. DATABASE & USER PREPARATION
// =============================================================================
echo "<div class='test-section'>";
echo "<h2>3. 🗄️ Database & User Preparation</h2>";

try {
    $pdo = epic_get_pdo();
    echo "<span class='success'>✅ Database connected</span><br>";
    
    // Find test user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE status = 'free' AND hierarchy_level = 1 LIMIT 1");
    $stmt->execute();
    $test_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($test_user) {
        echo "<span class='success'>✅ Test user found</span><br>";
        echo "<div class='response-box'>";
        echo "<strong>Test User Data:</strong><br>";
        echo "ID: {$test_user['id']}<br>";
        echo "Name: {$test_user['name']}<br>";
        echo "Email: {$test_user['email']}<br>";
        echo "Status: {$test_user['status']}<br>";
        echo "Level: {$test_user['hierarchy_level']}<br>";
        echo "Referral Code: {$test_user['referral_code']}<br>";
        echo "Supervisor ID: " . ($test_user['epis_supervisor_id'] ?? 'NULL') . "<br>";
        echo "</div>";
    } else {
        echo "<span class='warning'>⚠️ No free account users found</span><br>";
        
        // Create test user
        echo "<h3>🔧 Creating Test User...</h3>";
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, status, hierarchy_level, referral_code, created_at) VALUES (?, ?, ?, 'free', 1, ?, NOW())");
        $test_email = 'test_upgrade_' . time() . '@example.com';
        $test_referral = 'TEST' . strtoupper(substr(md5(time()), 0, 6));
        
        if ($stmt->execute(['Test Upgrade User', $test_email, password_hash('password123', PASSWORD_DEFAULT), $test_referral])) {
            $test_user_id = $pdo->lastInsertId();
            echo "<span class='success'>✅ Test user created with ID: $test_user_id</span><br>";
            
            // Fetch the created user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$test_user_id]);
            $test_user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "<span class='error'>❌ Failed to create test user</span><br>";
        }
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Database error: " . $e->getMessage() . "</span><br>";
}

echo "</div>";

// =============================================================================
// 4. FUNCTION AVAILABILITY TEST
// =============================================================================
echo "<div class='test-section'>";
echo "<h2>4. ⚙️ Function Availability Test</h2>";

$required_functions = [
    'epic_safe_upgrade_to_epic',
    'epic_validate_upgrade_eligibility', 
    'epic_get_upgrade_impact'
];

foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "<span class='success'>✅ $func exists</span><br>";
    } else {
        echo "<span class='error'>❌ $func missing</span><br>";
    }
}

echo "</div>";

// =============================================================================
// 5. DIRECT ENDPOINT TEST
// =============================================================================
echo "<div class='test-section'>";
echo "<h2>5. 🧪 Direct Endpoint Test</h2>";

if (file_exists($endpoint_path) && $test_user) {
    echo "<h3>📤 Testing POST Request to Endpoint</h3>";
    
    // Backup original values
    $original_post = $_POST;
    $original_server = $_SERVER;
    $original_get = $_GET;
    
    // Setup POST request simulation
    $_POST = ['member_id' => $test_user['id']];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    $_GET = []; // Clear GET params
    
    echo "<div class='response-box'>";
    echo "<strong>Request Data:</strong><br>";
    echo "Method: POST<br>";
    echo "Member ID: {$test_user['id']}<br>";
    echo "X-Requested-With: XMLHttpRequest<br>";
    echo "</div>";
    
    // Capture output and errors
    ob_start();
    $error_output = '';
    
    try {
        // Capture any errors
        set_error_handler(function($severity, $message, $file, $line) use (&$error_output) {
            $error_output .= "Error: $message in $file on line $line\n";
        });
        
        include $endpoint_path;
        
        restore_error_handler();
        
    } catch (Exception $e) {
        $error_output .= "Exception: " . $e->getMessage() . "\n";
    } catch (Error $e) {
        $error_output .= "Fatal Error: " . $e->getMessage() . "\n";
    }
    
    $output = ob_get_contents();
    ob_end_clean();
    
    // Restore original values
    $_POST = $original_post;
    $_SERVER = $original_server;
    $_GET = $original_get;
    
    echo "<h3>📥 Endpoint Response:</h3>";
    
    if (!empty($error_output)) {
        echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 3px; margin: 10px 0;'>";
        echo "<strong class='error'>❌ Errors Detected:</strong><br>";
        echo "<pre>" . htmlspecialchars($error_output) . "</pre>";
        echo "</div>";
    }
    
    if (!empty($output)) {
        echo "<div class='response-box'>";
        echo "<strong>Raw Output:</strong><br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        echo "</div>";
        
        // Try to parse as JSON
        $json_response = json_decode($output, true);
        if ($json_response !== null) {
            echo "<span class='success'>✅ Valid JSON Response</span><br>";
            echo "<div class='response-box'>";
            echo "<strong>Parsed JSON:</strong><br>";
            echo "<pre>" . print_r($json_response, true) . "</pre>";
            echo "</div>";
            
            // Analyze response
            if (isset($json_response['success'])) {
                if ($json_response['success']) {
                    echo "<span class='success'>✅ Upgrade successful</span><br>";
                } else {
                    echo "<span class='error'>❌ Upgrade failed: " . ($json_response['message'] ?? 'Unknown error') . "</span><br>";
                }
            }
        } else {
            echo "<span class='error'>❌ Invalid JSON Response</span><br>";
            echo "<span class='warning'>⚠️ JSON Error: " . json_last_error_msg() . "</span><br>";
        }
    } else {
        echo "<span class='warning'>⚠️ No output from endpoint</span><br>";
    }
    
} else {
    echo "<span class='error'>❌ Cannot test - missing endpoint file or test user</span><br>";
}

echo "</div>";

// =============================================================================
// 6. CURL TEST (Alternative Method)
// =============================================================================
echo "<div class='test-section'>";
echo "<h2>6. 🌐 cURL Test (Alternative Method)</h2>";

if ($test_user && function_exists('curl_init')) {
    echo "<h3>📡 Testing via cURL</h3>";
    
    $curl = curl_init();
    $post_data = http_build_query(['member_id' => $test_user['id']]);
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $endpoint_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post_data,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'X-Requested-With: XMLHttpRequest'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $curl_response = curl_exec($curl);
    $curl_error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    echo "<div class='response-box'>";
    echo "<strong>cURL Request:</strong><br>";
    echo "URL: $endpoint_url<br>";
    echo "Method: POST<br>";
    echo "Data: $post_data<br>";
    echo "</div>";
    
    if ($curl_error) {
        echo "<span class='error'>❌ cURL Error: $curl_error</span><br>";
    } else {
        echo "<span class='success'>✅ cURL executed successfully</span><br>";
        echo "<span class='info'>HTTP Code: $http_code</span><br>";
        
        if ($curl_response) {
            echo "<div class='response-box'>";
            echo "<strong>cURL Response:</strong><br>";
            echo "<pre>" . htmlspecialchars($curl_response) . "</pre>";
            echo "</div>";
            
            $json_response = json_decode($curl_response, true);
            if ($json_response !== null) {
                echo "<span class='success'>✅ Valid JSON from cURL</span><br>";
            } else {
                echo "<span class='error'>❌ Invalid JSON from cURL</span><br>";
            }
        } else {
            echo "<span class='warning'>⚠️ Empty response from cURL</span><br>";
        }
    }
} else {
    echo "<span class='warning'>⚠️ cURL not available or no test user</span><br>";
}

echo "</div>";

// =============================================================================
// 7. FRONTEND INTEGRATION TEST
// =============================================================================
echo "<div class='test-section'>";
echo "<h2>7. 🎨 Frontend Integration Test</h2>";

echo "<h3>📄 JavaScript Function Test</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Manual Test Instructions:</strong><br>";
echo "1. Open browser console (F12)<br>";
echo "2. Go to Member Management page<br>";
echo "3. Look for upgradeAccount function in console<br>";
echo "4. Check for any JavaScript errors<br>";
echo "5. Monitor Network tab when clicking upgrade button<br>";
echo "</div>";

echo "<div style='margin: 15px 0;'>";
echo "<a href='themes/modern/admin/member.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔗 Open Member Management</a>";
echo "<a href='debug-upgrade-system.php' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Full System Debug</a>";
echo "</div>";

echo "</div>";

// =============================================================================
// 8. SUMMARY & RECOMMENDATIONS
// =============================================================================
echo "<div class='test-section'>";
echo "<h2>8. 📋 Summary & Recommendations</h2>";

echo "<h3>🔍 Test Results Summary:</h3>";
$all_tests_passed = true;

// Check critical components
$critical_checks = [
    'Endpoint file exists' => file_exists($endpoint_path),
    'Database connection' => isset($pdo),
    'Test user available' => isset($test_user) && $test_user,
    'Required functions exist' => function_exists('epic_safe_upgrade_to_epic')
];

foreach ($critical_checks as $check => $passed) {
    $status = $passed ? '✅' : '❌';
    $class = $passed ? 'success' : 'error';
    echo "<span class='$class'>$status $check</span><br>";
    if (!$passed) $all_tests_passed = false;
}

echo "<h3>💡 Next Steps:</h3>";
if ($all_tests_passed) {
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; border-left: 4px solid #28a745;'>";
    echo "<strong>✅ All critical tests passed!</strong><br>";
    echo "The upgrade system appears to be properly configured. If the button still doesn't work:<br>";
    echo "1. Check browser console for JavaScript errors<br>";
    echo "2. Verify admin session is active<br>";
    echo "3. Test with a different browser<br>";
    echo "4. Check server error logs<br>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; border-left: 4px solid #dc3545;'>";
    echo "<strong>❌ Critical issues found!</strong><br>";
    echo "Please fix the failed tests above before proceeding.<br>";
    echo "</div>";
}

echo "</div>";

echo "<div style='margin-top: 30px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 5px;'>";
echo "<strong>🔄 Refresh this page after making changes to re-test</strong>";
echo "</div>";
?>