<?php
/**
 * Edit Product Content - New System
 * Content untuk halaman edit produk
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

<!-- Edit Product Form -->
<div class="edit-product-container">
    <form method="POST" enctype="multipart/form-data" class="edit-product-form" id="productForm">
        <!-- Product Category Info -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i data-feather="<?= $product['category'] === 'lms' ? 'book-open' : 'monitor' ?>" width="20" height="20"></i>
                Product Category: <?= ucfirst($product['category']) ?>
            </h3>
            <div class="category-info">
                <div class="category-badge">
                    <?php if ($product['category'] === 'lms'): ?>
                        <span class="badge badge-info">
                            <i data-feather="book-open" width="14" height="14"></i>
                            LMS Course
                        </span>
                    <?php else: ?>
                        <span class="badge badge-primary">
                            <i data-feather="monitor" width="14" height="14"></i>
                            Digital Product
                        </span>
                    <?php endif; ?>
                </div>
                <p class="category-note">
                    Product category cannot be changed after creation. If you need to change the category, 
                    please create a new product.
                </p>
            </div>
        </div>
        
        <!-- Basic Information -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i data-feather="info" width="20" height="20"></i>
                Basic Information
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name" class="form-label required">Product Name</label>
                    <input type="text" id="name" name="name" class="form-input" 
                           value="<?= htmlspecialchars($form_data['name'] ?? '') ?>"
                           placeholder="Enter product name" required>
                    <div class="form-help">Choose a clear, descriptive name for your product</div>
                </div>
                
                <div class="form-group">
                    <label for="price" class="form-label required">Price (Rp)</label>
                    <input type="number" id="price" name="price" class="form-input" 
                           value="<?= htmlspecialchars($form_data['price'] ?? '') ?>"
                           placeholder="0" min="0" step="1000" required>
                    <div class="form-help">Set the price in Indonesian Rupiah</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="short_description" class="form-label required">Short Description</label>
                <textarea id="short_description" name="short_description" class="form-textarea" rows="3" 
                          placeholder="Brief description for product cards and previews" required><?= htmlspecialchars($form_data['short_description'] ?? '') ?></textarea>
                <div class="form-help">This will be shown in product cards and search results (max 200 characters)</div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label required">Full Description</label>
                <textarea id="description" name="description" class="form-textarea" rows="6" 
                          placeholder="Detailed product description" required><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>
                <div class="form-help">Provide a comprehensive description of your product</div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="image" class="form-label">Product Image</label>
                    <?php if (!empty($product['image'])): ?>
                        <div class="current-image">
                            <img src="<?= epic_url('uploads/products/' . $product['image']) ?>" 
                                 alt="Current product image" class="current-image-preview">
                            <div class="current-image-info">
                                <span class="current-image-label">Current Image</span>
                                <span class="current-image-name"><?= htmlspecialchars($product['image']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" class="form-file" accept="image/*">
                    <div class="form-help">
                        <?= !empty($product['image']) ? 'Upload a new image to replace the current one' : 'Upload a square image' ?> 
                        (recommended: 400x400px, max 2MB)
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" <?= ($form_data['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($form_data['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <div class="form-help">Control whether this product is visible to customers</div>
                </div>
            </div>
        </div>
        
        <!-- Digital Product Specific Fields -->
        <?php if ($product['category'] === 'digital'): ?>
        <div class="form-section">
            <h3 class="form-section-title">
                <i data-feather="download" width="20" height="20"></i>
                Digital Product Details
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="download_url" class="form-label">Download URL</label>
                    <input type="url" id="download_url" name="download_url" class="form-input" 
                           value="<?= htmlspecialchars($form_data['download_url'] ?? '') ?>"
                           placeholder="https://example.com/download/file.zip">
                    <div class="form-help">Direct link to the downloadable file</div>
                </div>
                
                <div class="form-group">
                    <label for="file_size" class="form-label">File Size</label>
                    <input type="text" id="file_size" name="file_size" class="form-input" 
                           value="<?= htmlspecialchars($form_data['file_size'] ?? '') ?>"
                           placeholder="e.g., 25 MB, 1.2 GB">
                    <div class="form-help">Size of the downloadable file</div>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="file_format" class="form-label">File Format</label>
                    <input type="text" id="file_format" name="file_format" class="form-input" 
                           value="<?= htmlspecialchars($form_data['file_format'] ?? '') ?>"
                           placeholder="e.g., PDF, ZIP, MP4, etc.">
                    <div class="form-help">Format of the downloadable file</div>
                </div>
                
                <div class="form-group">
                    <label for="access_duration" class="form-label">Access Duration (days)</label>
                    <input type="number" id="access_duration" name="access_duration" class="form-input" 
                           value="<?= htmlspecialchars($form_data['access_duration'] ?? '365') ?>"
                           placeholder="365" min="1">
                    <div class="form-help">How long customers can access the download (0 = unlimited)</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- LMS Specific Fields -->
        <?php if ($product['category'] === 'lms'): ?>
        <div class="form-section">
            <h3 class="form-section-title">
                <i data-feather="book-open" width="20" height="20"></i>
                LMS Course Details
            </h3>
            
            <div class="lms-placeholder">
                <div class="placeholder-icon">
                    <i data-feather="construction" width="48" height="48"></i>
                </div>
                <h4 class="placeholder-title">LMS Features Coming Soon</h4>
                <p class="placeholder-description">
                    The Learning Management System features are currently under development. 
                    You can update the basic course information below.
                </p>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="course_duration" class="form-label">Course Duration</label>
                    <input type="text" id="course_duration" name="course_duration" class="form-input" 
                           value="<?= htmlspecialchars($form_data['course_duration'] ?? '') ?>"
                           placeholder="e.g., 4 weeks, 20 hours">
                    <div class="form-help">Estimated time to complete the course</div>
                </div>
                
                <div class="form-group">
                    <label for="difficulty_level" class="form-label">Difficulty Level</label>
                    <select id="difficulty_level" name="difficulty_level" class="form-select">
                        <option value="beginner" <?= ($form_data['difficulty_level'] ?? 'beginner') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= ($form_data['difficulty_level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= ($form_data['difficulty_level'] ?? '') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                    <div class="form-help">Target skill level for this course</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="instructor_name" class="form-label">Instructor Name</label>
                <input type="text" id="instructor_name" name="instructor_name" class="form-input" 
                       value="<?= htmlspecialchars($form_data['instructor_name'] ?? '') ?>"
                       placeholder="Name of the course instructor">
                <div class="form-help">Name of the person teaching this course</div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Product Metadata -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i data-feather="clock" width="20" height="20"></i>
                Product Information
            </h3>
            
            <div class="metadata-grid">
                <div class="metadata-item">
                    <label class="metadata-label">Product ID</label>
                    <span class="metadata-value">#<?= $product['id'] ?></span>
                </div>
                
                <div class="metadata-item">
                    <label class="metadata-label">Slug</label>
                    <span class="metadata-value"><?= htmlspecialchars($product['slug']) ?></span>
                </div>
                
                <div class="metadata-item">
                    <label class="metadata-label">Created</label>
                    <span class="metadata-value"><?= date('M j, Y \a\t H:i', strtotime($product['created_at'])) ?></span>
                </div>
                
                <div class="metadata-item">
                    <label class="metadata-label">Last Updated</label>
                    <span class="metadata-value"><?= date('M j, Y \a\t H:i', strtotime($product['updated_at'])) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= epic_url('admin/manage/product') ?>'">
                <i data-feather="x" width="16" height="16"></i>
                Cancel
            </button>
            
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i data-feather="save" width="16" height="16"></i>
                Update Product
            </button>
        </div>
    </form>
</div>

<style>
/* Edit Product Styles */
.edit-product-container {
    max-width: 800px;
    margin: 0 auto;
}

