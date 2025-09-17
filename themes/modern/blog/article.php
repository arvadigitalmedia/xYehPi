<?php
/**
 * Blog Article Template
 * Display single blog article
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= epic_url('blog/' . $article['slug']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($article['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
    <?php if ($page_image): ?>
    <meta property="og:image" content="<?= epic_url($page_image) ?>">
    <?php endif; ?>
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= epic_url('blog/' . $article['slug']) ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($article['title']) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($page_description) ?>">
    <?php if ($page_image): ?>
    <meta property="twitter:image" content="<?= epic_url($page_image) ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= epic_url('uploads/logos/favicon.ico') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "<?= htmlspecialchars($article['title']) ?>",
        "description": "<?= htmlspecialchars($page_description) ?>",
        "author": {
            "@type": "Person",
            "name": "<?= htmlspecialchars($article['author_name']) ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "<?= htmlspecialchars(epic_setting('site_name')) ?>",
            "logo": {
                "@type": "ImageObject",
                "url": "<?= epic_url('uploads/logos/logo.png') ?>"
            }
        },
        "datePublished": "<?= date('c', strtotime($article['published_at'])) ?>",
        "dateModified": "<?= date('c', strtotime($article['updated_at'] ?: $article['created_at'])) ?>",
        <?php if ($page_image): ?>
        "image": "<?= epic_url($page_image) ?>",
        <?php endif; ?>
        "url": "<?= epic_url('blog/' . $article['slug']) ?>"
    }
    </script>
    
    <!-- Shining Noir Theme Styles -->
    <style>
        :root {
            --bg-black: #0B0D11;
            --surface-1: #111318;
            --surface-2: #171A22;
            --border: #242A38;
            --text-primary: #F5F6F8;
            --text-secondary: #B8C0CF;
            --text-muted: #8A93A6;
            --gold-500: #D8B74A;
            --gold-600: #E8CC6B;
            --gold-700: #B8942E;
            --silver-500: #C8D0DA;
            --silver-600: #E1E6EE;
            --ring-color: rgba(232,204,107,.35);
            --success: #22C55E;
            --info: #3B82F6;
        }
        
        .gold-sheen {
            background: linear-gradient(90deg, #F5E7A9 0%, #E8CC6B 35%, #D8B74A 70%, #F1DB8C 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .silver-sheen {
            background: linear-gradient(90deg, #EFF3FA 0%, #E1E6EE 40%, #C8D0DA 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .gold-gradient {
            background: linear-gradient(90deg, #F5E7A9 0%, #E8CC6B 35%, #D8B74A 70%, #F1DB8C 100%);
        }
        
        .silver-gradient {
            background: linear-gradient(90deg, #EFF3FA 0%, #E1E6EE 40%, #C8D0DA 100%);
        }
        
        .text-clamp-h1 {
            font-size: clamp(26px, 3.2vw, 36px);
        }
        
        .text-clamp-h2 {
            font-size: clamp(20px, 2.4vw, 28px);
        }
        
        .text-clamp-base {
            font-size: clamp(15px, 1.2vw, 16px);
        }
        
        .shadow-noir {
            box-shadow: 0 10px 30px rgba(0,0,0,.35);
        }
        
        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px var(--ring-color);
        }
        
        .nav-link-active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gold-600);
        }
        
        .title-underline::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #F5E7A9 0%, #E8CC6B 35%, #D8B74A 70%, #F1DB8C 100%);
        }
    </style>
</head>
<body class="font-inter text-clamp-base" style="background-color: var(--bg-black); color: var(--text-primary);">
    <!-- Header -->
    <header class="sticky top-0 z-50" style="background-color: var(--surface-1); border-bottom: 1px solid var(--border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="<?= epic_url() ?>" class="flex items-center space-x-2 focus-ring rounded-lg px-2 py-1">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: var(--surface-2);">
                            <span class="text-lg font-bold silver-sheen">E</span>
                        </div>
                        <span class="text-lg font-semibold silver-sheen hidden sm:block"><?= htmlspecialchars(epic_setting('site_name', 'EPI HUB')) ?></span>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="<?= epic_url() ?>" class="relative px-3 py-2 text-sm font-medium focus-ring rounded-lg transition-colors" style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">Home</a>
                    <a href="<?= epic_url('blog') ?>" class="relative px-3 py-2 text-sm font-medium nav-link-active focus-ring rounded-lg" style="color: var(--text-primary);">Blog</a>
                    <a href="<?= epic_url('dashboard') ?>" class="relative px-3 py-2 text-sm font-medium focus-ring rounded-lg transition-colors" style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">Dashboard</a>
                </nav>
                
                <!-- Auth Actions -->
                <div class="flex items-center space-x-4">
                    <?php if (epic_is_logged_in()): ?>
                        <a href="<?= epic_url('logout') ?>" class="px-4 py-2 text-sm font-medium rounded-lg focus-ring transition-all" style="color: var(--text-secondary); border: 1px solid var(--border);" onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--text-primary)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-secondary)'">Logout</a>
                    <?php else: ?>
                        <a href="<?= epic_url('login') ?>" class="px-4 py-2 text-sm font-medium rounded-lg focus-ring transition-all" style="color: var(--text-secondary); border: 1px solid var(--border);" onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--text-primary)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-secondary)'">Login</a>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Button -->
                    <button class="md:hidden p-2 rounded-lg focus-ring" style="color: var(--text-secondary);" onclick="toggleMobileMenu()">
                        <i data-feather="menu" width="20" height="20"></i>
                    </button>
                </div>
                
                <!-- Mobile Navigation -->
                <div id="mobile-menu" class="md:hidden hidden border-t" style="border-color: var(--border);">
                    <div class="px-4 py-4 space-y-2">
                        <a href="<?= epic_url() ?>" class="block px-3 py-2 text-sm font-medium rounded-lg focus-ring" style="color: var(--text-secondary);">Home</a>
                        <a href="<?= epic_url('blog') ?>" class="block px-3 py-2 text-sm font-medium rounded-lg" style="color: var(--text-primary); background-color: var(--surface-2);">Blog</a>
                        <a href="<?= epic_url('dashboard') ?>" class="block px-3 py-2 text-sm font-medium rounded-lg focus-ring" style="color: var(--text-secondary);">Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Breadcrumb -->
    <div class="py-4" style="background-color: var(--surface-1);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="<?= epic_url() ?>" class="focus-ring rounded px-2 py-1 transition-colors" style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'; this.style.textDecoration='underline'" onmouseout="this.style.color='var(--text-secondary)'; this.style.textDecoration='none'">Home</a>
                <span style="color: var(--text-muted);">/</span>
                <a href="<?= epic_url('blog') ?>" class="focus-ring rounded px-2 py-1 transition-colors" style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'; this.style.textDecoration='underline'" onmouseout="this.style.color='var(--text-secondary)'; this.style.textDecoration='none'">Blog</a>
                <?php if ($article['category_name']): ?>
                    <span style="color: var(--text-muted);">/</span>
                    <a href="<?= epic_url('blog/category/' . $article['category_slug']) ?>" class="focus-ring rounded px-2 py-1 transition-colors" style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'; this.style.textDecoration='underline'" onmouseout="this.style.color='var(--text-secondary)'; this.style.textDecoration='none'"><?= htmlspecialchars($article['category_name']) ?></a>
                <?php endif; ?>
                <span style="color: var(--text-muted);">/</span>
                <span style="color: var(--text-muted);" class="truncate"><?= htmlspecialchars(strlen($article['title']) > 50 ? substr($article['title'], 0, 50) . '...' : $article['title']) ?></span>
            </nav>
        </div>
    </div>
    
    <!-- Article Content -->
    <main class="py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Content -->
                <article class="lg:col-span-3">
                    <!-- Article Header -->
                    <header class="mb-8">
                        <?php if ($article['category_name']): ?>
                            <div class="mb-4">
                                <a href="<?= epic_url('blog/category/' . $article['category_slug']) ?>" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium focus-ring transition-all" style="background-color: var(--surface-2); color: var(--gold-600); border: 1px solid var(--border);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--gold-600)'">
                                    <?= htmlspecialchars($article['category_name']) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <h1 class="text-clamp-h1 font-bold leading-tight mb-6 relative title-underline" style="color: var(--text-primary);"><?= htmlspecialchars($article['title']) ?></h1>
                        
                        <?php if ($article['excerpt']): ?>
                            <div class="text-lg leading-relaxed mb-6" style="color: var(--text-secondary);">
                                <?= htmlspecialchars($article['excerpt']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex flex-wrap items-center gap-4 text-sm" style="color: var(--text-secondary);">
                            <div class="flex items-center gap-2">
                                <i data-feather="user" width="16" height="16" class="silver-sheen"></i>
                                <span><?= htmlspecialchars($article['author_name']) ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                    <i data-feather="calendar" width="16" height="16" class="silver-sheen"></i>
                                <span><?= date('M j, Y', strtotime($article['published_at'])) ?></span>
                            </div>
                            <?php if ($article['reading_time']): ?>
                                <div class="flex items-center gap-2">
                                    <i data-feather="clock" width="16" height="16" class="silver-sheen"></i>
                                    <span><?= $article['reading_time'] ?> min read</span>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center gap-2">
                                <i data-feather="eye" width="16" height="16" class="silver-sheen"></i>
                                <span><?= number_format($article['view_count']) ?> views</span>
                            </div>
                        </div>
                    </header>
                    
                    <!-- Share Bar (Top) -->
                    <div class="mb-8">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-medium" style="color: var(--text-secondary);">Share:</span>
                            <div class="flex items-center gap-2">
                                <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' - ' . epic_url('blog/' . $article['slug'])) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="message-circle" width="16" height="16"></i>
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(epic_url('blog/' . $article['slug'])) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="facebook" width="16" height="16"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(epic_url('blog/' . $article['slug'])) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="twitter" width="16" height="16"></i>
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(epic_url('blog/' . $article['slug'])) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="linkedin" width="16" height="16"></i>
                                </a>
                                <a href="https://t.me/share/url?url=<?= urlencode(epic_url('blog/' . $article['slug'])) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="send" width="16" height="16"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Featured Image -->
                    <?php if ($article['featured_image']): ?>
                        <div class="mb-8">
                            <img src="<?= epic_url($article['featured_image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="w-full h-auto rounded-2xl shadow-noir" style="aspect-ratio: 16/9; object-fit: cover;" loading="lazy">
                        </div>
                    <?php endif; ?>
                    
                    <!-- Article Body -->
                    <div class="max-w-3xl mx-auto prose prose-lg" style="color: var(--text-primary); line-height: 1.7;">
                        <?= $article['content'] ?>
                    </div>
                    
                    <!-- Share Bar (Bottom) -->
                    <div class="mt-12 pt-8" style="border-top: 1px solid var(--border);">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-medium" style="color: var(--text-secondary);">Bagikan artikel ini:</span>
                            <div class="flex items-center gap-2">
                                <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' - ' . epic_url('blog/' . $article['slug'])) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="message-circle" width="16" height="16"></i>
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(epic_url('blog/' . $article['slug'])) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="facebook" width="16" height="16"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(epic_url('blog/' . $article['slug'])) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="twitter" width="16" height="16"></i>
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(epic_url('blog/' . $article['slug'])) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="linkedin" width="16" height="16"></i>
                                </a>
                                <a href="https://t.me/share/url?url=<?= urlencode(epic_url('blog/' . $article['slug'])) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-full focus-ring transition-all" style="background-color: var(--surface-2); border: 1px solid var(--border); color: var(--silver-500);" onmouseover="this.style.backgroundColor='var(--gold-600)'; this.style.color='var(--bg-black)'" onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--silver-500)'">
                                    <i data-feather="send" width="16" height="16"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- About the Author -->
                    <div class="mt-12 p-6 rounded-2xl shadow-noir relative" style="background-color: var(--surface-2); border-top: 3px solid transparent; background-image: linear-gradient(var(--surface-2), var(--surface-2)), linear-gradient(90deg, var(--silver-500), var(--gold-600)); background-origin: border-box; background-clip: padding-box, border-box;">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: var(--surface-1);">
                                <i data-feather="user" width="24" height="24" class="silver-sheen"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold mb-2" style="color: var(--text-primary);"><?= htmlspecialchars($article['author_name']) ?></h3>
                                <p class="text-sm leading-relaxed" style="color: var(--text-secondary);">Content creator dan digital marketing expert yang berpengalaman dalam industri affiliate marketing dan pengembangan bisnis online.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recommended Articles -->
                    <div class="mt-12">
                        <div class="flex items-center gap-3 mb-6">
                            <h2 class="text-clamp-h2 font-bold" style="color: var(--text-primary);">Rekomendasi untuk Anda</h2>
                            <div class="w-12 h-1 rounded-full gold-gradient"></div>
                        </div>
                        
                        <?php 
                        // Get recommended articles (related + recent)
                        $recommended_articles = [];
                        if (!empty($related_articles)) {
                            $recommended_articles = array_slice($related_articles, 0, 6);
                        }
                        
                        // Fill with recent articles if needed
                        if (count($recommended_articles) < 6) {
                            $recent_articles = epic_get_recent_articles(6 - count($recommended_articles));
                            $recommended_articles = array_merge($recommended_articles, $recent_articles);
                        }
                        ?>
                        
                        <?php if (!empty($recommended_articles)): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($recommended_articles as $rec_article): ?>
                                    <article class="group cursor-pointer transition-all duration-300 hover:-translate-y-1 focus-within:ring-2" style="--tw-ring-color: var(--ring-color);">
                                        <a href="<?= epic_url('blog/' . $rec_article['slug']) ?>" class="block">
                                            <?php if ($rec_article['featured_image']): ?>
                                                <div class="mb-4 overflow-hidden rounded-xl">
                                                    <img src="<?= epic_url($rec_article['featured_image']) ?>" alt="<?= htmlspecialchars($rec_article['title']) ?>" class="w-full h-40 object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($rec_article['category_name']): ?>
                                                <div class="mb-2">
                                                    <span class="inline-block px-2 py-1 text-xs font-medium rounded-full" style="background-color: var(--surface-1); color: var(--gold-600);">
                                                        <?= htmlspecialchars($rec_article['category_name']) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <h3 class="text-base font-semibold leading-tight mb-2 line-clamp-2" style="color: var(--text-primary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                <?= htmlspecialchars($rec_article['title']) ?>
                                            </h3>
                                            
                                            <div class="flex items-center gap-3 text-xs" style="color: var(--text-muted);">
                                                <span><?= date('M j, Y', strtotime($rec_article['published_at'])) ?></span>
                                                <span>•</span>
                                                <span><?= number_format($rec_article['view_count']) ?> views</span>
                                            </div>
                                        </a>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8" style="color: var(--text-muted);">
                                <i data-feather="file-text" width="48" height="48" class="mx-auto mb-4 opacity-50"></i>
                                <p>Belum ada artikel rekomendasi tersedia.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
                
                <!-- Sidebar -->
                <aside class="lg:col-span-1">
                    <!-- Quick Navigation -->
                    <div class="sticky top-24">
                        <div class="p-6 rounded-2xl shadow-noir mb-6" style="background-color: var(--surface-2); border: 1px solid var(--border);">
                            <h3 class="text-lg font-bold mb-4 relative title-underline" style="color: var(--text-primary);">Navigasi Cepat</h3>
                            <nav class="space-y-2">
                                <a href="<?= epic_url('blog') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg focus-ring transition-all" style="color: var(--text-secondary);" onmouseover="this.style.backgroundColor='var(--surface-1)'; this.style.color='var(--text-primary)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-secondary)'">
                                    <i data-feather="arrow-left" width="16" height="16"></i>
                                    <span class="text-sm">Kembali ke Blog</span>
                                </a>
                                <a href="<?= epic_url('dashboard') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg focus-ring transition-all" style="color: var(--text-secondary);" onmouseover="this.style.backgroundColor='var(--surface-1)'; this.style.color='var(--text-primary)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-secondary)'">
                                    <i data-feather="home" width="16" height="16"></i>
                                    <span class="text-sm">Dashboard</span>
                                </a>
                                <?php if ($article['category_name']): ?>
                                    <a href="<?= epic_url('blog/category/' . $article['category_slug']) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg focus-ring transition-all" style="color: var(--text-secondary);" onmouseover="this.style.backgroundColor='var(--surface-1)'; this.style.color='var(--text-primary)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-secondary)'">
                                        <i data-feather="folder" width="16" height="16"></i>
                                        <span class="text-sm"><?= htmlspecialchars($article['category_name']) ?></span>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                        
                        <!-- Categories -->
                        <div class="p-6 rounded-2xl shadow-noir mb-6" style="background-color: var(--surface-2); border: 1px solid var(--border);">
                            <h3 class="text-lg font-bold mb-4 relative title-underline" style="color: var(--text-primary);">Kategori</h3>
                            <div class="space-y-2">
                                <?php 
                                $categories = epic_get_blog_categories();
                                foreach ($categories as $category): 
                                ?>
                                    <a href="<?= epic_url('blog/category/' . $category['slug']) ?>" class="flex items-center justify-between px-3 py-2 rounded-lg focus-ring transition-all <?= $category['slug'] === $article['category_slug'] ? 'active' : '' ?>" style="color: var(--text-secondary); <?= $category['slug'] === $article['category_slug'] ? 'background-color: var(--surface-1); color: var(--gold-600);' : '' ?>" onmouseover="if (!this.classList.contains('active')) { this.style.backgroundColor='var(--surface-1)'; this.style.color='var(--text-primary)'; }" onmouseout="if (!this.classList.contains('active')) { this.style.backgroundColor='transparent'; this.style.color='var(--text-secondary)'; }">
                                        <span class="text-sm"><?= htmlspecialchars($category['name']) ?></span>
                                        <span class="text-xs px-2 py-1 rounded-full" style="background-color: var(--border); color: var(--text-muted);"><?= $category['article_count'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Article Stats -->
                        <div class="p-6 rounded-2xl shadow-noir" style="background-color: var(--surface-2); border: 1px solid var(--border);">
                            <h3 class="text-lg font-bold mb-4 relative title-underline" style="color: var(--text-primary);">Statistik Artikel</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i data-feather="eye" width="16" height="16" class="silver-sheen"></i>
                                        <span class="text-sm" style="color: var(--text-secondary);">Views</span>
                                    </div>
                                    <span class="text-sm font-medium" style="color: var(--text-primary);"><?= number_format($article['view_count']) ?></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i data-feather="calendar" width="16" height="16" class="silver-sheen"></i>
                                        <span class="text-sm" style="color: var(--text-secondary);">Published</span>
                                    </div>
                                    <span class="text-sm font-medium" style="color: var(--text-primary);"><?= date('M j, Y', strtotime($article['published_at'])) ?></span>
                                </div>
                                <?php if ($article['reading_time']): ?>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i data-feather="clock" width="16" height="16" class="silver-sheen"></i>
                                            <span class="text-sm" style="color: var(--text-secondary);">Reading Time</span>
                                        </div>
                                        <span class="text-sm font-medium" style="color: var(--text-primary);"><?= $article['reading_time'] ?> min</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
    
    <!-- Sticky CTA -->
    <div class="fixed bottom-0 left-0 right-0 z-40 p-4 md:p-6" style="background: linear-gradient(to top, var(--bg-black) 0%, var(--bg-black) 70%, transparent 100%);">
        <div class="max-w-7xl mx-auto">
            <!-- Mobile CTA -->
            <div class="md:hidden flex flex-col gap-3">
                <a href="<?= epic_url('register') ?>" class="flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-center focus-ring transition-all shadow-noir" style="background: var(--gold-600); color: var(--bg-black); min-height: 48px;" onmouseover="this.style.backgroundColor='var(--gold-700)'" onmouseout="this.style.backgroundColor='var(--gold-600)'">
                    <i data-feather="user-plus" width="20" height="20"></i>
                    <span>Daftar EPI HUB</span>
                </a>
                <a href="https://wa.me/6281234567890?text=Halo,%20saya%20tertarik%20dengan%20EPI%20HUB" target="_blank" class="flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-center focus-ring transition-all" style="border: 2px solid var(--gold-600); color: var(--gold-600); min-height: 48px;" onmouseover="this.style.backgroundColor='#3A3116'; this.style.borderColor='var(--gold-700)'" onmouseout="this.style.backgroundColor='transparent'; this.style.borderColor='var(--gold-600)'">
                    <i data-feather="message-circle" width="20" height="20"></i>
                    <span>WhatsApp Sponsor</span>
                </a>
            </div>
            
            <!-- Desktop CTA -->
            <div class="hidden md:flex items-center justify-end gap-4">
                <a href="https://wa.me/6281234567890?text=Halo,%20saya%20tertarik%20dengan%20EPI%20HUB" target="_blank" class="flex items-center gap-2 px-6 py-3 rounded-xl font-semibold focus-ring transition-all" style="border: 2px solid var(--gold-600); color: var(--gold-600); min-height: 52px;" onmouseover="this.style.backgroundColor='#3A3116'; this.style.borderColor='var(--gold-700)'" onmouseout="this.style.backgroundColor='transparent'; this.style.borderColor='var(--gold-600)'">
                    <i data-feather="message-circle" width="20" height="20"></i>
                    <span>WhatsApp Sponsor</span>
                </a>
                <a href="<?= epic_url('register') ?>" class="flex items-center gap-2 px-6 py-3 rounded-xl font-semibold focus-ring transition-all shadow-noir" style="background: var(--gold-600); color: var(--bg-black); min-height: 52px;" onmouseover="this.style.backgroundColor='var(--gold-700)'" onmouseout="this.style.backgroundColor='var(--gold-600)'">
                    <i data-feather="user-plus" width="20" height="20"></i>
                    <span>Daftar EPI HUB</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        // Initialize Feather icons
        feather.replace();
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
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
        
        // Reading progress and sticky CTA behavior
        let lastScrollTop = 0;
        const stickyCtaElement = document.querySelector('.fixed.bottom-0');
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            
            // Hide/show sticky CTA based on scroll direction
            if (scrollTop > lastScrollTop && scrollTop > 200) {
                // Scrolling down
                stickyCtaElement.style.transform = 'translateY(100%)';
            } else {
                // Scrolling up
                stickyCtaElement.style.transform = 'translateY(0)';
            }
            
            // Hide CTA when near bottom of page
            if (scrollTop + windowHeight >= documentHeight - 100) {
                stickyCtaElement.style.transform = 'translateY(100%)';
            }
            
            lastScrollTop = scrollTop;
        });
        
        // Add transition to sticky CTA
        if (stickyCtaElement) {
            stickyCtaElement.style.transition = 'transform 0.3s ease-in-out';
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            const mobileMenu = document.getElementById('mobile-menu');
            const menuButton = document.querySelector('[onclick="toggleMobileMenu()"]');
            
            if (!mobileMenu.contains(e.target) && !menuButton.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
        
        // Lazy loading for images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src || img.src;
                        img.classList.remove('opacity-0');
                        img.classList.add('opacity-100');
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[loading="lazy"]').forEach(img => {
                img.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                imageObserver.observe(img);
            });
        }
        
        // Add padding bottom to body to account for sticky CTA
        document.body.style.paddingBottom = window.innerWidth < 768 ? '140px' : '100px';
        
        // Adjust padding on resize
        window.addEventListener('resize', function() {
            document.body.style.paddingBottom = window.innerWidth < 768 ? '140px' : '100px';
        });
        
        // Enhanced focus management for accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const mobileMenu = document.getElementById('mobile-menu');
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            }
        });
        
        // Add custom styles for prose content
        const proseContent = document.querySelector('.prose');
        if (proseContent) {
            // Style headings
            proseContent.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(heading => {
                heading.style.color = 'var(--text-primary)';
                heading.style.fontWeight = '600';
                heading.style.marginTop = '2rem';
                heading.style.marginBottom = '1rem';
            });
            
            // Style paragraphs
            proseContent.querySelectorAll('p').forEach(p => {
                p.style.color = 'var(--text-secondary)';
                p.style.marginBottom = '1.5rem';
            });
            
            // Style links
            proseContent.querySelectorAll('a').forEach(link => {
                link.style.color = 'var(--gold-600)';
                link.style.textDecoration = 'underline';
                link.addEventListener('mouseover', function() {
                    this.style.color = 'var(--gold-700)';
                });
                link.addEventListener('mouseout', function() {
                    this.style.color = 'var(--gold-600)';
                });
            });
            
            // Style code blocks
            proseContent.querySelectorAll('pre, code').forEach(code => {
                code.style.backgroundColor = 'var(--surface-2)';
                code.style.color = 'var(--text-primary)';
                code.style.padding = '0.5rem';
                code.style.borderRadius = '0.5rem';
                code.style.border = '1px solid var(--border)';
            });
            
            // Style blockquotes
            proseContent.querySelectorAll('blockquote').forEach(quote => {
                quote.style.backgroundColor = 'var(--surface-2)';
                quote.style.borderLeft = '4px solid var(--gold-600)';
                quote.style.padding = '1rem';
                quote.style.margin = '1.5rem 0';
                quote.style.borderRadius = '0.5rem';
                quote.style.fontStyle = 'italic';
            });
            
            // Style images
            proseContent.querySelectorAll('img').forEach(img => {
                img.style.borderRadius = '1rem';
                img.style.boxShadow = '0 10px 30px rgba(0,0,0,.35)';
                img.style.margin = '1.5rem auto';
                img.style.display = 'block';
            });
        }
    </script>
</body>
</html>