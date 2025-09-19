<?php
/**
 * EPIC Hub Admin - Event Scheduling Content
 * Content template untuk halaman event scheduling management
 */

// Prevent direct access
if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

// Extract data from layout
extract($layout_data);
?>

<!-- Success Message -->
<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] === 'event_created'): ?>
    <div class="alert alert-success">
        <i data-feather="check-circle" width="16" height="16"></i>
        Event berhasil dibuat! Event baru telah dipublikasikan dan ditambahkan ke sistem scheduling.
    </div>
    <?php elseif ($_GET['success'] === 'event_draft_saved'): ?>
    <div class="alert alert-info">
        <i data-feather="file-text" width="16" height="16"></i>
        Draft event berhasil disimpan! Event tersimpan sebagai draft dan dapat dipublikasikan nanti.
    </div>
    <?php endif; ?>
<?php endif; ?>

<div class="event-scheduling-container">
    <!-- Tab Navigation -->
    <div class="event-tabs">
        <button class="event-tab active" data-tab="events">
            <i data-feather="calendar" width="16" height="16"></i>
            <span>Event Management</span>
        </button>
        <button class="event-tab" data-tab="categories">
            <i data-feather="folder" width="16" height="16"></i>
            <span>Categories</span>
        </button>
    </div>
    
    <!-- Tab Content Areas -->
    <div class="tab-content active" id="events-tab">
        <!-- Events Management -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">
                    <i data-feather="calendar" class="settings-card-icon"></i>
                    Event Management
                </h3>
                <a href="<?= epic_url('admin/event-scheduling-add') ?>" class="btn btn-primary">
                    <i data-feather="plus" width="16" height="16"></i>
                    Add New Event
                </a>
            </div>
            
            <div class="settings-card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Event Title</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Access Level</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($events)): ?>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td>
                                            <div class="event-info">
                                                <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
                                                <?php if ($event['location']): ?>
                                                    <div class="event-location">
                                                        <i data-feather="map-pin" width="12" height="12"></i>
                                                        <?= htmlspecialchars($event['location']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="event-description">
                                                <?= htmlspecialchars(substr($event['description'] ?? '', 0, 150)) ?><?= strlen($event['description'] ?? '') > 150 ? '...' : '' ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="category-badge" style="background-color: <?= htmlspecialchars($event['category_color'] ?? '#3B82F6') ?>">
                                                <i data-feather="<?= htmlspecialchars($event['category_icon'] ?? 'folder') ?>" width="12" height="12"></i>
                                                <?= htmlspecialchars($event['category_name'] ?? 'Uncategorized') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="access-levels">
                                                <?php 
                                                $access_levels = json_decode($event['access_levels'] ?? '[]', true);
                                                foreach ($access_levels as $level): 
                                                ?>
                                                    <span class="access-badge access-<?= $level ?>"><?= strtoupper($level) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="event-datetime">
                                                <div class="event-date"><?= date('d M Y', strtotime($event['start_time'])) ?></div>
                                                <div class="event-time"><?= date('H:i', strtotime($event['start_time'])) ?> - <?= date('H:i', strtotime($event['end_time'])) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <?= epic_get_event_schedule_status_badge($event['status']) ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-secondary" onclick="editEvent(<?= $event['id'] ?>)">
                                                    <i data-feather="edit" width="14" height="14"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteEvent(<?= $event['id'] ?>)">
                                                    <i data-feather="trash-2" width="14" height="14"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <div class="empty-state">
                                            <i data-feather="calendar" width="48" height="48"></i>
                                            <p>No events found. Create your first event to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Categories Tab -->
    <div class="tab-content" id="categories-tab">
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">
                    <i data-feather="folder" class="settings-card-icon"></i>
                    Event Categories
                </h3>
                <button class="btn btn-primary" onclick="openCategoryModal()">
                    <i data-feather="plus" width="16" height="16"></i>
                    Add Category
                </button>
            </div>
            
            <div class="settings-card-body">
                <div class="categories-grid">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <div class="category-card">
                                <div class="category-header" style="background-color: <?= htmlspecialchars($category['color']) ?>">
                                    <i data-feather="<?= htmlspecialchars($category['icon']) ?>" width="20" height="20"></i>
                                </div>
                                <div class="category-body">
                                    <h4 class="category-name"><?= htmlspecialchars($category['name']) ?></h4>
                                    <p class="category-description"><?= htmlspecialchars($category['description']) ?></p>
                                    <div class="category-access">
                                        <?php 
                                        $access_levels = json_decode($category['access_levels'], true);
                                        foreach ($access_levels as $level): 
                                        ?>
                                            <span class="access-badge access-<?= $level ?>"><?= strtoupper($level) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="category-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="editCategory(<?= $category['id'] ?>)">
                                        <i data-feather="edit" width="14" height="14"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?= $category['id'] ?>)">
                                        <i data-feather="trash-2" width="14" height="14"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i data-feather="folder" width="48" height="48"></i>
                            <p>No categories found. Create your first category to organize events.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Event</h3>
            <button type="button" class="modal-close" onclick="closeEventModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="event-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_title">Event Title *</label>
                        <input type="text" id="event_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="event_category">Category *</label>
                        <select id="event_category" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="event_description">Description</label>
                    <textarea id="event_description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="event_location">Location</label>
                    <input type="text" id="event_location" name="location" placeholder="e.g., Online via Zoom, Jakarta Convention Center">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_start_time">Start Time *</label>
                        <input type="datetime-local" id="event_start_time" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label for="event_end_time">End Time *</label>
                        <input type="datetime-local" id="event_end_time" name="end_time" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_max_participants">Max Participants</label>
                        <input type="number" id="event_max_participants" name="max_participants" min="1">
                    </div>
                    <div class="form-group">
                        <label for="event_status">Status</label>
                        <select id="event_status" name="status">
                            <option value="draft">Draft</option>
                            <option value="published" selected>Published</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Access Levels *</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_levels[]" value="free" checked>
                            <span>Free Account</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_levels[]" value="epic">
                            <span>EPIC Account</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_levels[]" value="epis">
                            <span>EPIS Account</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="event_url">Event URL</label>
                    <input type="url" id="event_url" name="event_url" placeholder="https://zoom.us/j/123456789">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="registration_required" name="registration_required" value="1">
                        <span>Require Registration</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="event_notes">Admin Notes</label>
                    <textarea id="event_notes" name="notes" rows="2" placeholder="Internal notes for admin use"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEventModal()">Cancel</button>
            <button type="submit" form="event-form" class="btn btn-primary">Save Event</button>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Category</h3>
            <button type="button" class="modal-close" onclick="closeCategoryModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="category-form">
                <div class="form-group">
                    <label for="category_name">Category Name *</label>
                    <input type="text" id="category_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="category_description">Description</label>
                    <textarea id="category_description" name="description" rows="2"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_color">Color</label>
                        <input type="color" id="category_color" name="color" value="#3B82F6">
                    </div>
                    <div class="form-group">
                        <label for="category_icon">Icon</label>
                        <select id="category_icon" name="icon">
                            <option value="calendar">Calendar</option>
                            <option value="monitor">Monitor</option>
                            <option value="book-open">Book Open</option>
                            <option value="users">Users</option>
                            <option value="tool">Tool</option>
                            <option value="mic">Microphone</option>
                            <option value="video">Video</option>
                            <option value="globe">Globe</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Access Levels *</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_levels[]" value="free" checked>
                            <span>Free Account</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_levels[]" value="epic">
                            <span>EPIC Account</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="access_levels[]" value="epis">
                            <span>EPIS Account</span>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Cancel</button>
            <button type="submit" form="category-form" class="btn btn-primary">Save Category</button>
        </div>
    </div>
