<?php
/**
 * Test Final - Simulasi Exact AJAX Request dari JavaScript
 */

// Define constants first
if (!defined('EPIC_INIT')) {
    define('EPIC_INIT', true);
}
if (!defined('EPIC_LOADED')) {
    define('EPIC_LOADED', true);
}

// Include core
require_once 'config/config.php';
require_once 'core/functions.php';

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🚀 Test Final - Upgrade System</h1>";
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
    button { padding: 10px 20px; margin: 5px; border: none; border-radius: 3px; cursor: pointer; }
    .btn-primary { background: #007bff; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-warning { background: #ffc107; color: black; }
</style>";

// Check prerequisites
$current_user = epic_current_user();
$is_admin = epic_is_admin();

echo "<div class='section info'>";
echo "<h2>📋 Prerequisites Check</h2>";

if (!$current_user) {
    echo "<div class='test-result fail'>❌ Not logged in</div>";
    echo "<p><strong>Action:</strong> Please <a href='/login.php'>login as admin</a> first</p>";
    exit;
}

if (!$is_admin) {
    echo "<div class='test-result fail'>❌ Not admin user</div>";
    echo "<p><strong>Current user:</strong> {$current_user['name']} (Role: {$current_user['role']})</p>";
    exit;
}

echo "<div class='test-result pass'>✅ Logged in as admin: {$current_user['name']}</div>";
echo "</div>";

// Find test users
echo "<div class='section'>";
echo "<h2>👥 Available Test Users</h2>";

try {
    $free_users = db()->select(
        "SELECT id, name, email, status, hierarchy_level, created_at 
         FROM " . db()->table('users') . " 
         WHERE status = 'free' AND hierarchy_level = 1 
         ORDER BY created_at DESC LIMIT 5"
    );
    
    if ($free_users) {
        echo "<div class='test-result pass'>✅ Found " . count($free_users) . " eligible users for upgrade</div>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; margin-top:10px;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Level</th><th>Action</th></tr>";
        
        foreach ($free_users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['status']}</td>";
            echo "<td>{$user['hierarchy_level']}</td>";
            echo "<td><button class='btn-primary' onclick='testUpgrade({$user['id']})'>Test Upgrade</button></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='test-result warning'>⚠ No eligible users found (free account with level 1)</div>";
        
        // Create a test user
        echo "<button class='btn-warning' onclick='createTestUser()'>Create Test User</button>";
    }
    
} catch (Exception $e) {
    echo "<div class='test-result fail'>❌ Database error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test Results Area
echo "<div class='section'>";
echo "<h2>📊 Test Results</h2>";
echo "<div id='testResults' style='min-height: 100px; border: 1px solid #ddd; padding: 10px; border-radius: 3px;'>";
echo "Click 'Test Upgrade' button above to start testing...";
echo "</div>";
echo "</div>";

// JavaScript for testing
echo "<script>
function testUpgrade(memberId) {
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.innerHTML = '⏳ Testing upgrade for member ID: ' + memberId + '...';
    
    console.log('Starting upgrade test for member ID:', memberId);
    
    // Exact same request as JavaScript in member-content.php
    fetch('/api/admin/upgrade-member.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            member_id: memberId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid JSON response: ' + text);
            }
        });
    })
    .then(data => {
        console.log('Parsed response:', data);
        
        let html = '<h3>✅ Response Received</h3>';
        html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        
        if (data.success) {
            html += '<div style=\"background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 3px;\">';
            html += '<strong>✅ SUCCESS:</strong> ' + data.message;
            html += '</div>';
            
            if (data.member) {
                html += '<h4>Updated Member Data:</h4>';
                html += '<pre>' + JSON.stringify(data.member, null, 2) + '</pre>';
            }
            
            if (data.preserved_data) {
                html += '<h4>Preserved Data:</h4>';
                html += '<ul>';
                if (data.preserved_data.referral) html += '<li>✅ Referral data preserved</li>';
                if (data.preserved_data.supervisor) html += '<li>✅ Supervisor data preserved</li>';
                html += '</ul>';
            }
        } else {
            html += '<div style=\"background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 3px;\">';
            html += '<strong>❌ ERROR:</strong> ' + data.message;
            html += '</div>';
        }
        
        resultsDiv.innerHTML = html;
    })
    .catch(error => {
        console.error('Test error:', error);
        
        let html = '<h3>❌ Test Failed</h3>';
        html += '<div style=\"background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 3px;\">';
        html += '<strong>Error:</strong> ' + error.message;
        html += '</div>';
        html += '<p><strong>Check browser console for detailed error information.</strong></p>';
        
        resultsDiv.innerHTML = html;
    });
}

function createTestUser() {
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.innerHTML = '⏳ Creating test user...';
    
    fetch('/api/admin/create-test-user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show new user
        } else {
            resultsDiv.innerHTML = '❌ Failed to create test user: ' + data.message;
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = '❌ Error creating test user: ' + error.message;
    });
}
</script>";

// Quick Links
echo "<div class='section info'>";
echo "<h2>🔗 Quick Links</h2>";
echo "<ul>";
echo "<li><a href='/themes/modern/admin/member.php' target='_blank'>Member Management Page</a></li>";
echo "<li><a href='/debug-auth-session.php' target='_blank'>Debug Authentication</a></li>";
echo "<li><a href='/test-ajax-endpoint-detailed.php' target='_blank'>Detailed Endpoint Test</a></li>";
echo "</ul>";
echo "</div>";
?>