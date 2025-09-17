<?php
/**
 * EPIC Hub Starsender Notification Triggers
 * Fungsi-fungsi trigger untuk berbagai aktivitas user
 * 
 * @package EPIC Hub
 * @version 1.0.0
 */

require_once __DIR__ . '/starsender-notifications.php';

/**
 * Trigger notifikasi saat user baru mendaftar
 */
function epic_starsender_on_registration($user_data, $sponsor_data = []) {
    global $current_user_data, $current_sponsor_data;
    $current_user_data = $user_data;
    $current_sponsor_data = $sponsor_data;
    
    // Notifikasi ke user yang baru mendaftar
    $user_level = $user_data['account_type'] ?? 'free';
    $message_key = 'starsender_registration_' . $user_level . '_message';
    $message = epic_setting($message_key, '');
    
    if (!empty($message) && !empty($user_data['phone'])) {
        epic_send_starsender_notification(
            $user_data['phone'],
            $message,
            epic_setting('starsender_registration_image', ''),
            epic_setting('starsender_registration_button', '')
        );
    }
    
    // Notifikasi ke sponsor jika ada
    if (!empty($sponsor_data) && !empty($sponsor_data['phone'])) {
        $sponsor_level = $sponsor_data['account_type'] ?? 'free';
        $sponsor_message_key = 'starsender_new_referral_' . $sponsor_level . '_message';
        $sponsor_message = epic_setting($sponsor_message_key, '');
        
        if (!empty($sponsor_message)) {
            epic_send_starsender_notification(
                $sponsor_data['phone'],
                $sponsor_message,
                epic_setting('starsender_new_referral_image', ''),
                epic_setting('starsender_new_referral_button', '')
            );
        }
    }
}

/**
 * Trigger notifikasi saat user upgrade akun
 */
function epic_starsender_on_upgrade($user_data, $sponsor_data = []) {
    global $current_user_data, $current_sponsor_data;
    $current_user_data = $user_data;
    $current_sponsor_data = $sponsor_data;
    
    // Notifikasi ke user yang upgrade
    $user_level = $user_data['account_type'] ?? 'epic';
    $message_key = 'starsender_upgrade_' . $user_level . '_message';
    $message = epic_setting($message_key, '');
    
    if (!empty($message) && !empty($user_data['phone'])) {
        epic_send_starsender_notification(
            $user_data['phone'],
            $message,
            epic_setting('starsender_upgrade_image', ''),
            epic_setting('starsender_upgrade_button', '')
        );
    }
    
    // Notifikasi ke sponsor jika ada
    if (!empty($sponsor_data) && !empty($sponsor_data['phone'])) {
        $sponsor_level = $sponsor_data['account_type'] ?? 'free';
        $sponsor_message_key = 'starsender_referral_upgrade_' . $sponsor_level . '_message';
        $sponsor_message = epic_setting($sponsor_message_key, '');
        
        if (!empty($sponsor_message)) {
            epic_send_starsender_notification(
                $sponsor_data['phone'],
                $sponsor_message,
                epic_setting('starsender_referral_upgrade_image', ''),
                epic_setting('starsender_referral_upgrade_button', '')
            );
        }
    }
}

/**
 * Trigger notifikasi saat user order produk
 */
function epic_starsender_on_order($user_data, $order_data, $sponsor_data = []) {
    global $current_user_data, $current_sponsor_data, $current_order_data;
    $current_user_data = $user_data;
    $current_sponsor_data = $sponsor_data;
    $current_order_data = $order_data;
    
    // Notifikasi ke user yang order
    $user_level = $user_data['account_type'] ?? 'free';
    $message_key = 'starsender_order_' . $user_level . '_message';
    $message = epic_setting($message_key, '');
    
    if (!empty($message) && !empty($user_data['phone'])) {
        epic_send_starsender_notification(
            $user_data['phone'],
            $message,
            epic_setting('starsender_order_image', ''),
            epic_setting('starsender_order_button', '')
        );
    }
    
    // Notifikasi ke sponsor jika ada
    if (!empty($sponsor_data) && !empty($sponsor_data['phone'])) {
        $sponsor_level = $sponsor_data['account_type'] ?? 'free';
        $sponsor_message_key = 'starsender_referral_order_' . $sponsor_level . '_message';
        $sponsor_message = epic_setting($sponsor_message_key, '');
        
        if (!empty($sponsor_message)) {
            epic_send_starsender_notification(
                $sponsor_data['phone'],
                $sponsor_message,
                epic_setting('starsender_referral_order_image', ''),
                epic_setting('starsender_referral_order_button', '')
            );
        }
    }
}

