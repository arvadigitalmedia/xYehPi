<?php
/**
 * EPIS Account Management - Admin Interface
 * Manage EPIS accounts and hierarchical system
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper untuk error handling yang konsisten
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Validate admin access dengan proper error handling
$user = epic_validate_admin_access('admin', 'admin/manage/epis');

// Validate system requirements
if (!epic_validate_system_requirements()) {
    epic_handle_403_error();
    exit;
}

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create_epis':
                    $target_user_id = (int)$_POST['user_id'];
                    $epis_data = [
                        'territory_name' => epic_sanitize($_POST['territory_name']),
                        'territory_description' => epic_sanitize($_POST['territory_description']),
                        'max_epic_recruits' => (int)$_POST['max_epic_recruits'],
                        'recruitment_commission_rate' => (float)$_POST['recruitment_commission_rate'],
                        'indirect_commission_rate' => (float)$_POST['indirect_commission_rate']
                    ];
                    
                    // Validate data
                    $validation_errors = epic_validate_epis_data($epis_data);
                    if (!empty($validation_errors)) {
                        throw new Exception(implode(', ', $validation_errors));
                    }
                    
                    $epis_id = epic_create_epis_account($target_user_id, $epis_data);
                    if ($epis_id) {
                        $success = 'EPIS account created successfully!';
                    } else {
                        throw new Exception('Failed to create EPIS account');
                    }
                    break;
                    
                case 'update_epis':
                    $epis_user_id = (int)$_POST['epis_user_id'];
                    
                    // Validation
                    $validation_errors = [];
                    
                    // Validate territory name
                    $territory_name = trim(epic_sanitize($_POST['territory_name']));
                    if (empty($territory_name)) {
                        $validation_errors[] = 'Territory name is required';
                    } elseif (strlen($territory_name) < 3) {
                        $validation_errors[] = 'Territory name must be at least 3 characters';
                    } elseif (strlen($territory_name) > 100) {
                        $validation_errors[] = 'Territory name must not exceed 100 characters';
                    }
                    
                    // Validate max epic recruits
                    $max_epic_recruits = (int)$_POST['max_epic_recruits'];
                    if ($max_epic_recruits < 0) {
                        $validation_errors[] = 'Max EPIC recruits must be a positive number';
                    } elseif ($max_epic_recruits > 1000000) {
                        $validation_errors[] = 'Max EPIC recruits cannot exceed 1,000,000';
                    }
                    
                    // Validate commission rates
                    $recruitment_rate = (float)$_POST['recruitment_commission_rate'];
                    $indirect_rate = (float)$_POST['indirect_commission_rate'];
                    
                    if (!empty($_POST['recruitment_commission_rate']) && ($recruitment_rate < 0 || $recruitment_rate > 100)) {
                        $validation_errors[] = 'Recruitment commission rate must be between 0 and 100';
                    }
                    
                    if (!empty($_POST['indirect_commission_rate']) && ($indirect_rate < 0 || $indirect_rate > 100)) {
                        $validation_errors[] = 'Indirect commission rate must be between 0 and 100';
                    }
                    
                    // Check if EPIS account exists
                    $existing_epis = db()->selectOne("SELECT * FROM epic_epis_accounts WHERE user_id = ?", [$epis_user_id]);
                    if (!$existing_epis) {
                        $validation_errors[] = 'EPIS account not found';
                    }
                    
                    if (!empty($validation_errors)) {
                        throw new Exception(implode(', ', $validation_errors));
                    }
                    
                    $update_data = [
                        'territory_name' => $territory_name,
                        'territory_description' => epic_sanitize($_POST['territory_description']),
                        'max_epic_recruits' => $max_epic_recruits,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    // Only update commission rates if provided
                    if (!empty($_POST['recruitment_commission_rate'])) {
                        $update_data['recruitment_commission_rate'] = $recruitment_rate;
                    }
                    if (!empty($_POST['indirect_commission_rate'])) {
                        $update_data['indirect_commission_rate'] = $indirect_rate;
                    }
                    
                    $updated = db()->update('epic_epis_accounts', $update_data, 'user_id = ?', [$epis_user_id]);
                    if ($updated) {
                        $success = 'EPIS account updated successfully!';
                    } else {
                        throw new Exception('Failed to update EPIS account');
                    }
                    break;
                    
                case 'suspend_epis':
                    $epis_user_id = (int)$_POST['epis_user_id'];
                    $updated = db()->update('epic_epis_accounts', 
                        ['status' => 'suspended', 'updated_at' => date('Y-m-d H:i:s')], 
                        'user_id = ?', 
                        [$epis_user_id]
                    );
                    
                    if ($updated) {
                        // Also update user status
                        db()->update('epic_users', 
                            ['status' => 'suspended', 'updated_at' => date('Y-m-d H:i:s')], 
                            'id = ?', 
                            [$epis_user_id]
                        );
                        $success = 'EPIS account suspended successfully!';
                    } else {
                        throw new Exception('Failed to suspend EPIS account');
                    }
                    break;
                    
                case 'activate_epis':
                    $epis_user_id = (int)$_POST['epis_user_id'];
                    $updated = db()->update('epic_epis_accounts', 
                        ['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')], 
                        'user_id = ?', 
                        [$epis_user_id]
                    );
                    
                    if ($updated) {
                        // Also update user status
                        db()->update('epic_users', 
                            ['status' => 'epis', 'updated_at' => date('Y-m-d H:i:s')], 
                            'id = ?', 
                            [$epis_user_id]
                        );
                        $success = 'EPIS account activated successfully!';
                    } else {
                        throw new Exception('Failed to activate EPIS account');
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Get EPIS accounts with filters
$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if (!empty($status_filter)) {
    $filters['status'] = $status_filter;
}

$epis_accounts = epic_get_all_epis_accounts($filters);

// Get statistics
$stats = [
    'total_epis' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts"),
    'active_epis' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'active'"),
    'suspended_epis' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'suspended'"),
    'total_epic_in_networks' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_networks WHERE status = 'active'"),
    'total_commissions' => db()->selectValue("SELECT COALESCE(SUM(total_commissions_earned), 0) FROM epic_epis_networks")
];

// Get eligible EPIC users for promotion
$eligible_epic_users = db()->select(
    "SELECT u.id, u.name, u.email, u.created_at
     FROM epic_users u
     LEFT JOIN epic_epis_accounts ea ON u.id = ea.user_id
     WHERE u.status = 'epic' AND ea.id IS NULL
     ORDER BY u.name ASC
     LIMIT 50"
);

// Prepare data untuk layout dengan struktur yang konsisten
$layout_data = [
    'page_title' => 'EPIS Account Management - ' . epic_setting('site_name', 'EPIC Hub'),
    'header_title' => 'EPIS Account Management',
    'current_page' => 'manage-epis',
    'body_class' => 'admin-body',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin/manage')],
        ['text' => 'EPIS Accounts']
    ],
    'page_actions' => [
        [
            'type' => 'link',
            'text' => 'Create EPIS Account',
            'url' => epic_url('admin/manage/epis/add'),
            'icon' => 'plus',
            'class' => 'btn-primary'
        ],
        [
            'type' => 'link',
            'text' => 'Commission Rules',
            'url' => epic_url('admin/manage/epis/commission-rules'),
            'icon' => 'settings',
            'class' => 'btn-secondary'
        ],
        [
            'type' => 'link',
            'text' => 'Registration Invitations',
            'url' => epic_url('admin/manage/epis/invitations'),
            'icon' => 'mail',
            'class' => 'btn-secondary'
        ]
    ],
    'content_file' => __DIR__ . '/content/epis-management-content.php',
    
    // Pass variables ke content dengan validasi
    'success' => $success,
    'error' => $error,
    'epis_accounts' => $epis_accounts ?? [],
    'stats' => $stats ?? [],
    'eligible_epic_users' => $eligible_epic_users ?? [],
    'search' => $search,
    'status_filter' => $status_filter,
    'page' => $page,
    'user' => $user,
    
    // Additional CSS dan JS untuk halaman ini
    'additional_css' => [
        'themes/modern/admin/pages/epis-management.css'
    ],
    'additional_js' => [
        'themes/modern/admin/pages/epis-management.js'
    ]
];

// Render halaman dengan layout global yang konsisten
epic_render_admin_page($layout_data['content_file'], $layout_data);

?>