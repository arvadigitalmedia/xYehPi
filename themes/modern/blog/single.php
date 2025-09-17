<?php
/**
 * EPIC Hub Single Blog Article Page
 * Individual article view with referral tracking and social sharing
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Get article slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    epic_route_404();
    return;
}

// Get article with author and category info
$article = db()->selectOne(
    "SELECT a.*, c.name as category_name, c.slug as category_slug,
            u.name as author_name, u.avatar as author_avatar,
            DATE_FORMAT(a.published_at, '%M %d, %Y') as formatted_date,
            DATE_FORMAT(a.published_at, '%Y-%m-%d') as iso_date
     FROM " . TABLE_ARTICLES . " a
     LEFT JOIN " . TABLE_CATEGORIES . " c ON a.category_id = c.id
     LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
     WHERE a.slug = ? AND a.status = 'published'",
    [$slug]
);

if (!$article) {
    epic_route_404();
    return;
}

// Check visibility permissions
$user = epic_current_user();
if ($article['visibility'] === 'members' && !$user) {
    epic_redirect(epic_url('login?redirect=blog/' . $slug));
    return;
}

if ($article['visibility'] === 'premium' && (!$user || !in_array($user['status'], ['epic', 'premium']))) {
    // Show premium content notice
    $show_premium_notice = true;
} else {
    $show_premium_notice = false;
}

// Track article view
if (!$show_premium_notice) {
    // Update view count
    db()->query(
        "UPDATE " . TABLE_ARTICLES . " SET view_count = view_count + 1 WHERE id = ?",
        [$article['id']]
    );
    
    // Track detailed analytics if tables exist
    try {
        // Check if blog analytics tables exist
        $table_exists = db()->selectValue(
            "SELECT COUNT(*) FROM information_schema.tables 
             WHERE table_schema = DATABASE() AND table_name = 'epic_blog_article_stats'"
        );
        
        if ($table_exists) {
            // Update daily stats
            db()->query(
                "INSERT INTO epic_blog_article_stats (article_id, date, views, unique_views) 
                 VALUES (?, CURDATE(), 1, 1)
                 ON DUPLICATE KEY UPDATE 
                 views = views + 1, 
                 unique_views = unique_views + 1",
                [$article['id']]
            );
        }
    } catch (Exception $e) {
        // Analytics tables don't exist yet, continue without error
        error_log('Blog analytics tracking failed: ' . $e->getMessage());
    }
}

// Handle referral tracking
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $referrer_code = $_GET['ref'];
    $referrer = db()->selectOne(
        "SELECT id FROM " . TABLE_USERS . " WHERE referral_code = ?",
        [$referrer_code]
    );
    
    if ($referrer) {
        // Store referrer info in session for later tracking
        $_SESSION['blog_referrer_id'] = $referrer['id'];
        $_SESSION['blog_referrer_source'] = 'blog_article';
        $_SESSION['blog_article_id'] = $article['id'];
        $_SESSION['blog_visit_time'] = time();
        
        // Track the referral visit
        try {
            $visit_data = [
                'user_id' => $user ? $user['id'] : null,
                'referrer_id' => $referrer['id'],
                'source' => 'blog_article_' . $article['slug'],
                'article_id' => $article['id'],
                'article_slug' => $article['slug'],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
                'visited_at' => date('Y-m-d H:i:s')
            ];
            
            db()->insert(TABLE_LANDING_VISITS, $visit_data);
        } catch (Exception $e) {
            error_log('Referral tracking failed: ' . $e->getMessage());
        }
    }
}

// Get related articles
$related_articles = [];
if ($article['category_id']) {
    $related_articles = db()->select(
        "SELECT a.*, DATE_FORMAT(a.published_at, '%M %d, %Y') as formatted_date
         FROM " . TABLE_ARTICLES . " a
         WHERE a.category_id = ? AND a.id != ? AND a.status = 'published' AND a.visibility = 'public'
         ORDER BY a.published_at DESC
         LIMIT 3",
        [$article['category_id'], $article['id']]
    );
}

// Parse gallery data for social image
$gallery_data = json_decode($article['gallery'] ?? '{}', true);
$social_image = $gallery_data['social_image'] ?? null;

// Determine the best image for social sharing
$share_image = $social_image ?: ($article['featured_image'] ? epic_url('uploads/' . $article['featured_image']) : epic_url('uploads/logos/' . epic_setting('site_logo', 'default-blog.jpg')));

// Page meta data
$page_title = $article['seo_title'] ?: $article['title'];
$page_description = $article['seo_description'] ?: $article['excerpt'] ?: substr(strip_tags($article['content']), 0, 160);
$page_keywords = $article['seo_keywords'] ?: 'affiliate marketing, blog, ' . ($article['category_name'] ? strtolower($article['category_name']) : 'business');

// Generate article URL for sharing
$article_url = epic_url('blog/' . $article['slug']);
$referral_url = $user && $user['referral_code'] ? epic_url('blog/' . $article['slug'] . '?ref=' . $user['referral_code']) : $article_url;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title . ' - ' . epic_setting('site_name')) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($page_keywords) ?>">
    <meta name="author" content="<?= htmlspecialchars($article['author_name']) ?>">
    <meta name="article:published_time" content="<?= $article['iso_date'] ?>T<?= date('H:i:s', strtotime($article['published_at'])) ?>Z">
    <meta name="article:author" content="<?= htmlspecialchars($article['author_name']) ?>">
    <?php if ($article['category_name']): ?>
        <meta name="article:section" content="<?= htmlspecialchars($article['category_name']) ?>">
    <?php endif; ?>
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= $article_url ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= $article_url ?>">
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta property="og:image" content="<?= $share_image ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= epic_setting('site_name') ?>">
    <meta property="article:published_time" content="<?= $article['iso_date'] ?>T<?= date('H:i:s', strtotime($article['published_at'])) ?>Z">
    <meta property="article:author" content="<?= htmlspecialchars($article['author_name']) ?>">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= $article_url ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="twitter:image" content="<?= $share_image ?>">
    <meta name="twitter:creator" content="@<?= epic_setting('twitter_handle', 'epichub') ?>">
    
    <!-- LinkedIn -->
    <meta property="og:image:alt" content="<?= htmlspecialchars($article['title']) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= epic_url('favicon.ico') ?>">
    
    <!-- Fonts -->
    <!-- Google Fonts removed to eliminate external dependency -->
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= epic_url('themes/modern/blog/blog.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/blog/single.css') ?>">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "<?= htmlspecialchars($article['title']) ?>",
        "description": "<?= htmlspecialchars($page_description) ?>",
        "image": "<?= $share_image ?>",
        "author": {
            "@type": "Person",
            "name": "<?= htmlspecialchars($article['author_name']) ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "<?= epic_setting('site_name') ?>",
            "logo": {
                "@type": "ImageObject",
                "url": "<?= epic_url('uploads/logos/' . epic_setting('site_logo', 'logo.png')) ?>"
            }
        },
        "datePublished": "<?= $article['iso_date'] ?>T<?= date('H:i:s', strtotime($article['published_at'])) ?>Z",
        "dateModified": "<?= date('Y-m-d', strtotime($article['updated_at'])) ?>T<?= date('H:i:s', strtotime($article['updated_at'])) ?>Z",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?= $article_url ?>"
        }
    }
    </script>
</head>
<body>
    <!-- Header -->
    <header class="blog-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?= epic_url() ?>">
                        <?php if (epic_setting('site_logo')): ?>
                            <img src="<?= epic_url('uploads/logos/' . epic_setting('site_logo')) ?>" alt="<?= epic_setting('site_name') ?>">
                        <?php else: ?>
                            <span class="logo-text"><?= epic_setting('site_name', 'EPIC Hub') ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <a href="<?= epic_url() ?>" class="nav-link">Home</a>
                    <a href="<?= epic_url('blog') ?>" class="nav-link">Blog</a>
                    <a href="<?= epic_url('about') ?>" class="nav-link">About</a>
                    <a href="<?= epic_url('contact') ?>" class="nav-link">Contact</a>
                </nav>
                
                <div class="header-actions">
                    <?php if (epic_current_user()): ?>
                        <a href="<?= epic_url('dashboard') ?>" class="btn btn-primary">Dashboard</a>
                    <?php else: ?>
                        <a href="<?= epic_url('login') ?>" class="btn btn-secondary">Login</a>
                        <a href="<?= epic_url('register') ?>" class="btn btn-primary">Join Now</a>
                    <?php endif; ?>
                </div>
                
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <i data-feather="menu"></i>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?= epic_url() ?>" class="breadcrumb-item">
                    <i data-feather="home" width="16" height="16"></i>
                    Home
                </a>
                <span class="breadcrumb-separator">/</span>
                <a href="<?= epic_url('blog') ?>" class="breadcrumb-item">Blog</a>
                <?php if ($article['category_name']): ?>
                    <span class="breadcrumb-separator">/</span>
                    <a href="<?= epic_url('blog?category=' . $article['category_id']) ?>" class="breadcrumb-item">
                        <?= htmlspecialchars($article['category_name']) ?>
                    </a>
                <?php endif; ?>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current"><?= htmlspecialchars($article['title']) ?></span>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="article-main">
        <div class="container">
            <?php if ($show_premium_notice): ?>
                <!-- Premium Content Notice -->
                <div class="premium-notice">
                    <div class="premium-notice-content">
                        <div class="premium-icon">
                            <i data-feather="lock" width="48" height="48"></i>
                        </div>
                        <h2 class="premium-title">Premium Content</h2>
                        <p class="premium-description">
                            This article is available exclusively to EPIC and Premium members. 
                            Upgrade your account to access this content and unlock all premium features.
                        </p>
                        <div class="premium-actions">
                            <a href="<?= epic_url('upgrade') ?>" class="btn btn-primary">
                                <i data-feather="star" width="16" height="16"></i>
                                Upgrade to Premium
                            </a>
                            <a href="<?= epic_url('blog') ?>" class="btn btn-secondary">
                                <i data-feather="arrow-left" width="16" height="16"></i>
                                Back to Blog
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="article-layout">
                    <!-- Article Content -->
                    <article class="article-content">
                        <!-- Article Header -->
                        <header class="article-header">
                            <?php if ($article['category_name']): ?>
                                <div class="article-category">
                                    <a href="<?= epic_url('blog?category=' . $article['category_id']) ?>">
                                        <?= htmlspecialchars($article['category_name']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>
                            
                            <?php if ($article['excerpt']): ?>
                                <p class="article-excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                            <?php endif; ?>
                            
                            <div class="article-meta">
                                <div class="author-info">
                                    <?php if ($article['author_avatar']): ?>
                                        <img src="<?= epic_url('uploads/profiles/' . $article['author_avatar']) ?>" 
                                             alt="<?= htmlspecialchars($article['author_name']) ?>" 
                                             class="author-avatar">
                                    <?php else: ?>
                                        <div class="author-avatar-placeholder">
                                            <?= strtoupper(substr($article['author_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="author-details">
                                        <div class="author-name"><?= htmlspecialchars($article['author_name']) ?></div>
                                        <div class="article-date"><?= $article['formatted_date'] ?></div>
                                    </div>
                                </div>
                                
                                <div class="article-stats">
                                    <span class="stat-item">
                                        <i data-feather="eye" width="16" height="16"></i>
                                        <?= number_format($article['view_count']) ?> views
                                    </span>
                                    <span class="stat-item">
                                        <i data-feather="clock" width="16" height="16"></i>
                                        <?= $article['reading_time'] ?? 5 ?> min read
                                    </span>
                                </div>
                            </div>
                        </header>
                        
                        <!-- Featured Image -->
                        <?php if ($article['featured_image']): ?>
                            <div class="article-featured-image">
                                <img src="<?= epic_url('uploads/' . $article['featured_image']) ?>" 
                                     alt="<?= htmlspecialchars($article['title']) ?>">
                            </div>
                        <?php endif; ?>
                        
                        <!-- Article Body -->
                        <div class="article-body">
                            <?= $article['content'] ?>
                        </div>
                        
                        <!-- Article Footer -->
                        <footer class="article-footer">
                            <!-- Social Sharing -->
                            <div class="social-sharing">
                                <h3 class="sharing-title">Share this article</h3>
                                <div class="sharing-buttons">
                                    <button class="share-btn facebook" onclick="shareOnFacebook()" data-platform="facebook">
                                        <i data-feather="facebook" width="20" height="20"></i>
                                        Facebook
                                    </button>
                                    <button class="share-btn twitter" onclick="shareOnTwitter()" data-platform="twitter">
                                        <i data-feather="twitter" width="20" height="20"></i>
                                        Twitter
                                    </button>
                                    <button class="share-btn linkedin" onclick="shareOnLinkedIn()" data-platform="linkedin">
                                        <i data-feather="linkedin" width="20" height="20"></i>
                                        LinkedIn
                                    </button>
                                    <button class="share-btn whatsapp" onclick="shareOnWhatsApp()" data-platform="whatsapp">
                                        <i data-feather="message-circle" width="20" height="20"></i>
                                        WhatsApp
                                    </button>
                                    <button class="share-btn copy" onclick="copyArticleLink()" data-platform="copy_link">
                                        <i data-feather="link" width="20" height="20"></i>
                                        Copy Link
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Referral Sharing (for logged-in users) -->
                            <?php if ($user && $user['referral_code']): ?>
                                <div class="referral-sharing">
                                    <h3 class="sharing-title">Earn commissions by sharing</h3>
                                    <p class="referral-description">
                                        Share this article with your referral link and earn commissions when people join through your link.
                                    </p>
                                    <div class="referral-link-container">
                                        <input type="text" 
                                               value="<?= $referral_url ?>" 
                                               class="referral-link-input" 
                                               id="referralLink" 
                                               readonly>
                                        <button class="copy-referral-btn" onclick="copyReferralLink()">
                                            <i data-feather="copy" width="16" height="16"></i>
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Tags -->
                            <?php if ($article['seo_keywords']): ?>
                                <div class="article-tags">
                                    <h3 class="tags-title">Tags</h3>
                                    <div class="tags-list">
                                        <?php foreach (explode(',', $article['seo_keywords']) as $tag): ?>
                                            <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </footer>
                    </article>
                    
                    <!-- Sidebar -->
                    <aside class="article-sidebar">
                        <!-- Author Bio -->
                        <div class="sidebar-widget author-widget">
                            <h3 class="widget-title">About the Author</h3>
                            <div class="author-bio">
                                <?php if ($article['author_avatar']): ?>
                                    <img src="<?= epic_url('uploads/profiles/' . $article['author_avatar']) ?>" 
                                         alt="<?= htmlspecialchars($article['author_name']) ?>" 
                                         class="author-bio-avatar">
                                <?php else: ?>
                                    <div class="author-bio-avatar-placeholder">
                                        <?= strtoupper(substr($article['author_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="author-bio-content">
                                    <h4 class="author-bio-name"><?= htmlspecialchars($article['author_name']) ?></h4>
                                    <p class="author-bio-description">
                                        Expert in affiliate marketing and digital business strategies. 
                                        Helping entrepreneurs build successful online businesses.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Table of Contents (if content has headings) -->
                        <div class="sidebar-widget toc-widget" id="tocWidget" style="display: none;">
                            <h3 class="widget-title">Table of Contents</h3>
                            <div class="toc-list" id="tocList">
                                <!-- Generated by JavaScript -->
                            </div>
                        </div>
                        
                        <!-- Related Articles -->
                        <?php if (!empty($related_articles)): ?>
                            <div class="sidebar-widget related-widget">
                                <h3 class="widget-title">Related Articles</h3>
                                <div class="related-articles">
                                    <?php foreach ($related_articles as $related): ?>
                                        <article class="related-article">
                                            <?php if ($related['featured_image']): ?>
                                                <div class="related-image">
                                                    <a href="<?= epic_url('blog/' . $related['slug']) ?>">
                                                        <img src="<?= epic_url('uploads/' . $related['featured_image']) ?>" 
                                                             alt="<?= htmlspecialchars($related['title']) ?>">
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <div class="related-content">
                                                <h4 class="related-title">
                                                    <a href="<?= epic_url('blog/' . $related['slug']) ?>">
                                                        <?= htmlspecialchars($related['title']) ?>
                                                    </a>
                                                </h4>
                                                <div class="related-meta">
                                                    <span class="meta-item">
                                                        <i data-feather="calendar" width="14" height="14"></i>
                                                        <?= $related['formatted_date'] ?>
                                                    </span>
                                                    <span class="meta-item">
                                                        <i data-feather="eye" width="14" height="14"></i>
                                                        <?= number_format($related['view_count']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Newsletter Signup -->
                        <div class="sidebar-widget newsletter-widget">
                            <h3 class="widget-title">Stay Updated</h3>
                            <p class="newsletter-description">Get the latest articles and tips delivered to your inbox.</p>
                            <form class="newsletter-form" onsubmit="subscribeNewsletter(event)">
                                <div class="form-group">
                                    <input type="email" placeholder="Your email address" class="newsletter-input" required>
                                </div>
                                <button type="submit" class="newsletter-btn">
                                    <i data-feather="mail" width="16" height="16"></i>
                                    Subscribe
                                </button>
                            </form>
                        </div>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="blog-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4 class="footer-title"><?= epic_setting('site_name', 'EPIC Hub') ?></h4>
                    <p class="footer-description">
                        Your ultimate platform for affiliate marketing success. Join thousands of marketers growing their business with us.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i data-feather="facebook"></i></a>
                        <a href="#" class="social-link"><i data-feather="twitter"></i></a>
                        <a href="#" class="social-link"><i data-feather="instagram"></i></a>
                        <a href="#" class="social-link"><i data-feather="linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= epic_url() ?>">Home</a></li>
                        <li><a href="<?= epic_url('blog') ?>">Blog</a></li>
                        <li><a href="<?= epic_url('about') ?>">About</a></li>
                        <li><a href="<?= epic_url('contact') ?>">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Resources</h4>
                    <ul class="footer-links">
                        <li><a href="<?= epic_url('help') ?>">Help Center</a></li>
                        <li><a href="<?= epic_url('tutorials') ?>">Tutorials</a></li>
                        <li><a href="<?= epic_url('api') ?>">API Documentation</a></li>
                        <li><a href="<?= epic_url('support') ?>">Support</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Get Started</h4>
                    <p class="footer-description">Ready to start your affiliate marketing journey?</p>
                    <a href="<?= epic_url('register') ?>" class="btn btn-primary">Join Now</a>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="copyright">
                        &copy; <?= date('Y') ?> <?= epic_setting('site_name', 'EPIC Hub') ?>. All rights reserved.
                    </p>
                    <div class="footer-bottom-links">
                        <a href="<?= epic_url('privacy') ?>">Privacy Policy</a>
                        <a href="<?= epic_url('terms') ?>">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script>
        // Article data for sharing
        const articleData = {
            title: <?= json_encode($article['title']) ?>,
            url: <?= json_encode($article_url) ?>,
            referralUrl: <?= json_encode($referral_url) ?>,
            description: <?= json_encode($page_description) ?>,
            image: <?= json_encode($share_image) ?>,
            id: <?= $article['id'] ?>
        };
        
        // Initialize Feather Icons
        feather.replace();
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            const nav = document.querySelector('.main-nav');
            nav.classList.toggle('mobile-open');
        }
        
        // Social sharing functions
        function shareOnFacebook() {
            const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(articleData.url)}`;
            openShareWindow(url, 'facebook');
        }
        
        function shareOnTwitter() {
            const text = `${articleData.title} - ${articleData.description}`;
            const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(articleData.url)}`;
            openShareWindow(url, 'twitter');
        }
        
        function shareOnLinkedIn() {
            const url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(articleData.url)}`;
            openShareWindow(url, 'linkedin');
        }
        
        function shareOnWhatsApp() {
            const text = `${articleData.title} - ${articleData.description} ${articleData.url}`;
            const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
            openShareWindow(url, 'whatsapp');
        }
        
        function copyArticleLink() {
            navigator.clipboard.writeText(articleData.url).then(() => {
                showToast('Article link copied to clipboard!', 'success');
                trackSocialShare('copy_link');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = articleData.url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast('Article link copied to clipboard!', 'success');
                trackSocialShare('copy_link');
            });
        }
        
        function copyReferralLink() {
            const referralInput = document.getElementById('referralLink');
            navigator.clipboard.writeText(referralInput.value).then(() => {
                showToast('Referral link copied to clipboard!', 'success');
            }).catch(() => {
                referralInput.select();
                document.execCommand('copy');
                showToast('Referral link copied to clipboard!', 'success');
            });
        }
        
        function openShareWindow(url, platform) {
            const width = 600;
            const height = 400;
            const left = (window.innerWidth - width) / 2;
            const top = (window.innerHeight - height) / 2;
            
            window.open(
                url,
                'share',
                `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`
            );
            
            trackSocialShare(platform);
        }
        
        function trackSocialShare(platform) {
            // Send analytics data
            if (typeof gtag !== 'undefined') {
                gtag('event', 'share', {
                    'method': platform,
                    'content_type': 'article',
                    'item_id': articleData.id
                });
            }
            
            // Track in backend
            fetch('<?= epic_url('api/blog/track-share') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    article_id: articleData.id,
                    platform: platform
                })
            }).catch(error => {
                console.log('Share tracking failed:', error);
            });
        }
        
        // Newsletter subscription
        function subscribeNewsletter(event) {
            event.preventDefault();
            const email = event.target.querySelector('input[type="email"]').value;
            
            // Here you would typically send the email to your backend
            console.log('Newsletter subscription:', email);
            
            showToast('Thank you for subscribing! You will receive our latest updates.', 'success');
            event.target.reset();
        }
        
        // Toast notification
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
        
        // Generate Table of Contents
        function generateTOC() {
            const headings = document.querySelectorAll('.article-body h2, .article-body h3, .article-body h4');
            if (headings.length === 0) return;
            
            const tocWidget = document.getElementById('tocWidget');
            const tocList = document.getElementById('tocList');
            
            let tocHTML = '<ul class="toc-items">';
            
            headings.forEach((heading, index) => {
                const id = `heading-${index}`;
                heading.id = id;
                
                const level = heading.tagName.toLowerCase();
                const text = heading.textContent;
                
                tocHTML += `<li class="toc-item toc-${level}">
                    <a href="#${id}" class="toc-link">${text}</a>
                </li>`;
            });
            
            tocHTML += '</ul>';
            tocList.innerHTML = tocHTML;
            tocWidget.style.display = 'block';
            
            // Smooth scroll for TOC links
            document.querySelectorAll('.toc-link').forEach(link => {
                link.addEventListener('click', function(e) {
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
        }
        
        // Reading progress indicator
        function initReadingProgress() {
            const progressBar = document.createElement('div');
            progressBar.className = 'reading-progress';
            document.body.appendChild(progressBar);
            
            window.addEventListener('scroll', () => {
                const article = document.querySelector('.article-body');
                if (!article) return;
                
                const articleTop = article.offsetTop;
                const articleHeight = article.offsetHeight;
                const windowHeight = window.innerHeight;
                const scrollTop = window.pageYOffset;
                
                const progress = Math.max(0, Math.min(100, 
                    ((scrollTop - articleTop + windowHeight) / articleHeight) * 100
                ));
                
                progressBar.style.width = progress + '%';
            });
        }
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            generateTOC();
            initReadingProgress();
            
            // Track time spent reading
            let startTime = Date.now();
            let isVisible = true;
            
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    isVisible = false;
                } else {
                    isVisible = true;
                    startTime = Date.now();
                }
            });
            
            window.addEventListener('beforeunload', function() {
                if (isVisible) {
                    const timeSpent = Math.round((Date.now() - startTime) / 1000);
                    
                    // Send reading time data
                    navigator.sendBeacon('<?= epic_url('api/blog/track-reading-time') ?>', 
                        JSON.stringify({
                            article_id: articleData.id,
                            time_spent: timeSpent
                        })
                    );
                }
            });
        });
    </script>
    
    <!-- Google Analytics (if configured) -->
    <?php if (epic_setting('google_analytics_id')): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= epic_setting('google_analytics_id') ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?= epic_setting('google_analytics_id') ?>');
            
            // Track article view
            gtag('event', 'page_view', {
                'page_title': <?= json_encode($article['title']) ?>,
                'page_location': <?= json_encode($article_url) ?>,
                'content_group1': 'Blog Article',
                'content_group2': <?= json_encode($article['category_name'] ?? 'Uncategorized') ?>
            });
        </script>
    <?php endif; ?>
</body>
</html>