<?php
/**
 * EPIC Hub Admin Settings Form Content
 * Konten halaman settings form registrasi untuk layout global
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Extract variables dari layout data
extract($data ?? []);
?>

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
    </div>
<?php endif; ?>

<!-- Settings Navigation -->
<div class="settings-navigation">
    <nav class="settings-nav">
        <a href="<?= epic_url('admin/settings/general') ?>" class="settings-nav-item">
            <i data-feather="globe" class="settings-nav-icon"></i>
            <span>General</span>
        </a>
        <a href="<?= epic_url('admin/settings/form-registrasi') ?>" class="settings-nav-item active">
            <i data-feather="file-text" class="settings-nav-icon"></i>
            <span>Form Registrasi</span>
        </a>
        <a href="<?= epic_url('admin/settings/email-notification') ?>" class="settings-nav-item">
            <i data-feather="mail" class="settings-nav-icon"></i>
            <span>Email Notification</span>
        </a>
        <a href="<?= epic_url('admin/settings/whatsapp-notification') ?>" class="settings-nav-item">
            <i data-feather="message-circle" class="settings-nav-icon"></i>
            <span>WhatsApp Notification</span>
        </a>
        <a href="<?= epic_url('admin/settings/payment-gateway') ?>" class="settings-nav-item">
            <i data-feather="credit-card" class="settings-nav-icon"></i>
            <span>Payment Gateway</span>
        </a>
    </nav>
</div>

<!-- Form Fields Management -->
<div class="form-fields-container">
    <!-- Add/Edit Field Form -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="plus-circle" class="settings-card-icon"></i>
                <?= $edit_field ? 'Edit Field' : 'Tambah Field Baru' ?>
            </h3>
            <p class="settings-card-description">
                <?= $edit_field ? 'Ubah konfigurasi field yang ada' : 'Buat field custom untuk form registrasi' ?>
            </p>
        </div>
        
        <div class="settings-card-body">
            <form method="POST" action="<?= epic_url('admin/settings/form-registrasi') ?>" class="field-form">
                <?php if ($edit_field): ?>
                    <input type="hidden" name="update_field" value="1">
                    <input type="hidden" name="field_id" value="<?= $edit_field['id'] ?>">
                <?php else: ?>
                    <input type="hidden" name="add_field" value="1">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="field_label">
                            Label Field
                            <span class="form-label-required">*</span>
                        </label>
                        <input type="text" 
                               id="field_label" 
                               name="field_label" 
                               class="form-input" 
                               placeholder="Nama Lengkap" 
                               value="<?= htmlspecialchars($edit_field['field_label'] ?? '') ?>"
                               required>
                    </div>
                    
                    <?php if (!$edit_field): ?>
                    <div class="form-group">
                        <label class="form-label" for="field_name">
                            Nama Field
                            <span class="form-label-required">*</span>
                        </label>
                        <input type="text" 
                               id="field_name" 
                               name="field_name" 
                               class="form-input" 
                               placeholder="full_name" 
                               value="<?= htmlspecialchars($edit_field['field_name'] ?? '') ?>"
                               required>
                        <div class="form-help">Gunakan format snake_case (contoh: full_name, phone_number)</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="field_type">
                            Tipe Field
                        </label>
                        <select id="field_type" name="field_type" class="form-input" onchange="toggleOptions()">
                            <option value="text" <?= ($edit_field['field_type'] ?? '') === 'text' ? 'selected' : '' ?>>Text Input</option>
                            <option value="email" <?= ($edit_field['field_type'] ?? '') === 'email' ? 'selected' : '' ?>>Email Input</option>
                            <option value="tel" <?= ($edit_field['field_type'] ?? '') === 'tel' ? 'selected' : '' ?>>Phone Input</option>
                            <option value="number" <?= ($edit_field['field_type'] ?? '') === 'number' ? 'selected' : '' ?>>Number Input</option>
                            <option value="date" <?= ($edit_field['field_type'] ?? '') === 'date' ? 'selected' : '' ?>>Date Input</option>
                            <option value="textarea" <?= ($edit_field['field_type'] ?? '') === 'textarea' ? 'selected' : '' ?>>Textarea</option>
                            <option value="select" <?= ($edit_field['field_type'] ?? '') === 'select' ? 'selected' : '' ?>>Select Dropdown</option>
                            <option value="checkbox" <?= ($edit_field['field_type'] ?? '') === 'checkbox' ? 'selected' : '' ?>>Checkbox</option>
                            <option value="radio" <?= ($edit_field['field_type'] ?? '') === 'radio' ? 'selected' : '' ?>>Radio Button</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="placeholder">
                            Placeholder
                        </label>
                        <input type="text" 
                               id="placeholder" 
                               name="placeholder" 
                               class="form-input" 
                               placeholder="Masukkan nama lengkap Anda" 
                               value="<?= htmlspecialchars($edit_field['placeholder'] ?? '') ?>">
                    </div>
                </div>
                
                <!-- Options for select/radio/checkbox -->
                <div class="form-group" id="options-group" style="display: <?= in_array($edit_field['field_type'] ?? '', ['select', 'radio', 'checkbox']) ? 'block' : 'none' ?>">
                    <label class="form-label" for="options">
                        Options
                    </label>
                    <textarea id="options" 
                              name="options" 
                              class="form-textarea" 
                              rows="4" 
                              placeholder="Satu opsi per baris:\nOption 1\nOption 2\nOption 3"><?= htmlspecialchars($edit_field['options'] ?? '') ?></textarea>
                    <div class="form-help">Satu opsi per baris untuk select, radio, atau checkbox</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="sort_order">
                            Urutan
                        </label>
                        <input type="number" 
                               id="sort_order" 
                               name="sort_order" 
                               class="form-input" 
                               placeholder="0" 
                               value="<?= htmlspecialchars($edit_field['sort_order'] ?? '0') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="validation">
                            Validasi
                        </label>
                        <select id="validation" name="validation" class="form-input">
                            <option value="" <?= ($edit_field['validation'] ?? '') === '' ? 'selected' : '' ?>>Tidak ada</option>
                            <option value="required" <?= ($edit_field['validation'] ?? '') === 'required' ? 'selected' : '' ?>>Wajib diisi</option>
                            <option value="email" <?= ($edit_field['validation'] ?? '') === 'email' ? 'selected' : '' ?>>Format email</option>
                            <option value="phone" <?= ($edit_field['validation'] ?? '') === 'phone' ? 'selected' : '' ?>>Format telepon</option>
                            <option value="numeric" <?= ($edit_field['validation'] ?? '') === 'numeric' ? 'selected' : '' ?>>Hanya angka</option>
                        </select>
                    </div>
                </div>
                
                <!-- Field Visibility Settings -->
                <div class="form-group">
                    <label class="form-label">Pengaturan Tampilan Field</label>
                    <div class="form-checkbox-list">
                        <div class="form-checkbox-group">
                            <input type="checkbox" 
                                   id="required" 
                                   name="required" 
                                   value="1" 
                                   class="form-checkbox"
                                   <?= ($edit_field['required'] ?? 0) == 1 ? 'checked' : '' ?>>
                            <label for="required" class="form-checkbox-label">
                                Wajib Diisi
                            </label>
                        </div>
                        
                        <div class="form-checkbox-group">
                            <input type="checkbox" 
                                   id="show_in_registration" 
                                   name="show_in_registration" 
                                   value="1" 
                                   class="form-checkbox"
                                   <?= ($edit_field['show_in_registration'] ?? 1) == 1 ? 'checked' : '' ?>>
                            <label for="show_in_registration" class="form-checkbox-label">
                                Tampil di Form Registrasi
                            </label>
                        </div>
                        
                        <div class="form-checkbox-group">
                            <input type="checkbox" 
                                   id="show_in_profile" 
                                   name="show_in_profile" 
                                   value="1" 
                                   class="form-checkbox"
                                   <?= ($edit_field['show_in_profile'] ?? 0) == 1 ? 'checked' : '' ?>>
                            <label for="show_in_profile" class="form-checkbox-label">
                                Tampil di Edit Profil
                            </label>
                        </div>
                        
                        <div class="form-checkbox-group">
                            <input type="checkbox" 
                                   id="show_in_network" 
                                   name="show_in_network" 
                                   value="1" 
                                   class="form-checkbox"
                                   <?= ($edit_field['show_in_network'] ?? 0) == 1 ? 'checked' : '' ?>>
                            <label for="show_in_network" class="form-checkbox-label">
                                Tampil di Halaman Network
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" width="16" height="16"></i>
                        <?= $edit_field ? 'Update Field' : 'Tambah Field' ?>
                    </button>
                    <?php if ($edit_field): ?>
                        <a href="<?= epic_url('admin/settings/form-registrasi') ?>" class="btn btn-secondary">
                            <i data-feather="x" width="16" height="16"></i>
                            Batal Edit
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Existing Fields List -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="list" class="settings-card-icon"></i>
                Form Fields yang Ada
            </h3>
            <p class="settings-card-description">
                Kelola dan atur urutan field yang sudah ada
            </p>
        </div>
        
        <div class="settings-card-body">
            <?php if (empty($form_fields)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i data-feather="inbox" width="48" height="48"></i>
                    </div>
                    <h3 class="empty-state-title">Belum Ada Form Fields</h3>
                    <p class="empty-state-text">
                        Tambahkan field custom untuk form registrasi menggunakan form di atas.
                    </p>
                </div>
            <?php else: ?>
                <div class="form-fields-list" id="sortable-fields">
                    <?php foreach ($form_fields as $field): ?>
                        <div class="form-field-item" data-field-id="<?= $field['id'] ?>">
                            <div class="form-field-drag-handle">
                                <i data-feather="move" width="16" height="16"></i>
                            </div>
                            
                            <div class="form-field-info">
                                 <div class="form-field-label">
                                     <?= htmlspecialchars($field['label']) ?>
                                     <?php if ($field['is_required']): ?>
                                         <span class="field-required">*</span>
                                     <?php endif; ?>
                                 </div>
                                 <div class="form-field-meta">
                                     <span class="field-name"><?= htmlspecialchars($field['name']) ?></span>
                                     <span class="field-type"><?= htmlspecialchars($field['type']) ?></span>
                                     <span class="field-order">Urutan: <?= $field['sort_order'] ?></span>
                                 </div>
                                <div class="form-field-visibility">
                                    <?php if ($field['show_in_registration']): ?>
                                        <span class="visibility-badge registration">Registrasi</span>
                                    <?php endif; ?>
                                    <?php if ($field['show_in_profile']): ?>
                                        <span class="visibility-badge profile">Profil</span>
                                    <?php endif; ?>
                                    <?php if ($field['show_in_network']): ?>
                                        <span class="visibility-badge network">Network</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($field['placeholder'])): ?>
                                    <div class="form-field-placeholder">
                                        Placeholder: <?= htmlspecialchars($field['placeholder']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-field-actions">
                                <a href="<?= epic_url('admin/settings/form-registrasi?edit=' . $field['id']) ?>" 
                                   class="btn btn-sm btn-secondary" 
                                   title="Edit Field">
                                    <i data-feather="edit-2" width="14" height="14"></i>
                                    Edit
                                </a>
                                
                                <button type="button" 
                                        class="btn btn-sm btn-danger" 
                                        title="Hapus Field"
                                        onclick="deleteField(<?= $field['id'] ?>, '<?= htmlspecialchars($field['label']) ?>')">
                                    <i data-feather="trash-2" width="14" height="14"></i>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">
                <i data-feather="alert-triangle" class="modal-icon"></i>
                Konfirmasi Hapus Field
            </h3>
            <button class="modal-close" onclick="closeDeleteModal()">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Anda akan menghapus field <strong id="fieldName"></strong>.</p>
            <p class="text-warning">
                <i data-feather="alert-circle"></i>
                Field yang dihapus tidak dapat dikembalikan.
            </p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
            <form method="POST" action="<?= epic_url('admin/settings/form-registrasi') ?>" style="display: inline;">
                <input type="hidden" name="delete_field" value="1">
                <input type="hidden" name="field_id" id="deleteFieldId">
                <button type="submit" class="btn btn-danger">
                    <i data-feather="trash-2" width="16" height="16"></i>
                    Hapus Field
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Include Sortable.js for drag & drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

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
    background: linear-gradient(135deg, var(--gold-500), var(--gold-400));
    border-color: var(--gold-400);
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
}

.settings-nav-icon {
    width: 16px;
    height: 16px;
}

/* Form fields specific styles */
.form-fields-container {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
}

