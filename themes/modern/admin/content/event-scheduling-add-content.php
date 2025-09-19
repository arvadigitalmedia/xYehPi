<?php
/**
 * EPIC Hub Admin - Add New Event Scheduling Content
 * Content template untuk halaman add event scheduling standalone
 */

// Prevent direct access
if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

// Extract data from layout
extract($layout_data);
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <div class="page-header-left">
            <h1 class="page-title">
                <i data-feather="<?= $edit_mode ? 'edit' : 'plus-circle' ?>" class="page-title-icon"></i>
                <?= $edit_mode ? 'Edit Event' : 'Add New Event' ?>
            </h1>
            <p class="page-description"><?= $edit_mode ? 'Update event information and scheduling' : 'Create a new event for member information and scheduling' ?></p>
        </div>
        <div class="page-header-right">
            <a href="<?= epic_url('admin/event-scheduling') ?>" class="btn btn-secondary">
                <i data-feather="arrow-left" width="16" height="16"></i>
                Back to Events
            </a>
        </div>
    </div>
</div>

<!-- Success Alert -->
<?php if (isset($success) && !empty($success)): ?>
<div class="alert alert-success">
    <i data-feather="check-circle" width="16" height="16"></i>
    <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<!-- Error Alert -->
<?php if (isset($error) && !empty($error)): ?>
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
        <form method="POST" action="<?= epic_url('admin/event-scheduling-add') ?>" class="event-form">
            <input type="hidden" name="action" value="create_event">
            <?php if ($edit_mode && $edit_event): ?>
                <input type="hidden" name="event_id" value="<?= $edit_event['id'] ?>">
            <?php endif; ?>
            
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
                               placeholder="Enter event title" maxlength="200"
                               value="<?= $edit_mode && $edit_event ? htmlspecialchars($edit_event['title']) : '' ?>">
                        <small class="form-help">A clear and descriptive title for your event</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" 
                                  placeholder="Describe your event in detail..."><?= $edit_mode && $edit_event ? htmlspecialchars($edit_event['description']) : '' ?></textarea>
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
                                        data-color="<?= htmlspecialchars($category['color']) ?>"
                                        <?= ($edit_mode && $edit_event && $edit_event['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-help">Choose the most appropriate category for this event</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" id="location" name="location" class="form-control" 
                               placeholder="e.g., Online via Zoom, Jakarta Convention Center"
                               value="<?= $edit_mode && $edit_event ? htmlspecialchars($edit_event['location']) : '' ?>">
                        <small class="form-help">Where the event will take place (online or physical location)</small>
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
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control" required
                               value="<?= $edit_mode && $edit_event && $edit_event['start_time'] ? date('Y-m-d\TH:i', strtotime($edit_event['start_time'])) : '' ?>">
                        <small class="form-help">When the event will begin</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time" class="form-label required">End Date & Time</label>
                        <input type="datetime-local" id="end_time" name="end_time" class="form-control" required
                               value="<?= $edit_mode && $edit_event && $edit_event['end_time'] ? date('Y-m-d\TH:i', strtotime($edit_event['end_time'])) : '' ?>">
                        <small class="form-help">When the event will end</small>
                    </div>
                </div>
            </div>
            
            <!-- Access Control -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i data-feather="users" width="16" height="16"></i>
                    Access Control
                </h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Access Levels</label>
                        <div class="access-level-grid">
                            <div class="access-level-card">
                                <div class="access-level-header">
                                    <input type="checkbox" id="access_free" name="access_levels[]" value="free" 
                                           <?php 
                                           if ($edit_mode && $edit_event) {
                                               $access_levels = json_decode($edit_event['access_levels'], true) ?: [];
                                               echo in_array('free', $access_levels) ? 'checked' : '';
                                           } else {
                                               echo 'checked';
                                           }
                                           ?>>
                                    <label for="access_free" class="access-level-label">
                                        <div class="access-level-icon free">
                                            <i data-feather="users" width="20" height="20"></i>
                                        </div>
                                        <div class="access-level-info">
                                            <h5>Free Account</h5>
                                            <p>Available for all free members</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="access-level-card">
                                <div class="access-level-header">
                                    <input type="checkbox" id="access_epic" name="access_levels[]" value="epic"
                                           <?php 
                                           if ($edit_mode && $edit_event) {
                                               $access_levels = json_decode($edit_event['access_levels'], true) ?: [];
                                               echo in_array('epic', $access_levels) ? 'checked' : '';
                                           }
                                           ?>>
                                    <label for="access_epic" class="access-level-label">
                                        <div class="access-level-icon epic">
                                            <i data-feather="star" width="20" height="20"></i>
                                        </div>
                                        <div class="access-level-info">
                                            <h5>EPIC Account</h5>
                                            <p>Exclusive for EPIC members</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="access-level-card">
                                <div class="access-level-header">
                                    <input type="checkbox" id="access_epis" name="access_levels[]" value="epis"
                                           <?php 
                                           if ($edit_mode && $edit_event) {
                                               $access_levels = json_decode($edit_event['access_levels'], true) ?: [];
                                               echo in_array('epis', $access_levels) ? 'checked' : '';
                                           }
                                           ?>>
                                    <label for="access_epis" class="access-level-label">
                                        <div class="access-level-icon epis">
                                            <i data-feather="crown" width="20" height="20"></i>
                                        </div>
                                        <div class="access-level-info">
                                            <h5>EPIS Account</h5>
                                            <p>Premium access for EPIS supervisors</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <small class="form-help">Select which member levels can view and access this event</small>
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
                               min="1" max="1000" placeholder="Leave empty for unlimited"
                               value="<?= $edit_mode && $edit_event && $edit_event['max_participants'] ? $edit_event['max_participants'] : '' ?>">
                        <small class="form-help">Maximum number of participants (leave empty for unlimited)</small>
                    </div>
                    
                    <!-- Status field removed - will be set by button action -->
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_url" class="form-label">Event URL</label>
                        <input type="url" id="event_url" name="event_url" class="form-control" 
                               placeholder="https://zoom.us/j/123456789 or meeting link"
                               value="<?= $edit_mode && $edit_event ? htmlspecialchars($edit_event['event_url']) : '' ?>">
                        <small class="form-help">Link to join the event (Zoom, Google Meet, etc.)</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="form-checkbox">
                            <input type="checkbox" id="registration_required" name="registration_required" value="1"
                                   <?= $edit_mode && $edit_event && $edit_event['registration_required'] ? 'checked' : '' ?>>
                            <label for="registration_required" class="checkbox-label">
                                <span class="checkbox-indicator"></span>
                                <span class="checkbox-text">Require Registration</span>
                            </label>
                            <small class="form-help">Participants must register before joining the event</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="notes" class="form-label">Admin Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3" 
                                  placeholder="Internal notes for admin use only..."><?= $edit_mode && $edit_event ? htmlspecialchars($edit_event['notes']) : '' ?></textarea>
                        <small class="form-help">Private notes visible only to administrators</small>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" id="save-draft-btn" class="btn btn-secondary">
                    <i data-feather="file-text" width="16" height="16"></i>
                    Simpan Draft
                </button>
                <button type="button" id="create-event-btn" class="btn btn-primary">
                    <i data-feather="<?= $edit_mode ? 'save' : 'check-circle' ?>" width="16" height="16"></i>
                    <?= $edit_mode ? 'Update Event' : 'Create Event' ?>
                </button>
                <a href="<?= epic_url('admin/event-scheduling') ?>" class="btn btn-outline">
                    <i data-feather="x" width="16" height="16"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
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

/* Access Level Grid */
.access-level-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-2);
}

