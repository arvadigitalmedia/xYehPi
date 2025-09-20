<?php
/**
 * EPIC Hub Referral-EPIS Handler
 * Handles automatic EPIS assignment based on EPIC referrals
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

require_once EPIC_ROOT . '/core/functions.php';
require_once EPIC_ROOT . '/core/epis-functions.php';
require_once EPIC_ROOT . '/core/sponsor.php';

/**
 * Handle referral tracking and EPIS auto-assignment for landing pages
 */
function epic_handle_referral_epis_tracking($referral_code) {
    if (empty($referral_code)) {
        return null;
    }
    
    try {
        // Get referrer information
        $referrer = epic_get_user_by_affiliate_code($referral_code);
        
        // Fallback to old referral_code system
        if (!$referrer) {
            $referrer = epic_get_user_by_referral_code($referral_code);
        }
        
        if (!$referrer) {
            return null;
        }
        
        $tracking_data = [
            'referrer' => $referrer,
            'has_epis_supervisor' => false,
            'epis_supervisor' => null,
            'auto_assignment_available' => false
        ];
        
        // Check if referrer is EPIC with EPIS supervisor
        if ($referrer['status'] === 'epic' && !empty($referrer['epis_supervisor_id'])) {
            $epis_supervisor = epic_get_user($referrer['epis_supervisor_id']);
            
            if ($epis_supervisor && $epis_supervisor['status'] === 'epis') {
                // Validate EPIC-EPIS connection
                $connection_validation = epic_validate_epic_epis_connection(
                    $referrer['id'], 
                    $referrer['epis_supervisor_id']
                );
                
                if ($connection_validation['valid']) {
                    $epis_account = epic_get_epis_account($referrer['epis_supervisor_id']);
                    
                    // Check if auto-assignment is available
                    $auto_assignment_available = (
                        $epis_account && 
                        $epis_account['status'] === 'active' &&
                        ($epis_account['max_epic_recruits'] == 0 || 
                         $epis_account['current_epic_count'] < $epis_account['max_epic_recruits'])
                    );
                    
                    $tracking_data['has_epis_supervisor'] = true;
                    $tracking_data['epis_supervisor'] = $epis_supervisor;
                    $tracking_data['epis_account'] = $epis_account;
                    $tracking_data['auto_assignment_available'] = $auto_assignment_available;
                    $tracking_data['connection_validation'] = $connection_validation;
                }
            }
        }
        
        return $tracking_data;
        
    } catch (Exception $e) {
        error_log('Error in epic_handle_referral_epis_tracking: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get EPIS information for display on registration/landing pages
 */
function epic_get_epis_info_for_display($epis_supervisor_id) {
    if (!$epis_supervisor_id) {
        return null;
    }
    
    try {
        $epis_supervisor = epic_get_user($epis_supervisor_id);
        if (!$epis_supervisor || $epis_supervisor['status'] !== 'epis') {
            return null;
        }
        
        $epis_account = epic_get_epis_account($epis_supervisor_id);
        if (!$epis_account) {
            return null;
        }
        
        return [
            'supervisor' => $epis_supervisor,
            'account' => $epis_account,
            'display_info' => [
                'name' => $epis_supervisor['name'],
                'epis_code' => $epis_account['epis_code'],
                'territory' => $epis_account['territory_name'] ?? 'General',
                'capacity' => $epis_account['current_epic_count'] . 
                             ($epis_account['max_epic_recruits'] > 0 ? '/' . $epis_account['max_epic_recruits'] : ''),
                'available_slots' => $epis_account['max_epic_recruits'] > 0 ? 
                                   ($epis_account['max_epic_recruits'] - $epis_account['current_epic_count']) : 'Unlimited',
                'initials' => strtoupper(substr($epis_supervisor['name'], 0, 2))
            ]
        ];
        
    } catch (Exception $e) {
        error_log('Error in epic_get_epis_info_for_display: ' . $e->getMessage());
        return null;
    }
}

/**
 * Generate referral tracking cookie with EPIS information
 */
function epic_set_referral_epis_cookie($referral_code, $referrer_name, $epis_info = null) {
    $tracking_data = [
        'code' => $referral_code,
        'name' => $referrer_name,
        'timestamp' => time()
    ];
    
    if ($epis_info) {
        $tracking_data['epis'] = [
            'supervisor_id' => $epis_info['supervisor']['id'],
            'supervisor_name' => $epis_info['supervisor']['name'],
            'epis_code' => $epis_info['account']['epis_code'],
            'auto_assignment' => true
        ];
    }
    
    $cookie_value = base64_encode(json_encode($tracking_data));
    setcookie('epic_referral_tracking', $cookie_value, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    
    return $tracking_data;
}

/**
 * Get referral tracking with EPIS information from cookie
 */
function epic_get_referral_epis_tracking() {
    if (!isset($_COOKIE['epic_referral_tracking'])) {
        return null;
    }
    
    try {
        $tracking_data = json_decode(base64_decode($_COOKIE['epic_referral_tracking']), true);
        
        if (!$tracking_data || !isset($tracking_data['code'])) {
            return null;
        }
        
        // Check if tracking is still valid (30 days)
        if (isset($tracking_data['timestamp']) && 
            (time() - $tracking_data['timestamp']) > (30 * 24 * 60 * 60)) {
            return null;
        }
        
        return $tracking_data;
        
    } catch (Exception $e) {
        error_log('Error in epic_get_referral_epis_tracking: ' . $e->getMessage());
        return null;
    }
}

/**
 * Process registration with EPIS auto-assignment
 */
function epic_process_registration_with_epis($user_data) {
    try {
        // Check if there's referral tracking with EPIS info
        $referral_tracking = epic_get_referral_epis_tracking();
        
        if ($referral_tracking && isset($referral_tracking['epis']['auto_assignment'])) {
            // Add EPIS supervisor info to user data for auto-assignment
            $user_data['referral_epis_info'] = $referral_tracking['epis'];
        }
        
        // Register user with standard process (auto-assignment will happen in epic_register_user)
        $user_id = epic_register_user($user_data);
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'epis_auto_assigned' => isset($referral_tracking['epis']['auto_assignment'])
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Validate and prepare EPIS assignment data
 */
function epic_validate_epis_assignment_data($referral_code, $epis_supervisor_id = null) {
    $validation = [
        'valid' => false,
        'auto_assignment' => false,
        'manual_assignment' => false,
        'epis_supervisor_id' => null,
        'referrer_id' => null,
        'recruitment_type' => 'direct',
        'errors' => []
    ];
    
    try {
        // If EPIS supervisor is manually selected, validate it
        if ($epis_supervisor_id) {
            $epis_supervisor = epic_get_user($epis_supervisor_id);
            if (!$epis_supervisor || $epis_supervisor['status'] !== 'epis') {
                $validation['errors'][] = 'Invalid EPIS supervisor selected';
                return $validation;
            }
            
            $epis_account = epic_get_epis_account($epis_supervisor_id);
            if (!$epis_account || $epis_account['status'] !== 'active') {
                $validation['errors'][] = 'EPIS supervisor account is not active';
                return $validation;
            }
            
            if ($epis_account['max_epic_recruits'] > 0 && 
                $epis_account['current_epic_count'] >= $epis_account['max_epic_recruits']) {
                $validation['errors'][] = 'EPIS supervisor has reached maximum capacity';
                return $validation;
            }
            
            $validation['valid'] = true;
            $validation['manual_assignment'] = true;
            $validation['epis_supervisor_id'] = $epis_supervisor_id;
            $validation['recruitment_type'] = 'direct';
            
            return $validation;
        }
        
        // Check for auto-assignment based on referral
        if ($referral_code) {
            $referrer = epic_get_user_by_affiliate_code($referral_code);
            if (!$referrer) {
                $referrer = epic_get_user_by_referral_code($referral_code);
            }
            
            if ($referrer && $referrer['status'] === 'epic' && !empty($referrer['epis_supervisor_id'])) {
                $connection_validation = epic_validate_epic_epis_connection(
                    $referrer['id'], 
                    $referrer['epis_supervisor_id']
                );
                
                if ($connection_validation['valid']) {
                    $epis_account = $connection_validation['epis_account'];
                    
                    if ($epis_account['max_epic_recruits'] == 0 || 
                        $epis_account['current_epic_count'] < $epis_account['max_epic_recruits']) {
                        
                        $validation['valid'] = true;
                        $validation['auto_assignment'] = true;
                        $validation['epis_supervisor_id'] = $referrer['epis_supervisor_id'];
                        $validation['referrer_id'] = $referrer['id'];
                        $validation['recruitment_type'] = 'indirect';
                        
                        return $validation;
                    } else {
                        $validation['errors'][] = 'EPIS supervisor from referrer has reached maximum capacity';
                    }
                } else {
                    $validation['errors'][] = 'Invalid EPIC-EPIS connection for referrer';
                }
            }
        }
        
        return $validation;
        
    } catch (Exception $e) {
        $validation['errors'][] = 'Validation error: ' . $e->getMessage();
        return $validation;
    }
}

/**
 * Log EPIS assignment activity with detailed information
 */
function epic_log_epis_assignment($user_id, $assignment_data) {
    try {
        $log_message = "EPIS Assignment - ";
        
        if ($assignment_data['auto_assignment']) {
            $log_message .= "Auto-assigned to EPIS {$assignment_data['epis_supervisor_id']} ";
            $log_message .= "via EPIC referrer {$assignment_data['referrer_id']} ";
            $log_message .= "(Type: {$assignment_data['recruitment_type']})";
        } else {
            $log_message .= "Manually assigned to EPIS {$assignment_data['epis_supervisor_id']} ";
            $log_message .= "(Type: {$assignment_data['recruitment_type']})";
        }
        
        epic_log_activity($user_id, 'epis_assignment_processed', $log_message);
        
        // Also log to EPIS supervisor
        if ($assignment_data['epis_supervisor_id']) {
            $recruitment_type = $assignment_data['auto_assignment'] ? 'indirect' : 'direct';
            epic_log_activity($assignment_data['epis_supervisor_id'], 'new_epic_recruit', 
                "New {$recruitment_type} EPIC recruit: User {$user_id}");
        }
        
    } catch (Exception $e) {
        error_log('Error logging EPIS assignment: ' . $e->getMessage());
    }
}

?>