</div>

<style>
/* Event Scheduling Styles */
.event-scheduling-container {
    max-width: 100%;
    margin: 0;
    padding: 0;
}

.event-tabs {
    display: flex;
    background: var(--surface-2);
    border-radius: var(--radius-lg);
    padding: var(--spacing-1);
    margin-bottom: var(--spacing-6);
    border: 1px solid var(--ink-600);
}

.event-tab {
    flex: 1;
    padding: var(--spacing-3) var(--spacing-4);
    background: transparent;
    border: none;
    color: var(--ink-300);
    font-weight: var(--font-weight-medium);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
}

.event-tab:hover:not(.active) {
    background: var(--surface-3);
    color: var(--ink-200);
}

.event-tab.active {
    background: var(--gold-400);
    color: var(--ink-900);
    box-shadow: var(--shadow-sm);
    font-weight: var(--font-weight-semibold);
}

/* Tab Content */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Event Info Styles */
.event-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-1);
}

.event-title {
    font-weight: var(--font-weight-medium);
    color: var(--ink-100);
}

.event-location {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
}

.event-description {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
    line-height: 1.4;
    max-width: 200px;
    word-wrap: break-word;
}

/* Access Level Badges */
.access-levels {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-1);
}

.access-badge {
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
}

.access-badge.access-free {
    background: rgba(34, 197, 94, 0.2);
    color: #86efac;
}

