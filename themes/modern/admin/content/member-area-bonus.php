<?php
/**
 * EPIC Hub Admin - Member Area Bonus Content
 * Konten halaman Member Area Bonus Cash yang dapat diakses dari admin dengan desain responsif
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Get selected member if provided
$selected_member_id = $_GET['member_id'] ?? null;
$demo_member = null;

if ($selected_member_id) {
    $demo_member = epic_get_user($selected_member_id);
}

// Sample bonus data
$bonus_data = [
    'total_earnings' => rand(500000, 2500000),
    'pending_bonus' => rand(50000, 250000),
    'this_month_bonus' => rand(100000, 500000),
    'referral_commissions' => rand(200000, 800000),
    'sales_commissions' => rand(150000, 600000),
    'bonus_rate' => rand(10, 25) . '%'
];
?>

<div class="bonus-management-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="header-title">
                <h1 class="page-title">
                    <i data-feather="coins" class="title-icon"></i>
                    Bonus Cash Management
                </h1>
                <p class="page-subtitle">Kelola sistem bonus dan komisi member dengan tracking real-time</p>
            </div>
            
            <div class="header-actions">
                <a href="<?= epic_url('dashboard/member/bonus') ?>" target="_blank" class="btn btn-primary">
                    <i data-feather="external-link" width="16" height="16"></i>
                    Buka Halaman Bonus
                </a>
            </div>
        </div>
    </div>
    
    <!-- Member Selection Card -->
    <div class="selection-card">
        <div class="card-header">
            <div class="card-title">
                <i data-feather="search" class="card-icon"></i>
                <h3>Silakan Cari Nama Member untuk Preview</h3>
            </div>
        </div>
        
        <div class="card-body">
            <div class="selection-form">
                <!-- Search Input -->
                <div class="search-container">
                    <div class="search-input-wrapper">
                        <i data-feather="search" class="search-icon"></i>
                        <input type="text" 
                               id="memberSearch" 
                               class="search-input" 
                               placeholder="Cari member berdasarkan nama..."
                               autocomplete="off">
                        <div class="search-loading" id="searchLoading" style="display: none;">
                            <i data-feather="loader" class="loading-icon"></i>
                        </div>
                    </div>
                    
                    <!-- Search Results Dropdown -->
                    <div class="search-results" id="searchResults" style="display: none;"></div>
                </div>
                

                
                <?php if ($demo_member): ?>
                    <div class="selected-member-info">
                        <div class="member-avatar">
                            <span><?= strtoupper(substr($demo_member['name'], 0, 2)) ?></span>
                        </div>
                        <div class="member-details">
                            <h4><?= htmlspecialchars($demo_member['name']) ?></h4>
                            <p><?= htmlspecialchars($demo_member['email']) ?></p>
                            <span class="status-badge status-<?= $demo_member['status'] ?>">
                                <?= ucfirst($demo_member['status']) ?> Account
                            </span>
                        </div>
                        <div class="member-actions">
                            <button onclick="clearSelection()" class="btn-clear" title="Clear Selection">
                                <i data-feather="x" width="16" height="16"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($demo_member): ?>
    <!-- Bonus Overview Cards -->
    <div class="bonus-overview">
        <div class="overview-grid">
            <div class="overview-card">
                <div class="card-icon total">
                    <i data-feather="dollar-sign"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value">Rp <?= number_format($bonus_data['total_earnings'], 0, ',', '.') ?></h3>
                    <p class="card-label">Total Earnings</p>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon pending">
                    <i data-feather="clock"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value">Rp <?= number_format($bonus_data['pending_bonus'], 0, ',', '.') ?></h3>
                    <p class="card-label">Pending Bonus</p>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon monthly">
                    <i data-feather="calendar"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value">Rp <?= number_format($bonus_data['this_month_bonus'], 0, ',', '.') ?></h3>
                    <p class="card-label">This Month</p>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon rate">
                    <i data-feather="trending-up"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value"><?= $bonus_data['bonus_rate'] ?></h3>
                    <p class="card-label">Commission Rate</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bonus Management Sections -->
    <div class="bonus-sections">
        <!-- Earning Features Section -->
        <div class="bonus-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i data-feather="dollar-sign" class="section-icon"></i>
                    Fitur Earning System
                </h3>
                <p class="section-description">Sistem bonus dan komisi yang komprehensif</p>
            </div>
            
            <div class="section-content">
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="bar-chart-2"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Dashboard Earning</h4>
                            <p>Total earned, monthly earning, pending bonus dengan visualisasi</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="link"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Referral Link Management</h4>
                            <p>Copy dan share referral link dengan mudah</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="list"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Bonus History</h4>
                            <p>Riwayat lengkap semua bonus dan komisi</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="activity"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Referral Performance</h4>
                            <p>Track aktivitas dan spending referral</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="lightbulb"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Tips Maksimalkan Earning</h4>
                            <p>Panduan dan tips untuk meningkatkan income</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="credit-card"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Withdrawal System</h4>
                            <p>Sistem penarikan bonus yang mudah dan aman</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Commission Structure Section -->
        <div class="bonus-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i data-feather="percent" class="section-icon"></i>
                    Struktur Komisi
                </h3>
                <p class="section-description">Sistem komisi yang transparan dan menguntungkan</p>
            </div>
            
            <div class="section-content">
                <div class="commission-grid">
                    <div class="commission-card referral">
                        <div class="commission-header">
                            <div class="commission-icon">
                                <i data-feather="users"></i>
                            </div>
                            <div class="commission-info">
                                <h4>Referral Commission</h4>
                                <div class="commission-rate">10%</div>
                            </div>
                        </div>
                        <p class="commission-desc">Dari setiap pembelian referral Anda</p>
                        <div class="commission-example">
                            <span>Contoh: Referral beli Rp 100.000 = Bonus Rp 10.000</span>
                        </div>
                    </div>
                    
                    <div class="commission-card upgrade">
                        <div class="commission-header">
                            <div class="commission-icon">
                                <i data-feather="arrow-up"></i>
                            </div>
                            <div class="commission-info">
                                <h4>EPIC Upgrade Bonus</h4>
                                <div class="commission-rate">Rp 29.700</div>
                            </div>
                        </div>
                        <p class="commission-desc">Per referral yang upgrade ke EPIC</p>
                        <div class="commission-example">
                            <span>Bonus langsung saat referral upgrade</span>
                        </div>
                    </div>
                    
                    <div class="commission-card unlimited">
                        <div class="commission-header">
                            <div class="commission-icon">
                                <i data-feather="infinity"></i>
                            </div>
                            <div class="commission-info">
                                <h4>Unlimited Earning</h4>
                                <div class="commission-rate">∞</div>
                            </div>
                        </div>
                        <p class="commission-desc">Tidak ada batas maksimal earning</p>
                        <div class="commission-example">
                            <span>Semakin banyak referral, semakin besar income</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Preview Instructions -->
    <div class="preview-instructions">
        <div class="instruction-card">
            <div class="instruction-icon">
                <i data-feather="info" width="24" height="24"></i>
            </div>
            <div class="instruction-content">
                <h4>Cara Mengakses Preview Bonus Cash</h4>
                <p>Gunakan kolom pencarian di atas untuk mencari nama member, lalu pilih member yang ingin Anda preview. Setelah memilih member, Anda dapat melihat sistem bonus dan komisi dengan tracking real-time.</p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3 class="section-title">
            <i data-feather="zap" class="section-icon"></i>
            Akses Cepat Member Area
        </h3>
        <div class="actions-grid">
            <a href="<?= epic_url('admin/member-area/home') ?>" class="action-card">
                <div class="action-icon">
                    <i data-feather="monitor"></i>
                </div>
                <div class="action-content">
                    <h4>Home Dashboard</h4>
                    <p>Lihat overview dashboard member</p>
                </div>
            </a>
            
            <a href="<?= epic_url('admin/member-area/profile') ?>" class="action-card">
                <div class="action-icon">
                    <i data-feather="user-edit"></i>
                </div>
                <div class="action-content">
                    <h4>Edit Profil</h4>
                    <p>Kelola profil dan informasi member</p>
                </div>
            </a>
            
            <a href="<?= epic_url('admin/member-area/prospects') ?>" class="action-card">
                <div class="action-icon">
                    <i data-feather="users"></i>
                </div>
                <div class="action-content">
                    <h4>Prospek</h4>
                    <p>Kelola prospek dan leads member</p>
                </div>
            </a>
            
            <a href="<?= epic_url('admin/member-area/products') ?>" class="action-card">
                <div class="action-icon">
                    <i data-feather="package"></i>
                </div>
                <div class="action-content">
                    <h4>Akses Produk</h4>
                    <p>Kelola akses produk member</p>
                </div>
            </a>
            
            <a href="<?= epic_url('admin/member-area/orders') ?>" class="action-card">
                <div class="action-icon">
                    <i data-feather="shopping-bag"></i>
                </div>
                <div class="action-content">
                    <h4>History Order</h4>
                    <p>Lihat riwayat pembelian member</p>
                </div>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.bonus-management-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--spacing-6);
}

.dashboard-header {
    margin-bottom: var(--spacing-8);
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--spacing-4);
}

.header-title {
    flex: 1;
}

.page-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-2) 0;
}

.title-icon {
    color: var(--gold-400);
}

.page-subtitle {
    color: var(--ink-300);
    font-size: var(--font-size-base);
    margin: 0;
}

.header-actions {
    display: flex;
    gap: var(--spacing-3);
}

.selection-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-8);
    overflow: visible;
}

.card-header {
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-600);
    background: var(--surface-3);
}

.card-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin: 0;
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
}

.card-icon {
    width: 32px;
    height: 32px;
    color: var(--gold-400);
    opacity: 0.7;
}

.card-body {
    padding: var(--spacing-6);
}

.selection-form {
    display: flex;
    align-items: center;
    gap: var(--spacing-6);
    flex-wrap: wrap;
}

.member-select {
    min-width: 250px;
    padding: var(--spacing-3) var(--spacing-4);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    color: var(--ink-200);
    font-size: var(--font-size-base);
}

.selected-member-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
}

.member-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-900);
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-lg);
}

.member-details h4 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--ink-100);
    font-weight: var(--font-weight-semibold);
}

.member-details p {
    margin: 0 0 var(--spacing-2) 0;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
}

.status-badge {
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
}

.status-free {
    background: var(--surface-3);
    color: var(--ink-300);
}

.status-premium {
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    color: var(--ink-900);
}

.bonus-overview {
    margin-bottom: var(--spacing-8);
}

.overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-6);
}

.overview-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    transition: all var(--transition-fast);
}

.overview-card:hover {
    border-color: var(--gold-400);
    transform: translateY(-2px);
}

.card-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.card-icon.total {
    background: linear-gradient(135deg, #10b981, #047857);
}

.card-icon.pending {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.card-icon.monthly {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.card-icon.rate {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.card-content {
    flex: 1;
}

.card-value {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-1) 0;
}

.card-label {
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    margin: 0;
}

.bonus-sections {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-8);
}

.bonus-section {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.section-header {
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-600);
    background: var(--surface-3);
}

.section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-1) 0;
}

.section-icon {
    color: var(--gold-400);
}

.section-description {
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    margin: 0;
}

.section-content {
    padding: var(--spacing-6);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-4);
}

.feature-card {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-3);
    padding: var(--spacing-4);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.feature-card:hover {
    border-color: var(--gold-400);
    transform: translateY(-1px);
}

.feature-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-900);
    flex-shrink: 0;
}

.feature-content h4 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--ink-100);
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-base);
}

.feature-content p {
    margin: 0;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    line-height: 1.5;
}

.commission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-6);
}

.commission-card {
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    background: var(--surface-1);
    transition: all var(--transition-fast);
}

.commission-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.commission-card.referral {
    border-color: #10b981;
}

.commission-card.upgrade {
    border-color: #3b82f6;
}

.commission-card.unlimited {
    border-color: #8b5cf6;
}

.commission-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-4);
}

.commission-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.commission-card.referral .commission-icon {
    background: linear-gradient(135deg, #10b981, #047857);
}

.commission-card.upgrade .commission-icon {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.commission-card.unlimited .commission-icon {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.commission-info h4 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--ink-100);
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-base);
}

.commission-rate {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--gold-400);
}

.commission-desc {
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    margin: 0 0 var(--spacing-3) 0;
    line-height: 1.5;
}

.commission-example {
    padding: var(--spacing-2) var(--spacing-3);
    background: var(--surface-3);
    border-radius: var(--radius-sm);
    border-left: 3px solid var(--gold-400);
}

.commission-example span {
    color: var(--ink-200);
    font-size: var(--font-size-xs);
    font-style: italic;
}

.empty-state {
    text-align: center;
    padding: var(--spacing-12) var(--spacing-6);
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
}

.empty-icon {
    color: var(--ink-400);
    margin-bottom: var(--spacing-4);
}

.empty-title {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-200);
    margin: 0 0 var(--spacing-2) 0;
}

.empty-description {
    color: var(--ink-300);
    font-size: var(--font-size-base);
    margin: 0;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .bonus-management-container {
        padding: var(--spacing-4);
    }
    
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .selection-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .member-select {
        min-width: auto;
        width: 100%;
    }
    
    .overview-grid {
        grid-template-columns: 1fr;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .commission-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: var(--font-size-xl);
    }
    
    .overview-card {
        padding: var(--spacing-4);
    }
    
    .section-content {
        padding: var(--spacing-4);
    }
}
</style>

<script>
function previewMember(memberId) {
    if (memberId) {
        window.location.href = '<?= epic_url('admin/member-area/bonus') ?>?member_id=' + memberId;
    } else {
        window.location.href = '<?= epic_url('admin/member-area/bonus') ?>';
    }
}

// Initialize Feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

<style>
/* Search Container */
.search-container {
    position: relative;
    width: 100%;
    margin-bottom: var(--spacing-4);
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: var(--spacing-3);
    color: var(--ink-400);
    z-index: 2;
}

