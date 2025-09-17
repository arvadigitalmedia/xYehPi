<?php
/**
 * Landing Page Manager Content
 * Content yang akan di-render oleh layout global
 */

// Variables sudah tersedia dari parent scope
?>

<!-- Alerts -->
<?php if ($success): ?>
    <div class="alert alert-success">
        <i data-feather="check-circle" width="16" height="16"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i data-feather="x-circle" width="16" height="16"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Landing Pages</h3>
            <i data-feather="file-text" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Active</h3>
            <i data-feather="check-circle" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['active']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Visits</h3>
            <i data-feather="eye" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total_visits']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">iFrame Method</h3>
            <i data-feather="monitor" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['iframe_method']) ?></div>
    </div>
</div>

<!-- Search and Filter -->
<div class="table-card">
    <div class="table-header">
        <h3 class="table-title">Landing Pages</h3>
        
        <form method="GET" class="table-search">
            <div class="search-filters">
                <input type="text" name="search" placeholder="Search landing pages..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                
                <select name="method" class="filter-select">
                    <option value="">All Methods</option>
                    <option value="1" <?= $method_filter === '1' ? 'selected' : '' ?>>iFrame</option>
                    <option value="2" <?= $method_filter === '2' ? 'selected' : '' ?>>Inject URL</option>
                    <option value="3" <?= $method_filter === '3' ? 'selected' : '' ?>>Redirect URL</option>
                </select>
                
                <select name="user" class="filter-select">
                    <option value="">All Users</option>
                    <?php foreach ($users as $filter_user): ?>
                        <option value="<?= $filter_user['id'] ?>" <?= $user_filter == $filter_user['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($filter_user['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="search-btn">
                    <i data-feather="search" width="16" height="16"></i>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Landing Pages Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Landing Page</th>
                    <th>Owner</th>
                    <th>URL</th>
                    <th>Method</th>
                    <th>Visits</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($landing_pages)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-8">
                            <i data-feather="file-text" width="48" height="48" class="text-gray-400 mb-4"></i>
                            <p class="text-gray-500">No landing pages found</p>
                            <a href="<?= epic_url('admin/manage/landing-page-manager/add') ?>" class="topbar-btn mt-4">
                                <i data-feather="plus" width="16" height="16"></i>
                                Create First Landing Page
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($landing_pages as $landing_page): ?>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($landing_page['page_image'])): ?>
                                        <img src="<?= epic_url('uploads/landing-pages/' . $landing_page['page_image']) ?>" 
                                             alt="<?= htmlspecialchars($landing_page['page_title']) ?>" 
                                             class="w-12 h-12 rounded object-cover">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded bg-gray-200 flex items-center justify-center">
                                            <i data-feather="image" width="20" height="20" class="text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="table-cell-main"><?= htmlspecialchars($landing_page['page_title']) ?></div>
                                        <div class="table-cell-sub"><?= htmlspecialchars($landing_page['page_slug']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-main"><?= htmlspecialchars($landing_page['user_name']) ?></div>
                                <div class="table-cell-sub"><?= htmlspecialchars($landing_page['referral_code']) ?></div>
                            </td>
                            <td>
                                <div class="url-display">
                                    <input type="text" 
                                           value="<?= epic_url($landing_page['referral_code'] . '/' . $landing_page['page_slug']) ?>" 
                                           class="url-input" readonly>
                                    <button type="button" class="copy-btn" 
                                            onclick="copyToClipboard('<?= epic_url($landing_page['referral_code'] . '/' . $landing_page['page_slug']) ?>')">
                                        <i data-feather="copy" width="14" height="14"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $method_labels = [1 => 'iFrame', 2 => 'Inject URL', 3 => 'Redirect URL'];
                                $method_classes = [1 => 'badge-info', 2 => 'badge-secondary', 3 => 'badge-success'];
                                ?>
                                <span class="badge <?= $method_classes[$landing_page['method']] ?>">
                                    <?= $method_labels[$landing_page['method']] ?>
                                </span>
                            </td>
                            <td>
                                <span class="table-cell-main"><?= number_format($landing_page['visit_count']) ?></span>
                            </td>
                            <td>
                                <?php if ($landing_page['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="date-text"><?= date('M j, Y', strtotime($landing_page['created_at'])) ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= epic_url($landing_page['referral_code'] . '/' . $landing_page['page_slug']) ?>" 
                                       target="_blank" class="action-btn" title="Preview">
                                        <i data-feather="external-link" width="14" height="14"></i>
                                    </a>
                                    <a href="<?= epic_url('admin/manage/landing-page-manager/edit/' . $landing_page['id']) ?>" 
                                       class="action-btn action-edit" title="Edit">
                                        <i data-feather="edit" width="14" height="14"></i>
                                    </a>
                                    <?php if ($landing_page['is_active']): ?>
                                        <a href="?deactivate=<?= $landing_page['id'] ?>" 
                                           class="action-btn action-suspend" title="Deactivate"
                                           onclick="return confirm('Apakah Anda yakin ingin menonaktifkan landing page ini?')">
                                            <i data-feather="pause" width="14" height="14"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?activate=<?= $landing_page['id'] ?>" 
                                           class="action-btn action-activate" title="Activate"
                                           onclick="return confirm('Apakah Anda yakin ingin mengaktifkan landing page ini?')">
                                            <i data-feather="play" width="14" height="14"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?delete=<?= $landing_page['id'] ?>" 
                                       class="action-btn action-delete" title="Delete"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus landing page ini? Tindakan ini tidak dapat dibatalkan.')">
                                        <i data-feather="trash-2" width="14" height="14"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="table-pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php 
                    $query_params = $_GET;
                    $query_params['page'] = $i;
                    $query_string = http_build_query($query_params);
                    ?>
                    <a href="?<?= $query_string ?>" class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Page-specific functionality
    function initPageFunctionality() {
        // Initialize any page-specific features here
        console.log('Landing Page Manager initialized');
    }
</script>