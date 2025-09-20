<?php
session_start();
require_once 'bootstrap.php';

// Set session admin
$_SESSION['epic_user_id'] = 1;
$_SESSION['user_id'] = 1;

echo "<!DOCTYPE html><html><head><title>Test EPIS Add</title></head><body>";
echo "<h1>Testing EPIS Add Page</h1>";

// Test user
$user = epic_current_user();
echo "<p>Current User: " . ($user ? $user['name'] . ' (' . $user['role'] . ')' : 'NULL') . "</p>";

// Test admin access
echo "<p>Is Admin: " . (epic_is_admin($user) ? 'YES' : 'NO') . "</p>";

// Test content file
$content_file = __DIR__ . '/themes/modern/admin/content/epis-add-content.php';
echo "<p>Content File Exists: " . (file_exists($content_file) ? 'YES' : 'NO') . "</p>";

if (file_exists($content_file) && $user && epic_is_admin($user)) {
    echo "<h2>Form Content:</h2>";
    echo "<div style='border: 1px solid #ccc; padding: 20px; margin: 20px 0;'>";
    
    // Set required variables
    $success = '';
    $error = '';
    $form_data = [];
    $eligible_epic_users = [];
    
    // Include content
    include $content_file;
    
    echo "</div>";
} else {
    echo "<p style='color: red;'>Cannot load content - missing file or no admin access</p>";
}

echo "</body></html>";
?>