<?php
/**
 * EPIC HUB - Bisnis Emas Perak Indonesia - Email Confirmation Page
 * Modern email confirmation interface with consistent design
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Redirect if already logged in
if (epic_is_logged_in()) {
    epic_redirect(epic_url('dashboard'));
}

// Get user email from session or parameter
$user_email = $_SESSION['registration_email'] ?? $_GET['email'] ?? '';
$user_name = $_SESSION['registration_name'] ?? $_GET['name'] ?? '';

// Clear registration session data
unset($_SESSION['registration_email'], $_SESSION['registration_name']);

// If no email provided, redirect to register
if (empty($user_email)) {
    epic_redirect(epic_url('register'));
}

$resend_success = $_GET['resent'] ?? false;
$resend_error = $_GET['error'] ?? false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['page_title'] ?? 'Konfirmasi Email - EPIC HUB - Bisnis Emas Perak Indonesia' ?></title>
    
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
        
        .btn-secondary {
            background: transparent;
            color: var(--gold-400);
            border: 1px solid var(--gold-400);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-secondary:hover {
            background: var(--gold-400);
            color: var(--ink-900);
            transform: translateY(-1px);
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
            background: var(--gradient-gold-subtle);
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
            bottom: 30%;
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
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.6;
            }
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .success-icon {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: successPulse 2s ease-in-out infinite;
        }
        
        @keyframes successPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }
        }
        
        .countdown {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--gold-400);
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
    
    <!-- Email Confirmation Container -->
    <div class="w-full max-w-lg">
        <!-- Email Confirmation Card -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <!-- Logo Inside Card -->
            <div class="text-center mb-8">
                <?php 
                $site_logo = epic_setting('site_logo');
                if ($site_logo && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_logo)): 
                ?>
                    <div class="inline-flex items-center justify-center mb-6">
                        <img src="<?= epic_url('uploads/logos/' . $site_logo) ?>" 
                             alt="EPIC Hub" 
                             class="h-20 w-auto">
                    </div>
                <?php else: ?>
                    <div class="inline-flex items-center justify-center mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl flex items-center justify-center">
                            <span class="text-2xl font-bold text-gray-900">EH</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Success Icon -->
            <div class="success-icon">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            
            <div class="text-center mb-8">
                <h2 class="text-2xl font-semibold text-white mb-2">Registrasi Berhasil!</h2>
                <p class="text-white text-opacity-70 mb-4">Silakan periksa email Anda untuk konfirmasi</p>
            </div>
            
            <!-- Success Message -->
            <?php if ($resend_success): ?>
                <div class="mb-6 p-4 bg-green-500 bg-opacity-20 border border-green-500 border-opacity-30 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-green-300">Email konfirmasi telah dikirim ulang!</span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Error Message -->
            <?php if ($resend_error): ?>
                <div class="mb-6 p-4 bg-red-500 bg-opacity-20 border border-red-500 border-opacity-30 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-red-300"><?= htmlspecialchars($resend_error) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Email Confirmation Info -->
            <div class="bg-gradient-to-r from-blue-500 bg-opacity-10 to-purple-500 bg-opacity-10 border border-blue-500 border-opacity-20 rounded-lg p-6 mb-6">
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-white mb-3">Konfirmasi Email Diperlukan</h3>
                    <p class="text-white text-opacity-80 text-sm mb-4">
                        Kami telah mengirimkan email konfirmasi ke:
                    </p>
                    <div class="bg-black bg-opacity-20 rounded-lg p-3 mb-4">
                        <p class="text-gold-400 font-semibold"><?= htmlspecialchars($user_email) ?></p>
                    </div>
                    
                    <?php if (!empty($user_name)): ?>
                        <p class="text-white text-opacity-70 text-sm mb-4">
                            Halo <span class="text-gold-400 font-medium"><?= htmlspecialchars($user_name) ?></span>, 
                            silakan cek email Anda dan klik link konfirmasi untuk mengaktifkan akun.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="space-y-4 mb-8">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-6 h-6 bg-gold-500 bg-opacity-20 rounded-full flex items-center justify-center mt-0.5">
                        <span class="text-gold-400 text-sm font-bold">1</span>
                    </div>
                    <p class="text-white text-opacity-80 text-sm">
                        Buka aplikasi email Anda dan cari email dari <strong>EPIC HUB</strong>
                    </p>
                </div>
                
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-6 h-6 bg-gold-500 bg-opacity-20 rounded-full flex items-center justify-center mt-0.5">
                        <span class="text-gold-400 text-sm font-bold">2</span>
                    </div>
                    <p class="text-white text-opacity-80 text-sm">
                        Klik tombol <strong>"Konfirmasi Email"</strong> atau link yang tersedia
                    </p>
                </div>
                
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-6 h-6 bg-gold-500 bg-opacity-20 rounded-full flex items-center justify-center mt-0.5">
                        <span class="text-gold-400 text-sm font-bold">3</span>
                    </div>
                    <p class="text-white text-opacity-80 text-sm">
                        Setelah konfirmasi, Anda dapat login dan mulai menggunakan platform
                    </p>
                </div>
            </div>
            
            <!-- Tips -->
            <div class="bg-yellow-500 bg-opacity-10 border border-yellow-500 border-opacity-20 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-yellow-300 text-sm font-medium mb-1">Tips:</p>
                        <ul class="text-yellow-200 text-sm space-y-1">
                            <li>• Periksa folder spam/junk jika email tidak ditemukan</li>
                            <li>• Email konfirmasi berlaku selama 24 jam</li>
                            <li>• Pastikan koneksi internet stabil saat mengklik link</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="space-y-3">
                <!-- Resend Email Button -->
                <form method="POST" action="<?= epic_url('resend-confirmation') ?>" class="w-full">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($user_email) ?>">
                    <input type="hidden" name="csrf_token" value="<?= epic_csrf_token() ?>">
                    <button type="submit" 
                            class="w-full btn-primary py-3 px-6 rounded-lg font-semibold transition-all duration-300 hover:shadow-lg"
                            id="resendBtn">
                        <span id="resendText">Kirim Ulang Email Konfirmasi</span>
                        <span id="resendCountdown" class="hidden">Kirim Ulang dalam <span class="countdown">60</span>s</span>
                    </button>
                </form>
                
                <!-- Back to Login -->
                <a href="<?= epic_url('login') ?>" 
                   class="w-full btn-secondary py-3 px-6 rounded-lg font-semibold text-center block transition-all duration-300">
                    Kembali ke Login
                </a>
                
                <!-- Back to Register -->
                <a href="<?= epic_url('register') ?>" 
                   class="w-full text-center text-white text-opacity-60 hover:text-opacity-80 text-sm transition-colors duration-300 block mt-4">
                    Daftar dengan email lain
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-white text-opacity-50 text-sm">
                © <?= date('Y') ?> EPIC HUB - Bisnis Emas Perak Indonesia. All rights reserved.
            </p>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Resend email countdown
        let resendCountdown = 60;
        let countdownInterval;
        
        function startResendCountdown() {
            const resendBtn = document.getElementById('resendBtn');
            const resendText = document.getElementById('resendText');
            const resendCountdownEl = document.getElementById('resendCountdown');
            const countdownSpan = resendCountdownEl.querySelector('.countdown');
            
            resendBtn.disabled = true;
            resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
            resendText.classList.add('hidden');
            resendCountdownEl.classList.remove('hidden');
            
            countdownInterval = setInterval(() => {
                resendCountdown--;
                countdownSpan.textContent = resendCountdown;
                
                if (resendCountdown <= 0) {
                    clearInterval(countdownInterval);
                    resendBtn.disabled = false;
                    resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    resendText.classList.remove('hidden');
                    resendCountdownEl.classList.add('hidden');
                    resendCountdown = 60;
                }
            }, 1000);
        }
        
        // Start countdown if page was loaded after resend
        <?php if ($resend_success): ?>
            startResendCountdown();
        <?php endif; ?>
        
        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const resendBtn = document.getElementById('resendBtn');
            if (resendBtn.disabled) {
                e.preventDefault();
                return false;
            }
            
            // Start countdown after form submission
            setTimeout(() => {
                startResendCountdown();
            }, 100);
        });
        
        // Auto-refresh page every 5 minutes to check if user has confirmed
        setInterval(() => {
            // Check if user is logged in (simple check)
            fetch('<?= epic_url('api/check-auth') ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.authenticated) {
                        window.location.href = '<?= epic_url('dashboard') ?>';
                    }
                })
                .catch(error => {
                    // Ignore errors, just continue
                });
        }, 300000); // 5 minutes
    </script>
</body>
</html>