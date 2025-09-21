<?php
/**
 * EPIC Hub Member Dashboard Home
 * Halaman utama member area dengan pembatasan akses berdasarkan level
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout system
require_once __DIR__ . '/components/page-layout.php';

// Initialize data
$user = $user ?? epic_current_user();
$access_level = $access_level ?? epic_get_member_access_level($user);
$stats = epic_get_member_stats($user);

/**
 * Helper function for relative time formatting
 */
if (!function_exists('epic_format_relative_time')) {
    function epic_format_relative_time($date) {
        $now = new DateTime();
        $target = new DateTime($date);
        $diff = $now->diff($target);
        
        if ($diff->days > 0) {
            return $diff->days . ' hari yang lalu';
        } elseif ($diff->h > 0) {
            return $diff->h . ' jam yang lalu';
        } elseif ($diff->i > 0) {
            return $diff->i . ' menit yang lalu';
        } else {
            return 'Baru saja';
        }
    }
}

// Get recent activities (dummy data for now)
$recent_activities = [
    [
        'type' => 'order',
        'title' => 'Pembelian Produk',
        'description' => 'Anda membeli produk Digital Marketing Course',
        'amount' => 299000,
        'time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'icon' => 'shopping-cart',
        'status' => 'success'
    ],
    [
        'type' => 'commission',
        'title' => 'Komisi Diterima',
        'description' => 'Komisi dari referral John Doe',
        'amount' => 50000,
        'time' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'icon' => 'dollar-sign',
        'status' => 'success'
    ],
    [
        'type' => 'referral',
        'title' => 'Referral Baru',
        'description' => 'Jane Smith bergabung melalui link Anda',
        'amount' => 0,
        'time' => date('Y-m-d H:i:s', strtotime('-3 days')),
        'icon' => 'user-plus',
        'status' => 'info'
    ]
];

// Include content
require_once __DIR__ . '/content/home-content.php';
?>

<style>
/* ===== MEMBER HOME DARK GOLD THEME ===== */

/* CSS Variables untuk kompatibilitas */
:root {
    --surface-2: var(--bg-2);
    --surface-3: var(--card);
    --surface-4: var(--bg);
    --ink-100: var(--tx);
    --ink-200: var(--tx-2);
    --ink-300: var(--tx-3);
    --ink-400: var(--tx-3);
    --ink-600: var(--border);
    --ink-700: var(--border);
    --ink-900: var(--bg);
    --spacing-1: var(--space-1);
    --spacing-2: var(--space-2);
    --spacing-3: var(--space-3);
    --spacing-4: var(--space-4);
    --spacing-5: var(--space-5);
    --spacing-6: var(--space-6);
    --spacing-8: var(--space-8);
    --spacing-12: var(--space-12);
    --gold-300: var(--gold);
    --gold-400: var(--gold-600);
    --gradient-gold: var(--gradient-gold);
    --gradient-gold-subtle: linear-gradient(135deg, rgba(216, 183, 74, 0.1) 0%, rgba(232, 204, 107, 0.05) 100%);
    --shadow-lg: var(--shadow-card);
    --shadow-md: var(--shadow-card);
    --transition-fast: var(--transition-fast);
    --transition-normal: var(--transition-normal);
    --success: #22C55E;
    --success-dark: #16A34A;
    --warning: #F59E0B;
    --warning-dark: #D97706;
    --danger: #EF4444;
    --danger-light: #FCA5A5;
    --success-light: #86EFAC;
    --warning-light: #FDE68A;
    --font-mono: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
    --radius-full: 9999px;
}

/* Welcome Section with Dark Gold Theme */
.welcome-section {
    background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%);
    border: 1px solid var(--ink-700);
    color: var(--ink-100);
    padding: var(--spacing-8);
    border-radius: var(--radius-2xl);
    margin-bottom: var(--spacing-8);
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.welcome-section::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: var(--gradient-gold-subtle);
    border-radius: 50%;
    transform: translate(50%, -50%);
}

