<?php
/**
 * EPIC Hub - Order Management API
 * Handles AJAX requests for order operations
 */

require_once '../../bootstrap.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Check authentication
    if (!epic_is_authenticated()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    $user = epic_get_current_user();
    if (!$user || $user['role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required'
        ]);
        exit;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_orders':
            handleGetOrders();
            break;
            
        case 'process_order':
            handleProcessOrder();
            break;
            
        case 'cancel_order':
            handleCancelOrder();
            break;
            
        case 'delete_order':
            handleDeleteOrder();
            break;
            
        case 'get_order_details':
            handleGetOrderDetails();
            break;
            
        case 'bulk_action':
            handleBulkAction();
            break;
            
        case 'export_orders':
            handleExportOrders();
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log('Order API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}

/**
 * Get orders with filtering and pagination
 */
function handleGetOrders() {
    try {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = min(100, max(10, (int)($_GET['per_page'] ?? 25)));
        $offset = ($page - 1) * $per_page;
        
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $date_from = $_GET['date_from'] ?? '';
        $date_to = $_GET['date_to'] ?? '';
        
        // Build where conditions
        $where_conditions = [];
        $params = [];
        
        if (!empty($search)) {
            $search_term = '%' . $search . '%';
            $where_conditions[] = "(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR p.name LIKE ?)";
            $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
        }
        
        if (!empty($status)) {
            $where_conditions[] = "o.status = ?";
            $params[] = $status;
        }
        
        if (!empty($date_from)) {
            $where_conditions[] = "DATE(o.created_at) >= ?";
            $params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $where_conditions[] = "DATE(o.created_at) <= ?";
            $params[] = $date_to;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Get total count
        $total_orders = db()->selectValue(
            "SELECT COUNT(*) FROM epic_orders o 
             LEFT JOIN epic_users u ON u.id = o.user_id 
             LEFT JOIN epic_products p ON p.id = o.product_id 
             {$where_clause}",
            $params
        );
        
        // Get orders
        $orders = db()->select(
            "SELECT o.*, u.name as customer_name, u.email as customer_email, 
                    p.name as product_name, p.slug as product_slug,
                    s.name as staff_name
             FROM epic_orders o 
             LEFT JOIN epic_users u ON u.id = o.user_id 
             LEFT JOIN epic_products p ON p.id = o.product_id 
             LEFT JOIN epic_users s ON s.id = o.staff_id
             {$where_clause}
             ORDER BY o.created_at DESC 
             LIMIT {$per_page} OFFSET {$offset}",
            $params
        );
        
        // Format orders
        $formatted_orders = array_map(function($order) {
            return [
                'id' => (int)$order['id'],
                'order_number' => $order['order_number'],
                'customer_name' => $order['customer_name'],
                'customer_email' => $order['customer_email'],
                'product_name' => $order['product_name'],
                'amount' => (float)$order['amount'],
                'unique_amount' => (float)$order['unique_amount'],
                'status' => $order['status'],
                'payment_method' => $order['payment_method'],
                'staff_name' => $order['staff_name'],
                'created_at' => $order['created_at'],
                'paid_at' => $order['paid_at'],
                'notes' => $order['notes']
            ];
        }, $orders);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'orders' => $formatted_orders,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total' => (int)$total_orders,
                    'total_pages' => ceil($total_orders / $per_page)
                ]
            ]
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Failed to get orders: ' . $e->getMessage());
    }
}

/**
 * Process an order (mark as paid)
 */
function handleProcessOrder() {
    try {
        $order_id = (int)($_POST['order_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$order_id) {
            throw new Exception('Order ID is required');
        }
        
        $user = epic_get_current_user();
        
        db()->beginTransaction();
        
        // Get order details
        $order = db()->selectOne(
            "SELECT o.*, u.name as customer_name, p.name as product_name 
             FROM epic_orders o 
             LEFT JOIN epic_users u ON u.id = o.user_id 
             LEFT JOIN epic_products p ON p.id = o.product_id 
             WHERE o.id = ? AND o.status = 'pending'",
            [$order_id]
        );
        
        if (!$order) {
            throw new Exception('Order not found or already processed');
        }
        
        // Update order status
        db()->update('epic_orders', [
            'status' => 'paid',
            'staff_id' => $user['id'],
            'paid_at' => date('Y-m-d H:i:s'),
            'notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$order_id]);
        
        // Update user status if needed
        if ($order['amount'] > 0) {
            db()->update('epic_users', [
                'status' => 'epic',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ? AND status IN ("pending", "free")', [$order['user_id']]);
        }
        
        // Create transaction record
        db()->insert('epic_transactions', [
            'order_id' => $order_id,
            'user_id' => $order['user_id'],
            'referrer_id' => $order['referrer_id'],
            'type' => 'sale',
            'amount_in' => $order['amount'],
            'status' => 'completed',
            'description' => 'Penjualan ' . $order['product_name'],
            'processed_by' => $user['id'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log activity
        db()->insert('epic_activity_logs', [
            'user_id' => $user['id'],
            'action' => 'order_processed',
            'description' => "Processed order #{$order['order_number']} for {$order['customer_name']}",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        db()->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Order #{$order['order_number']} has been processed successfully"
        ]);
        
    } catch (Exception $e) {
        db()->rollback();
        throw new Exception('Failed to process order: ' . $e->getMessage());
    }
}

/**
 * Cancel an order
 */
function handleCancelOrder() {
    try {
        $order_id = (int)($_POST['order_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        
        if (!$order_id) {
            throw new Exception('Order ID is required');
        }
        
        $user = epic_get_current_user();
        
        // Get order details
        $order = db()->selectOne(
            "SELECT * FROM epic_orders WHERE id = ? AND status = 'paid'",
            [$order_id]
        );
        
        if (!$order) {
            throw new Exception('Order not found or cannot be cancelled');
        }
        
        // Update order status
        db()->update('epic_orders', [
            'status' => 'cancelled',
            'staff_id' => $user['id'],
            'notes' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$order_id]);
        
        // Log activity
        db()->insert('epic_activity_logs', [
            'user_id' => $user['id'],
            'action' => 'order_cancelled',
            'description' => "Cancelled order #{$order['order_number']}",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => "Order #{$order['order_number']} has been cancelled"
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Failed to cancel order: ' . $e->getMessage());
    }
}

/**
 * Delete an order
 */
function handleDeleteOrder() {
    try {
        $order_id = (int)($_POST['order_id'] ?? 0);
        
        if (!$order_id) {
            throw new Exception('Order ID is required');
        }
        
        $user = epic_get_current_user();
        
        // Get order details for logging
        $order = db()->selectOne(
            "SELECT * FROM epic_orders WHERE id = ?",
            [$order_id]
        );
        
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        // Delete order (transactions will be cascade deleted)
        db()->delete('epic_orders', 'id = ?', [$order_id]);
        
        // Log activity
        db()->insert('epic_activity_logs', [
            'user_id' => $user['id'],
            'action' => 'order_deleted',
            'description' => "Deleted order #{$order['order_number']}",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => "Order #{$order['order_number']} has been deleted"
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Failed to delete order: ' . $e->getMessage());
    }
}

/**
 * Get detailed order information
 */
function handleGetOrderDetails() {
    try {
        $order_id = (int)($_GET['order_id'] ?? 0);
        
        if (!$order_id) {
            throw new Exception('Order ID is required');
        }
        
        $order = db()->selectOne(
            "SELECT o.*, 
                    u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                    p.name as product_name, p.slug as product_slug, p.price as product_price,
                    s.name as staff_name,
                    r.name as referrer_name
             FROM epic_orders o 
             LEFT JOIN epic_users u ON u.id = o.user_id 
             LEFT JOIN epic_products p ON p.id = o.product_id 
             LEFT JOIN epic_users s ON s.id = o.staff_id
             LEFT JOIN epic_users r ON r.id = o.referrer_id
             WHERE o.id = ?",
            [$order_id]
        );
        
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        // Get related transactions
        $transactions = db()->select(
            "SELECT * FROM epic_transactions 
             WHERE order_id = ? 
             ORDER BY created_at DESC",
            [$order_id]
        );
        
        echo json_encode([
            'success' => true,
            'data' => [
                'order' => $order,
                'transactions' => $transactions
            ]
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Failed to get order details: ' . $e->getMessage());
    }
}

/**
 * Handle bulk actions on multiple orders
 */
function handleBulkAction() {
    try {
        $action = $_POST['bulk_action'] ?? '';
        $order_ids = $_POST['order_ids'] ?? [];
        
        if (empty($action) || empty($order_ids)) {
            throw new Exception('Bulk action and order IDs are required');
        }
        
        if (!is_array($order_ids)) {
            $order_ids = explode(',', $order_ids);
        }
        
        $order_ids = array_map('intval', $order_ids);
        $order_ids = array_filter($order_ids);
        
        if (empty($order_ids)) {
            throw new Exception('Valid order IDs are required');
        }
        
        $user = epic_get_current_user();
        $processed_count = 0;
        
        db()->beginTransaction();
        
        foreach ($order_ids as $order_id) {
            switch ($action) {
                case 'process':
                    $updated = db()->update('epic_orders', [
                        'status' => 'paid',
                        'staff_id' => $user['id'],
                        'paid_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'id = ? AND status = "pending"', [$order_id]);
                    break;
                    
                case 'cancel':
                    $updated = db()->update('epic_orders', [
                        'status' => 'cancelled',
                        'staff_id' => $user['id'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'id = ? AND status = "paid"', [$order_id]);
                    break;
                    
                case 'delete':
                    $updated = db()->delete('epic_orders', 'id = ?', [$order_id]);
                    break;
                    
                default:
                    throw new Exception('Invalid bulk action');
            }
            
            if ($updated) {
                $processed_count++;
            }
        }
        
        // Log bulk activity
        db()->insert('epic_activity_logs', [
            'user_id' => $user['id'],
            'action' => 'bulk_order_' . $action,
            'description' => "Bulk {$action} on {$processed_count} orders",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        db()->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Bulk action completed. {$processed_count} orders processed."
        ]);
        
    } catch (Exception $e) {
        db()->rollback();
        throw new Exception('Failed to perform bulk action: ' . $e->getMessage());
    }
}

/**
 * Export orders to CSV
 */
function handleExportOrders() {
    try {
        $format = $_GET['format'] ?? 'csv';
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $date_from = $_GET['date_from'] ?? '';
        $date_to = $_GET['date_to'] ?? '';
        
        // Build where conditions (same as get_orders)
        $where_conditions = [];
        $params = [];
        
        if (!empty($search)) {
            $search_term = '%' . $search . '%';
            $where_conditions[] = "(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR p.name LIKE ?)";
            $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
        }
        
        if (!empty($status)) {
            $where_conditions[] = "o.status = ?";
            $params[] = $status;
        }
        
        if (!empty($date_from)) {
            $where_conditions[] = "DATE(o.created_at) >= ?";
            $params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $where_conditions[] = "DATE(o.created_at) <= ?";
            $params[] = $date_to;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Get all orders for export
        $orders = db()->select(
            "SELECT o.order_number, u.name as customer_name, u.email as customer_email, 
                    p.name as product_name, o.amount, o.unique_amount, o.status, 
                    o.payment_method, o.created_at, o.paid_at, s.name as staff_name
             FROM epic_orders o 
             LEFT JOIN epic_users u ON u.id = o.user_id 
             LEFT JOIN epic_products p ON p.id = o.product_id 
             LEFT JOIN epic_users s ON s.id = o.staff_id
             {$where_clause}
             ORDER BY o.created_at DESC",
            $params
        );
        
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, [
                'Order Number', 'Customer Name', 'Customer Email', 'Product Name', 
                'Amount', 'Unique Amount', 'Status', 'Payment Method', 
                'Order Date', 'Paid Date', 'Staff Name'
            ]);
            
            // CSV data
            foreach ($orders as $order) {
                fputcsv($output, [
                    $order['order_number'],
                    $order['customer_name'],
                    $order['customer_email'],
                    $order['product_name'],
                    $order['amount'],
                    $order['unique_amount'],
                    $order['status'],
                    $order['payment_method'],
                    $order['created_at'],
                    $order['paid_at'],
                    $order['staff_name']
                ]);
            }
            
            fclose($output);
        } else {
            echo json_encode([
                'success' => true,
                'data' => $orders
            ]);
        }
        
    } catch (Exception $e) {
        throw new Exception('Failed to export orders: ' . $e->getMessage());
    }
}
?>