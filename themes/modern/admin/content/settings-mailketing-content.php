<?php
/**
 * EPIC Hub Admin Settings Mailketing Content
 * Konten halaman settings Mailketing untuk layout global
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
        <a href="<?= epic_url('admin/settings/mailketing') ?>" class="settings-nav-item active">
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

<!-- Mailketing Status Dashboard -->
<div class="mailketing-status-dashboard">
    <div class="status-card">
        <div class="status-icon <?= $mailketing_status['enabled'] ? 'status-success' : 'status-warning' ?>">
            <i data-feather="<?= $mailketing_status['enabled'] ? 'check-circle' : 'alert-circle' ?>"></i>
        </div>
        <div class="status-info">
            <h4>Status Mailketing</h4>
            <p><?= $mailketing_status['enabled'] ? 'Aktif' : 'Tidak Aktif' ?></p>
        </div>
    </div>
    
    <div class="status-card">
        <div class="status-icon <?= $mailketing_status['configured'] ? 'status-success' : 'status-error' ?>">
            <i data-feather="<?= $mailketing_status['configured'] ? 'settings' : 'x-circle' ?>"></i>
        </div>
        <div class="status-info">
            <h4>Konfigurasi</h4>
            <p><?= $mailketing_status['configured'] ? 'Terkonfigurasi' : 'Belum Dikonfigurasi' ?></p>
        </div>
    </div>
    
    <div class="status-card">
        <div class="status-icon status-info">
            <i data-feather="credit-card"></i>
        </div>
        <div class="status-info">
            <h4>Credits</h4>
            <p id="mailketing-credits"><?= $mailketing_status['credits'] ?? 'Unknown' ?></p>
        </div>
    </div>
</div>

<!-- Mailketing Settings Tabs -->
<div class="settings-tabs">
    <div class="tab-navigation">
        <button class="tab-button active" data-tab="api-config">
            <i data-feather="settings"></i>
            Konfigurasi API
        </button>
        <button class="tab-button" data-tab="email-templates">
            <i data-feather="file-text"></i>
            Template Email
        </button>
        <button class="tab-button" data-tab="list-management">
            <i data-feather="users"></i>
            List Management
        </button>
        <button class="tab-button" data-tab="webhook-settings">
            <i data-feather="link"></i>
            Webhook
        </button>
        <button class="tab-button" data-tab="monitoring">
            <i data-feather="activity"></i>
            Monitoring
        </button>
    </div>

    <!-- Tab 1: API Configuration -->
    <div class="tab-content active" id="api-config">
        <form method="POST" action="<?= epic_url('admin/settings/mailketing') ?>" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?= epic_csrf_token() ?>">
            
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">
                        <i data-feather="key" class="settings-card-icon"></i>
                        Konfigurasi API Mailketing
                    </h3>
                    <p class="settings-card-description">
                        Pengaturan koneksi dengan Mailketing API untuk email marketing <mcreference link="https://mailketing.co.id/docs/send-email-via-api/" index="0">0</mcreference>
                    </p>
                </div>
                
                <div class="settings-card-body">
                    <div class="form-group">
                        <div class="form-checkbox-group">
                            <input type="checkbox" 
                                   id="mailketing_enabled" 
                                   name="mailketing_enabled" 
                                   value="1" 
                                   class="form-checkbox"
                                   <?= ($settings['mailketing_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                            <label for="mailketing_enabled" class="form-checkbox-label">
                                <strong>Aktifkan Mailketing API</strong>
                            </label>
                        </div>
                        <p class="form-help-text">Gunakan Mailketing sebagai service email utama</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="mailketing_api_token">
                                API Token
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="password" 
                                   id="mailketing_api_token" 
                                   name="mailketing_api_token" 
                                   class="form-input" 
                                   placeholder="Masukkan API Token dari Mailketing"
                                   value="<?= htmlspecialchars($settings['mailketing_api_token'] ?? '') ?>">
                            <p class="form-help-text">Dapatkan API Token dari menu Integration di dashboard Mailketing</p>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="mailketing_from_name">
                                Nama Pengirim
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="text" 
                                   id="mailketing_from_name" 
                                   name="mailketing_from_name" 
                                   class="form-input" 
                                   placeholder="EPIC Hub"
                                   value="<?= htmlspecialchars($settings['mailketing_from_name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="mailketing_from_email">
                                Email Pengirim
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="email" 
                                   id="mailketing_from_email" 
                                   name="mailketing_from_email" 
                                   class="form-input" 
                                   placeholder="noreply@epichub.com"
                                   value="<?= htmlspecialchars($settings['mailketing_from_email'] ?? '') ?>">
                            <p class="form-help-text">Email ini harus sudah diverifikasi di Mailketing</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="mailketing_default_list_id">
                            Default List ID
                        </label>
                        <input type="number" 
                               id="mailketing_default_list_id" 
                               name="mailketing_default_list_id" 
                               class="form-input" 
                               placeholder="1"
                               value="<?= htmlspecialchars($settings['mailketing_default_list_id'] ?? '') ?>">
                        <p class="form-help-text">ID list default untuk subscriber baru</p>
                    </div>
                </div>
            </div>
            
            <div class="settings-actions">
                <button type="submit" name="save_mailketing_settings" class="btn btn-primary">
                    <i data-feather="save" width="16" height="16"></i>
                    Simpan Konfigurasi
                </button>
                <button type="submit" name="test_mailketing_connection" class="btn btn-secondary">
                    <i data-feather="send" width="16" height="16"></i>
                    Test Koneksi
                </button>
                <button type="button" class="btn btn-secondary" onclick="checkMailketingCredits()">
                    <i data-feather="refresh-cw" width="16" height="16"></i>
                    Cek Credits
                </button>
            </div>
        </form>
    </div>

    <!-- Tab 2: Email Templates -->
    <div class="tab-content" id="email-templates">
        <form method="POST" action="<?= epic_url('admin/settings/mailketing') ?>" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?= epic_csrf_token() ?>">
            
            <!-- Welcome Email -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">
                        <i data-feather="user-plus" class="settings-card-icon"></i>
                        Welcome Email
                    </h3>
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="welcome_email_enabled" 
                               name="welcome_email_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($settings['welcome_email_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="welcome_email_enabled" class="form-checkbox-label">
                            Aktifkan
                        </label>
                    </div>
                </div>
                
                <div class="settings-card-body">
                    <div class="form-group">
                        <label class="form-label" for="welcome_email_subject">Subject</label>
                        <input type="text" 
                               id="welcome_email_subject" 
                               name="welcome_email_subject" 
                               class="form-input" 
                               placeholder="Selamat Datang di EPIC Hub!"
                               value="<?= htmlspecialchars($settings['welcome_email_subject'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="welcome_email_template">Template</label>
                        <textarea id="welcome_email_template" 
                                  name="welcome_email_template" 
                                  class="form-textarea" 
                                  rows="6"
                                  placeholder="Terima kasih telah bergabung dengan EPIC Hub..."><?= htmlspecialchars($settings['welcome_email_template'] ?? '') ?></textarea>
                        <p class="form-help-text">Gunakan {name}, {email}, {confirmation_link} sebagai placeholder</p>
                    </div>
                </div>
            </div>
            
            <!-- Order Confirmation Email -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">
                        <i data-feather="shopping-cart" class="settings-card-icon"></i>
                        Order Confirmation Email
                    </h3>
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="order_confirmation_enabled" 
                               name="order_confirmation_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($settings['order_confirmation_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="order_confirmation_enabled" class="form-checkbox-label">
                            Aktifkan
                        </label>
                    </div>
                </div>
                
                <div class="settings-card-body">
                    <div class="form-group">
                        <label class="form-label" for="order_confirmation_subject">Subject</label>
                        <input type="text" 
                               id="order_confirmation_subject" 
                               name="order_confirmation_subject" 
                               class="form-input" 
                               placeholder="Konfirmasi Pesanan - EPIC Hub"
                               value="<?= htmlspecialchars($settings['order_confirmation_subject'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="order_confirmation_template">Template</label>
                        <textarea id="order_confirmation_template" 
                                  name="order_confirmation_template" 
                                  class="form-textarea" 
                                  rows="6"
                                  placeholder="Pesanan Anda telah dikonfirmasi..."><?= htmlspecialchars($settings['order_confirmation_template'] ?? '') ?></textarea>
                        <p class="form-help-text">Gunakan {name}, {order_id}, {product_name}, {amount} sebagai placeholder</p>
                    </div>
                </div>
            </div>
            
            <!-- Password Reset Email -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">
                        <i data-feather="lock" class="settings-card-icon"></i>
                        Password Reset Email
                    </h3>
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="password_reset_enabled" 
                               name="password_reset_enabled" 
                               value="1" 
                               class="form-checkbox"
                               <?= ($settings['password_reset_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <label for="password_reset_enabled" class="form-checkbox-label">
                            Aktifkan
                        </label>
                    </div>
                </div>
                
                <div class="settings-card-body">
                    <div class="form-group">
                        <label class="form-label" for="password_reset_subject">Subject</label>
                        <input type="text" 
                               id="password_reset_subject" 
                               name="password_reset_subject" 
                               class="form-input" 
                               placeholder="Reset Password - EPIC Hub"
                               value="<?= htmlspecialchars($settings['password_reset_subject'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password_reset_template">Template</label>
                        <textarea id="password_reset_template" 
                                  name="password_reset_template" 
                                  class="form-textarea" 
                                  rows="6"
                                  placeholder="Klik link berikut untuk reset password..."><?= htmlspecialchars($settings['password_reset_template'] ?? '') ?></textarea>
                        <p class="form-help-text">Gunakan {name}, {reset_link}, {expire_time} sebagai placeholder</p>
                    </div>
                </div>
            </div>
            
            <div class="settings-actions">
                <button type="submit" name="save_email_templates" class="btn btn-primary">
                    <i data-feather="save" width="16" height="16"></i>
                    Simpan Template
                </button>
                <button type="button" class="btn btn-secondary" onclick="previewEmailTemplate()">
                    <i data-feather="eye" width="16" height="16"></i>
                    Preview Template
                </button>
            </div>
        </form>
    </div>

    <!-- Tab 3: List Management -->
    <div class="tab-content" id="list-management">
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">
                    <i data-feather="users" class="settings-card-icon"></i>
                    Mailketing Lists
                </h3>
                <p class="settings-card-description">
                    Kelola subscriber lists dari akun Mailketing Anda <mcreference link="https://mailketing.co.id/docs/api-get-all-list-from-account/" index="1">1</mcreference>
                </p>
            </div>
            
            <div class="settings-card-body">
                <div class="list-management-actions">
                    <button type="button" class="btn btn-primary" onclick="loadMailketingLists()">
                        <i data-feather="refresh-cw" width="16" height="16"></i>
                        Load Lists
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="syncSubscribers()">
                        <i data-feather="users" width="16" height="16"></i>
                        Sync Subscribers
                    </button>
                </div>
                
                <div id="mailketing-lists-container" class="lists-container">
                    <p class="text-muted">Klik "Load Lists" untuk melihat daftar lists dari Mailketing</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 4: Webhook Settings -->
    <div class="tab-content" id="webhook-settings">
        <form method="POST" action="<?= epic_url('admin/settings/mailketing') ?>" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?= epic_csrf_token() ?>">
            
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">
                        <i data-feather="link" class="settings-card-icon"></i>
                        Webhook Configuration
                    </h3>
                    <p class="settings-card-description">
                        Konfigurasi webhook untuk menerima notifikasi dari Mailketing <mcreference link="https://mailketing.co.id/docs/webhook/" index="4">4</mcreference>
                    </p>
                </div>
                
                <div class="settings-card-body">
                    <div class="form-group">
                        <label class="form-label" for="mailketing_webhook_url">
                            Webhook URL
                        </label>
                        <input type="url" 
                               id="mailketing_webhook_url" 
                               name="mailketing_webhook_url" 
                               class="form-input" 
                               placeholder="<?= epic_url('api/mailketing/webhook') ?>"
                               value="<?= htmlspecialchars($settings['mailketing_webhook_url'] ?? '') ?>">
                        <p class="form-help-text">URL ini harus diset di dashboard Mailketing untuk menerima webhook</p>
                    </div>
                    
                    <div class="webhook-events">
                        <h4>Webhook Events</h4>
                        <div class="webhook-event-list">
                            <div class="webhook-event">
                                <i data-feather="user-plus"></i>
                                <span>New Subscriber</span>
                                <span class="event-status active">Active</span>
                            </div>
                            <div class="webhook-event">
                                <i data-feather="user-minus"></i>
                                <span>Unsubscribe</span>
                                <span class="event-status active">Active</span>
                            </div>
                            <div class="webhook-event">
                                <i data-feather="mail-open"></i>
                                <span>Email Open</span>
                                <span class="event-status active">Active</span>
                            </div>
                            <div class="webhook-event">
                                <i data-feather="mouse-pointer"></i>
                                <span>Email Click</span>
                                <span class="event-status active">Active</span>
                            </div>
                            <div class="webhook-event">
                                <i data-feather="x-circle"></i>
                                <span>Email Bounce</span>
                                <span class="event-status active">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="settings-actions">
                <button type="submit" name="save_webhook_settings" class="btn btn-primary">
                    <i data-feather="save" width="16" height="16"></i>
                    Simpan Webhook
                </button>
                <button type="button" class="btn btn-secondary" onclick="testWebhook()">
                    <i data-feather="send" width="16" height="16"></i>
                    Test Webhook
                </button>
            </div>
        </form>
    </div>

    <!-- Tab 5: Monitoring -->
    <div class="tab-content" id="monitoring">
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">
                    <i data-feather="activity" class="settings-card-icon"></i>
                    Email Statistics
                </h3>
                <p class="settings-card-description">
                    Monitor performa email dan statistik pengiriman
                </p>
            </div>
            
            <div class="settings-card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i data-feather="send"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="emails-sent">0</h4>
                            <p>Emails Sent</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i data-feather="mail-open"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="emails-opened">0</h4>
                            <p>Emails Opened</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i data-feather="mouse-pointer"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="emails-clicked">0</h4>
                            <p>Emails Clicked</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i data-feather="x-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="emails-bounced">0</h4>
                            <p>Emails Bounced</p>
                        </div>
                    </div>
                </div>
                
                <div class="monitoring-actions">
                    <button type="button" class="btn btn-primary" onclick="loadEmailStats()">
                        <i data-feather="refresh-cw" width="16" height="16"></i>
                        Refresh Stats
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="exportEmailLogs()">
                        <i data-feather="download" width="16" height="16"></i>
                        Export Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

/* Mailketing Status Dashboard */
.mailketing-status-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-6);
}

