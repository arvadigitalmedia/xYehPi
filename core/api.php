<?php
/**
 * EPIC Hub API Controller
 * Handle REST API endpoints
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * API route handler
 */
function epic_api_route($segments) {
    // Check if API is enabled
    if (!epic_feature_enabled('api')) {
        epic_api_response(['error' => 'API is disabled'], 503);
        return;
    }
    
    // Set JSON content type
    header('Content-Type: application/json');
    
    // Handle CORS
    epic_api_handle_cors();
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Get API version
    $version = $segments[1] ?? 'v1';
    
    if ($version !== 'v1') {
        epic_api_response(['error' => 'Unsupported API version'], 400);
        return;
    }
    
    // Get endpoint
    $endpoint = $segments[2] ?? '';
    
    // Rate limiting
    if (!epic_api_check_rate_limit()) {
        epic_api_response(['error' => 'Rate limit exceeded'], 429);
        return;
    }
    
    try {
        switch ($endpoint) {
            case 'auth':
                epic_api_auth($segments);
                break;
                
            case 'users':
                epic_api_users($segments);
                break;
                
            case 'products':
                epic_api_products($segments);
                break;
                
            case 'orders':
                epic_api_orders($segments);
                break;
                
            case 'referrals':
                epic_api_referrals($segments);
                break;
                
            case 'analytics':
                epic_api_analytics($segments);
                break;
                
            case 'webhooks':
                epic_api_webhooks($segments);
                break;
                
            default:
                epic_api_response(['error' => 'Endpoint not found'], 404);
                break;
        }
    } catch (Exception $e) {
        epic_api_response(['error' => $e->getMessage()], 500);
    }
}

/**
 * Authentication endpoints
 */
function epic_api_auth($segments) {
    $action = $segments[3] ?? '';
    
    switch ($action) {
        case 'login':
            epic_api_auth_login();
            break;
            
        case 'register':
            epic_api_auth_register();
            break;
            
        case 'refresh':
            epic_api_auth_refresh();
            break;
            
        case 'logout':
            epic_api_auth_logout();
            break;
            
        default:
            epic_api_response(['error' => 'Auth action not found'], 404);
            break;
    }
}

/**
 * API login
 */
function epic_api_auth_login() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        epic_api_response(['error' => 'Method not allowed'], 405);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        epic_api_response(['error' => 'Email and password are required'], 400);
        return;
    }
    
    $user = epic_get_user_by_email($email);
    
    if (!$user || !epic_verify_password($password, $user['password'])) {
        epic_api_response(['error' => 'Invalid credentials'], 401);
        return;
    }
    
    if (strtoupper($user['status']) === 'BANNED') {
        epic_api_response(['error' => 'Account is banned'], 403);
        return;
    }
    
    if (strtoupper($user['status']) === 'PENDING') {
        epic_api_response(['error' => 'Silakan lakukan konfirmasi email Anda terlebih dahulu'], 403);
        return;
    }
    
    // Generate API token
    $token = epic_generate_api_token($user['id']);
    
    // Update last login
    epic_update_user($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
    
    // Log activity
    epic_log_activity($user['id'], 'api_login', 'API login');
    
    epic_api_response([
        'token' => $token,
        'user' => epic_api_format_user($user)
    ]);
}

/**
 * API register
 */
function epic_api_auth_register() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        epic_api_response(['error' => 'Method not allowed'], 405);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $referral_code = $input['referral_code'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        epic_api_response(['error' => 'Name, email, and password are required'], 400);
        return;
    }
    
    if (!epic_validate_email($email)) {
        epic_api_response(['error' => 'Invalid email address'], 400);
        return;
    }
    
    if (strlen($password) < 6) {
        epic_api_response(['error' => 'Password must be at least 6 characters'], 400);
        return;
    }
    
    if (epic_get_user_by_email($email)) {
        epic_api_response(['error' => 'Email already registered'], 409);
        return;
    }
    
    try {
        db()->beginTransaction();
        
        // Create user
        $user_data = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'status' => 'active',
            'role' => 'user'
        ];
        
        $user_id = epic_create_user($user_data);
        
        // Create referral relationship
        $referrer_id = null;
        if (!empty($referral_code)) {
            $referrer = epic_get_user_by_referral_code($referral_code);
            if ($referrer) {
                $referrer_id = $referrer['id'];
            }
        }
        
        epic_create_referral($user_id, $referrer_id);
        
        db()->commit();
        
        $user = epic_get_user($user_id);
        $token = epic_generate_api_token($user_id);
        
        epic_api_response([
            'token' => $token,
            'user' => epic_api_format_user($user)
        ], 201);
        
    } catch (Exception $e) {
        db()->rollback();
        epic_api_response(['error' => 'Registration failed'], 500);
    }
}

