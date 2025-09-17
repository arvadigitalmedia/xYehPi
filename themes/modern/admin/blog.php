<?php
/**
 * EPIC Hub Admin Blog Management
 * Comprehensive blog system with referral tracking and article management
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Ensure admin functions are loaded
if (!function_exists('epic_route_403')) {
    require_once EPIC_CORE_DIR . '/admin.php';
}

// Check admin access
$user = epic_current_user();
if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
    // Use fallback 403 handling if epic_route_403 not available
    if (function_exists('epic_route_403')) {
        epic_route_403();
    } else {
        http_response_code(403);
        echo '<h1>403 - Access Forbidden</h1><p>You do not have permission to access this page.</p>';
        exit;
    }
    return;
}

/**
 * Blog Dashboard - Main overview
 */
if (!function_exists('epic_admin_blog_dashboard')) {
function epic_admin_blog_dashboard() {
    // Get blog statistics
    $stats = [
        'total_articles' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table(TABLE_ARTICLES)),
        'published_articles' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table(TABLE_ARTICLES) . " WHERE status = 'published'"),
        'draft_articles' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table(TABLE_ARTICLES) . " WHERE status = 'draft'"),
        'total_views' => db()->selectValue("SELECT SUM(view_count) FROM " . db()->table(TABLE_ARTICLES)),
        'total_referrals_from_blog' => db()->selectValue(
            "SELECT COUNT(DISTINCT r.user_id) FROM " . db()->table(TABLE_REFERRALS) . " r 
             JOIN " . db()->table(TABLE_LANDING_VISITS) . " lv ON lv.sponsor_id = r.referrer_id 
             WHERE lv.article_id IS NOT NULL"
        ) ?: 0,
        'total_sales_from_blog' => db()->selectValue(
            "SELECT SUM(o.amount) FROM " . db()->table(TABLE_ORDERS) . " o 
             JOIN " . db()->table(TABLE_LANDING_VISITS) . " lv ON lv.sponsor_id = o.user_id 
             WHERE lv.article_id IS NOT NULL AND o.status = 'paid'"
        ) ?: 0
    ];
    
    // Get recent articles
    $recent_articles = db()->select(
        "SELECT a.*, u.name as author_name, c.name as category_name
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         ORDER BY a.created_at DESC
         LIMIT 10"
    );
    
    // Get top performing articles
    $top_articles = db()->select(
        "SELECT a.*, u.name as author_name, c.name as category_name
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         WHERE a.status = 'published'
         ORDER BY a.view_count DESC
         LIMIT 5"
    );
    
    // Get categories
    $categories = db()->select(
        "SELECT c.*, COUNT(a.id) as article_count
         FROM " . db()->table(TABLE_CATEGORIES) . " c
         LEFT JOIN " . db()->table(TABLE_ARTICLES) . " a ON c.id = a.category_id
         GROUP BY c.id
         ORDER BY c.name"
    );
    
    // Get published articles for table
    $published_articles = db()->select(
        "SELECT a.id, a.title, a.published_at, a.status, u.name as author_name
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         WHERE a.status = 'published'
         ORDER BY a.published_at DESC
         LIMIT 15"
    );
    
    $layout_data = [
        'page_title' => 'Blog Management - EPIC Hub Admin',
        'header_title' => 'Blog Management',
        'current_page' => 'blog',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Blog Management']
        ],
        'content_file' => __DIR__ . '/content/blog-dashboard.php',
        'stats' => $stats,
        'recent_articles' => $recent_articles,
        'top_articles' => $top_articles,
        'categories' => $categories,
        'published_articles' => $published_articles
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}
}

/**
 * Add new article
 */
