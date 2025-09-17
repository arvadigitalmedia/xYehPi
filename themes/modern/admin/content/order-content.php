<?php
/**
 * Order Management Content
 * Content yang akan di-render oleh layout global
 */

// Variables sudah tersedia dari parent scope
?>

<!-- Alerts -->
<?php if ($success): ?>
    <div class="alert alert-success">
        <i data-feather="check-circle" width="16" height="16"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i data-feather="x-circle" width="16" height="16"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Orders</h3>
            <i data-feather="shopping-cart" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Pending Orders</h3>
            <i data-feather="clock" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['pending']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Paid Orders</h3>
            <i data-feather="check-circle" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['paid']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Cancelled Orders</h3>
            <i data-feather="x-circle" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['cancelled']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Revenue</h3>
            <i data-feather="dollar-sign" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></div>
    </div>
</div>

<!-- Search and Filter -->
<div class="table-card">
    <div class="table-header">
        <h3 class="table-title">Orders</h3>
        
        <form method="GET" class="table-search">
            <div class="search-filters">
                <input type="text" name="search" placeholder="Search orders..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paid" <?= $status_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                
                <input type="date" name="date" value="<?= htmlspecialchars($date_filter) ?>" class="filter-select">
                
                <button type="submit" class="search-btn">
                    <i data-feather="search" width="16" height="16"></i>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8">
                            <i data-feather="shopping-cart" width="48" height="48" class="text-gray-400 mb-4"></i>
                            <p class="text-gray-500">No orders found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <div class="table-cell-main">#<?= $order['id'] ?></div>
                                <div class="table-cell-sub">Order ID</div>
                            </td>
                            <td>
                                <div class="table-cell-main"><?= htmlspecialchars($order['user_name'] ?? 'Unknown') ?></div>
                                <div class="table-cell-sub"><?= htmlspecialchars($order['user_email'] ?? '') ?></div>
                            </td>
                            <td>
                                <div class="table-cell-main"><?= htmlspecialchars($order['product_name'] ?? 'Unknown Product') ?></div>
                            </td>
                            <td>
                                <div class="table-cell-main">Rp <?= number_format($order['amount'] ?? 0, 0, ',', '.') ?></div>
                            </td>
                            <td>
                                <?php 
                                $status_classes = [
                                    'pending' => 'badge-warning',
                                    'paid' => 'badge-success',
                                    'cancelled' => 'badge-danger'
                                ];
                                $status_class = $status_classes[$order['status']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?= $status_class ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-cell-main"><?= date('M j, Y', strtotime($order['created_at'])) ?></div>
                                <div class="table-cell-sub"><?= date('H:i', strtotime($order['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <a href="?action=approve&id=<?= $order['id'] ?>" 
                                           class="action-btn action-activate" title="Approve"
                                           onclick="return confirm('Apakah Anda yakin ingin menyetujui order ini?')">
                                            <i data-feather="check" width="14" height="14"></i>
                                        </a>
                                        <a href="?action=reject&id=<?= $order['id'] ?>" 
                                           class="action-btn action-suspend" title="Reject"
                                           onclick="return confirm('Apakah Anda yakin ingin menolak order ini?')">
                                            <i data-feather="x" width="14" height="14"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="action-btn action-view" title="View Details" 
                                            onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                        <i data-feather="eye" width="14" height="14"></i>
                                    </button>
                                    
                                    <?php if ($order['status'] !== 'paid'): ?>
                                        <a href="?action=delete&id=<?= $order['id'] ?>" 
                                           class="action-btn action-delete" title="Delete"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus order ini? Tindakan ini tidak dapat dibatalkan.')">
                                            <i data-feather="trash-2" width="14" height="14"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="table-pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php 
                    $query_params = $_GET;
                    $query_params['page'] = $i;
                    $query_string = http_build_query($query_params);
                    ?>
                    <a href="?<?= $query_string ?>" class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Order Details</h3>
            <button type="button" class="modal-close" onclick="closeOrderModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        <div class="modal-body" id="orderModalBody">
            <!-- Order details will be loaded here -->
        </div>
    </div>
</div>

<style>
/* Order-specific styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--surface-2);
    border-radius: var(--radius-2xl);
    border: 1px solid var(--ink-700);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-700);
}

.modal-header h3 {
    margin: 0;
    color: var(--ink-100);
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
}

.modal-close {
    background: none;
    border: none;
    color: var(--ink-400);
    cursor: pointer;
    padding: var(--spacing-2);
    border-radius: var(--radius-md);
    transition: color var(--transition-fast);
}

.modal-close:hover {
    color: var(--ink-100);
}

.modal-body {
    padding: var(--spacing-6);
}

.action-btn.action-view {
    background: var(--surface-3);
    color: var(--ink-300);
}

.action-btn.action-view:hover {
    background: var(--gold-400);
    color: var(--ink-900);
}
</style>

<script>
    // Page-specific functionality
    function initPageFunctionality() {
        // Initialize any page-specific features here
        console.log('Order Management initialized');
    }
    
    function viewOrderDetails(orderId) {
        // Show modal
        document.getElementById('orderModal').style.display = 'flex';
        
        // Load order details (placeholder)
        document.getElementById('orderModalBody').innerHTML = `
            <div class="loading-spinner">
                <i data-feather="loader" width="24" height="24"></i>
                <span>Loading order details...</span>
            </div>
        `;
        
        // Replace feather icons
        feather.replace();
        
        // Simulate loading (in real app, this would be an AJAX call)
        setTimeout(() => {
            document.getElementById('orderModalBody').innerHTML = `
                <div class="order-details">
                    <h4>Order #${orderId}</h4>
                    <p>Order details would be loaded here via AJAX call.</p>
                    <p>This is a placeholder for the order details modal.</p>
                </div>
            `;
        }, 1000);
    }
    
    function closeOrderModal() {
        document.getElementById('orderModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('orderModal');
        if (event.target === modal) {
            closeOrderModal();
        }
    });
</script>