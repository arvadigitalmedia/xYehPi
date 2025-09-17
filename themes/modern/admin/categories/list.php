<?php
/**
 * Categories List Content
 * Display all categories with management options
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}
?>

<!-- Categories List Content -->
<div class="admin-content">
                <div class="content-header">
                    <div class="header-left">
                        <h1 class="page-title">
                            <i data-feather="folder" class="page-icon"></i>
                            Categories Management
                        </h1>
                        <nav class="breadcrumb">
                            <a href="<?= epic_url('admin') ?>" class="breadcrumb-item">Admin</a>
                            <span class="breadcrumb-separator">/</span>
                            <span class="breadcrumb-item active">Categories</span>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="<?= epic_url('admin/categories?action=add') ?>" class="btn btn-primary">
                            <i data-feather="plus" width="16" height="16"></i>
                            Add Category
                        </a>
                    </div>
                </div>
                
                <div class="content-body">
                    <?php if (isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
                            <?= htmlspecialchars($_SESSION['flash_message']) ?>
                        </div>
                        <?php 
                        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
                        ?>
                    <?php endif; ?>
                    
                    <div class="content-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i data-feather="list" class="card-icon"></i>
                                All Categories
                            </h3>
                            <div class="card-actions">
                                <span class="text-muted"><?= count($categories) ?> categories</span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <?php if (empty($categories)): ?>
                                <div class="empty-state">
                                    <i data-feather="folder-plus" width="48" height="48"></i>
                                    <h4>No Categories Yet</h4>
                                    <p>Start organizing your blog content by creating categories.</p>
                                    <a href="<?= epic_url('admin/categories?action=add') ?>" class="btn btn-primary">
                                        Create First Category
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Articles</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td>
                                                        <div class="category-name-cell">
                                                            <h5 class="category-name">
                                                                <?= htmlspecialchars($category['name']) ?>
                                                            </h5>
                                                            <span class="category-slug">
                                                                <?= htmlspecialchars($category['slug'] ?? '') ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="category-description">
                                                            <?= htmlspecialchars($category['description'] ?? '') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="article-count">
                                                            <?= number_format($category['article_count'] ?? 0) ?> articles
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-<?= $category['status'] ?? 'active' ?>">
                                                            <?php if (($category['status'] ?? 'active') === 'active'): ?>
                                                                <i data-feather="check-circle" width="12" height="12"></i>
                                                                Active
                                                            <?php else: ?>
                                                                <i data-feather="x-circle" width="12" height="12"></i>
                                                                Inactive
                                                            <?php endif; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="date-text">
                                                            <?= date('d M Y', strtotime($category['created_at'] ?? 'now')) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="<?= epic_url('admin/categories?action=edit&id=' . $category['id']) ?>" 
                                                               class="btn btn-sm btn-secondary" title="Edit">
                                                                <i data-feather="edit" width="14" height="14"></i>
                                                            </a>
                                                            <button onclick="deleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>')" 
                                                                    class="btn btn-sm btn-danger" title="Delete"
                                                                    <?= ($category['article_count'] ?? 0) > 0 ? 'disabled' : '' ?>>
                                                                <i data-feather="trash-2" width="14" height="14"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

<script>
function deleteCategory(categoryId, categoryName) {
    if (confirm(`Are you sure you want to delete category "${categoryName}"? This action cannot be undone.`)) {
        window.location.href = `<?= epic_url('admin/categories') ?>?action=delete&id=${categoryId}`;
    }
}
</script>
    
    <style>
    /* Categories List Specific Styles */
    .category-name-cell .category-name {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--ink-100);
    }
    
    .category-name-cell .category-slug {
        font-size: var(--font-size-xs);
        color: var(--ink-400);
        font-family: 'Courier New', monospace;
    }
    
    .category-description {
        color: var(--ink-300);
        font-size: var(--font-size-sm);
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .article-count {
        color: var(--ink-300);
        font-weight: var(--font-weight-medium);
    }
    
    .status-badge.status-active {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    
    .status-badge.status-inactive {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    
    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .btn:disabled:hover {
        background: var(--surface-3);
        transform: none;
    }
    </style>