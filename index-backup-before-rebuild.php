<?php
/**
 * EPIC Hub - Main Entry Point
 * Modern Affiliate Marketing & Referral Platform
 * 
 * @version 2.0.0
 * @author Arva Team
 */

// Initialize EPIC Hub
require_once __DIR__ . '/bootstrap.php';

// Check if this is an API request
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    require_once EPIC_CORE_DIR . '/api.php';
    exit;
}

// Parse the request
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);

// Remove base path from request URI
if ($base_path !== '/') {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Remove query string
$request_uri = strtok($request_uri, '?');

// Clean up the URI
$request_uri = trim($request_uri, '/');

// Split into segments
$segments = $request_uri ? explode('/', $request_uri) : [];

// Route the request
try {
    // Handle special routes first
    switch ($segments[0] ?? '') {
        case '':
        case 'home':
            epic_route_home();
            break;
            
        case 'login':
            epic_route_login();
            break;
            
        case 'register':
            epic_route_register();
            break;
            
        case 'logout':
            epic_route_logout();
            break;
            
        case 'forgot-password':
            epic_route_forgot_password();
            break;
            
        case 'reset-password':
            epic_route_reset_password();
            break;
            
        case 'email-confirmation':
            epic_route_email_confirmation();
            break;
            
        case 'confirm-email':
            epic_route_confirm_email($segments);
            break;
            
        case 'resend-confirmation':
            epic_route_resend_confirmation();
            break;
            
        case 'dashboard':
            epic_route_dashboard($segments);
            break;
            
        case 'product':
        case 'products':
            epic_route_products($segments);
            break;
            
        case 'order':
            epic_route_order($segments);
            break;
            
        case 'article':
        case 'articles':
            epic_route_articles($segments);
            break;
            
        case 'blog':
            epic_route_blog($segments);
            break;
            
        case 'api':
            epic_route_api($segments);
            break;
            
        case 'admin':
            epic_route_admin($segments);
            break;
            
        case 'ref':
            epic_route_referral($segments);
            break;
            
        case 'page':
            // Legacy support for old format
            epic_route_simple_landing($segments);
            break;
            
        case 'install':
        case 'install.php':
            require_once __DIR__ . '/install.php';
            break;
            
        case 'migration':
        case 'migration-script.php':
            require_once __DIR__ . '/migration-script.php';
            break;
            
        default:
            // Check if it's a landing page URL (username/page-slug)
            if (count($segments) >= 2) {
                // Try new landing page format first
                epic_route_simple_landing($segments);
            } elseif (count($segments) === 1 && ctype_alnum($segments[0])) {
                // Check if it's a referral code
                epic_route_referral($segments[0]);
            } else {
                epic_route_404();
            }
            break;
    }
    
} catch (Exception $e) {
    epic_route_error($e);
}

// =====================================================
// ROUTE HANDLERS
// =====================================================

/**
 * Home page route
 */
function epic_route_home() {
    $data = [
        'page_title' => epic_setting('site_name', 'EPIC Hub'),
        'page_description' => epic_setting('site_description', 'Modern Affiliate Marketing Platform'),
        'products' => epic_get_products(6),
        'stats' => epic_get_home_stats()
    ];
    
    epic_render_template('home', $data);
}

/**
 * Login route
 */
function epic_route_login() {
    // Redirect if already logged in
    if (epic_is_logged_in()) {
        epic_redirect(epic_get_user_redirect_url());
    }
    
    $error = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = epic_sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($email) || empty($password)) {
            $error = 'Email and password are required.';
        } else {
            $user = epic_get_user_by_email($email);
            
            if ($user && epic_verify_password($password, $user['password'])) {
                if ($user['status'] === 'banned') {
                    $error = 'Your account has been banned.';
                } else {
                    // Login user with error handling
                    try {
                        epic_login_user($user['id']);
                        
                        // Log activity (optional, skip if database issues)
                        try {
                            epic_log_activity($user['id'], 'login', 'User logged in');
                        } catch (Exception $e) {
                            // Silent fail for logging
                            error_log('Login logging failed: ' . $e->getMessage());
                        }
                        
                        // Determine redirect with error handling
                        $redirect = $_GET['redirect'] ?? null;
                        
                        if (!$redirect) {
                            try {
                                $redirect_url = epic_get_user_redirect_url($user);
                            } catch (Exception $e) {
                                // Fallback redirect based on role
                                if (in_array($user['role'], ['admin', 'super_admin'])) {
                                    $redirect_url = epic_url('admin');
                                } else {
                                    $redirect_url = epic_url('dashboard');
                                }
                                error_log('Redirect URL error: ' . $e->getMessage());
                            }
                        } else {
                            $redirect_url = epic_url($redirect);
                        }
                        
                        // Safe redirect with JavaScript fallback
                        if (!headers_sent()) {
                            header('Location: ' . $redirect_url);
                            exit;
                        } else {
                            echo '<script>window.location.href="' . htmlspecialchars($redirect_url) . '";</script>';
                            echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirect_url) . '"></noscript>';
                            exit;
                        }
                        
                    } catch (Exception $e) {
                        error_log('Login process error: ' . $e->getMessage());
                        $error = 'Login successful but redirect failed. Please try accessing your dashboard manually.';
                    }
                }
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
    
    $data = [
        'page_title' => 'Login - ' . epic_setting('site_name'),
        'error' => $error
    ];
    
    epic_render_template('auth/login', $data);
}

/**
 * Referral route
 */
function epic_route_referral($segments) {
    $affiliate_code = $segments[1] ?? '';
    
    if (empty($affiliate_code)) {
        epic_redirect(epic_url('register'));
        return;
    }
    
    // Find referrer by affiliate code
    $referrer = db()->selectOne('users', ['id', 'name', 'affiliate_code'], [
        'affiliate_code' => $affiliate_code
    ]);
    
    if (!$referrer) {
        // Try old referral_code system as fallback
        $referrer = epic_get_user_by_referral_code($affiliate_code);
    }
    
    if ($referrer) {
        // Set referral tracking cookies
        epic_set_referral_cookie($affiliate_code, $referrer['name']);
        
        // Log referral click
        epic_log_activity($referrer['id'], 'referral_click', 'Referral link clicked by: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
    
    // Redirect to register page with referral code
    epic_redirect(epic_url('register?ref=' . urlencode($affiliate_code)));
}

/**
 * Register route
 */
function epic_route_register() {
    // Redirect if already logged in
    if (epic_is_logged_in()) {
        epic_redirect(epic_url('dashboard'));
    }
    
    $error = null;
    $success = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Rate limiting for registration
        require_once EPIC_ROOT . '/core/rate-limiter.php';
        epic_check_registration_rate_limit();
        
        // CSRF Protection dan validasi input
        require_once EPIC_ROOT . '/core/csrf-protection.php';
        
        // Validasi form dengan CSRF protection
        $validation = epic_validate_registration_form($_POST);
        
        if (!$validation['valid']) {
            $errors = $validation['errors'];
            
            // Handle CSRF error specifically
            if (isset($errors['csrf'])) {
                epic_csrf_error($errors['csrf']);
                exit;
            }
            
            // Set error messages for display
            foreach ($errors as $field => $message) {
                $_SESSION['error_' . $field] = $message;
            }
            $_SESSION['error'] = 'Terdapat kesalahan pada form. Silakan periksa kembali.';
            
            // Redirect back with errors
            header('Location: ' . epic_url('register'));
            exit;
        }
        
        // Ambil data yang sudah divalidasi dan disanitasi
        $validated_data = $validation['data'];
        $name = $validated_data['name'];
        $email = $validated_data['email'];
        $phone = $validated_data['phone'] ?? '';
        $password = $validated_data['password'];
        $confirm_password = $validated_data['confirm_password'];
        // Get referral code from form, cookies, or session
        $tracking = epic_get_referral_tracking();
        $referral_code = $validated_data['referral_code'] ?? ($tracking ? $tracking['code'] : '');
        $terms = isset($_POST['terms']);
        $marketing = isset($_POST['marketing']);
        
        // Include referral-EPIS handler
        require_once EPIC_ROOT . '/referral-epis-handler.php';
        
        // Handle referral-EPIS tracking
        $epis_tracking_info = null;
        if ($referral_code) {
            $epis_tracking_info = epic_handle_referral_epis_tracking($referral_code);
            
            if ($epis_tracking_info && $epis_tracking_info['has_epis_supervisor']) {
                // Set enhanced tracking cookie with EPIS information
                $epis_display_info = epic_get_epis_info_for_display($epis_tracking_info['epis_supervisor']['id']);
                epic_set_referral_epis_cookie($referral_code, $epis_tracking_info['referrer']['name'], $epis_display_info);
            }
        }
        
        // Get referral settings
        $require_referral = epic_setting('require_referral', '0') == '1';
        $epic_account_only = epic_setting('epic_account_only', '1') == '1';
        
        // Additional validation for terms and referral requirements
        if (!$terms) {
            $_SESSION['error'] = 'You must agree to the Terms of Service and Privacy Policy.';
            header('Location: ' . epic_url('register'));
            exit;
        } elseif ($require_referral && empty($referral_code)) {
            $_SESSION['error'] = 'Kode referral wajib untuk melanjutkan registrasi.';
            header('Location: ' . epic_url('register'));
            exit;
        } elseif (!empty($referral_code)) {
            // Enhanced referral processing untuk semua skenario
            require_once EPIC_ROOT . '/core/enhanced-referral-handler.php';
            $referral_processing = epic_enhanced_referral_processing($referral_code);
            
            if (!$referral_processing['success']) {
                $error = $referral_processing['message'];
            } else {
                // Store referral info untuk display
                $referral_info = $referral_processing;
            }
        } else {
            try {
                // Get EPIS supervisor selection
                $epis_supervisor_id = (int)($_POST['epis_supervisor_id'] ?? 0);
                
                // Validate EPIS assignment using new validation system
                $assignment_validation = epic_validate_epis_assignment_data($referral_code, $epis_supervisor_id);
                
                // Check if EPIS is required and no valid assignment available
                $epis_required = epic_setting('epis_registration_required', '1') === '1';
                if ($epis_required && !$assignment_validation['valid']) {
                    if (!empty($assignment_validation['errors'])) {
                        throw new Exception('EPIS Assignment Error: ' . implode(', ', $assignment_validation['errors']));
                    } else {
                        throw new Exception('EPIS Supervisor selection is required for registration');
                    }
                }
                
                // Use validated EPIS supervisor ID
                if ($assignment_validation['valid']) {
                    $epis_supervisor_id = $assignment_validation['epis_supervisor_id'];
                }
                
                // Enhanced registration dengan auto-integration
                require_once EPIC_ROOT . '/core/enhanced-referral-handler.php';
                
                $user_data = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $password,
                    'referral_code' => $referral_code,
                    'marketing' => $marketing,
                    'epis_supervisor_id' => $epis_supervisor_id
                ];
                
                $registration_result = epic_enhanced_register_user($user_data);
                
                if (!$registration_result['success']) {
                    throw new Exception($registration_result['error']);
                }
                
                $user_id = $registration_result['user_id'];
                $referral_result = $registration_result['referral_result'];
                
                // Assign EPIS supervisor if validated
                 if ($assignment_validation['valid'] && $epis_supervisor_id > 0) {
                     // Update user with EPIS supervisor
                     db()->update(TABLE_USERS, [
                         'epis_supervisor_id' => $epis_supervisor_id,
                         'status' => 'epic',
                         'hierarchy_level' => 2,
                         'registration_source' => $assignment_validation['auto_assignment'] ? 'epis_recruit' : 'public',
                         'updated_at' => date('Y-m-d H:i:s')
                     ], 'id = ?', [$user_id]);
                     
                     // Add to EPIS network with proper recruitment type
                     $recruited_by_epic_id = $assignment_validation['referrer_id'] ?? null;
                     epic_add_to_epis_network($epis_supervisor_id, $user_id, $assignment_validation['recruitment_type'], $recruited_by_epic_id);
                     
                     // Log EPIS assignment with detailed information
                     epic_log_epis_assignment($user_id, $assignment_validation);
                     
                     // Enhanced success message berdasarkan skenario referral
                     if ($referral_result && $referral_result['success']) {
                         switch ($referral_result['scenario']) {
                             case 'epic_to_epis':
                                 $success .= ' Anda telah otomatis terhubung dengan EPIS Supervisor melalui referral EPIC Account.';
                                 break;
                             case 'epis_direct':
                                 $success .= ' Anda telah otomatis terhubung langsung dengan EPIS Account yang mereferalkan.';
                                 break;
                             case 'epic_standalone':
                                 $success .= ' Anda telah terhubung melalui referral EPIC Account.';
                                 break;
                         }
                     } elseif ($assignment_validation['auto_assignment']) {
                         $success .= ' Anda telah otomatis terhubung dengan EPIS supervisor.';
                     }
                 }
                
                // Redirect to email confirmation page
                epic_redirect('email-confirmation');
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
    
    $data = [
        'page_title' => 'Register - ' . epic_setting('site_name'),
        'error' => $error,
        'success' => $success,
        'epis_tracking_info' => $epis_tracking_info ?? null,
        'referral_info' => $referral_info ?? null
    ];
    
    epic_render_template('auth/register', $data);
}

/**
 * Forgot password route
 */
function epic_route_forgot_password() {
    // Redirect if already logged in
    if (epic_is_logged_in()) {
        epic_redirect(epic_get_user_redirect_url());
    }
    
    $error = null;
    $success = null;
    $step = 'request';
    $email = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = epic_sanitize($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Email address is required.';
        } elseif (!epic_validate_email($email)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                epic_send_reset_email($email);
                $step = 'sent';
                $success = 'Password reset instructions have been sent to your email.';
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
    
    $data = [
        'page_title' => 'Forgot Password - ' . epic_setting('site_name'),
        'error' => $error,
        'success' => $success,
        'step' => $step,
        'email' => $email
    ];
    
    epic_render_template('auth/forgot-password', $data);
}

/**
 * Reset password route
 */
function epic_route_reset_password() {
    // Redirect if already logged in
    if (epic_is_logged_in()) {
        epic_redirect(epic_get_user_redirect_url());
    }
    
    $token = $_GET['token'] ?? $_POST['token'] ?? '';
    $error = null;
    $success = null;
    $step = 'reset';
    
    if (empty($token)) {
        epic_redirect(epic_url('forgot-password'));
    }
    
    // Verify token
    $token_data = epic_verify_reset_token($token);
    if (!$token_data) {
        $error = 'Invalid or expired reset token. Please request a new password reset.';
        $step = 'request';
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_data) {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($password)) {
            $error = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            try {
                epic_reset_password($token, $password);
                $success = 'Password has been reset successfully. You can now login with your new password.';
                
                // Redirect to login after 3 seconds
                header('refresh:3;url=' . epic_url('login'));
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
    
    $data = [
        'page_title' => 'Reset Password - ' . epic_setting('site_name'),
        'error' => $error,
        'success' => $success,
        'step' => $step,
        'token' => $token
    ];
    
    epic_render_template('auth/forgot-password', $data);
}

/**
 * Logout route
 */
function epic_route_logout() {
    // Emergency logout - bypass semua error
    try {
        // Force start session jika belum
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        // Log activity jika user login (optional)
        if (isset($_SESSION['epic_user_id'])) {
            try {
                $user_id = $_SESSION['epic_user_id'];
                // Skip logging jika database bermasalah
                // epic_log_activity($user_id, 'logout', 'User logged out');
            } catch (Exception $e) {
                // Silent fail
            }
        }
        
        // Force clear all session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        @session_destroy();
        
    } catch (Exception $e) {
        // Emergency fallback - force clear
        $_SESSION = [];
        @session_destroy();
    }
    
    // JavaScript redirect (always works)
    echo '<script>window.location.href="' . epic_url('login') . '";</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . epic_url('login') . '"></noscript>';
    exit;
}

/**
 * Dashboard route
 */
function epic_route_dashboard($segments) {
    // Require authentication
    if (!epic_is_logged_in()) {
        epic_redirect(epic_url('login?redirect=dashboard'));
    }
    
    $user = epic_current_user();
    $page = $segments[1] ?? 'home';
    
    // Load dashboard controller
    require_once EPIC_CORE_DIR . '/dashboard.php';
    epic_dashboard_route($page, array_slice($segments, 2));
}

/**
 * Products route
 */
function epic_route_products($segments) {
    if (isset($segments[1])) {
        // Single product page
        $product = epic_get_product_by_slug($segments[1]);
        
        if (!$product) {
            epic_route_404();
            return;
        }
        
        $data = [
            'page_title' => $product['name'] . ' - ' . epic_setting('site_name'),
            'page_description' => $product['short_description'],
            'product' => $product
        ];
        
        epic_render_template('product/single', $data);
    } else {
        // Products listing
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        $products = epic_get_products($limit, $offset);
        $total = db()->count('epic_products', "status = 'active'");
        $total_pages = ceil($total / $limit);
        
        $data = [
            'page_title' => 'Products - ' . epic_setting('site_name'),
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'has_prev' => $page > 1,
                'has_next' => $page < $total_pages
            ]
        ];
        
        epic_render_template('products', $data);
    }
}

/**
 * Order route
 */
function epic_route_order($segments) {
    if (!isset($segments[1])) {
        epic_route_404();
        return;
    }
    
    $product = epic_get_product_by_slug($segments[1]);
    
    if (!$product) {
        epic_route_404();
        return;
    }
    
    // Load order controller
    require_once EPIC_CORE_DIR . '/order.php';
    epic_order_process($product);
}

/**
 * Articles route
 */
function epic_route_articles($segments) {
    // Load articles controller
    require_once EPIC_CORE_DIR . '/articles.php';
    epic_articles_route($segments);
}

/**
 * Blog route
 */
function epic_route_blog($segments) {
    // Load blog controller
    require_once EPIC_CORE_DIR . '/blog.php';
    epic_blog_route($segments);
}

/**
 * API route
 */
function epic_route_api($segments) {
    require_once EPIC_CORE_DIR . '/api.php';
    epic_api_route($segments);
}

/**
 * Admin route
 */
function epic_route_admin($segments) {
    // Require admin authentication
    if (!epic_is_logged_in()) {
        epic_redirect(epic_url('login?redirect=admin'));
    }
    
    $user = epic_current_user();
    if (!in_array($user['role'], ['admin', 'super_admin'])) {
        epic_route_403();
        return;
    }
    
    // Load admin controller
    require_once EPIC_CORE_DIR . '/admin.php';
    epic_admin_route($segments);
}



/**
 * 404 Not Found
 */
function epic_route_404() {
    http_response_code(404);
    
    $data = [
        'page_title' => '404 - Page Not Found',
        'error_code' => 404,
        'error_message' => 'The page you are looking for could not be found.'
    ];
    
    epic_render_template('error/404', $data);
}

/**
 * 403 Forbidden
 */
function epic_route_403() {
    http_response_code(403);
    
    $data = [
        'page_title' => '403 - Access Forbidden',
        'error_code' => 403,
        'error_message' => 'You do not have permission to access this page.'
    ];
    
    epic_render_template('error/403', $data);
}

/**
 * Error handler
 */
function epic_route_error($exception) {
    http_response_code(500);
    
    $data = [
        'page_title' => '500 - Internal Server Error',
        'error_code' => 500,
        'error_message' => 'An internal server error occurred.',
        'exception' => $exception
    ];
    
    epic_render_template('error/500', $data);
}

/**
 * Advanced landing page route handler
 * Handles URLs like: /username/[page-slug] where username is sponsor's referral code
 */
function epic_route_simple_landing($segments) {
    // Expected format: /username/[page-slug]
    if (count($segments) < 2) {
        epic_route_404();
        return;
    }
    
    $username = $segments[0]; // This is the sponsor's referral code
    $page_slug = $segments[1];
    
    // Find sponsor by username (referral_code or affiliate_code)
    $sponsor = db()->selectOne(
        "SELECT * FROM " . db()->table('users') . " WHERE referral_code = ? OR affiliate_code = ?",
        [$username, $username]
    );
    
    if (!$sponsor) {
        epic_route_404();
        return;
    }
    
    // Find the landing page by slug and owner
    $landing_page = db()->selectOne(
        "SELECT * FROM " . db()->table('landing_pages') . " WHERE page_slug = ? AND user_id = ? AND is_active = 1",
        [$page_slug, $sponsor['id']]
    );
    
    if (!$landing_page) {
        epic_route_404();
        return;
    }
    
    // Set referral tracking cookies
    epic_set_referral_cookie($username, $sponsor['name']);
    
    // Track landing page visit
    epic_track_landing_page_visit($landing_page['id'], $sponsor['id']);
    
    // Prepare data for landing page
    $data = [
        'page_title' => $landing_page['page_title'],
        'page_description' => $landing_page['page_description'],
        'page_image' => $landing_page['page_image'],
        'landing_page' => $landing_page,
        'sponsor' => $sponsor,
        'username' => $username,
        'landing_url' => $landing_page['landing_url'],
        'method' => $landing_page['method'],
        'find_replace_data' => $landing_page['find_replace_data'] ? json_decode($landing_page['find_replace_data'], true) : [],
        'register_url' => epic_url('register?ref=' . urlencode($username))
    ];
    
    // Render landing page template based on method
    epic_render_template('landing-page', $data);
}

/**
 * Track landing page visit
 */
function epic_track_landing_page_visit($landing_page_id, $sponsor_id) {
    try {
        db()->insert('landing_page_visits', [
            'landing_page_id' => $landing_page_id,
            'sponsor_id' => $sponsor_id,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
            'visited_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Silently fail
        error_log('Failed to track landing page visit: ' . $e->getMessage());
    }
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Get home page statistics
 */
function epic_get_home_stats() {
    try {
        return [
            'total_users' => db()->count('users'),
            'total_products' => db()->count('epic_products', "status = 'active'"),
            'total_orders' => db()->count('epic_orders', "status = 'paid'"),
            'total_commissions' => db()->selectValue(
                "SELECT SUM(amount_in) FROM " . TABLE_TRANSACTIONS . " WHERE type = 'commission' AND status = 'completed'"
            ) ?: 0
        ];
    } catch (Exception $e) {
        return [
            'total_users' => 0,
            'total_products' => 0,
            'total_orders' => 0,
            'total_commissions' => 0
        ];
    }
}

/**
 * Email confirmation page route
 */
function epic_route_email_confirmation() {
    $data = [
        'page_title' => 'Konfirmasi Email - ' . epic_setting('site_name')
    ];
    
    epic_render_template('auth/email-confirmation', $data);
}

/**
 * Email confirmation handler route
 */
function epic_route_confirm_email($segments) {
    if (empty($segments[1])) {
        epic_redirect(epic_url('register'));
        return;
    }
    
    $token = $segments[1];
    
    try {
        // Check if function exists
        if (!function_exists('epic_confirm_email_token')) {
            throw new Exception("Function epic_confirm_email_token not found");
        }
        
        // Verify email confirmation token
        try {
            $result = epic_confirm_email_token($token);
        } catch (Exception $e) {
            error_log("Email confirmation error: " . $e->getMessage());
            throw new Exception("Terjadi kesalahan sistem: " . $e->getMessage());
        }
        
        if ($result['success']) {
            // Store result in session for email-confirmed page
            $_SESSION['epic_email_confirmed'] = $result;
            // Set success session and redirect to login
            $_SESSION['email_confirmed'] = true;
            $_SESSION['confirmation_message'] = 'Email berhasil dikonfirmasi! Silakan login untuk melanjutkan.';
            epic_redirect(epic_url('login?confirmed=1'));
        } else {
            // Set error session and redirect to register
            $_SESSION['confirmation_error'] = $result['message'];
            epic_redirect(epic_url('register?error=confirmation'));
        }
        
    } catch (Exception $e) {
        $_SESSION['confirmation_error'] = 'Terjadi kesalahan saat konfirmasi email.';
        epic_redirect(epic_url('register?error=system'));
    }
}

/**
 * Resend confirmation email route
 */
function epic_route_resend_confirmation() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        epic_redirect(epic_url('register'));
        return;
    }
    
    // Verify CSRF token
    if (!epic_verify_csrf_token($_POST['csrf_token'] ?? '')) {
        epic_redirect(epic_url('email-confirmation?error=' . urlencode('Token keamanan tidak valid')));
        return;
    }
    
    $email = $_POST['email'] ?? '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        epic_redirect(epic_url('email-confirmation?error=' . urlencode('Email tidak valid')));
        return;
    }
    
    try {
        // Check if user exists and is not confirmed
        $user = db()->selectOne(
            "SELECT id, name, email, email_verified_at FROM epic_users WHERE email = ?",
            [$email]
        );
        
        if (!$user) {
            epic_redirect(epic_url('email-confirmation?error=' . urlencode('Email tidak ditemukan')));
            return;
        }
        
        if ($user['email_verified_at']) {
            epic_redirect(epic_url('login?message=' . urlencode('Email sudah dikonfirmasi, silakan login')));
            return;
        }
        
        // Send confirmation email
        $result = epic_send_confirmation_email($user);
        
        if ($result['success']) {
            epic_redirect(epic_url('email-confirmation?email=' . urlencode($email) . '&resent=1'));
        } else {
            epic_redirect(epic_url('email-confirmation?email=' . urlencode($email) . '&error=' . urlencode($result['message'])));
        }
        
    } catch (Exception $e) {
        error_log('Resend confirmation error: ' . $e->getMessage());
        epic_redirect(epic_url('email-confirmation?email=' . urlencode($email) . '&error=' . urlencode('Terjadi kesalahan sistem')));
    }
}

/**
 * Track referral visit
 */
function epic_track_referral_visit($referrer_id) {
    try {
        db()->insert('analytics', [
            'user_id' => $referrer_id,
            'session_id' => session_id(),
            'page_url' => $_SERVER['REQUEST_URI'],
            'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch (Exception $e) {
        // Silently fail
    }
}

/**
 * Render template
 */

?>