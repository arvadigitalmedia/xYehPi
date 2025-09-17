<?php
/**
 * EPIC Hub Member Profile Content
 * Konten halaman edit profil member
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include form fields helper
require_once EPIC_ROOT . '/form-fields-helper.php';

// Get dynamic form fields for profile
$profile_fields = get_form_fields('profile');
$profile_field_values = get_user_form_field_values($user['id'], 'profile');

// Data sudah disiapkan di profile.php
?>

<!-- Profile Edit Card - Welcome Style -->
<div class="profile-edit-section">
    <div class="profile-edit-card-with-photo">
        <div class="profile-photo-container">
            <div class="profile-photo">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= epic_url('uploads/profiles/' . $user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>" class="user-photo">
                <?php else: ?>
                    <div class="user-photo-placeholder">
                        <i data-feather="user" width="48" height="48"></i>
                        <span>No Photo</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-main-content">
            <div class="profile-header-new">
                <div class="profile-text-content">
                    <h1 class="profile-title-new">
                        Edit Profil - <?= htmlspecialchars($user['name']) ?>
                    </h1>
                    <div class="profile-badge-new">
                        <?php 
                        $level_badges = [
                            'free' => ['text' => 'Free Account', 'class' => 'pill-info'],
                            'epic' => ['text' => 'EPIC Account', 'class' => 'pill-success'],
                            'epis' => ['text' => 'EPIS Account', 'class' => 'pill-warning']
                        ];
                        $badge = $level_badges[$access_level] ?? ['text' => 'Member', 'class' => 'pill-info'];
                        ?>
                        <span class="<?= $badge['class'] ?>"><?= $badge['text'] ?></span>
                    </div>
                    <p class="profile-description-new">
                        <?php if ($completion_percentage < 100): ?>
                            Profil Anda <?= $completion_percentage ?>% lengkap. <?= $completed_fields ?> dari <?= count($completion_fields) ?> field telah diisi.
                        <?php else: ?>
                            Profil Anda sudah lengkap! Semua informasi telah diisi dengan baik.
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="profile-actions-new">
                    <div class="completion-circle-small">
                        <svg class="progress-ring-small" width="60" height="60">
                            <circle class="progress-ring-bg" cx="30" cy="30" r="26" stroke-width="4" fill="none"></circle>
                            <circle class="progress-ring-fill" cx="30" cy="30" r="26" stroke-width="4" fill="none"
                                    stroke-dasharray="<?= 2 * pi() * 26 ?>" 
                                    stroke-dashoffset="<?= 2 * pi() * 26 * (1 - $completion_percentage / 100) ?>"></circle>
                        </svg>
                        <div class="progress-text-small">
                            <div class="progress-percentage-small"><?= $completion_percentage ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Completion Tips -->
            <?php if ($completion_percentage < 100): ?>
                <div class="profile-completion-tips">
                    <div class="tips-header">
                        <i data-feather="info" width="16" height="16"></i>
                        <span>Tips Lengkapi Profil</span>
                    </div>
                    <div class="tips-list-inline">
                        <?php if (empty($user['name'])): ?>
                            <div class="tip-item">
                                <i data-feather="user" width="12" height="12"></i>
                                <span>Nama lengkap</span>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($user['phone'])): ?>
                            <div class="tip-item">
                                <i data-feather="phone" width="12" height="12"></i>
                                <span>Nomor telepon</span>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($user['avatar'])): ?>
                            <div class="tip-item">
                                <i data-feather="camera" width="12" height="12"></i>
                                <span>Foto profil</span>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($profile['bio'])): ?>
                            <div class="tip-item">
                                <i data-feather="edit-3" width="12" height="12"></i>
                                <span>Bio singkat</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="profile-completion-success">
                    <i data-feather="check-circle" width="16" height="16"></i>
                    <span>Profil Anda sudah lengkap dan terverifikasi!</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Profile Form Cards -->
<form method="POST" enctype="multipart/form-data" class="profile-form-cards">
    
    <!-- Profile Edit Options Grid -->
    <div class="profile-options-grid">
        
        <!-- Card Foto Profil -->
        <div class="profile-option-card">
            <div class="option-card-header">
                <div class="option-icon-container">
                    <div class="option-icon">
                        <i data-feather="camera" width="20" height="20"></i>
                    </div>
                </div>
                <div class="option-content">
                    <div class="option-title">Foto Profil</div>
                    <div class="option-description">Upload dan kelola foto profil Anda</div>
                </div>
            </div>
            
            <div class="option-card-body">
                <div class="photo-upload-section">
                    <input type="file" name="avatar" id="avatar" 
                           accept="image/*" style="display: none;" onchange="previewPhoto(this)">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('avatar').click()">
                        <i data-feather="upload" width="14" height="14"></i>
                        Ganti Foto
                    </button>
                    <div class="form-help-small">JPG, PNG, GIF. Max 2MB</div>
                </div>
            </div>
        </div>
        
        <!-- Card Informasi Dasar -->
        <div class="profile-option-card">
            <div class="option-card-header">
                <div class="option-icon-container">
                    <div class="option-icon">
                        <i data-feather="user" width="20" height="20"></i>
                    </div>
                </div>
                <div class="option-content">
                    <div class="option-title">Informasi Dasar</div>
                    <div class="option-description">Data pribadi dan informasi akun</div>
                </div>
            </div>
            
            <div class="option-card-body">
                <div class="form-group-compact">
                    <label class="form-label-compact required">Nama Lengkap</label>
                    <input type="text" name="name" class="form-input-compact" 
                           value="<?= htmlspecialchars($user['name']) ?>" 
                           placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="form-group-compact">
                    <label class="form-label-compact">Email</label>
                    <input type="email" class="form-input-compact" 
                           value="<?= htmlspecialchars($user['email']) ?>" 
                           readonly disabled>
                </div>
                
                <div class="form-group-compact">
                    <label class="form-label-compact">Nomor Telepon</label>
                    <input type="tel" name="phone" class="form-input-compact" 
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                           placeholder="+62 812 3456 7890">
                </div>
                
                <div class="form-group-compact">
                    <label class="form-label-compact">Bio</label>
                    <textarea name="bio" class="form-textarea-compact" rows="3" 
                              placeholder="Ceritakan tentang diri Anda..." maxlength="500"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Dynamic Profile Fields -->
        <?php if (!empty($profile_fields)): ?>
            <div class="profile-option-card">
                <div class="option-card-header">
                    <div class="option-icon-container">
                        <div class="option-icon">
                            <i data-feather="edit-3" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="option-content">
                        <div class="option-title">Informasi Tambahan</div>
                        <div class="option-description">Field tambahan untuk melengkapi profil</div>
                    </div>
                </div>
                
                <div class="option-card-body">
                    <?php foreach ($profile_fields as $field): ?>
                        <div class="form-group-compact">
                            <label class="form-label-compact <?= $field['is_required'] ? 'required' : '' ?>">
                                <?= htmlspecialchars($field['label']) ?>
                            </label>
                            <?php 
                            $field_value = $profile_field_values[$field['name']]['value'] ?? '';
                            echo render_profile_field($field, $field_value);
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Card Social Media (EPIC/EPIS only) -->
        <?php if (in_array($access_level, ['epic', 'epis'])): ?>
            <div class="profile-option-card">
                <div class="option-card-header">
                    <div class="option-icon-container">
                        <div class="option-icon">
                            <i data-feather="share-2" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="option-content">
                        <div class="option-title">Social Media</div>
                        <div class="option-description">Kelola akun media sosial Anda</div>
                        <span class="option-badge">Premium</span>
                    </div>
                </div>
                
                <div class="option-card-body">
                    <div class="form-group-compact">
                        <label class="form-label-compact">Website</label>
                        <input type="url" name="website" class="form-input-compact" 
                               value="<?= htmlspecialchars($social_data['website']) ?>" 
                               placeholder="https://website-anda.com">
                    </div>
                    
                    <div class="form-row-compact">
                        <div class="form-group-compact">
                            <label class="form-label-compact">Facebook</label>
                            <input type="text" name="facebook" class="form-input-compact" 
                                   value="<?= htmlspecialchars($social_data['facebook']) ?>" 
                                   placeholder="facebook.com/username">
                        </div>
                        
                        <div class="form-group-compact">
                            <label class="form-label-compact">Instagram</label>
                            <input type="text" name="instagram" class="form-input-compact" 
                                   value="<?= htmlspecialchars($social_data['instagram']) ?>" 
                                   placeholder="@username">
                        </div>
                    </div>
                    
                    <div class="form-row-compact">
                        <div class="form-group-compact">
                            <label class="form-label-compact">Twitter</label>
                            <input type="text" name="twitter" class="form-input-compact" 
                                   value="<?= htmlspecialchars($social_data['twitter']) ?>" 
                                   placeholder="@username">
                        </div>
                        
                        <div class="form-group-compact">
                            <label class="form-label-compact">LinkedIn</label>
                            <input type="text" name="linkedin" class="form-input-compact" 
                                   value="<?= htmlspecialchars($social_data['linkedin']) ?>" 
                                   placeholder="linkedin.com/in/username">
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Card Informasi Akun -->
        <div class="profile-option-card">
            <div class="option-card-header">
                <div class="option-icon-container">
                    <div class="option-icon">
                        <i data-feather="info" width="20" height="20"></i>
                    </div>
                </div>
                <div class="option-content">
                    <div class="option-title">Informasi Akun</div>
                    <div class="option-description">Status dan detail akun Anda</div>
                </div>
            </div>
            
            <div class="option-card-body">
                <div class="info-grid-compact">
                    <div class="info-item-compact">
                        <div class="info-label-compact">Status</div>
                        <div class="info-value-compact">
                            <span class="badge-compact <?= $user['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item-compact">
                        <div class="info-label-compact">Member Sejak</div>
                        <div class="info-value-compact"><?= date('d M Y', strtotime($user['created_at'])) ?></div>
                    </div>
                    
                    <div class="info-item-compact">
                        <div class="info-label-compact">Last Login</div>
                        <div class="info-value-compact">
                            <?= $user['last_login_at'] ? date('d M Y', strtotime($user['last_login_at'])) : 'Belum login' ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($user['referral_code'])): ?>
                        <div class="info-item-compact">
                            <div class="info-label-compact">Kode Referral</div>
                            <div class="info-value-compact">
                                <code class="referral-code-compact"><?= htmlspecialchars($user['referral_code']) ?></code>
                                <button type="button" class="copy-btn-compact" 
                                        onclick="copyToClipboard('<?= htmlspecialchars($user['referral_code']) ?>')">
                                    <i data-feather="copy" width="12" height="12"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Card Ubah Password -->
        <div class="profile-option-card">
            <div class="option-card-header">
                <div class="option-icon-container">
                    <div class="option-icon">
                        <i data-feather="lock" width="20" height="20"></i>
                    </div>
                </div>
                <div class="option-content">
                    <div class="option-title">Ubah Password</div>
                    <div class="option-description">Kelola keamanan akun Anda</div>
                    <span class="option-badge security">Security</span>
                </div>
            </div>
            
            <div class="option-card-body">
                <div class="security-notice-compact">
                    <i data-feather="shield" width="16" height="16"></i>
                    <span>Gunakan password yang kuat dan unik</span>
                </div>
                
                <div class="form-group-compact">
                    <label class="form-label-compact required">Password Saat Ini</label>
                    <input type="password" name="current_password" class="form-input-compact" 
                           placeholder="Password saat ini" required>
                </div>
                
                <div class="form-row-compact">
                    <div class="form-group-compact">
                        <label class="form-label-compact required">Password Baru</label>
                        <input type="password" name="new_password" class="form-input-compact" 
                               placeholder="Password baru" required minlength="8">
                    </div>
                    
                    <div class="form-group-compact">
                        <label class="form-label-compact required">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="form-input-compact" 
                               placeholder="Konfirmasi password" required>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="profile-form-actions">
        <button type="submit" class="btn btn-primary">
            <i data-feather="save" width="16" height="16"></i>
            Simpan Perubahan
        </button>
        <a href="<?= epic_url('dashboard/member') ?>" class="btn btn-secondary">
            <i data-feather="arrow-left" width="16" height="16"></i>
            Kembali
        </a>
    </div>
</form>