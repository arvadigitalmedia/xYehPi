<?php
/**
 * Member Management Content
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
            <h3 class="stat-card-title">Total Members</h3>
            <i data-feather="users" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Active Members</h3>
            <i data-feather="check-circle" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['active']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Inactive Members</h3>
            <i data-feather="pause-circle" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['inactive']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Admin Users</h3>
            <i data-feather="shield" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['admin']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Premium Members</h3>
            <i data-feather="star" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['premium']) ?></div>
    </div>
</div>

<!-- Search and Filter -->
<div class="table-card">
    <div class="table-header">
        <h3 class="table-title">Members</h3>
        
        <form method="GET" class="table-search">
            <div class="search-filters">
                <input type="text" name="search" placeholder="Search members..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                
                <select name="role" class="filter-select">
                    <option value="">All Roles</option>
                    <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="premium" <?= $role_filter === 'premium' ? 'selected' : '' ?>>Premium</option>
                    <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= $role_filter === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
                
                <button type="submit" class="search-btn">
                    <i data-feather="search" width="16" height="16"></i>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Members Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Referral Code</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8">
                            <i data-feather="users" width="48" height="48" class="text-gray-400 mb-4"></i>
                            <p class="text-gray-500">No members found</p>
                            <a href="<?= epic_url('admin/manage/member/add') ?>" class="topbar-btn mt-4">
                                <i data-feather="plus" width="16" height="16"></i>
                                Add First Member
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($member['profile_photo'])): ?>
                                        <img src="<?= epic_url('uploads/profiles/' . $member['profile_photo']) ?>" 
                                             alt="<?= htmlspecialchars($member['name']) ?>" 
                                             class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                            <span class="text-gray-600 font-medium text-xs">
                                                <?= strtoupper(substr($member['name'], 0, 2)) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="table-cell-main"><?= htmlspecialchars($member['name']) ?></div>
                                        <div class="table-cell-sub">ID: <?= $member['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-main"><?= htmlspecialchars($member['email']) ?></div>
                                <?php if (!empty($member['phone'])): ?>
                                    <div class="table-cell-sub"><?= htmlspecialchars($member['phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $role_classes = [
                                    'super_admin' => 'badge-danger',
                                    'admin' => 'badge-warning', 
                                    'premium' => 'badge-success',
                                    'user' => 'badge-secondary'
                                ];
                                $role_class = $role_classes[$member['role']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?= $role_class ?>">
                                    <?= ucfirst(str_replace('_', ' ', $member['role'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($member['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($member['referral_code'])): ?>
                                    <div class="referral-code-display">
                                        <code class="referral-code"><?= htmlspecialchars($member['referral_code']) ?></code>
                                        <button type="button" class="copy-btn" 
                                                onclick="copyToClipboard('<?= htmlspecialchars($member['referral_code']) ?>')">
                                            <i data-feather="copy" width="12" height="12"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="date-text"><?= date('M j, Y', strtotime($member['created_at'])) ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= epic_url('admin/manage/member/edit/' . $member['id']) ?>" 
                                       class="action-btn action-edit" title="Edit">
                                        <i data-feather="edit" width="14" height="14"></i>
                                    </a>
                                    <?php if ($member['status'] === 'active'): ?>
                                        <a href="?action=deactivate&id=<?= $member['id'] ?>" 
                                           class="action-btn action-suspend" title="Deactivate"
                                           onclick="return confirm('Apakah Anda yakin ingin menonaktifkan member ini?')">
                                            <i data-feather="pause" width="14" height="14"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=activate&id=<?= $member['id'] ?>" 
                                           class="action-btn action-activate" title="Activate"
                                           onclick="return confirm('Apakah Anda yakin ingin mengaktifkan member ini?')">
                                            <i data-feather="play" width="14" height="14"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($member['role'] !== 'super_admin'): ?>
                                        <a href="?action=delete&id=<?= $member['id'] ?>" 
                                           class="action-btn action-delete" title="Delete"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus member ini? Tindakan ini tidak dapat dibatalkan.')">
                                            <i data-feather="trash-2" width="14" height="14"></i>
                                        </a>
                                    <?php endif; ?>
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

<style>
/* Member-specific styles */
.referral-code-display {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.referral-code {
    background: var(--surface-1);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-family: 'Courier New', monospace;
    font-size: var(--font-size-xs);
    color: var(--gold-400);
    border: 1px solid var(--ink-600);
}

.copy-btn {
    background: none;
    border: none;
    color: var(--ink-400);
    cursor: pointer;
    padding: var(--spacing-1);
    border-radius: var(--radius-sm);
    transition: color var(--transition-fast);
}

.copy-btn:hover {
    color: var(--gold-400);
}

/* Improved table layout for member photos */
.data-table td:first-child {
    width: 250px;
    min-width: 250px;
}

.data-table .flex.items-center {
    min-height: 48px;
}

.data-table img,
.data-table .w-8.h-8 {
    min-width: 32px;
    min-height: 32px;
    max-width: 32px;
    max-height: 32px;
}

/* Ensure proper text wrapping in table cells */
.table-cell-main {
    font-weight: 500;
    color: var(--ink-100);
    line-height: 1.4;
    word-break: break-word;
}

.table-cell-sub {
    font-size: var(--font-size-xs);
    color: var(--ink-300);
    line-height: 1.3;
    margin-top: 2px;
}
</style>

<script>
    // Page-specific functionality
    function initPageFunctionality() {
        // Initialize any page-specific features here
        console.log('Member Management initialized');
    }
</script>