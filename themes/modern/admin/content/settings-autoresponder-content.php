<?php
/**
 * EPIC Hub Admin Settings Autoresponder Content
 * Konten halaman settings autoresponder email untuk layout global
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

<!-- Integration Navigation -->
<div class="settings-navigation">
    <nav class="settings-nav">
        <a href="<?= epic_url('admin/integrasi/autoresponder-email') ?>" class="settings-nav-item active">
            <i data-feather="send" class="settings-nav-icon"></i>
            <span>Autoresponder Email</span>
        </a>
        <a href="<?= epic_url('admin/zoom-integration') ?>" class="settings-nav-item">
            <i data-feather="video" class="settings-nav-icon"></i>
            <span>Zoom Integration</span>
        </a>
    </nav>
</div>

<!-- Autoresponder Settings Form -->
<form method="POST" action="<?= epic_url('admin/integrasi/autoresponder-email') ?>" class="settings-form">
    <!-- General Autoresponder Settings -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="settings" class="settings-card-icon"></i>
                General Autoresponder Settings
            </h3>
            <p class="settings-card-description">
                Pengaturan umum untuk integrasi autoresponder email
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="autoresponder_provider">
                        Autoresponder Provider
                    </label>
                    <select id="autoresponder_provider" name="autoresponder_provider" class="form-input" onchange="updateProviderSettings()">
                        <option value="mailchimp" <?= ($autoresponder_settings['autoresponder_provider'] ?? 'mailchimp') == 'mailchimp' ? 'selected' : '' ?>>MailChimp</option>
                        <option value="aweber" <?= ($autoresponder_settings['autoresponder_provider'] ?? '') == 'aweber' ? 'selected' : '' ?>>AWeber</option>
                        <option value="getresponse" <?= ($autoresponder_settings['autoresponder_provider'] ?? '') == 'getresponse' ? 'selected' : '' ?>>GetResponse</option>
                        <option value="activecampaign" <?= ($autoresponder_settings['autoresponder_provider'] ?? '') == 'activecampaign' ? 'selected' : '' ?>>ActiveCampaign</option>
                        <option value="convertkit" <?= ($autoresponder_settings['autoresponder_provider'] ?? '') == 'convertkit' ? 'selected' : '' ?>>ConvertKit</option>
                        <option value="custom" <?= ($autoresponder_settings['autoresponder_provider'] ?? '') == 'custom' ? 'selected' : '' ?>>Custom API</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="autoresponder_api_key">
                        API Key
                        <span class="form-label-required">*</span>
                    </label>
                    <input type="password" 
                           id="autoresponder_api_key" 
                           name="autoresponder_api_key" 
                           class="form-input" 
                           placeholder="••••••••••••••••" 
                           value="<?= htmlspecialchars($autoresponder_settings['autoresponder_api_key'] ?? '') ?>"
                           onchange="this.value = this.value.replace(/[<>&quot;'&amp;]/g, '')">
                    <div class="form-help">API key dari provider autoresponder Anda</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="autoresponder_api_url">
                        API URL (untuk Custom API)
                    </label>
                    <input type="url" 
                           id="autoresponder_api_url" 
                           name="autoresponder_api_url" 
                           class="form-input" 
                           placeholder="https://api.example.com/subscribe" 
                           value="<?= htmlspecialchars($autoresponder_settings['autoresponder_api_url'] ?? '') ?>"
                           onchange="this.value = this.value.replace(/[<>&quot;'&amp;]/g, '')">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="autoresponder_default_list">
                        Default List ID
                    </label>
                    <input type="text" 
                           id="autoresponder_default_list" 
                           name="autoresponder_default_list" 
                           class="form-input" 
                           placeholder="list123456" 
                           value="<?= htmlspecialchars($autoresponder_settings['autoresponder_default_list'] ?? '') ?>">
                    <div class="form-help">ID list default untuk subscriber baru</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Event-based Autoresponder Configuration -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="zap" class="settings-card-icon"></i>
                Event-based Autoresponder Configuration
            </h3>
            <p class="settings-card-description">
                Konfigurasi autoresponder berdasarkan event/aktivitas tertentu
            </p>
        </div>
        
        <div class="settings-card-body">
            <?php foreach ($notification_events as $event): ?>
                <div class="autoresponder-event-section">
                    <div class="event-header" onclick="toggleEventSection('<?= $event['key'] ?>')">
                        <div class="event-title">
                            <i data-feather="chevron-right" class="event-toggle-icon" id="icon-<?= $event['key'] ?>"></i>
                            <strong><?= htmlspecialchars($event['name']) ?></strong>
                        </div>
                        <div class="event-description">
                            <?= htmlspecialchars($event['description']) ?>
                        </div>
                    </div>
                    
                    <div class="event-content" id="content-<?= $event['key'] ?>" style="display: none;">
                        <!-- Action URL -->
                        <div class="form-group">
                            <label class="form-label" for="form_action_<?= $event['key'] ?>">
                                Action URL
                            </label>
                            <input type="url" 
                                   id="form_action_<?= $event['key'] ?>" 
                                   name="form_action_<?= $event['key'] ?>" 
                                   class="form-input" 
                                   placeholder="https://api.mailchimp.com/3.0/lists/listid/members" 
                                   value="<?= htmlspecialchars($autoresponder_settings['form_action_' . $event['key']] ?? '') ?>">
                            <div class="form-help">URL endpoint untuk mengirim data ke autoresponder</div>
                        </div>
                        
                        <!-- Field Mapping -->
                        <div class="field-mapping-section">
                            <h4 class="field-mapping-title">
                                <i data-feather="link" width="16" height="16"></i>
                                Field Mapping
                            </h4>
                            <p class="field-mapping-description">Mapping field data ke autoresponder (maksimal 10 field)</p>
                            
                            <div class="field-mapping-list">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <div class="field-mapping-row">
                                        <div class="field-mapping-field">
                                            <input type="text" 
                                                   class="form-input" 
                                                   name="form_field_<?= $event['key'] ?><?= $i ?>" 
                                                   placeholder="Field Name (e.g., email_address)" 
                                                   value="<?= htmlspecialchars($autoresponder_settings['form_field_' . $event['key'] . $i] ?? '') ?>">
                                        </div>
                                        <div class="field-mapping-arrow">
                                            <i data-feather="arrow-right" width="16" height="16"></i>
                                        </div>
                                        <div class="field-mapping-value">
                                            <input type="text" 
                                                   class="form-input" 
                                                   name="form_value_<?= $event['key'] ?><?= $i ?>" 
                                                   placeholder="Value/Shortcode (e.g., [member_email])" 
                                                   value="<?= htmlspecialchars($autoresponder_settings['form_value_' . $event['key'] . $i] ?? '') ?>">
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Event-specific Shortcodes -->
                        <?php if (!empty($event['shortcodes'])): ?>
                            <div class="shortcode-info">
                                <h4 class="shortcode-title">
                                    <i data-feather="code" width="16" height="16"></i>
                                    Shortcode Khusus untuk Event Ini
                                </h4>
                                <div class="shortcode-content">
                                    <?= $event['shortcodes'] ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Shortcode Reference -->
    <div class="settings-card shortcode-reference">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="book" class="settings-card-icon"></i>
                Daftar Shortcode
            </h3>
            <p class="settings-card-description">
                Shortcode yang tersedia untuk mapping data ke autoresponder
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="shortcode-grid">
                <!-- Member Data Shortcodes -->
                <div class="shortcode-section">
                    <h4 class="shortcode-section-title">
                        <i data-feather="user" width="16" height="16"></i>
                        Data Member
                    </h4>
                    <div class="shortcode-list">
                        <div class="shortcode-item">
                            <code>[member_id]</code>
                            <span>ID Member</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[member_name]</code>
                            <span>Nama Member</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[member_email]</code>
                            <span>Email Member</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[member_phone]</code>
                            <span>Nomor Telepon Member</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[member_whatsapp]</code>
                            <span>WhatsApp Member</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[member_referral_code]</code>
                            <span>Kode Referral Member</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[member_referral_url]</code>
                            <span>URL Referral Member</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[member_join_date]</code>
                            <span>Tanggal Bergabung</span>
                        </div>
                        
                        <!-- Dynamic form fields -->
                        <?php if (!empty($form_fields)): ?>
                            <?php foreach ($form_fields as $field): ?>
                                <?php if (!in_array($field['field_name'], ['name', 'email', 'phone', 'whatsapp'])): ?>
                                    <div class="shortcode-item">
                                        <code>[member_<?= htmlspecialchars($field['field_name']) ?>]</code>
                                        <span><?= htmlspecialchars($field['field_label']) ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sponsor/Affiliate Data Shortcodes -->
                <div class="shortcode-section">
                    <h4 class="shortcode-section-title">
                        <i data-feather="users" width="16" height="16"></i>
                        Data Sponsor/Affiliate
                    </h4>
                    <div class="shortcode-list">
                        <div class="shortcode-item">
                            <code>[sponsor_id]</code>
                            <span>ID Sponsor</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[sponsor_name]</code>
                            <span>Nama Sponsor</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[sponsor_email]</code>
                            <span>Email Sponsor</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[sponsor_phone]</code>
                            <span>Nomor Telepon Sponsor</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[sponsor_whatsapp]</code>
                            <span>WhatsApp Sponsor</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[sponsor_referral_code]</code>
                            <span>Kode Referral Sponsor</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[sponsor_referral_url]</code>
                            <span>URL Referral Sponsor</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[sponsor_level]</code>
                            <span>Level Sponsor</span>
                        </div>
                        
                        <!-- Dynamic form fields for sponsor -->
                        <?php if (!empty($form_fields)): ?>
                            <?php foreach ($form_fields as $field): ?>
                                <?php if (!in_array($field['field_name'], ['name', 'email', 'phone', 'whatsapp'])): ?>
                                    <div class="shortcode-item">
                                        <code>[sponsor_<?= htmlspecialchars($field['field_name']) ?>]</code>
                                        <span><?= htmlspecialchars($field['field_label']) ?> Sponsor</span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- System Data Shortcodes -->
                <div class="shortcode-section">
                    <h4 class="shortcode-section-title">
                        <i data-feather="server" width="16" height="16"></i>
                        Data Sistem
                    </h4>
                    <div class="shortcode-list">
                        <div class="shortcode-item">
                            <code>[site_name]</code>
                            <span>Nama Website</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[site_url]</code>
                            <span>URL Website</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[current_date]</code>
                            <span>Tanggal Saat Ini</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[current_time]</code>
                            <span>Waktu Saat Ini</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[ip_address]</code>
                            <span>IP Address User</span>
                        </div>
                        <div class="shortcode-item">
                            <code>[user_agent]</code>
                            <span>User Agent Browser</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Save Button -->
    <div class="settings-actions">
        <button type="submit" name="save_autoresponder_settings" class="btn btn-primary">
            <i data-feather="save" width="16" height="16"></i>
            Simpan Pengaturan Autoresponder
        </button>
        <button type="button" class="btn btn-secondary" onclick="testAutoresponder()">
            <i data-feather="send" width="16" height="16"></i>
            Test Autoresponder
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

/* Autoresponder specific styles */
.autoresponder-event-section {
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-4);
    background: var(--surface-3);
}

