<?php
/**
 * EPIC Hub Admin - Member Area Home Content
 * Konten halaman Member Area Home yang dapat diakses dari admin dengan desain responsif
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Get current user (admin)
$admin_user = epic_current_user();

// For demo purposes, we can simulate member data or allow admin to select a member
$selected_member_id = $_GET['member_id'] ?? null;
$demo_member = null;

if ($selected_member_id) {
    $demo_member = epic_get_user($selected_member_id);
}

// Get some sample members for selection
$sample_members = db()->select(
    "SELECT id, name, email, status FROM " . db()->table('users') . " 
     WHERE role IN ('user', 'affiliate') 
     ORDER BY created_at DESC 
     LIMIT 10"
) ?: [];

// Get member statistics if member is selected
$member_stats = null;
if ($demo_member) {
    $member_stats = [
        'total_orders' => rand(5, 25),
        'total_earnings' => rand(500000, 2500000),
        'active_campaigns' => rand(2, 8),
        'conversion_rate' => rand(15, 45) . '%'
    ];
}
?>

<div class="member-dashboard-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="header-title">
                <h1 class="page-title">
                    <i data-feather="monitor" class="title-icon"></i>
                    Member Area Preview
                </h1>
                <p class="page-subtitle">Lihat tampilan dashboard member dari perspektif admin</p>
            </div>
            
            <div class="header-actions">
                <a href="<?= epic_url('dashboard/member') ?>" target="_blank" class="btn btn-primary">
                    <i data-feather="external-link" width="16" height="16"></i>
                    Buka Member Area
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

    <?php if ($demo_member && $member_stats): ?>
    <!-- Dashboard Preview -->
    <div class="dashboard-preview">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon orders">
                    <i data-feather="shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= number_format($member_stats['total_orders']) ?></h3>
                    <p class="stat-label">Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon earnings">
                    <i data-feather="dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">Rp <?= number_format($member_stats['total_earnings'], 0, ',', '.') ?></h3>
                    <p class="stat-label">Total Earnings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon campaigns">
                    <i data-feather="target"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= $member_stats['active_campaigns'] ?></h3>
                    <p class="stat-label">Active Campaigns</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon conversion">
                    <i data-feather="trending-up"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= $member_stats['conversion_rate'] ?></h3>
                    <p class="stat-label">Conversion Rate</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3 class="section-title">Quick Actions</h3>
            <div class="actions-grid">
                <a href="<?= epic_url('admin/member-area/profile?member_id=' . $demo_member['id']) ?>" class="action-card">
                    <div class="action-icon">
                        <i data-feather="user-edit"></i>
                    </div>
                    <div class="action-content">
                        <h4>Edit Profil</h4>
                        <p>Kelola informasi profil member</p>
                    </div>
                </a>
                
                <a href="<?= epic_url('admin/member-area/prospects?member_id=' . $demo_member['id']) ?>" class="action-card">
                    <div class="action-icon">
                        <i data-feather="users"></i>
                    </div>
                    <div class="action-content">
                        <h4>Prospek</h4>
                        <p>Lihat dan kelola prospek member</p>
                    </div>
                </a>
                
                <a href="<?= epic_url('admin/member-area/bonus?member_id=' . $demo_member['id']) ?>" class="action-card">
                    <div class="action-icon">
                        <i data-feather="coins"></i>
                    </div>
                    <div class="action-content">
                        <h4>Bonus Cash</h4>
                        <p>Kelola bonus dan komisi member</p>
                    </div>
                </a>
                
                <a href="<?= epic_url('admin/member-area/products?member_id=' . $demo_member['id']) ?>" class="action-card">
                    <div class="action-icon">
                        <i data-feather="package"></i>
                    </div>
                    <div class="action-content">
                        <h4>Akses Produk</h4>
                        <p>Kelola akses produk member</p>
                    </div>
                </a>
                
                <a href="<?= epic_url('admin/member-area/orders?member_id=' . $demo_member['id']) ?>" class="action-card">
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
    </div>
    <?php else: ?>
    <!-- Preview Instructions -->
    <div class="preview-instructions">
        <div class="instruction-card">
            <div class="instruction-icon">
                <i data-feather="info" width="24" height="24"></i>
            </div>
            <div class="instruction-content">
                <h4>Cara Mengakses Preview Member</h4>
                <p>Gunakan kolom pencarian di atas untuk mencari nama member, lalu pilih member yang ingin Anda preview. Setelah memilih member, Anda dapat mengakses semua area member tersebut.</p>
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
.member-dashboard-container {
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
    color: var(--gold-400);
}

.card-body {
    padding: var(--spacing-6);
}

.selection-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

/* Search Container */
.search-container {
    position: relative;
    width: 100%;
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
    pointer-events: auto;
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

.member-details {
    flex: 1;
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

.dashboard-preview {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-8);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-6);
}

.stat-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    transition: all var(--transition-fast);
}

.stat-card:hover {
    border-color: var(--gold-400);
    transform: translateY(-2px);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    opacity: 0.7;
}

.stat-icon.orders {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.stat-icon.earnings {
    background: linear-gradient(135deg, #10b981, #047857);
}

.stat-icon.campaigns {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.stat-icon.conversion {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-1) 0;
}

.stat-label {
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    margin: 0;
}

.quick-actions {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
}

.section-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-6) 0;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-4);
}

.action-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
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

/* Responsive Design */
@media (max-width: 768px) {
    .member-dashboard-container {
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
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card,
    .action-card {
        padding: var(--spacing-4);
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: var(--font-size-xl);
    }
    
    .selected-member-info {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-3);
    }
}
</style>

<script>
function previewMember(memberId) {
    if (memberId) {
        window.location.href = '<?= epic_url('admin/member-area/home') ?>?member_id=' + memberId;
    } else {
        window.location.href = '<?= epic_url('admin/member-area/home') ?>';
    }
}

function clearSelection() {
    window.location.href = '<?= epic_url('admin/member-area/home') ?>';
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
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Hide results if query is too short
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        // Show loading
        searchLoading.style.display = 'block';
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // Clear search when select is used

}

function performSearch(query) {
    const searchResults = document.getElementById('searchResults');
    const searchLoading = document.getElementById('searchLoading');
    
    // Check cache first
    if (searchCache.has(query)) {
        displaySearchResults(searchCache.get(query));
        searchLoading.style.display = 'none';
        return;
    }
    
    // Perform API call
    fetch('<?= epic_url('api/admin/search-members.php') ?>?q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
            searchLoading.style.display = 'none';
            
            if (data.success) {
                // Cache results
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
    
    // Re-initialize feather icons for new content
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

function selectSearchResult(memberId, memberName) {
    const searchInput = document.getElementById('memberSearch');
    const searchResults = document.getElementById('searchResults');
    
    // Update search input
    searchInput.value = memberName;
    
    // Hide search results
    searchResults.style.display = 'none';
    
    // Navigate to member preview
    previewMember(memberId);
}

// Initialize Feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Initialize search functionality
    initializeSearch();
});

// Logout function for switching from member to admin
function logoutToAdmin() {
    // Clear any member session and redirect to admin login
    fetch('<?= epic_url('logout') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            redirect_to: 'admin'
        })
    }).then(() => {
        window.location.href = '<?= epic_url('admin/login') ?>';
    }).catch(() => {
        // Fallback: direct redirect
        window.location.href = '<?= epic_url('admin/login') ?>';
    });
}
</script>