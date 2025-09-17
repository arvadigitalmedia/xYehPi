<?php
/**
 * EPIC Hub - Landing Page Template 1
 * Professional Sales Letter with Sponsor Integration
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Get sponsor data from URL or session
$sponsor_id = $data['sponsor_id'] ?? null;
$sponsor = $data['sponsor'] ?? null;
$product = $data['product'] ?? null;
$landing_config = $data['landing_config'] ?? [];

// Default values if sponsor not found
if (!$sponsor) {
    $sponsor = [
        'name' => 'EPIC Hub Team',
        'phone' => '+6281234567890',
        'avatar' => epic_url('themes/modern/assets/default-avatar.png'),
        'title' => 'Digital Marketing Specialist',
        'experience' => '5+ Years Experience'
    ];
}

// Default product info
if (!$product) {
    $product = [
        'name' => 'Digital Marketing Mastery',
        'tagline' => 'Transform Your Business with Proven Digital Strategies',
        'price' => 'Rp 2.997.000',
        'discount_price' => 'Rp 997.000',
        'discount_percentage' => '67%',
        'features' => [
            'Complete Digital Marketing Course',
            'Live Mentoring Sessions',
            'Private Community Access',
            'Marketing Tools & Templates',
            'Lifetime Updates',
            '30-Day Money Back Guarantee'
        ],
        'testimonials' => [
            [
                'name' => 'Budi Santoso',
                'role' => 'Online Business Owner',
                'content' => 'Dalam 3 bulan setelah mengikuti program ini, omzet bisnis online saya meningkat 300%. Materinya sangat praktis dan mudah diterapkan.',
                'rating' => 5
            ],
            [
                'name' => 'Sari Dewi',
                'role' => 'Digital Marketer',
                'content' => 'Program terbaik yang pernah saya ikuti! Mentor sangat berpengalaman dan selalu siap membantu. Highly recommended!',
                'rating' => 5
            ],
            [
                'name' => 'Ahmad Rahman',
                'role' => 'E-commerce Entrepreneur',
                'content' => 'ROI yang luar biasa! Investment terbaik untuk bisnis digital. Sekarang saya bisa generate leads berkualitas setiap hari.',
                'rating' => 5
            ]
        ]
    ];
}

// WhatsApp link
$whatsapp_number = preg_replace('/[^0-9]/', '', $sponsor['phone']);
if (substr($whatsapp_number, 0, 1) === '0') {
    $whatsapp_number = '62' . substr($whatsapp_number, 1);
}
$whatsapp_link = "https://wa.me/{$whatsapp_number}?text=" . urlencode("Halo {$sponsor['name']}, saya tertarik dengan program {$product['name']}. Bisa tolong berikan informasi lebih lanjut?");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - <?= htmlspecialchars($sponsor['name']) ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($product['tagline']) ?> - Dipersembahkan oleh <?= htmlspecialchars($sponsor['name']) ?>">
    <meta name="keywords" content="digital marketing, online business, affiliate marketing, <?= htmlspecialchars($sponsor['name']) ?>">
    <meta name="author" content="<?= htmlspecialchars($sponsor['name']) ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($product['name']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($product['tagline']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($sponsor['avatar']) ?>">
    <meta property="og:type" content="website">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .font-display {
            font-family: 'Playfair Display', serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .fade-in {
            animation: fadeIn 1s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .countdown-timer {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        }
        
        .testimonial-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .cta-button {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4);
        }
        
        .whatsapp-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .section-divider {
            background: linear-gradient(90deg, transparent, #667eea, transparent);
            height: 2px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header Section -->
    <header class="gradient-bg text-white py-4 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <img src="<?= htmlspecialchars($sponsor['avatar']) ?>" 
                         alt="<?= htmlspecialchars($sponsor['name']) ?>" 
                         class="w-12 h-12 rounded-full border-2 border-white shadow-lg">
                    <div>
                        <h3 class="font-semibold text-lg"><?= htmlspecialchars($sponsor['name']) ?></h3>
                        <p class="text-sm opacity-90"><?= htmlspecialchars($sponsor['title'] ?? 'Digital Marketing Specialist') ?></p>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold">5000+</div>
                        <div class="text-xs opacity-90">Happy Clients</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold">98%</div>
                        <div class="text-xs opacity-90">Success Rate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold"><?= htmlspecialchars($sponsor['experience'] ?? '5+') ?></div>
                        <div class="text-xs opacity-90">Years Experience</div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="gradient-bg text-white py-20 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-20 h-20 bg-white rounded-full floating"></div>
            <div class="absolute top-32 right-20 w-16 h-16 bg-white rounded-full floating" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-white rounded-full floating" style="animation-delay: 2s;"></div>
        </div>
        
        <div class="container mx-auto px-4 text-center relative z-10">
            <div class="max-w-4xl mx-auto fade-in">
                <h1 class="font-display text-4xl md:text-6xl font-bold mb-6 leading-tight">
                    <?= htmlspecialchars($product['name']) ?>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90 leading-relaxed">
                    <?= htmlspecialchars($product['tagline']) ?>
                </p>
                
                <!-- Price Section -->
                <div class="bg-white bg-opacity-20 rounded-2xl p-8 mb-8 glass-effect">
                    <div class="flex items-center justify-center space-x-4 mb-4">
                        <span class="text-3xl md:text-4xl font-bold text-red-300 line-through">
                            <?= htmlspecialchars($product['price']) ?>
                        </span>
                        <span class="text-4xl md:text-6xl font-bold text-yellow-300">
                            <?= htmlspecialchars($product['discount_price']) ?>
                        </span>
                    </div>
                    <div class="inline-block bg-red-500 text-white px-6 py-2 rounded-full font-bold text-lg">
                        HEMAT <?= htmlspecialchars($product['discount_percentage']) ?>!
                    </div>
                </div>
                
                <!-- Countdown Timer -->
                <div class="countdown-timer rounded-2xl p-6 mb-8 text-center">
                    <h3 class="text-xl font-bold mb-4">⏰ PENAWARAN TERBATAS BERAKHIR DALAM:</h3>
                    <div id="countdown" class="flex justify-center space-x-4 text-2xl md:text-3xl font-bold">
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <div id="days">00</div>
                            <div class="text-sm">HARI</div>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <div id="hours">00</div>
                            <div class="text-sm">JAM</div>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <div id="minutes">00</div>
                            <div class="text-sm">MENIT</div>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <div id="seconds">00</div>
                            <div class="text-sm">DETIK</div>
                        </div>
                    </div>
                </div>
                
                <!-- CTA Button -->
                <a href="<?= $whatsapp_link ?>" 
                   class="inline-block cta-button text-white font-bold text-xl px-12 py-4 rounded-full pulse-animation">
                    <i class="fab fa-whatsapp mr-3"></i>
                    DAPATKAN SEKARANG VIA WHATSAPP
                </a>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="font-display text-4xl font-bold gradient-text mb-4">
                    Apa Yang Akan Anda Dapatkan?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Program lengkap yang telah terbukti membantu ribuan orang mencapai kesuksesan dalam bisnis digital
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($product['features'] as $index => $feature): ?>
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-8 text-center hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-check text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-4 text-gray-800"><?= htmlspecialchars($feature) ?></h3>
                        <p class="text-gray-600">Benefit detail untuk <?= htmlspecialchars($feature) ?> yang akan membantu kesuksesan Anda.</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Section Divider -->
    <div class="section-divider"></div>
    
    <!-- Testimonials Section -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="font-display text-4xl font-bold gradient-text mb-4">
                    Apa Kata Mereka Yang Sudah Berhasil?
                </h2>
                <p class="text-xl text-gray-600">
                    Testimoni nyata dari para member yang telah merasakan manfaatnya
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($product['testimonials'] as $testimonial): ?>
                    <div class="testimonial-card rounded-2xl p-8 relative">
                        <div class="flex items-center mb-6">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-gray-700 mb-6 italic leading-relaxed">
                            "<?= htmlspecialchars($testimonial['content']) ?>"
                        </p>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-4">
                                <span class="text-white font-bold"><?= strtoupper(substr($testimonial['name'], 0, 1)) ?></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800"><?= htmlspecialchars($testimonial['name']) ?></h4>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($testimonial['role']) ?></p>
                            </div>
                        </div>
                        <div class="absolute top-4 right-4 text-6xl text-blue-100 opacity-50">
                            <i class="fas fa-quote-right"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- About Sponsor Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="font-display text-4xl font-bold gradient-text mb-8">
                    Tentang Mentor Anda
                </h2>
                
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-3xl p-12">
                    <img src="<?= htmlspecialchars($sponsor['avatar']) ?>" 
                         alt="<?= htmlspecialchars($sponsor['name']) ?>" 
                         class="w-32 h-32 rounded-full mx-auto mb-8 border-4 border-white shadow-xl">
                    
                    <h3 class="text-3xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($sponsor['name']) ?></h3>
                    <p class="text-xl text-gray-600 mb-8"><?= htmlspecialchars($sponsor['title'] ?? 'Digital Marketing Specialist') ?></p>
                    
                    <div class="grid md:grid-cols-3 gap-8 mb-8">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 mb-2">5000+</div>
                            <div class="text-gray-600">Students Trained</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600 mb-2">98%</div>
                            <div class="text-gray-600">Success Rate</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600 mb-2"><?= htmlspecialchars($sponsor['experience'] ?? '5+') ?></div>
                            <div class="text-gray-600">Years Experience</div>
                        </div>
                    </div>
                    
                    <p class="text-lg text-gray-700 leading-relaxed mb-8">
                        Dengan pengalaman lebih dari <?= htmlspecialchars($sponsor['experience'] ?? '5 tahun') ?> di bidang digital marketing, 
                        <?= htmlspecialchars($sponsor['name']) ?> telah membantu ribuan orang mencapai kesuksesan dalam bisnis online. 
                        Keahlian dan dedikasi beliau dalam membimbing setiap student menjadikan program ini sangat efektif dan terpercaya.
                    </p>
                    
                    <a href="<?= $whatsapp_link ?>" 
                       class="inline-block cta-button text-white font-bold text-lg px-8 py-4 rounded-full">
                        <i class="fab fa-whatsapp mr-3"></i>
                        KONSULTASI GRATIS SEKARANG
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Final CTA Section -->
    <section class="gradient-bg text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-4xl mx-auto">
                <h2 class="font-display text-4xl md:text-5xl font-bold mb-8">
                    Jangan Sia-Siakan Kesempatan Emas Ini!
                </h2>
                <p class="text-xl mb-8 opacity-90 leading-relaxed">
                    Ribuan orang sudah merasakan manfaatnya. Sekarang giliran Anda untuk bergabung dan 
                    meraih kesuksesan yang sama. Penawaran terbatas ini tidak akan berlangsung selamanya!
                </p>
                
                <div class="bg-white bg-opacity-20 rounded-2xl p-8 mb-8 glass-effect">
                    <h3 class="text-2xl font-bold mb-4">🎁 BONUS EKSKLUSIF HARI INI:</h3>
                    <ul class="text-lg space-y-2 mb-6">
                        <li>✅ Free 1-on-1 Consultation (Worth Rp 500.000)</li>
                        <li>✅ Exclusive Marketing Templates (Worth Rp 300.000)</li>
                        <li>✅ Lifetime Community Access (Worth Rp 200.000)</li>
                    </ul>
                    <p class="text-xl font-bold text-yellow-300">Total Bonus: Rp 1.000.000 - GRATIS!</p>
                </div>
                
                <a href="<?= $whatsapp_link ?>" 
                   class="inline-block cta-button text-white font-bold text-2xl px-16 py-6 rounded-full pulse-animation mb-8">
                    <i class="fab fa-whatsapp mr-4"></i>
                    AMBIL PENAWARAN SEKARANG!
                </a>
                
                <p class="text-sm opacity-75">
                    💬 Klik tombol di atas untuk langsung chat dengan <?= htmlspecialchars($sponsor['name']) ?> via WhatsApp
                </p>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-4 mb-6">
                <img src="<?= htmlspecialchars($sponsor['avatar']) ?>" 
                     alt="<?= htmlspecialchars($sponsor['name']) ?>" 
                     class="w-16 h-16 rounded-full border-2 border-gray-600">
                <div class="text-left">
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($sponsor['name']) ?></h3>
                    <p class="text-gray-400"><?= htmlspecialchars($sponsor['title'] ?? 'Digital Marketing Specialist') ?></p>
                </div>
            </div>
            
            <div class="border-t border-gray-700 pt-8">
                <p class="text-gray-400 mb-4">
                    © <?= date('Y') ?> <?= htmlspecialchars($sponsor['name']) ?>. All rights reserved.
                </p>
                <p class="text-sm text-gray-500">
                    Powered by EPIC Hub - Professional Affiliate Marketing Platform
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Floating WhatsApp Button -->
    <a href="<?= $whatsapp_link ?>" 
       class="whatsapp-float bg-green-500 hover:bg-green-600 text-white p-4 rounded-full shadow-2xl transition-all duration-300">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>
    
    <!-- Scripts -->
    <script>
        // Countdown Timer
        function startCountdown() {
            // Set countdown to 24 hours from now
            const countdownDate = new Date().getTime() + (24 * 60 * 60 * 1000);
            
            const timer = setInterval(function() {
                const now = new Date().getTime();
                const distance = countdownDate - now;
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                document.getElementById('days').innerHTML = days.toString().padStart(2, '0');
                document.getElementById('hours').innerHTML = hours.toString().padStart(2, '0');
                document.getElementById('minutes').innerHTML = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').innerHTML = seconds.toString().padStart(2, '0');
                
                if (distance < 0) {
                    clearInterval(timer);
                    document.getElementById('countdown').innerHTML = '<div class="text-2xl font-bold text-red-300">PENAWARAN BERAKHIR!</div>';
                }
            }, 1000);
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Initialize countdown on page load
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
        });
        
        // Track CTA clicks
        document.querySelectorAll('.cta-button').forEach(button => {
            button.addEventListener('click', function() {
                // Analytics tracking can be added here
                console.log('CTA clicked:', this.textContent.trim());
            });
        });
    </script>
</body>
</html>