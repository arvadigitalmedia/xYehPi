<?php
/**
 * EPIC Hub Blog Index Page
 * Public blog listing with referral tracking
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Get current page for pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Get category filter
$category_id = $_GET['category'] ?? null;
$search = trim($_GET['search'] ?? '');

// Build query conditions
$where_conditions = ["a.status = 'published'", "a.visibility = 'public'"];
$params = [];

if ($category_id) {
    $where_conditions[] = "a.category_id = ?";
    $params[] = $category_id;
}

if ($search) {
    $where_conditions[] = "(a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
    $search_term = "%{$search}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = implode(' AND ', $where_conditions);

// Get articles
$articles = db()->select(
    "SELECT a.*, c.name as category_name, u.name as author_name,
            DATE_FORMAT(a.published_at, '%M %d, %Y') as formatted_date
     FROM " . TABLE_ARTICLES . " a
     LEFT JOIN " . TABLE_CATEGORIES . " c ON a.category_id = c.id
     LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
     WHERE {$where_clause}
     ORDER BY a.published_at DESC
     LIMIT {$limit} OFFSET {$offset}",
    $params
);

// Get total count for pagination
$total_articles = db()->selectValue(
    "SELECT COUNT(*) FROM " . TABLE_ARTICLES . " a WHERE {$where_clause}",
    $params
);

$total_pages = ceil($total_articles / $limit);

// Get categories for filter
$categories = db()->select(
    "SELECT c.*, COUNT(a.id) as article_count
     FROM " . TABLE_CATEGORIES . " c
     LEFT JOIN " . TABLE_ARTICLES . " a ON c.id = a.category_id AND a.status = 'published'
     WHERE c.status = 'active'
     GROUP BY c.id
     ORDER BY c.name"
);

// Get featured articles
$featured_articles = db()->select(
    "SELECT a.*, c.name as category_name, u.name as author_name,
            DATE_FORMAT(a.published_at, '%M %d, %Y') as formatted_date
     FROM " . TABLE_ARTICLES . " a
     LEFT JOIN " . TABLE_CATEGORIES . " c ON a.category_id = c.id
     LEFT JOIN " . TABLE_USERS . " u ON a.author_id = u.id
     WHERE a.status = 'published' AND a.visibility = 'public'
     ORDER BY a.view_count DESC, a.published_at DESC
     LIMIT 3"
);

// Track blog visit for referral purposes
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $referrer_code = $_GET['ref'];
    $referrer = db()->selectOne(
        "SELECT id FROM " . TABLE_USERS . " WHERE referral_code = ?",
        [$referrer_code]
    );
    
    if ($referrer) {
        // Store referrer info in session for later tracking
        $_SESSION['blog_referrer_id'] = $referrer['id'];
        $_SESSION['blog_referrer_source'] = 'blog_index';
        $_SESSION['blog_visit_time'] = time();
    }
}

// Page title and meta
$page_title = 'Blog - ' . epic_setting('site_name');
$page_description = 'Discover insights, tips, and strategies for affiliate marketing success. Learn from experts and grow your online business.';

if ($search) {
    $page_title = "Search results for '{$search}' - Blog";
}

if ($category_id) {
    $current_category = array_filter($categories, fn($cat) => $cat['id'] == $category_id);
    if ($current_category) {
        $current_category = reset($current_category);
        $page_title = $current_category['name'] . ' - Blog';
        $page_description = $current_category['description'] ?: $page_description;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="keywords" content="affiliate marketing, blog, tips, strategies, online business, EPIC Hub">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= epic_url('blog') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta property="og:image" content="<?= epic_url('uploads/logos/' . epic_setting('site_logo', 'default-blog.jpg')) ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= epic_url('blog') ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta property="twitter:image" content="<?= epic_url('uploads/logos/' . epic_setting('site_logo', 'default-blog.jpg')) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= epic_url('favicon.ico') ?>">
    
    <!-- Fonts -->
    <!-- Google Fonts removed to eliminate external dependency -->
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= epic_url('themes/modern/blog/blog.css') ?>">
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
                    <a href="<?= epic_url('blog') ?>" class="nav-link active">Blog</a>
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
    
    <!-- Hero Section -->
    <section class="blog-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">EPIC Hub Blog</h1>
                <p class="hero-subtitle">Discover insights, tips, and strategies for affiliate marketing success</p>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <form method="GET" action="<?= epic_url('blog') ?>" class="search-form">
                        <div class="search-input-group">
                            <i data-feather="search" class="search-icon"></i>
                            <input type="text" name="search" placeholder="Search articles..." 
                                   value="<?= htmlspecialchars($search) ?>" class="search-input">
                            <?php if ($category_id): ?>
                                <input type="hidden" name="category" value="<?= $category_id ?>">
                            <?php endif; ?>
                            <button type="submit" class="search-btn">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="blog-main">
        <div class="container">
            <div class="blog-layout">
                <!-- Sidebar -->
                <aside class="blog-sidebar">
                    <!-- Categories -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">Categories</h3>
                        <div class="category-list">
                            <a href="<?= epic_url('blog') ?>" class="category-item <?= !$category_id ? 'active' : '' ?>">
                                <span class="category-name">All Articles</span>
                                <span class="category-count"><?= $total_articles ?></span>
                            </a>
                            <?php foreach ($categories as $category): ?>
                                <a href="<?= epic_url('blog?category=' . $category['id']) ?>" 
                                   class="category-item <?= $category_id == $category['id'] ? 'active' : '' ?>">
                                    <span class="category-name"><?= htmlspecialchars($category['name']) ?></span>
                                    <span class="category-count"><?= $category['article_count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Featured Articles -->
                    <?php if (!empty($featured_articles)): ?>
                        <div class="sidebar-widget">
                            <h3 class="widget-title">Popular Articles</h3>
                            <div class="featured-articles">
                                <?php foreach ($featured_articles as $featured): ?>
                                    <article class="featured-article">
                                        <?php if ($featured['featured_image']): ?>
                                            <div class="featured-image">
                                                <a href="<?= epic_url('blog/' . $featured['slug']) ?>">
                                                    <img src="<?= epic_url('uploads/' . $featured['featured_image']) ?>" 
                                                         alt="<?= htmlspecialchars($featured['title']) ?>">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="featured-content">
                                            <h4 class="featured-title">
                                                <a href="<?= epic_url('blog/' . $featured['slug']) ?>">
                                                    <?= htmlspecialchars($featured['title']) ?>
                                                </a>
                                            </h4>
                                            <div class="featured-meta">
                                                <span class="meta-item">
                                                    <i data-feather="eye" width="14" height="14"></i>
                                                    <?= number_format($featured['view_count']) ?> views
                                                </span>
                                                <span class="meta-item">
                                                    <i data-feather="clock" width="14" height="14"></i>
                                                    <?= $featured['reading_time'] ?? 5 ?> min read
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
                
                <!-- Content Area -->
                <div class="blog-content">
                    <!-- Filter Bar -->
                    <?php if ($search || $category_id): ?>
                        <div class="filter-bar">
                            <div class="filter-info">
                                <?php if ($search): ?>
                                    <span class="filter-item">
                                        Search: "<?= htmlspecialchars($search) ?>"
                                        <a href="<?= epic_url('blog' . ($category_id ? '?category=' . $category_id : '')) ?>" class="remove-filter">
                                            <i data-feather="x" width="14" height="14"></i>
                                        </a>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($category_id): ?>
                                    <?php $current_cat = array_filter($categories, fn($cat) => $cat['id'] == $category_id); ?>
                                    <?php if ($current_cat): ?>
                                        <?php $current_cat = reset($current_cat); ?>
                                        <span class="filter-item">
                                            Category: <?= htmlspecialchars($current_cat['name']) ?>
                                            <a href="<?= epic_url('blog' . ($search ? '?search=' . urlencode($search) : '')) ?>" class="remove-filter">
                                                <i data-feather="x" width="14" height="14"></i>
                                            </a>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="results-count">
                                <?= number_format($total_articles) ?> articles found
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Articles Grid -->
                    <?php if (!empty($articles)): ?>
                        <div class="articles-grid">
                            <?php foreach ($articles as $article): ?>
                                <article class="article-card" onclick="trackArticleClick('<?= $article['slug'] ?>')">
                                    <?php if ($article['featured_image']): ?>
                                        <div class="article-image">
                                            <a href="<?= epic_url('blog/' . $article['slug']) ?>">
                                                <img src="<?= epic_url('uploads/' . $article['featured_image']) ?>" 
                                                     alt="<?= htmlspecialchars($article['title']) ?>">
                                            </a>
                                            <?php if ($article['category_name']): ?>
                                                <div class="article-category">
                                                    <a href="<?= epic_url('blog?category=' . $article['category_id']) ?>">
                                                        <?= htmlspecialchars($article['category_name']) ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="article-content">
                                        <h2 class="article-title">
                                            <a href="<?= epic_url('blog/' . $article['slug']) ?>">
                                                <?= htmlspecialchars($article['title']) ?>
                                            </a>
                                        </h2>
                                        
                                        <?php if ($article['excerpt']): ?>
                                            <p class="article-excerpt">
                                                <?= htmlspecialchars($article['excerpt']) ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="article-meta">
                                            <div class="meta-left">
                                                <span class="meta-item">
                                                    <i data-feather="user" width="14" height="14"></i>
                                                    <?= htmlspecialchars($article['author_name']) ?>
                                                </span>
                                                <span class="meta-item">
                                                    <i data-feather="calendar" width="14" height="14"></i>
                                                    <?= $article['formatted_date'] ?>
                                                </span>
                                            </div>
                                            
                                            <div class="meta-right">
                                                <span class="meta-item">
                                                    <i data-feather="eye" width="14" height="14"></i>
                                                    <?= number_format($article['view_count']) ?>
                                                </span>
                                                <span class="meta-item">
                                                    <i data-feather="clock" width="14" height="14"></i>
                                                    <?= $article['reading_time'] ?? 5 ?> min
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <a href="<?= epic_url('blog/' . $article['slug']) ?>" class="read-more">
                                            Read More
                                            <i data-feather="arrow-right" width="16" height="16"></i>
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="<?= epic_url('blog?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>" 
                                       class="pagination-btn">
                                        <i data-feather="chevron-left" width="16" height="16"></i>
                                        Previous
                                    </a>
                                <?php endif; ?>
                                
                                <div class="pagination-numbers">
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <a href="<?= epic_url('blog?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>" 
                                           class="pagination-number <?= $i === $page ? 'active' : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="<?= epic_url('blog?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>" 
                                       class="pagination-btn">
                                        Next
                                        <i data-feather="chevron-right" width="16" height="16"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i data-feather="file-text" width="64" height="64"></i>
                            </div>
                            <h3 class="empty-title">No Articles Found</h3>
                            <p class="empty-description">
                                <?php if ($search): ?>
                                    No articles match your search criteria. Try different keywords or browse all articles.
                                <?php elseif ($category_id): ?>
                                    No articles found in this category. Check out other categories or browse all articles.
                                <?php else: ?>
                                    No articles have been published yet. Check back soon for new content!
                                <?php endif; ?>
                            </p>
                            <a href="<?= epic_url('blog') ?>" class="btn btn-primary">Browse All Articles</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
                    <h4 class="footer-title">Categories</h4>
                    <ul class="footer-links">
                        <?php foreach (array_slice($categories, 0, 5) as $category): ?>
                            <li>
                                <a href="<?= epic_url('blog?category=' . $category['id']) ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
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
        // Initialize Feather Icons
        feather.replace();
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            const nav = document.querySelector('.main-nav');
            nav.classList.toggle('mobile-open');
        }
        
        // Track article clicks for analytics
        function trackArticleClick(slug) {
            // Send analytics data
            if (typeof gtag !== 'undefined') {
                gtag('event', 'article_click', {
                    'article_slug': slug,
                    'source': 'blog_index'
                });
            }
        }
        
        // Newsletter subscription
        function subscribeNewsletter(event) {
            event.preventDefault();
            const email = event.target.querySelector('input[type="email"]').value;
            
            // Here you would typically send the email to your backend
            console.log('Newsletter subscription:', email);
            
            // Show success message
            alert('Thank you for subscribing! You will receive our latest updates.');
            event.target.reset();
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
        
        // Search form enhancement
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const searchInput = this.querySelector('input[name="search"]');
                if (!searchInput.value.trim()) {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        }
    </script>
    
    <!-- Google Analytics (if configured) -->
    <?php if (epic_setting('google_analytics_id')): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= epic_setting('google_analytics_id') ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?= epic_setting('google_analytics_id') ?>');
        </script>
    <?php endif; ?>
</body>
</html>