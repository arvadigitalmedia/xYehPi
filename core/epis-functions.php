<?php
/**
 * EPIS Account System Functions
 * Core functions for hierarchical account management
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// =====================================================
// EPIS ACCOUNT MANAGEMENT
// =====================================================

/**
 * Create EPIS Account
 */
function epic_create_epis_account($user_id, $data = []) {
    try {
        // Validate user exists and is eligible
        $user = epic_get_user($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Check if user already has EPIS account
        $existing = db()->selectOne(
            "SELECT id FROM epic_epis_accounts WHERE user_id = ?",
            [$user_id]
        );
        
        if ($existing) {
            throw new Exception('User already has EPIS account');
        }
        
        // Generate unique EPIS code
        $epis_code = epic_generate_epis_code();
        
        // Prepare EPIS account data
        $epis_data = [
            'user_id' => $user_id,
            'epis_code' => $epis_code,
            'territory_name' => $data['territory_name'] ?? null,
            'territory_description' => $data['territory_description'] ?? null,
            'max_epic_recruits' => $data['max_epic_recruits'] ?? 0,
            'recruitment_commission_rate' => $data['recruitment_commission_rate'] ?? 10.00,
            'indirect_commission_rate' => $data['indirect_commission_rate'] ?? 5.00,
            'can_manage_benefits' => $data['can_manage_benefits'] ?? true,
            'can_view_epic_analytics' => $data['can_view_epic_analytics'] ?? true,
            'status' => 'active',
            'activated_at' => date('Y-m-d H:i:s'),
            'activated_by' => epic_current_user()['id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert EPIS account
        $columns = implode(', ', array_map(function($col) { return '`' . $col . '`'; }, array_keys($epis_data)));
        $placeholders = ':' . implode(', :', array_keys($epis_data));
        $sql = "INSERT INTO epic_epis_accounts ({$columns}) VALUES ({$placeholders})";
        
        $params = [];
        foreach ($epis_data as $key => $value) {
            $params[':' . $key] = $value;
        }
        
        db()->query($sql, $params);
        $epis_id = db()->getConnection()->lastInsertId();
        
        if (!$epis_id) {
            throw new Exception('Failed to create EPIS account');
        }
        
        // Update user status and hierarchy
        $user_update = [
            'status' => 'epis',
            'hierarchy_level' => 3,
            'can_recruit_epic' => true,
            'registration_source' => 'admin_only',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        db()->update('users', $user_update, 'id = ?', [$user_id]);
        
        // Log activity
        epic_log_activity($user_id, 'epis_account_created', "EPIS account created with code: {$epis_code}");
        
        return $epis_id;
        
    } catch (Exception $e) {
        error_log("EPIS Account Creation Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Generate unique EPIS code
 */
function epic_generate_epis_code() {
    do {
        $code = 'EPIS' . strtoupper(substr(md5(uniqid()), 0, 6));
        $exists = db()->selectValue(
            "SELECT COUNT(*) FROM epic_epis_accounts WHERE epis_code = ?",
            [$code]
        );
    } while ($exists > 0);
    
    return $code;
}

/**
 * Get EPIS account details
 */
function epic_get_epis_account($user_id) {
    return db()->selectOne(
        "SELECT ea.*, u.name, u.email, u.status as user_status
         FROM epic_epis_accounts ea
         JOIN epic_users u ON ea.user_id = u.id
         WHERE ea.user_id = ?",
        [$user_id]
    );
}

/**
 * Get all EPIS accounts
 */
function epic_get_all_epis_accounts($filters = []) {
    $where = '1=1';
    $params = [];
    
    if (!empty($filters['status'])) {
        $where .= ' AND ea.status = ?';
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['search'])) {
        $where .= ' AND (COALESCE(u.name, "Unknown User") LIKE ? OR COALESCE(u.email, "") LIKE ? OR ea.epis_code LIKE ?)';
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }
    
    return db()->select(
        "SELECT ea.*, 
                COALESCE(u.name, 'Unknown User') as name, 
                COALESCE(u.email, 'no-email@invalid.com') as email, 
                COALESCE(u.phone, '-') as phone, 
                COALESCE(u.status, 'invalid') as user_status,
                COUNT(en.epic_user_id) as network_size,
                COALESCE(SUM(en.total_commissions_earned), 0) as total_commissions,
                DATE_FORMAT(ea.created_at, '%d %b %Y %H:%i') as formatted_created_at
         FROM epic_epis_accounts ea
         LEFT JOIN epic_users u ON ea.user_id = u.id AND ea.user_id > 0
         LEFT JOIN epic_epis_networks en ON ea.user_id = en.epis_id AND en.status = 'active'
         WHERE {$where}
         GROUP BY ea.id, ea.user_id, ea.epis_code, ea.territory_name, ea.status, ea.created_at, 
                  u.name, u.email, u.phone, u.status
         ORDER BY ea.created_at DESC",
        $params
    );
}

// =====================================================
// EPIS NETWORK MANAGEMENT
// =====================================================

/**
 * Add EPIC user to EPIS network
 */
function epic_add_to_epis_network($epis_id, $epic_user_id, $recruitment_type = 'direct', $recruited_by_epic_id = null) {
    try {
        // Validate EPIS account
        $epis = epic_get_epis_account($epis_id);
        if (!$epis) {
            throw new Exception('EPIS account not found');
        }
        
        // Validate EPIC user
        $epic_user = epic_get_user($epic_user_id);
        if (!$epic_user || $epic_user['status'] !== 'epic') {
            throw new Exception('Invalid EPIC user');
        }
        
        // Check if already in network
        $existing = db()->selectOne(
            "SELECT id FROM epic_epis_networks WHERE epic_user_id = ?",
            [$epic_user_id]
        );
        
        if ($existing) {
            throw new Exception('EPIC user already assigned to EPIS network');
        }
        
        // Determine commission rate
        $commission_rate = ($recruitment_type === 'direct') 
            ? $epis['recruitment_commission_rate'] 
            : $epis['indirect_commission_rate'];
        
        // Add to network
        $network_data = [
            'epis_id' => $epis_id,
            'epic_user_id' => $epic_user_id,
            'recruitment_type' => $recruitment_type,
            'recruited_by_epic_id' => $recruited_by_epic_id,
            'recruitment_date' => date('Y-m-d H:i:s'),
            'commission_rate' => $commission_rate,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $network_id = db()->insert('epic_epis_networks', $network_data);
        
        if (!$network_id) {
            throw new Exception('Failed to add to EPIS network');
        }
        
        // Update EPIC user supervisor
        db()->update('epic_users', 
            [
                'epis_supervisor_id' => $epis_id,
                'supervisor_locked' => true,
                'updated_at' => date('Y-m-d H:i:s')
            ], 
            'id = ?', 
            [$epic_user_id]
        );
        
        // Update EPIS current count
        db()->query(
            "UPDATE epic_epis_accounts SET current_epic_count = current_epic_count + 1 WHERE user_id = ?",
            [$epis_id]
        );
        
        // Log activity
        epic_log_activity($epis_id, 'epic_added_to_network', "EPIC user {$epic_user['name']} added to network");
        epic_log_activity($epic_user_id, 'assigned_to_epis', "Assigned to EPIS {$epis['name']}");
        
        return $network_id;
        
    } catch (Exception $e) {
        error_log("EPIS Network Addition Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Get EPIS network members
 */
function epic_get_epis_network($epis_id, $filters = []) {
    $where = 'en.epis_id = ? AND en.status = ?';
    $params = [$epis_id, 'active'];
    
    if (!empty($filters['recruitment_type'])) {
        $where .= ' AND en.recruitment_type = ?';
        $params[] = $filters['recruitment_type'];
    }
    
    return db()->select(
        "SELECT en.*, u.name, u.email, u.phone, u.created_at as user_created_at,
                recruiter.name as recruited_by_name
         FROM epic_epis_networks en
         JOIN epic_users u ON en.epic_user_id = u.id
         LEFT JOIN epic_users recruiter ON en.recruited_by_epic_id = recruiter.id
         WHERE {$where}
         ORDER BY en.recruitment_date DESC",
        $params
    );
}

/**
 * Get EPIS network statistics
 */
function epic_get_epis_network_stats($epis_id) {
    return db()->selectOne(
        "SELECT 
            COUNT(en.epic_user_id) as total_network,
            COUNT(CASE WHEN en.recruitment_type = 'direct' THEN 1 END) as direct_recruits,
            COUNT(CASE WHEN en.recruitment_type = 'indirect' THEN 1 END) as indirect_recruits,
            SUM(en.total_commissions_earned) as total_commissions,
            AVG(en.commission_rate) as avg_commission_rate
         FROM epic_epis_networks en
         JOIN epic_users u ON en.epic_user_id = u.id
         WHERE en.epis_id = ? AND en.status = 'active' AND u.status = 'epic'",
        [$epis_id]
    );
}

// =====================================================
// COMMISSION SYSTEM
// =====================================================

/**
 * Calculate EPIS commission
 */
function epic_calculate_epis_commission($transaction_id, $source_user_id, $amount) {
    try {
        $source_user = epic_get_user($source_user_id);
        if (!$source_user) {
            return false;
        }
        
        // Get EPIS supervisor
        if (!$source_user['epis_supervisor_id']) {
            return false; // No EPIS supervisor
        }
        
        $epis_user = epic_get_user($source_user['epis_supervisor_id']);
        if (!$epis_user || $epis_user['status'] !== 'epis') {
            return false;
        }
        
        // Get network relationship
        $network = db()->selectOne(
            "SELECT * FROM epic_epis_networks 
             WHERE epis_id = ? AND epic_user_id = ? AND status = 'active'",
            [$source_user['epis_supervisor_id'], $source_user_id]
        );
        
        if (!$network) {
            return false;
        }
        
        // Calculate commission
        $commission_amount = ($amount * $network['commission_rate']) / 100;
        
        // Create commission record
        $commission_data = [
            'transaction_id' => $transaction_id,
            'source_user_id' => $source_user_id,
            'recipient_user_id' => $source_user['epis_supervisor_id'],
            'recipient_level' => 'epis',
            'commission_amount' => $commission_amount,
            'commission_percentage' => $network['commission_rate'],
            'original_amount' => $amount,
            'distribution_type' => $network['recruitment_type'] === 'direct' ? 'direct' : 'indirect',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert commission distribution (if table exists)
        try {
            $commission_id = db()->insert('epic_commission_distributions', $commission_data);
            
            // Update network total commissions
            db()->query(
                "UPDATE epic_epis_networks SET total_commissions_earned = total_commissions_earned + ? WHERE id = ?",
                [$commission_amount, $network['id']]
            );
            
            return $commission_id;
        } catch (Exception $e) {
            // Table might not exist yet, log for future implementation
            error_log("Commission distribution table not available: " . $e->getMessage());
            return false;
        }
        
    } catch (Exception $e) {
        error_log("EPIS Commission Calculation Error: " . $e->getMessage());
        return false;
    }
}

// =====================================================
// REGISTRATION SYSTEM
// =====================================================

/**
 * Create registration invitation
 */
function epic_create_registration_invitation($invited_by, $target_level, $data = []) {
    try {
        // Generate unique invitation code
        $invitation_code = epic_generate_invitation_code();
        
        // Determine invited_by_type
        $inviter = epic_get_user($invited_by);
        $invited_by_type = ($inviter['role'] === 'super_admin') ? 'admin' : 'epis';
        
        $invitation_data = [
            'invitation_code' => $invitation_code,
            'invited_by' => $invited_by,
            'invited_by_type' => $invited_by_type,
            'target_level' => $target_level,
            'target_email' => $data['target_email'] ?? null,
            'target_phone' => $data['target_phone'] ?? null,
            'invitation_message' => $data['invitation_message'] ?? null,
            'epis_supervisor_id' => $data['epis_supervisor_id'] ?? null,
            'max_uses' => $data['max_uses'] ?? 1,
            'expires_at' => $data['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+30 days')),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return db()->insert('epic_registration_invitations', $invitation_data);
        
    } catch (Exception $e) {
        error_log("Registration Invitation Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Generate unique invitation code
 */
function epic_generate_invitation_code() {
    do {
        $code = 'INV' . strtoupper(substr(md5(uniqid()), 0, 8));
        $exists = db()->selectValue(
            "SELECT COUNT(*) FROM epic_registration_invitations WHERE invitation_code = ?",
            [$code]
        );
    } while ($exists > 0);
    
    return $code;
}

/**
 * Validate registration invitation
 */
function epic_validate_registration_invitation($invitation_code) {
    $invitation = db()->selectOne(
        "SELECT * FROM epic_registration_invitations 
         WHERE invitation_code = ? AND status = 'active' AND expires_at > NOW()",
        [$invitation_code]
    );
    
    if (!$invitation) {
        return false;
    }
    
    // Check usage limit
    if ($invitation['used_count'] >= $invitation['max_uses']) {
        return false;
    }
    
    return $invitation;
}

/**
 * Use registration invitation
 */
function epic_use_registration_invitation($invitation_code, $registered_user_id) {
    try {
        // Update invitation usage
        db()->query(
            "UPDATE epic_registration_invitations 
             SET used_count = used_count + 1, updated_at = NOW() 
             WHERE invitation_code = ?",
            [$invitation_code]
        );
        
        // Record usage
        $usage_data = [
            'invitation_id' => db()->selectValue(
                "SELECT id FROM epic_registration_invitations WHERE invitation_code = ?",
                [$invitation_code]
            ),
            'registered_user_id' => $registered_user_id,
            'registration_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'registration_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'used_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert usage record (if table exists)
        try {
            return db()->insert('epic_registration_usage', $usage_data);
        } catch (Exception $e) {
            // Table might not exist yet
            error_log("Registration usage table not available: " . $e->getMessage());
            return true;
        }
        
    } catch (Exception $e) {
        error_log("Registration Invitation Usage Error: " . $e->getMessage());
        return false;
    }
}

// =====================================================
// HIERARCHY HELPER FUNCTIONS
// =====================================================

/**
 * Get user hierarchy level name
 */
function epic_get_hierarchy_level_name($level) {
    $levels = [
        1 => 'Free Account',
        2 => 'EPIC Account', 
        3 => 'EPIS Account'
    ];
    
    return $levels[$level] ?? 'Unknown';
}

/**
 * Check if user can recruit EPIC accounts
 */
function epic_can_recruit_epic($user_id) {
    $user = epic_get_user($user_id);
    return $user && ($user['can_recruit_epic'] || $user['status'] === 'epis');
}

/**
 * Get available EPIS supervisors for registration
 */
function epic_get_available_epis_supervisors() {
    return db()->select(
        "SELECT u.id, u.name, u.email, ea.epis_code, ea.territory_name,
                ea.current_epic_count, ea.max_epic_recruits
         FROM epic_users u
         JOIN epic_epis_accounts ea ON u.id = ea.user_id
         WHERE u.status = 'epis' AND ea.status = 'active'
           AND (ea.max_epic_recruits = 0 OR ea.current_epic_count < ea.max_epic_recruits)
         ORDER BY u.name ASC"
    );
}

/**
 * Update user hierarchy level
 */
function epic_update_user_hierarchy($user_id, $new_status) {
    $hierarchy_levels = [
        'free' => 1,
        'epic' => 2,
        'epis' => 3
    ];
    
    $hierarchy_level = $hierarchy_levels[$new_status] ?? 1;
    
    return db()->update('epic_users', 
        [
            'status' => $new_status,
            'hierarchy_level' => $hierarchy_level,
            'updated_at' => date('Y-m-d H:i:s')
        ], 
        'id = ?', 
        [$user_id]
    );
}

/**
 * Get user's EPIS supervisor
 */
function epic_get_user_epis_supervisor($user_id) {
    return db()->selectOne(
        "SELECT supervisor.*, ea.epis_code, ea.territory_name
         FROM epic_users u
         JOIN epic_users supervisor ON u.epis_supervisor_id = supervisor.id
         LEFT JOIN epic_epis_accounts ea ON supervisor.id = ea.user_id
         WHERE u.id = ?",
        [$user_id]
    );
}

/**
 * Auto-assign EPIS supervisor based on EPIC referrer
 * This function finds the EPIS supervisor of an EPIC referrer and assigns it to the new user
 */
function epic_auto_assign_epis_from_referral($user_id, $referral_code) {
    if (empty($referral_code)) {
        return false;
    }
    
    try {
        // Get the referrer (EPIC user) by referral code
        $referrer = epic_get_user_by_affiliate_code($referral_code);
        
        // Fallback to old referral_code system if not found
        if (!$referrer) {
            $referrer = epic_get_user_by_referral_code($referral_code);
        }
        
        if (!$referrer) {
            return false;
        }
        
        // Check if referrer is EPIC and has EPIS supervisor
        if ($referrer['status'] !== 'epic' || empty($referrer['epis_supervisor_id'])) {
            return false;
        }
        
        // Get the EPIS supervisor
        $epis_supervisor = epic_get_user($referrer['epis_supervisor_id']);
        if (!$epis_supervisor || $epis_supervisor['status'] !== 'epis') {
            return false;
        }
        
        // Check EPIS account status and capacity
        $epis_account = epic_get_epis_account($referrer['epis_supervisor_id']);
        if (!$epis_account || $epis_account['status'] !== 'active') {
            return false;
        }
        
        // Check capacity if limited
        if ($epis_account['max_epic_recruits'] > 0 && 
            $epis_account['current_epic_count'] >= $epis_account['max_epic_recruits']) {
            return false;
        }
        
        // Assign EPIS supervisor to the new user
        $updated = db()->update('epic_users', [
            'epis_supervisor_id' => $referrer['epis_supervisor_id'],
            'status' => 'epic',
            'hierarchy_level' => 2,
            'registration_source' => 'epis_recruit',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$user_id]);
        
        if ($updated) {
            // Add to EPIS network as indirect recruitment (through EPIC referral)
            epic_add_to_epis_network($referrer['epis_supervisor_id'], $user_id, 'indirect', $referrer['id']);
            
            // Log the auto-assignment
            epic_log_activity($user_id, 'epis_auto_assigned', 
                "Auto-assigned to EPIS supervisor {$referrer['epis_supervisor_id']} via EPIC referrer {$referrer['id']}");
            
            epic_log_activity($referrer['epis_supervisor_id'], 'epis_indirect_recruit', 
                "New indirect recruit {$user_id} via EPIC member {$referrer['id']}");
            
            return [
                'success' => true,
                'epis_supervisor_id' => $referrer['epis_supervisor_id'],
                'epis_supervisor' => $epis_supervisor,
                'referrer_id' => $referrer['id'],
                'recruitment_type' => 'indirect'
            ];
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log('Error in epic_auto_assign_epis_from_referral: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check if EPIC-EPIS connection is valid
 */
function epic_validate_epic_epis_connection($epic_user_id, $epis_supervisor_id) {
    // Get EPIC user
    $epic_user = epic_get_user($epic_user_id);
    if (!$epic_user || $epic_user['status'] !== 'epic') {
        return [
            'valid' => false,
            'error' => 'Invalid EPIC user or user is not EPIC status'
        ];
    }
    
    // Get EPIS supervisor
    $epis_supervisor = epic_get_user($epis_supervisor_id);
    if (!$epis_supervisor || $epis_supervisor['status'] !== 'epis') {
        return [
            'valid' => false,
            'error' => 'Invalid EPIS supervisor or user is not EPIS status'
        ];
    }
    
    // Check if EPIC user is already assigned to this EPIS
    if ($epic_user['epis_supervisor_id'] != $epis_supervisor_id) {
        return [
            'valid' => false,
            'error' => 'EPIC user is not assigned to this EPIS supervisor'
        ];
    }
    
    // Check EPIS account status
    $epis_account = epic_get_epis_account($epis_supervisor_id);
    if (!$epis_account || $epis_account['status'] !== 'active') {
        return [
            'valid' => false,
            'error' => 'EPIS account is not active'
        ];
    }
    
    // Check if connection exists in EPIS network
    $network_connection = db()->selectOne(
        "SELECT * FROM epic_epis_networks 
         WHERE epis_id = ? AND epic_user_id = ? AND status = 'active'",
        [$epis_supervisor_id, $epic_user_id]
    );
    
    if (!$network_connection) {
        return [
            'valid' => false,
            'error' => 'No active network connection found between EPIC and EPIS'
        ];
    }
    
    return [
        'valid' => true,
        'epic_user' => $epic_user,
        'epis_supervisor' => $epis_supervisor,
        'epis_account' => $epis_account,
        'network_connection' => $network_connection
    ];
}

// =====================================================
// VALIDATION FUNCTIONS
// =====================================================

/**
 * Validate EPIS account creation data
 */
function epic_validate_epis_data($data) {
    $errors = [];
    
    if (empty($data['territory_name'])) {
        $errors[] = 'Territory name is required';
    }
    
    if (isset($data['max_epic_recruits']) && $data['max_epic_recruits'] < 0) {
        $errors[] = 'Maximum EPIC recruits cannot be negative';
    }
    
    if (isset($data['recruitment_commission_rate']) && 
        ($data['recruitment_commission_rate'] < 0 || $data['recruitment_commission_rate'] > 100)) {
        $errors[] = 'Recruitment commission rate must be between 0 and 100';
    }
    
    if (isset($data['indirect_commission_rate']) && 
        ($data['indirect_commission_rate'] < 0 || $data['indirect_commission_rate'] > 100)) {
        $errors[] = 'Indirect commission rate must be between 0 and 100';
    }
    
    return $errors;
}

/**
 * Check if user can be promoted to EPIS
 */
function epic_can_promote_to_epis($user_id) {
    $user = epic_get_user($user_id);
    
    if (!$user) {
        return false;
    }
    
    // Must be EPIC account
    if ($user['status'] !== 'epic') {
        return false;
    }
    
    // Must not already have EPIS account
    $existing_epis = db()->selectValue(
        "SELECT COUNT(*) FROM epic_epis_accounts WHERE user_id = ?",
        [$user_id]
    );
    
    return $existing_epis === 0;
}

?>