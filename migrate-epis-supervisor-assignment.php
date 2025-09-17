<?php
/**
 * EPIS Supervisor Assignment Migration
 * Assigns EPIS supervisors to all existing members (except superadmin)
 * and ensures every new member must have a supervisor
 */

if (!defined('EPIC_INIT')) {
    require_once __DIR__ . '/bootstrap.php';
}

echo "=== EPIS Supervisor Assignment Migration ===\n";
echo "Starting migration process...\n\n";

try {
    // 1. Get all available EPIS supervisors
    echo "1. Checking available EPIS supervisors...\n";
    
    $users_table = db()->table(TABLE_USERS);
    $epis_supervisors = db()->select(
        "SELECT u.id, u.name, ea.epis_code, ea.territory_name, ea.max_epic_recruits, ea.current_epic_count
         FROM {$users_table} u 
         JOIN epic_epis_accounts ea ON u.id = ea.user_id 
         WHERE u.status = 'epis' AND ea.status = 'active'
         ORDER BY ea.current_epic_count ASC, u.name ASC"
    );
    
    if (empty($epis_supervisors)) {
        echo "❌ No active EPIS supervisors found. Creating default EPIS supervisor...\n";
        
        // Create default EPIS supervisor if none exists
        $default_epis_data = [
            'name' => 'EPIS Supervisor Default',
            'email' => 'epis.default@epichub.local',
            'password' => password_hash('EpisDefault123!', PASSWORD_DEFAULT),
            'referral_code' => 'EPIS001',
            'affiliate_code' => 'EPIS001',
            'status' => 'epis',
            'role' => 'member',
            'hierarchy_level' => 3,
            'can_recruit_epic' => true,
            'registration_source' => 'admin_only',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert default EPIS user
        $epis_user_id = db()->insert(TABLE_USERS, $default_epis_data);
        
        // Create EPIS account
        $epis_account_data = [
            'user_id' => $epis_user_id,
            'epis_code' => 'EPIS001',
            'territory_name' => 'Default Territory',
            'territory_description' => 'Default EPIS territory for member assignment',
            'max_epic_recruits' => 0, // Unlimited
            'current_epic_count' => 0,
            'recruitment_commission_rate' => 10.00,
            'indirect_commission_rate' => 5.00,
            'can_manage_benefits' => true,
            'can_view_epic_analytics' => true,
            'status' => 'active',
            'activated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        db()->insert('epic_epis_accounts', $epis_account_data);
        
        echo "✅ Created default EPIS supervisor (ID: {$epis_user_id})\n";
        
        // Refresh EPIS supervisors list
        $users_table = db()->table(TABLE_USERS);
        $epis_supervisors = db()->select(
            "SELECT u.id, u.name, ea.epis_code, ea.territory_name, ea.max_epic_recruits, ea.current_epic_count
             FROM {$users_table} u 
             JOIN epic_epis_accounts ea ON u.id = ea.user_id 
             WHERE u.status = 'epis' AND ea.status = 'active'
             ORDER BY ea.current_epic_count ASC, u.name ASC"
        );
    }
    
    echo "Found " . count($epis_supervisors) . " active EPIS supervisor(s):\n";
    foreach ($epis_supervisors as $supervisor) {
        $capacity = $supervisor['max_epic_recruits'] > 0 ? 
                   "{$supervisor['current_epic_count']}/{$supervisor['max_epic_recruits']}" : 
                   "{$supervisor['current_epic_count']}/Unlimited";
        echo "  - {$supervisor['name']} ({$supervisor['epis_code']}) - Capacity: {$capacity}\n";
    }
    echo "\n";
    
    // 2. Get all members without EPIS supervisor (excluding superadmin)
    echo "2. Finding members without EPIS supervisor...\n";
    
    $users_table = db()->table(TABLE_USERS);
    $members_without_supervisor = db()->select(
        "SELECT id, name, email, status, role 
         FROM {$users_table} 
         WHERE (epis_supervisor_id IS NULL OR epis_supervisor_id = 0) 
         AND role != 'super_admin' 
         AND status IN ('free', 'epic', 'pending')
         ORDER BY created_at ASC"
    );
    
    echo "Found " . count($members_without_supervisor) . " member(s) without supervisor:\n";
    foreach ($members_without_supervisor as $member) {
        echo "  - {$member['name']} ({$member['email']}) - Status: {$member['status']}\n";
    }
    echo "\n";
    
    if (empty($members_without_supervisor)) {
        echo "✅ All members already have EPIS supervisors assigned.\n";
    } else {
        // 3. Assign supervisors using round-robin distribution
        echo "3. Assigning EPIS supervisors...\n";
        
        $supervisor_index = 0;
        $assignments_made = 0;
        
        foreach ($members_without_supervisor as $member) {
            // Select supervisor using round-robin
            $selected_supervisor = $epis_supervisors[$supervisor_index % count($epis_supervisors)];
            
            // Check capacity if supervisor has limits
            if ($selected_supervisor['max_epic_recruits'] > 0 && 
                $selected_supervisor['current_epic_count'] >= $selected_supervisor['max_epic_recruits']) {
                
                // Find next available supervisor
                $found_available = false;
                for ($i = 0; $i < count($epis_supervisors); $i++) {
                    $check_supervisor = $epis_supervisors[($supervisor_index + $i) % count($epis_supervisors)];
                    if ($check_supervisor['max_epic_recruits'] == 0 || 
                        $check_supervisor['current_epic_count'] < $check_supervisor['max_epic_recruits']) {
                        $selected_supervisor = $check_supervisor;
                        $supervisor_index = ($supervisor_index + $i) % count($epis_supervisors);
                        $found_available = true;
                        break;
                    }
                }
                
                if (!$found_available) {
                    echo "⚠️  All supervisors at capacity. Assigning to first supervisor anyway.\n";
                    $selected_supervisor = $epis_supervisors[0];
                }
            }
            
            // Update member with supervisor
            $update_result = db()->update(TABLE_USERS, [
                'epis_supervisor_id' => $selected_supervisor['id'],
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$member['id']]);
            
            if ($update_result) {
                echo "  ✅ Assigned {$member['name']} to {$selected_supervisor['name']} ({$selected_supervisor['epis_code']})\n";
                
                // Update supervisor's current count
                $epis_supervisors[$supervisor_index % count($epis_supervisors)]['current_epic_count']++;
                
                // Log the assignment
                epic_log_activity($member['id'], 'epis_supervisor_assigned', 
                    "Assigned to EPIS supervisor: {$selected_supervisor['name']} ({$selected_supervisor['epis_code']}) via migration");
                
                epic_log_activity($selected_supervisor['id'], 'new_member_assigned', 
                    "New member assigned via migration: {$member['name']} ({$member['email']})");
                
                $assignments_made++;
            } else {
                echo "  ❌ Failed to assign {$member['name']}\n";
            }
            
            $supervisor_index++;
        }
        
        echo "\n✅ Migration completed. {$assignments_made} assignments made.\n";
    }
    
    // 4. Update EPIS accounts current_epic_count
    echo "\n4. Updating EPIS supervisor counts...\n";
    
    foreach ($epis_supervisors as $supervisor) {
        $users_table = db()->table(TABLE_USERS);
        $actual_count = db()->selectValue(
            "SELECT COUNT(*) FROM {$users_table} WHERE epis_supervisor_id = ? AND status IN ('free', 'epic')",
            [$supervisor['id']]
        );
        
        db()->query(
            "UPDATE epic_epis_accounts SET current_epic_count = ?, updated_at = ? WHERE user_id = ?",
            [$actual_count, date('Y-m-d H:i:s'), $supervisor['id']]
        );
        
        echo "  ✅ Updated {$supervisor['name']}: {$actual_count} members\n";
    }
    
    // 5. Ensure validation for new registrations
    echo "\n5. Checking registration validation settings...\n";
    
    $settings_table = db()->table(TABLE_SETTINGS);
    $epis_required_setting = db()->selectOne(
        "SELECT * FROM {$settings_table} WHERE `key` = 'epis_registration_required'"
    );
    
    if (!$epis_required_setting) {
        // Create setting to require EPIS supervisor for new registrations
        $settings_table = db()->table(TABLE_SETTINGS);
        db()->query(
            "INSERT INTO {$settings_table} (`key`, `value`, `type`, `group`, `description`) VALUES (?, ?, ?, ?, ?)",
            ['epis_registration_required', '1', 'boolean', 'registration', 'Require EPIS supervisor assignment for new member registrations']
        );
        echo "  ✅ Created setting: epis_registration_required = 1\n";
    } else {
        // Update existing setting
        db()->update(TABLE_SETTINGS, [
            'value' => '1'
        ], 'key = ?', ['epis_registration_required']);
        echo "  ✅ Updated setting: epis_registration_required = 1\n";
    }
    
    // 6. Summary report
    echo "\n=== MIGRATION SUMMARY ===\n";
    
    $users_table = db()->table(TABLE_USERS);
    $total_members = db()->selectValue(
        "SELECT COUNT(*) FROM {$users_table} WHERE role != 'super_admin'"
    );
    
    $members_with_supervisor = db()->selectValue(
        "SELECT COUNT(*) FROM {$users_table} WHERE epis_supervisor_id IS NOT NULL AND epis_supervisor_id > 0 AND role != 'super_admin'"
    );
    
    $members_without_supervisor = $total_members - $members_with_supervisor;
    
    echo "Total Members: {$total_members}\n";
    echo "With Supervisor: {$members_with_supervisor}\n";
    echo "Without Supervisor: {$members_without_supervisor}\n";
    echo "Total EPIS Supervisors: " . count($epis_supervisors) . "\n";
    
    if ($members_without_supervisor == 0) {
        echo "\n🎉 SUCCESS: All members now have EPIS supervisors assigned!\n";
    } else {
        echo "\n⚠️  WARNING: {$members_without_supervisor} members still without supervisors.\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== END MIGRATION ===\n";
?>