.search-input {
    width: 100%;
    padding: var(--spacing-3) var(--spacing-10) var(--spacing-3) var(--spacing-10);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    color: var(--ink-100);
    font-size: var(--font-size-sm);
    transition: all var(--transition-fast);
}

.search-input:focus {
    outline: none;
    border-color: var(--gold-400);
    background: var(--surface-2);
}

.search-input::placeholder {
    color: var(--ink-400);
}

.search-loading {
    position: absolute;
    right: var(--spacing-3);
    color: var(--gold-400);
}

.loading-icon {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Search Results */
.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-top: none;
    border-radius: 0 0 var(--radius-md) var(--radius-md);
    max-height: 450px;
    overflow-y: auto;
    z-index: 9999;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.search-result-item {
    padding: var(--spacing-3) var(--spacing-4);
    cursor: pointer;
    border-bottom: 1px solid var(--ink-700);
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.search-result-item:hover {
    background: var(--surface-3);
    border-color: var(--gold-400);
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-900);
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-sm);
    flex-shrink: 0;
}

.search-result-info {
    flex: 1;
}

.search-result-name {
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    font-size: var(--font-size-sm);
    margin: 0 0 var(--spacing-1) 0;
}

.search-result-email {
    color: var(--ink-300);
    font-size: var(--font-size-xs);
    margin: 0;
}

