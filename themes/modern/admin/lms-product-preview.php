<?php
/**
 * EPIC Hub Admin LMS Product Preview
 * Halaman preview produk LMS untuk admin
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Check admin access
$user = epic_current_user();
if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
    epic_route_403();
    return;
}

// Get product ID from URL parameter
$product_id = $_GET['preview'] ?? $_GET['id'] ?? null;

if (!$product_id || !is_numeric($product_id)) {
    epic_redirect(epic_url('admin/manage/product'));
    exit;
}

// Get product data
$product = db()->selectOne(
    "SELECT * FROM epic_products WHERE id = ?",
    [$product_id]
);

if (!$product) {
    epic_redirect(epic_url('admin/manage/product'));
    exit;
}

// Get product modules if it's an LMS product
$modules = [];
if ($product['category'] === 'lms') {
    $modules = db()->select(
        "SELECT * FROM epic_product_modules 
         WHERE product_id = ? 
         ORDER BY sort_order ASC, created_at ASC",
        [$product_id]
    ) ?: [];
}

// Get product statistics
$stats = [
    'total_sales' => db()->selectValue(
        "SELECT COUNT(*) FROM epic_orders WHERE product_id = ? AND status = 'paid'",
        [$product_id]
    ) ?: 0,
    'total_revenue' => db()->selectValue(
        "SELECT SUM(amount) FROM epic_orders WHERE product_id = ? AND status = 'paid'",
        [$product_id]
    ) ?: 0,
    'active_users' => db()->selectValue(
        "SELECT COUNT(DISTINCT user_id) FROM epic_user_progress WHERE product_id = ?",
        [$product_id]
    ) ?: 0
];

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Preview: ' . htmlspecialchars($product['name']) . ' - EPIC Hub Admin',
    'header_title' => 'Product Preview',
    'current_page' => 'product',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin')],
        ['text' => 'Product', 'url' => epic_url('admin/manage/product')],
        ['text' => 'Preview: ' . htmlspecialchars($product['name'])]
    ],
    'page_actions' => [
        [
            'type' => 'link',
            'url' => epic_url('admin/manage/product'),
            'text' => 'Back to Products',
            'icon' => 'arrow-left',
            'class' => 'secondary'
        ],
        [
            'type' => 'link',
            'url' => epic_url('admin/manage/product/edit/' . $product_id),
            'text' => 'Edit Product',
            'icon' => 'edit-2',
            'class' => 'primary'
        ]
    ],
    'content_file' => __DIR__ . '/content/lms-product-preview-content.php',
    // Pass variables ke content
    'product' => $product,
    'modules' => $modules,
    'stats' => $stats
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>