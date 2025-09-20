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

        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
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

        .input-focus:focus {
            border-color: var(--color-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
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
            background: var(--color-gold-light);
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

        .success-icon {
            animation: successPulse 2s ease-in-out infinite;
        }

        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .email-animation {
            animation: emailBounce 3s ease-in-out infinite;
        }

        @keyframes emailBounce {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
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
        <!-- Success Icon -->
        <div class="text-center mb-8">
            <div class="success-icon inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Registrasi Berhasil!</h1>
            <p class="text-gray-600">Silakan periksa email Anda untuk konfirmasi</p>
        </div>

        <!-- Email Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center mb-3">
                <div class="email-animation mr-3">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Email Konfirmasi Dikirim</h3>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($confirmation_data['email']) ?></p>
                </div>
            </div>
            
            <?php if ($confirmation_data['email_sent']): ?>
                <div class="flex items-center text-green-600 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Email berhasil dikirim
                </div>
            <?php else: ?>
                <div class="flex items-center text-red-600 text-sm">
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
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <div class="flex items-center text-green-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <?= htmlspecialchars($resend_message) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($resend_error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <div class="flex items-center text-red-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <?= htmlspecialchars($resend_error) ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="mb-6">
            <button type="submit" name="resend_email" class="w-full btn-primary font-semibold py-3 px-4 rounded-lg transition-all duration-300">
                Kirim Ulang Email Konfirmasi
            </button>
        </form>

        <!-- Additional Actions -->
        <div class="text-center space-y-3">
            <p class="text-sm text-gray-600">
                Tidak menerima email? Periksa folder spam/junk Anda
            </p>
            
            <div class="flex justify-center space-x-4 text-sm">
                <a href="<?= epic_url('login') ?>" class="text-blue-600 hover:text-blue-800 transition-colors">
                    Sudah Konfirmasi? Login
                </a>
                <span class="text-gray-400">|</span>
                <a href="<?= epic_url('register') ?>" class="text-blue-600 hover:text-blue-800 transition-colors">
                    Daftar Lagi
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">
                © <?= date('Y') ?> <?= htmlspecialchars(epic_setting('site_name')) ?>. Semua hak dilindungi.
            </p>
        </div>
    </div>

    <script>
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

        // Add some interactive feedback
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = e.target.querySelector('button[type="submit"]');
            button.innerHTML = 'Mengirim...';
            button.disabled = true;
        });
    </script>
</body>
</html>