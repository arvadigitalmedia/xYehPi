<?php
/**
 * Commission Settings Content
 * Form untuk mengatur commission rates global
 * 
 * Variables from parent scope:
 * @var string $success
 * @var string $error  
 * @var array $current_settings
 * @var string $csrf_token
 */
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i data-feather="percent" width="24" height="24"></i>
                Commission Settings
            </h1>
            <p class="page-description">
                Configure global commission rates for EPIS and EPIC accounts
            </p>
        </div>
    </div>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i data-feather="check-circle" width="20" height="20"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i data-feather="alert-circle" width="20" height="20"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Commission Settings Form -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">
                <i data-feather="settings" width="20" height="20"></i>
                Global Commission Rates
            </h2>
            <p class="card-description">
                These rates will be applied to all EPIS accounts. Changes take effect immediately.
            </p>
        </div>

        <form method="POST" class="commission-settings-form">
            <input type="hidden" name="action" value="update_commission_settings">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- EPIS Commission Settings -->
            <div class="form-section">
                <h3 class="section-title">
                    <i data-feather="users" width="20" height="20"></i>
                    EPIS Commission Rates
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="epis_direct_commission_rate" class="form-label">
                            Direct Recruitment Commission (%)
                            <span class="required">*</span>
                        </label>
                        <input type="number" 
                               name="epis_direct_commission_rate" 
                               id="epis_direct_commission_rate" 
                               class="form-input" 
                               value="<?= htmlspecialchars($current_settings['epis_direct_commission_rate']) ?>" 
                               min="0" max="100" step="0.01" required>
                        <small class="form-help">
                            Commission rate when EPIS directly recruits new EPIC members
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="epis_indirect_commission_rate" class="form-label">
                            Indirect Commission (%)
                            <span class="required">*</span>
                        </label>
                        <input type="number" 
                               name="epis_indirect_commission_rate" 
                               id="epis_indirect_commission_rate" 
                               class="form-input" 
                               value="<?= htmlspecialchars($current_settings['epis_indirect_commission_rate']) ?>" 
                               min="0" max="100" step="0.01" required>
                        <small class="form-help">
                            Commission rate when EPIC members recruit through EPIS network
                        </small>
                    </div>
                </div>
            </div>

            <!-- EPIC Commission Settings -->
            <div class="form-section">
                <h3 class="section-title">
                    <i data-feather="user" width="20" height="20"></i>
                    EPIC Commission Rates
                </h3>
                
                <div class="form-group">
                    <label for="epic_referral_commission_rate" class="form-label">
                        Referral Commission (%)
                        <span class="required">*</span>
                    </label>
                    <input type="number" 
                           name="epic_referral_commission_rate" 
                           id="epic_referral_commission_rate" 
                           class="form-input" 
                           value="<?= htmlspecialchars($current_settings['epic_referral_commission_rate']) ?>" 
                           min="0" max="100" step="0.01" required>
                    <small class="form-help">
                        Commission rate for EPIC members when they refer new members
                    </small>
                </div>
            </div>

            <!-- Commission Preview -->
            <div class="form-section">
                <h3 class="section-title">
                    <i data-feather="eye" width="20" height="20"></i>
                    Commission Preview
                </h3>
                
                <div class="commission-preview">
                    <div class="preview-item">
                        <div class="preview-label">Example: New EPIC registration fee Rp 100.000</div>
                        <div class="preview-calculations">
                            <div class="calc-item">
                                <span class="calc-label">EPIS Direct Commission:</span>
                                <span class="calc-value" id="preview-epis-direct">
                                    Rp <?= number_format((100000 * $current_settings['epis_direct_commission_rate']) / 100, 0, ',', '.') ?>
                                </span>
                            </div>
                            <div class="calc-item">
                                <span class="calc-label">EPIS Indirect Commission:</span>
                                <span class="calc-value" id="preview-epis-indirect">
                                    Rp <?= number_format((100000 * $current_settings['epis_indirect_commission_rate']) / 100, 0, ',', '.') ?>
                                </span>
                            </div>
                            <div class="calc-item">
                                <span class="calc-label">EPIC Referral Commission:</span>
                                <span class="calc-value" id="preview-epic-referral">
                                    Rp <?= number_format((100000 * $current_settings['epic_referral_commission_rate']) / 100, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="<?= epic_url('admin/settings') ?>" class="btn btn-secondary">
                    <i data-feather="arrow-left" width="16" height="16"></i>
                    Back to Settings
                </a>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save" width="16" height="16"></i>
                    Update Commission Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Information Card -->
    <div class="content-card info-card">
        <div class="card-header">
            <h3 class="card-title">
                <i data-feather="info" width="20" height="20"></i>
                Important Information
            </h3>
        </div>
        
        <div class="info-content">
            <ul class="info-list">
                <li>
                    <i data-feather="check" width="16" height="16"></i>
                    Commission rates are applied globally to all EPIS accounts
                </li>
                <li>
                    <i data-feather="check" width="16" height="16"></i>
                    Changes take effect immediately for new transactions
                </li>
                <li>
                    <i data-feather="check" width="16" height="16"></i>
                    Existing pending commissions are not affected by rate changes
                </li>
                <li>
                    <i data-feather="check" width="16" height="16"></i>
                    All commission calculations are logged for audit purposes
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- JavaScript for real-time preview -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const episDirectInput = document.getElementById('epis_direct_commission_rate');
    const episIndirectInput = document.getElementById('epis_indirect_commission_rate');
    const epicReferralInput = document.getElementById('epic_referral_commission_rate');
    
    const previewEpisDirect = document.getElementById('preview-epis-direct');
    const previewEpisIndirect = document.getElementById('preview-epis-indirect');
    const previewEpicReferral = document.getElementById('preview-epic-referral');
    
    function updatePreview() {
        const baseAmount = 100000;
        const episDirectRate = parseFloat(episDirectInput.value) || 0;
        const episIndirectRate = parseFloat(episIndirectInput.value) || 0;
        const epicReferralRate = parseFloat(epicReferralInput.value) || 0;
        
        const episDirectAmount = (baseAmount * episDirectRate) / 100;
        const episIndirectAmount = (baseAmount * episIndirectRate) / 100;
        const epicReferralAmount = (baseAmount * epicReferralRate) / 100;
        
        previewEpisDirect.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(episDirectAmount);
        previewEpisIndirect.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(episIndirectAmount);
        previewEpicReferral.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(epicReferralAmount);
    }
    
    episDirectInput.addEventListener('input', updatePreview);
    episIndirectInput.addEventListener('input', updatePreview);
    epicReferralInput.addEventListener('input', updatePreview);
});
</script>

<style>
.commission-settings-form .form-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--card-bg);
}

.commission-preview {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 1.5rem;
}

.preview-item {
    margin-bottom: 1rem;
}

.preview-label {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
}

.preview-calculations {
    display: grid;
    gap: 0.5rem;
}

.calc-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color);
}

.calc-item:last-child {
    border-bottom: none;
}

.calc-label {
    color: var(--text-secondary);
}

.calc-value {
    font-weight: 600;
    color: var(--primary-color);
}

.info-card {
    margin-top: 2rem;
}

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-list li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    color: var(--text-secondary);
}

.info-list li i {
    color: var(--success-color);
    flex-shrink: 0;
}
</style>