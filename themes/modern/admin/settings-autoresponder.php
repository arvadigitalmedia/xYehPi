<?php
/**
 * EPIC Hub Admin Settings - Autoresponder Email
 * Autoresponder email integration settings management
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/integrasi/autoresponder-email');
$user = $init_result['user'];

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_autoresponder_settings'])) {
    try {
        // Process autoresponder settings update
        $autoresponder_settings = [];
        
        // Get all autoresponder settings from POST
        foreach ($_POST as $key => $value) {
            if ($key !== 'save_autoresponder_settings' && $key !== 'csrf_token') {
                $autoresponder_settings[$key] = $value;
            }
        }
        
        // Save autoresponder settings to database
        foreach ($autoresponder_settings as $key => $value) {
            epic_safe_db_query(
                "INSERT INTO " . TABLE_SETTINGS . " (`key`, `value`) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                [$key, $value],
                'select'
            );
        }
        
        // Log activity
        if (function_exists('epic_log_activity')) {
            epic_log_activity($user['id'], 'autoresponder_settings_updated', 'Autoresponder settings updated');
        }
        
        $success = 'Autoresponder settings berhasil disimpan!';
        
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Get current autoresponder settings
$current_settings = epic_safe_db_query(
    "SELECT `key`, `value` FROM " . TABLE_SETTINGS . " WHERE `key` LIKE 'autoresponder_%' OR `key` LIKE 'form_action_%' OR `key` LIKE 'form_field_%' OR `key` LIKE 'form_value_%'",
    [],
    'select'
);

// Convert to associative array
$autoresponder_settings = [];
foreach ($current_settings as $setting) {
    $autoresponder_settings[$setting['key']] = $setting['value'];
}

// Define notification events
$notification_events = [
    [
        'key' => 'daftar',
        'name' => 'Subscribe saat Registrasi',
        'description' => 'Integrasi autoresponder saat member baru mendaftar',
        'shortcodes' => ''
    ],
    [
        'key' => 'upgrade',
        'name' => 'Subscribe saat Upgrade',
        'description' => 'Integrasi autoresponder saat member melakukan upgrade',
        'shortcodes' => ''
    ],
    [
        'key' => 'order',
        'name' => 'Subscribe saat Order Produk',
        'description' => 'Integrasi autoresponder saat ada order produk baru',
        'shortcodes' => '<code>[idorder]</code>: Nomor ID Invoice<br/><code>[hrgunik]</code>: Harga dengan kode unik<br/><code>[hrgproduk]</code>: Harga produk asli<br/><code>[namaproduk]</code>: Nama Produk<br/><code>[urlproduk]</code>: kode URL Produk'
    ],
    [
        'key' => 'prosesorder',
        'name' => 'Subscribe saat Proses Order',
        'description' => 'Integrasi autoresponder saat order diproses',
        'shortcodes' => '<code>[idorder]</code>: Nomor ID Invoice<br/><code>[hrgunik]</code>: Harga dengan kode unik<br/><code>[hrgproduk]</code>: Harga produk asli<br/><code>[namaproduk]</code>: Nama Produk<br/><code>[urlproduk]</code>: kode URL Produk'
    ],
    [
        'key' => 'payout',
        'name' => 'Subscribe saat Payout',
        'description' => 'Integrasi autoresponder saat pembayaran komisi',
        'shortcodes' => '<code>[amount]</code>: Jumlah pembayaran<br/><code>[payout_id]</code>: ID Payout<br/><code>[bank_account]</code>: Rekening bank'
    ],
    [
        'key' => 'referral',
        'name' => 'Subscribe saat Dapat Referral',
        'description' => 'Integrasi autoresponder saat mendapat referral baru',
        'shortcodes' => '<code>[referral_name]</code>: Nama referral<br/><code>[referral_email]</code>: Email referral<br/><code>[commission]</code>: Komisi yang didapat'
    ]
];

// Get form fields for dynamic shortcodes
$form_fields = epic_safe_db_query(
    "SELECT * FROM " . TABLE_FORM_FIELDS . " ORDER BY sort_order ASC, id ASC",
    [],
    'select'
);

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Autoresponder Email Settings - EPIC Hub Admin',
    'header_title' => 'Autoresponder Email Settings',
    'current_page' => 'settings',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Settings', 'url' => epic_url('admin/settings')],
        ['text' => 'Autoresponder Email']
    ],
    'content_file' => __DIR__ . '/content/settings-autoresponder-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'autoresponder_settings' => $autoresponder_settings,
    'notification_events' => $notification_events,
    'form_fields' => $form_fields,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>