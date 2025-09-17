<?php
/**
 * EPIC Hub - Landing Page Template 2
 * Modern Product Showcase with Interactive Elements
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
        'title' => 'Digital Marketing Expert',
        'experience' => '7+ Years Experience'
    ];
}

// Default product info
if (!$product) {
    $product = [
        'name' => 'Complete Digital Business Blueprint',
        'tagline' => 'Build Your Million Dollar Online Empire from Scratch',
        'price' => 'Rp 4.997.000',
        'discount_price' => 'Rp 1.497.000',
        'discount_percentage' => '70%',
        'features' => [
            '12-Week Intensive Training Program',
            'Personal Business Mentor Assignment',
            'Done-For-You Marketing Funnels',
            'Exclusive Mastermind Community',
            'Live Weekly Q&A Sessions',
            'Lifetime Access & Updates',
            'Money-Back Success Guarantee'
        ],
        'modules' => [
            [
                'title' => 'Foundation & Mindset',
                'description' => 'Build the entrepreneurial mindset and foundation for success',
                'lessons' => 8
            ],
            [
                'title' => 'Market Research & Validation',
                'description' => 'Find profitable niches and validate your business ideas',
                'lessons' => 12
            ],
            [
                'title' => 'Product Creation & Launch',
                'description' => 'Create and launch your first digital product',
                'lessons' => 15
            ],
            [
                'title' => 'Traffic & Lead Generation',
                'description' => 'Master organic and paid traffic strategies',
                'lessons' => 18
            ],
            [
                'title' => 'Sales & Conversion',
                'description' => 'Convert leads into paying customers consistently',
                'lessons' => 14
            ],
            [
                'title' => 'Scaling & Automation',
                'description' => 'Scale your business to 6-7 figures with automation',
                'lessons' => 10
            ]
        ],
        'testimonials' => [
            [
                'name' => 'Maria Sari',
                'role' => 'Online Course Creator',
                'content' => 'Dari nol sampai 100 juta dalam 6 bulan! Blueprint ini benar-benar mengubah hidup saya. Sekarang saya punya passive income yang stabil.',
                'rating' => 5,
                'revenue' => 'Rp 100.000.000'
            ],
            [
                'name' => 'Andi Pratama',
                'role' => 'Digital Agency Owner',
                'content' => 'Strategi yang diajarkan sangat praktis dan terbukti. Agency saya sekarang handle 50+ klien dengan sistem yang otomatis.',
                'rating' => 5,
                'revenue' => 'Rp 250.000.000'
            ],
            [
                'name' => 'Lisa Wijaya',
                'role' => 'E-commerce Entrepreneur',
                'content' => 'Incredible results! Toko online saya sekarang generate 500+ orders per hari. ROI dari program ini luar biasa.',
                'rating' => 5,
                'revenue' => 'Rp 500.000.000'
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
    <meta name="keywords" content="digital business, online empire, passive income, <?= htmlspecialchars($sponsor['name']) ?>">
    <meta name="author" content="<?= htmlspecialchars($sponsor['name']) ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($product['name']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($product['tagline']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($sponsor['avatar']) ?>">
    <meta property="og:type" content="website">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .font-display {
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .hero-bg {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .neon-glow {
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3), 0 0 40px rgba(102, 126, 234, 0.1);
        }
        
        .text-gradient {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: var(--success-gradient);
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.4);
        }
        
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .floating-element {
            position: absolute;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-element:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }
        
        .floating-element:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .module-card {
            background: linear-gradient(145deg, #1e1e2e 0%, #2a2a3e 100%);
            border: 1px solid rgba(102, 126, 234, 0.2);
            transition: all 0.3s ease;
        }
        
        .module-card:hover {
            border-color: rgba(102, 126, 234, 0.5);
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        
        .testimonial-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .revenue-badge {
            background: var(--success-gradient);
            color: white;
            font-weight: 700;
        }
        
        .countdown-box {
            background: var(--secondary-gradient);
            color: white;
        }
        
        .stats-counter {
            font-size: 3rem;
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .whatsapp-pulse {
            animation: pulse-green 2s infinite;
        }
        
        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(37, 211, 102, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
        }
        
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
            40% { transform: translateX(-50%) translateY(-10px); }
            60% { transform: translateX(-50%) translateY(-5px); }
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <!-- Hero Section -->
    <section class="hero-bg min-h-screen relative overflow-hidden flex items-center">
        <!-- Floating Elements -->
        <div class="floating-elements">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>
        
        <!-- Navigation -->
        <nav class="absolute top-0 w-full z-50 py-6">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <img src="<?= htmlspecialchars($sponsor['avatar']) ?>" 
                             alt="<?= htmlspecialchars($sponsor['name']) ?>" 
                             class="w-12 h-12 rounded-full border-2 border-blue-400 neon-glow">
                        <div>
                            <h3 class="font-bold text-lg"><?= htmlspecialchars($sponsor['name']) ?></h3>
                            <p class="text-sm text-blue-300"><?= htmlspecialchars($sponsor['title'] ?? 'Digital Marketing Expert') ?></p>
                        </div>
                    </div>
                    
                    <div class="hidden md:flex items-center space-x-8">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gradient">10,000+</div>
                            <div class="text-xs text-gray-400">Students</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gradient">₹50Cr+</div>
                            <div class="text-xs text-gray-400">Revenue Generated</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gradient"><?= htmlspecialchars($sponsor['experience'] ?? '7+') ?></div>
                            <div class="text-xs text-gray-400">Years Experience</div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Hero Content -->
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-6xl mx-auto text-center">
                <div class="mb-8" data-aos="fade-up">
                    <span class="inline-block bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-2 rounded-full text-sm font-semibold mb-6">
                        🔥 LIMITED TIME OFFER - 70% OFF
                    </span>
                </div>
                
                <h1 class="font-display text-5xl md:text-7xl font-bold mb-8 leading-tight" data-aos="fade-up" data-aos-delay="100">
                    <span class="text-gradient"><?= htmlspecialchars($product['name']) ?></span>
                </h1>
                
                <p class="text-xl md:text-2xl text-gray-300 mb-12 max-w-4xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="200">
                    <?= htmlspecialchars($product['tagline']) ?>
                </p>
                
                <!-- Price Display -->
                <div class="glass-card rounded-3xl p-8 mb-12 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center justify-center space-x-6 mb-6">
                        <span class="text-2xl md:text-3xl text-gray-400 line-through">
                            <?= htmlspecialchars($product['price']) ?>
                        </span>
                        <span class="text-4xl md:text-6xl font-bold text-gradient">
                            <?= htmlspecialchars($product['discount_price']) ?>
                        </span>
                    </div>
                    
                    <div class="countdown-box rounded-2xl p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">⏰ OFFER EXPIRES IN:</h3>
                        <div id="countdown" class="flex justify-center space-x-4 text-2xl md:text-3xl font-bold">
                            <div class="text-center">
                                <div id="days" class="bg-white bg-opacity-20 rounded-lg p-3 min-w-[60px]">00</div>
                                <div class="text-sm mt-1">DAYS</div>
                            </div>
                            <div class="text-center">
                                <div id="hours" class="bg-white bg-opacity-20 rounded-lg p-3 min-w-[60px]">00</div>
                                <div class="text-sm mt-1">HOURS</div>
                            </div>
                            <div class="text-center">
                                <div id="minutes" class="bg-white bg-opacity-20 rounded-lg p-3 min-w-[60px]">00</div>
                                <div class="text-sm mt-1">MINS</div>
                            </div>
                            <div class="text-center">
                                <div id="seconds" class="bg-white bg-opacity-20 rounded-lg p-3 min-w-[60px]">00</div>
                                <div class="text-sm mt-1">SECS</div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="<?= $whatsapp_link ?>" 
                       class="inline-block btn-success text-white font-bold text-xl px-12 py-4 rounded-full whatsapp-pulse">
                        <i class="fab fa-whatsapp mr-3"></i>
                        GET INSTANT ACCESS NOW
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
            <div class="w-6 h-10 border-2 border-white rounded-full flex justify-center">
                <div class="w-1 h-3 bg-white rounded-full mt-2 animate-pulse"></div>
            </div>
        </div>
    </section>
    
    <!-- What You'll Learn Section -->
    <section class="py-20 bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="font-display text-4xl md:text-5xl font-bold text-gradient mb-6">
                    Complete Training Modules
                </h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    6 comprehensive modules designed to take you from beginner to 7-figure entrepreneur
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($product['modules'] as $index => $module): ?>
                    <div class="module-card rounded-2xl p-8" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                <?= $index + 1 ?>
                            </div>
                            <span class="bg-blue-500 bg-opacity-20 text-blue-300 px-3 py-1 rounded-full text-sm font-semibold">
                                <?= $module['lessons'] ?> Lessons
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-bold text-white mb-4"><?= htmlspecialchars($module['title']) ?></h3>
                        <p class="text-gray-400 leading-relaxed"><?= htmlspecialchars($module['description']) ?></p>
                        
                        <div class="mt-6 pt-6 border-t border-gray-700">
                            <div class="flex items-center text-sm text-gray-400">
                                <i class="fas fa-play-circle mr-2 text-blue-400"></i>
                                <span>Video Lessons + Worksheets</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Success Stories Section -->
    <section class="py-20 bg-gray-800">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="font-display text-4xl md:text-5xl font-bold text-gradient mb-6">
                    Real Success Stories
                </h2>
                <p class="text-xl text-gray-300">
                    See how our students are building million-dollar businesses
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($product['testimonials'] as $index => $testimonial): ?>
                    <div class="testimonial-card rounded-2xl p-8 text-gray-800" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="revenue-badge px-3 py-1 rounded-full text-sm font-bold">
                                <?= htmlspecialchars($testimonial['revenue']) ?>
                            </span>
                        </div>
                        
                        <p class="text-gray-700 mb-6 italic leading-relaxed font-medium">
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
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="py-20 bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="font-display text-4xl md:text-5xl font-bold text-gradient mb-6">
                    Everything You Need to Succeed
                </h2>
                <p class="text-xl text-gray-300">
                    Complete business-in-a-box solution with lifetime support
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($product['features'] as $index => $feature): ?>
                    <div class="glass-card rounded-2xl p-8 text-center hover:neon-glow transition-all duration-300" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-check text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-4"><?= htmlspecialchars($feature) ?></h3>
                        <p class="text-gray-400">Premium feature designed to accelerate your success and maximize results.</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- About Mentor Section -->
    <section class="py-20 bg-gray-800">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div data-aos="fade-right">
                        <img src="<?= htmlspecialchars($sponsor['avatar']) ?>" 
                             alt="<?= htmlspecialchars($sponsor['name']) ?>" 
                             class="w-full max-w-md mx-auto rounded-3xl shadow-2xl neon-glow">
                    </div>
                    
                    <div data-aos="fade-left">
                        <h2 class="font-display text-4xl md:text-5xl font-bold text-gradient mb-6">
                            Meet Your Mentor
                        </h2>
                        
                        <h3 class="text-2xl font-bold text-white mb-4"><?= htmlspecialchars($sponsor['name']) ?></h3>
                        <p class="text-xl text-blue-300 mb-6"><?= htmlspecialchars($sponsor['title'] ?? 'Digital Marketing Expert') ?></p>
                        
                        <div class="grid grid-cols-3 gap-6 mb-8">
                            <div class="text-center">
                                <div class="stats-counter">10K+</div>
                                <div class="text-gray-400 text-sm">Students Mentored</div>
                            </div>
                            <div class="text-center">
                                <div class="stats-counter">₹50Cr+</div>
                                <div class="text-gray-400 text-sm">Revenue Generated</div>
                            </div>
                            <div class="text-center">
                                <div class="stats-counter"><?= htmlspecialchars($sponsor['experience'] ?? '7+') ?></div>
                                <div class="text-gray-400 text-sm">Years Experience</div>
                            </div>
                        </div>
                        
                        <p class="text-lg text-gray-300 leading-relaxed mb-8">
                            <?= htmlspecialchars($sponsor['name']) ?> adalah entrepreneur sukses yang telah membangun multiple 7-figure businesses. 
                            Dengan pengalaman <?= htmlspecialchars($sponsor['experience'] ?? '7+ tahun') ?> di industri digital marketing, 
                            beliau telah membantu ribuan orang mencapai financial freedom melalui bisnis online.
                        </p>
                        
                        <a href="<?= $whatsapp_link ?>" 
                           class="inline-block btn-primary text-white font-bold text-lg px-8 py-4 rounded-full">
                            <i class="fab fa-whatsapp mr-3"></i>
                            CHAT LANGSUNG DENGAN MENTOR
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Final CTA Section -->
    <section class="py-20 hero-bg relative overflow-hidden">
        <div class="floating-elements">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>
        
        <div class="container mx-auto px-4 text-center relative z-10">
            <div class="max-w-4xl mx-auto" data-aos="fade-up">
                <h2 class="font-display text-4xl md:text-6xl font-bold text-gradient mb-8">
                    Ready to Build Your Empire?
                </h2>
                
                <p class="text-xl md:text-2xl text-gray-300 mb-12 leading-relaxed">
                    Join thousands of successful entrepreneurs who have transformed their lives with this proven blueprint.
                    Your financial freedom is just one decision away.
                </p>
                
                <div class="glass-card rounded-3xl p-8 mb-12 max-w-2xl mx-auto">
                    <h3 class="text-2xl font-bold mb-6">🎁 EXCLUSIVE BONUSES TODAY:</h3>
                    <ul class="text-lg space-y-3 mb-8">
                        <li class="flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                            <span>Personal 1-on-1 Strategy Session (Worth ₹25,000)</span>
                        </li>
                        <li class="flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                            <span>Done-For-You Marketing Templates (Worth ₹15,000)</span>
                        </li>
                        <li class="flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                            <span>Exclusive Mastermind Access (Worth ₹50,000)</span>
                        </li>
                    </ul>
                    <p class="text-2xl font-bold text-gradient mb-6">Total Bonus Value: ₹90,000 - FREE!</p>
                </div>
                
                <div class="space-y-6">
                    <a href="<?= $whatsapp_link ?>" 
                       class="inline-block btn-success text-white font-bold text-2xl px-16 py-6 rounded-full whatsapp-pulse transform hover:scale-105 transition-all duration-300">
                        <i class="fab fa-whatsapp mr-4"></i>
                        CLAIM YOUR SPOT NOW - 70% OFF!
                    </a>
                    
                    <p class="text-sm text-gray-400">
                        💬 Click to chat directly with <?= htmlspecialchars($sponsor['name']) ?> on WhatsApp
                    </p>
                    
                    <div class="flex items-center justify-center space-x-6 text-sm text-gray-400">
                        <span class="flex items-center">
                            <i class="fas fa-shield-alt mr-2 text-green-400"></i>
                            30-Day Money Back Guarantee
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-infinity mr-2 text-blue-400"></i>
                            Lifetime Access
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-headset mr-2 text-purple-400"></i>
                            24/7 Support
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-black py-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-4 mb-6">
                <img src="<?= htmlspecialchars($sponsor['avatar']) ?>" 
                     alt="<?= htmlspecialchars($sponsor['name']) ?>" 
                     class="w-16 h-16 rounded-full border-2 border-gray-600">
                <div class="text-left">
                    <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($sponsor['name']) ?></h3>
                    <p class="text-gray-400"><?= htmlspecialchars($sponsor['title'] ?? 'Digital Marketing Expert') ?></p>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8">
                <p class="text-gray-400 mb-4">
                    © <?= date('Y') ?> <?= htmlspecialchars($sponsor['name']) ?>. All rights reserved.
                </p>
                <p class="text-sm text-gray-600">
                    Powered by EPIC Hub - Professional Affiliate Marketing Platform
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Floating WhatsApp Button -->
    <a href="<?= $whatsapp_link ?>" 
       class="fixed bottom-6 right-6 z-50 bg-green-500 hover:bg-green-600 text-white p-4 rounded-full shadow-2xl whatsapp-pulse transition-all duration-300 transform hover:scale-110">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>
    
    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Countdown Timer
        function startCountdown() {
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
                    document.getElementById('countdown').innerHTML = '<div class="text-2xl font-bold text-red-400">OFFER EXPIRED!</div>';
                }
            }, 1000);
        }
        
        // Smooth scrolling
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
        
        // Initialize countdown
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
        });
        
        // Track interactions
        document.querySelectorAll('a[href*="wa.me"]').forEach(link => {
            link.addEventListener('click', function() {
                console.log('WhatsApp CTA clicked:', this.textContent.trim());
                // Add analytics tracking here
            });
        });
        
        // Parallax effect for floating elements
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelectorAll('.floating-element');
            
            parallax.forEach((element, index) => {
                const speed = 0.5 + (index * 0.1);
                element.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });
    </script>
</body>
</html>