.form-checkbox-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.form-checkbox-group {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.form-checkbox {
    width: 18px;
    height: 18px;
}

.form-checkbox-label {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-200);
    cursor: pointer;
}

.form-fields-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

.form-field-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: var(--spacing-4);
    background: var(--surface-3);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    transition: all var(--transition-normal);
    cursor: move;
}

.form-field-item.sortable-ghost {
    opacity: 0.4;
}

.form-field-item.sortable-chosen {
    background: var(--surface-2);
    border-color: var(--gold-500);
}

.form-field-drag-handle {
    display: flex;
    align-items: center;
    color: var(--ink-500);
    margin-right: var(--spacing-3);
    cursor: grab;
}

.form-field-drag-handle:active {
    cursor: grabbing;
}

.form-field-item:hover {
    border-color: var(--ink-600);
    background: var(--surface-2);
}

.form-field-info {
    flex: 1;
}

.form-field-label {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-2);
}

.field-required {
    color: var(--danger);
    margin-left: var(--spacing-1);
}

.form-field-meta {
    display: flex;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-2);
}

.field-name,
.field-type,
.field-order {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
    background: var(--surface-1);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
}

.form-field-placeholder {
    font-size: var(--font-size-xs);
    color: var(--ink-500);
    font-style: italic;
}

