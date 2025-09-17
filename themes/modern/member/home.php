<?php
/**
 * EPIC Hub Member Dashboard Home
 * Halaman utama member area dengan pembatasan akses berdasarkan level
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

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

/* Activity Items */
.activity-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    background: var(--surface-3);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-3);
    transition: all var(--transition-fast);
}

.activity-item:hover {
    background: var(--surface-4);
    border-color: var(--gold-400);
    transform: translateX(2px);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-gold);
    color: var(--ink-900);
}

.activity-icon.success {
    background: linear-gradient(135deg, var(--success), var(--success-dark));
    color: white;
}

.activity-icon.warning {
    background: linear-gradient(135deg, var(--warning), var(--warning-dark));
    color: white;
}

.activity-content {
    flex: 1;
}

.activity-text {
    color: var(--ink-100);
    font-size: var(--font-size-sm);
    line-height: 1.5;
}

.activity-text strong {
    color: var(--gold-300);
    font-weight: var(--font-weight-semibold);
}

.activity-time {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
    margin-top: var(--spacing-1);
}

.activity-amount {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--success);
    background: rgba(16, 185, 129, 0.1);
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-md);
    border: 1px solid var(--success);
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
    
    .activity-item {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-3);
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
    
    .activity-item {
        padding: var(--spacing-3);
    }
}
</style>
