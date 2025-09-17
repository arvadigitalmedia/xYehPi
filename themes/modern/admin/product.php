<?php
/**
 * EPIC Hub Admin Product Management
 * Halaman produk dengan layout global admin
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Check admin access sudah dilakukan di layout helper
$user = epic_current_user();

// Pagination settings
$jmlperpage = 12; // 4 kolom x 3 baris
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
    $start = ($_GET['start'] - 1) * $jmlperpage;
    $page = $_GET['start'];
} else {
    $start = 0;
    $page = 1;
}

// Search functionality
$where = '1=1';
$params = [];
if (isset($_GET['cari']) && !empty($_GET['cari'])) {
    $search = trim($_GET['cari']);
    $where .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

// Status filter
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where .= " AND status = ?";
    $params[] = $_GET['status'];
}

// Get products with pagination
$products = db()->select(
    "SELECT * FROM epic_products 
     WHERE {$where} 
     ORDER BY name ASC 
     LIMIT {$jmlperpage} OFFSET {$start}",
    $params
) ?: [];

// Get total count for pagination
$total_products = db()->selectValue(
    "SELECT COUNT(*) FROM epic_products WHERE {$where}",
    $params
) ?: 0;

$jmlpage = ceil($total_products / $jmlperpage);

// Handle product actions
$success = '';
$error = '';

if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    try {
        switch ($action) {
            case 'activate':
                db()->update('epic_products', 
                    ['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ?', [$product_id]
                );
                $success = 'Product berhasil diaktifkan.';
                break;
                
            case 'deactivate':
                db()->update('epic_products', 
                    ['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ?', [$product_id]
                );
                $success = 'Product berhasil dinonaktifkan.';
                break;
        }
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Handle messages from redirect
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Product Management - EPIC Hub Admin',
    'header_title' => 'Product Management',
    'current_page' => 'product',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin')],
        ['text' => 'Product']
    ],
    'page_actions' => [
        [
            'type' => 'link',
            'url' => function_exists('epic_url') ? epic_url('admin/manage/product/add') : 'add',
            'text' => 'New Product',
            'icon' => 'plus',
            'class' => 'primary'
        ]
    ],
    'content_file' => __DIR__ . '/content/product-content.php',
    // Pass variables ke content
    'products' => $products,
    'search' => $search ?? '',
    'status_filter' => $_GET['status'] ?? '',
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $jmlpage,
        'total_items' => $total_products,
        'has_prev' => $page > 1,
        'has_next' => $page < $jmlpage,
        'prev_url' => $page > 1 ? '?' . http_build_query(array_merge($_GET, ['start' => $page - 1])) : null,
        'next_url' => $page < $jmlpage ? '?' . http_build_query(array_merge($_GET, ['start' => $page + 1])) : null
    ],
    'success' => $success,
    'error' => $error,
    'total_products' => $total_products,
    'jmlperpage' => $jmlperpage,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>