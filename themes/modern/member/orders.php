<?php
/**
 * EPIC Hub Member Orders Page
 * Halaman history order untuk member area
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout system
require_once __DIR__ . '/components/page-layout.php';

$user = $user ?? epic_current_user();
$access_level = $access_level ?? epic_get_member_access_level($user);

// Get orders data (dummy data for now)
$orders = [
    [
        'id' => 1,
        'order_number' => 'ORD-2024-001',
        'product_name' => 'Digital Marketing Mastery',
        'product_type' => 'course',
        'amount' => 299000,
        'original_amount' => 499000,
        'discount' => 200000,
        'status' => 'completed',
        'payment_method' => 'bank_transfer',
        'payment_status' => 'paid',
        'order_date' => '2024-01-15 10:30:00',
        'paid_date' => '2024-01-15 14:20:00',
        'access_granted' => true,
        'invoice_url' => '#',
        'receipt_url' => '#'
    ],
    [
        'id' => 2,
        'order_number' => 'ORD-2024-002',
        'product_name' => 'Advanced SEO Strategies',
        'product_type' => 'course',
        'amount' => 199000,
        'original_amount' => 399000,
        'discount' => 200000,
        'status' => 'completed',
        'payment_method' => 'e_wallet',
        'payment_status' => 'paid',
        'order_date' => '2024-01-10 09:15:00',
        'paid_date' => '2024-01-10 09:45:00',
        'access_granted' => true,
        'invoice_url' => '#',
        'receipt_url' => '#'
    ],
    [
        'id' => 3,
        'order_number' => 'ORD-2024-003',
        'product_name' => 'Social Media Marketing Tools',
        'product_type' => 'tools',
        'amount' => 149000,
        'original_amount' => 249000,
        'discount' => 100000,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
        'payment_status' => 'pending',
        'order_date' => '2024-01-12 16:45:00',
        'paid_date' => null,
        'access_granted' => false,
        'invoice_url' => '#',
        'receipt_url' => null
    ],
    [
        'id' => 4,
        'order_number' => 'ORD-2024-004',
        'product_name' => 'Email Marketing Automation',
        'product_type' => 'course',
        'amount' => 99000,
        'original_amount' => 199000,
        'discount' => 100000,
        'status' => 'cancelled',
        'payment_method' => 'credit_card',
        'payment_status' => 'failed',
        'order_date' => '2024-01-08 11:20:00',
        'paid_date' => null,
        'access_granted' => false,
        'invoice_url' => '#',
        'receipt_url' => null
    ]
];

// Calculate statistics
$total_orders = count($orders);
$completed_orders = count(array_filter($orders, fn($o) => $o['status'] === 'completed'));
$pending_orders = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$total_spent = array_sum(array_map(fn($o) => $o['status'] === 'completed' ? $o['amount'] : 0, $orders));
$total_saved = array_sum(array_map(fn($o) => $o['status'] === 'completed' ? $o['discount'] : 0, $orders));

$stats = [
    'total_orders' => $total_orders,
    'completed_orders' => $completed_orders,
    'pending_orders' => $pending_orders,
    'total_spent' => $total_spent,
    'total_saved' => $total_saved
];
?>

<?php
// Render consistent page header
render_page_header([
    'title' => 'History Order',
    'subtitle' => 'Kelola dan pantau semua pembelian produk Anda',
    'breadcrumb' => [
        ['text' => 'Dashboard', 'url' => epic_url('dashboard/member')],
        ['text' => 'Orders']
    ],
    'actions' => [
        [
            'text' => 'Export Orders',
            'url' => '#',
            'class' => 'btn-secondary',
            'icon' => 'download',
            'onclick' => 'exportOrders()'
        ]
    ]
]);
?>

<?php
// Render consistent statistics section
$order_stats = [
    [
        'title' => 'Total Orders',
        'value' => $stats['total_orders'],
        'icon' => 'shopping-cart',
        'change' => [
            'type' => 'neutral',
            'text' => 'Semua waktu'
        ]
    ],
    [
        'title' => 'Completed',
        'value' => $stats['completed_orders'],
        'icon' => 'check-circle',
        'variant' => 'success',
        'change' => [
            'type' => 'positive',
            'text' => 'Siap diakses'
        ]
    ],
    [
        'title' => 'Pending',
        'value' => $stats['pending_orders'],
        'icon' => 'clock',
        'variant' => 'warning',
        'change' => [
            'type' => 'neutral',
            'text' => 'Menunggu pembayaran'
        ]
    ],
    [
        'title' => 'Total Spent',
        'value' => 'Rp ' . number_format($stats['total_spent'], 0, ',', '.'),
        'icon' => 'credit-card',
        'variant' => 'info',
        'change' => [
            'type' => 'positive',
            'text' => 'Hemat Rp ' . number_format($stats['total_saved'], 0, ',', '.')
        ]
    ]
];
render_stats_section($order_stats);

// Include content
require_once __DIR__ . '/content/orders-content.php';
?>

<!-- Legacy content cleanup -->
            
            <div class="filter-buttons">
                <button class="filter-btn active" data-status="all">Semua</button>
                <button class="filter-btn" data-status="completed">Completed</button>
                <button class="filter-btn" data-status="pending">Pending</button>
                <button class="filter-btn" data-status="cancelled">Cancelled</button>
            </div>
            
            <div class="date-filter">
                <select class="form-select" id="dateFilter">
                    <option value="all">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Orders List -->
<div class="orders-container">
    <?php foreach ($orders as $order): ?>
        <div class="order-card" data-status="<?= $order['status'] ?>">
            <div class="order-header">
                <div class="order-info">
                    <div class="order-number">
                        <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                    </div>
                    <div class="order-date">
                        <?= date('d M Y, H:i', strtotime($order['order_date'])) ?>
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
                        echo $status_text[$order['status']] ?? $order['status'];
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="order-content">
                <div class="product-info">
                    <div class="product-icon">
                        <i data-feather="<?= $order['product_type'] === 'course' ? 'play-circle' : 'tool' ?>" width="24" height="24"></i>
                    </div>
                    
                    <div class="product-details">
                        <h4 class="product-name"><?= htmlspecialchars($order['product_name']) ?></h4>
                        <div class="product-meta">
                            <span class="product-type"><?= ucfirst($order['product_type']) ?></span>
                            <?php if ($order['access_granted']): ?>
                                <span class="access-status granted">
                                    <i data-feather="check" width="14" height="14"></i>
                                    Akses Diberikan
                                </span>
                            <?php else: ?>
                                <span class="access-status pending">
                                    <i data-feather="clock" width="14" height="14"></i>
                                    Menunggu Pembayaran
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="order-pricing">
                    <div class="pricing-details">
                        <?php if ($order['discount'] > 0): ?>
                            <div class="original-price">Rp <?= number_format($order['original_amount'], 0, ',', '.') ?></div>
                            <div class="discount-amount">-Rp <?= number_format($order['discount'], 0, ',', '.') ?></div>
                        <?php endif; ?>
                        <div class="final-price">Rp <?= number_format($order['amount'], 0, ',', '.') ?></div>
                    </div>
                    
                    <div class="payment-info">
                        <div class="payment-method">
                            <i data-feather="<?= $order['payment_method'] === 'bank_transfer' ? 'credit-card' : ($order['payment_method'] === 'e_wallet' ? 'smartphone' : 'credit-card') ?>" width="14" height="14"></i>
                            <?php 
                            $payment_methods = [
                                'bank_transfer' => 'Transfer Bank',
                                'e_wallet' => 'E-Wallet',
                                'credit_card' => 'Kartu Kredit'
                            ];
                            echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                            ?>
                        </div>
                        
                        <div class="payment-status payment-<?= $order['payment_status'] ?>">
                            <?php 
                            $payment_status_text = [
                                'paid' => 'Lunas',
                                'pending' => 'Menunggu',
                                'failed' => 'Gagal'
                            ];
                            echo $payment_status_text[$order['payment_status']] ?? $order['payment_status'];
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="order-footer">
                <div class="order-actions">
                    <?php if ($order['status'] === 'completed' && $order['access_granted']): ?>
                        <a href="<?= epic_url('learn/product/' . $order['id']) ?>" class="btn btn-success btn-sm">
                            <i data-feather="play" width="16" height="16"></i>
                            Akses Produk
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($order['status'] === 'pending'): ?>
                        <a href="<?= epic_url('payment/' . $order['id']) ?>" class="btn btn-warning btn-sm">
                            <i data-feather="credit-card" width="16" height="16"></i>
                            Bayar Sekarang
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($order['invoice_url']): ?>
                        <a href="<?= $order['invoice_url'] ?>" class="btn btn-secondary btn-sm" target="_blank">
                            <i data-feather="file-text" width="16" height="16"></i>
                            Invoice
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($order['receipt_url']): ?>
                        <a href="<?= $order['receipt_url'] ?>" class="btn btn-secondary btn-sm" target="_blank">
                            <i data-feather="download" width="16" height="16"></i>
                            Receipt
                        </a>
                    <?php endif; ?>
                    
                    <button class="btn btn-secondary btn-sm" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                        <i data-feather="eye" width="16" height="16"></i>
                        Detail
                    </button>
                </div>
                
                <?php if ($order['paid_date']): ?>
                    <div class="paid-date">
                        <i data-feather="check-circle" width="14" height="14"></i>
                        Dibayar: <?= date('d M Y, H:i', strtotime($order['paid_date'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Empty State -->
<div class="empty-state" id="emptyState" style="display: none;">
    <div class="empty-state-icon">
        <i data-feather="shopping-cart" width="48" height="48"></i>
    </div>
    <h4 class="empty-state-title">Tidak Ada Order</h4>
    <p class="empty-state-text">
        Belum ada order yang sesuai dengan filter yang dipilih.
    </p>
    <a href="<?= epic_url('dashboard/member/products') ?>" class="btn btn-primary">
        <i data-feather="package" width="16" height="16"></i>
        Lihat Produk
    </a>
</div>

<!-- Order Details Modal -->
<div class="modal" id="orderDetailsModal" style="display: none;">
    <div class="modal-overlay" onclick="closeOrderDetailsModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Detail Order</h3>
            <button class="modal-close" onclick="closeOrderDetailsModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <div class="modal-body" id="orderDetailsContent">
            <!-- Order details will be loaded here -->
        </div>
    </div>
</div>

<style>
/* Page Header - Consistent with Member Area */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.15);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    color: white;
    margin-bottom: 0.5rem;
    letter-spacing: -0.025em;
}

