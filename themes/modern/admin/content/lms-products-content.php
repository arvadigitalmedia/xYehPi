<?php
/**
 * EPIC Hub Admin LMS Products Management Content
 * Comprehensive LMS product management interface
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Extract variables dari layout data
extract($data ?? []);

// Get current action
$action = $_GET['action'] ?? 'list';
$product_id = $_GET['id'] ?? null;

// Get product data for edit
$product = null;
$modules = [];
if ($action === 'edit' && $product_id) {
    $product = db()->selectOne(
        "SELECT p.*, c.name as category_name, u.name as instructor_name
         FROM epic_products p
         LEFT JOIN epic_product_categories c ON p.category_id = c.id
         LEFT JOIN epic_users u ON p.instructor_id = u.id
         WHERE p.id = ?",
        [$product_id]
    );
    
    $modules = db()->select(
        "SELECT * FROM epic_product_modules 
         WHERE product_id = ? 
         ORDER BY sort_order, created_at",
        [$product_id]
    ) ?: [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i data-feather="book-open" width="24" height="24"></i>
            LMS Products Management
        </h1>
        <p class="page-subtitle">Manage your learning management system products, modules, and configurations</p>
    </div>
    
    <div class="page-actions">
        <?php if ($action === 'list'): ?>
            <button type="button" class="btn btn-primary" onclick="showCreateModal()">
                <i data-feather="plus" width="16" height="16"></i>
                Add New Product
            </button>
        <?php elseif ($action === 'edit'): ?>
            <a href="?" class="btn btn-secondary">
                <i data-feather="arrow-left" width="16" height="16"></i>
                Back to List
            </a>
            <button type="button" class="btn btn-primary" onclick="showCreateModuleModal()">
                <i data-feather="plus" width="16" height="16"></i>
                Add Module
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Alerts -->
<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i data-feather="check-circle" width="16" height="16"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">
        <i data-feather="alert-circle" width="16" height="16"></i>
        <?= htmlspecialchars($error) ?>
        <?php if (strpos($error, 'LMS database tables') !== false): ?>
            <br><br>
            <a href="<?= epic_url('install-lms.php') ?>" class="btn btn-primary" style="margin-top: 10px;">
                <i data-feather="download" width="16" height="16"></i>
                Install LMS Database
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- Search and Filters -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">Search & Filter</h2>
        </div>
        
        <form method="get" class="search-form">
            <div class="search-grid">
                <div class="form-group">
                    <label class="form-label">Search Products</label>
                    <div class="input-group">
                        <span class="input-group-icon">
                            <i data-feather="search" width="16" height="16"></i>
                        </span>
                        <input type="text" class="form-input" name="search" 
                               value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Search by name or description...">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Product Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="course" <?= $type_filter === 'course' ? 'selected' : '' ?>>Course</option>
                        <option value="tools" <?= $type_filter === 'tools' ? 'selected' : '' ?>>Tools</option>
                        <option value="masterclass" <?= $type_filter === 'masterclass' ? 'selected' : '' ?>>Masterclass</option>
                        <option value="digital" <?= $type_filter === 'digital' ? 'selected' : '' ?>>Digital</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="archived" <?= $status_filter === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="search" width="16" height="16"></i>
                        Search
                    </button>
                    <?php if (!empty($search) || !empty($type_filter) || !empty($status_filter) || !empty($category_filter)): ?>
                        <a href="?" class="btn btn-secondary">
                            <i data-feather="x" width="16" height="16"></i>
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="search-info">
                <i data-feather="info" width="14" height="14"></i>
                <span><?= number_format($total_products) ?> products found</span>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="content-section">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-feather="book-open" width="48" height="48"></i>
                </div>
                <h3 class="empty-state-title">No LMS Products Found</h3>
                <p class="empty-state-text">No products match your search criteria. Try adjusting your filters or add a new product.</p>
                <button type="button" class="btn btn-primary" onclick="showCreateModal()">
                    <i data-feather="plus" width="16" height="16"></i>
                    Add First Product
                </button>
            </div>
        <?php else: ?>
            <div class="lms-products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="lms-product-card" data-product-id="<?= $product['id'] ?>">
                        <div class="product-card-header">
                            <div class="product-type-badge type-<?= $product['type'] ?>">
                                <?= ucfirst($product['type']) ?>
                            </div>
                            <div class="product-status-badge status-<?= $product['status'] ?>">
                                <?= ucfirst($product['status']) ?>
                            </div>
                        </div>
                        
                        <div class="product-card-body">
                            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-description"><?= htmlspecialchars(substr($product['short_description'] ?? '', 0, 100)) ?>...</p>
                            
                            <div class="product-meta">
                                <div class="meta-item">
                                    <i data-feather="layers" width="14" height="14"></i>
                                    <span><?= $product['module_count'] ?> modules</span>
                                </div>
                                <div class="meta-item">
                                    <i data-feather="clock" width="14" height="14"></i>
                                    <span><?= $product['duration'] ?? 'N/A' ?></span>
                                </div>
                                <div class="meta-item">
                                    <i data-feather="users" width="14" height="14"></i>
                                    <span><?= $product['enrollment_count'] ?> enrolled</span>
                                </div>
                                <?php if ($product['avg_rating']): ?>
                                    <div class="meta-item">
                                        <i data-feather="star" width="14" height="14"></i>
                                        <span><?= number_format($product['avg_rating'], 1) ?> (<?= $product['review_count'] ?>)</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-price">
                                <span class="price-amount">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                                <span class="commission-info"><?= $product['commission_value'] ?>% commission</span>
                            </div>
                            
                            <?php if ($product['category_name']): ?>
                                <div class="product-category" style="--category-color: <?= $product['category_color'] ?? '#3B82F6' ?>">
                                    <?= htmlspecialchars($product['category_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-card-footer">
                            <div class="product-actions">
                                <a href="?action=edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                                    <i data-feather="edit" width="14" height="14"></i>
                                    Edit
                                </a>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="showEditModal(<?= $product['id'] ?>)">
                                    <i data-feather="settings" width="14" height="14"></i>
                                    Quick Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>')">
                                    <i data-feather="trash-2" width="14" height="14"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?start=<?= $current_page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($type_filter) ? '&type=' . urlencode($type_filter) : '' ?><?= !empty($status_filter) ? '&status=' . urlencode($status_filter) : '' ?><?= !empty($category_filter) ? '&category=' . urlencode($category_filter) : '' ?>" class="pagination-link">
                                <i data-feather="chevron-left" width="16" height="16"></i>
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                            <a href="?start=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($type_filter) ? '&type=' . urlencode($type_filter) : '' ?><?= !empty($status_filter) ? '&status=' . urlencode($status_filter) : '' ?><?= !empty($category_filter) ? '&category=' . urlencode($category_filter) : '' ?>" 
                               class="pagination-link <?= $i == $current_page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?start=<?= $current_page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($type_filter) ? '&type=' . urlencode($type_filter) : '' ?><?= !empty($status_filter) ? '&status=' . urlencode($status_filter) : '' ?><?= !empty($category_filter) ? '&category=' . urlencode($category_filter) : '' ?>" class="pagination-link">
                                Next
                                <i data-feather="chevron-right" width="16" height="16"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="pagination-info">
                        Showing <?= ($current_page - 1) * $jmlperpage + 1 ?> to <?= min($current_page * $jmlperpage, $total_products) ?> of <?= number_format($total_products) ?> products
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php elseif ($action === 'edit' && $product): ?>
    <!-- Product Edit Interface -->
    <div class="product-edit-container">
        <!-- Product Details Section -->
        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i data-feather="edit" width="20" height="20"></i>
                    Edit Product: <?= htmlspecialchars($product['name']) ?>
                </h2>
                <div class="section-actions">
                    <span class="status-badge status-<?= $product['status'] ?>"><?= ucfirst($product['status']) ?></span>
                    <span class="type-badge type-<?= $product['type'] ?>"><?= ucfirst($product['type']) ?></span>
                </div>
            </div>
            
            <form method="post" class="product-edit-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_product">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">Product Name</label>
                        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Course Type</label>
                        <select name="type" class="form-select" required>
                            <option value="course" <?= $product['type'] === 'course' ? 'selected' : '' ?>>Standard Course</option>
                            <option value="masterclass" <?= $product['type'] === 'masterclass' ? 'selected' : '' ?>>Masterclass</option>
                            <option value="workshop" <?= $product['type'] === 'workshop' ? 'selected' : '' ?>>Workshop</option>
                            <option value="certification" <?= $product['type'] === 'certification' ? 'selected' : '' ?>>Certification Program</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Instructor</label>
                        <select name="instructor_id" class="form-select">
                            <option value="">Select Instructor</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?= $instructor['id'] ?>" <?= $product['instructor_id'] == $instructor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($instructor['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Course Duration</label>
                        <input type="text" name="duration" class="form-input" value="<?= htmlspecialchars($product['duration'] ?? '') ?>" placeholder="e.g., 8 hours, 4 weeks">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Difficulty Level</label>
                        <select name="difficulty_level" class="form-select" required>
                            <option value="">Select Difficulty</option>
                            <option value="beginner" <?= $product['difficulty_level'] === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                            <option value="intermediate" <?= $product['difficulty_level'] === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                            <option value="advanced" <?= $product['difficulty_level'] === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                            <option value="expert" <?= $product['difficulty_level'] === 'expert' ? 'selected' : '' ?>>Expert</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Price (Rp)</label>
                        <input type="number" name="price" class="form-input" value="<?= $product['price'] ?>" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Estimated Hours</label>
                        <input type="number" name="estimated_hours" class="form-input" value="<?= htmlspecialchars($product['estimated_hours'] ?? '') ?>" placeholder="8" min="0" step="0.5">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Number of Modules</label>
                        <input type="number" name="total_modules" class="form-input" value="<?= htmlspecialchars($product['total_modules'] ?? '') ?>" placeholder="5" min="1" max="50">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Commission Type</label>
                        <select name="commission_type" class="form-select">
                            <option value="percentage" <?= ($product['commission_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                            <option value="fixed" <?= ($product['commission_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed Amount</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Commission Value</label>
                        <div class="input-group">
                            <input type="number" name="commission_value" class="form-input" value="<?= $product['commission_value'] ?>" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="archived" <?= $product['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" class="form-textarea" rows="3" placeholder="Brief description for product cards"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-file" accept="image/*">
                    <div class="form-help">Upload a square image (recommended: 400x400px, max 2MB)</div>
                    <?php if (!empty($product['image'])): ?>
                        <div class="current-image" style="margin-top: 10px;">
                            <img src="/uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="Current product image" style="max-width: 100px; height: auto; border-radius: 4px;">
                            <small style="display: block; color: #6b7280; margin-top: 5px;">Current image: <?= htmlspecialchars($product['image']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Full Description</label>
                    <textarea name="description" class="form-textarea" rows="6" required><?= htmlspecialchars($product['description']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Learning Objectives (one per line)</label>
                    <textarea name="learning_objectives" class="form-textarea" rows="5" placeholder="Enter learning objectives, one per line"><?php
                        if ($product['learning_objectives']) {
                            $objectives = json_decode($product['learning_objectives'], true);
                            echo htmlspecialchars(implode("\n", $objectives));
                        }
                    ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Access Levels</label>
                    <div class="checkbox-group">
                        <?php 
                        $access_levels = $product['access_level'] ? json_decode($product['access_level'], true) : [];
                        ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_free" value="1" <?= in_array('free', $access_levels) ? 'checked' : '' ?>>
                            <span class="checkbox-text">Free Members</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_epic" value="1" <?= in_array('epic', $access_levels) ? 'checked' : '' ?>>
                            <span class="checkbox-text">EPIC Members</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_epis" value="1" <?= in_array('epis', $access_levels) ? 'checked' : '' ?>>
                            <span class="checkbox-text">EPIS Members</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Course Features</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="certificate_enabled" value="1" <?= $product['certificate_enabled'] ? 'checked' : '' ?>>
                            <span class="checkbox-text">Enable Certificates</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="progress_tracking" value="1" <?= !empty($product['progress_tracking']) ? 'checked' : 'checked' ?>>
                            <span class="checkbox-text">Progress Tracking</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="quiz_enabled" value="1" <?= !empty($product['quiz_enabled']) ? 'checked' : '' ?>>
                            <span class="checkbox-text">Include Quizzes</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="discussion_enabled" value="1" <?= !empty($product['discussion_enabled']) ? 'checked' : '' ?>>
                            <span class="checkbox-text">Discussion Forum</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured" value="1" <?= $product['featured'] ? 'checked' : '' ?>>
                            <span class="checkbox-text">Featured Product</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" width="16" height="16"></i>
                        Update Product
                    </button>
                    <a href="?" class="btn btn-secondary">
                        <i data-feather="x" width="16" height="16"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Modules Section -->
        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i data-feather="layers" width="20" height="20"></i>
                    Course Modules (<?= count($modules) ?>)
                </h2>
                <button type="button" class="btn btn-primary" onclick="showCreateModuleModal()">
                    <i data-feather="plus" width="16" height="16"></i>
                    Add Module
                </button>
            </div>
            
            <?php if (empty($modules)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i data-feather="layers" width="48" height="48"></i>
                    </div>
                    <h3 class="empty-state-title">No Modules Yet</h3>
                    <p class="empty-state-text">Add modules to structure your course content.</p>
                    <button type="button" class="btn btn-primary" onclick="showCreateModuleModal()">
                        <i data-feather="plus" width="16" height="16"></i>
                        Add First Module
                    </button>
                </div>
            <?php else: ?>
                <div class="modules-list">
                    <?php foreach ($modules as $index => $module): ?>
                        <div class="module-item" data-module-id="<?= $module['id'] ?>">
                            <div class="module-header">
                                <div class="module-info">
                                    <div class="module-number"><?= $index + 1 ?></div>
                                    <div class="module-details">
                                        <h4 class="module-title"><?= htmlspecialchars($module['title']) ?></h4>
                                        <div class="module-meta">
                                            <span class="content-type type-<?= $module['content_type'] ?>"><?= ucfirst($module['content_type']) ?></span>
                                            <span class="duration"><?= $module['estimated_duration'] ?> min</span>
                                            <span class="status status-<?= $module['status'] ?>"><?= ucfirst($module['status']) ?></span>
                                            <?php if ($module['is_preview']): ?>
                                                <span class="preview-badge">Preview</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="module-actions">
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="showEditModuleModal(<?= $module['id'] ?>)">
                                        <i data-feather="edit" width="14" height="14"></i>
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteModule(<?= $module['id'] ?>, '<?= htmlspecialchars($module['title']) ?>')">
                                        <i data-feather="trash-2" width="14" height="14"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                            
                            <?php if ($module['description']): ?>
                                <div class="module-description">
                                    <?= htmlspecialchars($module['description']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Product not found -->
    <div class="content-section">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-feather="alert-circle" width="48" height="48"></i>
            </div>
            <h3 class="empty-state-title">Product Not Found</h3>
            <p class="empty-state-text">The requested product could not be found.</p>
            <a href="?" class="btn btn-primary">
                <i data-feather="arrow-left" width="16" height="16"></i>
                Back to Products
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Create Product Modal -->
<div id="createProductModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Create New LMS Product</h3>
            <button type="button" class="modal-close" onclick="hideCreateModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form method="post" class="modal-form">
            <input type="hidden" name="action" value="create_product">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">Product Name</label>
                        <input type="text" name="name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Product Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="course">Course</option>
                            <option value="tools">Tools</option>
                            <option value="masterclass">Masterclass</option>
                            <option value="digital">Digital</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Price (Rp)</label>
                        <input type="number" name="price" class="form-input" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" class="form-textarea" rows="2" placeholder="Brief description for product cards"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Full Description</label>
                    <textarea name="description" class="form-textarea" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Access Levels</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_free" value="1">
                            <span class="checkbox-text">Free Members</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_epic" value="1" checked>
                            <span class="checkbox-text">EPIC Members</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_epis" value="1" checked>
                            <span class="checkbox-text">EPIS Members</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="plus" width="16" height="16"></i>
                    Create Product
                </button>
                <button type="button" class="btn btn-secondary" onclick="hideCreateModal()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Create Module Modal -->
<div id="createModuleModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Module</h3>
            <button type="button" class="modal-close" onclick="hideCreateModuleModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form method="post" class="modal-form">
            <input type="hidden" name="action" value="create_module">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">Module Title</label>
                        <input type="text" name="title" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Content Type</label>
                        <select name="content_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="video">Video</option>
                            <option value="text">Text</option>
                            <option value="pdf">PDF</option>
                            <option value="quiz">Quiz</option>
                            <option value="assignment">Assignment</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-input" value="<?= count($modules) + 1 ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Estimated Duration (minutes)</label>
                        <input type="number" name="estimated_duration" class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Video URL (if applicable)</label>
                    <input type="url" name="video_url" class="form-input" placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_preview" value="1">
                            <span class="checkbox-text">Allow Preview</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="plus" width="16" height="16"></i>
                    Add Module
                </button>
                <button type="button" class="btn btn-secondary" onclick="hideCreateModuleModal()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Delete</h3>
            <button type="button" class="modal-close" onclick="hideDeleteModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="delete-confirmation">
                <div class="delete-icon">
                    <i data-feather="alert-triangle" width="48" height="48"></i>
                </div>
                <p class="delete-message">Are you sure you want to delete this product?</p>
                <p class="delete-product-name"></p>
                <p class="delete-warning">This action cannot be undone.</p>
            </div>
        </div>
        
        <div class="modal-footer">
            <form method="post" style="display: inline;">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="product_id" id="deleteProductId">
                <button type="submit" class="btn btn-danger">
                    <i data-feather="trash-2" width="16" height="16"></i>
                    Delete Product
                </button>
            </form>
            <button type="button" class="btn btn-secondary" onclick="hideDeleteModal()">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
// Modal functions
function showCreateModal() {
    document.getElementById('createProductModal').style.display = 'flex';
}

function hideCreateModal() {
    document.getElementById('createProductModal').style.display = 'none';
}

function showCreateModuleModal() {
    document.getElementById('createModuleModal').style.display = 'flex';
}

function hideCreateModuleModal() {
    document.getElementById('createModuleModal').style.display = 'none';
}

function confirmDelete(productId, productName) {
    document.getElementById('deleteProductId').value = productId;
    document.querySelector('.delete-product-name').textContent = productName;
    document.getElementById('deleteModal').style.display = 'flex';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

function confirmDeleteModule(moduleId, moduleTitle) {
    if (confirm(`Are you sure you want to delete the module "${moduleTitle}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_module">
            <input type="hidden" name="module_id" value="${moduleId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});
</script>

<style>
/* LMS Products specific styles */
.lms-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.lms-product-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.lms-product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.product-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--bg-secondary, #f9fafb);
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.product-type-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.product-type-badge.type-course {
    background: #dbeafe;
    color: #1d4ed8;
}

