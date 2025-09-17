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
?>

<footer class="member-footer">
    <div class="footer-content">
        <div class="footer-left">
            <p class="footer-text">
                © <?= $current_year ?> <?= htmlspecialchars($site_name) ?>. All rights reserved.
            </p>
        </div>
        
        <div class="footer-right">
            <div class="footer-links">
                <a href="<?= epic_url('privacy') ?>" class="footer-link">Privacy Policy</a>
                <a href="<?= epic_url('terms') ?>" class="footer-link">Terms of Service</a>
                <a href="<?= epic_url('support') ?>" class="footer-link">Support</a>
            </div>
            
            <div class="footer-version">
                <span class="version-text">v2.0.0</span>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.member-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 1.5rem 0;
    margin-top: auto;
}

.footer-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    max-width: 100%;
}

.footer-left {
    flex: 1;
}

.footer-text {
    margin: 0;
    color: #64748b;
    font-size: 0.875rem;
}

.footer-right {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.footer-links {
    display: flex;
    gap: 1.5rem;
}

.footer-link {
    color: #64748b;
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.2s;
}

.footer-link:hover {
    color: #334155;
}

.footer-version {
    display: flex;
    align-items: center;
}

.version-text {
    color: #94a3b8;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    background: #e2e8f0;
    border-radius: 0.375rem;
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