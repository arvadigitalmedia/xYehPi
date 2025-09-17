<?php
/**
 * EPIC Hub Modern Theme - Home Page
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
    <title><?= htmlspecialchars($page_title ?? 'EPIC Hub') ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description ?? 'Modern Affiliate Marketing Platform') ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
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
                        <span class="text-xl font-bold text-gray-900">EPIC Hub - Bisnis Emas Perak Indonesia</span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="<?= epic_url() ?>" class="text-gray-700 hover:text-blue-600 transition-colors">Home</a>
                    <a href="<?= epic_url('products') ?>" class="text-gray-700 hover:text-blue-600 transition-colors">Products</a>
                    <a href="<?= epic_url('articles') ?>" class="text-gray-700 hover:text-blue-600 transition-colors">Articles</a>
                    
                    <?php if (epic_is_logged_in()): ?>
                        <a href="<?= epic_url('dashboard') ?>" class="text-gray-700 hover:text-blue-600 transition-colors">DASHBOARD</a>
                        <a href="<?= epic_url('logout') ?>" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">Logout</a>
                    <?php else: ?>
                        <a href="<?= epic_url('login') ?>" class="text-gray-700 hover:text-blue-600 transition-colors">LOGIN</a>
                        <a href="<?= epic_url('register') ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">Register</a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button x-data="{ open: false }" @click="open = !open" class="text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-700 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Modern Affiliate Marketing Platform
            </h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90">
                Bergabunglah dengan platform affiliate marketing terdepan dan mulai earning dari referral Anda
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?= epic_url('register') ?>" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Mulai Sekarang
                </a>
                <a href="<?= epic_url('products') ?>" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    Lihat Produk
                </a>
            </div>
        </div>
    </section>
    
    <!-- Stats Section -->
    <?php if (isset($stats)): ?>
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl font-bold text-blue-600"><?= number_format($stats['total_users']) ?></div>
                    <div class="text-gray-600 mt-2">Total Users</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-green-600"><?= number_format($stats['total_products']) ?></div>
                    <div class="text-gray-600 mt-2">Products</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-purple-600"><?= number_format($stats['total_orders']) ?></div>
                    <div class="text-gray-600 mt-2">Orders</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-orange-600"><?= epic_format_currency($stats['total_commissions']) ?></div>
                    <div class="text-gray-600 mt-2">Commissions</div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Features Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Mengapa Memilih EPIC Hub?
                </h2>
                <p class="text-xl text-gray-600">
                    Platform affiliate marketing yang dirancang untuk kesuksesan Anda
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Smart Analytics</h3>
                    <p class="text-gray-600">
                        Pantau performa referral Anda dengan analytics real-time dan insights mendalam
                    </p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-link text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Smart Link Tracking</h3>
                    <p class="text-gray-600">
                        Sistem tracking link yang canggih untuk memaksimalkan konversi dan komisi
                    </p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-money-bill-wave text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Instant Commissions</h3>
                    <p class="text-gray-600">
                        Dapatkan komisi secara real-time dengan sistem pembayaran yang transparan
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Products Section -->
    <?php if (isset($products) && !empty($products)): ?>
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Produk Terpopuler
                </h2>
                <p class="text-xl text-gray-600">
                    Pilih produk terbaik untuk dipromosikan dan dapatkan komisi menarik
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach (array_slice($products, 0, 6) as $product): ?>
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
                    <?php if ($product['image']): ?>
                    <img src="<?= epic_url('uploads/' . $product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-48 object-cover">
                    <?php else: ?>
                    <div class="w-full h-48 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-box text-white text-4xl"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars($product['short_description'] ?? '') ?></p>
                        
                        <div class="flex justify-between items-center">
                            <div class="text-2xl font-bold text-blue-600">
                                <?= epic_format_currency($product['price']) ?>
                            </div>
                            <a href="<?= epic_url('order/' . $product['slug']) ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                                Order Now
                            </a>
                        </div>
                        
                        <div class="mt-4 text-sm text-gray-500">
                            Komisi: <?= $product['commission_value'] ?><?= $product['commission_type'] === 'percentage' ? '%' : ' IDR' ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-12">
                <a href="<?= epic_url('products') ?>" class="bg-blue-500 text-white px-8 py-3 rounded-lg hover:bg-blue-600 transition-colors">
                    Lihat Semua Produk
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-purple-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                Siap Memulai Journey Affiliate Marketing Anda?
            </h2>
            <p class="text-xl mb-8 opacity-90">
                Bergabunglah dengan ribuan affiliate yang sudah merasakan kesuksesan bersama EPIC Hub
            </p>
            <a href="<?= epic_url('register') ?>" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors inline-block">
                Daftar Gratis Sekarang
            </a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-rocket text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold">EPIC Hub</span>
                    </div>
                    <p class="text-gray-400">
                        Platform affiliate marketing modern untuk kesuksesan bisnis online Anda.
                    </p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="<?= epic_url() ?>" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="<?= epic_url('products') ?>" class="hover:text-white transition-colors">Products</a></li>
                        <li><a href="<?= epic_url('articles') ?>" class="hover:text-white transition-colors">Articles</a></li>
                        <li><a href="<?= epic_url('register') ?>" class="hover:text-white transition-colors">Register</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Connect</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> EPIC Hub. All rights reserved. Powered by Arva Team.</p>
            </div>
        </div>
    </footer>
</body>
</html>