<?php
/**
 * EPIC Hub - Forgot Password Page
 * Password reset request interface
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Redirect if already logged in
if (epic_is_logged_in()) {
    epic_redirect(epic_url('dashboard'));
}

$error = $data['error'] ?? null;
$success = $data['success'] ?? null;
$step = $data['step'] ?? 'request'; // request, sent, reset
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['page_title'] ?? 'Reset Password - EPIC Hub' ?></title>
    
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
                 30deg,
                 transparent 0%,
                 transparent 35%,
                 rgba(255, 215, 0, 0.08) 45%,
                 rgba(255, 255, 255, 0.12) 50%,
                 rgba(192, 192, 192, 0.08) 52%,
                 rgba(255, 215, 0, 0.06) 55%,
                 transparent 65%,
                 transparent 100%
             );
             animation: shimmer-glow 3s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
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
        
        .success-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
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
        
        /* Consistent with login/register pages */
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
    
    <!-- Reset Password Container -->
    <div class="w-full max-w-md">
        <!-- Reset Password Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <!-- Logo Only -->
            <div class="text-center mb-8">
                <?php 
                $site_logo = epic_setting('site_logo');
                if ($site_logo && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_logo)): 
                ?>
                    <div class="inline-flex items-center justify-center mb-6">
                        <img src="<?= epic_url('uploads/logos/' . $site_logo) ?>" 
                             alt="<?= htmlspecialchars(epic_setting('site_name', 'EPIC Hub')) ?>" 
                             class="h-20 w-auto">
                    </div>
                <?php else: ?>
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($step === 'request'): ?>
                <!-- Step 1: Request Reset -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-semibold text-white mb-2">Anda Lupa Password?</h2>
                    <p class="text-white text-opacity-70">Masukkan Email Anda untuk menerima instruksi reset password</p>
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
                
                <!-- Reset Request Form -->
                <form method="POST" action="<?= epic_url('forgot-password') ?>" class="space-y-6" id="resetForm">
                    <!-- Email Field -->
                    <div>
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
                                   placeholder="Masukkan email Anda yang terdaftar"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full btn-primary text-white font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-transparent"
                            id="resetBtn">
                        <span id="resetBtnText">KIRIM PETUNJUK RESET PASSWORD</span>
                        <svg id="resetSpinner" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
                
            <?php elseif ($step === 'sent'): ?>
                <!-- Step 2: Email Sent -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-500 bg-opacity-20 rounded-full mb-6 success-pulse">
                        <svg class="w-8 h-8 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    
                    <h2 class="text-2xl font-semibold text-white mb-4">Check Your Email</h2>
                    <p class="text-white text-opacity-80 mb-6 leading-relaxed">
                        We've sent password reset instructions to your email address. 
                        Please check your inbox and follow the link to reset your password.
                    </p>
                    
                    <div class="bg-blue-500 bg-opacity-20 border border-blue-500 border-opacity-30 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-300 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-blue-300 text-sm text-left">
                                <p class="font-medium mb-1">Didn't receive the email?</p>
                                <ul class="text-xs space-y-1 text-blue-200">
                                    <li>• Check your spam/junk folder</li>
                                    <li>• Make sure the email address is correct</li>
                                    <li>• Wait a few minutes for delivery</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resend Button -->
                    <form method="POST" action="<?= epic_url('forgot-password') ?>" class="mb-4">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                        <button type="submit" 
                                class="w-full py-3 px-4 border border-white border-opacity-30 rounded-lg text-white text-opacity-90 hover:bg-white hover:bg-opacity-10 transition-all duration-300">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Resend Email
                        </button>
                    </form>
                </div>
                
            <?php elseif ($step === 'reset'): ?>
                <!-- Step 3: Reset Password -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-500 bg-opacity-20 rounded-full mb-4">
                        <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-semibold text-white mb-2">Reset Password</h2>
                    <p class="text-white text-opacity-70">Enter your new password below</p>
                </div>
                
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="bg-red-500 bg-opacity-20 border border-red-500 border-opacity-30 rounded-lg p-4 mb-6 error-shake">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-red-300 text-sm"><?= htmlspecialchars($error) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- New Password Form -->
                <form method="POST" action="<?= epic_url('reset-password') ?>" class="space-y-6" id="newPasswordForm">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <!-- New Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            New Password
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
                                   class="w-full pl-10 pr-12 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 input-focus transition-all duration-300"
                                   placeholder="Enter new password"
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
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            Confirm New Password
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
                                   placeholder="Confirm new password"
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
                            <span class="text-red-300">Passwords do not match</span>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full btn-primary text-white font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-transparent"
                            id="newPasswordBtn">
                        <span id="newPasswordBtnText">Update Password</span>
                        <svg id="newPasswordSpinner" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
            <?php endif; ?>
            
            <!-- Back to Login -->
            <div class="mt-8 text-center">
                <a href="<?= epic_url('login') ?>" 
                   class="inline-flex items-center text-white text-opacity-80 hover:text-opacity-100 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali untuk Login
                </a>
            </div>
        </div>
        
        <!-- Footer Links -->
        <div class="text-center mt-8">
            <div class="flex justify-center space-x-6 text-sm text-white text-opacity-60">
                <a href="<?= epic_url() ?>" class="hover:text-opacity-100 transition-colors">Home</a>
                <a href="<?= epic_url('about') ?>" class="hover:text-opacity-100 transition-colors">About</a>
                <a href="<?= epic_url('contact') ?>" class="hover:text-opacity-100 transition-colors">Contact</a>
                <a href="<?= epic_url('privacy') ?>" class="hover:text-opacity-100 transition-colors">Privacy</a>
            </div>
            <p class="mt-4 text-xs text-white text-opacity-50">
                © <?= date('Y') ?> <?= htmlspecialchars(epic_setting('site_name', 'EPIC Hub')) ?>. All rights reserved.
            </p>
        </div>
    </div>
    
    <script>
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
            
            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Character variety checks
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update meter and text
            meter.className = 'strength-meter rounded-full';
            
            if (strength <= 2) {
                meter.classList.add('strength-weak');
                feedback = 'Weak password';
            } else if (strength <= 4) {
                meter.classList.add('strength-fair');
                feedback = 'Fair password';
            } else if (strength <= 5) {
                meter.classList.add('strength-good');
                feedback = 'Good password';
            } else {
                meter.classList.add('strength-strong');
                feedback = 'Strong password';
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
        
        // Form submission handlers
        document.addEventListener('DOMContentLoaded', function() {
            // Reset request form
            const resetForm = document.getElementById('resetForm');
            if (resetForm) {
                resetForm.addEventListener('submit', function() {
                    const btn = document.getElementById('resetBtn');
                    const btnText = document.getElementById('resetBtnText');
                    const spinner = document.getElementById('resetSpinner');
                    
                    btn.disabled = true;
                    btnText.textContent = 'Sending...';
                    spinner.classList.remove('hidden');
                });
                
                // Auto-focus email field
                document.getElementById('email').focus();
            }
            
            // New password form
            const newPasswordForm = document.getElementById('newPasswordForm');
            if (newPasswordForm) {
                newPasswordForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        return;
                    }
                    
                    const btn = document.getElementById('newPasswordBtn');
                    const btnText = document.getElementById('newPasswordBtnText');
                    const spinner = document.getElementById('newPasswordSpinner');
                    
                    btn.disabled = true;
                    btnText.textContent = 'Updating...';
                    spinner.classList.remove('hidden');
                });
                
                // Auto-focus password field
                document.getElementById('password').focus();
            }
        });
    </script>
</body>
</html>