.page-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    margin: 0;
}

/* Filters Section - Modern Card Style */
.filters-section {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #f1f5f9;
    margin-bottom: 2rem;
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 250px;
}

.search-input-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}

.search-input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 2.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.875rem;
    background: #fafbfc;
    transition: all 0.2s ease;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.filter-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 0.625rem 1.25rem;
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
}

.filter-btn:hover {
    border-color: #667eea;
    color: #667eea;
    background: #f8faff;
}

.filter-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.25);
}

.date-filter {
    min-width: 150px;
}

/* Orders Container */
.orders-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Order Card - Modern Card Style */
.order-card {
    background: var(--card) !important;
    border: 1px solid var(--border) !important;
    border-radius: var(--epic-radius-xl) !important;
    padding: 0 !important;
    transition: var(--epic-transition-normal) !important;
    box-shadow: var(--epic-shadow-sm) !important;
    overflow: hidden !important;
}

.order-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: var(--epic-shadow-md) !important;
    border-color: var(--gold-600) !important;
}

.order-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: var(--epic-space-xl) !important;
    margin-bottom: 0 !important;
    border-bottom: 1px solid var(--border) !important;
    background: var(--bg-2) !important;
}

.order-number {
    font-size: var(--epic-text-base) !important;
    color: var(--tx) !important;
    margin-bottom: var(--epic-space-xs) !important;
    font-weight: var(--epic-font-semibold) !important;
}

