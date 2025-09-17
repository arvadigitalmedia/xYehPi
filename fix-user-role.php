<?php
/**
 * Fix User Role for Admin Access
 */

require_once __DIR__ . '/bootstrap.php';

echo "Fixing User Role for Admin Access...\n";
echo "====================================\n\n";

$email = 'email@bisnisemasperak.com';

// Check table structure
echo "1. Checking epic_users table structure...\n";
try {
    $columns = db()->select("SHOW COLUMNS FROM epic_users");
    $has_role_column = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'role') {
            $has_role_column = true;
            echo "✓ Role column exists: {$col['Type']}\n";
            break;
        }
    }
    
    if (!$has_role_column) {
        echo "✗ Role column missing. Adding role column...\n";
        db()->query("ALTER TABLE epic_users ADD COLUMN role ENUM('user', 'admin', 'super_admin') DEFAULT 'user' AFTER status");
        echo "✓ Role column added\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking table: " . $e->getMessage() . "\n";
}

// Check current user
echo "\n2. Checking current user...\n";
try {
    $user = db()->selectOne(
        "SELECT id, email, name, status, role FROM epic_users WHERE email = ?",
        [$email]
    );
    
    if (!$user) {
        echo "✗ User not found: {$email}\n";
        exit(1);
    }
    
    echo "Current user details:\n";
    echo "- ID: {$user['id']}\n";
    echo "- Name: {$user['name']}\n";
    echo "- Email: {$user['email']}\n";
    echo "- Status: {$user['status']}\n";
    echo "- Role: " . ($user['role'] ?? 'NULL') . "\n";
    
} catch (Exception $e) {
    echo "✗ Error getting user: " . $e->getMessage() . "\n";
    exit(1);
}

// Update role if needed
echo "\n3. Updating user role...\n";
try {
    if (empty($user['role']) || !in_array($user['role'], ['admin', 'super_admin'])) {
        echo "Updating role to 'super_admin'...\n";
        
        $result = db()->query(
            "UPDATE epic_users SET role = 'super_admin' WHERE id = ?",
            [$user['id']]
        );
        
        if ($result) {
            echo "✓ Role updated to 'super_admin'\n";
        } else {
            echo "✗ Failed to update role\n";
        }
    } else {
        echo "✓ User already has admin role: {$user['role']}\n";
    }
} catch (Exception $e) {
    echo "✗ Error updating role: " . $e->getMessage() . "\n";
}

// Verify final state
echo "\n4. Final verification...\n";
try {
    $final_user = db()->selectOne(
        "SELECT id, email, name, status, role FROM epic_users WHERE email = ?",
        [$email]
    );
    
    echo "Final user state:\n";
    echo "- Name: {$final_user['name']}\n";
    echo "- Email: {$final_user['email']}\n";
    echo "- Status: {$final_user['status']}\n";
    echo "- Role: {$final_user['role']}\n";
    
    $can_access_admin = in_array($final_user['role'], ['admin', 'super_admin']);
    echo "- Can access admin: " . ($can_access_admin ? "✓ YES" : "✗ NO") . "\n";
    
} catch (Exception $e) {
    echo "✗ Error in verification: " . $e->getMessage() . "\n";
}

echo "\n====================================\n";
echo "🎯 Testing Instructions:\n";
echo "\n1. Open browser and go to: http://localhost/epichub/admin\n";
echo "2. Login with credentials:\n";
echo "   - Email: {$email}\n";
echo "   - Password: !Shadow007\n";
echo "3. Navigate to Blog menu in sidebar\n";
echo "4. Test blog management features\n";

echo "\n✅ User role fix completed!\n";
?>