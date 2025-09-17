<?php
/**
 * EPIC Hub Member Bonus Content
 * Konten halaman bonus dan komisi member
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Data sudah disiapkan di bonus.php
// Data untuk breadcrumb yang akan digunakan di card
$breadcrumb_data = [
    ['text' => 'Dashboard', 'url' => epic_url('dashboard/member')],
    ['text' => 'Bonus Cash']
];
?>

<!-- Card Bonus Cash - Main Menu dengan desain welcome card -->
<div class="product-access-section">
    <div class="product-access-card-with-icon">
        <div class="product-icon-container">
            <div class="product-main-icon">
                <i data-feather="dollar-sign" width="48" height="48"></i>
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
                        Bonus Cash - <?= htmlspecialchars($user['name']) ?>
                    </h1>
                    <div class="product-badge-new">
                        <?php 
                        $level_badges = [
                            'free' => ['text' => 'Free Member', 'class' => 'pill-info'],
                            'epic' => ['text' => 'EPIC Member', 'class' => 'pill-success'],
                            'epis' => ['text' => 'EPIS Member', 'class' => 'pill-warning']
                        ];
                        $badge = $level_badges[$access_level] ?? ['text' => 'Member', 'class' => 'pill-info'];
                        ?>
                        <span class="<?= $badge['class'] ?>"><?= $badge['text'] ?></span>
                    </div>
                    <p class="product-description-new">
                        Kelola komisi, bonus, dan penarikan dana Anda. Saldo tersedia: <strong>Rp <?= number_format($current_balance, 0, ',', '.') ?></strong>
                    </p>
                </div>
                
                <div class="product-actions-new">
                    <?php if ($current_balance >= 100000): ?>
                        <button onclick="openWithdrawModal()" class="btn btn-primary">
                            <i data-feather="credit-card" width="16" height="16"></i>
                            Tarik Dana
                        </button>
                    <?php else: ?>
                        <a href="<?= epic_url('dashboard/member/prospects') ?>" class="btn btn-secondary">
                            <i data-feather="users" width="16" height="16"></i>
                            Kelola Referral
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Integrated Bonus Statistics Grid -->
            <div class="product-stats-grid">
                <!-- Saldo Saat Ini -->
                <div class="product-stat-card">
                    <div class="stat-icon-container-new">
                        <div class="stat-icon-new stat-icon-success-new">
                            <i data-feather="dollar-sign" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="stat-content-new">
                        <div class="stat-title-new">Saldo Saat Ini</div>
                        <div class="stat-value-new">Rp <?= number_format($current_balance, 0, ',', '.') ?></div>
                        <div class="stat-change-new <?= $current_balance >= 100000 ? 'positive' : 'neutral' ?>">
                            <i data-feather="<?= $current_balance >= 100000 ? 'check-circle' : 'clock' ?>" width="12" height="12"></i>
                            <span><?= $current_balance >= 100000 ? 'Dapat ditarik' : 'Min. Rp 100K' ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Total Pendapatan -->
                <div class="product-stat-card">
                    <div class="stat-icon-container-new">
                        <div class="stat-icon-new stat-icon-info-new">
                            <i data-feather="trending-up" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="stat-content-new">
                        <div class="stat-title-new">Total Pendapatan</div>
                        <div class="stat-value-new">Rp <?= number_format($total_earned, 0, ',', '.') ?></div>
                        <div class="stat-change-new neutral">
                            <i data-feather="award" width="12" height="12"></i>
                            <span>Semua waktu</span>
                        </div>
                    </div>
                </div>
                
                <!-- Pending -->
                <div class="product-stat-card">
                    <div class="stat-icon-container-new">
                        <div class="stat-icon-new stat-icon-warning-new">
                            <i data-feather="clock" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="stat-content-new">
                        <div class="stat-title-new">Pending</div>
                        <div class="stat-value-new">Rp <?= number_format($pending_amount, 0, ',', '.') ?></div>
                        <div class="stat-change-new neutral">
                            <i data-feather="info" width="12" height="12"></i>
                            <span>Menunggu verifikasi</span>
                        </div>
                    </div>
                </div>
                
                <!-- Bulan Ini -->
                <div class="product-stat-card">
                    <div class="stat-icon-container-new">
                        <div class="stat-icon-new">
                            <i data-feather="calendar" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="stat-content-new">
                        <div class="stat-title-new">Bulan Ini</div>
                        <div class="stat-value-new">Rp <?= number_format($stats['this_month_earning'], 0, ',', '.') ?></div>
                        <div class="stat-change-new positive">
                            <i data-feather="calendar" width="12" height="12"></i>
                            <span><?= date('F Y') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bonus-actions-section">
    <div class="section-header">
        <h3 class="section-title">Aksi Cepat</h3>
        <p class="section-subtitle">Kelola dana dan komisi Anda</p>
    </div>
    
    <div class="bonus-actions-grid">
        <div class="bonus-action-card <?= $current_balance < 100000 ? 'disabled' : '' ?>">
            <div class="action-icon">
                <i data-feather="credit-card" width="24" height="24"></i>
            </div>
            <div class="action-content">
                <div class="action-title">Tarik Dana</div>
                <div class="action-desc">Minimum penarikan Rp 100.000</div>
            </div>
            <?php if ($current_balance >= 100000): ?>
                <button class="action-btn" onclick="openWithdrawModal()">
                    <i data-feather="arrow-right" width="16" height="16"></i>
                </button>
            <?php else: ?>
                <div class="action-btn disabled">
                    <i data-feather="lock" width="16" height="16"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bonus-action-card">
            <div class="action-icon">
                <i data-feather="users" width="24" height="24"></i>
            </div>
            <div class="action-content">
                <div class="action-title">Referral Link</div>
                <div class="action-desc">Bagikan link referral Anda</div>
            </div>
            <button class="action-btn" onclick="copyReferralLink()">
                <i data-feather="copy" width="16" height="16"></i>
            </button>
        </div>
        
        <div class="bonus-action-card">
            <div class="action-icon">
                <i data-feather="bar-chart-2" width="24" height="24"></i>
            </div>
            <div class="action-content">
                <div class="action-title">Laporan Komisi</div>
                <div class="action-desc">Download laporan bulanan</div>
            </div>
            <button class="action-btn" onclick="downloadReport()">
                <i data-feather="download" width="16" height="16"></i>
            </button>
        </div>
    </div>
</div>

<!-- Transaction History Section -->
<div class="transaction-history-section">
    <div class="section-header">
        <h3 class="section-title">
            <i data-feather="list" class="section-icon"></i>
            Riwayat Transaksi
        </h3>
        <div class="section-actions">
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">Semua</button>
                <button class="filter-tab" data-filter="commission">Komisi</button>
                <button class="filter-tab" data-filter="bonus">Bonus</button>
                <button class="filter-tab" data-filter="withdrawal">Penarikan</button>
            </div>
        </div>
    </div>
    
    <div class="transaction-list">
        <?php if (!empty($transactions)): ?>
            <?php foreach ($transactions as $transaction): ?>
                <div class="transaction-item" data-type="<?= $transaction['type'] ?>">
                    <div class="transaction-icon-container">
                        <div class="transaction-icon transaction-icon-<?= $transaction['type'] ?>">
                            <?php 
                            $icons = [
                                'commission' => 'users',
                                'bonus' => 'award',
                                'withdrawal' => 'credit-card'
                            ];
                            ?>
                            <i data-feather="<?= $icons[$transaction['type']] ?? 'dollar-sign' ?>" width="20" height="20"></i>
                        </div>
                    </div>
                    
                    <div class="transaction-content">
                        <div class="transaction-header">
                            <div class="transaction-title"><?= htmlspecialchars($transaction['description']) ?></div>
                            <div class="transaction-amount <?= $transaction['amount'] > 0 ? 'positive' : 'negative' ?>">
                                <?= $transaction['amount'] > 0 ? '+' : '' ?>Rp <?= number_format(abs($transaction['amount']), 0, ',', '.') ?>
                            </div>
                        </div>
                        
                        <div class="transaction-meta">
                            <div class="transaction-date">
                                <i data-feather="calendar" width="14" height="14"></i>
                                <span><?= date('d M Y, H:i', strtotime($transaction['date'])) ?></span>
                            </div>
                            
                            <div class="transaction-reference">
                                <i data-feather="hash" width="14" height="14"></i>
                                <span><?= $transaction['reference'] ?></span>
                            </div>
                            
                            <?php if ($transaction['from_user']): ?>
                                <div class="transaction-from">
                                    <i data-feather="user" width="14" height="14"></i>
                                    <span><?= htmlspecialchars($transaction['from_user']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="transaction-status">
                        <span class="status-badge status-<?= $transaction['status'] ?>">
                            <?php 
                            $status_text = [
                                'completed' => 'Selesai',
                                'pending' => 'Pending',
                                'failed' => 'Gagal'
                            ];
                            echo $status_text[$transaction['status']] ?? ucfirst($transaction['status']);
                            ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="transaction-empty-state">
                <div class="empty-state-icon">
                    <i data-feather="dollar-sign" width="48" height="48"></i>
                </div>
                <div class="empty-state-content">
                    <h4 class="empty-state-title">Belum Ada Transaksi</h4>
                    <p class="empty-state-text">
                        Transaksi komisi dan bonus Anda akan muncul di sini.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Withdrawal Modal -->
<div id="withdrawModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeWithdrawModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tarik Dana</h3>
            <button class="modal-close" onclick="closeWithdrawModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form class="modal-body" method="POST" action="<?= epic_url('dashboard/member/bonus/withdraw') ?>">
            <div class="form-group">
                <label class="form-label">Jumlah Penarikan</label>
                <div class="input-group">
                    <span class="input-prefix">Rp</span>
                    <input type="number" name="amount" class="form-input" 
                           min="100000" max="<?= $current_balance ?>" 
                           placeholder="Minimum 100.000" required>
                </div>
                <div class="form-help">Saldo tersedia: Rp <?= number_format($current_balance, 0, ',', '.') ?></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Bank Tujuan</label>
                <select name="bank" class="form-select" required>
                    <option value="">Pilih Bank</option>
                    <option value="bca">BCA</option>
                    <option value="mandiri">Mandiri</option>
                    <option value="bni">BNI</option>
                    <option value="bri">BRI</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nomor Rekening</label>
                <input type="text" name="account_number" class="form-input" 
                       placeholder="Masukkan nomor rekening" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nama Pemilik Rekening</label>
                <input type="text" name="account_name" class="form-input" 
                       placeholder="Sesuai dengan rekening bank" required>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWithdrawModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="credit-card" width="16" height="16"></i>
                    Tarik Dana
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openWithdrawModal() {
    document.getElementById('withdrawModal').style.display = 'flex';
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').style.display = 'none';
}

function copyReferralLink() {
    const referralLink = '<?= epic_url('register?ref=' . ($user['referral_code'] ?? '')) ?>';
    navigator.clipboard.writeText(referralLink).then(() => {
        showToast('Link referral berhasil disalin!', 'success');
    });
}

function downloadReport() {
    window.open('<?= epic_url('dashboard/member/bonus/report') ?>', '_blank');
}

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const transactionItems = document.querySelectorAll('.transaction-item');
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter transactions
            transactionItems.forEach(item => {
                if (filter === 'all' || item.dataset.type === filter) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

<style>
/* Transaction History Section */
.transaction-history-section {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    margin-top: var(--space-8);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-6);
    flex-wrap: wrap;
    gap: var(--space-4);
}

