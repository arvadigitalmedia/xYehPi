<?php
/**
 * Member Landing Pages Dashboard
 * Dashboard untuk manajemen landing page member yang sudah upgrade
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Check member access
$user = epic_current_user();
if (!$user) {
    epic_redirect(epic_url('login'));
    return;
}

// Check if user has access to landing pages
if (!epic_member_can_access('landing_pages', $user)) {
    epic_redirect(epic_url('dashboard/member/home?error=' . urlencode('Fitur Landing Pages hanya tersedia untuk member EPIC dan EPIS')));
    return;
}

$access_level = epic_get_member_access_level($user);
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get user's landing pages
try {
    $landing_pages = db()->select(
        "SELECT lp.*, 
                DATE_FORMAT(lp.created_at, '%d %M %Y') as created_date,
                DATE_FORMAT(lp.updated_at, '%d %M %Y %H:%i') as updated_date
         FROM " . db()->table('landing_pages') . " lp 
         WHERE lp.user_id = ? 
         ORDER BY lp.created_at DESC",
        [$user['id']]
    );
    
    // Get analytics data for each landing page
    foreach ($landing_pages as &$page) {
        // Simulate analytics data (replace with actual analytics implementation)
        $page['views'] = rand(50, 500);
        $page['conversions'] = rand(1, 25);
        $page['conversion_rate'] = $page['views'] > 0 ? round(($page['conversions'] / $page['views']) * 100, 2) : 0;
        $page['revenue'] = rand(100000, 2000000); // in rupiah
    }
    
    // Calculate total stats
    $total_pages = count($landing_pages);
    $total_views = array_sum(array_column($landing_pages, 'views'));
    $total_conversions = array_sum(array_column($landing_pages, 'conversions'));
    $total_revenue = array_sum(array_column($landing_pages, 'revenue'));
    $avg_conversion_rate = $total_views > 0 ? round(($total_conversions / $total_views) * 100, 2) : 0;
    
} catch (Exception $e) {
    $landing_pages = [];
    $total_pages = 0;
    $total_views = 0;
    $total_conversions = 0;
    $total_revenue = 0;
    $avg_conversion_rate = 0;
    error_log('Landing pages error: ' . $e->getMessage());
}

// Get user limits based on access level
$limits = [
    'free' => ['max_pages' => 1, 'templates' => ['basic'], 'analytics' => false],
    'epic' => ['max_pages' => 10, 'templates' => ['basic', 'premium'], 'analytics' => true],
    'epis' => ['max_pages' => 50, 'templates' => ['basic', 'premium', 'exclusive'], 'analytics' => true]
];

$user_limits = $limits[$access_level] ?? $limits['free'];

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Landing Pages - Member Area',
    'current_page' => 'landing-pages',
    'user' => $user,
    'access_level' => $access_level,
    'success' => $success,
    'error' => $error,
    'landing_pages' => $landing_pages,
    'user_limits' => $user_limits,
    'stats' => [
        'total_pages' => $total_pages,
        'total_views' => $total_views,
        'total_conversions' => $total_conversions,
        'total_revenue' => $total_revenue,
        'avg_conversion_rate' => $avg_conversion_rate
    ]
];

// Render halaman dengan layout member
epic_render_member_page(__FILE__, $layout_data);
?>