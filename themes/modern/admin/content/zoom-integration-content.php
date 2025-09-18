<?php
/**
 * EPIC Hub Admin - Zoom Integration Content
 * Konten halaman Zoom Integration untuk layout admin
 */

if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

// Extract data from layout
$categories = $categories ?? [];
$events = $events ?? [];
$total_pages = $total_pages ?? 1;
?>

<!-- Integration Navigation -->
<div class="settings-navigation">
    <nav class="settings-nav">
        <a href="<?= epic_url('admin/integrasi/autoresponder-email') ?>" class="settings-nav-item">
            <i data-feather="send" class="settings-nav-icon"></i>
            <span>Autoresponder Email</span>
        </a>
        <a href="<?= epic_url('admin/zoom-integration') ?>" class="settings-nav-item active">
            <i data-feather="video" class="settings-nav-icon"></i>
            <span>Zoom Integration</span>
        </a>
    </nav>
</div>

<!-- Success Message -->
<?php if (isset($_GET['success']) && $_GET['success'] === 'event_created'): ?>
<div class="alert alert-success">
    <i data-feather="check-circle" width="16" height="16"></i>
    Event berhasil dibuat! Event baru telah ditambahkan ke sistem.
</div>
<?php endif; ?>

<div class="zoom-admin-container">
    <!-- Tab Navigation -->
    <div class="zoom-tabs">
        <button class="zoom-tab active" data-tab="events">
            <i data-feather="calendar" width="16" height="16"></i>
            <span>Event Management</span>
        </button>
        <button class="zoom-tab" data-tab="categories">
            <i data-feather="folder" width="16" height="16"></i>
            <span>Categories</span>
        </button>
        <button class="zoom-tab" data-tab="settings">
            <i data-feather="settings" width="16" height="16"></i>
            <span>Zoom Settings</span>
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
                <a href="<?= epic_url('admin/zoom-add-event') ?>" class="btn btn-primary">
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
                                            <div class="event-datetime">
                                                <div class="event-date"><?= date('d M Y', strtotime($event['start_time'])) ?></div>
                                                <div class="event-time"><?= date('H:i', strtotime($event['start_time'])) ?> - <?= date('H:i', strtotime($event['end_time'])) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <?= epic_get_zoom_event_status_badge($event['status']) ?>
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
                                    <td colspan="6" class="text-center text-muted">
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

    <div class="tab-content" id="categories-tab">
        <!-- Categories Management -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">
                    <i data-feather="folder" class="settings-card-icon"></i>
                    Event Categories
                </h3>
                <button class="btn btn-primary" onclick="openCategoryModal()">
                    <i data-feather="plus" width="16" height="16"></i>
                    Add New Category
                </button>
            </div>
            
            <div class="settings-card-body">
                <div class="categories-grid">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <div class="category-card">
                                <div class="category-header" style="background-color: <?= htmlspecialchars($category['color']) ?>">
                                    <i data-feather="<?= htmlspecialchars($category['icon']) ?>" width="24" height="24"></i>
                                </div>
                                <div class="category-body">
                                    <h4 class="category-name"><?= htmlspecialchars($category['name']) ?></h4>
                                    <p class="category-description"><?= htmlspecialchars($category['description']) ?></p>
                                    <div class="category-access">
                                        <?php 
                                        $access_levels = json_decode($category['access_levels'], true);
                                        if (is_array($access_levels)):
                                        ?>
                                            <?php foreach ($access_levels as $level): ?>
                                                <span class="access-badge"><?= htmlspecialchars($level) ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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

    <div class="tab-content" id="settings-tab">
        <!-- Zoom Settings -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">
                    <i data-feather="settings" class="settings-card-icon"></i>
                    Zoom API Settings
                </h3>
            </div>
            
            <div class="settings-card-body">
                <form id="zoom-settings-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="zoom_api_key">
                                Zoom API Key
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="password" 
                                   id="zoom_api_key" 
                                   name="zoom_api_key" 
                                   class="form-input" 
                                   placeholder="Enter your Zoom API Key">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="zoom_api_secret">
                                Zoom API Secret
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="password" 
                                   id="zoom_api_secret" 
                                   name="zoom_api_secret" 
                                   class="form-input" 
                                   placeholder="Enter your Zoom API Secret">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="zoom_account_id">
                            Zoom Account ID
                            <span class="form-label-required">*</span>
                        </label>
                        <input type="text" 
                               id="zoom_account_id" 
                               name="zoom_account_id" 
                               class="form-input" 
                               placeholder="Enter your Zoom Account ID">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save" width="16" height="16"></i>
                            Save Settings
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="testZoomConnection()">
                            <i data-feather="wifi" width="16" height="16"></i>
                            Test Connection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Event Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Event</h3>
            <button class="modal-close" onclick="closeEventModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="event-form">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="event_title">
                            Event Title
                            <span class="form-label-required">*</span>
                        </label>
                        <input type="text" 
                               id="event_title" 
                               name="title" 
                               class="form-input" 
                               placeholder="Enter event title" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="event_category">
                            Category
                            <span class="form-label-required">*</span>
                        </label>
                        <select id="event_category" name="category_id" class="form-input" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="event_description">
                        Description
                    </label>
                    <textarea id="event_description" 
                              name="description" 
                              class="form-textarea" 
                              rows="3" 
                              placeholder="Enter event description"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="event_start_time">
                            Start Time
                            <span class="form-label-required">*</span>
                        </label>
                        <input type="datetime-local" 
                               id="event_start_time" 
                               name="start_time" 
                               class="form-input" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="event_end_time">
                            End Time
                            <span class="form-label-required">*</span>
                        </label>
                        <input type="datetime-local" 
                               id="event_end_time" 
                               name="end_time" 
                               class="form-input" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="event_max_participants">
                            Max Participants
                        </label>
                        <input type="number" 
                               id="event_max_participants" 
                               name="max_participants" 
                               class="form-input" 
                               placeholder="Leave empty for unlimited">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="event_status">
                            Status
                        </label>
                        <select id="event_status" name="status" class="form-input">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-checkbox-group">
                    <input type="checkbox" id="registration_required" name="registration_required" value="1">
                    <label for="registration_required">Registration Required</label>
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
            <button class="modal-close" onclick="closeCategoryModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="category-form">
                <div class="form-group">
                    <label class="form-label" for="category_name">
                        Category Name
                        <span class="form-label-required">*</span>
                    </label>
                    <input type="text" 
                           id="category_name" 
                           name="name" 
                           class="form-input" 
                           placeholder="Enter category name" 
                           required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="category_description">
                        Description
                    </label>
                    <textarea id="category_description" 
                              name="description" 
                              class="form-textarea" 
                              rows="3" 
                              placeholder="Enter category description"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="category_color">
                            Color
                        </label>
                        <input type="color" 
                               id="category_color" 
                               name="color" 
                               class="form-input" 
                               value="#3B82F6">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="category_icon">
                            Icon
                        </label>
                        <select id="category_icon" name="icon" class="form-input">
                            <option value="folder">Folder</option>
                            <option value="calendar">Calendar</option>
                            <option value="video">Video</option>
                            <option value="users">Users</option>
                            <option value="star">Star</option>
                            <option value="heart">Heart</option>
                            <option value="bookmark">Bookmark</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Access Levels</label>
                    <div class="checkbox-group">
                        <div class="form-checkbox-group">
                            <input type="checkbox" id="access_free" name="access_levels[]" value="free">
                            <label for="access_free">Free Members</label>
                        </div>
                        <div class="form-checkbox-group">
                            <input type="checkbox" id="access_epic" name="access_levels[]" value="epic">
                            <label for="access_epic">EPIC Members</label>
                        </div>
                        <div class="form-checkbox-group">
                            <input type="checkbox" id="access_epis" name="access_levels[]" value="epis">
                            <label for="access_epis">EPIS Members</label>
                        </div>
                        <div class="form-checkbox-group">
                            <input type="checkbox" id="access_admin" name="access_levels[]" value="admin">
                            <label for="access_admin">Admin</label>
                        </div>
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
/* Settings navigation styles */
.settings-navigation {
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-2xl);
    padding: var(--spacing-4);
    margin-bottom: var(--spacing-6);
}

