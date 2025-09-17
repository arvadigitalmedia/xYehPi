<?php
/**
 * EPIC Hub Member Layout Template
 * Global layout untuk semua halaman member - Identical to Admin Theme
 * 
 * @version 3.0.0
 * @author EPIC Hub Team
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Check member access
$user = epic_current_user();
if (!$user) {
    epic_route_login();
    return;
}

// Default values untuk layout - Identical to Admin
$layout_data = array_merge([
    'page_title' => 'Member Area - EPIC Hub',
    'current_page' => 'home',
    'breadcrumb' => [],
    'show_back_button' => false,
    'back_url' => '',
    'page_actions' => [],
    'content_class' => '',
    'body_class' => 'admin-body'  // Using admin body class
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- JavaScript -->
    <script src="<?= epic_url('themes/modern/member/pages/admin.js') ?>"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- CSS Files -->
    <!-- Base Design System (Primary) -->
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/epic-member-design-system.css') ?>">
    
    <!-- Theme & Layout (Secondary) -->
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/admin.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/components.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/aurora-dark-gold-theme.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/epic-layout-system.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/epic-components.css') ?>">
    
    <!-- Page Specific Styles (Tertiary) -->
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/pages/profile.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/pages/products.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/pages/bonus.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/pages/orders.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/pages/prospects.css') ?>">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?= epic_url($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Alpine.js cloak style -->
    <style>
        [x-cloak] { display: none !important; }
        /* Fallback untuk memastikan konten tetap terlihat */
        .member-content {
            opacity: 1 !important;
            visibility: visible !important;
        }
    </style>
    
    <!-- Alpine.js cloak style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <?php if (isset($inline_css)): ?>
        <style><?= $inline_css ?></style>
    <?php endif; ?>
</head>
<body class="<?= $body_class ?>" x-data="memberApp()" x-init="init()" x-cloak>
    <div class="admin-layout">
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
                        <span>Content not found</span>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= epic_url($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_js)): ?>
        <script><?= $inline_js ?></script>
    <?php endif; ?>
    
    <!-- Initialize Feather Icons -->
    <script>
        feather.replace();
    </script>
</body>
</html>