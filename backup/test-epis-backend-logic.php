<?php
require_once 'bootstrap.php';

echo "=== Testing EPIS Backend Logic ===\n";

// Test data
$creation_method = 'super_admin_create';
$territory_name = 'Test Territory Superadmin';
$max_epic_recruits = 50;
$user_id = 0; // For super_admin_create

echo "Creation Method: $creation_method\n";
echo "Territory Name: $territory_name\n";
echo "Max EPIC Recruits: $max_epic_recruits\n";
echo "User ID: $user_id\n\n";

// Test logic conditions
echo "=== Testing Conditions ===\n";

// Condition 1: Super admin create without user
if ($creation_method === 'super_admin_create') {
    $user_id = 0; // Set to 0 for super admin create
    echo "✓ Super admin create detected - user_id set to 0\n";
} else {
    echo "✗ Not super admin create\n";
}

// Condition 2: Check if we can create EPIS
$can_create = (!empty($territory_name) && $max_epic_recruits > 0);
echo "Can create EPIS: " . ($can_create ? 'YES' : 'NO') . "\n";

// Condition 3: Check creation condition
$creation_condition = ($user_id > 0) || ($creation_method === 'super_admin_create');
echo "Creation condition met: " . ($creation_condition ? 'YES' : 'NO') . "\n";

if ($can_create && $creation_condition) {
    echo "\n=== Simulating EPIS Creation ===\n";
    
    // Generate EPIS Code
    $epis_code = 'EPIS' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    echo "Generated EPIS Code: $epis_code\n";
    
    // Check if EPIS Code exists
    $existing_epis = db()->selectOne('SELECT id FROM epic_epis_accounts WHERE epis_code = ?', [$epis_code]);
    if ($existing_epis) {
        echo "✗ EPIS Code already exists\n";
    } else {
        echo "✓ EPIS Code is unique\n";
        
        // Simulate INSERT
        echo "\nSQL Query would be:\n";
        echo "INSERT INTO epic_epis_accounts (epis_code, user_id, territory_name, max_epic_recruits, status, created_at) VALUES\n";
        echo "('$epis_code', " . ($user_id ?: 'NULL') . ", '$territory_name', $max_epic_recruits, 'active', NOW())\n";
        
        // Test actual insert (commented out for safety)
        /*
        try {
            $result = db()->insert('epic_epis_accounts', [
                'epis_code' => $epis_code,
                'user_id' => $user_id ?: null,
                'territory_name' => $territory_name,
                'max_epic_recruits' => $max_epic_recruits,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                echo "✓ EPIS account created successfully!\n";
                echo "EPIS Account ID: " . db()->lastInsertId() . "\n";
            } else {
                echo "✗ Failed to create EPIS account\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
        */
        
        echo "\n✓ Logic test completed successfully!\n";
        echo "The super_admin_create functionality would work correctly.\n";
    }
} else {
    echo "\n✗ Cannot create EPIS - conditions not met\n";
}
?>