.settings-nav {
    display: flex;
    gap: var(--spacing-2);
    flex-wrap: wrap;
}

.settings-nav-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-3) var(--spacing-4);
    border-radius: var(--radius-lg);
    text-decoration: none;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    transition: all var(--transition-normal);
    border: 1px solid transparent;
}

.settings-nav-item:hover {
    color: var(--ink-100);
    background: var(--surface-3);
    border-color: var(--ink-600);
}

.settings-nav-item.active {
    color: var(--ink-100);
    background: linear-gradient(
        45deg,
        #ffd700 0%,
        #ffed4e 20%,
        #fff9c4 40%,
        #ffed4e 60%,
        #ffd700 80%,
        #b8860b 100%
    );
    background-size: 300% 300%;
    animation: shimmer-glow 3s ease-in-out infinite;
    color: #1a1a1a;
    border: 2px solid #ffd700;
    border-radius: var(--radius-lg);
    box-shadow: 
        0 4px 15px rgba(255, 215, 0, 0.4),
        0 0 25px rgba(255, 215, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    font-weight: 700;
    position: relative;
    overflow: hidden;
    text-shadow: 
        0 1px 2px rgba(0, 0, 0, 0.3),
        0 0 5px rgba(255, 255, 255, 0.5);
}

.settings-nav-item.active::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        45deg,
        transparent 0%,
        rgba(255, 255, 255, 0.8) 30%,
        rgba(255, 255, 255, 0.9) 50%,
        rgba(255, 255, 255, 0.8) 70%,
        transparent 100%
    );
    animation: shimmer-sweep 3s ease-in-out infinite;
}

