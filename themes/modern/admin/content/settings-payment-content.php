<?php
/**
 * EPIC Hub Admin Settings Payment Content
 * Konten halaman settings payment gateway untuk layout global
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
        <a href="<?= epic_url('admin/settings/mailketing') ?>" class="settings-nav-item">
            <i data-feather="mail" class="settings-nav-icon"></i>
            <span>Mailketing</span>
        </a>
        <a href="<?= epic_url('admin/settings/whatsapp-notification') ?>" class="settings-nav-item">
            <i data-feather="message-circle" class="settings-nav-icon"></i>
            <span>WhatsApp Notification</span>
        </a>
        <a href="<?= epic_url('admin/settings/payment-gateway') ?>" class="settings-nav-item active">
            <i data-feather="credit-card" class="settings-nav-icon"></i>
            <span>Payment Gateway</span>
        </a>
    </nav>
</div>

<!-- Payment Settings Form -->
<form method="POST" action="<?= epic_url('admin/settings/payment-gateway') ?>" class="settings-form">
    <!-- General Payment Settings -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="settings" class="settings-card-icon"></i>
                General Payment Settings
            </h3>
            <p class="settings-card-description">
                Pengaturan umum untuk sistem pembayaran
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="payment_default_gateway">
                        Default Payment Gateway
                    </label>
                    <select id="payment_default_gateway" name="payment_default_gateway" class="form-input">
                        <option value="tripay" <?= ($payment_settings['payment_default_gateway'] ?? 'tripay') == 'tripay' ? 'selected' : '' ?>>Tripay</option>
                        <option value="midtrans" <?= ($payment_settings['payment_default_gateway'] ?? '') == 'midtrans' ? 'selected' : '' ?>>Midtrans</option>
                        <option value="paypal" <?= ($payment_settings['payment_default_gateway'] ?? '') == 'paypal' ? 'selected' : '' ?>>PayPal</option>
                        <option value="bank_transfer" <?= ($payment_settings['payment_default_gateway'] ?? '') == 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="payment_currency">
                        Currency
                    </label>
                    <select id="payment_currency" name="payment_currency" class="form-input">
                        <option value="IDR" <?= ($payment_settings['payment_currency'] ?? 'IDR') == 'IDR' ? 'selected' : '' ?>>Indonesian Rupiah (IDR)</option>
                        <option value="USD" <?= ($payment_settings['payment_currency'] ?? '') == 'USD' ? 'selected' : '' ?>>US Dollar (USD)</option>
                        <option value="EUR" <?= ($payment_settings['payment_currency'] ?? '') == 'EUR' ? 'selected' : '' ?>>Euro (EUR)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="payment_tax_rate">
                    Tax Rate (%)
                </label>
                <input type="number" 
                       id="payment_tax_rate" 
                       name="payment_tax_rate" 
                       class="form-input" 
                       placeholder="0" 
                       min="0" 
                       max="100" 
                       step="0.01" 
                       value="<?= htmlspecialchars($payment_settings['payment_tax_rate'] ?? '0') ?>">
                <div class="form-help">Tarif pajak yang akan ditambahkan ke setiap transaksi</div>
            </div>
        </div>
    </div>
    
    <!-- Tripay Configuration -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="zap" class="settings-card-icon"></i>
                Tripay Configuration
            </h3>
            <p class="settings-card-description">
                Konfigurasi payment gateway Tripay untuk pembayaran lokal Indonesia
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="form-checkbox-group">
                <input type="checkbox" 
                       id="tripay_enabled" 
                       name="tripay_enabled" 
                       value="1" 
                       class="form-checkbox"
                       <?= ($payment_settings['tripay_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="tripay_enabled" class="form-checkbox-label">
                    <strong>Enable Tripay</strong>
                </label>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="tripay_merchant_code">
                        Merchant Code
                    </label>
                    <input type="text" 
                           id="tripay_merchant_code" 
                           name="tripay_merchant_code" 
                           class="form-input" 
                           placeholder="T1234" 
                           value="<?= htmlspecialchars($payment_settings['tripay_merchant_code'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="tripay_api_key">
                        API Key
                    </label>
                    <input type="password" 
                           id="tripay_api_key" 
                           name="tripay_api_key" 
                           class="form-input" 
                           placeholder="••••••••••••••••" 
                           value="<?= htmlspecialchars($payment_settings['tripay_api_key'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="tripay_private_key">
                    Private Key
                </label>
                <textarea id="tripay_private_key" 
                          name="tripay_private_key" 
                          class="form-textarea" 
                          rows="3" 
                          placeholder="Private key dari Tripay dashboard"><?= htmlspecialchars($payment_settings['tripay_private_key'] ?? '') ?></textarea>
            </div>
            
            <div class="form-checkbox-group">
                <input type="checkbox" 
                       id="tripay_sandbox_mode" 
                       name="tripay_sandbox_mode" 
                       value="1" 
                       class="form-checkbox"
                       <?= ($payment_settings['tripay_sandbox_mode'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="tripay_sandbox_mode" class="form-checkbox-label">
                    Sandbox Mode (Testing)
                </label>
            </div>
        </div>
    </div>
    
    <!-- Midtrans Configuration -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="layers" class="settings-card-icon"></i>
                Midtrans Configuration
            </h3>
            <p class="settings-card-description">
                Konfigurasi payment gateway Midtrans
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="form-checkbox-group">
                <input type="checkbox" 
                       id="midtrans_enabled" 
                       name="midtrans_enabled" 
                       value="1" 
                       class="form-checkbox"
                       <?= ($payment_settings['midtrans_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                <label for="midtrans_enabled" class="form-checkbox-label">
                    <strong>Enable Midtrans</strong>
                </label>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="midtrans_server_key">
                        Server Key
                    </label>
                    <input type="password" 
                           id="midtrans_server_key" 
                           name="midtrans_server_key" 
                           class="form-input" 
                           placeholder="••••••••••••••••" 
                           value="<?= htmlspecialchars($payment_settings['midtrans_server_key'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="midtrans_client_key">
                        Client Key
                    </label>
                    <input type="text" 
                           id="midtrans_client_key" 
                           name="midtrans_client_key" 
                           class="form-input" 
                           placeholder="SB-Mid-client-..." 
                           value="<?= htmlspecialchars($payment_settings['midtrans_client_key'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-checkbox-group">
                <input type="checkbox" 
                       id="midtrans_sandbox_mode" 
                       name="midtrans_sandbox_mode" 
                       value="1" 
                       class="form-checkbox"
                       <?= ($payment_settings['midtrans_sandbox_mode'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="midtrans_sandbox_mode" class="form-checkbox-label">
                    Sandbox Mode (Testing)
                </label>
            </div>
        </div>
    </div>
    
    <!-- PayPal Configuration -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="dollar-sign" class="settings-card-icon"></i>
                PayPal Configuration
            </h3>
            <p class="settings-card-description">
                Konfigurasi payment gateway PayPal untuk pembayaran internasional
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="form-checkbox-group">
                <input type="checkbox" 
                       id="paypal_enabled" 
                       name="paypal_enabled" 
                       value="1" 
                       class="form-checkbox"
                       <?= ($payment_settings['paypal_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                <label for="paypal_enabled" class="form-checkbox-label">
                    <strong>Enable PayPal</strong>
                </label>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="paypal_client_id">
                        Client ID
                    </label>
                    <input type="text" 
                           id="paypal_client_id" 
                           name="paypal_client_id" 
                           class="form-input" 
                           placeholder="AYSq3RDGsmBLJE-otTkBtM-jBRd1TCQwFf9RGfwddNXWz0uFU9ztymylOhRS" 
                           value="<?= htmlspecialchars($payment_settings['paypal_client_id'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="paypal_client_secret">
                        Client Secret
                    </label>
                    <input type="password" 
                           id="paypal_client_secret" 
                           name="paypal_client_secret" 
                           class="form-input" 
                           placeholder="••••••••••••••••" 
                           value="<?= htmlspecialchars($payment_settings['paypal_client_secret'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-checkbox-group">
                <input type="checkbox" 
                       id="paypal_sandbox_mode" 
                       name="paypal_sandbox_mode" 
                       value="1" 
                       class="form-checkbox"
                       <?= ($payment_settings['paypal_sandbox_mode'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="paypal_sandbox_mode" class="form-checkbox-label">
                    Sandbox Mode (Testing)
                </label>
            </div>
        </div>
    </div>
    
    <!-- Bank Transfer Configuration -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="home" class="settings-card-icon"></i>
                Bank Transfer Configuration
            </h3>
            <p class="settings-card-description">
                Konfigurasi rekening bank untuk pembayaran manual
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="form-checkbox-group">
                <input type="checkbox" 
                       id="bank_transfer_enabled" 
                       name="bank_transfer_enabled" 
                       value="1" 
                       class="form-checkbox"
                       <?= ($payment_settings['bank_transfer_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="bank_transfer_enabled" class="form-checkbox-label">
                    <strong>Enable Bank Transfer</strong>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="bank_transfer_accounts">
                    Bank Accounts (JSON Format)
                </label>
                <textarea id="bank_transfer_accounts" 
                          name="bank_transfer_accounts" 
                          class="form-textarea" 
                          rows="6" 
                          placeholder='[{"bank":"BCA","account_number":"1234567890","account_name":"EPIC Hub"}]'><?= htmlspecialchars($payment_settings['bank_transfer_accounts'] ?? '') ?></textarea>
                <div class="form-help">Format JSON untuk daftar rekening bank yang tersedia</div>
            </div>
        </div>
    </div>
    
    <!-- E-Wallet Configuration -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3 class="settings-card-title">
                <i data-feather="smartphone" class="settings-card-icon"></i>
                E-Wallet Configuration
            </h3>
            <p class="settings-card-description">
                Konfigurasi e-wallet untuk pembayaran digital
            </p>
        </div>
        
        <div class="settings-card-body">
            <div class="form-checkbox-group">
                <input type="checkbox" 
                       id="ewallet_enabled" 
                       name="ewallet_enabled" 
                       value="1" 
                       class="form-checkbox"
                       <?= ($payment_settings['ewallet_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="ewallet_enabled" class="form-checkbox-label">
                    <strong>Enable E-Wallet</strong>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-label">E-Wallet Providers</label>
                <div class="checkbox-grid">
                    <?php 
                    $available_ewallets = ['gopay', 'ovo', 'dana', 'linkaja', 'shopeepay'];
                    $enabled_ewallets = $payment_settings['ewallet_providers_parsed'] ?? [];
                    foreach ($available_ewallets as $ewallet): 
                    ?>
                        <div class="form-checkbox-group">
                            <input type="checkbox" 
                                   id="ewallet_<?= $ewallet ?>" 
                                   name="ewallet_providers[]" 
                                   value="<?= $ewallet ?>" 
                                   class="form-checkbox"
                                   <?= in_array($ewallet, $enabled_ewallets) ? 'checked' : '' ?>>
                            <label for="ewallet_<?= $ewallet ?>" class="form-checkbox-label">
                                <?= ucfirst($ewallet) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Save Button -->
    <div class="settings-actions">
        <button type="submit" name="save_payment_settings" class="btn btn-primary">
            <i data-feather="save" width="16" height="16"></i>
            Simpan Pengaturan Payment
        </button>
        <button type="button" class="btn btn-secondary" onclick="testPaymentGateway()">
            <i data-feather="zap" width="16" height="16"></i>
            Test Payment Gateway
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

/* Payment specific styles */
.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--spacing-3);
    margin-top: var(--spacing-3);
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
    margin-bottom: var(--spacing-3);
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
    
    .checkbox-grid {
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

// Test payment gateway
function testPaymentGateway() {
    const gateway = document.getElementById('payment_default_gateway').value;
    
    const formData = new FormData();
    formData.append('test_payment', '1');
    formData.append('gateway', gateway);
    
    // Add gateway specific data
    if (gateway === 'tripay') {
        formData.append('merchant_code', document.getElementById('tripay_merchant_code').value);
        formData.append('api_key', document.getElementById('tripay_api_key').value);
        formData.append('private_key', document.getElementById('tripay_private_key').value);
    } else if (gateway === 'midtrans') {
        formData.append('server_key', document.getElementById('midtrans_server_key').value);
        formData.append('client_key', document.getElementById('midtrans_client_key').value);
    } else if (gateway === 'paypal') {
        formData.append('client_id', document.getElementById('paypal_client_id').value);
        formData.append('client_secret', document.getElementById('paypal_client_secret').value);
    }
    
    fetch('<?= epic_url('admin/settings/payment-gateway') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test payment gateway berhasil!');
        } else {
            alert('Test payment gateway gagal: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat test payment gateway');
    });
}
</script>