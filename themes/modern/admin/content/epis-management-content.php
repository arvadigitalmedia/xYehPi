<?php
/**
 * EPIS Account Management Content
 * Content yang akan di-render oleh layout global
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
 * 
 * Variables yang tersedia dari parent scope:
 * - $success, $error: Alert messages
 * - $stats: Statistics data
 * - $epis_accounts: EPIS accounts data
 * - $eligible_epic_users: Users eligible for EPIS promotion
 * - $search, $status_filter, $page: Filter parameters
 * - $user: Current admin user
 */

// Ensure variables are set with defaults
$success = $success ?? '';
$error = $error ?? '';
$stats = $stats ?? [];
$epis_accounts = $epis_accounts ?? [];
$eligible_epic_users = $eligible_epic_users ?? [];
$search = $search ?? '';
$status_filter = $status_filter ?? '';
$page = $page ?? 1;
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
            <h3 class="stat-card-title">Total EPIS Accounts</h3>
            <i data-feather="users" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total_epis']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Active EPIS</h3>
            <i data-feather="check-circle" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['active_epis']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">EPIC in Networks</h3>
            <i data-feather="network" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total_epic_in_networks']) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Commissions</h3>
            <i data-feather="dollar-sign" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($stats['total_commissions'], 0, ',', '.') ?></div>
    </div>
</div>

