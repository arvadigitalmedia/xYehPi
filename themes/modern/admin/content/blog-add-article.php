<?php
/**
 * Add New Article Form
 * WordPress-style article creation with SEO optimization
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}
?>

<!-- Error/Success Messages -->
<?php if (isset($error) && $error): ?>
    <div class="alert alert-error alert-dismissible">
        <i data-feather="alert-circle" class="alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">Error!</div>
            <div class="alert-message"><?= htmlspecialchars($error) ?></div>
        </div>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
            <i data-feather="x" width="16" height="16"></i>
        </button>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="article-form" id="articleForm">
    <div class="form-layout">
        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Article Title -->
            <div class="form-section">
                <div class="form-group">
                    <label class="form-label required">Article Title</label>
                    <input type="text" name="title" id="articleTitle" class="form-input title-input" 
                           placeholder="Enter your article title..." 
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                           required>
                    <div class="form-help">This will be the main headline of your article</div>
                </div>
            </div>
            
            <!-- Article Content -->
            <div class="form-section">
                <div class="form-group">
                    <label class="form-label required">Article Content</label>
                    <div class="editor-toolbar">
                        <button type="button" class="editor-btn" onclick="formatText('bold')" title="Bold">
                            <i data-feather="bold" width="16" height="16"></i>
                        </button>
                        <button type="button" class="editor-btn" onclick="formatText('italic')" title="Italic">
                            <i data-feather="italic" width="16" height="16"></i>
                        </button>
                        <button type="button" class="editor-btn" onclick="formatText('underline')" title="Underline">
                            <i data-feather="underline" width="16" height="16"></i>
                        </button>
                        <div class="editor-separator"></div>
                        <button type="button" class="editor-btn" onclick="insertHeading('h2')" title="Heading 2">
                            H2
                        </button>
                        <button type="button" class="editor-btn" onclick="insertHeading('h3')" title="Heading 3">
                            H3
                        </button>
                        <div class="editor-separator"></div>
                        <button type="button" class="editor-btn" onclick="insertList('ul')" title="Bullet List">
                            <i data-feather="list" width="16" height="16"></i>
                        </button>
                        <button type="button" class="editor-btn" onclick="insertList('ol')" title="Numbered List">
                            <i data-feather="hash" width="16" height="16"></i>
                        </button>
                        <div class="editor-separator"></div>
                        <button type="button" class="editor-btn" onclick="insertLink()" title="Insert Link">
                            <i data-feather="link" width="16" height="16"></i>
                        </button>
                        <button type="button" class="editor-btn" onclick="insertImage()" title="Insert Image">
                            <i data-feather="image" width="16" height="16"></i>
                        </button>
                    </div>
                    <textarea name="content" id="articleContent" class="form-textarea content-editor" 
                              placeholder="Write your article content here..." 
                              rows="20" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    <div class="editor-stats">
                        <span id="wordCount">0 words</span>
                        <span id="readingTime">0 min read</span>
                    </div>
                </div>
            </div>
            
            <!-- Article Excerpt -->
            <div class="form-section">
                <div class="form-group">
                    <label class="form-label">Article Excerpt</label>
                    <textarea name="excerpt" id="articleExcerpt" class="form-textarea" 
                              placeholder="Brief summary of your article (optional)..." 
                              rows="3" maxlength="500"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                    <div class="form-help">
                        <span>Optional summary that will be displayed in article previews</span>
                        <span id="excerptCount" class="char-count">0/500</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="sidebar-content">
            <!-- Publish Settings -->
            <div class="sidebar-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i data-feather="send" class="section-icon"></i>
                        Publish Settings
                    </h3>
                </div>
                
                <div class="section-body">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="private" <?= ($_POST['status'] ?? '') === 'private' ? 'selected' : '' ?>>Private</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Visibility</label>
                        <select name="visibility" class="form-select">
                            <option value="public" <?= ($_POST['visibility'] ?? '') === 'public' ? 'selected' : '' ?>>Public</option>
                            <option value="members" <?= ($_POST['visibility'] ?? '') === 'members' ? 'selected' : '' ?>>Members Only</option>
                            <option value="premium" <?= ($_POST['visibility'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium Only</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Featured Image -->
            <div class="sidebar-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i data-feather="image" class="section-icon"></i>
                        Featured Image
                    </h3>
                </div>
                
                <div class="section-body">
                    <div class="image-upload-area" id="imageUploadArea">
                        <div class="upload-placeholder" id="uploadPlaceholder">
                            <i data-feather="upload" width="32" height="32"></i>
                            <p>Click to upload or drag image here</p>
                            <small>JPG, PNG, GIF, WebP (Max 5MB)</small>
                        </div>
                        <div class="image-preview" id="imagePreview" style="display: none;">
                            <img id="previewImage" src="" alt="Preview">
                            <div class="image-actions">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="changeImage()">
                                    <i data-feather="edit" width="14" height="14"></i>
                                    Change
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeImage()">
                                    <i data-feather="trash-2" width="14" height="14"></i>
                                    Remove
                                </button>
                            </div>
                        </div>
                        <input type="file" name="featured_image" id="featuredImage" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                               style="display: none;" onchange="previewFeaturedImage(this)">
                    </div>
                </div>
            </div>
            
            <!-- Social Media Optimization -->
            <div class="sidebar-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i data-feather="share-2" class="section-icon"></i>
                        Social Media
                    </h3>
                </div>
                
                <div class="section-body">
                    <div class="form-group">
                        <label class="form-label">Social Sharing Image URL</label>
                        <input type="url" name="social_image" id="socialImage" class="form-input" 
                               placeholder="https://example.com/image.jpg" 
                               value="<?= htmlspecialchars($_POST['social_image'] ?? '') ?>">
                        <div class="form-help">Image that will appear when shared on social media (1200x630px recommended)</div>
                    </div>
                    
                    <div class="social-preview" id="socialPreview">
                        <div class="preview-header">
                            <i data-feather="eye" width="16" height="16"></i>
                            <span>Social Media Preview</span>
                        </div>
                        <div class="preview-card">
                            <div class="preview-image" id="previewSocialImage">
                                <i data-feather="image" width="24" height="24"></i>
                            </div>
                            <div class="preview-content">
                                <div class="preview-title" id="previewTitle">Your Article Title</div>
                                <div class="preview-description" id="previewDescription">Article excerpt will appear here...</div>
                                <div class="preview-url">epicHub.com</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SEO Optimization -->
            <div class="sidebar-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i data-feather="search" class="section-icon"></i>
                        SEO Optimization
                    </h3>
                </div>
                
                <div class="section-body">
                    <div class="form-group">
                        <label class="form-label">SEO Title</label>
                        <input type="text" name="seo_title" id="seoTitle" class="form-input" 
                               placeholder="Custom SEO title (optional)" 
                               value="<?= htmlspecialchars($_POST['seo_title'] ?? '') ?>" 
                               maxlength="60">
                        <div class="form-help">
                            <span>Title that appears in search results</span>
                            <span id="seoTitleCount" class="char-count">0/60</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">SEO Description</label>
                        <textarea name="seo_description" id="seoDescription" class="form-textarea" 
                                  placeholder="Brief description for search engines..." 
                                  rows="3" maxlength="160"><?= htmlspecialchars($_POST['seo_description'] ?? '') ?></textarea>
                        <div class="form-help">
                            <span>Description that appears in search results</span>
                            <span id="seoDescCount" class="char-count">0/160</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">SEO Keywords</label>
                        <input type="text" name="seo_keywords" id="seoKeywords" class="form-input" 
                               placeholder="keyword1, keyword2, keyword3" 
                               value="<?= htmlspecialchars($_POST['seo_keywords'] ?? '') ?>">
                        <div class="form-help">Comma-separated keywords (5-10 recommended)</div>
                    </div>
                    
                    <!-- SEO Preview -->
                    <div class="seo-preview">
                        <div class="preview-header">
                            <i data-feather="search" width="16" height="16"></i>
                            <span>Search Engine Preview</span>
                        </div>
                        <div class="search-result-preview">
                            <div class="result-url">epicHub.com/blog/<span id="previewSlug">article-title</span></div>
                            <div class="result-title" id="resultTitle">Your Article Title</div>
                            <div class="result-description" id="resultDescription">Article description will appear here...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <div class="actions-left">
            <a href="<?= epic_url('admin/blog') ?>" class="btn btn-secondary">
                <i data-feather="arrow-left" width="16" height="16"></i>
                Back to Blog
            </a>
        </div>
        
        <div class="actions-right">
            <button type="submit" name="status" value="draft" class="btn btn-secondary">
                <i data-feather="save" width="16" height="16"></i>
                Save as Draft
            </button>
            <button type="submit" name="status" value="published" class="btn btn-primary">
                <i data-feather="send" width="16" height="16"></i>
                Publish Article
            </button>
        </div>
    </div>
</form>

<style>
/* Article Form Styles */
.article-form {
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

/* Form Sections */
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
    overflow: hidden;
}

