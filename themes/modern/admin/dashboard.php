<?php
/**
 * EPIC Hub Admin Dashboard
 * Menggunakan layout global yang baru
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Check admin access sudah dilakukan di layout helper
$user = epic_current_user();

// Get dashboard statistics
$stats = [
    'total_users' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users')) ?: 0,
    'active_users' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE status = 'active'") ?: 0,
    'premium_users' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE role = 'premium'") ?: 0,
    'total_products' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('products')) ?: 0,
    'active_products' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('products') . " WHERE status = 'active'") ?: 0,
    'total_orders' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('orders')) ?: 0,
    'pending_orders' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('orders') . " WHERE status = 'pending'") ?: 0,
    'paid_orders' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('orders') . " WHERE status = 'paid'") ?: 0,
    'total_revenue' => db()->selectValue(
        "SELECT SUM(amount) FROM " . db()->table('orders') . " WHERE status = 'paid'"
    ) ?: 0,
    'total_commissions' => db()->selectValue(
        "SELECT SUM(amount_in) FROM " . db()->table('transactions') . " WHERE type = 'commission' AND status = 'completed'"
    ) ?: 0
];

// Get recent activities
$recent_orders = db()->select(
    "SELECT o.*, u.name as user_name, p.name as product_name
     FROM " . db()->table('orders') . " o
     LEFT JOIN " . db()->table('users') . " u ON o.user_id = u.id
     LEFT JOIN " . db()->table('products') . " p ON o.product_id = p.id
     ORDER BY o.created_at DESC
     LIMIT 10"
) ?: [];

$recent_users = db()->select(
    "SELECT * FROM " . db()->table('users') . " ORDER BY created_at DESC LIMIT 10"
) ?: [];

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Dashboard - EPIC Hub Admin',
    'header_title' => 'Dashboard',
    'current_page' => 'dashboard',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Dashboard']
    ],
    'content_file' => __DIR__ . '/content/dashboard-content.php',
    'additional_js' => ['https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js'],
    // Pass variables ke content
    'stats' => $stats,
    'recent_orders' => $recent_orders,
    'recent_users' => $recent_users
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>