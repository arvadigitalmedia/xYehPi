<?php
/**
 * EPIC Hub Mailketing API Endpoints
 * Menangani semua request API untuk integrasi Mailketing
 */

if (!defined('EPIC_INIT')) {
    define('EPIC_INIT', true);
    require_once '../../bootstrap.php';
}

// Set JSON response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Extract endpoint from path
$endpoint = end($path_parts);

try {
    switch ($endpoint) {
        case 'credits':
            handleCreditsRequest();
            break;
            
        case 'lists':
            handleListsRequest();
            break;
            
        case 'sync-subscribers':
            handleSyncSubscribersRequest();
            break;
            
        case 'test-webhook':
            handleTestWebhookRequest();
            break;
            
        case 'webhook':
            handleWebhookRequest();
            break;
            
        case 'stats':
            handleStatsRequest();
            break;
            
        case 'export-logs':
            handleExportLogsRequest();
            break;
            
        default:
            throw new Exception('Endpoint tidak ditemukan');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Handle credits check request
 */
function handleCreditsRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }
    
    // Ambil API token dari settings
    $settings = epic_get_all_settings();
    $api_token = $settings['mailketing_api_token'] ?? '';
    
    if (empty($api_token)) {
        throw new Exception('API Token belum dikonfigurasi');
    }
    
    // Call Mailketing API untuk cek credits
    $response = epic_mailketing_check_credits($api_token);
    
    if ($response['success']) {
        echo json_encode([
            'success' => true,
            'credits' => $response['credits']
        ]);
    } else {
        throw new Exception($response['error'] ?? 'Gagal mengecek credits');
    }
}

/**
 * Handle lists request
 */
function handleListsRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }
    
    // Ambil API token dari settings
    $settings = epic_get_all_settings();
    $api_token = $settings['mailketing_api_token'] ?? '';
    
    if (empty($api_token)) {
        throw new Exception('API Token belum dikonfigurasi');
    }
    
    // Call Mailketing API untuk get lists
    $response = epic_mailketing_get_lists($api_token);
    
    if ($response['success']) {
        echo json_encode([
            'success' => true,
            'lists' => $response['lists']
        ]);
    } else {
        throw new Exception($response['error'] ?? 'Gagal memuat lists');
    }
}

/**
 * Handle sync subscribers request
 */
function handleSyncSubscribersRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }
    
    // Ambil API token dan default list ID dari settings
    $settings = epic_get_all_settings();
    $api_token = $settings['mailketing_api_token'] ?? '';
    $default_list_id = $settings['mailketing_default_list_id'] ?? 1;
    
    if (empty($api_token)) {
        throw new Exception('API Token belum dikonfigurasi');
    }
    
    // Sync subscribers dari database ke Mailketing
    $response = epic_mailketing_sync_subscribers($api_token, $default_list_id);
    
    if ($response['success']) {
        echo json_encode([
            'success' => true,
            'synced' => $response['synced'],
            'message' => 'Sync berhasil'
        ]);
    } else {
        throw new Exception($response['error'] ?? 'Gagal sync subscribers');
    }
}

/**
 * Handle test webhook request
 */
function handleTestWebhookRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }
    
    // Test webhook dengan data dummy
    $test_data = [
        'event' => 'test',
        'email' => 'test@example.com',
        'timestamp' => time(),
        'list_id' => 1
    ];
    
    // Log test webhook
    epic_log_webhook_event('test', $test_data);
    
    echo json_encode([
        'success' => true,
        'message' => 'Test webhook berhasil'
    ]);
}

/**
 * Handle incoming webhook from Mailketing
 */
function handleWebhookRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }
    
    // Get raw POST data
    $raw_data = file_get_contents('php://input');
    $webhook_data = json_decode($raw_data, true);
    
    if (!$webhook_data) {
        throw new Exception('Invalid webhook data');
    }
    
    // Process webhook berdasarkan event type
    $event_type = $webhook_data['event'] ?? 'unknown';
    
    switch ($event_type) {
        case 'subscriber_added':
            epic_process_subscriber_added_webhook($webhook_data);
            break;
            
        case 'subscriber_removed':
            epic_process_subscriber_removed_webhook($webhook_data);
            break;
            
        case 'email_opened':
            epic_process_email_opened_webhook($webhook_data);
            break;
            
        case 'email_clicked':
            epic_process_email_clicked_webhook($webhook_data);
            break;
            
        case 'email_bounced':
            epic_process_email_bounced_webhook($webhook_data);
            break;
            
        default:
            epic_log_webhook_event($event_type, $webhook_data);
            break;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Webhook processed'
    ]);
}

