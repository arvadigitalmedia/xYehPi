<?php
/**
 * EPIC Hub Admin Settings Email Content
 * Konten halaman settings email notification untuk layout global
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
        <a href="<?= epic_url('admin/settings/form-registrasi') ?>" class="settings-nav-item">
            <i data-feather="file-text" class="settings-nav-icon"></i>
            <span>Form Registrasi</span>
        </a>
        <a href="<?= epic_url('admin/settings/email-notification') ?>" class="settings-nav-item active">
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

<!-- Email Settings Form -->
<form method="POST" action="<?= epic_url('admin/settings/email-notification') ?>" class="settings-form">
    <!-- SMTP Configuration Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="server" class="settings-card-icon"></i>
                SMTP Configuration
            </h3>
            <p class="settings-card-description">
                Konfigurasi server SMTP untuk pengiriman email
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="smtp_host">
                        SMTP Host
                        <span class="form-label-required">*</span>
                    </label>
                    <input type="text" 
                           id="smtp_host" 
                           name="smtp_host" 
                           class="form-input" 
                           placeholder="smtp.gmail.com" 
                           value="<?= htmlspecialchars($email_settings['smtp_host'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="smtp_port">
                        SMTP Port
                        <span class="form-label-required">*</span>
                    </label>
                    <input type="number" 
                           id="smtp_port" 
                           name="smtp_port" 
                           class="form-input" 
                           placeholder="587" 
                           value="<?= htmlspecialchars($email_settings['smtp_port'] ?? '') ?>"
                           required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="smtp_username">
                        SMTP Username
                        <span class="form-label-required">*</span>
                    </label>
                    <input type="email" 
                           id="smtp_username" 
                           name="smtp_username" 
                           class="form-input" 
                           placeholder="your-email@gmail.com" 
                           value="<?= htmlspecialchars($email_settings['smtp_username'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="smtp_password">
                        SMTP Password
                        <span class="form-label-required">*</span>
                    </label>
                    <input type="password" 
                           id="smtp_password" 
                           name="smtp_password" 
                           class="form-input" 
                           placeholder="••••••••" 
                           value="<?= htmlspecialchars($email_settings['smtp_password'] ?? '') ?>">
                    <div class="form-help">Kosongkan jika tidak ingin mengubah password</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="smtp_encryption">
                        Enkripsi
                    </label>
                    <select id="smtp_encryption" name="smtp_encryption" class="form-input">
                        <option value="tls" <?= ($email_settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= ($email_settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="none" <?= ($email_settings['smtp_encryption'] ?? '') == 'none' ? 'selected' : '' ?>>None</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="mail_from_name">
                        Nama Pengirim
                    </label>
                    <input type="text" 
                           id="mail_from_name" 
                           name="mail_from_name" 
                           class="form-input" 
                           placeholder="EPIC Hub" 
                           value="<?= htmlspecialchars($email_settings['mail_from_name'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="mail_from_email">
                    Email Pengirim
                </label>
                <input type="email" 
                       id="mail_from_email" 
                       name="mail_from_email" 
                       class="form-input" 
                       placeholder="noreply@epichub.com" 
                       value="<?= htmlspecialchars($email_settings['mail_from_email'] ?? '') ?>">
            </div>
        </div>
    </div>
    
    <!-- Email Notifications Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="bell" class="settings-card-icon"></i>
                Email Notifications
            </h3>
            <p class="settings-card-description">
                Pengaturan notifikasi email otomatis
            </p>
        </div>
        
        <div class="settings-card-body">
            <!-- Welcome Email -->
            <div class="notification-section">
                <div class="notification-header">
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="welcome_email_enabled" 
                               name="welcome_email_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($email_settings['welcome_email_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="welcome_email_enabled" class="form-checkbox-label">
                            <strong>Welcome Email</strong>
                        </label>
                    </div>
                    <p class="notification-description">Email yang dikirim saat user baru mendaftar</p>
                </div>
                
                <div class="notification-content">
                    <div class="form-group">
                        <label class="form-label" for="welcome_email_subject">
                            Subject
                        </label>
                        <input type="text" 
                               id="welcome_email_subject" 
                               name="welcome_email_subject" 
                               class="form-input" 
                               placeholder="Selamat Datang di EPIC Hub!" 
                               value="<?= htmlspecialchars($email_settings['welcome_email_subject'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="welcome_email_template">
                            Template
                        </label>
                        <textarea id="welcome_email_template" 
                                  name="welcome_email_template" 
                                  class="form-textarea" 
                                  rows="4" 
                                  placeholder="Terima kasih telah bergabung dengan EPIC Hub..."><?= htmlspecialchars($email_settings['welcome_email_template'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Order Confirmation Email -->
            <div class="notification-section">
                <div class="notification-header">
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="order_confirmation_enabled" 
                               name="order_confirmation_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($email_settings['order_confirmation_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="order_confirmation_enabled" class="form-checkbox-label">
                            <strong>Order Confirmation Email</strong>
                        </label>
                    </div>
                    <p class="notification-description">Email konfirmasi saat ada pesanan baru</p>
                </div>
                
                <div class="notification-content">
                    <div class="form-group">
                        <label class="form-label" for="order_confirmation_subject">
                            Subject
                        </label>
                        <input type="text" 
                               id="order_confirmation_subject" 
                               name="order_confirmation_subject" 
                               class="form-input" 
                               placeholder="Konfirmasi Pesanan - EPIC Hub" 
                               value="<?= htmlspecialchars($email_settings['order_confirmation_subject'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="order_confirmation_template">
                            Template
                        </label>
                        <textarea id="order_confirmation_template" 
                                  name="order_confirmation_template" 
                                  class="form-textarea" 
                                  rows="4" 
                                  placeholder="Pesanan Anda telah berhasil diproses..."><?= htmlspecialchars($email_settings['order_confirmation_template'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Payout Notification Email -->
            <div class="notification-section">
                <div class="notification-header">
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="payout_notification_enabled" 
                               name="payout_notification_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($email_settings['payout_notification_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="payout_notification_enabled" class="form-checkbox-label">
                            <strong>Payout Notification Email</strong>
                        </label>
                    </div>
                    <p class="notification-description">Email notifikasi pembayaran komisi</p>
                </div>
                
                <div class="notification-content">
                    <div class="form-group">
                        <label class="form-label" for="payout_notification_subject">
                            Subject
                        </label>
                        <input type="text" 
                               id="payout_notification_subject" 
                               name="payout_notification_subject" 
                               class="form-input" 
                               placeholder="Notifikasi Pembayaran - EPIC Hub" 
                               value="<?= htmlspecialchars($email_settings['payout_notification_subject'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="payout_notification_template">
                            Template
                        </label>
                        <textarea id="payout_notification_template" 
                                  name="payout_notification_template" 
                                  class="form-textarea" 
                                  rows="4" 
                                  placeholder="Pembayaran komisi Anda telah diproses..."><?= htmlspecialchars($email_settings['payout_notification_template'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Admin Notification -->
            <div class="notification-section">
                <div class="notification-header">
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="admin_notification_enabled" 
                               name="admin_notification_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($email_settings['admin_notification_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="admin_notification_enabled" class="form-checkbox-label">
                            <strong>Admin Notification</strong>
                        </label>
                    </div>
                    <p class="notification-description">Email notifikasi untuk admin saat ada aktivitas penting</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Save Button -->
    <div class="settings-actions">
        <button type="submit" name="save_email_settings" class="btn btn-primary">
            <i data-feather="save" width="16" height="16"></i>
            Simpan Pengaturan Email
        </button>
        <button type="button" class="btn btn-secondary" onclick="testEmailConnection()">
            <i data-feather="send" width="16" height="16"></i>
            Test Koneksi Email
        </button>
        <a href="<?= epic_url('admin') ?>" class="btn btn-secondary">
            <i data-feather="arrow-left" width="16" height="16"></i>
            Kembali ke Dashboard
        </a>
    </div>
</form>

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

/* Email specific styles */
.notification-section {
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    padding: var(--spacing-4);
    margin-bottom: var(--spacing-4);
    background: var(--surface-3);
}

.notification-section:last-child {
    margin-bottom: 0;
}

.notification-header {
    margin-bottom: var(--spacing-4);
}

.notification-description {
    color: var(--ink-400);
    font-size: var(--font-size-sm);
    margin: var(--spacing-2) 0 0 0;
}

.notification-content {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
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

.settings-actions {
    display: flex;
    gap: var(--spacing-4);
    padding: var(--spacing-6) 0;
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
    
    .settings-actions {
        flex-direction: column;
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
});

// Test email connection
function testEmailConnection() {
    const formData = new FormData();
    formData.append('test_email', '1');
    formData.append('smtp_host', document.getElementById('smtp_host').value);
    formData.append('smtp_port', document.getElementById('smtp_port').value);
    formData.append('smtp_username', document.getElementById('smtp_username').value);
    formData.append('smtp_password', document.getElementById('smtp_password').value);
    formData.append('smtp_encryption', document.getElementById('smtp_encryption').value);
    
    fetch('<?= epic_url('admin/settings/email-notification') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Koneksi email berhasil!');
        } else {
            alert('Koneksi email gagal: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat test koneksi');
    });
}
</script>