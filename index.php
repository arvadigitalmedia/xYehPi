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
            epic_route_enhanced_register();
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
 * Enhanced Registration Route dengan Auto-Integration System
 */
function epic_route_enhanced_register() {
    // Redirect if already logged in
    if (epic_is_logged_in()) {
        epic_redirect(epic_url('dashboard'));
    }
    
    $error = null;
    $success = null;
    $referral_info = null;
    $epis_info = null;
    
    // Include enhanced referral handler
    require_once EPIC_ROOT . '/core/enhanced-referral-handler.php';
    
    // Process referral code dari URL atau cookie
    $referral_code = $_GET['ref'] ?? $_GET['referral'] ?? '';
    if ($referral_code) {
        // Process referral dan set cookie tracking
        $referral_processing = epic_enhanced_referral_processing($referral_code);
        if ($referral_processing['success']) {
            $referral_info = $referral_processing;
            
            // Set cookie untuk tracking
            $cookie_data = [
                'code' => $referral_code,
                'referrer_name' => $referral_processing['referrer']['name'],
                'scenario' => $referral_processing['scenario'],
                'auto_integration' => $referral_processing['auto_integration']
            ];
            
            if (isset($referral_processing['epis_supervisor'])) {
                $cookie_data['epis_supervisor'] = $referral_processing['epis_supervisor'];
            }
            
            epic_set_referral_epis_cookie($referral_code, $referral_processing['referrer']['name'], $cookie_data);
        }
    } else {
        // Check existing cookie
        $tracking = epic_get_referral_epis_tracking();
        if ($tracking) {
            $referral_code = $tracking['code'];
            $referral_processing = epic_enhanced_referral_processing($referral_code);
            if ($referral_processing['success']) {
                $referral_info = $referral_processing;
            }
        }
    }
    
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
            $error = 'Terdapat kesalahan pada form. Silakan periksa kembali.';
        } else {
            try {
                // Ambil data yang sudah divalidasi dan disanitasi
                $validated_data = $validation['data'];
                $name = $validated_data['name'];
                $email = $validated_data['email'];
                $phone = $validated_data['phone'] ?? '';
                $password = $validated_data['password'];
                $confirm_password = $validated_data['confirm_password'];
                $referral_code = $validated_data['referral_code'] ?? $referral_code;
                $terms = isset($_POST['terms']);
                $marketing = isset($_POST['marketing']);
                
                // Validasi terms
                if (!$terms) {
                    throw new Exception('Anda harus menyetujui Ketentuan Layanan dan Kebijakan Privasi.');
                }
                
                // Validasi password match
                if ($password !== $confirm_password) {
                    throw new Exception('Password dan konfirmasi password tidak cocok.');
                }
                
                // Enhanced registration dengan auto-integration
                require_once EPIC_ROOT . '/core/enhanced-referral-handler.php';
                
                $user_data = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $password,
                    'referral_code' => $referral_code,
                    'marketing' => $marketing
                ];
                
                $registration_result = epic_enhanced_register_user($user_data);
                
                if (!$registration_result['success']) {
                    throw new Exception($registration_result['error']);
                }
                
                $user_id = $registration_result['user_id'];
                $referral_result = $registration_result['referral_result'];
                
                // Set success message berdasarkan skenario
                $success = 'Akun berhasil dibuat! ';
                
                if ($referral_result && $referral_result['success']) {
                    switch ($referral_result['scenario']) {
                        case 'epic_to_epis':
                            $success .= 'Anda telah otomatis terhubung dengan EPIS Supervisor melalui referral EPIC Account.';
                            break;
                        case 'epis_direct':
                            $success .= 'Anda telah otomatis terhubung langsung dengan EPIS Account yang mereferalkan.';
                            break;
                        case 'epic_standalone':
                            $success .= 'Anda telah terhubung melalui referral EPIC Account.';
                            break;
                        default:
                            $success .= 'Registrasi berhasil dengan referral tracking.';
                    }
                } else {
                    $success .= 'Silakan cek email untuk konfirmasi akun.';
                }
                
                // Clear referral cookie setelah registrasi berhasil
                epic_clear_referral_epis_cookie();
                
                // Redirect to email confirmation page
                $_SESSION['registration_success'] = $success;
                epic_redirect('email-confirmation');
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
    
    // Prepare data untuk template
    $data = [
        'page_title' => 'Daftar Akun Baru - ' . epic_setting('site_name'),
        'error' => $error,
        'success' => $success,
        'referral_info' => $referral_info,
        'referral_code' => $referral_code
    ];
    
    epic_render_enhanced_registration_template($data);
}

