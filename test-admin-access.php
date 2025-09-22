<?php
session_start();
require_once 'bootstrap.php';

// Set admin session untuk testing
\['epic_user'] = [
    'id' => 1,
    'username' => 'admin',
    'email' => 'admin@test.com',
    'role' => 'admin',
    'name' => 'Test Admin'
];

echo 'Admin session set. Testing access...' . PHP_EOL;

// Test epic_current_user
\ = epic_current_user();
echo 'Current user: ' . print_r(\, true) . PHP_EOL;

// Test admin access
if (\ && in_array(\['role'], ['admin', 'super_admin'])) {
    echo 'Admin access: GRANTED' . PHP_EOL;
} else {
    echo 'Admin access: DENIED' . PHP_EOL;
}

// Test file exists
\ = __DIR__ . '/themes/modern/admin/landing-page-manager.php';
echo 'Landing manager file exists: ' . (file_exists(\) ? 'YES' : 'NO') . PHP_EOL;
echo 'File path: ' . \ . PHP_EOL;
?>
