<?php
/**
 * EPIC Hub Landing Page Manager Add
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['user_id', 'page_title', 'page_slug', 'landing_url', 'method'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field {$field} is required.");
            }
        }
        
        $user_id = (int)$_POST['user_id'];
        $page_title = trim($_POST['page_title']);
        $page_slug = trim($_POST['page_slug']);
        $page_description = trim($_POST['page_description'] ?? '');
        $landing_url = trim($_POST['landing_url']);
        $method = (int)$_POST['method'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate URL
        if (!filter_var($landing_url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid landing page URL.');
        }
        
        // Validate slug uniqueness
        $existing_slug = db()->selectValue(
            "SELECT id FROM " . db()->table('landing_pages') . " WHERE page_slug = ?",
            [$page_slug]
        );
        
        if ($existing_slug) {
            throw new Exception('Slug already exists. Please choose a different slug.');
        }
        
        // Handle image upload
        $page_image = null;
        if (isset($_FILES['page_image']) && $_FILES['page_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../../uploads/landing-pages/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['page_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Invalid image format. Only JPG, PNG, and GIF are allowed.');
            }
            
            if ($_FILES['page_image']['size'] > 2 * 1024 * 1024) { // 2MB
                throw new Exception('Image size too large. Maximum 2MB allowed.');
            }
            
            $page_image = 'landing_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $upload_path = $upload_dir . $page_image;
            
            if (!move_uploaded_file($_FILES['page_image']['tmp_name'], $upload_path)) {
                throw new Exception('Failed to upload image.');
            }
        }
        
        // Process find & replace data for inject method
        $find_replace_data = null;
        if ($method === 2 && isset($_POST['find_replace']) && is_array($_POST['find_replace'])) {
            $find_replace_array = [];
            foreach ($_POST['find_replace'] as $item) {
                if (!empty($item['find']) && !empty($item['replace'])) {
                    $find_replace_array[] = [
                        'find' => $item['find'],
                        'replace' => $item['replace']
                    ];
                }
            }
            if (!empty($find_replace_array)) {
                $find_replace_data = json_encode($find_replace_array);
            }
        }
        
        // Insert into database
        $landing_page_id = db()->insert('landing_pages', [
            'user_id' => $user_id,
            'page_title' => $page_title,
            'page_description' => $page_description,
            'page_image' => $page_image,
            'page_slug' => $page_slug,
            'landing_url' => $landing_url,
            'method' => $method,
            'find_replace_data' => $find_replace_data,
            'is_active' => $is_active,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log activity
        epic_log_activity($user['id'], 'create_landing_page', [
            'landing_page_id' => $landing_page_id,
            'page_title' => $page_title,
            'page_slug' => $page_slug
        ]);
        
        $success = "Landing page berhasil dibuat!";
        
        // Redirect to landing page manager after success
        header('Location: ' . epic_url('admin/manage/landing-page-manager?success=' . urlencode($success)));
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log('Create landing page error: ' . $e->getMessage());
    }
}

// Get all users for dropdown
$users = db()->select(
    "SELECT id, name, email, referral_code FROM " . db()->table('users') . " WHERE status = 'active' ORDER BY name ASC"
);

// Method options with descriptions
$method_options = [
    1 => [
        'name' => 'iFrame',
        'description' => 'Menampilkan landing page dalam frame di dalam website. Cocok untuk landing page yang sudah responsive dan tidak memerlukan modifikasi konten.'
    ],
    2 => [
        'name' => 'Inject URL',
        'description' => 'Mengambil konten dari URL dan menampilkannya dengan kemampuan find & replace untuk memodifikasi teks. Cocok untuk kustomisasi konten landing page.'
    ],
    3 => [
        'name' => 'Redirect URL',
        'description' => 'Mengarahkan pengunjung langsung ke URL tujuan. Cocok untuk landing page eksternal yang tidak memerlukan branding internal.'
    ]
];

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Tambah Landing Page - EPIC Hub Admin',
    'header_title' => 'Tambah Landing Page',
    'current_page' => 'landing-page-manager',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin/manage')],
        ['text' => 'Landing Page Manager', 'url' => epic_url('admin/manage/landing-page-manager')],
        ['text' => 'Add']
    ],
    'show_back_button' => true,
    'back_url' => epic_url('admin/manage/landing-page-manager'),
    'page_actions' => [
        epic_link_action('Back to Landing Pages', epic_url('admin/manage/landing-page-manager'), 'arrow-left', 'secondary')
    ],
    'content_file' => __DIR__ . '/content/landing-page-manager-add-content.php',
    'inline_css' => '
        /* Landing Page Manager Add Form Styles */
        .landing-form {
            background: var(--surface-2);
            border: 1px solid var(--ink-700);
            border-radius: var(--radius-2xl);
            padding: var(--spacing-8);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }
        
        .landing-form::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-gold);
        }
        
        .landing-form:hover {
            border-color: var(--gold-400);
            box-shadow: var(--shadow-lg);
        }
        
        .form-section {
            margin-bottom: var(--spacing-8);
            position: relative;
        }
        
        .form-section-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-semibold);
            color: var(--ink-100);
            margin-bottom: var(--spacing-6);
            padding-bottom: var(--spacing-3);
            border-bottom: 2px solid var(--ink-700);
            position: relative;
        }
        
        .form-section-title::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--gradient-gold);
        }
        
        .method-options {
            display: grid;
            gap: var(--spacing-4);
        }
        
        .method-option {
            border: 2px solid var(--ink-600);
            border-radius: var(--radius-xl);
            padding: var(--spacing-6);
            cursor: pointer;
            transition: all var(--transition-normal);
            background: var(--surface-3);
            position: relative;
            overflow: hidden;
        }
        
        .method-option::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-gold);
            opacity: 0;
            transition: opacity var(--transition-fast);
        }
        
        .method-option:hover {
            border-color: var(--gold-400);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .method-option:hover::before {
            opacity: 0.7;
        }
        
        .method-option.selected {
            border-color: var(--gold-400);
            background: var(--gradient-gold-subtle);
            box-shadow: var(--shadow-gold);
        }
        
        .method-option.selected::before {
            opacity: 1;
        }
        
        .find-replace-section {
            display: none;
            margin-top: var(--spacing-6);
            padding: var(--spacing-6);
            background: var(--surface-1);
            border-radius: var(--radius-xl);
            border: 1px solid var(--ink-700);
        }
        
        .find-replace-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: var(--spacing-3);
            margin-bottom: var(--spacing-4);
            align-items: end;
        }
        
        .image-upload {
            position: relative;
            display: inline-block;
        }
        
        .image-preview {
            width: 280px;
            height: 160px;
            border: 3px dashed var(--ink-600);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-normal);
            overflow: hidden;
            background: var(--surface-3);
            position: relative;
        }
        
        .image-preview:hover {
            border-color: var(--gold-400);
            transform: scale(1.02);
            box-shadow: var(--shadow-gold-lg);
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--radius-lg);
        }
    ',
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'users' => $users,
    'method_options' => $method_options
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>