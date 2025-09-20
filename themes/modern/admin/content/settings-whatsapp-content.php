<?php
/**
 * EPIC Hub Admin Settings WhatsApp Content
 * Konten halaman settings WhatsApp notification untuk layout global
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
        <a href="<?= epic_url('admin/settings/email-notification') ?>" class="settings-nav-item">
            <i data-feather="mail" class="settings-nav-icon"></i>
            <span>Email Notification</span>
        </a>
        <a href="<?= epic_url('admin/settings/whatsapp-notification') ?>" class="settings-nav-item active">
            <i data-feather="message-circle" class="settings-nav-icon"></i>
            <span>WhatsApp Notification</span>
        </a>
        <a href="<?= epic_url('admin/settings/payment-gateway') ?>" class="settings-nav-item">
            <i data-feather="credit-card" class="settings-nav-icon"></i>
            <span>Payment Gateway</span>
        </a>
    </nav>
</div>

<!-- WhatsApp Settings Form -->
<form method="POST" action="<?= epic_url('admin/settings/whatsapp-notification') ?>" class="settings-form">
    <!-- WhatsApp API Configuration Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="smartphone" class="settings-card-icon"></i>
                WhatsApp API Configuration
            </h3>
            <p class="settings-card-description">
                Konfigurasi API WhatsApp untuk pengiriman pesan otomatis
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="form-group">
                <label class="form-label" for="whatsapp_service_provider">
                    Service Provider
                </label>
                <select id="whatsapp_service_provider" name="whatsapp_service_provider" class="form-input" onchange="updateApiSettings()">
                    <option value="fonnte" <?= ($whatsapp_settings['whatsapp_service_provider'] ?? 'fonnte') == 'fonnte' ? 'selected' : '' ?>>Fonnte</option>
                    <option value="wabiz" <?= ($whatsapp_settings['whatsapp_service_provider'] ?? '') == 'wabiz' ? 'selected' : '' ?>>WA Biz</option>
    
                    <option value="waplus" <?= ($whatsapp_settings['whatsapp_service_provider'] ?? '') == 'waplus' ? 'selected' : '' ?>>WA Plus</option>
                    <option value="custom" <?= ($whatsapp_settings['whatsapp_service_provider'] ?? '') == 'custom' ? 'selected' : '' ?>>Custom API</option>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="whatsapp_api_url">
                        API URL
                        <span class="form-label-required">*</span>
                    </label>
                    <input type="url" 
                           id="whatsapp_api_url" 
                           name="whatsapp_api_url" 
                           class="form-input" 
                           placeholder="https://api.fonnte.com/send" 
                           value="<?= htmlspecialchars($whatsapp_settings['whatsapp_api_url'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="whatsapp_api_key">
                        API Key/Token
                        <span class="form-label-required">*</span>
                    </label>
                    <input type="password" 
                           id="whatsapp_api_key" 
                           name="whatsapp_api_key" 
                           class="form-input" 
                           placeholder="••••••••••••••••" 
                           value="<?= htmlspecialchars($whatsapp_settings['whatsapp_api_key'] ?? '') ?>">
                    <div class="form-help">Kosongkan jika tidak ingin mengubah API key</div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="whatsapp_sender_number">
                    Nomor Pengirim
                </label>
                <input type="tel" 
                       id="whatsapp_sender_number" 
                       name="whatsapp_sender_number" 
                       class="form-input" 
                       placeholder="628123456789" 
                       value="<?= htmlspecialchars($whatsapp_settings['whatsapp_sender_number'] ?? '') ?>">
                <div class="form-help">Format: 628123456789 (tanpa tanda + dan spasi)</div>
            </div>
        </div>
    </div>
    
    <!-- WhatsApp Notifications Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="message-square" class="settings-card-icon"></i>
                WhatsApp Notifications
            </h3>
            <p class="settings-card-description">
                Pengaturan notifikasi WhatsApp otomatis
            </p>
        </div>
        
        <div class="settings-card-body">
            <!-- Welcome Message -->
            <div class="notification-section">
                <div class="notification-header">
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="whatsapp_welcome_enabled" 
                               name="whatsapp_welcome_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($whatsapp_settings['whatsapp_welcome_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="whatsapp_welcome_enabled" class="form-checkbox-label">
                            <strong>Welcome Message</strong>
                        </label>
                    </div>
                    <p class="notification-description">Pesan WhatsApp yang dikirim saat user baru mendaftar</p>
                </div>
                
                <div class="notification-content">
                    <div class="form-group">
                        <label class="form-label" for="whatsapp_welcome_template">
                            Template Pesan
                        </label>
                        <textarea id="whatsapp_welcome_template" 
                                  name="whatsapp_welcome_template" 
                                  class="form-textarea" 
                                  rows="4" 
                                  placeholder="Selamat datang di EPIC Hub! Akun Anda telah berhasil dibuat..."><?= htmlspecialchars($whatsapp_settings['whatsapp_welcome_template'] ?? '') ?></textarea>
                        <div class="form-help">Variabel yang tersedia: {name}, {email}, {phone}</div>
                    </div>
                </div>
            </div>
            
            <!-- Order Confirmation Message -->
            <div class="notification-section">
                <div class="notification-header">
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="whatsapp_order_confirmation_enabled" 
                               name="whatsapp_order_confirmation_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($whatsapp_settings['whatsapp_order_confirmation_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="whatsapp_order_confirmation_enabled" class="form-checkbox-label">
                            <strong>Order Confirmation Message</strong>
                        </label>
                    </div>
                    <p class="notification-description">Pesan konfirmasi saat ada pesanan baru</p>
                </div>
                
                <div class="notification-content">
                    <div class="form-group">
                        <label class="form-label" for="whatsapp_order_confirmation_template">
                            Template Pesan
                        </label>
                        <textarea id="whatsapp_order_confirmation_template" 
                                  name="whatsapp_order_confirmation_template" 
                                  class="form-textarea" 
                                  rows="4" 
                                  placeholder="Pesanan Anda telah berhasil diproses..."><?= htmlspecialchars($whatsapp_settings['whatsapp_order_confirmation_template'] ?? '') ?></textarea>
                        <div class="form-help">Variabel yang tersedia: {name}, {order_id}, {product_name}, {amount}</div>
                    </div>
                </div>
            </div>
            
            <!-- Payout Notification Message -->
            <div class="notification-section">
                <div class="notification-header">
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="whatsapp_payout_notification_enabled" 
                               name="whatsapp_payout_notification_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($whatsapp_settings['whatsapp_payout_notification_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="whatsapp_payout_notification_enabled" class="form-checkbox-label">
                            <strong>Payout Notification Message</strong>
                        </label>
                    </div>
                    <p class="notification-description">Pesan notifikasi pembayaran komisi</p>
                </div>
                
                <div class="notification-content">
                    <div class="form-group">
                        <label class="form-label" for="whatsapp_payout_notification_template">
                            Template Pesan
                        </label>
                        <textarea id="whatsapp_payout_notification_template" 
                                  name="whatsapp_payout_notification_template" 
                                  class="form-textarea" 
                                  rows="4" 
                                  placeholder="Pembayaran komisi Anda sebesar {amount} telah diproses..."><?= htmlspecialchars($whatsapp_settings['whatsapp_payout_notification_template'] ?? '') ?></textarea>
                        <div class="form-help">Variabel yang tersedia: {name}, {amount}, {bank_account}</div>
                    </div>
                </div>
            </div>
            
            <!-- Admin Notification -->
            <div class="notification-section">
                <div class="notification-header">
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="whatsapp_admin_notification_enabled" 
                               name="whatsapp_admin_notification_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($whatsapp_settings['whatsapp_admin_notification_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="whatsapp_admin_notification_enabled" class="form-checkbox-label">
                            <strong>Admin Notification</strong>
                        </label>
                    </div>
                    <p class="notification-description">Notifikasi WhatsApp untuk admin saat ada aktivitas penting</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Save Button -->
    <div class="settings-actions">
        <button type="submit" name="save_whatsapp_settings" class="btn btn-primary">
            <i data-feather="save" width="16" height="16"></i>
            Simpan Pengaturan WhatsApp
        </button>
        <button type="button" class="btn btn-secondary" onclick="testWhatsAppConnection()">
            <i data-feather="send" width="16" height="16"></i>
            Test Kirim Pesan
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

/* WhatsApp specific styles */
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