/* ===== WELCOME SECTION & CARD FAMILY STYLES ===== */
/* Base Welcome Section with Dark Gold Theme */
.welcome-section {
    background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%);
    border: 1px solid var(--ink-700);
    color: var(--ink-100);
    padding: var(--spacing-8);
    border-radius: var(--radius-2xl);
    margin-bottom: var(--spacing-8);
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.welcome-section::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: var(--gradient-gold-subtle);
    border-radius: 50%;
    transform: translate(50%, -50%);
}

/* ===== WELCOME CARD FAMILY - SHARED STYLES ===== */
/* Base card styles yang digunakan oleh semua card dalam Welcome family */
.welcome-card-base,
.combined-info-card {
    background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: all var(--transition-fast);
    max-width: 100%;
    box-sizing: border-box;
}

/* Shared top accent border untuk semua card dalam Welcome family */
.welcome-card-base::before,
.combined-info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gradient-gold);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    opacity: 0.8;
    transition: opacity var(--transition-fast);
}

/* Shared hover effects untuk Welcome card family */
.welcome-card-base:hover,
.combined-info-card:hover {
    border-color: var(--gold-400);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg), 0 0 20px rgba(216, 183, 74, 0.15);
}

.welcome-card-base:hover::before,
.combined-info-card:hover::before {
    opacity: 1;
}



/* ===== WELCOME UPGRADE SECTION ===== */
.welcome-upgrade-section {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(216, 183, 74, 0.05) 100%);
    border: 1px solid rgba(245, 158, 11, 0.2);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    margin-top: var(--spacing-4);
    position: relative;
    overflow: hidden;
}

.welcome-upgrade-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--warning) 0%, var(--gold-400) 50%, var(--warning) 100%);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.upgrade-benefits {
    margin-bottom: var(--spacing-4);
}

.upgrade-benefits-title {
    color: var(--gold-300);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    margin: 0 0 var(--spacing-3) 0;
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.upgrade-benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-2);
}

.upgrade-benefits-list li {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    color: var(--ink-200);
    font-size: var(--font-size-sm);
    padding: var(--spacing-1) 0;
}

.upgrade-benefits-list li i {
    color: var(--success);
    flex-shrink: 0;
}

.upgrade-actions {
    display: flex;
    gap: var(--spacing-3);
    flex-wrap: wrap;
}

.upgrade-actions .btn {
    flex: 1;
    min-width: 140px;
}



/* ===== COMBINED INFO CARD - WELCOME FAMILY MEMBER ===== */
.combined-info-card {
    margin-bottom: var(--spacing-6);
}

.combined-card-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

.epis-supervisor-column,
.referral-link-column {
    padding: var(--spacing-5);
    position: relative;
}

.epis-supervisor-column {
    border-right: 1px solid var(--ink-600);
}

/* ===== WELCOME FAMILY RESPONSIVE DESIGN ===== */
@media (max-width: 768px) {
    .welcome-section,
    .upgrade-prompt,
    .combined-info-card {
        margin-left: calc(-1 * var(--spacing-4));
        margin-right: calc(-1 * var(--spacing-4));
        border-radius: var(--radius-md);
    }
    
    .combined-card-content {
        grid-template-columns: 1fr;
    }
    
    .epis-supervisor-column {
        border-right: none;
        border-bottom: 1px solid var(--ink-600);
    }
    
    .upgrade-content {
        flex-direction: column;
        text-align: center;
    }
    
    .upgrade-icon {
        align-self: center;
    }
}

@media (max-width: 480px) {
    .welcome-section,
    .upgrade-prompt,
    .combined-info-card {
        border-radius: 0;
        border-left: none;
        border-right: none;
    }
    
    .upgrade-prompt {
        padding: var(--spacing-4);
    }
    
    .epis-supervisor-column,
    .referral-link-column {
        padding: var(--spacing-4);
    }
}

/* ===== WELCOME FAMILY LOCKED STATE ===== */
.welcome-card-base.locked,
.upgrade-prompt.locked {
    opacity: 0.6;
    cursor: not-allowed;
}

.welcome-card-base.locked::before,
.upgrade-prompt.locked::before {
    background: linear-gradient(90deg, var(--ink-500) 0%, var(--ink-400) 50%, var(--ink-500) 100%);
}

