<?php
/**
 * EPIC Hub Core Functions
 * Modern core functions for EPIC Hub platform
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

require_once __DIR__ . '/../config/database.php';

// Check if session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// USER MANAGEMENT FUNCTIONS
// =====================================================

/**
 * Get user by ID
 */
function epic_get_user($user_id) {
    return db()->selectOne(
        "SELECT * FROM epic_users WHERE id = ?",
        [$user_id]
    );
}

/**
 * Get user by email
 */
function epic_get_user_by_email($email) {
    return db()->selectOne(
        "SELECT * FROM epic_users WHERE email = ?",
        [strtolower(trim($email))]
    );
}

/**
 * Get user by referral code
 */
function epic_get_user_by_referral_code($referral_code) {
    $table = db()->table(TABLE_USERS);
    return db()->selectOne(
        "SELECT * FROM {$table} WHERE referral_code = ?",
        [strtoupper(trim($referral_code))]
    );
}

/**
 * Create new user
 */
function epic_create_user($data) {
    // Generate UUID and referral code if not provided
    if (!isset($data['uuid'])) {
        $data['uuid'] = epic_generate_uuid();
    }
    if (!isset($data['referral_code'])) {
        $data['referral_code'] = epic_generate_referral_code();
    }
    
    // Hash password if provided
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    return db()->insert(TABLE_USERS, $data);
}

/**
 * Update user
 */
function epic_update_user($user_id, $data) {
    // Hash password if being updated
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    $data['updated_at'] = date('Y-m-d H:i:s');
    
    // Build SET clause
    $set_clauses = [];
    $params = [];
    foreach ($data as $key => $value) {
        $set_clauses[] = "`{$key}` = ?";
        $params[] = $value;
    }
    $params[] = $user_id;
    
    $sql = "UPDATE epic_users SET " . implode(', ', $set_clauses) . " WHERE id = ?";
    return db()->query($sql, $params);
}

/**
 * Verify user password
 */
function epic_verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function epic_is_logged_in() {
    return isset($_SESSION['epic_user_id']) && !empty($_SESSION['epic_user_id']);
}

/**
 * Get current logged in user
 */
function epic_current_user() {
    if (!epic_is_logged_in()) {
        return null;
    }
    
    return epic_get_user($_SESSION['epic_user_id']);
}

/**
 * Alias for epic_current_user() for consistency
 */
function epic_get_current_user() {
    return epic_current_user();
}

/**
 * Check if user is authenticated (alias for epic_is_logged_in)
 */
function epic_is_authenticated() {
    return epic_is_logged_in();
}

/**
 * Get current logged in user ID
 */
function epic_get_current_user_id() {
    if (!epic_is_logged_in()) {
        return null;
    }
    
    return $_SESSION['epic_user_id'];
}

/**
 * Get appropriate redirect URL based on user type
 * 
 * @param array $user User data (optional, uses current user if not provided)
 * @return string Redirect URL
 */
function epic_get_user_redirect_url($user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return epic_url('login');
    }
    
    // Admin and Super Admin go to admin dashboard
    if (in_array($user['role'], ['admin', 'super_admin'])) {
        return epic_url('admin');
    }
    
    // Staff members go to admin dashboard (if staff role exists)
    if ($user['role'] === 'staff') {
        return epic_url('admin');
    }
    
    // Regular users (Free, EPIC, EPIS) go to member area based on status
    if (in_array($user['role'], ['user', 'affiliate'])) {
        return epic_url('dashboard/member');
    }
    
    // Users with specific status go to member area
    if (in_array($user['status'], ['free', 'active', 'pending', 'epic', 'epis', 'premium'])) {
        return epic_url('dashboard/member');
    }
    
    // Suspended or banned users go to login with message
    if (in_array($user['status'], ['suspended', 'banned'])) {
        return epic_url('login?message=account_restricted');
    }
    
    // Fallback to member dashboard for any other user types
    return epic_url('dashboard/member');
}

/**
 * Login user
 */
function epic_login_user($user_id) {
    $_SESSION['epic_user_id'] = $user_id;
    
    // Update last login
    epic_update_user($user_id, ['last_login_at' => date('Y-m-d H:i:s')]);
    
    return true;
}

/**
 * Logout user
 */
