<?php
/**
 * EPIC Hub Admin Page Template
 * Template untuk membuat halaman admin baru dengan error handling yang konsisten
 * 
 * CARA PENGGUNAAN:
 * 1. Copy file ini dengan nama yang sesuai (misal: new-menu.php)
 * 2. Ganti [PAGE_NAME] dengan nama halaman yang sesuai
 * 3. Ganti [REQUIRED_ROLE] dengan role yang diperlukan (admin atau super_admin)
 * 4. Implementasikan logic bisnis di bagian yang ditandai
 * 5. Buat file content di folder content/ dengan nama yang sesuai
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper untuk error handling yang konsisten
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Initialize admin page dengan validasi yang proper
// Ganti 'admin' dengan 'super_admin' jika hanya super admin yang boleh akses
$init_result = epic_init_admin_page('[REQUIRED_ROLE]', 'admin/manage/[PAGE_NAME]');
$user = $init_result['user'];

$success = '';
$error = '';

// ========================================
// BAGIAN FORM PROCESSING
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submissions di sini
    // Contoh:
    /*
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'create':
                    // Logic untuk create
                    $success = 'Data berhasil dibuat';
                    break;
                case 'update':
                    // Logic untuk update
                    $success = 'Data berhasil diupdate';
                    break;
                case 'delete':
                    // Logic untuk delete
                    $success = 'Data berhasil dihapus';
                    break;
                default:
                    $error = 'Action tidak valid';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
    */
}

// ========================================
// BAGIAN DATA RETRIEVAL
// ========================================

// Get filter parameters
$search_query = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build WHERE conditions untuk filtering
$where_conditions = ['1=1']; // Always true condition
$params = [];

// Search filter
if (!empty($search_query)) {
    $where_conditions[] = "(column_name LIKE ?)";
    $params[] = "%{$search_query}%";
}

// Status filter
if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get main data dengan safe query
$data_items = epic_safe_db_query(
    "SELECT * FROM your_table 
     WHERE {$where_clause}
     ORDER BY created_at DESC
     LIMIT {$per_page} OFFSET {$offset}",
    $params,
    'select'
);

// Get total count untuk pagination
$total_count = epic_safe_db_query(
    "SELECT COUNT(*) FROM your_table WHERE {$where_clause}",
    $params,
    'selectValue'
);

// Calculate pagination
$total_pages = ceil($total_count / $per_page);

// Get statistics (opsional)
$stats = [
    'total_items' => epic_safe_db_query(
        "SELECT COUNT(*) FROM your_table",
        [],
        'selectValue'
    ),
    'active_items' => epic_safe_db_query(
        "SELECT COUNT(*) FROM your_table WHERE status = 'active'",
        [],
        'selectValue'
    ),
    // Tambahkan statistik lain sesuai kebutuhan
];

// ========================================
// BAGIAN LAYOUT DATA PREPARATION
// ========================================

// Prepare data untuk layout
$layout_data = [
    'page_title' => '[PAGE_NAME] Management - EPIC Hub Admin',
    'header_title' => '[PAGE_NAME] Management',
    'current_page' => '[PAGE_NAME]',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin')],
        ['text' => '[PAGE_NAME]']
    ],
    'content_file' => __DIR__ . '/content/[PAGE_NAME]-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'data_items' => $data_items,
    'stats' => $stats,
    'search_query' => $search_query,
    'status_filter' => $status_filter,
    'page' => $page,
    'per_page' => $per_page,
    'total_count' => $total_count,
    'total_pages' => $total_pages,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);

// ========================================
// CHECKLIST UNTUK IMPLEMENTASI:
// ========================================
/*

□ 1. Ganti [PAGE_NAME] dengan nama halaman yang sesuai
□ 2. Ganti [REQUIRED_ROLE] dengan role yang diperlukan
□ 3. Implementasikan form processing sesuai kebutuhan
□ 4. Sesuaikan query database dengan tabel yang benar
□ 5. Buat file content di content/[PAGE_NAME]-content.php
□ 6. Update routing di core/admin.php:
     function epic_admin_[PAGE_NAME]($segments) {
         include __DIR__ . '/../themes/modern/admin/[PAGE_NAME].php';
     }
□ 7. Test halaman untuk memastikan tidak ada error 500
□ 8. Test access control (login required, role permission)
□ 9. Test form functionality jika ada
□ 10. Test responsive design di berbagai device

CATATAN PENTING:
- Selalu gunakan epic_safe_db_query() untuk query database
- Selalu gunakan epic_init_admin_page() untuk validasi akses
- Selalu include routing-helper.php untuk error handling
- Gunakan prepared statements untuk mencegah SQL injection
- Validasi semua input dari user
- Berikan feedback yang jelas untuk user (success/error messages)

*/
?>