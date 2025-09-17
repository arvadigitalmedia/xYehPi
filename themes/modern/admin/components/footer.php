<?php
/**
 * EPIC Hub Admin Footer Component
 * Global footer untuk semua halaman admin
 */

$current_year = date('Y');
$app_version = '2.0.0';
?>
<footer class="admin-footer">
    <div class="footer-content">
        <div class="footer-left">
            <p class="footer-text">
                &copy; <?= $current_year ?> EPIC Hub. All rights reserved.
            </p>
            <p class="footer-version">
                Version <?= $app_version ?>
            </p>
        </div>
        
        <div class="footer-right">
            <div class="footer-links">
                <a href="<?= epic_url('admin/help') ?>" class="footer-link">
                    <i data-feather="help-circle" width="14" height="14"></i>
                    <span>Help</span>
                </a>
                <a href="<?= epic_url('admin/documentation') ?>" class="footer-link">
                    <i data-feather="book" width="14" height="14"></i>
                    <span>Documentation</span>
                </a>
                <a href="<?= epic_url('admin/support') ?>" class="footer-link">
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
.admin-footer {
    background: var(--surface-2);
    border-top: 1px solid var(--ink-700);
    padding: var(--spacing-4) var(--spacing-6);
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
    gap: var(--spacing-1);
}

.footer-text {
    color: var(--ink-300);
    font-size: var(--font-size-sm);
    margin: 0;
}

.footer-version {
    color: var(--ink-400);
    font-size: var(--font-size-xs);
    margin: 0;
}

.footer-right {
    display: flex;
    align-items: center;
    gap: var(--spacing-6);
}

.footer-links {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
}

.footer-link {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    color: var(--ink-400);
    text-decoration: none;
    font-size: var(--font-size-sm);
    transition: color var(--transition-fast);
}

.footer-link:hover {
    color: var(--gold-400);
}

.footer-status {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--success);
    animation: pulse 2s infinite;
}

.status-indicator.status-online {
    background: var(--success);
}

.status-indicator.status-warning {
    background: var(--warning);
}

.status-indicator.status-error {
    background: var(--danger);
}

.status-text {
    color: var(--ink-400);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        opacity: 1;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        gap: var(--spacing-4);
        text-align: center;
    }
    
    .footer-right {
        flex-direction: column;
        gap: var(--spacing-3);
    }
    
    .footer-links {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .footer-links {
        flex-direction: column;
        gap: var(--spacing-2);
    }
}
</style>