function epic_logout_user() {
    unset($_SESSION['epic_user_id']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    return true;
}

/**
 * Register new user
 */
function epic_register_user($data) {
    // Validate required fields
    $required = ['name', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field {$field} is required");
        }
    }
    
    // Check if email already exists
    if (epic_get_user_by_email($data['email'])) {
        throw new Exception('Email address is already registered');
    }
    
    // Generate UUID and referral code
    $uuid = epic_generate_uuid();
    $referral_code = epic_generate_referral_code();
    
    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Prepare user data
    $user_data = [
        'uuid' => $uuid,
        'name' => epic_sanitize($data['name']),
        'email' => strtolower(trim($data['email'])),
        'password' => $hashed_password,
        'phone' => epic_sanitize($data['phone'] ?? ''),
        'referral_code' => $referral_code,
        'status' => 'active',
        'role' => 'user',
        'email_verified' => 0,
        'marketing_consent' => isset($data['marketing']) ? 1 : 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Insert user
    $user_id = db()->insert(TABLE_USERS, $user_data);
    
    if (!$user_id) {
        throw new Exception('Failed to create user account');
    }
    
    // Generate default affiliate code for new user
    $default_affiliate_code = str_pad($user_id, 6, '0', STR_PAD_LEFT);
    db()->update(TABLE_USERS, 
        ['affiliate_code' => $default_affiliate_code], 
        'id = ?', [$user_id]
    );
    
    // Create sponsor relationship if referral code provided
    require_once EPIC_CORE_DIR . '/sponsor.php';
    
    if (!empty($data['referral_code'])) {
        // Create sponsor record with referral code
        epic_create_sponsor($user_id, null, $data['referral_code']);
    } else {
        // Create sponsor record without sponsor (top level)
        epic_create_sponsor($user_id);
    }
    
    // Legacy referral system compatibility
    if (!empty($data['referral_code'])) {
        $referrer = epic_get_user_by_affiliate_code($data['referral_code']);
        
        // Fallback to old referral_code system if not found
        if (!$referrer) {
            $referrer = epic_get_user_by_referral_code($data['referral_code']);
        }
        
        if ($referrer) {
            // Create legacy referral record if exists
            if (function_exists('epic_create_referral')) {
                epic_create_referral($user_id, $referrer['id']);
            }
            
            // Log referral activity
            epic_log_activity($referrer['id'], 'referral_earned', 'New referral from: ' . $user_data['name']);
        }
    }
    
    // Auto-assign EPIS supervisor if referral is from EPIC user
    if (!empty($data['referral_code']) && empty($data['epis_supervisor_id'])) {
        require_once EPIC_ROOT . '/core/epis-functions.php';
        
        $auto_assignment = epic_auto_assign_epis_from_referral($user_id, $data['referral_code']);
        
        if ($auto_assignment && $auto_assignment['success']) {
            // Log successful auto-assignment
            epic_log_activity($user_id, 'epis_auto_assignment_success', 
                "Successfully auto-assigned to EPIS {$auto_assignment['epis_supervisor_id']} via EPIC referrer {$auto_assignment['referrer_id']}");
        }
    }
    
    // Log activity
    epic_log_activity($user_id, 'register', 'User registered');
    
    // Trigger autoresponder for registration
    if (function_exists('epic_autoresponder_on_registration')) {
        $sponsor_data = [];
        if (!empty($data['referral_code'])) {
            $sponsor = epic_get_user_by_referral_code($data['referral_code']);
            if ($sponsor) {
                $sponsor_data = $sponsor;
            }
        }
        
        // Get complete user data for autoresponder
        $complete_user_data = epic_get_user($user_id);
        epic_autoresponder_on_registration($complete_user_data, $sponsor_data);
    }
    
    // Trigger Starsender WhatsApp notification for registration
    if (function_exists('epic_starsender_on_registration')) {
        $sponsor_data = [];
        if (!empty($data['referral_code'])) {
            $sponsor = epic_get_user_by_referral_code($data['referral_code']);
            if ($sponsor) {
                $sponsor_data = $sponsor;
            }
        }
        
        // Get complete user data for Starsender
        $complete_user_data = epic_get_user($user_id);
        epic_starsender_on_registration($complete_user_data, $sponsor_data);
    }
    
    return $user_id;
}

/**
 * Generate password reset token
 */
function epic_generate_reset_token($user_id) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store token in database
    $data = [
        'user_id' => $user_id,
        'token' => $token,
        'type' => 'password_reset',
        'expires_at' => $expires_at,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Delete existing tokens for this user
    db()->delete(TABLE_USER_TOKENS, 'user_id = ? AND type = ?', [$user_id, 'password_reset']);
    
    // Insert new token
    db()->insert(TABLE_USER_TOKENS, $data);
    
    return $token;
}

/**
 * Verify reset token
 */
function epic_verify_reset_token($token) {
    $user_tokens_table = db()->table(TABLE_USER_TOKENS);
    $users_table = db()->table(TABLE_USERS);
    
    $token_data = db()->selectOne(
        "SELECT ut.*, u.email, u.name FROM {$user_tokens_table} ut 
         JOIN {$users_table} u ON ut.user_id = u.id 
         WHERE ut.token = ? AND ut.type = 'password_reset' AND ut.expires_at > NOW()",
        [$token]
    );
    
    return $token_data;
}

/**
 * Reset user password
 */
function epic_reset_password($token, $new_password) {
    $token_data = epic_verify_reset_token($token);
    
    if (!$token_data) {
        throw new Exception('Invalid or expired reset token');
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update user password
    $updated = epic_update_user($token_data['user_id'], [
        'password' => $hashed_password
    ]);
    
    if (!$updated) {
        throw new Exception('Failed to update password');
    }
    
    // Delete used token
    db()->delete('epic_user_tokens', 'token = ?', [$token]);
    
    // Log activity
    epic_log_activity($token_data['user_id'], 'password_reset', 'Password reset successfully');
    
    return true;
}

/**
 * Send password reset email
 */
function epic_send_reset_email($email) {
    $user = epic_get_user_by_email($email);
    
    if (!$user) {
        throw new Exception('Email address not found');
    }
    
    // Generate reset token
    $token = epic_generate_reset_token($user['id']);
    
    // Create reset link
    $reset_link = epic_url('reset-password?token=' . $token);
    
    // Email content
    $subject = 'Reset Your EPIC Hub Password';
    $message = "
    <html>
    <head>
        <title>Reset Your Password</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #3B82F6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>EPIC Hub</h1>
                <p>Password Reset Request</p>
            </div>
            <div class='content'>
                <h2>Hello {$user['name']},</h2>
                <p>We received a request to reset your password for your EPIC Hub account.</p>
                <p>Click the button below to reset your password:</p>
                <p><a href='{$reset_link}' class='button'>Reset Password</a></p>
                <p>Or copy and paste this link into your browser:</p>
                <p><a href='{$reset_link}'>{$reset_link}</a></p>
                <p><strong>This link will expire in 1 hour.</strong></p>
                <p>If you didn't request this password reset, please ignore this email.</p>
                <p>Best regards,<br>The EPIC Hub Team</p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " EPIC Hub. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Send email
    return epic_send_email($email, $subject, $message);
}

/**
 * Validate email format
 */
function epic_validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Send email function
 */
function epic_send_email($to, $subject, $message, $from_name = null, $from_email = null) {
    $from_name = $from_name ?: (defined('EPIC_MAIL_FROM_NAME') ? EPIC_MAIL_FROM_NAME : 'EPIC Hub');
    $from_email = $from_email ?: (defined('EPIC_MAIL_FROM_EMAIL') ? EPIC_MAIL_FROM_EMAIL : 'noreply@epichub.local');
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        "From: {$from_name} <{$from_email}>",
        "Reply-To: {$from_email}",
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

// =====================================================
// ACCESS CONTROL FUNCTIONS
// =====================================================

/**
 * Check if user has admin access
 */
function epic_is_admin($user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return false;
    }
    
    return in_array($user['role'], ['admin', 'super_admin']);
}

/**
 * Check if user has epic account (premium)
 */
function epic_is_epic_account($user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return false;
    }
    
    return $user['status'] === 'premium' && in_array($user['role'], ['affiliate', 'user']);
}

/**
 * Check if user has free account
 */
function epic_is_free_account($user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return false;
    }
    
    return in_array($user['status'], ['active', 'pending']) && $user['role'] === 'user' && !epic_is_epic_account($user);
}

