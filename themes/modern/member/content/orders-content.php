<?php
/**
 * EPIC Hub Member Orders Content
 * Konten halaman history order member
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Data sudah disiapkan di orders.php
?>

<!-- Orders Filter and Search - Modern Card Style -->
<div class="orders-filter-section">
    <div class="filter-search-card">
        <div class="filter-card-header">
            <div class="filter-title">
                <i data-feather="filter" width="20" height="20"></i>
                <span>Filter & Pencarian</span>
            </div>
        </div>
        
        <div class="filter-card-body">
            <div class="search-section">
                <div class="search-input-container">
                    <i data-feather="search" width="18" height="18" class="search-icon"></i>
                    <input type="text" class="search-input-modern" placeholder="Cari berdasarkan nama produk atau nomor order..." id="orderSearch">
                </div>
            </div>
            
            <div class="filter-controls-modern">
                <div class="filter-tabs-modern">
                    <button class="filter-tab-modern active" data-filter="all">
                        <i data-feather="list" width="16" height="16"></i>
                        <span>Semua</span>
                    </button>
                    <button class="filter-tab-modern" data-filter="completed">
                        <i data-feather="check-circle" width="16" height="16"></i>
                        <span>Selesai</span>
                    </button>
                    <button class="filter-tab-modern" data-filter="pending">
                        <i data-feather="clock" width="16" height="16"></i>
                        <span>Pending</span>
                    </button>
                    <button class="filter-tab-modern" data-filter="cancelled">
                        <i data-feather="x-circle" width="16" height="16"></i>
                        <span>Dibatalkan</span>
                    </button>
                </div>
                
                <div class="sort-dropdown-modern">
                    <select class="sort-select-modern" id="orderSort">
                        <option value="date_desc">Terbaru</option>
                        <option value="date_asc">Terlama</option>
                        <option value="amount_desc">Harga Tertinggi</option>
                        <option value="amount_asc">Harga Terendah</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders List -->
<div class="orders-list-section">
    <?php if (!empty($orders)): ?>
        <div class="orders-grid" id="ordersContainer">
            <?php foreach ($orders as $order): ?>
                <div class="order-card" data-status="<?= $order['status'] ?>" data-search="<?= strtolower($order['product_name'] . ' ' . $order['order_number']) ?>">
                    <div class="order-card-header">
                        <div class="order-info">
                            <div class="order-number">
                                <i data-feather="hash" width="16" height="16"></i>
                                <span><?= $order['order_number'] ?></span>
                            </div>
                            <div class="order-date">
                                <i data-feather="calendar" width="16" height="16"></i>
                                <span><?= date('d M Y, H:i', strtotime($order['order_date'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="order-status">
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?php 
                                $status_text = [
                                    'completed' => 'Selesai',
                                    'pending' => 'Pending',
                                    'cancelled' => 'Dibatalkan',
                                    'failed' => 'Gagal'
                                ];
                                echo $status_text[$order['status']] ?? ucfirst($order['status']);
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-card-body">
                        <div class="product-info">
                            <div class="product-icon">
                                <?php 
                                $type_icons = [
                                    'course' => 'play-circle',
                                    'tools' => 'tool',
                                    'ebook' => 'book',
                                    'template' => 'file-text'
                                ];
                                ?>
                                <i data-feather="<?= $type_icons[$order['product_type']] ?? 'package' ?>" width="24" height="24"></i>
                            </div>
                            
                            <div class="product-details">
                                <h4 class="product-name"><?= htmlspecialchars($order['product_name']) ?></h4>
                                <div class="product-type">
                                    <span class="type-badge type-<?= $order['product_type'] ?>">
                                        <?= ucfirst($order['product_type']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pricing-info">
                            <div class="current-price">Rp <?= number_format($order['amount'], 0, ',', '.') ?></div>
                            <?php if ($order['discount'] > 0): ?>
                                <div class="original-price">Rp <?= number_format($order['original_amount'], 0, ',', '.') ?></div>
                                <div class="discount-badge">
                                    Hemat Rp <?= number_format($order['discount'], 0, ',', '.') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-card-footer">
                        <div class="payment-info">
                            <div class="payment-method">
                                <i data-feather="credit-card" width="14" height="14"></i>
                                <span>
                                    <?php 
                                    $payment_methods = [
                                        'bank_transfer' => 'Transfer Bank',
                                        'e_wallet' => 'E-Wallet',
                                        'credit_card' => 'Kartu Kredit',
                                        'debit_card' => 'Kartu Debit'
                                    ];
                                    echo $payment_methods[$order['payment_method']] ?? ucfirst($order['payment_method']);
                                    ?>
                                </span>
                            </div>
                            
                            <div class="payment-status">
                                <span class="payment-badge payment-<?= $order['payment_status'] ?>">
                                    <?php 
                                    $payment_status_text = [
                                        'paid' => 'Lunas',
                                        'pending' => 'Menunggu',
                                        'failed' => 'Gagal',
                                        'refunded' => 'Dikembalikan'
                                    ];
                                    echo $payment_status_text[$order['payment_status']] ?? ucfirst($order['payment_status']);
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <?php if ($order['status'] === 'completed' && $order['access_granted']): ?>
                                <a href="<?= epic_url('learn/product/' . $order['id']) ?>" class="btn btn-primary btn-sm">
                                    <i data-feather="play" width="14" height="14"></i>
                                    Akses Produk
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($order['invoice_url']): ?>
                                <a href="<?= $order['invoice_url'] ?>" class="btn btn-secondary btn-sm" target="_blank">
                                    <i data-feather="file-text" width="14" height="14"></i>
                                    Invoice
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($order['receipt_url']): ?>
                                <a href="<?= $order['receipt_url'] ?>" class="btn btn-secondary btn-sm" target="_blank">
                                    <i data-feather="download" width="14" height="14"></i>
                                    Receipt
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'pending'): ?>
                                <button class="btn btn-warning btn-sm" onclick="payOrder('<?= $order['order_number'] ?>')">
                                    <i data-feather="credit-card" width="14" height="14"></i>
                                    Bayar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="orders-empty-state">
            <div class="empty-state-icon">
                <i data-feather="shopping-cart" width="64" height="64"></i>
            </div>
            <div class="empty-state-content">
                <h3 class="empty-state-title">Belum Ada Pesanan</h3>
                <p class="empty-state-text">
                    Anda belum memiliki riwayat pesanan. Mulai jelajahi produk-produk menarik kami!
                </p>
                <div class="empty-state-actions">
                    <a href="<?= epic_url('dashboard/member/products') ?>" class="btn btn-primary">
                        <i data-feather="package" width="16" height="16"></i>
                        Lihat Produk
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Order Summary Stats -->
<?php if (!empty($orders)): ?>
    <div class="order-summary-section">
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-icon">
                    <i data-feather="shopping-bag" width="20" height="20"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-value"><?= $total_orders ?></div>
                    <div class="summary-label">Total Pesanan</div>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon summary-icon-success">
                    <i data-feather="check-circle" width="20" height="20"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-value"><?= $completed_orders ?></div>
                    <div class="summary-label">Selesai</div>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon summary-icon-warning">
                    <i data-feather="clock" width="20" height="20"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-value"><?= $pending_orders ?></div>
                    <div class="summary-label">Pending</div>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon summary-icon-info">
                    <i data-feather="dollar-sign" width="20" height="20"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-value">Rp <?= number_format($total_spent, 0, ',', '.') ?></div>
                    <div class="summary-label">Total Belanja</div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('orderSearch');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const sortSelect = document.getElementById('orderSort');
    const orderCards = document.querySelectorAll('.order-card');
    
    let currentFilter = 'all';
    let currentSort = 'date_desc';
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterAndSort();
        });
    }
    
    // Filter functionality
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            currentFilter = this.dataset.filter;
            
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            filterAndSort();
        });
    });
    
    // Sort functionality
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            currentSort = this.value;
            filterAndSort();
        });
    }
    
    function filterAndSort() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        let visibleCards = [];
        
        orderCards.forEach(card => {
            const status = card.dataset.status;
            const searchData = card.dataset.search;
            
            // Apply filter
            const matchesFilter = currentFilter === 'all' || status === currentFilter;
            const matchesSearch = !searchTerm || searchData.includes(searchTerm);
            
            if (matchesFilter && matchesSearch) {
                card.style.display = 'block';
                visibleCards.push(card);
            } else {
                card.style.display = 'none';
            }
        });
        
        // Apply sort
        const container = document.getElementById('ordersContainer');
        if (container && visibleCards.length > 0) {
            visibleCards.sort((a, b) => {
                const aDate = new Date(a.querySelector('.order-date span').textContent);
                const bDate = new Date(b.querySelector('.order-date span').textContent);
                const aAmount = parseInt(a.querySelector('.current-price').textContent.replace(/[^0-9]/g, ''));
                const bAmount = parseInt(b.querySelector('.current-price').textContent.replace(/[^0-9]/g, ''));
                
                switch (currentSort) {
                    case 'date_asc':
                        return aDate - bDate;
                    case 'date_desc':
                        return bDate - aDate;
                    case 'amount_asc':
                        return aAmount - bAmount;
                    case 'amount_desc':
                        return bAmount - aAmount;
                    default:
                        return bDate - aDate;
                }
            });
            
            // Reorder DOM elements
            visibleCards.forEach(card => {
                container.appendChild(card);
            });
        }
    }
});

// Payment function
function payOrder(orderNumber) {
    // Redirect to payment page or open payment modal
    window.location.href = `<?= epic_url('payment') ?>?order=${orderNumber}`;
}

// Toast notification function
function showToast(message, type = 'info') {
    // Implementation depends on your toast system
    console.log(`${type.toUpperCase()}: ${message}`);
}
</script>