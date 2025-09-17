<?php
/**
 * Create EPIS Account for Existing User
 * Convert existing user to EPIS account
 */

require_once __DIR__ . '/bootstrap.php';

try {
    $user_id = 5; // The existing user ID
    
    echo "Creating EPIS account for user ID: {$user_id}\n";
    
    // Get user details
    $user = epic_get_user($user_id);
    if (!$user) {
        throw new Exception('User not found');
    }
    
    echo "User: {$user['name']} ({$user['email']})\n";
    
    // Check if EPIS account already exists
    $existing_epis = db()->selectOne(
        "SELECT * FROM epic_epis_accounts WHERE user_id = ?",
        [$user_id]
    );
    
    if ($existing_epis) {
        echo "EPIS account already exists!\n";
        echo "EPIS Code: {$existing_epis['epis_code']}\n";
        echo "Territory: {$existing_epis['territory_name']}\n";
        echo "Status: {$existing_epis['status']}\n";
    } else {
        // Create EPIS account
        echo "\n=== CREATING EPIS ACCOUNT ===\n";
        
        $epis_data = [
            'territory_name' => 'Jakarta Metropolitan Area',
            'territory_description' => 'Covers Jakarta, Bogor, Depok, Tangerang, and Bekasi areas with focus on digital marketing and e-commerce development.',
            'max_epic_recruits' => 50,
            'recruitment_commission_rate' => 15.00,
            'indirect_commission_rate' => 7.50
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
        } else {
            echo "❌ Failed to create EPIS account\n";
        }
    }
    
    echo "\n=== EPIS LOGIN CREDENTIALS ===\n";
    echo "Email: epis@epichub.com\n";
    echo "Password: epis123\n";
    
    echo "\n=== ACCESS URLS ===\n";
    echo "Login: http://localhost:8000/login\n";
    echo "Admin EPIS Management: http://localhost:8000/admin/manage/epis\n";
    echo "EPIC Registration (with EPIS): http://localhost:8000/register-epic\n";
    
    echo "\n=== HIERARCHY TESTING ===\n";
    echo "Free Account: testmember@epichub.com / testmember123\n";
    echo "EPIC Account: premium@epichub.com / premium123\n";
    echo "EPIS Account: epis@epichub.com / epis123\n";
    echo "\nTest the complete hierarchy: Free → EPIC → EPIS!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>