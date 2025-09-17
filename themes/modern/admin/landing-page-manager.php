<?php
/**
 * EPIC Hub Landing Page Manager
 * Menggunakan layout global yang baru
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Check admin access sudah dilakukan di layout helper
$user = epic_current_user();

// Handle landing page actions
$success = '';
$error = '';

if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $page_id = (int)$_GET['activate'];
    try {
        db()->update('landing_pages', [
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$page_id]);
        $success = "Landing page berhasil diaktifkan!";
    } catch (Exception $e) {
        error_log('Activate landing page error: ' . $e->getMessage());
        $error = "Terjadi kesalahan saat mengaktifkan landing page.";
    }
}

if (isset($_GET['deactivate']) && is_numeric($_GET['deactivate'])) {
    $page_id = (int)$_GET['deactivate'];
    try {
        db()->update('landing_pages', [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$page_id]);
        $success = "Landing page berhasil dinonaktifkan!";
    } catch (Exception $e) {
        error_log('Deactivate landing page error: ' . $e->getMessage());
        $error = "Terjadi kesalahan saat menonaktifkan landing page.";
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $page_id = (int)$_GET['delete'];
    try {
        // Get landing page info first
        $landing_page = db()->selectOne(
            "SELECT * FROM " . db()->table('landing_pages') . " WHERE id = ?",
            [$page_id]
        );
        
        if ($landing_page) {
            // Delete image file if exists
            if (!empty($landing_page['page_image'])) {
                $image_path = __DIR__ . '/../../../../uploads/landing-pages/' . $landing_page['page_image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // Delete from database
            db()->delete('landing_pages', 'id = ?', [$page_id]);
            
            // Log activity
            epic_log_activity($user['id'], 'delete_landing_page', [
                'page_id' => $page_id,
                'page_title' => $landing_page['page_title']
            ]);
            
            $success = "Landing page berhasil dihapus!";
        } else {
            $error = "Landing page tidak ditemukan.";
        }
    } catch (Exception $e) {
        error_log('Delete landing page error: ' . $e->getMessage());
        $error = "Terjadi kesalahan saat menghapus landing page.";
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$method_filter = $_GET['method'] ?? '';
$user_filter = $_GET['user'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query conditions
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(lp.page_title LIKE ? OR lp.page_slug LIKE ? OR u.name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($status_filter === 'active') {
    $conditions[] = "lp.is_active = 1";
} elseif ($status_filter === 'inactive') {
    $conditions[] = "lp.is_active = 0";
}

if (!empty($method_filter)) {
    $conditions[] = "lp.method = ?";
    $params[] = $method_filter;
}

if (!empty($user_filter)) {
    $conditions[] = "lp.user_id = ?";
    $params[] = $user_filter;
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get total count for pagination
$total_count = db()->selectValue(
    "SELECT COUNT(*) FROM " . db()->table('landing_pages') . " lp
     LEFT JOIN " . db()->table('users') . " u ON u.id = lp.user_id
     {$where_clause}",
    $params
);

$total_pages = ceil($total_count / $per_page);

// Get landing pages data
$landing_pages = db()->select(
    "SELECT lp.*, u.name as user_name, u.referral_code,
            (SELECT COUNT(*) FROM " . db()->table('landing_page_visits') . " lpv WHERE lpv.landing_page_id = lp.id) as visit_count
     FROM " . db()->table('landing_pages') . " lp
     LEFT JOIN " . db()->table('users') . " u ON u.id = lp.user_id
     {$where_clause}
     ORDER BY lp.created_at DESC
     LIMIT {$per_page} OFFSET {$offset}",
    $params
);

// Get statistics
$stats = [
    'total' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('landing_pages')),
    'active' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('landing_pages') . " WHERE is_active = 1"),
    'total_visits' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('landing_page_visits')),
    'iframe_method' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('landing_pages') . " WHERE method = 1")
];

// Get all users for filter dropdown
$users = db()->select(
    "SELECT id, name FROM " . db()->table('users') . " WHERE status = 'active' ORDER BY name ASC"
);

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Landing Page Manager - EPIC Hub Admin',
    'header_title' => 'Landing Page Manager',
    'current_page' => 'landing-page-manager',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin/manage')],
        ['text' => 'Landing Page Manager']
    ],
    'page_actions' => [
        epic_link_action('New Landing Page', epic_url('admin/manage/landing-page-manager/add'), 'plus')
    ],
    'content_file' => __DIR__ . '/content/landing-page-manager-content.php',
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'landing_pages' => $landing_pages,
    'stats' => $stats,
    'users' => $users,
    'search' => $search,
    'status_filter' => $status_filter,
    'method_filter' => $method_filter,
    'user_filter' => $user_filter,
    'page' => $page,
    'total_pages' => $total_pages,
    'total_count' => $total_count
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>