.status-card {
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    padding: var(--spacing-4);
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.status-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-icon.status-success {
    background: rgba(34, 197, 94, 0.2);
    color: var(--green-400);
}

.status-icon.status-warning {
    background: rgba(251, 191, 36, 0.2);
    color: var(--yellow-400);
}

.status-icon.status-error {
    background: rgba(239, 68, 68, 0.2);
    color: var(--red-400);
}

.status-icon.status-info {
    background: rgba(59, 130, 246, 0.2);
    color: var(--blue-400);
}

.status-info h4 {
    margin: 0;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-200);
}

.status-info p {
    margin: 0;
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

/* Settings Tabs */
.settings-tabs {
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-2xl);
    overflow: hidden;
}

.tab-navigation {
    display: flex;
    background: var(--surface-3);
    border-bottom: 1px solid var(--ink-700);
    overflow-x: auto;
}

.tab-button {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-4) var(--spacing-6);
    background: none;
    border: none;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    cursor: pointer;
    transition: all var(--transition-normal);
    white-space: nowrap;
    border-bottom: 3px solid transparent;
}

.tab-button:hover {
    color: var(--ink-100);
    background: var(--surface-2);
}

.tab-button.active {
    color: var(--gold-400);
    background: var(--surface-1);
    border-bottom-color: var(--gold-400);
}

