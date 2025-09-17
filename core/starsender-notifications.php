<?php
/**
 * EPIC Hub Starsender WhatsApp Notifications Integration
 * Sistem notifikasi WhatsApp menggunakan Starsender API
 * 
 * @package EPIC Hub
 * @version 1.0.0
 */

/**
 * Send WhatsApp notification via Starsender API
 * 
 * @param string $phone_number Nomor WhatsApp tujuan
 * @param string $message Pesan yang akan dikirim
 * @param string $image_url URL gambar (optional)
 * @param string $button_text Teks button (optional)
 * @return array Result of API call
 */
function epic_send_starsender_notification($phone_number, $message, $image_url = '', $button_text = '') {
    try {
        // Get Starsender API key from settings
        $api_key = epic_setting('starsender_api_key');
        
        if (empty($api_key)) {
            throw new Exception('Starsender API Key tidak ditemukan. Silakan konfigurasi di pengaturan.');
        }
        
        // Format phone number
        $formatted_phone = epic_format_phone_starsender($phone_number);
        
        // Process shortcodes in message
        $processed_message = epic_process_starsender_shortcodes($message);
        
        // Send message with button if button text provided
        if (!empty($button_text)) {
            return epic_send_starsender_button_message($api_key, $formatted_phone, $processed_message, $button_text, $image_url);
        } else {
            return epic_send_starsender_text_message($api_key, $formatted_phone, $processed_message, $image_url);
        }
        
    } catch (Exception $e) {
        error_log('Starsender Notification Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Send text message via Starsender
 */
function epic_send_starsender_text_message($api_key, $phone, $message, $image_url = '') {
    $data = [
        'tujuan' => $phone,
        'message' => $message
    ];
    
    if (!empty($image_url)) {
        $data['file'] = $image_url;
    }
    
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://starsender.online/api/sendFiles?message=' . rawurlencode($message) . '&tujuan=' . rawurlencode($phone . '@s.whatsapp.net'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => !empty($image_url) ? ['file' => $image_url] : [],
        CURLOPT_HTTPHEADER => [
            'apikey: ' . $api_key
        ],
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return [
        'success' => $http_code === 200,
        'response' => $response,
        'http_code' => $http_code
    ];
}

/**
 * Send button message via Starsender
 */
function epic_send_starsender_button_message($api_key, $phone, $message, $button_text, $image_url = '') {
    $data = [
        'tujuan' => $phone,
        'message' => $message,
        'button' => $button_text
    ];
    
    if (!empty($image_url)) {
        $data['file_url'] = $image_url;
    }
    
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://starsender.online/api/sendButton',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . $api_key
        ],
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return [
        'success' => $http_code === 200,
        'response' => $response,
        'http_code' => $http_code
    ];
}

/**
 * Format phone number for Starsender API
 */
function epic_format_phone_starsender($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add country code if not present
    if (substr($phone, 0, 2) !== '62') {
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } else {
            $phone = '62' . $phone;
        }
    }
    
    return $phone;
}

/**
 * Process shortcodes in message
 */
function epic_process_starsender_shortcodes($message, $user_data = [], $sponsor_data = [], $order_data = []) {
    global $current_user_data, $current_sponsor_data, $current_order_data;
    
    // Use global data if not provided
    if (empty($user_data) && !empty($current_user_data)) {
        $user_data = $current_user_data;
    }
    if (empty($sponsor_data) && !empty($current_sponsor_data)) {
        $sponsor_data = $current_sponsor_data;
    }
    if (empty($order_data) && !empty($current_order_data)) {
        $order_data = $current_order_data;
    }
    
    // User shortcodes
    if (!empty($user_data)) {
        $message = str_replace('[user_name]', $user_data['name'] ?? '', $message);
        $message = str_replace('[user_email]', $user_data['email'] ?? '', $message);
        $message = str_replace('[user_phone]', $user_data['phone'] ?? '', $message);
        $message = str_replace('[user_level]', $user_data['account_type'] ?? 'free', $message);
        $message = str_replace('[user_referral_code]', $user_data['referral_code'] ?? '', $message);
        $message = str_replace('[user_join_date]', date('d/m/Y', strtotime($user_data['created_at'] ?? 'now')), $message);
    }
    
    // Sponsor shortcodes
    if (!empty($sponsor_data)) {
        $message = str_replace('[sponsor_name]', $sponsor_data['name'] ?? '', $message);
        $message = str_replace('[sponsor_email]', $sponsor_data['email'] ?? '', $message);
        $message = str_replace('[sponsor_phone]', $sponsor_data['phone'] ?? '', $message);
        $message = str_replace('[sponsor_level]', $sponsor_data['account_type'] ?? 'free', $message);
    }
    
    // Order shortcodes
    if (!empty($order_data)) {
        $message = str_replace('[order_id]', $order_data['id'] ?? '', $message);
        $message = str_replace('[order_total]', number_format($order_data['total'] ?? 0, 0, ',', '.'), $message);
        $message = str_replace('[product_name]', $order_data['product_name'] ?? '', $message);
        $message = str_replace('[order_date]', date('d/m/Y H:i', strtotime($order_data['created_at'] ?? 'now')), $message);
        $message = str_replace('[order_status]', $order_data['status'] ?? '', $message);
    }
    
    // System shortcodes
    $message = str_replace('[site_name]', epic_setting('site_name', 'EPIC Hub'), $message);
    $message = str_replace('[site_url]', epic_setting('site_url', 'https://epichub.com'), $message);
    $message = str_replace('[current_date]', date('d/m/Y'), $message);
    $message = str_replace('[current_time]', date('H:i'), $message);
    $message = str_replace('[current_year]', date('Y'), $message);
    
    return $message;
}

/**
 * Test Starsender API connection
 */
function epic_test_starsender_connection($api_key) {
    try {
        // Test with a simple API call
        $test_phone = '6281234567890'; // Test number
        $test_message = 'Test koneksi Starsender API - ' . date('Y-m-d H:i:s');
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://starsender.online/api/sendFiles?message=' . rawurlencode($test_message) . '&tujuan=' . rawurlencode($test_phone . '@s.whatsapp.net'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $api_key
            ],
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        curl_close($curl);
        
        if ($curl_error) {
            throw new Exception('CURL Error: ' . $curl_error);
        }
        
        return [
            'success' => $http_code === 200,
            'message' => $http_code === 200 ? 'Koneksi berhasil!' : 'Koneksi gagal. HTTP Code: ' . $http_code,
            'response' => $response,
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Test koneksi gagal: ' . $e->getMessage()
        ];
    }
}