/**
 * Get user access level
 */
function epic_get_user_access_level($user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return 'guest';
    }
    
    // Check for admin roles first (highest priority)
    if ($user['role'] === 'super_admin') {
        return 'super_admin';
    }
    
    if ($user['role'] === 'admin') {
        return 'admin';
    }
    
    if ($user['role'] === 'staff') {
        return 'staff';
    }
    
    // Check for suspended/banned status
    if (in_array($user['status'], ['suspended', 'banned'])) {
        return 'inactive';
    }
    
    // Check for premium/epic status
    if ($user['status'] === 'premium' || $user['status'] === 'epic') {
        return 'epic';
    }
    
    // Check for EPIS status
    if ($user['status'] === 'epis') {
        return 'epis';
    }
    
    // Check for active/free status
    if (in_array($user['status'], ['active', 'free', 'pending'])) {
        return 'free';
    }
    
    // Fallback for any other status
    return 'inactive';
}

/**
 * Check if user can access feature
 */
function epic_can_access_feature($feature, $user = null) {
    $access_level = epic_get_user_access_level($user);
    
    $feature_permissions = [
        'super_admin' => [
            'admin_panel', 'user_management', 'system_settings', 'analytics_full',
            'landing_pages_all', 'referral_system', 'commission_management',
            'template_management', 'email_system', 'backup_restore',
            'system_monitoring', 'database_management', 'security_settings',
            'global_settings', 'user_roles_management', 'system_logs'
        ],
        'admin' => [
            'admin_panel', 'user_management', 'system_settings', 'analytics_full',
            'landing_pages_all', 'referral_system', 'commission_management',
            'template_management', 'email_system', 'backup_restore'
        ],
        'staff' => [
            'admin_panel', 'user_management', 'analytics_basic',
            'landing_pages_management', 'support_management',
            'content_management', 'order_management'
        ],
        'epis' => [
            'landing_pages_premium', 'referral_system', 'analytics_advanced',
            'commission_tracking', 'priority_support', 'custom_domain',
            'email_automation', 'conversion_tracking', 'template_all',
            'team_management', 'advanced_analytics', 'commission_management',
            'epic_recruitment', 'territory_management'
        ],
        'epic' => [
            'landing_pages_premium', 'referral_system', 'analytics_advanced',
            'commission_tracking', 'priority_support', 'custom_domain',
            'email_automation', 'conversion_tracking', 'template_all'
        ],
        'free' => [
            'profile_basic', 'landing_page_basic', 'support_basic',
            'basic_products', 'basic_orders'
        ]
    ];
    
    if (!isset($feature_permissions[$access_level])) {
        return false;
    }
    
    return in_array($feature, $feature_permissions[$access_level]);
}

/**
 * Require specific access level
 */
function epic_require_access_level($required_level, $redirect_url = null) {
    $user = epic_current_user();
    $current_level = epic_get_user_access_level($user);
    
    $level_hierarchy = [
        'guest' => 0,
        'inactive' => 1,
        'free' => 2,
        'epic' => 3,
        'epis' => 4,
        'staff' => 5,
        'admin' => 6,
        'super_admin' => 7
    ];
    
    if (!isset($level_hierarchy[$current_level]) || !isset($level_hierarchy[$required_level])) {
        epic_route_403();
        return;
    }
    
    if ($level_hierarchy[$current_level] < $level_hierarchy[$required_level]) {
        if ($redirect_url) {
            epic_redirect($redirect_url);
        } else {
            epic_route_403();
        }
        return;
    }
}

/**
 * Require specific feature access
 */
function epic_require_feature($feature, $redirect_url = null) {
    if (!epic_can_access_feature($feature)) {
        if ($redirect_url) {
            epic_redirect($redirect_url);
        } else {
            epic_route_403();
        }
        return;
    }
}

/**
 * Get available features for user
 */
