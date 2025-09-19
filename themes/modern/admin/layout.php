<?php
/**
 * EPIC Hub Admin Layout Template
 * Global layout untuk semua halaman admin
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
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

// Default values untuk layout
$layout_data = array_merge([
    'page_title' => 'Admin Panel - EPIC Hub',
    'current_page' => 'dashboard',
    'breadcrumb' => [],
    'show_back_button' => false,
    'back_url' => '',
    'page_actions' => [],
    'content_class' => '',
    'body_class' => 'admin-body'
], $data ?? []);

extract($layout_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    <!-- Favicon -->
    <?php 
    $site_favicon = epic_setting('site_favicon');
    if ($site_favicon && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_favicon)): 
    ?>
        <link rel="icon" type="image/x-icon" href="<?= epic_url('uploads/logos/' . $site_favicon) ?>">
    <?php else: ?>
        <link rel="icon" type="image/x-icon" href="<?= epic_url('themes/modern/assets/favicon.ico') ?>">
    <?php endif; ?>
    
    <!-- Fonts -->
    <!-- Google Fonts removed to eliminate external dependency -->
    
    <!-- Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/admin.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/components.css') ?>">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?= epic_url($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Alpine.js cloak style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <?php if (isset($inline_css)): ?>
        <style><?= $inline_css ?></style>
    <?php endif; ?>
</head>
<body class="<?= $body_class ?>" x-data="adminApp()" x-init="init()" x-cloak>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include __DIR__ . '/components/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header/Topbar -->
            <?php include __DIR__ . '/components/header.php'; ?>
            
            <!-- Content Area -->
            <div class="admin-content <?= $content_class ?>">
                <?php if (isset($content_file) && file_exists($content_file)): ?>
                    <?php include $content_file; ?>
                <?php elseif (isset($content)): ?>
                    <?= $content ?>
                <?php else: ?>
                    <div class="alert alert-error">
                        <i data-feather="alert-circle" width="16" height="16"></i>
                        Content not found
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Footer -->
            <?php include __DIR__ . '/components/footer.php'; ?>
        </main>
    </div>
    
    <!-- Global JavaScript -->
    <script>
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
        
        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                showNotification('Link berhasil disalin!', 'success');
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                // Fallback for older browsers
                var textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showNotification('Link berhasil disalin!', 'success');
            });
        }
        
        // Show notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i data-feather="${type === 'success' ? 'check-circle' : 'info'}" width="20" height="20"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            feather.replace();
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const leftIcon = document.querySelector('.collapse-icon-left');
            const rightIcon = document.querySelector('.collapse-icon-right');
            
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                leftIcon.style.display = 'block';
                rightIcon.style.display = 'none';
            } else {
                sidebar.classList.add('collapsed');
                leftIcon.style.display = 'none';
                rightIcon.style.display = 'block';
                
                // Close all submenus when sidebar is collapsed
                document.querySelectorAll('.sidebar-nav-parent').forEach(p => {
                    p.classList.remove('expanded');
                });
                document.querySelectorAll('.sidebar-submenu').forEach(s => {
                    s.classList.remove('expanded');
                });
            }
        }
        
        // Mobile Sidebar Toggle
        function toggleMobileSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            const body = document.body;
            
            if (sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                body.classList.remove('mobile-menu-open');
            } else {
                sidebar.classList.add('mobile-open');
                body.classList.add('mobile-menu-open');
            }
        }
        
        // Close mobile sidebar when clicking backdrop
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.admin-sidebar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            
            // Check if click is outside sidebar and not on toggle button
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('mobile-open') && 
                !sidebar.contains(e.target) && 
                !mobileToggle.contains(e.target)) {
                toggleMobileSidebar();
            }
        });
        
        // Close mobile sidebar on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const sidebar = document.querySelector('.admin-sidebar');
                const body = document.body;
                sidebar.classList.remove('mobile-open');
                body.classList.remove('mobile-menu-open');
            }
        });
        
        // Alpine.js Admin App
        function adminApp() {
            return {
                init() {
                    // Initialize page functionality
                    if (window.feather) {
                        feather.replace();
                    }
                    
                    // Initialize page-specific functionality
                    if (typeof initPageFunctionality === 'function') {
                        initPageFunctionality();
                    }
                }
            }
        }
    </script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <?php if (strpos($js, 'http') === 0): ?>
                <script src="<?= $js ?>"></script>
            <?php else: ?>
                <script src="<?= epic_url($js) ?>"></script>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_js)): ?>
        <script><?= $inline_js ?></script>
    <?php endif; ?>
</body>
</html>