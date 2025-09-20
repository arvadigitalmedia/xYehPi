<?php
/**
 * EPIC Hub Admin Settings Content
 * Konten halaman settings untuk layout global
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
        <a href="<?= epic_url('admin/settings/general') ?>" class="settings-nav-item active">
            <i data-feather="globe" class="settings-nav-icon"></i>
            <span>General</span>
        </a>
        <a href="<?= epic_url('admin/settings/mailketing') ?>" class="settings-nav-item">
            <i data-feather="mail" class="settings-nav-icon"></i>
            <span>Mailketing</span>
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

<!-- Settings Form -->
<form method="POST" action="<?= epic_url('admin/settings/general') ?>" enctype="multipart/form-data" class="settings-form">
    <!-- EPIC Referral System Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="users" class="settings-card-icon"></i>
                EPIC Referral System
            </h3>
            <p class="settings-card-description">
                Konfigurasi sistem referral untuk EPIC Account (Premium Members)
            </p>
        </div>
        
        <div class="settings-card-body">
            <!-- Default Sponsor IDs -->
            <div class="form-group">
                <label class="form-label" for="default_sponsor_ids">
                    ID Sponsor Default
                    <span class="form-label-required">*</span>
                </label>
                <input type="text" 
                       id="default_sponsor_ids" 
                       name="default_sponsor_ids" 
                       class="form-input" 
                       placeholder="1,2,3,4,5" 
                       value="<?= htmlspecialchars($settings['default_sponsor_ids'] ?? '1') ?>">
                <div class="form-help">
                    ID Sponsor yang akan diacak jika pengunjung mengakses registrasi tanpa link referral. 
                    Pisahkan dengan koma untuk multiple sponsor. Contoh: 1,2,3,4,5
                </div>
            </div>
            
            <!-- Require Referral for Registration -->
            <div class="form-group">
                <div class="form-checkbox-group">
                    <input type="checkbox" 
                           id="require_referral" 
                           name="require_referral" 
                           value="1" 
                           class="form-checkbox"
                           <?= ($settings['require_referral'] ?? '0') == '1' ? 'checked' : '' ?>>
                    <label for="require_referral" class="form-checkbox-label">
                        Wajibkan Link Referral untuk Registrasi
                    </label>
                </div>
                <div class="form-help">
                    Jika diaktifkan, pengunjung hanya dapat registrasi melalui link referral atau harus memasukkan kode referral.
                </div>
            </div>
            
            <!-- Show Referral Input Form -->
            <div class="form-group">
                <div class="form-checkbox-group">
                    <input type="checkbox" 
                           id="show_referral_input" 
                           name="show_referral_input" 
                           value="1" 
                           class="form-checkbox"
                           <?= ($settings['show_referral_input'] ?? '1') == '1' ? 'checked' : '' ?>>
                    <label for="show_referral_input" class="form-checkbox-label">
                        Tampilkan Form Input Kode Referral
                    </label>
                </div>
                <div class="form-help">
                    Munculkan form input kode referral jika pengunjung datang tanpa link referral.
                </div>
            </div>
            
            <!-- EPIC Account Only -->
            <div class="form-group">
                <div class="form-checkbox-group">
                    <input type="checkbox" 
                           id="epic_account_only" 
                           name="epic_account_only" 
                           value="1" 
                           class="form-checkbox"
                           <?= ($settings['epic_account_only'] ?? '1') == '1' ? 'checked' : '' ?>>
                    <label for="epic_account_only" class="form-checkbox-label">
                        Sistem Referral Khusus EPIC Account
                    </label>
                </div>
                <div class="form-help">
                    Sistem referral hanya berlaku untuk member dengan status EPIC Account (Premium).
                </div>
            </div>
        </div>
    </div>
    
    <!-- Website Settings Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="globe" class="settings-card-icon"></i>
                Website Settings
            </h3>
            <p class="settings-card-description">
                Pengaturan dasar website dan branding
            </p>
        </div>
        
        <div class="settings-card-body">
            <!-- Site Name -->
            <div class="form-group">
                <label class="form-label" for="site_name">
                    Nama Website
                    <span class="form-label-required">*</span>
                </label>
                <input type="text" 
                       id="site_name" 
                       name="site_name" 
                       class="form-input" 
                       placeholder="EPIC Hub" 
                       value="<?= htmlspecialchars($settings['site_name'] ?? 'EPIC Hub') ?>">
            </div>
            
            <!-- Site Description -->
            <div class="form-group">
                <label class="form-label" for="site_description">
                    Deskripsi Website
                </label>
                <textarea id="site_description" 
                          name="site_description" 
                          class="form-textarea" 
                          rows="3" 
                          placeholder="Platform affiliate marketing modern untuk bisnis yang berkembang"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
            </div>
            
            <!-- Admin Email -->
            <div class="form-group">
                <label class="form-label" for="admin_email">
                    Email Administrator
                </label>
                <input type="email" 
                       id="admin_email" 
                       name="admin_email" 
                       class="form-input" 
                       placeholder="admin@epichub.com" 
                       value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>">
            </div>
            
            <!-- Timezone -->
            <div class="form-group">
                <label class="form-label" for="timezone">
                    Zona Waktu
                </label>
                <select id="timezone" name="timezone" class="form-input">
                    <option value="Asia/Jakarta" <?= ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : '' ?>>Asia/Jakarta (WIB)</option>
                    <option value="Asia/Makassar" <?= ($settings['timezone'] ?? '') == 'Asia/Makassar' ? 'selected' : '' ?>>Asia/Makassar (WITA)</option>
                    <option value="Asia/Jayapura" <?= ($settings['timezone'] ?? '') == 'Asia/Jayapura' ? 'selected' : '' ?>>Asia/Jayapura (WIT)</option>
                </select>
            </div>
            
            <!-- Currency -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="currency">
                        Mata Uang
                    </label>
                    <select id="currency" name="currency" class="form-input">
                        <option value="IDR" <?= ($settings['currency'] ?? 'IDR') == 'IDR' ? 'selected' : '' ?>>Indonesian Rupiah (IDR)</option>
                        <option value="USD" <?= ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>US Dollar (USD)</option>
                        <option value="EUR" <?= ($settings['currency'] ?? '') == 'EUR' ? 'selected' : '' ?>>Euro (EUR)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="currency_symbol">
                        Simbol Mata Uang
                    </label>
                    <input type="text" 
                           id="currency_symbol" 
                           name="currency_symbol" 
                           class="form-input" 
                           placeholder="Rp" 
                           value="<?= htmlspecialchars($settings['currency_symbol'] ?? 'Rp') ?>">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Logo & Favicon Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="image" class="settings-card-icon"></i>
                Logo & Favicon
            </h3>
            <p class="settings-card-description">
                Upload dan kelola logo website serta favicon untuk branding yang konsisten
            </p>
        </div>
        
        <div class="settings-card-body">
            <!-- Website Logo -->
            <div class="form-group">
                <label class="form-label">Logo Website</label>
                <div class="logo-upload-container">
                    <div class="logo-preview-wrapper">
                        <div class="logo-preview" id="logo-preview">
                            <?php 
                            $current_logo = $settings['site_logo'] ?? '';
                            if ($current_logo && file_exists(__DIR__ . '/../../../../uploads/logos/' . $current_logo)): 
                            ?>
                                <img src="<?= epic_url('uploads/logos/' . $current_logo) ?>" alt="Current Logo" class="current-logo">
                            <?php else: ?>
                                <div class="logo-placeholder">
                                    <i data-feather="image" width="48" height="48"></i>
                                    <span>Logo Website</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="logo-actions">
                            <label for="logo_upload" class="btn btn-secondary btn-sm">
                                <i data-feather="upload" width="16" height="16"></i>
                                Upload Logo
                            </label>
                            <?php if ($current_logo): ?>
                                <button type="button" class="btn btn-danger btn-sm" onclick="resetLogo('logo')">
                                    <i data-feather="trash-2" width="16" height="16"></i>
                                    Hapus
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <input type="file" 
                           id="logo_upload" 
                           name="site_logo" 
                           accept=".png,.jpg,.jpeg,.gif,.svg" 
                           class="file-input" 
                           onchange="previewImage(this, 'logo-preview')">
                    <div class="form-help">
                        <strong>Format yang didukung:</strong> PNG, JPG, JPEG, GIF, SVG<br>
                        <strong>Ukuran yang disarankan:</strong> 200x60px atau rasio 10:3 (landscape)<br>
                        <strong>Ukuran file maksimal:</strong> 2MB
                    </div>
                </div>
            </div>
            
            <!-- Favicon -->
            <div class="form-group">
                <label class="form-label">Favicon</label>
                <div class="favicon-upload-container">
                    <div class="favicon-preview-wrapper">
                        <div class="favicon-preview" id="favicon-preview">
                            <?php 
                            $current_favicon = $settings['site_favicon'] ?? '';
                            if ($current_favicon && file_exists(__DIR__ . '/../../../../uploads/logos/' . $current_favicon)): 
                            ?>
                                <img src="<?= epic_url('uploads/logos/' . $current_favicon) ?>" alt="Current Favicon" class="current-favicon">
                            <?php else: ?>
                                <div class="favicon-placeholder">
                                    <i data-feather="bookmark" width="24" height="24"></i>
                                    <span>Favicon</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="favicon-actions">
                            <label for="favicon_upload" class="btn btn-secondary btn-sm">
                                <i data-feather="upload" width="16" height="16"></i>
                                Upload Favicon
                            </label>
                            <?php if ($current_favicon): ?>
                                <button type="button" class="btn btn-danger btn-sm" onclick="resetLogo('favicon')">
                                    <i data-feather="trash-2" width="16" height="16"></i>
                                    Hapus
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <input type="file" 
                           id="favicon_upload" 
                           name="site_favicon" 
                           accept=".ico,.png,.jpg,.jpeg,.gif" 
                           class="file-input" 
                           onchange="previewImage(this, 'favicon-preview')">
                    <div class="form-help">
                        <strong>Format yang didukung:</strong> ICO, PNG, JPG, JPEG, GIF<br>
                        <strong>Ukuran yang disarankan:</strong> 32x32px atau 16x16px (square)<br>
                        <strong>Ukuran file maksimal:</strong> 1MB
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Settings Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="settings" class="settings-card-icon"></i>
                System Settings
            </h3>
            <p class="settings-card-description">
                Pengaturan sistem dan keamanan
            </p>
        </div>
        
        <div class="settings-card-body">
            <!-- Referral Commission -->
            <div class="form-group">
                <label class="form-label" for="referral_commission">
                    Komisi Referral (%)
                </label>
                <input type="number" 
                       id="referral_commission" 
                       name="referral_commission" 
                       class="form-input" 
                       placeholder="10" 
                       min="0" 
                       max="100" 
                       value="<?= htmlspecialchars($settings['referral_commission'] ?? '10') ?>">
                <div class="form-help">
                    Persentase komisi yang diberikan kepada referrer dari setiap transaksi.
                </div>
            </div>
            
            <!-- Minimum Payout -->
            <div class="form-group">
                <label class="form-label" for="min_payout">
                    Minimum Payout
                </label>
                <input type="number" 
                       id="min_payout" 
                       name="min_payout" 
                       class="form-input" 
                       placeholder="100000" 
                       min="0" 
                       value="<?= htmlspecialchars($settings['min_payout'] ?? '100000') ?>">
                <div class="form-help">
                    Jumlah minimum komisi yang dapat dicairkan oleh member.
                </div>
            </div>
            
            <!-- System Options -->
            <div class="form-group">
                <label class="form-label">Opsi Sistem</label>
                
                <div class="form-checkbox-group">
                    <input type="checkbox" 
                           id="maintenance_mode" 
                           name="maintenance_mode" 
                           value="1" 
                           class="form-checkbox"
                           <?= ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' ?>>
                    <label for="maintenance_mode" class="form-checkbox-label">
                        Mode Maintenance
                    </label>
                </div>
                
                <div class="form-checkbox-group">
                    <input type="checkbox" 
                           id="registration_enabled" 
                           name="registration_enabled" 
                           value="1" 
                           class="form-checkbox"
                           <?= ($settings['registration_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                    <label for="registration_enabled" class="form-checkbox-label">
                        Registrasi Diaktifkan
                    </label>
                </div>
                
                <div class="form-checkbox-group">
                    <input type="checkbox" 
                           id="email_verification" 
                           name="email_verification" 
                           value="1" 
                           class="form-checkbox"
                           <?= ($settings['email_verification'] ?? '1') == '1' ? 'checked' : '' ?>>
                    <label for="email_verification" class="form-checkbox-label">
                        Verifikasi Email Wajib
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Save Button -->
    <div class="settings-actions">
        <button type="submit" name="save_settings" class="btn btn-primary">
            <i data-feather="save" width="16" height="16"></i>
            Simpan Pengaturan
        </button>
        <a href="<?= epic_url('admin') ?>" class="btn btn-secondary">
            <i data-feather="arrow-left" width="16" height="16"></i>
            Kembali ke Dashboard
        </a>
    </div>
</form>

<style>
/* Settings specific styles */
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

