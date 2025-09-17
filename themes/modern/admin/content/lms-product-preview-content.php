<?php
/**
 * EPIC Hub Admin LMS Product Preview Content
 * Konten halaman preview produk LMS untuk admin
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Extract variables dari layout data
extract($data ?? []);
?>

<!-- Product Overview Section -->
<div class="content-section">
    <div class="section-header">
        <h2 class="section-title">Product Overview</h2>
        <div class="section-actions">
            <span class="badge <?= $product['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                <?= ucfirst($product['status']) ?>
            </span>
            <span class="badge badge-outline">
                <i data-feather="<?= $product['category'] === 'lms' ? 'book-open' : 'package' ?>" width="12" height="12"></i>
                <?= strtoupper($product['category'] ?? 'REGULAR') ?>
            </span>
        </div>
    </div>
    
    <div class="product-preview-grid">
        <!-- Product Image -->
        <div class="product-preview-image">
            <?php if (!empty($product['image'])): ?>
                <img src="/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     class="preview-image">
            <?php else: ?>
                <div class="preview-placeholder">
                    <i data-feather="<?= $product['category'] === 'lms' ? 'book-open' : 'package' ?>" width="64" height="64"></i>
                    <p>No Image Available</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Details -->
        <div class="product-preview-details">
            <h1 class="product-preview-title"><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="product-preview-price">
                <span class="price-label">Price:</span>
                <span class="price-value">Rp <?= number_format($product['price'] ?? 0, 0, ',', '.') ?></span>
            </div>
            
            <div class="product-preview-description">
                <h3>Description</h3>
                <p><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></p>
            </div>
            
            <?php if (!empty($product['short_description'])): ?>
                <div class="product-preview-short-desc">
                    <h3>Short Description</h3>
                    <p><?= nl2br(htmlspecialchars($product['short_description'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Product Statistics -->
<div class="content-section">
    <div class="section-header">
        <h2 class="section-title">Product Statistics</h2>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="shopping-cart" width="24" height="24"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['total_sales']) ?></div>
                <div class="stat-label">Total Sales</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="dollar-sign" width="24" height="24"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="users" width="24" height="24"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['active_users']) ?></div>
                <div class="stat-label">Active Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="book-open" width="24" height="24"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= count($modules) ?></div>
                <div class="stat-label">Total Modules</div>
            </div>
        </div>
    </div>
</div>

<!-- LMS Modules Section (if applicable) -->
<?php if ($product['category'] === 'lms' && !empty($modules)): ?>
<div class="content-section">
    <div class="section-header">
        <h2 class="section-title">Course Modules</h2>
        <div class="section-actions">
            <a href="<?= epic_url('admin/lms-products/modules/' . $product['id']) ?>" class="btn btn-primary btn-sm">
                <i data-feather="plus" width="14" height="14"></i>
                Manage Modules
            </a>
        </div>
    </div>
    
    <div class="modules-list">
        <?php foreach ($modules as $index => $module): ?>
            <div class="module-card">
                <div class="module-header">
                    <div class="module-number"><?= $index + 1 ?></div>
                    <div class="module-info">
                        <h4 class="module-title"><?= htmlspecialchars($module['title']) ?></h4>
                        <div class="module-meta">
                            <span class="badge <?= $module['status'] === 'published' ? 'badge-success' : 'badge-secondary' ?>">
                                <?= ucfirst($module['status']) ?>
                            </span>
                            <?php if (!empty($module['duration_minutes'])): ?>
                                <span class="module-duration">
                                    <i data-feather="clock" width="12" height="12"></i>
                                    <?= $module['duration_minutes'] ?> min
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($module['description'])): ?>
                    <div class="module-description">
                        <p><?= htmlspecialchars(substr($module['description'], 0, 150)) ?>
                        <?= strlen($module['description']) > 150 ? '...' : '' ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php elseif ($product['category'] === 'lms'): ?>
<div class="content-section">
    <div class="section-header">
        <h2 class="section-title">Course Modules</h2>
    </div>
    
    <div class="empty-state">
        <div class="empty-state-icon">
            <i data-feather="book-open" width="48" height="48"></i>
        </div>
        <h3 class="empty-state-title">No Modules Yet</h3>
        <p class="empty-state-text">This LMS product doesn't have any modules yet. Add modules to create the course content.</p>
        <a href="<?= epic_url('admin/lms-products/modules/' . $product['id']) ?>" class="btn btn-primary">
            <i data-feather="plus" width="16" height="16"></i>
            Add First Module
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Product Actions -->
<div class="content-section">
    <div class="section-header">
        <h2 class="section-title">Quick Actions</h2>
    </div>
    
    <div class="action-buttons">
        <a href="<?= epic_url('admin/manage/product/edit/' . $product['id']) ?>" class="btn btn-primary">
            <i data-feather="edit-2" width="16" height="16"></i>
            Edit Product
        </a>
        
        <?php if ($product['category'] === 'lms'): ?>
            <a href="<?= epic_url('admin/lms-products/modules/' . $product['id']) ?>" class="btn btn-secondary">
                <i data-feather="book-open" width="16" height="16"></i>
                Manage Modules
            </a>
        <?php endif; ?>
        
        <a href="<?= epic_url('dashboard/member/products?preview=' . $product['id']) ?>" class="btn btn-outline" target="_blank">
            <i data-feather="external-link" width="16" height="16"></i>
            View as Member
        </a>
        
        <a href="<?= epic_url('admin/manage/product') ?>" class="btn btn-secondary">
            <i data-feather="arrow-left" width="16" height="16"></i>
            Back to Products
        </a>
    </div>
</div>

<style>
/* Product Preview Styles */
.product-preview-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin-top: 1rem;
}

.product-preview-image {
    position: relative;
}

.preview-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid var(--ink-600);
}

.preview-placeholder {
    width: 100%;
    height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: var(--surface-3);
    border: 2px dashed var(--ink-600);
    border-radius: 12px;
    color: var(--ink-300);
}

.product-preview-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.product-preview-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--ink-100);
    margin: 0;
}

.product-preview-price {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.price-label {
    font-size: 0.875rem;
    color: var(--ink-300);
}

.price-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gold-400);
}

.product-preview-description h3,
.product-preview-short-desc h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--ink-100);
    margin: 0 0 0.5rem 0;
}

.product-preview-description p,
.product-preview-short-desc p {
    color: var(--ink-200);
    line-height: 1.6;
    margin: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    border-color: var(--gold-400);
    transform: translateY(-2px);
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: var(--gradient-gold-subtle);
    border-radius: 12px;
    color: var(--gold-400);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--ink-100);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--ink-300);
}

/* Modules List */
.modules-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1rem;
}

.module-card {
    padding: 1.5rem;
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.module-card:hover {
    border-color: var(--gold-400);
}

.module-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.module-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: var(--gradient-gold);
    color: var(--ink-900);
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.875rem;
}

.module-info {
    flex: 1;
}

.module-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--ink-100);
    margin: 0 0 0.5rem 0;
}

.module-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.module-duration {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: var(--ink-300);
}

.module-description {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--ink-700);
}

.module-description p {
    color: var(--ink-200);
    line-height: 1.5;
    margin: 0;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .product-preview-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn {
        justify-content: center;
    }
}
</style>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>