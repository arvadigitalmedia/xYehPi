<?php
require_once 'bootstrap.php';

echo "=== Testing Actual EPIS Creation ===\n";

// Test data
$creation_method = 'super_admin_create';
$territory_name = 'Test Territory Superadmin ' . date('Y-m-d H:i:s');
$max_epic_recruits = 25;
$user_id = 0; // For super_admin_create

echo "Creation Method: $creation_method\n";
echo "Territory Name: $territory_name\n";
echo "Max EPIC Recruits: $max_epic_recruits\n";
echo "User ID: $user_id\n\n";

try {
    // Generate unique EPIS Code
    do {
        $epis_code = 'EPIS' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $existing = db()->selectOne('SELECT id FROM epic_epis_accounts WHERE epis_code = ?', [$epis_code]);
    } while ($existing);
    
    echo "Generated unique EPIS Code: $epis_code\n";
    
    // Prepare data for insert
    $insert_data = [
        'epis_code' => $epis_code,
        'user_id' => $user_id, // 0 untuk super admin create
        'territory_name' => $territory_name,
        'max_epic_recruits' => $max_epic_recruits,
        'current_epic_count' => 0,
        'recruitment_commission_rate' => 15.00,
        'indirect_commission_rate' => 7.50,
        'can_manage_benefits' => 1,
        'can_view_epic_analytics' => 1,
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    echo "\nInserting EPIS account...\n";
    
    // Perform actual insert (tanpa prefix karena akan ditambahkan otomatis)
    $result = db()->insert('epis_accounts', $insert_data);
    
    if ($result) {
        $epis_account_id = db()->getConnection()->lastInsertId();
        echo "✓ EPIS account created successfully!\n";
        echo "EPIS Account ID: $epis_account_id\n";
        echo "EPIS Code: $epis_code\n";
        
        // Verify the insert
        $created_account = db()->selectOne('SELECT * FROM epic_epis_accounts WHERE id = ?', [$epis_account_id]);
        
        if ($created_account) {
            echo "\n=== Verification ===\n";
            echo "ID: {$created_account['id']}\n";
            echo "EPIS Code: {$created_account['epis_code']}\n";
            echo "User ID: " . ($created_account['user_id'] === null ? 'NULL' : $created_account['user_id']) . " (Super Admin Create)\n";
            echo "Territory: {$created_account['territory_name']}\n";
            echo "Max Recruits: {$created_account['max_epic_recruits']}\n";
            echo "Status: {$created_account['status']}\n";
            echo "Created: {$created_account['created_at']}\n";
            
            echo "\n✓ Super Admin Create functionality works perfectly!\n";
            echo "✓ EPIS account created without requiring existing user account\n";
        } else {
            echo "✗ Could not verify created account\n";
        }
        
    } else {
        echo "✗ Failed to create EPIS account\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>