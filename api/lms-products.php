<?php
/**
 * EPIC Hub LMS Products API
 * API for synchronizing LMS product data between admin and member areas
 */

if (!defined('EPIC_INIT')) {
    require_once __DIR__ . '/../bootstrap.php';
}

// Include LMS integration for generate_uuid function
require_once EPIC_ROOT . '/core/lms-integration.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get request method and endpoint
    $method = $_SERVER['REQUEST_METHOD'];
    $endpoint = $_GET['endpoint'] ?? '';
    
    // Route requests
    switch ($endpoint) {
        case 'products':
            handleProductsEndpoint($method);
            break;
            
        case 'product':
            handleProductEndpoint($method);
            break;
            
        case 'modules':
            handleModulesEndpoint($method);
            break;
            
        case 'progress':
            handleProgressEndpoint($method);
            break;
            
        case 'categories':
            handleCategoriesEndpoint($method);
            break;
            
        case 'sync':
            handleSyncEndpoint($method);
            break;
            
        default:
            sendError('Invalid endpoint', 404);
    }
    
} catch (Exception $e) {
    sendError('Internal server error: ' . $e->getMessage(), 500);
}

/**
 * Handle products endpoint
 */
function handleProductsEndpoint($method) {
    switch ($method) {
        case 'GET':
            getProducts();
            break;
        case 'POST':
            createProduct();
            break;
        default:
            sendError('Method not allowed', 405);
    }
}

/**
 * Handle single product endpoint
 */
function handleProductEndpoint($method) {
    $product_id = $_GET['id'] ?? null;
    if (!$product_id) {
        sendError('Product ID is required', 400);
    }
    
    switch ($method) {
        case 'GET':
            getProduct($product_id);
            break;
        case 'PUT':
            updateProduct($product_id);
            break;
        case 'DELETE':
            deleteProduct($product_id);
            break;
        default:
            sendError('Method not allowed', 405);
    }
}

/**
 * Handle modules endpoint
 */
function handleModulesEndpoint($method) {
    switch ($method) {
        case 'GET':
            getModules();
            break;
        case 'POST':
            createModule();
            break;
        case 'PUT':
            updateModule();
            break;
        case 'DELETE':
            deleteModule();
            break;
        default:
            sendError('Method not allowed', 405);
    }
}

/**
 * Handle progress endpoint
 */
function handleProgressEndpoint($method) {
    switch ($method) {
        case 'GET':
            getUserProgress();
            break;
        case 'POST':
            updateUserProgress();
            break;
        default:
            sendError('Method not allowed', 405);
    }
}

/**
 * Handle categories endpoint
 */
function handleCategoriesEndpoint($method) {
    switch ($method) {
        case 'GET':
            getCategories();
            break;
        default:
            sendError('Method not allowed', 405);
    }
}

/**
 * Handle sync endpoint for real-time synchronization
 */
function handleSyncEndpoint($method) {
    switch ($method) {
        case 'POST':
            syncData();
            break;
        default:
            sendError('Method not allowed', 405);
    }
}

/**
 * Get products with filtering and pagination
 */
