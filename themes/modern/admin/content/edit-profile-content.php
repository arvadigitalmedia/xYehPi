<?php
/**
 * Edit Profile Content
 * Content yang akan di-render oleh layout global
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

<!-- Profile Form -->
<form method="POST" enctype="multipart/form-data" class="profile-form">
    <!-- Profile Photo Section -->
    <div class="form-section">
        <h2 class="form-section-title">
            <i data-feather="camera" class="section-icon"></i>
            Profile Photo
        </h2>
        
        <div class="profile-photo-section">
            <div class="current-photo">
                <?php if (!empty($user['profile_photo'])): ?>
                    <img src="<?= epic_url('uploads/profiles/' . $user['profile_photo']) ?>" 
                         alt="Current Profile Photo" class="profile-photo-preview" id="photo-preview">
                <?php else: ?>
                    <div class="profile-photo-placeholder" id="photo-preview">
                        <i data-feather="user" width="48" height="48"></i>
                        <span>No Photo</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="photo-upload-controls">
                <input type="file" name="profile_photo" id="profile_photo" 
                       accept="image/jpeg,image/jpg,image/png,image/gif" style="display: none;" onchange="previewPhoto(this)">
                <button type="button" class="topbar-btn secondary" onclick="document.getElementById('profile_photo').click()">
                    <i data-feather="camera" width="16" height="16"></i>
                    Change Photo
                </button>
                <div class="form-help">
                    <div class="help-item">
                        <i data-feather="file-text" width="14" height="14"></i>
                        <span>Format: JPG, PNG, GIF</span>
                    </div>
                    <div class="help-item">
                        <i data-feather="hard-drive" width="14" height="14"></i>
                        <span>Maksimal: 1MB</span>
                    </div>
                </div>
                <div id="upload-error" class="upload-error" style="display: none;"></div>
            </div>
        </div>
    </div>
    
    <!-- Basic Information -->
    <div class="form-section">
        <h2 class="form-section-title">
            <i data-feather="user" class="section-icon"></i>
            Basic Information
        </h2>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label required">Full Name</label>
                <input type="text" name="name" class="form-input" 
                       value="<?= htmlspecialchars($user['name']) ?>" 
                       placeholder="Enter your full name" required>
            </div>
            
            <div class="form-group">
                <label class="form-label required">Email Address</label>
                <input type="email" name="email" class="form-input" 
                       value="<?= htmlspecialchars($user['email']) ?>" 
                       placeholder="Enter your email address" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Phone Number (WhatsApp)</label>
                <div class="phone-input-wrapper">
                    <span class="phone-prefix">+</span>
                    <input type="tel" name="phone" id="phone" class="form-input phone-input" 
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                           placeholder="6281234567890" 
                           pattern="[0-9]{10,15}" 
                           onkeyup="validatePhoneNumber(this)">
                </div>
                <div class="form-help">
                    <div class="help-item">
                        <i data-feather="phone" width="14" height="14"></i>
                        <span>Format: +kode negara + nomor (contoh: +6281234567890)</span>
                    </div>
                    <div class="help-item">
                        <i data-feather="message-circle" width="14" height="14"></i>
                        <span>Nomor akan digunakan untuk notifikasi WhatsApp</span>
                    </div>
                </div>
                <div id="phone-validation" class="validation-message" style="display: none;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Role</label>
                <input type="text" class="form-input" 
                       value="<?= ucfirst($user['role']) ?>" 
                       readonly disabled>
                <div class="form-help">Your role cannot be changed</div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-textarea" 
                      placeholder="Enter your address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
        </div>
    </div>
    
    <!-- Password Change -->
    <div class="form-section">
        <h2 class="form-section-title">
            <i data-feather="lock" class="section-icon"></i>
            Change Password
        </h2>
        <div class="form-help section-help">Leave password fields empty if you don't want to change your password</div>
        
        <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-input" 
                   placeholder="Enter your current password">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <div class="password-input-wrapper">
                    <input type="password" name="new_password" id="new_password" class="form-input" 
                           placeholder="Enter new password" 
                           onkeyup="checkPasswordStrength(this)">
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                        <i data-feather="eye" width="16" height="16"></i>
                    </button>
                </div>
                <div class="password-strength" id="password-strength" style="display: none;">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strength-fill"></div>
                    </div>
                    <div class="strength-text" id="strength-text"></div>
                </div>
                <div class="password-requirements">
                    <div class="form-help">Rekomendasi password kuat:</div>
                    <div class="requirements-list">
                        <div class="requirement" id="req-length">
                            <i data-feather="x" width="14" height="14"></i>
                            <span>Minimal 8 karakter</span>
                        </div>
                        <div class="requirement" id="req-uppercase">
                            <i data-feather="x" width="14" height="14"></i>
                            <span>Huruf besar (A-Z)</span>
                        </div>
                        <div class="requirement" id="req-lowercase">
                            <i data-feather="x" width="14" height="14"></i>
                            <span>Huruf kecil (a-z)</span>
                        </div>
                        <div class="requirement" id="req-number">
                            <i data-feather="x" width="14" height="14"></i>
                            <span>Angka (0-9)</span>
                        </div>
                        <div class="requirement" id="req-symbol">
                            <i data-feather="x" width="14" height="14"></i>
                            <span>Simbol (!@#$%^&*)</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <div class="password-input-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-input" 
                           placeholder="Confirm your new password" 
                           onkeyup="checkPasswordMatch()">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i data-feather="eye" width="16" height="16"></i>
                    </button>
                </div>
                <div id="password-match" class="validation-message" style="display: none;"></div>
            </div>
        </div>
    </div>
    
    <!-- Account Information -->
    <div class="form-section">
        <h2 class="form-section-title">
            <i data-feather="info" class="section-icon"></i>
            Account Information
        </h2>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-header">
                    <i data-feather="check-circle" class="info-icon" width="18" height="18"></i>
                    <div class="info-label">Account Status</div>
                </div>
                <div class="info-value">
                    <span class="badge <?= $user['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                        <?= ucfirst($user['status']) ?>
                    </span>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-header">
                    <i data-feather="calendar" class="info-icon" width="18" height="18"></i>
                    <div class="info-label">Member Since</div>
                </div>
                <div class="info-value"><?= date('F j, Y', strtotime($user['created_at'])) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-header">
                    <i data-feather="clock" class="info-icon" width="18" height="18"></i>
                    <div class="info-label">Last Updated</div>
                </div>
                <div class="info-value"><?= date('F j, Y g:i A', strtotime($user['updated_at'])) ?></div>
            </div>
            
            <?php if (!empty($user['referral_code'])): ?>
                <div class="info-item">
                    <div class="info-header">
                        <i data-feather="link" class="info-icon" width="18" height="18"></i>
                        <div class="info-label">Referral Code</div>
                    </div>
                    <div class="info-value">
                        <code class="referral-code"><?= htmlspecialchars($user['referral_code']) ?></code>
                        <button type="button" class="copy-btn" 
                                onclick="copyToClipboard('<?= htmlspecialchars($user['referral_code']) ?>')">
                            <i data-feather="copy" width="14" height="14"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <a href="<?= epic_url('admin') ?>" class="topbar-btn secondary">
            <i data-feather="arrow-left" width="16" height="16"></i>
            Back to Dashboard
        </a>
        <button type="submit" class="topbar-btn">
            <i data-feather="save" width="16" height="16"></i>
            Update Profile
        </button>
    </div>
</form>

<style>
/* Edit Profile Specific Styles */
.profile-form {
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-2xl);
    padding: var(--spacing-8);
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-8);
}