/**
 * Render Enhanced Registration Template
 */
function epic_render_enhanced_registration_template($data) {
    $page_title = $data['page_title'];
    $error = $data['error'];
    $success = $data['success'];
    $referral_info = $data['referral_info'];
    $referral_code = $data['referral_code'];
    
    // Generate CSRF token
    require_once EPIC_ROOT . '/core/csrf-protection.php';
    $csrf_token = epic_generate_csrf_token('register');
    
    // Get site settings
    $site_name = epic_setting('site_name', 'EPIC Hub');
    $logo_url = epic_setting('site_logo', epic_url('assets/images/logo.png'));
    
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($page_title) ?></title>
        
        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Custom Styles -->
        <style>
            /* Glass Effect */
            .glass-effect {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            /* Gradient Background */
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            
            /* Floating Animation */
            .floating-shapes {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 1;
            }
            
            .shape {
                position: absolute;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                animation: float 6s ease-in-out infinite;
            }
            
            .shape:nth-child(1) {
                width: 80px;
                height: 80px;
                top: 10%;
                left: 10%;
                animation-delay: 0s;
            }
            
            .shape:nth-child(2) {
                width: 120px;
                height: 120px;
                top: 20%;
                right: 10%;
                animation-delay: 2s;
            }
            
            .shape:nth-child(3) {
                width: 60px;
                height: 60px;
                bottom: 20%;
                left: 20%;
                animation-delay: 4s;
            }
            
            .shape:nth-child(4) {
                width: 100px;
                height: 100px;
                bottom: 10%;
                right: 20%;
                animation-delay: 1s;
            }
            
            .shape:nth-child(5) {
                width: 40px;
                height: 40px;
                top: 50%;
                left: 50%;
                animation-delay: 3s;
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(180deg); }
            }
            
            /* Input Styles */
            .form-input {
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: white;
                transition: all 0.3s ease;
            }
            
            .form-input:focus {
                background: rgba(255, 255, 255, 0.15);
                border-color: rgba(255, 255, 255, 0.4);
                outline: none;
                box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
            }
            
            .form-input::placeholder {
                color: rgba(255, 255, 255, 0.6);
            }
            
            /* Button Styles */
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                transition: all 0.3s ease;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            }
            
            /* Referral Info Card */
            .referral-card {
                background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
                border: 1px solid rgba(255, 255, 255, 0.3);
                backdrop-filter: blur(10px);
            }
            
            /* Success/Error Messages */
            .alert-success {
                background: linear-gradient(135deg, rgba(34, 197, 94, 0.2) 0%, rgba(21, 128, 61, 0.2) 100%);
                border: 1px solid rgba(34, 197, 94, 0.3);
                color: #bbf7d0;
            }
            
            .alert-error {
                background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(185, 28, 28, 0.2) 100%);
                border: 1px solid rgba(239, 68, 68, 0.3);
                color: #fecaca;
            }
        </style>
    </head>
    <body class="min-h-screen flex items-center justify-center p-4 relative">
        <!-- Floating Background Shapes -->
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        
        <!-- Registration Container -->
        <div class="w-full max-w-lg relative z-10">
            
            <?php if ($referral_info): ?>
            <!-- Referral Info Card -->
            <div class="referral-card rounded-2xl p-6 mb-6 shadow-xl">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-500 bg-opacity-20 rounded-full mb-4">
                        <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-white mb-2">
                        <?php
                        switch ($referral_info['scenario']) {
                            case 'epic_to_epis':
                                echo '🎯 Auto-Integration EPIS';
                                break;
                            case 'epis_direct':
                                echo '👑 Direct EPIS Assignment';
                                break;
                            case 'epic_standalone':
                                echo '⭐ EPIC Referral';
                                break;
                            default:
                                echo '🔗 Referral Tracking';
                        }
                        ?>
                    </h3>
                    
                    <p class="text-white text-opacity-80 text-sm mb-3">
                        Direferalkan oleh: <strong><?= htmlspecialchars($referral_info['referrer']['name']) ?></strong>
                    </p>
                    
                    <?php if (isset($referral_info['epis_supervisor'])): ?>
                    <div class="bg-white bg-opacity-10 rounded-lg p-3 mb-3">
                        <p class="text-xs text-white text-opacity-70 mb-1">EPIS Supervisor:</p>
                        <p class="text-sm font-medium text-white"><?= htmlspecialchars($referral_info['epis_supervisor']['name']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex items-center justify-center text-xs text-green-300">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?= $referral_info['auto_integration'] ? 'Auto-Integration Aktif' : 'Referral Tracking Aktif' ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Registration Form -->
            <div class="glass-effect rounded-2xl p-8 shadow-2xl">
                <!-- Logo Inside Card -->
                <div class="text-center mb-8">
                    <?php if ($logo_url): ?>
                    <div class="inline-flex items-center justify-center mb-6">
                        <img src="<?= htmlspecialchars($logo_url) ?>" 
                             alt="<?= htmlspecialchars($site_name) ?>" 
                             class="h-20 w-auto">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-semibold text-white mb-2">Buat Akun Baru</h2>
                    <p class="text-white text-opacity-70">Mulai Bisnis Emas dan Perak Hari Ini</p>
                </div>
                
                <!-- Error Message -->
                <?php if ($error): ?>
                <div class="alert-error rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Success Message -->
                <?php if ($success): ?>
                <div class="alert-success rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Registration Form -->
                <form method="POST" action="<?= epic_url('register') ?>" class="space-y-6" id="registerForm">
                    <?= epic_csrf_field('register') ?>
                    
                    <?php if ($referral_code): ?>
                    <input type="hidden" name="referral_code" value="<?= htmlspecialchars($referral_code) ?>">
                    <?php endif; ?>
                    
                    <!-- Full Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            Nama Lengkap *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-white text-opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required 
                                   minlength="2"
                                   maxlength="100"
                                   placeholder="Masukkan nama lengkap Anda"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                   class="form-input w-full pl-10 pr-4 py-3 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            Email Address *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-white text-opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required 
                                   placeholder="nama@email.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   class="form-input w-full pl-10 pr-4 py-3 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <!-- Phone Field -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            Nomor WhatsApp
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-white text-opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="08123456789"
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                   class="form-input w-full pl-10 pr-4 py-3 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            Password *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-white text-opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="8"
                                   placeholder="Minimal 8 karakter"
                                   class="form-input w-full pl-10 pr-12 py-3 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword('password', 'passwordEyeIcon')">
                                <svg id="passwordEyeIcon" class="w-5 h-5 text-white text-opacity-50 hover:text-opacity-80 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            Konfirmasi Password *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-white text-opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required 
                                   placeholder="Ulangi password Anda"
                                   class="form-input w-full pl-10 pr-12 py-3 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword('confirm_password', 'confirmPasswordEyeIcon')">
                                <svg id="confirmPasswordEyeIcon" class="w-5 h-5 text-white text-opacity-50 hover:text-opacity-80 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="passwordMatch" class="hidden text-xs mt-1">
                            <span class="text-red-300">Password tidak cocok</span>
                        </div>
                    </div>
                    
                    <!-- Referral Code Field (jika tidak ada dari URL) -->
                    <?php if (!$referral_code): ?>
                    <div>
                        <label for="manual_referral_code" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            Kode Referral (Opsional)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-white text-opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   id="manual_referral_code" 
                                   name="referral_code" 
                                   placeholder="Masukkan kode referral"
                                   value="<?= htmlspecialchars($_POST['referral_code'] ?? '') ?>"
                                   class="form-input w-full pl-10 pr-4 py-3 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <p class="text-xs text-white text-opacity-60 mt-1">
                            Masukkan kode referral jika Anda memilikinya
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Terms & Conditions -->
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="terms" 
                               name="terms" 
                               required
                               class="w-4 h-4 mt-1 text-blue-600 bg-white bg-opacity-10 border-white border-opacity-20 rounded focus:ring-blue-500 focus:ring-2">
                        <label for="terms" class="ml-3 text-sm text-white text-opacity-80 leading-relaxed">
                            Saya setuju dengan 
                            <a href="#" onclick="openModal('termsModal')" class="text-blue-300 hover:text-blue-200 underline cursor-pointer">Ketentuan Layanan</a> 
                            dan 
                            <a href="#" onclick="openModal('privacyModal')" class="text-blue-300 hover:text-blue-200 underline cursor-pointer">Kebijakan Privasi</a>
                        </label>
                    </div>
                    
                    <!-- Marketing Consent -->
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="marketing" 
                               name="marketing"
                               class="w-4 h-4 mt-1 text-blue-600 bg-white bg-opacity-10 border-white border-opacity-20 rounded focus:ring-blue-500 focus:ring-2">
                        <label for="marketing" class="ml-3 text-sm text-white text-opacity-80 leading-relaxed">
                            Saya ingin menerima update produk, promosi, dan newsletter via email
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full btn-primary text-white font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-transparent"
                            id="registerBtn">
                        <span id="registerBtnText">BUAT AKUN SEKARANG</span>
                        <svg id="registerSpinner" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="mt-8 mb-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-white border-opacity-20"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Sudah Punya Akun Text -->
                <div class="text-center" style="margin-top: 40px; margin-bottom: 20px;">
                    <span class="text-sm text-white text-opacity-60">Sudah punya akun terdaftar?</span>
                </div>
                
                <!-- Login Link -->
                <div class="text-center">
                    <a href="<?= epic_url('login') ?>" 
                       class="inline-flex items-center justify-center w-full py-3 px-4 border border-white border-opacity-30 rounded-lg text-white text-opacity-90 hover:bg-white hover:bg-opacity-10 transition-all duration-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Masuk ke Akun Anda
                    </a>
                </div>
            </div>
        </div>
        
        <!-- JavaScript -->
        <script>
            // Password visibility toggle
            function togglePassword(fieldId, iconId) {
                const field = document.getElementById(fieldId);
                const icon = document.getElementById(iconId);
                
                if (field.type === 'password') {
                    field.type = 'text';
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>';
                } else {
                    field.type = 'password';
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
                }
            }
            
            // Password match validation
            function checkPasswordMatch() {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                const matchDiv = document.getElementById('passwordMatch');
                
                if (confirmPassword && password !== confirmPassword) {
                    matchDiv.classList.remove('hidden');
                } else {
                    matchDiv.classList.add('hidden');
                }
            }
            
            document.getElementById('password').addEventListener('input', checkPasswordMatch);
            document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
            
            // Form submission with loading state
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Password dan konfirmasi password tidak cocok!');
                    return;
                }
                
                const btn = document.getElementById('registerBtn');
                const btnText = document.getElementById('registerBtnText');
                const spinner = document.getElementById('registerSpinner');
                
                btn.disabled = true;
                btnText.textContent = 'Membuat Akun...';
                spinner.classList.remove('hidden');
            });
            
            // Auto-focus name field
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('name').focus();
            });
            
            // Modal functions (placeholder)
            function openModal(modalId) {
                alert('Modal ' + modalId + ' akan dibuka (implementasi modal belum tersedia)');
            }
        </script>
    </body>
    </html>
    <?php
}

/**
 * Enhanced register route handler
 */
function epic_route_register() {
    epic_route_enhanced_register();
}

/**
 * Route handlers - Essential functions for routing
 */
function epic_route_home() {
    // Default home route menampilkan halaman registrasi
    epic_route_enhanced_register();
}

function epic_route_login() {
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
    require_once EPIC_CORE_DIR . '/dashboard.php';
    epic_handle_dashboard_route($segments);
}

function epic_route_products($segments) {
    require_once EPIC_THEME_DIR . '/modern/products.php';
}

function epic_route_order($segments) {
    require_once EPIC_CORE_DIR . '/order.php';
    epic_handle_order_route($segments);
}

function epic_route_articles($segments) {
    require_once EPIC_CORE_DIR . '/articles.php';
    epic_handle_articles_route($segments);
}

function epic_route_blog($segments) {
    require_once EPIC_CORE_DIR . '/blog.php';
    epic_handle_blog_route($segments);
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