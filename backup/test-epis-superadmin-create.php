<?php
session_start();
require_once 'bootstrap.php';

// Set session admin
$_SESSION['epic_user_id'] = 1;
$_SESSION['user_id'] = 1;
$_SESSION['csrf_token'] = 'test_token_' . time();

echo "<!DOCTYPE html><html><head><title>Test EPIS Superadmin Create</title></head><body>";
echo "<h1>Testing EPIS Superadmin Create</h1>";

// Test user
$user = epic_current_user();
echo "<p>Current User: " . ($user ? $user['name'] . ' (' . $user['role'] . ')' : 'NULL') . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Request Received</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Simulate the creation process
    if (isset($_POST['action']) && $_POST['action'] === 'create_epis') {
        $creation_method = $_POST['creation_method'] ?? '';
        echo "<p>Creation Method: $creation_method</p>";
        
        if ($creation_method === 'super_admin_create') {
            echo "<p style='color: green;'>✓ Super Admin Create method detected!</p>";
            
            // Test data
            $territory_name = $_POST['territory_name'] ?? '';
            $max_epic_recruits = $_POST['max_epic_recruits'] ?? 0;
            
            echo "<p>Territory Name: $territory_name</p>";
            echo "<p>Max EPIC Recruits: $max_epic_recruits</p>";
            
            if (!empty($territory_name) && $max_epic_recruits > 0) {
                echo "<p style='color: green;'>✓ All required fields provided!</p>";
                echo "<p style='color: blue;'>This would create EPIS account without user_id (user_id = 0)</p>";
            } else {
                echo "<p style='color: red;'>✗ Missing required fields</p>";
            }
        }
    }
} else {
    // Show form
    echo "<h2>Test Form</h2>";
    echo "<form method='POST' style='border: 1px solid #ccc; padding: 20px; max-width: 500px;'>";
    echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
    echo "<input type='hidden' name='action' value='create_epis'>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>Creation Method:</label><br>";
    echo "<select name='creation_method' style='width: 100%; padding: 8px;'>";
    echo "<option value='existing_user'>Existing User</option>";
    if ($user && $user['role'] === 'super_admin') {
        echo "<option value='super_admin_create'>Buat EPIS tanpa akun EPIC (Super Admin)</option>";
    }
    echo "</select>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>Territory Name:</label><br>";
    echo "<input type='text' name='territory_name' value='Test Territory' style='width: 100%; padding: 8px;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>Max EPIC Recruits:</label><br>";
    echo "<input type='number' name='max_epic_recruits' value='100' style='width: 100%; padding: 8px;'>";
    echo "</div>";
    
    echo "<button type='submit' style='background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Test Create EPIS</button>";
    echo "</form>";
}

echo "</body></html>";
?>