.welcome-card-base.locked:hover,
.upgrade-prompt.locked:hover {
    transform: none;
    border-color: var(--ink-600);
    box-shadow: var(--shadow-md);
}

.welcome-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 1;
}

.welcome-title {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    margin-bottom: var(--spacing-2);
    color: var(--gold-300);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.welcome-subtitle {
    font-size: var(--font-size-lg);
    opacity: 0.9;
    margin: 0;
    color: var(--ink-200);
}

.welcome-actions {
    flex-shrink: 0;
}

.upgrade-actions {
    display: flex;
    gap: var(--spacing-4);
    margin-top: var(--spacing-6);
}



/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--spacing-12) var(--spacing-6);
    color: var(--ink-300);
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    background: var(--surface-3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-6);
    border: 2px solid var(--ink-600);
}

.empty-state-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-200);
    margin-bottom: var(--spacing-2);
}

.empty-state-text {
    color: var(--ink-400);
    font-size: var(--font-size-sm);
    max-width: 300px;
    margin: 0 auto;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: var(--spacing-1) var(--spacing-3);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-completed {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success-light);
    border: 1px solid var(--success);
}

.status-pending {
    background: rgba(245, 158, 11, 0.2);
    color: var(--warning-light);
    border: 1px solid var(--warning);
}

.status-cancelled {
    background: rgba(239, 68, 68, 0.2);
    color: var(--danger-light);
    border: 1px solid var(--danger);
}

/* Copy to Clipboard */
.copy-btn {
    background: var(--surface-3);
    border: 1px solid var(--ink-600);
    color: var(--ink-300);
    padding: var(--spacing-2);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.copy-btn:hover {
    background: var(--gold-400);
    color: var(--ink-900);
    border-color: var(--gold-400);
}

/* Referral Link Section Styling */
.referral-link-section {
    background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-xl);
    padding: var(--spacing-6);
    margin-top: var(--spacing-6);
    position: relative;
    overflow: hidden;
}

.referral-link-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gradient-gold);
    opacity: 0.9;
}

.referral-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-5);
}

.referral-icon {
    width: 48px;
    height: 48px;
    background: var(--gradient-gold);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-900);
    box-shadow: var(--shadow-md);
}

.referral-title h4 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--gold-300);
    margin: 0 0 var(--spacing-1) 0;
}

.referral-title p {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
    margin: 0;
}

.referral-link-container {
    background: var(--surface-4);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-4);
}

.referral-link-input-group {
    display: flex;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-4);
}

.referral-link-input {
    flex: 1;
    background: var(--surface-2);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    padding: var(--spacing-3) var(--spacing-4);
    color: var(--ink-100);
    font-family: var(--font-mono);
    font-size: var(--font-size-sm);
    transition: all var(--transition-fast);
}

.referral-link-input:focus {
    outline: none;
    border-color: var(--gold-400);
    box-shadow: 0 0 0 3px rgba(207, 168, 78, 0.1);
}

.referral-copy-btn {
    background: var(--gradient-gold);
    border: none;
    border-radius: var(--radius-md);
    padding: var(--spacing-3) var(--spacing-4);
    color: var(--ink-900);
    font-weight: var(--font-weight-semibold);
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    min-width: 80px;
    justify-content: center;
}

.referral-copy-btn:hover {
    background: linear-gradient(135deg, #ffed4e 0%, #ffd700 100%);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.referral-copy-btn:active {
    transform: translateY(0);
}

.referral-copy-btn.copied {
    background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
    color: white;
}

.referral-stats {
    display: flex;
    gap: var(--spacing-6);
    flex-wrap: wrap;
}

.referral-stat-item {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-1);
}

.referral-stat-item .stat-label {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: var(--font-weight-medium);
}

.referral-stat-item .stat-value {
    font-size: var(--font-size-sm);
    color: var(--gold-300);
    font-weight: var(--font-weight-bold);
    font-family: var(--font-mono);
}

/* Copy Success Animation */
@keyframes copySuccess {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.referral-copy-btn.copied {
    animation: copySuccess 0.3s ease-in-out;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .welcome-content {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-6);
    }
    
    .welcome-title {
        font-size: var(--font-size-xl);
    }
    
    .welcome-subtitle {
        font-size: var(--font-size-base);
    }
    
    .upgrade-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .upgrade-actions .btn {
        width: 100%;
        justify-content: center;
    }
    

}