.order-date {
    font-size: var(--epic-text-sm) !important;
    color: var(--tx-2) !important;
    font-weight: var(--epic-font-medium) !important;
}

.status-badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: var(--epic-space-sm) var(--epic-space-md) !important;
    border-radius: var(--epic-radius-full) !important;
    font-size: var(--epic-text-xs) !important;
    font-weight: var(--epic-font-semibold) !important;
    text-transform: uppercase !important;
    letter-spacing: 0.025em !important;
}

.status-completed {
    background: rgba(34, 197, 94, 0.1) !important;
    color: var(--success) !important;
    border: 1px solid rgba(34, 197, 94, 0.2) !important;
}

.status-pending {
    background: rgba(245, 158, 11, 0.1) !important;
    color: var(--warning) !important;
    border: 1px solid rgba(245, 158, 11, 0.2) !important;
}

.status-cancelled {
    background: rgba(239, 68, 68, 0.1) !important;
    color: var(--danger) !important;
    border: 1px solid rgba(239, 68, 68, 0.2) !important;
}

.status-failed {
    background: rgba(239, 68, 68, 0.1) !important;
    color: var(--danger) !important;
    border: 1px solid rgba(239, 68, 68, 0.2) !important;
}

.order-content {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: var(--epic-space-xl) !important;
    margin-bottom: 0 !important;
}

