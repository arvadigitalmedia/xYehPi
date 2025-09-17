<?php
/**
 * Create Test EPIS Account
 * Script untuk membuat EPIS account untuk testing sistem hierarki
 */

require_once __DIR__ . '/bootstrap.php';

try {
    // Test EPIS user data
    $epis_user = [
        'name' => 'EPIS Test Supervisor',
        'email' => 'epis@epichub.com',
        'password' => 'epis123',
        'phone' => '+6281234567892',
        'status' => 'epic', // Will be updated to epis
        'role' => 'user'
    ];
    
    // Check if user already exists
    $existing_user = epic_get_user_by_email($epis_user['email']);
    if ($existing_user) {
        echo "EPIS user already exists with ID: " . $existing_user['id'] . "\n";
        echo "Email: " . $existing_user['email'] . "\n";
        echo "Name: " . $existing_user['name'] . "\n";
        echo "Status: " . $existing_user['status'] . "\n";
        echo "Role: " . $existing_user['role'] . "\n";
        
        // Check if EPIS account exists
        $epis_account = epic_get_epis_account($existing_user['id']);
        if ($epis_account) {
            echo "\n=== EPIS ACCOUNT DETAILS ===\n";
            echo "EPIS Code: " . $epis_account['epis_code'] . "\n";
            echo "Territory: " . $epis_account['territory_name'] . "\n";
            echo "Status: " . $epis_account['status'] . "\n";
        }
        
        echo "\n=== EPIS LOGIN CREDENTIALS ===\n";
        echo "Email: epis@epichub.com\n";
        echo "Password: epis123\n";
        echo "Login URL: http://localhost:8000/login\n";
        exit;
    }
    
    // Generate UUID and referral code
    $uuid = epic_generate_uuid();
    $referral_code = epic_generate_referral_code();
    
    // Hash password
    $hashed_password = password_hash($epis_user['password'], PASSWORD_DEFAULT);
    
    // Prepare user data for database
    $user_data = [
        'uuid' => $uuid,
        'name' => $epis_user['name'],
        'email' => $epis_user['email'],
        'password' => $hashed_password,
        'phone' => $epis_user['phone'],
        'referral_code' => $referral_code,
        'status' => 'epic', // Start as EPIC, will be promoted
        'role' => $epis_user['role'],
        'hierarchy_level' => 2, // Will be updated to 3
        'email_verified_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Insert user into database
    $user_id = db()->insert('users', $user_data);
    
    if ($user_id) {
        echo "✅ EPIS test user created successfully!\n";
        echo "User ID: " . $user_id . "\n";
        echo "UUID: " . $uuid . "\n";
        echo "Referral Code: " . $referral_code . "\n";
        
        // Now create EPIS account
        echo "\n=== CREATING EPIS ACCOUNT ===\n";
        
        $epis_data = [
            'territory_name' => 'Jakarta Metropolitan Area',
            'territory_description' => 'Covers Jakarta, Bogor, Depok, Tangerang, and Bekasi areas with focus on digital marketing and e-commerce development.',
            'max_epic_recruits' => 50, // Allow up to 50 EPIC recruits
            'recruitment_commission_rate' => 15.00, // 15% for direct recruitment
            'indirect_commission_rate' => 7.50 // 7.5% for indirect recruitment
        ];
        
        $epis_id = epic_create_epis_account($user_id, $epis_data);
        
        if ($epis_id) {
            echo "✅ EPIS account created successfully!\n";
            
            // Get EPIS account details
            $epis_account = epic_get_epis_account($user_id);
            
            echo "\n=== EPIS ACCOUNT DETAILS ===\n";
            echo "EPIS ID: " . $epis_id . "\n";
            echo "EPIS Code: " . $epis_account['epis_code'] . "\n";
            echo "Territory: " . $epis_account['territory_name'] . "\n";
            echo "Max EPIC Recruits: " . $epis_account['max_epic_recruits'] . "\n";
            echo "Direct Commission Rate: " . $epis_account['recruitment_commission_rate'] . "%\n";
            echo "Indirect Commission Rate: " . $epis_account['indirect_commission_rate'] . "%\n";
            echo "Status: " . $epis_account['status'] . "\n";
            
            echo "\n=== EPIS LOGIN CREDENTIALS ===\n";
            echo "Email: " . $epis_user['email'] . "\n";
            echo "Password: " . $epis_user['password'] . "\n";
            
            echo "\n=== ACCESS URLS ===\n";
            echo "Login: http://localhost:8000/login\n";
            echo "Admin EPIS Management: http://localhost:8000/admin/manage/epis\n";
            echo "EPIC Registration (with EPIS): http://localhost:8000/register-epic\n";
            
            echo "\n=== EPIS CAPABILITIES ===\n";
            echo "✅ Can recruit EPIC accounts directly\n";
            echo "✅ Receives commission from direct EPIC recruitment (15%)\n";
            echo "✅ Receives commission from indirect recruitment via EPIC network (7.5%)\n";
            echo "✅ Can manage benefits for EPIC accounts in network\n";
            echo "✅ Can view analytics for entire network\n";
            echo "✅ Territory management for Jakarta Metropolitan Area\n";
            
            echo "\n=== TESTING SCENARIOS ===\n";
            echo "1. Login as EPIS account and access admin panel\n";
            echo "2. Create EPIC accounts through EPIS recruitment\n";
            echo "3. Test commission calculation system\n";
            echo "4. Manage EPIC network and benefits\n";
            echo "5. View hierarchical analytics and reports\n";
            
            echo "\n=== HIERARCHY TESTING ===\n";
            echo "Free Account: testmember@epichub.com / testmember123\n";
            echo "EPIC Account: premium@epichub.com / premium123\n";
            echo "EPIS Account: epis@epichub.com / epis123\n";
            echo "\nTest the complete hierarchy: Free → EPIC → EPIS!\n";
            
        } else {
            echo "❌ Failed to create EPIS account\n";
        }
        
    } else {
        echo "❌ Failed to create EPIS test user\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating EPIS test account: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>