/**
 * Handle stats request
 */
function handleStatsRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }
    
    // Get email statistics dari database
    $stats = epic_get_email_statistics();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

/**
 * Handle export logs request
 */
function handleExportLogsRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method tidak diizinkan');
    }
    
    // Export email logs sebagai CSV
    $logs = epic_get_email_logs();
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="mailketing_logs_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV header
    fputcsv($output, ['Timestamp', 'Event', 'Email', 'Subject', 'Status', 'Details']);
    
    // CSV data
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['created_at'],
            $log['event_type'],
            $log['email'],
            $log['subject'] ?? '',
            $log['status'],
            $log['details'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}

/**
 * Check Mailketing credits via API
 */
function epic_mailketing_check_credits($api_token) {
    $url = 'https://mailketing.co.id/api/v1/credits';
    
    $data = [
        'api_token' => $api_token
    ];
    
    $response = epic_http_request($url, 'POST', $data);
    
    if ($response['success']) {
        $result = json_decode($response['body'], true);
        
        if ($result && isset($result['credits'])) {
            return [
                'success' => true,
                'credits' => $result['credits']
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Invalid response format'
            ];
        }
    } else {
        return [
            'success' => false,
            'error' => $response['error']
        ];
    }
}

/**
 * Get all lists from Mailketing account
 */
function epic_mailketing_get_lists($api_token) {
    $url = 'https://mailketing.co.id/api/v1/lists';
    
    $data = [
        'api_token' => $api_token
    ];
    
    $response = epic_http_request($url, 'POST', $data);
    
    if ($response['success']) {
        $result = json_decode($response['body'], true);
        
        if ($result && isset($result['lists'])) {
            return [
                'success' => true,
                'lists' => $result['lists']
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Invalid response format'
            ];
        }
    } else {
        return [
            'success' => false,
            'error' => $response['error']
        ];
    }
}

/**
 * Sync subscribers from database to Mailketing
 */
