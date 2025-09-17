<?php
/**
 * EPIC Hub Member Dashboard Home Content
 * Konten halaman utama member area
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Data sudah disiapkan di home.php
?>

<!-- Welcome Section with Integrated Stats -->
<div class="welcome-section">
    <div class="welcome-card-with-photo">
        <div class="welcome-photo-container">
            <div class="welcome-photo">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= epic_url('uploads/profiles/' . $user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>" class="user-photo">
                <?php else: ?>
                    <div class="user-photo-placeholder">
                        <i data-feather="user" width="48" height="48"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="welcome-main-content">
            <div class="welcome-header-new">
                <div class="welcome-text-content">
                    <h1 class="welcome-title-new">
                        Selamat datang, <?= htmlspecialchars($user['name']) ?>!
                    </h1>
                    <div class="welcome-badge-new">
                        <?php 
                        $level_badges = [
                            'free' => ['text' => 'Free Account', 'class' => 'pill-info'],
                            'epic' => ['text' => 'EPIC Account', 'class' => 'pill-success'],
                            'epis' => ['text' => 'EPIS Account', 'class' => 'pill-warning']
                        ];
                        $badge = $level_badges[$access_level] ?? ['text' => 'Member', 'class' => 'pill-info'];
                        ?>
                        <span class="<?= $badge['class'] ?>"><?= $badge['text'] ?></span>
                    </div>
                    <p class="welcome-description-new">
                        <?php 
                        $welcome_messages = [
                            'free' => 'Mulai perjalanan Anda dengan akses dasar ke platform EPIC Hub. Upgrade untuk membuka semua fitur premium dan mulai earning!',
                            'epic' => 'Nikmati akses penuh ke semua fitur premium EPIC Hub. Kelola referral, dapatkan komisi, dan maksimalkan potensi earning Anda!',
                            'epis' => 'Sebagai member EPIS, Anda memiliki akses ke fitur team management dan komisi maksimal. Kelola tim dan raih kesuksesan bersama!'
                        ];
                        echo $welcome_messages[$access_level] ?? 'Selamat datang di member area EPIC Hub! Jelajahi semua fitur yang tersedia untuk Anda.';
                        ?>
                    </p>
                </div>
                
                <div class="welcome-actions-new">
                    <?php if ($access_level === 'free'): ?>
                        <a href="<?= epic_url('upgrade') ?>" class="btn btn-primary">
                            <i data-feather="arrow-up" width="16" height="16"></i>
                            Upgrade
                        </a>
                    <?php else: ?>
                        <a href="<?= epic_url('dashboard/member/profile') ?>" class="btn btn-secondary">
                            <i data-feather="settings" width="16" height="16"></i>
                            Pengaturan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Integrated Statistics Grid -->
            <div class="welcome-stats-grid">
                <!-- Total Pesanan -->
                <div class="welcome-stat-card">
                    <div class="stat-icon-container-new">
                        <div class="stat-icon-new">
                            <i data-feather="shopping-cart" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="stat-content-new">
                        <div class="stat-title-new">Total Pesanan</div>
                        <div class="stat-value-new"><?= number_format($stats['total_orders']) ?></div>
                        <div class="stat-change-new neutral">
                            <i data-feather="calendar" width="12" height="12"></i>
                            <span>Semua waktu</span>
                        </div>
                    </div>
                </div>
                
                <!-- Total Pendapatan -->
                <?php if (in_array($access_level, ['epic', 'epis'])): ?>
                    <div class="welcome-stat-card">
                        <div class="stat-icon-container-new">
                            <div class="stat-icon-new stat-icon-success-new">
                                <i data-feather="dollar-sign" width="20" height="20"></i>
                            </div>
                        </div>
                        <div class="stat-content-new">
                            <div class="stat-title-new">Total Pendapatan</div>
                            <div class="stat-value-new">Rp <?= number_format($stats['total_earnings'], 0, ',', '.') ?></div>
                            <div class="stat-change-new positive">
                                <i data-feather="trending-up" width="12" height="12"></i>
                                <span>+12% bulan ini</span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="welcome-stat-card stat-locked-new">
                        <div class="stat-icon-container-new">
                            <div class="stat-icon-new stat-icon-locked-new">
                                <i data-feather="lock" width="20" height="20"></i>
                            </div>
                        </div>
                        <div class="stat-content-new">
                            <div class="stat-title-new">Total Pendapatan</div>
                            <div class="stat-value-new">Locked</div>
                            <div class="stat-change-new neutral">
                                <span class="pill-warning">Upgrade untuk akses</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Referral Aktif -->
                <?php if (in_array($access_level, ['epic', 'epis'])): ?>
                    <div class="welcome-stat-card">
                        <div class="stat-icon-container-new">
                            <div class="stat-icon-new stat-icon-info-new">
                                <i data-feather="users" width="20" height="20"></i>
                            </div>
                        </div>
                        <div class="stat-content-new">
                            <div class="stat-title-new">Referral Aktif</div>
                            <div class="stat-value-new"><?= number_format($stats['active_referrals']) ?></div>
                            <div class="stat-change-new positive">
                                <i data-feather="user-plus" width="12" height="12"></i>
                                <span>+3 minggu ini</span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="welcome-stat-card stat-locked-new">
                        <div class="stat-icon-container-new">
                            <div class="stat-icon-new stat-icon-locked-new">
                                <i data-feather="lock" width="20" height="20"></i>
                            </div>
                        </div>
                        <div class="stat-content-new">
                            <div class="stat-title-new">Referral Aktif</div>
                            <div class="stat-value-new">Locked</div>
                            <div class="stat-change-new neutral">
                                <span class="pill-warning">Upgrade untuk akses</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Tingkat Konversi -->
                <?php if (in_array($access_level, ['epic', 'epis'])): ?>
                    <div class="welcome-stat-card">
                        <div class="stat-icon-container-new">
                            <div class="stat-icon-new stat-icon-warning-new">
                                <i data-feather="target" width="20" height="20"></i>
                            </div>
                        </div>
                        <div class="stat-content-new">
                            <div class="stat-title-new">Tingkat Konversi</div>
                            <div class="stat-value-new"><?= $stats['conversion_rate'] ?></div>
                            <div class="stat-change-new positive">
                                <i data-feather="trending-up" width="12" height="12"></i>
                                <span>+5% dari target</span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="welcome-stat-card stat-locked-new">
                        <div class="stat-icon-container-new">
                            <div class="stat-icon-new stat-icon-locked-new">
                                <i data-feather="lock" width="20" height="20"></i>
                            </div>
                        </div>
                        <div class="stat-content-new">
                            <div class="stat-title-new">Tingkat Konversi</div>
                            <div class="stat-value-new">Locked</div>
                            <div class="stat-change-new neutral">
                                <span class="pill-warning">Upgrade untuk akses</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upgrade Prompt for Free Users -->
<?php if ($access_level === 'free'): ?>
    <div class="upgrade-prompt">
        <div class="upgrade-content">
            <div class="upgrade-icon">
                <i data-feather="star" width="24" height="24"></i>
            </div>
            <div class="upgrade-text">
                <h3 class="upgrade-title">Upgrade ke EPIC Account</h3>
                <p class="upgrade-desc">
                    Dapatkan akses ke semua fitur premium dan mulai earning dengan sistem referral!
                </p>
                <ul class="upgrade-features">
                    <li class="upgrade-feature">Sistem Referral Aktif</li>
                    <li class="upgrade-feature">Komisi & Bonus Tracking</li>
                    <li class="upgrade-feature">Landing Page Premium</li>
                    <li class="upgrade-feature">Analytics Lengkap</li>
                    <li class="upgrade-feature">Priority Support</li>
                </ul>
                <div class="upgrade-actions">
                    <a href="<?= epic_url('upgrade') ?>" class="btn btn-warning btn-lg">
                        <i data-feather="arrow-up" width="16" height="16"></i>
                        Upgrade Sekarang
                    </a>
                    <a href="<?= epic_url('pricing') ?>" class="btn btn-secondary">
                        Lihat Paket
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Recent Activity -->
<div class="activity-section">
    <div class="section-header">
        <h3 class="section-title">Aktivitas Terbaru</h3>
        <p class="section-subtitle">Riwayat aktivitas dan transaksi terkini</p>
    </div>
    
    <div class="activity-container">
        <?php if (!empty($recent_activities)): ?>
            <?php foreach ($recent_activities as $activity): ?>
                <?php 
                // Skip commission activities for free users
                if ($access_level === 'free' && in_array($activity['type'], ['commission', 'referral'])) {
                    continue;
                }
                ?>
                <div class="activity-card">
                    <div class="activity-icon-container">
                        <div class="activity-icon activity-icon-<?= $activity['status'] ?>">
                            <i data-feather="<?= $activity['icon'] ?>" width="20" height="20"></i>
                        </div>
                    </div>
                    <div class="activity-content">
                        <div class="activity-header">
                            <div class="activity-title"><?= htmlspecialchars($activity['title']) ?></div>
                            <div class="activity-time">
                                <i data-feather="clock" width="14" height="14"></i>
                                <span><?= epic_format_relative_time($activity['time']) ?></span>
                            </div>
                        </div>
                        <div class="activity-description"><?= htmlspecialchars($activity['description']) ?></div>
                        <?php if ($activity['amount'] > 0): ?>
                            <div class="activity-amount">
                                <span class="amount-label">Jumlah:</span>
                                <span class="amount-value">+Rp <?= number_format($activity['amount'], 0, ',', '.') ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="activity-status">
                        <span class="pill-<?= $activity['status'] ?>"><?= ucfirst($activity['status']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="activity-empty-state">
                <div class="empty-state-icon">
                    <i data-feather="activity" width="48" height="48"></i>
                </div>
                <div class="empty-state-content">
                    <h4 class="empty-state-title">Belum Ada Aktivitas</h4>
                    <p class="empty-state-text">
                        Aktivitas Anda akan muncul di sini setelah Anda mulai menggunakan platform.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions-section">
    <div class="section-header">
        <h3 class="section-title">Quick Actions</h3>
        <p class="section-subtitle">Akses cepat ke fitur utama</p>
    </div>
    
    <div class="quick-actions-grid">
        <a href="<?= epic_url('dashboard/member/profile') ?>" class="quick-action-card">
            <div class="quick-action-icon">
                <i data-feather="user" width="24" height="24"></i>
            </div>
            <div class="quick-action-content">
                <div class="quick-action-title">Edit Profil</div>
                <div class="quick-action-desc">Kelola informasi akun Anda</div>
            </div>
            <div class="quick-action-arrow">
                <i data-feather="arrow-right" width="16" height="16"></i>
            </div>
        </a>
        
        <?php if (epic_member_can_access('prospects', $user)): ?>
            <a href="<?= epic_url('dashboard/member/prospects') ?>" class="quick-action-card">
                <div class="quick-action-icon">
                    <i data-feather="users" width="24" height="24"></i>
                </div>
                <div class="quick-action-content">
                    <div class="quick-action-title">Kelola Prospek</div>
                    <div class="quick-action-desc">Manajemen prospek dan leads</div>
                </div>
                <div class="quick-action-arrow">
                    <i data-feather="arrow-right" width="16" height="16"></i>
                </div>
            </a>
        <?php else: ?>
            <div class="quick-action-card quick-action-locked">
                <div class="quick-action-icon">
                    <i data-feather="lock" width="24" height="24"></i>
                </div>
                <div class="quick-action-content">
                    <div class="quick-action-title">Kelola Prospek</div>
                    <div class="quick-action-desc">Upgrade untuk akses</div>
                </div>
                <div class="quick-action-badge">
                    <span class="pill-warning">Premium</span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (epic_member_can_access('bonus', $user)): ?>
            <a href="<?= epic_url('dashboard/member/bonus') ?>" class="quick-action-card">
                <div class="quick-action-icon">
                    <i data-feather="dollar-sign" width="24" height="24"></i>
                </div>
                <div class="quick-action-content">
                    <div class="quick-action-title">Bonus Cash</div>
                    <div class="quick-action-desc">Lihat komisi dan bonus Anda</div>
                </div>
                <div class="quick-action-arrow">
                    <i data-feather="arrow-right" width="16" height="16"></i>
                </div>
            </a>
        <?php else: ?>
            <div class="quick-action-card quick-action-locked">
                <div class="quick-action-icon">
                    <i data-feather="lock" width="24" height="24"></i>
                </div>
                <div class="quick-action-content">
                    <div class="quick-action-title">Bonus Cash</div>
                    <div class="quick-action-desc">Upgrade untuk akses</div>
                </div>
                <div class="quick-action-badge">
                    <span class="pill-warning">Premium</span>
                </div>
            </div>
        <?php endif; ?>
        
        <a href="<?= epic_url('dashboard/member/products') ?>" class="quick-action-card">
            <div class="quick-action-icon">
                <i data-feather="package" width="24" height="24"></i>
            </div>
            <div class="quick-action-content">
                <div class="quick-action-title">Akses Produk</div>
                <div class="quick-action-desc">Produk yang dapat Anda akses</div>
            </div>
            <div class="quick-action-arrow">
                <i data-feather="arrow-right" width="16" height="16"></i>
            </div>
        </a>
    </div>
    
    <!-- Secondary Actions Row -->
    <div class="quick-actions-secondary">
        <a href="<?= epic_url('dashboard/member/orders') ?>" class="quick-action-secondary">
            <div class="quick-action-icon-sm">
                <i data-feather="shopping-cart" width="18" height="18"></i>
            </div>
            <span>History Order</span>
            <i data-feather="external-link" width="14" height="14"></i>
        </a>
        
        <?php if ($access_level === 'free'): ?>
            <a href="<?= epic_url('upgrade') ?>" class="quick-action-secondary quick-action-upgrade">
                <div class="quick-action-icon-sm">
                    <i data-feather="star" width="18" height="18"></i>
                </div>
                <span>Upgrade Account</span>
                <i data-feather="arrow-up-right" width="14" height="14"></i>
            </a>
        <?php else: ?>
            <a href="<?= epic_url('dashboard/member/analytics') ?>" class="quick-action-secondary">
                <div class="quick-action-icon-sm">
                    <i data-feather="bar-chart-2" width="18" height="18"></i>
                </div>
                <span>Analytics</span>
                <i data-feather="external-link" width="14" height="14"></i>
            </a>
        <?php endif; ?>
        
        <a href="<?= epic_url('support') ?>" class="quick-action-secondary">
            <div class="quick-action-icon-sm">
                <i data-feather="help-circle" width="18" height="18"></i>
            </div>
            <span>Bantuan</span>
            <i data-feather="external-link" width="14" height="14"></i>
        </a>
    </div>
</div>