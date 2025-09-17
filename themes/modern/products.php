<?php
/**
 * EPIC Hub - Products Template
 * Uses data from routing, no additional queries
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Extract data from routing
$page_title = $page_title ?? 'Products - EPIC Hub';
$products = $products ?? [];
$pagination = $pagination ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    
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

    <!-- Header -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-700 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Produk Digital</h1>
            <p class="text-xl opacity-90">Temukan produk terbaik untuk dipromosikan dan dapatkan komisi menarik</p>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (!empty($products)): ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="w-full h-48 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?= epic_url('uploads/products/' . $product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-box text-white text-4xl"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="text-gray-600 mb-4"><?= htmlspecialchars(substr($product['short_description'] ?? $product['description'] ?? '', 0, 100)) ?></p>
                                
                                <div class="flex justify-between items-center">
                                    <div class="text-2xl font-bold text-blue-600">
                                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                    </div>
                                    <a href="<?= epic_url('order/' . $product['slug']) ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                                        Order Now
                                    </a>
                                </div>
                                
                                <div class="mt-4 text-sm text-gray-500">
                                    Komisi: <?= number_format($product['commission_value'], 2) ?>%
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <div class="mt-12 flex justify-center">
                        <nav class="flex space-x-2">
                            <?php if ($pagination['has_prev']): ?>
                                <a href="<?= epic_url('products?page=' . ($pagination['current_page'] - 1)) ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <a href="<?= epic_url('products?page=' . $i) ?>" class="px-4 py-2 <?= $i === $pagination['current_page'] ? 'bg-blue-500 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50' ?> rounded-lg"><?= $i ?></a>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                                <a href="<?= epic_url('products?page=' . ($pagination['current_page'] + 1)) ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Next</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-16">
                    <i class="fas fa-box-open text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-600 mb-2">Belum Ada Produk</h3>
                    <p class="text-gray-500">Produk akan segera tersedia. Silakan kembali lagi nanti.</p>
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