<?php
/**
 * EPIC Hub Admin Product Management Content
 * Konten halaman produk untuk layout global
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Extract variables dari layout data
extract($data ?? []);

// Get search parameters
$search = $_GET['cari'] ?? '';
$status_filter = $_GET['status'] ?? '';
?>

<!-- Alerts -->
<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i data-feather="check-circle" width="16" height="16"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">
        <i data-feather="alert-circle" width="16" height="16"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- Search and Filters -->
<div class="content-section">
    <div class="section-header">
        <h2 class="section-title">Search & Filter</h2>
    </div>
    
    <form method="get" class="search-form">
        <div class="search-grid">
            <div class="form-group">
                <label class="form-label">Search Products</label>
                <div class="input-group">
                    <span class="input-group-icon">
                        <i data-feather="search" width="16" height="16"></i>
                    </span>
                    <input type="text" class="form-input" name="cari" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search by name or description...">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="search" width="16" height="16"></i>
                    Search
                </button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="?" class="btn btn-secondary">
                        <i data-feather="x" width="16" height="16"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="search-info">
            <i data-feather="info" width="14" height="14"></i>
            <span><?= number_format($total_products ?? 0) ?> products found</span>
        </div>
    </form>
</div>

<!-- Products Grid -->
<div class="content-section">
    <?php if (empty($products)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-feather="package" width="48" height="48"></i>
            </div>
            <h3 class="empty-state-title">No Products Found</h3>
            <p class="empty-state-text">No products match your search criteria. Try adjusting your filters or add a new product.</p>
            <a href="<?= function_exists('epic_url') ? epic_url('admin/manage/product/add') : 'add' ?>" class="btn btn-primary">
                <i data-feather="plus" width="16" height="16"></i>
                Add First Product
            </a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card" onclick="handleProductCardClick(<?= $product['id'] ?>, '<?= htmlspecialchars($product['category'] ?? 'regular') ?>')" style="cursor: pointer;">
                    <!-- Admin Edit Icon - Positioned absolutely -->
                    <?php if (in_array($user['role'], ['admin', 'super_admin'])): ?>
                        <div class="product-edit-icon" onclick="event.stopPropagation(); window.location.href='<?= function_exists('epic_url') ? epic_url('admin/manage/product/edit/' . $product['id']) : 'edit/' . $product['id'] ?>'" title="Edit Product">
                            <i data-feather="edit-2" width="16" height="16"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <i data-feather="<?= $product['category'] === 'lms' ? 'book-open' : 'package' ?>" width="32" height="32"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-content">
                        <div class="product-badges">
                            <span class="badge <?= $product['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                <?= ucfirst($product['status']) ?>
                            </span>
                            <span class="badge badge-outline">
                                <i data-feather="<?= $product['category'] === 'lms' ? 'book-open' : 'package' ?>" width="12" height="12"></i>
                                <?= strtoupper($product['category'] ?? 'REGULAR') ?>
                            </span>
                        </div>
                        
                        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                        
                        <p class="product-description">
                            <?= htmlspecialchars(substr($product['description'] ?? '', 0, 120)) ?>
                            <?= strlen($product['description'] ?? '') > 120 ? '...' : '' ?>
                        </p>
                        
                        <div class="product-price">
                            Rp <?= number_format($product['price'] ?? 0, 0, ',', '.') ?>
                        </div>
                        
                        <!-- Click hint for admin -->
                        <?php if (in_array($user['role'], ['admin', 'super_admin'])): ?>
                            <div class="product-click-hint">
                                <i data-feather="eye" width="12" height="12"></i>
                                <span>Click to preview</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($user['role'] === 'super_admin'): ?>
                            <div class="product-actions" onclick="event.stopPropagation();">
                                <a href="<?= function_exists('epic_url') ? epic_url('admin/manage/product/edit/' . $product['id']) : 'edit/' . $product['id'] ?>" 
                                   class="btn btn-secondary btn-sm">
                                    <i data-feather="edit-2" width="14" height="14"></i>
                                    Edit
                                </a>
                                <?php if ($product['status'] === 'active'): ?>
                                    <a href="?action=deactivate&id=<?= $product['id'] ?>" 
                                       class="btn btn-warning btn-sm"
                                       onclick="return confirm('Deactivate this product?')">
                                        <i data-feather="pause" width="14" height="14"></i>
                                        Deactivate
                                    </a>
                                <?php else: ?>
                                    <a href="?action=activate&id=<?= $product['id'] ?>" 
                                       class="btn btn-success btn-sm"
                                       onclick="return confirm('Activate this product?')">
                                        <i data-feather="play" width="14" height="14"></i>
                                        Activate
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if (($pagination['total_pages'] ?? 0) > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination">
                    <?php
                    $query_params = $_GET;
                    $current_page = $pagination['current_page'];
                    $total_pages = $pagination['total_pages'];
                    
                    if ($total_pages > 10) {
                        if ($current_page <= 4) {
                            // Beginning pages
                            for ($i = 1; $i <= 5; $i++) {
                                $query_params['start'] = $i;
                                $active = ($i == $current_page) ? 'active' : '';
                                echo '<a href="?' . http_build_query($query_params) . '" class="pagination-link ' . $active . '">' . $i . '</a>';
                            }
                            echo '<span class="pagination-ellipsis">...</span>';
                            $query_params['start'] = $total_pages;
                            echo '<a href="?' . http_build_query($query_params) . '" class="pagination-link">' . $total_pages . '</a>';
                        } elseif ($current_page >= 5 && $current_page <= ($total_pages - 5)) {
                            // Middle pages
                            $query_params['start'] = 1;
                            echo '<a href="?' . http_build_query($query_params) . '" class="pagination-link">1</a>';
                            echo '<span class="pagination-ellipsis">...</span>';
                            
                            for ($i = ($current_page - 2); $i <= ($current_page + 2); $i++) {
                                $query_params['start'] = $i;
                                $active = ($i == $current_page) ? 'active' : '';
                                echo '<a href="?' . http_build_query($query_params) . '" class="pagination-link ' . $active . '">' . $i . '</a>';
                            }
                            
                            echo '<span class="pagination-ellipsis">...</span>';
                            $query_params['start'] = $total_pages;
                            echo '<a href="?' . http_build_query($query_params) . '" class="pagination-link">' . $total_pages . '</a>';
                        } else {
                            // End pages
                            $query_params['start'] = 1;
                            echo '<a href="?' . http_build_query($query_params) . '" class="pagination-link">1</a>';
                            echo '<span class="pagination-ellipsis">...</span>';
                            
                            for ($i = ($total_pages - 5); $i <= $total_pages; $i++) {
                                $query_params['start'] = $i;
                                $active = ($i == $current_page) ? 'active' : '';
                                echo '<a href="?' . http_build_query($query_params) . '" class="pagination-link ' . $active . '">' . $i . '</a>';
                            }
                        }
                    } else {
                        // Simple pagination for <= 10 pages
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $query_params['start'] = $i;
                            $active = ($i == $current_page) ? 'active' : '';
                            echo '<a href="?' . http_build_query($query_params) . '" class="pagination-link ' . $active . '">' . $i . '</a>';
                        }
                    }
                    ?>
                </div>
                
                <div class="pagination-info">
                    Showing <?= (($current_page - 1) * ($jmlperpage ?? 12)) + 1 ?> to <?= min($current_page * ($jmlperpage ?? 12), $total_products ?? 0) ?> 
                    of <?= number_format($total_products ?? 0) ?> products
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
/* Product Card Enhancements for Admin */
.product-card {
    position: relative;
    transition: all 0.3s ease;
    border: 1px solid var(--ink-600);
    border-radius: 12px;
    overflow: hidden;
    background: var(--surface-2);
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: var(--gold-400);
}

