<?php
/**
 * EPIC Hub Autoresponder Functions
 * Functions for autoresponder email integration
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Process autoresponder shortcodes
 * 
 * @param string $content Content with shortcodes
 * @param array $member_data Member data
 * @param array $sponsor_data Sponsor data (optional)
 * @param array $order_data Order data (optional)
 * @param array $payout_data Payout data (optional)
 * @return string Processed content
 */
function epic_process_autoresponder_shortcodes($content, $member_data = [], $sponsor_data = [], $order_data = [], $payout_data = []) {
    if (empty($content)) {
        return $content;
    }
    
    // Member shortcodes
    if (!empty($member_data)) {
        $content = str_replace('[member_id]', $member_data['id'] ?? '', $content);
        $content = str_replace('[member_name]', $member_data['name'] ?? '', $content);
        $content = str_replace('[member_email]', $member_data['email'] ?? '', $content);
        $content = str_replace('[member_phone]', $member_data['phone'] ?? '', $content);
        $content = str_replace('[member_whatsapp]', $member_data['whatsapp'] ?? '', $content);
        $content = str_replace('[member_referral_code]', $member_data['referral_code'] ?? '', $content);
        $content = str_replace('[member_referral_url]', epic_url($member_data['referral_code'] ?? ''), $content);
        $content = str_replace('[member_join_date]', $member_data['created_at'] ?? '', $content);
        
        // Dynamic member fields
        foreach ($member_data as $key => $value) {
            if (!in_array($key, ['id', 'name', 'email', 'phone', 'whatsapp', 'referral_code', 'created_at'])) {
                $content = str_replace('[member_' . $key . ']', $value, $content);
            }
        }
    }
    
    // Sponsor shortcodes
    if (!empty($sponsor_data)) {
        $content = str_replace('[sponsor_id]', $sponsor_data['id'] ?? '', $content);
        $content = str_replace('[sponsor_name]', $sponsor_data['name'] ?? '', $content);
        $content = str_replace('[sponsor_email]', $sponsor_data['email'] ?? '', $content);
        $content = str_replace('[sponsor_phone]', $sponsor_data['phone'] ?? '', $content);
        $content = str_replace('[sponsor_whatsapp]', $sponsor_data['whatsapp'] ?? '', $content);
        $content = str_replace('[sponsor_referral_code]', $sponsor_data['referral_code'] ?? '', $content);
        $content = str_replace('[sponsor_referral_url]', epic_url($sponsor_data['referral_code'] ?? ''), $content);
        $content = str_replace('[sponsor_level]', $sponsor_data['level'] ?? '', $content);
        
        // Dynamic sponsor fields
        foreach ($sponsor_data as $key => $value) {
            if (!in_array($key, ['id', 'name', 'email', 'phone', 'whatsapp', 'referral_code', 'level'])) {
                $content = str_replace('[sponsor_' . $key . ']', $value, $content);
            }
        }
    }
    
    // Order shortcodes
    if (!empty($order_data)) {
        $content = str_replace('[idorder]', $order_data['id'] ?? '', $content);
        $content = str_replace('[hrgunik]', $order_data['unique_price'] ?? '', $content);
        $content = str_replace('[hrgproduk]', $order_data['product_price'] ?? '', $content);
        $content = str_replace('[namaproduk]', $order_data['product_name'] ?? '', $content);
        $content = str_replace('[urlproduk]', $order_data['product_url'] ?? '', $content);
        $content = str_replace('[order_status]', $order_data['status'] ?? '', $content);
        $content = str_replace('[order_date]', $order_data['created_at'] ?? '', $content);
    }
    
    // Payout shortcodes
    if (!empty($payout_data)) {
        $content = str_replace('[amount]', $payout_data['amount'] ?? '', $content);
        $content = str_replace('[payout_id]', $payout_data['id'] ?? '', $content);
        $content = str_replace('[bank_account]', $payout_data['bank_account'] ?? '', $content);
        $content = str_replace('[payout_date]', $payout_data['created_at'] ?? '', $content);
        $content = str_replace('[payout_status]', $payout_data['status'] ?? '', $content);
    }
    
    // System shortcodes
    $content = str_replace('[site_name]', epic_setting('site_name', 'EPIC Hub'), $content);
    $content = str_replace('[site_url]', epic_url(''), $content);
    $content = str_replace('[current_date]', date('Y-m-d'), $content);
    $content = str_replace('[current_time]', date('H:i:s'), $content);
    $content = str_replace('[ip_address]', $_SERVER['REMOTE_ADDR'] ?? '', $content);
    $content = str_replace('[user_agent]', $_SERVER['HTTP_USER_AGENT'] ?? '', $content);
    
    return $content;
}

