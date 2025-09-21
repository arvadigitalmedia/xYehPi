<?php
/**
 * EPIC Hub Member Footer Component
 * Footer untuk member area
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

$site_name = epic_setting('site_name', 'EPIC Hub');
$current_year = date('Y');
$app_version = '2.0.0';
?>

<footer class="member-footer">
    <div class="footer-content">
        <div class="footer-left">
            <p class="footer-text">
                &copy; <?= $current_year ?> <?= htmlspecialchars($site_name) ?>. All rights reserved.
            </p>
            <p class="footer-version">
                Version <?= $app_version ?>
            </p>
        </div>
        
        <div class="footer-right">
            <div class="footer-links">
                <a href="<?= epic_url('help') ?>" class="footer-link">
                    <i data-feather="help-circle" width="14" height="14"></i>
                    <span>Help</span>
                </a>
                <a href="<?= epic_url('docs') ?>" class="footer-link">
                    <i data-feather="book-open" width="14" height="14"></i>
                    <span>Documentation</span>
                </a>
                <a href="<?= epic_url('support') ?>" class="footer-link">
                    <i data-feather="message-circle" width="14" height="14"></i>
                    <span>Support</span>
                </a>
            </div>
            
            <div class="footer-status">
                <div class="status-indicator status-online" title="System Online"></div>
                <span class="status-text">System Online</span>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.member-footer {
    background: var(--surface-2, #f8fafc);
    border-top: 1px solid var(--ink-700, #e2e8f0);
    padding: var(--spacing-4, 1rem) var(--spacing-6, 1.5rem);
    margin-top: auto;
}

.footer-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 100%;
}

.footer-left {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-1, 0.25rem);
}

.footer-text {
    color: var(--ink-300, #64748b);
    font-size: var(--font-size-sm, 0.875rem);
    margin: 0;
}

.footer-version {
    color: var(--ink-400, #94a3b8);
    font-size: var(--font-size-xs, 0.75rem);
    margin: 0;
}

.footer-right {
    display: flex;
    align-items: center;
    gap: var(--spacing-6, 1.5rem);
}

.footer-links {
    display: flex;
    align-items: center;
    gap: var(--spacing-4, 1rem);
}

.footer-link {
    display: flex;
    align-items: center;
    gap: var(--spacing-2, 0.5rem);
    color: var(--ink-400, #64748b);
    text-decoration: none;
    font-size: var(--font-size-sm, 0.875rem);
    transition: color var(--transition-fast, 0.2s);
}

.footer-link:hover {
    color: var(--ink-200, #334155);
}

.footer-status {
    display: flex;
    align-items: center;
    gap: var(--spacing-2, 0.5rem);
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--success, #22c55e);
}

.status-indicator.status-online {
    background: var(--success, #22c55e);
    box-shadow: 0 0 0 2px var(--success-light, rgba(34, 197, 94, 0.2));
}

.status-text {
    color: var(--ink-400, #64748b);
    font-size: var(--font-size-xs, 0.75rem);
    font-weight: 500;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        gap: 1rem;
        padding: 0 1rem;
        text-align: center;
    }
    
    .footer-right {
        flex-direction: column;
        gap: 1rem;
    }
    
    .footer-links {
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    .footer-content {
        padding: 0 0.75rem;
    }
    
    .footer-links {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.75rem;
    }
}
</style>