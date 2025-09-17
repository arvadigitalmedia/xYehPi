<?php
/**
 * EPIC Hub Admin Settings - Form Registrasi
 * Custom form fields management for registration
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/settings/form-registrasi');
$user = $init_result['user'];

$success = '';
$error = '';
$edit_field = null;

// Handle AJAX requests for field ordering
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $json_input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($json_input['update_order']) && $json_input['update_order']) {
        try {
            $field_order = $json_input['field_order'] ?? [];
            
            foreach ($field_order as $field) {
                $field_id = (int)($field['id'] ?? 0);
                $sort_order = (int)($field['sort_order'] ?? 0);
                
                if ($field_id > 0) {
                    epic_safe_db_query(
                        "UPDATE " . TABLE_FORM_FIELDS . " SET sort_order = ? WHERE id = ?",
                        [$sort_order, $field_id],
                        'select'
                    );
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Field order updated successfully']);
            exit;
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}

// Handle form field operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_field'])) {
            // Add new form field
            $field_data = [
                'name' => trim($_POST['field_name'] ?? ''),
                'label' => trim($_POST['field_label'] ?? ''),
                'type' => $_POST['field_type'] ?? 'text',
                'is_required' => isset($_POST['is_required']) ? 1 : 0,
                'placeholder' => trim($_POST['placeholder'] ?? ''),
                'options' => !empty($_POST['options']) ? json_encode(explode('\n', trim($_POST['options']))) : null,
                'validation_rules' => !empty($_POST['validation']) ? json_encode([$_POST['validation']]) : null,
                'show_in_registration' => isset($_POST['show_in_registration']) ? 1 : 0,
                'show_in_profile' => isset($_POST['show_in_profile']) ? 1 : 0,
                'show_in_network' => isset($_POST['show_in_network']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if (empty($field_data['name']) || empty($field_data['label'])) {
                throw new Exception('Nama field dan label wajib diisi.');
            }
            
            // Check if field name already exists
            $existing = epic_safe_db_query(
                "SELECT id FROM " . TABLE_FORM_FIELDS . " WHERE name = ?",
                [$field_data['name']],
                'selectValue'
            );
            
            if ($existing) {
                throw new Exception('Nama field sudah ada. Gunakan nama yang berbeda.');
            }
            
            // Insert new field
            epic_safe_db_query(
                "INSERT INTO " . TABLE_FORM_FIELDS . " 
                 (name, label, type, is_required, placeholder, options, validation_rules, show_in_registration, show_in_profile, show_in_network, sort_order, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array_values($field_data),
                'select'
            );
            
            $success = 'Field berhasil ditambahkan!';
            
        } elseif (isset($_POST['update_field'])) {
            // Update existing field
            $field_id = (int)($_POST['field_id'] ?? 0);
            $field_data = [
                'label' => trim($_POST['field_label'] ?? ''),
                'type' => $_POST['field_type'] ?? 'text',
                'is_required' => isset($_POST['is_required']) ? 1 : 0,
                'placeholder' => trim($_POST['placeholder'] ?? ''),
                'options' => !empty($_POST['options']) ? json_encode(explode('\n', trim($_POST['options']))) : null,
                'validation_rules' => !empty($_POST['validation']) ? json_encode([$_POST['validation']]) : null,
                'show_in_registration' => isset($_POST['show_in_registration']) ? 1 : 0,
                'show_in_profile' => isset($_POST['show_in_profile']) ? 1 : 0,
                'show_in_network' => isset($_POST['show_in_network']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (empty($field_data['label'])) {
                throw new Exception('Label field wajib diisi.');
            }
            
            epic_safe_db_query(
                "UPDATE " . TABLE_FORM_FIELDS . " 
                 SET label = ?, type = ?, is_required = ?, placeholder = ?, 
                     options = ?, validation_rules = ?, show_in_registration = ?, show_in_profile = ?, show_in_network = ?, sort_order = ?, updated_at = ?
                 WHERE id = ?",
                array_merge(array_values($field_data), [$field_id]),
                'select'
            );
            
            $success = 'Field berhasil diupdate!';
            
        } elseif (isset($_POST['delete_field'])) {
            // Delete field
            $field_id = (int)($_POST['field_id'] ?? 0);
            
            epic_safe_db_query(
                "DELETE FROM " . TABLE_FORM_FIELDS . " WHERE id = ?",
                [$field_id],
                'select'
            );
            
            $success = 'Field berhasil dihapus!';
        }
        
        // Log activity
        if (function_exists('epic_log_activity')) {
            epic_log_activity($user['id'], 'form_fields_updated', 'Form registration fields updated');
        }
        
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Get form fields
$form_fields = epic_safe_db_query(
    "SELECT * FROM " . TABLE_FORM_FIELDS . " ORDER BY sort_order ASC, id ASC",
    [],
    'select'
);

// Check if editing a field
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    foreach ($form_fields as $field) {
        if ($field['id'] == $edit_id) {
            $edit_field = $field;
            break;
        }
    }
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Form Registrasi Settings - EPIC Hub Admin',
    'header_title' => 'Form Registrasi Settings',
    'current_page' => 'settings',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Settings', 'url' => epic_url('admin/settings')],
        ['text' => 'Form Registrasi']
    ],
    'content_file' => __DIR__ . '/content/settings-form-content.php',
    
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'form_fields' => $form_fields,
    'edit_field' => $edit_field,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>