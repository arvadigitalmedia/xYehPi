<?php
/**
 * EPIC Hub Member Page Layout Component
 * Template konsisten untuk semua halaman member area
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Render page header dengan struktur konsisten
 * 
 * @param array $config Konfigurasi header
 *   - title: string - Judul halaman
 *   - subtitle: string - Subtitle halaman
 *   - breadcrumb: array - Breadcrumb navigation
 *   - actions: array - Action buttons
 */
function render_page_header($config = []) {
    $title = $config['title'] ?? 'Halaman Member';
    $subtitle = $config['subtitle'] ?? '';
    $breadcrumb = $config['breadcrumb'] ?? [];
    $actions = $config['actions'] ?? [];
    ?>
    <!-- Consistent Page Header -->
    <div class="epic-page-header">
        <?php if (!empty($breadcrumb)): ?>
            <nav class="epic-breadcrumb">
                <ol class="breadcrumb-list">
                    <?php foreach ($breadcrumb as $index => $item): ?>
                        <li class="breadcrumb-item <?= $index === count($breadcrumb) - 1 ? 'active' : '' ?>">
                            <?php if (isset($item['url']) && $index !== count($breadcrumb) - 1): ?>
                                <a href="<?= htmlspecialchars($item['url']) ?>" class="breadcrumb-link">
                                    <?= htmlspecialchars($item['text']) ?>
                                </a>
                            <?php else: ?>
                                <span class="breadcrumb-text"><?= htmlspecialchars($item['text']) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
        
        <div class="epic-page-header-content">
            <div class="epic-page-title-section">
                <h1 class="epic-page-title"><?= htmlspecialchars($title) ?></h1>
                <?php if ($subtitle): ?>
                    <p class="epic-page-subtitle"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($actions)): ?>
                <div class="epic-page-actions">
                    <?php foreach ($actions as $action): ?>
                        <a href="<?= htmlspecialchars($action['url']) ?>" 
                           class="btn <?= htmlspecialchars($action['class'] ?? 'btn-secondary') ?>"
                           <?= isset($action['onclick']) ? 'onclick="' . htmlspecialchars($action['onclick']) . '"' : '' ?>>
                            <?php if (isset($action['icon'])): ?>
                                <i data-feather="<?= htmlspecialchars($action['icon']) ?>" width="16" height="16"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($action['text']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Render statistics cards dengan struktur konsisten
 * 
 * @param array $stats Array statistik
 */
function render_stats_section($stats = []) {
    if (empty($stats)) return;
    ?>
    <!-- Consistent Statistics Section -->
    <div class="epic-stats-section">
        <div class="epic-stats-grid">
            <?php foreach ($stats as $stat): ?>
                <div class="epic-stat-card <?= isset($stat['variant']) ? 'stat-' . $stat['variant'] : '' ?>">
                    <div class="stat-header">
                        <div class="stat-icon-container">
                            <div class="stat-icon">
                                <i data-feather="<?= htmlspecialchars($stat['icon']) ?>" width="20" height="20"></i>
                            </div>
                        </div>
                        <div class="stat-content">
                            <div class="stat-title"><?= htmlspecialchars($stat['title']) ?></div>
                            <div class="stat-value"><?= htmlspecialchars($stat['value']) ?></div>
                            <?php if (isset($stat['change'])): ?>
                                <div class="stat-change <?= htmlspecialchars($stat['change']['type'] ?? 'neutral') ?>">
                                    <?php if (isset($stat['change']['icon'])): ?>
                                        <i data-feather="<?= htmlspecialchars($stat['change']['icon']) ?>" width="12" height="12"></i>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($stat['change']['text']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Render alert messages dengan struktur konsisten
 * 
 * @param array $alerts Array alert messages
 */
function render_alerts($alerts = []) {
    if (empty($alerts)) return;
    ?>
    <!-- Consistent Alert Messages -->
    <div class="epic-alerts-section">
        <?php foreach ($alerts as $alert): ?>
            <div class="epic-alert alert-<?= htmlspecialchars($alert['type']) ?>">
                <div class="alert-icon">
                    <i data-feather="<?= htmlspecialchars($alert['icon']) ?>" width="20" height="20"></i>
                </div>
                <div class="alert-content">
                    <?php if (isset($alert['title'])): ?>
                        <h4 class="alert-title"><?= htmlspecialchars($alert['title']) ?></h4>
                    <?php endif; ?>
                    <p class="alert-message"><?= htmlspecialchars($alert['message']) ?></p>
                </div>
                <?php if (isset($alert['dismissible']) && $alert['dismissible']): ?>
                    <button class="alert-dismiss" onclick="this.parentElement.remove()">
                        <i data-feather="x" width="16" height="16"></i>
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Render content section dengan struktur konsisten
 * 
 * @param array $config Konfigurasi content section
 */
function render_content_section($config = []) {
    $title = $config['title'] ?? '';
    $description = $config['description'] ?? '';
    $class = $config['class'] ?? '';
    ?>
    <div class="epic-content-section <?= htmlspecialchars($class) ?>">
        <?php if ($title || $description): ?>
            <div class="epic-section-header">
                <?php if ($title): ?>
                    <h2 class="epic-section-title"><?= htmlspecialchars($title) ?></h2>
                <?php endif; ?>
                <?php if ($description): ?>
                    <p class="epic-section-description"><?= htmlspecialchars($description) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="epic-section-content">
            <?= $config['content'] ?? '' ?>
        </div>
    </div>
    <?php
}

/**
 * Render data table dengan struktur konsisten
 * 
 * @param array $config Konfigurasi table
 */
function render_data_table($config = []) {
    $headers = $config['headers'] ?? [];
    $data = $config['data'] ?? [];
    $actions = $config['actions'] ?? [];
    $empty_message = $config['empty_message'] ?? 'Tidak ada data tersedia';
    ?>
    <div class="epic-table-container">
        <?php if (!empty($data)): ?>
            <div class="epic-table-wrapper">
                <table class="epic-table">
                    <?php if (!empty($headers)): ?>
                        <thead class="table-header">
                            <tr>
                                <?php foreach ($headers as $header): ?>
                                    <th class="table-th <?= isset($header['class']) ? htmlspecialchars($header['class']) : '' ?>">
                                        <?= htmlspecialchars($header['text']) ?>
                                    </th>
                                <?php endforeach; ?>
                                <?php if (!empty($actions)): ?>
                                    <th class="table-th table-actions">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                    <?php endif; ?>
                    <tbody class="table-body">
                        <?php foreach ($data as $row): ?>
                            <tr class="table-row">
                                <?php foreach ($headers as $key => $header): ?>
                                    <td class="table-td <?= isset($header['class']) ? htmlspecialchars($header['class']) : '' ?>">
                                        <?= isset($row[$key]) ? htmlspecialchars($row[$key]) : '-' ?>
                                    </td>
                                <?php endforeach; ?>
                                <?php if (!empty($actions)): ?>
                                    <td class="table-td table-actions">
                                        <div class="action-buttons">
                                            <?php foreach ($actions as $action): ?>
                                                <button class="btn-icon <?= htmlspecialchars($action['class'] ?? '') ?>"
                                                        onclick="<?= htmlspecialchars($action['onclick']) ?>"
                                                        title="<?= htmlspecialchars($action['title'] ?? '') ?>">
                                                    <i data-feather="<?= htmlspecialchars($action['icon']) ?>" width="16" height="16"></i>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="epic-empty-state">
                <div class="empty-icon">
                    <i data-feather="inbox" width="48" height="48"></i>
                </div>
                <h3 class="empty-title">Tidak Ada Data</h3>
                <p class="empty-message"><?= htmlspecialchars($empty_message) ?></p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>