<?php
/**
 * Test Script untuk Verifikasi Fungsi Aktivasi Member dan Data Sponsor
 */

define('EPIC_LOADED', true);
require_once 'config/database.php';

echo "=== TEST MEMBER ACTIVATION & SPONSOR DATA ===\n\n";

try {
    // 1. Test koneksi database
    echo "1. Testing database connection...\n";
    $db = EpicDatabase::getInstance();
    echo "✓ Database connection successful\n\n";
    
    // 2. Cek struktur tabel
    echo "2. Checking table structure...\n";
    $tables = ['epic_users', 'epic_sponsors'];
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->rowCount() > 0) {
            echo "✓ Table $table exists\n";
        } else {
            echo "✗ Table $table not found\n";
        }
    }
    echo "\n";
    
    // 3. Test data sponsor
    echo "3. Testing sponsor data creation...\n";
    
    $timestamp = time();
    
    // Buat sponsor test
    $sponsor_data = [
        'name' => 'Test Sponsor',
        'email' => "sponsor{$timestamp}@test.com",
        'password' => password_hash('test123', PASSWORD_DEFAULT),
        'referral_code' => "SPONSOR{$timestamp}",
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $sponsor_id = $db->insert('epic_users', $sponsor_data);
    if ($sponsor_id) {
        echo "✓ Test sponsor created with ID: $sponsor_id\n";
    } else {
        echo "✗ Failed to create test sponsor\n";
    }
    
    // 4. Test member
    echo "4. Testing member creation...\n";
    
    $member_data = [
        'name' => 'Test Member',
        'email' => "member{$timestamp}@test.com",
        'password' => password_hash('test123', PASSWORD_DEFAULT),
        'referral_code' => "MEMBER{$timestamp}",
        'status' => 'suspended',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $member_id = $db->insert('epic_users', $member_data);
    if ($member_id) {
        echo "✓ Test member created with ID: $member_id\n";
    } else {
        echo "✗ Failed to create test member\n";
    }
    
    // 5. Test relasi sponsor di tabel epic_sponsors
    echo "5. Testing sponsor relationship...\n";
    
    $sponsor_relation = [
        'user_id' => $member_id,
        'sponsor_id' => $sponsor_id,
        'sponsor_code' => "SPONSOR{$timestamp}",
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $relation_id = $db->insert('epic_sponsors', $sponsor_relation);
    if ($relation_id) {
        echo "✓ Sponsor relationship created with ID: $relation_id\n";
    } else {
        echo "✗ Failed to create sponsor relationship\n";
    }

    // 6. Test query JOIN untuk data sponsor
    echo "6. Testing JOIN query for sponsor data...\n";
    
    $query = "SELECT u.*, 
                     sponsor.name as sponsor_name,
                     sponsor.referral_code as sponsor_code
              FROM epic_users u 
              LEFT JOIN epic_sponsors es ON u.id = es.user_id
              LEFT JOIN epic_users sponsor ON es.sponsor_id = sponsor.id
              WHERE u.id = ?";
    
    $result = $db->query($query, [$member_id]);
    if ($result && $result->rowCount() > 0) {
        $member = $result->fetch(PDO::FETCH_ASSOC);
        echo "✓ JOIN query successful\n";
        echo "  Member: {$member['name']}\n";
        echo "  Sponsor: " . ($member['sponsor_name'] ?? 'None') . "\n";
        echo "  Sponsor Code: " . ($member['sponsor_code'] ?? 'None') . "\n";
    } else {
        echo "✗ JOIN query failed\n";
    }
    echo "\n";

    // 7. Test aktivasi member
    echo "7. Testing member activation...\n";
    
    $update_result = $db->update('epic_users', 
        ['status' => 'active'], 
        'id = ?', 
        [$member_id]
    );
    
    if ($update_result) {
        echo "✓ Member activation successful\n";
        
        // Verifikasi status
        $verify_result = $db->query("SELECT status FROM epic_users WHERE id = ?", [$member_id]);
        if ($verify_result && $verify_result->rowCount() > 0) {
            $updated_member = $verify_result->fetch(PDO::FETCH_ASSOC);
            if ($updated_member['status'] === 'active') {
                echo "✓ Status verified as 'active'\n";
            } else {
                echo "✗ Status verification failed\n";
            }
        } else {
            echo "✗ Status verification query failed\n";
        }
    } else {
        echo "✗ Member activation failed\n";
    }
    echo "\n";

    // 8. Test query lengkap seperti di admin
    echo "8. Testing complete admin query...\n";
    
    $admin_query = "SELECT u.*, 
                           supervisor.name as supervisor_name,
                           supervisor.referral_code as supervisor_code,
                           sponsor.name as sponsor_name,
                           sponsor.referral_code as sponsor_code
                    FROM epic_users u
                    LEFT JOIN epic_users supervisor ON u.epis_supervisor_id = supervisor.id
                    LEFT JOIN epic_sponsors es ON u.id = es.user_id
                    LEFT JOIN epic_users sponsor ON es.sponsor_id = sponsor.id
                    WHERE u.id = ?";
    
    $admin_result = $db->query($admin_query, [$member_id]);
    if ($admin_result && $admin_result->rowCount() > 0) {
        $admin_member = $admin_result->fetch(PDO::FETCH_ASSOC);
        echo "✓ Admin query successful\n";
        echo "  Member: {$admin_member['name']}\n";
        echo "  Status: {$admin_member['status']}\n";
        echo "  Sponsor: " . ($admin_member['sponsor_name'] ?? 'None') . "\n";
    } else {
        echo "✗ Admin query failed\n";
    }
    echo "\n";

    // 9. Cleanup test data
    echo "9. Cleaning up test data...\n";
    
    $db->delete('epic_sponsors', 'user_id = ?', [$member_id]);
    $db->delete('epic_users', 'id = ?', [$member_id]);
    $db->delete('epic_users', 'id = ?', [$sponsor_id]);
    
    echo "✓ Test data cleaned up\n";
    
    echo "\n=== ALL TESTS COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>