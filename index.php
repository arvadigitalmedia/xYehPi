<?php
/**
 * EPIC Hub - Enhanced Registration System
 * Modern Affiliate Marketing & Referral Platform with Auto-Integration
 * 
 * @version 3.0.0
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
            // Load registration controller
            require_once EPIC_ROOT . '/core/registration-controller.php';
            epic_handle_registration();
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
            
        case 'admin':
            epic_route_admin($segments);
            break;
            
        case 'member':
            epic_route_member($segments);
            break;
            
        case 'health':
            epic_route_health();
            break;
            
        default:
            epic_route_404();
            break;
    }
} catch (Exception $e) {
    epic_handle_error($e);
}

/**
 * Register route handler - now uses separated controller
 */
function epic_route_register() {
    // Load registration controller
    require_once EPIC_ROOT . '/core/registration-controller.php';
    epic_handle_registration();
}

function epic_route_home() {
    // Default home route menampilkan halaman maintenance
    $home_template = EPIC_ROOT . '/themes/modern/home.php';
    
    if (file_exists($home_template)) {
        require_once $home_template;
    } else {
        // Fallback jika file tidak ditemukan
        http_response_code(404);
        echo '<h1>404 - Halaman tidak ditemukan</h1>';
        echo '<p>Template home.php tidak ditemukan di: ' . htmlspecialchars($home_template) . '</p>';
    }
}

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
    
    require_once EPIC_THEME_DIR . '/modern/auth/login.php';
}

function epic_route_logout() {
    epic_logout_user();
    epic_redirect(epic_url());
}

function epic_route_forgot_password() {
    require_once EPIC_THEME_DIR . '/modern/auth/forgot-password.php';
}

function epic_route_reset_password() {
    require_once EPIC_THEME_DIR . '/modern/auth/reset-password.php';
}

function epic_route_email_confirmation() {
    require_once EPIC_ROOT . '/email-confirmation.php';
}

function epic_route_confirm_email($segments) {
    require_once EPIC_ROOT . '/confirm-email.php';
}

function epic_route_resend_confirmation() {
    require_once EPIC_ROOT . '/email-confirmation.php';
}

function epic_route_dashboard($segments) {
    $page = $segments[1] ?? 'home';
    
    // Load dashboard controller
    require_once EPIC_CORE_DIR . '/dashboard.php';
    epic_dashboard_route($page, array_slice($segments, 2));
}

function epic_route_products($segments) {
    require_once EPIC_THEME_DIR . '/modern/products.php';
}

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

function epic_route_articles($segments) {
    // Load articles controller
    require_once EPIC_CORE_DIR . '/articles.php';
    epic_articles_route($segments);
}

function epic_route_blog($segments) {
    // Load blog controller
    require_once EPIC_CORE_DIR . '/blog.php';
    epic_blog_route($segments);
}

function epic_route_admin($segments) {
    require_once EPIC_CORE_DIR . '/admin.php';
    epic_admin_route($segments);
}

function epic_route_member($segments) {
    if (epic_is_logged_in()) {
        require_once EPIC_THEME_DIR . '/modern/member/index.php';
    } else {
        epic_redirect(epic_url('login'));
    }
}

function epic_route_health() {
    require_once EPIC_ROOT . '/health.php';
}

function epic_route_404() {
    http_response_code(404);
    echo '<h1>404 - Page Not Found</h1>';
    echo '<p>The requested page could not be found.</p>';
    echo '<p><a href="' . epic_url() . '">Return to Home</a></p>';
}

function epic_handle_error($exception) {
    require_once EPIC_CORE_DIR . '/error-handler.php';
    epic_handle_route_error($exception);
}
?>