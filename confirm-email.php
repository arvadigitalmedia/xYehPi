<?php
/**
 * Email Confirmation Handler
 * Handles email confirmation token validation
 */

// Include core functions
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/email-confirmation.php';

// Get token from URL
$token = $_GET['token'] ?? '';
$success = false;
$error_message = '';
$user_data = null;

if (empty($token)) {
    $error_message = 'Token konfirmasi tidak valid atau kosong.';
} else {
    try {
        // Validate token
        $stored_token = db()->selectOne(
            "SELECT * FROM " . db()->table('user_tokens') . " 
             WHERE token = ? AND type = 'email_verification' 
             AND expires_at > NOW() AND used_at IS NULL", 
            [$token]
        );
        
        if (!$stored_token) {
            $error_message = 'Token konfirmasi tidak valid, sudah digunakan, atau sudah kedaluwarsa.';
        } else {
            // Get user data
            $user = db()->selectOne("SELECT * FROM " . db()->table('users') . " WHERE id = ?", [$stored_token['user_id']]);
            
            if (!$user) {
                $error_message = 'User tidak ditemukan.';
            } else {
                // Check if already confirmed
                if ($user['email_verified_at']) {
                    $success = true;
                    $user_data = $user;
                    $error_message = 'Email sudah dikonfirmasi sebelumnya.';
                } else {
                    // Update user status to free (confirmed user)
                    $update_user = db()->query(
                        "UPDATE " . db()->table('users') . " 
                         SET status = 'free', email_verified_at = NOW() 
                         WHERE id = ?", 
                        [$user['id']]
                    );
                    
                    // Mark token as used
                    $update_token = db()->query(
                        "UPDATE " . db()->table('user_tokens') . " 
                         SET used_at = NOW() 
                         WHERE id = ?", 
                        [$stored_token['id']]
                    );
                    
                    if ($update_user && $update_token) {
                        $success = true;
                        $user_data = $user;
                        
                        // Set success session for login page
                        $_SESSION['epic_success'] = 'Email berhasil dikonfirmasi! Silakan login untuk melanjutkan.';
                        
                        // Send welcome email (optional)
                        try {
                            if (function_exists('epic_send_welcome_email')) {
                                epic_send_welcome_email($user);
                            }
                        } catch (Exception $e) {
                            // Log error but don't fail the confirmation
                            error_log('Welcome email failed: ' . $e->getMessage());
                        }
                        
                        // Redirect ke halaman login dengan pesan sukses
                         header('Location: ' . epic_url('login?confirmed=1'));
                         exit;
                    } else {
                        $error_message = 'Terjadi kesalahan saat mengupdate data. Silakan coba lagi.';
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        error_log('Email confirmation error: ' . $e->getMessage());
        $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
    }
}

// Clear any existing session data that might cause conflicts
unset($_SESSION['epic_email_confirmation']);

// Generate CSRF token for resend form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Email - EPIC Hub</title>
    
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
        }
        
        .input-focus:focus {
            border-color: var(--gold-400);
            box-shadow: 0 0 0 3px rgba(207, 168, 78, 0.15);
            background: white;
        }
        
        .btn-primary {
            background: var(--gradient-gold);
            border: 1px solid var(--gold-400);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(207, 168, 78, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--surface-3) 0%, var(--surface-4) 100%);
            border: 1px solid var(--ink-500);
            color: var(--ink-100);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            border-color: var(--gold-400);
            background: linear-gradient(135deg, var(--surface-4) 0%, var(--surface-3) 100%);
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
        
        /* Floating Shapes */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
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
            top: 70%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 40%;
            left: 80%;
            animation-delay: 4s;
        }
        
        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 1s;
        }
        
        .shape:nth-child(5) {
            width: 90px;
            height: 90px;
            top: 20%;
            right: 30%;
            animation-delay: 3s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.5;
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.8;
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .shape {
                display: none;
            }
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
    
    <!-- Main Container -->
    <div class="w-full max-w-md">
        <!-- Confirmation Card -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <!-- Logo Website -->
            <div class="text-center mb-6">
                <?php 
                $site_logo = epic_setting('site_logo');
                if (!empty($site_logo) && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_logo)): 
                ?>
                    <div class="mb-4">
                        <img src="<?= epic_url('uploads/logos/' . $site_logo) ?>" 
                             alt="<?= htmlspecialchars(epic_setting('site_name', 'EPIC Hub')) ?>" 
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
                
                <!-- Status Icon -->
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center mb-4 <?php echo $success ? 'bg-green-500 bg-opacity-20' : 'bg-red-500 bg-opacity-20'; ?>">
                    <?php if ($success): ?>
                        <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                
                <h1 class="text-2xl font-semibold text-white mb-2">
                    <?php echo $success ? 'Email Berhasil Dikonfirmasi!' : 'Konfirmasi Email Gagal'; ?>
                </h1>
                <p class="text-white text-opacity-70">
                    <?php echo $success ? 'Akun Anda sekarang aktif dan siap digunakan' : 'Terjadi masalah dengan konfirmasi email Anda'; ?>
                </p>
            </div>

            <?php if ($success): ?>
                <div class="success-message rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-300">Konfirmasi Berhasil</h3>
                            <div class="mt-2 text-sm text-green-200">
                                <?php if ($user_data): ?>
                                    <p>Selamat datang, <strong><?php echo htmlspecialchars($user_data['name']); ?></strong>!</p>
                                    <p>Email <strong><?php echo htmlspecialchars($user_data['email']); ?></strong> telah berhasil dikonfirmasi.</p>
                                <?php endif; ?>
                                <p class="mt-2">Akun Anda sekarang aktif dan siap digunakan.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <a href="<?= epic_url('login') ?>" 
                       class="w-full btn-primary text-ink-900 font-semibold py-3 px-4 rounded-lg text-center block transition-all duration-300">
                        LOGIN SEKARANG
                    </a>
                    <a href="<?= epic_url('dashboard') ?>" 
                       class="w-full btn-secondary font-medium py-3 px-4 rounded-lg text-center block transition-all duration-300">
                        Ke Dashboard
                    </a>
                </div>
            
            <?php else: ?>
                <div class="error-message rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-300">Konfirmasi Gagal</h3>
                            <div class="mt-2 text-sm text-red-200">
                                <p><?php echo htmlspecialchars($error_message); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <!-- Resend Email Form -->
                    <div class="bg-surface-3 bg-opacity-50 rounded-lg p-4 border border-ink-600">
                        <h4 class="text-sm font-medium text-white mb-3">Kirim Ulang Email Konfirmasi</h4>
                        <form id="resendForm" class="space-y-3">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="space-y-2">
                                <input type="email" 
                                       id="resendEmail" 
                                       name="email" 
                                       placeholder="Masukkan email Anda"
                                       class="w-full px-3 py-2 bg-white border border-ink-600 rounded-lg text-black placeholder-gray-500 input-focus transition-all duration-300"
                                       required>
                                <button type="submit" 
                                        id="resendBtn"
                                        class="w-full btn-primary text-ink-900 font-semibold py-2 px-4 rounded-lg transition-all duration-300">
                                    Kirim Ulang Email
                                </button>
                            </div>
                        </form>
                        
                        <!-- Resend Status Message -->
                        <div id="resendMessage" class="hidden mt-3 p-3 rounded-lg text-sm"></div>
                    </div>
                </div>
            <?php endif; ?>
        
            <div class="mt-6 text-center">
                <p class="text-sm text-white text-opacity-60">
                    Kembali ke Akun Anda? 
                    <a href="<?= epic_url('login') ?>" class="text-gold-400 hover:text-gold-300 transition-colors">Klik Disini</a>
                </p>
            </div>
        </div>
        
        <!-- Footer Links -->
        <div class="text-center mt-8">
            <div class="flex justify-center space-x-6 text-sm text-white">
                <a href="<?= epic_url() ?>" class="hover:text-gray-300 transition-colors">Home</a>
                <a href="<?= epic_url('about') ?>" class="hover:text-gray-300 transition-colors">About</a>
                <a href="<?= epic_url('contact') ?>" class="hover:text-gray-300 transition-colors">Contact</a>
                <a href="<?= epic_url('privacy') ?>" class="hover:text-gray-300 transition-colors">Privacy</a>
            </div>
            <p class="mt-4 text-xs text-white">
                © <?= date('Y') ?> EPIC Hub. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        // Auto-focus pada input email jika ada form resend
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('resendEmail');
            if (emailInput) {
                emailInput.focus();
            }
            
            // Animasi fade-in untuk card
            const card = document.querySelector('.glass-effect');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }
        });

        document.getElementById('resendForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const resendBtn = document.getElementById('resendBtn');
            const resendMessage = document.getElementById('resendMessage');
            
            // Disable button and show loading
            resendBtn.disabled = true;
            resendBtn.textContent = 'Mengirim...';
            resendMessage.className = 'hidden p-3 rounded-lg text-sm';
            
            try {
                const response = await fetch('/resend-confirmation.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resendMessage.className = 'p-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-700';
                    resendMessage.textContent = result.message;
                    
                    // Show debug URL in development
                    if (result.debug_url) {
                        resendMessage.innerHTML += '<br><small>Debug URL: <a href="' + result.debug_url + '" target="_blank" class="underline">' + result.debug_url + '</a></small>';
                    }
                    
                    // Reset form
                    form.reset();
                } else {
                    resendMessage.className = 'p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700';
                    resendMessage.textContent = result.message;
                }
                
            } catch (error) {
                resendMessage.className = 'p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700';
                resendMessage.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
            } finally {
                // Re-enable button
                resendBtn.disabled = false;
                resendBtn.textContent = 'Kirim Ulang';
            }
        });
    </script>
</body>
</html>