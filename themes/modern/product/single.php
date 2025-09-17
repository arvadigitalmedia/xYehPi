<?php
/**
 * EPIC Hub - Single Product Template
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
    <title><?= htmlspecialchars($page_title ?? 'Product - EPIC Hub') ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description ?? '') ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?= epic_url() ?>" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-rocket text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">EPIC Hub</span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="<?= epic_url() ?>" class="text-gray-700 hover:text-blue-600 transition-colors">Home</a>
                    <a href="<?= epic_url('products') ?>" class="text-blue-600 font-semibold">Products</a>
                    <a href="<?= epic_url('login') ?>" class="text-gray-700 hover:text-blue-600 transition-colors">Login</a>
                    <a href="<?= epic_url('register') ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Product Detail -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (isset($product)): ?>
                <div class="grid md:grid-cols-2 gap-12">
                    <!-- Product Image -->
                    <div>
                        <div class="w-full h-96 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= epic_url('uploads/products/' . $product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover rounded-xl">
                            <?php else: ?>
                                <i class="fas fa-box text-white text-6xl"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($product['name']) ?></h1>
                        <p class="text-gray-600 mb-6"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                        
                        <div class="bg-gray-50 p-6 rounded-xl mb-6">
                            <div class="text-3xl font-bold text-blue-600 mb-2">
                                Rp <?= number_format($product['price'], 0, ',', '.') ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                Komisi: <?= number_format($product['commission_value'], 2) ?>%
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <a href="<?= epic_url('order/' . $product['slug']) ?>" class="block w-full bg-blue-500 text-white text-center px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors font-semibold">
                                Order Sekarang
                            </a>
                            <a href="<?= epic_url('register?product=' . $product['slug']) ?>" class="block w-full border-2 border-blue-500 text-blue-500 text-center px-6 py-3 rounded-lg hover:bg-blue-50 transition-colors font-semibold">
                                Daftar & Promosikan
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-16">
                    <i class="fas fa-exclamation-triangle text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-600 mb-2">Produk Tidak Ditemukan</h3>
                    <p class="text-gray-500 mb-6">Produk yang Anda cari tidak tersedia.</p>
                    <a href="<?= epic_url('products') ?>" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors">Kembali ke Produk</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2025 EPIC Hub. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>