.product-info {
    display: flex !important;
    align-items: center !important;
    gap: var(--epic-space-lg) !important;
    flex: 1 !important;
}

.product-icon {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 48px !important;
    height: 48px !important;
    background: var(--bg-2) !important;
    color: var(--tx-2) !important;
    border-radius: var(--epic-radius-lg) !important;
    flex-shrink: 0 !important;
    border: 1px solid var(--border) !important;
}

.product-details {
    flex: 1 !important;
    min-width: 0 !important;
}

.product-name {
    font-size: var(--epic-text-lg) !important;
    font-weight: var(--epic-font-semibold) !important;
    color: var(--tx) !important;
    margin: 0 0 var(--epic-space-sm) 0 !important;
    letter-spacing: -0.025em !important;
}

.product-meta {
    display: flex !important;
    gap: var(--epic-space-lg) !important;
    align-items: center !important;
    flex-wrap: wrap !important;
}

.product-type {
    font-size: var(--epic-text-sm) !important;
    color: var(--tx-2) !important;
    font-weight: var(--epic-font-medium) !important;
}

.access-status {
    display: flex !important;
    align-items: center !important;
    gap: var(--epic-space-xs) !important;
    font-size: var(--epic-text-sm) !important;
    font-weight: var(--epic-font-medium) !important;
}

.access-status.granted {
    color: var(--success) !important;
}

.access-status.pending {
    color: var(--warning) !important;
}

.order-pricing {
    text-align: right !important;
    flex-shrink: 0 !important;
}

.pricing-details {
    margin-bottom: var(--epic-space-sm) !important;
}

.original-price {
    font-size: var(--epic-text-sm) !important;
    color: var(--tx-3) !important;
    text-decoration: line-through !important;
    margin-bottom: var(--epic-space-xs) !important;
}

