<?php
/**
 * Enhanced EPIC Hub Referral Handler
 * Mendukung semua skenario referral: EPIC→EPIS, EPIS→EPIS, link vs kode manual
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

require_once EPIC_ROOT . '/core/functions.php';
require_once EPIC_ROOT . '/core/epis-functions.php';
require_once EPIC_ROOT . '/referral-epis-handler.php';

/**
 * Enhanced referral processing untuk semua skenario
 * Mendukung: EPIC Account → EPIS Supervisor, EPIS Account → EPIS Direct
 */
function epic_enhanced_referral_processing($referral_code, $registration_data = []) {
    if (empty($referral_code)) {
        return [
            'success' => false,
            'message' => 'Kode referral tidak ditemukan',
            'scenario' => 'none'
        ];
    }
    
    try {
        // Step 1: Identifikasi referrer (EPIC atau EPIS)
        $referrer = epic_get_user_by_affiliate_code($referral_code);
        
        // Fallback ke sistem lama
        if (!$referrer) {
            $referrer = epic_get_user_by_referral_code($referral_code);
        }
        
        if (!$referrer) {
            return [
                'success' => false,
                'message' => 'Kode referral tidak valid',
                'scenario' => 'invalid'
            ];
        }
        
        // Step 2: Tentukan skenario berdasarkan status referrer
        $scenario = epic_determine_referral_scenario($referrer);
        
        // Step 3: Proses sesuai skenario
        switch ($scenario['type']) {
            case 'epic_to_epis':
                return epic_process_epic_to_epis_referral($referrer, $scenario, $registration_data);
                
            case 'epis_direct':
                return epic_process_epis_direct_referral($referrer, $scenario, $registration_data);
                
            case 'epic_standalone':
                return epic_process_epic_standalone_referral($referrer, $scenario, $registration_data);
                
            default:
                return [
                    'success' => false,
                    'message' => 'Skenario referral tidak didukung',
                    'scenario' => $scenario['type']
                ];
        }
        
    } catch (Exception $e) {
        error_log('Error in epic_enhanced_referral_processing: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan dalam memproses referral',
            'scenario' => 'error',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Tentukan skenario referral berdasarkan status referrer
 */
function epic_determine_referral_scenario($referrer) {
    $scenario = [
        'type' => 'unknown',
        'referrer' => $referrer,
        'epis_supervisor' => null,
        'auto_integration' => false,
        'direct_assignment' => false
    ];
    
    if ($referrer['status'] === 'epis') {
        // Skenario: EPIS Account mereferalkan → langsung ke EPIS tersebut
        $scenario['type'] = 'epis_direct';
        $scenario['epis_supervisor'] = $referrer;
        $scenario['direct_assignment'] = true;
        $scenario['auto_integration'] = true;
        
    } elseif ($referrer['status'] === 'epic' && !empty($referrer['epis_supervisor_id'])) {
        // Skenario: EPIC Account mereferalkan → auto-integrate ke EPIS Supervisor
        $epis_supervisor = epic_get_user($referrer['epis_supervisor_id']);
        
        if ($epis_supervisor && $epis_supervisor['status'] === 'epis') {
            $scenario['type'] = 'epic_to_epis';
            $scenario['epis_supervisor'] = $epis_supervisor;
            $scenario['auto_integration'] = true;
            $scenario['direct_assignment'] = false;
        } else {
            $scenario['type'] = 'epic_standalone';
        }
        
    } elseif ($referrer['status'] === 'epic') {
        // Skenario: EPIC Account tanpa EPIS Supervisor
        $scenario['type'] = 'epic_standalone';
        
    }
    
    return $scenario;
}

/**
 * Proses referral EPIC → EPIS (auto-integration)
 */
function epic_process_epic_to_epis_referral($referrer, $scenario, $registration_data) {
    try {
        // Validasi EPIC-EPIS connection
        $connection_validation = epic_validate_epic_epis_connection(
            $referrer['id'], 
            $scenario['epis_supervisor']['id']
        );
        
        if (!$connection_validation['valid']) {
            return [
                'success' => false,
                'message' => 'Koneksi EPIC-EPIS tidak valid',
                'scenario' => 'epic_to_epis',
                'validation_error' => $connection_validation['error'] ?? 'Unknown error'
            ];
        }
        
        // Cek kapasitas EPIS
        $epis_account = epic_get_epis_account($scenario['epis_supervisor']['id']);
        if (!$epis_account || $epis_account['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'EPIS Supervisor tidak aktif',
                'scenario' => 'epic_to_epis'
            ];
        }
        
        if ($epis_account['max_epic_recruits'] > 0 && 
            $epis_account['current_epic_count'] >= $epis_account['max_epic_recruits']) {
            return [
                'success' => false,
                'message' => 'EPIS Supervisor telah mencapai kapasitas maksimal',
                'scenario' => 'epic_to_epis'
            ];
        }
        
        // Set cookie tracking dengan info EPIS
        $epis_info = [
            'supervisor' => $scenario['epis_supervisor'],
            'account' => $epis_account
        ];
        
        epic_set_referral_epis_cookie($referrer['affiliate_code'], $referrer['name'], $epis_info);
        
        return [
            'success' => true,
            'message' => 'Referral EPIC berhasil diproses, akan auto-assign ke EPIS Supervisor',
            'scenario' => 'epic_to_epis',
            'referrer' => $referrer,
            'epis_supervisor' => $scenario['epis_supervisor'],
            'epis_account' => $epis_account,
            'auto_integration' => true,
            'assignment_data' => [
                'epis_supervisor_id' => $scenario['epis_supervisor']['id'],
                'referrer_id' => $referrer['id'],
                'recruitment_type' => 'indirect',
                'auto_assignment' => true
            ]
        ];
        
    } catch (Exception $e) {
        error_log('Error in epic_process_epic_to_epis_referral: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Gagal memproses referral EPIC→EPIS',
            'scenario' => 'epic_to_epis',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Proses referral EPIS direct
 */
function epic_process_epis_direct_referral($referrer, $scenario, $registration_data) {
    try {
        // Validasi EPIS account
        $epis_account = epic_get_epis_account($referrer['id']);
        if (!$epis_account || $epis_account['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'EPIS Account tidak aktif',
                'scenario' => 'epis_direct'
            ];
        }
        
        // Cek kapasitas
        if ($epis_account['max_epic_recruits'] > 0 && 
            $epis_account['current_epic_count'] >= $epis_account['max_epic_recruits']) {
            return [
                'success' => false,
                'message' => 'EPIS telah mencapai kapasitas maksimal',
                'scenario' => 'epis_direct'
            ];
        }
        
        // Set cookie tracking untuk EPIS direct
        $epis_info = [
            'supervisor' => $referrer,
            'account' => $epis_account
        ];
        
        epic_set_referral_epis_cookie($referrer['affiliate_code'], $referrer['name'], $epis_info);
        
        return [
            'success' => true,
            'message' => 'Referral EPIS berhasil diproses, akan langsung assign ke EPIS',
            'scenario' => 'epis_direct',
            'referrer' => $referrer,
            'epis_supervisor' => $referrer,
            'epis_account' => $epis_account,
            'auto_integration' => true,
            'assignment_data' => [
                'epis_supervisor_id' => $referrer['id'],
                'referrer_id' => $referrer['id'],
                'recruitment_type' => 'direct',
                'auto_assignment' => true
            ]
        ];
        
    } catch (Exception $e) {
        error_log('Error in epic_process_epis_direct_referral: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Gagal memproses referral EPIS direct',
            'scenario' => 'epis_direct',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Proses referral EPIC standalone (tanpa EPIS)
 */
function epic_process_epic_standalone_referral($referrer, $scenario, $registration_data) {
    try {
        // Set cookie tracking tanpa EPIS info
        epic_set_referral_epis_cookie($referrer['affiliate_code'], $referrer['name']);
        
        return [
            'success' => true,
            'message' => 'Referral EPIC berhasil diproses, akan assign ke EPIS default',
            'scenario' => 'epic_standalone',
            'referrer' => $referrer,
            'epis_supervisor' => null,
            'auto_integration' => false,
            'assignment_data' => [
                'epis_supervisor_id' => null,
                'referrer_id' => $referrer['id'],
                'recruitment_type' => 'direct',
                'auto_assignment' => false
            ]
        ];
        
    } catch (Exception $e) {
        error_log('Error in epic_process_epic_standalone_referral: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Gagal memproses referral EPIC standalone',
            'scenario' => 'epic_standalone',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Enhanced registration dengan auto-integration
 */
function epic_enhanced_register_user($user_data) {
    try {
        // Proses referral jika ada
        $referral_result = null;
        if (!empty($user_data['referral_code'])) {
            $referral_result = epic_enhanced_referral_processing($user_data['referral_code'], $user_data);
            
            if ($referral_result['success'] && isset($referral_result['assignment_data'])) {
                // Tambahkan data assignment ke user data
                $user_data['epis_supervisor_id'] = $referral_result['assignment_data']['epis_supervisor_id'];
                $user_data['referral_scenario'] = $referral_result['scenario'];
                $user_data['auto_integration'] = $referral_result['auto_integration'];
            }
        }
        
        // Register user dengan sistem existing
        $user_id = epic_register_user($user_data);
        
        // Log enhanced referral processing
        if ($referral_result && $referral_result['success']) {
            epic_log_activity($user_id, 'enhanced_referral_processed', 
                "Enhanced referral processed: {$referral_result['scenario']} - " . $referral_result['message']);
        }
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'referral_result' => $referral_result
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'referral_result' => $referral_result
        ];
    }
}

/**
 * Wrapper function untuk kompatibilitas dengan sistem existing
 */
function epic_process_referral_link($referral_code) {
    return epic_enhanced_referral_processing($referral_code);
}

/**
 * Get referral info untuk display di halaman registrasi
 */
function epic_get_enhanced_referral_info($referral_code) {
    $result = epic_enhanced_referral_processing($referral_code);
    
    if (!$result['success']) {
        return null;
    }
    
    return [
        'referrer' => $result['referrer'],
        'scenario' => $result['scenario'],
        'epis_supervisor' => $result['epis_supervisor'] ?? null,
        'auto_integration' => $result['auto_integration'] ?? false,
        'message' => $result['message']
    ];
}

?>