.profile-form::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-gold);
}

/* Form Section Styling */
.form-section {
    background: var(--surface-3);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-xl);
    padding: var(--spacing-6);
    margin-bottom: var(--spacing-6);
    transition: all var(--transition-normal);
    position: relative;
}

.form-section:hover {
    border-color: var(--gold-400);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.form-section:last-of-type {
    margin-bottom: 0;
}

/* Section Title with Icon */
.form-section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin: 0 0 var(--spacing-6) 0;
    color: var(--gold-300);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    padding-bottom: var(--spacing-3);
    border-bottom: 2px solid var(--ink-600);
}

.section-icon {
    width: 24px;
    height: 24px;
    color: var(--gold-400);
    opacity: 0.8;
    transition: all var(--transition-fast);
}

.form-section:hover .section-icon {
    opacity: 1;
    transform: scale(1.1);
}

.profile-photo-section {
    display: flex;
    align-items: center;
    gap: var(--spacing-6);
}

.profile-photo-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--gold-400);
    box-shadow: var(--shadow-gold-lg);
}

.profile-photo-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: var(--surface-3);
    border: 4px dashed var(--ink-600);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--ink-400);
    font-size: var(--font-size-sm);
    gap: var(--spacing-2);
}

.photo-upload-controls {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-4);
}

