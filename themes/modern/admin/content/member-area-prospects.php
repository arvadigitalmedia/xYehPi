<?php
/**
 * EPIC Hub Admin - Member Area Prospects Content
 * Konten halaman Member Area Prospects yang dapat diakses dari admin dengan desain responsif
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

// Sample prospects data
$prospects_data = [
    'total_prospects' => rand(15, 45),
    'new_prospects' => rand(3, 8),
    'contacted_prospects' => rand(5, 12),
    'qualified_prospects' => rand(2, 6),
    'converted_prospects' => rand(1, 4),
    'conversion_rate' => rand(8, 25) . '%'
];
?>

<div class="prospects-management-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="header-title">
                <h1 class="page-title">
                    <i data-feather="users" class="title-icon"></i>
                    Manajemen Prospek
                </h1>
                <p class="page-subtitle">Kelola dan track prospek member dengan sistem CRM terintegrasi</p>
            </div>
            
            <div class="header-actions">
                <a href="<?= epic_url('dashboard/member/prospects') ?>" target="_blank" class="btn btn-primary">
                    <i data-feather="external-link" width="16" height="16"></i>
                    Buka Halaman Prospek
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
    <!-- Prospects Overview Cards -->
    <div class="prospects-overview">
        <div class="overview-grid">
            <div class="overview-card">
                <div class="card-icon total">
                    <i data-feather="users"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value"><?= $prospects_data['total_prospects'] ?></h3>
                    <p class="card-label">Total Prospek</p>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon new">
                    <i data-feather="user-plus"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value"><?= $prospects_data['new_prospects'] ?></h3>
                    <p class="card-label">Prospek Baru</p>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon qualified">
                    <i data-feather="user-check"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value"><?= $prospects_data['qualified_prospects'] ?></h3>
                    <p class="card-label">Qualified</p>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon converted">
                    <i data-feather="award"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value"><?= $prospects_data['conversion_rate'] ?></h3>
                    <p class="card-label">Conversion Rate</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Prospects Management Sections -->
    <div class="prospects-sections">
        <!-- CRM Features Section -->
        <div class="prospects-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i data-feather="target" class="section-icon"></i>
                    Fitur CRM Terintegrasi
                </h3>
                <p class="section-description">Sistem manajemen prospek yang komprehensif</p>
            </div>
            
            <div class="section-content">
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="plus-circle"></i>
                        </div>
                        <div class="feature-content">
                            <h4>CRUD Prospek</h4>
                            <p>Tambah, edit, hapus, dan kelola prospek dengan mudah</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="activity"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Status Tracking</h4>
                            <p>Track progress: New, Contacted, Qualified, Converted, Lost</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="bar-chart-2"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Statistik Konversi</h4>
                            <p>Dashboard statistik dengan breakdown per status</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="message-circle"></i>
                        </div>
                        <div class="feature-content">
                            <h4>WhatsApp & Email</h4>
                            <p>Direct link ke WhatsApp dan email dari tabel prospek</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="edit-3"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Notes & Follow-up</h4>
                            <p>Catat interaksi dan reminder untuk follow-up</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i data-feather="filter"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Advanced Filtering</h4>
                            <p>Filter prospek berdasarkan status, tanggal, dan kriteria lain</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Access Control Section -->
        <div class="prospects-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i data-feather="shield" class="section-icon"></i>
                    Kontrol Akses
                </h3>
                <p class="section-description">Pembatasan akses berdasarkan level member</p>
            </div>
            
            <div class="section-content">
                <div class="access-levels">
                    <div class="access-level epic">
                        <div class="access-header">
                            <div class="access-icon">
                                <i data-feather="crown"></i>
                            </div>
                            <h4>EPIC Account</h4>
                        </div>
                        <div class="access-features">
                            <div class="access-feature">
                                <i data-feather="check"></i>
                                <span>Akses penuh ke semua fitur CRM</span>
                            </div>
                            <div class="access-feature">
                                <i data-feather="check"></i>
                                <span>Unlimited prospek</span>
                            </div>
                            <div class="access-feature">
                                <i data-feather="check"></i>
                                <span>Advanced analytics & reports</span>
                            </div>
                            <div class="access-feature">
                                <i data-feather="check"></i>
                                <span>WhatsApp & Email integration</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="access-level free">
                        <div class="access-header">
                            <div class="access-icon">
                                <i data-feather="user"></i>
                            </div>
                            <h4>Free Account</h4>
                        </div>
                        <div class="access-features">
                            <div class="access-feature locked">
                                <i data-feather="lock"></i>
                                <span>Fitur terkunci - Upgrade required</span>
                            </div>
                            <div class="access-feature locked">
                                <i data-feather="lock"></i>
                                <span>Tampilkan upgrade prompts</span>
                            </div>
                            <div class="access-feature locked">
                                <i data-feather="lock"></i>
                                <span>Limited preview access</span>
                            </div>
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
                <h4>Cara Mengakses Preview Prospek</h4>
                <p>Gunakan kolom pencarian di atas untuk mencari nama member, lalu pilih member yang ingin Anda preview. Setelah memilih member, Anda dapat melihat sistem manajemen prospek dan CRM terintegrasi.</p>
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
            
            <a href="<?= epic_url('admin/member-area/bonus') ?>" class="action-card">
                <div class="action-icon">
                    <i data-feather="dollar-sign"></i>
                </div>
                <div class="action-content">
                    <h4>Bonus Cash</h4>
                    <p>Kelola bonus dan komisi member</p>
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
.prospects-management-container {
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

.prospects-overview {
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
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.card-icon.new {
    background: linear-gradient(135deg, #10b981, #047857);
}

.card-icon.qualified {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.card-icon.converted {
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

.prospects-sections {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-8);
}

.prospects-section {
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

.access-levels {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-6);
}

.access-level {
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.access-level.epic {
    border-color: var(--gold-400);
}

.access-level.free {
    border-color: var(--ink-500);
    opacity: 0.8;
}

.access-header {
    padding: var(--spacing-4);
    background: var(--surface-1);
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.access-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
}

.access-level.epic .access-icon {
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    color: var(--ink-900);
}

.access-level.free .access-icon {
    background: var(--surface-3);
    color: var(--ink-400);
}

.access-header h4 {
    margin: 0;
    color: var(--ink-100);
    font-weight: var(--font-weight-semibold);
}

.access-features {
    padding: var(--spacing-4);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.access-feature {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
}

.access-feature i {
    width: 16px;
    height: 16px;
    color: var(--gold-400);
}

.access-feature.locked i {
    color: var(--ink-400);
}

.access-feature span {
    color: var(--ink-200);
}

.access-feature.locked span {
    color: var(--ink-400);
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
    .prospects-management-container {
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
    
    .access-levels {
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
        window.location.href = '<?= epic_url('admin/member-area/prospects') ?>?member_id=' + memberId;
    } else {
        window.location.href = '<?= epic_url('admin/member-area/prospects') ?>';
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
        window.location.href = '<?= epic_url('admin/member-area/prospects') ?>?member_id=' + memberId;
    } else {
        window.location.href = '<?= epic_url('admin/member-area/prospects') ?>';
    }
}

function clearSelection() {
    window.location.href = '<?= epic_url('admin/member-area/prospects') ?>';
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