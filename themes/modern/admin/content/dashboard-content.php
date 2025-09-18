<?php
/**
 * Dashboard Content
 * Content yang akan di-render oleh layout global
 */

// Variables sudah tersedia dari parent scope
?>

<!-- Dashboard Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Users</h3>
            <i data-feather="users" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total_users']) ?></div>
        <div class="stat-card-change positive">
            <i data-feather="trending-up" width="16" height="16"></i>
            <span>Active: <?= number_format($stats['active_users']) ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Premium Users</h3>
            <i data-feather="star" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['premium_users']) ?></div>
        <div class="stat-card-change neutral">
            <i data-feather="info" width="16" height="16"></i>
            <span><?= $stats['total_users'] > 0 ? round(($stats['premium_users'] / $stats['total_users']) * 100, 1) : 0 ?>% of total</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Products</h3>
            <i data-feather="package" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total_products']) ?></div>
        <div class="stat-card-change positive">
            <i data-feather="check-circle" width="16" height="16"></i>
            <span>Active: <?= number_format($stats['active_products']) ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Orders</h3>
            <i data-feather="shopping-cart" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total_orders']) ?></div>
        <div class="stat-card-change <?= $stats['pending_orders'] > 0 ? 'neutral' : 'positive' ?>">
            <i data-feather="<?= $stats['pending_orders'] > 0 ? 'clock' : 'check-circle' ?>" width="16" height="16"></i>
            <span>Pending: <?= number_format($stats['pending_orders']) ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Revenue</h3>
            <i data-feather="dollar-sign" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></div>
        <div class="stat-card-change positive">
            <i data-feather="trending-up" width="16" height="16"></i>
            <span>Paid Orders: <?= number_format($stats['paid_orders']) ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Commissions</h3>
            <i data-feather="percent" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($stats['total_commissions'], 0, ',', '.') ?></div>
        <div class="stat-card-change neutral">
            <i data-feather="trending-up" width="16" height="16"></i>
            <span>Total Earned</span>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Revenue Overview</h3>
            <div class="chart-toggle">
                <button class="toggle-btn active" data-period="7d">7D</button>
                <button class="toggle-btn" data-period="30d">30D</button>
                <button class="toggle-btn" data-period="90d">90D</button>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="revenueChart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Top Affiliates</h3>
        </div>
        <div class="chart-container">
            <canvas id="affiliatesChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="activities-grid">
    <div class="activity-card">
        <div class="activity-header">
            <h3 class="activity-title">Recent Orders</h3>
            <a href="<?= epic_url('admin/manage/order') ?>" class="activity-link">
                <span>View All</span>
                <i data-feather="arrow-right" width="16" height="16"></i>
            </a>
        </div>
        <div class="activity-list">
            <?php if (empty($recent_orders)): ?>
                <div class="activity-empty">
                    <i data-feather="shopping-cart" width="32" height="32"></i>
                    <p>No recent orders</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_orders as $order): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i data-feather="shopping-cart" width="16" height="16"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-main">
                                <span class="activity-user"><?= htmlspecialchars($order['user_name'] ?? 'Unknown User') ?></span>
                                <span class="activity-action">ordered</span>
                                <span class="activity-target"><?= htmlspecialchars($order['product_name'] ?? 'Unknown Product') ?></span>
                            </div>
                            <div class="activity-meta">
                                <span class="activity-amount">Rp <?= number_format($order['amount'] ?? 0, 0, ',', '.') ?></span>
                                <span class="activity-time"><?= date('M j, H:i', strtotime($order['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="activity-status">
                            <span class="badge <?= ($order['status'] ?? '') === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($order['status'] ?? 'pending') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="activity-card">
        <div class="activity-header">
            <h3 class="activity-title">New Users</h3>
            <a href="<?= epic_url('admin/manage/member') ?>" class="activity-link">
                <span>View All</span>
                <i data-feather="arrow-right" width="16" height="16"></i>
            </a>
        </div>
        <div class="activity-list">
            <?php if (empty($recent_users)): ?>
                <div class="activity-empty">
                    <i data-feather="users" width="32" height="32"></i>
                    <p>No new users</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_users as $new_user): ?>
                    <div class="activity-item">
                        <div class="activity-avatar">
                            <?php if (!empty($new_user['profile_photo'])): ?>
                                <img src="<?= epic_url('uploads/profiles/' . $new_user['profile_photo']) ?>" alt="Profile">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?= strtoupper(substr($new_user['name'], 0, 2)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-main">
                                <span class="activity-user"><?= htmlspecialchars($new_user['name']) ?></span>
                                <span class="activity-action">joined</span>
                            </div>
                            <div class="activity-meta">
                                <span class="activity-email"><?= htmlspecialchars($new_user['email']) ?></span>
                                <span class="activity-time"><?= date('M j, H:i', strtotime($new_user['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="activity-status">
                            <span class="badge <?= $new_user['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                <?= ucfirst($new_user['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Page-specific functionality
    function initPageFunctionality() {
        // Initialize charts
        initDashboardCharts();
        
        // Initialize chart toggles
        initChartToggles();
    }
    
    function initDashboardCharts() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.log('Chart.js not loaded yet, retrying...');
            setTimeout(initDashboardCharts, 100);
            return;
        }
        
        // Revenue Chart
        const ctx1 = document.getElementById('revenueChart');
        if (ctx1) {
            // Destroy existing chart if it exists
            const existingChart1 = Chart.getChart(ctx1);
            if (existingChart1) {
                existingChart1.destroy();
            }
            
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Revenue (Juta Rp)',
                        data: [12, 19, 8, 15, 22, 18, 25],
                        borderColor: '#CFA84E',
                        backgroundColor: 'rgba(207, 168, 78, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#9B9CA8'
                            },
                            grid: {
                                color: '#1D1D25'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#9B9CA8'
                            },
                            grid: {
                                color: '#1D1D25'
                            }
                        }
                    }
                }
            });
        }
        
        // Top Affiliates Chart
        const ctx2 = document.getElementById('affiliatesChart');
        if (ctx2) {
            // Destroy existing chart if it exists
            const existingChart2 = Chart.getChart(ctx2);
            if (existingChart2) {
                existingChart2.destroy();
            }
            
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Bustanul A.', 'Siti M.', 'Ahmad H.', 'Rina N.', 'Dedi W.'],
                    datasets: [{
                        label: 'Revenue (Juta Rp)',
                        data: [32, 28, 24, 21, 18],
                        backgroundColor: 'rgba(207, 168, 78, 0.8)',
                        borderColor: '#CFA84E',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#9B9CA8'
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            ticks: {
                                color: '#9B9CA8'
                            },
                            grid: {
                                color: '#1D1D25'
                            }
                        }
                    }
                }
            });
        }
    }
    
    function initChartToggles() {
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                // Here you would typically reload chart data based on period
                const period = this.dataset.period;
                console.log('Loading data for period:', period);
            });
        });
    }
</script>