.tab-content {
    display: none;
    padding: var(--spacing-6);
}

.tab-content.active {
    display: block;
}

/* List Management */
.list-management-actions {
    display: flex;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-4);
}

.lists-container {
    min-height: 200px;
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    padding: var(--spacing-4);
}

/* Webhook Events */
.webhook-events {
    margin-top: var(--spacing-4);
}

.webhook-event-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
    margin-top: var(--spacing-3);
}

.webhook-event {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-3);
    background: var(--surface-3);
    border-radius: var(--radius-lg);
}

.event-status {
    margin-left: auto;
    padding: var(--spacing-1) var(--spacing-3);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

.event-status.active {
    background: rgba(34, 197, 94, 0.2);
    color: var(--green-400);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-4);
}

.stat-card {
    background: var(--surface-3);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    padding: var(--spacing-4);
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-lg);
    background: rgba(59, 130, 246, 0.2);
    color: var(--blue-400);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-info h4 {
    margin: 0;
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    color: var(--ink-100);
}

.stat-info p {
    margin: 0;
    font-size: var(--font-size-sm);
    color: var(--ink-400);
}

.monitoring-actions {
    display: flex;
    gap: var(--spacing-3);
}

/* Responsive */
@media (max-width: 768px) {
    .tab-navigation {
        flex-direction: column;
    }
    
    .mailketing-status-dashboard {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
});

