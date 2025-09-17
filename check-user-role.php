<?php
/**
 * Check and Update User Role for Blog Admin Access
 */

require_once __DIR__ . '/bootstrap.php';

echo "Checking User Role for Blog Admin Access...\n";
echo "============================================\n\n";

// Check current user details
$email = 'email@bisnisemasperak.com';
$user = db()->selectOne(
    "SELECT id, email, name, status, role FROM epic_users WHERE email = ?",
    [$email]
);

if (!$user) {
    echo "✗ User not found: {$email}\n";
    exit(1);
}

echo "Current User Details:\n";
echo "- ID: {$user['id']}\n";
echo "- Name: {$user['name']}\n";
echo "- Email: {$user['email']}\n";
echo "- Status: {$user['status']}\n";
echo "- Role: " . ($user['role'] ?? 'not set') . "\n\n";

// Check if user has admin access
$has_admin_access = in_array($user['role'] ?? '', ['admin', 'super_admin']);

if ($has_admin_access) {
    echo "✓ User has admin access\n";
} else {
    echo "✗ User does not have admin access\n";
    echo "\nUpdating user role to 'admin'...\n";
    
    try {
        $result = db()->update(
            'epic_users',
            ['role' => 'admin'],
            ['id' => $user['id']]
        );
        
        if ($result) {
            echo "✓ User role updated to 'admin'\n";
            
            // Verify update
            $updated_user = db()->selectOne(
                "SELECT role FROM epic_users WHERE id = ?",
                [$user['id']]
            );
            
            echo "✓ Verified new role: {$updated_user['role']}\n";
        } else {
            echo "✗ Failed to update user role\n";
        }
    } catch (Exception $e) {
        echo "✗ Error updating role: " . $e->getMessage() . "\n";
    }
}

echo "\n============================================\n";
echo "Testing Blog Admin Access Requirements:\n\n";

// Test admin functions availability
$admin_functions = [
    'epic_current_user',
    'epic_route_403',
    'epic_url',
    'epic_redirect'
];

foreach ($admin_functions as $func) {
    if (function_exists($func)) {
        echo "✓ Function {$func} available\n";
    } else {
        echo "✗ Function {$func} missing\n";
    }
}

// Test database constants
echo "\nDatabase Constants:\n";
$constants = ['TABLE_USERS', 'TABLE_ARTICLES', 'TABLE_CATEGORIES'];
foreach ($constants as $const) {
    if (defined($const)) {
        echo "✓ Constant {$const} defined: " . constant($const) . "\n";
    } else {
        echo "✗ Constant {$const} not defined\n";
    }
}

echo "\n============================================\n";
echo "Ready for Testing:\n";
echo "\n🔗 Admin Blog URL: http://localhost/epichub/admin/blog\n";
echo "\n📋 Login Credentials:\n";
echo "- Email: {$email}\n";
echo "- Password: !Shadow007\n";
echo "- Role: " . ($updated_user['role'] ?? $user['role'] ?? 'not set') . "\n";

echo "\n✅ User role check completed!\n";
?>