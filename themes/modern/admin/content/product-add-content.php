<?php
/**
 * Add Product Content - New System
 * Content untuk halaman tambah produk dengan pilihan kategori
 */

// Variables sudah tersedia dari parent scope
?>

<!-- Alerts -->
<?php if ($success): ?>
    <div class="alert alert-success">
        <i data-feather="check-circle" width="16" height="16"></i>
        <?= htmlspecialchars($success) ?>
        <div class="alert-subtitle">Redirecting to product list...</div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i data-feather="x-circle" width="16" height="16"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- Add Product Form -->
<div class="add-product-container">
    <form method="POST" enctype="multipart/form-data" class="add-product-form" id="productForm">
        <!-- Category Selection -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i data-feather="tag" width="20" height="20"></i>
                Product Category
            </h3>
            <p class="form-section-description">
                Choose the type of product you want to create. This will determine the available fields and features.
            </p>
            
            <div class="category-selection">
                <div class="category-option">
                    <input type="radio" id="category_digital" name="category" value="digital" 
                           <?= ($form_data['category'] ?? '') === 'digital' ? 'checked' : '' ?>
                           class="category-radio">
                    <label for="category_digital" class="category-card">
                        <div class="category-icon">
                            <i data-feather="monitor" width="32" height="32"></i>
                        </div>
                        <div class="category-content">
                            <h4 class="category-title">Digital Product</h4>
                            <p class="category-description">
                                Downloadable digital products like ebooks, software, templates, courses, etc.
                            </p>
                            <div class="category-features">
                                <span class="feature-tag">File Downloads</span>
                                <span class="feature-tag">Access Control</span>
                                <span class="feature-tag">Digital Delivery</span>
                            </div>
                        </div>
                    </label>
                </div>
                
                <div class="category-option">
                    <input type="radio" id="category_lms" name="category" value="lms" 
                           <?= ($form_data['category'] ?? '') === 'lms' ? 'checked' : '' ?>
                           class="category-radio">
                    <label for="category_lms" class="category-card">
                        <div class="category-icon">
                            <i data-feather="book-open" width="32" height="32"></i>
                        </div>
                        <div class="category-content">
                            <h4 class="category-title">LMS (Learning Management System)</h4>
                            <p class="category-description">
                                Structured learning courses with modules, quizzes, progress tracking, and certificates.
                            </p>
                            <div class="category-features">
                                <span class="feature-tag">Course Modules</span>
                                <span class="feature-tag">Progress Tracking</span>
                                <span class="feature-tag">Certificates</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Basic Information -->
        <div class="form-section" id="basicInfoSection" style="display: none;">
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
                    <input type="file" id="image" name="image" class="form-file" accept="image/*">
                    <div class="form-help">Upload a square image (recommended: 400x400px, max 2MB)</div>
                </div>
                
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" <?= ($form_data['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($form_data['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <div class="form-help">Set the initial status of the product</div>
                </div>
            </div>
        </div>
        
        <!-- Digital Product Specific Fields -->
        <div class="form-section" id="digitalSection" style="display: none;">
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
        
        <!-- LMS Specific Fields -->
        <div class="form-section" id="lmsSection" style="display: none;">
            <h3 class="form-section-title">
                <i data-feather="book-open" width="20" height="20"></i>
                LMS Course Configuration
            </h3>
            <p class="form-section-description">
                Configure your Learning Management System course with advanced features including modules, progress tracking, and certificates.
            </p>
            
            <!-- Course Basic Information -->
            <div class="form-grid">
                <div class="form-group">
                    <label for="lms_type" class="form-label required">Course Type</label>
                    <select id="lms_type" name="lms_type" class="form-select" required>
                        <option value="">Select Course Type</option>
                        <option value="course" <?= ($form_data['lms_type'] ?? '') === 'course' ? 'selected' : '' ?>>Standard Course</option>
                        <option value="masterclass" <?= ($form_data['lms_type'] ?? '') === 'masterclass' ? 'selected' : '' ?>>Masterclass</option>
                        <option value="workshop" <?= ($form_data['lms_type'] ?? '') === 'workshop' ? 'selected' : '' ?>>Workshop</option>
                        <option value="certification" <?= ($form_data['lms_type'] ?? '') === 'certification' ? 'selected' : '' ?>>Certification Program</option>
                    </select>
                    <div class="form-help">Choose the type of learning experience</div>
                </div>
                
                <div class="form-group">
                    <label for="difficulty_level" class="form-label required">Difficulty Level</label>
                    <select id="difficulty_level" name="difficulty_level" class="form-select" required>
                        <option value="">Select Difficulty</option>
                        <option value="beginner" <?= ($form_data['difficulty_level'] ?? '') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= ($form_data['difficulty_level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= ($form_data['difficulty_level'] ?? '') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                        <option value="expert" <?= ($form_data['difficulty_level'] ?? '') === 'expert' ? 'selected' : '' ?>>Expert</option>
                    </select>
                    <div class="form-help">Target skill level for learners</div>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="duration" class="form-label">Course Duration</label>
                    <input type="text" id="duration" name="duration" class="form-input" 
                           value="<?= htmlspecialchars($form_data['duration'] ?? '') ?>"
                           placeholder="e.g., 8 hours, 4 weeks">
                    <div class="form-help">Total estimated completion time</div>
                </div>
                
                <div class="form-group">
                    <label for="estimated_hours" class="form-label">Estimated Hours</label>
                    <input type="number" id="estimated_hours" name="estimated_hours" class="form-input" 
                           value="<?= htmlspecialchars($form_data['estimated_hours'] ?? '') ?>"
                           placeholder="8" min="0" step="0.5">
                    <div class="form-help">Total learning hours (numeric)</div>
                </div>
            </div>
            
            <!-- Course Structure -->
            <div class="form-group">
                <label for="total_modules" class="form-label">Number of Modules</label>
                <input type="number" id="total_modules" name="total_modules" class="form-input" 
                       value="<?= htmlspecialchars($form_data['total_modules'] ?? '5') ?>"
                       placeholder="5" min="1" max="50">
                <div class="form-help">How many learning modules will this course have?</div>
            </div>
            
            <!-- Learning Objectives -->
            <div class="form-group">
                <label for="learning_objectives" class="form-label">Learning Objectives</label>
                <textarea id="learning_objectives" name="learning_objectives" class="form-textarea" rows="4"
                          placeholder="Enter learning objectives, one per line"><?= htmlspecialchars($form_data['learning_objectives'] ?? '') ?></textarea>
                <div class="form-help">What will students learn? (One objective per line)</div>
            </div>
            
            <!-- Access Control -->
            <div class="form-group">
                <label class="form-label">Access Levels</label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="access_free" value="1" <?= !empty($form_data['access_free']) ? 'checked' : '' ?>>
                        <span class="checkbox-text">Free Members</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="access_epic" value="1" <?= !empty($form_data['access_epic']) ? 'checked' : 'checked' ?>>
                        <span class="checkbox-text">EPIC Members</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="access_epis" value="1" <?= !empty($form_data['access_epis']) ? 'checked' : 'checked' ?>>
                        <span class="checkbox-text">EPIS Members</span>
                    </label>
                </div>
                <div class="form-help">Select which member levels can access this course</div>
            </div>
            
            <!-- Course Features -->
            <div class="form-group">
                <label class="form-label">Course Features</label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="certificate_enabled" value="1" <?= !empty($form_data['certificate_enabled']) ? 'checked' : 'checked' ?>>
                        <span class="checkbox-text">Enable Certificates</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="progress_tracking" value="1" <?= !empty($form_data['progress_tracking']) ? 'checked' : 'checked' ?>>
                        <span class="checkbox-text">Progress Tracking</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="quiz_enabled" value="1" <?= !empty($form_data['quiz_enabled']) ? 'checked' : '' ?>>
                        <span class="checkbox-text">Include Quizzes</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="discussion_enabled" value="1" <?= !empty($form_data['discussion_enabled']) ? 'checked' : '' ?>>
                        <span class="checkbox-text">Discussion Forum</span>
                    </label>
                </div>
                <div class="form-help">Select additional features for this course</div>
            </div>
            
            <!-- Instructor Information -->
            <div class="form-grid">
                <div class="form-group">
                    <label for="instructor_id" class="form-label">Instructor</label>
                    <select id="instructor_id" name="instructor_id" class="form-select">
                        <option value="">Select Instructor</option>
                        <?php
                        // Get available instructors
                        try {
                            $instructors = db()->select("SELECT id, name FROM epic_users WHERE role IN ('admin', 'super_admin') ORDER BY name") ?: [];
                            foreach ($instructors as $instructor) {
                                $selected = ($form_data['instructor_id'] ?? '') == $instructor['id'] ? 'selected' : '';
                                echo "<option value='{$instructor['id']}' {$selected}>" . htmlspecialchars($instructor['name']) . "</option>";
                            }
                        } catch (Exception $e) {
                            echo "<option value=''>No instructors available</option>";
                        }
                        ?>
                    </select>
                    <div class="form-help">Assign an instructor to this course</div>
                </div>
                
                <div class="form-group">
                    <label for="category_id" class="form-label">Course Category</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">Select Category</option>
                        <?php
                        // Get available categories
                        try {
                            $categories = db()->select("SELECT id, name FROM epic_product_categories WHERE status = 'active' ORDER BY sort_order, name") ?: [];
                            foreach ($categories as $category) {
                                $selected = ($form_data['category_id'] ?? '') == $category['id'] ? 'selected' : '';
                                echo "<option value='{$category['id']}' {$selected}>" . htmlspecialchars($category['name']) . "</option>";
                            }
                        } catch (Exception $e) {
                            echo "<option value=''>No categories available</option>";
                        }
                        ?>
                    </select>
                    <div class="form-help">Organize course by category</div>
                </div>
            </div>
            
            <!-- Commission Settings -->
            <div class="form-grid">
                <div class="form-group">
                    <label for="commission_type" class="form-label">Commission Type</label>
                    <select id="commission_type" name="commission_type" class="form-select">
                        <option value="percentage" <?= ($form_data['commission_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                        <option value="fixed" <?= ($form_data['commission_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed Amount</option>
                    </select>
                    <div class="form-help">How commission is calculated</div>
                </div>
                
                <div class="form-group">
                    <label for="commission_value" class="form-label">Commission Value</label>
                    <input type="number" id="commission_value" name="commission_value" class="form-input" 
                           value="<?= htmlspecialchars($form_data['commission_value'] ?? '10') ?>"
                           placeholder="10" min="0" step="0.01">
                    <div class="form-help">Commission percentage or fixed amount</div>
                </div>
            </div>
            
            <!-- LMS Integration Notice -->
            <div class="lms-integration-notice">
                <div class="notice-icon">
                    <i data-feather="info" width="20" height="20"></i>
                </div>
                <div class="notice-content">
                    <h4 class="notice-title">LMS Integration Active</h4>
                    <p class="notice-description">
                        This course will be created with full LMS capabilities including module management, progress tracking, and certificate generation. 
                        You can manage course modules after creating the product.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="form-actions" id="formActions" style="display: none;">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= epic_url('admin/manage/product') ?>'">
                <i data-feather="x" width="16" height="16"></i>
                Cancel
            </button>
            
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i data-feather="plus" width="16" height="16"></i>
                Create Product
            </button>
        </div>
    </form>
</div>

<style>
/* Add Product Styles */
.add-product-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: var(--spacing-6) var(--spacing-4);
}

.add-product-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.add-product-form {
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.form-section {
    padding: var(--spacing-8) var(--spacing-6);
    margin-bottom: var(--spacing-4);
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    transition: all var(--transition-normal);
}

.form-section:hover {
    border-color: var(--ink-600);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-section:last-child {
    margin-bottom: var(--spacing-6);
}

.form-section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-4);
    padding-bottom: var(--spacing-2);
    border-bottom: 2px solid var(--gold-400);
}

.form-section-description {
    color: var(--ink-400);
    font-size: var(--font-size-sm);
    line-height: 1.6;
    margin-bottom: var(--spacing-6);
    padding: var(--spacing-3) var(--spacing-4);
    background: var(--surface-2);
    border-radius: var(--radius-md);
    border-left: 3px solid var(--gold-400);
}

/* Category Selection */
.category-selection {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-5);
    margin-top: var(--spacing-2);
}

@media (max-width: 768px) {
    .category-selection {
        grid-template-columns: 1fr;
        gap: var(--spacing-4);
    }
}

.category-option {
    position: relative;
}

.category-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.category-card {
    display: block;
    padding: var(--spacing-6);
    border: 2px solid var(--ink-600);
    border-radius: var(--radius-lg);
    background: var(--surface-2);
    cursor: pointer;
    transition: all var(--transition-normal);
    height: 100%;
}

.category-card:hover {
    border-color: var(--gold-400);
    background: var(--surface-3);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.category-radio:checked + .category-card {
    border-color: var(--gold-400);
    background: var(--surface-3);
    box-shadow: 0 0 0 3px rgba(207, 168, 78, 0.1);
}

.category-icon {
    color: var(--gold-400);
    margin-bottom: var(--spacing-4);
}

.category-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-2);
}

.category-description {
    color: var(--ink-400);
    font-size: var(--font-size-sm);
    line-height: 1.5;
    margin-bottom: var(--spacing-4);
}

.category-features {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-2);
}

