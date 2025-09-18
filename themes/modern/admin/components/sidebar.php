<?php
/**
 * EPIC Hub Admin Sidebar Component
 * Global sidebar untuk semua halaman admin
 */

// Determine active menu based on current page
$current_url = $_SERVER['REQUEST_URI'] ?? '';
$current_page = $current_page ?? 'dashboard';

// Helper function to check if menu is active
function isMenuActive($menu_path, $current_url) {
    if (empty($current_url) || empty($menu_path)) {
        return false;
    }
    return strpos($current_url, $menu_path) !== false;
}

// Helper function to check if submenu should be expanded
function shouldExpandSubmenu($submenu_items, $current_url) {
    foreach ($submenu_items as $item) {
        if (isMenuActive($item['path'], $current_url)) {
            return true;
        }
    }
    return false;
}

// Define menu structure
$manage_submenu = [
    ['path' => '/admin/manage/member', 'text' => 'Member'],
    ['path' => '/admin/manage/epis', 'text' => 'EPIS Accounts'],
    ['path' => '/admin/manage/order', 'text' => 'Order'],
    ['path' => '/admin/manage/product', 'text' => 'Product'],
    ['path' => '/admin/manage/landing-page-manager', 'text' => 'Landing Page Manager'],
    ['path' => '/admin/event-scheduling', 'text' => 'Event Scheduling'],
    ['path' => '/admin/manage/payout', 'text' => 'Payout'],
    ['path' => '/admin/manage/finance', 'text' => 'Finance'],
    ['path' => '/admin/manage/update-price', 'text' => 'Update Price']
];

// Settings submenu removed - now using direct link to general settings

// Integrasi submenu
$integrasi_submenu = [
    ['path' => '/admin/integrasi/autoresponder-email', 'text' => 'Autoresponder Email'],
    ['path' => '/admin/zoom-integration', 'text' => 'Zoom Integration']
];

$dashboard_member_submenu = [
    ['path' => '/admin/member-area/home', 'text' => 'Home Dashboard'],
    ['path' => '/admin/member-area/profile', 'text' => 'Edit Profil'],
    ['path' => '/admin/member-area/prospects', 'text' => 'Prospek'],
    ['path' => '/admin/member-area/bonus', 'text' => 'Bonus Cash'],
    ['path' => '/admin/member-area/products', 'text' => 'Akses Produk'],
    ['path' => '/admin/member-area/orders', 'text' => 'History Order']
];
?>
<aside class="admin-sidebar" id="admin-sidebar">
    <div class="sidebar-header">
        <a href="<?= epic_url('admin') ?>" class="sidebar-logo">
            <?php 
            $site_logo = epic_setting('site_logo');
            if ($site_logo && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_logo)): 
            ?>
                <img src="<?= epic_url('uploads/logos/' . $site_logo) ?>" alt="<?= epic_setting('site_name', 'EPIC Hub') ?>" class="sidebar-logo-image">
            <?php else: ?>
                <div class="sidebar-logo-icon">EH</div>
                <span class="sidebar-logo-text">EPIC Hub</span>
            <?php endif; ?>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <!-- 1. Home -->
        <a href="<?= epic_url('admin') ?>" class="sidebar-nav-item <?= $current_url === '/admin' || $current_url === '/admin/' ? 'active' : '' ?>">
            <i data-feather="home" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Home</span>
        </a>
        
        <!-- 2. Edit Profile -->
        <a href="<?= epic_url('admin/edit-profile') ?>" class="sidebar-nav-item <?= isMenuActive('/admin/edit-profile', $current_url) ? 'active' : '' ?>">
            <i data-feather="user" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Edit Profile</span>
        </a>
        
        <!-- 3. Manage -->
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-item sidebar-nav-parent <?= shouldExpandSubmenu($manage_submenu, $current_url) ? 'expanded' : '' ?>" onclick="toggleSubmenu(this)">
                <i data-feather="settings" class="sidebar-nav-icon"></i>
                <span class="sidebar-nav-text">Manage</span>
                <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
            </div>
            <div class="sidebar-submenu <?= shouldExpandSubmenu($manage_submenu, $current_url) ? 'show' : '' ?>">
                <?php foreach ($manage_submenu as $item): ?>
                    <a href="<?= epic_url($item['path']) ?>" class="sidebar-submenu-item <?= isMenuActive($item['path'], $current_url) ? 'active' : '' ?>">
                        <span class="sidebar-submenu-text"><?= $item['text'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- 4. Settings -->
        <a href="<?= epic_url('admin/settings/general') ?>" class="sidebar-nav-item <?= isMenuActive('/admin/settings', $current_url) ? 'active' : '' ?>">
            <i data-feather="sliders" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Settings</span>
        </a>
        
        <!-- 5. Integrasi -->
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-item sidebar-nav-parent <?= shouldExpandSubmenu($integrasi_submenu, $current_url) ? 'expanded' : '' ?>" onclick="toggleSubmenu(this)">
                <i data-feather="zap" class="sidebar-nav-icon"></i>
                <span class="sidebar-nav-text">Integrasi</span>
                <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
            </div>
            <div class="sidebar-submenu <?= shouldExpandSubmenu($integrasi_submenu, $current_url) ? 'show' : '' ?>">
                <?php foreach ($integrasi_submenu as $item): ?>
                    <a href="<?= epic_url($item['path']) ?>" class="sidebar-submenu-item <?= isMenuActive($item['path'], $current_url) ? 'active' : '' ?>">
                        <span class="sidebar-submenu-text"><?= $item['text'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- 6. Dashboard Member -->
        <div class="sidebar-nav-group">
            <div class="sidebar-nav-item sidebar-nav-parent <?= shouldExpandSubmenu($dashboard_member_submenu, $current_url) ? 'expanded' : '' ?>" onclick="toggleSubmenu(this)">
                <i data-feather="users" class="sidebar-nav-icon"></i>
                <span class="sidebar-nav-text">Dashboard Member</span>
                <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
            </div>
            <div class="sidebar-submenu <?= shouldExpandSubmenu($dashboard_member_submenu, $current_url) ? 'show' : '' ?>">
                <?php foreach ($dashboard_member_submenu as $item): ?>
                    <a href="<?= epic_url($item['path']) ?>" class="sidebar-submenu-item <?= isMenuActive($item['path'], $current_url) ? 'active' : '' ?>">
                        <span class="sidebar-submenu-text"><?= $item['text'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- 7. Blog -->
        <a href="<?= epic_url('admin/blog') ?>" class="sidebar-nav-item <?= isMenuActive('/admin/blog', $current_url) ? 'active' : '' ?>">
            <i data-feather="edit-3" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Blog</span>
        </a>
        

        
        <!-- Separator -->
        <div class="sidebar-separator"></div>
        
        <!-- 9. Logout -->
        <a href="<?= epic_url('logout') ?>" class="sidebar-nav-item sidebar-logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">
            <i data-feather="log-out" class="sidebar-nav-icon"></i>
            <span class="sidebar-nav-text">Logout</span>
        </a>
    </nav>
    
    <!-- Collapse Button -->
    <button class="sidebar-collapse-btn" onclick="toggleSidebar()">
        <i data-feather="chevron-left" class="collapse-icon-left"></i>
        <i data-feather="chevron-right" class="collapse-icon-right" style="display: none;"></i>
    </button>
</aside>