.event-header {
    padding: var(--spacing-4);
    cursor: pointer;
    transition: all var(--transition-normal);
    border-bottom: 1px solid var(--ink-700);
}

.event-header:hover {
    background: var(--surface-2);
}

.event-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-2);
}

.event-toggle-icon {
    transition: transform var(--transition-normal);
}

.event-toggle-icon.rotated {
    transform: rotate(90deg);
}

.event-description {
    color: var(--ink-400);
    font-size: var(--font-size-sm);
}

.event-content {
    padding: var(--spacing-4);
    border-top: 1px solid var(--ink-700);
}

.field-mapping-section {
    margin: var(--spacing-6) 0;
}

.field-mapping-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-2);
}

.field-mapping-description {
    color: var(--ink-400);
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-4);
}

.field-mapping-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.field-mapping-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: var(--spacing-3);
    align-items: center;
}

.field-mapping-arrow {
    color: var(--ink-400);
    display: flex;
    justify-content: center;
}

.shortcode-info {
    margin-top: var(--spacing-6);
    padding: var(--spacing-4);
    background: var(--surface-1);
    border-radius: var(--radius-lg);
    border: 1px solid var(--ink-600);
}

.shortcode-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-3);
}

.shortcode-content {
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    line-height: 1.6;
}

.shortcode-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-6);
}

