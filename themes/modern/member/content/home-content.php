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
    <!-- Welcome Header without Photo -->
    <div class="welcome-header-card">
        <div class="welcome-header-content">
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
                    <div class="welcome-upgrade-section">
                        <div class="upgrade-benefits">
                            <h4 class="upgrade-benefits-title">Upgrade ke EPIC Account</h4>
                            <ul class="upgrade-benefits-list">
                                <li><i data-feather="check" width="14" height="14"></i> Sistem Referral Aktif</li>
                                <li><i data-feather="check" width="14" height="14"></i> Komisi & Bonus Tracking</li>
                                <li><i data-feather="check" width="14" height="14"></i> Analytics Lengkap</li>
                                <li><i data-feather="check" width="14" height="14"></i> Priority Support</li>
                            </ul>
                        </div>
                        <div class="upgrade-actions">
                            <a href="<?= epic_url('upgrade') ?>" class="btn btn-warning btn-lg">
                                <i data-feather="star" width="16" height="16" class="icon-light"></i>
                                Upgrade Sekarang
                            </a>
                            <a href="<?= epic_url('pricing') ?>" class="btn btn-secondary">
                                <i data-feather="info" width="16" height="16" class="icon-light"></i>
                                Lihat Paket
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= epic_url('dashboard/member/profile') ?>" class="btn btn-secondary">
                        <i data-feather="settings" width="16" height="16" class="icon-light"></i>
                        Pengaturan
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Combined EPIS Supervisor & Referral Link Card -->
    <?php 
    // Get EPIS Supervisor information
    $epis_supervisor = null;
    $epis_account = null;
    if (!empty($user['epis_supervisor_id'])) {
        $epis_supervisor = epic_get_user($user['epis_supervisor_id']);
        if ($epis_supervisor && $epis_supervisor['status'] === 'epis') {
            // Get EPIS account details
            $epis_account = db()->selectOne(
                "SELECT * FROM epic_epis_accounts WHERE user_id = ?",
                [$epis_supervisor['id']]
            );
        }
    }
    
    // Generate referral link - direct to registration page
    $referral_code = $user['affiliate_code'] ?? $user['referral_code'] ?? str_pad($user['id'], 6, '0', STR_PAD_LEFT);
    $referral_link = epic_url('register?ref=' . urlencode($referral_code));
    ?>
    <div class="combined-info-card">
        <div class="combined-card-content">
            <!-- Left Column: EPIS Supervisor -->
            <div class="epis-supervisor-column">
                <div class="section-header">
                    <div class="section-icon">
                        <i data-feather="user-check" width="20" height="20" class="icon-light"></i>
                    </div>
                    <h3 class="section-title">EPIS Supervisor</h3>
                </div>
                
                <?php if ($epis_supervisor): ?>
                    <div class="supervisor-info">
                        <div class="supervisor-name"><?= htmlspecialchars($epis_supervisor['name']) ?></div>
                        <?php if ($epis_account): ?>
                             <div class="supervisor-details">
                                 <div class="detail-item-left">
                                     <span class="detail-label">Kode: <?= htmlspecialchars($epis_account['epis_code']) ?></span>
                                 </div>
                                 <?php if (!empty($epis_account['territory_name'])): ?>
                                     <div class="detail-item-left">
                                         <span class="detail-label">Territory: <?= htmlspecialchars($epis_account['territory_name']) ?></span>
                                     </div>
                                 <?php endif; ?>
                             </div>
                         <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="supervisor-info no-supervisor">
                        <div class="supervisor-name">Belum Terhubung</div>
                        <div class="supervisor-note">
                            <i data-feather="alert-circle" width="14" height="14" class="icon-warning"></i>
                            Hubungi admin untuk penugasan supervisor
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column: Referral Link -->
            <div class="referral-link-column">
                <div class="section-header">
                    <div class="section-icon">
                        <i data-feather="share-2" width="20" height="20" class="icon-light"></i>
                    </div>
                    <h3 class="section-title">Link Referral Anda</h3>
                </div>
                
                <div class="referral-content">
                    <div class="referral-link-input-group">
                        <input type="text" 
                               id="referralLinkInput" 
                               class="referral-link-input" 
                               value="<?= htmlspecialchars($referral_link) ?>" 
                               readonly>
                        <button type="button" 
                                class="referral-copy-btn" 
                                onclick="copyReferralLink()" 
                                title="Salin Link">
                            <i data-feather="copy" width="16" height="16" class="icon-light"></i>
                            <span class="copy-text">Salin</span>
                        </button>
                    </div>
                    
                    <div class="referral-stats">
                         <div class="referral-stat-item-left">
                             <i data-feather="tag" width="14" height="14" class="icon-light"></i>
                             <span class="stat-label">Kode Referral: <?= htmlspecialchars($referral_code) ?></span>
                         </div>
                         <?php if (in_array($access_level, ['epic', 'epis'])): ?>
                             <div class="referral-stat-item-left">
                                 <i data-feather="users" width="14" height="14" class="icon-light"></i>
                                 <span class="stat-label">Total Referral: <?= number_format($stats['active_referrals'] ?? 0) ?></span>
                             </div>
                         <?php endif; ?>
                     </div>
                </div>
            </div>
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

<!-- Spacing untuk footer -->
<div style="margin-bottom: var(--spacing-8);"></div>