/**
 * Send data to autoresponder
 * 
 * @param string $event Event type (daftar, upgrade, order, etc.)
 * @param array $member_data Member data
 * @param array $sponsor_data Sponsor data (optional)
 * @param array $order_data Order data (optional)
 * @param array $payout_data Payout data (optional)
 * @return bool Success status
 */
function epic_send_to_autoresponder($event, $member_data = [], $sponsor_data = [], $order_data = [], $payout_data = []) {
    try {
        // Get autoresponder settings
        $action_url = epic_setting('form_action_' . $event);
        
        if (empty($action_url)) {
            return false; // No action URL configured for this event
        }
        
        // Prepare form data
        $form_data = [];
        
        // Get field mappings for this event
        for ($i = 1; $i <= 10; $i++) {
            $field_name = epic_setting('form_field_' . $event . $i);
            $field_value = epic_setting('form_value_' . $event . $i);
            
            if (!empty($field_name) && !empty($field_value)) {
                // Process shortcodes in field value
                $processed_value = epic_process_autoresponder_shortcodes(
                    $field_value, 
                    $member_data, 
                    $sponsor_data, 
                    $order_data, 
                    $payout_data
                );
                
                $form_data[$field_name] = $processed_value;
            }
        }
        
        if (empty($form_data)) {
            return false; // No field mappings configured
        }
        
        // Send data to autoresponder
        $response = epic_send_http_request($action_url, $form_data, 'POST');
        
        // Log the activity
        if (function_exists('epic_log_activity')) {
            epic_log_activity(
                $member_data['id'] ?? 0, 
                'autoresponder_sent', 
                'Autoresponder data sent for event: ' . $event
            );
        }
        
        return $response !== false;
        
    } catch (Exception $e) {
        // Log error
        if (function_exists('epic_log_error')) {
            epic_log_error('Autoresponder Error: ' . $e->getMessage());
        }
        
        return false;
    }
}

/**
 * Send HTTP request to autoresponder
 * 
 * @param string $url Target URL
 * @param array $data Form data
 * @param string $method HTTP method
 * @return mixed Response or false on failure
 */