function getProducts() {
    $user = epic_current_user();
    if (!$user) {
        sendError('Authentication required', 401);
    }
    
    // Get query parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    $category_id = $_GET['category_id'] ?? '';
    $access_level = $_GET['access_level'] ?? $user['access_level'];
    
    // Build WHERE clause
    $where = ['1=1'];
    $params = [];
    
    if ($search) {
        $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    if ($type) {
        $where[] = 'p.type = ?';
        $params[] = $type;
    }
    
    if ($status) {
        $where[] = 'p.status = ?';
        $params[] = $status;
    } else {
        // Default to active products for non-admin users
        if (!epic_is_admin($user)) {
            $where[] = 'p.status = ?';
            $params[] = 'active';
        }
    }
    
    if ($category_id) {
        $where[] = 'p.category_id = ?';
        $params[] = $category_id;
    }
    
    // Filter by access level for non-admin users
    if (!epic_is_admin($user)) {
        $where[] = 'JSON_CONTAINS(p.access_level, ?)';
        $params[] = json_encode($access_level);
    }
    
    $where_clause = implode(' AND ', $where);
    
    // Get products
    $products = db()->select(
        "SELECT p.*, 
                c.name as category_name,
                c.color as category_color,
                u.name as instructor_name,
                COUNT(DISTINCT m.id) as module_count,
                COUNT(DISTINCT pm.id) as published_modules,
                AVG(pr.rating) as avg_rating,
                COUNT(DISTINCT pr.id) as review_count,
                COUNT(DISTINCT o.id) as enrollment_count
         FROM epic_products p
         LEFT JOIN epic_product_categories c ON p.category_id = c.id
         LEFT JOIN epic_users u ON p.instructor_id = u.id
         LEFT JOIN epic_product_modules m ON p.id = m.product_id
         LEFT JOIN epic_product_modules pm ON p.id = pm.product_id AND pm.status = 'published'
         LEFT JOIN epic_product_reviews pr ON p.id = pr.product_id AND pr.status = 'approved'
         LEFT JOIN epic_orders o ON p.id = o.product_id AND o.status = 'paid'
         WHERE {$where_clause}
         GROUP BY p.id
         ORDER BY p.featured DESC, p.created_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    ) ?: [];
    
    // Get total count
    $total = db()->selectOne(
        "SELECT COUNT(DISTINCT p.id) as total
         FROM epic_products p
         LEFT JOIN epic_product_categories c ON p.category_id = c.id
         WHERE {$where_clause}",
        $params
    )['total'] ?? 0;
    
    // Process products data
    foreach ($products as &$product) {
        $product['access_level'] = json_decode($product['access_level'], true) ?: [];
        $product['learning_objectives'] = json_decode($product['learning_objectives'], true) ?: [];
        $product['tags'] = json_decode($product['tags'], true) ?: [];
        $product['avg_rating'] = $product['avg_rating'] ? round(floatval($product['avg_rating']), 1) : null;
        $product['module_count'] = intval($product['module_count']);
        $product['published_modules'] = intval($product['published_modules']);
        $product['review_count'] = intval($product['review_count']);
        $product['enrollment_count'] = intval($product['enrollment_count']);
    }
    
    sendSuccess([
        'products' => $products,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => intval($total),
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Create new product
 */
function createProduct() {
    $user = epic_current_user();
    if (!$user || !epic_is_admin($user)) {
        sendError('Admin access required', 403);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        sendError('Invalid JSON input', 400);
    }
    
    // Required fields
    $required = ['name', 'description', 'price', 'access_level'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            sendError("Field {$field} is required", 400);
        }
    }
    
    // Validate access level
    $valid_access_levels = ['free', 'premium', 'epic', 'epis'];
    $access_levels = is_array($input['access_level']) ? $input['access_level'] : [$input['access_level']];
    foreach ($access_levels as $level) {
        if (!in_array($level, $valid_access_levels)) {
            sendError("Invalid access level: {$level}", 400);
        }
    }
    
    try {
        // Prepare product data
        $product_data = [
            'uuid' => generate_uuid(),
            'name' => $input['name'],
            'slug' => epic_generate_slug($input['name']),
            'description' => $input['description'],
            'short_description' => $input['short_description'] ?? '',
            'price' => floatval($input['price']),
            'sale_price' => isset($input['sale_price']) ? floatval($input['sale_price']) : null,
            'access_level' => json_encode($access_levels),
            'category_id' => $input['category_id'] ?? null,
            'instructor_id' => $input['instructor_id'] ?? $user['id'],
            'duration_hours' => $input['duration_hours'] ?? null,
            'difficulty_level' => $input['difficulty_level'] ?? 'beginner',
            'learning_objectives' => isset($input['learning_objectives']) ? json_encode($input['learning_objectives']) : null,
            'tags' => isset($input['tags']) ? json_encode($input['tags']) : null,
            'featured' => isset($input['featured']) ? intval($input['featured']) : 0,
            'status' => $input['status'] ?? 'draft',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert product
        $product_id = db()->insert('epic_products', $product_data);
        
        if (!$product_id) {
            sendError('Failed to create product', 500);
        }
        
        // Get created product
        $product = db()->selectOne(
            "SELECT * FROM epic_products WHERE id = ?",
            [$product_id]
        );
        
        // Process response data
        $product['access_level'] = json_decode($product['access_level'], true);
        $product['learning_objectives'] = json_decode($product['learning_objectives'], true) ?: [];
        $product['tags'] = json_decode($product['tags'], true) ?: [];
        
        sendSuccess($product, 201);
        
    } catch (Exception $e) {
        sendError('Failed to create product: ' . $e->getMessage(), 500);
    }
}

/**
 * Update existing product
 */
function updateProduct($product_id) {
    $user = epic_current_user();
    if (!$user || !epic_is_admin($user)) {
        sendError('Admin access required', 403);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        sendError('Invalid JSON input', 400);
    }
    
    // Check if product exists
    $existing_product = db()->selectOne(
        "SELECT * FROM epic_products WHERE id = ?",
        [$product_id]
    );
    
    if (!$existing_product) {
        sendError('Product not found', 404);
    }
    
    try {
        // Prepare update data
        $update_data = ['updated_at' => date('Y-m-d H:i:s')];
        
        // Update allowed fields
        $allowed_fields = [
            'name', 'description', 'short_description', 'price', 'sale_price',
            'category_id', 'instructor_id', 'duration_hours', 'difficulty_level',
            'featured', 'status'
        ];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                if (in_array($field, ['price', 'sale_price', 'duration_hours'])) {
                    $update_data[$field] = $input[$field] ? floatval($input[$field]) : null;
                } elseif (in_array($field, ['featured'])) {
                    $update_data[$field] = intval($input[$field]);
                } else {
                    $update_data[$field] = $input[$field];
                }
            }
        }
        
        // Handle special fields
        if (isset($input['access_level'])) {
            $valid_access_levels = ['free', 'premium', 'epic', 'epis'];
            $access_levels = is_array($input['access_level']) ? $input['access_level'] : [$input['access_level']];
            foreach ($access_levels as $level) {
                if (!in_array($level, $valid_access_levels)) {
                    sendError("Invalid access level: {$level}", 400);
                }
            }
            $update_data['access_level'] = json_encode($access_levels);
        }
        
        if (isset($input['learning_objectives'])) {
            $update_data['learning_objectives'] = json_encode($input['learning_objectives']);
        }
        
        if (isset($input['tags'])) {
            $update_data['tags'] = json_encode($input['tags']);
        }
        
        // Update slug if name changed
        if (isset($input['name']) && $input['name'] !== $existing_product['name']) {
            $update_data['slug'] = epic_generate_slug($input['name']);
        }
        
        // Update product
        $updated = db()->update('epic_products', $update_data, 'id = ?', [$product_id]);
        
        if (!$updated) {
            sendError('Failed to update product', 500);
        }
        
        // Get updated product
        $product = db()->selectOne(
            "SELECT * FROM epic_products WHERE id = ?",
            [$product_id]
        );
        
        // Process response data
        $product['access_level'] = json_decode($product['access_level'], true);
        $product['learning_objectives'] = json_decode($product['learning_objectives'], true) ?: [];
        $product['tags'] = json_decode($product['tags'], true) ?: [];
        
        sendSuccess($product);
        
    } catch (Exception $e) {
        sendError('Failed to update product: ' . $e->getMessage(), 500);
    }
}

/**
 * Delete product
 */
function deleteProduct($product_id) {
    $user = epic_current_user();
    if (!$user || !epic_is_admin($user)) {
        sendError('Admin access required', 403);
    }
    
    // Check if product exists
    $product = db()->selectOne(
        "SELECT * FROM epic_products WHERE id = ?",
        [$product_id]
    );
    
    if (!$product) {
        sendError('Product not found', 404);
    }
    
    // Check if product has orders
    $has_orders = db()->selectOne(
        "SELECT COUNT(*) as count FROM epic_orders WHERE product_id = ?",
        [$product_id]
    )['count'] > 0;
    
    if ($has_orders) {
        // Soft delete - just change status
        $updated = db()->update('epic_products', [
            'status' => 'deleted',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$product_id]);
        
        if (!$updated) {
            sendError('Failed to delete product', 500);
        }
        
        sendSuccess(['message' => 'Product marked as deleted']);
    } else {
        // Hard delete
        try {
            // Delete related data first
            db()->delete('epic_product_modules', 'product_id = ?', [$product_id]);
            db()->delete('epic_user_progress', 'product_id = ?', [$product_id]);
            
            // Delete product
            $deleted = db()->delete('epic_products', 'id = ?', [$product_id]);
            
            if (!$deleted) {
                sendError('Failed to delete product', 500);
            }
            
            sendSuccess(['message' => 'Product deleted successfully']);
            
        } catch (Exception $e) {
            sendError('Failed to delete product: ' . $e->getMessage(), 500);
        }
    }
}

/**
 * Create new module
 */
function createModule() {
    $user = epic_current_user();
    if (!$user || !epic_is_admin($user)) {
        sendError('Admin access required', 403);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        sendError('Invalid JSON input', 400);
    }
    
    // Required fields
    $required = ['product_id', 'title', 'content'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            sendError("Field {$field} is required", 400);
        }
    }
    
    // Check if product exists
    $product = db()->selectOne(
        "SELECT id FROM epic_products WHERE id = ?",
        [$input['product_id']]
    );
    
    if (!$product) {
        sendError('Product not found', 404);
    }
    
    try {
        // Get next sort order
        $max_order = db()->selectOne(
            "SELECT MAX(sort_order) as max_order FROM epic_product_modules WHERE product_id = ?",
            [$input['product_id']]
        )['max_order'] ?? 0;
        
        // Prepare module data
        $module_data = [
            'uuid' => generate_uuid(),
            'product_id' => $input['product_id'],
            'title' => $input['title'],
            'description' => $input['description'] ?? '',
            'content' => $input['content'],
            'content_type' => $input['content_type'] ?? 'text',
            'duration_minutes' => $input['duration_minutes'] ?? null,
            'sort_order' => $max_order + 1,
            'status' => $input['status'] ?? 'draft',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert module
        $module_id = db()->insert('epic_product_modules', $module_data);
        
        if (!$module_id) {
            sendError('Failed to create module', 500);
        }
        
        // Get created module
        $module = db()->selectOne(
            "SELECT * FROM epic_product_modules WHERE id = ?",
            [$module_id]
        );
        
        sendSuccess($module, 201);
        
    } catch (Exception $e) {
        sendError('Failed to create module: ' . $e->getMessage(), 500);
    }
}

/**
 * Update existing module
 */
function updateModule() {
    $user = epic_current_user();
    if (!$user || !epic_is_admin($user)) {
        sendError('Admin access required', 403);
    }
    
    $module_id = $_GET['id'] ?? null;
    if (!$module_id) {
        sendError('Module ID is required', 400);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        sendError('Invalid JSON input', 400);
    }
    
    // Check if module exists
    $existing_module = db()->selectOne(
        "SELECT * FROM epic_product_modules WHERE id = ?",
        [$module_id]
    );
    
    if (!$existing_module) {
        sendError('Module not found', 404);
    }
    
    try {
        // Prepare update data
        $update_data = ['updated_at' => date('Y-m-d H:i:s')];
        
        // Update allowed fields
        $allowed_fields = [
            'title', 'description', 'content', 'content_type',
            'duration_minutes', 'sort_order', 'status'
        ];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                if (in_array($field, ['duration_minutes', 'sort_order'])) {
                    $update_data[$field] = $input[$field] ? intval($input[$field]) : null;
                } else {
                    $update_data[$field] = $input[$field];
                }
            }
        }
        
        // Update module
        $updated = db()->update('epic_product_modules', $update_data, 'id = ?', [$module_id]);
        
        if (!$updated) {
            sendError('Failed to update module', 500);
        }
        
        // Get updated module
        $module = db()->selectOne(
            "SELECT * FROM epic_product_modules WHERE id = ?",
            [$module_id]
        );
        
        sendSuccess($module);
        
    } catch (Exception $e) {
        sendError('Failed to update module: ' . $e->getMessage(), 500);
    }
}

