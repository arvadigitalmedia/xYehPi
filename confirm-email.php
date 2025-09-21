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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Email - EPIC Hub</title>
    <link rel="icon" type="image/x-icon" href="/themes/modern/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full">
        <div class="text-center mb-6">
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <?php if ($success): ?>
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                <?php else: ?>
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                <?php endif; ?>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                <?php echo $success ? 'Email Berhasil Dikonfirmasi!' : 'Konfirmasi Email Gagal'; ?>
            </h1>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Konfirmasi Berhasil</h3>
                        <div class="mt-2 text-sm text-green-700">
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
                <a href="/index.php" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 text-center block">
                    Login Sekarang
                </a>
                <a href="/dashboard" 
                   class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-lg transition duration-200 text-center block">
                    Ke Dashboard
                </a>
            </div>
            
        <?php else: ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Konfirmasi Gagal</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><?php echo htmlspecialchars($error_message); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="space-y-3">
                <a href="/register" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 text-center block">
                    Daftar Ulang
                </a>
                <a href="/index.php" 
                   class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-lg transition duration-200 text-center block">
                    Ke Halaman Login
                </a>
            </div>
        <?php endif; ?>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                Butuh bantuan? 
                <a href="mailto:support@epichub.com" class="text-blue-600 hover:text-blue-500">Hubungi Support</a>
            </p>
        </div>
    </div>
</body>
</html>