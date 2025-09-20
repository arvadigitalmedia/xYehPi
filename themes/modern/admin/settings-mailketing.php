<?php
/**
 * EPIC Hub Admin Settings - Mailketing Integration
 * Halaman pengaturan Mailketing yang menggantikan SMTP dan Email Notification
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/settings/mailketing');
$user = $init_result['user'];

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle different form actions
        if (isset($_POST['save_mailketing_settings'])) {
            // Save Mailketing API settings
            $mailketing_settings = [
                'mailketing_enabled' => isset($_POST['mailketing_enabled']) ? '1' : '0',
                'mailketing_api_token' => $_POST['mailketing_api_token'] ?? '',
                'mailketing_from_name' => $_POST['mailketing_from_name'] ?? '',
                'mailketing_from_email' => $_POST['mailketing_from_email'] ?? '',
                'mailketing_webhook_url' => $_POST['mailketing_webhook_url'] ?? '',
                'mailketing_default_list_id' => $_POST['mailketing_default_list_id'] ?? ''
            ];
            
            foreach ($mailketing_settings as $key => $value) {
                epic_safe_db_query(
                    "INSERT INTO " . TABLE_SETTINGS . " (`key`, `value`) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                    [$key, $value],
                    'select'
                );
            }
            
            $success = 'Pengaturan Mailketing berhasil disimpan!';
            
        } elseif (isset($_POST['save_email_templates'])) {
            // Save email templates
            $template_settings = [
                'welcome_email_enabled' => isset($_POST['welcome_email_enabled']) ? '1' : '0',
                'welcome_email_subject' => $_POST['welcome_email_subject'] ?? '',
                'welcome_email_template' => $_POST['welcome_email_template'] ?? '',
                'order_confirmation_enabled' => isset($_POST['order_confirmation_enabled']) ? '1' : '0',
                'order_confirmation_subject' => $_POST['order_confirmation_subject'] ?? '',
                'order_confirmation_template' => $_POST['order_confirmation_template'] ?? '',
                'password_reset_enabled' => isset($_POST['password_reset_enabled']) ? '1' : '0',
                'password_reset_subject' => $_POST['password_reset_subject'] ?? '',
                'password_reset_template' => $_POST['password_reset_template'] ?? ''
            ];
            
            foreach ($template_settings as $key => $value) {
                epic_safe_db_query(
                    "INSERT INTO " . TABLE_SETTINGS . " (`key`, `value`) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                    [$key, $value],
                    'select'
                );
            }
            
            $success = 'Template email berhasil disimpan!';
            
        } elseif (isset($_POST['test_mailketing_connection'])) {
            // Test Mailketing connection
            if (function_exists('epic_test_mailketing_connection')) {
                $test_result = epic_test_mailketing_connection();
                if ($test_result['success']) {
                    $success = 'Test koneksi Mailketing berhasil! Email test telah dikirim.';
                } else {
                    $error = 'Test koneksi gagal: ' . $test_result['error'];
                }
            } else {
                $error = 'Fungsi test Mailketing tidak tersedia.';
            }
        }
        
        // Log activity
        if (function_exists('epic_log_activity')) {
            epic_log_activity($user['id'], 'mailketing_settings_updated', 'Mailketing settings updated');
        }
        
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Get current settings
$current_settings = epic_safe_db_query(
    "SELECT `key`, `value` FROM " . TABLE_SETTINGS . " WHERE 
     `key` LIKE 'mailketing_%' OR 
     `key` LIKE 'welcome_email_%' OR 
     `key` LIKE 'order_confirmation_%' OR 
     `key` LIKE 'password_reset_%'",
    [],
    'select'
);

// Convert to associative array
$settings = [];
foreach ($current_settings as $setting) {
    $settings[$setting['key']] = $setting['value'];
}

// Default settings
$default_settings = [
    'mailketing_enabled' => '1',
    'mailketing_api_token' => '',
    'mailketing_from_name' => 'EPIC Hub',
    'mailketing_from_email' => 'noreply@epichub.com',
    'mailketing_webhook_url' => '',
    'mailketing_default_list_id' => '',
    'welcome_email_enabled' => '1',
    'welcome_email_subject' => 'Selamat Datang di EPIC Hub!',
    'welcome_email_template' => 'Terima kasih telah bergabung dengan EPIC Hub...',
    'order_confirmation_enabled' => '1',
    'order_confirmation_subject' => 'Konfirmasi Pesanan - EPIC Hub',
    'order_confirmation_template' => 'Pesanan Anda telah dikonfirmasi...',
    'password_reset_enabled' => '1',
    'password_reset_subject' => 'Reset Password - EPIC Hub',
    'password_reset_template' => 'Klik link berikut untuk reset password...'
];

// Merge with defaults
$settings = array_merge($default_settings, $settings);

// Get Mailketing status
$mailketing_status = function_exists('epic_get_mailketing_status') ? epic_get_mailketing_status() : [
    'enabled' => false,
    'configured' => false,
    'credits' => 0
];

// Prepare layout data
$layout_data = [
    'page_title' => 'Pengaturan Mailketing - EPIC Hub Admin',
    'header_title' => 'Pengaturan Mailketing',
    'current_page' => 'settings',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Settings', 'url' => epic_url('admin/settings')],
        ['text' => 'Mailketing']
    ],
    'content_file' => __DIR__ . '/content/settings-mailketing-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'settings' => $settings,
    'mailketing_status' => $mailketing_status,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);