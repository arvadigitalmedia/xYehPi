<?php
/**
 * EPIC Hub Admin Settings - WhatsApp Notifications
 * Pengaturan notifikasi WhatsApp menggunakan Starsender API
 * 
 * @package EPIC Hub
 * @version 1.0.0
 */

// Security check
if (!defined('EPIC_INIT')) {
    die('Direct access not permitted');
}

// Include required files
require_once __DIR__ . '/routing-helper.php';
require_once __DIR__ . '/layout-helper.php';
require_once __DIR__ . '/../../../core/starsender-notifications.php';
require_once __DIR__ . '/../../../core/starsender-triggers.php';

// Initialize admin page
$init_result = epic_init_admin_page('admin', 'admin/settings/whatsapp-notification');
$user = $init_result['user'];

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // CSRF protection
        if (!epic_verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        // Save Starsender settings
        $settings_to_save = [
            'starsender_enabled',
            'starsender_api_key',
            'test_phone_number',
            
            // 1. Registration messages - User
            'starsender_registration_user_free_message',
            'starsender_registration_user_epic_message', 
            'starsender_registration_user_epis_message',
            // Registration messages - Referral
            'starsender_registration_referral_message',
            'starsender_registration_image',
            'starsender_registration_button',
            
            // 2. Upgrade messages - User
            'starsender_upgrade_user_message',
            // Upgrade messages - Sponsor
            'starsender_upgrade_sponsor_message',
            'starsender_upgrade_image',
            'starsender_upgrade_button',
            
            // 3. Purchase messages - Buyer
            'starsender_purchase_buyer_message',
            // Purchase messages - Referral
            'starsender_purchase_referral_message',
            'starsender_purchase_image',
            'starsender_purchase_button',
            
            // 4. Payout messages
            'starsender_payout_message',
            'starsender_payout_image',
            'starsender_payout_button',
            
            // 5. Closing EPIC Account messages
            'starsender_closing_epis_message',
            'starsender_closing_image',
            'starsender_closing_button'
        ];
        
        foreach ($settings_to_save as $setting) {
            $value = $_POST[$setting] ?? '';
            epic_safe_db_query(
                "INSERT INTO " . TABLE_SETTINGS . " (`key`, `value`) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                [$setting, $value],
                'select'
            );
        }
        
        // Log activity
        epic_log_activity($user['id'], 'whatsapp_notification_settings_updated', 'WhatsApp notification settings updated');
        
        $success = 'Pengaturan notifikasi WhatsApp berhasil disimpan!';
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Test connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_connection'])) {
    header('Content-Type: application/json');
    try {
        $api_key = $_POST['starsender_api_key'] ?? '';
        $test_phone = $_POST['test_phone_number'] ?? '';
        
        if (empty($api_key)) {
            throw new Exception('API Key Starsender diperlukan untuk test koneksi');
        }
        
        if (empty($test_phone)) {
            throw new Exception('Nomor tujuan test diperlukan');
        }
        
        // Validate phone number format
        if (!preg_match('/^62\d{9,13}$/', $test_phone)) {
            throw new Exception('Format nomor tidak valid. Gunakan format: 628xxxxxxxxx');
        }
        
        // Test message
        $test_message = 'Test koneksi whatsapp, jika kamu bisa membaca pesan ini artinya koneksi berhasil dilakukan';
        
        // Send test message
        $result = epic_send_starsender_notification($test_phone, $test_message, '', '');
        
        if ($result['success']) {
            echo json_encode([
                'success' => true, 
                'message' => 'Test koneksi berhasil! Pesan telah dikirim ke ' . $test_phone
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Test koneksi gagal: ' . $result['message']
            ]);
        }
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Test notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_notification'])) {
    header('Content-Type: application/json');
    try {
        $api_key = $_POST['starsender_api_key'] ?? '';
        $notification_type = $_POST['notification_type'] ?? '';
        
        if (empty($api_key)) {
            throw new Exception('API Key Starsender diperlukan untuk test notifikasi');
        }
        
        if (empty($notification_type)) {
            throw new Exception('Tipe notifikasi tidak valid');
        }
        
        // Get admin phone number for testing
        $admin_phone = '6281234567890'; // Default test number
        if (isset($user['phone']) && !empty($user['phone'])) {
            $admin_phone = $user['phone'];
        }
        
        // Test message based on type
        $test_messages = [
            'registration' => 'Test notifikasi pendaftaran user baru dari EPIC Hub. Fitur notifikasi WhatsApp berfungsi dengan baik!',
            'upgrade' => 'Test notifikasi upgrade akun dari EPIC Hub. Sistem notifikasi upgrade berjalan normal!',
            'purchase' => 'Test notifikasi pembelian produk dari EPIC Hub. Notifikasi pembelian berhasil dikirim!',
            'payout' => 'Test notifikasi pencairan komisi dari EPIC Hub. Sistem payout notification aktif!',
            'closing' => 'Test notifikasi closing EPIC Account dari EPIC Hub. Notifikasi supervisor berfungsi dengan baik!'
        ];
        
        $message = $test_messages[$notification_type] ?? 'Test notifikasi WhatsApp dari EPIC Hub';
        
        // Send test notification
        $result = epic_send_starsender_notification($admin_phone, $message, '', '');
        
        if ($result['success']) {
            echo json_encode([
                'success' => true, 
                'message' => 'Test notifikasi berhasil dikirim ke ' . $admin_phone
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Gagal mengirim test notifikasi: ' . $result['message']
            ]);
        }
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Get current settings
$current_settings = epic_safe_db_query(
    "SELECT `key`, `value` FROM " . TABLE_SETTINGS . " WHERE `key` LIKE 'starsender_%'",
    [],
    'select'
);

// Convert to associative array
$starsender_settings = [];
foreach ($current_settings as $setting) {
    $starsender_settings[$setting['key']] = $setting['value'];
}

// Get shortcodes
$shortcodes = epic_get_starsender_shortcodes();

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'WhatsApp Notification Settings - EPIC Hub Admin',
    'header_title' => 'WhatsApp Notification Settings',
    'current_page' => 'settings',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Settings', 'url' => epic_url('admin/settings')],
        ['text' => 'WhatsApp Notifications']
    ],
    'content_file' => __DIR__ . '/content/settings-whatsapp-notification-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'starsender_settings' => $starsender_settings,
    'shortcodes' => $shortcodes,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>