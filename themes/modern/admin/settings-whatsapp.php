<?php
/**
 * EPIC Hub Admin Settings - WhatsApp Notification
 * WhatsApp notification settings management
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/settings/whatsapp-notification');
$user = $init_result['user'];

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_whatsapp_settings'])) {
    try {
        // Process WhatsApp settings update
        $whatsapp_settings = [];
        
        // Get all WhatsApp settings from POST
        $whatsapp_fields = [
            'whatsapp_api_url', 'whatsapp_api_key', 'whatsapp_sender_number',
            'whatsapp_welcome_enabled', 'whatsapp_order_confirmation_enabled',
            'whatsapp_payout_notification_enabled', 'whatsapp_admin_notification_enabled',
            'whatsapp_welcome_template', 'whatsapp_order_confirmation_template',
            'whatsapp_payout_notification_template', 'whatsapp_service_provider'
        ];
        
        foreach ($whatsapp_fields as $field) {
            if (isset($_POST[$field])) {
                $whatsapp_settings[$field] = $_POST[$field];
            }
        }
        
        // Handle checkboxes
        $whatsapp_settings['whatsapp_welcome_enabled'] = isset($_POST['whatsapp_welcome_enabled']) ? '1' : '0';
        $whatsapp_settings['whatsapp_order_confirmation_enabled'] = isset($_POST['whatsapp_order_confirmation_enabled']) ? '1' : '0';
        $whatsapp_settings['whatsapp_payout_notification_enabled'] = isset($_POST['whatsapp_payout_notification_enabled']) ? '1' : '0';
        $whatsapp_settings['whatsapp_admin_notification_enabled'] = isset($_POST['whatsapp_admin_notification_enabled']) ? '1' : '0';
        
        // Save WhatsApp settings to database
        foreach ($whatsapp_settings as $key => $value) {
            epic_safe_db_query(
                "INSERT INTO " . TABLE_SETTINGS . " (`key`, `value`) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                [$key, $value],
                'select'
            );
        }
        
        // Log activity
        if (function_exists('epic_log_activity')) {
            epic_log_activity($user['id'], 'whatsapp_settings_updated', 'WhatsApp notification settings updated');
        }
        
        $success = 'WhatsApp settings berhasil disimpan!';
        
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Get current WhatsApp settings
$current_settings = epic_safe_db_query(
    "SELECT `key`, `value` FROM " . TABLE_SETTINGS . " WHERE `key` LIKE 'whatsapp_%'",
    [],
    'select'
);

// Convert to associative array
$whatsapp_settings = [];
foreach ($current_settings as $setting) {
    $whatsapp_settings[$setting['key']] = $setting['value'];
}

// Default WhatsApp settings if not exist
$default_whatsapp_settings = [
    'whatsapp_api_url' => 'https://api.fonnte.com/send',
    'whatsapp_api_key' => '',
    'whatsapp_sender_number' => '',
    'whatsapp_service_provider' => 'fonnte',
    'whatsapp_welcome_enabled' => '1',
    'whatsapp_order_confirmation_enabled' => '1',
    'whatsapp_payout_notification_enabled' => '1',
    'whatsapp_admin_notification_enabled' => '1',
    'whatsapp_welcome_template' => 'Selamat datang di EPIC Hub! Akun Anda telah berhasil dibuat. Terima kasih telah bergabung dengan kami.',
    'whatsapp_order_confirmation_template' => 'Pesanan Anda telah berhasil diproses. Terima kasih atas kepercayaan Anda kepada EPIC Hub.',
    'whatsapp_payout_notification_template' => 'Pembayaran komisi Anda sebesar {amount} telah diproses. Silakan cek rekening Anda.'
];

// Merge with defaults
foreach ($default_whatsapp_settings as $key => $default_value) {
    if (!isset($whatsapp_settings[$key])) {
        $whatsapp_settings[$key] = $default_value;
    }
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'WhatsApp Notification Settings - EPIC Hub Admin',
    'header_title' => 'WhatsApp Notification Settings',
    'current_page' => 'settings',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Settings', 'url' => epic_url('admin/settings')],
        ['text' => 'WhatsApp Notification']
    ],
    'content_file' => __DIR__ . '/content/settings-whatsapp-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'whatsapp_settings' => $whatsapp_settings,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>