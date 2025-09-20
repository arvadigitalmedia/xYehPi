<?php
/**
 * EPIS Account Creation - Standalone Page
 * Halaman terpisah untuk membuat EPIS Account dengan layout konsisten
 * 
 * @version 1.0.0
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
$user = epic_validate_admin_access('admin', 'admin/manage/epis/add');

// Validate system requirements
if (!epic_validate_system_requirements()) {
    epic_handle_403_error();
    exit;
}

$success = '';
$error = '';
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_epis') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Get creation method
        $creation_method = isset($_POST['creation_method']) ? $_POST['creation_method'] : 'existing_user';
        
        // Common validation
        $territory_name = isset($_POST['territory_name']) ? trim($_POST['territory_name']) : '';
        $max_epic_recruits = isset($_POST['max_epic_recruits']) ? (int)$_POST['max_epic_recruits'] : 0;
        
        if (empty($territory_name)) {
            $error = 'Territory name is required.';
        } elseif ($max_epic_recruits <= 0 || $max_epic_recruits > 1000000) {
            $error = 'Maximum EPIC recruits must be between 1 and 1,000,000.';
        } else {
            $user_id = 0;
            
            if ($creation_method === 'existing_user') {
                // Validate existing user selection
                $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
                
                // Allow superadmin to create EPIS without selecting existing user
                if ($user_id <= 0 && $user['role'] !== 'super_admin') {
                    $error = 'Please select a valid EPIC user to promote.';
                } elseif ($user_id > 0) {
                    // Check if user is already an EPIS (only if user_id is provided)
                    $check_query = "SELECT id FROM epic_epis_accounts WHERE user_id = ?";
                    $check_stmt = $db->prepare($check_query);
                    $check_stmt->bind_param("i", $user_id);
                    $check_stmt->execute();
                    $existing = $check_stmt->get_result()->fetch_assoc();
                    
                    if ($existing) {
                        $error = 'This user is already an EPIS account.';
                    }
                }
            } elseif ($creation_method === 'super_admin_create') {
                // Super admin dapat membuat EPIS tanpa memilih user existing
                // Set user_id ke 0 untuk menandakan tidak ada user existing yang dipromosikan
                $user_id = 0;
            } elseif ($creation_method === 'manual') {
                // Validate manual input fields
                $manual_name = isset($_POST['manual_name']) ? trim($_POST['manual_name']) : '';
                $manual_email = isset($_POST['manual_email']) ? trim($_POST['manual_email']) : '';
                $manual_password = isset($_POST['manual_password']) ? $_POST['manual_password'] : '';
                
                if (empty($manual_name)) {
                    $error = 'Full name is required for manual input.';
                } elseif (empty($manual_email) || !filter_var($manual_email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Valid email address is required.';
                } elseif (strlen($manual_password) < 8) {
                    $error = 'Password must be at least 8 characters long.';
                } else {
                    // Check if email already exists
                    $email_check_query = "SELECT id FROM epic_users WHERE email = ?";
                    $email_check_stmt = $db->prepare($email_check_query);
                    $email_check_stmt->bind_param("s", $manual_email);
                    $email_check_stmt->execute();
                    $existing_email = $email_check_stmt->get_result()->fetch_assoc();
                    
                    if ($existing_email) {
                        $error = 'Email address already exists in the system.';
                    } else {
                        // Create new user first
                        $hashed_password = password_hash($manual_password, PASSWORD_DEFAULT);
                        $username = strtolower(str_replace(' ', '', $manual_name)) . rand(100, 999);
                        
                        $create_user_query = "INSERT INTO epic_users (
                            username, email, password, full_name, user_type, 
                            status, created_at, updated_at
                        ) VALUES (?, ?, ?, ?, 'epis', 'active', NOW(), NOW())";
                        
                        $create_user_stmt = $db->prepare($create_user_query);
                        $create_user_stmt->bind_param("ssss", 
                            $username, $manual_email, $hashed_password, $manual_name
                        );
                        
                        if ($create_user_stmt->execute()) {
                            $user_id = $db->insert_id;
                        } else {
                            $error = 'Failed to create user account. Please try again.';
                        }
                    }
                }
            }
            
            // Handle superadmin creating EPIS without selecting existing user
            if (!$error && $user_id == 0 && $user['role'] === 'super_admin' && 
                ($creation_method === 'existing_user' || $creation_method === 'super_admin_create')) {
                // For superadmin, create a placeholder EPIS account without user association
                // This allows territory management without requiring an existing user
                $user_id = null; // Set to null for database insertion
            }
            
            // If no errors and we have a valid user_id OR superadmin creating standalone EPIS, create EPIS account
            if (!$error && ($user_id > 0 || ($user['role'] === 'super_admin' && 
                ($creation_method === 'existing_user' || $creation_method === 'super_admin_create')))) {
                // Create EPIS account
                $insert_query = "INSERT INTO epic_epis_accounts (
                    user_id, territory_name, max_epic_recruits, 
                    status, created_at
                ) VALUES (?, ?, ?, 'active', NOW())";
                
                $insert_stmt = $db->prepare($insert_query);
                $insert_stmt->bind_param("isi", 
                    $user_id, $territory_name, $max_epic_recruits
                );
                
                if ($insert_stmt->execute()) {
                    // Update user role to EPIS if user_id is provided (not null)
                    if ($user_id > 0) {
                        $update_query = "UPDATE epic_users SET user_type = 'epis' WHERE id = ?";
                        $update_stmt = $db->prepare($update_query);
                        $update_stmt->bind_param("i", $user_id);
                        $update_stmt->execute();
                    }
                    
                    $success = 'EPIS account created successfully!';
                    
                    // Redirect to prevent form resubmission
                    header('Location: ' . epic_url('admin/manage/epis?success=created'));
                    exit;
                } else {
                    $error = 'Failed to create EPIS account. Please try again.';
                }
            }
        }
    }
}

// Get eligible EPIC users for promotion
$eligible_epic_users = db()->select(
    "SELECT u.id, u.name, u.email, u.created_at
     FROM epic_users u
     LEFT JOIN epic_epis_accounts ea ON u.id = ea.user_id
     WHERE u.status = 'epic' AND ea.id IS NULL
     ORDER BY u.name ASC
     LIMIT 50"
);

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Prepare data untuk layout dengan struktur yang konsisten
$layout_data = [
    'page_title' => 'Create EPIS Account - ' . epic_setting('site_name', 'EPIC Hub'),
    'header_title' => 'Create EPIS Account',
    'current_page' => 'manage-epis',
    'body_class' => 'admin-body',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin/manage')],
        ['text' => 'EPIS Accounts', 'url' => epic_url('admin/manage/epis')],
        ['text' => 'Create Account']
    ],
    'page_actions' => [
        [
            'type' => 'link',
            'text' => 'Back to EPIS Management',
            'url' => epic_url('admin/manage/epis'),
            'icon' => 'arrow-left',
            'class' => 'btn-secondary'
        ]
    ],
    'content_file' => __DIR__ . '/content/epis-add-content.php',
    
    // Pass variables ke content dengan validasi
    'success' => $success,
    'error' => $error,
    'eligible_epic_users' => $eligible_epic_users ?? [],
    'form_data' => $form_data,
    'user' => $user,
    
    // Additional CSS dan JS untuk halaman ini
    'additional_css' => [
        'themes/modern/admin/pages/epis-management.css'
    ],
    'additional_js' => [
        'themes/modern/admin/pages/epis-add.js'
    ]
];

// Render halaman dengan layout global yang konsisten
epic_render_admin_page($layout_data['content_file'], $layout_data);

?>