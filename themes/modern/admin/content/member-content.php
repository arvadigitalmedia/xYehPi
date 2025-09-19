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
            <div class="stat-card-icon">
                <i data-feather="users" width="24" height="24"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total']) ?></div>
        <div class="stat-card-change neutral">
            <i data-feather="database" width="14" height="14"></i>
            <span>Semua member terdaftar</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Active</h3>
            <div class="stat-card-icon">
                <i data-feather="user-check" width="24" height="24"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['active']) ?></div>
        <div class="stat-card-change positive">
            <i data-feather="trending-up" width="14" height="14"></i>
            <span>Member aktif</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Inactive</h3>
            <div class="stat-card-icon">
                <i data-feather="user-x" width="24" height="24"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['inactive']) ?></div>
        <div class="stat-card-change negative">
            <i data-feather="trending-down" width="14" height="14"></i>
            <span>Member nonaktif</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Free Account</h3>
            <div class="stat-card-icon">
                <i data-feather="gift" width="24" height="24"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['free_account']) ?></div>
        <div class="stat-card-change neutral">
            <i data-feather="star" width="14" height="14"></i>
            <span>Akun gratis</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">EPIC Account</h3>
            <div class="stat-card-icon">
                <i data-feather="crown" width="24" height="24"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['epic_account']) ?></div>
        <div class="stat-card-change positive">
            <i data-feather="zap" width="14" height="14"></i>
            <span>Premium member</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">EPIS Account</h3>
            <div class="stat-card-icon">
                <i data-feather="award" width="24" height="24"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['epis_account']) ?></div>
        <div class="stat-card-change positive">
            <i data-feather="shield" width="14" height="14"></i>
            <span>Supervisor</span>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="table-card">
    <div class="table-header">
        <h3 class="table-title">Members</h3>
        
        <form method="GET" class="table-search" action="<?= epic_url('admin/manage/member') ?>">
            <div class="search-filters">
                <input type="text" name="search" placeholder="Cari nama, email, atau kode referral..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                
                <select name="role" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Level</option>
                    <option value="free" <?= $role_filter === 'free' ? 'selected' : '' ?>>Free Account</option>
                    <option value="epic" <?= $role_filter === 'epic' ? 'selected' : '' ?>>EPIC Account</option>
                    <option value="epis" <?= $role_filter === 'epis' ? 'selected' : '' ?>>EPIS Account</option>
                    <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= $role_filter === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
                
                <button type="submit" class="search-btn" title="Cari">
                    <i data-feather="search" width="16" height="16"></i>
                </button>
                
                <?php if (!empty($search) || !empty($status_filter) || !empty($role_filter)): ?>
                <a href="<?= epic_url('admin/manage/member') ?>" class="btn btn-sm btn-secondary" title="Reset Filter">
                    <i data-feather="x" width="16" height="16"></i>
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Members Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Email</th>
                        <th>WhatsApp</th>
                        <th>Supervisor</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th class="text-center">Actions</th>
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
                        <tr data-member-id="<?= $member['id'] ?>" class="member-row level-<?= $member['status'] ?>">
                            <!-- 1. Member Info -->
                            <td>
                                <div class="flex items-center">
                                    <?php if (!empty($member['photo'])): ?>
                                        <img src="<?= epic_url('uploads/photos/' . $member['photo']) ?>" 
                                             alt="<?= htmlspecialchars($member['name']) ?>" 
                                             class="w-8 h-8 rounded-full mr-3 object-cover">
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-full mr-3 bg-gray-300 flex items-center justify-center">
                                            <span class="text-gray-600 text-sm font-medium">
                                                <?= strtoupper(substr($member['name'], 0, 1)) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="member-info">
                                        <div class="table-cell-main"><?= htmlspecialchars($member['name']) ?></div>
                                        <div class="table-cell-sub"><?= htmlspecialchars($member['email']) ?></div>
                                        
                                        <?php if (!empty($member['referral_code'])): ?>
                                            <div class="referral-code">
                                                <small class="text-muted">
                                                    Kode: 
                                                    <a href="#" onclick="copyToClipboard('<?= $member['referral_code'] ?>'); return false;" 
                                                       class="text-success" title="Klik untuk copy">
                                                        <?= $member['referral_code'] ?>
                                                    </a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- 2. Phone -->
                            <td><?= htmlspecialchars($member['phone'] ?? '-') ?></td>
                            
                            <!-- 3. Sponsor -->
                            <td>
                                <?php if (!empty($member['sponsor_name'])): ?>
                                    <div class="sponsor-info">
                                        <div><?= htmlspecialchars($member['sponsor_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($member['sponsor_code']) ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- 4. EPIS Supervisor -->
                            <td>
                                <?php if (!empty($member['supervisor_name'])): ?>
                                    <div class="supervisor-info">
                                        <div><?= htmlspecialchars($member['supervisor_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($member['supervisor_code']) ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- 5. Level (Account Type) -->
                            <td class="member-level">
                                <?php
                                $level_class = '';
                                $level_text = '';
                                
                                // Prioritas berdasarkan status, kemudian hierarchy_level
                                if ($member['status'] === 'epis' || $member['hierarchy_level'] == 3) {
                                    $level_class = 'badge-dark';
                                    $level_text = 'EPIS Account';
                                } elseif ($member['status'] === 'epic' || $member['hierarchy_level'] == 2) {
                                    $level_class = 'badge-primary';
                                    $level_text = 'EPIC Account';
                                } elseif ($member['status'] === 'free' || $member['hierarchy_level'] == 1) {
                                    $level_class = 'badge-secondary';
                                    $level_text = 'Free Account';
                                } else {
                                    // Fallback untuk admin/super_admin
                                    if ($member['role'] === 'super_admin') {
                                        $level_class = 'badge-danger';
                                        $level_text = 'Super Admin';
                                    } elseif ($member['role'] === 'admin') {
                                        $level_class = 'badge-warning';
                                        $level_text = 'Admin';
                                    } else {
                                        $level_class = 'badge-info';
                                        $level_text = 'User';
                                    }
                                }
                                ?>
                                <span class="badge <?= $level_class ?>"><?= $level_text ?></span>
                            </td>
                            
                            <!-- 6. Status -->
                            <td class="member-status">
                                <?php 
                                $is_active = in_array($member['status'], ['free', 'epic', 'epis']);
                                ?>
                                <?php if ($is_active): ?>
                                    <span class="badge badge-success">ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">INACTIVE</span>
                                <?php endif; ?>
                            </td>

                            <!-- 7. Joined -->
                            <td><?= date('M d, Y', strtotime($member['created_at'])) ?></td>
                            
                            <!-- 8. Actions -->
                            <td class="actions-column">
                                <div class="action-buttons">
                                    <a href="<?= epic_url('admin/manage/member/edit/' . $member['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary action-btn" 
                                       title="Edit Member"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top">
                                        <i data-feather="edit-2" width="16" height="16"></i>
                                        <span class="btn-text">Edit</span>
                                    </a>
                                    
                                    <?php if ($is_active): ?>
                                        <a href="<?= epic_url('admin/manage/member?action=deactivate&id=' . $member['id']) ?>" 
                                           class="btn btn-sm btn-outline-warning action-btn" 
                                           title="Nonaktifkan Member"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top"
                                           onclick="return confirm('Yakin ingin menonaktifkan member ini?')">
                                            <i data-feather="user-x" width="16" height="16"></i>
                                            <span class="btn-text">Nonaktif</span>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= epic_url('admin/manage/member?action=activate&id=' . $member['id']) ?>" 
                                           class="btn btn-sm btn-outline-success action-btn" 
                                           title="Aktifkan Member"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top"
                                           onclick="return confirm('Yakin ingin mengaktifkan member ini?')">
                                            <i data-feather="user-check" width="16" height="16"></i>
                                            <span class="btn-text">Aktifkan</span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Upgrade Button - Hanya untuk Free Account -->
                                    <?php 
                                    // Tombol upgrade hanya muncul jika:
                                    // 1. Status adalah 'free' DAN hierarchy_level adalah 1 (Free Account)
                                    // 2. TIDAK muncul jika status sudah 'epic' atau 'epis' (hierarchy_level 2 atau 3)
                                    $is_free_account = ($member['status'] === 'free' && $member['hierarchy_level'] == 1);
                                    $is_premium_account = ($member['status'] === 'epic' || $member['status'] === 'epis' || 
                                                          $member['hierarchy_level'] == 2 || $member['hierarchy_level'] == 3);
                                    
                                    if ($is_free_account && !$is_premium_account): 
                                    ?>
                                        <button onclick="upgradeAccount(<?= $member['id'] ?>)" 
                                                class="btn btn-sm btn-outline-info action-btn" 
                                                title="Upgrade ke EPIC"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top">
                                            <i data-feather="arrow-up-circle" width="16" height="16"></i>
                                            <span class="btn-text">Upgrade</span>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="<?= epic_url('admin/manage/member?action=delete&id=' . $member['id']) ?>" 
                                       class="btn btn-sm btn-outline-danger action-btn" 
                                       title="Hapus Member"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top"
                                       onclick="return confirm('Yakin ingin menghapus member ini? Tindakan ini tidak dapat dibatalkan.')">
                                        <i data-feather="trash-2" width="16" height="16"></i>
                                        <span class="btn-text">Hapus</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
        
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

<style>
/* Stat Cards Styling - Dark Gold Theme */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #15161C 0%, #1C1D24 100%);
        border: 1px solid #262732;
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #CFA84E, #DDB966);
        border-radius: 12px 12px 0 0;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(207, 168, 78, 0.15);
        border-color: #CFA84E;
    }
    
    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    
    .stat-card-title {
        font-size: 14px;
        font-weight: 600;
        color: #9B9CA8;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-card-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #CFA84E, #DDB966);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0B0B0F;
    }
    
    .stat-card-value {
        font-size: 32px;
        font-weight: 700;
        color: #D1D2D9;
        margin-bottom: 12px;
        line-height: 1;
    }
    
    .stat-card-change {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .stat-card-change.positive {
        color: #34D399;
    }
    
    .stat-card-change.negative {
        color: #F87171;
    }
    
    .stat-card-change.neutral {
        color: #6B6C78;
    }

.bg-purple {
    background-color: #6f42c1 !important;
}

.supervisor-info {
    font-size: 0.9em;
}

.supervisor-info small {
    display: block;
    color: #6c757d;
}

.member-info .referral-code {
    margin-top: 2px;
}

.member-info .referral-code small {
    color: #6c757d;
    font-size: 0.8em;
}

.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
}

.action-buttons .action-btn {
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    border-width: 1.5px;
}

.action-buttons .action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-buttons .btn-outline-primary:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.action-buttons .btn-outline-success:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.action-buttons .btn-outline-warning:hover {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.action-buttons .btn-outline-info:hover {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

.action-buttons .btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.action-buttons .btn-text {
    display: none;
}

/* Tooltip Styling */
.tooltip {
    font-size: 12px;
}

.tooltip-inner {
    background-color: #212529;
    color: white;
    border-radius: 6px;
    padding: 6px 10px;
    font-weight: 500;
}

.tooltip.bs-tooltip-top .tooltip-arrow::before {
    border-top-color: #212529;
}

@media (min-width: 768px) {
    .action-buttons .btn-text {
        display: none;
    }
    .action-buttons .action-btn {
        width: 36px;
        height: 36px;
    }
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table th, .table td {
    white-space: nowrap;
    vertical-align: middle;
}

.actions-column {
    min-width: 200px;
}

@media (max-width: 767px) {
    .table th, .table td {
        font-size: 12px;
        padding: 8px 4px;
    }
    
    .actions-column {
        min-width: 120px;
    }
    
    .action-buttons .btn {
        padding: 4px 6px;
        font-size: 10px;
    }
}

.badge-dark {
    background-color: #343a40;
    color: white;
}

.text-success {
    color: #28a745 !important;
    text-decoration: none;
}

.text-success:hover {
    color: #1e7e34 !important;
    text-decoration: underline;
}
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'toast toast-success';
        toast.textContent = 'Referral code copied to clipboard!';
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    });
}

function upgradeAccount(memberId) {
    if (confirm('Yakin ingin upgrade member ini ke EPIC account?\n\nMember akan mendapatkan akses penuh ke fitur EPIC.')) {
        // Show loading state
        const btn = event.target.closest('button');
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i data-feather="loader" width="14" height="14"></i> <span class="btn-text">Upgrading...</span>';
        btn.disabled = true;
        
        // Make AJAX request
        fetch('<?= epic_url('api/admin/upgrade-member.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                member_id: memberId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update table row with new data
                updateMemberRow(memberId, data.member);
                
                // Show success message
                showToast('success', data.message);
                
                // Log preserved data info
                if (data.preserved_data.referral || data.preserved_data.supervisor) {
                    const preservedInfo = [];
                    if (data.preserved_data.referral) preservedInfo.push('data referral');
                    if (data.preserved_data.supervisor) preservedInfo.push('EPIS supervisor');
                    
                    setTimeout(() => {
                        showToast('info', `✅ ${preservedInfo.join(' dan ')} tetap terjaga`);
                    }, 1500);
                }
                
            } else {
                // Show error message
                showToast('error', data.message);
                
                // Restore button
                btn.innerHTML = originalContent;
                btn.disabled = false;
                feather.replace();
            }
        })
        .catch(error => {
            console.error('Upgrade error:', error);
            showToast('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
            
            // Restore button
            btn.innerHTML = originalContent;
            btn.disabled = false;
            feather.replace();
        });
    }
}

function updateMemberRow(memberId, memberData) {
    // Find the table row for this member
    const row = document.querySelector(`tr[data-member-id="${memberId}"]`);
    if (!row) {
        console.warn('Member row not found for ID:', memberId);
        return;
    }
    
    // Update row class for styling
    row.className = `member-row level-${memberData.status}`;
    
    // Update level badge
    const levelCell = row.querySelector('.member-level');
    if (levelCell) {
        let badgeClass = '';
        let badgeText = '';
        
        switch(memberData.status) {
            case 'epic':
                badgeClass = 'badge-primary';
                badgeText = 'EPIC Account';
                break;
            case 'epis':
                badgeClass = 'badge-dark';
                badgeText = 'EPIS Account';
                break;
            case 'free':
                badgeClass = 'badge-secondary';
                badgeText = 'Free Account';
                break;
            default:
                badgeClass = 'badge-secondary';
                badgeText = 'Free Account';
        }
        
        levelCell.innerHTML = `<span class="badge ${badgeClass}">${badgeText}</span>`;
    }
    
    // Update status badge
    const statusCell = row.querySelector('.member-status');
    if (statusCell) {
        const isActive = ['free', 'epic', 'epis'].includes(memberData.status);
        const statusBadge = isActive ? 
            '<span class="badge badge-success">ACTIVE</span>' : 
            '<span class="badge badge-secondary">INACTIVE</span>';
        statusCell.innerHTML = statusBadge;
    }
    
    // Remove upgrade button since member is now EPIC
    const upgradeBtn = row.querySelector('button[onclick*="upgradeAccount"]');
    if (upgradeBtn) {
        upgradeBtn.remove();
    }
    
    // Update last updated timestamp
    const updatedCell = row.querySelector('.member-updated');
    if (updatedCell && memberData.updated_at) {
        const updatedDate = new Date(memberData.updated_at);
        updatedCell.textContent = updatedDate.toLocaleDateString('id-ID') + ' ' + 
                                 updatedDate.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
    }
    
    // Add visual feedback - highlight the row briefly
    row.style.backgroundColor = '#d4edda';
    setTimeout(() => {
        row.style.backgroundColor = '';
    }, 3000);
}

function confirmDelete(memberId, memberName) {
    if (confirm(`Yakin ingin menghapus member "${memberName}"?\n\nTindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait.`)) {
        window.location.href = '<?= epic_url('admin/manage/member') ?>?action=delete&id=' + memberId;
    }
}

// Page-specific functionality
function initPageFunctionality() {
    // Initialize any page-specific features here
    console.log('Member Management initialized');
    
    // Initialize Feather icons
    feather.replace();
    
    // Initialize tooltips
    initializeTooltips();
}

// Initialize Bootstrap tooltips
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover focus',
            delay: { show: 300, hide: 100 }
        });
    });
}

// Initialize when page loads
 document.addEventListener('DOMContentLoaded', function() {
     initPageFunctionality();
 });
 </script>

<style>
/* Row Level Styling - Pewarnaan Baris Berdasarkan Level Akun */
.member-row.level-free {
    background-color: rgba(108, 117, 125, 0.05) !important; /* Soft gray untuk Free Account */
}

.member-row.level-epic {
    background-color: rgba(0, 123, 255, 0.08) !important; /* Soft blue untuk EPIC Account */
}

.member-row.level-epis {
    background-color: rgba(52, 58, 64, 0.08) !important; /* Soft dark untuk EPIS Account */
}

.member-row.level-free:hover {
    background-color: rgba(108, 117, 125, 0.12) !important;
}

.member-row.level-epic:hover {
    background-color: rgba(0, 123, 255, 0.15) !important;
}

.member-row.level-epis:hover {
    background-color: rgba(52, 58, 64, 0.15) !important;
}

/* Ensure smooth transitions */
.member-row {
    transition: background-color 0.3s ease;
}
</style>