.shortcode-section {
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-4);
}

.shortcode-section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-4);
}

.shortcode-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.shortcode-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-2);
    background: var(--surface-2);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
}

.shortcode-item code {
    background: var(--surface-3);
    color: var(--gold-400);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-family: 'Courier New', monospace;
    font-weight: var(--font-weight-medium);
}

.shortcode-item span {
    color: var(--ink-300);
    font-size: var(--font-size-xs);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
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
    
    .field-mapping-row {
        grid-template-columns: 1fr;
        gap: var(--spacing-2);
    }
    
    .field-mapping-arrow {
        transform: rotate(90deg);
    }
    
    .shortcode-grid {
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

// Toggle event section
function toggleEventSection(eventKey) {
    const content = document.getElementById('content-' + eventKey);
    const icon = document.getElementById('icon-' + eventKey);
    
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.classList.add('rotated');
    } else {
        content.style.display = 'none';
        icon.classList.remove('rotated');
    }
}

// Update provider settings
function updateProviderSettings() {
    const provider = document.getElementById('autoresponder_provider').value;
    const apiUrlField = document.getElementById('autoresponder_api_url');
    
    // Show/hide API URL field based on provider
    if (provider === 'custom') {
        apiUrlField.parentElement.style.display = 'block';
    } else {
        apiUrlField.parentElement.style.display = 'none';
    }
}

// Test autoresponder
function testAutoresponder() {
    const provider = document.getElementById('autoresponder_provider').value;
    const apiKey = document.getElementById('autoresponder_api_key').value;
    
    if (!apiKey) {
        alert('Mohon masukkan API Key terlebih dahulu');
        return;
    }
    
    const formData = new FormData();
    formData.append('test_autoresponder', '1');
    formData.append('provider', provider);
    formData.append('api_key', apiKey);
    
    if (provider === 'custom') {
        const apiUrl = document.getElementById('autoresponder_api_url').value;
        if (!apiUrl) {
            alert('Mohon masukkan API URL untuk Custom API');
            return;
        }
        formData.append('api_url', apiUrl);
    }
    
    fetch('<?= epic_url('admin/integrasi/autoresponder-email') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test autoresponder berhasil! Koneksi ke ' + provider + ' berfungsi dengan baik.');
        } else {
            alert('Test autoresponder gagal: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat test autoresponder');
    });
}

// Initialize provider settings on page load
document.addEventListener('DOMContentLoaded', function() {
    updateProviderSettings();
});
</script>