/* Edit Icon Styling */
.product-edit-icon {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 32px;
    height: 32px;
    background: var(--gradient-gold);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.product-edit-icon:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(207, 168, 78, 0.4);
}

.product-edit-icon i {
    color: var(--ink-900);
}

/* Click Hint Styling */
.product-click-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    padding: 4px 8px;
    background: var(--gradient-gold-subtle);
    border: 1px solid var(--gold-400);
    border-radius: 6px;
    font-size: 11px;
    color: var(--gold-400);
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-click-hint {
    opacity: 1;
}

.product-click-hint i {
    color: var(--gold-400);
}

/* Product Actions Enhancement */
.product-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-edit-icon {
        width: 28px;
        height: 28px;
        top: 8px;
        right: 8px;
    }
    
    .product-edit-icon i {
        width: 14px;
        height: 14px;
    }
    
    .product-click-hint {
        font-size: 10px;
        padding: 3px 6px;
    }
}
</style>

<script>
// Handle product card click for admin preview
function handleProductCardClick(productId, category) {
    // Check if user has admin access (this should be passed from PHP)
    const isAdmin = <?= json_encode(in_array($user['role'], ['admin', 'super_admin'])) ?>;
    
    if (!isAdmin) {
        return;
    }
    
    // Determine preview URL based on product category
    let previewUrl;
    
    if (category === 'lms') {
        // For LMS products, redirect to LMS product preview
        previewUrl = `<?= function_exists('epic_url') ? epic_url('admin/lms-products') : '/admin/lms-products' ?>?preview=${productId}`;
    } else {
        // For regular products, redirect to product preview or member area
        previewUrl = `<?= function_exists('epic_url') ? epic_url('dashboard/member/products') : '/dashboard/member/products' ?>?preview=${productId}`;
    }
    
    // Open preview in new tab
    window.open(previewUrl, '_blank');
}

// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Add hover effects for better UX
document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>