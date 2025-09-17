<?php
/**
 * EPIC Hub Admin Add Product - New System
 * Halaman tambah produk dengan pilihan kategori Digital Product dan LMS
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Ensure bootstrap is loaded
if (!function_exists('db')) {
    require_once dirname(dirname(dirname(__DIR__))) . '/bootstrap.php';
}

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Check admin access sudah dilakukan di layout helper
$user = epic_current_user();

// Handle form submission
$error = '';
$success = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $category = $_POST['category'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    // Store form data for repopulation
    $form_data = $_POST;
    
    // Basic validation
    if (empty($category)) {
        $error = 'Please select a product category.';
    } elseif (empty($name)) {
        $error = 'Product name is required.';
    } elseif (empty($description)) {
        $error = 'Product description is required.';
    } elseif ($price <= 0) {
        $error = 'Product price must be greater than 0.';
    } else {
        try {
            // Handle image upload if provided
            $image_filename = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = EPIC_ROOT . '/uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $image_filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                    $upload_path = $upload_dir . $image_filename;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $error = 'Failed to upload image.';
                    }
                } else {
                    $error = 'Invalid image format. Please use JPG, PNG, GIF, or WebP.';
                }
            }
            
            if (!$error) {
                // Prepare product data
                $product_data = [
                    'name' => $name,
                    'slug' => epic_generate_slug($name),
                    'description' => $description,
                    'short_description' => $short_description,
                    'price' => $price,
                    'category' => $category,
                    'status' => $status,
                    'image' => $image_filename,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Category-specific fields
                if ($category === 'digital') {
                    $product_data['download_url'] = trim($_POST['download_url'] ?? '');
                    $product_data['file_size'] = trim($_POST['file_size'] ?? '');
                    $product_data['file_format'] = trim($_POST['file_format'] ?? '');
                    $product_data['access_duration'] = intval($_POST['access_duration'] ?? 0);
                } elseif ($category === 'lms') {
                    // LMS specific fields - integrated with new LMS system
                    $product_data['type'] = $_POST['lms_type'] ?? 'course';
                    $product_data['duration'] = trim($_POST['duration'] ?? '');
                    $product_data['difficulty_level'] = $_POST['difficulty_level'] ?? 'beginner';
                    $product_data['total_modules'] = intval($_POST['total_modules'] ?? 0);
                    $product_data['estimated_hours'] = floatval($_POST['estimated_hours'] ?? 0);
                    $product_data['certificate_enabled'] = !empty($_POST['certificate_enabled']);
                    $product_data['instructor_id'] = !empty($_POST['instructor_id']) ? intval($_POST['instructor_id']) : null;
                    $product_data['category_id'] = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
                    $product_data['commission_type'] = $_POST['commission_type'] ?? 'percentage';
                    $product_data['commission_value'] = floatval($_POST['commission_value'] ?? 10);
                    
                    // Prepare access levels as JSON
                    $access_levels = [];
                    if (!empty($_POST['access_free'])) $access_levels[] = 'free';
                    if (!empty($_POST['access_epic'])) $access_levels[] = 'epic';
                    if (!empty($_POST['access_epis'])) $access_levels[] = 'epis';
                    $product_data['access_level'] = json_encode($access_levels);
                    
                    // Prepare learning objectives as JSON
                    $objectives = [];
                    if (!empty($_POST['learning_objectives'])) {
                        $objectives = array_filter(explode("\n", trim($_POST['learning_objectives'])));
                    }
                    $product_data['learning_objectives'] = json_encode($objectives);
                    
                    // Additional LMS features
                    $product_data['quiz_enabled'] = !empty($_POST['quiz_enabled']);
                    $product_data['discussion_enabled'] = !empty($_POST['discussion_enabled']);
                    $product_data['progress_tracking'] = !empty($_POST['progress_tracking']);
                }
                
                // Insert product
                $product_id = db()->insert('epic_products', $product_data);
                
                if ($product_id) {
                    epic_log_activity($user['id'], 'product_created', "Product '{$name}' created", 'product', $product_id);
                    $success = 'Product created successfully!';
                    
                    // Redirect to product list after 2 seconds
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = '" . epic_url('admin/manage/product') . "';
                        }, 2000);
                    </script>";
                } else {
                    $error = 'Failed to create product. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
        }
    }
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Add New Product - EPIC Hub Admin',
    'header_title' => 'Add New Product',
    'current_page' => 'product',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin')],
        ['text' => 'Product', 'url' => epic_url('admin/manage/product')],
        ['text' => 'Add New']
    ],
    'page_actions' => [
        [
            'type' => 'link',
            'url' => epic_url('admin/manage/product'),
            'text' => 'Back to Products',
            'icon' => 'arrow-left',
            'class' => 'secondary'
        ]
    ],
    'content_file' => __DIR__ . '/content/product-add-content.php',
    // Pass variables ke content
    'error' => $error,
    'success' => $success,
    'form_data' => $form_data
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);

// Helper function to generate slug
if (!function_exists('epic_generate_slug')) {
    function epic_generate_slug($text) {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Replace spaces and special characters with hyphens
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Remove leading/trailing hyphens
        $text = trim($text, '-');
        
        // Ensure uniqueness by checking database
        $base_slug = $text;
        $counter = 1;
        
        while (db()->selectValue("SELECT COUNT(*) FROM epic_products WHERE slug = ?", [$text]) > 0) {
            $text = $base_slug . '-' . $counter;
            $counter++;
        }
        
        return $text;
    }
}
?>