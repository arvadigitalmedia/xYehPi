<?php
/**
 * EPIC HUB - Bisnis Emas Perak Indonesia - Reset Password Page
 * Modern password reset interface with consistent design
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Redirect if already logged in
if (epic_is_logged_in()) {
    epic_redirect(epic_url('dashboard'));
}

// Get token from URL
$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    epic_redirect(epic_url('forgot-password'));
}

// Verify token
$token_data = epic_verify_reset_token($token);
if (!$token_data) {
    $error = 'Token reset password tidak valid atau sudah kedaluwarsa. Silakan minta reset password baru.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_data) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = 'Password wajib diisi.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        try {
            epic_reset_password($token, $password);
            $success = 'Password berhasil direset! Anda akan diarahkan ke halaman login.';
            
            // Redirect to login after 3 seconds
            header('refresh:3;url=' . epic_url('login'));
            
        } catch (Exception $e) {
            $error = 'Gagal mereset password: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['page_title'] ?? 'Reset Password - EPIC HUB - Bisnis Emas Perak Indonesia' ?></title>
    
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
            background: white;
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
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(207, 168, 78, 0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-primary:disabled::before {
            display: none;
        }
        
        /* Floating Shapes */
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: var(--gradient-gold-subtle);
            animation: float 6s ease-in-out infinite;
            pointer-events: none;
        }
        
        .floating-shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .floating-shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        .floating-shape:nth-child(4) {
            width: 100px;
            height: 100px;
            bottom: 10%;
            right: 20%;
            animation-delay: 1s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.6;
            }
        }
        
        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(11, 11, 15, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .loading-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--ink-600);
            border-top: 3px solid var(--gold-400);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        .loading-dots {
            display: flex;
            gap: 8px;
        }
        
        .loading-dot {
            width: 8px;
            height: 8px;
            background: var(--gold-400);
            border-radius: 50%;
            animation: bounce 1.4s ease-in-out infinite both;
        }
        
        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }
        .loading-dot:nth-child(3) { animation-delay: 0s; }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }
        
        /* Mobile Responsive */
        @media (max-width: 640px) {
            .floating-shape {
                display: none;
            }
            
            .glass-effect {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
        
        /* Error/Success Messages */
        .alert {
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.3);
            color: #10B981;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #EF4444;
        }
        
        .alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Floating Shapes -->
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-logo">
            <?php 
            $site_logo = epic_setting('site_logo');
            if ($site_logo && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_logo)): 
            ?>
                <img src="<?= epic_url('uploads/logos/' . $site_logo) ?>" alt="EPIC HUB" class="w-full h-full object-contain">
            <?php else: ?>
                <svg viewBox="0 0 100 100" class="w-full h-full">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="var(--gold-400)" stroke-width="3"/>
                    <text x="50" y="55" text-anchor="middle" fill="var(--gold-400)" font-size="20" font-weight="bold">E</text>
                </svg>
            <?php endif; ?>
        </div>
        <div class="loading-spinner"></div>
        <div class="loading-dots">
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
        </div>
        <p class="text-white text-opacity-80 mt-4">Memproses reset password...</p>
    </div>
    
    <!-- Main Container -->
    <div class="min-h-screen flex items-center justify-center p-4 relative z-10">
        <div class="w-full max-w-md">
            <!-- Logo/Brand -->
            <div class="text-center mb-8">
                <?php 
                $site_logo = epic_setting('site_logo');
                if ($site_logo && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_logo)): 
                ?>
                    <img src="<?= epic_url('uploads/logos/' . $site_logo) ?>" alt="EPIC HUB" class="h-16 mx-auto mb-4">
                <?php else: ?>
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">E</span>
                    </div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold text-white mb-2">Reset Password</h1>
                <p class="text-gray-400 text-sm">Masukkan password baru untuk akun Anda</p>
            </div>
            
            <!-- Alert Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Reset Form -->
            <?php if ($token_data && !$success): ?>
                <form method="POST" id="reset-form" class="glass-effect rounded-2xl p-8 space-y-6">
                    <div class="space-y-4">
                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                                Password Baru
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                       class="input-focus w-full px-4 py-3 bg-gray-800 bg-opacity-50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none transition-all duration-200"
                                       placeholder="Masukkan password baru">
                                <button type="button" id="toggle-password" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                                    <svg id="eye-closed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                    </svg>
                                    <svg id="eye-open" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-2">
                                Konfirmasi Password
                            </label>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="confirm_password" required
                                       class="input-focus w-full px-4 py-3 bg-gray-800 bg-opacity-50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none transition-all duration-200"
                                       placeholder="Konfirmasi password baru">
                                <button type="button" id="toggle-confirm-password" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                                    <svg id="eye-closed-confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                    </svg>
                                    <svg id="eye-open-confirm" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="bg-gray-800 bg-opacity-30 rounded-lg p-4 space-y-2">
                        <p class="text-gray-300 text-sm font-medium mb-2">Persyaratan Password:</p>
                        <div id="length-check" class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-gray-400 text-sm">Minimal 8 karakter</span>
                        </div>
                        <div id="match-check" class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-gray-400 text-sm">Password harus sama</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submit-btn" disabled
                            class="btn-primary w-full py-3 px-4 rounded-lg font-semibold text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <!-- Footer Links -->
            <div class="text-center space-y-4 mt-8">
                <a href="<?= epic_url('login') ?>" class="inline-flex items-center text-gray-400 hover:text-white transition-colors text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Login
                </a>
                
                <div class="flex justify-center space-x-6 text-xs text-gray-500">
                    <a href="<?= epic_url('privacy') ?>" class="hover:text-gray-400 transition-colors">Kebijakan Privasi</a>
                    <a href="<?= epic_url('terms') ?>" class="hover:text-gray-400 transition-colors">Syarat & Ketentuan</a>
                    <a href="<?= epic_url('contact') ?>" class="hover:text-gray-400 transition-colors">Bantuan</a>
                </div>
                
                <p class="text-xs text-gray-500">
                    © <?= date('Y') ?> EPIC HUB - Bisnis Emas Perak Indonesia. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Loading overlay
            const loadingOverlay = document.getElementById('loading-overlay');
            
            // Hide loading after page loads
            setTimeout(() => {
                if (loadingOverlay) {
                    loadingOverlay.style.opacity = '0';
                    setTimeout(() => {
                        loadingOverlay.style.display = 'none';
                    }, 300);
                }
            }, 1000);

            // Password toggle functionality
            const togglePassword = document.getElementById('toggle-password');
            const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    const eyeClosed = document.getElementById('eye-closed');
                    const eyeOpen = document.getElementById('eye-open');
                    
                    if (type === 'password') {
                        eyeClosed.classList.remove('hidden');
                        eyeOpen.classList.add('hidden');
                    } else {
                        eyeClosed.classList.add('hidden');
                        eyeOpen.classList.remove('hidden');
                    }
                });
            }

            if (toggleConfirmPassword && confirmPasswordInput) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPasswordInput.setAttribute('type', type);
                    
                    const eyeClosed = document.getElementById('eye-closed-confirm');
                    const eyeOpen = document.getElementById('eye-open-confirm');
                    
                    if (type === 'password') {
                        eyeClosed.classList.remove('hidden');
                        eyeOpen.classList.add('hidden');
                    } else {
                        eyeClosed.classList.add('hidden');
                        eyeOpen.classList.remove('hidden');
                    }
                });
            }

            // Password validation
            const submitBtn = document.getElementById('submit-btn');
            const lengthCheck = document.getElementById('length-check');
            const matchCheck = document.getElementById('match-check');

            function validatePassword() {
                const password = passwordInput ? passwordInput.value : '';
                const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';
                
                // Check length
                const lengthValid = password.length >= 8;
                const lengthIcon = lengthCheck.querySelector('svg');
                const lengthText = lengthCheck.querySelector('span');
                
                if (lengthValid) {
                    lengthIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                    lengthIcon.className = 'w-4 h-4 mr-2 text-green-400';
                    lengthText.className = 'text-gray-300 text-sm';
                } else {
                    lengthIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                    lengthIcon.className = 'w-4 h-4 mr-2 text-red-400';
                    lengthText.className = 'text-gray-400 text-sm';
                }
                
                // Check match
                const matchValid = password === confirmPassword && confirmPassword.length > 0;
                const matchIcon = matchCheck.querySelector('svg');
                const matchText = matchCheck.querySelector('span');
                
                if (matchValid) {
                    matchIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                    matchIcon.className = 'w-4 h-4 mr-2 text-green-400';
                    matchText.className = 'text-gray-300 text-sm';
                } else {
                    matchIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                    matchIcon.className = 'w-4 h-4 mr-2 text-red-400';
                    matchText.className = 'text-gray-400 text-sm';
                }
                
                // Enable/disable submit button
                if (submitBtn) {
                    submitBtn.disabled = !(lengthValid && matchValid);
                }
            }

            if (passwordInput) {
                passwordInput.addEventListener('input', validatePassword);
                passwordInput.focus(); // Auto focus on password field
            }
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', validatePassword);
            }

            // Form submission with loading state
            const resetForm = document.getElementById('reset-form');
            if (resetForm) {
                resetForm.addEventListener('submit', function(e) {
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 inline mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses...';
                        submitBtn.disabled = true;
                    }
                });
            }
        });
    </script>
</body>
</html>