<?php
/**
 * EPIC Hub Admin Settings Page
 * Settings management with EPIC Referral System configuration
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/settings/general');
$user = $init_result['user'];

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        // Process settings update
        $updated_settings = [];
        
        // Handle logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $logo_result = epic_handle_logo_upload($_FILES['site_logo'], 'logo');
            if ($logo_result['success']) {
                $updated_settings['site_logo'] = $logo_result['filename'];
                $success = 'Logo website berhasil diupload.';
            } else {
                $error = $logo_result['error'] ?? $logo_result['message'] ?? 'Gagal mengupload logo.';
            }
        }
        
        // Handle favicon upload
        if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
            $favicon_result = epic_handle_logo_upload($_FILES['site_favicon'], 'favicon');
            if ($favicon_result['success']) {
                $updated_settings['site_favicon'] = $favicon_result['filename'];
                $success = ($success ? $success . ' ' : '') . 'Favicon berhasil diupload.';
            } else {
                $error = $favicon_result['error'] ?? $favicon_result['message'] ?? 'Gagal mengupload favicon.';
            }
        }
        
        // Handle logo deletion
        if (isset($_POST['delete_site_logo'])) {
            $current_logo = epic_setting('site_logo');
            if ($current_logo) {
                $logo_path = __DIR__ . '/../../../uploads/logos/' . $current_logo;
                if (file_exists($logo_path)) {
                    unlink($logo_path);
                }
                $updated_settings['site_logo'] = '';
                $success = 'Logo website berhasil dihapus.';
            }
        }
        
        // Handle favicon deletion
        if (isset($_POST['delete_site_favicon'])) {
            $current_favicon = epic_setting('site_favicon');
            if ($current_favicon) {
                $favicon_path = __DIR__ . '/../../../uploads/logos/' . $current_favicon;
                if (file_exists($favicon_path)) {
                    unlink($favicon_path);
                }
                $updated_settings['site_favicon'] = '';
                $success = ($success ? $success . ' ' : '') . 'Favicon berhasil dihapus.';
            }
        }
        
        // Process other form fields
        foreach ($_POST as $key => $value) {
            if ($key !== 'save_settings' && $key !== 'csrf_token' && 
                !str_starts_with($key, 'delete_')) {
                $updated_settings[$key] = $value;
            }
        }
        
        // Save settings to database
        foreach ($updated_settings as $key => $value) {
            epic_safe_db_query(
                "INSERT INTO " . TABLE_SETTINGS . " (`key`, `value`) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                [$key, $value],
                'select'
            );
        }
        
        // Log activity
        if (function_exists('epic_log_activity')) {
            epic_log_activity($user['id'], 'settings_updated', 'General settings updated');
        }
        
        $success = 'Settings berhasil disimpan!';
        
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Get current settings
$current_settings = epic_safe_db_query(
    "SELECT `key`, `value` FROM " . TABLE_SETTINGS,
    [],
    'select'
);

// Convert to associative array
$settings = [];
foreach ($current_settings as $setting) {
    $settings[$setting['key']] = $setting['value'];
}

// Default settings if not exist
$default_settings = [
    'site_name' => 'EPIC Hub',
    'site_description' => 'Modern Affiliate Marketing Platform',
    'admin_email' => 'admin@epichub.com',
    'timezone' => 'Asia/Jakarta',
    'currency' => 'IDR',
    'currency_symbol' => 'Rp',
    'referral_commission' => '10',
    'min_payout' => '100000',
    'maintenance_mode' => '0',
    'registration_enabled' => '1',
    'email_verification' => '1'
];

// Merge with defaults
foreach ($default_settings as $key => $default_value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $default_value;
    }
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'General Settings - EPIC Hub Admin',
    'header_title' => 'General Settings',
    'current_page' => 'settings',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Settings', 'url' => epic_url('admin/settings')],
        ['text' => 'General']
    ],
    'content_file' => __DIR__ . '/content/settings-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'settings' => $settings,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>