// Check Mailketing credits
function checkMailketingCredits() {
    fetch('<?= epic_url('api/mailketing/credits') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('mailketing-credits').textContent = data.credits;
            alert('Credits: ' + data.credits);
        } else {
            alert('Gagal mengecek credits: ' + data.error);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat mengecek credits');
    });
}

// Load Mailketing lists
function loadMailketingLists() {
    const container = document.getElementById('mailketing-lists-container');
    container.innerHTML = '<p>Loading...</p>';
    
    fetch('<?= epic_url('api/mailketing/lists') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.lists) {
            let html = '<div class="lists-grid">';
            data.lists.forEach(list => {
                html += `
                    <div class="list-item">
                        <h4>List ID: ${list.list_id}</h4>
                        <p>${list.list_name}</p>
                        <button class="btn btn-sm btn-secondary" onclick="viewListSubscribers(${list.list_id})">
                            View Subscribers
                        </button>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-error">Gagal memuat lists: ' + (data.error || 'Unknown error') + '</p>';
        }
    })
    .catch(error => {
        container.innerHTML = '<p class="text-error">Terjadi kesalahan saat memuat lists</p>';
    });
}

// Sync subscribers
function syncSubscribers() {
    if (!confirm('Sync semua subscribers dari database ke Mailketing?')) return;
    
    fetch('<?= epic_url('api/mailketing/sync-subscribers') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Sync berhasil! ' + data.synced + ' subscribers telah disync.');
        } else {
            alert('Sync gagal: ' + data.error);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat sync subscribers');
    });
}

// Preview email template
function previewEmailTemplate() {
    const subject = document.getElementById('welcome_email_subject').value;
    const template = document.getElementById('welcome_email_template').value;
    
    const previewWindow = window.open('', '_blank', 'width=600,height=400');
    previewWindow.document.write(`
        <html>
            <head><title>Email Preview</title></head>
            <body style="font-family: Arial, sans-serif; padding: 20px;">
                <h3>Subject: ${subject}</h3>
                <hr>
                <div>${template.replace(/\n/g, '<br>')}</div>
            </body>
        </html>
    `);
}

// Test webhook
function testWebhook() {
    fetch('<?= epic_url('api/mailketing/test-webhook') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test webhook berhasil!');
        } else {
            alert('Test webhook gagal: ' + data.error);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat test webhook');
    });
}

// Load email stats
function loadEmailStats() {
    fetch('<?= epic_url('api/mailketing/stats') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('emails-sent').textContent = data.stats.sent || 0;
            document.getElementById('emails-opened').textContent = data.stats.opened || 0;
            document.getElementById('emails-clicked').textContent = data.stats.clicked || 0;
            document.getElementById('emails-bounced').textContent = data.stats.bounced || 0;
        } else {
            alert('Gagal memuat statistik: ' + data.error);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat memuat statistik');
    });
}

// Export email logs
function exportEmailLogs() {
    window.open('<?= epic_url('api/mailketing/export-logs') ?>', '_blank');
}
</script>