.access-badge.access-epic {
    background: rgba(59, 130, 246, 0.2);
    color: #93c5fd;
}

.access-badge.access-epis {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}

/* Category Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-4);
}

.category-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--transition-fast);
}

.category-card:hover {
    border-color: var(--ink-500);
    box-shadow: var(--shadow-md);
}

.category-header {
    padding: var(--spacing-4);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.category-body {
    padding: var(--spacing-4);
}

.category-name {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-2);
}

.category-description {
    font-size: var(--font-size-sm);
    color: var(--ink-400);
    margin-bottom: var(--spacing-3);
}

.category-access {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-1);
}

.category-actions {
    padding: var(--spacing-3) var(--spacing-4);
    border-top: 1px solid var(--ink-600);
    display: flex;
    gap: var(--spacing-2);
    justify-content: flex-end;
}

/* Form Styles */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-4);
}

.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--ink-200);
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
}

/* Event DateTime */
.event-datetime {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-1);
}

.event-date {
    font-weight: var(--font-weight-medium);
    color: var(--ink-100);
}

.event-time {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

/* Category Badge */
.category-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-1);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    color: white;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: var(--spacing-2);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--spacing-8);
    color: var(--ink-400);
}

.empty-state i {
    margin-bottom: var(--spacing-4);
    opacity: 0.5;
}

/* Alert Styles */
.alert {
    padding: var(--spacing-4);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-4);
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #86efac;
}

.alert-info {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #93c5fd;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: var(--surface-2);
    border-radius: var(--radius-xl);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    border: 1px solid var(--ink-600);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-600);
}

.modal-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: var(--ink-400);
    cursor: pointer;
    padding: var(--spacing-1);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.modal-close:hover {
    background: var(--surface-3);
    color: var(--ink-200);
}

.modal-body {
    padding: var(--spacing-6);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-3);
    padding: var(--spacing-6);
    border-top: 1px solid var(--ink-600);
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .event-tabs {
        flex-direction: column;
    }
}
</style>

<script>
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.event-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(tc => tc.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab + '-tab').classList.add('active');
        });
    });
});

// Modal functions
function openEventModal() {
    document.getElementById('eventModal').classList.add('active');
}

function closeEventModal() {
    document.getElementById('eventModal').classList.remove('active');
    document.getElementById('event-form').reset();
}

function openCategoryModal() {
    document.getElementById('categoryModal').classList.add('active');
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.remove('active');
    document.getElementById('category-form').reset();
    
    // Reset modal title
    document.querySelector('#categoryModal .modal-title').textContent = 'Add New Category';
    
    // Remove hidden ID field if exists
    const hiddenIdField = document.getElementById('edit_category_id');
    if (hiddenIdField) {
        hiddenIdField.remove();
    }
    
    // Reset form action
    document.getElementById('category-form').dataset.action = 'create_category';
}