.edit-product-form {
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.form-section {
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-700);
}

.form-section:last-child {
    border-bottom: none;
}

.form-section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-4);
}

/* Category Info */
.category-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
}

.category-badge .badge {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}

.badge-info {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.badge-primary {
    background: rgba(207, 168, 78, 0.2);
    color: var(--gold-300);
    border: 1px solid rgba(207, 168, 78, 0.3);
}

.category-note {
    color: var(--ink-400);
    font-size: var(--font-size-sm);
    line-height: 1.5;
    margin: 0;
}

/* Current Image */
.current-image {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-3);
}

.current-image-preview {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: var(--radius-md);
    border: 1px solid var(--ink-600);
}

.current-image-info {
    flex: 1;
}

.current-image-label {
    display: block;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-200);
    margin-bottom: var(--spacing-1);
}

.current-image-name {
    display: block;
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

/* Metadata */
.metadata-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-4);
}

@media (max-width: 768px) {
    .metadata-grid {
        grid-template-columns: 1fr;
    }
}

.metadata-item {
    padding: var(--spacing-3);
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
}

.metadata-label {
    display: block;
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    color: var(--ink-400);
    margin-bottom: var(--spacing-1);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.metadata-value {
    display: block;
    font-size: var(--font-size-sm);
    color: var(--ink-200);
    font-weight: var(--font-weight-medium);
}

/* Form Elements - Inherit from add product styles */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

.form-group {
    margin-bottom: var(--spacing-4);
}

.form-label {
    display: block;
    margin-bottom: var(--spacing-2);
    font-weight: var(--font-weight-medium);
    color: var(--ink-200);
    font-size: var(--font-size-sm);
}

.form-label.required::after {
    content: ' *';
    color: var(--error-light);
}

.form-input,
.form-textarea,
.form-select,
.form-file {
    width: 100%;
    padding: var(--spacing-3);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    background: var(--surface-2);
    color: var(--ink-100);
    font-size: var(--font-size-sm);
    transition: all var(--transition-fast);
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
    outline: none;
    border-color: var(--gold-400);
    box-shadow: 0 0 0 3px rgba(207, 168, 78, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-file {
    padding: var(--spacing-2);
}

.form-help {
    margin-top: var(--spacing-1);
    color: var(--ink-500);
    font-size: var(--font-size-xs);
    line-height: 1.4;
}

/* LMS Placeholder */
.lms-placeholder {
    text-align: center;
    padding: var(--spacing-6) var(--spacing-4);
    background: var(--surface-2);
    border: 2px dashed var(--ink-600);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-6);
}

.placeholder-icon {
    color: var(--ink-500);
    margin-bottom: var(--spacing-3);
}

.placeholder-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-300);
    margin-bottom: var(--spacing-2);
}

