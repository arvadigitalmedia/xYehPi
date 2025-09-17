<?php
/**
 * Categories Management Page
 * Main wrapper for categories management using layout system
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper untuk error handling yang konsisten
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Initialize admin page dengan validasi yang proper
$init_result = epic_init_admin_page('admin', 'admin/categories');
$user = $init_result['user'];

// Get action from URL
$action = $_GET['action'] ?? 'list';
$category_id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'list':
        // Get categories data
        $categories = db()->select(
            "SELECT c.*, COUNT(a.id) as article_count
             FROM " . db()->table(TABLE_CATEGORIES) . " c
             LEFT JOIN " . db()->table(TABLE_ARTICLES) . " a ON c.id = a.category_id
             GROUP BY c.id
             ORDER BY c.sort_order, c.name"
        );
        
        $layout_data = [
            'page_title' => 'Categories Management - EPIC Hub Admin',
            'header_title' => 'Categories Management',
            'current_page' => 'categories',
            'breadcrumb' => [
                ['text' => 'Admin', 'url' => epic_url('admin')],
                ['text' => 'Categories']
            ],
            'content_file' => __DIR__ . '/categories/list.php',
            'categories' => $categories
        ];
        break;
        
    case 'add':
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $status = $_POST['status'] ?? 'active';
            
            // Validation
            if (empty($name)) {
                $error = 'Category name is required.';
            } else {
                try {
                    // Create category
                    $category_data = [
                        'name' => $name,
                        'description' => $description,
                        'status' => $status,
                        'slug' => epic_generate_slug($name),
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $category_id = db()->insert(TABLE_CATEGORIES, $category_data);
                    
                    if ($category_id) {
                        epic_log_activity($user['id'], 'category_created', "Category {$name} created");
                        epic_flash('success', 'Category created successfully.');
                        epic_redirect(epic_url('admin/categories'));
                        return;
                    } else {
                        $error = 'Failed to create category.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
        
        $layout_data = [
            'page_title' => 'Add Category - EPIC Hub Admin',
            'header_title' => 'Add New Category',
            'current_page' => 'categories',
            'breadcrumb' => [
                ['text' => 'Admin', 'url' => epic_url('admin')],
                ['text' => 'Categories', 'url' => epic_url('admin/categories')],
                ['text' => 'Add New']
            ],
            'content_file' => __DIR__ . '/categories/add.php',
            'error' => $error,
            'success' => $success
        ];
        break;
        
    case 'edit':
        if (!$category_id) {
            epic_redirect(epic_url('admin/categories'));
            return;
        }
        
        $category = db()->selectOne(
            "SELECT * FROM " . db()->table(TABLE_CATEGORIES) . " WHERE id = ?",
            [$category_id]
        );
        
        if (!$category) {
            epic_flash('error', 'Category not found.');
            epic_redirect(epic_url('admin/categories'));
            return;
        }
        
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $status = $_POST['status'] ?? 'active';
            
            // Validation
            if (empty($name)) {
                $error = 'Category name is required.';
            } else {
                try {
                    // Update category
                    $update_data = [
                        'name' => $name,
                        'description' => $description,
                        'status' => $status,
                        'slug' => epic_generate_slug($name),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $updated = db()->update(TABLE_CATEGORIES, $update_data, 'id = ?', [$category_id]);
                    
                    if ($updated) {
                        epic_log_activity($user['id'], 'category_updated', "Category {$name} updated");
                        epic_flash('success', 'Category updated successfully.');
                        epic_redirect(epic_url('admin/categories'));
                        return;
                    } else {
                        $error = 'Failed to update category.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
        
        $layout_data = [
            'page_title' => 'Edit Category - EPIC Hub Admin',
            'header_title' => 'Edit Category',
            'current_page' => 'categories',
            'breadcrumb' => [
                ['text' => 'Admin', 'url' => epic_url('admin')],
                ['text' => 'Categories', 'url' => epic_url('admin/categories')],
                ['text' => 'Edit']
            ],
            'content_file' => __DIR__ . '/categories/edit.php',
            'category' => $category,
            'error' => $error,
            'success' => $success
        ];
        break;
        
    case 'delete':
        if (!$category_id) {
            epic_redirect(epic_url('admin/categories'));
            return;
        }
        
        $category = db()->selectOne(
            "SELECT * FROM " . db()->table(TABLE_CATEGORIES) . " WHERE id = ?",
            [$category_id]
        );
        
        if (!$category) {
            epic_flash('error', 'Category not found.');
            epic_redirect(epic_url('admin/categories'));
            return;
        }
        
        // Check if category has articles
        $has_articles = db()->selectValue(
            "SELECT COUNT(*) FROM " . db()->table(TABLE_ARTICLES) . " WHERE category_id = ?",
            [$category_id]
        );
        
        if ($has_articles > 0) {
            epic_flash('error', 'Cannot delete category that has articles.');
        } else {
            try {
                // Delete category
                $deleted = db()->delete(TABLE_CATEGORIES, 'id = ?', [$category_id]);
                
                if ($deleted) {
                    epic_log_activity($user['id'], 'category_deleted', "Category {$category['name']} deleted");
                    epic_flash('success', 'Category deleted successfully.');
                } else {
                    epic_flash('error', 'Failed to delete category.');
                }
            } catch (Exception $e) {
                epic_flash('error', 'Database error: ' . $e->getMessage());
            }
        }
        
        epic_redirect(epic_url('admin/categories'));
        return;
        
    default:
        epic_route_404();
        return;
}

// Render the page using layout system
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>