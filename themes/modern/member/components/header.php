<?php
/**
 * EPIC Hub Member Header Component
 * Header/topbar untuk member area
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

$user = epic_current_user();
$access_level = epic_get_member_access_level($user);
$current_page = $current_page ?? 'home';

// Page titles
$page_titles = [
    'home' => 'Dashboard',
    'profile' => 'Edit Profil',
    'prospects' => 'Manajemen Prospek',
    'bonus' => 'Bonus Cash',
    'products' => 'Akses Produk',
    'orders' => 'History Order'
];

$page_title = $page_titles[$current_page] ?? 'Member Area';
?>

<header class="member-header">
    <div class="header-content">
        <!-- Left Section -->
        <div class="header-left">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-btn" @click="mobileMenuOpen = !mobileMenuOpen">
                <i data-feather="menu" width="20" height="20"></i>
            </button>
            
            <!-- Page Title -->
            <div class="page-title-section">
                <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
                <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
                    <nav class="breadcrumb">
                        <?php foreach ($breadcrumb as $index => $item): ?>
                            <?php if ($index > 0): ?>
                                <span class="breadcrumb-separator">/</span>
                            <?php endif; ?>
                            <?php if (isset($item['url'])): ?>
                                <a href="<?= $item['url'] ?>" class="breadcrumb-link">
                                    <?= htmlspecialchars($item['text']) ?>
                                </a>
                            <?php else: ?>
                                <span class="breadcrumb-current">
                                    <?= htmlspecialchars($item['text']) ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Right Section -->
        <div class="header-right">
            <!-- Quick Stats (for EPIC/EPIS users) - Horizontal Layout -->
            <?php if (in_array($access_level, ['epic', 'epis'])): ?>
                <?php $stats = epic_get_member_stats($user); ?>
                <div class="quick-stats-horizontal">
                    <div class="stat-item-horizontal">
                        <div class="stat-icon-gold">
                            <i data-feather="dollar-sign" width="14" height="14"></i>
                        </div>
                        <div class="stat-content-compact">
                            <span class="stat-value-gold">Rp <?= number_format($stats['total_earnings'], 0, ',', '.') ?></span>
                            <span class="stat-label-compact">Earnings</span>
                        </div>
                    </div>
                    
                    <div class="stat-divider"></div>
                    
                    <div class="stat-item-horizontal">
                        <div class="stat-icon-gold">
                            <i data-feather="users" width="14" height="14"></i>
                        </div>
                        <div class="stat-content-compact">
                            <span class="stat-value-gold"><?= $stats['active_referrals'] ?></span>
                            <span class="stat-label-compact">Referrals</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Notifications -->
            <div class="header-notifications" x-data="{ open: false }">
                <button class="notification-btn" @click="open = !open">
                    <i data-feather="bell" width="20" height="20"></i>
                    <span class="notification-badge">3</span>
                </button>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" x-show="open" x-cloak @click.away="open = false">
                    <div class="dropdown-header">
                        <h4>Notifikasi</h4>
                        <button class="mark-all-read">Tandai Semua Dibaca</button>
                    </div>
                    
                    <div class="notification-list">
                        <div class="notification-item unread">
                            <div class="notification-icon">
                                <i data-feather="dollar-sign" width="16" height="16"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Komisi Baru</div>
                                <div class="notification-text">Anda mendapat komisi Rp 50.000</div>
                                <div class="notification-time">2 jam yang lalu</div>
                            </div>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i data-feather="user-plus" width="16" height="16"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Referral Baru</div>
                                <div class="notification-text">John Doe bergabung melalui link Anda</div>
                                <div class="notification-time">1 hari yang lalu</div>
                            </div>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i data-feather="package" width="16" height="16"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Produk Baru</div>
                                <div class="notification-text">Produk baru tersedia untuk Anda</div>
                                <div class="notification-time">3 hari yang lalu</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dropdown-footer">
                        <a href="#" class="view-all-link">Lihat Semua Notifikasi</a>
                    </div>
                </div>
            </div>
            
            <!-- Profile Icon - Direct Link to Edit Profile -->
            <div class="header-profile-icon">
                <a href="<?= epic_url('dashboard/member/profile') ?>" class="profile-link" title="Edit Profil">
                    <div class="profile-avatar-gold">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= epic_url('uploads/profiles/' . $user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>" class="avatar-image-gold">
                        <?php else: ?>
                            <div class="avatar-placeholder-gold">
                                <i data-feather="user" width="20" height="20"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        </div>
    </div>
</header>

<style>
/* ===== HEADER SOFT GOLD ELEGANT THEME ===== */

