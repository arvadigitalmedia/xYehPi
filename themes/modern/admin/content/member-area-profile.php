<?php
/**
 * EPIC Hub Admin - Member Area Profile Content
 * Konten halaman Member Area Profile yang dapat diakses dari admin dengan desain responsif
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

// Sample profile data
$profile_data = [
    'completion_percentage' => rand(60, 95),
    'last_updated' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
    'profile_views' => rand(15, 150)
];
?>

<div class="profile-management-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="header-title">
                <h1 class="page-title">
                    <i data-feather="user-edit" class="title-icon"></i>
                    Edit Profil Member
                </h1>
                <p class="page-subtitle">Kelola informasi profil dan pengaturan akun member</p>
            </div>
            
            <div class="header-actions">
                <a href="<?= epic_url('dashboard/member/profile') ?>" target="_blank" class="btn btn-primary">
                    <i data-feather="external-link" width="16" height="16"></i>
                    Buka Halaman Profil
                </a>
            </div>
        </div>
    </div>
    
    <!-- Notifications -->
    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success">
            <i data-feather="check-circle" width="16" height="16"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error">
            <i data-feather="alert-circle" width="16" height="16"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
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
    <!-- Profile Overview Cards -->
    <div class="profile-overview">
        <div class="overview-grid">
            <div class="overview-card">
                <div class="card-icon completion">
                    <i data-feather="user-check"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value"><?= $profile_data['completion_percentage'] ?>%</h3>
                    <p class="card-label">Profile Completion</p>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon views">
                    <i data-feather="eye"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value"><?= $profile_data['profile_views'] ?></h3>
                    <p class="card-label">Profile Views</p>
                </div>
            </div>
            
            <div class="overview-card">
                <div class="card-icon updated">
                    <i data-feather="clock"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-value"><?= date('M d', strtotime($profile_data['last_updated'])) ?></h3>
                    <p class="card-label">Last Updated</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Management Sections -->
    <div class="profile-sections">
        <!-- Profile Form Section -->
        <div class="profile-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i data-feather="user" class="section-icon"></i>
                    Informasi Personal
                </h3>
                <p class="section-description">Kelola informasi dasar profil member</p>
            </div>
            
            <div class="section-content">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-input" value="<?= $demo_member ? htmlspecialchars($demo_member['name']) : 'John Doe' ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" value="<?= $demo_member ? htmlspecialchars($demo_member['email']) : 'john@example.com' ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="tel" class="form-input" value="+62 812 3456 7890" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status Akun</label>
                        <span class="status-badge status-<?= $demo_member ? $demo_member['status'] : 'premium' ?>">
                            <?= $demo_member ? ucfirst($demo_member['status']) : 'EPIC' ?> Account
                        </span>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Bio</label>
                    <textarea class="form-textarea" rows="3" readonly>Digital marketer dan entrepreneur yang passionate dalam membantu orang lain sukses online.</textarea>
                </div>
            </div>
        </div>
        
        <!-- Profile Picture Section -->
        <div class="profile-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i data-feather="camera" class="section-icon"></i>
                    Foto Profil
                </h3>
                <p class="section-description">Upload dan kelola foto profil</p>
            </div>
            
            <div class="section-content">
                <div class="profile-picture-area">
                    <div class="current-picture">
                        <div class="picture-placeholder">
                            <i data-feather="user" width="48" height="48"></i>
                        </div>
                        <div class="picture-info">
                            <h4>Foto Profil Saat Ini</h4>
                            <p>JPG, PNG maksimal 2MB</p>
                        </div>
                    </div>
                    
                    <div class="picture-actions">
                        <button class="btn btn-secondary" disabled>
                            <i data-feather="upload" width="16" height="16"></i>
                            Upload Foto
                        </button>
                        <button class="btn btn-outline" disabled>
                            <i data-feather="trash-2" width="16" height="16"></i>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Section -->
        <div class="profile-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i data-feather="shield" class="section-icon"></i>
                    Keamanan Akun
                </h3>
                <p class="section-description">Kelola password dan pengaturan keamanan</p>
            </div>
            
            <div class="section-content">
                <div class="security-options">
                    <div class="security-item">
                        <div class="security-info">
                            <h4>Ganti Password</h4>
                            <p>Terakhir diubah 30 hari yang lalu</p>
                        </div>
                        <button class="btn btn-outline" onclick="openPasswordModal()">
                            <i data-feather="key" width="16" height="16"></i>
                            Ubah Password
                        </button>
                    </div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <h4>Two-Factor Authentication</h4>
                            <p>Tambahan keamanan untuk akun Anda</p>
                        </div>
                        <button class="btn btn-outline" disabled>
                            <i data-feather="smartphone" width="16" height="16"></i>
                            Setup 2FA
                        </button>
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
                <h4>Cara Mengakses Preview Member</h4>
                <p>Gunakan kolom pencarian di atas untuk mencari nama member, lalu pilih member yang ingin Anda preview. Setelah memilih member, Anda dapat mengelola profil dan informasi member tersebut.</p>
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
.profile-management-container {
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

.profile-overview {
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
    opacity: 0.7;
}

.card-icon.completion {
    background: linear-gradient(135deg, #10b981, #047857);
}

.card-icon.views {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.card-icon.updated {
    background: linear-gradient(135deg, #f59e0b, #d97706);
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

.profile-sections {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-8);
}

.profile-section {
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

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-6);
    margin-bottom: var(--spacing-6);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    color: var(--ink-200);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    margin: 0;
}

.form-input,
.form-textarea {
    padding: var(--spacing-3) var(--spacing-4);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    color: var(--ink-200);
    font-size: var(--font-size-base);
    transition: border-color var(--transition-fast);
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--gold-400);
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
}

.status-badge {
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
}

.status-free {
    background: var(--surface-3);
    color: var(--ink-300);
}

.status-premium {
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    color: var(--ink-900);
}

.profile-picture-area {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--spacing-6);
}

.current-picture {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
}

.picture-placeholder {
    width: 80px;
    height: 80px;
    background: var(--surface-1);
    border: 2px dashed var(--ink-600);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-400);
}

.picture-info h4 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--ink-100);
    font-weight: var(--font-weight-semibold);
}

.picture-info p {
    margin: 0;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
}

.picture-actions {
    display: flex;
    gap: var(--spacing-3);
    flex-wrap: wrap;
}

.security-options {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}

.security-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
}

.security-info {
    flex: 1;
}

.security-info h4 {
    margin: 0 0 var(--spacing-1) 0;
    color: var(--ink-100);
    font-weight: var(--font-weight-semibold);
}

.security-info p {
    margin: 0;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-management-container {
        padding: var(--spacing-4);
    }
    
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .overview-grid {
        grid-template-columns: 1fr;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-picture-area {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .security-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .picture-actions {
        width: 100%;
        justify-content: flex-start;
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

/* Select Container */
.select-container {
    width: 100%;
}

