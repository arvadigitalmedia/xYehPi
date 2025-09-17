<?php
/**
 * EPIC Hub Blog Social Share Tracking API
 * Track social media shares for blog articles
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
    if (!isset($input['article_id']) || !isset($input['platform'])) {
        throw new Exception('Missing required fields: article_id, platform');
    }
    
    $article_id = (int)$input['article_id'];
    $platform = $input['platform'];
    
    // Validate platform
    $allowed_platforms = ['facebook', 'twitter', 'linkedin', 'whatsapp', 'telegram', 'email', 'copy_link'];
    if (!in_array($platform, $allowed_platforms)) {
        throw new Exception('Invalid platform');
    }
    
    // Verify article exists
    $article = db()->selectOne(
        "SELECT id FROM " . TABLE_ARTICLES . " WHERE id = ? AND status = 'published'",
        [$article_id]
    );
    
    if (!$article) {
        throw new Exception('Article not found');
    }
    
    // Get user info if logged in
    $user = epic_current_user();
    $user_id = $user ? $user['id'] : null;
    
    // Prepare share data
    $share_data = [
        'article_id' => $article_id,
        'platform' => $platform,
        'shared_by_user_id' => $user_id,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
        'shared_at' => date('Y-m-d H:i:s')
    ];
    
    // Insert share record
    $share_id = db()->insert('epic_blog_social_shares', $share_data);
    
    if (!$share_id) {
        throw new Exception('Failed to track share');
    }
    
    // Update daily stats if table exists
    try {
        db()->query(
            "INSERT INTO epic_blog_article_stats (article_id, date, social_shares) 
             VALUES (?, CURDATE(), 1)
             ON DUPLICATE KEY UPDATE 
             social_shares = social_shares + 1,
             updated_at = CURRENT_TIMESTAMP",
            [$article_id]
        );
    } catch (Exception $e) {
        // Stats table might not exist, continue without error
        error_log('Blog stats update failed: ' . $e->getMessage());
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Share tracked successfully',
        'share_id' => $share_id,
        'platform' => $platform,
        'article_id' => $article_id
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>