.feature-tag {
    padding: var(--spacing-1) var(--spacing-2);
    background: var(--gold-400);
    color: var(--ink-900);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    border-radius: var(--radius-sm);
}

/* Checkbox Groups */
.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    cursor: pointer;
    padding: var(--spacing-2);
    border-radius: var(--radius-sm);
    transition: background-color var(--transition-normal);
}

.checkbox-label:hover {
    background: var(--surface-3);
}

.checkbox-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--gold-400);
}

.checkbox-text {
    font-size: var(--font-size-sm);
    color: var(--ink-200);
    font-weight: var(--font-weight-medium);
}

/* LMS Integration Notice */
.lms-integration-notice {
    display: flex;
    gap: var(--spacing-3);
    padding: var(--spacing-4);
    background: linear-gradient(135deg, rgba(207, 168, 78, 0.1) 0%, rgba(207, 168, 78, 0.05) 100%);
    border: 1px solid rgba(207, 168, 78, 0.2);
    border-radius: var(--radius-lg);
    margin-top: var(--spacing-4);
}

.notice-icon {
    color: var(--gold-400);
    flex-shrink: 0;
}

.notice-content {
    flex: 1;
}

.notice-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--gold-400);
    margin-bottom: var(--spacing-1);
}

.notice-description {
    font-size: var(--font-size-xs);
    color: var(--ink-300);
    line-height: 1.4;
}