function epic_get_user_features($user = null) {
    $access_level = epic_get_user_access_level($user);
    
    $all_features = [
        'super_admin' => [
            'admin_panel' => 'Full Admin Panel Access',
            'user_management' => 'Complete User Management',
            'system_settings' => 'System Configuration',
            'analytics_full' => 'Complete Analytics Suite',
            'landing_pages_all' => 'All Landing Page Templates',
            'referral_system' => 'Advanced Referral System',
            'commission_management' => 'Commission Management',
            'template_management' => 'Template Creation & Editing',
            'email_system' => 'Email Marketing System',
            'backup_restore' => 'Backup & Restore',
            'system_monitoring' => 'Real-time System Monitoring',
            'database_management' => 'Database Management',
            'security_settings' => 'Security Configuration',
            'global_settings' => 'Global System Settings',
            'user_roles_management' => 'User Roles Management',
            'system_logs' => 'System Logs Access'
        ],
        'admin' => [
            'admin_panel' => 'Admin Panel Access',
            'user_management' => 'User Management',
            'system_settings' => 'System Configuration',
            'analytics_full' => 'Complete Analytics Suite',
            'landing_pages_all' => 'All Landing Page Templates',
            'referral_system' => 'Advanced Referral System',
            'commission_management' => 'Commission Management',
            'template_management' => 'Template Creation & Editing',
            'email_system' => 'Email Marketing System',
            'backup_restore' => 'Backup & Restore'
        ],
        'staff' => [
            'admin_panel' => 'Limited Admin Panel Access',
            'user_management' => 'Basic User Management',
            'analytics_basic' => 'Basic Analytics',
            'landing_pages_management' => 'Landing Pages Management',
            'support_management' => 'Support Management',
            'content_management' => 'Content Management',
            'order_management' => 'Order Management'
        ],
        'epis' => [
            'landing_pages_premium' => 'Premium Landing Page Templates',
            'referral_system' => 'Active Referral System',
            'analytics_advanced' => 'Advanced Analytics & Tracking',
            'commission_tracking' => 'Commission Tracking',
            'priority_support' => '24/7 Priority Support',
            'custom_domain' => 'Custom Domain Support',
            'email_automation' => 'Email Marketing Automation',
            'conversion_tracking' => 'Advanced Conversion Tracking',
            'template_all' => 'All Premium Templates',
            'team_management' => 'Team Management',
            'advanced_analytics' => 'Advanced Analytics Suite',
            'commission_management' => 'Commission Management',
            'epic_recruitment' => 'EPIC Account Recruitment',
            'territory_management' => 'Territory Management'
        ],
        'epic' => [
            'landing_pages_premium' => 'Premium Landing Page Templates',
            'referral_system' => 'Active Referral System',
            'analytics_advanced' => 'Advanced Analytics & Tracking',
            'commission_tracking' => 'Commission Tracking',
            'priority_support' => '24/7 Priority Support',
            'custom_domain' => 'Custom Domain Support',
            'email_automation' => 'Email Marketing Automation',
            'conversion_tracking' => 'Advanced Conversion Tracking',
            'template_all' => 'All Premium Templates'
        ],
        'free' => [
            'profile_basic' => 'Basic Profile Management',
            'landing_page_basic' => 'Basic Landing Page (1 Template)',
            'support_basic' => 'Email Support (48h Response)',
            'basic_products' => 'Basic Product Access',
            'basic_orders' => 'Basic Order Management'
        ]
    ];
    
    return $all_features[$access_level] ?? [];
}

/**
 * Upgrade user to epic account
 */
function epic_upgrade_to_epic_account($user_id) {
    $updated = epic_update_user($user_id, [
        'status' => 'premium',
        'role' => 'affiliate'
    ]);
    
    if ($updated) {
        // Log upgrade activity
        epic_log_activity($user_id, 'account_upgrade', 'Account upgraded to Epic Account');
        
        // Send welcome email for epic account
        $user = epic_get_user($user_id);
        if ($user) {
            epic_send_epic_welcome_email($user);
        }
        
        // Trigger autoresponder for upgrade
        if (function_exists('epic_autoresponder_on_upgrade') && $user) {
            $sponsor_data = [];
            $referral = epic_get_referral($user_id);
            if ($referral && $referral['referrer_id']) {
                $sponsor_data = epic_get_user($referral['referrer_id']);
            }
            
            epic_autoresponder_on_upgrade($user, $sponsor_data);
        }
        
        // Trigger Starsender WhatsApp notification for upgrade
        if (function_exists('epic_starsender_on_upgrade') && $user) {
            $sponsor_data = [];
            $referral = epic_get_referral($user_id);
            if ($referral && $referral['referrer_id']) {
                $sponsor_data = epic_get_user($referral['referrer_id']);
            }
            
            epic_starsender_on_upgrade($user, $sponsor_data);
        }
        
        return true;
    }
    
    return false;
}

/**
 * Send welcome email for epic account
 */