/**
 * Delete module
 */
function deleteModule() {
    $user = epic_current_user();
    if (!$user || !epic_is_admin($user)) {
        sendError('Admin access required', 403);
    }
    
    $module_id = $_GET['id'] ?? null;
    if (!$module_id) {
        sendError('Module ID is required', 400);
    }
    
    // Check if module exists
    $module = db()->selectOne(
        "SELECT * FROM epic_product_modules WHERE id = ?",
        [$module_id]
    );
    
    if (!$module) {
        sendError('Module not found', 404);
    }
    
    try {
        // Delete related progress data
        db()->delete('epic_user_progress', 'module_id = ?', [$module_id]);
        
        // Delete module
        $deleted = db()->delete('epic_product_modules', 'id = ?', [$module_id]);
        
        if (!$deleted) {
            sendError('Failed to delete module', 500);
        }
        
        sendSuccess(['message' => 'Module deleted successfully']);
        
    } catch (Exception $e) {
        sendError('Failed to delete module: ' . $e->getMessage(), 500);
    }
}

/**
 * Get categories
 */
function getCategories() {
    $user = epic_current_user();
    if (!$user) {
        sendError('Authentication required', 401);
    }
    
    try {
        $categories = db()->select(
            "SELECT * FROM epic_product_categories 
             WHERE status = 'active' 
             ORDER BY name ASC"
        ) ?: [];
        
        sendSuccess($categories);
        
    } catch (Exception $e) {
        sendError('Failed to get categories: ' . $e->getMessage(), 500);
    }
}

