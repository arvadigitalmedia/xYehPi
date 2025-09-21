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

// Handle success message from redirect
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Handle member actions
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $member_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    try {
        switch ($action) {
            case 'activate':
                // Log before update
                error_log("Attempting to activate member ID: {$member_id}");
                
                $result = db()->update(TABLE_USERS, 
                    ['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ?', [$member_id]
                );
                
                // Log result and verify
                error_log("Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
                
                if ($result) {
                    // Verify the update actually happened
                    $updated_member = db()->selectOne(
                        "SELECT status FROM " . db()->table('users') . " WHERE id = ?",
                        [$member_id]
                    );
                    error_log("Member status after update: " . ($updated_member['status'] ?? 'NOT_FOUND'));
                    
                    epic_log_activity($user['id'], 'member_activated', "Member ID {$member_id} activated", 'user', $member_id);
                    epic_redirect(epic_url('admin/manage/member?success=' . urlencode('Member berhasil diaktivasi.')));
                } else {
                    error_log("Failed to activate member ID: {$member_id}");
                    $error = 'Gagal mengaktivasi member.';
                }
                break;
                
            case 'deactivate':
                $result = db()->update(TABLE_USERS, 
                    ['status' => 'suspended', 'updated_at' => date('Y-m-d H:i:s')], 
                    'id = ?', [$member_id]
                );
                if ($result) {
                    epic_log_activity($user['id'], 'member_deactivated', "Member ID {$member_id} deactivated", 'user', $member_id);
                    epic_redirect(epic_url('admin/manage/member?success=' . urlencode('Member berhasil dinonaktifkan.')));
                } else {
                    $error = 'Gagal menonaktifkan member.';
                }
                break;
                
            case 'upgrade':
                // Use the safe upgrade function
                $upgrade_result = epic_safe_upgrade_to_epic($member_id, $user['id']);
                
                if ($upgrade_result['success']) {
                    $success_message = $upgrade_result['message'];
                    
                    // Add details about preserved data
                    $details = [];
                    if ($upgrade_result['referral_preserved']) {
                        $details[] = 'data referral terjaga';
                    }
                    if ($upgrade_result['supervisor_preserved']) {
                        $details[] = 'EPIS supervisor terjaga';
                    }
                    
                    if (!empty($details)) {
                        $success_message .= ' (' . implode(', ', $details) . ')';
                    }
                    
                    epic_redirect(epic_url('admin/manage/member?success=' . urlencode($success_message)));
                } else {
                    $error = $upgrade_result['message'];
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
                    
                    epic_log_activity($user['id'], 'member_deleted', "Member {$member['name']} (ID: {$member_id}) deleted", 'user', $member_id);
                    epic_redirect(epic_url('admin/manage/member?success=' . urlencode('Member berhasil dihapus.')));
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

// Filter berdasarkan status
if (!empty($status_filter) && $status_filter !== 'all') {
    if ($status_filter === 'active') {
        $conditions[] = "status IN ('free', 'epic', 'epis')";
    } elseif ($status_filter === 'inactive') {
        $conditions[] = "status IN ('pending', 'suspended', 'banned')";
    }
}

// Filter berdasarkan role/hierarchy level
if (!empty($role_filter) && $role_filter !== 'all') {
    if ($role_filter === 'free') {
        $conditions[] = "(hierarchy_level = 1 OR status = 'free')";
    } elseif ($role_filter === 'epic') {
        $conditions[] = "(hierarchy_level = 2 OR status = 'epic')";
    } elseif ($role_filter === 'epis') {
        $conditions[] = "(hierarchy_level = 3 OR status = 'epis')";
    } else {
        // Legacy role filter untuk admin, super_admin, dll
        $conditions[] = "role = ?";
        $params[] = $role_filter;
    }
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get total count for pagination
$total_count = db()->selectValue(
    "SELECT COUNT(*) FROM " . db()->table('users') . " {$where_clause}",
    $params
);

$total_pages = ceil($total_count / $per_page);

// Get members data with supervisor and sponsor info
// Check if sponsor_id column exists first
$sponsor_column_exists = false;
try {
    $columns = db()->select("DESCRIBE " . db()->table('users'));
    foreach ($columns as $column) {
        if ($column['Field'] === 'sponsor_id') {
            $sponsor_column_exists = true;
            break;
        }
    }
} catch (Exception $e) {
    // If describe fails, assume column doesn't exist
    $sponsor_column_exists = false;
}

if ($sponsor_column_exists) {
    // Query with sponsor_id column
    $members = db()->select(
        "SELECT u.*, 
                     supervisor.name as supervisor_name,
                     supervisor.referral_code as supervisor_code,
                     sponsor_user.name as sponsor_name,
                     sponsor_user.referral_code as sponsor_code
              FROM " . db()->table('users') . " u
              LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
              LEFT JOIN " . db()->table('users') . " sponsor_user ON u.sponsor_id = sponsor_user.id
         {$where_clause}
         ORDER BY u.created_at DESC
         LIMIT {$per_page} OFFSET {$offset}",
        $params
    );
} else {
    // Fallback query without sponsor_id column
    $members = db()->select(
        "SELECT u.*, 
                     supervisor.name as supervisor_name,
                     supervisor.referral_code as supervisor_code,
                     NULL as sponsor_name,
                     NULL as sponsor_code
              FROM " . db()->table('users') . " u
              LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
         {$where_clause}
         ORDER BY u.created_at DESC
         LIMIT {$per_page} OFFSET {$offset}",
        $params
    );
}

// Statistik berdasarkan hierarchy level dan status
$stats = [
    'total' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users'))['count'],
    'active' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users') . " WHERE status IN ('free', 'epic', 'epis')")['count'],
    'inactive' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users') . " WHERE status IN ('pending', 'suspended', 'banned')")['count'],
    'free_account' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users') . " WHERE hierarchy_level = 1 OR status = 'free'")['count'],
    'epic_account' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users') . " WHERE hierarchy_level = 2 OR status = 'epic'")['count'],
    'epis_account' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users') . " WHERE hierarchy_level = 3 OR status = 'epis'")['count'],
    'admin' => db()->selectOne("SELECT COUNT(*) as count FROM " . db()->table('users') . " WHERE role IN ('admin', 'super_admin')")['count']
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