.access-level-card {
    border: 2px solid var(--ink-600);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
    overflow: hidden;
}

.access-level-card:has(input:checked) {
    border-color: var(--gold-400);
    background: rgba(255, 215, 0, 0.05);
}

.access-level-header {
    position: relative;
}

.access-level-header input[type="checkbox"] {
    position: absolute;
    top: var(--spacing-3);
    right: var(--spacing-3);
    width: 20px;
    height: 20px;
    z-index: 2;
}

.access-level-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-4);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.access-level-label:hover {
    background: var(--surface-3);
}

.access-level-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.access-level-icon.free {
    background: linear-gradient(135deg, #10B981, #059669);
}

.access-level-icon.epic {
    background: linear-gradient(135deg, #3B82F6, #1D4ED8);
}

.access-level-icon.epis {
    background: linear-gradient(135deg, #F59E0B, #D97706);
}

.access-level-info h5 {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-1) 0;
}

.access-level-info p {
    font-size: var(--font-size-sm);
    color: var(--ink-400);
    margin: 0;
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

.btn-outline {
    background: transparent;
    color: var(--ink-300);
    border: 1px solid var(--ink-600);
    padding: var(--spacing-3) var(--spacing-4);
    border-radius: var(--radius-lg);
    font-weight: var(--font-weight-medium);
    font-size: var(--font-size-sm);
    cursor: pointer;
    transition: all var(--transition-fast);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
}

.btn-outline:hover {
    background: var(--surface-3);
    color: var(--ink-100);
    border-color: var(--ink-500);
    transform: translateY(-1px);
}

.btn-outline:active {
    transform: scale(0.99);
}

/* Loading Animation */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
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
    
    .access-level-grid {
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
    
    // Validate at least one access level is selected
    function validateAccessLevels() {
        const accessCheckboxes = document.querySelectorAll('input[name="access_levels[]"]');
        const isAnyChecked = Array.from(accessCheckboxes).some(cb => cb.checked);
        
        accessCheckboxes.forEach(cb => {
            if (!isAnyChecked) {
                cb.setCustomValidity('Please select at least one access level');
            } else {
                cb.setCustomValidity('');
            }
        });
    }
    
    // Add event listeners for access level checkboxes
    document.querySelectorAll('input[name="access_levels[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', validateAccessLevels);
    });
    
    // Button event handlers
    const saveDraftBtn = document.getElementById('save-draft-btn');
    const createEventBtn = document.getElementById('create-event-btn');
    
    // Save Draft button handler
    saveDraftBtn.addEventListener('click', function(e) {
        e.preventDefault();
        submitForm('save_draft');
    });
    
    // Create Event button handler
    createEventBtn.addEventListener('click', function(e) {
        e.preventDefault();
        submitForm('create_event');
    });
    
    // Form submission function
    function submitForm(action) {
        validateTimes();
        validateAccessLevels();
        
        // For draft, skip some validations
        if (action === 'save_draft') {
            // Remove required attributes temporarily for draft
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.removeAttribute('required');
            });
        }
        
        if (!form.checkValidity() && action !== 'save_draft') {
            form.classList.add('was-validated');
            return;
        }
        
        // Create hidden input for action
        let actionInput = document.getElementById('hidden-action');
        if (!actionInput) {
            actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.id = 'hidden-action';
            actionInput.name = 'action';
            form.appendChild(actionInput);
        }
        actionInput.value = action;
        
        // Show loading state
        const clickedBtn = action === 'save_draft' ? saveDraftBtn : createEventBtn;
        const originalText = clickedBtn.innerHTML;
        clickedBtn.disabled = true;
        clickedBtn.innerHTML = '<i data-feather="loader" width="16" height="16" class="animate-spin"></i> ' + (action === 'save_draft' ? 'Menyimpan...' : 'Membuat...');
        
        // Replace feather icons
        if (window.feather) {
            feather.replace();
        }
        
        // Submit form
        form.submit();
    }
    
    // Initialize feather icons
    if (window.feather) {
        feather.replace();
    }
});
</script>