// syncData() function is implemented later in the file with more complete functionality

/**
 * Get single product with modules
 */
function getProduct($product_id) {
    $user = epic_current_user();
    if (!$user) {
        sendError('Authentication required', 401);
    }
    
    // Get product
    $product = db()->selectOne(
        "SELECT p.*, 
                c.name as category_name,
                c.color as category_color,
                u.name as instructor_name,
                AVG(pr.rating) as avg_rating,
                COUNT(DISTINCT pr.id) as review_count
         FROM epic_products p
         LEFT JOIN epic_product_categories c ON p.category_id = c.id
         LEFT JOIN epic_users u ON p.instructor_id = u.id
         LEFT JOIN epic_product_reviews pr ON p.id = pr.product_id AND pr.status = 'approved'
         WHERE p.id = ?
         GROUP BY p.id",
        [$product_id]
    );
    
    if (!$product) {
        sendError('Product not found', 404);
    }
    
    // Check access for non-admin users
    if (!epic_is_admin($user)) {
        $access_levels = json_decode($product['access_level'], true) ?: [];
        if (!in_array($user['access_level'], $access_levels)) {
            sendError('Access denied', 403);
        }
        
        if ($product['status'] !== 'active') {
            sendError('Product not available', 404);
        }
    }
    
    // Get modules
    $modules = db()->select(
        "SELECT * FROM epic_product_modules 
         WHERE product_id = ? 
         ORDER BY sort_order, created_at",
        [$product_id]
    ) ?: [];
    
    // Filter modules for non-admin users
    if (!epic_is_admin($user)) {
        $modules = array_filter($modules, function($module) {
            return $module['status'] === 'published';
        });
    }
    
    // Get user progress if user is enrolled
    $user_progress = [];
    if (!epic_is_admin($user)) {
        $is_enrolled = db()->selectOne(
            "SELECT COUNT(*) as count FROM epic_orders 
             WHERE user_id = ? AND product_id = ? AND status = 'paid'",
            [$user['id'], $product_id]
        )['count'] > 0;
        
        if ($is_enrolled) {
            $progress_data = db()->select(
                "SELECT * FROM epic_user_progress 
                 WHERE user_id = ? AND product_id = ?",
                [$user['id'], $product_id]
            ) ?: [];
            
            foreach ($progress_data as $progress) {
                $user_progress[$progress['module_id'] ?? 'overall'] = $progress;
            }
        }
    }
    
    // Process product data
    $product['access_level'] = json_decode($product['access_level'], true) ?: [];
    $product['learning_objectives'] = json_decode($product['learning_objectives'], true) ?: [];
    $product['tags'] = json_decode($product['tags'], true) ?: [];
    $product['avg_rating'] = $product['avg_rating'] ? round(floatval($product['avg_rating']), 1) : null;
    $product['review_count'] = intval($product['review_count']);
    $product['modules'] = array_values($modules);
    $product['user_progress'] = $user_progress;
    
    sendSuccess($product);
}

