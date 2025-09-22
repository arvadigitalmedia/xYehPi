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

$success = '';
$error = '';

// Handle landing page actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            $page_id = (int)($_POST['page_id'] ?? 0);
            if ($page_id > 0) {
                try {
                    db()->delete('landing_pages', 'id = ?', [$page_id]);
                    epic_log_activity($user['id'], 'delete_landing_page', "Landing page ID: {$page_id} deleted", 'landing_page', $page_id);
                    $success = 'Landing page berhasil dihapus.';
                } catch (Exception $e) {
                    error_log('Delete landing page error: ' . $e->getMessage());
                    $error = 'Terjadi kesalahan saat menghapus landing page.';
                }
            }
            break;
            
        case 'toggle_status':
            $page_id = (int)($_POST['page_id'] ?? 0);
            $status = (int)($_POST['status'] ?? 0);
            if ($page_id > 0) {
                try {
                    db()->update('landing_pages', ['is_active' => $status], 'id = ?', [$page_id]);
                    $status_text = $status ? 'diaktifkan' : 'dinonaktifkan';
                    epic_log_activity($user['id'], 'toggle_landing_page_status', "Landing page ID: {$page_id} {$status_text}", 'landing_page', $page_id);
                    $success = "Landing page berhasil {$status_text}.";
                } catch (Exception $e) {
                    error_log('Toggle landing page status error: ' . $e->getMessage());
                    $error = 'Terjadi kesalahan saat mengubah status landing page.';
                }
            }
            break;
    }
}

// Handle GET actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $page_id = (int)($_GET['id'] ?? 0);
    
    switch ($action) {
        case 'delete':
            if ($page_id > 0) {
                try {
                    db()->delete('landing_pages', 'id = ?', [$page_id]);
                    epic_log_activity($user['id'], 'delete_landing_page', "Landing page ID: {$page_id} deleted", 'landing_page', $page_id);
                    epic_redirect(epic_url('admin/manage/landing-page-manager?success=' . urlencode('Landing page berhasil dihapus.')));
                } catch (Exception $e) {
                    error_log('Delete landing page error: ' . $e->getMessage());
                    epic_redirect(epic_url('admin/manage/landing-page-manager?error=' . urlencode('Terjadi kesalahan saat menghapus landing page.')));
                }
            }
            break;
            
        case 'activate':
        case 'deactivate':
            if ($page_id > 0) {
                $status = ($action === 'activate') ? 1 : 0;
                try {
                    db()->update('landing_pages', ['is_active' => $status], 'id = ?', [$page_id]);
                    $status_text = $status ? 'diaktifkan' : 'dinonaktifkan';
                    epic_log_activity($user['id'], 'toggle_landing_page_status', "Landing page ID: {$page_id} {$status_text}", 'landing_page', $page_id);
                    epic_redirect(epic_url('admin/manage/landing-page-manager?success=' . urlencode("Landing page berhasil {$status_text}.")));
                } catch (Exception $e) {
                    error_log('Toggle landing page status error: ' . $e->getMessage());
                    epic_redirect(epic_url('admin/manage/landing-page-manager?error=' . urlencode('Terjadi kesalahan saat mengubah status landing page.')));
                }
            }
            break;
    }
}

// Get URL parameters
$success = $_GET['success'] ?? $success;
$error = $_GET['error'] ?? $error;
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Build query conditions
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(page_title LIKE ? OR page_slug LIKE ? OR page_description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get landing pages
try {
    $landing_pages = db()->select(
        "SELECT lp.*, u.name as user_name, u.email as user_email,
                DATE_FORMAT(lp.created_at, '%d %M %Y') as created_date,
                DATE_FORMAT(lp.updated_at, '%d %M %Y %H:%i') as updated_date
         FROM " . db()->table('landing_pages') . " lp 
         LEFT JOIN " . db()->table('users') . " u ON lp.user_id = u.id 
         {$where_clause}
         ORDER BY lp.created_at DESC 
         LIMIT {$per_page} OFFSET {$offset}",
        $params
    );
    
    // Get total count for pagination
    $total_count = db()->selectValue(
        "SELECT COUNT(*) FROM " . db()->table('landing_pages') . " lp {$where_clause}",
        $params
    ) ?: 0;
    
    $total_pages = ceil($total_count / $per_page);
    
} catch (Exception $e) {
    error_log('Landing pages query error: ' . $e->getMessage());
    $landing_pages = [];
    $total_count = 0;
    $total_pages = 0;
}

// Get statistics
try {
    $stats = [
        'total_pages' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('landing_pages')) ?: 0,
        'active_pages' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('landing_pages') . " WHERE is_active = 1") ?: 0,
        'total_visits' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('landing_page_visits')) ?: 0,
        'total_users' => db()->selectValue("SELECT COUNT(DISTINCT user_id) FROM " . db()->table('landing_pages')) ?: 0
    ];
    
    $stats['conversion_rate'] = $stats['total_visits'] > 0 ? round(($stats['active_pages'] / $stats['total_visits']) * 100, 1) : 0;
    
} catch (Exception $e) {
    error_log('Landing page stats error: ' . $e->getMessage());
    $stats = [
        'total_pages' => 0,
        'active_pages' => 0,
        'total_visits' => 0,
        'total_users' => 0,
        'conversion_rate' => 0
    ];
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Landing Page Manager - Admin',
    'current_page' => 'landing-page-manager',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin/manage')],
        ['text' => 'Landing Page Manager']
    ],
    'content_file' => __DIR__ . '/content/landing-page-manager-content.php',
    'user' => $user,
    'success' => $success,
    'error' => $error,
    'landing_pages' => $landing_pages,
    'stats' => $stats,
    'search' => $search,
    'page' => $page,
    'total_pages' => $total_pages,
    'total_count' => $total_count
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>