.section-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--tx);
    margin: 0;
}

.section-icon {
    color: var(--gold);
}

.filter-tabs {
    display: flex;
    gap: var(--space-2);
    background: var(--surface-2);
    padding: var(--space-1);
    border-radius: var(--radius-lg);
}

.filter-tab {
    padding: var(--space-2) var(--space-4);
    border: none;
    background: transparent;
    color: var(--tx-2);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.filter-tab:hover {
    background: var(--surface-3);
    color: var(--tx);
}

.filter-tab.active {
    background: var(--gold);
    color: var(--surface-1);
}

.transaction-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.transaction-item {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
}

.transaction-item:hover {
    background: var(--surface-3);
    border-color: var(--gold);
}

.transaction-icon-container {
    flex-shrink: 0;
}

.transaction-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.transaction-icon-commission {
    background: linear-gradient(135deg, var(--blue-500), var(--blue-600));
}

.transaction-icon-bonus {
    background: linear-gradient(135deg, var(--green-500), var(--green-600));
}

.transaction-icon-withdrawal {
    background: linear-gradient(135deg, var(--red-500), var(--red-600));
}

.transaction-content {
    flex: 1;
    min-width: 0;
}

.transaction-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--space-2);
    gap: var(--space-4);
}

.transaction-title {
    font-weight: var(--font-weight-semibold);
    color: var(--tx);
    font-size: var(--font-size-base);
}