@media (max-width: 480px) {
    .welcome-section {
        padding: var(--spacing-6);
    }
    
    .welcome-title {
        font-size: var(--font-size-lg);
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    

    
    /* Referral Section Mobile */
    .referral-link-section {
        padding: var(--spacing-4);
        margin-top: var(--spacing-4);
    }
    
    .referral-header {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-3);
    }
    
    .referral-link-input-group {
        flex-direction: column;
        gap: var(--spacing-3);
    }
    
    .referral-copy-btn {
        width: 100%;
        justify-content: center;
    }
    
    .referral-stats {
        flex-direction: column;
        gap: var(--spacing-3);
        text-align: center;
    }
}

/* EPIS Supervisor Information Styling */
.epis-supervisor-info {
    margin-top: var(--spacing-4);
    padding: var(--spacing-4);
    background: linear-gradient(135deg, var(--surface-3) 0%, var(--surface-2) 100%);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    position: relative;
}

.epis-supervisor-info::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--gradient-gold);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.epis-supervisor-info.no-supervisor::before {
    background: linear-gradient(90deg, var(--warning) 0%, var(--warning-dark) 100%);
}

.supervisor-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-2);
}

.supervisor-icon {
    width: 24px;
    height: 24px;
    background: var(--gradient-gold);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-900);
}

.supervisor-icon.warning {
    background: linear-gradient(135deg, var(--warning) 0%, var(--warning-dark) 100%);
    color: white;
}

.supervisor-label {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-200);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.supervisor-details {
    margin-left: 32px;
}

.supervisor-name {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--gold-300);
    margin-bottom: var(--spacing-1);
}

.no-supervisor .supervisor-name {
    color: var(--warning);
}

.supervisor-code {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
    font-family: var(--font-mono);
    margin-bottom: var(--spacing-1);
}

.supervisor-territory {
    font-size: var(--font-size-sm);
    color: var(--ink-400);
    font-style: italic;
}

.supervisor-note {
    font-size: var(--font-size-sm);
    color: var(--warning-light);
    font-style: italic;
}

/* Mobile responsive for EPIS info */
@media (max-width: 768px) {
    .epis-supervisor-info {
        padding: var(--spacing-3);
        margin-top: var(--spacing-3);
    }
    
    .supervisor-details {
        margin-left: 28px;
    }
    
    .supervisor-name {
        font-size: var(--font-size-base);
    }
}

/* New Layout Styles */
/* Welcome Header Card */
.welcome-header-card {
    background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    margin-bottom: var(--spacing-6);
    position: relative;
    overflow: hidden;
}

.welcome-header-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gradient-gold);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.welcome-header-content {
     display: flex;
     align-items: center;
     justify-content: space-between;
     gap: var(--spacing-4);
 }

.welcome-photo {
     width: 60px;
     height: 60px;
     border-radius: var(--radius-full);
     overflow: hidden;
     border: 2px solid var(--gold-400);
     flex-shrink: 0;
     position: relative;
 }

.welcome-photo .user-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.welcome-photo .user-photo-placeholder {
    width: 100%;
    height: 100%;
    background: var(--gradient-gold);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-900);
}

.welcome-text-content {
    flex: 1;
    min-width: 0;
}

.welcome-actions-new {
    flex-shrink: 0;
}

/* Combined Info Card */
.combined-info-card {
    background: linear-gradient(135deg, var(--surface-3) 0%, var(--surface-2) 100%);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-6);
    overflow: hidden;
    position: relative;
}

.combined-info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--gradient-gold);
}

.combined-card-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

.epis-supervisor-column,
.referral-link-column {
    padding: var(--spacing-6);
    position: relative;
}

.epis-supervisor-column {
    border-right: 1px solid var(--ink-600);
}

.section-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-4);
}

.section-icon {
    width: 40px;
    height: 40px;
    background: var(--gradient-gold);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-900);
}

.section-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--gold-300);
    margin: 0;
}