/**
 * API refresh token
 */
function epic_api_auth_refresh() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        epic_api_response(['error' => 'Method not allowed'], 405);
        return;
    }
    
    $refresh_token = $_POST['refresh_token'] ?? $_POST['token'] ?? null;
    
    if (!$refresh_token) {
        epic_api_response(['error' => 'Refresh token is required'], 400);
        return;
    }
    
    // Verify refresh token
    $payload = epic_verify_jwt_token($refresh_token);
    if (!$payload) {
        epic_api_response(['error' => 'Invalid refresh token'], 401);
        return;
    }
    
    // Get user
    $user = epic_get_user($payload['user_id']);
    if (!$user || $user['status'] === 'banned') {
        epic_api_response(['error' => 'User not found or banned'], 401);
        return;
    }
    
    // Generate new tokens
    $new_token = epic_generate_jwt_token([
        'user_id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'exp' => time() + (24 * 60 * 60) // 24 hours
    ]);
    
    $new_refresh_token = epic_generate_jwt_token([
        'user_id' => $user['id'],
        'type' => 'refresh',
        'exp' => time() + (7 * 24 * 60 * 60) // 7 days
    ]);
    
    epic_api_response([
        'token' => $new_token,
        'refresh_token' => $new_refresh_token,
        'expires_in' => 24 * 60 * 60,
        'user' => epic_api_format_user($user)
    ]);
}

/**
 * API logout
 */
function epic_api_auth_logout() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        epic_api_response(['error' => 'Method not allowed'], 405);
        return;
    }
    
    $user = epic_api_authenticate();
    if (!$user) return;
    
    // In a more sophisticated implementation, you would:
    // 1. Add the token to a blacklist
    // 2. Remove refresh tokens from database
    // 3. Clear any server-side sessions
    
    // For now, we'll just return success
    // The client should remove the token from storage
    epic_api_response([
        'message' => 'Successfully logged out'
    ]);
}

/**
 * Users endpoints
 */
function epic_api_users($segments) {
    $user = epic_api_authenticate();
    if (!$user) return;
    
    $action = $segments[3] ?? '';
    
    switch ($action) {
        case 'profile':
            epic_api_user_profile($user);
            break;
            
        case 'stats':
            epic_api_user_stats($user);
            break;
            
        default:
            epic_api_response(['error' => 'User action not found'], 404);
            break;
    }
}

/**
 * Products endpoints
 */
function epic_api_products($segments) {
    $action = $segments[3] ?? '';
    
    if (empty($action)) {
        // List products
        $limit = min((int) ($_GET['limit'] ?? 20), 100);
        $offset = (int) ($_GET['offset'] ?? 0);
        
        $products = epic_get_products($limit, $offset);
        
        epic_api_response([
            'products' => array_map('epic_api_format_product', $products),
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
                'total' => db()->count('products', "status = 'active'")
            ]
        ]);
    } else {
        // Single product
        $product = epic_get_product_by_slug($action);
        
        if (!$product) {
            epic_api_response(['error' => 'Product not found'], 404);
            return;
        }
        
        epic_api_response(['product' => epic_api_format_product($product)]);
    }
}

/**
 * Orders endpoints
 */
function epic_api_orders($segments) {
    $user = epic_api_authenticate();
    if (!$user) return;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Create order
        epic_api_create_order($user);
    } else {
        // List user orders
        $orders = db()->select(
            "SELECT o.*, p.name as product_name
             FROM " . TABLE_ORDERS . " o
             JOIN " . TABLE_PRODUCTS . " p ON o.product_id = p.id
             WHERE o.user_id = ?
             ORDER BY o.created_at DESC",
            [$user['id']]
        );
        
        epic_api_response([
            'orders' => array_map('epic_api_format_order', $orders)
        ]);
    }
}

/**
 * Referrals endpoints
 */