.settings-nav-item.active .settings-nav-icon {
    color: #1a1a1a;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3)) drop-shadow(0 0 5px rgba(255, 255, 255, 0.5));
}

.settings-nav-icon {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
    opacity: 0.7;
}

@keyframes shimmer-glow {
    0% {
        background-position: 0% 50%;
    }
    25% {
        background-position: 100% 50%;
    }
    50% {
        background-position: 100% 100%;
    }
    75% {
        background-position: 0% 100%;
    }
    100% {
        background-position: 0% 50%;
    }
}

@keyframes shimmer-sweep {
    0% {
        left: -100%;
        opacity: 0;
    }
    20% {
        opacity: 1;
    }
    80% {
        opacity: 1;
    }
    100% {
        left: 100%;
        opacity: 0;
    }
}

/* Zoom Integration Styles */
.zoom-admin-container {
    max-width: 100%;
    margin: var(--spacing-6) 0 0 0;
    padding: 0;
}

.zoom-tabs {
    display: flex;
    background: var(--surface-2);
    border-radius: var(--radius-lg);
    padding: var(--spacing-1);
    margin-bottom: var(--spacing-6);
    border: 1px solid var(--ink-600);
}

.zoom-tab {
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

.zoom-tab:hover:not(.active) {
    background: var(--surface-3);
    color: var(--ink-200);
}

.zoom-tab.active {
    background: var(--gold-400);
    color: var(--ink-900);
    box-shadow: var(--shadow-sm);
    font-weight: var(--font-weight-semibold);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Settings Card Styles */
.settings-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-xl);
    margin-bottom: var(--spacing-6);
    overflow: hidden;
}

.settings-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-600);
    background: var(--surface-3);
}

.settings-card-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0;
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.settings-card-icon {
    color: var(--gold-400);
}

.settings-card-body {
    padding: var(--spacing-6);
}

/* Table Styles */
.table-container {
    overflow-x: auto;
    border-radius: var(--radius-lg);
    border: 1px solid var(--ink-600);
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.table th {
    background: var(--surface-3);
    color: var(--ink-200);
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-sm);
    padding: var(--spacing-4);
    text-align: left;
    border-bottom: 1px solid var(--ink-600);
}

.table td {
    padding: var(--spacing-4);
    border-bottom: 1px solid var(--ink-700);
    color: var(--ink-200);
    font-size: var(--font-size-sm);
}

.table tbody tr:hover {
    background: var(--surface-3);
}

.table tbody tr:last-child td {
    border-bottom: none;
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

.event-description {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
    line-height: 1.4;
    max-width: 200px;
    word-wrap: break-word;
}

.event-datetime {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-1);
}

