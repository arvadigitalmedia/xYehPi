<?php
/**
 * Email Confirmed Success Page
 * Displays success message after email confirmation
 */

// Include core functions
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/email-confirmation.php';

// Get confirmation result from session
$confirmation_result = $_SESSION['epic_email_confirmed'] ?? null;

// If no confirmation result, redirect to login
if (!$confirmation_result) {
    epic_redirect('login');
}

// Clear session data
unset($_SESSION['epic_email_confirmed']);

$page_title = 'Email Dikonfirmasi - ' . epic_setting('site_name');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= epic_url('assets/images/favicon.ico') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-gold: #d4af37;
            --color-gold-light: #f4e4a6;
            --color-gold-dark: #b8941f;
            --color-ink: #1a1a1a;
            --color-ink-light: #2d2d2d;
            --color-surface: #f8fafc;
            --color-surface-dark: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--color-surface) 0%, #ffffff 100%);
            min-height: 100vh;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-gold) 0%, var(--color-gold-light) 100%);
            color: var(--color-ink);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
        }

        .success-animation {
            animation: successScale 1s ease-out;
        }

        @keyframes successScale {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--color-gold);
            animation: confetti-fall 3s linear infinite;
        }

        .confetti:nth-child(odd) {
            background: var(--color-gold-light);
            animation-delay: 0.5s;
        }

        .confetti:nth-child(3n) {
            background: #10b981;
            animation-delay: 1s;
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .floating-shape:nth-child(1) {
            width: 80px;
            height: 80px;
            background: var(--color-gold);
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 120px;
            height: 120px;
            background: #10b981;
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            width: 60px;
            height: 60px;
            background: var(--color-gold-dark);
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Floating Background Shapes -->
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>

    <!-- Confetti Animation -->
    <div class="confetti" style="left: 10%; animation-delay: 0s;"></div>
    <div class="confetti" style="left: 20%; animation-delay: 0.2s;"></div>
    <div class="confetti" style="left: 30%; animation-delay: 0.4s;"></div>
    <div class="confetti" style="left: 40%; animation-delay: 0.6s;"></div>
    <div class="confetti" style="left: 50%; animation-delay: 0.8s;"></div>
    <div class="confetti" style="left: 60%; animation-delay: 1s;"></div>
    <div class="confetti" style="left: 70%; animation-delay: 1.2s;"></div>
    <div class="confetti" style="left: 80%; animation-delay: 1.4s;"></div>
    <div class="confetti" style="left: 90%; animation-delay: 1.6s;"></div>

    <!-- Main Container -->
    <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-md p-8 relative z-10">
        <!-- Success Animation -->
        <div class="text-center mb-8 success-animation">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-6">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-3">🎉 Selamat!</h1>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Email Berhasil Dikonfirmasi</h2>
            <p class="text-gray-600">Akun Anda telah aktif dan siap digunakan</p>
        </div>

        <!-- Success Message -->
        <?php if ($confirmation_result['success']): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center text-green-700">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold">Konfirmasi Berhasil!</p>
                        <p class="text-sm"><?= htmlspecialchars($confirmation_result['message']) ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center text-red-700">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold">Konfirmasi Gagal</p>
                        <p class="text-sm"><?= htmlspecialchars($confirmation_result['message']) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Welcome Benefits -->
        <?php if ($confirmation_result['success']): ?>
            <div class="mb-6">
                <h3 class="font-semibold text-gray-800 mb-4">Apa yang bisa Anda lakukan sekarang:</h3>
                <div class="space-y-3">
                    <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Akses Dashboard Pribadi</p>
                            <p class="text-sm text-gray-600">Kelola profil dan pengaturan akun</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-green-50 rounded-lg">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 15h8v-2H4v2zM4 11h10V9H4v2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Fitur Lengkap Tersedia</p>
                            <p class="text-sm text-gray-600">Gunakan semua layanan yang ada</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-yellow-50 rounded-lg">
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 15h8v-2H4v2zM4 11h10V9H4v2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Notifikasi Email</p>
                            <p class="text-sm text-gray-600">Terima update dan informasi penting</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <?php if ($confirmation_result['success']): ?>
                <a href="<?= epic_url('login') ?>" class="block w-full btn-primary font-semibold py-3 px-4 rounded-lg text-center transition-all duration-300">
                    Masuk ke Akun Saya
                </a>
                <a href="<?= epic_url('dashboard') ?>" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg text-center transition-all duration-300">
                    Langsung ke Dashboard
                </a>
            <?php else: ?>
                <a href="<?= epic_url('register') ?>" class="block w-full btn-primary font-semibold py-3 px-4 rounded-lg text-center transition-all duration-300">
                    Daftar Ulang
                </a>
                <a href="<?= epic_url('email-confirmation') ?>" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg text-center transition-all duration-300">
                    Kirim Ulang Email
                </a>
            <?php endif; ?>
        </div>

        <!-- Additional Info -->
        <div class="mt-6 pt-6 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-600 mb-2">
                Butuh bantuan? 
                <a href="<?= epic_url('contact') ?>" class="text-blue-600 hover:text-blue-800 transition-colors">
                    Hubungi Support
                </a>
            </p>
            <p class="text-xs text-gray-500">
                © <?= date('Y') ?> <?= htmlspecialchars(epic_setting('site_name')) ?>. Semua hak dilindungi.
            </p>
        </div>
    </div>

    <script>
        // Auto redirect to login after 10 seconds if successful
        <?php if ($confirmation_result['success']): ?>
        let countdown = 10;
        const countdownElement = document.createElement('div');
        countdownElement.className = 'text-center mt-4 text-sm text-gray-600';
        countdownElement.innerHTML = `Otomatis mengarahkan ke halaman login dalam <span id="countdown">${countdown}</span> detik...`;
        document.querySelector('.glass-effect').appendChild(countdownElement);

        const timer = setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '<?= epic_url("login?confirmed=1") ?>';
            }
        }, 1000);

        // Stop countdown if user interacts with page
        document.addEventListener('click', () => {
            clearInterval(timer);
            countdownElement.remove();
        });
        <?php endif; ?>
    </script>
</body>
</html>