.search-result-status {
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

.search-result-status.status-free {
    background: var(--surface-3);
    color: var(--ink-200);
    border: 1px solid var(--ink-500);
}

.search-result-status.status-premium,
.search-result-status.status-epic {
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    color: var(--ink-900);
}

.search-no-results {
    padding: var(--spacing-6) var(--spacing-4);
    text-align: center;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
}

/* Preview Instructions */
.preview-instructions {
    margin-bottom: var(--spacing-8);
}

.instruction-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-4);
}

.instruction-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-900);
    flex-shrink: 0;
}

.instruction-content h4 {
    margin: 0 0 var(--spacing-2) 0;
    color: var(--ink-100);
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-lg);
}

.instruction-content p {
    margin: 0;
    color: var(--ink-300);
    font-size: var(--font-size-base);
    line-height: 1.6;
}

/* Quick Actions */
.quick-actions {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
}

.section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-6) 0;
}

.section-icon {
    color: var(--gold-400);
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-4);
}

.action-card {
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    padding: var(--spacing-4);
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    text-decoration: none;
    transition: all var(--transition-fast);
}

.action-card:hover {
    border-color: var(--gold-400);
    transform: translateY(-1px);
    background: var(--surface-3);
}

.action-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-900);
}

.action-content h4 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--ink-100);
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-base);
}