/* EPIS Supervisor Column Styles */
.supervisor-info {
    margin-top: var(--spacing-2);
}

.supervisor-name {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--gold-300);
    margin-bottom: var(--spacing-3);
}

.supervisor-details {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.detail-item {
     display: flex;
     justify-content: space-between;
     align-items: center;
     padding: var(--spacing-2) 0;
     border-bottom: 1px solid var(--ink-700);
 }
 
 .detail-item:last-child {
     border-bottom: none;
 }
 
 .detail-item-left {
     display: flex;
     align-items: flex-start;
     padding: var(--spacing-1) 0;
 }
 
 .detail-label {
     font-size: var(--font-size-sm);
     color: var(--ink-300);
     font-weight: var(--font-weight-medium);
 }
 
 .detail-value {
     font-size: var(--font-size-sm);
     color: var(--gold-200);
     font-weight: var(--font-weight-semibold);
     font-family: var(--font-mono);
 }

.supervisor-info.no-supervisor .supervisor-name {
    color: var(--warning);
}

.supervisor-note {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--warning-light);
    font-style: italic;
    margin-top: var(--spacing-2);
}

/* Referral Link Column Styles */
.referral-content {
    margin-top: var(--spacing-2);
}

.referral-link-input-group {
    display: flex;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-4);
}

.referral-link-input {
    flex: 1;
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-md);
    padding: var(--spacing-3);
    color: var(--ink-100);
    font-size: var(--font-size-sm);
    font-family: var(--font-mono);
}

.referral-copy-btn {
    background: var(--gradient-gold);
    border: none;
    border-radius: var(--radius-md);
    padding: var(--spacing-3) var(--spacing-4);
    color: var(--ink-900);
    font-weight: var(--font-weight-semibold);
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    min-width: 100px;
    justify-content: center;
}

.referral-copy-btn:hover {
    background: linear-gradient(135deg, #ffed4e 0%, #ffd700 100%);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.referral-copy-btn.copied {
    background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
    color: white;
}

.referral-stats {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.referral-stat-item {
     display: flex;
     justify-content: space-between;
     align-items: center;
     padding: var(--spacing-2) 0;
     border-bottom: 1px solid var(--ink-700);
 }
 
 .referral-stat-item:last-child {
     border-bottom: none;
 }
 
 .referral-stat-item-left {
     display: flex;
     align-items: center;
     gap: var(--spacing-2);
     padding: var(--spacing-1) 0;
 }
 
 .referral-stat-item .stat-label {
     font-size: var(--font-size-sm);
     color: var(--ink-300);
     font-weight: var(--font-weight-medium);
 }
 
 .referral-stat-item .stat-value {
     font-size: var(--font-size-sm);
     color: var(--gold-200);
     font-weight: var(--font-weight-semibold);
     font-family: var(--font-mono);
 }
 
 .referral-stat-item-left .stat-label {
     font-size: var(--font-size-sm);
     color: var(--gold-200);
     font-weight: var(--font-weight-medium);
 }

/* Light Icons */
.icon-light {
    color: var(--gold-200) !important;
    stroke: var(--gold-200) !important;
}

.icon-warning {
    color: var(--warning) !important;
    stroke: var(--warning) !important;
}

/* Button Improvements */
.btn {
    background: var(--gradient-gold);
    color: var(--ink-900);
    border: none;
    padding: var(--spacing-3) var(--spacing-4);
    border-radius: var(--radius-md);
    font-weight: var(--font-weight-semibold);
    cursor: pointer;
    transition: all var(--transition-fast);
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    text-decoration: none;
}

.btn:hover {
    background: linear-gradient(135deg, #ffed4e 0%, #ffd700 100%);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.btn-secondary {
    background: linear-gradient(135deg, var(--surface-3) 0%, var(--surface-2) 100%);
    color: var(--gold-300);
    border: 1px solid var(--gold-400);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, var(--gold-400) 0%, var(--gold-300) 100%);
    color: var(--ink-900);
}

/* Mobile Responsive */
  @media (max-width: 768px) {
      .welcome-header-content {
          flex-direction: column;
          text-align: center;
          gap: var(--spacing-3);
      }
    
    .combined-card-content {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .epis-supervisor-column {
        border-right: none;
        border-bottom: 1px solid var(--ink-600);
    }
    
    .epis-supervisor-column,
    .referral-link-column {
        padding: var(--spacing-4);
    }
    
    .referral-link-input-group {
        flex-direction: column;
    }
    
    .referral-copy-btn {
        width: 100%;
    }
    
    .section-header {
        justify-content: center;
        text-align: center;
    }
    
    .detail-item,
    .referral-stat-item {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-1);
    }
}

@media (max-width: 480px) {
     .welcome-header-card,
     .combined-info-card {
         margin-left: calc(-1 * var(--spacing-4));
         margin-right: calc(-1 * var(--spacing-4));
         border-radius: 0;
     }
     
     .welcome-header-content {
         padding: var(--spacing-4);
     }
     
     .epis-supervisor-column,
     .referral-link-column {
         padding: var(--spacing-3);
     }
 }
 

 


/* ===== UPGRADE PROMPT OPTIMIZATION ===== */
.upgrade-prompt {
    background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    margin-bottom: var(--spacing-6);
    position: relative;
    overflow: hidden;
    max-width: 100%;
    box-sizing: border-box;
}

.upgrade-prompt::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--warning) 0%, var(--gold-400) 50%, var(--warning) 100%);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.upgrade-content {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-4);
    position: relative;
    z-index: 1;
}

