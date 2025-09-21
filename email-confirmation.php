<?php
/**
 * Email Confirmation Page
 * Displays information about email confirmation after registration
 */

// Include core functions
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/email-confirmation.php';

// Redirect if already logged in
if (epic_is_logged_in()) {
    epic_redirect('dashboard');
}

// Get email confirmation data from session
$confirmation_data = $_SESSION['epic_email_confirmation'] ?? null;

// If no confirmation data, redirect to register
if (!$confirmation_data) {
    epic_redirect('register');
}

// Handle resend email request
$resend_message = null;
$resend_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_email'])) {
    try {
        // Get user data
        $user = epic_get_user($confirmation_data['user_id']);
        
        if ($user) {
            $result = epic_send_confirmation_email($user);
            
            if ($result['success']) {
                $resend_message = 'Email konfirmasi berhasil dikirim ulang!';
            } else {
                $resend_error = $result['message'];
            }
        } else {
            $resend_error = 'User tidak ditemukan';
        }
        
    } catch (Exception $e) {
        $resend_error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Clear session data after use (but keep for resend functionality)
// unset($_SESSION['epic_email_confirmation']);

$page_title = 'Konfirmasi Email - ' . epic_setting('site_name');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    <!-- Dynamic Favicon -->
    <?php 
    $favicon_path = epic_url('assets/images/favicon.ico');
    if (file_exists(__DIR__ . '/assets/images/logo-icon.png')) {
        $favicon_path = epic_url('assets/images/logo-icon.png');
    }
    ?>
    <link rel="icon" type="image/x-icon" href="<?= $favicon_path ?>">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root {
            /* Gold Palette */
            --gold-50: #fffdf7;
            --gold-100: #fef7e0;
            --gold-200: #fdecc8;
            --gold-300: #fbd38d;
            --gold-400: #f6ad55;
            --gold-500: #d4af37;
            --gold-600: #b7791f;
            --gold-700: #975a16;
            --gold-800: #744210;
            --gold-900: #5f370e;
            
            /* Ink/Dark Palette */
            --ink-50: #f8fafc;
            --ink-100: #f1f5f9;
            --ink-200: #e2e8f0;
            --ink-300: #cbd5e1;
            --ink-400: #94a3b8;
            --ink-500: #64748b;
            --ink-600: #475569;
            --ink-700: #334155;
            --ink-800: #1e293b;
            --ink-900: #0f172a;
            
            /* Surface Layers */
            --surface-1: rgba(15, 23, 42, 0.95);
            --surface-2: rgba(30, 41, 59, 0.9);
            --surface-3: rgba(51, 65, 85, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--ink-900) 0%, var(--ink-800) 50%, var(--ink-900) 100%);
            min-height: 100vh;
            color: white;
        }

        /* Shimmer Effect */
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.3), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Glass Effect */
        .glass-effect {
            background: var(--surface-2);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--gold-500) 0%, var(--gold-400) 100%);
            color: var(--ink-900);
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
            background: linear-gradient(135deg, var(--gold-400) 0%, var(--gold-300) 100%);
        }

        .btn-secondary {
            background: transparent;
            color: var(--gold-400);
            border: 2px solid var(--gold-500);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--gold-500);
            color: var(--ink-900);
            transform: translateY(-1px);
        }

        /* Input Focus */
        .input-focus:focus {
            border-color: var(--gold-500);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
            outline: none;
        }

        /* Floating Shapes */
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.15;
            animation: float 6s ease-in-out infinite;
            z-index: 1;
        }

        .floating-shape:nth-child(1) {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--gold-500), var(--gold-400));
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--gold-400), var(--gold-300));
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--gold-600), var(--gold-500));
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Success Icon Animation */
        .success-icon {
            animation: successPulse 2s ease-in-out infinite;
            background: linear-gradient(135deg, var(--gold-500), var(--gold-400));
            border: 3px solid var(--gold-600);
        }

        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Email Animation */
        .email-animation {
            animation: emailBounce 3s ease-in-out infinite;
        }

        @keyframes emailBounce {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Loading Animation */
        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid var(--ink-900);
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .glass-effect {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .floating-shape {
                opacity: 0.08;
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Floating Background Shapes -->
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>

    <!-- Main Container -->
    <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-md p-8 relative z-10">
        <!-- Logo Website -->
        <div class="text-center mb-6">
            <?php 
            $logo_path = __DIR__ . '/assets/images/logo.png';
            if (file_exists($logo_path)): ?>
                <img src="<?= epic_url('assets/images/logo.png') ?>" alt="<?= htmlspecialchars(epic_setting('site_name')) ?>" class="h-12 mx-auto mb-4">
            <?php else: ?>
                <div class="text-2xl font-bold text-gold-400 mb-4"><?= htmlspecialchars(epic_setting('site_name')) ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Success Icon -->
        <div class="text-center mb-8">
            <div class="success-icon inline-flex items-center justify-center w-20 h-20 rounded-full mb-4">
                <svg class="w-10 h-10 text-gold-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">Registrasi Berhasil!</h1>
            <p class="text-white text-opacity-70">Silakan periksa email Anda untuk konfirmasi</p>
        </div>

        <!-- Email Info -->
        <div class="bg-gold-500 bg-opacity-10 border border-gold-500 border-opacity-30 rounded-lg p-4 mb-6">
            <div class="flex items-center mb-3">
                <div class="email-animation mr-3">
                    <svg class="w-6 h-6 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white">Email Konfirmasi Dikirim</h3>
                    <p class="text-sm text-gold-300"><?= htmlspecialchars($confirmation_data['email']) ?></p>
                </div>
            </div>
            
            <?php if ($confirmation_data['email_sent']): ?>
                <div class="flex items-center text-green-300 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Email berhasil dikirim
                </div>
            <?php else: ?>
                <div class="flex items-center text-red-300 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <?= htmlspecialchars($confirmation_data['message']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Instructions -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-800 mb-3">Langkah Selanjutnya:</h3>
            <ol class="text-sm text-gray-600 space-y-2">
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-500 text-white text-xs rounded-full mr-3 mt-0.5 flex-shrink-0">1</span>
                    Buka email Anda dan cari email dari <?= htmlspecialchars(epic_setting('site_name')) ?>
                </li>
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-500 text-white text-xs rounded-full mr-3 mt-0.5 flex-shrink-0">2</span>
                    Klik tombol "Konfirmasi Email" dalam email tersebut
                </li>
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-500 text-white text-xs rounded-full mr-3 mt-0.5 flex-shrink-0">3</span>
                    Setelah dikonfirmasi, Anda dapat login ke akun Anda
                </li>
            </ol>
        </div>

        <!-- Resend Email Section -->
        <?php if ($resend_message): ?>
            <div class="bg-green-500 bg-opacity-20 border border-green-500 border-opacity-30 rounded-lg p-4 mb-4">
                <div class="flex items-center text-green-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <?= htmlspecialchars($resend_message) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($resend_error): ?>
            <div class="bg-red-500 bg-opacity-20 border border-red-500 border-opacity-30 rounded-lg p-4 mb-4">
                <div class="flex items-center text-red-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <?= htmlspecialchars($resend_error) ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="mb-6" id="resendForm">
            <button type="submit" name="resend_email" class="w-full btn-primary font-semibold py-3 px-4 rounded-lg transition-all duration-300">
                Kirim Ulang Email Konfirmasi
            </button>
        </form>

        <!-- Additional Actions -->
        <div class="text-center space-y-3">
            <p class="text-sm text-white text-opacity-60">
                Tidak menerima email? Periksa folder spam/junk Anda
            </p>
            
            <div class="flex justify-center space-x-4 text-sm">
                <a href="<?= epic_url('login') ?>" class="text-gold-400 hover:text-gold-300 transition-colors">
                    Sudah Konfirmasi? Login
                </a>
                <span class="text-white text-opacity-40">|</span>
                <a href="<?= epic_url('register') ?>" class="text-gold-400 hover:text-gold-300 transition-colors">
                    Daftar Lagi
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-white border-opacity-20 text-center">
            <p class="text-xs text-white text-opacity-60">
                © <?= date('Y') ?> <?= htmlspecialchars(epic_setting('site_name')) ?>. Semua hak dilindungi.
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
        // Auto-focus dan animasi fade-in
        document.addEventListener('DOMContentLoaded', function() {
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

        // Auto-refresh page every 30 seconds to check for confirmation
        setTimeout(function() {
            // Check if email was confirmed by making a simple request
            fetch('<?= epic_url("api/check-email-status") ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.confirmed) {
                        window.location.href = '<?= epic_url("login?confirmed=1") ?>';
                    }
                })
                .catch(error => {
                    // Silently fail
                });
        }, 30000);

        // Handle form submission dengan loading state
        function handleFormSubmit(form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="loading-spinner"></div> Mengirim...';
            
            // Submit form
            form.submit();
        }

        // Attach event listener ke form resend
        const resendForm = document.getElementById('resendForm');
        if (resendForm) {
            resendForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleFormSubmit(this);
            });
        }
    </script>
</body>
</html>