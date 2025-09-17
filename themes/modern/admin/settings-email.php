<?php
/**
 * EPIC Hub Admin Settings - Email Notification
 * Email notification settings management
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/settings/email-notification');
$user = $init_result['user'];

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_email_settings'])) {
    try {
        // Process email settings update
        $email_settings = [];
        
        // Get all email settings from POST
        $email_fields = [
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_encryption', 'mail_from_name', 'mail_from_email',
            'welcome_email_enabled', 'order_confirmation_enabled',
            'payout_notification_enabled', 'admin_notification_enabled',
            'welcome_email_subject', 'welcome_email_template',
            'order_confirmation_subject', 'order_confirmation_template',
            'payout_notification_subject', 'payout_notification_template'
        ];
        
        foreach ($email_fields as $field) {
            if (isset($_POST[$field])) {
                $email_settings[$field] = $_POST[$field];
            }
        }
        
        // Handle checkboxes
        $email_settings['welcome_email_enabled'] = isset($_POST['welcome_email_enabled']) ? '1' : '0';
        $email_settings['order_confirmation_enabled'] = isset($_POST['order_confirmation_enabled']) ? '1' : '0';
        $email_settings['payout_notification_enabled'] = isset($_POST['payout_notification_enabled']) ? '1' : '0';
        $email_settings['admin_notification_enabled'] = isset($_POST['admin_notification_enabled']) ? '1' : '0';
        
        // Save email settings to database
        foreach ($email_settings as $key => $value) {
            epic_safe_db_query(
                "INSERT INTO " . TABLE_SETTINGS . " (`key`, `value`) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                [$key, $value],
                'select'
            );
        }
        
        // Log activity
        if (function_exists('epic_log_activity')) {
            epic_log_activity($user['id'], 'email_settings_updated', 'Email notification settings updated');
        }
        
        $success = 'Email settings berhasil disimpan!';
        
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Get current email settings
$current_settings = epic_safe_db_query(
    "SELECT `key`, `value` FROM " . TABLE_SETTINGS . " WHERE `key` LIKE 'smtp_%' OR `key` LIKE 'mail_%' OR `key` LIKE '%_email_%' OR `key` LIKE '%_notification_%'",
    [],
    'select'
);

// Convert to associative array
$email_settings = [];
foreach ($current_settings as $setting) {
    $email_settings[$setting['key']] = $setting['value'];
}

// Default email settings if not exist
$default_email_settings = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => '587',
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_encryption' => 'tls',
    'mail_from_name' => 'EPIC Hub',
    'mail_from_email' => 'noreply@epichub.com',
    'welcome_email_enabled' => '1',
    'order_confirmation_enabled' => '1',
    'payout_notification_enabled' => '1',
    'admin_notification_enabled' => '1',
    'welcome_email_subject' => 'Selamat Datang di EPIC Hub!',
    'welcome_email_template' => 'Terima kasih telah bergabung dengan EPIC Hub. Akun Anda telah berhasil dibuat.',
    'order_confirmation_subject' => 'Konfirmasi Pesanan - EPIC Hub',
    'order_confirmation_template' => 'Pesanan Anda telah berhasil diproses. Terima kasih atas kepercayaan Anda.',
    'payout_notification_subject' => 'Notifikasi Pembayaran - EPIC Hub',
    'payout_notification_template' => 'Pembayaran komisi Anda telah diproses.'
];

// Merge with defaults
foreach ($default_email_settings as $key => $default_value) {
    if (!isset($email_settings[$key])) {
        $email_settings[$key] = $default_value;
    }
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Email Notification Settings - EPIC Hub Admin',
    'header_title' => 'Email Notification Settings',
    'current_page' => 'settings',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Settings', 'url' => epic_url('admin/settings')],
        ['text' => 'Email Notification']
    ],
    'content_file' => __DIR__ . '/content/settings-email-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'email_settings' => $email_settings,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>