<?php
/**
 * EPIC Hub Articles Controller
 * Handle articles/blog functionality
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Articles route handler
 */
function epic_articles_route($segments) {
    if (isset($segments[1])) {
        // Single article page
        epic_article_single($segments[1]);
    } else {
        // Articles listing
        epic_articles_list();
    }
}

/**
 * Articles listing page
 */
function epic_articles_list() {
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 12;
    $offset = ($page - 1) * $limit;
    $category_id = $_GET['category'] ?? null;
    
    // Build query
    $where = "status = 'published' AND visibility = 'public'";
    $params = [];
    
    if ($category_id) {
        $where .= " AND category_id = ?";
        $params[] = $category_id;
    }
    
    // Get articles
    $articles = db()->select(
        "SELECT a.*, c.name as category_name, u.name as author_name
         FROM " . TABLE_ARTICLES . " a
         LEFT JOIN " . TABLE_CATEGORIES . " c ON a.category_id = c.id
         LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
         WHERE {$where}
         ORDER BY a.published_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );
    
    // Get total count for pagination
    $total = db()->selectValue(
        "SELECT COUNT(*) FROM " . TABLE_ARTICLES . " WHERE {$where}",
        $params
    );
    
    $total_pages = ceil($total / $limit);
    
    // Get categories for filter
    $categories = db()->select(
        "SELECT * FROM " . TABLE_CATEGORIES . " WHERE status = 'active' ORDER BY name"
    );
    
    $data = [
        'page_title' => 'Articles - ' . epic_setting('site_name'),
        'articles' => $articles,
        'categories' => $categories,
        'current_category' => $category_id,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ];
    
    epic_render_template('articles/list', $data);
}

/**
 * Single article page
 */
function epic_article_single($slug) {
    // Get article
    $article = db()->selectOne(
        "SELECT a.*, c.name as category_name, u.name as author_name
         FROM " . TABLE_ARTICLES . " a
         LEFT JOIN " . TABLE_CATEGORIES . " c ON a.category_id = c.id
         LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
         WHERE a.slug = ? AND a.status = 'published'",
        [$slug]
    );
    
    if (!$article) {
        epic_route_404();
        return;
    }
    
    // Check visibility
    $user = epic_current_user();
    if ($article['visibility'] === 'members' && !$user) {
        epic_redirect(epic_url('login?redirect=articles/' . $slug));
        return;
    }
    
    if ($article['visibility'] === 'premium' && (!$user || $user['status'] !== 'premium')) {
        $data = [
            'page_title' => 'Premium Content - ' . epic_setting('site_name'),
            'article' => $article,
            'message' => 'This content is only available to premium members.'
        ];
        epic_render_template('articles/premium', $data);
        return;
    }
    
    // Update view count
    db()->query(
        "UPDATE " . TABLE_ARTICLES . " SET view_count = view_count + 1 WHERE id = ?",
        [$article['id']]
    );
    
    // Get related articles
    $related_articles = [];
    if ($article['category_id']) {
        $related_articles = db()->select(
            "SELECT * FROM " . TABLE_ARTICLES . "
             WHERE category_id = ? AND id != ? AND status = 'published' AND visibility = 'public'
             ORDER BY published_at DESC
             LIMIT 3",
            [$article['category_id'], $article['id']]
        );
    }
    
    $data = [
        'page_title' => $article['title'] . ' - ' . epic_setting('site_name'),
        'page_description' => $article['excerpt'],
        'article' => $article,
        'related_articles' => $related_articles
    ];
    
    epic_render_template('articles/single', $data);
}

/**
 * Get articles by category
 */
function epic_get_articles_by_category($category_id, $limit = 10) {
    return db()->select(
        "SELECT a.*, u.name as author_name
         FROM " . TABLE_ARTICLES . " a
         LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
         WHERE a.category_id = ? AND a.status = 'published' AND a.visibility = 'public'
         ORDER BY a.published_at DESC
         LIMIT {$limit}",
        [$category_id]
    );
}

/**
 * Get featured articles
 */
function epic_get_featured_articles($limit = 5) {
    return db()->select(
        "SELECT a.*, c.name as category_name, u.name as author_name
         FROM " . TABLE_ARTICLES . " a
         LEFT JOIN " . TABLE_CATEGORIES . " c ON a.category_id = c.id
         LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
         WHERE a.status = 'published' AND a.visibility = 'public'
         ORDER BY a.view_count DESC, a.published_at DESC
         LIMIT {$limit}"
    );
}

/**
 * Get recent articles
 */
function epic_get_recent_articles($limit = 5) {
    return db()->select(
        "SELECT a.*, c.name as category_name, u.name as author_name
         FROM " . TABLE_ARTICLES . " a
         LEFT JOIN " . TABLE_CATEGORIES . " c ON a.category_id = c.id
         LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
         WHERE a.status = 'published' AND a.visibility = 'public'
         ORDER BY a.published_at DESC
         LIMIT {$limit}"
    );
}

