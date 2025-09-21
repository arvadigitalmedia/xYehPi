<?php
/**
 * EPIC Hub Member Sidebar Component
 * Sidebar navigation untuk member area - Konsisten dengan Admin Panel
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Determine active menu based on current page
$current_url = $_SERVER['REQUEST_URI'];
$current_page = $current_page ?? 'home';

// Helper function to check if menu is active
function isMenuActive($menu_path, $current_url) {
    return strpos($current_url, $menu_path) !== false;
}

$user = epic_current_user();
$access_level = epic_get_member_access_level($user);

// Get site logo
$site_logo = epic_setting('site_logo');
$site_name = epic_setting('site_name', 'EPIC Hub');
?>

<aside class="admin-sidebar" :class="{ 'collapsed': sidebarCollapsed, 'mobile-open': mobileMenuOpen }" id="memberSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <a href="<?= epic_url('dashboard/member') ?>" class="sidebar-logo">
            <?php if ($site_logo && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_logo)): ?>
                <img src="<?= epic_url('uploads/logos/' . $site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>" class="sidebar-logo-image">
            <?php else: ?>
                <div class="sidebar-logo-icon">EH</div>
            <?php endif; ?>
            <span class="sidebar-logo-text"><?= htmlspecialchars($site_name) ?></span>
        </a>
        
        <!-- Mobile Close Button -->
        <button class="mobile-close-btn" @click="mobileMenuOpen = false" x-show="mobileMenuOpen">
            <i data-feather="x" width="20" height="20"></i>
        </button>
    </div>
        
    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="<?= epic_url('dashboard/member') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member', $current_url) && !isMenuActive('/dashboard/member/profile', $current_url) && !isMenuActive('/dashboard/member/products', $current_url) && !isMenuActive('/dashboard/member/orders', $current_url) && !isMenuActive('/dashboard/member/prospects', $current_url) ? 'active' : '' ?>">
            <i data-feather="home" width="18" height="18" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Dashboard</span>
        </a>
        
        <!-- Profile -->
        <a href="<?= epic_url('dashboard/member/profile') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/profile', $current_url) ? 'active' : '' ?>">
            <i data-feather="user" width="18" height="18" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Profile</span>
        </a>
        
        <!-- Products -->
        <a href="<?= epic_url('dashboard/member/products') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/products', $current_url) ? 'active' : '' ?>">
            <i data-feather="package" width="18" height="18" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Akses Produk</span>
        </a>
        
        <!-- Orders -->
        <a href="<?= epic_url('dashboard/member/orders') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/orders', $current_url) ? 'active' : '' ?>">
            <i data-feather="shopping-cart" width="18" height="18" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">History Order</span>
        </a>
        
        <!-- Prospects (Premium Only) -->
        <?php if ($access_level !== 'free'): ?>
        <a href="<?= epic_url('dashboard/member/prospects') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/prospects', $current_url) ? 'active' : '' ?>">
            <i data-feather="users" width="18" height="18" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Prospek</span>
        </a>
        <?php endif; ?>
        
        <!-- Bonus (Premium Only) -->
        <?php if ($access_level !== 'free'): ?>
        <a href="<?= epic_url('dashboard/member/bonus') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/bonus', $current_url) ? 'active' : '' ?>">
            <i data-feather="dollar-sign" width="18" height="18" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Bonus Cash</span>
        </a>
        <?php endif; ?>
        
        <!-- Separator -->
        <div class="sidebar-separator"></div>
        
        <!-- User Info Section -->
        <div class="sidebar-user-info">
            <div class="user-avatar">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= epic_url('uploads/profiles/' . $user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <i data-feather="user" width="20" height="20"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="user-level">
                    <?php 
                    $level_badges = [
                        'free' => ['text' => 'FREE', 'class' => 'badge-free'],
                        'epic' => ['text' => 'EPIC', 'class' => 'badge-epic'],
                        'epis' => ['text' => 'EPIS', 'class' => 'badge-epis']
                    ];
                    $badge = $level_badges[$access_level] ?? $level_badges['free'];
                    ?>
                    <span class="user-badge <?= $badge['class'] ?>">
                        <?= $badge['text'] ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Logout -->
        <a href="<?= epic_url('logout') ?>" class="sidebar-nav-item logout-item">
            <i data-feather="log-out" width="18" height="18"></i>
            <span class="sidebar-nav-text">Logout</span>
        </a>
    </nav>
    

    
    <!-- Footer Actions -->
    <div class="sidebar-footer">
        <div class="sidebar-footer-content">
            <button type="button" class="sidebar-toggle-btn" @click="toggleSidebar()" title="Toggle Sidebar">
                <i data-feather="chevron-left" width="16" height="16" x-show="!sidebarCollapsed" class="toggle-icon"></i>
                <i data-feather="chevron-right" width="16" height="16" x-show="sidebarCollapsed" class="toggle-icon"></i>
            </button>
            <span class="sidebar-footer-text" x-show="!sidebarCollapsed">EPIC Hub Member</span>
        </div>
    </div>
    
    <!-- Mobile Menu Toggle removed - using header toggle instead -->
</aside>



<style>
/* Member Sidebar Styles - Konsisten dengan Admin Panel */
.admin-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 2px 0 20px rgba(0,0,0,0.15);
    overflow: hidden;
}

.admin-sidebar.collapsed {
    width: 70px;
}

.sidebar-collapsed .admin-sidebar {
    width: 70px;
}

