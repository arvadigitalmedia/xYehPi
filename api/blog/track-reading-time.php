<?php
/**
 * EPIC Hub Blog Reading Time Tracking API
 * Track time spent reading blog articles
 */

if (!defined('EPIC_INIT')) {
    define('EPIC_INIT', true);
    require_once __DIR__ . '/../../bootstrap.php';
}

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    if (!isset($input['article_id']) || !isset($input['time_spent'])) {
        throw new Exception('Missing required fields: article_id, time_spent');
    }
    
    $article_id = (int)$input['article_id'];
    $time_spent = (int)$input['time_spent'];
    
    // Validate time spent (should be reasonable)
    if ($time_spent < 1 || $time_spent > 3600) { // 1 second to 1 hour
        throw new Exception('Invalid time_spent value');
    }
    
    // Verify article exists
    $article = db()->selectOne(
        "SELECT id FROM " . TABLE_ARTICLES . " WHERE id = ? AND status = 'published'",
        [$article_id]
    );
    
    if (!$article) {
        throw new Exception('Article not found');
    }
    
    // Update daily stats if table exists
    try {
        // Get current average time on page
        $current_stats = db()->selectOne(
            "SELECT avg_time_on_page, views FROM epic_blog_article_stats 
             WHERE article_id = ? AND date = CURDATE()",
            [$article_id]
        );
        
        if ($current_stats) {
            // Calculate new average
            $current_avg = $current_stats['avg_time_on_page'];
            $views = $current_stats['views'];
            
            if ($views > 0) {
                $new_avg = (($current_avg * $views) + $time_spent) / ($views + 1);
            } else {
                $new_avg = $time_spent;
            }
            
            // Update existing record
            db()->query(
                "UPDATE epic_blog_article_stats 
                 SET avg_time_on_page = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE article_id = ? AND date = CURDATE()",
                [$new_avg, $article_id]
            );
        } else {
            // Create new record
            db()->query(
                "INSERT INTO epic_blog_article_stats (article_id, date, avg_time_on_page, views) 
                 VALUES (?, CURDATE(), ?, 1)
                 ON DUPLICATE KEY UPDATE 
                 avg_time_on_page = VALUES(avg_time_on_page),
                 updated_at = CURRENT_TIMESTAMP",
                [$article_id, $time_spent]
            );
        }
        
        $stats_updated = true;
        
    } catch (Exception $e) {
        // Stats table might not exist, continue without error
        error_log('Blog reading time stats update failed: ' . $e->getMessage());
        $stats_updated = false;
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Reading time tracked successfully',
        'article_id' => $article_id,
        'time_spent' => $time_spent,
        'stats_updated' => $stats_updated
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>