.upgrade-icon {
    width: 60px;
    height: 60px;
    background: rgba(245, 158, 11, 0.15);
    border: 2px solid rgba(245, 158, 11, 0.3);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--warning);
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.1);
}

.upgrade-text {
    flex: 1;
    min-width: 0;
}

.upgrade-text h3 {
    color: var(--gold-300);
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    margin: 0 0 var(--spacing-2) 0;
    line-height: 1.3;
}

.upgrade-desc {
    font-size: var(--font-size-base);
    color: var(--ink-200);
    margin: 0 0 var(--spacing-4) 0;
    line-height: 1.5;
}

.upgrade-features {
    list-style: none;
    padding: 0;
    margin: 0 0 var(--spacing-5) 0;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.upgrade-feature {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--ink-200);
}

.upgrade-feature::before {
    content: '✓';
    color: var(--warning);
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-base);
}

.upgrade-actions {
    display: flex;
    gap: var(--spacing-3);
    flex-wrap: wrap;
}

/* ===== SECTION CONSISTENCY OPTIMIZATION ===== */

/* Consistent section styling */
.upgrade-prompt {
    background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-6);
    margin-bottom: var(--spacing-6);
    position: relative;
    overflow: hidden;
    max-width: 100%;
    box-sizing: border-box;
}

/* Section headers consistency */
.section-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-5);
    position: relative;
    z-index: 1;
}

.section-title {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    color: var(--gold-300);
    margin: 0;
    line-height: 1.3;
}

.section-subtitle {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
    margin: var(--spacing-1) 0 0 0;
    line-height: 1.4;
}



.pill-warning {
    background: rgba(245, 158, 11, 0.2);
    color: var(--warning);
    border: 1px solid var(--warning);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* ===== RESPONSE CONSISTENCY ===== */
@media (max-width: 768px) {
    .upgrade-content {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-4);
    }
    
    .upgrade-icon {
        align-self: center;
    }
    
    .upgrade-actions {
        justify-content: center;
        width: 100%;
    }
    
    .upgrade-actions .btn {
        flex: 1;
        min-width: 120px;
    }
    
    .upgrade-prompt {
        padding: var(--spacing-4);
    }
    
    .section-header {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-2);
    }
}

@media (max-width: 480px) {
    .upgrade-prompt {
        margin-left: calc(-1 * var(--spacing-4));
        margin-right: calc(-1 * var(--spacing-4));
        border-radius: 0;
        border-left: none;
        border-right: none;
        padding: var(--spacing-4);
    }
    
    .upgrade-features {
        gap: var(--spacing-3);
    }
    
    .upgrade-actions {
        flex-direction: column;
    }
    
    .upgrade-actions .btn {
        width: 100%;
    }
}