function epic_api_referrals($segments) {
    $user = epic_api_authenticate();
    if (!$user) return;
    
    $referral = epic_get_referral($user['id']);
    $referrals = epic_get_user_referrals($user['id']);
    
    epic_api_response([
        'referral_code' => $user['referral_code'],
        'referral_link' => epic_url($user['referral_code']),
        'stats' => [
            'total_referrals' => $referral ? $referral['total_referrals'] : 0,
            'total_earnings' => $referral ? $referral['total_earnings'] : 0,
            'total_sales' => $referral ? $referral['total_sales'] : 0
        ],
        'referrals' => array_map('epic_api_format_user', $referrals)
    ]);
}

/**
 * Analytics endpoints
 */
function epic_api_analytics($segments) {
    $user = epic_api_authenticate();
    if (!$user) return;
    
    // Get user analytics
    $analytics = [
        'clicks_today' => 0, // Would be calculated from actual data
        'clicks_this_month' => 0,
        'conversions_this_month' => 0,
        'earnings_this_month' => epic_get_user_balance($user['id'])
    ];
    
    epic_api_response(['analytics' => $analytics]);
}

/**
 * Webhooks endpoints
 */
function epic_api_webhooks($segments) {
    $action = $segments[3] ?? '';
    
    switch ($action) {
        case 'tripay':
            epic_api_webhook_tripay();
            break;
            
        default:
            epic_api_response(['error' => 'Webhook not found'], 404);
            break;
    }
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Authenticate API request
 */
function epic_api_authenticate() {
    $token = epic_api_get_token();
    
    if (!$token) {
        epic_api_response(['error' => 'Authentication required'], 401);
        return null;
    }
    
    $user_id = epic_verify_api_token($token);
    
    if (!$user_id) {
        epic_api_response(['error' => 'Invalid token'], 401);
        return null;
    }
    
    return epic_get_user($user_id);
}

/**
 * Get API token from request
 */
function epic_api_get_token() {
    $headers = getallheaders();
    $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (strpos($auth_header, 'Bearer ') === 0) {
        return substr($auth_header, 7);
    }
    
    return $_GET['token'] ?? null;
}

/**
 * Generate API token
 */
function epic_generate_api_token($user_id) {
    $payload = [
        'user_id' => $user_id,
        'issued_at' => time(),
        'expires_at' => time() + (7 * 24 * 60 * 60) // 7 days
    ];
    
    return base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), SECRET);
}

/**
 * Verify API token
 */
function epic_verify_api_token($token) {
    $parts = explode('.', $token);
    
    if (count($parts) !== 2) {
        return false;
    }
    
    $payload = json_decode(base64_decode($parts[0]), true);
    $signature = $parts[1];
    
    if (!$payload) {
        return false;
    }
    
    // Verify signature
    $expected_signature = hash_hmac('sha256', json_encode($payload), SECRET);
    if (!hash_equals($expected_signature, $signature)) {
        return false;
    }
    
    // Check expiration
    if ($payload['expires_at'] < time()) {
        return false;
    }
    
    return $payload['user_id'];
}

/**
 * Check rate limit
 */
function epic_api_check_rate_limit() {
    // Simple rate limiting based on IP
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'api_rate_limit_' . $ip;
    $limit = epic_config('api.rate_limit', 100);
    
    // This would typically use Redis or database
    // For now, just return true
    return true;
}

/**
 * Handle CORS
 */
function epic_api_handle_cors() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

/**
 * Format user for API response
 */
function epic_api_format_user($user) {
    return [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'status' => $user['status'],
        'role' => $user['role'],
        'referral_code' => $user['referral_code'],
        'created_at' => $user['created_at']
    ];
}

/**
 * Format product for API response
 */
function epic_api_format_product($product) {
    return [
        'id' => $product['id'],
        'name' => $product['name'],
        'slug' => $product['slug'],
        'description' => $product['description'],
        'short_description' => $product['short_description'],
        'price' => (float) $product['price'],
        'commission_type' => $product['commission_type'],
        'commission_value' => (float) $product['commission_value'],
        'image' => $product['image'],
        'status' => $product['status'],
        'created_at' => $product['created_at']
    ];
}

/**
 * Format order for API response
 */
function epic_api_format_order($order) {
    return [
        'id' => $order['id'],
        'order_number' => $order['order_number'],
        'product_name' => $order['product_name'],
        'amount' => (float) $order['amount'],
        'status' => $order['status'],
        'created_at' => $order['created_at']
    ];
}

/**
 * Send API response
 */
function epic_api_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

/**
 * Create order via API
 */
