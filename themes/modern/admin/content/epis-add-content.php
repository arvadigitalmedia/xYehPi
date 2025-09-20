<?php
/**
 * EPIS Account Creation Content
 * Content untuk halaman standalone Create EPIS Account
 * 
 * @version 1.0.0
 * @author EPIC Hub Team
 * 
 * Variables yang tersedia dari parent scope:
 * - $success, $error: Alert messages
 * - $eligible_epic_users: Users eligible for EPIS promotion
 * - $form_data: Form data for repopulation
 * - $user: Current admin user
 */

// Ensure variables are set with defaults
$success = $success ?? '';
$error = $error ?? '';
$eligible_epic_users = $eligible_epic_users ?? [];
$form_data = $form_data ?? [];
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

<!-- Main Content - Card 1: Create New EPIS Account -->
<div class="admin-content">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h2 class="form-title">
                    <i data-feather="crown" width="24" height="24"></i>
                    Create New EPIS Account
                </h2>
                <p class="form-description">
                    Promote an existing EPIC user to EPIS status and configure their territory settings.
                </p>
            </div>
            
            <form method="POST" class="form-grid" id="createEpisForm">
                <input type="hidden" name="action" value="create_epis">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <!-- 1. Dropdown Selection -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i data-feather="list" width="20" height="20"></i>
                        1. Select Account Option
                    </h3>
                    <div class="form-group full-width">
                        <label for="account_selection" class="form-label required">
                            <i data-feather="user-plus" width="16" height="16"></i>
                            Select Account or Create New
                        </label>
                        <select name="account_selection" id="account_selection" class="form-select" required>
                            <option value="">Choose an option...</option>
                            <?php foreach ($eligible_epic_users as $epic_user): ?>
                                <option value="existing_<?= $epic_user['id'] ?>" 
                                        <?= isset($form_data['account_selection']) && $form_data['account_selection'] == 'existing_'.$epic_user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($epic_user['name']) ?> (<?= htmlspecialchars($epic_user['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                            <option value="manual_input" 
                                    <?= isset($form_data['account_selection']) && $form_data['account_selection'] === 'manual_input' ? 'selected' : '' ?>>
                                Add Manual EPIS
                            </option>
                            <?php if ($user['role'] === 'super_admin'): ?>
                            <option value="super_admin_create" 
                                    <?= isset($form_data['account_selection']) && $form_data['account_selection'] === 'super_admin_create' ? 'selected' : '' ?>>
                                Buat EPIS tanpa akun EPIC (Super Admin)
                            </option>
                            <?php endif; ?>
                        </select>
                        <small class="form-help">Select existing EPIC user to promote or create new manual EPIS account</small>
                    </div>
                </div>

                <!-- 2. EPIS Account Details -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i data-feather="user-check" width="20" height="20"></i>
                        2. EPIS Account Details
                    </h3>
                    
                    <!-- Manual Input Section -->
                    <div class="form-subsection" id="manual-input-section" style="display: none;">
                        <h4 class="subsection-title">
                            <i data-feather="edit-3" width="18" height="18"></i>
                            New EPIS Account Information
                        </h4>
                    
                    <div class="form-group">
                        <label for="manual_name" class="form-label required">
                            <i data-feather="user" width="16" height="16"></i>
                            Nama Lengkap
                        </label>
                        <input type="text" name="manual_name" id="manual_name" class="form-input" 
                               placeholder="Masukkan nama lengkap" 
                               value="<?= htmlspecialchars($form_data['manual_name'] ?? '') ?>" required>
                        <small class="form-help">Nama lengkap untuk akun EPIS baru</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_epis_id" class="form-label required">
                            <i data-feather="hash" width="16" height="16"></i>
                            No. ID EPIS
                        </label>
                        <input type="text" name="manual_epis_id" id="manual_epis_id" class="form-input" 
                               placeholder="Masukkan nomor ID EPIS unik" 
                               value="<?= htmlspecialchars($form_data['manual_epis_id'] ?? '') ?>" required>
                        <small class="form-help">Nomor identifikasi unik untuk EPIS (contoh: EPIS001, EPIS-JKT-001)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_email" class="form-label required">
                            <i data-feather="mail" width="16" height="16"></i>
                            Email
                        </label>
                        <input type="email" name="manual_email" id="manual_email" class="form-input" 
                               placeholder="Masukkan alamat email" 
                               value="<?= htmlspecialchars($form_data['manual_email'] ?? '') ?>" required>
                        <small class="form-help">Alamat email valid untuk login dan notifikasi</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_phone" class="form-label required">
                            <i data-feather="phone" width="16" height="16"></i>
                            Nomor Telepon
                        </label>
                        <input type="tel" name="manual_phone" id="manual_phone" class="form-input" 
                               placeholder="Masukkan nomor telepon" 
                               value="<?= htmlspecialchars($form_data['manual_phone'] ?? '') ?>" required>
                        <small class="form-help">Nomor telepon untuk kontak dan verifikasi</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="manual_address" class="form-label required">
                            <i data-feather="map-pin" width="16" height="16"></i>
                            Alamat Lengkap
                        </label>
                        <textarea name="manual_address" id="manual_address" class="form-textarea" 
                                  rows="3" placeholder="Masukkan alamat lengkap termasuk kota, provinsi, dan kode pos" required><?= htmlspecialchars($form_data['manual_address'] ?? '') ?></textarea>
                        <small class="form-help">Alamat lengkap untuk keperluan administrasi dan pengiriman</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="manual_password" class="form-label required">
                            <i data-feather="lock" width="16" height="16"></i>
                            Password Awal
                        </label>
                        <input type="password" name="manual_password" id="manual_password" class="form-input" 
                               placeholder="Masukkan password awal" 
                               value="<?= htmlspecialchars($form_data['manual_password'] ?? '') ?>" required>
                        <small class="form-help">Minimum 8 karakter, user dapat mengubah kemudian</small>
                    </div>
                    </div>
                    
                    <!-- Territory Information -->
                    <div class="form-subsection">
                        <h4 class="subsection-title">
                            <i data-feather="map" width="18" height="18"></i>
                            Territory Information
                        </h4>
                    
                    <div class="form-group">
                        <label for="territory_name" class="form-label required">Territory Name</label>
                        <input type="text" name="territory_name" id="territory_name" class="form-input" 
                               placeholder="e.g., Jakarta Region, East Java Territory" 
                               value="<?= htmlspecialchars($form_data['territory_name'] ?? '') ?>" required>
                        <small class="form-help">A descriptive name for the EPIS territory</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="territory_description" class="form-label">Territory Description</label>
                        <textarea name="territory_description" id="territory_description" class="form-textarea" 
                                  rows="3" placeholder="Describe the territory coverage, target market, or special notes..."><?= htmlspecialchars($form_data['territory_description'] ?? '') ?></textarea>
                        <small class="form-help">Optional detailed description of the territory</small>
                    </div>
                    
                    <!-- Network Settings -->
                    <div class="form-subsection">
                        <h4 class="subsection-title">
                            <i data-feather="users" width="18" height="18"></i>
                            Network Settings
                        </h4>
                        
                        <div class="form-group">
                            <label for="max_epic_recruits" class="form-label required">Maximum EPIC Recruits</label>
                            <input type="number" name="max_epic_recruits" id="max_epic_recruits"
                               class="form-input" value="<?= htmlspecialchars($form_data['max_epic_recruits'] ?? '50') ?>"
                               min="1" max="1000000" required>
                            <small class="form-help">Maximum number of EPIC accounts this EPIS can recruit (1 - 1,000,000)</small>
                        </div>
                    </div>
                </div>
                
                <!-- 3. Action Buttons -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i data-feather="check-circle" width="20" height="20"></i>
                        3. Create Account
                    </h3>
                    <div class="form-actions full-width">
                        <a href="<?= epic_url('admin/manage/epis') ?>" class="btn btn-secondary">
                            <i data-feather="arrow-left" width="16" height="16"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="plus" width="16" height="16"></i>
                            Create EPIS Account
                        </button>
                    </div>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Card 2: Commission Information -->
<div class="admin-content" style="margin-top: 2rem;">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h2 class="form-title">
                    <i data-feather="percent" width="24" height="24"></i>
                    % Commission Information
                </h2>
                <p class="form-description">
                    Manage global commission rates for all EPIS accounts in a single column layout.
                </p>
            </div>
            
            <form method="POST" class="form-single-column" id="commissionForm">
                <input type="hidden" name="action" value="update_commission">
                
                <!-- Commission Settings Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i data-feather="settings" width="20" height="20"></i>
                        Current Commission Rates
                    </h3>
                    
                    <!-- Direct Commission -->
                    <div class="form-group full-width">
                        <label for="direct_commission_rate" class="form-label required">
                            <i data-feather="trending-up" width="16" height="16"></i>
                            Direct Recruitment Commission (%)
                        </label>
                        <input type="number" name="direct_commission_rate" id="direct_commission_rate" 
                               class="form-input" step="0.01" min="0" max="100" 
                               value="<?= epic_setting('epis_direct_commission_rate', '10.00') ?>" required>
                        <small class="form-help">Komisi yang diterima EPIS saat merekrut member secara langsung</small>
                    </div>
                    
                    <!-- Indirect Commission -->
                    <div class="form-group full-width">
                        <label for="indirect_commission_rate" class="form-label required">
                            <i data-feather="share-2" width="16" height="16"></i>
                            Indirect Commission (%)
                        </label>
                        <input type="number" name="indirect_commission_rate" id="indirect_commission_rate" 
                               class="form-input" step="0.01" min="0" max="100" 
                               value="<?= epic_setting('epis_indirect_commission_rate', '5.00') ?>" required>
                        <small class="form-help">Komisi yang diterima EPIS dari aktivitas network EPIC</small>
                    </div>
                    
                    <!-- Information Grid -->
                    <div class="info-grid single-column">
                        <div class="info-item full-width">
                            <div class="info-label">
                                <i data-feather="info" width="16" height="16"></i>
                                Status Aplikasi
                            </div>
                            <div class="info-value">
                                Global untuk semua EPIS
                            </div>
                            <small class="info-help">Pengaturan ini berlaku untuk seluruh akun EPIS</small>
                        </div>
                        
                        <div class="info-item full-width">
                            <div class="info-label">
                                <i data-feather="clock" width="16" height="16"></i>
                                Terakhir Diupdate
                            </div>
                            <div class="info-value">
                                <?= date('d/m/Y H:i', strtotime(epic_setting('commission_last_updated', date('Y-m-d H:i:s')))) ?>
                            </div>
                            <small class="info-help">Waktu terakhir pengaturan komisi diubah</small>
                        </div>
                    </div>
                </div>
                
                <!-- Commission Form Actions -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i data-feather="save" width="20" height="20"></i>
                        Save Changes
                    </h3>
                    <div class="form-actions full-width">
                        <button type="button" class="btn btn-secondary" id="cancelCommissionBtn">
                            <i data-feather="x" width="16" height="16"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save" width="16" height="16"></i>
                            Save Commission Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Additional Information Card -->
<div class="info-card">
    <div class="info-header">
        <h3 class="info-title">
            <i data-feather="info" width="20" height="20"></i>
            EPIS Account Information
        </h3>
    </div>
    <div class="info-content">
        <ul class="info-list">
            <li><strong>EPIS Status:</strong> EPIS (Elite Partner in Success) accounts have enhanced privileges and commission structures.</li>
            <li><strong>Territory Management:</strong> Each EPIS manages a specific territory with defined recruitment limits.</li>
            <li><strong>Commission Structure:</strong> EPIS earns commissions from both direct recruitment and network activities.</li>
            <li><strong>Network Hierarchy:</strong> EPIS accounts can recruit and manage EPIC accounts within their territory.</li>
        </ul>
    </div>
</div>

<!-- JavaScript for Form Handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const creationMethod = document.getElementById('creation_method');
    const accountSelection = document.getElementById('account_selection');
    const manualInputSection = document.getElementById('manual-input-section');
    const createEpisForm = document.getElementById('createEpisForm');
    const commissionForm = document.getElementById('commissionForm');
    const cancelCommissionBtn = document.getElementById('cancelCommissionBtn');
    
    // Store original commission values for cancel functionality
    let originalCommissionValues = {
        direct: document.getElementById('direct_commission_rate').value,
        indirect: document.getElementById('indirect_commission_rate').value
    };
    
    // Handle creation method change
    if (creationMethod) {
        creationMethod.addEventListener('change', function() {
            const selectedValue = this.value;
            
            if (selectedValue === 'super_admin_create') {
                // For super admin create, hide user selection and show manual input
                if (accountSelection) {
                    accountSelection.value = 'manual_input';
                    accountSelection.dispatchEvent(new Event('change'));
                }
            }
        });
    }
    
    // Handle dropdown change for showing/hiding manual input section
    if (accountSelection) {
        accountSelection.addEventListener('change', function() {
            const selectedValue = this.value;
            
            if (selectedValue === 'manual_input') {
                manualInputSection.style.display = 'block';
                // Make manual input fields required
                setManualFieldsRequired(true);
            } else {
                manualInputSection.style.display = 'none';
                // Remove required from manual input fields
                setManualFieldsRequired(false);
            }
        });
    }
    
    // Function to set required attribute for manual input fields
    function setManualFieldsRequired(required) {
        const manualFields = [
            'manual_name', 'manual_epis_id', 'manual_email', 
            'manual_phone', 'manual_address', 'manual_password'
        ];
        
        manualFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                if (required) {
                    field.setAttribute('required', 'required');
                } else {
                    field.removeAttribute('required');
                    field.value = ''; // Clear value when not required
                }
            }
        });
    }
    
    // Form validation for EPIS creation
    createEpisForm.addEventListener('submit', function(e) {
        const selectedValue = accountSelection.value;
        
        if (!selectedValue) {
            e.preventDefault();
            alert('Silakan pilih akun EPIC yang akan dipromosikan atau pilih "Add Manual EPIS"');
            accountSelection.focus();
            return false;
        }
        
        // Additional validation for manual input
        if (selectedValue === 'manual_input') {
            const episId = document.getElementById('manual_epis_id').value.trim();
            const email = document.getElementById('manual_email').value.trim();
            const password = document.getElementById('manual_password').value;
            
            // Validate EPIS ID format
            if (episId && !/^[A-Z0-9\-_]+$/i.test(episId)) {
                e.preventDefault();
                alert('No. ID EPIS hanya boleh mengandung huruf, angka, tanda hubung (-), dan underscore (_)');
                document.getElementById('manual_epis_id').focus();
                return false;
            }
            
            // Validate password length
            if (password && password.length < 8) {
                e.preventDefault();
                alert('Password minimal 8 karakter');
                document.getElementById('manual_password').focus();
                return false;
            }
            
            // Validate email format (additional check)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email)) {
                e.preventDefault();
                alert('Format email tidak valid');
                document.getElementById('manual_email').focus();
                return false;
            }
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i data-feather="loader" width="16" height="16"></i> Creating...';
        submitBtn.disabled = true;
        
        // Re-enable button after 5 seconds (fallback)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            feather.replace(); // Refresh feather icons
        }, 5000);
    });
    
    // Commission form handling
    if (commissionForm) {
        commissionForm.addEventListener('submit', function(e) {
            const directRate = parseFloat(document.getElementById('direct_commission_rate').value);
            const indirectRate = parseFloat(document.getElementById('indirect_commission_rate').value);
            
            // Validation
            if (directRate < 0 || directRate > 100) {
                e.preventDefault();
                alert('Direct commission rate harus antara 0-100%');
                document.getElementById('direct_commission_rate').focus();
                return false;
            }
            
            if (indirectRate < 0 || indirectRate > 100) {
                e.preventDefault();
                alert('Indirect commission rate harus antara 0-100%');
                document.getElementById('indirect_commission_rate').focus();
                return false;
            }
            
            if (indirectRate > directRate) {
                if (!confirm('Indirect commission rate lebih tinggi dari direct commission rate. Apakah Anda yakin?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i data-feather="loader" width="16" height="16"></i> Saving...';
            submitBtn.disabled = true;
            
            // Update original values
            originalCommissionValues.direct = directRate;
            originalCommissionValues.indirect = indirectRate;
        });
    }
    
    // Cancel commission changes
    if (cancelCommissionBtn) {
        cancelCommissionBtn.addEventListener('click', function() {
            if (confirm('Batalkan perubahan dan kembali ke nilai semula?')) {
                document.getElementById('direct_commission_rate').value = originalCommissionValues.direct;
                document.getElementById('indirect_commission_rate').value = originalCommissionValues.indirect;
            }
        });
    }
    
    // Initialize form state based on current selection
    if (accountSelection.value === 'manual_input') {
        manualInputSection.style.display = 'block';
        setManualFieldsRequired(true);
    }
    
    // Real-time commission preview
    const directCommissionInput = document.getElementById('direct_commission_rate');
    const indirectCommissionInput = document.getElementById('indirect_commission_rate');
    
    if (directCommissionInput && indirectCommissionInput) {
        [directCommissionInput, indirectCommissionInput].forEach(input => {
            input.addEventListener('input', function() {
                // Add visual feedback for changes
                if (this.value != originalCommissionValues[this.id.includes('direct') ? 'direct' : 'indirect']) {
                    this.style.borderColor = '#f59e0b';
                    this.style.backgroundColor = '#fffbeb';
                } else {
                    this.style.borderColor = '';
                    this.style.backgroundColor = '';
                }
            });
        });
    }
    
    // Auto-generate EPIS ID suggestion
    const manualNameInput = document.getElementById('manual_name');
    const manualEpisIdInput = document.getElementById('manual_epis_id');
    
    if (manualNameInput && manualEpisIdInput) {
        manualNameInput.addEventListener('blur', function() {
            if (this.value && !manualEpisIdInput.value) {
                // Generate EPIS ID suggestion from name
                const name = this.value.trim();
                const nameParts = name.split(' ');
                let suggestion = 'EPIS-';
                
                if (nameParts.length >= 2) {
                    suggestion += nameParts[0].substring(0, 3).toUpperCase() + 
                                 nameParts[nameParts.length - 1].substring(0, 3).toUpperCase();
                } else {
                    suggestion += name.substring(0, 6).toUpperCase();
                }
                
                suggestion += '-' + String(Date.now()).slice(-3);
                manualEpisIdInput.value = suggestion;
                manualEpisIdInput.style.borderColor = '#10b981';
                manualEpisIdInput.style.backgroundColor = '#f0fdf4';
                
                // Reset styling after 2 seconds
                setTimeout(() => {
                    manualEpisIdInput.style.borderColor = '';
                    manualEpisIdInput.style.backgroundColor = '';
                }, 2000);
            }
        });
    }
});
</script>