.selection-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
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

.member-select {
    width: 100%;
    padding: var(--spacing-3) var(--spacing-4);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    color: var(--ink-100);
    font-size: var(--font-size-sm);
}

.member-select:focus {
    outline: none;
    border-color: var(--gold-400);
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
    border-radius: var(--radius-full);
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
    font-size: var(--font-size-base);
}

.member-details p {
    margin: 0 0 var(--spacing-2) 0;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: var(--spacing-1) var(--spacing-3);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

.status-badge.status-free {
    background: var(--surface-3);
    color: var(--ink-200);
    border: 1px solid var(--ink-500);
}

.status-badge.status-premium,
.status-badge.status-epic {
    background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
    color: var(--ink-900);
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

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--spacing-12) var(--spacing-6);
}

.empty-icon {
    color: var(--ink-400);
    margin-bottom: var(--spacing-6);
}

.empty-title {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-200);
    margin: 0 0 var(--spacing-3) 0;
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
</style>

<script>
// Preview member function
function previewMember(memberId) {
    if (memberId) {
        window.location.href = '<?= epic_url('admin/member-area/profile') ?>?member_id=' + memberId;
    } else {
        window.location.href = '<?= epic_url('admin/member-area/profile') ?>';
    }
}

function clearSelection() {
    window.location.href = '<?= epic_url('admin/member-area/profile') ?>';
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

// Password Modal Functions
function openPasswordModal() {
    const selectedMemberId = new URLSearchParams(window.location.search).get('member_id');
    if (!selectedMemberId) {
        alert('Silakan pilih member terlebih dahulu');
        return;
    }
    document.getElementById('passwordModal').style.display = 'flex';
    document.getElementById('memberIdInput').value = selectedMemberId;
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('passwordForm').reset();
}

function submitPasswordChange() {
    const form = document.getElementById('passwordForm');
    const formData = new FormData(form);
    
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');
    
    if (newPassword !== confirmPassword) {
        alert('Password baru dan konfirmasi password tidak cocok');
        return;
    }
    
    if (newPassword.length < 6) {
        alert('Password minimal 6 karakter');
        return;
    }
    
    // Submit form
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password berhasil diubah');
            closePasswordModal();
        } else {
            alert(data.message || 'Terjadi kesalahan saat mengubah password');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengubah password');
    });
}

// Initialize Feather icons and search
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    initializeSearch();
});
</script>

<!-- Password Change Modal -->
<div id="passwordModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ubah Password Member</h3>
            <button type="button" class="modal-close" onclick="closePasswordModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form id="passwordForm" onsubmit="event.preventDefault(); submitPasswordChange();">
            <input type="hidden" id="memberIdInput" name="member_id" value="">
            <input type="hidden" name="action" value="change_password">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Password Baru</label>
                    <input type="password" name="new_password" class="form-input" 
                           placeholder="Masukkan password baru" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-input" 
                           placeholder="Konfirmasi password baru" required>
                </div>
                
                <div class="form-help">
                    <i data-feather="info" width="16" height="16"></i>
                    Password minimal 6 karakter
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save" width="16" height="16"></i>
                    Ubah Password
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    color: #6b7280;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-label.required::after {
    content: ' *';
    color: #ef4444;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-help {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.alert-success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}
</style>