// Update API settings based on provider
function updateApiSettings() {
    const provider = document.getElementById('whatsapp_service_provider').value;
    const apiUrlField = document.getElementById('whatsapp_api_url');
    
    const apiUrls = {
        'fonnte': 'https://api.fonnte.com/send',
        'wabiz': 'https://api.wabiz.id/send',
        'waplus': 'https://api.waplus.id/send',
        'custom': ''
    };
    
    if (apiUrls[provider]) {
        apiUrlField.value = apiUrls[provider];
    }
}

// Test WhatsApp connection
function testWhatsAppConnection() {
    const formData = new FormData();
    formData.append('test_whatsapp', '1');
    formData.append('whatsapp_api_url', document.getElementById('whatsapp_api_url').value);
    formData.append('whatsapp_api_key', document.getElementById('whatsapp_api_key').value);
    formData.append('whatsapp_sender_number', document.getElementById('whatsapp_sender_number').value);
    
    const testNumber = prompt('Masukkan nomor WhatsApp untuk test (format: 628123456789):');
    if (!testNumber) return;
    
    formData.append('test_number', testNumber);
    
    fetch('<?= epic_url('admin/settings/whatsapp-notification') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pesan test berhasil dikirim!');
        } else {
            alert('Gagal mengirim pesan: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat test kirim pesan');
    });
}
</script>