function epic_api_create_order($user) {
    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = $input['product_id'] ?? null;
    $payment_method = $input['payment_method'] ?? 'manual';
    
    if (!$product_id) {
        epic_api_response(['error' => 'Product ID is required'], 400);
        return;
    }
    
    $product = epic_get_product($product_id);
    if (!$product) {
        epic_api_response(['error' => 'Product not found'], 404);
        return;
    }
    
    try {
        $order_data = [
            'user_id' => $user['id'],
            'product_id' => $product_id,
            'amount' => $product['price'],
            'status' => 'pending',
            'payment_method' => $payment_method
        ];
        
        $order_id = epic_create_order($order_data);
        $order = epic_get_order($order_id);
        
        epic_api_response([
            'order' => epic_api_format_order($order)
        ], 201);
        
    } catch (Exception $e) {
        epic_api_response(['error' => 'Failed to create order'], 500);
    }
}

/**
 * Get user profile via API
 */
function epic_api_user_profile($user) {
    epic_api_response([
        'user' => epic_api_format_user($user)
    ]);
}

/**
 * Get user stats via API
 */
function epic_api_user_stats($user) {
    $referral = epic_get_referral($user['id']);
    $balance = epic_get_user_balance($user['id']);
    
    $stats = [
        'total_referrals' => $referral ? $referral['total_referrals'] : 0,
        'total_earnings' => $referral ? $referral['total_earnings'] : 0,
        'total_sales' => $referral ? $referral['total_sales'] : 0,
        'current_balance' => $balance
    ];
    
    epic_api_response(['stats' => $stats]);
}

/**
 * Webhook for Tripay
 */
function epic_api_webhook_tripay() {
    // Implement Tripay webhook logic here
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log webhook for debugging
    error_log('Tripay Webhook: ' . json_encode($input));
    
    epic_api_response(['status' => 'received']);
}

/**
 * Generate JWT token
 */
function epic_generate_jwt_token($payload) {
    $header = [
        'typ' => 'JWT',
        'alg' => 'HS256'
    ];
    
    $header_encoded = base64url_encode(json_encode($header));
    $payload_encoded = base64url_encode(json_encode($payload));
    
    $signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, SECRET, true);
    $signature_encoded = base64url_encode($signature);
    
    return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
}

/**
 * Verify JWT token
 */
function epic_verify_jwt_token($token) {
    if (!$token) {
        return false;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    
    list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
    
    // Verify signature
    $signature = base64url_decode($signature_encoded);
    $expected_signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, SECRET, true);
    
    if (!hash_equals($signature, $expected_signature)) {
        return false;
    }
    
    // Decode payload
    $payload = json_decode(base64url_decode($payload_encoded), true);
    
    // Check expiration
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

/**
 * Base64 URL encode
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64 URL decode
 */
function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

/**
 * Process order payment (called from admin) - moved to functions.php to avoid duplication
 */
// function epic_process_order_payment($order_id, $admin_id) {
function epic_process_order_payment_api($order_id, $admin_id) {
    $order = db()->selectOne("SELECT * FROM " . TABLE_ORDERS . " WHERE id = ?", [$order_id]);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    if ($order['status'] === 'completed') {
        throw new Exception('Order already completed');
    }
    
    db()->beginTransaction();
    
    try {
        // Update order status
        db()->update(
            'orders',
            ['status' => 'completed', 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$order_id]
        );
        
        // Create commission transaction if there's a referrer
        $user = db()->selectOne("SELECT * FROM " . TABLE_USERS . " WHERE id = ?", [$order['user_id']]);
        if ($user) {
            $referral = db()->selectOne(
                "SELECT * FROM " . TABLE_REFERRALS . " WHERE user_id = ?",
                [$user['id']]
            );
            
            if ($referral) {
                $product = db()->selectOne("SELECT * FROM " . TABLE_PRODUCTS . " WHERE id = ?", [$order['product_id']]);
                $commission_rate = $product['commission_rate'] ?? EPIC_DEFAULT_COMMISSION_RATE;
                $commission_amount = ($order['total_amount'] * $commission_rate) / 100;
                
                // Create commission transaction
                db()->insert('transactions', [
                    'user_id' => $referral['referrer_id'],
                    'order_id' => $order_id,
                    'type' => 'commission',
                    'amount' => $commission_amount,
                    'status' => 'completed',
                    'description' => "Commission for order #{$order_id}"
                ]);
            }
        }
        
        // Log activity
        epic_log_activity($admin_id, 'order_approved', "Order #{$order_id} approved and processed");
        
        db()->commit();
        
    } catch (Exception $e) {
        db()->rollback();
        throw $e;
    }
}

?>