.form-field-visibility {
    display: flex;
    gap: var(--spacing-2);
    margin: var(--spacing-2) 0;
    flex-wrap: wrap;
}

.visibility-badge {
    font-size: var(--font-size-xs);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-weight: var(--font-weight-medium);
}

.visibility-badge.registration {
    background: rgba(34, 197, 94, 0.2);
    color: var(--success);
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.visibility-badge.profile {
    background: rgba(59, 130, 246, 0.2);
    color: var(--primary);
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.visibility-badge.network {
    background: rgba(168, 85, 247, 0.2);
    color: #a855f7;
    border: 1px solid rgba(168, 85, 247, 0.3);
}

.form-field-actions {
    display: flex;
    gap: var(--spacing-2);
    margin-left: var(--spacing-4);
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-container {
    background: var(--surface-2);
    border-radius: var(--radius-2xl);
    border: 1px solid var(--ink-700);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-700);
}

.modal-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0;
}

.modal-icon {
    color: var(--warning);
}

.modal-close {
    background: none;
    border: none;
    color: var(--ink-400);
    cursor: pointer;
    padding: var(--spacing-2);
    border-radius: var(--radius-sm);
    transition: all var(--transition-normal);
}

.modal-close:hover {
    color: var(--ink-200);
    background: var(--surface-3);
}

.modal-body {
    padding: var(--spacing-6);
}

.modal-body p {
    margin: 0 0 var(--spacing-4) 0;
    color: var(--ink-300);
}

.text-warning {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    color: var(--warning);
    background: rgba(251, 191, 36, 0.1);
    padding: var(--spacing-3);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(251, 191, 36, 0.2);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-3);
    padding: var(--spacing-6);
    border-top: 1px solid var(--ink-700);
}