.info-item {
    background: linear-gradient(135deg, var(--surface-3) 0%, var(--surface-2) 100%);
    padding: var(--spacing-5);
    border-radius: var(--radius-lg);
    border: 1px solid var(--ink-600);
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;
}

.info-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--gradient-gold);
    opacity: 0.7;
}

.info-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    border-color: var(--gold-400);
}

.info-item:hover::before {
    opacity: 1;
    width: 6px;
}

.info-label {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-300);
    margin-bottom: var(--spacing-2);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-value {
    font-size: var(--font-size-base);
    color: var(--ink-100);
    font-weight: var(--font-weight-medium);
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.referral-code {
    background: var(--surface-1);
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-md);
    font-family: 'Courier New', monospace;
    font-size: var(--font-size-sm);
    color: var(--gold-400);
    border: 1px solid var(--ink-600);
}

.copy-btn {
    background: none;
    border: none;
    color: var(--ink-400);
    cursor: pointer;
    padding: var(--spacing-1);
    border-radius: var(--radius-sm);
    transition: color var(--transition-fast);
}

.copy-btn:hover {
    color: var(--gold-400);
}

.section-help {
    background: var(--surface-3);
    padding: var(--spacing-3) var(--spacing-4);
    border-radius: var(--radius-lg);
    border-left: 4px solid var(--gold-400);
    margin-bottom: var(--spacing-6);
    font-size: var(--font-size-sm);
    color: var(--ink-300);
}

/* Form Actions Styling */
.form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--spacing-4);
    padding: var(--spacing-6);
    background: var(--surface-3);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-xl);
    margin-top: var(--spacing-6);
}

.form-actions .topbar-btn {
    min-width: 140px;
    justify-content: center;
}

/* Enhanced Visual Hierarchy */
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-4);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.form-label {
    color: var(--ink-200);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    margin-bottom: var(--spacing-1);
}

.form-label.required::after {
    content: ' *';
    color: var(--danger);
}

.form-input,
.form-textarea {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    padding: var(--spacing-3);
    color: var(--ink-100);
    font-size: var(--font-size-base);
    transition: all var(--transition-fast);
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--gold-400);
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
}

/* Enhanced Form Help Styling */
.help-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-1);
    font-size: var(--font-size-sm);
    color: var(--ink-300);
}

.help-item:last-child {
    margin-bottom: 0;
}

/* Upload Error Styling */
.upload-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid var(--danger);
    border-radius: var(--radius-md);
    padding: var(--spacing-3);
    margin-top: var(--spacing-2);
    color: var(--danger-light);
    font-size: var(--font-size-sm);
}

