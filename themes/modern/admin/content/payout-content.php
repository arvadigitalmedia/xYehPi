<?php
/**
 * EPIC Hub Admin Payout Management Content
 * Konten halaman payout untuk layout global
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Extract variables dari layout data
extract($data ?? []);
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

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Members</h3>
            <i data-feather="users" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($total_members) ?></div>
        <div class="stat-card-subtitle">Members dengan komisi</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Saldo Komisi</h3>
            <i data-feather="dollar-sign" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($total_commission_balance, 0, ',', '.') ?></div>
        <div class="stat-card-subtitle">Belum dicairkan</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Payout Bulan Ini</h3>
            <i data-feather="trending-up" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($total_payouts_this_month, 0, ',', '.') ?></div>
        <div class="stat-card-subtitle">Total pencairan</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Rata-rata Komisi</h3>
            <i data-feather="bar-chart" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($total_members > 0 ? $total_commission_balance / $total_members : 0, 0, ',', '.') ?></div>
        <div class="stat-card-subtitle">Per member</div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Pencairan Per Bulan</h3>
            <div class="chart-subtitle">12 bulan terakhir</div>
        </div>
        <div class="chart-container">
            <canvas id="payoutChart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Nominal Pencairan</h3>
            <div class="chart-subtitle">Total amount per bulan</div>
        </div>
        <div class="chart-container">
            <canvas id="amountChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Members Table -->
<div class="content-section">
    <div class="section-header">
        <h2 class="section-title">Daftar Member dengan Komisi</h2>
        <div class="section-subtitle">Kelola pencairan komisi member</div>
    </div>
    
    <?php if (empty($members_with_commission)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-feather="users" width="48" height="48"></i>
            </div>
            <h3 class="empty-state-title">Tidak Ada Member dengan Komisi</h3>
            <p class="empty-state-text">Belum ada member yang memiliki saldo komisi untuk dicairkan.</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Member</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Detail Rekening</th>
                        <th>Total Komisi</th>
                        <th>Total Payout</th>
                        <th>Terakhir Payout</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members_with_commission as $member): ?>
                        <tr>
                            <td>
                                <div class="member-info">
                                    <div class="member-name"><?= htmlspecialchars($member['name']) ?></div>
                                    <div class="member-id">ID: <?= $member['id'] ?></div>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($member['email']) ?>" class="email-link">
                                    <?= htmlspecialchars($member['email']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($member['phone']): ?>
                                    <a href="tel:<?= htmlspecialchars($member['phone']) ?>" class="phone-link">
                                        <?= htmlspecialchars($member['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $additional_data = json_decode($member['additional_data'] ?? '{}', true);
                                $bank_account = $additional_data['bank_account'] ?? $additional_data['rekening'] ?? null;
                                if ($bank_account): ?>
                                    <div class="bank-info">
                                        <?= htmlspecialchars($bank_account) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-warning">Belum diisi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="amount-display">
                                    <span class="amount">Rp <?= number_format($member['commission_balance'], 0, ',', '.') ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="payout-count">
                                    <?= number_format($member['total_payouts']) ?> kali
                                </div>
                            </td>
                            <td>
                                <?php if ($member['last_payout_date']): ?>
                                    <div class="date-display">
                                        <?= date('d/m/Y', strtotime($member['last_payout_date'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Belum pernah</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($member['commission_balance'] >= 50000): ?>
                                    <span class="badge badge-success">Siap Cair</span>
                                <?php elseif ($member['commission_balance'] > 0): ?>
                                    <span class="badge badge-warning">Minimum Rp 50K</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Tidak Ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($member['commission_balance'] > 0): ?>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="openPayoutModal(<?= $member['id'] ?>, '<?= htmlspecialchars($member['name']) ?>', <?= $member['commission_balance'] ?>)">
                                            <i data-feather="dollar-sign" width="14" height="14"></i>
                                            Proses Payout
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?= epic_url('admin/manage/member/edit/' . $member['id']) ?>" class="btn btn-secondary btn-sm">
                                        <i data-feather="edit" width="14" height="14"></i>
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Recent Payouts -->
<?php if (!empty($recent_payouts)): ?>
<div class="content-section">
    <div class="section-header">
        <h2 class="section-title">Riwayat Payout Terbaru</h2>
        <div class="section-subtitle">10 pencairan terakhir</div>
    </div>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Member</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_payouts as $payout): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($payout['created_at'])) ?></td>
                        <td>
                            <div class="member-info">
                                <div class="member-name"><?= htmlspecialchars($payout['member_name']) ?></div>
                                <div class="member-email"><?= htmlspecialchars($payout['member_email']) ?></div>
                            </div>
                        </td>
                        <td>
                            <span class="amount">Rp <?= number_format($payout['amount_out'], 0, ',', '.') ?></span>
                        </td>
                        <td>
                            <span class="badge badge-<?= $payout['status'] === 'completed' ? 'success' : 'warning' ?>">
                                <?= ucfirst($payout['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($payout['description']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Payout Modal -->
<div id="payoutModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">
                <i data-feather="dollar-sign" width="20" height="20"></i>
                Proses Payout
            </h3>
            <button type="button" class="modal-close" onclick="closePayoutModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form method="POST" class="modal-body">
            <input type="hidden" name="process_payout" value="1">
            <input type="hidden" name="member_id" id="modal_member_id">
            
            <div class="form-group">
                <label class="form-label">Member</label>
                <div class="form-value" id="modal_member_name"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Saldo Komisi Tersedia</label>
                <div class="form-value" id="modal_commission_balance"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="payout_amount">Jumlah Payout</label>
                <input type="number" class="form-input" name="amount" id="payout_amount" 
                       min="1" step="1000" required>
                <div class="form-help">Masukkan jumlah yang akan dicairkan</div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closePayoutModal()">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="check" width="16" height="16"></i>
                    Proses Payout
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Payout specific styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-6);
    margin-bottom: var(--spacing-8);
}

.stat-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-2xl);
    padding: var(--spacing-6);
    transition: all var(--transition-normal);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.stat-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--spacing-4);
}

.stat-card-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-300);
    margin: 0;
}

.stat-card-icon {
    color: var(--gold-400);
    width: 20px;
    height: 20px;
}

.stat-card-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-2);
}

.stat-card-subtitle {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-6);
    margin-bottom: var(--spacing-8);
}

.chart-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-2xl);
    padding: var(--spacing-6);
}

.chart-header {
    margin-bottom: var(--spacing-4);
}

.chart-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-1) 0;
}

.chart-subtitle {
    font-size: var(--font-size-sm);
    color: var(--ink-400);
}

.chart-container {
    position: relative;
    height: 300px;
}

.member-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-1);
}

.member-name {
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
}

.member-id, .member-email {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

.email-link, .phone-link {
    color: var(--gold-400);
    text-decoration: none;
}

.email-link:hover, .phone-link:hover {
    text-decoration: underline;
}

.bank-info {
    font-size: var(--font-size-sm);
    color: var(--ink-200);
    background: var(--surface-3);
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-md);
    border: 1px solid var(--ink-600);
}

.amount-display .amount {
    font-weight: var(--font-weight-bold);
    color: var(--gold-400);
}

.payout-count {
    font-weight: var(--font-weight-medium);
    color: var(--ink-200);
}

.date-display {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
}

.action-buttons {
    display: flex;
    gap: var(--spacing-2);
    flex-wrap: wrap;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(4px);
}

.modal-container {
    background: var(--surface-1);
    border-radius: var(--radius-xl);
    border: 1px solid var(--ink-600);
    box-shadow: var(--shadow-xl);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-600);
    background: var(--surface-2);
}

.modal-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin: 0;
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
}

.modal-close {
    background: none;
    border: none;
    color: var(--ink-400);
    cursor: pointer;
    padding: var(--spacing-2);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.modal-close:hover {
    background: var(--surface-3);
    color: var(--ink-100);
}

.modal-body {
    padding: var(--spacing-6);
}

.form-value {
    padding: var(--spacing-3) var(--spacing-4);
    background: var(--surface-3);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    color: var(--ink-100);
    font-weight: var(--font-weight-medium);
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
// Chart data from PHP
const monthlyPayouts = <?= json_encode($monthly_payouts) ?>;

// Payout Modal Functions
function openPayoutModal(memberId, memberName, commissionBalance) {
    document.getElementById('modal_member_id').value = memberId;
    document.getElementById('modal_member_name').textContent = memberName;
    document.getElementById('modal_commission_balance').textContent = 'Rp ' + commissionBalance.toLocaleString('id-ID');
    document.getElementById('payout_amount').value = commissionBalance;
    document.getElementById('payout_amount').max = commissionBalance;
    document.getElementById('payoutModal').style.display = 'flex';
}

function closePayoutModal() {
    document.getElementById('payoutModal').style.display = 'none';
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Initialize charts
    initPayoutCharts();
});

function initPayoutCharts() {
    // Prepare chart data
    const months = monthlyPayouts.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
    });
    const payoutCounts = monthlyPayouts.map(item => parseInt(item.payout_count));
    const payoutAmounts = monthlyPayouts.map(item => parseFloat(item.total_amount));
    
    // Chart.js configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(107, 108, 120, 0.1)'
                },
                ticks: {
                    color: '#6B6C78'
                }
            },
            x: {
                grid: {
                    color: 'rgba(107, 108, 120, 0.1)'
                },
                ticks: {
                    color: '#6B6C78'
                }
            }
        }
    };
    
    // Payout Count Chart
    const payoutCtx = document.getElementById('payoutChart');
    if (payoutCtx) {
        new Chart(payoutCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Jumlah Payout',
                    data: payoutCounts,
                    borderColor: '#DDB966',
                    backgroundColor: 'rgba(221, 185, 102, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                ...chartOptions,
                scales: {
                    ...chartOptions.scales,
                    y: {
                        ...chartOptions.scales.y,
                        ticks: {
                            ...chartOptions.scales.y.ticks,
                            callback: function(value) {
                                return value + ' payout';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Payout Amount Chart
    const amountCtx = document.getElementById('amountChart');
    if (amountCtx) {
        new Chart(amountCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Total Amount',
                    data: payoutAmounts,
                    backgroundColor: 'rgba(221, 185, 102, 0.8)',
                    borderColor: '#DDB966',
                    borderWidth: 1
                }]
            },
            options: {
                ...chartOptions,
                scales: {
                    ...chartOptions.scales,
                    y: {
                        ...chartOptions.scales.y,
                        ticks: {
                            ...chartOptions.scales.y.ticks,
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('payoutModal');
    if (e.target === modal) {
        closePayoutModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePayoutModal();
    }
});
</script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>