.settings-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}

.settings-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-2xl);
    overflow: hidden;
}

.settings-card-header {
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-700);
    background: var(--surface-3);
}

.settings-card-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-2) 0;
}

.settings-card-icon {
    width: 20px;
    height: 20px;
    color: var(--gold-400);
}

.settings-card-description {
    color: var(--ink-400);
    font-size: var(--font-size-sm);
    margin: 0;
}

.settings-card-body {
    padding: var(--spacing-6);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
}

.form-checkbox-group {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-3);
}

.form-checkbox {
    width: 18px;
    height: 18px;
    margin-top: 2px;
}

.form-checkbox-label {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-200);
    cursor: pointer;
    line-height: 1.4;
}

.settings-actions {
    display: flex;
    gap: var(--spacing-4);
    padding: var(--spacing-6) 0;
    border-top: 1px solid var(--ink-700);
}

/* Logo & Favicon Upload Styles */
.logo-upload-container,
.favicon-upload-container {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

.logo-preview-wrapper,
.favicon-preview-wrapper {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    background: var(--surface-3);
    border: 2px dashed var(--ink-600);
    border-radius: var(--radius-lg);
    transition: all var(--transition-normal);
}

.logo-preview-wrapper:hover,
.favicon-preview-wrapper:hover {
    border-color: var(--gold-400);
    background: var(--surface-4);
}

.logo-preview,
.favicon-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 120px;
    min-height: 80px;
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.favicon-preview {
    min-width: 60px;
    min-height: 60px;
}

.logo-preview img,
.favicon-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.current-logo {
    max-height: 60px;
}

.current-favicon {
    max-height: 32px;
    max-width: 32px;
}

.logo-placeholder,
.favicon-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-2);
    color: var(--ink-400);
    font-size: var(--font-size-sm);
    text-align: center;
    padding: var(--spacing-4);
}