/* Phone Input Styling */
.phone-input-wrapper {
    display: flex;
    align-items: center;
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.phone-input-wrapper:focus-within {
    border-color: var(--gold-400);
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
}

.phone-prefix {
    padding: var(--spacing-3) var(--spacing-2) var(--spacing-3) var(--spacing-3);
    color: var(--ink-300);
    font-weight: var(--font-weight-medium);
    border-right: 1px solid var(--ink-600);
}

.phone-input {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    flex: 1;
}

/* Password Input Styling */
.password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-toggle {
    position: absolute;
    right: var(--spacing-3);
    background: none;
    border: none;
    color: var(--ink-400);
    cursor: pointer;
    padding: var(--spacing-1);
    border-radius: var(--radius-sm);
    transition: color var(--transition-fast);
}

.password-toggle:hover {
    color: var(--gold-400);
}

/* Password Strength Indicator */
.password-strength {
    margin-top: var(--spacing-2);
}

.strength-bar {
    width: 100%;
    height: 4px;
    background: var(--ink-700);
    border-radius: var(--radius-sm);
    overflow: hidden;
    margin-bottom: var(--spacing-2);
}

.strength-fill {
    height: 100%;
    transition: all var(--transition-normal);
    border-radius: var(--radius-sm);
}

.strength-text {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}

/* Password Requirements */
.password-requirements {
    margin-top: var(--spacing-3);
}

.requirements-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-2);
    margin-top: var(--spacing-2);
}

.requirement {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--ink-400);
    transition: color var(--transition-fast);
}

.requirement.met {
    color: var(--success-light);
}

.requirement.met i {
    color: var(--success-light);
}

/* Validation Messages */
.validation-message {
    margin-top: var(--spacing-2);
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}

.validation-message.success {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid var(--success);
    color: var(--success-light);
}

.validation-message.error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid var(--danger);
    color: var(--danger-light);
}

/* Info Item Enhanced */
.info-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-2);
}

.info-icon {
    color: var(--gold-400);
    opacity: 0.8;
}