/* Sidebar Header */
.sidebar-header {
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    min-height: 80px;
    flex-shrink: 0;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: white;
}

.sidebar-logo-image {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.sidebar-logo-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

.sidebar-logo-text {
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
}

.admin-sidebar.collapsed .sidebar-logo-text,
.sidebar-collapsed .admin-sidebar .sidebar-logo-text {
    opacity: 0;
    width: 0;
}

/* Mobile Close Button */
.mobile-close-btn {
    display: none;
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 0.625rem;
    border-radius: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1001;
}

.mobile-close-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

.mobile-close-btn:active {
    transform: scale(0.95);
    background: rgba(255, 255, 255, 0.3);
}

/* Navigation */
.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 3px solid transparent;
    position: relative;
    white-space: nowrap;
}

.sidebar-nav-item:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    border-left-color: rgba(255,255,255,0.3);
}

.sidebar-nav-item.active {
    background: rgba(255,255,255,0.15);
    color: white;
    border-left-color: #ffd700;
    box-shadow: inset 0 0 0 1px rgba(255,215,0,0.2);
}

.sidebar-nav-icon {
    flex-shrink: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-nav-text {
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.admin-sidebar.collapsed .sidebar-nav-text,
.sidebar-collapsed .admin-sidebar .sidebar-nav-text {
    opacity: 0;
    width: 0;
}

.admin-sidebar.collapsed .sidebar-nav-item,
.sidebar-collapsed .admin-sidebar .sidebar-nav-item {
    justify-content: center;
    padding-left: 1.25rem;
    padding-right: 1.25rem;
}

/* Separator */
.sidebar-separator {
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 1rem 0;
}

/* User Info */
.sidebar-user-info {
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    color: rgba(255,255,255,0.7);
}

.user-details {
    flex: 1;
    overflow: hidden;
}

.user-name {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
    color: white;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-level {
    margin: 0;
}

.user-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}

.badge-free {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.badge-epic {
    background: rgba(233, 30, 99, 0.2);
    color: #e91e63;
    border: 1px solid rgba(233, 30, 99, 0.3);
}

.badge-epis {
    background: rgba(76, 175, 80, 0.2);
    color: #4caf50;
    border: 1px solid rgba(76, 175, 80, 0.3);
}

.admin-sidebar.collapsed .user-details,
.sidebar-collapsed .admin-sidebar .user-details {
    opacity: 0;
    width: 0;
}

/* Logout Item */
.logout-item {
    margin-top: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    padding-top: 1rem;
}

/* Upgrade Section */
.sidebar-upgrade {
    padding: 1rem;
    margin: 1rem;
    background: rgba(0,0,0,0.2);
    border-radius: 8px;
    text-align: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.upgrade-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 1.5rem;
    text-align: center;
    backdrop-filter: blur(10px);
}

.upgrade-icon {
    margin-bottom: 1rem;
    color: #ffc107;
}

.upgrade-content h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.upgrade-content p {
    margin: 0 0 1rem 0;
    font-size: 0.85rem;
    opacity: 0.8;
}

.btn-upgrade {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #ffc107, #ff9800);
    color: #000;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    transition: transform 0.2s;
}

.btn-upgrade:hover {
    transform: translateY(-1px);
    color: #000;
}

.admin-sidebar.collapsed .sidebar-upgrade,
.sidebar-collapsed .admin-sidebar .sidebar-upgrade {
    opacity: 0;
    margin: 0;
    padding: 0;
    height: 0;
    overflow: hidden;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    flex-shrink: 0;
}

.sidebar-footer-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sidebar-toggle-btn {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    padding: 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sidebar-toggle-btn:hover {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.3);
    transform: scale(1.05);
}

.toggle-icon {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-footer-text {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.7);
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
}

/* Mobile Menu Toggle - removed, using header toggle instead */

/* Content Area Adjustment - Handled by layout system */

/* Mobile Overlay - hidden by default, only shown on mobile when needed */
.sidebar-overlay {
    display: none;
}

/* Tablet Responsive */
@media (max-width: 1024px) and (min-width: 769px) {
    .admin-sidebar {
        width: 260px;
    }
    
    .admin-sidebar.collapsed {
        width: 70px;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
        width: 280px !important;
        z-index: 1001;
        transition: transform 0.3s ease;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
    }
    
    .admin-sidebar.mobile-open {
        transform: translateX(0);
    }
    
    /* Mobile menu toggle removed - using header toggle instead */
    
    body.has-sidebar {
        margin-left: 0;
    }
    
    body.has-sidebar.sidebar-collapsed {
        margin-left: 0;
    }
    
    /* Sidebar overlay - only active on mobile, controlled by Alpine.js x-show */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        /* Enable overlay on mobile - Alpine.js x-show will control visibility */
        display: block;
    }
    
    /* Ensure sidebar is visible when mobile menu is open */
    .admin-sidebar.mobile-open {
        display: flex !important;
        transform: translateX(0) !important;
    }
    
    /* Hide desktop sidebar toggle in mobile */
    .sidebar-toggle-btn {
        display: none;
    }
    
    /* Show mobile close button */
    .mobile-close-btn {
        display: flex !important;
    }
}

/* Small Mobile */
@media (max-width: 480px) {
    .admin-sidebar {
        width: 100% !important;
    }
    
    .sidebar-nav-text {
        font-size: 0.9rem;
    }
}

/* Scrollbar Styling */
.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}
</style>