/**
 * Get available shortcodes for message templates
 */
function epic_get_starsender_shortcodes() {
    return [
        'user' => [
            '[user_name]' => 'Nama lengkap user',
            '[user_email]' => 'Email user',
            '[user_phone]' => 'Nomor telepon user',
            '[user_level]' => 'Level akun user (free/epic/epis)',
            '[user_referral_code]' => 'Kode referral user',
            '[user_join_date]' => 'Tanggal bergabung user'
        ],
        'sponsor' => [
            '[sponsor_name]' => 'Nama sponsor/referrer',
            '[sponsor_email]' => 'Email sponsor',
            '[sponsor_phone]' => 'Nomor telepon sponsor',
            '[sponsor_level]' => 'Level akun sponsor'
        ],
        'order' => [
            '[order_id]' => 'ID order',
            '[order_total]' => 'Total order',
            '[product_name]' => 'Nama produk',
            '[order_date]' => 'Tanggal order',
            '[order_status]' => 'Status order'
        ],
        'system' => [
            '[site_name]' => 'Nama website',
            '[site_url]' => 'URL website',
            '[current_date]' => 'Tanggal hari ini',
            '[current_time]' => 'Waktu saat ini',
            '[current_year]' => 'Tahun saat ini'
        ]
    ];
}

?>