/**
 * Search articles
 */
function epic_search_articles($query, $limit = 20) {
    $search_terms = '%' . $query . '%';
    
    return db()->select(
        "SELECT a.*, c.name as category_name, u.name as author_name
         FROM " . TABLE_ARTICLES . " a
         LEFT JOIN " . TABLE_CATEGORIES . " c ON a.category_id = c.id
         LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
         WHERE (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)
         AND a.status = 'published' AND a.visibility = 'public'
         ORDER BY a.published_at DESC
         LIMIT {$limit}",
        [$search_terms, $search_terms, $search_terms]
    );
}

/**
 * Get article categories
 */
function epic_get_article_categories() {
    return db()->select(
        "SELECT c.*, COUNT(a.id) as article_count
         FROM " . TABLE_CATEGORIES . " c
         LEFT JOIN " . TABLE_ARTICLES . " a ON c.id = a.category_id AND a.status = 'published'
         WHERE c.status = 'active'
         GROUP BY c.id
         ORDER BY c.sort_order, c.name"
    );
}

/**
 * Create article (for admin/authors)
 */
function epic_create_article($data) {
    // Validate required fields
    if (empty($data['title']) || empty($data['content'])) {
        throw new Exception('Title and content are required');
    }
    
    // Generate slug if not provided
    if (empty($data['slug'])) {
        $data['slug'] = epic_generate_slug($data['title']);
    }
    
    // Ensure slug is unique
    $original_slug = $data['slug'];
    $counter = 1;
    while (db()->exists('articles', 'slug = ?', [$data['slug']])) {
        $data['slug'] = $original_slug . '-' . $counter;
        $counter++;
    }
    
    // Generate UUID if not provided
    if (empty($data['uuid'])) {
        $data['uuid'] = epic_generate_uuid();
    }
    
    // Set default values
    $data['status'] = $data['status'] ?? 'draft';
    $data['visibility'] = $data['visibility'] ?? 'public';
    $data['author_id'] = $data['author_id'] ?? epic_current_user()['id'];
    
    // Generate excerpt if not provided
    if (empty($data['excerpt']) && !empty($data['content'])) {
        $data['excerpt'] = epic_generate_excerpt($data['content']);
    }
    
    // Calculate reading time
    if (!empty($data['content'])) {
        $data['reading_time'] = epic_calculate_reading_time($data['content']);
    }
    
    // Set published date if publishing
    if ($data['status'] === 'published' && empty($data['published_at'])) {
        $data['published_at'] = date('Y-m-d H:i:s');
    }
    
    return db()->insert('articles', $data);
}

/**
 * Update article
 */
function epic_update_article($article_id, $data) {
    // Update slug if title changed
    if (isset($data['title'])) {
        $current_article = db()->selectOne("SELECT slug, title FROM " . TABLE_ARTICLES . " WHERE id = ?", [$article_id]);
        if ($current_article && $current_article['title'] !== $data['title']) {
            $data['slug'] = epic_generate_slug($data['title']);
            
            // Ensure slug is unique
            $original_slug = $data['slug'];
            $counter = 1;
            while (db()->exists('articles', 'slug = ? AND id != ?', [$data['slug'], $article_id])) {
                $data['slug'] = $original_slug . '-' . $counter;
                $counter++;
            }
        }
    }
    
    // Update excerpt if content changed
    if (isset($data['content']) && empty($data['excerpt'])) {
        $data['excerpt'] = epic_generate_excerpt($data['content']);
    }
    
    // Update reading time if content changed
    if (isset($data['content'])) {
        $data['reading_time'] = epic_calculate_reading_time($data['content']);
    }
    
    // Set published date if publishing for first time
    if (isset($data['status']) && $data['status'] === 'published') {
        $current_article = db()->selectOne("SELECT published_at FROM " . TABLE_ARTICLES . " WHERE id = ?", [$article_id]);
        if ($current_article && empty($current_article['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
    }
    
    $data['updated_at'] = date('Y-m-d H:i:s');
    
    return db()->update('articles', $data, 'id = ?', [$article_id]);
}

/**
 * Generate excerpt from content
 */
function epic_generate_excerpt($content, $length = 160) {
    // Strip HTML tags
    $text = strip_tags($content);
    
    // Trim whitespace
    $text = trim($text);
    
    // Truncate to specified length
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' ')) . '...';
    }
    
    return $text;
}

/**
 * Calculate reading time in minutes
 */
function epic_calculate_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_speed = 200; // words per minute
    
    return max(1, ceil($word_count / $reading_speed));
}

/**
 * Generate slug from title
 */
// Duplicate function removed - epic_generate_slug already defined in functions.php

/**
 * Generate UUID v4
 */
// epic_generate_uuid and epic_generate_referral_code functions moved to core/functions.php to avoid duplication

?>