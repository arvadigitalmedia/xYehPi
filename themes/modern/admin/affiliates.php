<?php
/**
 * EPIC Hub Admin - Affiliates Management
 * Premium Dark Gold Theme
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Check admin access
$user = epic_current_user();
if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
    epic_route_403();
    return;
}

// Get affiliates data (mock data for demo)
$affiliates = [
    [
        'id' => 1,
        'name' => 'Bustanul Arifin',
        'email' => 'bustanul@email.com',
        'tier' => 'Gold',
        'status' => 'Active',
        'join_date' => '2025-01-15',
        'conversions' => 156,
        'revenue' => 32500000,
        'balance' => 1625000,
        'last_active' => '2025-09-07 09:14:00'
    ],
    [
        'id' => 2,
        'name' => 'Siti Maryam',
        'email' => 'siti@email.com',
        'tier' => 'Silver',
        'status' => 'Active',
        'join_date' => '2025-02-20',
        'conversions' => 89,
        'revenue' => 18750000,
        'balance' => 937500,
        'last_active' => '2025-09-07 08:32:00'
    ],
    [
        'id' => 3,
        'name' => 'Ahmad Hidayat',
        'email' => 'ahmad@email.com',
        'tier' => 'Bronze',
        'status' => 'Pending',
        'join_date' => '2025-09-05',
        'conversions' => 12,
        'revenue' => 2400000,
        'balance' => 120000,
        'last_active' => '2025-09-07 07:15:00'
    ],
    [
        'id' => 4,
        'name' => 'Rina Novita',
        'email' => 'rina@email.com',
        'tier' => 'Gold',
        'status' => 'Active',
        'join_date' => '2024-11-10',
        'conversions' => 234,
        'revenue' => 45600000,
        'balance' => 2280000,
        'last_active' => '2025-09-06 23:45:00'
    ],
    [
        'id' => 5,
        'name' => 'Dedi Wijaya',
        'email' => 'dedi@email.com',
        'tier' => 'Silver',
        'status' => 'Suspended',
        'join_date' => '2025-03-12',
        'conversions' => 67,
        'revenue' => 8900000,
        'balance' => 0,
        'last_active' => '2025-09-06 22:18:00'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliates - EPIC Hub Admin</title>
    
    <!-- Fonts -->
    <!-- Google Fonts removed to eliminate external dependency -->
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.css">
    
    <!-- Admin Styles -->
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/admin.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/components.css') ?>">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="affiliatesApp()" x-init="init()">
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" :class="{ 'collapsed': sidebarCollapsed }">
            <div class="sidebar-header">
                <a href="<?= epic_url('admin') ?>" class="sidebar-logo">
                    <div class="sidebar-logo-icon">EH</div>
                    <span class="sidebar-logo-text">EPIC Hub</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <!-- 1. Home -->
                <a href="<?= epic_url('admin') ?>" class="sidebar-nav-item">
                    <i data-feather="home" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Home</span>
                </a>
                
                <!-- 2. Edit Profile -->
                <div class="sidebar-nav-item" onclick="openProfileModal()">
                    <i data-feather="user" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Edit Profile</span>
                </div>
                
                <!-- 3. Dashboard Member -->
                <div class="sidebar-nav-group">
                    <div class="sidebar-nav-item sidebar-nav-parent" onclick="toggleSubmenu(this)">
                        <i data-feather="users" class="sidebar-nav-icon"></i>
                        <span class="sidebar-nav-text">Dashboard Member</span>
                        <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
                    </div>
                    <div class="sidebar-submenu">
                        <a href="<?= epic_url('admin/member-area/home') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Home Dashboard</span>
                        </a>
                        <a href="<?= epic_url('admin/member-area/profile') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Edit Profil</span>
                        </a>
                        <a href="<?= epic_url('admin/member-area/prospects') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Prospek</span>
                        </a>
                        <a href="<?= epic_url('admin/member-area/bonus') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Bonus Cash</span>
                        </a>
                        <a href="<?= epic_url('admin/member-area/products') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Akses Produk</span>
                        </a>
                        <a href="<?= epic_url('admin/member-area/orders') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">History Order</span>
                        </a>
                    </div>
                </div>
                
                <!-- 4. Manage -->
                <div class="sidebar-nav-group">
                    <div class="sidebar-nav-item sidebar-nav-parent expanded" onclick="toggleSubmenu(this)">
                        <i data-feather="settings" class="sidebar-nav-icon"></i>
                        <span class="sidebar-nav-text">Manage</span>
                        <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
                    </div>
                    <div class="sidebar-submenu expanded">
                        <a href="<?= epic_url('admin/member') ?>" class="sidebar-submenu-item active">
                            <span class="sidebar-submenu-text">Member</span>
                        </a>
                        <a href="<?= epic_url('admin/order') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Order</span>
                        </a>
                        <a href="<?= epic_url('admin/product') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Product</span>
                        </a>
                        <a href="<?= epic_url('admin/landing-page') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Landing Page</span>
                        </a>
                        <a href="<?= epic_url('admin/payout') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Payout</span>
                        </a>
                        <a href="<?= epic_url('admin/finance') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Finance</span>
                        </a>
                        <a href="<?= epic_url('admin/update-price') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Update Price</span>
                        </a>
                    </div>
                </div>
                
                <!-- 5. Integrasi -->
                <div class="sidebar-nav-group">
                    <div class="sidebar-nav-item sidebar-nav-parent" onclick="toggleSubmenu(this)">
                        <i data-feather="zap" class="sidebar-nav-icon"></i>
                        <span class="sidebar-nav-text">Integrasi</span>
                        <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
                    </div>
                    <div class="sidebar-submenu">
                        <a href="<?= epic_url('admin/integrasi/autoresponder-email') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Autoresponder Email</span>
                        </a>
                        <a href="<?= epic_url('admin/zoom-integration') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Zoom Integration</span>
                        </a>
                    </div>
                </div>
                
                <!-- 6. Blog -->
                <a href="<?= epic_url('admin/blog') ?>" class="sidebar-nav-item">
                    <i data-feather="edit-3" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Blog</span>
                </a>
                
                <!-- 7. Settings -->
                <div class="sidebar-nav-group">
                    <div class="sidebar-nav-item sidebar-nav-parent" onclick="toggleSubmenu(this)">
                        <i data-feather="sliders" class="sidebar-nav-icon"></i>
                        <span class="sidebar-nav-text">Settings</span>
                        <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
                    </div>
                    <div class="sidebar-submenu">
                        <a href="<?= epic_url('admin/settings/general') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">General</span>
                        </a>
                        <a href="<?= epic_url('admin/settings/form-registrasi') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Form Registrasi</span>
                        </a>
                        <a href="<?= epic_url('admin/settings/email-notification') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Email Notification</span>
                        </a>
                        <a href="<?= epic_url('admin/settings/whatsapp-notification') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">WhatsApp Notification</span>
                        </a>
                        <a href="<?= epic_url('admin/settings/payment-gateway') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Payment Gateway</span>
                        </a>
                    </div>
                </div>
                <div class="sidebar-nav-group">
                    <div class="sidebar-nav-item sidebar-nav-parent" onclick="toggleSubmenu(this)">
                        <i data-feather="users" class="sidebar-nav-icon"></i>
                        <span class="sidebar-nav-text">Dashboard Member</span>
                        <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
                    </div>
                    <div class="sidebar-submenu">
                        <a href="<?= epic_url('admin/dashboard-member/prospek') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Prospek</span>
                        </a>
                        <a href="<?= epic_url('admin/dashboard-member/bonus-cash') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Bonus Cash</span>
                        </a>
                        <a href="<?= epic_url('admin/dashboard-member/akses-produk') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Akses Produk</span>
                        </a>
                        <a href="<?= epic_url('admin/dashboard-member/history-order') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">History Order</span>
                        </a>
                    </div>
                </div>
                
                <!-- 6. Blog -->
                <a href="<?= epic_url('admin/blog') ?>" class="sidebar-nav-item">
                    <i data-feather="edit-3" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Blog</span>
                </a>
                

                
                <!-- Separator -->
                <div class="sidebar-separator"></div>
                
                <!-- 8. Logout -->
                <a href="<?= epic_url('logout') ?>" class="sidebar-nav-item sidebar-logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                    <i data-feather="log-out" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Logout</span>
                </a>
            </nav>
            
            <button class="sidebar-collapse-btn" @click="toggleSidebar()">
                <i data-feather="chevron-left" x-show="!sidebarCollapsed"></i>
                <i data-feather="chevron-right" x-show="sidebarCollapsed"></i>
            </button>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" aria-label="Toggle mobile menu">
                        <i data-feather="menu" width="20" height="20"></i>
                    </button>
                    
                    <h1 class="topbar-title">Affiliates</h1>
                    <nav class="topbar-breadcrumb">
                        <a href="<?= epic_url('admin') ?>">Admin</a>
                        <span class="breadcrumb-separator">/</span>
                        <span>Affiliates</span>
                    </nav>
                </div>
                
                <div class="topbar-right">
                    <div class="topbar-actions">
                        <button class="topbar-btn" data-modal="inviteAffiliateModal">
                            <i data-feather="user-plus" width="16" height="16"></i>
                            <span>Invite Affiliate</span>
                        </button>
                        
                        <button class="topbar-btn secondary">
                            <i data-feather="download" width="16" height="16"></i>
                            <span>Export Data</span>
                        </button>
                    </div>
                    
                    <div class="topbar-notifications">
                        <i data-feather="bell" width="20" height="20"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    
                    <div class="topbar-avatar" onclick="window.location.href='<?= epic_url('admin/edit-profile') ?>'" style="cursor: pointer;" title="Edit Profile">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img src="<?= epic_url('uploads/profiles/' . $user['profile_photo']) ?>" alt="Profile" class="avatar-image">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?= strtoupper(substr($user['name'], 0, 2)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <!-- Filters & Search -->
                <div class="data-table-card">
                    <div class="table-header">
                        <h3 class="table-title">Manage Affiliates</h3>
                        <div class="table-filters">
                            <div class="filter-group">
                                <input type="text" 
                                       class="form-input" 
                                       placeholder="Search affiliates..." 
                                       x-model="searchTerm"
                                       @input="filterAffiliates()">
                            </div>
                            <div class="filter-group">
                                <select class="form-select" x-model="statusFilter" @change="filterAffiliates()">
                                    <option value="">All Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Suspended">Suspended</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <select class="form-select" x-model="tierFilter" @change="filterAffiliates()">
                                    <option value="">All Tiers</option>
                                    <option value="Gold">Gold</option>
                                    <option value="Silver">Silver</option>
                                    <option value="Bronze">Bronze</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Affiliate</th>
                                    <th>Tier</th>
                                    <th>Status</th>
                                    <th>Join Date</th>
                                    <th>Conversions</th>
                                    <th>Revenue</th>
                                    <th>Balance</th>
                                    <th>Last Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="affiliate in filteredAffiliates" :key="affiliate.id">
                                    <tr>
                                        <td>
                                            <div class="table-user">
                                                <div class="user-avatar" x-text="affiliate.name.split(' ').map(n => n[0]).join('').toUpperCase()"></div>
                                                <div class="user-info">
                                                    <div class="user-name" x-text="affiliate.name"></div>
                                                    <div class="user-email" x-text="affiliate.email"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" 
                                                  :class="{
                                                      'badge-gold': affiliate.tier === 'Gold',
                                                      'badge-info': affiliate.tier === 'Silver',
                                                      'badge-warning': affiliate.tier === 'Bronze'
                                                  }"
                                                  x-text="affiliate.tier"></span>
                                        </td>
                                        <td>
                                            <span class="badge" 
                                                  :class="{
                                                      'badge-success': affiliate.status === 'Active',
                                                      'badge-warning': affiliate.status === 'Pending',
                                                      'badge-danger': affiliate.status === 'Suspended'
                                                  }"
                                                  x-text="affiliate.status"></span>
                                        </td>
                                        <td>
                                            <span class="table-date" x-text="formatDate(affiliate.join_date)"></span>
                                        </td>
                                        <td>
                                            <span class="table-amount" x-text="affiliate.conversions.toLocaleString('id-ID')"></span>
                                        </td>
                                        <td>
                                            <span class="table-amount" x-text="formatCurrency(affiliate.revenue)"></span>
                                        </td>
                                        <td>
                                            <span class="table-commission" x-text="formatCurrency(affiliate.balance)"></span>
                                        </td>
                                        <td>
                                            <span class="table-date" x-text="formatDateTime(affiliate.last_active)"></span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <button class="btn btn-sm btn-outline" 
                                                        @click="viewAffiliate(affiliate)"
                                                        data-tooltip="View Details">
                                                    <i data-feather="eye" width="14" height="14"></i>
                                                </button>
                                                
                                                <template x-if="affiliate.status === 'Pending'">
                                                    <button class="btn btn-sm btn-success" 
                                                            @click="approveAffiliate(affiliate.id)"
                                                            data-tooltip="Approve">
                                                        <i data-feather="check" width="14" height="14"></i>
                                                    </button>
                                                </template>
                                                
                                                <template x-if="affiliate.status === 'Active'">
                                                    <button class="btn btn-sm btn-warning" 
                                                            @click="suspendAffiliate(affiliate.id)"
                                                            data-tooltip="Suspend">
                                                        <i data-feather="pause" width="14" height="14"></i>
                                                    </button>
                                                </template>
                                                
                                                <template x-if="affiliate.status === 'Suspended'">
                                                    <button class="btn btn-sm btn-success" 
                                                            @click="activateAffiliate(affiliate.id)"
                                                            data-tooltip="Activate">
                                                        <i data-feather="play" width="14" height="14"></i>
                                                    </button>
                                                </template>
                                                
                                                <button class="btn btn-sm btn-outline" 
                                                        @click="messageAffiliate(affiliate)"
                                                        data-tooltip="Send Message">
                                                    <i data-feather="message-circle" width="14" height="14"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        
                        <!-- Empty State -->
                        <div x-show="filteredAffiliates.length === 0" class="empty-state">
                            <div class="empty-state-icon">
                                <i data-feather="users" width="64" height="64"></i>
                            </div>
                            <h3 class="empty-state-title">No Affiliates Found</h3>
                            <p class="empty-state-message">
                                <span x-show="searchTerm || statusFilter || tierFilter">
                                    No affiliates match your current filters. Try adjusting your search criteria.
                                </span>
                                <span x-show="!searchTerm && !statusFilter && !tierFilter">
                                    You haven't added any affiliates yet. Start by inviting your first affiliate.
                                </span>
                            </p>
                            <button class="btn btn-primary" data-modal="inviteAffiliateModal">
                                <i data-feather="user-plus" width="16" height="16"></i>
                                <span>Invite First Affiliate</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Affiliate Detail Drawer -->
    <div class="drawer-overlay" id="affiliateDetailDrawer">
        <div class="drawer">
            <div class="drawer-header">
                <h3 class="drawer-title">Affiliate Details</h3>
                <button class="drawer-close">
                    <i data-feather="x" width="20" height="20"></i>
                </button>
            </div>
            <div class="drawer-body" x-show="selectedAffiliate">
                <template x-if="selectedAffiliate">
                    <div>
                        <!-- Affiliate Info -->
                        <div class="affiliate-info">
                            <div class="affiliate-avatar">
                                <div class="user-avatar large" x-text="selectedAffiliate.name.split(' ').map(n => n[0]).join('').toUpperCase()"></div>
                            </div>
                            <div class="affiliate-details">
                                <h4 x-text="selectedAffiliate.name"></h4>
                                <p x-text="selectedAffiliate.email"></p>
                                <div class="affiliate-badges">
                                    <span class="badge" 
                                          :class="{
                                              'badge-gold': selectedAffiliate.tier === 'Gold',
                                              'badge-info': selectedAffiliate.tier === 'Silver',
                                              'badge-warning': selectedAffiliate.tier === 'Bronze'
                                          }"
                                          x-text="selectedAffiliate.tier"></span>
                                    <span class="badge" 
                                          :class="{
                                              'badge-success': selectedAffiliate.status === 'Active',
                                              'badge-warning': selectedAffiliate.status === 'Pending',
                                              'badge-danger': selectedAffiliate.status === 'Suspended'
                                          }"
                                          x-text="selectedAffiliate.status"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stats -->
                        <div class="affiliate-stats">
                            <div class="stat-item">
                                <div class="stat-label">Total Conversions</div>
                                <div class="stat-value" x-text="selectedAffiliate.conversions.toLocaleString('id-ID')"></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Total Revenue</div>
                                <div class="stat-value" x-text="formatCurrency(selectedAffiliate.revenue)"></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Current Balance</div>
                                <div class="stat-value" x-text="formatCurrency(selectedAffiliate.balance)"></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Join Date</div>
                                <div class="stat-value" x-text="formatDate(selectedAffiliate.join_date)"></div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="affiliate-actions">
                            <button class="btn btn-primary btn-sm">
                                <i data-feather="message-circle" width="16" height="16"></i>
                                <span>Send Message</span>
                            </button>
                            <button class="btn btn-secondary btn-sm">
                                <i data-feather="edit" width="16" height="16"></i>
                                <span>Edit Profile</span>
                            </button>
                            <button class="btn btn-outline btn-sm">
                                <i data-feather="download" width="16" height="16"></i>
                                <span>Export Data</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    
    <!-- Invite Affiliate Modal -->
    <div class="modal-overlay" id="inviteAffiliateModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Invite New Affiliate</h3>
                <button class="modal-close">
                    <i data-feather="x" width="20" height="20"></i>
                </button>
            </div>
            <div class="modal-body">
                <form class="ajax-form" action="<?= epic_url('admin/affiliates/invite') ?>" method="POST">
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-input" required placeholder="affiliate@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-input" required placeholder="Full Name">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Initial Tier</label>
                        <select name="tier" class="form-select">
                            <option value="Bronze">Bronze</option>
                            <option value="Silver">Silver</option>
                            <option value="Gold">Gold</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Personal Message</label>
                        <textarea name="message" class="form-textarea" placeholder="Optional personal message to include in the invitation..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline modal-close">Cancel</button>
                <button type="submit" form="inviteForm" class="btn btn-primary">Send Invitation</button>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="<?= epic_url('themes/modern/admin/admin.js') ?>"></script>
    
    <script>
        // Initialize Feather Icons
        feather.replace();
        
        // Toggle submenu function
        function toggleSubmenu(element) {
            const parent = element;
            const submenu = parent.nextElementSibling;
            
            // Toggle expanded class on parent
            parent.classList.toggle('expanded');
            
            // Toggle expanded class on submenu
            submenu.classList.toggle('expanded');
            
            // Close other submenus
            const allParents = document.querySelectorAll('.sidebar-nav-parent');
            const allSubmenus = document.querySelectorAll('.sidebar-submenu');
            
            allParents.forEach(p => {
                if (p !== parent) {
                    p.classList.remove('expanded');
                }
            });
            
            allSubmenus.forEach(s => {
                if (s !== submenu) {
                    s.classList.remove('expanded');
                }
            });
        }
        
        // Alpine.js Affiliates App
        function affiliatesApp() {
            return {
                sidebarCollapsed: false,
                searchTerm: '',
                statusFilter: '',
                tierFilter: '',
                selectedAffiliate: null,
                affiliates: <?= json_encode($affiliates) ?>,
                filteredAffiliates: <?= json_encode($affiliates) ?>,
                
                init() {
                    this.filterAffiliates();
                },
                
                toggleSidebar() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                },
                
                filterAffiliates() {
                    this.filteredAffiliates = this.affiliates.filter(affiliate => {
                        const matchesSearch = !this.searchTerm || 
                            affiliate.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            affiliate.email.toLowerCase().includes(this.searchTerm.toLowerCase());
                        
                        const matchesStatus = !this.statusFilter || affiliate.status === this.statusFilter;
                        const matchesTier = !this.tierFilter || affiliate.tier === this.tierFilter;
                        
                        return matchesSearch && matchesStatus && matchesTier;
                    });
                },
                
                viewAffiliate(affiliate) {
                    this.selectedAffiliate = affiliate;
                    window.AdminApp.openDrawer('affiliateDetailDrawer');
                },
                
                approveAffiliate(id) {
                    if (confirm('Are you sure you want to approve this affiliate?')) {
                        // API call to approve affiliate
                        window.AdminApp.showToast('success', 'Approved', 'Affiliate has been approved successfully');
                        
                        // Update local data
                        const affiliate = this.affiliates.find(a => a.id === id);
                        if (affiliate) {
                            affiliate.status = 'Active';
                            this.filterAffiliates();
                        }
                    }
                },
                
                suspendAffiliate(id) {
                    if (confirm('Are you sure you want to suspend this affiliate?')) {
                        // API call to suspend affiliate
                        window.AdminApp.showToast('warning', 'Suspended', 'Affiliate has been suspended');
                        
                        // Update local data
                        const affiliate = this.affiliates.find(a => a.id === id);
                        if (affiliate) {
                            affiliate.status = 'Suspended';
                            this.filterAffiliates();
                        }
                    }
                },
                
                activateAffiliate(id) {
                    if (confirm('Are you sure you want to activate this affiliate?')) {
                        // API call to activate affiliate
                        window.AdminApp.showToast('success', 'Activated', 'Affiliate has been activated');
                        
                        // Update local data
                        const affiliate = this.affiliates.find(a => a.id === id);
                        if (affiliate) {
                            affiliate.status = 'Active';
                            this.filterAffiliates();
                        }
                    }
                },
                
                messageAffiliate(affiliate) {
                    window.AdminApp.showToast('info', 'Message', `Opening message composer for ${affiliate.name}`);
                },
                
                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(amount);
                },
                
                formatDate(date) {
                    return new Intl.DateTimeFormat('id-ID', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    }).format(new Date(date));
                },
                
                formatDateTime(datetime) {
                    return new Intl.DateTimeFormat('id-ID', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }).format(new Date(datetime));
                }
            }
        }
    </script>
    
    <style>
        .table-filters {
            display: flex;
            gap: var(--spacing-4);
            align-items: center;
        }
        
        .filter-group {
            min-width: 200px;
        }
        
        .table-actions {
            display: flex;
            gap: var(--spacing-2);
            align-items: center;
        }
        
        .affiliate-info {
            display: flex;
            gap: var(--spacing-4);
            align-items: center;
            margin-bottom: var(--spacing-6);
            padding-bottom: var(--spacing-6);
            border-bottom: 1px solid var(--ink-700);
        }
        
        .user-avatar.large {
            width: 64px;
            height: 64px;
            font-size: var(--font-size-lg);
        }
        
        .affiliate-details h4 {
            margin: 0 0 var(--spacing-2) 0;
            color: var(--ink-100);
            font-size: var(--font-size-lg);
        }
        
        .affiliate-details p {
            margin: 0 0 var(--spacing-3) 0;
            color: var(--ink-400);
        }
        
        .affiliate-badges {
            display: flex;
            gap: var(--spacing-2);
        }
        
        .affiliate-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-4);
            margin-bottom: var(--spacing-6);
        }
        
        .stat-item {
            padding: var(--spacing-4);
            background: var(--surface-3);
            border-radius: var(--radius-lg);
            border: 1px solid var(--ink-700);
        }
        
        .stat-label {
            font-size: var(--font-size-xs);
            color: var(--ink-400);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: var(--spacing-2);
        }
        
        .stat-value {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-bold);
            color: var(--ink-100);
        }
        
        .affiliate-actions {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-3);
        }
        
        @media (max-width: 768px) {
            .table-filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                min-width: auto;
            }
            
            .affiliate-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
    

    
    <script>

    </script>
</body>
</html>