.product-type-badge.type-tools {
    background: #d1fae5;
    color: #059669;
}

.product-type-badge.type-masterclass {
    background: #fef3c7;
    color: #d97706;
}

.product-status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.product-status-badge.status-active {
    background: #d1fae5;
    color: #059669;
}

.product-status-badge.status-draft {
    background: #f3f4f6;
    color: #6b7280;
}

.product-status-badge.status-inactive {
    background: #fee2e2;
    color: #dc2626;
}

.product-card-body {
    padding: 1.5rem;
}

.product-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary, #111827);
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.product-description {
    color: var(--text-secondary, #6b7280);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.product-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: var(--text-secondary, #6b7280);
}

.product-price {
    margin-bottom: 1rem;
}

.price-amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary, #111827);
}

.commission-info {
    display: block;
    font-size: 0.75rem;
    color: var(--text-secondary, #6b7280);
    margin-top: 0.25rem;
}

.product-category {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: var(--category-color, #3b82f6);
    color: white;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    opacity: 0.9;
}

.product-card-footer {
    padding: 1rem 1.5rem;
    background: var(--bg-secondary, #f9fafb);
    border-top: 1px solid var(--border-color, #e5e7eb);
}

.product-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.modules-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.module-item {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.2s ease;
}

.module-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.module-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.module-info {
    display: flex;
    gap: 1rem;
    flex: 1;
}

.module-number {
    width: 2rem;
    height: 2rem;
    background: var(--primary-color, #3b82f6);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.module-details {
    flex: 1;
}

.module-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary, #111827);
    margin-bottom: 0.5rem;
}

.module-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.module-meta > span {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
}

.content-type {
    background: var(--bg-secondary, #f3f4f6);
    color: var(--text-secondary, #6b7280);
}

.duration {
    background: #dbeafe;
    color: #1d4ed8;
}

.status {
    background: #d1fae5;
    color: #059669;
}

.preview-badge {
    background: #fef3c7;
    color: #d97706;
}

.module-actions {
    display: flex;
    gap: 0.5rem;
}

.module-description {
    color: var(--text-secondary, #6b7280);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-top: 0.5rem;
    padding-left: 3rem;
}

/* Modal styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-content.modal-sm {
    max-width: 400px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary, #111827);
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: background-color 0.2s;
}

.modal-close:hover {
    background: var(--bg-secondary, #f3f4f6);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid var(--border-color, #e5e7eb);
    background: var(--bg-secondary, #f9fafb);
}

.delete-confirmation {
    text-align: center;
}

.delete-icon {
    color: #dc2626;
    margin-bottom: 1rem;
}

.delete-message {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary, #111827);
    margin-bottom: 0.5rem;
}

.delete-product-name {
    font-weight: 600;
    color: var(--primary-color, #3b82f6);
    margin-bottom: 1rem;
}

.delete-warning {
    font-size: 0.875rem;
    color: var(--text-secondary, #6b7280);
}

/* Responsive design */
@media (max-width: 768px) {
    .lms-products-grid {
        grid-template-columns: 1fr;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .product-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .module-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .module-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .modal-content {
        width: 95%;
        margin: 1rem;
    }
}
</style>