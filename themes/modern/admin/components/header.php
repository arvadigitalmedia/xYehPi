<?php
/**
 * EPIC Hub Admin Header Component
 * Global header/topbar untuk semua halaman admin
 */

// Default values
$header_title = $header_title ?? $page_title ?? 'Dashboard';
$breadcrumb = $breadcrumb ?? [];
$show_back_button = $show_back_button ?? false;
$back_url = $back_url ?? epic_url('admin');
$page_actions = $page_actions ?? [];

// Generate breadcrumb
if (empty($breadcrumb)) {
    $breadcrumb = [
        ['text' => 'Admin', 'url' => epic_url('admin')]
    ];
    
    // Auto-generate breadcrumb based on current URL
    $current_url = $_SERVER['REQUEST_URI'];
    $url_parts = explode('/', trim($current_url, '/'));
    
    if (count($url_parts) > 1) {
        $path = '';
        for ($i = 1; $i < count($url_parts); $i++) {
            $path .= '/' . $url_parts[$i];
            $text = ucwords(str_replace('-', ' ', $url_parts[$i]));
            
            // Don't add link for the last item (current page)
            if ($i === count($url_parts) - 1) {
                $breadcrumb[] = ['text' => $text];
            } else {
                $breadcrumb[] = ['text' => $text, 'url' => epic_url($path)];
            }
        }
    }
}
?>
<header class="admin-topbar">
    <div class="topbar-left">
        <?php if ($show_back_button): ?>
            <button type="button" class="topbar-back-btn" onclick="window.location.href='<?= $back_url ?>'">
                <i data-feather="arrow-left" width="20" height="20"></i>
            </button>
        <?php endif; ?>
        
        <div class="topbar-title-section">
            <h1 class="topbar-title"><?= htmlspecialchars($header_title) ?></h1>
            
            <?php if (!empty($breadcrumb)): ?>
                <nav class="topbar-breadcrumb">
                    <?php foreach ($breadcrumb as $index => $crumb): ?>
                        <?php if ($index > 0): ?>
                            <span class="breadcrumb-separator">/</span>
                        <?php endif; ?>
                        
                        <?php if (isset($crumb['url'])): ?>
                            <a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['text']) ?></a>
                        <?php else: ?>
                            <span><?= htmlspecialchars($crumb['text']) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="topbar-right">
        <!-- Page Actions -->
        <?php if (!empty($page_actions)): ?>
            <div class="topbar-actions">
                <?php foreach ($page_actions as $action): ?>
                    <?php if ($action['type'] === 'button'): ?>
                        <button type="<?= $action['button_type'] ?? 'button' ?>" 
                                class="topbar-btn <?= $action['class'] ?? '' ?>" 
                                <?= isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '"' : '' ?>
                                <?= isset($action['form']) ? 'form="' . $action['form'] . '"' : '' ?>>
                            <?php if (isset($action['icon'])): ?>
                                <i data-feather="<?= $action['icon'] ?>" width="16" height="16"></i>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($action['text']) ?></span>
                        </button>
                    <?php elseif ($action['type'] === 'link'): ?>
                        <a href="<?= $action['url'] ?>" class="topbar-btn <?= $action['class'] ?? '' ?>">
                            <?php if (isset($action['icon'])): ?>
                                <i data-feather="<?= $action['icon'] ?>" width="16" height="16"></i>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($action['text']) ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Notifications -->
        <div class="topbar-notifications" onclick="toggleNotifications()">
            <i data-feather="bell" width="20" height="20"></i>
            <span class="notification-badge">3</span>
        </div>
        
        <!-- User Avatar -->
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

<script>
    // Toggle notifications
    function toggleNotifications() {
        // Placeholder for notifications functionality
        showNotification('Notifications feature coming soon!', 'info');
    }
</script>