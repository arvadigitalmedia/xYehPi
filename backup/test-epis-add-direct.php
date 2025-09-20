<?php
session_start();
require_once 'bootstrap.php';

// Set session admin
$_SESSION['epic_user_id'] = 1;
$_SESSION['user_id'] = 1;

echo "=== Testing EPIS Add Direct ===\n";

// Test path content file
$content_file = __DIR__ . '/themes/modern/admin/content/epis-add-content.php';
echo "Content file path: $content_file\n";
echo "File exists: " . (file_exists($content_file) ? 'YES' : 'NO') . "\n";

if (file_exists($content_file)) {
    echo "File readable: " . (is_readable($content_file) ? 'YES' : 'NO') . "\n";
    
    // Test include
    echo "\n=== Testing Include ===\n";
    
    // Set required variables
    $user = epic_current_user();
    $success = '';
    $error = '';
    $form_data = [];
    $eligible_epic_users = [];
    
    echo "User: " . ($user ? $user['name'] . ' (' . $user['role'] . ')' : 'NULL') . "\n";
    
    // Include content
    ob_start();
    include $content_file;
    $content = ob_get_clean();
    
    echo "Content length: " . strlen($content) . " characters\n";
    echo "Contains super_admin_create: " . (strpos($content, 'super_admin_create') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains creation_method: " . (strpos($content, 'creation_method') !== false ? 'YES' : 'NO') . "\n";
    
    // Show first 500 chars
    echo "\n=== First 500 chars ===\n";
    echo substr($content, 0, 500) . "...\n";
}
?>