<?php
/**
 * EPIC Hub - Sponsor System Functions
 * Sistem sponsor/referral seperti SimpleAff Pro
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Create sponsor record for new user
 */
function epic_create_sponsor($user_id, $sponsor_id = null, $sponsor_code = null) {
    try {
        // Get user data
        $user = epic_get_user($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Determine sponsor
        $sponsor = null;
        if ($sponsor_id) {
            $sponsor = epic_get_user($sponsor_id);
        } elseif ($sponsor_code) {
            $sponsor = epic_get_user_by_affiliate_code($sponsor_code);
        }
        
        // Calculate level and network path
        $level = 1;
        $network_path = (string)$user_id;
        
        if ($sponsor) {
            $sponsor_record = epic_get_sponsor_by_user($sponsor['id']);
            if ($sponsor_record) {
                $level = $sponsor_record['level'] + 1;
                $network_path = $sponsor_record['network_path'] . ',' . $user_id;
            }
        }
        
        // Create sponsor record
        $sponsor_data = [
            'user_id' => $user_id,
            'sponsor_id' => $sponsor ? $sponsor['id'] : null,
            'sponsor_code' => $sponsor ? $sponsor['affiliate_code'] : null,
            'level' => $level,
            'network_path' => $network_path,
            'commission_rate' => 10.00, // Default 10%
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = db()->insert('sponsors', $sponsor_data);
        
        if ($result) {
            // Update sponsor's referral count
            if ($sponsor) {
                epic_update_sponsor_stats($sponsor['id']);
            }
            
            // Log activity
            epic_log_activity($user_id, 'sponsor_created', 'Sponsor record created');
            if ($sponsor) {
                epic_log_activity($sponsor['id'], 'referral_added', 'New referral: ' . $user['name']);
            }
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Error creating sponsor: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get sponsor record by user ID
 */
function epic_get_sponsor_by_user($user_id) {
    return db()->selectOne(
        "SELECT * FROM " . db()->table('sponsors') . " WHERE user_id = ?",
        [$user_id]
    );
}

/**
 * Get user by affiliate code
 */
function epic_get_user_by_affiliate_code($affiliate_code) {
    return db()->selectOne(
        "SELECT * FROM " . db()->table('users') . " WHERE affiliate_code = ?",
        [$affiliate_code]
    );
}

/**
 * Get sponsor's referrals
 */
function epic_get_sponsor_referrals($sponsor_id, $level = null) {
    $sql = "SELECT u.*, s.level, s.commission_rate, s.total_commission, s.created_at as joined_at
            FROM " . db()->table('sponsors') . " s
            JOIN " . db()->table('users') . " u ON s.user_id = u.id
            WHERE s.sponsor_id = ?";
    
    $params = [$sponsor_id];
    
    if ($level !== null) {
        $sql .= " AND s.level = ?";
        $params[] = $level;
    }
    
    $sql .= " ORDER BY s.created_at DESC";
    
    return db()->select($sql, $params);
}

/**
 * Update sponsor statistics
 */
function epic_update_sponsor_stats($sponsor_id) {
    try {
        // Count total referrals
        $total_referrals = db()->selectValue(
            "SELECT COUNT(*) FROM " . db()->table('sponsors') . " WHERE sponsor_id = ?",
            [$sponsor_id]
        );
        
        // Calculate total commission (placeholder - implement based on your commission system)
        $total_commission = db()->selectValue(
            "SELECT COALESCE(SUM(amount_in), 0) FROM " . db()->table('transactions') . " 
             WHERE user_id = ? AND type = 'commission' AND status = 'completed'",
            [$sponsor_id]
        ) ?: 0;
        
        // Update sponsor record
        $result = db()->update('sponsors', [
            'total_referrals' => $total_referrals,
            'total_commission' => $total_commission,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'user_id = ?', [$sponsor_id]);
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Error updating sponsor stats: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get sponsor network tree
 */
function epic_get_sponsor_network($sponsor_id, $max_levels = 5) {
    $network = [];
    
    for ($level = 1; $level <= $max_levels; $level++) {
        $referrals = epic_get_sponsor_referrals($sponsor_id, $level);
        if (empty($referrals)) {
            break;
        }
        
        $network[$level] = $referrals;
    }
    
    return $network;
}

/**
 * Get sponsor genealogy (upline)
 */
function epic_get_sponsor_genealogy($user_id) {
    $genealogy = [];
    $current_user_id = $user_id;
    
    while ($current_user_id) {
        $sponsor_record = epic_get_sponsor_by_user($current_user_id);
        if (!$sponsor_record || !$sponsor_record['sponsor_id']) {
            break;
        }
        
        $sponsor = epic_get_user($sponsor_record['sponsor_id']);
        if ($sponsor) {
            $genealogy[] = [
                'user' => $sponsor,
                'level' => $sponsor_record['level'] - 1,
                'commission_rate' => $sponsor_record['commission_rate']
            ];
            
            $current_user_id = $sponsor['id'];
        } else {
            break;
        }
    }
    
    return array_reverse($genealogy); // Top-down order
}

/**
 * Calculate commission for sponsor network
 */
function epic_calculate_sponsor_commission($user_id, $amount, $commission_rates = []) {
    $default_rates = [
        1 => 10.00, // Level 1: 10%
        2 => 5.00,  // Level 2: 5%
        3 => 3.00,  // Level 3: 3%
        4 => 2.00,  // Level 4: 2%
        5 => 1.00   // Level 5: 1%
    ];
    
    $rates = array_merge($default_rates, $commission_rates);
    $commissions = [];
    
    $genealogy = epic_get_sponsor_genealogy($user_id);
    
    foreach ($genealogy as $index => $sponsor_data) {
        $level = $index + 1;
        if (isset($rates[$level])) {
            $commission_rate = $rates[$level];
            $commission_amount = ($amount * $commission_rate) / 100;
            
            $commissions[] = [
                'sponsor_id' => $sponsor_data['user']['id'],
                'sponsor_name' => $sponsor_data['user']['name'],
                'level' => $level,
                'rate' => $commission_rate,
                'amount' => $commission_amount
            ];
        }
    }
    
    return $commissions;
}

/**
 * Get sponsor dashboard data
 */
function epic_get_sponsor_dashboard($user_id) {
    $sponsor_record = epic_get_sponsor_by_user($user_id);
    if (!$sponsor_record) {
        return null;
    }
    
    // Get direct referrals
    $direct_referrals = epic_get_sponsor_referrals($user_id, $sponsor_record['level'] + 1);
    
    // Get network statistics
    $network_stats = [
        'total_referrals' => $sponsor_record['total_referrals'],
        'total_commission' => $sponsor_record['total_commission'],
        'direct_referrals' => count($direct_referrals),
        'active_referrals' => 0
    ];
    
    // Count active referrals
    foreach ($direct_referrals as $referral) {
        if ($referral['status'] === 'active' || $referral['status'] === 'premium') {
            $network_stats['active_referrals']++;
        }
    }
    
    return [
        'sponsor_record' => $sponsor_record,
        'direct_referrals' => $direct_referrals,
        'network_stats' => $network_stats,
        'genealogy' => epic_get_sponsor_genealogy($user_id)
    ];
}

?>