.placeholder-description {
    color: var(--ink-500);
    font-size: var(--font-size-sm);
    line-height: 1.6;
    max-width: 400px;
    margin: 0 auto;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-3);
    padding: var(--spacing-6);
    background: var(--surface-2);
    border-top: 1px solid var(--ink-700);
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
}

.btn {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-3) var(--spacing-5);
    border: none;
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    text-decoration: none;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.btn-primary {
    background: var(--gradient-gold);
    color: var(--ink-900);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.btn-secondary {
    background: var(--surface-3);
    color: var(--ink-300);
    border: 1px solid var(--ink-600);
}

.btn-secondary:hover {
    background: var(--surface-4);
    color: var(--ink-100);
    border-color: var(--ink-500);
}

/* Alert Styles */
.alert {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-3);
    padding: var(--spacing-4);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-6);
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: var(--success-light);
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: var(--error-light);
}
</style>

<script>
// Form functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Form validation
    const form = document.getElementById('productForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-feather="loader" width="16" height="16" class="animate-spin"></i> Updating Product...';
        
        // Re-initialize feather icons for the new icon
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
    
    // File input preview
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    this.value = '';
                    return;
                }
                
                // Show file name
                const fileName = file.name;
                const helpText = this.parentNode.querySelector('.form-help');
                helpText.innerHTML = `Selected: ${fileName} (will replace current image)`;
            }
        });
    }
});
</script>