.transaction-amount {
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-lg);
    white-space: nowrap;
}

.transaction-amount.positive {
    color: var(--green-500);
}

.transaction-amount.negative {
    color: var(--red-500);
}

.transaction-meta {
    display: flex;
    gap: var(--space-4);
    font-size: var(--font-size-sm);
    color: var(--tx-2);
    flex-wrap: wrap;
}

.transaction-date,
.transaction-reference,
.transaction-from {
    display: flex;
    align-items: center;
    gap: var(--space-1);
}

.transaction-status {
    flex-shrink: 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-completed {
    background: var(--green-100);
    color: var(--green-700);
}

.status-pending {
    background: var(--yellow-100);
    color: var(--yellow-700);
}

.status-failed {
    background: var(--red-100);
    color: var(--red-700);
}

.transaction-empty-state {
    text-align: center;
    padding: var(--space-12) var(--space-6);
}

.empty-state-icon {
    color: var(--tx-3);
    margin-bottom: var(--space-4);
}

.empty-state-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--tx-2);
    margin: 0 0 var(--space-2) 0;
}

.empty-state-text {
    color: var(--tx-3);
    margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filter-tabs {
        width: 100%;
        justify-content: center;
    }
    
    .transaction-item {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-3);
    }
    
    .transaction-header {
        width: 100%;
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-2);
    }
    
    .transaction-meta {
        width: 100%;
        flex-direction: column;
        gap: var(--space-2);
    }
    
    .transaction-status {
        align-self: flex-end;
    }
}
</style>