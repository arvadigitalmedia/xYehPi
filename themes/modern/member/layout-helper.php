<?php
/**
 * EPIC Hub Member Layout Helper
 * Helper functions untuk menggunakan layout member area
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Render halaman member dengan layout global
 * 
 * @param string $content_file Path ke file content
 * @param array $data Data untuk halaman
 */
function epic_render_member_page($content_file, $data = []) {
    // Pastikan user sudah login
    $user = epic_current_user();
    if (!$user) {
        epic_redirect(epic_url('login'));
        return;
    }
    
    // Allow admin to access member area if they have preview parameter or explicit access
    $allow_admin_access = isset($_GET['preview']) || isset($_GET['admin_view']) || isset($_GET['as_member']);
    
    // Redirect admin ke admin panel (unless they have explicit access)
    if (in_array($user['role'], ['admin', 'super_admin']) && !$allow_admin_access) {
        epic_redirect(epic_url('admin'));
        return;
    }
    
    // Set content file path
    $data['content_file'] = $content_file;
    $data['user'] = $user;
    
    // Include layout utama
    include __DIR__ . '/layout.php';
}

/**
 * Render halaman member dengan content string
 * 
 * @param string $content HTML content
 * @param array $data Data untuk halaman
 */
function epic_render_member_content($content, $data = []) {
    // Pastikan user sudah login
    $user = epic_current_user();
    if (!$user) {
        epic_redirect(epic_url('login'));
        return;
    }
    
    // Allow admin to access member area if they have preview parameter or explicit access
    $allow_admin_access = isset($_GET['preview']) || isset($_GET['admin_view']) || isset($_GET['as_member']);
    
    // Redirect admin ke admin panel (unless they have explicit access)
    if (in_array($user['role'], ['admin', 'super_admin']) && !$allow_admin_access) {
        epic_redirect(epic_url('admin'));
        return;
    }
    
    // Set content
    $data['content'] = $content;
    $data['user'] = $user;
    
    // Include layout utama
    include __DIR__ . '/layout.php';
}

/**
 * Get user access level untuk member area
 * 
 * @param array $user User data
 * @return string Access level (free, epic, epis)
 */
function epic_get_member_access_level($user) {
    if (!$user) {
        return 'guest';
    }
    
    // Determine access level based on status
    switch ($user['status']) {
        case 'free':
        case 'pending':
        case 'active':
            return 'free';
        case 'epic':
            return 'epic';
        case 'epis':
            return 'epis';
        default:
            return 'free';
    }
}

/**
 * Check if user can access specific feature
 * 
 * @param string $feature Feature name
 * @param array $user User data (optional, uses current user if not provided)
 * @return bool
 */
function epic_member_can_access($feature, $user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return false;
    }
    
    $access_level = epic_get_member_access_level($user);
    
    // Define feature access matrix
    $feature_access = [
        'free' => [
            'profile', 'basic_products', 'basic_orders'
        ],
        'epic' => [
            'profile', 'prospects', 'bonus', 'products', 'orders', 
            'referrals', 'analytics', 'landing_pages'
        ],
        'epis' => [
            'profile', 'prospects', 'bonus', 'products', 'orders',
            'referrals', 'analytics', 'landing_pages', 'team_management',
            'advanced_analytics', 'commission_management'
        ]
    ];
    
    return in_array($feature, $feature_access[$access_level] ?? []);
}

/**
 * Require specific feature access (redirect if not allowed)
 * 
 * @param string $feature Feature name
 * @param string $redirect_url URL to redirect if access denied
 */
function epic_member_require_access($feature, $redirect_url = null) {
    if (!epic_member_can_access($feature)) {
        if (!$redirect_url) {
            $redirect_url = epic_url('dashboard/member');
        }
        epic_redirect($redirect_url);
        exit;
    }
}

/**
 * Get member navigation items based on access level
 * 
 * @param array $user User data
 * @return array Navigation items
 */
function epic_get_member_navigation($user) {
    $access_level = epic_get_member_access_level($user);
    
    $nav_items = [
        [
            'id' => 'home',
            'title' => 'Dashboard',
            'url' => epic_url('dashboard/member'),
            'icon' => 'home',
            'access' => ['free', 'epic', 'epis']
        ],
        [
            'id' => 'profile',
            'title' => 'Profil',
            'url' => epic_url('dashboard/member/profile'),
            'icon' => 'user',
            'access' => ['free', 'epic', 'epis']
        ],
        [
            'id' => 'prospects',
            'title' => 'Prospek',
            'url' => epic_url('dashboard/member/prospects'),
            'icon' => 'users',
            'access' => ['epic', 'epis'],
            'locked_for' => ['free']
        ],
        [
            'id' => 'bonus',
            'title' => 'Bonus Cash',
            'url' => epic_url('dashboard/member/bonus'),
            'icon' => 'dollar-sign',
            'access' => ['epic', 'epis'],
            'locked_for' => ['free']
        ],
        [
            'id' => 'products',
            'title' => 'Akses Produk',
            'url' => epic_url('dashboard/member/products'),
            'icon' => 'package',
            'access' => ['free', 'epic', 'epis']
        ],
        [
            'id' => 'orders',
            'title' => 'History Order',
            'url' => epic_url('dashboard/member/orders'),
            'icon' => 'shopping-cart',
            'access' => ['free', 'epic', 'epis']
        ]
    ];
    
    // Filter navigation based on access level
    return array_filter($nav_items, function($item) use ($access_level) {
        return in_array($access_level, $item['access']);
    });
}

/**
 * Get member statistics
 * 
 * @param array $user User data
 * @return array Statistics
 */
function epic_get_member_stats($user) {
    $stats = [
        'total_orders' => 0,
        'total_earnings' => 0,
        'active_referrals' => 0,
        'conversion_rate' => '0%'
    ];
    
    try {
        // Get user orders
        $stats['total_orders'] = db()->selectValue(
            "SELECT COUNT(*) FROM " . db()->table('orders') . " WHERE user_id = ?",
            [$user['id']]
        ) ?: 0;
        
        // Get user earnings (if EPIC or EPIS)
        if (in_array($user['status'], ['epic', 'epis'])) {
            $stats['total_earnings'] = db()->selectValue(
                "SELECT SUM(amount_in) FROM " . db()->table('transactions') . " 
                 WHERE user_id = ? AND type = 'commission' AND status = 'completed'",
                [$user['id']]
            ) ?: 0;
            
            // Get active referrals
            $stats['active_referrals'] = db()->selectValue(
                "SELECT COUNT(*) FROM " . db()->table('referrals') . " WHERE referrer_id = ?",
                [$user['id']]
            ) ?: 0;
        }
        
    } catch (Exception $e) {
        // Log error but don't break the page
        error_log('Error getting member stats: ' . $e->getMessage());
    }
    
    return $stats;
}

?>