.section-header {
    padding: var(--spacing-4) var(--spacing-5);
    background: var(--surface-2);
    border-bottom: 1px solid var(--ink-700);
}

.section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0;
}

.section-icon {
    color: var(--gold-400);
}

.section-body {
    padding: var(--spacing-5);
}

/* Title Input */
.title-input {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    padding: var(--spacing-4);
}

/* Editor Toolbar */
.editor-toolbar {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
    padding: var(--spacing-3);
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-bottom: none;
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    flex-wrap: wrap;
}

.editor-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: none;
    border: 1px solid transparent;
    border-radius: var(--radius-sm);
    color: var(--ink-300);
    cursor: pointer;
    transition: all var(--transition-fast);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-bold);
}

.editor-btn:hover {
    background: var(--surface-3);
    border-color: var(--ink-600);
    color: var(--ink-100);
}

.editor-btn:active {
    background: var(--surface-4);
}

.editor-separator {
    width: 1px;
    height: 20px;
    background: var(--ink-600);
    margin: 0 var(--spacing-2);
}

/* Content Editor */
.content-editor {
    border-radius: 0 0 var(--radius-md) var(--radius-md);
    border-top: none;
    min-height: 400px;
    font-family: var(--font-family);
    line-height: 1.6;
}

.editor-stats {
    display: flex;
    justify-content: space-between;
    margin-top: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--ink-400);
}