function epic_mailketing_sync_subscribers($api_token, $list_id) {
    global $epic_db;
    
    try {
        // Get all active users from database
        $stmt = $epic_db->prepare("
            SELECT email, name 
            FROM epic_users 
            WHERE status = 'active' 
            AND email IS NOT NULL 
            AND email != ''
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $synced_count = 0;
        $errors = [];
        
        foreach ($users as $user) {
            $result = epic_mailketing_add_subscriber(
                $api_token,
                $list_id,
                $user['email'],
                $user['name']
            );
            
            if ($result['success']) {
                $synced_count++;
            } else {
                $errors[] = $user['email'] . ': ' . $result['error'];
            }
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }
        
        return [
            'success' => true,
            'synced' => $synced_count,
            'errors' => $errors
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Add subscriber to Mailketing list
 */
function epic_mailketing_add_subscriber($api_token, $list_id, $email, $name = '') {
    $url = 'https://mailketing.co.id/api/v1/subscriber/add';
    
    $data = [
        'api_token' => $api_token,
        'list_id' => $list_id,
        'email' => $email,
        'name' => $name
    ];
    
    $response = epic_http_request($url, 'POST', $data);
    
    if ($response['success']) {
        $result = json_decode($response['body'], true);
        
        if ($result && isset($result['status'])) {
            return [
                'success' => $result['status'] === 'success',
                'message' => $result['message'] ?? ''
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Invalid response format'
            ];
        }
    } else {
        return [
            'success' => false,
            'error' => $response['error']
        ];
    }
}

/**
 * Log webhook event
 */
function epic_log_webhook_event($event_type, $data) {
    global $epic_db;
    
    try {
        $stmt = $epic_db->prepare("
            INSERT INTO epi_mailketing_logs 
            (event_type, email, data, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $event_type,
            $data['email'] ?? '',
            json_encode($data)
        ]);
        
    } catch (Exception $e) {
        error_log('Failed to log webhook event: ' . $e->getMessage());
    }
}

/**
 * Process subscriber added webhook
 */
function epic_process_subscriber_added_webhook($data) {
    // Update user status atau log event
    epic_log_webhook_event('subscriber_added', $data);
}

/**
 * Process subscriber removed webhook
 */
function epic_process_subscriber_removed_webhook($data) {
    // Update user status atau log event
    epic_log_webhook_event('subscriber_removed', $data);
}

/**
 * Process email opened webhook
 */
function epic_process_email_opened_webhook($data) {
    global $epic_db;
    
    try {
        // Update email statistics
        $stmt = $epic_db->prepare("
            UPDATE epi_email_stats 
            SET opened_count = opened_count + 1 
            WHERE email = ?
        ");
        $stmt->execute([$data['email'] ?? '']);
        
        epic_log_webhook_event('email_opened', $data);
        
    } catch (Exception $e) {
        error_log('Failed to process email opened webhook: ' . $e->getMessage());
    }
}

/**
 * Process email clicked webhook
 */
function epic_process_email_clicked_webhook($data) {
    global $epic_db;
    
    try {
        // Update email statistics
        $stmt = $epic_db->prepare("
            UPDATE epi_email_stats 
            SET clicked_count = clicked_count + 1 
            WHERE email = ?
        ");
        $stmt->execute([$data['email'] ?? '']);
        
        epic_log_webhook_event('email_clicked', $data);
        
    } catch (Exception $e) {
        error_log('Failed to process email clicked webhook: ' . $e->getMessage());
    }
}

/**
 * Process email bounced webhook
 */
function epic_process_email_bounced_webhook($data) {
    global $epic_db;
    
    try {
        // Update email statistics
        $stmt = $epic_db->prepare("
            UPDATE epi_email_stats 
            SET bounced_count = bounced_count + 1 
            WHERE email = ?
        ");
        $stmt->execute([$data['email'] ?? '']);
        
        epic_log_webhook_event('email_bounced', $data);
        
    } catch (Exception $e) {
        error_log('Failed to process email bounced webhook: ' . $e->getMessage());
    }
}

/**
 * Get email statistics
 */
function epic_get_email_statistics() {
    global $epic_db;
    
    try {
        $stmt = $epic_db->prepare("
            SELECT 
                COALESCE(SUM(sent_count), 0) as sent,
                COALESCE(SUM(opened_count), 0) as opened,
                COALESCE(SUM(clicked_count), 0) as clicked,
                COALESCE(SUM(bounced_count), 0) as bounced
            FROM epi_email_stats
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $stats ?: [
            'sent' => 0,
            'opened' => 0,
            'clicked' => 0,
            'bounced' => 0
        ];
        
    } catch (Exception $e) {
        return [
            'sent' => 0,
            'opened' => 0,
            'clicked' => 0,
            'bounced' => 0
        ];
    }
}

/**
 * Get email logs for export
 */
function epic_get_email_logs($limit = 1000) {
    global $epic_db;
    
    try {
        $stmt = $epic_db->prepare("
            SELECT 
                created_at,
                event_type,
                email,
                data
            FROM epi_mailketing_logs 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse data untuk mendapatkan subject dan details
        foreach ($logs as &$log) {
            $data = json_decode($log['data'], true);
            $log['subject'] = $data['subject'] ?? '';
            $log['status'] = $data['status'] ?? 'unknown';
            $log['details'] = $data['message'] ?? '';
        }
        
        return $logs;
        
    } catch (Exception $e) {
        return [];
    }
}

/**
 * HTTP request helper function
 */
function epic_http_request($url, $method = 'POST', $data = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'EPIC Hub Mailketing Integration/1.0'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'cURL Error: ' . $error
        ];
    }
    
    if ($http_code >= 400) {
        return [
            'success' => false,
            'error' => 'HTTP Error: ' . $http_code
        ];
    }
    
    return [
        'success' => true,
        'body' => $response,
        'http_code' => $http_code
    ];
}
?>