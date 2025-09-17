<?php
/**
 * EPIC Hub Admin Member Management
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

// Handle member actions
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $member_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    try {
        switch ($action) {
            case 'activate':
                $result = db()->update('users', 
                    ['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ?', [$member_id]
                );
                if ($result) {
                    $success = 'Member berhasil diaktivasi.';
                    epic_log_activity($user['id'], 'member_activated', "Member ID {$member_id} activated", 'user', $member_id);
                } else {
                    $error = 'Gagal mengaktivasi member.';
                }
                break;
                
            case 'deactivate':
                $result = db()->update('users', 
                    ['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ?', [$member_id]
                );
                if ($result) {
                    $success = 'Member berhasil dinonaktifkan.';
                    epic_log_activity($user['id'], 'member_deactivated', "Member ID {$member_id} deactivated", 'user', $member_id);
                } else {
                    $error = 'Gagal menonaktifkan member.';
                }
                break;
                
            case 'delete':
                // Get member info first
                $member = db()->selectOne(
                    "SELECT * FROM " . db()->table('users') . " WHERE id = ?",
                    [$member_id]
                );
                
                if ($member) {
                    // Delete profile photo if exists
                    if (!empty($member['profile_photo'])) {
                        $photo_path = __DIR__ . '/../../../../uploads/profiles/' . $member['profile_photo'];
                        if (file_exists($photo_path)) {
                            unlink($photo_path);
                        }
                    }
                    
                    // Delete from database
                    db()->delete('users', 'id = ?', [$member_id]);
                    
                    $success = 'Member berhasil dihapus.';
                    epic_log_activity($user['id'], 'member_deleted', "Member {$member['name']} (ID: {$member_id}) deleted", 'user', $member_id);
                } else {
                    $error = 'Member tidak ditemukan.';
                }
                break;
        }
    } catch (Exception $e) {
        error_log('Member action error: ' . $e->getMessage());
        $error = 'Terjadi kesalahan saat memproses aksi member.';
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$role_filter = $_GET['role'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Build query conditions
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(name LIKE ? OR email LIKE ? OR referral_code LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($status_filter)) {
    $conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($role_filter)) {
    $conditions[] = "role = ?";
    $params[] = $role_filter;
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get total count for pagination
$total_count = db()->selectValue(
    "SELECT COUNT(*) FROM " . db()->table('users') . " {$where_clause}",
    $params
);

$total_pages = ceil($total_count / $per_page);

// Get members data
$members = db()->select(
    "SELECT *
     FROM " . db()->table('users') . "
     {$where_clause}
     ORDER BY created_at DESC
     LIMIT {$per_page} OFFSET {$offset}",
    $params
);

// Get statistics
$stats = [
    'total' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users')),
    'active' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE status = 'active'"),
    'inactive' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE status = 'inactive'"),
    'admin' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE role IN ('admin', 'super_admin')"),
    'premium' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE role = 'premium'")
];

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Member Management - EPIC Hub Admin',
    'header_title' => 'Member Management',
    'current_page' => 'member',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin/manage')],
        ['text' => 'Member']
    ],
    'page_actions' => [
        epic_link_action('Add New Member', epic_url('admin/manage/member/add'), 'plus')
    ],
    'content_file' => __DIR__ . '/content/member-content.php',
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'members' => $members,
    'stats' => $stats,
    'search' => $search,
    'status_filter' => $status_filter,
    'role_filter' => $role_filter,
    'page' => $page,
    'total_pages' => $total_pages,
    'total_count' => $total_count
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>