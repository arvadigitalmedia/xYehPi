<?php
/**
 * EPIC Hub Blog Controller
 * Handle public blog functionality
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Blog route handler
 */
function epic_blog_route($segments) {
    $action = $segments[1] ?? 'list';
    
    switch ($action) {
        case 'list':
        case '':
            epic_blog_list();
            break;
            
        case 'category':
            $category_slug = $segments[2] ?? null;
            epic_blog_category($category_slug);
            break;
            
        default:
            // Treat as article slug
            epic_blog_article($action);
            break;
    }
}

/**
 * Blog list page
 */
function epic_blog_list() {
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 12;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    
    // Build query
    $where = "a.status = 'published' AND a.visibility = 'public'";
    $params = [];
    
    if (!empty($search)) {
        $where .= " AND (a.title LIKE ? OR a.content LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    if (!empty($category)) {
        $where .= " AND c.slug = ?";
        $params[] = $category;
    }
    
    // Get articles
    $articles = db()->select(
        "SELECT a.*, u.name as author_name, c.name as category_name, c.slug as category_slug
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         WHERE {$where}
         ORDER BY a.published_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );
    
    // Get total count for pagination
    $total = db()->selectValue(
        "SELECT COUNT(*) FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         WHERE {$where}",
        $params
    );
    
    $total_pages = ceil($total / $limit);
    
    // Get categories for filter
    $categories = db()->select(
        "SELECT c.*, COUNT(a.id) as article_count
         FROM " . db()->table(TABLE_CATEGORIES) . " c
         LEFT JOIN " . db()->table(TABLE_ARTICLES) . " a ON c.id = a.category_id AND a.status = 'published'
         WHERE c.status = 'active'
         GROUP BY c.id
         ORDER BY c.name"
    );
    
    $data = [
        'page_title' => 'Blog - ' . epic_setting('site_name'),
        'page_description' => 'Latest articles and insights',
        'articles' => $articles,
        'categories' => $categories,
        'search' => $search,
        'current_category' => $category,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages,
            'prev_page' => $page - 1,
            'next_page' => $page + 1
        ]
    ];
    
    epic_render_template('blog/list', $data);
}

/**
 * Blog category page
 */
function epic_blog_category($category_slug) {
    if (!$category_slug) {
        epic_route_404();
        return;
    }
    
    // Get category
    $category = db()->selectOne(
        "SELECT * FROM " . db()->table(TABLE_CATEGORIES) . " WHERE slug = ? AND status = 'active'",
        [$category_slug]
    );
    
    if (!$category) {
        epic_route_404();
        return;
    }
    
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 12;
    $offset = ($page - 1) * $limit;
    
    // Get articles in category
    $articles = db()->select(
        "SELECT a.*, u.name as author_name, c.name as category_name, c.slug as category_slug
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         WHERE a.status = 'published' AND a.visibility = 'public' AND c.id = ?
         ORDER BY a.published_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        [$category['id']]
    );
    
    // Get total count
    $total = db()->selectValue(
        "SELECT COUNT(*) FROM " . db()->table(TABLE_ARTICLES) . " WHERE category_id = ? AND status = 'published' AND visibility = 'public'",
        [$category['id']]
    );
    
    $total_pages = ceil($total / $limit);
    
    $data = [
        'page_title' => $category['name'] . ' - Blog - ' . epic_setting('site_name'),
        'page_description' => $category['description'] ?: 'Articles in ' . $category['name'],
        'category' => $category,
        'articles' => $articles,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages,
            'prev_page' => $page - 1,
            'next_page' => $page + 1
        ]
    ];
    
    epic_render_template('blog/category', $data);
}

/**
 * Blog article page
 */
