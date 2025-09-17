<?php
/**
 * EPIC Hub Admin Edit Product - New System
 * Halaman edit produk dengan sistem baru
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

// Get product ID from URL
$product_id = $data['product_id'] ?? 0;

if (!$product_id) {
    epic_redirect(epic_url('admin/manage/product'));
    exit;
}

// Get product data
$product = db()->selectOne(
    "SELECT * FROM epic_products WHERE id = ?",
    [$product_id]
);

if (!$product) {
    epic_redirect(epic_url('admin/manage/product'));
    exit;
}

// Handle form submission
$error = '';
$success = '';
$form_data = $product; // Pre-populate with existing data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    // Store form data for repopulation
    $form_data = array_merge($product, $_POST);
    
    // Basic validation
    if (empty($name)) {
        $error = 'Product name is required.';
    } elseif (empty($description)) {
        $error = 'Product description is required.';
    } elseif ($price <= 0) {
        $error = 'Product price must be greater than 0.';
    } else {
        try {
            // Handle image upload if provided
            $image_filename = $product['image']; // Keep existing image by default
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = EPIC_ROOT . '/uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    // Delete old image if exists
                    if ($product['image'] && file_exists($upload_dir . $product['image'])) {
                        unlink($upload_dir . $product['image']);
                    }
                    
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
                // Prepare update data
                $update_data = [
                    'name' => $name,
                    'slug' => epic_generate_slug($name, $product_id),
                    'description' => $description,
                    'short_description' => $short_description,
                    'price' => $price,
                    'status' => $status,
                    'image' => $image_filename,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Category-specific fields
                if ($product['category'] === 'digital') {
                    $update_data['download_url'] = trim($_POST['download_url'] ?? '');
                    $update_data['file_size'] = trim($_POST['file_size'] ?? '');
                    $update_data['file_format'] = trim($_POST['file_format'] ?? '');
                    $update_data['access_duration'] = intval($_POST['access_duration'] ?? 0);
                } elseif ($product['category'] === 'lms') {
                    $update_data['course_duration'] = trim($_POST['course_duration'] ?? '');
                    $update_data['difficulty_level'] = $_POST['difficulty_level'] ?? 'beginner';
                    $update_data['instructor_name'] = trim($_POST['instructor_name'] ?? '');
                }
                
                // Update product
                $result = db()->update('epic_products', $update_data, 'id = ?', [$product_id]);
                
                if ($result !== false) {
                    epic_log_activity($user['id'], 'product_updated', "Product '{$name}' updated", 'product', $product_id);
                    $success = 'Product updated successfully!';
                    
                    // Refresh product data
                    $product = db()->selectOne("SELECT * FROM epic_products WHERE id = ?", [$product_id]);
                    $form_data = $product;
                } else {
                    $error = 'Failed to update product. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
        }
    }
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Edit Product: ' . htmlspecialchars($product['name']) . ' - EPIC Hub Admin',
    'header_title' => 'Edit Product',
    'current_page' => 'product',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin')],
        ['text' => 'Product', 'url' => epic_url('admin/manage/product')],
        ['text' => 'Edit: ' . htmlspecialchars($product['name'])]
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
    'content_file' => __DIR__ . '/content/product-edit-content.php',
    // Pass variables ke content
    'product' => $product,
    'error' => $error,
    'success' => $success,
    'form_data' => $form_data
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);

// Helper function to generate slug (with exclusion for current product)
if (!function_exists('epic_generate_slug')) {
    function epic_generate_slug($text, $exclude_id = null) {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Replace spaces and special characters with hyphens
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Remove leading/trailing hyphens
        $text = trim($text, '-');
        
        // Ensure uniqueness by checking database
        $base_slug = $text;
        $counter = 1;
        
        $where_clause = "slug = ?";
        $params = [$text];
        
        if ($exclude_id) {
            $where_clause .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        while (db()->selectValue("SELECT COUNT(*) FROM epic_products WHERE {$where_clause}", $params) > 0) {
            $text = $base_slug . '-' . $counter;
            $params[0] = $text;
            $counter++;
        }
        
        return $text;
    }
}
?>