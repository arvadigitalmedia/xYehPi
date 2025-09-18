<?php
/**
 * EPIC Hub Admin Controller
 * Handle admin panel functionality
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Ensure required functions are available
if (!function_exists('epic_route_403')) {
    function epic_route_403() {
        http_response_code(403);
        echo '<h1>403 - Access Forbidden</h1>';
        echo '<p>You do not have permission to access this page.</p>';
        exit;
    }
}

if (!function_exists('epic_route_404')) {
    function epic_route_404() {
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p>The requested page could not be found.</p>';
        exit;
    }
}

/**
 * Admin route handler
 */
function epic_admin_route($segments) {
    $user = epic_current_user();
    
    if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
        epic_route_403();
        return;
    }
    
    $page = $segments[1] ?? 'dashboard';
    
    switch ($page) {
        case '':
        case 'dashboard':
            epic_admin_dashboard();
            break;
            
        case 'users':
            epic_admin_users($segments);
            break;
            
        case 'products':
            epic_admin_products($segments);
            break;
            
        case 'orders':
            epic_admin_orders($segments);
            break;
            
        case 'transactions':
            epic_admin_transactions($segments);
            break;
            
        case 'articles':
            epic_admin_articles($segments);
            break;
            
        case 'categories':
            epic_admin_categories($segments);
            break;
            
        case 'reports':
            epic_admin_reports($segments);
            break;
            
        case 'maintenance':
            epic_admin_maintenance();
            break;
            
        case 'settings':
            epic_admin_settings($segments);
            break;
            
        case 'analytics':
            epic_admin_analytics();
            break;
            
        case 'logs':
            epic_admin_logs();
            break;
            
        case 'profile':
            epic_admin_profile($segments);
            break;
            
        case 'edit-profile':
            epic_admin_edit_profile();
            break;
            
        case 'lms-products':
            epic_admin_lms_products($segments);
            break;
            
        case 'manage':
            epic_admin_manage($segments);
            break;
            
        case 'member':
            epic_admin_member($segments);
            break;
            
        case 'order':
            epic_admin_order($segments);
            break;
            
        case 'product':
            epic_admin_product($segments);
            break;
            
        case 'landing-page':
            epic_admin_landing_page($segments);
            break;
            
        case 'landing-page-manager':
            epic_admin_landing_page_manager($segments);
            break;
            
        case 'payout':
            epic_admin_payout($segments);
            break;
            
        case 'finance':
            epic_admin_finance($segments);
            break;
            
        case 'update-price':
            epic_admin_update_price($segments);
            break;
            
        case 'blog':
            epic_admin_blog($segments);
            break;
            
        case 'all-products':
            epic_admin_all_products($segments);
            break;
            
        case 'ajax':
            epic_admin_ajax($segments);
            break;
            
        case 'integrasi':
            epic_admin_integrasi($segments);
            break;
            
        case 'zoom-integration':
            epic_admin_integrasi_zoom();
            break;
            
        case 'zoom-add-event':
            epic_admin_zoom_add_event();
            break;
            
        case 'event-scheduling':
            epic_admin_event_scheduling();
            break;
            
        case 'event-scheduling-add':
            epic_admin_event_scheduling_add();
            break;
            
        case 'member-area':
            epic_admin_member_area($segments);
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Admin dashboard using new layout system
 */
function epic_admin_dashboard() {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/dashboard.php';
}

/**
 * Admin users management
 */
function epic_admin_users($segments) {
    $action = $segments[2] ?? 'list';
    
    switch ($action) {
        case 'list':
            epic_admin_users_list();
            break;
            
        case 'add':
            epic_admin_users_add();
            break;
            
        case 'edit':
            $user_id = $segments[3] ?? null;
            epic_admin_users_edit($user_id);
            break;
            
        case 'delete':
            $user_id = $segments[3] ?? null;
            epic_admin_users_delete($user_id);
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Admin users list
 */
function epic_admin_users_list() {
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    
    // Build query
    $where = '1=1';
    $params = [];
    
    if (!empty($search)) {
        $where .= " AND (name LIKE ? OR email LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    if (!empty($status)) {
        $where .= " AND status = ?";
        $params[] = $status;
    }
    
    // Get users
    $users = db()->select(
        "SELECT u.*, r.total_referrals, r.total_earnings
         FROM " . TABLE_USERS . " u
         LEFT JOIN " . TABLE_REFERRALS . " r ON u.id = r.user_id
         WHERE {$where}
         ORDER BY u.created_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );
    
    // Get total count
    $total = db()->selectValue(
        "SELECT COUNT(*) FROM " . TABLE_USERS . " WHERE {$where}",
        $params
    );
    
    $total_pages = ceil($total / $limit);
    
    $data = [
        'page_title' => 'Users Management - Admin',
        'users' => $users,
        'search' => $search,
        'status' => $status,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ];
    
    epic_render_template('admin/users/list', $data);
}

/**
 * Add new user
 */
function epic_admin_users_add() {
    $error = null;
    $success = null;
    
    if (epic_is_post()) {
        $name = epic_post('name');
        $email = epic_post('email');
        $password = epic_post('password');
        $role = epic_post('role', 'user');
        $status = epic_post('status', 'active');
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } elseif (epic_get_user_by_email($email)) {
            $error = 'Email already exists.';
        } else {
            // Create user
            $user_data = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'status' => $status,
                'email_verified_at' => date('Y-m-d H:i:s')
            ];
            
            $user_id = epic_create_user($user_data);
            
            if ($user_id) {
                epic_log_activity(epic_current_user()['id'], 'user_created', "User {$name} created");
                epic_flash('success', 'User created successfully.');
                epic_redirect(epic_url('admin/users'));
                return;
            } else {
                $error = 'Failed to create user.';
            }
        }
    }
    
    $data = [
        'page_title' => 'Add User - Admin',
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('admin/users/add', $data);
}

/**
 * Edit user
 */
function epic_admin_users_edit($user_id) {
    if (!$user_id) {
        epic_redirect(epic_url('admin/users'));
        return;
    }
    
    $user = epic_get_user($user_id);
    if (!$user) {
        epic_redirect(epic_url('admin/users'));
        return;
    }
    
    $error = null;
    $success = null;
    
    if (epic_is_post()) {
        $name = epic_post('name');
        $email = epic_post('email');
        $role = epic_post('role');
        $status = epic_post('status');
        $password = epic_post('password');
        
        // Validation
        if (empty($name) || empty($email)) {
            $error = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } else {
            // Check if email exists for other users
            $existing_user = epic_get_user_by_email($email);
            if ($existing_user && $existing_user['id'] != $user_id) {
                $error = 'Email already exists.';
            } else {
                // Update user
                $update_data = [
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'status' => $status
                ];
                
                if (!empty($password)) {
                    $update_data['password'] = $password;
                }
                
                $updated = epic_update_user($user_id, $update_data);
                
                if ($updated) {
                    epic_log_activity(epic_current_user()['id'], 'user_updated', "User {$name} updated");
                    epic_flash('success', 'User updated successfully.');
                    epic_redirect(epic_url('admin/users'));
                    return;
                } else {
                    $error = 'Failed to update user.';
                }
            }
        }
    }
    
    $data = [
        'page_title' => 'Edit User - Admin',
        'user' => $user,
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('admin/users/edit', $data);
}

/**
 * Delete user
 */
function epic_admin_users_delete($user_id) {
    if (!$user_id) {
        epic_redirect(epic_url('admin/users'));
        return;
    }
    
    $user = epic_get_user($user_id);
    if (!$user) {
        epic_redirect(epic_url('admin/users'));
        return;
    }
    
    // Prevent deleting current user
    $current_user = epic_current_user();
    if ($current_user['id'] == $user_id) {
        epic_flash('error', 'Cannot delete your own account.');
        epic_redirect(epic_url('admin/users'));
        return;
    }
    
    // Delete user (soft delete by changing status)
    $deleted = db()->update(
        'users',
        ['status' => 'deleted', 'updated_at' => date('Y-m-d H:i:s')],
        'id = ?',
        [$user_id]
    );
    
    if ($deleted) {
        epic_log_activity($current_user['id'], 'user_deleted', "User {$user['name']} deleted");
        epic_flash('success', 'User deleted successfully.');
    } else {
        epic_flash('error', 'Failed to delete user.');
    }
    
    epic_redirect(epic_url('admin/users'));
}

/**
 * Admin products management
 */
function epic_admin_products($segments) {
    $action = $segments[2] ?? 'list';
    
    switch ($action) {
        case 'list':
            epic_admin_products_list();
            break;
            
        case 'add':
            epic_admin_products_add();
            break;
            
        case 'edit':
            $product_id = $segments[3] ?? null;
            epic_admin_products_edit($product_id);
            break;
            
        case 'delete':
            $product_id = $segments[3] ?? null;
            epic_admin_products_delete($product_id);
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Admin products list
 */
function epic_admin_products_list() {
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    
    // Build query
    $where = '1=1';
    $params = [];
    
    if (!empty($search)) {
        $where .= " AND (name LIKE ? OR description LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    if (!empty($status)) {
        $where .= " AND status = ?";
        $params[] = $status;
    }
    
    // Get products
    $products = db()->select(
        "SELECT p.*, 
         (SELECT COUNT(*) FROM " . TABLE_ORDERS . " WHERE product_id = p.id AND status = 'paid') as sales_count,
         (SELECT SUM(amount) FROM " . TABLE_ORDERS . " WHERE product_id = p.id AND status = 'paid') as total_revenue
         FROM " . TABLE_PRODUCTS . " p
         WHERE {$where}
         ORDER BY p.created_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );
    
    // Get total count
    $total = db()->selectValue(
        "SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " WHERE {$where}",
        $params
    );
    
    $total_pages = ceil($total / $limit);
    
    $data = [
        'page_title' => 'Products Management - Admin',
        'products' => $products,
        'search' => $search,
        'status' => $status,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ];
    
    epic_render_template('admin/products/list', $data);
}

/**
 * Add new product
 */
function epic_admin_products_add() {
    $error = null;
    $success = null;
    
    if (epic_is_post()) {
        $name = epic_post('name');
        $description = epic_post('description');
        $price = epic_post('price');
        $commission_rate = epic_post('commission_rate', 10);
        $status = epic_post('status', 'active');
        
        // Validation
        if (empty($name) || empty($price)) {
            $error = 'Name and price are required.';
        } elseif (!is_numeric($price) || $price <= 0) {
            $error = 'Price must be a valid positive number.';
        } else {
            // Create product
            $product_data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'commission_rate' => $commission_rate,
                'status' => $status
            ];
            
            $product_id = db()->insert('products', $product_data);
            
            if ($product_id) {
                epic_log_activity(epic_current_user()['id'], 'product_created', "Product {$name} created");
                epic_flash('success', 'Product created successfully.');
                epic_redirect(epic_url('admin/products'));
                return;
            } else {
                $error = 'Failed to create product.';
            }
        }
    }
    
    $data = [
        'page_title' => 'Add Product - Admin',
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('admin/products/add', $data);
}

/**
 * Edit product
 */
function epic_admin_products_edit($product_id) {
    if (!$product_id) {
        epic_redirect(epic_url('admin/products'));
        return;
    }
    
    $product = db()->selectOne("SELECT * FROM " . TABLE_PRODUCTS . " WHERE id = ?", [$product_id]);
    if (!$product) {
        epic_redirect(epic_url('admin/products'));
        return;
    }
    
    $error = null;
    $success = null;
    
    if (epic_is_post()) {
        $name = epic_post('name');
        $description = epic_post('description');
        $price = epic_post('price');
        $commission_rate = epic_post('commission_rate');
        $status = epic_post('status');
        
        // Validation
        if (empty($name) || empty($price)) {
            $error = 'Name and price are required.';
        } elseif (!is_numeric($price) || $price <= 0) {
            $error = 'Price must be a valid positive number.';
        } else {
            // Update product
            $update_data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'commission_rate' => $commission_rate,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $updated = db()->update('products', $update_data, 'id = ?', [$product_id]);
            
            if ($updated) {
                epic_log_activity(epic_current_user()['id'], 'product_updated', "Product {$name} updated");
                epic_flash('success', 'Product updated successfully.');
                epic_redirect(epic_url('admin/products'));
                return;
            } else {
                $error = 'Failed to update product.';
            }
        }
    }
    
    $data = [
        'page_title' => 'Edit Product - Admin',
        'product' => $product,
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('admin/products/edit', $data);
}

/**
 * Delete product
 */
function epic_admin_products_delete($product_id) {
    if (!$product_id) {
        epic_redirect(epic_url('admin/products'));
        return;
    }
    
    $product = db()->selectOne("SELECT * FROM " . TABLE_PRODUCTS . " WHERE id = ?", [$product_id]);
    if (!$product) {
        epic_redirect(epic_url('admin/products'));
        return;
    }
    
    // Check if product has orders
    $has_orders = db()->selectValue(
        "SELECT COUNT(*) FROM " . TABLE_ORDERS . " WHERE product_id = ?",
        [$product_id]
    );
    
    if ($has_orders > 0) {
        // Soft delete by changing status
        $deleted = db()->update(
            'products',
            ['status' => 'deleted', 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$product_id]
        );
    } else {
        // Hard delete if no orders
        $deleted = db()->delete('products', 'id = ?', [$product_id]);
    }
    
    if ($deleted) {
        epic_log_activity(epic_current_user()['id'], 'product_deleted', "Product {$product['name']} deleted");
        epic_flash('success', 'Product deleted successfully.');
    } else {
        epic_flash('error', 'Failed to delete product.');
    }
    
    epic_redirect(epic_url('admin/products'));
}

/**
 * Admin orders management
 */
function epic_admin_orders($segments) {
    $action = $segments[2] ?? 'list';
    
    switch ($action) {
        case 'list':
            epic_admin_orders_list();
            break;
            
        case 'view':
            $order_id = $segments[3] ?? null;
            epic_admin_orders_view($order_id);
            break;
            
        case 'approve':
            $order_id = $segments[3] ?? null;
            epic_admin_orders_approve($order_id);
            break;
            
        case 'reject':
            $order_id = $segments[3] ?? null;
            epic_admin_orders_reject($order_id);
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Admin orders list
 */
function epic_admin_orders_list() {
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $status = $_GET['status'] ?? '';
    
    // Build query
    $where = '1=1';
    $params = [];
    
    if (!empty($status)) {
        $where .= " AND o.status = ?";
        $params[] = $status;
    }
    
    // Get orders
    $orders = db()->select(
        "SELECT o.*, u.name as user_name, u.email as user_email, p.name as product_name
         FROM " . TABLE_ORDERS . " o
         JOIN " . TABLE_USERS . " u ON o.user_id = u.id
         JOIN " . TABLE_PRODUCTS . " p ON o.product_id = p.id
         WHERE {$where}
         ORDER BY o.created_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );
    
    // Get total count
    $total = db()->selectValue(
        "SELECT COUNT(*) FROM " . TABLE_ORDERS . " o WHERE {$where}",
        $params
    );
    
    $total_pages = ceil($total / $limit);
    
    $data = [
        'page_title' => 'Orders Management - Admin',
        'orders' => $orders,
        'status' => $status,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ];
    
    epic_render_template('admin/orders/list', $data);
}

/**
 * Approve order
 */
function epic_admin_orders_approve($order_id) {
    if (!$order_id) {
        epic_redirect(epic_url('admin/orders'));
        return;
    }
    
    $user = epic_current_user();
    
    try {
        epic_admin_process_order_payment($order_id, $user['id']);
        epic_flash('success', 'Order approved successfully.');
    } catch (Exception $e) {
        epic_flash('error', 'Failed to approve order: ' . $e->getMessage());
    }
    
    epic_redirect(epic_url('admin/orders'));
}

/**
 * View specific order details
 */
function epic_admin_orders_view($order_id) {
    if (!$order_id) {
        epic_redirect(epic_url('admin/orders'));
        return;
    }
    
    // Get order details with user and product information
    $order = db()->selectOne(
        "SELECT o.*, u.name as user_name, u.email as user_email, 
                p.name as product_name, p.price as product_price
         FROM " . TABLE_ORDERS . " o
         LEFT JOIN " . TABLE_USERS . " u ON o.user_id = u.id
         LEFT JOIN " . TABLE_PRODUCTS . " p ON o.product_id = p.id
         WHERE o.id = ?",
        [$order_id]
    );
    
    if (!$order) {
        epic_redirect(epic_url('admin/orders'));
        return;
    }
    
    // Get order transactions
    $transactions = db()->select(
        "SELECT * FROM " . TABLE_TRANSACTIONS . " 
         WHERE order_id = ? 
         ORDER BY created_at DESC",
        [$order_id]
    );
    
    $data = [
        'page_title' => 'Order Details - Admin',
        'order' => $order,
        'transactions' => $transactions
    ];
    
    epic_render_template('admin/orders/view', $data);
}

/**
 * Reject order
 */
function epic_admin_orders_reject($order_id) {
    if (!$order_id) {
        epic_redirect(epic_url('admin/orders'));
        return;
    }
    
    // Update order status
    $updated = db()->update(
        'orders',
        ['status' => 'rejected', 'updated_at' => date('Y-m-d H:i:s')],
        'id = ?',
        [$order_id]
    );
    
    if ($updated) {
        // Log activity
        epic_log_activity(epic_current_user()['id'], 'order_rejected', "Order #{$order_id} rejected");
        
        // Send notification to user
        $order = db()->selectOne("SELECT * FROM " . TABLE_ORDERS . " WHERE id = ?", [$order_id]);
        if ($order) {
            $user = db()->selectOne("SELECT * FROM " . TABLE_USERS . " WHERE id = ?", [$order['user_id']]);
            if ($user) {
                epic_send_order_rejection_email($user, $order);
            }
        }
        
        epic_flash('success', 'Order rejected successfully.');
    } else {
        epic_flash('error', 'Failed to reject order.');
    }
    
    epic_redirect(epic_url('admin/orders'));
}

/**
 * Admin settings
 */
function epic_admin_settings($segments) {
    $subsection = $segments[2] ?? '';
    
    switch ($subsection) {
        case 'form':
        case 'form-registrasi':
            epic_admin_settings_form();
            break;
        case 'email':
        case 'email-notification':
            epic_admin_settings_email();
            break;
        case 'whatsapp':
        case 'whatsapp-notification':
            epic_admin_settings_whatsapp();
            break;
        case 'payment':
        case 'payment-gateway':
            epic_admin_settings_payment();
            break;
        case 'general':
        case '':
        default:
            epic_admin_settings_general();
            break;
    }
}

/**
 * General Settings
 */
function epic_admin_settings_general() {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/settings.php';
}

/**
 * Form Registration Settings
 */
function epic_admin_settings_form() {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/settings-form.php';
}

/**
 * Email Notification Settings
 */
function epic_admin_settings_email() {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/settings-email.php';
}

/**
 * WhatsApp Notification Settings
 */
function epic_admin_settings_whatsapp() {
    // Use WhatsApp notification settings page with Starsender integration
    include __DIR__ . '/../themes/modern/admin/settings-whatsapp-notification.php';
}

/**
 * Payment Gateway Settings
 */
function epic_admin_settings_payment() {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/settings-payment.php';
}

/**
 * Autoresponder Email Settings
 */
function epic_admin_settings_autoresponder() {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/settings-autoresponder.php';
}

/**
 * Admin Integrasi Route Handler
 */
function epic_admin_integrasi($segments) {
    $sub_page = $segments[2] ?? '';
    
    switch ($sub_page) {
        case 'autoresponder-email':
            epic_admin_integrasi_autoresponder_email();
            break;
            
        case 'zoom-integration':
            epic_admin_integrasi_zoom();
            break;

        case 'payment-gateway':
            epic_admin_integrasi_payment_gateway();
            break;
            
        case 'api-integration':
            epic_admin_integrasi_api();
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Integrasi Autoresponder Email
 */
function epic_admin_integrasi_autoresponder_email() {
    // Use existing autoresponder settings page
    include __DIR__ . '/../themes/modern/admin/settings-autoresponder.php';
}

/**
 * Integrasi Zoom Integration
 */
function epic_admin_integrasi_zoom() {
    // Use existing zoom integration page
    include __DIR__ . '/../themes/modern/admin/zoom-integration.php';
}

/**
 * Zoom Add Event Page
 */
function epic_admin_zoom_add_event() {
    // Use zoom add event page
    include __DIR__ . '/../admin/zoom-add-event.php';
}

/**
 * Event Scheduling Management Page
 */
function epic_admin_event_scheduling() {
    // Use event scheduling page
    include __DIR__ . '/../admin/event-scheduling.php';
}

/**
 * Event Scheduling Add Page
 */
function epic_admin_event_scheduling_add() {
    // Use event scheduling add page
    include __DIR__ . '/../admin/event-scheduling-add.php';
}

/**
 * Integrasi Autoresponder WhatsApp
 */
/**
 * Integrasi Payment Gateway
 */
function epic_admin_integrasi_payment_gateway() {
    // Use existing payment settings page
    include __DIR__ . '/../themes/modern/admin/settings-payment.php';
}

/**
 * Integrasi API
 */
function epic_admin_integrasi_api() {
    // Create dedicated API integration page
    include __DIR__ . '/../themes/modern/admin/integrasi-api.php';
}

/**
 * Save form field
 */
function epic_admin_save_form_field($data) {
    $field_id = (int)($data['field_id'] ?? 0);
    $label = epic_sanitize($data['label'] ?? '');
    $field_name = epic_sanitize($data['field_name'] ?? '');
    $field_type = epic_sanitize($data['field_type'] ?? 'text');
    $placeholder = epic_sanitize($data['placeholder'] ?? '');
    $options = epic_sanitize($data['options'] ?? '');
    $required = isset($data['required']) ? 1 : 0;
    $show_in_registration = isset($data['show_in_registration']) ? 1 : 0;
    $show_in_profile = isset($data['show_in_profile']) ? 1 : 0;
    $sort_order = (int)($data['sort_order'] ?? 0);
    
    if (empty($label) || empty($field_name)) {
        throw new Exception('Label and field name are required');
    }
    
    $field_data = [
        'label' => $label,
        'field_name' => $field_name,
        'field_type' => $field_type,
        'placeholder' => $placeholder,
        'options' => $options,
        'required' => $required,
        'show_in_registration' => $show_in_registration,
        'show_in_profile' => $show_in_profile,
        'sort_order' => $sort_order,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($field_id > 0) {
        // Update existing field
        db()->update('epic_form_fields', $field_data, 'id = ?', [$field_id]);
    } else {
        // Create new field
        $field_data['created_at'] = date('Y-m-d H:i:s');
        db()->insert('epic_form_fields', $field_data);
    }
}

/**
 * Admin reports
 */
function epic_admin_reports($segments) {
    $action = $segments[1] ?? 'list';
    
    switch ($action) {
        case 'sales':
            epic_admin_reports_sales();
            break;
            
        case 'users':
            epic_admin_reports_users();
            break;
            
        case 'commissions':
            epic_admin_reports_commissions();
            break;
            
        case 'traffic':
            epic_admin_reports_traffic();
            break;
            
        default:
            epic_admin_reports_list();
            break;
    }
}

/**
 * Admin reports list
 */
function epic_admin_reports_list() {
    // Get report summary data
    $reports_data = [
        'total_sales' => db()->selectValue(
            "SELECT COALESCE(SUM(total_amount), 0) FROM " . TABLE_ORDERS . " WHERE status = 'completed'"
        ) ?: 0,
        'total_users' => db()->selectValue(
            "SELECT COUNT(*) FROM " . TABLE_USERS
        ) ?: 0,
        'total_commissions' => db()->selectValue(
            "SELECT COALESCE(SUM(amount_in), 0) FROM " . TABLE_TRANSACTIONS . " WHERE type = 'commission' AND status = 'completed'"
        ) ?: 0,
        'total_visits' => db()->selectValue(
            "SELECT COUNT(*) FROM " . db()->table('landing_visits')
        ) ?: 0,
        'monthly_sales' => epic_admin_get_monthly_sales_report(),
        'top_affiliates' => epic_admin_get_top_affiliates_report(),
        'recent_activities' => epic_admin_get_recent_activities()
    ];
    
    $data = [
        'page_title' => 'Reports - Admin',
        'reports' => $reports_data
    ];
    
    epic_render_template('admin/reports', $data);
}

/**
 * Admin sales reports
 */
function epic_admin_reports_sales() {
    $period = $_GET['period'] ?? '30';
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime("-{$period} days"));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    $sales_data = [
        'daily_sales' => epic_admin_get_daily_sales($start_date, $end_date),
        'product_sales' => epic_admin_get_product_sales($start_date, $end_date),
        'affiliate_sales' => epic_admin_get_affiliate_sales($start_date, $end_date),
        'total_revenue' => epic_admin_get_total_revenue($start_date, $end_date)
    ];
    
    $data = [
        'page_title' => 'Sales Reports - Admin',
        'sales_data' => $sales_data,
        'period' => $period,
        'start_date' => $start_date,
        'end_date' => $end_date
    ];
    
    epic_render_template('admin/reports/sales', $data);
}

/**
 * Admin users reports
 */
function epic_admin_reports_users() {
    $period = $_GET['period'] ?? '30';
    
    $users_data = [
        'user_growth' => epic_admin_get_user_growth($period),
        'user_types' => epic_admin_get_user_types_breakdown(),
        'active_users' => epic_admin_get_active_users($period),
        'user_locations' => epic_admin_get_user_locations()
    ];
    
    $data = [
        'page_title' => 'Users Reports - Admin',
        'users_data' => $users_data,
        'period' => $period
    ];
    
    epic_render_template('admin/reports/users', $data);
}

/**
 * Admin commissions reports
 */
function epic_admin_reports_commissions() {
    $period = $_GET['period'] ?? '30';
    $status = $_GET['status'] ?? 'all';
    
    $commissions_data = [
        'commission_summary' => epic_admin_get_commission_summary($period, $status),
        'top_earners' => epic_admin_get_top_commission_earners($period),
        'commission_trends' => epic_admin_get_commission_trends($period),
        'pending_payouts' => epic_admin_get_pending_payouts()
    ];
    
    $data = [
        'page_title' => 'Commissions Reports - Admin',
        'commissions_data' => $commissions_data,
        'period' => $period,
        'status' => $status
    ];
    
    epic_render_template('admin/reports/commissions', $data);
}

/**
 * Admin traffic reports
 */
function epic_admin_reports_traffic() {
    $period = $_GET['period'] ?? '30';
    
    $traffic_data = [
        'landing_page_visits' => epic_admin_get_landing_page_visits($period),
        'traffic_sources' => epic_admin_get_traffic_sources($period),
        'popular_templates' => epic_admin_get_popular_templates($period),
        'conversion_rates' => epic_admin_get_conversion_rates($period)
    ];
    
    $data = [
        'page_title' => 'Traffic Reports - Admin',
        'traffic_data' => $traffic_data,
        'period' => $period
    ];
    
    epic_render_template('admin/reports/traffic', $data);
}

/**
 * Admin analytics
 */
function epic_admin_analytics() {
    // Get analytics data
    $analytics = [
        'users_by_month' => epic_admin_get_users_by_month(),
        'orders_by_month' => epic_admin_get_orders_by_month(),
        'revenue_by_month' => epic_admin_get_revenue_by_month(),
        'top_products' => epic_admin_get_top_products(),
        'top_referrers' => epic_admin_get_top_referrers()
    ];
    
    $data = [
        'page_title' => 'Analytics - Admin',
        'analytics' => $analytics
    ];
    
    epic_render_template('admin/analytics', $data);
}

/**
 * Admin activity logs
 */
function epic_admin_logs() {
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 50;
    $offset = ($page - 1) * $limit;
    $action = $_GET['action'] ?? '';
    
    // Build query
    $where = '1=1';
    $params = [];
    
    if (!empty($action)) {
        $where .= " AND action = ?";
        $params[] = $action;
    }
    
    // Get logs
    $logs = db()->select(
        "SELECT al.*, u.name as user_name
         FROM " . TABLE_ACTIVITY_LOG . " al
         LEFT JOIN " . TABLE_USERS . " u ON al.user_id = u.id
         WHERE {$where}
         ORDER BY al.created_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );
    
    // Get total count
    $total = db()->selectValue(
        "SELECT COUNT(*) FROM " . TABLE_ACTIVITY_LOG . " WHERE {$where}",
        $params
    );
    
    $total_pages = ceil($total / $limit);
    
    $data = [
        'page_title' => 'Activity Logs - Admin',
        'logs' => $logs,
        'action' => $action,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ];
    
    epic_render_template('admin/logs', $data);
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Get users by month for analytics
 */
function epic_admin_get_users_by_month() {
    return db()->select(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
         FROM " . TABLE_USERS . "
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
         GROUP BY month
         ORDER BY month"
    );
}

/**
 * Get orders by month for analytics
 */
function epic_admin_get_orders_by_month() {
    return db()->select(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
         FROM " . TABLE_ORDERS . "
         WHERE status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
         GROUP BY month
         ORDER BY month"
    );
}

/**
 * Get revenue by month for analytics
 */
function epic_admin_get_revenue_by_month() {
    return db()->select(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue
         FROM " . TABLE_ORDERS . "
         WHERE status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
         GROUP BY month
         ORDER BY month"
    );
}

/**
 * Get top products for analytics
 */
function epic_admin_get_top_products() {
    return db()->select(
        "SELECT p.name, COUNT(o.id) as sales, SUM(o.amount) as revenue
         FROM " . TABLE_PRODUCTS . " p
         LEFT JOIN " . TABLE_ORDERS . " o ON p.id = o.product_id AND o.status = 'paid'
         GROUP BY p.id
         ORDER BY sales DESC
         LIMIT 10"
    );
}

/**
 * Get top referrers for analytics
 */
function epic_admin_get_top_referrers() {
    return db()->select(
        "SELECT u.name, r.total_referrals, r.total_earnings
         FROM " . TABLE_USERS . " u
         JOIN " . TABLE_REFERRALS . " r ON u.id = r.user_id
         WHERE r.total_referrals > 0
         ORDER BY r.total_referrals DESC
         LIMIT 10"
    );
}

/**
 * Get monthly sales report
 */
function epic_admin_get_monthly_sales_report() {
    return db()->select(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                COUNT(*) as orders, 
                COALESCE(SUM(total_amount), 0) as revenue
         FROM " . TABLE_ORDERS . " 
         WHERE status = 'completed' 
           AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
         GROUP BY DATE_FORMAT(created_at, '%Y-%m')
         ORDER BY month DESC"
    );
}

/**
 * Get top affiliates report
 */
function epic_admin_get_top_affiliates_report() {
    return db()->select(
        "SELECT u.name, u.email, 
                COUNT(DISTINCT r.user_id) as referrals,
                COALESCE(SUM(t.amount), 0) as total_commissions
         FROM " . TABLE_USERS . " u
         LEFT JOIN " . TABLE_REFERRALS . " r ON u.id = r.referrer_id
         LEFT JOIN " . TABLE_TRANSACTIONS . " t ON u.id = t.user_id AND t.type = 'commission'
         WHERE u.role IN ('affiliate', 'user') AND u.status = 'premium'
         GROUP BY u.id
         HAVING total_commissions > 0
         ORDER BY total_commissions DESC
         LIMIT 10"
    );
}

/**
 * Get recent activities
 */
function epic_admin_get_recent_activities() {
    $users_table = db()->table(TABLE_USERS);
    $orders_table = db()->table(TABLE_ORDERS);
    $products_table = db()->table(TABLE_PRODUCTS);
    
    return db()->select(
        "SELECT 'user_registration' as type, u.name as description, u.created_at as timestamp
         FROM {$users_table} u
         WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         UNION ALL
         SELECT 'order' as type, CONCAT('Order #', o.id, ' - ', p.name) as description, o.created_at as timestamp
         FROM {$orders_table} o
         LEFT JOIN {$products_table} p ON o.product_id = p.id
         WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         ORDER BY timestamp DESC
         LIMIT 20"
    );
}

/**
 * Get daily sales data
 */
function epic_admin_get_daily_sales($start_date, $end_date) {
    return db()->select(
        "SELECT DATE(created_at) as date, 
                COUNT(*) as orders, 
                COALESCE(SUM(total_amount), 0) as revenue
         FROM " . TABLE_ORDERS . " 
         WHERE status = 'completed' 
           AND DATE(created_at) BETWEEN ? AND ?
         GROUP BY DATE(created_at)
         ORDER BY date ASC",
        [$start_date, $end_date]
    );
}

/**
 * Get product sales data
 */
function epic_admin_get_product_sales($start_date, $end_date) {
    return db()->select(
        "SELECT p.name, 
                COUNT(o.id) as orders, 
                COALESCE(SUM(o.total_amount), 0) as revenue
         FROM " . TABLE_PRODUCTS . " p
         LEFT JOIN " . TABLE_ORDERS . " o ON p.id = o.product_id 
           AND o.status = 'completed'
           AND DATE(o.created_at) BETWEEN ? AND ?
         GROUP BY p.id
         ORDER BY revenue DESC",
        [$start_date, $end_date]
    );
}

/**
 * Get affiliate sales data
 */
function epic_admin_get_affiliate_sales($start_date, $end_date) {
    return db()->select(
        "SELECT u.name, u.email,
                COUNT(o.id) as orders,
                COALESCE(SUM(o.total_amount), 0) as revenue,
                COALESCE(SUM(t.amount), 0) as commissions
         FROM " . TABLE_USERS . " u
         LEFT JOIN " . TABLE_REFERRALS . " r ON u.id = r.referrer_id
         LEFT JOIN " . TABLE_ORDERS . " o ON r.user_id = o.user_id 
           AND o.status = 'completed'
           AND DATE(o.created_at) BETWEEN ? AND ?
         LEFT JOIN " . TABLE_TRANSACTIONS . " t ON u.id = t.user_id 
           AND t.type = 'commission'
           AND DATE(t.created_at) BETWEEN ? AND ?
         WHERE u.role IN ('affiliate', 'user')
         GROUP BY u.id
         HAVING orders > 0
         ORDER BY revenue DESC",
        [$start_date, $end_date, $start_date, $end_date]
    );
}

/**
 * Get total revenue
 */
function epic_admin_get_total_revenue($start_date, $end_date) {
    return db()->selectValue(
        "SELECT COALESCE(SUM(total_amount), 0)
         FROM " . TABLE_ORDERS . " 
         WHERE status = 'completed' 
           AND DATE(created_at) BETWEEN ? AND ?",
        [$start_date, $end_date]
    ) ?: 0;
}

/**
 * Get user growth data
 */
function epic_admin_get_user_growth($period) {
    return db()->select(
        "SELECT DATE(created_at) as date, COUNT(*) as new_users
         FROM " . TABLE_USERS . " 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         GROUP BY DATE(created_at)
         ORDER BY date ASC",
        [$period]
    );
}

/**
 * Get user types breakdown
 */
function epic_admin_get_user_types_breakdown() {
    return db()->select(
        "SELECT status, role, COUNT(*) as count
         FROM " . TABLE_USERS . " 
         GROUP BY status, role
         ORDER BY count DESC"
    );
}

/**
 * Get active users
 */
function epic_admin_get_active_users($period) {
    return db()->selectValue(
        "SELECT COUNT(DISTINCT user_id)
         FROM " . db()->table('landing_visits') . " 
         WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
        [$period]
    ) ?: 0;
}

/**
 * Get user locations (mock data)
 */
function epic_admin_get_user_locations() {
    return [
        ['country' => 'Indonesia', 'users' => 150],
        ['country' => 'Malaysia', 'users' => 45],
        ['country' => 'Singapore', 'users' => 30],
        ['country' => 'Thailand', 'users' => 25],
        ['country' => 'Philippines', 'users' => 20]
    ];
}

/**
 * Get commission summary
 */
function epic_admin_get_commission_summary($period, $status) {
    $where_clause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params = [$period];
    
    if ($status !== 'all') {
        $where_clause .= " AND status = ?";
        $params[] = $status;
    }
    
    return db()->selectOne(
        "SELECT COUNT(*) as total_transactions,
                COALESCE(SUM(amount), 0) as total_amount,
                AVG(amount) as average_amount
         FROM " . TABLE_TRANSACTIONS . " 
         {$where_clause} AND type = 'commission'",
        $params
    );
}

/**
 * Get top commission earners
 */
function epic_admin_get_top_commission_earners($period) {
    return db()->select(
        "SELECT u.name, u.email,
                COUNT(t.id) as transactions,
                COALESCE(SUM(t.amount), 0) as total_earned
         FROM " . TABLE_USERS . " u
         JOIN " . TABLE_TRANSACTIONS . " t ON u.id = t.user_id
         WHERE t.type = 'commission'
           AND t.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         GROUP BY u.id
         ORDER BY total_earned DESC
         LIMIT 10",
        [$period]
    );
}

/**
 * Get commission trends
 */
function epic_admin_get_commission_trends($period) {
    return db()->select(
        "SELECT DATE(created_at) as date,
                COUNT(*) as transactions,
                COALESCE(SUM(amount), 0) as total_amount
         FROM " . TABLE_TRANSACTIONS . " 
         WHERE type = 'commission'
           AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         GROUP BY DATE(created_at)
         ORDER BY date ASC",
        [$period]
    );
}

/**
 * Get pending payouts
 */
function epic_admin_get_pending_payouts() {
    return db()->select(
        "SELECT u.name, u.email,
                COUNT(t.id) as pending_transactions,
                COALESCE(SUM(t.amount), 0) as pending_amount
         FROM " . TABLE_USERS . " u
         JOIN " . TABLE_TRANSACTIONS . " t ON u.id = t.user_id
         WHERE t.type = 'commission' AND t.status = 'pending'
         GROUP BY u.id
         ORDER BY pending_amount DESC"
    );
}

/**
 * Get landing page visits
 */
function epic_admin_get_landing_page_visits($period) {
    return db()->select(
        "SELECT template_name,
                COUNT(*) as visits,
                COUNT(DISTINCT ip_address) as unique_visitors
         FROM " . db()->table('landing_visits') . " 
         WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         GROUP BY template_name
         ORDER BY visits DESC",
        [$period]
    );
}

/**
 * Get traffic sources
 */
function epic_admin_get_traffic_sources($period) {
    return db()->select(
        "SELECT CASE 
                  WHEN referrer = '' OR referrer IS NULL THEN 'Direct'
                  ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(referrer, '/', 3), '/', -1)
                END as source,
                COUNT(*) as visits
         FROM " . db()->table('landing_visits') . " 
         WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         GROUP BY source
         ORDER BY visits DESC
         LIMIT 10",
        [$period]
    );
}

/**
 * Get popular templates
 */
function epic_admin_get_popular_templates($period) {
    return db()->select(
        "SELECT template_name,
                COUNT(*) as visits,
                COUNT(DISTINCT sponsor_id) as unique_sponsors
         FROM " . db()->table('landing_visits') . " 
         WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         GROUP BY template_name
         ORDER BY visits DESC",
        [$period]
    );
}

/**
 * Get conversion rates (mock calculation)
 */
function epic_admin_get_conversion_rates($period) {
    $visits = db()->selectValue(
        "SELECT COUNT(*) FROM " . db()->table('landing_visits') . " 
         WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
        [$period]
    ) ?: 1;
    
    $orders = db()->selectValue(
        "SELECT COUNT(*) FROM " . TABLE_ORDERS . " 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
        [$period]
    ) ?: 0;
    
    return [
        'total_visits' => $visits,
        'total_orders' => $orders,
        'conversion_rate' => round(($orders / $visits) * 100, 2)
    ];
}

/**
 * Admin transactions management
 */
function epic_admin_transactions($segments) {
    $action = $segments[2] ?? 'list';
    
    switch ($action) {
        case 'list':
            epic_admin_transactions_list();
            break;
            
        case 'view':
            $transaction_id = $segments[3] ?? null;
            epic_admin_transactions_view($transaction_id);
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Admin transactions list
 */
function epic_admin_transactions_list() {
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $type = $_GET['type'] ?? '';
    
    // Build query
    $where = '1=1';
    $params = [];
    
    if (!empty($type)) {
        $where .= " AND t.type = ?";
        $params[] = $type;
    }
    
    // Get transactions
    $transactions = db()->select(
        "SELECT t.*, u.name as user_name, u.email as user_email
         FROM " . TABLE_TRANSACTIONS . " t
         JOIN " . TABLE_USERS . " u ON t.user_id = u.id
         WHERE {$where}
         ORDER BY t.created_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );
    
    // Get total count
    $total = db()->selectValue(
        "SELECT COUNT(*) FROM " . TABLE_TRANSACTIONS . " t WHERE {$where}",
        $params
    );
    
    $total_pages = ceil($total / $limit);
    
    $data = [
        'page_title' => 'Transactions Management - Admin',
        'transactions' => $transactions,
        'type' => $type,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ];
    
    epic_render_template('admin/transactions/list', $data);
}

/**
 * View transaction details
 */
function epic_admin_transactions_view($transaction_id) {
    if (!$transaction_id) {
        epic_redirect(epic_url('admin/transactions'));
        return;
    }
    
    // Get transaction details with user information
    $transaction = db()->selectOne(
        "SELECT t.*, u.name as user_name, u.email as user_email,
                o.id as order_id, p.name as product_name
         FROM " . TABLE_TRANSACTIONS . " t
         LEFT JOIN " . TABLE_USERS . " u ON t.user_id = u.id
         LEFT JOIN " . TABLE_ORDERS . " o ON t.order_id = o.id
         LEFT JOIN " . TABLE_PRODUCTS . " p ON o.product_id = p.id
         WHERE t.id = ?",
        [$transaction_id]
    );
    
    if (!$transaction) {
        epic_redirect(epic_url('admin/transactions'));
        return;
    }
    
    $data = [
        'page_title' => 'Transaction Details - Admin',
        'transaction' => $transaction
    ];
    
    epic_render_template('admin/transactions/view', $data);
}

/**
 * Admin articles management
 */
function epic_admin_articles($segments) {
    $action = $segments[2] ?? 'list';
    
    switch ($action) {
        case 'list':
            epic_admin_articles_list();
            break;
            
        case 'add':
            epic_admin_articles_add();
            break;
            
        case 'edit':
            $article_id = $segments[3] ?? null;
            epic_admin_articles_edit($article_id);
            break;
            
        case 'delete':
            $article_id = $segments[3] ?? null;
            epic_admin_articles_delete($article_id);
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Admin articles list
 */
function epic_admin_articles_list() {
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    
    // Build query
    $where = '1=1';
    $params = [];
    
    if (!empty($search)) {
        $where .= " AND (title LIKE ? OR content LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    if (!empty($status)) {
        $where .= " AND status = ?";
        $params[] = $status;
    }
    
    // Get articles
    $articles = db()->select(
        "SELECT a.*, u.name as author_name
         FROM " . TABLE_ARTICLES . " a
         LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
         WHERE {$where}
         ORDER BY a.created_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );
    
    // Get total count
    $total = db()->selectValue(
        "SELECT COUNT(*) FROM " . TABLE_ARTICLES . " WHERE {$where}",
        $params
    );
    
    $total_pages = ceil($total / $limit);
    
    $data = [
        'page_title' => 'Articles Management - Admin',
        'articles' => $articles,
        'search' => $search,
        'status' => $status,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ];
    
    epic_render_template('admin/articles/list', $data);
}

/**
 * Add new article
 */
function epic_admin_articles_add() {
    $error = null;
    $success = null;
    
    if (epic_is_post()) {
        $title = epic_post('title');
        $content = epic_post('content');
        $excerpt = epic_post('excerpt');
        $status = epic_post('status', 'draft');
        $category_id = epic_post('category_id');
        
        // Validation
        if (empty($title) || empty($content)) {
            $error = 'Title and content are required.';
        } else {
            // Create article
            $article_data = [
                'title' => $title,
                'content' => $content,
                'excerpt' => $excerpt,
                'status' => $status,
                'category_id' => $category_id,
                'author_id' => epic_current_user()['id'],
                'slug' => epic_generate_slug($title)
            ];
            
            $article_id = db()->insert('articles', $article_data);
            
            if ($article_id) {
                epic_log_activity(epic_current_user()['id'], 'article_created', "Article {$title} created");
                epic_flash('success', 'Article created successfully.');
                epic_redirect(epic_url('admin/articles'));
                return;
            } else {
                $error = 'Failed to create article.';
            }
        }
    }
    
    // Get categories for dropdown
    $categories = db()->select("SELECT * FROM " . TABLE_CATEGORIES . " WHERE status = 'active' ORDER BY name");
    
    $data = [
        'page_title' => 'Add Article - Admin',
        'categories' => $categories,
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('admin/articles/add', $data);
}

/**
 * Edit article
 */
function epic_admin_articles_edit($article_id) {
    if (!$article_id) {
        epic_redirect(epic_url('admin/articles'));
        return;
    }
    
    $article = db()->selectOne("SELECT * FROM " . TABLE_ARTICLES . " WHERE id = ?", [$article_id]);
    if (!$article) {
        epic_redirect(epic_url('admin/articles'));
        return;
    }
    
    $error = null;
    $success = null;
    
    if (epic_is_post()) {
        $title = epic_post('title');
        $content = epic_post('content');
        $excerpt = epic_post('excerpt');
        $status = epic_post('status');
        $category_id = epic_post('category_id');
        
        // Validation
        if (empty($title) || empty($content)) {
            $error = 'Title and content are required.';
        } else {
            // Update article
            $update_data = [
                'title' => $title,
                'content' => $content,
                'excerpt' => $excerpt,
                'status' => $status,
                'category_id' => $category_id,
                'slug' => epic_generate_slug($title),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $updated = db()->update('articles', $update_data, 'id = ?', [$article_id]);
            
            if ($updated) {
                epic_log_activity(epic_current_user()['id'], 'article_updated', "Article {$title} updated");
                epic_flash('success', 'Article updated successfully.');
                epic_redirect(epic_url('admin/articles'));
                return;
            } else {
                $error = 'Failed to update article.';
            }
        }
    }
    
    // Get categories for dropdown
    $categories = db()->select("SELECT * FROM " . TABLE_CATEGORIES . " WHERE status = 'active' ORDER BY name");
    
    $data = [
        'page_title' => 'Edit Article - Admin',
        'article' => $article,
        'categories' => $categories,
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('admin/articles/edit', $data);
}

/**
 * Delete article
 */
function epic_admin_articles_delete($article_id) {
    if (!$article_id) {
        epic_redirect(epic_url('admin/articles'));
        return;
    }
    
    $article = db()->selectOne("SELECT * FROM " . TABLE_ARTICLES . " WHERE id = ?", [$article_id]);
    if (!$article) {
        epic_redirect(epic_url('admin/articles'));
        return;
    }
    
    // Delete article
    $deleted = db()->delete('articles', 'id = ?', [$article_id]);
    
    if ($deleted) {
        epic_log_activity(epic_current_user()['id'], 'article_deleted', "Article {$article['title']} deleted");
        epic_flash('success', 'Article deleted successfully.');
    } else {
        epic_flash('error', 'Failed to delete article.');
    }
    
    epic_redirect(epic_url('admin/articles'));
}

/**
 * Admin categories management
 */
function epic_admin_categories($segments) {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/categories.php';
}

/**
 * Admin categories list
 */
function epic_admin_categories_list() {
    $categories = db()->select(
        "SELECT c.*, COUNT(a.id) as article_count
         FROM " . db()->table(TABLE_CATEGORIES) . " c
         LEFT JOIN " . db()->table(TABLE_ARTICLES) . " a ON c.id = a.category_id
         GROUP BY c.id
         ORDER BY c.sort_order, c.name"
    );
    
    $data = [
        'page_title' => 'Categories Management - Admin',
        'categories' => $categories
    ];
    
    epic_render_template('admin/categories/list', $data);
}

/**
 * Add new category
 */
function epic_admin_categories_add() {
    $error = null;
    $success = null;
    
    if (epic_is_post()) {
        $name = epic_post('name');
        $description = epic_post('description');
        $status = epic_post('status', 'active');
        
        // Validation
        if (empty($name)) {
            $error = 'Category name is required.';
        } else {
            // Create category
            $category_data = [
                'name' => $name,
                'description' => $description,
                'status' => $status,
                'slug' => epic_generate_slug($name)
            ];
            
            $category_id = db()->insert(TABLE_CATEGORIES, $category_data);
            
            if ($category_id) {
                epic_log_activity(epic_current_user()['id'], 'category_created', "Category {$name} created");
                epic_flash('success', 'Category created successfully.');
                epic_redirect(epic_url('admin/categories'));
                return;
            } else {
                $error = 'Failed to create category.';
            }
        }
    }
    
    $data = [
        'page_title' => 'Add Category - Admin',
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('admin/categories/add', $data);
}

/**
 * Edit category
 */
function epic_admin_categories_edit($category_id) {
    if (!$category_id) {
        epic_redirect(epic_url('admin/categories'));
        return;
    }
    
    $category = db()->selectOne("SELECT * FROM " . db()->table(TABLE_CATEGORIES) . " WHERE id = ?", [$category_id]);
    if (!$category) {
        epic_redirect(epic_url('admin/categories'));
        return;
    }
    
    $error = null;
    $success = null;
    
    if (epic_is_post()) {
        $name = epic_post('name');
        $description = epic_post('description');
        $status = epic_post('status');
        
        // Validation
        if (empty($name)) {
            $error = 'Category name is required.';
        } else {
            // Update category
            $update_data = [
                'name' => $name,
                'description' => $description,
                'status' => $status,
                'slug' => epic_generate_slug($name),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $updated = db()->update(TABLE_CATEGORIES, $update_data, 'id = ?', [$category_id]);
            
            if ($updated) {
                epic_log_activity(epic_current_user()['id'], 'category_updated', "Category {$name} updated");
                epic_flash('success', 'Category updated successfully.');
                epic_redirect(epic_url('admin/categories'));
                return;
            } else {
                $error = 'Failed to update category.';
            }
        }
    }
    
    $data = [
        'page_title' => 'Edit Category - Admin',
        'category' => $category,
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('admin/categories/edit', $data);
}

/**
 * Delete category
 */
function epic_admin_categories_delete($category_id) {
    if (!$category_id) {
        epic_redirect(epic_url('admin/categories'));
        return;
    }
    
    $category = db()->selectOne("SELECT * FROM " . db()->table(TABLE_CATEGORIES) . " WHERE id = ?", [$category_id]);
    if (!$category) {
        epic_redirect(epic_url('admin/categories'));
        return;
    }
    
    // Check if category has articles
    $has_articles = db()->selectValue(
        "SELECT COUNT(*) FROM " . db()->table(TABLE_ARTICLES) . " WHERE category_id = ?",
        [$category_id]
    );
    
    if ($has_articles > 0) {
        epic_flash('error', 'Cannot delete category that has articles.');
    } else {
        // Delete category
        $deleted = db()->delete(TABLE_CATEGORIES, 'id = ?', [$category_id]);
        
        if ($deleted) {
            epic_log_activity(epic_current_user()['id'], 'category_deleted', "Category {$category['name']} deleted");
            epic_flash('success', 'Category deleted successfully.');
        } else {
            epic_flash('error', 'Failed to delete category.');
        }
    }
    
    epic_redirect(epic_url('admin/categories'));
}

/**
 * Admin maintenance
 */
function epic_admin_maintenance() {
    $error = null;
    $success = null;
    
    if (epic_is_post()) {
        $action = epic_post('action');
        
        switch ($action) {
            case 'clear_cache':
                // Clear cache files
                $cache_dir = EPIC_CACHE_DIR;
                if (is_dir($cache_dir)) {
                    $files = glob($cache_dir . '/*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                }
                $success = 'Cache cleared successfully.';
                break;
                
            case 'clear_logs':
                // Clear log files
                $logs_dir = EPIC_ROOT . '/logs';
                if (is_dir($logs_dir)) {
                    $files = glob($logs_dir . '/*.log');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            file_put_contents($file, '');
                        }
                    }
                }
                $success = 'Logs cleared successfully.';
                break;
                
            case 'optimize_db':
                // Optimize database tables
                try {
                    $tables = ['epic_users', 'epic_orders', 'epic_transactions', 'epic_articles', 'epic_categories'];
                    foreach ($tables as $table) {
                        db()->query("OPTIMIZE TABLE {$table}");
                    }
                    $success = 'Database optimized successfully.';
                } catch (Exception $e) {
                    $error = 'Failed to optimize database: ' . $e->getMessage();
                }
                break;
                
            default:
                $error = 'Invalid maintenance action.';
                break;
        }
        
        if ($success) {
            epic_log_activity(epic_current_user()['id'], 'maintenance', "Maintenance action: {$action}");
        }
    }
    
    // Get system info
    $system_info = [
        'php_version' => PHP_VERSION,
        'memory_usage' => memory_get_usage(true),
        'memory_limit' => ini_get('memory_limit'),
        'disk_usage' => disk_free_space('.'),
        'cache_size' => epic_get_directory_size(EPIC_CACHE_DIR),
        'logs_size' => epic_get_directory_size(EPIC_ROOT . '/logs')
    ];
    
    $data = [
        'page_title' => 'System Maintenance - Admin',
        'system_info' => $system_info,
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_template('admin/maintenance', $data);
}

/**
 * Get directory size
 */
function epic_get_directory_size($directory) {
    $size = 0;
    if (is_dir($directory)) {
        try {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                $size += $file->getSize();
            }
        } catch (Exception $e) {
            // Handle permission errors gracefully
            return 0;
        }
    }
    return $size;
}

/**
 * Send order rejection email
 */
function epic_send_order_rejection_email($user, $order) {
    $subject = 'Order Rejected - EPIC Hub';
    $message = "
    <html>
    <head>
        <title>Order Rejected</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .order-info { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Order Rejected</h1>
            </div>
            <div class='content'>
                <h2>Hello {$user['name']},</h2>
                <p>We regret to inform you that your order has been rejected.</p>
                
                <div class='order-info'>
                    <h3>Order Details:</h3>
                    <p><strong>Order ID:</strong> #{$order['id']}</p>
                    <p><strong>Amount:</strong> Rp " . number_format($order['total_amount']) . "</p>
                    <p><strong>Date:</strong> " . date('d/m/Y H:i', strtotime($order['created_at'])) . "</p>
                </div>
                
                <p>If you have any questions about this rejection, please contact our support team.</p>
                
                <p>Best regards,<br>The EPIC Hub Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return epic_send_email($user['email'], $subject, $message);
}

/**
 * Process order payment (admin approval) - renamed to avoid duplication
 */
function epic_admin_process_order_payment($order_id, $admin_id) {
    try {
        db()->beginTransaction();
        
        // Get order details
        $order = epic_get_order($order_id);
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        // Update order status
        epic_update_order_status($order_id, 'paid');
        
        // Process commission if there's a referrer
        if ($order['referrer_id'] && $order['commission_amount'] > 0) {
            epic_create_transaction([
                'user_id' => $order['referrer_id'],
                'order_id' => $order_id,
                'type' => 'commission',
                'amount_in' => $order['commission_amount'],
                'status' => 'completed',
                'description' => 'Commission from order #' . $order['order_number']
            ]);
            
            // Update referral stats
            epic_update_referral_stats($order['referrer_id'], $order['commission_amount'], $order['amount']);
        }
        
        // Log activity
        epic_log_activity($admin_id, 'order_approved', 'Order #' . $order['order_number'] . ' approved');
        
        db()->commit();
        return true;
        
    } catch (Exception $e) {
        db()->rollback();
        throw $e;
    }
}

/**
 * Admin AJAX handler
 */
function epic_admin_ajax($segments) {
    $action = $segments[2] ?? '';
    
    switch ($action) {
        case 'profile-form':
            epic_admin_ajax_profile_form();
            break;
            
        case 'member-detail':
            epic_admin_ajax_member_detail();
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'AJAX endpoint not found']);
            break;
    }
}

/**
 * AJAX Profile Form
 */
function epic_admin_ajax_profile_form() {
    $user = epic_current_user();
    if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
        http_response_code(403);
        echo '<div class="error-message">Access denied</div>';
        return;
    }
    
    // Set data for template
    $data = [
        'user' => $user,
        'success' => null,
        'error' => null
    ];
    
    // Determine which template to use based on referer
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $template = 'profile-form-content.php';
    
    if (strpos($referer, '/admin/affiliates') !== false) {
        $template = 'profile-form-content-affiliates.php';
    }
    
    // Output only the form content without full page layout
    ob_start();
    include EPIC_ROOT . '/themes/modern/admin/' . $template;
    $content = ob_get_clean();
    
    echo $content;
}

/**
 * Admin edit profile page
 */
function epic_admin_edit_profile() {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/edit-profile.php';
}

/**
 * Admin manage section
 */
function epic_admin_manage($segments) {
    $subsection = $segments[2] ?? '';
    
    switch ($subsection) {
        case 'member':
            epic_admin_member($segments);
            break;
        case 'order':
            epic_admin_order($segments);
            break;
        case 'product':
            epic_admin_product($segments);
            break;
        case 'landing-page':
            epic_admin_landing_page($segments);
            break;
        case 'landing-page-manager':
            epic_admin_landing_page_manager($segments);
            break;
        case 'payout':
            epic_admin_payout($segments);
            break;
        case 'finance':
            epic_admin_finance($segments);
            break;
        case 'update-price':
            epic_admin_update_price($segments);
            break;
        case 'epis':
            epic_admin_epis($segments);
            break;
        default:
            epic_route_404();
            break;
    }
}

/**
 * Placeholder functions for admin sections
 */
function epic_admin_member($segments) {
    $action = $segments[3] ?? '';
    
    switch ($action) {
        case 'add':
            // Handle add member page using new layout system
            // TODO: Create member-add page using layout global
            $data = [
                'page_title' => 'Tambah Member - ' . epic_setting('site_name'),
                'current_page' => 'member'
            ];
            epic_render_template('admin/member-add', $data);
            break;
            
        case 'edit':
            // Handle edit member page using new layout system
            $member_id = $segments[4] ?? null;
            if (!$member_id || !is_numeric($member_id)) {
                epic_route_404();
                return;
            }
            
            // TODO: Create member-edit page using layout global
            $data = [
                'page_title' => 'Edit Member - ' . epic_setting('site_name'),
                'current_page' => 'member',
                'member_id' => (int)$member_id
            ];
            epic_render_template('admin/member-edit', $data);
            break;
            
        default:
            // Default member list page using new layout system
            include __DIR__ . '/../themes/modern/admin/member.php';
            break;
    }
}

function epic_admin_order($segments) {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/order.php';
}

function epic_admin_product($segments) {
    $action = $segments[3] ?? '';
    
    switch ($action) {
        case 'add':
            // Handle add product page using new layout system
            $data = [
                'page_title' => 'Tambah Produk - ' . epic_setting('site_name'),
                'current_page' => 'product'
            ];
            include __DIR__ . '/../themes/modern/admin/product-add.php';
            break;
            
        case 'edit':
            // Handle edit product page using new layout system
            $product_id = $segments[4] ?? null;
            if (!$product_id || !is_numeric($product_id)) {
                epic_route_404();
                return;
            }
            
            $data = [
                'page_title' => 'Edit Produk - ' . epic_setting('site_name'),
                'current_page' => 'product',
                'product_id' => (int)$product_id
            ];
            include __DIR__ . '/../themes/modern/admin/product-edit.php';
            break;
            
        default:
            // Default product list page using new layout system
            include __DIR__ . '/../themes/modern/admin/product.php';
            break;
    }
}

function epic_admin_landing_page($segments) {
    $action = $segments[3] ?? '';
    
    switch ($action) {
        case 'add':
            // Handle add landing page
            $data = [
                'page_title' => 'Tambah Landing Page - ' . epic_setting('site_name'),
                'current_page' => 'landing-page'
            ];
            epic_render_template('admin/landing-page-add', $data);
            break;
            
        case 'edit':
            // Handle edit landing page
            $config_id = $segments[4] ?? null;
            if (!$config_id || !is_numeric($config_id)) {
                epic_route_404();
                return;
            }
            
            $data = [
                'page_title' => 'Edit Landing Page - ' . epic_setting('site_name'),
                'current_page' => 'landing-page',
                'config_id' => (int)$config_id
            ];
            epic_render_template('admin/landing-page-edit', $data);
            break;
            
        case 'preview':
            // Handle preview landing page
            $template_name = $segments[4] ?? 'template-1';
            $user_id = $segments[5] ?? null;
            
            if (!$user_id || !is_numeric($user_id)) {
                epic_route_404();
                return;
            }
            
            // Redirect to actual landing page
            epic_redirect($user_id . '/' . $template_name);
            break;
            
        case 'analytics':
            // Handle landing page analytics
            $data = [
                'page_title' => 'Landing Page Analytics - ' . epic_setting('site_name'),
                'current_page' => 'landing-page'
            ];
            epic_render_template('admin/landing-page-analytics', $data);
            break;
            
        default:
            // Default landing page list
            $data = [
                'page_title' => 'Landing Page Management - ' . epic_setting('site_name'),
                'current_page' => 'landing-page'
            ];
            epic_render_template('admin/landing-page', $data);
            break;
    }
}

function epic_admin_payout($segments) {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/payout.php';
}

function epic_admin_finance($segments) {
    // Use new layout system
    include __DIR__ . '/../themes/modern/admin/finance.php';
}

function epic_admin_update_price($segments) {
    $data = [
        'page_title' => 'Update Price - ' . epic_setting('site_name'),
        'current_page' => 'update-price'
    ];
    epic_render_template('admin/update-price', $data);
}

function epic_admin_blog($segments) {
    // Use the new comprehensive blog management system
    include __DIR__ . '/../themes/modern/admin/blog.php';
}

function epic_admin_all_products($segments) {
    $data = [
        'page_title' => 'All Products - ' . epic_setting('site_name'),
        'current_page' => 'all-products'
    ];
    epic_render_template('admin/all-products', $data);
}

/**
 * AJAX: Get member detail
 */
function epic_admin_ajax_member_detail() {
    header('Content-Type: application/json');
    
    // Check admin access
    $user = epic_current_user();
    if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        return;
    }
    
    $member_id = (int)($_GET['id'] ?? 0);
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
        return;
    }
    
    try {
        // Get member details with referrer info
        $member = db()->selectOne(
            "SELECT u.*, 
                    r.referrer_id,
                    ref.name as referrer_name,
                    ref.email as referrer_email,
                    ref.phone as referrer_phone,
                    ref.status as referrer_status
             FROM epic_users u
             LEFT JOIN epic_referrals r ON u.id = r.user_id
             LEFT JOIN epic_users ref ON r.referrer_id = ref.id
             WHERE u.id = ?",
            [$member_id]
        );
        
        if (!$member) {
            echo json_encode(['success' => false, 'message' => 'Member not found']);
            return;
        }
        
        // Get referral statistics
        $referral_stats = db()->selectOne(
            "SELECT 
                COUNT(*) as total_referrals,
                SUM(CASE WHEN u.status = 'free' THEN 1 ELSE 0 END) as free_referrals,
                SUM(CASE WHEN u.status = 'epic' THEN 1 ELSE 0 END) as epic_referrals
             FROM epic_referrals r
             JOIN epic_users u ON r.user_id = u.id
             WHERE r.referrer_id = ?",
            [$member_id]
        ) ?: ['total_referrals' => 0, 'free_referrals' => 0, 'epic_referrals' => 0];
        
        // Generate HTML content
        ob_start();
        ?>
        <div class="member-detail-content">
            <!-- Member Info Section -->
            <div class="detail-section">
                <div class="detail-section-header">
                    <h4 class="detail-section-title">
                        <i data-feather="user" width="18" height="18"></i>
                        Informasi Pribadi
                    </h4>
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label class="detail-label">ID Member</label>
                        <span class="detail-value"><?= $member['id'] ?></span>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Nama Lengkap</label>
                        <span class="detail-value"><?= htmlspecialchars($member['name']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Email</label>
                        <span class="detail-value"><?= htmlspecialchars($member['email']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Nomor WhatsApp</label>
                        <span class="detail-value">
                            <?php if (!empty($member['phone'])): ?>
                                <a href="https://wa.me/<?= $member['phone'] ?>" target="_blank" class="whatsapp-link">
                                    <i data-feather="message-circle" width="14" height="14"></i>
                                    <?= htmlspecialchars($member['phone']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Status</label>
                        <span class="detail-value">
                            <span class="status-badge status-<?= $member['status'] ?>">
                                <?= ucfirst($member['status']) ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Role</label>
                        <span class="detail-value">
                            <span class="role-badge role-<?= $member['role'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $member['role'])) ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Kode Referral</label>
                        <span class="detail-value">
                            <?php if (!empty($member['referral_code'])): ?>
                                <code class="referral-code"><?= htmlspecialchars($member['referral_code']) ?></code>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Tanggal Bergabung</label>
                        <span class="detail-value"><?= date('d/m/Y H:i', strtotime($member['created_at'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Login Terakhir</label>
                        <span class="detail-value">
                            <?php if ($member['last_login_at']): ?>
                                <?= date('d/m/Y H:i', strtotime($member['last_login_at'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Belum pernah login</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Referrer Section -->
            <?php if ($member['referrer_id']): ?>
            <div class="detail-section">
                <div class="detail-section-header">
                    <h4 class="detail-section-title">
                        <i data-feather="users" width="18" height="18"></i>
                        Pemberi Referensi
                    </h4>
                </div>
                <div class="referrer-info">
                    <div class="referrer-avatar">
                        <div class="avatar-circle">
                            <?= strtoupper(substr($member['referrer_name'], 0, 2)) ?>
                        </div>
                    </div>
                    <div class="referrer-details">
                        <div class="referrer-name">
                            <?= htmlspecialchars($member['referrer_name']) ?>
                            <span class="status-badge status-<?= $member['referrer_status'] ?>">
                                <?= ucfirst($member['referrer_status']) ?>
                            </span>
                        </div>
                        <div class="referrer-contact">
                            <span class="referrer-email">
                                <i data-feather="mail" width="12" height="12"></i>
                                <?= htmlspecialchars($member['referrer_email']) ?>
                            </span>
                            <?php if (!empty($member['referrer_phone'])): ?>
                            <span class="referrer-phone">
                                <i data-feather="phone" width="12" height="12"></i>
                                <?= htmlspecialchars($member['referrer_phone']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="detail-section">
                <div class="detail-section-header">
                    <h4 class="detail-section-title">
                        <i data-feather="users" width="18" height="18"></i>
                        Pemberi Referensi
                    </h4>
                </div>
                <div class="no-referrer">
                    <i data-feather="user-x" width="24" height="24"></i>
                    <span>Member ini tidak memiliki pemberi referensi</span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Referral Statistics -->
            <div class="detail-section">
                <div class="detail-section-header">
                    <h4 class="detail-section-title">
                        <i data-feather="trending-up" width="18" height="18"></i>
                        Statistik Referral
                    </h4>
                </div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?= $referral_stats['total_referrals'] ?></div>
                        <div class="stat-label">Total Referral</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $referral_stats['free_referrals'] ?></div>
                        <div class="stat-label">Free Account</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $referral_stats['epic_referrals'] ?></div>
                        <div class="stat-label">EPIC Account</div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        
        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
        
    } catch (Exception $e) {
        error_log('Member detail error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while loading member details']);
    }
}

/**
 * Process profile edit form submission
 */
function epic_process_profile_edit($post_data, $files_data) {
    $user = epic_current_user();
    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }
    
    try {
        $updated_data = [];
        
        // Process basic profile data
        if (isset($post_data['name'])) {
            $updated_data['name'] = epic_sanitize($post_data['name']);
        }
        if (isset($post_data['email'])) {
            $updated_data['email'] = epic_sanitize($post_data['email']);
        }
        if (isset($post_data['phone'])) {
            $phone = epic_sanitize($post_data['phone']);
            // Phone number is already formatted with country code from frontend
            if (!empty($phone)) {
                $updated_data['phone'] = $phone;
            }
        }
        if (isset($post_data['affiliate_code'])) {
            $updated_data['affiliate_code'] = epic_sanitize($post_data['affiliate_code']);
        }
        
        // Process social media links
        if (isset($post_data['social_facebook'])) {
            $updated_data['social_facebook'] = epic_sanitize($post_data['social_facebook']);
        }
        if (isset($post_data['social_instagram'])) {
            $updated_data['social_instagram'] = epic_sanitize($post_data['social_instagram']);
        }
        if (isset($post_data['social_tiktok'])) {
            $updated_data['social_tiktok'] = epic_sanitize($post_data['social_tiktok']);
        }
        if (isset($post_data['social_youtube'])) {
            $updated_data['social_youtube'] = epic_sanitize($post_data['social_youtube']);
        }
        
        // Handle photo upload
        if (isset($files_data['profile_photo']) && $files_data['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $photo_result = handleProfilePhotoUpload($files_data['profile_photo'], $user['id']);
            if ($photo_result['success']) {
                $updated_data['profile_photo'] = $photo_result['filename'];
            } else {
                return ['success' => false, 'message' => $photo_result['message']];
            }
        }
        
        // Handle password change
        if (!empty($post_data['password'])) {
            if ($post_data['password'] !== $post_data['password_confirm']) {
                return ['success' => false, 'message' => 'Password confirmation does not match'];
            }
            $updated_data['password'] = password_hash($post_data['password'], PASSWORD_DEFAULT);
        }
        
        // Update database
        if (!empty($updated_data)) {
            $updated_data['updated_at'] = date('Y-m-d H:i:s');
            
            $result = db()->update('users', $updated_data, 'id = ?', [$user['id']]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile'];
            }
        }
        
        return ['success' => true, 'message' => 'No changes to update'];
        
    } catch (Exception $e) {
        error_log('Profile update error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while updating profile'];
    }
}

/**
 * Admin profile management
 */
function epic_admin_profile($segments) {
    $action = $segments[2] ?? 'view';
    
    switch ($action) {
        case 'edit':
            epic_admin_profile_edit();
            break;
            
        default:
            epic_admin_profile_view();
            break;
    }
}

/**
 * Admin profile view
 */
function epic_admin_profile_view() {
    $user = epic_current_user();
    
    $data = [
        'page_title' => 'Profil Admin - ' . epic_setting('site_name'),
        'user' => $user
    ];
    
    epic_render_template('admin/profile/view', $data);
}

/**
 * Admin profile edit
 */
function epic_admin_profile_edit() {
    $user = epic_current_user();
    if (!$user) {
        epic_redirect(epic_url('login'));
        return;
    }
    
    $success = null;
    $error = null;
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $updated_data = [];
            
            // Process basic profile data
            if (isset($_POST['name'])) {
                $updated_data['name'] = epic_sanitize($_POST['name']);
            }
            if (isset($_POST['email'])) {
                $updated_data['email'] = epic_sanitize($_POST['email']);
            }
            if (isset($_POST['phone'])) {
                $updated_data['phone'] = epic_sanitize($_POST['phone']);
            }
            if (isset($_POST['bio'])) {
                $updated_data['bio'] = epic_sanitize($_POST['bio']);
            }
            if (isset($_POST['company'])) {
                $updated_data['company'] = epic_sanitize($_POST['company']);
            }
            if (isset($_POST['website'])) {
                $updated_data['website'] = epic_sanitize($_POST['website']);
            }
            
            // Process social media links
            if (isset($_POST['social_facebook'])) {
                $updated_data['social_facebook'] = epic_sanitize($_POST['social_facebook']);
            }
            if (isset($_POST['social_instagram'])) {
                $updated_data['social_instagram'] = epic_sanitize($_POST['social_instagram']);
            }
            if (isset($_POST['social_tiktok'])) {
                $updated_data['social_tiktok'] = epic_sanitize($_POST['social_tiktok']);
            }
            if (isset($_POST['social_youtube'])) {
                $updated_data['social_youtube'] = epic_sanitize($_POST['social_youtube']);
            }
            
            // Process affiliate code
            if (isset($_POST['affiliate_code'])) {
                $affiliate_code = trim($_POST['affiliate_code']);
                if (empty($affiliate_code)) {
                    $affiliate_code = str_pad($user['id'], 6, '0', STR_PAD_LEFT);
                }
                
                // Validate affiliate code
                if (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $affiliate_code)) {
                    throw new Exception('Kode affiliasi harus 3-20 karakter alfanumerik');
                }
                
                // Check if affiliate code is already taken
                $existing = db()->selectOne(
                    "SELECT id FROM " . db()->table('users') . " WHERE affiliate_code = ? AND id != ?",
                    [$affiliate_code, $user['id']]
                );
                
                if ($existing) {
                    throw new Exception('Kode affiliasi sudah digunakan oleh user lain');
                }
                
                $updated_data['affiliate_code'] = $affiliate_code;
            }
            
            // Handle profile photo upload
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $upload_result = handleProfilePhotoUpload($user['id']);
                if ($upload_result['success']) {
                    $updated_data['profile_photo'] = basename($upload_result['image_url']);
                } else {
                    throw new Exception($upload_result['message']);
                }
            }
            
            // Handle password update
            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== $_POST['password_confirm']) {
                    throw new Exception('Konfirmasi password tidak cocok');
                }
                $updated_data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            // Update user data
            if (!empty($updated_data)) {
                $updated_data['updated_at'] = date('Y-m-d H:i:s');
                
                $result = db()->update('users', $updated_data, 'id = ?', [$user['id']]);
                
                if ($result) {
                    // Log activity
                    epic_log_activity($user['id'], 'profile_updated', 'Profile information updated');
                    
                    $success = 'Profil berhasil diperbarui!';
                    
                    // Refresh user data
                    $user = epic_get_user($user['id']);
                } else {
                    throw new Exception('Gagal memperbarui profil');
                }
            } else {
                $success = 'Tidak ada perubahan untuk disimpan.';
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Pass data to template
    $data = [
        'user' => $user,
        'success' => $success,
        'error' => $error
    ];
    
    // Include the profile edit template
    include EPIC_ROOT . '/themes/modern/admin/profile-edit.php';
}

/**
 * Handle logo upload
 */
function handleLogoUpload($file) {
    try {
        // Validate file size (2MB)
        if ($file['size'] > 2097152) {
            return ['success' => false, 'message' => 'File size exceeds 2MB limit'];
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed'];
        }
        
        // Create logos directory if it doesn't exist
        $logoDir = EPIC_ROOT . '/uploads/logos/';
        if (!is_dir($logoDir)) {
            mkdir($logoDir, 0755, true);
        }
        
        // Create unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $extension;
        $uploadPath = $logoDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => false, 'message' => 'Failed to save uploaded file'];
        }
        
        return [
            'success' => true, 
            'message' => 'Logo uploaded successfully',
            'filename' => $filename
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
    }
}

/**
 * Handle profile photo upload
 */
function handleProfilePhotoUpload($userId) {
    try {
        if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No file uploaded or upload error'];
        }
        
        $file = $_FILES['profile_photo'];
        
        // Validate file size (1MB)
        if ($file['size'] > 1048576) {
            return ['success' => false, 'message' => 'File size exceeds 1MB limit'];
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed'];
        }
        
        // Create unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
        $uploadPath = EPIC_ROOT . '/uploads/profiles/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => false, 'message' => 'Failed to save uploaded file'];
        }
        
        // Update database
        $updated = db()->update('users', 
            ['profile_photo' => $filename], 
            'id = ?', [$userId]
        );
        
        if (!$updated) {
            // Clean up uploaded file if database update fails
            unlink($uploadPath);
            return ['success' => false, 'message' => 'Failed to update database'];
        }
        
        // Log activity
        epic_log_activity($userId, 'profile_photo_updated', 'Profile photo updated');
        
        return [
            'success' => true, 
            'message' => 'Profile photo uploaded successfully',
            'image_url' => epic_url('uploads/profiles/' . $filename)
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
    }
}

/**
 * Save affiliate code
 */
function saveAffiliateCode($userId, $affiliateCode) {
    try {
        // Sanitize affiliate code
        $affiliateCode = trim($affiliateCode);
        
        // If empty, use default (user ID padded)
        if (empty($affiliateCode)) {
            $affiliateCode = str_pad($userId, 6, '0', STR_PAD_LEFT);
        }
        
        // Validate affiliate code format (alphanumeric, 3-20 chars)
        if (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $affiliateCode)) {
            return ['success' => false, 'message' => 'Kode affiliasi harus 3-20 karakter alfanumerik'];
        }
        
        // Check if affiliate code is already taken by another user
        $existing = db()->select('epic_users', ['id'], [
            'affiliate_code' => $affiliateCode,
            'id' => ['!=', $userId]
        ]);
        
        if (!empty($existing)) {
            return ['success' => false, 'message' => 'Kode affiliasi sudah digunakan oleh user lain'];
        }
        
        // Update database
        $updated = db()->update('epic_users', 
            ['affiliate_code' => $affiliateCode], 
            ['id' => $userId]
        );
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Gagal menyimpan ke database'];
        }
        
        // Log activity
        epic_log_activity($userId, 'affiliate_code_updated', 'Affiliate code updated to: ' . $affiliateCode);
        
        return [
            'success' => true, 
            'message' => 'Kode affiliasi berhasil disimpan',
            'affiliate_code' => $affiliateCode
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()];
    }
}

/**
 * Landing Page Manager
 */
function epic_admin_landing_page_manager($segments) {
    $action = $segments[3] ?? '';
    
    switch ($action) {
        case 'add':
            // Handle add landing page using new layout system
            include __DIR__ . '/../themes/modern/admin/landing-page-manager-add.php';
            break;
            
        case 'edit':
            // Handle edit landing page
            $page_id = $segments[4] ?? null;
            if (!$page_id || !is_numeric($page_id)) {
                epic_route_404();
                return;
            }
            
            // TODO: Create edit page using new layout system
            $data = [
                'page_title' => 'Edit Landing Page - ' . epic_setting('site_name'),
                'current_page' => 'landing-page-manager',
                'page_id' => (int)$page_id
            ];
            epic_render_template('admin/landing-page-manager-edit', $data);
            break;
            
        default:
            // Default landing page list using new layout system
            include __DIR__ . '/../themes/modern/admin/landing-page-manager.php';
            break;
    }
}

// Include layout helper for admin pages
require_once __DIR__ . '/../themes/modern/admin/layout-helper.php';

/**
 * Admin Member Area - Access to member dashboard from admin
 */
function epic_admin_member_area($segments) {
    $page = $segments[2] ?? 'home';
    
    // Check admin access
    $user = epic_current_user();
    if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
        epic_route_403();
        return;
    }
    
    switch ($page) {
        case 'home':
            epic_admin_member_area_home();
            break;
            
        case 'profile':
            epic_admin_member_area_profile();
            break;
            
        case 'prospects':
            epic_admin_member_area_prospects();
            break;
            
        case 'bonus':
            epic_admin_member_area_bonus();
            break;
            
        case 'products':
            epic_admin_member_area_products();
            break;
            
        case 'orders':
            epic_admin_member_area_orders();
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Admin Member Area - Home Dashboard
 */
function epic_admin_member_area_home() {
    // Create admin wrapper for member home
    $layout_data = [
        'page_title' => 'Member Area - Home Dashboard',
        'header_title' => 'Member Area - Home Dashboard',
        'current_page' => 'member-area',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Member Area', 'url' => epic_url('admin/member-area')],
            ['text' => 'Home Dashboard']
        ],
        'content_file' => __DIR__ . '/../themes/modern/admin/content/member-area-home.php'
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}

/**
 * Admin Member Area - Profile
 */
function epic_admin_member_area_profile() {
    $success = null;
    $error = null;
    
    // Handle password change request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
        try {
            $member_id = (int)$_POST['member_id'];
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (empty($member_id)) {
                throw new Exception('Member ID tidak valid');
            }
            
            if (empty($new_password)) {
                throw new Exception('Password baru wajib diisi');
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception('Password minimal 6 karakter');
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception('Password baru dan konfirmasi password tidak cocok');
            }
            
            // Check if member exists
            $member = epic_get_user($member_id);
            if (!$member) {
                throw new Exception('Member tidak ditemukan');
            }
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updated = db()->query(
                "UPDATE epic_users SET password = ?, updated_at = NOW() WHERE id = ?",
                [$hashed_password, $member_id]
            );
            
            if ($updated) {
                // Log activity
                epic_log_activity(
                    epic_get_current_user_id(),
                    'admin_change_member_password',
                    "Changed password for member: {$member['name']} (ID: {$member_id})"
                );
                
                $success = 'Password member berhasil diubah';
                
                // Return JSON response for AJAX
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $success]);
                    return;
                }
            } else {
                throw new Exception('Gagal mengubah password');
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            
            // Return JSON response for AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                return;
            }
        }
    }
    
    $layout_data = [
        'page_title' => 'Member Area - Edit Profil',
        'header_title' => 'Member Area - Edit Profil',
        'current_page' => 'member-area',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Member Area', 'url' => epic_url('admin/member-area')],
            ['text' => 'Edit Profil']
        ],
        'content_file' => __DIR__ . '/../themes/modern/admin/content/member-area-profile.php',
        'success' => $success,
        'error' => $error
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}

/**
 * Admin Member Area - Prospects
 */
function epic_admin_member_area_prospects() {
    $layout_data = [
        'page_title' => 'Member Area - Prospek',
        'header_title' => 'Member Area - Prospek',
        'current_page' => 'member-area',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Member Area', 'url' => epic_url('admin/member-area')],
            ['text' => 'Prospek']
        ],
        'content_file' => __DIR__ . '/../themes/modern/admin/content/member-area-prospects.php'
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}

/**
 * Admin Member Area - Bonus Cash
 */
function epic_admin_member_area_bonus() {
    $layout_data = [
        'page_title' => 'Member Area - Bonus Cash',
        'header_title' => 'Member Area - Bonus Cash',
        'current_page' => 'member-area',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Member Area', 'url' => epic_url('admin/member-area')],
            ['text' => 'Bonus Cash']
        ],
        'content_file' => __DIR__ . '/../themes/modern/admin/content/member-area-bonus.php'
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}

/**
 * Admin Member Area - Products
 */
function epic_admin_member_area_products() {
    $layout_data = [
        'page_title' => 'Member Area - Akses Produk',
        'header_title' => 'Member Area - Akses Produk',
        'current_page' => 'member-area',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Member Area', 'url' => epic_url('admin/member-area')],
            ['text' => 'Akses Produk']
        ],
        'content_file' => __DIR__ . '/../themes/modern/admin/content/member-area-products.php'
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}

/**
 * Admin Member Area - Orders
 */
function epic_admin_member_area_orders() {
    $layout_data = [
        'page_title' => 'Member Area - History Order',
        'header_title' => 'Member Area - History Order',
        'current_page' => 'member-area',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Member Area', 'url' => epic_url('admin/member-area')],
            ['text' => 'History Order']
        ],
        'content_file' => __DIR__ . '/../themes/modern/admin/content/member-area-orders.php'
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}

/**
 * EPIS Account Management
 */
function epic_admin_epis($segments) {
    $action = $segments[3] ?? '';
    
    switch ($action) {
        case 'view':
            $epis_id = $segments[4] ?? null;
            if (!$epis_id || !is_numeric($epis_id)) {
                epic_route_404();
                return;
            }
            // TODO: Create EPIS view page
            include __DIR__ . '/../themes/modern/admin/epis-view.php';
            break;
            
        case 'network':
            $epis_id = $segments[4] ?? null;
            if (!$epis_id || !is_numeric($epis_id)) {
                epic_route_404();
                return;
            }
            // TODO: Create EPIS network management page
            include __DIR__ . '/../themes/modern/admin/epis-network.php';
            break;
            
        case 'commission-rules':
            // TODO: Create commission rules management page
            include __DIR__ . '/../themes/modern/admin/epis-commission-rules.php';
            break;
            
        case 'invitations':
            // TODO: Create registration invitations management page
            include __DIR__ . '/../themes/modern/admin/epis-invitations.php';
            break;
            
        default:
            // Default EPIS management page
            include __DIR__ . '/../themes/modern/admin/epis-management.php';
            break;
    }
}

/**
 * LMS Products Management
 */
function epic_admin_lms_products($segments) {
    $action = $segments[2] ?? '';
    
    // Check for preview parameter in query string
    if (isset($_GET['preview']) && is_numeric($_GET['preview'])) {
        include __DIR__ . '/../themes/modern/admin/lms-product-preview.php';
        return;
    }
    
    switch ($action) {
        case 'add':
            // Add new LMS product
            include __DIR__ . '/../themes/modern/admin/lms-products-add.php';
            break;
            
        case 'edit':
            $product_id = $segments[3] ?? null;
            if (!$product_id || !is_numeric($product_id)) {
                epic_route_404();
                return;
            }
            $_GET['id'] = $product_id;
            $_GET['action'] = 'edit';
            include __DIR__ . '/../themes/modern/admin/lms-products.php';
            break;
            
        case 'modules':
            $product_id = $segments[3] ?? null;
            if (!$product_id || !is_numeric($product_id)) {
                epic_route_404();
                return;
            }
            $_GET['product_id'] = $product_id;
            include __DIR__ . '/../themes/modern/admin/lms-products-modules.php';
            break;
            
        case 'preview':
            $product_id = $segments[3] ?? null;
            if (!$product_id || !is_numeric($product_id)) {
                epic_route_404();
                return;
            }
            $_GET['preview'] = $product_id;
            include __DIR__ . '/../themes/modern/admin/lms-product-preview.php';
            break;
            
        default:
            // Default LMS products management page
            include __DIR__ . '/../themes/modern/admin/lms-products.php';
            break;
    }
}

?>