/* Header Container */
.member-header {
    background: linear-gradient(135deg, #0F0F0F 0%, #000000 100%);
    border-bottom: 2px solid #D4AF37;
    padding: 0;
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(212, 175, 55, 0.15), 0 2px 10px rgba(0, 0, 0, 0.8);
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 2rem;
    max-width: 100%;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.mobile-menu-btn {
    display: none;
    background: rgba(212, 175, 55, 0.1);
    color: #D4AF37;
    border: 1px solid rgba(212, 175, 55, 0.3);
    padding: 0.75rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    cursor: pointer;
    z-index: 1002;
}

.mobile-menu-btn:hover {
    background: rgba(212, 175, 55, 0.15);
    color: #E6C068;
    box-shadow: 0 0 8px rgba(212, 175, 55, 0.3);
}

.page-title-section {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #FFFFFF;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #B0B0B0;
}

.breadcrumb-link {
    color: #D4AF37;
    text-decoration: none;
    transition: color 0.2s;
}

.breadcrumb-link:hover {
    color: #E6C068;
    text-shadow: 0 0 4px rgba(212, 175, 55, 0.4);
}

.breadcrumb-separator {
    color: #6A6A6A;
}

.breadcrumb-current {
    color: #FFFFFF;
    font-weight: 500;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

/* ===== HORIZONTAL STATISTICS STYLING ===== */
.quick-stats-horizontal {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(212, 175, 55, 0.08);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    backdrop-filter: blur(10px);
}

.stat-item-horizontal {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stat-icon-gold {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #D4AF37, #C9A96E);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2A2A2A;
    box-shadow: 0 2px 6px rgba(212, 175, 55, 0.25);
}

.stat-content-compact {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.stat-value-gold {
    font-size: 0.875rem;
    font-weight: 600;
    color: #D4AF37;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.stat-label-compact {
    font-size: 0.75rem;
    color: #B8B8B8;
    font-weight: 500;
}

.stat-divider {
    width: 1px;
    height: 24px;
    background: linear-gradient(to bottom, transparent, rgba(212, 175, 55, 0.4), transparent);
}

/* ===== PROFILE ICON STYLING ===== */
.header-profile-icon {
    display: flex;
    align-items: center;
}

.profile-link {
    display: block;
    text-decoration: none;
    transition: all 0.3s ease;
}

.profile-link:hover {
    transform: scale(1.05);
}

.profile-avatar-gold {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #D4AF37;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #D4AF37, #C9A96E);
    box-shadow: 0 3px 12px rgba(212, 175, 55, 0.3), 0 0 15px rgba(212, 175, 55, 0.15);
    transition: all 0.3s ease;
}

.profile-avatar-gold:hover {
    box-shadow: 0 4px 16px rgba(212, 175, 55, 0.4), 0 0 20px rgba(212, 175, 55, 0.25);
    border-color: #E6C068;
}

.avatar-image-gold {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.avatar-placeholder-gold {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #D4AF37, #C9A96E);
    color: #2A2A2A;
}

/* ===== NOTIFICATION STYLING ===== */
.header-notifications {
    position: relative;
}

.notification-btn {
    background: rgba(212, 175, 55, 0.08);
    border: 1px solid rgba(212, 175, 55, 0.2);
    color: #D4AF37;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.2s;
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-btn:hover {
    background: rgba(212, 175, 55, 0.15);
    border-color: #D4AF37;
    box-shadow: 0 0 8px rgba(212, 175, 55, 0.25);
}

.notification-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: linear-gradient(135deg, #FF4444, #CC0000);
    color: white;
    font-size: 0.625rem;
    font-weight: 600;
    padding: 0.125rem 0.375rem;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* ===== SIDEBAR DARK PROFESSIONAL THEME ===== */
.sidebar-dark-professional {
    background: linear-gradient(180deg, #0F0F0F 0%, #000000 100%) !important;
    border-right: 2px solid rgba(208, 208, 208, 0.3) !important;
    box-shadow: 2px 0 15px rgba(0, 0, 0, 0.8) !important;
}

.sidebar-dark-professional .sidebar-header {
    background: linear-gradient(135deg, #0F0F0F 0%, #000000 100%) !important;
}

.sidebar-dark-professional .sidebar-nav-item {
    color: #B8B8B8 !important;
    border-radius: 8px !important;
    margin: 0.25rem 0.75rem !important;
    transition: all 0.3s ease !important;
}

.sidebar-dark-professional .sidebar-nav-item:hover {
    background: rgba(212, 175, 55, 0.1) !important;
    color: #D4AF37 !important;
    transform: translateX(4px) !important;
}

.sidebar-dark-professional .sidebar-nav-item.active {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.08)) !important;
    color: #D4AF37 !important;
    border-left: 3px solid #D4AF37 !important;
    font-weight: 600 !important;
}

.sidebar-dark-professional .sidebar-divider {
    background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.3), transparent) !important;
    height: 1px !important;
    margin: 1rem 0.75rem !important;
}

.sidebar-dark-professional .sidebar-user-info {
    background: rgba(15, 15, 15, 0.8) !important;
    border: 1px solid rgba(212, 175, 55, 0.2) !important;
    border-radius: 12px !important;
    margin: 0.75rem !important;
    padding: 1rem !important;
}

.sidebar-dark-professional .user-badge {
    background: linear-gradient(135deg, #D4AF37, #C9A96E) !important;
    color: #000000 !important;
    font-weight: 600 !important;
    padding: 0.25rem 0.5rem !important;
    border-radius: 6px !important;
    font-size: 0.75rem !important;
}

.sidebar-dark-professional .logout-item {
    background: rgba(255, 68, 68, 0.1) !important;
    color: #FF6B6B !important;
    border: 1px solid rgba(255, 68, 68, 0.3) !important;
}

.sidebar-dark-professional .logout-item:hover {
    background: rgba(255, 68, 68, 0.2) !important;
    color: #FF4444 !important;
    transform: translateX(4px) !important;
}

.quick-stats {
    display: flex;
    gap: 1.5rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 50%;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1;
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 320px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    margin-top: 0.5rem;
}

.dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.dropdown-header h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
}

.mark-all-read {
    background: none;
    border: none;
    color: #6366f1;
    font-size: 0.875rem;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    transition: background-color 0.2s;
}

.mark-all-read:hover {
    background: #f0f9ff;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    gap: 0.75rem;
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s;
    cursor: pointer;
}

.notification-item:hover {
    background: #f9fafb;
}

.notification-item.unread {
    background: #f0f9ff;
    border-left: 3px solid #3b82f6;
}

.notification-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 50%;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    color: #111827;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.notification-text {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.notification-time {
    color: #9ca3af;
    font-size: 0.75rem;
}

.dropdown-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.view-all-link {
    color: #6366f1;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: color 0.2s;
}

.view-all-link:hover {
    color: #4f46e5;
}

.header-user-menu {
    position: relative;
}

.user-menu-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.75rem;
    transition: all 0.2s;
}

.user-menu-btn:hover {
    background: #f3f4f6;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    color: #6b7280;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
}

.user-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    line-height: 1;
    margin-bottom: 0.125rem;
}

.user-level {
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1;
}

.dropdown-arrow {
    color: #6b7280;
    transition: transform 0.2s;
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 240px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    margin-top: 0.5rem;
}

.user-info-full {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.user-info-full .user-name {
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.user-email {
    font-size: 0.875rem;
    color: #6b7280;
}

.dropdown-menu {
    padding: 0.5rem;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    color: #374151;
    text-decoration: none;
    border-radius: 0.5rem;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.dropdown-item:hover {
    background: #f3f4f6;
    color: #111827;
}

.upgrade-item {
    color: #f59e0b;
}

.upgrade-item:hover {
    background: #fef3c7;
    color: #d97706;
}

.logout-item {
    color: #ef4444;
}

.logout-item:hover {
    background: #fee2e2;
    color: #dc2626;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .header-content {
        padding: 1rem;
    }
    
    .mobile-menu-btn {
        display: block !important;
        position: relative;
        z-index: 1002;
    }
    
    .page-title {
        font-size: 1.25rem;
    }
    
    .quick-stats-horizontal {
        display: none;
    }
    
    .user-info {
        display: none;
    }
    
    .notification-dropdown,
    .user-dropdown {
        width: 280px;
    }
}

@media (max-width: 480px) {
    .header-content {
        padding: 0.75rem;
    }
    
    .header-right {
        gap: 0.75rem;
    }
    
    .notification-dropdown,
    .user-dropdown {
        width: calc(100vw - 2rem);
        right: -1rem;
    }
}
</style>