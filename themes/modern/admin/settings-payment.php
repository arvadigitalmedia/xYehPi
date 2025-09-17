<?php
/**
 * EPIC Hub Admin Settings - Payment Gateway
 * Payment gateway settings management
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/settings/payment-gateway');
$user = $init_result['user'];

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_payment_settings'])) {
    try {
        // Process payment settings update
        $payment_settings = [];
        
        // Get all payment settings from POST
        $payment_fields = [
            'payment_default_gateway', 'payment_currency', 'payment_tax_rate',
            // Tripay settings
            'tripay_merchant_code', 'tripay_api_key', 'tripay_private_key',
            'tripay_sandbox_mode', 'tripay_enabled',
            // PayPal settings
            'paypal_client_id', 'paypal_client_secret', 'paypal_sandbox_mode', 'paypal_enabled',
            // Midtrans settings
            'midtrans_server_key', 'midtrans_client_key', 'midtrans_sandbox_mode', 'midtrans_enabled',
            // Bank Transfer settings
            'bank_transfer_enabled', 'bank_transfer_accounts',
            // E-wallet settings
            'ewallet_enabled', 'ewallet_providers'
        ];
        
        foreach ($payment_fields as $field) {
            if (isset($_POST[$field])) {
                $payment_settings[$field] = $_POST[$field];
            }
        }
        
        // Handle checkboxes
        $payment_settings['tripay_enabled'] = isset($_POST['tripay_enabled']) ? '1' : '0';
        $payment_settings['tripay_sandbox_mode'] = isset($_POST['tripay_sandbox_mode']) ? '1' : '0';
        $payment_settings['paypal_enabled'] = isset($_POST['paypal_enabled']) ? '1' : '0';
        $payment_settings['paypal_sandbox_mode'] = isset($_POST['paypal_sandbox_mode']) ? '1' : '0';
        $payment_settings['midtrans_enabled'] = isset($_POST['midtrans_enabled']) ? '1' : '0';
        $payment_settings['midtrans_sandbox_mode'] = isset($_POST['midtrans_sandbox_mode']) ? '1' : '0';
        $payment_settings['bank_transfer_enabled'] = isset($_POST['bank_transfer_enabled']) ? '1' : '0';
        $payment_settings['ewallet_enabled'] = isset($_POST['ewallet_enabled']) ? '1' : '0';
        
        // Save payment settings to database
        foreach ($payment_settings as $key => $value) {
            epic_safe_db_query(
                "INSERT INTO " . TABLE_SETTINGS . " (`key`, `value`) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                [$key, $value],
                'select'
            );
        }
        
        // Log activity
        if (function_exists('epic_log_activity')) {
            epic_log_activity($user['id'], 'payment_settings_updated', 'Payment gateway settings updated');
        }
        
        $success = 'Payment gateway settings berhasil disimpan!';
        
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Get current payment settings
$current_settings = epic_safe_db_query(
    "SELECT `key`, `value` FROM " . TABLE_SETTINGS . " WHERE `key` LIKE 'payment_%' OR `key` LIKE 'tripay_%' OR `key` LIKE 'paypal_%' OR `key` LIKE 'midtrans_%' OR `key` LIKE 'bank_%' OR `key` LIKE 'ewallet_%'",
    [],
    'select'
);

// Convert to associative array
$payment_settings = [];
foreach ($current_settings as $setting) {
    $payment_settings[$setting['key']] = $setting['value'];
}

// Default payment settings if not exist
$default_payment_settings = [
    'payment_default_gateway' => 'tripay',
    'payment_currency' => 'IDR',
    'payment_tax_rate' => '0',
    // Tripay defaults
    'tripay_merchant_code' => '',
    'tripay_api_key' => '',
    'tripay_private_key' => '',
    'tripay_sandbox_mode' => '1',
    'tripay_enabled' => '1',
    // PayPal defaults
    'paypal_client_id' => '',
    'paypal_client_secret' => '',
    'paypal_sandbox_mode' => '1',
    'paypal_enabled' => '0',
    // Midtrans defaults
    'midtrans_server_key' => '',
    'midtrans_client_key' => '',
    'midtrans_sandbox_mode' => '1',
    'midtrans_enabled' => '0',
    // Bank Transfer defaults
    'bank_transfer_enabled' => '1',
    'bank_transfer_accounts' => json_encode([
        ['bank' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'EPIC Hub'],
        ['bank' => 'Mandiri', 'account_number' => '0987654321', 'account_name' => 'EPIC Hub']
    ]),
    // E-wallet defaults
    'ewallet_enabled' => '1',
    'ewallet_providers' => json_encode(['gopay', 'ovo', 'dana', 'linkaja'])
];

// Merge with defaults
foreach ($default_payment_settings as $key => $default_value) {
    if (!isset($payment_settings[$key])) {
        $payment_settings[$key] = $default_value;
    }
}

// Parse JSON fields
$payment_settings['bank_transfer_accounts_parsed'] = json_decode($payment_settings['bank_transfer_accounts'] ?? '[]', true);
$payment_settings['ewallet_providers_parsed'] = json_decode($payment_settings['ewallet_providers'] ?? '[]', true);

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Payment Gateway Settings - EPIC Hub Admin',
    'header_title' => 'Payment Gateway Settings',
    'current_page' => 'settings',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Settings', 'url' => epic_url('admin/settings')],
        ['text' => 'Payment Gateway']
    ],
    'content_file' => __DIR__ . '/content/settings-payment-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'payment_settings' => $payment_settings,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>