function epic_send_http_request($url, $data = [], $method = 'POST') {
    try {
        // Use cURL if available
        if (function_exists('curl_init')) {
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
            
            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: EPIC Hub Autoresponder/1.0'
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            
            // Check if request was successful
            if ($http_code >= 200 && $http_code < 300) {
                return $response;
            } else {
                return false;
            }
        }
        
        // Fallback to file_get_contents
        $context_options = [
            'http' => [
                'method' => $method,
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                           "User-Agent: EPIC Hub Autoresponder/1.0\r\n",
                'content' => http_build_query($data),
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($context_options);
        $response = file_get_contents($url, false, $context);
        
        return $response;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Trigger autoresponder on user registration
 * 
 * @param array $user_data User data
 * @param array $sponsor_data Sponsor data (optional)
 * @return bool Success status
 */
function epic_autoresponder_on_registration($user_data, $sponsor_data = []) {
    return epic_send_to_autoresponder('daftar', $user_data, $sponsor_data);
}

/**
 * Trigger autoresponder on user upgrade
 * 
 * @param array $user_data User data
 * @param array $sponsor_data Sponsor data (optional)
 * @return bool Success status
 */
function epic_autoresponder_on_upgrade($user_data, $sponsor_data = []) {
    return epic_send_to_autoresponder('upgrade', $user_data, $sponsor_data);
}

/**
 * Trigger autoresponder on new order
 * 
 * @param array $user_data User data
 * @param array $order_data Order data
 * @param array $sponsor_data Sponsor data (optional)
 * @return bool Success status
 */
function epic_autoresponder_on_order($user_data, $order_data, $sponsor_data = []) {
    return epic_send_to_autoresponder('order', $user_data, $sponsor_data, $order_data);
}

/**
 * Trigger autoresponder on order processing
 * 
 * @param array $user_data User data
 * @param array $order_data Order data
 * @param array $sponsor_data Sponsor data (optional)
 * @return bool Success status
 */
function epic_autoresponder_on_process_order($user_data, $order_data, $sponsor_data = []) {
    return epic_send_to_autoresponder('prosesorder', $user_data, $sponsor_data, $order_data);
}

/**
 * Trigger autoresponder on payout
 * 
 * @param array $user_data User data
 * @param array $payout_data Payout data
 * @param array $sponsor_data Sponsor data (optional)
 * @return bool Success status
 */
function epic_autoresponder_on_payout($user_data, $payout_data, $sponsor_data = []) {
    return epic_send_to_autoresponder('payout', $user_data, $sponsor_data, [], $payout_data);
}

/**
 * Trigger autoresponder on new referral
 * 
 * @param array $sponsor_data Sponsor data
 * @param array $referral_data Referral data
 * @return bool Success status
 */
function epic_autoresponder_on_referral($sponsor_data, $referral_data) {
    // Merge referral data into sponsor data for processing
    $referral_info = [
        'referral_name' => $referral_data['name'] ?? '',
        'referral_email' => $referral_data['email'] ?? '',
        'commission' => $referral_data['commission'] ?? ''
    ];
    
    $combined_data = array_merge($sponsor_data, $referral_info);
    
    return epic_send_to_autoresponder('referral', $combined_data);
}

/**
 * Test autoresponder connection
 * 
 * @param string $provider Provider name
 * @param string $api_key API key
 * @param string $api_url API URL (for custom)
 * @return array Test result
 */
function epic_test_autoresponder_connection($provider, $api_key, $api_url = '') {
    try {
        $test_data = [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'source' => 'EPIC Hub Test'
        ];
        
        $test_url = '';
        
        switch ($provider) {
            case 'mailchimp':
                // MailChimp API test
                $test_url = 'https://us1.api.mailchimp.com/3.0/ping';
                break;
                
            case 'aweber':
                // AWeber API test
                $test_url = 'https://api.aweber.com/1.0/accounts';
                break;
                
            case 'getresponse':
                // GetResponse API test
                $test_url = 'https://api.getresponse.com/v3/accounts';
                break;
                
            case 'activecampaign':
                // ActiveCampaign API test
                $test_url = 'https://youraccountname.api-us1.com/api/3/contacts';
                break;
                
            case 'convertkit':
                // ConvertKit API test
                $test_url = 'https://api.convertkit.com/v3/account';
                break;
                
            case 'custom':
                $test_url = $api_url;
                break;
                
            default:
                return ['success' => false, 'message' => 'Provider tidak didukung'];
        }
        
        if (empty($test_url)) {
            return ['success' => false, 'message' => 'URL API tidak valid'];
        }
        
        // Send test request
        $response = epic_send_http_request($test_url, $test_data, 'GET');
        
        if ($response !== false) {
            return ['success' => true, 'message' => 'Koneksi berhasil'];
        } else {
            return ['success' => false, 'message' => 'Koneksi gagal'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get available autoresponder providers
 * 
 * @return array List of providers
 */
function epic_get_autoresponder_providers() {
    return [
        'mailchimp' => 'MailChimp',
        'aweber' => 'AWeber',
        'getresponse' => 'GetResponse',
        'activecampaign' => 'ActiveCampaign',
        'convertkit' => 'ConvertKit',
        'custom' => 'Custom API'
    ];
}

/**
 * Get autoresponder statistics
 * 
 * @return array Statistics data
 */
function epic_get_autoresponder_stats() {
    try {
        // Get total sent count from activity log
        $total_sent = epic_safe_db_query(
            "SELECT COUNT(*) as count FROM " . TABLE_ACTIVITY_LOG . " WHERE action = 'autoresponder_sent'",
            [],
            'selectOne'
        );
        
        // Get sent count by event type
        $by_event = epic_safe_db_query(
            "SELECT description, COUNT(*) as count FROM " . TABLE_ACTIVITY_LOG . " 
             WHERE action = 'autoresponder_sent' 
             GROUP BY description",
            [],
            'select'
        );
        
        // Get recent activity
        $recent_activity = epic_safe_db_query(
            "SELECT * FROM " . TABLE_ACTIVITY_LOG . " 
             WHERE action = 'autoresponder_sent' 
             ORDER BY created_at DESC 
             LIMIT 10",
            [],
            'select'
        );
        
        return [
            'total_sent' => $total_sent['count'] ?? 0,
            'by_event' => $by_event ?? [],
            'recent_activity' => $recent_activity ?? []
        ];
        
    } catch (Exception $e) {
        return [
            'total_sent' => 0,
            'by_event' => [],
            'recent_activity' => []
        ];
    }
}
?>