.logo-actions,
.favicon-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
    flex: 1;
}

.file-input {
    display: none;
}

.btn-sm {
    padding: var(--spacing-2) var(--spacing-3);
    font-size: var(--font-size-sm);
    border-radius: var(--radius-md);
}

/* Upload progress and validation styles */
.upload-progress {
    width: 100%;
    height: 4px;
    background: var(--surface-3);
    border-radius: var(--radius-sm);
    overflow: hidden;
    margin-top: var(--spacing-2);
}

.upload-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--gold-500), var(--gold-400));
    transition: width var(--transition-normal);
    width: 0%;
}

.upload-error {
    color: var(--red-400);
    font-size: var(--font-size-xs);
    margin-top: var(--spacing-1);
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
}

.upload-success {
    color: var(--green-400);
    font-size: var(--font-size-xs);
    margin-top: var(--spacing-1);
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
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
    
    .settings-actions {
        flex-direction: column;
    }
    
    /* Logo & Favicon responsive styles */
    .logo-preview-wrapper,
    .favicon-preview-wrapper {
        flex-direction: column;
        text-align: center;
    }
    
    .logo-actions,
    .favicon-actions {
        flex-direction: row;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .logo-preview,
    .favicon-preview {
        min-width: 100px;
        min-height: 60px;
    }
    
    .favicon-preview {
        min-width: 50px;
        min-height: 50px;
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
    
    // Form validation
    const form = document.querySelector('.settings-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
            }
        });
    }
    
    // Auto-save draft (optional)
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            // Save to localStorage as draft
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            localStorage.setItem('settings_draft', JSON.stringify(data));
        });
    });
});