if (!function_exists('epic_admin_blog_add_article')) {
function epic_admin_blog_add_article() {
    $error = null;
    $success = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $excerpt = trim($_POST['excerpt'] ?? '');
            $category_id = $_POST['category_id'] ?? null;
            $status = $_POST['status'] ?? 'draft';
            $visibility = $_POST['visibility'] ?? 'public';
            $seo_title = trim($_POST['seo_title'] ?? '');
            $seo_description = trim($_POST['seo_description'] ?? '');
            $seo_keywords = trim($_POST['seo_keywords'] ?? '');
            $social_image = $_POST['social_image'] ?? '';
            
            // Validation
            if (empty($title)) {
                throw new Exception('Title is required.');
            }
            
            if (empty($content)) {
                throw new Exception('Content is required.');
            }
            
            // Generate slug
            $slug = epic_generate_slug($title);
            
            // Check if slug exists
            $existing = db()->selectValue(
                "SELECT id FROM " . db()->table(TABLE_ARTICLES) . " WHERE slug = ?",
                [$slug]
            );
            
            if ($existing) {
                $slug .= '-' . time();
            }
            
            // Handle featured image upload
            $featured_image = null;
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $featured_image = epic_handle_image_upload($_FILES['featured_image'], 'blog');
            }
            
            // Calculate reading time (average 200 words per minute)
            $word_count = str_word_count(strip_tags($content));
            $reading_time = max(1, ceil($word_count / 200));
            
            // Insert article
            $article_data = [
                'uuid' => epic_generate_uuid(),
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt,
                'category_id' => $category_id ?: null,
                'author_id' => epic_current_user()['id'],
                'status' => $status,
                'visibility' => $visibility,
                'featured_image' => $featured_image,
                'reading_time' => $reading_time,
                'seo_title' => $seo_title ?: $title,
                'seo_description' => $seo_description ?: $excerpt,
                'seo_keywords' => $seo_keywords,
                'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Add social image to gallery if provided
            if ($social_image) {
                $article_data['gallery'] = json_encode(['social_image' => $social_image]);
            }
            
            $article_id = db()->insert(TABLE_ARTICLES, $article_data);
            
            if ($article_id) {
                epic_log_activity(epic_current_user()['id'], 'article_created', "Article '{$title}' created");
                epic_redirect(epic_url('admin/blog?success=Article created successfully'));
                return;
            } else {
                throw new Exception('Failed to create article.');
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Get categories for dropdown
    $categories = db()->select(
        "SELECT * FROM " . db()->table(TABLE_CATEGORIES) . " WHERE status = 'active' ORDER BY name"
    );
    
    $layout_data = [
        'page_title' => 'Add New Article - Blog Management',
        'header_title' => 'Add New Article',
        'current_page' => 'blog',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Blog', 'url' => epic_url('admin/blog')],
            ['text' => 'Add Article']
        ],
        'content_file' => __DIR__ . '/content/blog-add-article.php',
        'categories' => $categories,
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}
}

/**
 * Edit article
 */
if (!function_exists('epic_admin_blog_edit_article')) {
function epic_admin_blog_edit_article($article_id) {
    if (!$article_id) {
        epic_redirect(epic_url('admin/blog'));
        return;
    }
    
    // Get article
    $article = db()->selectOne(
        "SELECT * FROM " . db()->table(TABLE_ARTICLES) . " WHERE id = ?",
        [$article_id]
    );
    
    if (!$article) {
        epic_redirect(epic_url('admin/blog?error=Article not found'));
        return;
    }
    
    $error = null;
    $success = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $excerpt = trim($_POST['excerpt'] ?? '');
            $category_id = $_POST['category_id'] ?? null;
            $status = $_POST['status'] ?? 'draft';
            $visibility = $_POST['visibility'] ?? 'public';
            $seo_title = trim($_POST['seo_title'] ?? '');
            $seo_description = trim($_POST['seo_description'] ?? '');
            $seo_keywords = trim($_POST['seo_keywords'] ?? '');
            $social_image = $_POST['social_image'] ?? '';
            
            // Validation
            if (empty($title)) {
                throw new Exception('Title is required.');
            }
            
            if (empty($content)) {
                throw new Exception('Content is required.');
            }
            
            // Generate slug if title changed
            $slug = $article['slug'];
            if ($title !== $article['title']) {
                $new_slug = epic_generate_slug($title);
                
                // Check if new slug exists
                $existing = db()->selectValue(
                    "SELECT id FROM " . db()->table(TABLE_ARTICLES) . " WHERE slug = ? AND id != ?",
                    [$new_slug, $article_id]
                );
                
                if (!$existing) {
                    $slug = $new_slug;
                }
            }
            
            // Handle featured image upload
            $featured_image = $article['featured_image'];
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image
                if ($featured_image) {
                    epic_delete_uploaded_file($featured_image);
                }
                $featured_image = epic_handle_image_upload($_FILES['featured_image'], 'blog');
            }
            
            // Calculate reading time
            $word_count = str_word_count(strip_tags($content));
            $reading_time = max(1, ceil($word_count / 200));
            
            // Update article
            $update_data = [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt,
                'category_id' => $category_id ?: null,
                'status' => $status,
                'visibility' => $visibility,
                'featured_image' => $featured_image,
                'reading_time' => $reading_time,
                'seo_title' => $seo_title ?: $title,
                'seo_description' => $seo_description ?: $excerpt,
                'seo_keywords' => $seo_keywords,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Update published_at if status changed to published
            if ($status === 'published' && $article['status'] !== 'published') {
                $update_data['published_at'] = date('Y-m-d H:i:s');
            }
            
            // Add social image to gallery if provided
            if ($social_image) {
                $gallery = json_decode($article['gallery'] ?? '{}', true);
                $gallery['social_image'] = $social_image;
                $update_data['gallery'] = json_encode($gallery);
            }
            
            $updated = db()->update(TABLE_ARTICLES, $update_data, 'id = ?', [$article_id]);
            
            if ($updated) {
                epic_log_activity(epic_current_user()['id'], 'article_updated', "Article '{$title}' updated");
                epic_redirect(epic_url('admin/blog?success=Article updated successfully'));
                return;
            } else {
                throw new Exception('Failed to update article.');
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Get categories for dropdown
    $categories = db()->select(
        "SELECT * FROM " . db()->table(TABLE_CATEGORIES) . " WHERE status = 'active' ORDER BY name"
    );
    
    $layout_data = [
        'page_title' => 'Edit Article - Blog Management',
        'header_title' => 'Edit Article',
        'current_page' => 'blog',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Blog', 'url' => epic_url('admin/blog')],
            ['text' => 'Edit Article']
        ],
        'content_file' => __DIR__ . '/content/blog-edit-article.php',
        'article' => $article,
        'categories' => $categories,
        'error' => $error,
        'success' => $success
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}
}

/**
 * Delete article
 */
if (!function_exists('epic_admin_blog_delete_article')) {
function epic_admin_blog_delete_article($article_id) {
    if (!$article_id) {
        epic_redirect(epic_url('admin/blog'));
        return;
    }
    
    $article = db()->selectOne(
        "SELECT * FROM " . TABLE_ARTICLES . " WHERE id = ?",
        [$article_id]
    );
    
    if (!$article) {
        epic_redirect(epic_url('admin/blog?error=Article not found'));
        return;
    }
    
    // Delete featured image if exists
    if ($article['featured_image']) {
        epic_delete_uploaded_file($article['featured_image']);
    }
    
    // Delete article
    $deleted = db()->delete(TABLE_ARTICLES, 'id = ?', [$article_id]);
    
    if ($deleted) {
        epic_log_activity(epic_current_user()['id'], 'article_deleted', "Article '{$article['title']}' deleted");
        epic_redirect(epic_url('admin/blog?success=Article deleted successfully'));
    } else {
        epic_redirect(epic_url('admin/blog?error=Failed to delete article'));
    }
}
}

/**
 * Blog analytics
 */
if (!function_exists('epic_admin_blog_analytics')) {
function epic_admin_blog_analytics() {
    // Get analytics data
    $analytics = [
        'monthly_views' => db()->select(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(view_count) as views
             FROM " . db()->table(TABLE_ARTICLES) . "
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month DESC"
        ),
        'category_performance' => db()->select(
            "SELECT c.name, COUNT(a.id) as article_count, SUM(a.view_count) as total_views
             FROM " . db()->table(TABLE_CATEGORIES) . " c
             LEFT JOIN " . db()->table(TABLE_ARTICLES) . " a ON c.id = a.category_id
             GROUP BY c.id, c.name
             ORDER BY total_views DESC"
        ),
        'referral_sources' => db()->select(
            "SELECT CONCAT('blog_', COALESCE(lv.article_slug, 'unknown')) as source, 
                    COUNT(DISTINCT r.user_id) as referrals, 
                    SUM(o.amount) as sales
             FROM " . db()->table(TABLE_LANDING_VISITS) . " lv
             JOIN " . db()->table(TABLE_REFERRALS) . " r ON lv.sponsor_id = r.referrer_id
             LEFT JOIN " . db()->table(TABLE_ORDERS) . " o ON r.user_id = o.user_id AND o.status = 'paid'
             WHERE lv.article_id IS NOT NULL
             GROUP BY lv.article_slug
             ORDER BY referrals DESC"
        ) ?: []
    ];
    
    $layout_data = [
        'page_title' => 'Blog Analytics - EPIC Hub Admin',
        'header_title' => 'Blog Analytics',
        'current_page' => 'blog',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Blog', 'url' => epic_url('admin/blog')],
            ['text' => 'Analytics']
        ],
        'content_file' => __DIR__ . '/content/blog-analytics.php',
        'analytics' => $analytics
    ];
    
    epic_render_admin_page($layout_data['content_file'], $layout_data);
}
}

/**
 * Helper functions
 */
if (!function_exists('epic_handle_image_upload')) {
function epic_handle_image_upload($file, $folder = 'blog') {
    $upload_dir = EPIC_ROOT . '/uploads/' . $folder . '/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        throw new Exception('Invalid image format. Only JPG, PNG, GIF, and WebP are allowed.');
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        throw new Exception('Image size too large. Maximum 5MB allowed.');
    }
    
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $folder . '/' . $filename;
    } else {
        throw new Exception('Failed to upload image.');
    }
}
}

if (!function_exists('epic_delete_uploaded_file')) {
function epic_delete_uploaded_file($file_path) {
    $full_path = EPIC_ROOT . '/uploads/' . $file_path;
    if (file_exists($full_path)) {
        unlink($full_path);
    }
}
}

// Handle actions
$action = $_GET['action'] ?? 'dashboard';
$article_id = $_GET['id'] ?? null;

switch ($action) {
    case 'add':
        epic_admin_blog_add_article();
        break;
    case 'edit':
        epic_admin_blog_edit_article($article_id);
        break;
    case 'delete':
        epic_admin_blog_delete_article($article_id);
        break;
    case 'analytics':
        epic_admin_blog_analytics();
        break;
    default:
        epic_admin_blog_dashboard();
        break;
}

?>