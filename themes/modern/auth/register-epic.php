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
        // RATE LIMITING - Prevent registration spam
        require_once EPIC_ROOT . '/core/rate-limiter.php';
        epic_check_registration_rate_limit();
        
        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $epis_supervisor_id = (int)($_POST['epis_supervisor_id'] ?? 0);
        $terms = isset($_POST['terms']);
        $marketing = isset($_POST['marketing']);
        $referral_code = trim($_POST['referral_code'] ?? '');
        
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
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .epic-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .epis-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .error-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .epis-card {
            transition: all 0.3s ease;
        }
        
        .epis-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .epis-card.selected {
            border-color: #f093fb;
            box-shadow: 0 0 20px rgba(240, 147, 251, 0.3);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
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
                            <div class="bg-red-500 bg-opacity-20 border border-red-500 border-opacity-30 rounded-lg p-4 mb-6 error-shake">
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
                            <div class="bg-green-500 bg-opacity-20 border border-green-500 border-opacity-30 rounded-lg p-4 mb-6">
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
                                           class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                                        Email Address *
                                    </label>
                                    <input type="email" id="email" name="email" required 
                                           value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                           class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                                    Phone Number (WhatsApp)
                                </label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>"
                                       placeholder="+62812345678"
                                       class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            </div>
                            
                            <!-- Password Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                                        Password *
                                    </label>
                                    <input type="password" id="password" name="password" required 
                                           minlength="6"
                                           class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                                        Confirm Password *
                                    </label>
                                    <input type="password" id="confirm_password" name="confirm_password" required 
                                           minlength="6"
                                           class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <!-- EPIS Supervisor Selection Status -->
                            <?php if ($epis_enabled && !empty($available_epis)): ?>
                            <div id="episSelectionStatus" class="bg-yellow-500 bg-opacity-20 border border-yellow-500 border-opacity-30 rounded-lg p-4">
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
                                           class="mt-1 h-4 w-4 text-pink-500 focus:ring-pink-500 border-white border-opacity-30 rounded">
                                    <label for="terms" class="ml-3 text-sm text-white text-opacity-90">
                                        I agree to the <a href="#" class="text-pink-300 hover:text-pink-200 underline">Terms of Service</a> 
                                        and <a href="#" class="text-pink-300 hover:text-pink-200 underline">Privacy Policy</a> *
                                    </label>
                                </div>
                                
                                <div class="flex items-start">
                                    <input type="checkbox" id="marketing" name="marketing" 
                                           <?= ($form_data['marketing'] ?? false) ? 'checked' : '' ?>
                                           class="mt-1 h-4 w-4 text-pink-500 focus:ring-pink-500 border-white border-opacity-30 rounded">
                                    <label for="marketing" class="ml-3 text-sm text-white text-opacity-90">
                                        I agree to receive marketing communications and updates
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" id="registerBtn" 
                                    class="w-full epic-gradient text-white font-semibold py-4 px-6 rounded-lg hover:opacity-90 transition-opacity duration-300 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 focus:ring-offset-transparent">
                                <span id="registerBtnText">Create EPIC Account</span>
                                <svg id="registerSpinner" class="hidden animate-spin -mr-1 ml-3 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </form>
                        
                        <!-- Login Link -->
                        <div class="text-center mt-6">
                            <p class="text-white text-opacity-70 text-sm">
                                Already have an account? 
                                <a href="<?= epic_url('login') ?>" class="text-pink-300 hover:text-pink-200 font-medium">Sign in here</a>
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
                statusDiv.className = 'bg-green-500 bg-opacity-20 border border-green-500 border-opacity-30 rounded-lg p-4';
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