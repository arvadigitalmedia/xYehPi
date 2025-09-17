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
/* Page Header */
.page-header {
    margin-bottom: 2rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: #64748b;
    font-size: 1rem;
    margin: 0;
}

/* Filters Section */
.filters-section {
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
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input {
    width: 100%;
    padding: 0.75rem 0.75rem 0.75rem 2.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.filter-buttons {
    display: flex;
    gap: 0.5rem;
}

.filter-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    background: white;
    color: #374151;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-btn:hover {
    background: #f3f4f6;
}

.filter-btn.active {
    background: #6366f1;
    color: white;
    border-color: #6366f1;
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

/* Order Card */
.order-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.5rem;
    transition: all 0.2s;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.order-number {
    font-size: 1.125rem;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.order-date {
    font-size: 0.875rem;
    color: #6b7280;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-completed {
    background: #dcfce7;
    color: #166534;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.status-failed {
    background: #fee2e2;
    color: #991b1b;
}

.order-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.product-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    color: #6366f1;
    border-radius: 0.75rem;
    flex-shrink: 0;
}

.product-details {
    flex: 1;
    min-width: 0;
}

.product-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
}

.product-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.product-type {
    font-size: 0.8125rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
}

.access-status {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.8125rem;
    font-weight: 500;
}

.access-status.granted {
    color: #059669;
}

.access-status.pending {
    color: #d97706;
}

.order-pricing {
    text-align: right;
    flex-shrink: 0;
}

.pricing-details {
    margin-bottom: 0.5rem;
}

.original-price {
    font-size: 0.875rem;
    color: #9ca3af;
    text-decoration: line-through;
    margin-bottom: 0.25rem;
}

.discount-amount {
    font-size: 0.875rem;
    color: #dc2626;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.final-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #059669;
}

.payment-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    align-items: flex-end;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.payment-status {
    font-size: 0.8125rem;
    font-weight: 500;
}

.payment-paid {
    color: #059669;
}

.payment-pending {
    color: #d97706;
}

.payment-failed {
    color: #dc2626;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #f1f5f9;
}

.order-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.paid-date {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.8125rem;
    color: #059669;
    font-weight: 500;
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
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    z-index: 1001;
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
    
    // Search functionality
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
    
    // Date filter functionality
    dateFilter.addEventListener('change', function() {
        const dateRange = this.value;
        console.log('Filtering by date:', dateRange);
        // Implement date filtering logic
    });
    
    function toggleEmptyState(visibleCount) {
        if (visibleCount === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }
    }
});
</script>