/* Form Elements */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-5);
    margin-bottom: var(--spacing-2);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-4);
    }
}

.form-group {
    margin-bottom: var(--spacing-5);
}

.form-group:last-child {
    margin-bottom: 0;
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
    padding: var(--spacing-8) var(--spacing-4);
    background: var(--surface-2);
    border: 2px dashed var(--ink-600);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-6);
}

.placeholder-icon {
    color: var(--ink-500);
    margin-bottom: var(--spacing-4);
}

.placeholder-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-300);
    margin-bottom: var(--spacing-3);
}

.placeholder-description {
    color: var(--ink-500);
    font-size: var(--font-size-sm);
    line-height: 1.6;
    max-width: 500px;
    margin: 0 auto;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-4);
    padding: var(--spacing-6) var(--spacing-8);
    margin-top: var(--spacing-6);
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
        padding: var(--spacing-5);
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
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

.alert-subtitle {
    font-size: var(--font-size-xs);
    opacity: 0.8;
    margin-top: var(--spacing-1);
}
</style>

<script>
// Form functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    const categoryRadios = document.querySelectorAll('.category-radio');
    const basicInfoSection = document.getElementById('basicInfoSection');
    const digitalSection = document.getElementById('digitalSection');
    const lmsSection = document.getElementById('lmsSection');
    const formActions = document.getElementById('formActions');
    
    // Handle category selection
    categoryRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                // Show basic info section
                basicInfoSection.style.display = 'block';
                formActions.style.display = 'flex';
                
                // Show/hide category-specific sections
                if (this.value === 'digital') {
                    digitalSection.style.display = 'block';
                    lmsSection.style.display = 'none';
                } else if (this.value === 'lms') {
                    digitalSection.style.display = 'none';
                    lmsSection.style.display = 'block';
                }
                
                // Smooth scroll to basic info
                basicInfoSection.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }
        });
    });
    
    // Check if a category is already selected (on form reload)
    const selectedCategory = document.querySelector('.category-radio:checked');
    if (selectedCategory) {
        selectedCategory.dispatchEvent(new Event('change'));
    }
    
    // Form validation
    const form = document.getElementById('productForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        const category = document.querySelector('.category-radio:checked');
        if (!category) {
            e.preventDefault();
            alert('Please select a product category.');
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-feather="loader" width="16" height="16" class="animate-spin"></i> Creating Product...';
        
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
                helpText.innerHTML = `Selected: ${fileName}`;
            }
        });
    }
});
</script>