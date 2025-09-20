<?php
/**
 * EPIC Account Registration with EPIS Supervisor Selection
 * Enhanced registration form for EPIC accounts with hierarchical system
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Check if EPIS registration is enabled
$epis_enabled = epic_setting('epis_registration_enabled', '1') === '1';

// Get available EPIS supervisors
$available_epis = [];
if ($epis_enabled) {
    $available_epis = epic_get_available_epis_supervisors();
}

// Handle form submission
$error = '';
$success = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data first
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $epis_supervisor_id = (int)($_POST['epis_supervisor_id'] ?? 0);
        $terms = isset($_POST['terms']);
        $marketing = isset($_POST['marketing']);
        $referral_code = trim($_POST['referral_code'] ?? '');
        
        // ENHANCED RATE LIMITING - Multi-layer protection
        require_once EPIC_ROOT . '/core/rate-limiter.php';
        epic_check_enhanced_registration_rate_limit($email);
        
        // Store form data for repopulation
        $form_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'epis_supervisor_id' => $epis_supervisor_id,
            'marketing' => $marketing,
            'referral_code' => $referral_code
        ];
        
        // Validation
        if (empty($name)) {
            throw new Exception('Full name is required');
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Valid email address is required');
        }
        
        if (empty($password) || strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }
        
        if ($epis_enabled && $epis_supervisor_id <= 0) {
            throw new Exception('EPIS Supervisor selection is required for EPIC account registration');
        }
        
        if (!$terms) {
            throw new Exception('You must agree to the Terms of Service and Privacy Policy');
        }
        
        // Check if email already exists
        if (epic_get_user_by_email($email)) {
            throw new Exception('Email address is already registered');
        }
        
        // Validate EPIS supervisor
        if ($epis_enabled && $epis_supervisor_id > 0) {
            $epis_supervisor = epic_get_user($epis_supervisor_id);
            if (!$epis_supervisor || $epis_supervisor['status'] !== 'epis') {
                throw new Exception('Invalid EPIS supervisor selected');
            }
            
            // Check if EPIS has capacity
            $epis_account = epic_get_epis_account($epis_supervisor_id);
            if ($epis_account['max_epic_recruits'] > 0 && 
                $epis_account['current_epic_count'] >= $epis_account['max_epic_recruits']) {
                throw new Exception('Selected EPIS supervisor has reached maximum capacity');
            }
        }
        
        // Create user account
        $user_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'referral_code' => $referral_code,
            'marketing' => $marketing
        ];
        
        $user_id = epic_register_user($user_data);
        
        if (!$user_id) {
            throw new Exception('Failed to create account');
        }
        
        // Update user to EPIC status with EPIS supervisor
        $update_data = [
            'status' => 'epic',
            'hierarchy_level' => 2,
            'epis_supervisor_id' => $epis_supervisor_id,
            'supervisor_locked' => true,
            'registration_source' => 'epis_recruit',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        db()->update('users', $update_data, 'id = ?', [$user_id]);
        
        // Add to EPIS network
        if ($epis_enabled && $epis_supervisor_id > 0) {
            epic_add_to_epis_network($epis_supervisor_id, $user_id, 'direct');
        }
        
        // Log activity
        epic_log_activity($user_id, 'epic_account_registered', "EPIC account registered with EPIS supervisor: {$epis_supervisor_id}");
        
        $success = 'EPIC account created successfully! You can now login with your credentials.';
        $form_data = []; // Clear form on success
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get referrer info if referral code provided
$referrer_info = null;
if (!empty($_GET['ref'])) {
    $referral_code = trim($_GET['ref']);
    $referrer_info = epic_get_user_by_referral_code($referral_code);
    if ($referrer_info) {
        $referrer_info['tracking_source'] = 'direct';
        $referrer_info['tracking_time'] = date('H:i');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPIC Account Registration - <?= epic_setting('site_name', 'EPIC Hub') ?></title>
    
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
        
        .epic-gradient {
            background: var(--gradient-gold);
        }
        
        .epis-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
        
        .error-shake {
            animation: shake 0.6s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
            20%, 40%, 60%, 80% { transform: translateX(8px); }
        }
        
        .epis-card {
            transition: all 0.3s ease;
        }
        
        .epis-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .epis-card.selected {
            border-color: var(--gold-400);
            box-shadow: 0 0 20px rgba(207, 168, 78, 0.3);
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
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-4xl w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 epic-gradient rounded-full flex items-center justify-center mb-4">
                    <span class="text-white font-bold text-2xl">EH</span>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">EPIC Account Registration</h1>
                <p class="text-white text-opacity-70 text-lg">Join the hierarchical affiliate system with EPIS supervision</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- EPIS Supervisor Selection -->
                <?php if ($epis_enabled && !empty($available_epis)): ?>
                <div class="lg:col-span-1">
                    <div class="glass-effect rounded-2xl p-6 shadow-2xl">
                        <div class="text-center mb-6">
                            <div class="mx-auto h-12 w-12 epis-gradient rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-white mb-2">Choose Your EPIS Supervisor</h3>
                            <p class="text-white text-opacity-70 text-sm">Select an EPIS account to guide your EPIC journey</p>
                        </div>
                        
                        <div class="space-y-3 max-h-96 overflow-y-auto" id="episList">
                            <?php foreach ($available_epis as $epis): ?>
                            <div class="epis-card glass-effect rounded-lg p-4 cursor-pointer border border-white border-opacity-20" 
                                 onclick="selectEpis(<?= $epis['id'] ?>)" 
                                 data-epis-id="<?= $epis['id'] ?>">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 epis-gradient rounded-full flex items-center justify-center mr-3">
                                        <span class="text-white font-bold text-sm"><?= strtoupper(substr($epis['name'], 0, 2)) ?></span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-white text-sm"><?= htmlspecialchars($epis['name']) ?></p>
                                        <p class="text-xs text-white text-opacity-60"><?= htmlspecialchars($epis['epis_code']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <div class="w-4 h-4 border-2 border-white border-opacity-50 rounded-full epis-radio" 
                                             data-epis-id="<?= $epis['id'] ?>"></div>
                                    </div>
                                </div>
                                
                                <div class="text-xs text-white text-opacity-80">
                                    <div class="mb-1">
                                        <strong>Territory:</strong> <?= htmlspecialchars($epis['territory_name'] ?: 'General') ?>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Network: <?= $epis['current_epic_count'] ?><?= $epis['max_epic_recruits'] > 0 ? '/' . $epis['max_epic_recruits'] : '' ?></span>
                                        <span class="text-green-300">Available</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (empty($available_epis)): ?>
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-white text-opacity-50 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p class="text-white text-opacity-70 text-sm">No EPIS supervisors available at the moment</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Registration Form -->
                <div class="<?= $epis_enabled && !empty($available_epis) ? 'lg:col-span-2' : 'lg:col-span-3' ?>">
                    <div class="glass-effect rounded-2xl p-8 shadow-2xl">
                        <div class="text-center mb-6">
                            <h2 class="text-2xl font-semibold text-white mb-2">Create EPIC Account</h2>
                            <p class="text-white text-opacity-70">Join the next level of affiliate marketing</p>
                        </div>
                        
                        <!-- Error Message -->
                        <?php if ($error): ?>
                            <div class="error-message rounded-lg p-4 mb-6 error-shake">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-red-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-red-300 text-sm"><?= htmlspecialchars($error) ?></span>
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
                                    <span class="text-green-300 text-sm"><?= htmlspecialchars($success) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Registration Form -->
                        <form method="POST" class="space-y-6" id="registerForm">
                            <input type="hidden" name="epis_supervisor_id" id="selectedEpisId" value="<?= $form_data['epis_supervisor_id'] ?? '' ?>">
                            <input type="hidden" name="referral_code" value="<?= htmlspecialchars($form_data['referral_code'] ?? $_GET['ref'] ?? '') ?>">
                            
                            <!-- Personal Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                                        Full Name *
                                    </label>
                                    <input type="text" id="name" name="name" required 
                                           value="<?= htmlspecialchars($form_data['name'] ?? '') ?>"
                                           class="input-focus w-full px-4 py-3 bg-surface-2 border border-ink-600 rounded-lg text-ink-100 placeholder-ink-400 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-400">
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                                        Email Address *
                                    </label>
                                    <input type="email" id="email" name="email" required 
                                           value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                           class="input-focus w-full px-4 py-3 bg-surface-2 border border-ink-600 rounded-lg text-ink-100 placeholder-ink-400 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-400">
                                </div>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                                    Phone Number (WhatsApp)
                                </label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>"
                                       placeholder="+62812345678"
                                       class="input-focus w-full px-4 py-3 bg-surface-2 border border-ink-600 rounded-lg text-ink-100 placeholder-ink-400 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-400">
                            </div>
                            
                            <!-- Password Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                                        Password *
                                    </label>
                                    <input type="password" id="password" name="password" required 
                                           minlength="6"
                                           class="input-focus w-full px-4 py-3 bg-surface-2 border border-ink-600 rounded-lg text-ink-100 placeholder-ink-400 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-400">
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                                        Confirm Password *
                                    </label>
                                    <input type="password" id="confirm_password" name="confirm_password" required 
                                           minlength="6"
                                           class="input-focus w-full px-4 py-3 bg-surface-2 border border-ink-600 rounded-lg text-ink-100 placeholder-ink-400 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-400">
                                </div>
                            </div>
                            
                            <!-- EPIS Supervisor Selection Status -->
                            <?php if ($epis_enabled && !empty($available_epis)): ?>
                            <div id="episSelectionStatus" class="bg-warning bg-opacity-20 border border-warning border-opacity-30 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span class="text-yellow-300 text-sm">Please select an EPIS supervisor from the left panel</span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Terms and Marketing -->
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <input type="checkbox" id="terms" name="terms" required 
                                           class="mt-1 h-4 w-4 text-gold-400 focus:ring-gold-400 bg-surface-2 border border-ink-600 rounded">
                                    <label for="terms" class="ml-3 text-sm text-ink-100">
                                        I agree to the <a href="#" class="text-gold-400 hover:text-gold-300 underline">Terms of Service</a> 
                                        and <a href="#" class="text-gold-400 hover:text-gold-300 underline">Privacy Policy</a> *
                                    </label>
                                </div>
                                
                                <div class="flex items-start">
                                    <input type="checkbox" id="marketing" name="marketing" 
                                           <?= ($form_data['marketing'] ?? false) ? 'checked' : '' ?>
                                           class="mt-1 h-4 w-4 text-gold-400 focus:ring-gold-400 bg-surface-2 border border-ink-600 rounded">
                                    <label for="marketing" class="ml-3 text-sm text-ink-100">
                                        I agree to receive marketing communications and updates
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" id="registerBtn" 
                                    class="btn-primary w-full text-ink-900 font-semibold py-4 px-6 rounded-lg bg-gradient-to-r from-gold-400 to-gold-500 hover:from-gold-500 hover:to-gold-600 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:ring-offset-2 focus:ring-offset-transparent">
                                <span id="registerBtnText">Create EPIC Account</span>
                                <svg id="registerSpinner" class="hidden animate-spin -mr-1 ml-3 h-5 w-5 text-ink-900 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </form>
                        
                        <!-- Login Link -->
                        <div class="text-center mt-6">
                            <p class="text-ink-300 text-sm">
                                Already have an account? 
                                <a href="<?= epic_url('login') ?>" class="text-gold-400 hover:text-gold-300 font-medium">Sign in here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let selectedEpisId = <?= json_encode($form_data['epis_supervisor_id'] ?? 0) ?>;
        
        function selectEpis(episId) {
            // Remove previous selection
            document.querySelectorAll('.epis-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            document.querySelectorAll('.epis-radio').forEach(radio => {
                radio.innerHTML = '';
                radio.classList.remove('bg-pink-500');
            });
            
            // Add new selection
            const selectedCard = document.querySelector(`[data-epis-id="${episId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
                
                const radio = selectedCard.querySelector('.epis-radio');
                radio.classList.add('bg-pink-500');
                radio.innerHTML = '<svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>';
            }
            
            // Update hidden field
            document.getElementById('selectedEpisId').value = episId;
            selectedEpisId = episId;
            
            // Update status message
            const statusDiv = document.getElementById('episSelectionStatus');
            if (statusDiv) {
                statusDiv.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-green-300 text-sm">EPIS supervisor selected successfully</span>
                    </div>
                `;
                statusDiv.className = 'bg-success bg-opacity-20 border border-success border-opacity-30 rounded-lg p-4';
            }
        }
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            <?php if ($epis_enabled && !empty($available_epis)): ?>
            if (selectedEpisId <= 0) {
                e.preventDefault();
                alert('Please select an EPIS supervisor before registering!');
                return;
            }
            <?php endif; ?>
            
            const btn = document.getElementById('registerBtn');
            const btnText = document.getElementById('registerBtnText');
            const spinner = document.getElementById('registerSpinner');
            
            btn.disabled = true;
            btnText.textContent = 'Creating Account...';
            spinner.classList.remove('hidden');
        });
        
        // Initialize selection if form data exists
        if (selectedEpisId > 0) {
            selectEpis(selectedEpisId);
        }
        
        // Auto-focus name field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name').focus();
        });
    </script>
</body>
</html>