.event-date {
    font-weight: var(--font-weight-medium);
    color: var(--ink-200);
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
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    color: white;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-4);
}

.category-card {
    background: var(--surface-3);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--transition-fast);
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
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
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-2) 0;
}

.category-description {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
    margin: 0 0 var(--spacing-3) 0;
}

.category-access {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-1);
}

.access-badge {
    display: inline-block;
    padding: var(--spacing-1) var(--spacing-2);
    background: var(--gold-400);
    color: var(--ink-900);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

.category-actions {
    padding: var(--spacing-3) var(--spacing-4);
    border-top: 1px solid var(--ink-600);
    display: flex;
    gap: var(--spacing-2);
    justify-content: flex-end;
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

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
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
    color: var(--ink-300);
    cursor: pointer;
    padding: var(--spacing-2);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.modal-close:hover {
    background: var(--surface-3);
    color: var(--ink-100);
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

/* Form Styles */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-4);
}

.form-group {
    margin-bottom: var(--spacing-4);
}

.form-label {
    display: block;
    margin-bottom: var(--spacing-2);
    color: var(--ink-200);
    font-weight: var(--font-weight-medium);
    font-size: var(--font-size-sm);
}

.form-label-required {
    color: var(--danger);
}

.form-input,
.form-textarea {
    width: 100%;
    padding: var(--spacing-3);
    background: var(--surface-3);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    color: var(--ink-100);
    font-size: var(--font-size-sm);
    transition: all var(--transition-fast);
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--gold-400);
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-checkbox-group {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-3);
}

.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-3);
    margin-top: var(--spacing-6);
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-3) var(--spacing-4);
    border: none;
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    cursor: pointer;
    transition: all var(--transition-fast);
    text-decoration: none;
}

.btn-primary {
    background: var(--gold-400);
    color: var(--ink-900);
}

.btn-primary:hover {
    background: var(--gold-300);
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--surface-3);
    color: var(--ink-200);
    border: 1px solid var(--ink-600);
}

.btn-secondary:hover {
    background: var(--surface-4);
    color: var(--ink-100);
}

.btn-danger {
    background: var(--danger);
    color: white;
}

.btn-danger:hover {
    background: var(--danger-dark);
}

.btn-sm {
    padding: var(--spacing-2) var(--spacing-3);
    font-size: var(--font-size-xs);
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
}

.status-draft {
    background: rgba(156, 163, 175, 0.2);
    color: var(--ink-300);
}

.status-published {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success-light);
}

.status-ongoing {
    background: rgba(59, 130, 246, 0.2);
    color: var(--primary-light);
}

.status-completed {
    background: rgba(139, 92, 246, 0.2);
    color: var(--purple-light);
}

.status-cancelled {
    background: rgba(239, 68, 68, 0.2);
    color: var(--danger-light);
}

/* Responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .zoom-tabs {
        flex-direction: column;
    }
    
    .modal-content {
        width: 95%;
        margin: var(--spacing-4);
    }
}
</style>

<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.zoom-tab');
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
    
    console.log('Submitting event data:', Object.fromEntries(formData));
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data);
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
            closeCategoryModal();
            location.reload();
        } else {
            const actionText = action === 'update_category' ? 'update' : 'create';
            alert('Error: ' + (data.message || `Failed to ${actionText} category`));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const actionText = action === 'update_category' ? 'updating' : 'creating';
        alert(`An error occurred while ${actionText} the category`);
    });
});

// Zoom settings form submission
document.getElementById('zoom-settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'save_zoom_settings');
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Settings saved successfully!');
        } else {
            alert('Error: ' + (data.message || 'Failed to save settings'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving settings');
    });
});

// Edit functions
function editEvent(id) {
    // Implementation for editing events
    console.log('Edit event:', id);
}

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
            document.getElementById('category_icon').value = category.icon || 'folder';
            
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
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete category'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the category');
        });
    }
}

function testZoomConnection() {
    alert('Testing Zoom connection... (Feature coming soon)');
}

// Close modal when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

// Initialize feather icons
if (window.feather) {
    feather.replace();
}
</script>