function epic_send_epic_welcome_email($user) {
    $subject = 'Welcome to EPIC Hub - Epic Account Activated!';
    $message = "
    <html>
    <head>
        <title>Welcome to Epic Account</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .feature { background: white; margin: 10px 0; padding: 15px; border-radius: 5px; border-left: 4px solid #f5576c; }
            .cta { display: inline-block; background: #f5576c; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Welcome to Epic Account!</h1>
                <p>Your premium features are now active</p>
            </div>
            <div class='content'>
                <h2>Hello {$user['name']},</h2>
                <p>Congratulations! Your EPIC Hub account has been upgraded to <strong>Epic Account</strong>.</p>
                
                <h3>🚀 Your New Features:</h3>
                <div class='feature'>
                    <strong>✅ Premium Landing Pages</strong><br>
                    Access to all premium templates with advanced customization
                </div>
                <div class='feature'>
                    <strong>✅ Active Referral System</strong><br>
                    Start earning commissions from your referrals immediately
                </div>
                <div class='feature'>
                    <strong>✅ Advanced Analytics</strong><br>
                    Detailed tracking and conversion analytics
                </div>
                <div class='feature'>
                    <strong>✅ Priority Support</strong><br>
                    24/7 priority support with faster response times
                </div>
                
                <p><a href='" . epic_url('dashboard') . "' class='cta'>Access Your Epic Dashboard</a></p>
                
                <p>Start maximizing your earning potential today!</p>
                <p>Best regards,<br>The EPIC Hub Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return epic_send_email($user['email'], $subject, $message);
}

// =====================================================
// REFERRAL SYSTEM FUNCTIONS
// =====================================================

/**
 * Get user referral data
 */
function epic_get_referral($user_id) {
    return db()->selectOne(
        "SELECT * FROM " . TABLE_REFERRALS . " WHERE user_id = ?",
        [$user_id]
    );
}

/**
 * Create referral relationship
 */
function epic_create_referral($user_id, $referrer_id = null) {
    $data = [
        'user_id' => $user_id,
        'referrer_id' => $referrer_id,
        'status' => 'active'
    ];
    
    return db()->insert('referrals', $data);
}

/**
 * Get user's referrals (downline)
 */
function epic_get_user_referrals($user_id, $status = null) {
    $users_table = db()->table(TABLE_USERS);
    $referrals_table = db()->table(TABLE_REFERRALS);
    
    $sql = "SELECT u.*, r.created_at as referred_at, r.referral_date, r.total_earnings, r.total_sales 
            FROM {$users_table} u 
            JOIN {$referrals_table} r ON u.id = r.user_id 
            WHERE r.referrer_id = ?";
    $params = [$user_id];
    
    if ($status !== null) {
        $sql .= " AND r.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY r.created_at DESC";
    
    return db()->select($sql, $params);
}

/**
 * Update referral statistics
 */
// Duplicate function removed - epic_update_referral_stats defined below with better implementation

// =====================================================
// PRODUCT MANAGEMENT FUNCTIONS
// =====================================================

/**
 * Get product by ID
 */
function epic_get_product($product_id) {
    return db()->selectOne(
        "SELECT * FROM epic_products WHERE id = ?",
        [$product_id]
    );
}

/**
 * Get product by slug
 */
function epic_get_product_by_slug($slug) {
    return db()->selectOne(
        "SELECT * FROM epic_products WHERE slug = ? AND status = 'active'",
        [$slug]
    );
}

/**
 * Get all active products
 */
function epic_get_products($limit = null, $offset = 0) {
    $sql = "SELECT * FROM epic_products WHERE status = 'active' ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
    }
    
    return db()->select($sql);
}

/**
 * Create new product
 */
function epic_create_product($data) {
    if (!isset($data['uuid'])) {
        $data['uuid'] = epic_generate_uuid();
    }
    
    return db()->insert('products', $data);
}

/**
 * Update product
 */
function epic_update_product($product_id, $data) {
    $data['updated_at'] = date('Y-m-d H:i:s');
    return db()->update('products', $data, 'id = ?', [$product_id]);
}

// =====================================================
// ORDER MANAGEMENT FUNCTIONS
// =====================================================

/**
 * Get order by ID
 */
function epic_get_order($order_id) {
    $orders_table = db()->table(TABLE_ORDERS);
    $users_table = db()->table(TABLE_USERS);
    $products_table = db()->table(TABLE_PRODUCTS);
    
    return db()->selectOne(
        "SELECT o.*, u.name as user_name, u.email as user_email, 
                p.name as product_name, p.price as product_price
         FROM {$orders_table} o
         JOIN {$users_table} u ON o.user_id = u.id
         JOIN {$products_table} p ON o.product_id = p.id
         WHERE o.id = ?",
        [$order_id]
    );
}

/**
 * Get order by order number
 */
// Duplicate function removed - epic_get_order_by_number defined in order.php with better implementation

/**
 * Create new order
 */
// Duplicate function removed - epic_create_order defined below with better order number generation

/**
 * Update order status
 */
function epic_update_order_status($order_id, $status, $staff_id = null) {
    $data = [
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($status === 'paid') {
        $data['paid_at'] = date('Y-m-d H:i:s');
    }
    
    if ($staff_id) {
        $data['staff_id'] = $staff_id;
    }
    
    return db()->update('orders', $data, 'id = ?', [$order_id]);
}

/**
 * Process order payment
 */
function epic_process_order_payment($order_id, $staff_id = null) {
    db()->beginTransaction();
    
    try {
        // Get order details
        $order = epic_get_order($order_id);
        if (!$order || $order['status'] !== 'pending') {
            throw new Exception('Invalid order or already processed');
        }
        
        // Update order status
        epic_update_order_status($order_id, 'paid', $staff_id);
        
        // Update user status to premium if product has price
        if ($order['product_price'] > 0) {
            epic_update_user($order['user_id'], ['status' => 'premium']);
        }
        
        // Create sale transaction
        epic_create_transaction([
            'order_id' => $order_id,
            'user_id' => $order['user_id'],
            'type' => 'sale',
            'amount_in' => $order['amount'],
            'status' => 'completed',
            'description' => 'Sale: ' . $order['product_name']
        ]);
        
        // Process commission if there's a referrer
        if ($order['referrer_id'] && $order['commission_amount'] > 0) {
            epic_create_transaction([
                'order_id' => $order_id,
                'user_id' => $order['referrer_id'],
                'referrer_id' => $order['user_id'],
                'type' => 'commission',
                'amount_in' => $order['commission_amount'],
                'status' => 'completed',
                'description' => 'Commission: ' . $order['product_name']
            ]);
            
            // Update referral stats
            epic_update_referral_stats($order['referrer_id'], $order['commission_amount'], $order['amount']);
        }
        
        db()->commit();
        return true;
        
    } catch (Exception $e) {
        db()->rollback();
        throw $e;
    }
}

// =====================================================
// TRANSACTION FUNCTIONS
// =====================================================

/**
 * Create transaction
 */
function epic_create_transaction($data) {
    return db()->insert(TABLE_TRANSACTIONS, $data);
}

/**
 * Get user transactions
 */
function epic_get_user_transactions($user_id, $type = null, $limit = 50) {
    $table = db()->table(TABLE_TRANSACTIONS);
    $sql = "SELECT * FROM {$table} WHERE user_id = ?";
    $params = [$user_id];
    
    if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT {$limit}";
    
    return db()->select($sql, $params);
}

/**
 * Get user balance
 */
function epic_get_user_balance($user_id) {
    $transactions_table = db()->table(TABLE_TRANSACTIONS);
    
    $balance = db()->selectValue(
        "SELECT (COALESCE(SUM(amount_in), 0) - COALESCE(SUM(amount_out), 0)) as balance
         FROM {$transactions_table}
         WHERE user_id = ? AND status = 'completed'",
        [$user_id]
    );
    
    return $balance ?: 0;
}

// =====================================================
// SETTINGS FUNCTIONS
// =====================================================

// epic_get_setting function moved to bootstrap.php as epic_setting

/**
 * Set setting value
 */
function epic_set_setting($key, $value, $type = 'string') {
    $exists = db()->exists('settings', '`key` = ?', [$key]);
    
    if ($exists) {
        return db()->update('settings', 
            ['value' => $value, 'type' => $type, 'updated_at' => date('Y-m-d H:i:s')],
            '`key` = ?', 
            [$key]
        );
    } else {
        return db()->insert('settings', [
            'key' => $key,
            'value' => $value,
            'type' => $type
        ]);
    }
}

/**
 * Get all settings
 */
function epic_get_all_settings() {
    $settings = db()->select("SELECT `key`, `value`, `type` FROM " . TABLE_SETTINGS);
    $result = [];
    
    foreach ($settings as $setting) {
        $result[$setting['key']] = $setting['value'];
    }
    
    return $result;
}

// =====================================================
// UTILITY FUNCTIONS
// =====================================================

/**
 * Generate UUID v4
 */
function epic_generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Generate referral code
 */
function epic_generate_referral_code($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    
    do {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
    } while (db()->exists(TABLE_USERS, 'referral_code = ?', [$code]));
    
    return $code;
}

/**
 * Generate order number
 */
function epic_generate_order_number() {
    $prefix = 'EPIC';
    $timestamp = date('ymd');
    $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    return $prefix . $timestamp . $random;
}

/**
 * Sanitize input
 */
function epic_sanitize($input) {
    if (is_array($input)) {
        return array_map('epic_sanitize', $input);
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
// Duplicate function removed - epic_validate_email already defined above

/**
 * Generate slug from string
 */
function epic_generate_slug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Format currency
 */
// Duplicate function removed - epic_format_currency defined below with better implementation

/**
 * Log activity
 */
function epic_log_activity($user_id, $action, $description = null, $model_type = null, $model_id = null) {
    try {
        $sql = "INSERT INTO epic_activity_log (user_id, action, description, model_type, model_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        return db()->query($sql, [
            $user_id,
            $action,
            is_array($description) ? json_encode($description) : $description,
            $model_type,
            $model_id,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        // Log error but don't break the main functionality
        error_log('Activity log error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send notification
 */
function epic_send_notification($user_id, $type, $title, $message, $channels = ['dashboard'], $data = null) {
    return db()->insert('notifications', [
        'user_id' => $user_id,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'channels' => json_encode($channels),
        'data' => $data ? json_encode($data) : null,
        'status' => 'pending'
    ]);
}

// =====================================================
// LEGACY COMPATIBILITY FUNCTIONS
// =====================================================

/**
 * Legacy function compatibility
 */
function cek($input) {
    return epic_sanitize($input);
}

function txtonly($input) {
    return preg_replace('/[^a-zA-Z0-9]/', '', $input);
}

function numonly($input) {
    return preg_replace('/[^0-9]/', '', $input);
}

function create_hash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function validate_password($password, $hash) {
    return epic_verify_password($password, $hash);
}

function is_login() {
    return epic_is_logged_in() ? epic_current_user()['id'] : false;
}

// =====================================================
// MISSING FUNCTIONS IMPLEMENTATION
// =====================================================

/**
 * Check if feature is enabled
 */
function epic_feature_enabled($feature) {
    $feature_map = [
        'registration' => defined('EPIC_FEATURE_REGISTRATION') ? EPIC_FEATURE_REGISTRATION : true,
        'referrals' => defined('EPIC_FEATURE_REFERRALS') ? EPIC_FEATURE_REFERRALS : true,
        'commissions' => defined('EPIC_FEATURE_COMMISSIONS') ? EPIC_FEATURE_COMMISSIONS : true,
        'analytics' => defined('EPIC_FEATURE_ANALYTICS') ? EPIC_FEATURE_ANALYTICS : true,
        'api' => defined('EPIC_FEATURE_API') ? EPIC_FEATURE_API : true,
        'blog' => defined('EPIC_FEATURE_BLOG') ? EPIC_FEATURE_BLOG : true
    ];
    
    return $feature_map[$feature] ?? false;
}

/**
 * Get URL helper function
 */
function epic_url($path = '') {
    global $weburl;
    if (!isset($weburl)) {
        $weburl = 'http://localhost/bisnisemasperak';
    }
    return rtrim($weburl, '/') . '/' . ltrim($path, '/');
}

/**
 * Get asset URL
 */
function epic_asset($path) {
    return epic_url('assets/' . ltrim($path, '/'));
}

/**
 * Get theme URL
 */
function epic_theme_url($path = '') {
    $theme = epic_setting('theme', 'modern');
    return epic_url('themes/' . $theme . '/' . ltrim($path, '/'));
}

/**
 * Redirect helper function
 */
function epic_redirect($url, $code = 302) {
    if (!headers_sent()) {
        header('Location: ' . $url, true, $code);
        exit;
    }
    echo '<script>window.location.href="' . htmlspecialchars($url) . '";</script>';
    exit;
}

/**
 * JSON response helper
 */
function epic_json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Flash message helper
 */
function epic_flash($type, $message) {
    if (!isset($_SESSION['epic_flash'])) {
        $_SESSION['epic_flash'] = [];
    }
    $_SESSION['epic_flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Get flash messages
 */
function epic_get_flash() {
    $messages = $_SESSION['epic_flash'] ?? [];
    unset($_SESSION['epic_flash']);
    return $messages;
}

/**
 * Check if request is AJAX
 */
function epic_is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get request method
 */
function epic_request_method() {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * Check if request is POST
 */
function epic_is_post() {
    return epic_request_method() === 'POST';
}

/**
 * Check if request is GET
 */
function epic_is_get() {
    return epic_request_method() === 'GET';
}

/**
 * Get POST data
 */
function epic_post($key = null, $default = null) {
    if ($key === null) {
        return $_POST;
    }
    return $_POST[$key] ?? $default;
}

/**
 * Get GET data
 */
function epic_get($key = null, $default = null) {
    if ($key === null) {
        return $_GET;
    }
    return $_GET[$key] ?? $default;
}

/**
 * Get request data (POST or GET)
 */
function epic_request($key = null, $default = null) {
    if ($key === null) {
        return array_merge($_GET, $_POST);
    }
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

// Route functions moved to index.php to avoid duplication

/**
 * Handle logo and favicon upload
 */
function epic_handle_logo_upload($file, $type = 'logo') {
    try {
        // Validate file size based on type
        $maxSize = ($type === 'favicon') ? 1048576 : 2097152; // 1MB for favicon, 2MB for logo
        if ($file['size'] > $maxSize) {
            $maxSizeMB = $maxSize / 1048576;
            return ['success' => false, 'error' => "Ukuran file melebihi batas {$maxSizeMB}MB"];
        }
        
        // Validate file type based on type
        $allowedTypes = ($type === 'favicon') 
            ? ['image/x-icon', 'image/png', 'image/jpeg', 'image/jpg', 'image/gif']
            : ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Format file tidak didukung. Gunakan format yang diizinkan.'];
        }
        
        // Additional validation for file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ($type === 'favicon')
            ? ['ico', 'png', 'jpg', 'jpeg', 'gif']
            : ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            
        if (!in_array($extension, $allowedExtensions)) {
            return ['success' => false, 'error' => 'Ekstensi file tidak didukung.'];
        }
        
        // Create logos directory if it doesn't exist
        $logoDir = EPIC_ROOT . '/uploads/logos/';
        if (!is_dir($logoDir)) {
            if (!mkdir($logoDir, 0755, true)) {
                return ['success' => false, 'error' => 'Gagal membuat direktori upload.'];
            }
        }
        
        // Create unique filename
        $prefix = ($type === 'favicon') ? 'favicon' : 'logo';
        $filename = $prefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        $uploadPath = $logoDir . $filename;
        
        // Check if file already exists (unlikely but safe)
        if (file_exists($uploadPath)) {
            $filename = $prefix . '_' . time() . '_' . rand(10000, 99999) . '.' . $extension;
            $uploadPath = $logoDir . $filename;
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => false, 'error' => 'Gagal menyimpan file yang diupload.'];
        }
        
        // Additional validation: check if it's actually an image
        if ($type !== 'favicon' || $extension !== 'ico') {
            $imageInfo = getimagesize($uploadPath);
            if ($imageInfo === false) {
                unlink($uploadPath); // Delete invalid file
                return ['success' => false, 'error' => 'File yang diupload bukan gambar yang valid.'];
            }
            
            // Optional: Check image dimensions for recommendations
            if ($type === 'logo') {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                // Just log a warning, don't fail the upload
                if ($width < 100 || $height < 30) {
                    error_log("Logo uploaded with small dimensions: {$width}x{$height}");
                }
            } elseif ($type === 'favicon') {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                if ($width > 256 || $height > 256) {
                    error_log("Favicon uploaded with large dimensions: {$width}x{$height}");
                }
            }
        }
        
        return [
            'success' => true, 
            'message' => ucfirst($type) . ' berhasil diupload',
            'filename' => $filename,
            'url' => epic_url('uploads/logos/' . $filename)
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Upload gagal: ' . $e->getMessage()];
    }
}

/**
 * Render template function
 */
function epic_render_template($template, $data = []) {
    $theme = epic_setting('theme', 'modern');
    $theme_dir = __DIR__ . '/../themes/' . $theme;
    $template_file = $theme_dir . '/' . $template . '.php';
    
    // Fallback to default theme if template not found
    if (!file_exists($template_file)) {
        $template_file = __DIR__ . '/../themes/modern/' . $template . '.php';
    }
    
    if (!file_exists($template_file)) {
        // Create a simple error template if none exists
        echo '<h1>Template Error</h1>';
        echo '<p>Template not found: ' . htmlspecialchars($template) . '</p>';
        return;
    }
    
    // Extract data to variables
    extract($data);
    
    // Start output buffering
    ob_start();
    
    // Include template
    include $template_file;
    
    // Get content and clean buffer
    $content = ob_get_clean();
    
    // Output content
    echo $content;
}

/**
 * Format currency helper
 */
function epic_format_currency($amount, $currency = 'IDR') {
    if ($currency === 'IDR') {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Validate email helper
 */
// Duplicate function removed - epic_validate_email already defined above

/**
 * Get product functions
 */
// Duplicate function removed - epic_get_product already defined above

// Duplicate function removed - epic_get_product_by_slug already defined above with better implementation

// Duplicate function removed - epic_get_products already defined above with better implementation

/**
 * Order functions
 */
// Duplicate function removed - epic_get_order already defined above with better implementation

function epic_create_order($data) {
    // Generate order number if not provided
    if (!isset($data['order_number'])) {
        $data['order_number'] = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
    
    // Generate UUID if not provided
    if (!isset($data['uuid'])) {
        $data['uuid'] = epic_generate_uuid();
    }
    
    return db()->insert('orders', $data);
}

/**
 * Transaction functions
 */
// Duplicate function removed - epic_create_transaction already defined above

/**
 * Update referral stats
 */
function epic_update_referral_stats($user_id, $commission_amount, $sale_amount) {
    return db()->query(
        "UPDATE " . TABLE_REFERRALS . " 
         SET total_earnings = total_earnings + ?, 
             total_sales = total_sales + ?,
             updated_at = NOW()
         WHERE user_id = ?",
        [$commission_amount, $sale_amount, $user_id]
    );
}

// Duplicate function removed - epic_get_all_settings already defined above

/**
 * Set setting value
 */
// Duplicate function removed - epic_set_setting already defined above with better implementation

// =====================================================
// REFERRAL COOKIES TRACKING FUNCTIONS
// =====================================================

/**
 * Set referral tracking cookie
 */
function epic_set_referral_cookie($referral_code, $referrer_name = '', $days = 30) {
    $cookie_data = [
        'code' => $referral_code,
        'name' => $referrer_name,
        'timestamp' => time(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $cookie_value = base64_encode(json_encode($cookie_data));
    $expire_time = time() + ($days * 24 * 60 * 60);
    
    // Set multiple cookies for redundancy
    setcookie('epic_referral', $cookie_value, $expire_time, '/', '', false, true);
    setcookie('epic_ref_code', $referral_code, $expire_time, '/', '', false, true);
    setcookie('epic_ref_name', $referrer_name, $expire_time, '/', '', false, true);
    
    // Also store in session as backup
    $_SESSION['epic_referrer_code'] = $referral_code;
    $_SESSION['epic_referrer_name'] = $referrer_name;
    $_SESSION['epic_referrer_timestamp'] = time();
    
    return true;
}

/**
 * Get referral tracking from cookies or session
 */
function epic_get_referral_tracking() {
    // First try to get from cookies
    if (isset($_COOKIE['epic_referral'])) {
        $cookie_data = json_decode(base64_decode($_COOKIE['epic_referral']), true);
        if ($cookie_data && isset($cookie_data['code'])) {
            return [
                'code' => $cookie_data['code'],
                'name' => $cookie_data['name'] ?? '',
                'timestamp' => $cookie_data['timestamp'] ?? time(),
                'source' => 'cookie'
            ];
        }
    }
    
    // Fallback to individual cookies
    if (isset($_COOKIE['epic_ref_code'])) {
        return [
            'code' => $_COOKIE['epic_ref_code'],
            'name' => $_COOKIE['epic_ref_name'] ?? '',
            'timestamp' => time(),
            'source' => 'cookie_fallback'
        ];
    }
    
    // Fallback to session
    if (isset($_SESSION['epic_referrer_code'])) {
        return [
            'code' => $_SESSION['epic_referrer_code'],
            'name' => $_SESSION['epic_referrer_name'] ?? '',
            'timestamp' => $_SESSION['epic_referrer_timestamp'] ?? time(),
            'source' => 'session'
        ];
    }
    
    return null;
}

/**
 * Clear referral tracking cookies and session
 */
function epic_clear_referral_tracking() {
    // Clear cookies
    setcookie('epic_referral', '', time() - 3600, '/');
    setcookie('epic_ref_code', '', time() - 3600, '/');
    setcookie('epic_ref_name', '', time() - 3600, '/');
    
    // Clear session
    unset($_SESSION['epic_referrer_code']);
    unset($_SESSION['epic_referrer_name']);
    unset($_SESSION['epic_referrer_timestamp']);
    
    return true;
}

/**
 * Check if referral tracking is active
 */
function epic_has_referral_tracking() {
    return epic_get_referral_tracking() !== null;
}

/**
 * Get referrer information with validation
 */
function epic_get_referrer_info($referral_code = null) {
    if (!$referral_code) {
        $tracking = epic_get_referral_tracking();
        if (!$tracking) {
            return null;
        }
        $referral_code = $tracking['code'];
    }
    
    // Try to find by affiliate code first
    $referrer = db()->selectOne(
        "SELECT id, name, email, referral_code, affiliate_code, status, role FROM epic_users WHERE affiliate_code = ?",
        [$referral_code]
    );
    
    // Fallback to referral_code
    if (!$referrer) {
        $referrer = db()->selectOne(
            "SELECT id, name, email, referral_code, affiliate_code, status, role FROM epic_users WHERE referral_code = ?",
            [$referral_code]
        );
    }
    
    if (!$referrer) {
        return null;
    }
    
    // Check if referrer is eligible (EPIC Account only if setting enabled)
    $epic_account_only = epic_setting('epic_account_only', '1') == '1';
    if ($epic_account_only && $referrer['status'] !== 'premium') {
        return null;
    }
    
    return $referrer;
}

/**
 * Process referral tracking on page load
 */
function epic_process_referral_tracking() {
    // Check for referral code in URL parameters
    $ref_param = $_GET['ref'] ?? $_GET['referral'] ?? $_GET['affiliate'] ?? '';
    
    if (!empty($ref_param)) {
        // Validate referral code
        $referrer = epic_get_referrer_info($ref_param);
        
        if ($referrer) {
            // Set tracking cookies
            epic_set_referral_cookie($ref_param, $referrer['name']);
            
            // Log referral click
            epic_log_activity($referrer['id'], 'referral_click', 'Referral link accessed from: ' . ($_SERVER['HTTP_REFERER'] ?? 'direct'));
            
            return true;
        }
    }
    
    return false;
}

// epic_get_member_stats() function moved to themes/modern/member/layout-helper.php
// to avoid duplication and use the more comprehensive implementation for member area

/**
 * Calculate profile completion percentage
 * 
 * @param int $user_id User ID
 * @return int Percentage (0-100)
 */
function epic_calculate_profile_completion($user_id) {
    $user = epic_get_user($user_id);
    
    if (!$user) {
        return 0;
    }
    
    $fields = [
        'name' => !empty($user['name']),
        'email' => !empty($user['email']),
        'phone' => !empty($user['phone']),
        'profile_picture' => !empty($user['profile_picture']),
        'bio' => !empty($user['bio']),
        'address' => !empty($user['address'])
    ];
    
    $completed = array_sum($fields);
    $total = count($fields);
    
    return round(($completed / $total) * 100);
}

// epic_member_can_access() function moved to themes/modern/member/layout-helper.php
// to avoid duplication and use the more comprehensive implementation

/**
 * Get upgrade benefits for free users
 * 
 * @return array
 */
function epic_get_upgrade_benefits() {
    return [
        'Akses ke semua template landing page premium',
        'Sistem referral/affiliate yang aktif',
        'Analytics dan tracking lengkap',
        'Support prioritas 24/7',
        'Custom domain untuk landing page',
        'Email marketing automation',
        'Advanced conversion tracking',
        'Bonus dan komisi dari referral',
        'Akses ke produk premium',
        'Training dan webinar eksklusif'
    ];
}

/**
 * Get current URL
 * Returns the full current URL including protocol, host, and path
 */
function epic_current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    
    return $protocol . '://' . $host . $uri;
}

?>