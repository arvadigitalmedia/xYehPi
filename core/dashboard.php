<?php
/**
 * EPIC Hub Dashboard Controller
 * Handle dashboard routes and functionality
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Dashboard route handler
 */
function epic_dashboard_route($page, $segments = []) {
    $user = epic_current_user();
    
    if (!$user) {
        epic_redirect(epic_url('login'));
        return;
    }
    
    switch ($page) {
        case '':
        case 'home':
            epic_dashboard_home();
            break;
            
        case 'profile':
            epic_dashboard_profile();
            break;
            
        case 'referrals':
            epic_dashboard_referrals();
            break;
            
        case 'orders':
            epic_dashboard_orders();
            break;
            
        case 'commissions':
            epic_dashboard_commissions();
            break;
            
        case 'links':
            epic_dashboard_affiliate_links();
            break;
            
        case 'analytics':
            epic_dashboard_analytics();
            break;
            
        case 'settings':
            epic_dashboard_settings();
            break;
            
        case 'products':
            epic_dashboard_products();
            break;
            
        case 'landing-pages':
            epic_dashboard_landing_pages();
            break;
            
        // Member Area Routes
        case 'member':
            epic_dashboard_member($segments);
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Dashboard home page with access level routing
 */
function epic_dashboard_home() {
    $user = epic_current_user();
    
    // Allow admin to access member area if they have preview parameter or explicit access
    $allow_admin_access = isset($_GET['preview']) || isset($_GET['admin_view']) || isset($_GET['as_member']);
    
    // Route to appropriate dashboard based on user role and status
    if (in_array($user['role'], ['admin', 'super_admin']) && !$allow_admin_access) {
        // Redirect admin to admin dashboard
        epic_redirect(epic_url('admin'));
        return;
    }
    
    // Redirect all regular users to member area
    if (in_array($user['role'], ['user', 'affiliate']) || in_array($user['status'], ['free', 'epic', 'epis'])) {
        epic_redirect(epic_url('dashboard/member'));
        return;
    }
    
    // Fallback for any other user types (should rarely happen)
    epic_redirect(epic_url('dashboard/member'));
}

/**
 * Dashboard profile page - Redirect to member area
 */
function epic_dashboard_profile() {
    epic_redirect(epic_url('dashboard/member/profile'));
}

/**
 * Dashboard referrals page - Redirect to member area
 */
function epic_dashboard_referrals() {
    epic_redirect(epic_url('dashboard/member/referrals'));
}

/**
 * Dashboard orders page - Redirect to member area
 */
function epic_dashboard_orders() {
    epic_redirect(epic_url('dashboard/member/orders'));
}

/**
 * Dashboard commissions page - Redirect to member area
 */
function epic_dashboard_commissions() {
    epic_redirect(epic_url('dashboard/member/bonus'));
}

/**
 * Dashboard affiliate links page - Redirect to member area
 */
function epic_dashboard_affiliate_links() {
    epic_redirect(epic_url('dashboard/member/referrals'));
}

/**
 * Dashboard analytics page - Redirect to member area
 */
function epic_dashboard_analytics() {
    epic_redirect(epic_url('dashboard/member/analytics'));
}

/**
 * Dashboard settings page - Redirect to member area
 */
function epic_dashboard_settings() {
    epic_redirect(epic_url('dashboard/member/profile'));
}

/**
 * Dashboard products page - Redirect to member area
 */
function epic_dashboard_products() {
    epic_redirect(epic_url('dashboard/member/products'));
}

/**
 * Dashboard landing pages - Redirect to member area
 */
function epic_dashboard_landing_pages() {
    epic_redirect(epic_url('dashboard/member/landing-pages'));
}

/**
 * Member Area main router
 */
function epic_dashboard_member($segments) {
    $user = epic_current_user();
    
    if (!$user) {
        epic_redirect(epic_url('login'));
        return;
    }
    
    // Check if user has member access
    // Allow admin to access member area if they have preview parameter or explicit access
    $allow_admin_access = isset($_GET['preview']) || isset($_GET['admin_view']) || isset($_GET['as_member']);
    
    if (in_array($user['role'], ['admin', 'super_admin']) && !$allow_admin_access) {
        epic_redirect(epic_url('admin'));
        return;
    }
    
    // Include member layout helper
    require_once EPIC_ROOT . '/themes/modern/member/layout-helper.php';
    
    $page = $segments[0] ?? 'home';
    
    switch ($page) {
        case '':
        case 'home':
            epic_member_home();
            break;
            
        case 'profile':
            epic_member_profile();
            break;
            
        case 'prospects':
            epic_member_prospects();
            break;
            
        case 'bonus':
            epic_member_bonus();
            break;
            
        case 'products':
            epic_member_products();
            break;
            
        case 'orders':
            epic_member_orders();
            break;
            
        case 'landing-pages':
            epic_member_landing_pages();
            break;
            
        case 'referrals':
            epic_member_referrals();
            break;
            
        case 'analytics':
            epic_member_analytics();
            break;
            
        default:
            epic_route_404();
            break;
    }
}

/**
 * Member Area - Home Dashboard
 */
function epic_member_home() {
    $user = epic_current_user();
    $access_level = epic_get_member_access_level($user);
    
    $data = [
        'page_title' => 'Member Dashboard - EPIC Hub',
        'current_page' => 'home',
        'user' => $user,
        'access_level' => $access_level
    ];
    
    // Use member layout system
    epic_render_member_page(__DIR__ . '/../themes/modern/member/home.php', $data);
}

/**
 * Member Area - Edit Profile
 */
function epic_member_profile() {
    $user = epic_current_user();
    $access_level = epic_get_member_access_level($user);
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        epic_handle_profile_update($user);
        return;
    }
    
    $data = [
        'page_title' => 'Edit Profil - Member Area',
        'current_page' => 'profile',
        'user' => $user,
        'access_level' => $access_level
    ];
    
    // Use member layout system
    epic_render_member_page(__DIR__ . '/../themes/modern/member/profile.php', $data);
}

/**
 * Member Area - Prospects Management
 */
function epic_member_prospects() {
    $user = epic_current_user();
    $access_level = epic_get_member_access_level($user);
    
    // Check access permission
    epic_member_require_access('prospects');
    
    $data = [
        'page_title' => 'Manajemen Prospek - Member Area',
        'current_page' => 'prospects',
        'user' => $user,
        'access_level' => $access_level
    ];
    
    // Use member layout system
    epic_render_member_page(__DIR__ . '/../themes/modern/member/prospects.php', $data);
}

/**
 * Member Area - Bonus Cash
 */
function epic_member_bonus() {
    $user = epic_current_user();
    $access_level = epic_get_member_access_level($user);
    
    // Check access permission
    epic_member_require_access('bonus');
    
    $data = [
        'page_title' => 'Bonus Cash - Member Area',
        'current_page' => 'bonus',
        'user' => $user,
        'access_level' => $access_level
    ];
    
    // Use member layout system
    epic_render_member_page(__DIR__ . '/../themes/modern/member/bonus.php', $data);
}

/**
 * Member Area - Product Access
 */
function epic_member_products() {
    $user = epic_current_user();
    $access_level = epic_get_member_access_level($user);
    
    $data = [
        'page_title' => 'Akses Produk - Member Area',
        'current_page' => 'products',
        'user' => $user,
        'access_level' => $access_level
    ];
    
    // Use member layout system
    epic_render_member_page(__DIR__ . '/../themes/modern/member/products.php', $data);
}

/**
 * Member Area - Order History
 */
function epic_member_orders() {
    $user = epic_current_user();
    $access_level = epic_get_member_access_level($user);
    
    $data = [
        'page_title' => 'History Order - Member Area',
        'current_page' => 'orders',
        'user' => $user,
        'access_level' => $access_level
    ];
    
    // Use member layout system
    epic_render_member_page(__DIR__ . '/../themes/modern/member/orders.php', $data);
}

/**
 * Member Area - Landing Pages Management
 */
function epic_member_landing_pages() {
    $user = epic_current_user();
    
    $data = [
        'page_title' => 'Landing Pages - Member Area',
        'user' => $user
    ];
    
    // Use member layout system
    include __DIR__ . '/../themes/modern/member/landing-pages.php';
}

/**
 * Member Area - Referrals Management
 */
function epic_member_referrals() {
    $user = epic_current_user();
    
    $data = [
        'page_title' => 'Referrals - Member Area',
        'user' => $user
    ];
    
    // Use member layout system
    include __DIR__ . '/../themes/modern/member/referrals.php';
}

/**
 * Member Area - Analytics Dashboard
 */
function epic_member_analytics() {
    $user = epic_current_user();
    
    $data = [
        'page_title' => 'Analytics - Member Area',
        'user' => $user
    ];
    
    // Use member layout system
    include __DIR__ . '/../themes/modern/member/analytics.php';
}

/**
 * Get user balance
 */
// epic_get_user_transactions function moved to core/functions.php to avoid duplication

// epic_get_user_referrals function moved to core/functions.php to avoid duplication

/**
 * Handle profile update for member area
 */
function epic_handle_profile_update($user) {
    try {
        // Validate CSRF token if implemented
        // epic_verify_csrf_token();
        
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        // Basic validation
        if (empty($name)) {
            throw new Exception('Nama tidak boleh kosong');
        }
        
        if (strlen($name) < 2) {
            throw new Exception('Nama minimal 2 karakter');
        }
        
        // Handle avatar upload if present
        $avatar_filename = $user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar_filename = epic_handle_avatar_upload($_FILES['avatar'], $user['id']);
        }
        
        // Update user data
        $update_data = [
            'name' => $name,
            'phone' => $phone,
            'avatar' => $avatar_filename,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $updated = db()->update(
            db()->table('users'),
            $update_data,
            ['id' => $user['id']]
        );
        
        if ($updated) {
            // Get user access level for social media fields
        $access_level = epic_get_member_access_level($user);
        
        // Update user profile if exists
        $profile_data = [
            'bio' => $bio,
            'phone' => $phone,
            'avatar' => $avatar_filename,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Add social media fields for EPIC/EPIS users
        if (in_array($access_level, ['epic', 'epis'])) {
            $profile_data['website'] = trim($_POST['website'] ?? '');
            $profile_data['facebook'] = trim($_POST['facebook'] ?? '');
            $profile_data['instagram'] = trim($_POST['instagram'] ?? '');
            $profile_data['twitter'] = trim($_POST['twitter'] ?? '');
            $profile_data['linkedin'] = trim($_POST['linkedin'] ?? '');
        }
            
            // Check if profile exists
            $profile_exists = db()->selectValue(
                "SELECT id FROM " . db()->table('user_profiles') . " WHERE user_id = ?",
                [$user['id']]
            );
            
            if ($profile_exists) {
                db()->update(
                    db()->table('user_profiles'),
                    $profile_data,
                    ['user_id' => $user['id']]
                );
            } else {
                $profile_data['user_id'] = $user['id'];
                db()->insert(db()->table('user_profiles'), $profile_data);
            }
            
            // Redirect with success message
            epic_redirect(epic_url('dashboard/member/profile?updated=1'));
        } else {
            throw new Exception('Gagal mengupdate profil');
        }
        
    } catch (Exception $e) {
        // Redirect with error message
        epic_redirect(epic_url('dashboard/member/profile?error=' . urlencode($e->getMessage())));
    }
}

/**
 * Handle avatar upload
 */
function epic_handle_avatar_upload($file, $user_id) {
    $upload_dir = EPIC_ROOT . '/uploads/profiles/';
    
    // Create directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('Ukuran file terlalu besar. Maksimal 2MB.');
    }
    
    // Generate filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    } else {
        throw new Exception('Gagal mengupload file');
    }
}

?>