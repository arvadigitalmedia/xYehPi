<?php
/**
 * EPIC Hub Admin - Add New Zoom Event Content
 * Content template untuk halaman add event standalone
 */

// Prevent direct access
if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

// Extract data from layout
extract($layout_data);
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

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <div class="page-header-left">
            <h1 class="page-title">
                <i data-feather="plus-circle" class="page-title-icon"></i>
                Add New Zoom Event
            </h1>
            <p class="page-description">Create a new Zoom event with detailed information and settings</p>
        </div>
        <div class="page-header-right">
            <a href="<?= epic_url('admin/zoom-integration') ?>" class="btn btn-secondary">
                <i data-feather="arrow-left" width="16" height="16"></i>
                Back to Events
            </a>
        </div>
    </div>
</div>

<!-- Error Alert -->
<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i data-feather="alert-circle" width="16" height="16"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Add Event Form -->
<div class="settings-card">
    <div class="settings-card-header">
        <h3 class="settings-card-title">
            <i data-feather="calendar" class="settings-card-icon"></i>
            Event Details
        </h3>
    </div>
    
    <div class="settings-card-body">
        <form method="POST" action="<?= epic_url('admin/zoom-add-event') ?>" class="event-form">
            <input type="hidden" name="action" value="create_event">
            
            <!-- Basic Information -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i data-feather="info" width="16" height="16"></i>
                    Basic Information
                </h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title" class="form-label required">Event Title</label>
                        <input type="text" id="title" name="title" class="form-control" required 
                               placeholder="Enter event title" maxlength="200">
                        <small class="form-help">A clear and descriptive title for your event</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" 
                                  placeholder="Describe your event in detail..."></textarea>
                        <small class="form-help">Provide detailed information about the event content and objectives</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id" class="form-label required">Category</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        data-color="<?= htmlspecialchars($category['color']) ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-help">Choose the most appropriate category for this event</small>
                    </div>
                </div>
            </div>
            
            <!-- Schedule Information -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i data-feather="clock" width="16" height="16"></i>
                    Schedule
                </h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time" class="form-label required">Start Date & Time</label>
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control" required>
                        <small class="form-help">When the event will begin</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time" class="form-label required">End Date & Time</label>
                        <input type="datetime-local" id="end_time" name="end_time" class="form-control" required>
                        <small class="form-help">When the event will end</small>
                    </div>
                </div>
            </div>
            
            <!-- Event Settings -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i data-feather="settings" width="16" height="16"></i>
                    Event Settings
                </h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="max_participants" class="form-label">Maximum Participants</label>
                        <input type="number" id="max_participants" name="max_participants" class="form-control" 
                               min="1" max="1000" placeholder="Leave empty for unlimited">
                        <small class="form-help">Maximum number of participants (leave empty for unlimited)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label required">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                        <small class="form-help">Event visibility status</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="form-checkbox">
                            <input type="checkbox" id="registration_required" name="registration_required" value="1">
                            <label for="registration_required" class="checkbox-label">
                                <span class="checkbox-indicator"></span>
                                <span class="checkbox-text">Require Registration</span>
                            </label>
                            <small class="form-help">Participants must register before joining the event</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save" width="16" height="16"></i>
                    Create Event
                </button>
                <a href="<?= epic_url('admin/zoom-integration') ?>" class="btn btn-secondary">
                    <i data-feather="x" width="16" height="16"></i>
                    Cancel
                </a>
            </div>
        </form>
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
        #ffd700 0%, #ffed4e 20%, #fff9c4 40%,
        #ffed4e 60%, #ffd700 80%, #b8860b 100%
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
    0% { background-position: 0% 50%; }
    25% { background-position: 100% 50%; }
    50% { background-position: 100% 100%; }
    75% { background-position: 0% 100%; }
    100% { background-position: 0% 50%; }
}

@keyframes shimmer-sweep {
    0% { left: -100%; opacity: 0; }
    20% { opacity: 1; }
    80% { opacity: 1; }
    100% { left: 100%; opacity: 0; }
}

/* Page Header */
.page-header {
    margin-bottom: var(--spacing-6);
}

.page-header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-4);
}

.page-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--ink-100);
    margin: 0;
}

.page-title-icon {
    width: 28px;
    height: 28px;
    color: var(--gold-400);
}

.page-description {
    color: var(--ink-400);
    margin: var(--spacing-2) 0 0 0;
    font-size: var(--font-size-sm);
}

/* Form Sections */
.form-section {
    margin-bottom: var(--spacing-8);
    padding-bottom: var(--spacing-6);
    border-bottom: 1px solid var(--ink-700);
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-200);
    margin-bottom: var(--spacing-4);
}

/* Form Layout */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-4);
}

.form-row:has(.form-group:only-child) {
    grid-template-columns: 1fr;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.form-label {
    font-weight: var(--font-weight-medium);
    color: var(--ink-200);
    font-size: var(--font-size-sm);
}

.form-label.required::after {
    content: ' *';
    color: var(--red-400);
}

.form-control {
    padding: var(--spacing-3);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    background: var(--surface-3);
    color: var(--ink-100);
    font-size: var(--font-size-sm);
    transition: all var(--transition-fast);
}

.form-control:focus {
    outline: none;
    border-color: var(--gold-400);
    box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
}

.form-control::placeholder {
    color: var(--ink-400);
}

.form-help {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
    margin-top: var(--spacing-1);
}

/* Checkbox Styling */
.form-checkbox {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    cursor: pointer;
    font-size: var(--font-size-sm);
    color: var(--ink-200);
}

.checkbox-indicator {
    width: 18px;
    height: 18px;
    border: 2px solid var(--ink-600);
    border-radius: var(--radius-sm);
    background: var(--surface-3);
    position: relative;
    transition: all var(--transition-fast);
}

input[type="checkbox"]:checked + .checkbox-label .checkbox-indicator {
    background: var(--gold-400);
    border-color: var(--gold-400);
}

input[type="checkbox"]:checked + .checkbox-label .checkbox-indicator::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: var(--ink-900);
    font-size: 12px;
    font-weight: bold;
}

input[type="checkbox"] {
    display: none;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: var(--spacing-3);
    padding-top: var(--spacing-6);
    border-top: 1px solid var(--ink-700);
    margin-top: var(--spacing-6);
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

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.event-form');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    
    // Set minimum date to today
    const now = new Date();
    const minDateTime = now.toISOString().slice(0, 16);
    startTimeInput.min = minDateTime;
    endTimeInput.min = minDateTime;
    
    // Validate end time is after start time
    function validateTimes() {
        const startTime = new Date(startTimeInput.value);
        const endTime = new Date(endTimeInput.value);
        
        if (startTime && endTime && endTime <= startTime) {
            endTimeInput.setCustomValidity('End time must be after start time');
        } else {
            endTimeInput.setCustomValidity('');
        }
    }
    
    startTimeInput.addEventListener('change', function() {
        // Set minimum end time to start time
        endTimeInput.min = this.value;
        validateTimes();
    });
    
    endTimeInput.addEventListener('change', validateTimes);
    
    // Form submission
    form.addEventListener('submit', function(e) {
        validateTimes();
        
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
    
    // Initialize feather icons
    if (window.feather) {
        feather.replace();
    }
});
</script>