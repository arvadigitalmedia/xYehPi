<?php
/**
 * EPIC Hub Admin Content - WhatsApp Notification Settings
 * Pengaturan notifikasi WhatsApp menggunakan Starsender API
 * 
 * @package EPIC Hub
 * @version 1.0.0
 */

// Security check
if (!defined('EPIC_INIT')) {
    die('Direct access not permitted');
}

// Variables are now passed directly from layout_data
// $success, $error, $starsender_settings, $shortcodes, $user are available
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
    
    <!-- WhatsApp Notification Settings Form -->
    <form method="POST" action="<?= epic_url('admin/settings/whatsapp-notification') ?>" class="settings-form">
        <input type="hidden" name="csrf_token" value="<?= epic_csrf_token() ?>">
        
        <!-- Starsender API Configuration -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">
                    <i data-feather="message-circle" class="settings-card-icon"></i>
                    Konfigurasi Starsender API
                </h3>
                <p class="settings-card-description">
                    Pengaturan koneksi dengan Starsender WhatsApp Gateway API untuk notifikasi otomatis
                </p>
            </div>
            
            <div class="settings-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <div class="form-checkbox">
                            <input type="checkbox" 
                                   id="starsender_enabled" 
                                   name="starsender_enabled" 
                                   value="1" 
                                   <?= ($starsender_settings['starsender_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                            <label for="starsender_enabled" class="form-checkbox-label">
                                <i data-feather="power" width="16" height="16"></i>
                                Aktifkan Notifikasi WhatsApp Starsender
                            </label>
                        </div>
                        <div class="form-help">
                            Centang untuk mengaktifkan sistem notifikasi WhatsApp menggunakan Starsender API
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="starsender_api_key">
                            API Key Starsender
                            <span class="form-label-required">*</span>
                        </label>
                        <input type="password" 
                               id="starsender_api_key" 
                               name="starsender_api_key" 
                               class="form-input" 
                               placeholder="Masukkan API Key Starsender" 
                               value="<?= htmlspecialchars($starsender_settings['starsender_api_key'] ?? '') ?>">
                        <div class="form-help">
                            <i data-feather="key" width="14" height="14"></i>
                            Dapatkan API Key di <a href="https://starsender.online/dashboard" target="_blank" class="external-link">Dashboard Starsender</a>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="test_phone_number">
                            Nomor Tujuan Test
                            <span class="form-label-required">*</span>
                        </label>
                        <input type="text" 
                               id="test_phone_number" 
                               name="test_phone_number" 
                               class="form-input" 
                               placeholder="628123456789" 
                               value="<?= htmlspecialchars($starsender_settings['test_phone_number'] ?? '6281234567890') ?>">
                        <div class="form-help">
                            <i data-feather="phone" width="14" height="14"></i>
                            Nomor WhatsApp tujuan untuk test koneksi (format: 628xxxxxxxxx)
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" onclick="testStarsenderConnection()">
                            <i data-feather="wifi" width="16" height="16"></i>
                            Test Koneksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notification Messages Configuration -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">
                    <i data-feather="bell" class="settings-card-icon"></i>
                    Pengaturan Pesan Notifikasi WhatsApp
                </h3>
                <p class="settings-card-description">
                    Konfigurasi pesan notifikasi untuk berbagai aktivitas user dan alur bisnis
                </p>
            </div>
            
            <div class="settings-card-body">
                <!-- 1. Registration Notifications -->
                <div class="notification-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i data-feather="user-plus" width="18" height="18"></i>
                            1. Notifikasi Pendaftaran User Baru
                        </h4>
                        <button type="button" class="btn btn-sm btn-outline" onclick="testNotification('registration')">
                            <i data-feather="send" width="14" height="14"></i>
                            Test Notifikasi
                        </button>
                    </div>
                    
                    <div class="notification-types">
                        <!-- User Registration Message -->
                        <div class="notification-type">
                            <h5 class="notification-type-title">
                                <i data-feather="user" width="16" height="16"></i>
                                Pesan untuk User yang Mendaftar
                            </h5>
                            
                            <div class="account-level-tabs">
                                <div class="tab-nav">
                                    <button type="button" class="tab-btn active" data-tab="reg-user-free">Free Account</button>
                                    <button type="button" class="tab-btn" data-tab="reg-user-epic">Epic Account</button>
                                    <button type="button" class="tab-btn" data-tab="reg-user-epis">Epis Account</button>
                                </div>
                                
                                <div class="tab-content active" id="reg-user-free">
                                    <div class="form-group">
                                        <label class="form-label" for="starsender_registration_user_free_message">
                                            Pesan Selamat Datang - Free Account
                                        </label>
                                        <textarea id="starsender_registration_user_free_message" 
                                                  name="starsender_registration_user_free_message" 
                                                  class="form-textarea" 
                                                  rows="4" 
                                                  placeholder="Selamat datang [user_name]! Akun Free Anda telah berhasil dibuat di [site_name]. Kode referral Anda: [user_referral_code]"><?= htmlspecialchars($starsender_settings['starsender_registration_user_free_message'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="tab-content" id="reg-user-epic">
                                    <div class="form-group">
                                        <label class="form-label" for="starsender_registration_user_epic_message">
                                            Pesan Selamat Datang - Epic Account
                                        </label>
                                        <textarea id="starsender_registration_user_epic_message" 
                                                  name="starsender_registration_user_epic_message" 
                                                  class="form-textarea" 
                                                  rows="4" 
                                                  placeholder="Selamat datang [user_name]! Akun Epic Anda telah berhasil dibuat di [site_name]. Nikmati fitur premium kami!"><?= htmlspecialchars($starsender_settings['starsender_registration_user_epic_message'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="tab-content" id="reg-user-epis">
                                    <div class="form-group">
                                        <label class="form-label" for="starsender_registration_user_epis_message">
                                            Pesan Selamat Datang - Epis Account
                                        </label>
                                        <textarea id="starsender_registration_user_epis_message" 
                                                  name="starsender_registration_user_epis_message" 
                                                  class="form-textarea" 
                                                  rows="4" 
                                                  placeholder="Selamat datang [user_name]! Akun Epis Anda telah berhasil dibuat di [site_name]. Anda sekarang adalah supervisor!"><?= htmlspecialchars($starsender_settings['starsender_registration_user_epis_message'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Referral Registration Message -->
                        <div class="notification-type">
                            <h5 class="notification-type-title">
                                <i data-feather="users" width="16" height="16"></i>
                                Pesan untuk Referral/Sponsor (EPIC Account)
                            </h5>
                            
                            <div class="form-group">
                                <label class="form-label" for="starsender_registration_referral_message">
                                    Notifikasi Referral Baru
                                </label>
                                <textarea id="starsender_registration_referral_message" 
                                          name="starsender_registration_referral_message" 
                                          class="form-textarea" 
                                          rows="4" 
                                          placeholder="Selamat [sponsor_name]! Anda mendapat referral baru: [user_name] ([user_level]). Bergabung: [user_join_date]"><?= htmlspecialchars($starsender_settings['starsender_registration_referral_message'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="starsender_registration_image">
                                URL Gambar (Opsional)
                            </label>
                            <input type="url" 
                                   id="starsender_registration_image" 
                                   name="starsender_registration_image" 
                                   class="form-input" 
                                   placeholder="https://example.com/welcome-image.jpg" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_registration_image'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="starsender_registration_button">
                                Teks Button (Opsional)
                            </label>
                            <input type="text" 
                                   id="starsender_registration_button" 
                                   name="starsender_registration_button" 
                                   class="form-input" 
                                   placeholder="Mulai Sekarang" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_registration_button'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 2. Upgrade Notifications -->
                <div class="notification-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i data-feather="trending-up" width="18" height="18"></i>
                            2. Notifikasi Upgrade Akun Free ke EPIC
                        </h4>
                        <button type="button" class="btn btn-sm btn-outline" onclick="testNotification('upgrade')">
                            <i data-feather="send" width="14" height="14"></i>
                            Test Notifikasi
                        </button>
                    </div>
                    
                    <div class="notification-types">
                        <!-- User Upgrade Message -->
                        <div class="notification-type">
                            <h5 class="notification-type-title">
                                <i data-feather="user-check" width="16" height="16"></i>
                                Pesan untuk Member yang Upgrade
                            </h5>
                            
                            <div class="form-group">
                                <label class="form-label" for="starsender_upgrade_user_message">
                                    Notifikasi Upgrade Berhasil
                                </label>
                                <textarea id="starsender_upgrade_user_message" 
                                          name="starsender_upgrade_user_message" 
                                          class="form-textarea" 
                                          rows="4" 
                                          placeholder="Selamat [user_name]! Akun Anda telah berhasil diupgrade ke Epic Account. Nikmati fitur premium di [site_name]!"><?= htmlspecialchars($starsender_settings['starsender_upgrade_user_message'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Sponsor Upgrade Message -->
                        <div class="notification-type">
                            <h5 class="notification-type-title">
                                <i data-feather="award" width="16" height="16"></i>
                                Pesan untuk Sponsor/Referral
                            </h5>
                            
                            <div class="form-group">
                                <label class="form-label" for="starsender_upgrade_sponsor_message">
                                    Notifikasi Referral Upgrade
                                </label>
                                <textarea id="starsender_upgrade_sponsor_message" 
                                          name="starsender_upgrade_sponsor_message" 
                                          class="form-textarea" 
                                          rows="4" 
                                          placeholder="Selamat [sponsor_name]! Referral Anda [user_name] telah upgrade ke Epic Account. Anda mendapat komisi!"><?= htmlspecialchars($starsender_settings['starsender_upgrade_sponsor_message'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="starsender_upgrade_image">
                                URL Gambar (Opsional)
                            </label>
                            <input type="url" 
                                   id="starsender_upgrade_image" 
                                   name="starsender_upgrade_image" 
                                   class="form-input" 
                                   placeholder="https://example.com/upgrade-image.jpg" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_upgrade_image'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="starsender_upgrade_button">
                                Teks Button (Opsional)
                            </label>
                            <input type="text" 
                                   id="starsender_upgrade_button" 
                                   name="starsender_upgrade_button" 
                                   class="form-input" 
                                   placeholder="Lihat Fitur Premium" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_upgrade_button'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 3. Purchase Notifications -->
                <div class="notification-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i data-feather="shopping-cart" width="18" height="18"></i>
                            3. Notifikasi Pembelian Produk
                        </h4>
                        <button type="button" class="btn btn-sm btn-outline" onclick="testNotification('purchase')">
                            <i data-feather="send" width="14" height="14"></i>
                            Test Notifikasi
                        </button>
                    </div>
                    
                    <div class="notification-types">
                        <!-- Buyer Purchase Message -->
                        <div class="notification-type">
                            <h5 class="notification-type-title">
                                <i data-feather="user" width="16" height="16"></i>
                                Pesan untuk Member Pembeli
                            </h5>
                            
                            <div class="form-group">
                                <label class="form-label" for="starsender_purchase_buyer_message">
                                    Notifikasi Pembelian Berhasil
                                </label>
                                <textarea id="starsender_purchase_buyer_message" 
                                          name="starsender_purchase_buyer_message" 
                                          class="form-textarea" 
                                          rows="4" 
                                          placeholder="Terima kasih [user_name]! Pembelian [product_name] sebesar [order_total] telah berhasil. Order ID: [order_id]"><?= htmlspecialchars($starsender_settings['starsender_purchase_buyer_message'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Referral Purchase Message -->
                        <div class="notification-type">
                            <h5 class="notification-type-title">
                                <i data-feather="dollar-sign" width="16" height="16"></i>
                                Pesan untuk Referral Member
                            </h5>
                            
                            <div class="form-group">
                                <label class="form-label" for="starsender_purchase_referral_message">
                                    Notifikasi Pembelian Referral
                                </label>
                                <textarea id="starsender_purchase_referral_message" 
                                          name="starsender_purchase_referral_message" 
                                          class="form-textarea" 
                                          rows="4" 
                                          placeholder="Selamat [sponsor_name]! Referral Anda [user_name] telah membeli [product_name] sebesar [order_total]. Anda mendapat komisi!"><?= htmlspecialchars($starsender_settings['starsender_purchase_referral_message'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="starsender_purchase_image">
                                URL Gambar (Opsional)
                            </label>
                            <input type="url" 
                                   id="starsender_purchase_image" 
                                   name="starsender_purchase_image" 
                                   class="form-input" 
                                   placeholder="https://example.com/purchase-image.jpg" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_purchase_image'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="starsender_purchase_button">
                                Teks Button (Opsional)
                            </label>
                            <input type="text" 
                                   id="starsender_purchase_button" 
                                   name="starsender_purchase_button" 
                                   class="form-input" 
                                   placeholder="Lihat Pesanan" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_purchase_button'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 4. Payout Notifications -->
                <div class="notification-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i data-feather="credit-card" width="18" height="18"></i>
                            4. Notifikasi Pencairan Komisi/Payout
                        </h4>
                        <button type="button" class="btn btn-sm btn-outline" onclick="testNotification('payout')">
                            <i data-feather="send" width="14" height="14"></i>
                            Test Notifikasi
                        </button>
                    </div>
                    
                    <div class="notification-types">
                        <div class="notification-type">
                            <h5 class="notification-type-title">
                                <i data-feather="banknote" width="16" height="16"></i>
                                Pesan untuk Member yang Menerima Payout
                            </h5>
                            
                            <div class="form-group">
                                <label class="form-label" for="starsender_payout_message">
                                    Notifikasi Pencairan Komisi
                                </label>
                                <textarea id="starsender_payout_message" 
                                          name="starsender_payout_message" 
                                          class="form-textarea" 
                                          rows="4" 
                                          placeholder="Selamat [user_name]! Pencairan komisi sebesar [payout_amount] telah diproses ke [payout_method]. Tanggal: [payout_date]"><?= htmlspecialchars($starsender_settings['starsender_payout_message'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="starsender_payout_image">
                                URL Gambar (Opsional)
                            </label>
                            <input type="url" 
                                   id="starsender_payout_image" 
                                   name="starsender_payout_image" 
                                   class="form-input" 
                                   placeholder="https://example.com/payout-image.jpg" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_payout_image'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="starsender_payout_button">
                                Teks Button (Opsional)
                            </label>
                            <input type="text" 
                                   id="starsender_payout_button" 
                                   name="starsender_payout_button" 
                                   class="form-input" 
                                   placeholder="Lihat Riwayat" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_payout_button'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 5. Closing EPIC Account Notifications -->
                <div class="notification-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i data-feather="target" width="18" height="18"></i>
                            5. Notifikasi Closing EPIC Account
                        </h4>
                        <button type="button" class="btn btn-sm btn-outline" onclick="testNotification('closing')">
                            <i data-feather="send" width="14" height="14"></i>
                            Test Notifikasi
                        </button>
                    </div>
                    
                    <div class="notification-types">
                        <div class="notification-type">
                            <h5 class="notification-type-title">
                                <i data-feather="crown" width="16" height="16"></i>
                                Pesan untuk EPIS Account (Supervisor)
                            </h5>
                            
                            <div class="form-group">
                                <label class="form-label" for="starsender_closing_epis_message">
                                    Notifikasi Closing dari Jaringan
                                </label>
                                <textarea id="starsender_closing_epis_message" 
                                          name="starsender_closing_epis_message" 
                                          class="form-textarea" 
                                          rows="4" 
                                          placeholder="Selamat [epis_name]! Ada closing baru di jaringan Anda: [user_name] upgrade ke Epic Account melalui referral [sponsor_name]. Tanggal: [current_date]"><?= htmlspecialchars($starsender_settings['starsender_closing_epis_message'] ?? '') ?></textarea>
                                <div class="form-help">
                                    <i data-feather="info" width="14" height="14"></i>
                                    Notifikasi ini dikirim ke EPIS Account ketika ada Free Account yang upgrade melalui referral EPIC Account di jaringannya
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="starsender_closing_image">
                                URL Gambar (Opsional)
                            </label>
                            <input type="url" 
                                   id="starsender_closing_image" 
                                   name="starsender_closing_image" 
                                   class="form-input" 
                                   placeholder="https://example.com/closing-image.jpg" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_closing_image'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="starsender_closing_button">
                                Teks Button (Opsional)
                            </label>
                            <input type="text" 
                                   id="starsender_closing_button" 
                                   name="starsender_closing_button" 
                                   class="form-input" 
                                   placeholder="Lihat Jaringan" 
                                   value="<?= htmlspecialchars($starsender_settings['starsender_closing_button'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Shortcode Reference -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">
                    <i data-feather="code" class="settings-card-icon"></i>
                    Panduan Shortcode
                </h3>
                <p class="settings-card-description">
                    Gunakan shortcode berikut untuk personalisasi pesan notifikasi
                </p>
            </div>
            
            <div class="settings-card-body">
                <div class="shortcode-grid">
                    <div class="shortcode-category">
                        <h4 class="shortcode-category-title">
                            <i data-feather="user" width="16" height="16"></i>
                            Data User
                        </h4>
                        <div class="shortcode-list">
                            <div class="shortcode-item">
                                <code class="shortcode-code">[user_name]</code>
                                <span class="shortcode-desc">Nama lengkap user</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[user_email]</code>
                                <span class="shortcode-desc">Email user</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[user_phone]</code>
                                <span class="shortcode-desc">Nomor telepon user</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[user_level]</code>
                                <span class="shortcode-desc">Level akun (Free/Epic/Epis)</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[user_referral_code]</code>
                                <span class="shortcode-desc">Kode referral user</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[user_join_date]</code>
                                <span class="shortcode-desc">Tanggal bergabung</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="shortcode-category">
                        <h4 class="shortcode-category-title">
                            <i data-feather="users" width="16" height="16"></i>
                            Data Sponsor/Referral
                        </h4>
                        <div class="shortcode-list">
                            <div class="shortcode-item">
                                <code class="shortcode-code">[sponsor_name]</code>
                                <span class="shortcode-desc">Nama sponsor/referrer</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[sponsor_email]</code>
                                <span class="shortcode-desc">Email sponsor</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[sponsor_phone]</code>
                                <span class="shortcode-desc">Nomor telepon sponsor</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[sponsor_level]</code>
                                <span class="shortcode-desc">Level akun sponsor</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[epis_name]</code>
                                <span class="shortcode-desc">Nama EPIS Account (Supervisor)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="shortcode-category">
                        <h4 class="shortcode-category-title">
                            <i data-feather="shopping-bag" width="16" height="16"></i>
                            Data Order/Produk
                        </h4>
                        <div class="shortcode-list">
                            <div class="shortcode-item">
                                <code class="shortcode-code">[order_id]</code>
                                <span class="shortcode-desc">ID order</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[order_total]</code>
                                <span class="shortcode-desc">Total order (format rupiah)</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[product_name]</code>
                                <span class="shortcode-desc">Nama produk</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[order_date]</code>
                                <span class="shortcode-desc">Tanggal order</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[order_status]</code>
                                <span class="shortcode-desc">Status order</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="shortcode-category">
                        <h4 class="shortcode-category-title">
                            <i data-feather="credit-card" width="16" height="16"></i>
                            Data Payout
                        </h4>
                        <div class="shortcode-list">
                            <div class="shortcode-item">
                                <code class="shortcode-code">[payout_amount]</code>
                                <span class="shortcode-desc">Jumlah payout (format rupiah)</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[payout_date]</code>
                                <span class="shortcode-desc">Tanggal payout</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[payout_method]</code>
                                <span class="shortcode-desc">Metode payout</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="shortcode-category">
                        <h4 class="shortcode-category-title">
                            <i data-feather="globe" width="16" height="16"></i>
                            Data Sistem
                        </h4>
                        <div class="shortcode-list">
                            <div class="shortcode-item">
                                <code class="shortcode-code">[site_name]</code>
                                <span class="shortcode-desc">Nama website</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[site_url]</code>
                                <span class="shortcode-desc">URL website</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[current_date]</code>
                                <span class="shortcode-desc">Tanggal hari ini</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[current_time]</code>
                                <span class="shortcode-desc">Waktu saat ini</span>
                            </div>
                            <div class="shortcode-item">
                                <code class="shortcode-code">[current_year]</code>
                                <span class="shortcode-desc">Tahun saat ini</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="usage-info">
                    <h4>💡 Tips Penggunaan</h4>
                    <p>• Gunakan shortcode di dalam tanda kurung siku [ ] untuk menampilkan data dinamis</p>
                    <p>• Shortcode akan otomatis diganti dengan data sebenarnya saat notifikasi dikirim</p>
                    <p>• Kombinasikan shortcode dengan teks biasa untuk membuat pesan yang personal dan informatif</p>
                    <p>• Test notifikasi untuk memastikan shortcode berfungsi dengan benar</p>
                </div>
            </div>
        </div>
        
    <!-- Save Button -->
    <div class="settings-actions">
        <button type="submit" name="save_whatsapp_notification_settings" class="btn btn-primary">
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

/* Settings Form Styles */
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
    transition: all var(--transition-normal);
}

.settings-card:hover {
    border-color: var(--gold-400);
    box-shadow: var(--shadow-lg);
}

.settings-card-header {
    background: var(--surface-3);
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-700);
}

.settings-card-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin: 0 0 var(--spacing-2) 0;
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
}

.settings-card-icon {
    color: var(--gold-400);
    width: 24px;
    height: 24px;
}

.settings-card-description {
    margin: 0;
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    line-height: 1.5;
}

.settings-card-body {
    padding: var(--spacing-6);
}

.settings-actions {
    display: flex;
    gap: var(--spacing-4);
    align-items: center;
    justify-content: flex-start;
    margin-top: var(--spacing-8);
}

/* Notification Section Styles */
.notification-section {
    margin-bottom: var(--spacing-8);
    padding: var(--spacing-6);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-6);
    padding-bottom: var(--spacing-4);
    border-bottom: 1px solid var(--ink-600);
}

.section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0;
}

.notification-types {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}

.notification-type {
    padding: var(--spacing-4);
    background: var(--surface-2);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-md);
}

.notification-type-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-md);
    font-weight: var(--font-weight-medium);
    color: var(--ink-200);
    margin: 0 0 var(--spacing-4) 0;
}

/* Account Level Tabs */
.account-level-tabs {
    margin-bottom: var(--spacing-4);
}

.tab-nav {
    display: flex;
    gap: var(--spacing-1);
    margin-bottom: var(--spacing-4);
    border-bottom: 1px solid var(--ink-600);
}

.tab-btn {
    background: none;
    border: none;
    padding: var(--spacing-3) var(--spacing-4);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-400);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
}

.tab-btn:hover {
    color: var(--ink-200);
    background: var(--surface-2);
}

.tab-btn.active {
    color: var(--blue-500);
    border-bottom-color: var(--blue-500);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Shortcode Styles */
.shortcode-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-6);
    margin-bottom: var(--spacing-6);
}

.shortcode-category {
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    padding: var(--spacing-4);
}

.shortcode-category-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-md);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-4) 0;
}

.shortcode-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.shortcode-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-2);
    background: var(--surface-3);
    border-radius: var(--radius-sm);
}

.shortcode-code {
    background: var(--blue-900);
    color: var(--blue-300);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-sm);
    font-family: 'Courier New', monospace;
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    min-width: 120px;
    text-align: center;
}

.shortcode-desc {
    font-size: var(--font-size-xs);
    color: var(--ink-300);
    flex: 1;
}

.usage-info {
    background: var(--surface-2);
    border: 1px solid var(--blue-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-4);
}

.usage-info h4 {
    font-size: var(--font-size-md);
    font-weight: var(--font-weight-semibold);
    color: var(--blue-400);
    margin: 0 0 var(--spacing-2) 0;
}

.usage-info p {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
    margin: 0 0 var(--spacing-2) 0;
    line-height: 1.5;
}

.usage-info p:last-child {
    margin-bottom: 0;
}

/* Button Styles - Consistent with components.css */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-2);
    padding: var(--spacing-3) var(--spacing-5);
    border: 1px solid transparent;
    border-radius: var(--radius-lg);
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-sm);
    text-decoration: none;
    cursor: pointer;
    transition: all var(--transition-fast);
    min-height: 44px;
    white-space: nowrap;
}

.btn:focus {
    outline: 2px solid var(--gold-400);
    outline-offset: 2px;
}

.btn:active {
    transform: scale(0.99);
}

.btn-primary {
    background: var(--gradient-gold);
    color: var(--ink-900);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-gold-lg);
}

