<?php
/**
 * EPIC Hub Admin Order Management
 * Menggunakan layout global yang baru
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Check admin access sudah dilakukan di layout helper
$user = epic_current_user();

$success = '';
$error = '';

// Handle order actions
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    try {
        switch ($action) {
            case 'approve':
                $result = db()->update('orders', 
                    ['status' => 'paid', 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ? AND status = "pending"', [$order_id]
                );
                if ($result) {
                    $success = 'Order berhasil disetujui.';
                    epic_log_activity($user['id'], 'order_approved', [
                        'order_id' => $order_id,
                        'action' => 'approve'
                    ]);
                } else {
                    $error = 'Gagal menyetujui order atau order sudah diproses.';
                }
                break;
                
            case 'reject':
                $result = db()->update('orders', 
                    ['status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ? AND status = "pending"', [$order_id]
                );
                if ($result) {
                    $success = 'Order berhasil ditolak.';
                    epic_log_activity($user['id'], 'order_rejected', [
                        'order_id' => $order_id,
                        'action' => 'reject'
                    ]);
                } else {
                    $error = 'Gagal menolak order atau order sudah diproses.';
                }
                break;
                
            case 'delete':
                // Get order info first
                $order = db()->selectOne(
                    "SELECT * FROM " . db()->table('orders') . " WHERE id = ?",
                    [$order_id]
                );
                
                if ($order) {
                    // Delete from database
                    db()->delete('orders', 'id = ?', [$order_id]);
                    
                    $success = 'Order berhasil dihapus.';
                    epic_log_activity($user['id'], 'order_deleted', [
                        'order_id' => $order_id,
                        'order_amount' => $order['amount']
                    ]);
                } else {
                    $error = 'Order tidak ditemukan.';
                }
                break;
        }
    } catch (Exception $e) {
        error_log('Order action error: ' . $e->getMessage());
        $error = 'Terjadi kesalahan saat memproses order.';
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Build query conditions
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR p.name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($status_filter)) {
    $conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_filter)) {
    $conditions[] = "DATE(o.created_at) = ?";
    $params[] = $date_filter;
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get total count for pagination
$total_count = db()->selectValue(
    "SELECT COUNT(*) FROM " . db()->table('orders') . " o
     LEFT JOIN " . db()->table('users') . " u ON u.id = o.user_id
     LEFT JOIN " . db()->table('products') . " p ON p.id = o.product_id
     {$where_clause}",
    $params
);

$total_pages = ceil($total_count / $per_page);

// Get orders data
$orders = db()->select(
    "SELECT o.*, u.name as user_name, u.email as user_email, p.name as product_name
     FROM " . db()->table('orders') . " o
     LEFT JOIN " . db()->table('users') . " u ON u.id = o.user_id
     LEFT JOIN " . db()->table('products') . " p ON p.id = o.product_id
     {$where_clause}
     ORDER BY o.created_at DESC
     LIMIT {$per_page} OFFSET {$offset}",
    $params
);

// Get statistics
$stats = [
    'total' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('orders')),
    'pending' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('orders') . " WHERE status = 'pending'"),
    'paid' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('orders') . " WHERE status = 'paid'"),
    'cancelled' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('orders') . " WHERE status = 'cancelled'"),
    'total_revenue' => db()->selectValue(
        "SELECT SUM(amount) FROM " . db()->table('orders') . " WHERE status = 'paid'"
    ) ?: 0
];

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Order Management - EPIC Hub Admin',
    'header_title' => 'Order Management',
    'current_page' => 'order',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin/manage')],
        ['text' => 'Order']
    ],
    'content_file' => __DIR__ . '/content/order-content.php',
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'orders' => $orders,
    'stats' => $stats,
    'search' => $search,
    'status_filter' => $status_filter,
    'date_filter' => $date_filter,
    'page' => $page,
    'total_pages' => $total_pages,
    'total_count' => $total_count
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>