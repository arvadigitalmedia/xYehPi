<?php
/**
 * EPIC Hub Admin LMS Products Management
 * Comprehensive LMS product management with modules
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Check admin access - handled by layout helper
$user = epic_current_user();
if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
    epic_route_403();
    return;
}

// Handle form submissions
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_product':
            $result = create_lms_product($_POST);
            if ($result['success']) {
                $success = 'Product created successfully!';
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'update_product':
            $result = update_lms_product($_POST);
            if ($result['success']) {
                $success = 'Product updated successfully!';
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'delete_product':
            $result = delete_lms_product($_POST['product_id']);
            if ($result['success']) {
                $success = 'Product deleted successfully!';
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'create_module':
            $result = create_product_module($_POST);
            if ($result['success']) {
                $success = 'Module created successfully!';
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'update_module':
            $result = update_product_module($_POST);
            if ($result['success']) {
                $success = 'Module updated successfully!';
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'delete_module':
            $result = delete_product_module($_POST['module_id']);
            if ($result['success']) {
                $success = 'Module deleted successfully!';
            } else {
                $error = $result['message'];
            }
            break;
    }
}

// Pagination settings
$jmlperpage = 12;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
    $start = ($_GET['start'] - 1) * $jmlperpage;
    $page = $_GET['start'];
} else {
    $start = 0;
    $page = 1;
}

// Search and filter functionality
$where = '1=1';
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (isset($_GET['type']) && $_GET['type'] !== '') {
    $where .= " AND p.type = ?";
    $params[] = $_GET['type'];
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where .= " AND p.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['category']) && $_GET['category'] !== '') {
    $where .= " AND p.category_id = ?";
    $params[] = $_GET['category'];
}

// Check if LMS tables exist, if not show installation message
$lms_tables_exist = false;
try {
    $test_query = db()->selectOne("SHOW TABLES LIKE 'epic_product_categories'");
    $lms_tables_exist = !empty($test_query);
} catch (Exception $e) {
    $lms_tables_exist = false;
}

if (!$lms_tables_exist) {
    // LMS tables don't exist, show installation message
    $products = [];
    $total_products = 0;
    $categories = [];
    $instructors = [];
    $error = 'LMS database tables are not installed. Please run the LMS installation first.';
} else {
    // Get products with enhanced data
    $products = db()->select(
        "SELECT p.*, 
                c.name as category_name,
                c.color as category_color,
                u.name as instructor_name,
                COUNT(m.id) as module_count,
                AVG(pr.rating) as avg_rating,
                COUNT(pr.id) as review_count
         FROM epic_products p
         LEFT JOIN epic_product_categories c ON p.category_id = c.id
         LEFT JOIN epic_users u ON p.instructor_id = u.id
         LEFT JOIN epic_product_modules m ON p.id = m.product_id AND m.status = 'published'
         LEFT JOIN epic_product_reviews pr ON p.id = pr.product_id AND pr.status = 'approved'
         WHERE {$where}
         GROUP BY p.id
         ORDER BY p.created_at DESC
         LIMIT {$jmlperpage} OFFSET {$start}",
        $params
    ) ?: [];
}

if ($lms_tables_exist) {
    // Get total count for pagination
    $total_products = db()->selectOne(
        "SELECT COUNT(DISTINCT p.id) as total 
         FROM epic_products p
         LEFT JOIN epic_product_categories c ON p.category_id = c.id
         WHERE {$where}",
        $params
    )['total'] ?? 0;
    
    // Get categories for filter
    $categories = db()->select(
        "SELECT * FROM epic_product_categories WHERE status = 'active' ORDER BY sort_order, name"
    ) ?: [];
    
    // Get instructors for filter
    $instructors = db()->select(
        "SELECT id, name FROM epic_users WHERE role IN ('admin', 'super_admin') ORDER BY name"
    ) ?: [];
}

// Calculate pagination
$total_pages = ceil($total_products / $jmlperpage);

// Prepare data for layout
$data = [
    'products' => $products,
    'categories' => $categories,
    'instructors' => $instructors,
    'total_products' => $total_products,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'success' => $success,
    'error' => $error,
    'search' => $_GET['search'] ?? '',
    'type_filter' => $_GET['type'] ?? '',
    'status_filter' => $_GET['status'] ?? '',
    'category_filter' => $_GET['category'] ?? ''
];

// Render layout using admin page helper
epic_render_admin_page(__DIR__ . '/content/lms-products-content.php', $data);

// LMS Product Management Functions
function create_lms_product($data) {
    try {
        // Validate required fields
        $required = ['name', 'type', 'description', 'price'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field {$field} is required"];
            }
        }
        
        // Generate UUID and slug
        $uuid = generate_uuid();
        $slug = generate_slug($data['name']);
        
        // Prepare access levels
        $access_level = [];
        if (!empty($data['access_free'])) $access_level[] = 'free';
        if (!empty($data['access_epic'])) $access_level[] = 'epic';
        if (!empty($data['access_epis'])) $access_level[] = 'epis';
        
        // Prepare learning objectives
        $objectives = [];
        if (!empty($data['learning_objectives'])) {
            $objectives = array_filter(explode("\n", $data['learning_objectives']));
        }
        
        // Insert product
        $product_id = db()->insert('epic_products', [
            'uuid' => $uuid,
            'name' => $data['name'],
            'type' => $data['type'],
            'slug' => $slug,
            'description' => $data['description'],
            'short_description' => $data['short_description'] ?? '',
            'duration' => $data['duration'] ?? null,
            'difficulty_level' => $data['difficulty_level'] ?? null,
            'total_modules' => intval($data['total_modules'] ?? 0),
            'estimated_hours' => floatval($data['estimated_hours'] ?? 0),
            'certificate_enabled' => !empty($data['certificate_enabled']),
            'access_level' => json_encode($access_level),
            'learning_objectives' => json_encode($objectives),
            'instructor_id' => !empty($data['instructor_id']) ? $data['instructor_id'] : null,
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'price' => floatval($data['price']),
            'commission_type' => $data['commission_type'] ?? 'percentage',
            'commission_value' => floatval($data['commission_value'] ?? 0),
            'status' => $data['status'] ?? 'draft',
            'featured' => !empty($data['featured'])
        ]);
        
        return ['success' => true, 'product_id' => $product_id];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function update_lms_product($data) {
    try {
        $product_id = $data['product_id'];
        if (empty($product_id)) {
            return ['success' => false, 'message' => 'Product ID is required'];
        }
        
        // Prepare access levels
        $access_level = [];
        if (!empty($data['access_free'])) $access_level[] = 'free';
        if (!empty($data['access_epic'])) $access_level[] = 'epic';
        if (!empty($data['access_epis'])) $access_level[] = 'epis';
        
        // Prepare learning objectives
        $objectives = [];
        if (!empty($data['learning_objectives'])) {
            $objectives = array_filter(explode("\n", $data['learning_objectives']));
        }
        
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
                    return ['success' => false, 'message' => 'Failed to upload image.'];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid image format. Please use JPG, PNG, GIF, or WebP.'];
            }
        }
        
        // Prepare update data
        $update_data = [
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'],
            'short_description' => $data['short_description'] ?? '',
            'duration' => $data['duration'] ?? null,
            'difficulty_level' => $data['difficulty_level'] ?? null,
            'total_modules' => intval($data['total_modules'] ?? 0),
            'estimated_hours' => floatval($data['estimated_hours'] ?? 0),
            'certificate_enabled' => !empty($data['certificate_enabled']),
            'progress_tracking' => !empty($data['progress_tracking']),
            'quiz_enabled' => !empty($data['quiz_enabled']),
            'discussion_enabled' => !empty($data['discussion_enabled']),
            'access_level' => json_encode($access_level),
            'learning_objectives' => json_encode($objectives),
            'instructor_id' => !empty($data['instructor_id']) ? $data['instructor_id'] : null,
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'price' => floatval($data['price']),
            'commission_type' => $data['commission_type'] ?? 'percentage',
            'commission_value' => floatval($data['commission_value'] ?? 0),
            'status' => $data['status'] ?? 'draft',
            'featured' => !empty($data['featured'])
        ];
        
        // Add image to update data if uploaded
        if ($image_filename) {
            $update_data['image'] = $image_filename;
        }
        
        // Update product
        db()->update('epic_products', $update_data, ['id' => $product_id]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function delete_lms_product($product_id) {
    try {
        if (empty($product_id)) {
            return ['success' => false, 'message' => 'Product ID is required'];
        }
        
        // Check if product has orders
        $has_orders = db()->selectOne(
            "SELECT COUNT(*) as count FROM epic_orders WHERE product_id = ?",
            [$product_id]
        )['count'] ?? 0;
        
        if ($has_orders > 0) {
            return ['success' => false, 'message' => 'Cannot delete product with existing orders. Archive it instead.'];
        }
        
        // Delete product (modules will be deleted by CASCADE)
        db()->delete('epic_products', ['id' => $product_id]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function create_product_module($data) {
    try {
        $required = ['product_id', 'title', 'content_type'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field {$field} is required"];
            }
        }
        
        $uuid = generate_uuid();
        
        $module_id = db()->insert('epic_product_modules', [
            'uuid' => $uuid,
            'product_id' => $data['product_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'content' => $data['content'] ?? '',
            'content_type' => $data['content_type'],
            'video_url' => $data['video_url'] ?? null,
            'video_duration' => !empty($data['video_duration']) ? intval($data['video_duration']) : null,
            'file_url' => $data['file_url'] ?? null,
            'sort_order' => intval($data['sort_order'] ?? 0),
            'is_preview' => !empty($data['is_preview']),
            'estimated_duration' => intval($data['estimated_duration'] ?? 0),
            'status' => $data['status'] ?? 'draft'
        ]);
        
        // Update product total modules count
        $module_count = db()->selectOne(
            "SELECT COUNT(*) as count FROM epic_product_modules WHERE product_id = ? AND status = 'published'",
            [$data['product_id']]
        )['count'] ?? 0;
        
        db()->update('epic_products', [
            'total_modules' => $module_count
        ], ['id' => $data['product_id']]);
        
        return ['success' => true, 'module_id' => $module_id];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function update_product_module($data) {
    try {
        $module_id = $data['module_id'];
        if (empty($module_id)) {
            return ['success' => false, 'message' => 'Module ID is required'];
        }
        
        db()->update('epic_product_modules', [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'content' => $data['content'] ?? '',
            'content_type' => $data['content_type'],
            'video_url' => $data['video_url'] ?? null,
            'video_duration' => !empty($data['video_duration']) ? intval($data['video_duration']) : null,
            'file_url' => $data['file_url'] ?? null,
            'sort_order' => intval($data['sort_order'] ?? 0),
            'is_preview' => !empty($data['is_preview']),
            'estimated_duration' => intval($data['estimated_duration'] ?? 0),
            'status' => $data['status'] ?? 'draft'
        ], ['id' => $module_id]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function delete_product_module($module_id) {
    try {
        if (empty($module_id)) {
            return ['success' => false, 'message' => 'Module ID is required'];
        }
        
        // Get product_id before deletion
        $module = db()->selectOne(
            "SELECT product_id FROM epic_product_modules WHERE id = ?",
            [$module_id]
        );
        
        if (!$module) {
            return ['success' => false, 'message' => 'Module not found'];
        }
        
        // Delete module
        db()->delete('epic_product_modules', ['id' => $module_id]);
        
        // Update product total modules count
        $module_count = db()->selectOne(
            "SELECT COUNT(*) as count FROM epic_product_modules WHERE product_id = ? AND status = 'published'",
            [$module['product_id']]
        )['count'] ?? 0;
        
        db()->update('epic_products', [
            'total_modules' => $module_count
        ], ['id' => $module['product_id']]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function generate_slug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}
?>