.btn-secondary {
    background: var(--surface-3);
    color: var(--ink-100);
    border-color: var(--ink-600);
}

.btn-secondary:hover {
    background: var(--surface-4);
    border-color: var(--gold-400);
    color: var(--gold-400);
}

.btn-outline {
    background: transparent;
    color: var(--ink-200);
    border-color: var(--ink-600);
}

.btn-outline:hover {
    background: var(--surface-3);
    border-color: var(--gold-400);
    color: var(--gold-400);
}

.btn-sm {
    padding: var(--spacing-2) var(--spacing-4);
    font-size: var(--font-size-xs);
    min-height: 36px;
}

.btn-lg {
    padding: var(--spacing-4) var(--spacing-6);
    font-size: var(--font-size-base);
    min-height: 52px;
}

.btn-icon {
    width: 44px;
    height: 44px;
    padding: 0;
}

.btn-icon.btn-sm {
    width: 36px;
    height: 36px;
}

.btn-icon.btn-lg {
    width: 52px;
    height: 52px;
}

/* Responsive Design */
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
    
    .shortcode-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        gap: var(--spacing-3);
        align-items: flex-start;
    }
    
    .tab-nav {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        flex: 1;
        min-width: 100px;
    }
}
</style>

<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            const tabGroup = this.closest('.account-level-tabs');
            
            // Remove active class from all tabs and contents in this group
            tabGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            tabGroup.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            const content = tabGroup.querySelector('#' + tabId);
            if (content) {
                content.classList.add('active');
            }
        });
    });
});

