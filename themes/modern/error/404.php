<?php
/**
 * EPIC Hub Modern Theme - 404 Error Page
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? '404 - Page Not Found') ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto text-center px-4">
        <div class="mb-8">
            <div class="w-32 h-32 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-exclamation-triangle text-white text-4xl"></i>
            </div>
            
            <h1 class="text-6xl font-bold text-gray-900 mb-4"><?= $error_code ?? '404' ?></h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Halaman Tidak Ditemukan</h2>
            <p class="text-gray-600 mb-8">
                <?= htmlspecialchars($error_message ?? 'Halaman yang Anda cari tidak dapat ditemukan.') ?>
            </p>
        </div>
        
        <div class="space-y-4">
            <a href="<?= epic_url() ?>" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors inline-block">
                <i class="fas fa-home mr-2"></i>
                Kembali ke Beranda
            </a>
            
            <div>
                <a href="javascript:history.back()" class="text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Halaman Sebelumnya
                </a>
            </div>
        </div>
        
        <div class="mt-12 text-sm text-gray-500">
            <p>Jika Anda yakin ini adalah kesalahan, silakan hubungi administrator.</p>
        </div>
    </div>
</body>
</html>