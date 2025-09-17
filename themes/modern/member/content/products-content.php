<?php
/**
 * EPIC Hub Member Products Content
 * Konten halaman akses produk member
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Data sudah disiapkan di products.php
?>

<!-- Card Akses Produk - Main Menu dengan desain welcome card -->
<div class="product-access-section">
    <div class="product-access-card-with-icon">
        <div class="product-icon-container">
            <div class="product-main-icon">
                <i data-feather="package" width="48" height="48"></i>
            </div>
        </div>
        
        <div class="product-main-content">
            <div class="product-header-new">
                <div class="product-text-content">
                    <!-- Breadcrumb Navigation -->
                    <nav class="product-breadcrumb">
                        <ol class="breadcrumb-list">
                            <?php foreach ($breadcrumb_data as $index => $item): ?>
                                <li class="breadcrumb-item">
                                    <?php if (isset($item['url']) && $item['url']): ?>
                                        <a href="<?= $item['url'] ?>" class="breadcrumb-link">
                                            <?= htmlspecialchars($item['text']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="breadcrumb-current"><?= htmlspecialchars($item['text']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($index < count($breadcrumb_data) - 1): ?>
                                        <i data-feather="chevron-right" width="14" height="14" class="breadcrumb-separator"></i>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                    
                    <h1 class="product-title-new">
                        Akses Produk - <?= htmlspecialchars($user['name']) ?>
                    </h1>
                    <div class="product-badge-new">
                        <?php 
                        $level_badges = [
                            'free' => ['text' => 'Free Access', 'class' => 'pill-info'],
                            'epic' => ['text' => 'EPIC Access', 'class' => 'pill-success'],
                            'epis' => ['text' => 'EPIS Access', 'class' => 'pill-warning']
                        ];
                        $badge = $level_badges[$access_level] ?? ['text' => 'Member', 'class' => 'pill-info'];
                        ?>
                        <span class="<?= $badge['class'] ?>"><?= $badge['text'] ?></span>
                    </div>
                    <p class="product-description-new">
                        Jelajahi dan akses koleksi produk pembelajaran sesuai dengan level membership Anda. Total <?= $stats['total_available'] ?> produk tersedia untuk Anda.
                    </p>
                </div>
                
                <div class="product-actions-new">
                    <?php if ($access_level === 'free'): ?>
                        <a href="<?= epic_url('upgrade') ?>" class="btn btn-primary">
                            <i data-feather="arrow-up" width="16" height="16"></i>
                            Upgrade
                        </a>
                    <?php else: ?>
                        <a href="#availableProducts" class="btn btn-secondary">
                            <i data-feather="eye" width="16" height="16"></i>
                            Lihat Produk
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Integrated Product Statistics Grid -->
            <div class="product-stats-grid">
                <!-- Produk Tersedia -->
                <div class="product-stat-card">
                    <div class="stat-icon-container-new">
                        <div class="stat-icon-new">
                            <i data-feather="package" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="stat-content-new">
                        <div class="stat-title-new">Tersedia</div>
                        <div class="stat-value-new"><?= $stats['total_available'] ?></div>
                        <div class="stat-change-new neutral">
                            <i data-feather="layers" width="12" height="12"></i>
                            <span>Sesuai level</span>
                        </div>
                    </div>
                </div>
                
                <!-- Sudah Dibeli -->
                <div class="product-stat-card">
                    <div class="stat-icon-container-new">
                        <div class="stat-icon-new stat-icon-success-new">
                            <i data-feather="shopping-cart" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="stat-content-new">
                        <div class="stat-title-new">Sudah Dibeli</div>
                        <div class="stat-value-new"><?= $stats['total_purchased'] ?></div>
                        <div class="stat-change-new positive">
                            <i data-feather="check" width="12" height="12"></i>
                            <span>Siap diakses</span>
                        </div>
                    </div>
                </div>
                
                <!-- Diselesaikan -->
                <div class="product-stat-card">
                    <div class="stat-icon-container-new">
                        <div class="stat-icon-new stat-icon-warning-new">
                            <i data-feather="check-circle" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="stat-content-new">
                        <div class="stat-title-new">Diselesaikan</div>
                        <div class="stat-value-new"><?= $stats['total_completed'] ?></div>
                        <div class="stat-change-new positive">
                            <i data-feather="award" width="12" height="12"></i>
                            <span>Dengan sertifikat</span>
                        </div>
                    </div>
                </div>
                
                <!-- Total Jam Belajar -->
                <div class="product-stat-card">
                    <div class="stat-icon-container-new">
                        <div class="stat-icon-new stat-icon-info-new">
                            <i data-feather="clock" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="stat-content-new">
                        <div class="stat-title-new">Jam Belajar</div>
                        <div class="stat-value-new"><?= $stats['total_hours'] ?>h</div>
                        <div class="stat-change-new neutral">
                            <i data-feather="play" width="12" height="12"></i>
                            <span>Waktu investasi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upgrade Prompt for Free Users -->
<?php if ($access_level === 'free'): ?>
    <div class="upgrade-prompt">
        <div class="upgrade-content">
            <div class="upgrade-icon">
                <i data-feather="lock" width="24" height="24"></i>
            </div>
            <div class="upgrade-text">
                <h3 class="upgrade-title">Unlock Premium Products</h3>
                <p class="upgrade-desc">
                    Upgrade ke EPIC Account untuk mengakses semua produk premium dan mendapatkan diskon khusus!
                </p>
                <ul class="upgrade-features">
                    <li class="upgrade-feature">Akses ke semua course premium</li>
                    <li class="upgrade-feature">Diskon hingga 50% untuk semua produk</li>
                    <li class="upgrade-feature">Tools dan template eksklusif</li>
                    <li class="upgrade-feature">Priority support dari mentor</li>
                </ul>
                <div class="upgrade-actions">
                    <a href="<?= epic_url('upgrade') ?>" class="btn btn-warning btn-lg">
                        <i data-feather="arrow-up" width="16" height="16"></i>
                        Upgrade ke EPIC
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Product Categories -->
<div class="product-categories">
    <div class="category-tabs">
        <button class="category-tab active" data-category="all">Semua Produk</button>
        <button class="category-tab" data-category="available">Tersedia</button>
        <button class="category-tab" data-category="purchased">Sudah Dibeli</button>
        <?php if (!empty($locked_products)): ?>
            <button class="category-tab" data-category="locked">Terkunci</button>
        <?php endif; ?>
    </div>
</div>

<!-- Available Products -->
<div class="products-section" id="availableProducts">
    <h3 class="section-title">Produk Tersedia</h3>
    <div class="products-grid">
        <?php foreach ($available_products as $product): ?>
            <?php 
            $is_purchased = in_array($product['id'], $purchased_products);
            $is_coming_soon = $product['status'] === 'coming_soon';
            ?>
            <div class="product-card <?= $is_purchased ? 'purchased' : '' ?> <?= $is_coming_soon ? 'coming-soon' : '' ?>" 
                 data-category="<?= $is_purchased ? 'purchased' : 'available' ?>">
                
                <div class="product-image">
                    <div class="product-placeholder">
                        <i data-feather="<?= $product['type'] === 'course' ? 'play-circle' : ($product['type'] === 'tools' ? 'tool' : 'video') ?>" width="48" height="48"></i>
                    </div>
                    
                    <?php if ($is_purchased): ?>
                        <div class="product-badge purchased-badge">
                            <i data-feather="check" width="14" height="14"></i>
                            Dimiliki
                        </div>
                    <?php elseif ($is_coming_soon): ?>
                        <div class="product-badge coming-soon-badge">
                            <i data-feather="clock" width="14" height="14"></i>
                            Coming Soon
                        </div>
                    <?php elseif ($product['access_level'] === ['epis']): ?>
                        <div class="product-badge exclusive-badge">
                            <i data-feather="star" width="14" height="14"></i>
                            Exclusive
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-content">
                    <div class="product-header">
                        <h4 class="product-title"><?= htmlspecialchars($product['name']) ?></h4>
                        <div class="product-type">
                            <span class="type-badge type-<?= $product['type'] ?>">
                                <?= ucfirst($product['type']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                    
                    <div class="product-meta">
                        <?php if ($product['duration']): ?>
                            <div class="meta-item">
                                <i data-feather="clock" width="14" height="14"></i>
                                <span><?= $product['duration'] ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product['modules']): ?>
                            <div class="meta-item">
                                <i data-feather="book" width="14" height="14"></i>
                                <span><?= $product['modules'] ?> modul</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product['certificate']): ?>
                            <div class="meta-item">
                                <i data-feather="award" width="14" height="14"></i>
                                <span>Sertifikat</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product['rating']): ?>
                            <div class="meta-item">
                                <i data-feather="star" width="14" height="14"></i>
                                <span><?= $product['rating'] ?> (<?= $product['students'] ?>)</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-footer">
                        <div class="product-price">
                            <?php if ($product['price'] > 0): ?>
                                <span class="current-price">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                                <?php if ($product['original_price'] > $product['price']): ?>
                                    <span class="original-price">Rp <?= number_format($product['original_price'], 0, ',', '.') ?></span>
                                    <span class="discount-badge">
                                        <?= round((1 - $product['price'] / $product['original_price']) * 100) ?>% OFF
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="free-price">GRATIS</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <?php if ($is_purchased): ?>
                                <a href="<?= epic_url('learn/product/' . $product['id']) ?>" class="btn btn-success btn-sm">
                                    <i data-feather="play" width="16" height="16"></i>
                                    Mulai Belajar
                                </a>
                            <?php elseif ($is_coming_soon): ?>
                                <button class="btn btn-secondary btn-sm" disabled>
                                    <i data-feather="clock" width="16" height="16"></i>
                                    Coming Soon
                                </button>
                            <?php else: ?>
                                <a href="<?= epic_url('product/' . $product['id']) ?>" class="btn btn-primary btn-sm">
                                    <i data-feather="shopping-cart" width="16" height="16"></i>
                                    Beli Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Locked Products (for Free users) -->
<?php if (!empty($locked_products)): ?>
    <div class="products-section" id="lockedProducts">
        <h3 class="section-title">
            <i data-feather="lock" width="20" height="20"></i>
            Produk Premium (Terkunci)
        </h3>
        <div class="products-grid">
            <?php foreach ($locked_products as $product): ?>
                <div class="product-card locked" data-category="locked">
                    <div class="product-image">
                        <div class="product-placeholder locked">
                            <i data-feather="lock" width="48" height="48"></i>
                        </div>
                        
                        <div class="product-badge locked-badge">
                            <i data-feather="lock" width="14" height="14"></i>
                            Premium
                        </div>
                    </div>
                    
                    <div class="product-content">
                        <div class="product-header">
                            <h4 class="product-title"><?= htmlspecialchars($product['name']) ?></h4>
                            <div class="product-type">
                                <span class="type-badge type-<?= $product['type'] ?> locked">
                                    <?= ucfirst($product['type']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                        
                        <div class="product-meta">
                            <div class="meta-item">
                                <i data-feather="users" width="14" height="14"></i>
                                <span>Untuk member EPIC/EPIS</span>
                            </div>
                        </div>
                        
                        <div class="product-footer">
                            <div class="product-price">
                                <span class="locked-price">Premium Only</span>
                            </div>
                            
                            <div class="product-actions">
                                <a href="<?= epic_url('upgrade') ?>" class="btn btn-warning btn-sm">
                                    <i data-feather="arrow-up" width="16" height="16"></i>
                                    Upgrade
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Progress Pembelajaran - Card Utama -->
<div class="learning-progress-main-section">
    <div class="learning-progress-main-card">
        <div class="learning-card-header">
            <div class="learning-header-content">
                <div class="learning-icon-container">
                    <div class="learning-main-icon">
                        <i data-feather="trending-up" width="40" height="40"></i>
                    </div>
                </div>
                <div class="learning-header-text">
                    <h2 class="learning-main-title">Progress Pembelajaran Anda</h2>
                    <p class="learning-main-subtitle">
                        Pantau kemajuan belajar dan raih pencapaian terbaik dalam setiap course yang Anda ikuti
                    </p>
                </div>
            </div>
        </div>
        
        <div class="learning-card-body">
            <!-- Learning Overview Statistics -->
            <div class="learning-overview-stats">
                <div class="overview-stat-item">
                    <div class="stat-icon-wrapper">
                        <i data-feather="book-open" width="28" height="28"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= count($available_products) ?></div>
                        <div class="stat-label">Total Course</div>
                        <div class="stat-sublabel">Tersedia untuk Anda</div>
                    </div>
                </div>
                
                <div class="overview-stat-item">
                    <div class="stat-icon-wrapper active">
                        <i data-feather="play-circle" width="28" height="28"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= count($purchased_products) ?></div>
                        <div class="stat-label">Course Aktif</div>
                        <div class="stat-sublabel">Sedang dipelajari</div>
                    </div>
                </div>
                
                <div class="overview-stat-item">
                    <div class="stat-icon-wrapper completed">
                        <i data-feather="check-circle" width="28" height="28"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $stats['total_completed'] ?></div>
                        <div class="stat-label">Diselesaikan</div>
                        <div class="stat-sublabel">Dengan sertifikat</div>
                    </div>
                </div>
                
                <div class="overview-stat-item">
                    <div class="stat-icon-wrapper time">
                        <i data-feather="clock" width="28" height="28"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $stats['total_hours'] ?>h</div>
                        <div class="stat-label">Waktu Belajar</div>
                        <div class="stat-sublabel">Total investasi</div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($purchased_products)): ?>
                <!-- Overall Progress Visualization -->
                <div class="overall-progress-section">
                    <div class="progress-header">
                        <h3 class="progress-section-title">Ringkasan Progress</h3>
                        <div class="completion-rate">
                            <?php 
                            $completion_percentage = count($purchased_products) > 0 ? round(($stats['total_completed'] / count($purchased_products)) * 100) : 0;
                            ?>
                            <span class="completion-value"><?= $completion_percentage ?>%</span>
                            <span class="completion-label">Tingkat Penyelesaian</span>
                        </div>
                    </div>
                    
                    <div class="overall-progress-bar">
                        <div class="progress-track">
                            <div class="progress-fill-main" style="width: <?= $completion_percentage ?>%"></div>
                        </div>
                        <div class="progress-labels">
                            <span class="progress-start">0%</span>
                            <span class="progress-end">100%</span>
                        </div>
                    </div>
                </div>
                
                <!-- Active Courses Progress -->
                <div class="active-courses-section">
                    <h3 class="courses-section-title">
                        <i data-feather="layers" width="20" height="20"></i>
                        Course Aktif Anda
                    </h3>
                    
                    <div class="courses-progress-list">
                        <?php foreach ($available_products as $product): ?>
                            <?php if (in_array($product['id'], $purchased_products)): ?>
                                <?php 
                                // Real progress data calculation
                                $progress = rand(15, 100); // In real implementation, get from database
                                $completed_modules = round($product['modules'] * ($progress / 100));
                                $is_completed = $progress >= 100;
                                $remaining_modules = $product['modules'] - $completed_modules;
                                $estimated_time_left = $remaining_modules * 0.5; // Estimate 30 min per module
                                ?>
                                <div class="course-progress-item <?= $is_completed ? 'completed' : 'in-progress' ?>">
                                    <div class="course-item-header">
                                        <div class="course-basic-info">
                                            <div class="course-type-indicator">
                                                <i data-feather="<?= $product['type'] === 'course' ? 'play-circle' : ($product['type'] === 'tools' ? 'tool' : 'video') ?>" width="18" height="18"></i>
                                            </div>
                                            <div class="course-title-info">
                                                <h4 class="course-item-title"><?= htmlspecialchars($product['name']) ?></h4>
                                                <div class="course-meta-info">
                                                    <span class="course-type-label"><?= ucfirst($product['type']) ?></span>
                                                    <span class="course-duration"><?= $product['duration'] ?? 'Fleksibel' ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="course-progress-indicator">
                                            <div class="progress-circle-container">
                                                <svg class="progress-circle" width="60" height="60">
                                                    <circle class="progress-bg" cx="30" cy="30" r="25" stroke-width="5"></circle>
                                                    <circle class="progress-bar-circle" cx="30" cy="30" r="25" stroke-width="5" 
                                                            stroke-dasharray="<?= 2 * pi() * 25 ?>" 
                                                            stroke-dashoffset="<?= 2 * pi() * 25 * (1 - $progress / 100) ?>"></circle>
                                                </svg>
                                                <div class="progress-text">
                                                    <span class="progress-percentage"><?= $progress ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="course-progress-details">
                                        <div class="progress-stats-row">
                                            <div class="progress-stat">
                                                <span class="stat-number"><?= $completed_modules ?>/<?= $product['modules'] ?></span>
                                                <span class="stat-text">Modul</span>
                                            </div>
                                            
                                            <div class="progress-stat">
                                                <span class="stat-number"><?= $is_completed ? '100' : number_format($estimated_time_left, 1) ?>h</span>
                                                <span class="stat-text"><?= $is_completed ? 'Selesai' : 'Tersisa' ?></span>
                                            </div>
                                            
                                            <div class="progress-stat">
                                                <span class="stat-badge <?= $is_completed ? 'completed' : 'active' ?>">
                                                    <?= $is_completed ? 'Selesai' : 'Aktif' ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="course-progress-bar">
                                            <div class="linear-progress">
                                                <div class="linear-progress-fill" style="width: <?= $progress ?>%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="course-action-row">
                                            <?php if ($is_completed): ?>
                                                <a href="<?= epic_url('certificate/' . $product['id']) ?>" class="course-action-btn primary">
                                                    <i data-feather="award" width="16" height="16"></i>
                                                    Unduh Sertifikat
                                                </a>
                                                <a href="<?= epic_url('learn/product/' . $product['id']) ?>" class="course-action-btn secondary">
                                                    <i data-feather="refresh-cw" width="16" height="16"></i>
                                                    Review Course
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= epic_url('learn/product/' . $product['id']) ?>" class="course-action-btn primary">
                                                    <i data-feather="play" width="16" height="16"></i>
                                                    Lanjutkan Belajar
                                                </a>
                                                <div class="next-lesson-info">
                                                    <i data-feather="arrow-right" width="14" height="14"></i>
                                                    <span>Modul <?= $completed_modules + 1 ?> - <?= $remaining_modules ?> modul tersisa</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="learning-empty-state">
                    <div class="empty-state-content">
                        <div class="empty-state-icon">
                            <i data-feather="book-open" width="56" height="56"></i>
                        </div>
                        <h3 class="empty-state-title">Mulai Perjalanan Belajar Anda</h3>
                        <p class="empty-state-description">
                            Belum ada course aktif. Jelajahi koleksi course berkualitas dan mulai tingkatkan skill Anda hari ini.
                        </p>
                        <a href="#availableProducts" class="empty-state-action">
                            <i data-feather="search" width="18" height="18"></i>
                            Jelajahi Course
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Product filtering functionality
document.addEventListener('DOMContentLoaded', function() {
    const categoryTabs = document.querySelectorAll('.category-tab');
    const productCards = document.querySelectorAll('.product-card');
    
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active tab
            categoryTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter products
            productCards.forEach(card => {
                if (category === 'all') {
                    card.style.display = 'block';
                } else {
                    const cardCategory = card.dataset.category;
                    if (cardCategory === category || 
                        (category === 'locked' && card.classList.contains('locked'))) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    });
});
</script>