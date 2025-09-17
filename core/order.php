<?php
/**
 * EPIC Hub Order Controller
 * Handle order processing and payment
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Process order for a product
 */
function epic_order_process($product) {
    $error = null;
    $success = null;
    $order = null;
    
    // Check if user is logged in
    $user = epic_current_user();
    $referrer_id = $_SESSION['epic_referrer'] ?? null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle user registration/login during order
        if (!$user) {
            $user = epic_handle_order_user_creation();
            if (!$user) {
                $error = 'Failed to create user account.';
            }
        }
        
        if ($user && !$error) {
            try {
                db()->beginTransaction();
                
                // Calculate commission
                $commission_amount = 0;
                if ($referrer_id && $product['commission_type'] === 'percentage') {
                    $commission_amount = ($product['price'] * $product['commission_value']) / 100;
                } elseif ($referrer_id && $product['commission_type'] === 'fixed') {
                    $commission_amount = $product['commission_value'];
                }
                
                // Create order
                $order_data = [
                    'user_id' => $user['id'],
                    'referrer_id' => $referrer_id,
                    'product_id' => $product['id'],
                    'amount' => $product['price'],
                    'commission_amount' => $commission_amount,
                    'status' => 'pending'
                ];
                
                $order_id = epic_create_order($order_data);
                $order = epic_get_order($order_id);
                
                // Log activity
                epic_log_activity($user['id'], 'order_created', 'Order created for ' . $product['name']);
                
                // Send notification
                epic_send_notification(
                    $user['id'],
                    'order_created',
                    'Order Created',
                    'Your order for ' . $product['name'] . ' has been created.',
                    ['email', 'dashboard']
                );
                
                db()->commit();
                
                // Redirect to payment
                epic_redirect(epic_url('order/payment/' . $order['order_number']));
                
            } catch (Exception $e) {
                db()->rollback();
                $error = 'Failed to create order. Please try again.';
            }
        }
    }
    
    $data = [
        'page_title' => 'Order ' . $product['name'] . ' - ' . epic_setting('site_name'),
        'product' => $product,
        'user' => $user,
        'error' => $error,
        'success' => $success,
        'referrer_id' => $referrer_id
    ];
    
    epic_render_template('order/form', $data);
}

/**
 * Handle user creation during order process
 */
function epic_handle_order_user_creation() {
    $name = epic_sanitize($_POST['name'] ?? '');
    $email = epic_sanitize($_POST['email'] ?? '');
    $phone = epic_sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        return null;
    }
    
    if (!epic_validate_email($email)) {
        return null;
    }
    
    if (epic_get_user_by_email($email)) {
        // User exists, try to login
        $existing_user = epic_get_user_by_email($email);
        if (epic_verify_password($password, $existing_user['password'])) {
            epic_login_user($existing_user['id']);
            return $existing_user;
        }
        return null;
    }
    
    // Create new user
    try {
        $user_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'status' => 'active',
            'role' => 'user'
        ];
        
        $user_id = epic_create_user($user_data);
        
        // Create referral relationship
        $referrer_id = $_SESSION['epic_referrer'] ?? null;
        epic_create_referral($user_id, $referrer_id);
        
        // Login user
        epic_login_user($user_id);
        
        return epic_get_user($user_id);
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Process payment for order
 */
function epic_process_payment($order_number) {
    $order = epic_get_order_by_number($order_number);
    
    if (!$order) {
        epic_route_404();
        return;
    }
    
    $user = epic_current_user();
    if (!$user || $user['id'] !== $order['user_id']) {
        epic_route_403();
        return;
    }
    
    $error = null;
    $success = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payment_method = $_POST['payment_method'] ?? '';
        
        switch ($payment_method) {
            case 'manual':
                epic_process_manual_payment($order);
                break;
                
            case 'tripay':
                epic_process_tripay_payment($order);
                break;
                
            default:
                $error = 'Please select a payment method.';
                break;
        }
    }
    
    // Get available payment methods
    $payment_methods = epic_get_payment_methods();
    
    $data = [
        'page_title' => 'Payment - Order #' . $order['order_number'],
        'order' => $order,
        'payment_methods' => $payment_methods,
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('order/payment', $data);
}

/**
 * Process manual payment
 */
function epic_process_manual_payment($order) {
    try {
        // Update order with manual payment reference
        epic_update_order_status($order['id'], 'pending');
        
        db()->update('orders', 
            ['payment_method' => 'manual', 'payment_reference' => 'MANUAL-' . time()],
            'id = ?', 
            [$order['id']]
        );
        
        // Send notification to admin
        $admin_users = db()->select("SELECT * FROM " . TABLE_USERS . " WHERE role IN ('admin', 'super_admin')");
        foreach ($admin_users as $admin) {
            epic_send_notification(
                $admin['id'],
                'manual_payment',
                'Manual Payment Received',
                'Order #' . $order['order_number'] . ' requires manual payment verification.',
                ['email', 'dashboard']
            );
        }
        
        epic_redirect(epic_url('order/success/' . $order['order_number']));
        
    } catch (Exception $e) {
        throw new Exception('Manual payment processing failed');
    }
}

/**
 * Process Tripay payment
 */
function epic_process_tripay_payment($order) {
    if (!epic_setting('tripay_enabled', false)) {
        throw new Exception('Tripay payment is not enabled');
    }
    
    // Tripay integration would go here
    // For now, redirect to manual payment
    epic_process_manual_payment($order);
}

/**
 * Get available payment methods
 */
function epic_get_payment_methods() {
    $methods = [
        'manual' => [
            'name' => 'Manual Transfer',
            'description' => 'Transfer to our bank account',
            'enabled' => true
        ]
    ];
    
    if (epic_setting('tripay_enabled', false)) {
        $methods['tripay'] = [
            'name' => 'Tripay Gateway',
            'description' => 'Pay with various methods via Tripay',
            'enabled' => true
        ];
    }
    
    return array_filter($methods, function($method) {
        return $method['enabled'];
    });
}

/**
 * Order success page
 */
function epic_order_success($order_number) {
    $order = epic_get_order_by_number($order_number);
    
    if (!$order) {
        epic_route_404();
        return;
    }
    
    $user = epic_current_user();
    if (!$user || $user['id'] !== $order['user_id']) {
        epic_route_403();
        return;
    }
    
    $data = [
        'page_title' => 'Order Success - ' . epic_setting('site_name'),
        'order' => $order
    ];
    
    epic_render_template('order/success', $data);
}

/**
 * Order invoice page
 */
function epic_order_invoice($order_number) {
    $order = epic_get_order_by_number($order_number);
    
    if (!$order) {
        epic_route_404();
        return;
    }
    
    $user = epic_current_user();
    if (!$user || $user['id'] !== $order['user_id']) {
        epic_route_403();
        return;
    }
    
    $data = [
        'page_title' => 'Invoice #' . $order['order_number'],
        'order' => $order
    ];
    
    epic_render_template('order/invoice', $data);
}

/**
 * Get order by number
 */
function epic_get_order_by_number($order_number) {
    return db()->selectOne(
        "SELECT o.*, p.name as product_name, p.price as product_price, u.name as user_name, u.email as user_email
         FROM " . TABLE_ORDERS . " o
         JOIN " . TABLE_PRODUCTS . " p ON o.product_id = p.id
         JOIN " . TABLE_USERS . " u ON o.user_id = u.id
         WHERE o.order_number = ?",
        [$order_number]
    );
}

/**
 * Update order status
 */
function epic_update_order_status($order_id, $status) {
    return db()->update('orders', 
        ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')],
        'id = ?', 
        [$order_id]
    );
}

?>