@media (max-width: 768px) {
    .settings-nav {
        flex-direction: column;
    }
    
    .settings-nav-item {
        justify-content: flex-start;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-field-item {
        flex-direction: column;
        gap: var(--spacing-3);
    }
    
    .form-field-actions {
        margin-left: 0;
        justify-content: flex-start;
    }
    
    .form-field-meta {
        flex-wrap: wrap;
    }
}
</style>

<script>
// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Initialize sortable for drag & drop
    initializeSortable();
});

// Initialize Sortable.js for drag & drop functionality
function initializeSortable() {
    const sortableElement = document.getElementById('sortable-fields');
    if (sortableElement && typeof Sortable !== 'undefined') {
        new Sortable(sortableElement, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            handle: '.form-field-drag-handle',
            onEnd: function(evt) {
                updateFieldOrder();
            }
        });
    }
}

// Update field order after drag & drop
function updateFieldOrder() {
    const fieldItems = document.querySelectorAll('.form-field-item');
    const fieldOrder = [];
    
    fieldItems.forEach((item, index) => {
        const fieldId = item.getAttribute('data-field-id');
        if (fieldId) {
            fieldOrder.push({
                id: fieldId,
                sort_order: index
            });
        }
    });
    
    // Send AJAX request to update order
    fetch('<?= epic_url("admin/settings/form-registrasi") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            update_order: true,
            field_order: fieldOrder
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update sort order display
            fieldItems.forEach((item, index) => {
                const orderSpan = item.querySelector('.field-order');
                if (orderSpan) {
                    orderSpan.textContent = `Urutan: ${index}`;
                }
            });
        }
    })
    .catch(error => {
        console.error('Error updating field order:', error);
    });
}

// Toggle Options Field
function toggleOptions() {
    const fieldType = document.getElementById('field_type').value;
    const optionsGroup = document.getElementById('options-group');
    
    if (['select', 'radio', 'checkbox'].includes(fieldType)) {
        optionsGroup.style.display = 'block';
    } else {
        optionsGroup.style.display = 'none';
    }
}

// Delete Field
function deleteField(fieldId, fieldName) {
    document.getElementById('fieldName').textContent = fieldName;
    document.getElementById('deleteFieldId').value = fieldId;
    document.getElementById('deleteModal').style.display = 'flex';
}

// Close Delete Modal
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('deleteModal');
    if (e.target === modal) {
        closeDeleteModal();
    }
});
</script>