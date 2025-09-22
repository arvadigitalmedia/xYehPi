<?php
/**
 * Member Landing Pages Content
 * Template untuk konten landing pages member
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Extract data dari layout
extract($data);
?>

<!-- Alert Messages -->
<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <strong>Sukses!</strong> <?= htmlspecialchars($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Error!</strong> <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= number_format($stats['total_pages']) ?></h4>
                        <p class="mb-0">Total Landing Pages</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= number_format($stats['total_views']) ?></h4>
                        <p class="mb-0">Total Views</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-eye fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= number_format($stats['total_conversions']) ?></h4>
                        <p class="mb-0">Conversions</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= $stats['avg_conversion_rate'] ?>%</h4>
                        <p class="mb-0">Conversion Rate</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-percentage fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Limits Info -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="card-title">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    Informasi Akun <?= strtoupper($access_level) ?>
                </h5>
                <p class="card-text">
                    Anda dapat membuat maksimal <strong><?= $user_limits['max_pages'] ?> landing page</strong>. 
                    Saat ini Anda telah menggunakan <strong><?= $stats['total_pages'] ?></strong> dari 
                    <strong><?= $user_limits['max_pages'] ?></strong> slot yang tersedia.
                </p>
                <?php if ($user_limits['analytics']): ?>
                <p class="text-success mb-0">
                    <i class="fas fa-check me-1"></i> Analytics tersedia untuk akun Anda
                </p>
                <?php else: ?>
                <p class="text-warning mb-0">
                    <i class="fas fa-times me-1"></i> Analytics tidak tersedia untuk akun Free
                </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if ($stats['total_pages'] < $user_limits['max_pages']): ?>
                <a href="<?= epic_url('dashboard/member/landing-pages/create') ?>" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Buat Landing Page
                </a>
                <?php else: ?>
                <button class="btn btn-secondary" disabled>
                    <i class="fas fa-lock me-2"></i>Limit Tercapai
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="mt-3">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-muted">Penggunaan Slot</small>
                <small class="text-muted"><?= $stats['total_pages'] ?>/<?= $user_limits['max_pages'] ?></small>
            </div>
            <div class="progress">
                <?php 
                $usage_percentage = ($stats['total_pages'] / $user_limits['max_pages']) * 100;
                $progress_class = $usage_percentage >= 90 ? 'bg-danger' : ($usage_percentage >= 70 ? 'bg-warning' : 'bg-success');
                ?>
                <div class="progress-bar <?= $progress_class ?>" 
                     role="progressbar" 
                     style="width: <?= min(100, $usage_percentage) ?>%"
                     aria-valuenow="<?= $stats['total_pages'] ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="<?= $user_limits['max_pages'] ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Landing Pages List -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>Landing Pages Saya
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($landing_pages)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Landing Page</th>
                        <th>Status</th>
                        <?php if ($user_limits['analytics']): ?>
                        <th>Views</th>
                        <th>Conversions</th>
                        <th>Revenue</th>
                        <?php endif; ?>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($landing_pages as $page): ?>
                    <tr>
                        <td>
                            <div>
                                <h6 class="mb-1">
                                    <a href="#" class="text-decoration-none toggle-info" 
                                       data-target="info-<?= $page['id'] ?>">
                                        <?= htmlspecialchars($page['page_title']) ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    /<?= htmlspecialchars($page['page_slug']) ?>
                                </small>
                            </div>
                            <div id="info-<?= $page['id'] ?>" class="mt-2 collapse">
                                <div class="card card-body bg-light">
                                    <small>
                                        <strong>URL:</strong> 
                                        <a href="<?= epic_url('landing/' . $page['page_slug']) ?>" 
                                           target="_blank" class="text-decoration-none">
                                            <?= epic_url('landing/' . $page['page_slug']) ?>
                                        </a><br>
                                        <strong>Description:</strong> 
                                        <?= htmlspecialchars($page['page_description'] ?: 'No description') ?><br>
                                        <strong>Referral URL:</strong>
                                        <div class="input-group input-group-sm mt-1">
                                            <input type="text" class="form-control" 
                                                   value="<?= epic_url('landing/' . $page['page_slug'] . '?ref=' . $user['referral_code']) ?>" 
                                                   readonly id="url-<?= $page['id'] ?>">
                                            <button class="btn btn-outline-secondary copy-url" 
                                                    data-url="<?= epic_url('landing/' . $page['page_slug'] . '?ref=' . $user['referral_code']) ?>"
                                                    title="Copy URL">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($page['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($user_limits['analytics']): ?>
                        <td>
                            <span class="badge bg-info"><?= number_format($page['views']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-success"><?= number_format($page['conversions']) ?></span>
                            <small class="text-muted d-block"><?= $page['conversion_rate'] ?>%</small>
                        </td>
                        <td>
                            <span class="text-success fw-bold">Rp <?= number_format($page['revenue']) ?></span>
                        </td>
                        <?php endif; ?>
                        <td>
                            <small><?= $page['created_date'] ?></small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <!-- Preview -->
                                <a href="<?= epic_url('landing/' . $page['page_slug']) ?>" 
                                   target="_blank" class="btn btn-outline-primary" title="Preview">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                
                                <!-- Edit -->
                                <a href="<?= epic_url('dashboard/member/landing-pages/edit/' . $page['id']) ?>" 
                                   class="btn btn-outline-success" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Toggle Status -->
                                <?php if ($page['is_active']): ?>
                                <button type="button" class="btn btn-outline-warning toggle-status" 
                                        data-id="<?= $page['id'] ?>" data-status="0" title="Deactivate">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <?php else: ?>
                                <button type="button" class="btn btn-outline-info toggle-status" 
                                        data-id="<?= $page['id'] ?>" data-status="1" title="Activate">
                                    <i class="fas fa-play"></i>
                                </button>
                                <?php endif; ?>
                                
                                <!-- Delete -->
                                <button type="button" class="btn btn-outline-danger delete-landing" 
                                        data-id="<?= $page['id'] ?>" 
                                        data-title="<?= htmlspecialchars($page['page_title']) ?>" 
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-5">
            <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Belum ada landing page</h5>
            <p class="text-muted">
                Mulai buat landing page pertama Anda untuk meningkatkan konversi
            </p>
            <?php if ($stats['total_pages'] < $user_limits['max_pages']): ?>
            <a href="<?= epic_url('dashboard/member/landing-pages/create') ?>" class="btn btn-success mt-2">
                <i class="fas fa-plus me-2"></i>Buat Landing Page Pertama
            </a>
            <?php else: ?>
            <p class="text-warning mt-2">
                <i class="fas fa-lock me-1"></i>Limit landing page telah tercapai
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p>Anda akan menghapus landing page:</p>
                    <h6 id="deleteTitle" class="fw-bold"></h6>
                    <p class="text-danger mt-3">Tindakan ini tidak dapat dibatalkan!</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Forms for Actions -->
<form id="actionForm" method="POST" style="display: none;">
    <input type="hidden" name="action" id="actionType">
    <input type="hidden" name="page_id" id="actionPageId">
    <input type="hidden" name="status" id="actionStatus">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle info display
    document.querySelectorAll('.toggle-info').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const target = document.getElementById(targetId);
            const collapse = new bootstrap.Collapse(target);
            collapse.toggle();
        });
    });
    
    // Copy URL functionality
    document.querySelectorAll('.copy-url').forEach(function(element) {
        element.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            navigator.clipboard.writeText(url).then(function() {
                // Show success feedback
                const originalIcon = element.innerHTML;
                element.innerHTML = '<i class="fas fa-check"></i>';
                element.classList.add('btn-success');
                element.classList.remove('btn-outline-secondary');
                
                setTimeout(function() {
                    element.innerHTML = originalIcon;
                    element.classList.remove('btn-success');
                    element.classList.add('btn-outline-secondary');
                }, 2000);
            });
        });
    });
    
    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(function(element) {
        element.addEventListener('click', function() {
            const pageId = this.getAttribute('data-id');
            const status = this.getAttribute('data-status');
            const statusText = status === '1' ? 'mengaktifkan' : 'menonaktifkan';
            
            if (confirm(`Yakin ingin ${statusText} landing page ini?`)) {
                document.getElementById('actionType').value = 'toggle_status';
                document.getElementById('actionPageId').value = pageId;
                document.getElementById('actionStatus').value = status;
                document.getElementById('actionForm').submit();
            }
        });
    });
    
    // Delete landing page
    let deletePageId = null;
    document.querySelectorAll('.delete-landing').forEach(function(element) {
        element.addEventListener('click', function() {
            deletePageId = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            
            document.getElementById('deleteTitle').textContent = title;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
    
    // Confirm delete
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deletePageId) {
            document.getElementById('actionType').value = 'delete';
            document.getElementById('actionPageId').value = deletePageId;
            document.getElementById('actionForm').submit();
        }
    });
});
</script>