.discount-amount {
    font-size: var(--epic-text-sm) !important;
    color: var(--danger) !important;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.final-price {
    font-size: var(--epic-text-xl) !important;
    font-weight: var(--epic-font-bold) !important;
    color: var(--tx) !important;
    letter-spacing: -0.025em !important;
}

.payment-info {
    display: flex !important;
    flex-direction: column !important;
    gap: var(--epic-space-xs) !important;
    align-items: flex-end !important;
}

.payment-method {
    display: flex !important;
    align-items: center !important;
    gap: var(--epic-space-xs) !important;
    font-size: var(--epic-text-sm) !important;
    color: var(--tx-2) !important;
}

.payment-status {
    font-size: var(--epic-text-sm) !important;
    font-weight: var(--epic-font-medium) !important;
}

.payment-paid {
    color: var(--success) !important;
}

.payment-pending {
    color: var(--warning) !important;
}

.payment-failed {
    color: var(--danger) !important;
}

.order-footer {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: var(--epic-space-lg) var(--epic-space-xl) !important;
    background: var(--bg-2) !important;
    border-top: 1px solid var(--border) !important;
}

.order-actions {
    display: flex !important;
    gap: var(--epic-space-sm) !important;
    flex-wrap: wrap !important;
}

.paid-date {
    display: flex !important;
    align-items: center !important;
    gap: var(--epic-space-xs) !important;
    font-size: var(--epic-text-sm) !important;
    color: var(--success) !important;
    font-weight: var(--epic-font-semibold) !important;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background: var(--card) !important;
    border-radius: var(--epic-radius-xl) !important;
    box-shadow: var(--epic-shadow-lg) !important;
    width: 90% !important;
    max-width: 600px !important;
    max-height: 90vh !important;
    overflow-y: auto !important;
    position: relative !important;
    z-index: 1001 !important;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .filters-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .order-content {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .order-pricing {
        text-align: left;
    }
    
    .payment-info {
        align-items: flex-start;
    }
    
    .order-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .order-actions {
        justify-content: stretch;
    }
    
    .order-actions .btn {
        flex: 1;
        justify-content: center;
    }
    
    .product-info {
        flex-direction: column;
        text-align: center;
    }
    
    .product-meta {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .order-card {
        padding: 1rem;
    }
    
    .order-header {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .modal-content {
        width: 95%;
        margin: 1rem;
    }
}

/* Modern Filter Card Styles */
.orders-filter-section {
    margin-bottom: 2rem;
}

.filter-search-card {
    background: var(--card) !important;
    border-radius: var(--epic-radius-xl) !important;
    box-shadow: var(--epic-shadow-sm) !important;
    border: 1px solid var(--border) !important;
    overflow: hidden !important;
    transition: all var(--epic-transition-fast) !important;
}

.filter-search-card:hover {
    box-shadow: var(--epic-shadow-md) !important;
    transform: translateY(-1px) !important;
}

.filter-card-header {
    padding: var(--epic-space-xl) var(--epic-space-2xl) !important;
    background: var(--bg-2) !important;
    border-bottom: 1px solid var(--border) !important;
}

.filter-title {
    display: flex !important;
    align-items: center !important;
    gap: var(--epic-space-md) !important;
    font-size: var(--epic-text-lg) !important;
    font-weight: var(--epic-font-semibold) !important;
    color: var(--tx) !important;
    letter-spacing: -0.025em !important;
}

.filter-title i {
    color: var(--gold-600) !important;
}

.filter-card-body {
    padding: var(--epic-space-2xl) !important;
}

.search-section {
    margin-bottom: var(--epic-space-xl) !important;
}

.search-input-container {
    position: relative !important;
}

.search-icon {
    position: absolute !important;
    left: var(--epic-space-lg) !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    color: var(--tx-3) !important;
    z-index: 2 !important;
}

.search-input-modern {
    width: 100% !important;
    padding: var(--epic-space-md) var(--epic-space-lg) var(--epic-space-md) calc(var(--epic-space-lg) * 2.5) !important;
    border: 1px solid var(--border) !important;
    border-radius: var(--epic-radius-lg) !important;
    font-size: var(--epic-text-sm) !important;
    background: var(--bg-2) !important;
    transition: var(--epic-transition-fast) !important;
    font-weight: var(--epic-font-medium) !important;
    color: var(--tx) !important;
}

.search-input-modern:focus {
    outline: none !important;
    border-color: var(--gold-600) !important;
    box-shadow: 0 0 0 3px rgba(var(--gold-600-rgb), 0.1) !important;
    background: var(--card) !important;
}

.filter-controls-modern {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: var(--epic-space-lg) !important;
    flex-wrap: wrap !important;
}

.filter-tabs-modern {
    display: flex !important;
    gap: var(--epic-space-sm) !important;
    flex-wrap: wrap !important;
}

.filter-tab-modern {
    display: flex !important;
    align-items: center !important;
    gap: var(--epic-space-sm) !important;
    padding: var(--epic-space-sm) var(--epic-space-lg) !important;
    border: 1px solid var(--border) !important;
    background: var(--card) !important;
    color: var(--tx-2) !important;
    border-radius: var(--epic-radius-lg) !important;
    cursor: pointer !important;
    transition: var(--epic-transition-fast) !important;
    font-size: var(--epic-text-sm) !important;
    font-weight: var(--epic-font-medium) !important;
}

.filter-tab-modern:hover {
    border-color: var(--gold-600) !important;
    color: var(--gold-600) !important;
    background: var(--gold-soft) !important;
}

.filter-tab-modern.active {
    background: var(--gold-600) !important;
    border-color: var(--gold-600) !important;
    color: var(--bg) !important;
    box-shadow: var(--epic-shadow-sm) !important;
}

.filter-tab-modern.active i {
    color: var(--bg) !important;
}

.sort-dropdown-modern {
    min-width: 180px !important;
}

.sort-select-modern {
    width: 100% !important;
    padding: var(--epic-space-sm) var(--epic-space-lg) !important;
    border: 1px solid var(--border) !important;
    border-radius: var(--epic-radius-lg) !important;
    background: var(--card) !important;
    color: var(--tx-2) !important;
    font-size: var(--epic-text-sm) !important;
    font-weight: var(--epic-font-medium) !important;
    cursor: pointer !important;
    transition: var(--epic-transition-fast) !important;
}

.sort-select-modern:focus {
    outline: none !important;
    border-color: var(--gold-600) !important;
    box-shadow: 0 0 0 3px rgba(var(--gold-600-rgb), 0.1) !important;
}

/* Modern Order Cards */
.orders-list-section {
    margin-bottom: 2rem;
}

.orders-grid {
    display: grid;
    gap: 1.5rem;
}

.order-card-header {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #fafbfc 0%, #f8fafc 100%);
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.type-badge.type-course {
    background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%);
    color: #5b21b6;
}

.type-badge.type-tools {
    background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
    color: #c53030;
}

.type-badge.type-ebook {
    background: linear-gradient(135deg, #bee3f8 0%, #90cdf4 100%);
    color: #2c5282;
}

.type-badge.type-template {
    background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
    color: #276749;
}

.discount-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.payment-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.payment-badge.payment-paid {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

.payment-badge.payment-pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.payment-badge.payment-failed {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border: 1px solid #f59e0b;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.25);
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
}

/* Order Summary Section */
.order-summary-section {
    margin-top: var(--epic-space-2xl) !important;
}

.summary-cards {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: var(--epic-space-lg) !important;
}

.summary-card {
    background: var(--card) !important;
    padding: var(--epic-space-xl) !important;
    border-radius: var(--epic-radius-xl) !important;
    box-shadow: var(--epic-shadow-sm) !important;
    border: 1px solid var(--border) !important;
    display: flex !important;
    align-items: center !important;
    gap: var(--epic-space-lg) !important;
    transition: var(--epic-transition-normal) !important;
}

.summary-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: var(--epic-shadow-md) !important;
}

.summary-icon {
    width: 40px !important;
    height: 40px !important;
    border-radius: var(--epic-radius-lg) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: var(--gold-600) !important;
    color: var(--bg) !important;
}

.summary-icon-success {
    background: var(--success) !important;
}

.summary-icon-warning {
    background: var(--warning) !important;
}

.summary-icon-info {
    background: var(--info) !important;
}

.summary-content {
    flex: 1 !important;
}

.summary-value {
    font-size: var(--epic-text-xl) !important;
    font-weight: var(--epic-font-bold) !important;
    color: var(--tx) !important;
    margin: 0 0 var(--epic-space-xs) 0 !important;
    letter-spacing: -0.025em !important;
}

.summary-label {
    color: var(--tx-2) !important;
    font-size: var(--epic-text-sm) !important;
    margin: 0 !important;
    font-weight: var(--epic-font-medium) !important;
}

/* Empty State */
.orders-empty-state {
    text-align: center !important;
    padding: var(--epic-space-4xl) var(--epic-space-2xl) !important;
    background: var(--card) !important;
    border-radius: var(--epic-radius-xl) !important;
    box-shadow: var(--epic-shadow-sm) !important;
    border: 1px solid var(--border) !important;
}

.empty-state-icon {
    margin-bottom: var(--epic-space-xl) !important;
}

.empty-state-icon i {
    color: var(--tx-3) !important;
}

.empty-state-title {
    margin: 0 0 var(--epic-space-md) 0 !important;
    font-size: var(--epic-text-xl) !important;
    font-weight: var(--epic-font-semibold) !important;
    color: var(--tx) !important;
    letter-spacing: -0.025em !important;
}

.empty-state-text {
    margin: 0 0 var(--epic-space-2xl) 0 !important;
    color: var(--tx-2) !important;
    font-size: var(--epic-text-base) !important;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.empty-state-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

@media (max-width: 768px) {
    .filter-controls-modern {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-tabs-modern {
        justify-content: center;
    }
    
    .sort-dropdown-modern {
        min-width: auto;
    }
}
</style>

<script>
// Order management functions
function viewOrderDetails(orderId) {
    console.log('Viewing order details:', orderId);
    
    // Here you would normally fetch order details from server
    const orderDetailsHTML = `
        <div class="order-details">
            <h4>Order #ORD-2024-00${orderId}</h4>
            <p>Detailed information about the order would be displayed here.</p>
            <div class="detail-section">
                <h5>Product Information</h5>
                <p>Product details, specifications, etc.</p>
            </div>
            <div class="detail-section">
                <h5>Payment Information</h5>
                <p>Payment method, transaction details, etc.</p>
            </div>
            <div class="detail-section">
                <h5>Order Timeline</h5>
                <p>Order status history and timeline.</p>
            </div>
        </div>
    `;
    
    document.getElementById('orderDetailsContent').innerHTML = orderDetailsHTML;
    document.getElementById('orderDetailsModal').style.display = 'flex';
}

function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}

function exportOrders() {
    console.log('Exporting orders...');
    // Implement export functionality
}

// Filter and search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchOrders');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const dateFilter = document.getElementById('dateFilter');
    const orderCards = document.querySelectorAll('.order-card');
    const emptyState = document.getElementById('emptyState');
    
    // Search functionality (with null check)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;
            
            orderCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            toggleEmptyState(visibleCount);
        });
    }
    
    // Status filter functionality
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const status = this.dataset.status;
            let visibleCount = 0;
            
            orderCards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            toggleEmptyState(visibleCount);
        });
    });
    
    // Date filter functionality (with null check)
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            const dateRange = this.value;
            console.log('Filtering by date:', dateRange);
            // Implement date filtering logic
        });
    }
    
    function toggleEmptyState(visibleCount) {
        if (emptyState) {
            if (visibleCount === 0) {
                emptyState.style.display = 'block';
            } else {
                emptyState.style.display = 'none';
            }
        }
    }
});
</script>