/**
 * Trigger notifikasi saat order selesai diproses
 */
function epic_starsender_on_order_completed($user_data, $order_data, $sponsor_data = []) {
    global $current_user_data, $current_sponsor_data, $current_order_data;
    $current_user_data = $user_data;
    $current_sponsor_data = $sponsor_data;
    $current_order_data = $order_data;
    
    // Notifikasi ke user
    $user_level = $user_data['account_type'] ?? 'free';
    $message_key = 'starsender_order_completed_' . $user_level . '_message';
    $message = epic_setting($message_key, '');
    
    if (!empty($message) && !empty($user_data['phone'])) {
        epic_send_starsender_notification(
            $user_data['phone'],
            $message,
            epic_setting('starsender_order_completed_image', ''),
            epic_setting('starsender_order_completed_button', '')
        );
    }
}

/**
 * Trigger notifikasi saat pencairan payout/komisi
 */
function epic_starsender_on_payout($user_data, $payout_data) {
    global $current_user_data, $current_payout_data;
    $current_user_data = $user_data;
    $current_payout_data = $payout_data;
    
    // Notifikasi ke user yang menerima payout
    $user_level = $user_data['account_type'] ?? 'free';
    $message_key = 'starsender_payout_' . $user_level . '_message';
    $message = epic_setting($message_key, '');
    
    if (!empty($message) && !empty($user_data['phone'])) {
        // Add payout shortcodes
        $message = str_replace('[payout_amount]', number_format($payout_data['amount'] ?? 0, 0, ',', '.'), $message);
        $message = str_replace('[payout_date]', date('d/m/Y', strtotime($payout_data['created_at'] ?? 'now')), $message);
        $message = str_replace('[payout_method]', $payout_data['method'] ?? '', $message);
        
        epic_send_starsender_notification(
            $user_data['phone'],
            $message,
            epic_setting('starsender_payout_image', ''),
            epic_setting('starsender_payout_button', '')
        );
    }
}

/**
 * Note: Trigger functions are called directly from functions.php
 * No hook system needed - functions are called manually:
 * 
 * - epic_starsender_on_registration() called from epic_register_user()
 * - epic_starsender_on_upgrade() called from epic_upgrade_to_epic_account()
 * - epic_starsender_on_order() called from order processing
 * - epic_starsender_on_order_completed() called from order completion
 * - epic_starsender_on_payout() called from payout processing
 */

/**
 * Manual trigger functions for testing
 */
function epic_test_starsender_notification($type, $phone, $user_level = 'free') {
    $test_user = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => $phone,
        'account_type' => $user_level,
        'referral_code' => 'TEST123',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $test_sponsor = [
        'name' => 'Test Sponsor',
        'email' => 'sponsor@example.com',
        'phone' => '6281234567890',
        'account_type' => 'epic'
    ];
    
    $test_order = [
        'id' => 'ORD001',
        'total' => 100000,
        'product_name' => 'Test Product',
        'status' => 'completed',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    switch ($type) {
        case 'registration':
            epic_starsender_on_registration($test_user, $test_sponsor);
            break;
        case 'upgrade':
            epic_starsender_on_upgrade($test_user, $test_sponsor);
            break;
        case 'order':
            epic_starsender_on_order($test_user, $test_order, $test_sponsor);
            break;
        case 'order_completed':
            epic_starsender_on_order_completed($test_user, $test_order, $test_sponsor);
            break;
        case 'payout':
            $test_payout = [
                'amount' => 50000,
                'method' => 'Bank Transfer',
                'created_at' => date('Y-m-d H:i:s')
            ];
            epic_starsender_on_payout($test_user, $test_payout);
            break;
    }
}

?>