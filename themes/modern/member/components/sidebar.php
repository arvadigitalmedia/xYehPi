<?php
/**
 * EPIC Hub Member Sidebar Component
 * Sidebar navigation untuk member area - Identical to Admin
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

<aside class="admin-sidebar sidebar-dark-professional" x-data="{ collapsed: false }" :class="{ 'collapsed': collapsed }">
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
    </div>
        
    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="<?= epic_url('dashboard/member') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member', $current_url) && !isMenuActive('/dashboard/member/profile', $current_url) && !isMenuActive('/dashboard/member/products', $current_url) && !isMenuActive('/dashboard/member/orders', $current_url) && !isMenuActive('/dashboard/member/prospects', $current_url) ? 'active' : '' ?>">
            <i data-feather="home" width="18" height="18"></i>
            <span class="sidebar-nav-text">Dashboard</span>
        </a>
        
        <!-- Profile -->
        <a href="<?= epic_url('dashboard/member/profile') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/profile', $current_url) ? 'active' : '' ?>">
            <i data-feather="user" width="18" height="18"></i>
            <span class="sidebar-nav-text">Profile</span>
        </a>
        
        <!-- Products -->
        <a href="<?= epic_url('dashboard/member/products') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/products', $current_url) ? 'active' : '' ?>">
            <i data-feather="package" width="18" height="18"></i>
            <span class="sidebar-nav-text">Akses Produk</span>
        </a>
        
        <!-- Orders -->
        <a href="<?= epic_url('dashboard/member/orders') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/orders', $current_url) ? 'active' : '' ?>">
            <i data-feather="shopping-cart" width="18" height="18"></i>
            <span class="sidebar-nav-text">History Order</span>
        </a>
        
        <!-- Prospects (Premium Only) -->
        <?php if ($access_level !== 'free'): ?>
        <a href="<?= epic_url('dashboard/member/prospects') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/prospects', $current_url) ? 'active' : '' ?>">
            <i data-feather="users" width="18" height="18"></i>
            <span class="sidebar-nav-text">Prospek</span>
        </a>
        <?php endif; ?>
        
        <!-- Bonus (Premium Only) -->
        <?php if ($access_level !== 'free'): ?>
        <a href="<?= epic_url('dashboard/member/bonus') ?>" class="sidebar-nav-item <?= isMenuActive('/dashboard/member/bonus', $current_url) ? 'active' : '' ?>">
            <i data-feather="dollar-sign" width="18" height="18"></i>
            <span class="sidebar-nav-text">Bonus Cash</span>
        </a>
        <?php endif; ?>
        
        <!-- Divider -->
        <div class="sidebar-divider"></div>
        
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
    
    <!-- Upgrade Section (for Free Users) -->
    <?php if ($access_level === 'free'): ?>
        <div class="sidebar-upgrade">
            <div class="upgrade-card">
                <div class="upgrade-icon">
                    <i data-feather="star" width="24" height="24"></i>
                </div>
                <div class="upgrade-content">
                    <h4>Upgrade ke EPIC</h4>
                    <p>Dapatkan akses ke semua fitur premium</p>
                    <a href="<?= epic_url('upgrade') ?>" class="btn btn-upgrade">
                        <i data-feather="arrow-up" width="16" height="16"></i>
                        Upgrade Sekarang
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Footer Actions -->
    <div class="sidebar-footer">
        <div class="footer-actions">
            <!-- Collapse Toggle -->
            <button @click="collapsed = !collapsed" class="footer-link sidebar-toggle-btn" type="button" title="Toggle Sidebar">
                <i data-feather="sidebar" width="18" height="18"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" @click="sidebarOpen = !sidebarOpen">
        <i data-feather="menu" width="20" height="20"></i>
    </button>
</aside>

<style>
/* Sidebar Styles */
.member-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 280px;
    z-index: 1000;
    transition: transform 0.3s ease;
}

.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: none;
}

.sidebar-content {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.sidebar-logo .logo-image {
    height: 40px;
    width: auto;
}

.sidebar-logo .logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
}

.sidebar-close {
    display: none;
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: background-color 0.2s;
}

.sidebar-close:hover {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar-user {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.1);
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
    color: rgba(255, 255, 255, 0.7);
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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

.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
}

.nav-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    margin: 0.25rem 0;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.2s;
    position: relative;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.nav-item.active .nav-link {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-right: 3px solid white;
}

.nav-item.locked .nav-link {
    opacity: 0.5;
    cursor: not-allowed;
}

.nav-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
}

.nav-text {
    flex: 1;
    font-weight: 500;
}

.lock-icon {
    opacity: 0.6;
}

.sidebar-upgrade {
    padding: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
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

.sidebar-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.footer-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.2s;
}

.footer-link:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.mobile-menu-toggle {
    display: none;
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 1001;
    background: rgba(102, 126, 234, 0.9);
    color: white;
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem;
    cursor: pointer;
    backdrop-filter: blur(10px);
    transition: all 0.2s;
}

.mobile-menu-toggle:hover {
    background: rgba(102, 126, 234, 1);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .member-sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar-content.sidebar-open {
        transform: translateX(0);
    }
    
    .sidebar-overlay {
        display: block;
    }
    
    .sidebar-close {
        display: block;
    }
    
    .mobile-menu-toggle {
        display: block;
    }
}
</style>