/**
 * Get modules for a product
 */
function getModules() {
    $product_id = $_GET['product_id'] ?? null;
    if (!$product_id) {
        sendError('Product ID is required', 400);
    }
    
    $user = epic_current_user();
    if (!$user) {
        sendError('Authentication required', 401);
    }
    
    // Check if user has access to the product
    $product = db()->selectOne(
        "SELECT access_level, status FROM epic_products WHERE id = ?",
        [$product_id]
    );
    
    if (!$product) {
        sendError('Product not found', 404);
    }
    
    if (!epic_is_admin($user)) {
        $access_levels = json_decode($product['access_level'], true) ?: [];
        if (!in_array($user['access_level'], $access_levels) || $product['status'] !== 'active') {
            sendError('Access denied', 403);
        }
    }
    
    // Get modules
    $where = 'product_id = ?';
    $params = [$product_id];
    
    if (!epic_is_admin($user)) {
        $where .= ' AND status = ?';
        $params[] = 'published';
    }
    
    $modules = db()->select(
        "SELECT * FROM epic_product_modules 
         WHERE {$where}
         ORDER BY sort_order, created_at",
        $params
    ) ?: [];
    
    sendSuccess($modules);
}

/**
 * Get user progress
 */
function getUserProgress() {
    $user = epic_current_user();
    if (!$user) {
        sendError('Authentication required', 401);
    }
    
    $product_id = $_GET['product_id'] ?? null;
    $module_id = $_GET['module_id'] ?? null;
    
    $where = 'user_id = ?';
    $params = [$user['id']];
    
    if ($product_id) {
        $where .= ' AND product_id = ?';
        $params[] = $product_id;
    }
    
    if ($module_id) {
        $where .= ' AND module_id = ?';
        $params[] = $module_id;
    }
    
    $progress = db()->select(
        "SELECT up.*, p.name as product_name, m.title as module_title
         FROM epic_user_progress up
         LEFT JOIN epic_products p ON up.product_id = p.id
         LEFT JOIN epic_product_modules m ON up.module_id = m.id
         WHERE {$where}
         ORDER BY up.last_accessed_at DESC",
        $params
    ) ?: [];
    
    sendSuccess($progress);
}