// Image preview function
function previewImage(input, previewId) {
    const file = input.files[0];
    const previewContainer = document.getElementById(previewId);
    
    if (!file) {
        return;
    }
    
    // Validate file type
    const allowedTypes = {
        'logo-preview': ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/svg+xml'],
        'favicon-preview': ['image/x-icon', 'image/png', 'image/jpeg', 'image/jpg', 'image/gif']
    };
    
    const maxSizes = {
        'logo-preview': 2 * 1024 * 1024, // 2MB
        'favicon-preview': 1 * 1024 * 1024 // 1MB
    };
    
    // Remove previous error messages
    const existingError = input.parentNode.querySelector('.upload-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Validate file type
    if (!allowedTypes[previewId].includes(file.type)) {
        showUploadError(input, 'Format file tidak didukung. Gunakan format yang diizinkan.');
        input.value = '';
        return;
    }
    
    // Validate file size
    if (file.size > maxSizes[previewId]) {
        const maxSizeMB = maxSizes[previewId] / (1024 * 1024);
        showUploadError(input, `Ukuran file terlalu besar. Maksimal ${maxSizeMB}MB.`);
        input.value = '';
        return;
    }
    
    // Show upload progress
    showUploadProgress(input);
    
    // Create FileReader to preview image
    const reader = new FileReader();
    reader.onload = function(e) {
        // Clear preview container
        previewContainer.innerHTML = '';
        
        // Create image element
        const img = document.createElement('img');
        img.src = e.target.result;
        img.alt = 'Preview';
        img.className = previewId === 'logo-preview' ? 'current-logo' : 'current-favicon';
        
        // Add image to preview
        previewContainer.appendChild(img);
        
        // Hide progress and show success
        hideUploadProgress(input);
        showUploadSuccess(input, 'File berhasil dipilih dan siap diupload.');
        
        // Update action buttons
        updateActionButtons(input, previewId);
    };
    
    reader.onerror = function() {
        hideUploadProgress(input);
        showUploadError(input, 'Gagal membaca file. Silakan coba lagi.');
        input.value = '';
    };
    
    reader.readAsDataURL(file);
}

// Show upload error
function showUploadError(input, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'upload-error';
    errorDiv.innerHTML = `<i data-feather="alert-circle" width="12" height="12"></i> ${message}`;
    input.parentNode.appendChild(errorDiv);
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Show upload success
function showUploadSuccess(input, message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'upload-success';
    successDiv.innerHTML = `<i data-feather="check-circle" width="12" height="12"></i> ${message}`;
    input.parentNode.appendChild(successDiv);
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Auto-hide success message after 3 seconds
    setTimeout(() => {
        if (successDiv.parentNode) {
            successDiv.remove();
        }
    }, 3000);
}

// Show upload progress
function showUploadProgress(input) {
    const progressDiv = document.createElement('div');
    progressDiv.className = 'upload-progress';
    progressDiv.innerHTML = '<div class="upload-progress-bar"></div>';
    input.parentNode.appendChild(progressDiv);
    
    // Animate progress bar
    const progressBar = progressDiv.querySelector('.upload-progress-bar');
    let width = 0;
    const interval = setInterval(() => {
        width += 10;
        progressBar.style.width = width + '%';
        if (width >= 100) {
            clearInterval(interval);
        }
    }, 50);
}

// Hide upload progress
function hideUploadProgress(input) {
    const progress = input.parentNode.querySelector('.upload-progress');
    if (progress) {
        progress.remove();
    }
}

// Update action buttons after file selection
function updateActionButtons(input, previewId) {
    const wrapper = input.closest('.logo-preview-wrapper, .favicon-preview-wrapper');
    const actionsContainer = wrapper.querySelector('.logo-actions, .favicon-actions');
    
    // Check if delete button already exists
    let deleteBtn = actionsContainer.querySelector('.btn-danger');
    if (!deleteBtn) {
        deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-danger btn-sm';
        deleteBtn.innerHTML = '<i data-feather="trash-2" width="16" height="16"></i> Hapus';
        
        const type = previewId === 'logo-preview' ? 'logo' : 'favicon';
        deleteBtn.onclick = () => resetLogo(type);
        
        actionsContainer.appendChild(deleteBtn);
        
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
}

// Reset logo function
function resetLogo(type) {
    if (!confirm(`Apakah Anda yakin ingin menghapus ${type === 'logo' ? 'logo website' : 'favicon'}?`)) {
        return;
    }
    
    const input = document.getElementById(type === 'logo' ? 'logo_upload' : 'favicon_upload');
    const previewId = type === 'logo' ? 'logo-preview' : 'favicon-preview';
    const previewContainer = document.getElementById(previewId);
    
    // Clear file input
    input.value = '';
    
    // Reset preview to placeholder
    const placeholderClass = type === 'logo' ? 'logo-placeholder' : 'favicon-placeholder';
    const iconName = type === 'logo' ? 'image' : 'bookmark';
    const iconSize = type === 'logo' ? '48' : '24';
    const text = type === 'logo' ? 'Logo Website' : 'Favicon';
    
    previewContainer.innerHTML = `
        <div class="${placeholderClass}">
            <i data-feather="${iconName}" width="${iconSize}" height="${iconSize}"></i>
            <span>${text}</span>
        </div>
    `;
    
    // Remove delete button
    const wrapper = input.closest('.logo-preview-wrapper, .favicon-preview-wrapper');
    const deleteBtn = wrapper.querySelector('.btn-danger');
    if (deleteBtn) {
        deleteBtn.remove();
    }
    
    // Remove any error/success messages
    const messages = input.parentNode.querySelectorAll('.upload-error, .upload-success');
    messages.forEach(msg => msg.remove());
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Add hidden input to mark for deletion
    const deleteInput = document.createElement('input');
    deleteInput.type = 'hidden';
    deleteInput.name = `delete_${type === 'logo' ? 'site_logo' : 'site_favicon'}`;
    deleteInput.value = '1';
    input.parentNode.appendChild(deleteInput);
}
</script>