/* Character Counters */
.char-count {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
    margin-left: auto;
}

.form-help {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Image Upload */
.image-upload-area {
    border: 2px dashed var(--ink-600);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
    cursor: pointer;
    overflow: hidden;
}

.image-upload-area:hover {
    border-color: var(--gold-400);
    background: var(--surface-2);
}

.upload-placeholder {
    padding: var(--spacing-8);
    text-align: center;
    color: var(--ink-400);
}

.upload-placeholder i {
    color: var(--ink-500);
    margin-bottom: var(--spacing-3);
}

.upload-placeholder p {
    margin: 0 0 var(--spacing-1) 0;
    font-weight: var(--font-weight-medium);
}

.upload-placeholder small {
    font-size: var(--font-size-xs);
}

.image-preview {
    position: relative;
}

.image-preview img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

.image-actions {
    position: absolute;
    top: var(--spacing-2);
    right: var(--spacing-2);
    display: flex;
    gap: var(--spacing-2);
}

/* Social Preview */
.social-preview {
    margin-top: var(--spacing-4);
}

.preview-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-3);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-300);
}

.preview-card {
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    overflow: hidden;
    background: var(--surface-2);
}

.preview-image {
    height: 120px;
    background: var(--surface-3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-500);
}

.preview-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-content {
    padding: var(--spacing-3);
}

.preview-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-1);
    line-height: 1.3;
}

.preview-description {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
    margin-bottom: var(--spacing-2);
    line-height: 1.4;
}

.preview-url {
    font-size: var(--font-size-xs);
    color: var(--ink-500);
}

/* SEO Preview */
.seo-preview {
    margin-top: var(--spacing-4);
}

.search-result-preview {
    padding: var(--spacing-4);
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
}

.result-url {
    font-size: var(--font-size-xs);
    color: var(--success);
    margin-bottom: var(--spacing-1);
}

.result-title {
    font-size: var(--font-size-base);
    color: #1a73e8;
    margin-bottom: var(--spacing-1);
    cursor: pointer;
}

.result-title:hover {
    text-decoration: underline;
}

.result-description {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
    line-height: 1.4;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-6);
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-xl);
    position: sticky;
    bottom: var(--spacing-4);
    z-index: 10;
}

.actions-left,
.actions-right {
    display: flex;
    gap: var(--spacing-3);
}

/* Responsive */
@media (max-width: 1024px) {
    .form-layout {
        grid-template-columns: 1fr;
    }
    
    .sidebar-content {
        order: -1;
    }
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
        gap: var(--spacing-4);
    }
    
    .actions-left,
    .actions-right {
        width: 100%;
        justify-content: center;
    }
    
    .editor-toolbar {
        padding: var(--spacing-2);
    }
    
    .editor-btn {
        width: 28px;
        height: 28px;
    }
}
</style>

<script>
// Article form functionality
class ArticleEditor {
    constructor() {
        this.titleInput = document.getElementById('articleTitle');
        this.contentEditor = document.getElementById('articleContent');
        this.excerptInput = document.getElementById('articleExcerpt');
        this.seoTitle = document.getElementById('seoTitle');
        this.seoDescription = document.getElementById('seoDescription');
        this.socialImage = document.getElementById('socialImage');
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.updatePreviews();
        this.updateStats();
        this.setupImageUpload();
    }
    
