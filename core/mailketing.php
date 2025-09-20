<?php
/**
 * Mailketing API Integration
 * Wrapper functions untuk mengirim email via Mailketing API
 */

// Prevent direct access
if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

/**
 * Send email via Mailketing API
 * 
 * @param string $to Email tujuan
 * @param string $subject Subject email
 * @param string $message Isi email (HTML)
 * @param string $from_name Nama pengirim (optional)
 * @param string $from_email Email pengirim (optional)
 * @return array Response dari API
 */
function epic_send_email_mailketing($to, $subject, $message, $from_name = null, $from_email = null) {
    // Cek apakah Mailketing diaktifkan
    if (!defined('MAILKETING_ENABLED') || MAILKETING_ENABLED !== 'true') {
        return [
            'success' => false,
            'error' => 'Mailketing API tidak diaktifkan'
        ];
    }
    
    // Validasi konfigurasi
    $required_configs = ['MAILKETING_API_URL', 'MAILKETING_API_TOKEN'];
    foreach ($required_configs as $config) {
        if (!defined($config) || empty(constant($config))) {
            return [
                'success' => false,
                'error' => "Konfigurasi $config tidak ditemukan"
            ];
        }
    }
    
    // Set default values
    if (empty($from_name)) {
        $from_name = defined('MAILKETING_FROM_NAME') ? MAILKETING_FROM_NAME : 'Admin Bisnisemasperak.com';
    }
    if (empty($from_email)) {
        $from_email = defined('MAILKETING_FROM_EMAIL') ? MAILKETING_FROM_EMAIL : 'email@bisnisemasperak.com';
    }
    
    // Prepare data untuk API sesuai dokumentasi Mailketing
    $data = [
        'api_token' => MAILKETING_API_TOKEN,
        'recipient' => $to,
        'subject' => $subject,
        'content' => $message,
        'from_name' => $from_name,
        'from_email' => $from_email
    ];
    
    // Log aktivitas
    if (function_exists('epic_log_activity') && isset($_SESSION['user_id'])) {
        epic_log_activity($_SESSION['user_id'], 'mailketing_send_attempt', [
            'to' => $to,
            'subject' => $subject,
            'from_name' => $from_name,
            'from_email' => $from_email
        ]);
    }
    
    try {
        // Initialize cURL
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => MAILKETING_API_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: EPIC-Hub/2.0'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        // Handle cURL errors
        if ($response === false || !empty($curl_error)) {
            $error_msg = "cURL Error: " . $curl_error;
            error_log("Mailketing API cURL Error: " . $error_msg);
            
            return [
                'success' => false,
                'error' => $error_msg,
                'http_code' => $http_code
            ];
        }
        
        // Parse response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Mailketing API JSON Error: " . json_last_error_msg() . " | Response: " . $response);
            return [
                'success' => false,
                'error' => 'Invalid JSON response from Mailketing API',
                'raw_response' => $response,
                'http_code' => $http_code
            ];
        }
        
        // Log response
        if (function_exists('epic_log_activity') && isset($_SESSION['user_id'])) {
            epic_log_activity($_SESSION['user_id'], 'mailketing_send_response', [
                'to' => $to,
                'http_code' => $http_code,
                'success' => isset($result['success']) ? $result['success'] : false,
                'response' => $result
            ]);
        }
        
        // Check API response - Mailketing menggunakan format berbeda
        if ($http_code === 200 && isset($result['status']) && $result['status'] === 'success') {
            return [
                'success' => true,
                'message' => 'Email berhasil dikirim via Mailketing',
                'response' => $result,
                'http_code' => $http_code
            ];
        } else {
            $error_msg = isset($result['response']) ? $result['response'] : 
                        (isset($result['message']) ? $result['message'] : 'Unknown error from Mailketing API');
            error_log("Mailketing API Error: " . $error_msg . " | HTTP Code: " . $http_code);
            
            return [
                'success' => false,
                'error' => $error_msg,
                'response' => $result,
                'http_code' => $http_code
            ];
        }
        
    } catch (Exception $e) {
        $error_msg = "Exception: " . $e->getMessage();
        error_log("Mailketing API Exception: " . $error_msg);
        
        return [
            'success' => false,
            'error' => $error_msg
        ];
    }
}

/**
 * Test Mailketing API connection
 * 
 * @return array Test result
 */
function epic_test_mailketing_connection() {
    $test_email = defined('MAILKETING_FROM_EMAIL') ? MAILKETING_FROM_EMAIL : 'test@bisnisemasperak.com';
    
    $result = epic_send_email_mailketing(
        $test_email,
        'Test Connection - ' . date('Y-m-d H:i:s'),
        '<h1>Test Email</h1><p>Ini adalah test email untuk memverifikasi koneksi Mailketing API.</p><p>Waktu: ' . date('Y-m-d H:i:s') . '</p>',
        'Test System',
        $test_email
    );
    
    return $result;
}

/**
 * Get Mailketing configuration status
 * 
 * @return array Configuration status
 */
function epic_get_mailketing_status() {
    $status = [
        'enabled' => defined('MAILKETING_ENABLED') && MAILKETING_ENABLED === 'true',
        'configured' => true,
        'missing_configs' => []
    ];
    
    $required_configs = [
        'MAILKETING_API_URL',
        'MAILKETING_API_TOKEN',
        'MAILKETING_FROM_NAME',
        'MAILKETING_FROM_EMAIL'
    ];
    
    foreach ($required_configs as $config) {
        if (!defined($config) || empty(constant($config))) {
            $status['configured'] = false;
            $status['missing_configs'][] = $config;
        }
    }
    
    return $status;
}
?>