// Test Starsender connection
function testStarsenderConnection() {
    const apiKey = document.getElementById('starsender_api_key').value;
    const testPhone = document.getElementById('test_phone_number').value;
    const testButton = document.querySelector('button[onclick="testStarsenderConnection()"]');
    
    if (!apiKey) {
        alert('Mohon masukkan API Key Starsender terlebih dahulu');
        return;
    }
    
    if (!testPhone) {
        alert('Mohon masukkan nomor tujuan test terlebih dahulu');
        return;
    }
    
    // Validate phone number format
    if (!/^62\d{9,13}$/.test(testPhone)) {
        alert('Format nomor tidak valid. Gunakan format: 628xxxxxxxxx');
        return;
    }
    
    // Show loading state
    const originalText = testButton.innerHTML;
    testButton.innerHTML = '<i data-feather="loader" width="16" height="16" class="animate-spin"></i> Testing...';
    testButton.disabled = true;
    
    // Create form data
    const formData = new FormData();
    formData.append('test_connection', '1');
    formData.append('starsender_api_key', apiKey);
    formData.append('test_phone_number', testPhone);
    
    fetch('<?= epic_url('admin/settings/whatsapp-notification') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Terjadi kesalahan: ' + error.message);
    })
    .finally(() => {
        // Restore button state
        testButton.innerHTML = originalText;
        testButton.disabled = false;
        
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
}

// Test notification functions
function testNotification(type) {
    const apiKey = document.getElementById('starsender_api_key').value;
    
    if (!apiKey) {
        alert('Mohon masukkan API Key Starsender terlebih dahulu');
        return;
    }
    
    const testButton = event.target.closest('button');
    const originalText = testButton.innerHTML;
    testButton.innerHTML = '<i data-feather="loader" width="14" height="14" class="animate-spin"></i> Testing...';
    testButton.disabled = true;
    
    // Create form data
    const formData = new FormData();
    formData.append('test_notification', '1');
    formData.append('notification_type', type);
    formData.append('starsender_api_key', apiKey);
    
    fetch('<?= epic_url('admin/settings/whatsapp-notification') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Test notifikasi ' + type + ' berhasil dikirim!\n\n' + data.message);
        } else {
            alert('❌ Test notifikasi ' + type + ' gagal!\n\n' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Terjadi kesalahan: ' + error.message);
    })
    .finally(() => {
        // Restore button state
        testButton.innerHTML = originalText;
        testButton.disabled = false;
        
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
}
</script>

<?php
// Initialize feather icons
echo '<script>if (typeof feather !== "undefined") { feather.replace(); }</script>';
?>