.info-label {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-300);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-photo-section {
        flex-direction: column;
        text-align: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-actions .topbar-btn {
        min-width: auto;
        width: 100%;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .requirements-list {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
    // Page-specific functionality
    function initPageFunctionality() {
        // Initialize any page-specific features here
        console.log('Edit Profile page initialized');
        
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
    
    // Photo upload with validation
    function previewPhoto(input) {
        const preview = document.getElementById('photo-preview');
        const errorDiv = document.getElementById('upload-error');
        
        // Hide previous errors
        errorDiv.style.display = 'none';
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size (1MB = 1048576 bytes)
            if (file.size > 1048576) {
                errorDiv.innerHTML = '<i data-feather="alert-circle" width="14" height="14"></i> File terlalu besar! Maksimal ukuran file adalah 1MB.';
                errorDiv.style.display = 'block';
                input.value = ''; // Clear the input
                feather.replace();
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                errorDiv.innerHTML = '<i data-feather="alert-circle" width="14" height="14"></i> Format file tidak didukung! Gunakan JPG, PNG, atau GIF.';
                errorDiv.style.display = 'block';
                input.value = ''; // Clear the input
                feather.replace();
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview" class="profile-photo-preview">`;
            };
            
            reader.readAsDataURL(file);
        }
    }
    
    // Phone number validation
    function validatePhoneNumber(input) {
        const phoneValue = input.value.replace(/\D/g, ''); // Remove non-digits
        const validationDiv = document.getElementById('phone-validation');
        
        if (phoneValue.length === 0) {
            validationDiv.style.display = 'none';
            return;
        }
        
        // Check if it's a valid WhatsApp format (country code + number)
        if (phoneValue.length >= 10 && phoneValue.length <= 15) {
            // Check if starts with common country codes
            const commonCodes = ['62', '1', '44', '91', '86', '81', '33', '49', '39', '34'];
            const startsWithValidCode = commonCodes.some(code => phoneValue.startsWith(code));
            
            if (startsWithValidCode) {
                validationDiv.innerHTML = '<i data-feather="check-circle" width="14" height="14"></i> Format nomor valid untuk WhatsApp';
                validationDiv.className = 'validation-message success';
                validationDiv.style.display = 'block';
            } else {
                validationDiv.innerHTML = '<i data-feather="alert-circle" width="14" height="14"></i> Pastikan nomor dimulai dengan kode negara (contoh: 62 untuk Indonesia)';
                validationDiv.className = 'validation-message error';
                validationDiv.style.display = 'block';
            }
        } else {
            validationDiv.innerHTML = '<i data-feather="alert-circle" width="14" height="14"></i> Nomor terlalu pendek atau terlalu panjang';
            validationDiv.className = 'validation-message error';
            validationDiv.style.display = 'block';
        }
        
        feather.replace();
    }
    
    // Password strength checker
    function checkPasswordStrength(input) {
        const password = input.value;
        const strengthDiv = document.getElementById('password-strength');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');
        
        if (password.length === 0) {
            strengthDiv.style.display = 'none';
            updatePasswordRequirements(password);
            return;
        }
        
        strengthDiv.style.display = 'block';
        
        let score = 0;
        let feedback = '';
        
        // Length check
        if (password.length >= 8) score += 1;
        
        // Uppercase check
        if (/[A-Z]/.test(password)) score += 1;
        
        // Lowercase check
        if (/[a-z]/.test(password)) score += 1;
        
        // Number check
        if (/[0-9]/.test(password)) score += 1;
        
        // Symbol check
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) score += 1;
        
        // Update strength bar and text
        const percentage = (score / 5) * 100;
        strengthFill.style.width = percentage + '%';
        
        if (score <= 1) {
            strengthFill.style.background = '#ef4444';
            feedback = 'Sangat Lemah';
            strengthText.style.color = '#ef4444';
        } else if (score <= 2) {
            strengthFill.style.background = '#f97316';
            feedback = 'Lemah';
            strengthText.style.color = '#f97316';
        } else if (score <= 3) {
            strengthFill.style.background = '#eab308';
            feedback = 'Sedang';
            strengthText.style.color = '#eab308';
        } else if (score <= 4) {
            strengthFill.style.background = '#22c55e';
            feedback = 'Kuat';
            strengthText.style.color = '#22c55e';
        } else {
            strengthFill.style.background = '#16a34a';
            feedback = 'Sangat Kuat';
            strengthText.style.color = '#16a34a';
        }
        
        strengthText.textContent = feedback;
        updatePasswordRequirements(password);
    }
    
    // Update password requirements indicators
    function updatePasswordRequirements(password) {
        const requirements = [
            { id: 'req-length', test: password.length >= 8 },
            { id: 'req-uppercase', test: /[A-Z]/.test(password) },
            { id: 'req-lowercase', test: /[a-z]/.test(password) },
            { id: 'req-number', test: /[0-9]/.test(password) },
            { id: 'req-symbol', test: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password) }
        ];
        
        requirements.forEach(req => {
            const element = document.getElementById(req.id);
            const icon = element.querySelector('i');
            
            if (req.test) {
                element.classList.add('met');
                icon.setAttribute('data-feather', 'check');
            } else {
                element.classList.remove('met');
                icon.setAttribute('data-feather', 'x');
            }
        });
        
        feather.replace();
    }
    
    // Password match checker
    function checkPasswordMatch() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const matchDiv = document.getElementById('password-match');
        
        if (confirmPassword.length === 0) {
            matchDiv.style.display = 'none';
            return;
        }
        
        if (newPassword === confirmPassword) {
            matchDiv.innerHTML = '<i data-feather="check-circle" width="14" height="14"></i> Password cocok';
            matchDiv.className = 'validation-message success';
            matchDiv.style.display = 'block';
        } else {
            matchDiv.innerHTML = '<i data-feather="x-circle" width="14" height="14"></i> Password tidak cocok';
            matchDiv.className = 'validation-message error';
            matchDiv.style.display = 'block';
        }
        
        feather.replace();
    }
    
    // Toggle password visibility
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const button = input.parentElement.querySelector('.password-toggle');
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-feather', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-feather', 'eye');
        }
        
        feather.replace();
    }
    
    // Copy to clipboard function
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Show success feedback
            const notification = document.createElement('div');
            notification.className = 'copy-notification';
            notification.innerHTML = '<i data-feather="check" width="16" height="16"></i> Copied to clipboard!';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--success);
                color: white;
                padding: 12px 16px;
                border-radius: 8px;
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            
            document.body.appendChild(notification);
            feather.replace();
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
        });
    }
    
    function previewPhoto(input) {
        const preview = document.getElementById('photo-preview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview" class="profile-photo-preview">`;
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>