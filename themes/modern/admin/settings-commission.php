<?php
/**
 * Commission Settings Admin Page
 * Pengaturan commission rates global untuk EPIS dan EPIC
 * 
 * @version 1.0.0
 * @author EPIC Hub Team
 */

// Security check
if (!defined('EPIC_LOADED') || !epic_is_admin()) {
    epic_redirect('login');
    exit;
}

// Initialize variables
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_commission_settings') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $epis_direct_rate = isset($_POST['epis_direct_commission_rate']) ? (float)$_POST['epis_direct_commission_rate'] : 0;
        $epis_indirect_rate = isset($_POST['epis_indirect_commission_rate']) ? (float)$_POST['epis_indirect_commission_rate'] : 0;
        $epic_referral_rate = isset($_POST['epic_referral_commission_rate']) ? (float)$_POST['epic_referral_commission_rate'] : 0;
        
        // Validate rates
        if ($epis_direct_rate < 0 || $epis_direct_rate > 100) {
            $error = 'EPIS Direct Commission rate must be between 0% and 100%.';
        } elseif ($epis_indirect_rate < 0 || $epis_indirect_rate > 100) {
            $error = 'EPIS Indirect Commission rate must be between 0% and 100%.';
        } elseif ($epic_referral_rate < 0 || $epic_referral_rate > 100) {
            $error = 'EPIC Referral Commission rate must be between 0% and 100%.';
        } else {
            // Update settings
            $settings_updated = 0;
            
            if (epic_update_setting('epis_direct_commission_rate', number_format($epis_direct_rate, 2, '.', ''))) {
                $settings_updated++;
            }
            
            if (epic_update_setting('epis_indirect_commission_rate', number_format($epis_indirect_rate, 2, '.', ''))) {
                $settings_updated++;
            }
            
            if (epic_update_setting('epic_referral_commission_rate', number_format($epic_referral_rate, 2, '.', ''))) {
                $settings_updated++;
            }
            
            if ($settings_updated > 0) {
                $success = 'Commission settings updated successfully!';
            } else {
                $error = 'Failed to update commission settings. Please try again.';
            }
        }
    }
}

// Get current commission settings from database
$current_settings = [
    'epis_direct_commission_rate' => epic_setting('epis_direct_commission_rate', 5.0),
    'epis_indirect_commission_rate' => epic_setting('epis_indirect_commission_rate', 2.5),
    'epic_referral_commission_rate' => epic_setting('epic_referral_commission_rate', 3.0)
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_commission_settings') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Validate input
        $epis_direct_rate = filter_input(INPUT_POST, 'epis_direct_commission_rate', FILTER_VALIDATE_FLOAT);
        $epis_indirect_rate = filter_input(INPUT_POST, 'epis_indirect_commission_rate', FILTER_VALIDATE_FLOAT);
        $epic_referral_rate = filter_input(INPUT_POST, 'epic_referral_commission_rate', FILTER_VALIDATE_FLOAT);
        
        if ($epis_direct_rate === false || $epis_direct_rate < 0 || $epis_direct_rate > 100) {
            $error = 'EPIS Direct Commission rate must be between 0 and 100.';
        } elseif ($epis_indirect_rate === false || $epis_indirect_rate < 0 || $epis_indirect_rate > 100) {
            $error = 'EPIS Indirect Commission rate must be between 0 and 100.';
        } elseif ($epic_referral_rate === false || $epic_referral_rate < 0 || $epic_referral_rate > 100) {
            $error = 'EPIC Referral Commission rate must be between 0 and 100.';
        } else {
            try {
                // Update commission settings in database
                $stmt = $pdo->prepare("
                    INSERT INTO epic_settings (setting_key, setting_value, updated_at) 
                    VALUES (?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
                ");
                
                // Update EPIS direct commission rate
                $stmt->execute(['epis_direct_commission_rate', $epis_direct_rate]);
                
                // Update EPIS indirect commission rate  
                $stmt->execute(['epis_indirect_commission_rate', $epis_indirect_rate]);
                
                // Update EPIC referral commission rate
                $stmt->execute(['epic_referral_commission_rate', $epic_referral_rate]);
                
                // Log the changes
                epic_log_activity([
                    'user_id' => $_SESSION['user_id'],
                    'action' => 'commission_settings_updated',
                    'details' => json_encode([
                        'epis_direct_rate' => $epis_direct_rate,
                        'epis_indirect_rate' => $epis_indirect_rate,
                        'epic_referral_rate' => $epic_referral_rate,
                        'updated_by' => $_SESSION['username']
                    ])
                ]);
                
                // Update current settings for display
                $current_settings = [
                    'epis_direct_commission_rate' => $epis_direct_rate,
                    'epis_indirect_commission_rate' => $epis_indirect_rate,
                    'epic_referral_commission_rate' => $epic_referral_rate
                ];
                
                $success = 'Commission settings updated successfully.';
                
            } catch (Exception $e) {
                epic_log_error('Commission settings update failed: ' . $e->getMessage());
                $error = 'Failed to update commission settings. Please try again.';
            }
        }
    }
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Commission Settings - EPIC Hub Admin',
    'header_title' => 'Commission Settings',
    'current_page' => 'settings',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Settings', 'url' => epic_url('admin/settings')],
        ['text' => 'Commission']
    ],
    'content_file' => __DIR__ . '/content/settings-commission-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'current_settings' => $current_settings,
    'csrf_token' => $_SESSION['csrf_token']
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>