// Event form submission
document.getElementById('event-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'create_event');
    
    // Ensure status is included
    if (!formData.get('status')) {
        formData.append('status', 'published');
    }
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEventModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to create event'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the event');
    });
});

// Category form submission
document.getElementById('category-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Determine action based on form state
    const action = this.dataset.action || 'create_category';
    formData.append('action', action);
    
    // Add category ID for update action
    if (action === 'update_category') {
        const categoryId = document.getElementById('edit_category_id');
        if (categoryId) {
            formData.append('id', categoryId.value);
        }
    }
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification(data.message || 'Operation completed successfully', 'success');
            closeCategoryModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            // Show error message
            showNotification(data.message || 'Operation failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const actionText = action === 'update_category' ? 'updating' : 'creating';
        showNotification(`An error occurred while ${actionText} the category`, 'error');
    });
});

// Edit functions
function editEvent(id) {
    // Redirect to add page with edit parameter
    window.location.href = `<?= epic_url('admin/event-scheduling-add') ?>?edit=${id}`;
}

function editCategory(id) {
    // Get category data and populate edit modal
    const formData = new FormData();
    formData.append('action', 'get_category');
    formData.append('id', id);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.category) {
            const category = data.category;
            
            // Update modal title
            document.querySelector('#categoryModal .modal-title').textContent = 'Edit Category';
            
            // Populate form fields
            document.getElementById('category_name').value = category.name || '';
            document.getElementById('category_description').value = category.description || '';
            document.getElementById('category_color').value = category.color || '#3B82F6';
            document.getElementById('category_icon').value = category.icon || 'calendar';
            
            // Set access levels
            const accessLevels = JSON.parse(category.access_levels || '[]');
            document.querySelectorAll('input[name="access_levels[]"]').forEach(checkbox => {
                checkbox.checked = accessLevels.includes(checkbox.value);
            });
            
            // Add hidden field for category ID
            let hiddenIdField = document.getElementById('edit_category_id');
            if (!hiddenIdField) {
                hiddenIdField = document.createElement('input');
                hiddenIdField.type = 'hidden';
                hiddenIdField.id = 'edit_category_id';
                hiddenIdField.name = 'category_id';
                document.getElementById('category-form').appendChild(hiddenIdField);
            }
            hiddenIdField.value = id;
            
            // Update form action for editing
            document.getElementById('category-form').dataset.action = 'update_category';
            
            // Open modal
            openCategoryModal();
        } else {
            alert('Error: Failed to load category data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading category data');
    });
}

// Delete functions
function deleteEvent(id) {
    if (confirm('Are you sure you want to delete this event?')) {
        const formData = new FormData();
        formData.append('action', 'delete_event');
        formData.append('id', id);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete event'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the event');
        });
    }
}

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        const formData = new FormData();
        formData.append('action', 'delete_category');
        formData.append('id', id);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Category deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message || 'Failed to delete category', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting the category', 'error');
        });
    }
}

// Close modal when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info'}" width="16" height="16"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i data-feather="x" width="14" height="14"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-width: 300px;
        max-width: 500px;
        font-size: 14px;
        font-weight: 500;
        animation: slideInRight 0.3s ease-out;
    `;
    
    // Set colors based on type
    if (type === 'success') {
        notification.style.background = 'rgba(34, 197, 94, 0.1)';
        notification.style.border = '1px solid rgba(34, 197, 94, 0.3)';
        notification.style.color = '#86efac';
    } else if (type === 'error') {
        notification.style.background = 'rgba(239, 68, 68, 0.1)';
        notification.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        notification.style.color = '#fca5a5';
    } else {
        notification.style.background = 'rgba(59, 130, 246, 0.1)';
        notification.style.border = '1px solid rgba(59, 130, 246, 0.3)';
        notification.style.color = '#93c5fd';
    }
    
    // Add to page
    document.body.appendChild(notification);
    
    // Replace feather icons
    if (window.feather) {
        feather.replace();
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    
    .notification-close:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.1);
    }
`;
document.head.appendChild(style);

// Initialize feather icons
if (window.feather) {
    feather.replace();
}
</script>