<!-- Search and Filter -->
<div class="table-card">
    <div class="table-header">
        <h3 class="table-title">EPIS Accounts</h3>
        
        <form method="GET" class="table-search">
            <div class="search-filters">
                <input type="text" name="search" placeholder="Search EPIS accounts..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="suspended" <?= $status_filter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="terminated" <?= $status_filter === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                </select>
                
                <button type="submit" class="search-btn">
                    <i data-feather="search" width="16" height="16"></i>
                </button>
            </div>
        </form>
    </div>
    
    <!-- EPIS Accounts Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>EPIS Account</th>
                    <th>Territory</th>
                    <th>Network Size</th>
                    <th>Commission Rates</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($epis_accounts)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8">
                            <i data-feather="users" width="48" height="48" class="text-gray-400 mb-4"></i>
                            <p class="text-gray-500">No EPIS accounts found</p>
                            <button onclick="showCreateModal()" class="topbar-btn mt-4">
                                <i data-feather="plus" width="16" height="16"></i>
                                Create First EPIS Account
                            </button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($epis_accounts as $epis): ?>
                        <tr>
                            <td>
                                <div class="member-info">
                                    <div class="member-name">
                                        <a href="<?= epic_url('admin/manage/epis/view/' . $epis['user_id']) ?>">
                                            <?= htmlspecialchars($epis['name']) ?>
                                        </a>
                                        <span class="epic-badge" title="EPIS Account">
                                            <i data-feather="crown" width="14" height="14"></i>
                                        </span>
                                    </div>
                                    <div class="member-details">
                                        <div class="member-email">
                                            <i data-feather="mail" width="12" height="12"></i>
                                            <?= htmlspecialchars($epis['email']) ?>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Code: <?= htmlspecialchars($epis['epis_code']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-main"><?= htmlspecialchars($epis['territory_name'] ?: '-') ?></div>
                                <?php if ($epis['max_epic_recruits'] > 0): ?>
                                    <div class="table-cell-sub">
                                        Limit: <?= $epis['current_epic_count'] ?>/<?= $epis['max_epic_recruits'] ?> EPIC
                                    </div>
                                <?php else: ?>
                                    <div class="table-cell-sub">Unlimited recruits</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-cell-main"><?= number_format($epis['network_size']) ?> EPIC</div>
                                <div class="table-cell-sub">
                                    <a href="<?= epic_url('admin/manage/epis/network/' . $epis['user_id']) ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        View Network
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="table-cell-main">
                                    Direct: <?= number_format($epis['recruitment_commission_rate'], 1) ?>%
                                </div>
                                <div class="table-cell-sub">
                                    Indirect: <?= number_format($epis['indirect_commission_rate'], 1) ?>%
                                </div>
                            </td>
                            <td>
                                <?php 
                                $status_classes = [
                                    'active' => 'badge-success',
                                    'suspended' => 'badge-warning',
                                    'terminated' => 'badge-danger'
                                ];
                                $status_class = $status_classes[$epis['status']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?= $status_class ?>">
                                    <?= ucfirst($epis['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="date-text"><?= date('M j, Y', strtotime($epis['created_at'])) ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= epic_url('admin/manage/epis/view/' . $epis['user_id']) ?>" 
                                       class="action-btn action-view" title="View Details">
                                        <i data-feather="eye" width="14" height="14"></i>
                                    </a>
                                    <button onclick="showEditModal(<?= htmlspecialchars(json_encode($epis)) ?>)" 
                                            class="action-btn action-edit" title="Edit">
                                        <i data-feather="edit" width="14" height="14"></i>
                                    </button>
                                    <?php if ($epis['status'] === 'active'): ?>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to suspend this EPIS account?')">
                                            <input type="hidden" name="action" value="suspend_epis">
                                            <input type="hidden" name="epis_user_id" value="<?= $epis['user_id'] ?>">
                                            <button type="submit" class="action-btn action-suspend" title="Suspend">
                                                <i data-feather="pause" width="14" height="14"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to activate this EPIS account?')">
                                            <input type="hidden" name="action" value="activate_epis">
                                            <input type="hidden" name="epis_user_id" value="<?= $epis['user_id'] ?>">
                                            <button type="submit" class="action-btn action-activate" title="Activate">
                                                <i data-feather="play" width="14" height="14"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create EPIS Account Modal -->
<div id="createEpisModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Create EPIS Account</h3>
            <button type="button" class="modal-close" onclick="hideCreateModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form method="POST" class="modal-form">
            <input type="hidden" name="action" value="create_epis">
            
            <div class="form-group">
                <label for="user_id" class="form-label">Select EPIC User to Promote</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">Choose EPIC user...</option>
                    <?php foreach ($eligible_epic_users as $epic_user): ?>
                        <option value="<?= $epic_user['id'] ?>">
                            <?= htmlspecialchars($epic_user['name']) ?> (<?= htmlspecialchars($epic_user['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Only EPIC accounts without existing EPIS status are shown</small>
            </div>
            
            <div class="form-group">
                <label for="territory_name" class="form-label">Territory Name</label>
                <input type="text" name="territory_name" id="territory_name" class="form-input" 
                       placeholder="e.g., Jakarta Region, East Java Territory" required>
            </div>
            
            <div class="form-group">
                <label for="territory_description" class="form-label">Territory Description</label>
                <textarea name="territory_description" id="territory_description" class="form-textarea" 
                          placeholder="Describe the territory coverage and responsibilities..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="max_epic_recruits" class="form-label">Max EPIC Recruits</label>
                    <input type="number" name="max_epic_recruits" id="max_epic_recruits" class="form-input" 
                           value="0" min="0">
                    <small class="form-help">0 = unlimited</small>
                </div>
                
                <div class="form-group">
                    <label for="recruitment_commission_rate" class="form-label">Direct Commission Rate (%)</label>
                    <input type="number" name="recruitment_commission_rate" id="recruitment_commission_rate" 
                           class="form-input" value="10.00" min="0" max="100" step="0.01" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="indirect_commission_rate" class="form-label">Indirect Commission Rate (%)</label>
                <input type="number" name="indirect_commission_rate" id="indirect_commission_rate" 
                       class="form-input" value="5.00" min="0" max="100" step="0.01" required>
                <small class="form-help">Commission rate when EPIC recruits through their network</small>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="hideCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="plus" width="16" height="16"></i>
                    Create EPIS Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit EPIS Account Modal -->
<div id="editEpisModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit EPIS Account</h3>
            <button type="button" class="modal-close" onclick="hideEditModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form method="POST" class="modal-form" id="editEpisForm">
            <input type="hidden" name="action" value="update_epis">
            <input type="hidden" name="epis_user_id" id="edit_epis_user_id">
            
            <div class="form-group">
                <label class="form-label">EPIS Account</label>
                <div class="form-static" id="edit_epis_info"></div>
            </div>
            
            <div class="form-group">
                <label for="edit_territory_name" class="form-label">Territory Name</label>
                <input type="text" name="territory_name" id="edit_territory_name" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="edit_territory_description" class="form-label">Territory Description</label>
                <textarea name="territory_description" id="edit_territory_description" class="form-textarea"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_max_epic_recruits" class="form-label">Max EPIC Recruits</label>
                    <input type="number" name="max_epic_recruits" id="edit_max_epic_recruits" 
                           class="form-input" min="0">
                    <small class="form-help">0 = unlimited</small>
                </div>
                
                <div class="form-group">
                    <label for="edit_recruitment_commission_rate" class="form-label">Direct Commission Rate (%)</label>
                    <input type="number" name="recruitment_commission_rate" id="edit_recruitment_commission_rate" 
                           class="form-input" min="0" max="100" step="0.01" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit_indirect_commission_rate" class="form-label">Indirect Commission Rate (%)</label>
                <input type="number" name="indirect_commission_rate" id="edit_indirect_commission_rate" 
                       class="form-input" min="0" max="100" step="0.01" required>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="hideEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save" width="16" height="16"></i>
                    Update EPIS Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Styles are now in separate CSS file: themes/modern/admin/pages/epis-management.css -->

<!-- JavaScript functionality is now in separate JS file: themes/modern/admin/pages/epis-management.js -->