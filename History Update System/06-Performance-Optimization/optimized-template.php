<?php
/**
 * EPIC Hub - Optimized HTML Template
 * Performance-optimized template with all 10 optimization rules applied
 */

// Include performance optimizer
require_once __DIR__ . '/performance-optimizer.php';

// Start output buffering to apply optimizations
ob_start();
?>
<!DOCTYPE html>
<html lang="id" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Performance Optimizations -->
    <meta name="theme-color" content="#CFA84E">
    <meta name="color-scheme" content="dark">
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- DNS prefetch for external resources -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    
    <!-- Preload critical fonts -->
    <!-- Font preloading removed - using Google Fonts instead -->
    
    <!-- Preload hero image -->
    <link rel="preload" href="/uploads/hero-image.webp" as="image">
    
    <!-- Critical CSS inlined -->
    <style>
        /* Critical above-the-fold CSS */
        :root {
            --gold-500: #CFA84E;
            --ink-900: #0B0B0F;
            --surface-1: #0F0F14;
            --surface-2: #15161C;
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --transition-fast: 150ms ease-in-out;
        }
        
        * {
            box-sizing: border-box;
        }
        
        html {
            font-size: 16px;
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            scroll-behavior: smooth;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: var(--font-family);
            color: #ffffff;
            background-color: var(--ink-900);
            overflow-x: hidden;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }
        
        .admin-sidebar {
            width: 280px;
            background-color: var(--surface-2);
            flex-shrink: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transform: translateX(0);
            transition: transform var(--transition-fast);
        }
        
        .admin-main {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            background-color: var(--surface-1);
            transition: margin-left var(--transition-fast);
        }
        
        .admin-header {
            background-color: var(--surface-2);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
            backdrop-filter: blur(10px);
        }
        
        .admin-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Lazy loading placeholder */
        .lazy {
            opacity: 0;
            transition: opacity var(--transition-fast);
        }
        
        .lazy.loaded {
            opacity: 1;
        }
        
        /* Prevent layout shift */
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.open {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
        }
        
        /* Reduce motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
    
    <!-- Preload main stylesheet -->
    <link rel="preload" href="/themes/modern/admin/admin.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/themes/modern/admin/admin.css"></noscript>
    
    <!-- Page title and meta -->
    <title><?= $page_title ?? 'EPIC Hub Admin' ?></title>
    <meta name="description" content="<?= $page_description ?? 'EPIC Hub Admin Panel - Manage your business with powerful tools' ?>">
    
    <!-- Open Graph meta tags -->
    <meta property="og:title" content="<?= $page_title ?? 'EPIC Hub Admin' ?>">
    <meta property="og:description" content="<?= $page_description ?? 'EPIC Hub Admin Panel' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= epic_current_url() ?>">
    <meta property="og:image" content="<?= epic_url('/uploads/og-image.webp') ?>">
    
    <!-- Twitter Card meta tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $page_title ?? 'EPIC Hub Admin' ?>">
    <meta name="twitter:description" content="<?= $page_description ?? 'EPIC Hub Admin Panel' ?>">
    <meta name="twitter:image" content="<?= epic_url('/uploads/twitter-card.webp') ?>">
    
    <!-- Structured data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "EPIC Hub Admin",
        "description": "Business management platform",
        "url": "<?= epic_url() ?>",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web"
    }
    </script>
    
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-to-content">Skip to main content</a>
</head>
<body>
    <!-- Admin Container -->
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar" role="navigation" aria-label="Main navigation">
            <div class="sidebar-content">
                <!-- Logo -->
                <div class="sidebar-logo">
                    <img src="/uploads/logos/logo.webp" 
                         alt="EPIC Hub Logo" 
                         width="120" 
                         height="40"
                         style="aspect-ratio: 3/1;"
                         loading="eager">
                </div>
                
                <!-- Navigation -->
                <nav class="sidebar-nav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?= epic_url('admin') ?>" class="nav-link">
                                <span class="nav-icon">🏠</span>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= epic_url('admin/manage/product') ?>" class="nav-link">
                                <span class="nav-icon">📦</span>
                                <span class="nav-text">Products</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= epic_url('admin/lms-products') ?>" class="nav-link">
                                <span class="nav-icon">🎓</span>
                                <span class="nav-text">LMS Products</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= epic_url('admin/member') ?>" class="nav-link">
                                <span class="nav-icon">👥</span>
                                <span class="nav-text">Members</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= epic_url('admin/order') ?>" class="nav-link">
                                <span class="nav-icon">🛒</span>
                                <span class="nav-text">Orders</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main" role="main">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-content">
                    <button class="mobile-menu-toggle" aria-label="Toggle mobile menu">
                        <span class="hamburger"></span>
                    </button>
                    
                    <h1 class="page-title"><?= $page_title ?? 'Dashboard' ?></h1>
                    
                    <div class="header-actions">
                        <button class="btn btn-primary">
                            <span class="btn-icon">➕</span>
                            <span class="btn-text">Add New</span>
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content" id="main-content">
                <!-- Hero Section with optimized image -->
                <section class="hero-section">
                    <div class="hero-content">
                        <h2 class="hero-title">Welcome to EPIC Hub</h2>
                        <p class="hero-description">Manage your business with powerful tools and insights.</p>
                    </div>
                    
                    <!-- Optimized hero image with WebP, lazy loading, and responsive srcset -->
                    <div class="hero-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 400'%3E%3C/svg%3E"
                             data-src="/uploads/hero-image.webp"
                             data-srcset="/uploads/hero-image-400.webp 400w,
                                         /uploads/hero-image-800.webp 800w,
                                         /uploads/hero-image-1200.webp 1200w"
                             sizes="(max-width: 768px) 400px, (max-width: 1200px) 800px, 1200px"
                             alt="EPIC Hub Dashboard Overview"
                             width="800"
                             height="400"
                             style="aspect-ratio: 2/1;"
                             loading="lazy"
                             decoding="async"
                             class="lazy hero-img">
                    </div>
                </section>
                
                <!-- Stats Cards -->
                <section class="stats-section">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">📊</div>
                            <div class="stat-content">
                                <h3 class="stat-title">Total Sales</h3>
                                <p class="stat-value">$12,345</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-content">
                                <h3 class="stat-title">Active Users</h3>
                                <p class="stat-value">1,234</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-content">
                                <h3 class="stat-title">Products</h3>
                                <p class="stat-value">56</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">🎓</div>
                            <div class="stat-content">
                                <h3 class="stat-title">LMS Courses</h3>
                                <p class="stat-value">23</p>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- YouTube Facade Example -->
                <section class="video-section">
                    <h3>Tutorial Video</h3>
                    <div class="youtube-facade" 
                         data-video-id="dQw4w9WgXcQ"
                         style="aspect-ratio: 16/9; background-image: url('/uploads/video-thumbnail.webp');">
                        <!-- YouTube play button will be added by CSS -->
                    </div>
                </section>
                
                <!-- Dynamic content area -->
                <div class="dynamic-content">
                    <?= $content ?? '' ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Performance monitoring (development only) -->
    <?php if (defined('EPIC_DEBUG') && EPIC_DEBUG): ?>
    <div class="perf-monitor" id="perfMonitor">
        <div class="perf-metric">Load: <span id="loadTime">-</span>ms</div>
        <div class="perf-metric">FCP: <span id="fcp">-</span>ms</div>
        <div class="perf-metric">LCP: <span id="lcp">-</span>ms</div>
        <div class="perf-metric">CLS: <span id="cls">-</span></div>
    </div>
    <?php endif; ?>
    
    <!-- Modern JavaScript with defer and type="module" -->
    <script type="module" src="/themes/modern/admin/admin.js" defer></script>
    
    <!-- Service Worker for caching -->
    <script type="module" defer>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('SW registered: ', registration);
                    })
                    .catch(registrationError => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>
    
    <!-- YouTube facade handler -->
    <script type="module" defer>
        document.addEventListener('click', (e) => {
            if (e.target.closest('.youtube-facade')) {
                const facade = e.target.closest('.youtube-facade');
                const videoId = facade.dataset.videoId;
                
                // Replace facade with actual YouTube embed
                const iframe = document.createElement('iframe');
                iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
                iframe.width = '100%';
                iframe.height = '100%';
                iframe.frameBorder = '0';
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                iframe.allowFullscreen = true;
                
                facade.replaceWith(iframe);
            }
        });
    </script>
    
    <!-- Analytics (loaded after idle) -->
    <script type="module" defer>
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                // Load analytics after idle
                console.log('Analytics loaded after idle');
            });
        } else {
            setTimeout(() => {
                console.log('Analytics loaded after timeout');
            }, 2000);
        }
    </script>
</body>
</html>

<?php
// Apply performance optimizations to the output
$html = ob_get_clean();
$optimized_html = optimize_performance($html);
echo $optimized_html;
?>