<?php
/**
 * Debug Authentication & Session untuk Upgrade System
 */

// Include core
require_once 'core/config.php';
require_once 'core/functions.php';

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🔍 Debug Authentication & Session</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background: #d4edda; border-color: #c3e6cb; }
    .error { background: #f8d7da; border-color: #f5c6cb; }
    .warning { background: #fff3cd; border-color: #ffeaa7; }
    .info { background: #d1ecf1; border-color: #bee5eb; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
    .pass { background: #d4edda; }
    .fail { background: #f8d7da; }
</style>";

// 1. Session Information
echo "<div class='section info'>";
echo "<h2>📋 Session Information</h2>";
echo "<pre>";
echo "Session Status: " . (session_status() == PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Data:\n";
print_r($_SESSION);
echo "</pre>";
echo "</div>";

// 2. Authentication Check
echo "<div class='section'>";
echo "<h2>🔐 Authentication Check</h2>";

$is_logged_in = epic_is_logged_in();
$current_user = epic_current_user();
$is_admin = epic_is_admin();

echo "<div class='test-result " . ($is_logged_in ? "pass" : "fail") . "'>";
echo "✓ epic_is_logged_in(): " . ($is_logged_in ? "TRUE" : "FALSE");
echo "</div>";

echo "<div class='test-result " . ($current_user ? "pass" : "fail") . "'>";
echo "✓ epic_current_user(): " . ($current_user ? "USER FOUND" : "NULL");
echo "</div>";

if ($current_user) {
    echo "<pre>";
    print_r($current_user);
    echo "</pre>";
}

echo "<div class='test-result " . ($is_admin ? "pass" : "fail") . "'>";
echo "✓ epic_is_admin(): " . ($is_admin ? "TRUE" : "FALSE");
echo "</div>";
echo "</div>";

// 3. Database Connection Test
echo "<div class='section'>";
echo "<h2>🗄️ Database Connection</h2>";

try {
    $db_test = db()->selectOne("SELECT 1 as test");
    echo "<div class='test-result pass'>✓ Database Connection: OK</div>";
    
    // Test users table
    $user_count = db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users'));
    echo "<div class='test-result pass'>✓ Users Table: {$user_count['count']} users found</div>";
    
} catch (Exception $e) {
    echo "<div class='test-result fail'>✗ Database Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// 4. Test Upgrade Function
echo "<div class='section'>";
echo "<h2>⚡ Test Upgrade Function</h2>";

if (function_exists('epic_safe_upgrade_to_epic')) {
    echo "<div class='test-result pass'>✓ epic_safe_upgrade_to_epic() function exists</div>";
} else {
    echo "<div class='test-result fail'>✗ epic_safe_upgrade_to_epic() function NOT found</div>";
}

// Find a test user
try {
    $test_user = db()->selectOne(
        "SELECT id, name, email, status, hierarchy_level FROM " . db()->table('users') . " 
         WHERE status = 'free' AND hierarchy_level = 1 LIMIT 1"
    );
    
    if ($test_user) {
        echo "<div class='test-result pass'>✓ Test User Found: {$test_user['name']} (ID: {$test_user['id']})</div>";
        echo "<pre>";
        print_r($test_user);
        echo "</pre>";
    } else {
        echo "<div class='test-result warning'>⚠ No eligible test user found (free account with level 1)</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-result fail'>✗ Error finding test user: " . $e->getMessage() . "</div>";
}
echo "</div>";

// 5. Test AJAX Endpoint Direct
echo "<div class='section'>";
echo "<h2>🌐 Test AJAX Endpoint Direct</h2>";

if ($current_user && $is_admin) {
    echo "<div class='test-result pass'>✓ Admin access confirmed - can test endpoint</div>";
    
    // Test endpoint accessibility
    $endpoint_path = __DIR__ . '/api/admin/upgrade-member.php';
    if (file_exists($endpoint_path)) {
        echo "<div class='test-result pass'>✓ Endpoint file exists: /api/admin/upgrade-member.php</div>";
    } else {
        echo "<div class='test-result fail'>✗ Endpoint file NOT found: /api/admin/upgrade-member.php</div>";
    }
    
} else {
    echo "<div class='test-result fail'>✗ Not logged in as admin - cannot test endpoint</div>";
    echo "<p><strong>Action Required:</strong> Please login as admin first</p>";
}
echo "</div>";

// 6. JavaScript Test
echo "<div class='section'>";
echo "<h2>🔧 JavaScript Test</h2>";

if ($current_user && $is_admin && isset($test_user)) {
    echo "<p>Test the upgrade function with JavaScript:</p>";
    echo "<button id='testUpgrade' onclick='testUpgradeFunction({$test_user['id']})'>Test Upgrade User ID: {$test_user['id']}</button>";
    echo "<div id='testResult' style='margin-top: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 3px;'></div>";
    
    echo "<script>
    function testUpgradeFunction(memberId) {
        const resultDiv = document.getElementById('testResult');
        resultDiv.innerHTML = '⏳ Testing upgrade...';
        
        const formData = new FormData();
        formData.append('member_id', memberId);
        
        fetch('/api/admin/upgrade-member.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            if (data.success) {
                resultDiv.style.background = '#d4edda';
            } else {
                resultDiv.style.background = '#f8d7da';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = '❌ Error: ' + error.message;
            resultDiv.style.background = '#f8d7da';
        });
    }
    </script>";
} else {
    echo "<p>❌ Cannot test JavaScript - missing requirements (admin login or test user)</p>";
}
echo "</div>";

// 7. Quick Fixes
echo "<div class='section warning'>";
echo "<h2>🔧 Quick Fixes</h2>";
echo "<ul>";

if (!$is_logged_in) {
    echo "<li>❌ <strong>Login Required:</strong> Please login as admin first</li>";
}

if (!function_exists('epic_safe_upgrade_to_epic')) {
    echo "<li>❌ <strong>Missing Function:</strong> epic_safe_upgrade_to_epic() not found</li>";
}

if (!file_exists(__DIR__ . '/api/admin/upgrade-member.php')) {
    echo "<li>❌ <strong>Missing Endpoint:</strong> /api/admin/upgrade-member.php not found</li>";
}

echo "<li>✅ <strong>Test Endpoint:</strong> <a href='/test-ajax-endpoint-detailed.php' target='_blank'>Run detailed endpoint test</a></li>";
echo "<li>✅ <strong>Member Management:</strong> <a href='/themes/modern/admin/member.php' target='_blank'>Go to Member Management</a></li>";
echo "</ul>";
echo "</div>";

echo "<div class='section info'>";
echo "<h2>📝 Summary</h2>";
echo "<p><strong>Status:</strong> ";
if ($is_logged_in && $is_admin && function_exists('epic_safe_upgrade_to_epic')) {
    echo "✅ System ready for upgrade testing";
} else {
    echo "❌ System has issues that need to be resolved";
}
echo "</p>";
echo "</div>";
?>