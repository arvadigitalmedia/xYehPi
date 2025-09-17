<?php
/**
 * Add Category Content
 * Form to create new category
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}
?>

<!-- Add Category Content -->
<div class="admin-content">
                <div class="content-header">
                    <div class="header-left">
                        <h1 class="page-title">
                            <i data-feather="folder-plus" class="page-icon"></i>
                            Add New Category
                        </h1>
                        <nav class="breadcrumb">
                            <a href="<?= epic_url('admin') ?>" class="breadcrumb-item">Admin</a>
                            <span class="breadcrumb-separator">/</span>
                            <a href="<?= epic_url('admin/categories') ?>" class="breadcrumb-item">Categories</a>
                            <span class="breadcrumb-separator">/</span>
                            <span class="breadcrumb-item active">Add New</span>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="<?= epic_url('admin/categories') ?>" class="btn btn-secondary">
                            <i data-feather="arrow-left" width="16" height="16"></i>
                            Back to Categories
                        </a>
                    </div>
                </div>
                
                <div class="content-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i data-feather="alert-circle" width="16" height="16"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i data-feather="check-circle" width="16" height="16"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="category-form">
                        <div class="form-layout">
                            <div class="main-content">
                                <!-- Category Information -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <h3 class="section-title">
                                            <i data-feather="info" class="section-icon"></i>
                                            Category Information
                                        </h3>
                                    </div>
                                    
                                    <div class="section-body">
                                        <div class="form-group">
                                            <label class="form-label required">Category Name</label>
                                            <input type="text" name="name" class="form-input" 
                                                   placeholder="Enter category name" 
                                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                                                   required>
                                            <div class="form-help">
                                                A descriptive name for your category
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-textarea" 
                                                      placeholder="Brief description of this category (optional)" 
                                                      rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                            <div class="form-help">
                                                Optional description to help organize your content
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="sidebar-content">
                                <!-- Category Settings -->
                                <div class="sidebar-section">
                                    <div class="section-header">
                                        <h3 class="section-title">
                                            <i data-feather="settings" class="section-icon"></i>
                                            Settings
                                        </h3>
                                    </div>
                                    
                                    <div class="section-body">
                                        <div class="form-group">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>
                                                    Active
                                                </option>
                                                <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                                                    Inactive
                                                </option>
                                            </select>
                                            <div class="form-help">
                                                Only active categories will be available for articles
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Category Preview -->
                                <div class="sidebar-section">
                                    <div class="section-header">
                                        <h3 class="section-title">
                                            <i data-feather="eye" class="section-icon"></i>
                                            Preview
                                        </h3>
                                    </div>
                                    
                                    <div class="section-body">
                                        <div class="category-preview">
                                            <div class="preview-item">
                                                <div class="preview-label">Name:</div>
                                                <div class="preview-value" id="previewName">Category Name</div>
                                            </div>
                                            <div class="preview-item">
                                                <div class="preview-label">Slug:</div>
                                                <div class="preview-value" id="previewSlug">category-name</div>
                                            </div>
                                            <div class="preview-item">
                                                <div class="preview-label">Status:</div>
                                                <div class="preview-value" id="previewStatus">
                                                    <span class="status-badge status-active">
                                                        <i data-feather="check-circle" width="12" height="12"></i>
                                                        Active
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <div class="actions-left">
                                <a href="<?= epic_url('admin/categories') ?>" class="btn btn-secondary">
                                    <i data-feather="x" width="16" height="16"></i>
                                    Cancel
                                </a>
                            </div>
                            <div class="actions-right">
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="save" width="16" height="16"></i>
                                    Create Category
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

<script>
    // Generate slug from name
    function generateSlug(text) {
        return text
            .toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
    }
    
    // Update preview
    function updatePreview() {
        const nameInput = document.querySelector('input[name="name"]');
        const statusSelect = document.querySelector('select[name="status"]');
        
        const name = nameInput.value || 'Category Name';
        const slug = generateSlug(name);
        const status = statusSelect.value;
        
        document.getElementById('previewName').textContent = name;
        document.getElementById('previewSlug').textContent = slug || 'category-name';
        
        const statusBadge = document.querySelector('#previewStatus .status-badge');
        const statusIcon = statusBadge.querySelector('i');
        const statusText = statusBadge.lastChild;
        
        if (status === 'active') {
            statusBadge.className = 'status-badge status-active';
            statusIcon.setAttribute('data-feather', 'check-circle');
            statusText.textContent = ' Active';
        } else {
            statusBadge.className = 'status-badge status-inactive';
            statusIcon.setAttribute('data-feather', 'x-circle');
            statusText.textContent = ' Inactive';
        }
        
        feather.replace();
    }
    
    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.querySelector('input[name="name"]');
        const statusSelect = document.querySelector('select[name="status"]');
        
        nameInput.addEventListener('input', updatePreview);
        statusSelect.addEventListener('change', updatePreview);
        
        // Initial preview update
        updatePreview();
    });
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    </script>
    
    <style>
    /* Category Form Styles */
    .category-form {
        max-width: none;
    }
    
    .form-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: var(--spacing-8);
        margin-bottom: var(--spacing-8);
    }
    
    .main-content {
        min-width: 0;
    }
    
    .sidebar-content {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-6);
    }
    
    .form-section {
        background: var(--surface-1);
        border: 1px solid var(--ink-700);
        border-radius: var(--radius-xl);
        padding: var(--spacing-6);
        margin-bottom: var(--spacing-6);
    }
    
    .sidebar-section {
        background: var(--surface-1);
        border: 1px solid var(--ink-700);
        border-radius: var(--radius-xl);
        padding: var(--spacing-6);
    }
    
    .section-header {
        margin-bottom: var(--spacing-4);
        padding-bottom: var(--spacing-3);
        border-bottom: 1px solid var(--ink-700);
    }
    
    .section-title {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        margin: 0;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--ink-100);
    }
    
    .section-icon {
        color: var(--gold-400);
    }
    
    .category-preview {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-3);
    }
    
    .preview-item {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-1);
    }
    
    .preview-label {
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        color: var(--ink-400);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .preview-value {
        font-size: var(--font-size-sm);
        color: var(--ink-200);
        font-weight: var(--font-weight-medium);
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-6);
        background: var(--surface-1);
        border: 1px solid var(--ink-700);
        border-radius: var(--radius-xl);
        margin-top: var(--spacing-6);
    }
    
    .actions-left,
    .actions-right {
        display: flex;
        gap: var(--spacing-3);
    }
    
    @media (max-width: 768px) {
        .form-layout {
            grid-template-columns: 1fr;
            gap: var(--spacing-6);
        }
        
        .form-actions {
            flex-direction: column;
            gap: var(--spacing-4);
        }
        
        .actions-left,
        .actions-right {
            width: 100%;
            justify-content: center;
        }
    }
    </style>