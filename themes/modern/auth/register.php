<?php
/**
 * EPIC HUB - Bisnis Emas Perak Indonesia - Registration Page
 * Modern registration interface with affiliate integration
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include CSRF protection
require_once EPIC_ROOT . '/core/csrf-protection.php';

// Include form fields helper
require_once EPIC_ROOT . '/form-fields-helper.php';

// Include EPIS functions
require_once EPIC_ROOT . '/core/epis-functions.php';

// Redirect if already logged in
if (epic_is_logged_in()) {
    epic_redirect(epic_url('dashboard'));
}

// EPIC Supervisor functionality removed - simplified registration

// Form fields are now hardcoded in template for better performance

$error = $data['error'] ?? null;
$success = $data['success'] ?? null;
// Get referral tracking from cookies/session or URL parameter
$tracking = epic_get_referral_tracking();
$referral_code = $_GET['ref'] ?? $_POST['referral_code'] ?? ($tracking ? $tracking['code'] : '');
$referrer_info = null;
$require_referral = epic_setting('require_referral', '0') == '1';
$show_referral_input = epic_setting('show_referral_input', '1') == '1';
$default_sponsor_ids = epic_setting('default_sponsor_ids', '1');

// Check if registration requires referral
if ($require_referral && empty($referral_code)) {
    // If no referral code and it's required, assign random default sponsor
    $sponsor_ids = array_filter(array_map('trim', explode(',', $default_sponsor_ids)));
    if (!empty($sponsor_ids)) {
        $random_sponsor_id = $sponsor_ids[array_rand($sponsor_ids)];
        $random_sponsor = db()->selectOne(
            "SELECT referral_code, name, email FROM epic_users WHERE id = ? AND status = 'premium'",
            [$random_sponsor_id]
        );
        if ($random_sponsor) {
            $referral_code = $random_sponsor['referral_code'];
            // Set tracking for the assigned sponsor
            epic_set_referral_cookie($referral_code, $random_sponsor['name']);
        }
    }
}

// Get referrer information if referral code exists
if ($referral_code) {
    $referrer = epic_get_referrer_info($referral_code);
    if ($referrer) {
        $referrer_info = [
            'id' => $referrer['id'],
            'name' => $referrer['name'],
            'email' => $referrer['email'],
            'code' => $referral_code,
            'status' => $referrer['status'],
            'role' => $referrer['role'],
            'tracking_source' => $tracking ? $tracking['source'] : 'url',
            'tracking_time' => $tracking ? date('d/m/Y H:i', $tracking['timestamp']) : date('d/m/Y H:i')
        ];
    } else {
        // Invalid referral code or not eligible
        $referral_code = '';
        if ($require_referral) {
            $error = 'Kode referral tidak valid atau tidak memenuhi syarat.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['page_title'] ?? 'Register - EPIC HUB - Bisnis Emas Perak Indonesia' ?></title>
    
    <!-- Favicon -->
    <?php 
    $site_favicon = epic_setting('site_favicon');
    if ($site_favicon && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_favicon)): 
    ?>
        <link rel="icon" type="image/x-icon" href="<?= epic_url('uploads/logos/' . $site_favicon) ?>">
    <?php else: ?>
        <link rel="icon" type="image/png" href="<?= epic_url('themes/modern/assets/favicon.png') ?>">
    <?php endif; ?>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    <style>
        :root {
            /* Gold Palette - Admin Consistent */
            --gold-500: #CFA84E;
            --gold-400: #DDB966;
            --gold-300: #E6CD8B;
            --gold-200: #F0D9A8;
            --gold-100: #F8EDD0;
            
            /* Ink/Dark Palette */
            --ink-900: #0B0B0F;
            --ink-800: #141419;
            --ink-700: #1D1D25;
            --ink-600: #262732;
            --ink-500: #3A3B47;
            --ink-400: #52535F;
            --ink-300: #6B6C78;
            --ink-200: #9B9CA8;
            --ink-100: #D1D2D9;
            
            /* Surface Layers */
            --surface-1: #0F0F14;
            --surface-2: #15161C;
            --surface-3: #1C1D24;
            --surface-4: #23242C;
            
            /* Status Colors */
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
            
            /* Gold Gradient */
            --gradient-gold: linear-gradient(135deg, #F3E5BE 0%, #D7B965 50%, #B88A2C 100%);
            --gradient-gold-subtle: linear-gradient(135deg, rgba(243, 229, 190, 0.1) 0%, rgba(215, 185, 101, 0.1) 50%, rgba(184, 138, 44, 0.1) 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--ink-900) 0%, var(--ink-800) 30%, var(--ink-700) 70%, var(--surface-2) 100%);
            position: relative;
            overflow-x: hidden;
        }
        
        /* Elegant Gold Shimmer Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(207, 168, 78, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(221, 185, 102, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(230, 205, 139, 0.04) 0%, transparent 50%);
            animation: shimmer 8s ease-in-out infinite;
            pointer-events: none;
            z-index: -2;
        }
        
        @keyframes shimmer {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }
        
        .glass-effect {
            background: linear-gradient(135deg, var(--surface-1) 0%, var(--surface-2) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid var(--ink-600);
            box-shadow: 
                0 8px 32px rgba(11, 11, 15, 0.3),
                inset 0 1px 0 rgba(207, 168, 78, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .glass-effect::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: linear-gradient(
                45deg,
                transparent 0%,
                transparent 35%,
                rgba(255, 215, 0, 0.08) 45%,
                rgba(255, 255, 255, 0.12) 50%,
                rgba(192, 192, 192, 0.08) 52%,
                rgba(255, 215, 0, 0.06) 55%,
                transparent 65%,
                transparent 100%
            );
            animation: shimmer-glow 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            pointer-events: none;
            z-index: 1;
        }
        
        .glass-effect > * {
            position: relative;
            z-index: 2;
        }
        
        @keyframes shimmer-glow {
            0% {
                transform: translateX(-150%) translateY(-150%) rotate(45deg);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            25% {
                opacity: 0.8;
            }
            50% {
                opacity: 1;
                transform: translateX(0%) translateY(0%) rotate(45deg);
            }
            75% {
                opacity: 0.8;
            }
            90% {
                opacity: 0.3;
            }
            100% {
                transform: translateX(150%) translateY(150%) rotate(45deg);
                opacity: 0;
            }
        }
        
        .input-focus:focus {
            border-color: var(--gold-400);
            box-shadow: 0 0 0 3px rgba(207, 168, 78, 0.15);
            background: var(--surface-3);
        }
        
        .btn-primary {
            background: var(--gradient-gold);
            color: var(--ink-900);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            font-weight: 600;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(207, 168, 78, 0.4);
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            background: linear-gradient(45deg, rgba(207, 168, 78, 0.1), rgba(221, 185, 102, 0.05));
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            backdrop-filter: blur(2px);
        }
        
        .shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 8%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 55%;
            right: 8%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 15%;
            left: 15%;
            animation-delay: 4s;
        }
        
        .shape:nth-child(4) {
            width: 60px;
            height: 60px;
            top: 30%;
            right: 25%;
            animation-delay: 1s;
        }
        
        .shape:nth-child(5) {
            width: 120px;
            height: 120px;
            bottom: 40%;
            right: 40%;
            animation-delay: 3s;
        }
        
        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg) scale(1); 
                opacity: 0.6;
            }
            33% { 
                transform: translateY(-30px) rotate(120deg) scale(1.1); 
                opacity: 0.8;
            }
            66% { 
                transform: translateY(-15px) rotate(240deg) scale(0.9); 
                opacity: 0.4;
            }
        }
        
        .error-shake {
            animation: shake 0.6s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
            20%, 40%, 60%, 80% { transform: translateX(8px); }
        }
        
        .strength-meter {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background: var(--danger); width: 25%; }
        .strength-fair { background: var(--warning); width: 50%; }
        .strength-good { background: var(--success); width: 75%; }
        .strength-strong { background: #059669; width: 100%; }
        
        .referrer-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), var(--surface-3));
            border: 1px solid var(--success);
            backdrop-filter: blur(15px);
        }
        
        .referral-input-card {
            background: linear-gradient(135deg, rgba(207, 168, 78, 0.15), var(--surface-3));
            backdrop-filter: blur(15px);
            border: 1px solid var(--gold-400);
        }
        

        
        .brand-logo {
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .success-message {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), var(--surface-3));
            border: 1px solid var(--success);
            backdrop-filter: blur(10px);
        }
        
        .error-message {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), var(--surface-3));
            border: 1px solid var(--danger);
            backdrop-filter: blur(10px);
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            padding: 20px;
            position: relative;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            color: #000000;
            font-weight: 500;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            color: #000000;
            transition: background 0.3s ease;
        }
        
        .modal-close:hover {
            background: rgba(0, 0, 0, 0.2);
        }
        
        .modal-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #000000;
            text-align: center;
        }
        
        .modal-section {
            margin-bottom: 20px;
        }
        
        .modal-section h3 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000000;
        }
        
        .modal-section p {
            margin-bottom: 10px;
            line-height: 1.6;
            color: #000000;
        }
        
        .modal-section ol {
            padding-left: 20px;
            margin-bottom: 15px;
        }
        
        .modal-section li {
            margin-bottom: 8px;
            line-height: 1.6;
            color: #000000;
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
    <div class="w-full max-w-md">
        
        <!-- Registration Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <!-- Logo Website -->
            <div class="text-center mb-6">
                <?php 
                $site_logo = epic_setting('site_logo');
                if (!empty($site_logo) && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_logo)): 
                ?>
                    <div class="mb-4">
                        <img src="<?= epic_url('uploads/logos/' . $site_logo) ?>" 
                             alt="<?= htmlspecialchars(epic_setting('site_name', 'EPIC HUB - Bisnis Emas Perak Indonesia')) ?>" 
                             class="mx-auto" 
                             style="max-height: 80px; max-width: 200px; object-fit: contain;">
                    </div>
                <?php else: ?>
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                <?php endif; ?>
                <h2 class="text-2xl font-semibold text-white mb-2">Buat Akun Baru</h2>
                <p class="text-white text-opacity-70">Bergabung dengan EPI Partner Network</p>
            </div>
            
            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="error-message rounded-lg p-4 mb-6 error-shake">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-red-300 text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="success-message rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-green-300 text-sm font-medium"><?= htmlspecialchars($success) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Registration Form -->
            <form method="POST" action="<?= epic_url('register') ?>" class="space-y-6" id="registerForm">
                <?php 
                // Add CSRF protection
                echo epic_csrf_field('register');
                ?>
                <input type="hidden" name="referral_code" value="<?= htmlspecialchars($referral_code) ?>">
                
                <!-- Full Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                        Full Name *
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
                               class="w-full pl-10 pr-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 input-focus transition-all duration-300"
                               placeholder="Masukkan Nama Anda"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <?php if (isset($_SESSION['error_name'])): ?>
                        <div class="mt-1 text-xs text-red-300">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><?= htmlspecialchars($_SESSION['error_name']) ?></span>
                            </div>
                        </div>
                        <?php unset($_SESSION['error_name']); ?>
                    <?php endif; ?>
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
                               class="w-full pl-10 pr-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 input-focus transition-all duration-300"
                               placeholder="Masukkan email Anda"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <div id="emailValidation" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <?php if (isset($_SESSION['error_email'])): ?>
                        <div class="mt-1 text-xs text-red-300">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><?= htmlspecialchars($_SESSION['error_email']) ?></span>
                            </div>
                        </div>
                        <?php unset($_SESSION['error_email']); ?>
                    <?php endif; ?>
                </div>
                
                <!-- WhatsApp Number Field -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                        Nomor WhatsApp *
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-white text-opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="w-full pl-10 pr-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 input-focus transition-all duration-300"
                               placeholder="Masukkan nomor whatsapp aktif"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        <div id="whatsappValidation" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div id="whatsappHelp" class="mt-1 text-xs text-white text-opacity-60">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Nomor akan digunakan untuk notifikasi WhatsApp dari sistem
                        </div>
                    </div>
                    <div id="whatsappError" class="hidden mt-1 text-xs text-red-300">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span id="whatsappErrorText">Format nomor tidak valid</span>
                        </div>
                    </div>
                    <?php if (isset($_SESSION['error_phone'])): ?>
                        <div class="mt-1 text-xs text-red-300">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><?= htmlspecialchars($_SESSION['error_phone']) ?></span>
                            </div>
                        </div>
                        <?php unset($_SESSION['error_phone']); ?>
                    <?php endif; ?>
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
                               minlength="6"
                               class="w-full pl-10 pr-12 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 input-focus transition-all duration-300"
                               placeholder="Minimal 6 karakter, harus ada huruf dan angka"
                               oninput="checkPasswordStrength()">
                        <button type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onclick="togglePassword('password')">
                            <svg id="passwordEyeIcon" class="w-5 h-5 text-white text-opacity-50 hover:text-opacity-80 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <!-- Password Strength Meter -->
                    <div class="mt-2">
                        <div class="bg-white bg-opacity-20 rounded-full h-1">
                            <div id="strengthMeter" class="strength-meter bg-gray-400 rounded-full"></div>
                        </div>
                        <div id="strengthText" class="text-xs text-white text-opacity-60 mt-1">Password strength</div>
                    </div>
                    <?php if (isset($_SESSION['error_password'])): ?>
                        <div class="mt-1 text-xs text-red-300">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><?= htmlspecialchars($_SESSION['error_password']) ?></span>
                            </div>
                        </div>
                        <?php unset($_SESSION['error_password']); ?>
                    <?php endif; ?>
                </div>
                
                <!-- Confirm Password Field -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                        Confirm Password *
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-white text-opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required 
                               class="w-full pl-10 pr-12 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 input-focus transition-all duration-300"
                               placeholder="Konfirmasi ulang password Anda"
                               oninput="checkPasswordMatch()">
                        <button type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onclick="togglePassword('confirm_password')">
                            <svg id="confirmPasswordEyeIcon" class="w-5 h-5 text-white text-opacity-50 hover:text-opacity-80 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="passwordMatch" class="hidden text-xs mt-1">
                        <span class="text-red-300">Maaf password tidak cocok</span>
                    </div>
                    <?php if (isset($_SESSION['error_confirm_password'])): ?>
                        <div class="mt-1 text-xs text-red-300">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><?= htmlspecialchars($_SESSION['error_confirm_password']) ?></span>
                            </div>
                        </div>
                        <?php unset($_SESSION['error_confirm_password']); ?>
                    <?php endif; ?>
                </div>
                

                
                <!-- EPIC Supervisor field removed - simplified registration -->
                
                <!-- Terms & Conditions -->
                <div class="flex items-start">
                    <input type="checkbox" 
                           id="terms" 
                           name="terms" 
                           required
                           class="w-4 h-4 mt-1 text-blue-600 bg-white bg-opacity-10 border border-white border-opacity-20 rounded focus:ring-blue-500 focus:ring-2">
                    <label for="terms" class="ml-3 text-sm text-white text-opacity-90 leading-relaxed">
                        Saya setuju dengan 
                        <a href="#" onclick="openModal('termsModal')" class="text-blue-300 hover:text-blue-200 underline cursor-pointer">Ketentuan Layanan</a> 
                        dan 
                        <a href="#" onclick="openModal('privacyModal')" class="text-blue-300 hover:text-blue-200 underline cursor-pointer">Kebijakan Privasi</a>
                    </label>
                </div>
                <?php if (isset($_SESSION['error_terms'])): ?>
                    <div class="mt-1 text-xs text-red-300">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><?= htmlspecialchars($_SESSION['error_terms']) ?></span>
                        </div>
                    </div>
                    <?php unset($_SESSION['error_terms']); ?>
                <?php endif; ?>
                
                <!-- Marketing Consent -->
                <div class="flex items-start">
                    <input type="checkbox" 
                           id="marketing" 
                           name="marketing" 
                           class="w-4 h-4 mt-1 text-blue-600 bg-white bg-opacity-10 border border-white border-opacity-20 rounded focus:ring-blue-500 focus:ring-2">
                    <label for="marketing" class="ml-3 text-sm text-white text-opacity-90 leading-relaxed">
                        Saya bersedia menerima email pemasaran tentang peluang bisnis dan pembaruan platform
                    </label>
                </div>
                
                <!-- Register Button -->
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
                        <div class="w-full border-t border-ink-600"></div>
                    </div>
                </div>
            </div>
            
            <!-- Sudah Punya Akun Text -->
            <div class="text-center" style="margin-top: 40px; margin-bottom: 20px;">
                <span class="text-sm text-white">Sudah punya akun terdaftar?</span>
            </div>
            
            <!-- Login Link -->
            <div class="text-center">
                <a href="<?= epic_url('login') ?>" 
                   class="inline-flex items-center justify-center w-full py-3 px-4 border border-gray-400 border-opacity-30 rounded-lg text-white bg-gray-300 bg-opacity-20 backdrop-blur-sm hover:bg-gray-200 hover:bg-opacity-30 transition-all duration-300 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Masuk ke Akun Anda
                </a>
            </div>
            

        </div>
        
        <!-- Referral Cards Section (Moved to Bottom) -->
        <div class="w-full max-w-lg mt-8">
            <!-- Informasi Pereferal -->
            <?php if ($referrer_info): ?>
                <div class="referral-stats-card rounded-2xl p-6 mb-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-blue-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-white">Informasi Pereferal</h3>
                    </div>
                    
                    <div class="text-white text-opacity-90 mb-4">
                        <!-- Nama Pereferal -->
                        <div class="bg-white bg-opacity-10 rounded-lg p-4 mb-3">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-4">
                                    <span class="text-white font-bold text-lg"><?= strtoupper(substr($referrer_info['name'], 0, 2)) ?></span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-white text-opacity-60 text-xs mb-1">Nama Pereferal</p>
                                    <p class="font-semibold text-white text-lg"><?= htmlspecialchars($referrer_info['name']) ?></p>
                                </div>
                            </div>
                            
                            <!-- EPIS Supervisor -->
                            <div class="bg-white bg-opacity-5 rounded p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white text-opacity-60 text-xs mb-1">Supervisor</p>
                                        <p class="text-blue-300 font-medium"><?= htmlspecialchars($referrer_info['supervisor_name'] ?? 'Admin EPIS') ?></p>
                                        <p class="text-white text-opacity-50 text-xs mt-1">EPIS Pembina <?= htmlspecialchars($referrer_info['name']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-white text-opacity-60 text-xs mb-1">Kode Referral</p>
                                        <p class="text-white font-medium"><?= htmlspecialchars($referral_code) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info Otomatis -->
                        <div class="flex items-center justify-between text-xs text-white text-opacity-60">
                            <span>🔒 Terdeteksi otomatis dari link referral</span>
                            <span>⏰ <?= date('d M Y H:i') ?> WIB</span>
                        </div>
                    </div>
                </div>
            <?php elseif (!$referrer_info && $show_referral_input): ?>
                <!-- Fallback Stats Card jika tidak ada referral -->
                <div class="referral-stats-card rounded-2xl p-6 mb-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-blue-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-white">Informasi Pereferal</h3>
                    </div>
                    <div class="text-white text-opacity-90 mb-4">
                        <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center">
                            <svg class="w-12 h-12 text-white text-opacity-50 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            <p class="text-white text-opacity-70 text-sm mb-2">Tidak ada referral terdeteksi</p>
                            <p class="text-white text-opacity-50 text-xs">Gunakan link referral dari EPI Channel untuk mendapatkan sponsor</p>
                            
                            <!-- Info untuk menggunakan link referral -->
                            <div class="mt-4 p-3 bg-blue-500 bg-opacity-20 rounded-lg">
                                <p class="text-blue-300 text-xs font-medium mb-1">Cara mendapatkan referral:</p>
                                <p class="text-white text-opacity-70 text-xs">Klik link referral dari EPI Channel atau hubungi sponsor Anda</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        
        <!-- Footer Links -->
        <div class="text-center mt-8">
            <div class="flex justify-center space-x-6 text-sm text-white">
                <a href="<?= epic_url() ?>" class="hover:text-ink-100 transition-colors">Home</a>
                <a href="<?= epic_url('about') ?>" class="hover:text-ink-100 transition-colors">About</a>
                <a href="<?= epic_url('contact') ?>" class="hover:text-ink-100 transition-colors">Contact</a>
                <a href="<?= epic_url('privacy') ?>" class="hover:text-ink-100 transition-colors">Privacy</a>
            </div>
            <p class="mt-4 text-xs text-white text-opacity-80">
                © <?= date('Y') ?> EPIC HUB - Bisnis Emas Perak Indonesia. All rights reserved.
            </p>
        </div>
    </div>
    
    <script>
        // Auto-set referral cookie if present in URL (simplified - no manual checking)
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const refCode = urlParams.get('ref');
            
            // Set referral cookie for future visits if referral code exists
            if (refCode && refCode.trim() !== '') {
                setReferralCookie(refCode);
            }
        });

        
        // Set referral cookie
        function setReferralCookie(code, name = '') {
            const cookieData = {
                code: code,
                name: name,
                timestamp: Date.now(),
                ip: '<?= $_SERVER["REMOTE_ADDR"] ?? "unknown" ?>'
            };
            
            const cookieValue = btoa(JSON.stringify(cookieData));
            const expireDate = new Date();
            expireDate.setTime(expireDate.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 days
            
            document.cookie = `epic_referral=${cookieValue}; expires=${expireDate.toUTCString()}; path=/; SameSite=Lax`;
            document.cookie = `epic_ref_code=${code}; expires=${expireDate.toUTCString()}; path=/; SameSite=Lax`;
            document.cookie = `epic_ref_name=${name}; expires=${expireDate.toUTCString()}; path=/; SameSite=Lax`;
        }
        
        // EPIC Supervisor functionality removed - simplified registration
        
        // Show message function
        function showMessage(message, type = 'info') {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.temp-message');
            existingMessages.forEach(msg => msg.remove());
            
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = `temp-message mb-4 p-4 rounded-lg border ${
                type === 'error' 
                    ? 'bg-red-500 bg-opacity-20 border-red-500 border-opacity-30 text-red-300' 
                    : 'bg-blue-500 bg-opacity-20 border-blue-500 border-opacity-30 text-blue-300'
            }`;
            
            messageDiv.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${type === 'error' 
                            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                        }
                    </svg>
                    <span class="text-sm">${message}</span>
                </div>
            `;
            
            // Insert before form
            const form = document.getElementById('registerForm');
            if (form) {
                form.parentNode.insertBefore(messageDiv, form);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 5000);
            }
        }
        
        // Handle referral form submission
        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('referral-form')) {
                e.preventDefault();
                const input = e.target.querySelector('input[name="ref"]');
                if (input) {
                    checkReferralCode(input.value);
                }
            }
        });
        
        // Toggle password visibility
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId + 'EyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }
        

        
        // Check password strength
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const meter = document.getElementById('strengthMeter');
            const text = document.getElementById('strengthText');
            
            let strength = 0;
            let feedback = '';
            
            // Basic requirements check
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const minLength = password.length >= 6;
            
            // Length check
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            
            // Character variety checks
            if (hasLetter) strength++;
            if (hasNumber) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update meter and text
            meter.className = 'strength-meter rounded-full';
            
            if (!minLength) {
                meter.classList.add('strength-weak');
                feedback = 'Minimal 6 karakter';
            } else if (!hasLetter || !hasNumber) {
                meter.classList.add('strength-weak');
                feedback = 'Perlu huruf dan angka';
            } else if (strength <= 3) {
                meter.classList.add('strength-fair');
                feedback = 'Password memenuhi syarat';
            } else if (strength <= 4) {
                meter.classList.add('strength-good');
                feedback = 'Password cukup kuat';
            } else {
                meter.classList.add('strength-strong');
                feedback = 'Password sangat kuat';
            }
            
            text.textContent = feedback;
        }
        
        // Check password match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    matchDiv.classList.add('hidden');
                } else {
                    matchDiv.classList.remove('hidden');
                }
            } else {
                matchDiv.classList.add('hidden');
            }
        }
        
        // Email validation
        document.getElementById('email').addEventListener('input', function() {
            const email = this.value;
            const validation = document.getElementById('emailValidation');
            
            if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                validation.classList.remove('hidden');
            } else {
                validation.classList.add('hidden');
            }
        });
        
        // WhatsApp number validation
        function validateWhatsAppNumber(input) {
            const phoneValue = input.value.replace(/\D/g, ''); // Remove non-digits
            const validationDiv = document.getElementById('whatsappValidation');
            const errorDiv = document.getElementById('whatsappError');
            const helpDiv = document.getElementById('whatsappHelp');
            const errorText = document.getElementById('whatsappErrorText');
            
            // Hide all indicators first
            validationDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');
            helpDiv.classList.remove('hidden');
            
            if (phoneValue.length === 0) {
                return;
            }
            
            // Check if it's a valid WhatsApp format (country code + number)
            if (phoneValue.length >= 10 && phoneValue.length <= 15) {
                // Check if starts with common country codes
                const commonCodes = ['62', '1', '44', '91', '86', '81', '33', '49', '39', '34', '60', '65', '66', '84', '63'];
                const startsWithValidCode = commonCodes.some(code => phoneValue.startsWith(code));
                
                if (startsWithValidCode) {
                    // Valid format
                    validationDiv.classList.remove('hidden');
                    helpDiv.classList.add('hidden');
                    input.classList.remove('border-red-500');
                    input.classList.add('border-green-500');
                } else {
                    // Invalid country code
                    errorText.textContent = 'Pastikan nomor dimulai dengan kode negara (contoh: 62 untuk Indonesia)';
                    errorDiv.classList.remove('hidden');
                    helpDiv.classList.add('hidden');
                    input.classList.remove('border-green-500');
                    input.classList.add('border-red-500');
                }
            } else if (phoneValue.length < 10) {
                // Too short
                errorText.textContent = 'Nomor terlalu pendek (minimal 10 digit)';
                errorDiv.classList.remove('hidden');
                helpDiv.classList.add('hidden');
                input.classList.remove('border-green-500');
                input.classList.add('border-red-500');
            } else {
                // Too long
                errorText.textContent = 'Nomor terlalu panjang (maksimal 15 digit)';
                errorDiv.classList.remove('hidden');
                helpDiv.classList.add('hidden');
                input.classList.remove('border-green-500');
                input.classList.add('border-red-500');
            }
        }
        
        // Form submission with loading state
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            // Additional frontend validation can be added here if needed
            
            const btn = document.getElementById('registerBtn');
            const btnText = document.getElementById('registerBtnText');
            const spinner = document.getElementById('registerSpinner');
            
            btn.disabled = true;
            btnText.textContent = 'Creating Account...';
            spinner.classList.remove('hidden');
        });
        
        // Auto-focus name field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name').focus();
        });
        
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
    </script>
    
    <!-- Terms of Service Modal -->
    <div id="termsModal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('termsModal')">&times;</button>
            <h2 class="modal-title">Syarat dan Ketentuan Layanan</h2>
            
            <div class="modal-section">
                <p><strong>Terakhir diperbarui:</strong> <?= date('d F Y') ?></p>
                <p>Selamat datang di EPIC HUB - Bisnis Emas Perak Indonesia. Dengan menggunakan layanan kami, Anda menyetujui syarat dan ketentuan berikut:</p>
            </div>
            
            <div class="modal-section">
                <h3>1. Penerimaan Syarat</h3>
                <p>Dengan mendaftar dan menggunakan platform EPIC HUB - Bisnis Emas Perak Indonesia, Anda menyetujui untuk terikat oleh syarat dan ketentuan ini. Jika Anda tidak setuju dengan syarat ini, mohon untuk tidak menggunakan layanan kami.</p>
            </div>
            
            <div class="modal-section">
                <h3>2. Deskripsi Layanan</h3>
                <p>EPIC HUB - Bisnis Emas Perak Indonesia adalah platform support system marketing dari EPI yang menyediakan:</p>
                <ol>
                    <li>Sistem manajemen promosi dan referral</li>
                    <li>Tools untuk mengakses fasilitas landing page</li>
                    <li>Analytics dan tracking performa</li>
                    <li>Sistem komisi dan pembayaran</li>
                    <li>Akses produk digital secara gratis dan berbayar</li>
                    <li>Training dan support untuk EPI Channel</li>
                </ol>
            </div>
            
            <div class="modal-section">
                <h3>3. Kewajiban Pengguna</h3>
                <p>Sebagai pengguna EPIC HUB - Bisnis Emas Perak Indonesia, Anda wajib:</p>
                <ol>
                    <li>Memberikan informasi yang akurat dan terkini</li>
                    <li>Menjaga kerahasiaan akun dan password</li>
                    <li>Tidak menggunakan platform untuk aktivitas ilegal</li>
                    <li>Mematuhi semua peraturan yang berlaku</li>
                    <li>Tidak melakukan spam atau aktivitas yang merugikan</li>
                </ol>
            </div>
            
            <div class="modal-section">
                <h3>4. Komisi dan Pembayaran</h3>
                <p>Sistem komisi EPIC HUB - Bisnis Emas Perak Indonesia beroperasi dengan ketentuan:</p>
                <ol>
                    <li>Komisi dihitung berdasarkan penjualan yang valid</li>
                    <li>Pembayaran dilakukan sesuai jadwal yang ditentukan</li>
                    <li>Minimum withdrawal sesuai kebijakan yang berlaku</li>
                    <li>Pajak dan biaya administrasi menjadi tanggung jawab pengguna</li>
                </ol>
            </div>
            
            <div class="modal-section">
                <h3>5. Hak Kekayaan Intelektual</h3>
                <p>Semua konten, logo, dan materi di platform EPIC Hub - Bisnis Emas Perak Indonesia adalah milik kami atau mitra yang berwenang. Pengguna tidak diperkenankan menggunakan materi tersebut tanpa izin tertulis.</p>
            </div>
            
            <div class="modal-section">
                <h3>6. Pembatasan Tanggung Jawab</h3>
                <p>EPIC HUB - Bisnis Emas Perak Indonesia tidak bertanggung jawab atas kerugian yang timbul dari penggunaan platform, termasuk namun tidak terbatas pada kehilangan data, keuntungan, atau gangguan bisnis.</p>
            </div>
            
            <div class="modal-section">
                <h3>7. Pemutusan Layanan</h3>
                <p>Kami berhak menangguhkan atau menghentikan akun pengguna yang melanggar syarat dan ketentuan ini tanpa pemberitahuan sebelumnya.</p>
            </div>
            
            <div class="modal-section">
                <h3>8. Perubahan Syarat</h3>
                <p>EPIC HUB - Bisnis Emas Perak Indonesia berhak mengubah syarat dan ketentuan ini sewaktu-waktu. Perubahan akan diberitahukan melalui platform atau email.</p>
            </div>
            
            <div class="modal-section">
                <h3>9. Hukum yang Berlaku</h3>
                <p>Syarat dan ketentuan ini tunduk pada hukum Republik Indonesia. Setiap sengketa akan diselesaikan melalui pengadilan yang berwenang di Indonesia.</p>
            </div>
            
            <div class="modal-section">
                <h3>10. Kontak</h3>
                <p>Jika Anda memiliki pertanyaan mengenai syarat dan ketentuan ini, silakan hubungi kami melalui email atau sistem support yang tersedia di platform.</p>
            </div>
        </div>
    </div>
    
    <!-- Privacy Policy Modal -->
    <div id="privacyModal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('privacyModal')">&times;</button>
            <h2 class="modal-title">Kebijakan Privasi</h2>
            
            <div class="modal-section">
                <p><strong>Terakhir diperbarui:</strong> <?= date('d F Y') ?></p>
                <p>EPIC HUB - Bisnis Emas Perak Indonesia berkomitmen untuk melindungi privasi dan keamanan data pribadi Anda. Kebijakan privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.</p>
            </div>
            
            <div class="modal-section">
                <h3>1. Informasi yang Kami Kumpulkan</h3>
                <p>Kami mengumpulkan informasi berikut:</p>
                <ol>
                    <li><strong>Informasi Pribadi:</strong> Nama, email, nomor telepon, alamat</li>
                    <li><strong>Informasi Akun:</strong> Username, password, preferensi</li>
                    <li><strong>Data Aktivitas:</strong> Log aktivitas, riwayat transaksi, performa affiliate</li>
                    <li><strong>Data Teknis:</strong> IP address, browser, device information</li>
                    <li><strong>Data Komunikasi:</strong> Pesan, feedback, support tickets</li>
                </ol>
            </div>
            
            <div class="modal-section">
                <h3>2. Cara Kami Menggunakan Informasi</h3>
                <p>Informasi yang dikumpulkan digunakan untuk:</p>
                <ol>
                    <li>Menyediakan dan meningkatkan layanan platform</li>
                    <li>Memproses transaksi dan pembayaran komisi</li>
                    <li>Berkomunikasi dengan pengguna</li>
                    <li>Memberikan support dan customer service</li>
                    <li>Melakukan analisis untuk pengembangan produk</li>
                    <li>Mematuhi kewajiban hukum dan regulasi</li>
                </ol>
            </div>
            
            <div class="modal-section">
                <h3>3. Pembagian Informasi</h3>
                <p>Kami tidak menjual atau menyewakan data pribadi Anda. Informasi hanya dibagikan dalam kondisi berikut:</p>
                <ol>
                    <li>Dengan persetujuan eksplisit dari Anda</li>
                    <li>Kepada penyedia layanan pihak ketiga yang terpercaya</li>
                    <li>Untuk mematuhi kewajiban hukum</li>
                    <li>Dalam rangka merger, akuisisi, atau penjualan aset</li>
                </ol>
            </div>
            
            <div class="modal-section">
                <h3>4. Keamanan Data</h3>
                <p>Kami menerapkan langkah-langkah keamanan yang ketat:</p>
                <ol>
                    <li>Enkripsi data saat transmisi dan penyimpanan</li>
                    <li>Akses terbatas hanya untuk personel yang berwenang</li>
                    <li>Monitoring keamanan secara berkala</li>
                    <li>Backup data secara rutin</li>
                    <li>Update sistem keamanan secara berkala</li>
                </ol>
            </div>
            
            <div class="modal-section">
                <h3>5. Hak Pengguna</h3>
                <p>Anda memiliki hak untuk:</p>
                <ol>
                    <li>Mengakses data pribadi yang kami simpan</li>
                    <li>Memperbarui atau mengoreksi informasi</li>
                    <li>Menghapus akun dan data pribadi</li>
                    <li>Membatasi pemrosesan data tertentu</li>
                    <li>Memindahkan data ke platform lain</li>
                    <li>Mengajukan keluhan terkait pemrosesan data</li>
                </ol>
            </div>
            
            <div class="modal-section">
                <h3>6. Cookies dan Teknologi Pelacakan</h3>
                <p>Kami menggunakan cookies untuk:</p>
                <ol>
                    <li>Menjaga sesi login pengguna</li>
                    <li>Mengingat preferensi dan pengaturan</li>
                    <li>Menganalisis penggunaan platform</li>
                    <li>Memberikan konten yang relevan</li>
                </ol>
                <p>Anda dapat mengatur preferensi cookies melalui browser Anda.</p>
            </div>
            
            <div class="modal-section">
                <h3>7. Retensi Data</h3>
                <p>Kami menyimpan data pribadi selama:</p>
                <ol>
                    <li>Akun aktif dan periode yang diperlukan untuk layanan</li>
                    <li>Sesuai dengan kewajiban hukum dan regulasi</li>
                    <li>Untuk tujuan arsip dan backup (dengan enkripsi)</li>
                </ol>
            </div>
            
            <div class="modal-section">
                <h3>8. Transfer Data Internasional</h3>
                <p>Data dapat ditransfer ke server di luar Indonesia dengan jaminan perlindungan yang setara sesuai standar internasional.</p>
            </div>
            
            <div class="modal-section">
                <h3>9. Perubahan Kebijakan</h3>
                <p>Kebijakan privasi ini dapat diperbarui sewaktu-waktu. Perubahan signifikan akan diberitahukan melalui email atau notifikasi di platform.</p>
            </div>
            
            <div class="modal-section">
                <h3>10. Kontak</h3>
                <p>Untuk pertanyaan mengenai kebijakan privasi atau penggunaan data pribadi, silakan hubungi:</p>
                <p><strong>Email:</strong> email@bisnisemasperak.com<br>
                <strong>Alamat:</strong> Taman Tekno BSD Sektor XI Blok J3 No 28, Kec. Setu South Tanggerang City, Banten - Indonesia<br>
                <strong>Telepon:</strong> 0822-9943-3869</p>
            </div>
        </div>
    </div>
</body>
</html>