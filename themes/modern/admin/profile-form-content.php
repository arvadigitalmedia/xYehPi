<?php
/**
 * Profile Form Content for AJAX Modal
 * Contains only the form content without full page layout
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}
?>

<form id="profile-form" class="profile-edit-form" method="POST" action="<?= epic_url('admin/profile/edit') ?>" enctype="multipart/form-data">
    <!-- Personal Information -->
    <div class="form-section">
        <h3 class="form-section-title">
            <i data-feather="user" width="20" height="20"></i>
            Informasi Personal
        </h3>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="name" class="form-label">Nama Lengkap *</label>
                <input type="text" id="name" name="name" class="form-input" 
                       value="<?= htmlspecialchars($data['user']['name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email *</label>
                <input type="email" id="email" name="email" class="form-input" 
                       value="<?= htmlspecialchars($data['user']['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone" class="form-label">Nomor WhatsApp</label>
                <div class="phone-input-container">
                    <select id="country_code" name="country_code" class="form-select country-code-select">
                        <option value="+62" <?= (strpos($data['user']['phone'] ?? '', '+62') === 0) ? 'selected' : '' ?>>🇮🇩 +62</option>
                        <option value="+1" <?= (strpos($data['user']['phone'] ?? '', '+1') === 0) ? 'selected' : '' ?>>🇺🇸 +1</option>
                        <option value="+44" <?= (strpos($data['user']['phone'] ?? '', '+44') === 0) ? 'selected' : '' ?>>🇬🇧 +44</option>
                        <option value="+91" <?= (strpos($data['user']['phone'] ?? '', '+91') === 0) ? 'selected' : '' ?>>🇮🇳 +91</option>
                        <option value="+86" <?= (strpos($data['user']['phone'] ?? '', '+86') === 0) ? 'selected' : '' ?>>🇨🇳 +86</option>
                        <option value="+81" <?= (strpos($data['user']['phone'] ?? '', '+81') === 0) ? 'selected' : '' ?>>🇯🇵 +81</option>
                        <option value="+82" <?= (strpos($data['user']['phone'] ?? '', '+82') === 0) ? 'selected' : '' ?>>🇰🇷 +82</option>
                        <option value="+65" <?= (strpos($data['user']['phone'] ?? '', '+65') === 0) ? 'selected' : '' ?>>🇸🇬 +65</option>
                        <option value="+60" <?= (strpos($data['user']['phone'] ?? '', '+60') === 0) ? 'selected' : '' ?>>🇲🇾 +60</option>
                        <option value="+66" <?= (strpos($data['user']['phone'] ?? '', '+66') === 0) ? 'selected' : '' ?>>🇹🇭 +66</option>
                        <option value="+84" <?= (strpos($data['user']['phone'] ?? '', '+84') === 0) ? 'selected' : '' ?>>🇻🇳 +84</option>
                        <option value="+63" <?= (strpos($data['user']['phone'] ?? '', '+63') === 0) ? 'selected' : '' ?>>🇵🇭 +63</option>
                    </select>
                    <input type="tel" id="phone" name="phone" class="form-input phone-number-input" 
                           value="<?php
                               $phone = $data['user']['phone'] ?? '';
                               // Remove any country code from the phone number for display
                               $cleanPhone = preg_replace('/^\+\d{1,4}/', '', $phone);
                               echo htmlspecialchars(ltrim($cleanPhone, '0'));
                           ?>" 
                           placeholder="858604373123">
                </div>
                <small class="form-help">Pilih kode negara dan masukkan nomor tanpa kode negara (contoh: 858604373123)</small>
                <div id="phone-validation-message" class="validation-message" style="display: none;"></div>
            </div>
        </div>
        
        <!-- Photo Upload Section -->
        <div class="form-group photo-upload-section">
            <label class="form-label">Foto Profil</label>
            <div class="photo-upload-container">
                <div class="photo-preview" id="profile-avatar-display">
                    <?php if (!empty($data['user']['profile_photo'])): ?>
                        <img src="<?= epic_url('uploads/profiles/' . $data['user']['profile_photo']) ?>" alt="Profile Photo" class="avatar-image">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <i data-feather="user" width="40" height="40"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="photo-upload-controls">
                    <input type="file" id="profile-photo" name="profile_photo" accept="image/*" class="photo-input" style="display: none;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('profile-photo').click()">
                        <i data-feather="camera" width="16" height="16"></i>
                        <?= !empty($data['user']['profile_photo']) ? 'Ganti Foto' : 'Upload Foto' ?>
                    </button>
                    <div class="form-help-detailed">
                        <small class="form-help-title">📋 Persyaratan Foto Profil:</small>
                        <ul class="form-help-list">
                            <li><strong>Format:</strong> JPG, PNG, GIF, atau WebP</li>
                            <li><strong>Ukuran File:</strong> Maksimal 1MB (1.024 KB)</li>
                            <li><strong>Rasio:</strong> 1:1 (persegi) untuk hasil terbaik</li>
                            <li><strong>Resolusi:</strong> Minimal 200x200px, disarankan 400x400px</li>
                            <li><strong>Tips:</strong> Gunakan foto dengan pencahayaan baik dan wajah terlihat jelas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Social Media Links -->
    <div class="form-section">
        <h3 class="form-section-title">
            <i data-feather="share-2" width="20" height="20"></i>
            Media Sosial
        </h3>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="social_facebook" class="form-label">Facebook</label>
                <input type="url" id="social_facebook" name="social_facebook" class="form-input" 
                       value="<?= htmlspecialchars($data['user']['social_facebook'] ?? '') ?>" 
                       placeholder="https://facebook.com/username">
            </div>
            
            <div class="form-group">
                <label for="social_instagram" class="form-label">Instagram</label>
                <input type="url" id="social_instagram" name="social_instagram" class="form-input" 
                       value="<?= htmlspecialchars($data['user']['social_instagram'] ?? '') ?>" 
                       placeholder="https://instagram.com/username">
            </div>
            
            <div class="form-group">
                <label for="social_tiktok" class="form-label">TikTok</label>
                <input type="url" id="social_tiktok" name="social_tiktok" class="form-input" 
                       value="<?= htmlspecialchars($data['user']['social_tiktok'] ?? '') ?>" 
                       placeholder="https://tiktok.com/@username">
            </div>
            
            <div class="form-group">
                <label for="social_youtube" class="form-label">YouTube</label>
                <input type="url" id="social_youtube" name="social_youtube" class="form-input" 
                       value="<?= htmlspecialchars($data['user']['social_youtube'] ?? '') ?>" 
                       placeholder="https://youtube.com/@username">
            </div>
        </div>
    </div>
    
    <!-- Affiliate System -->
    <div class="form-section">
        <h3 class="form-section-title">
            <i data-feather="link" width="20" height="20"></i>
            Sistem Affiliasi
        </h3>
        
        <div class="form-group">
            <label for="affiliate_link" class="form-label">Link Affiliasi</label>
            <div class="affiliate-link-display">
                <input type="text" id="affiliate_link" class="form-input" 
                       value="<?= epic_url('ref/' . ($data['user']['affiliate_code'] ?? str_pad($data['user']['id'], 6, '0', STR_PAD_LEFT))) ?>" 
                       readonly>
                <button type="button" class="btn btn-secondary btn-sm" onclick="copyAffiliateLink()">
                    <i data-feather="copy" width="16" height="16"></i>
                    Copy
                </button>
            </div>
        </div>
        
        <div class="form-group">
            <label for="affiliate_code" class="form-label">Kode Affiliasi</label>
            <input type="text" id="affiliate_code" name="affiliate_code" class="form-input" 
                   value="<?= htmlspecialchars($data['user']['affiliate_code'] ?? str_pad($data['user']['id'], 6, '0', STR_PAD_LEFT)) ?>" 
                   placeholder="Masukkan kode affiliasi custom">
            <small class="form-help">Kode unik untuk identifikasi affiliasi Anda (opsional, akan menggunakan ID member jika kosong)</small>
        </div>
    </div>
    
    <!-- Security -->
    <div class="form-section">
        <h3 class="form-section-title">
            <i data-feather="shield" width="20" height="20"></i>
            Keamanan
        </h3>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="password" class="form-label">Password Baru</label>
                <input type="password" id="password" name="password" class="form-input" 
                       placeholder="Kosongkan jika tidak ingin mengubah">
            </div>
            
            <div class="form-group">
                <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input" 
                       placeholder="Ulangi password baru">
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <button type="button" class="btn btn-secondary" onclick="showDashboard()">
            <i data-feather="arrow-left" width="16" height="16"></i>
            Kembali ke Dashboard
        </button>
        <button type="submit" class="btn btn-primary" id="save-button">
            <i data-feather="save" width="16" height="16"></i>
            Simpan Perubahan
        </button>
    </div>
</form>

<script>
    // Phone number formatting and validation
    const phoneInput = document.getElementById('phone');
    const countryCodeSelect = document.getElementById('country_code');
    const validationMessage = document.getElementById('phone-validation-message');
    
    // Phone number validation patterns for different countries
    const phonePatterns = {
        '+62': { pattern: /^8\d{8,11}$/, example: '858604373123', minLength: 9, maxLength: 12 }, // Indonesia
        '+1': { pattern: /^\d{10}$/, example: '2125551234', minLength: 10, maxLength: 10 }, // US/Canada
        '+44': { pattern: /^\d{10,11}$/, example: '7911123456', minLength: 10, maxLength: 11 }, // UK
        '+91': { pattern: /^[6-9]\d{9}$/, example: '9876543210', minLength: 10, maxLength: 10 }, // India
        '+86': { pattern: /^1[3-9]\d{9}$/, example: '13812345678', minLength: 11, maxLength: 11 }, // China
        '+81': { pattern: /^[7-9]\d{8,9}$/, example: '9012345678', minLength: 9, maxLength: 10 }, // Japan
        '+82': { pattern: /^1[0-9]\d{7,8}$/, example: '1012345678', minLength: 9, maxLength: 10 }, // South Korea
        '+65': { pattern: /^[89]\d{7}$/, example: '81234567', minLength: 8, maxLength: 8 }, // Singapore
        '+60': { pattern: /^1[0-9]\d{7,8}$/, example: '123456789', minLength: 8, maxLength: 10 }, // Malaysia
        '+66': { pattern: /^[689]\d{8}$/, example: '812345678', minLength: 9, maxLength: 9 }, // Thailand
        '+84': { pattern: /^[3-9]\d{8}$/, example: '912345678', minLength: 9, maxLength: 9 }, // Vietnam
        '+63': { pattern: /^9\d{9}$/, example: '9171234567', minLength: 10, maxLength: 10 } // Philippines
    };
    
    function showValidationMessage(message, type) {
        validationMessage.textContent = message;
        validationMessage.className = `validation-message ${type}`;
        validationMessage.style.display = 'flex';
    }
    
    function hideValidationMessage() {
        validationMessage.style.display = 'none';
    }
    
    function validatePhoneNumber() {
        const countryCode = countryCodeSelect.value;
        const phoneNumber = phoneInput.value.trim();
        
        if (!phoneNumber) {
            hideValidationMessage();
            return true;
        }
        
        const pattern = phonePatterns[countryCode];
        if (!pattern) {
            showValidationMessage('Kode negara tidak didukung', 'error');
            return false;
        }
        
        // Remove any non-digit characters
        const cleanNumber = phoneNumber.replace(/\D/g, '');
        
        if (cleanNumber.length < pattern.minLength) {
            showValidationMessage(`Nomor terlalu pendek. Minimal ${pattern.minLength} digit (contoh: ${pattern.example})`, 'error');
            return false;
        }
        
        if (cleanNumber.length > pattern.maxLength) {
            showValidationMessage(`Nomor terlalu panjang. Maksimal ${pattern.maxLength} digit (contoh: ${pattern.example})`, 'error');
            return false;
        }
        
        if (!pattern.pattern.test(cleanNumber)) {
            showValidationMessage(`Format nomor tidak valid untuk ${countryCode}. Contoh yang benar: ${pattern.example}`, 'error');
            return false;
        }
        
        showValidationMessage(`✓ Format nomor valid untuk ${countryCode}`, 'success');
        return true;
    }
    
    function updatePlaceholder() {
        const countryCode = countryCodeSelect.value;
        const pattern = phonePatterns[countryCode];
        if (pattern) {
            phoneInput.placeholder = pattern.example;
        }
    }
    
    if (phoneInput && countryCodeSelect) {
        // Update placeholder when country code changes
        countryCodeSelect.addEventListener('change', function() {
            updatePlaceholder();
            if (phoneInput.value.trim()) {
                validatePhoneNumber();
            }
        });
        
        // Format and validate phone input
        phoneInput.addEventListener('input', function(e) {
            // Remove any non-digit characters
            let value = e.target.value.replace(/\D/g, '');
            
            // Remove leading zeros for most countries (except some specific cases)
            const countryCode = countryCodeSelect.value;
            if (countryCode !== '+1' && value.startsWith('0')) {
                value = value.substring(1);
            }
            
            e.target.value = value;
            
            // Validate after a short delay to avoid too frequent validation
            clearTimeout(this.validationTimeout);
            this.validationTimeout = setTimeout(() => {
                validatePhoneNumber();
            }, 500);
        });
        
        // Validate on blur
        phoneInput.addEventListener('blur', validatePhoneNumber);
        
        // Set initial placeholder
        updatePlaceholder();
        
        // Validate existing value if any
        if (phoneInput.value.trim()) {
            setTimeout(validatePhoneNumber, 100);
        }
    }
    
    // Photo preview functionality
    document.getElementById('profile-photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validate file size (1MB = 1048576 bytes)
        if (file.size > 1048576) {
            const sizeMB = (file.size / 1048576).toFixed(2);
            alert(`❌ Ukuran file terlalu besar!\n\nUkuran file: ${sizeMB} MB\nMaksimal: 1 MB\n\nSilakan pilih foto dengan ukuran lebih kecil.`);
            this.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const isValidType = allowedTypes.some(type => 
            file.type === type || file.type === `image/${fileExtension}`
        );
        
        if (!isValidType) {
            alert(`❌ Format file tidak didukung!\n\nFormat file: ${file.type || 'Unknown'}\nFormat yang didukung: JPG, PNG, GIF, WebP\n\nSilakan pilih foto dengan format yang benar.`);
            this.value = '';
            return;
        }
        
        // Check image dimensions (optional)
        const img = new Image();
        img.onload = function() {
            const width = this.width;
            const height = this.height;
            
            // Show dimension info
            let dimensionInfo = '';
            if (width < 200 || height < 200) {
                dimensionInfo = `\n⚠️ Resolusi rendah: ${width}x${height}px\nDisarankan minimal 200x200px untuk kualitas terbaik.`;
            } else {
                dimensionInfo = `\n✅ Resolusi: ${width}x${height}px`;
            }
            
            showNotification(`📸 Preview foto siap!${dimensionInfo}\n\nKlik "Simpan Perubahan" untuk menyimpan.`, 'info', 4000);
        };
        
        // Show preview only (no upload)
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarDisplay = document.getElementById('profile-avatar-display');
            avatarDisplay.innerHTML = `<img src="${e.target.result}" alt="Profile Photo Preview" class="avatar-image">`;
            
            // Load image for dimension check
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
    
    // Update affiliate link when affiliate code changes
    document.getElementById('affiliate_code').addEventListener('input', function() {
        const code = this.value || '<?= str_pad($data["user"]["id"], 6, "0", STR_PAD_LEFT) ?>';
        const baseUrl = '<?= epic_url("ref/") ?>';
        document.getElementById('affiliate_link').value = baseUrl + code;
    });
    
    // Copy affiliate link function
    function copyAffiliateLink() {
        const affiliateLink = document.getElementById('affiliate_link');
        affiliateLink.select();
        affiliateLink.setSelectionRange(0, 99999);
        
        try {
            document.execCommand('copy');
            showNotification('Link affiliasi berhasil disalin!', 'success', 2000);
        } catch (err) {
            showNotification('Gagal menyalin link. Silakan copy manual.', 'error');
        }
    }
    
    // Notification system
    function showNotification(message, type = 'info', duration = 3000) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i data-feather="x" width="16" height="16"></i>
                </button>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        feather.replace();
        
        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, duration);
        }
    }
    
    // Form submission with AJAX
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate phone number before submission
        if (phoneInput && phoneInput.value.trim() && !validatePhoneNumber()) {
            showNotification('Silakan perbaiki format nomor WhatsApp terlebih dahulu.', 'error');
            return;
        }
        
        const formData = new FormData(this);
        
        // Combine country code with phone number
        if (phoneInput && countryCodeSelect && phoneInput.value.trim()) {
            const countryCode = countryCodeSelect.value;
            const phoneNumber = phoneInput.value.replace(/\D/g, '');
            const fullPhoneNumber = countryCode + phoneNumber;
            formData.set('phone', fullPhoneNumber);
        }
        
        const submitButton = document.getElementById('save-button');
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i data-feather="loader" width="16" height="16" class="spin"></i> Menyimpan...';
        feather.replace();
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Check if response contains success or error
            if (data.includes('berhasil') || data.includes('success')) {
                showNotification('Profil berhasil diperbarui!', 'success');
                
                // Update topbar avatar if photo was uploaded
                const photoPreview = document.querySelector('#profile-avatar-display img');
                if (photoPreview) {
                    updateTopbarAvatar(photoPreview.src);
                }
                
                // Stay on profile page - don't redirect
                // User can manually go back if needed
            } else {
                showNotification('Terjadi kesalahan saat menyimpan profil.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan jaringan.', 'error');
        })
        .finally(() => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = '<i data-feather="save" width="16" height="16"></i> Simpan Perubahan';
            feather.replace();
        });
    });
</script>