.action-content p {
    margin: 0;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
}

/* Select Container */
.select-container {
    width: 100%;
}

.selection-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

.member-actions {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.btn-clear {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: var(--surface-3);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    color: var(--ink-300);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.btn-clear:hover {
    background: var(--surface-1);
    border-color: var(--gold-400);
    color: var(--gold-400);
}

.member-details {
    flex: 1;
}
</style>

<script>
function previewMember(memberId) {
    if (memberId) {
        window.location.href = '<?= epic_url('admin/member-area/bonus') ?>?member_id=' + memberId;
    } else {
        window.location.href = '<?= epic_url('admin/member-area/bonus') ?>';
    }
}

function clearSelection() {
    window.location.href = '<?= epic_url('admin/member-area/bonus') ?>';
}

// Search functionality
let searchTimeout;
let searchCache = new Map();

function initializeSearch() {
    const searchInput = document.getElementById('memberSearch');
    const searchResults = document.getElementById('searchResults');
    const searchLoading = document.getElementById('searchLoading');
    
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        searchLoading.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    

}

function performSearch(query) {
    const searchResults = document.getElementById('searchResults');
    const searchLoading = document.getElementById('searchLoading');
    
    if (searchCache.has(query)) {
        displaySearchResults(searchCache.get(query));
        searchLoading.style.display = 'none';
        return;
    }
    
    fetch('<?= epic_url('api/admin/search-members.php') ?>?q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
            searchLoading.style.display = 'none';
            
            if (data.success) {
                searchCache.set(query, data.members);
                displaySearchResults(data.members);
            } else {
                console.error('Search error:', data.error);
                displaySearchResults([]);
            }
        })
        .catch(error => {
            console.error('Search request failed:', error);
            searchLoading.style.display = 'none';
            displaySearchResults([]);
        });
}

function displaySearchResults(members) {
    const searchResults = document.getElementById('searchResults');
    
    if (members.length === 0) {
        searchResults.innerHTML = '<div class="search-no-results"><i data-feather="search" width="20" height="20"></i><br>Tidak ada member ditemukan</div>';
    } else {
        let html = '';
        members.forEach(member => {
            const initials = member.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
            html += `
                <div class="search-result-item" onclick="selectSearchResult(${member.id}, '${member.name.replace(/'/g, "\\'")}')">  
                    <div class="search-result-avatar">
                        <span>${initials}</span>
                    </div>
                    <div class="search-result-info">
                        <div class="search-result-name">${member.name}</div>
                        <div class="search-result-email">${member.email}</div>
                    </div>
                    <div class="search-result-status status-${member.status}">
                        ${member.status.charAt(0).toUpperCase() + member.status.slice(1)}
                    </div>
                </div>
            `;
        });
        searchResults.innerHTML = html;
    }
    
    searchResults.style.display = 'block';
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

function selectSearchResult(memberId, memberName) {
    const searchInput = document.getElementById('memberSearch');
    const searchResults = document.getElementById('searchResults');
    
    searchInput.value = memberName;
    searchResults.style.display = 'none';
    
    previewMember(memberId);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    initializeSearch();
});
</script>