function epic_blog_article($slug) {
    if (!$slug) {
        epic_route_404();
        return;
    }
    
    // Get article by slug
    $article = db()->selectOne(
        "SELECT a.*, u.name as author_name, u.email as author_email, c.name as category_name, c.slug as category_slug
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         WHERE a.slug = ? AND a.status = 'published'",
        [$slug]
    );
    
    if (!$article) {
        epic_route_404();
        return;
    }
    
    // Check visibility
    if ($article['visibility'] !== 'public') {
        $user = epic_current_user();
        if (!$user) {
            epic_redirect(epic_url('login?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
            return;
        }
        
        if ($article['visibility'] === 'premium' && !in_array($user['role'], ['premium', 'admin', 'super_admin'])) {
            epic_route_403();
            return;
        }
    }
    
    // Update view count
    try {
        db()->query(
            "UPDATE " . db()->table(TABLE_ARTICLES) . " SET view_count = view_count + 1 WHERE id = ?",
            [$article['id']]
        );
    } catch (Exception $e) {
        // Silently fail
    }
    
    // Get related articles
    $related_articles = [];
    if ($article['category_id']) {
        $related_articles = db()->select(
            "SELECT a.*, u.name as author_name, c.name as category_name, c.slug as category_slug
             FROM " . db()->table(TABLE_ARTICLES) . " a
             LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
             LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
             WHERE a.category_id = ? AND a.id != ? AND a.status = 'published' AND a.visibility = 'public'
             ORDER BY a.published_at DESC
             LIMIT 3",
            [$article['category_id'], $article['id']]
        );
    }
    
    // Parse gallery if exists
    $gallery = [];
    if ($article['gallery']) {
        $gallery = json_decode($article['gallery'], true) ?: [];
    }
    
    $data = [
        'page_title' => $article['title'] . ' - ' . epic_setting('site_name'),
        'page_description' => $article['excerpt'] ?: strip_tags(substr($article['content'], 0, 160)),
        'page_image' => $article['featured_image'] ?: ($gallery['social_image'] ?? null),
        'article' => $article,
        'related_articles' => $related_articles,
        'gallery' => $gallery
    ];
    
    epic_render_template('blog/article', $data);
}

/**
 * Get popular articles
 */
function epic_get_popular_articles($limit = 5) {
    return db()->select(
        "SELECT a.*, u.name as author_name, c.name as category_name, c.slug as category_slug
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         WHERE a.status = 'published' AND a.visibility = 'public'
         ORDER BY a.view_count DESC
         LIMIT {$limit}"
    );
}

/**
 * Get recent articles
 */
function epic_get_recent_articles($limit = 5) {
    return db()->select(
        "SELECT a.*, u.name as author_name, c.name as category_name, c.slug as category_slug
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         WHERE a.status = 'published' AND a.visibility = 'public'
         ORDER BY a.published_at DESC
         LIMIT {$limit}"
    );
}

/**
 * Get article by ID
 */
function epic_get_article($id) {
    return db()->selectOne(
        "SELECT a.*, u.name as author_name, c.name as category_name, c.slug as category_slug
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         WHERE a.id = ?",
        [$id]
    );
}

/**
 * Get article by slug
 */
function epic_get_article_by_slug($slug) {
    return db()->selectOne(
        "SELECT a.*, u.name as author_name, c.name as category_name, c.slug as category_slug
         FROM " . db()->table(TABLE_ARTICLES) . " a
         LEFT JOIN " . db()->table(TABLE_USERS) . " u ON a.author_id = u.id
         LEFT JOIN " . db()->table(TABLE_CATEGORIES) . " c ON a.category_id = c.id
         WHERE a.slug = ?",
        [$slug]
    );
}

/**
 * Get blog categories
 */
function epic_get_blog_categories() {
    return db()->select(
        "SELECT c.*, COUNT(a.id) as article_count
         FROM " . db()->table(TABLE_CATEGORIES) . " c
         LEFT JOIN " . db()->table(TABLE_ARTICLES) . " a ON c.id = a.category_id AND a.status = 'published'
         WHERE c.status = 'active'
         GROUP BY c.id
         ORDER BY c.name"
    );
}
?>