/**
 * Update user progress
 */
function updateUserProgress() {
    $user = epic_current_user();
    if (!$user) {
        sendError('Authentication required', 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        sendError('Invalid JSON input', 400);
    }
    
    $required = ['product_id', 'progress_percentage'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            sendError("Field {$field} is required", 400);
        }
    }
    
    // Check if user is enrolled
    $is_enrolled = db()->selectOne(
        "SELECT COUNT(*) as count FROM epic_orders 
         WHERE user_id = ? AND product_id = ? AND status = 'paid'",
        [$user['id'], $input['product_id']]
    )['count'] > 0;
    
    if (!$is_enrolled) {
        sendError('User not enrolled in this product', 403);
    }
    
    try {
        // Update or insert progress
        $existing = db()->selectOne(
            "SELECT id FROM epic_user_progress 
             WHERE user_id = ? AND product_id = ? AND module_id = ?",
            [$user['id'], $input['product_id'], $input['module_id'] ?? null]
        );
        
        $data = [
            'progress_percentage' => min(100, max(0, floatval($input['progress_percentage']))),
            'time_spent' => intval($input['time_spent'] ?? 0),
            'status' => $input['status'] ?? 'in_progress',
            'last_accessed_at' => date('Y-m-d H:i:s')
        ];
        
        if ($data['progress_percentage'] >= 100) {
            $data['status'] = 'completed';
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        if ($existing) {
            db()->update('epic_user_progress', $data, ['id' => $existing['id']]);
            $progress_id = $existing['id'];
        } else {
            $data['uuid'] = generate_uuid();
            $data['user_id'] = $user['id'];
            $data['product_id'] = $input['product_id'];
            $data['module_id'] = $input['module_id'] ?? null;
            $progress_id = db()->insert('epic_user_progress', $data);
        }
        
        // Check if user completed the entire course
        if (!$input['module_id']) { // Overall course progress
            $total_modules = db()->selectOne(
                "SELECT COUNT(*) as count FROM epic_product_modules 
                 WHERE product_id = ? AND status = 'published'",
                [$input['product_id']]
            )['count'] ?? 0;
            
            $completed_modules = db()->selectOne(
                "SELECT COUNT(*) as count FROM epic_user_progress 
                 WHERE user_id = ? AND product_id = ? AND module_id IS NOT NULL AND status = 'completed'",
                [$user['id'], $input['product_id']]
            )['count'] ?? 0;
            
            if ($total_modules > 0 && $completed_modules >= $total_modules) {
                // Issue certificate if enabled
                $product = db()->selectOne(
                    "SELECT certificate_enabled FROM epic_products WHERE id = ?",
                    [$input['product_id']]
                );
                
                if ($product && $product['certificate_enabled']) {
                    issueCertificate($user['id'], $input['product_id']);
                }
            }
        }
        
        sendSuccess(['progress_id' => $progress_id, 'message' => 'Progress updated successfully']);
        
    } catch (Exception $e) {
        sendError('Failed to update progress: ' . $e->getMessage(), 500);
    }
}

// getCategories() function is already implemented above with proper authentication

/**
 * Sync data between admin and member areas
 */
function syncData() {
    $user = epic_current_user();
    if (!epic_is_admin($user)) {
        sendError('Admin access required', 403);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $sync_type = $input['type'] ?? 'all';
    
    $synced = [];
    
    try {
        switch ($sync_type) {
            case 'products':
                $synced['products'] = syncProducts();
                break;
                
            case 'modules':
                $synced['modules'] = syncModules();
                break;
                
            case 'progress':
                $synced['progress'] = syncProgress();
                break;
                
            case 'all':
            default:
                $synced['products'] = syncProducts();
                $synced['modules'] = syncModules();
                $synced['progress'] = syncProgress();
                break;
        }
        
        sendSuccess([
            'message' => 'Data synchronized successfully',
            'synced' => $synced,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        sendError('Sync failed: ' . $e->getMessage(), 500);
    }
}

/**
 * Issue certificate to user
 */
function issueCertificate($user_id, $product_id) {
    // Check if certificate already exists
    $existing = db()->selectOne(
        "SELECT id FROM epic_user_certificates 
         WHERE user_id = ? AND product_id = ?",
        [$user_id, $product_id]
    );
    
    if ($existing) {
        return $existing['id'];
    }
    
    // Generate certificate number
    $certificate_number = 'EPIC-' . date('Y') . '-' . str_pad($product_id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
    
    // Insert certificate
    $certificate_id = db()->insert('epic_user_certificates', [
        'uuid' => generate_uuid(),
        'user_id' => $user_id,
        'product_id' => $product_id,
        'certificate_number' => $certificate_number,
        'completion_percentage' => 100.00,
        'issued_at' => date('Y-m-d H:i:s'),
        'certificate_data' => json_encode([
            'issued_date' => date('Y-m-d'),
            'completion_date' => date('Y-m-d'),
            'grade' => 'Completed'
        ])
    ]);
    
    return $certificate_id;
}

/**
 * Sync products data
 */
function syncProducts() {
    // Update product statistics
    db()->query(
        "UPDATE epic_products p SET 
         enrollment_count = (
             SELECT COUNT(*) FROM epic_orders o 
             WHERE o.product_id = p.id AND o.status = 'paid'
         ),
         total_modules = (
             SELECT COUNT(*) FROM epic_product_modules m 
             WHERE m.product_id = p.id AND m.status = 'published'
         ),
         rating = (
             SELECT AVG(rating) FROM epic_product_reviews r 
             WHERE r.product_id = p.id AND r.status = 'approved'
         ),
         total_reviews = (
             SELECT COUNT(*) FROM epic_product_reviews r 
             WHERE r.product_id = p.id AND r.status = 'approved'
         )"
    );
    
    return 'Products statistics updated';
}

/**
 * Sync modules data
 */
function syncModules() {
    // Update module order and status
    $modules = db()->select(
        "SELECT id, product_id FROM epic_product_modules ORDER BY product_id, sort_order, created_at"
    ) ?: [];
    
    $current_product = null;
    $order = 1;
    
    foreach ($modules as $module) {
        if ($current_product !== $module['product_id']) {
            $current_product = $module['product_id'];
            $order = 1;
        }
        
        db()->update('epic_product_modules', [
            'sort_order' => $order
        ], ['id' => $module['id']]);
        
        $order++;
    }
    
    return 'Module ordering updated';
}

/**
 * Sync progress data
 */
function syncProgress() {
    // Update overall course progress based on module completion
    db()->query(
        "INSERT INTO epic_user_progress (uuid, user_id, product_id, progress_percentage, status, created_at, updated_at)
         SELECT 
             UUID() as uuid,
             user_id,
             product_id,
             ROUND((COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0) / COUNT(*), 2) as progress_percentage,
             CASE 
                 WHEN COUNT(CASE WHEN status = 'completed' THEN 1 END) = COUNT(*) THEN 'completed'
                 WHEN COUNT(CASE WHEN status = 'completed' THEN 1 END) > 0 THEN 'in_progress'
                 ELSE 'not_started'
             END as status,
             NOW() as created_at,
             NOW() as updated_at
         FROM epic_user_progress 
         WHERE module_id IS NOT NULL
         GROUP BY user_id, product_id
         ON DUPLICATE KEY UPDATE 
             progress_percentage = VALUES(progress_percentage),
             status = VALUES(status),
             updated_at = VALUES(updated_at)"
    );
    
    return 'Progress data synchronized';
}

// generate_uuid() function is available from core/lms-integration.php

/**
 * Send success response
 */
function sendSuccess($data, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('c')
    ]);
    exit();
}

/**
 * Send error response
 */
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => date('c')
    ]);
    exit();
}
?>