    setupEventListeners() {
        // Update previews on input
        this.titleInput.addEventListener('input', () => this.updatePreviews());
        this.contentEditor.addEventListener('input', () => this.updateStats());
        this.excerptInput.addEventListener('input', () => this.updatePreviews());
        this.seoTitle.addEventListener('input', () => this.updatePreviews());
        this.seoDescription.addEventListener('input', () => this.updatePreviews());
        this.socialImage.addEventListener('input', () => this.updateSocialPreview());
        
        // Character counters
        this.excerptInput.addEventListener('input', () => this.updateCharCount('excerptCount', this.excerptInput, 500));
        this.seoTitle.addEventListener('input', () => this.updateCharCount('seoTitleCount', this.seoTitle, 60));
        this.seoDescription.addEventListener('input', () => this.updateCharCount('seoDescCount', this.seoDescription, 160));
    }
    
    updatePreviews() {
        const title = this.titleInput.value || 'Your Article Title';
        const excerpt = this.excerptInput.value || 'Article excerpt will appear here...';
        const seoTitle = this.seoTitle.value || title;
        const seoDesc = this.seoDescription.value || excerpt;
        
        // Update social preview
        document.getElementById('previewTitle').textContent = title;
        document.getElementById('previewDescription').textContent = excerpt;
        
        // Update SEO preview
        document.getElementById('resultTitle').textContent = seoTitle;
        document.getElementById('resultDescription').textContent = seoDesc;
        document.getElementById('previewSlug').textContent = this.generateSlug(title);
    }
    
    updateStats() {
        const content = this.contentEditor.value;
        const wordCount = content.trim() ? content.trim().split(/\s+/).length : 0;
        const readingTime = Math.max(1, Math.ceil(wordCount / 200));
        
        document.getElementById('wordCount').textContent = `${wordCount} words`;
        document.getElementById('readingTime').textContent = `${readingTime} min read`;
    }
    
    updateCharCount(elementId, input, maxLength) {
        const count = input.value.length;
        const element = document.getElementById(elementId);
        element.textContent = `${count}/${maxLength}`;
        element.style.color = count > maxLength ? 'var(--danger)' : 'var(--ink-400)';
    }
    
    updateSocialPreview() {
        const imageUrl = this.socialImage.value;
        const previewImage = document.getElementById('previewSocialImage');
        
        if (imageUrl) {
            previewImage.innerHTML = `<img src="${imageUrl}" alt="Social preview" onerror="this.parentElement.innerHTML='<i data-feather=\"image\" width=\"24\" height=\"24\"></i>'; feather.replace();">`;
        } else {
            previewImage.innerHTML = '<i data-feather="image" width="24" height="24"></i>';
            feather.replace();
        }
    }
    
    generateSlug(text) {
        return text.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-+|-+$/g, '') || 'article-title';
    }
    
    setupImageUpload() {
        const uploadArea = document.getElementById('imageUploadArea');
        const fileInput = document.getElementById('featuredImage');
        
        uploadArea.addEventListener('click', () => fileInput.click());
        
        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--gold-400)';
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = 'var(--ink-600)';
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--ink-600)';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                this.previewFeaturedImage(fileInput);
            }
        });
    }
    
    previewFeaturedImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert('Image size must be less than 5MB.');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('uploadPlaceholder').style.display = 'none';
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }
}

// Editor functions
function formatText(command) {
    document.execCommand(command, false, null);
    document.getElementById('articleContent').focus();
}

function insertHeading(tag) {
    const selection = window.getSelection();
    const text = selection.toString() || 'Heading text';
    const html = `<${tag}>${text}</${tag}>`;
    document.execCommand('insertHTML', false, html);
}

function insertList(type) {
    document.execCommand(type === 'ul' ? 'insertUnorderedList' : 'insertOrderedList', false, null);
}

function insertLink() {
    const url = prompt('Enter URL:');
    if (url) {
        document.execCommand('createLink', false, url);
    }
}

function insertImage() {
    const url = prompt('Enter image URL:');
    if (url) {
        document.execCommand('insertImage', false, url);
    }
}

function changeImage() {
    document.getElementById('featuredImage').click();
}

function removeImage() {
    document.getElementById('featuredImage').value = '';
    document.getElementById('uploadPlaceholder').style.display = 'block';
    document.getElementById('imagePreview').style.display = 'none';
}

function previewFeaturedImage(input) {
    window.articleEditor.previewFeaturedImage(input);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.articleEditor = new ArticleEditor();
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>