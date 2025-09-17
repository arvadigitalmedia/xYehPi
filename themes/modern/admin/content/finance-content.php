<?php
/**
 * EPIC Hub Admin Finance Management Content
 * Konten halaman finance untuk layout global
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
 * 
 * Variables yang tersedia dari parent scope:
 * - $success, $error: Alert messages
 * - $stats, $overall_stats: Statistics data
 * - $transactions: Transaction data
 * - $monthly_data: Monthly comparison data
 * - $available_months: Available months for filter
 * - $selected_month, $search_query: Filter parameters
 * - $user: Current admin user
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Ensure variables are set with defaults
$success = $success ?? '';
$error = $error ?? '';
$stats = $stats ?? [];
$overall_stats = $overall_stats ?? [];
$transactions = $transactions ?? [];
$monthly_data = $monthly_data ?? [];
$available_months = $available_months ?? [];
$selected_month = $selected_month ?? date('Y-m');
$search_query = $search_query ?? '';
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

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card income">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Pemasukan</h3>
            <i data-feather="trending-up" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($stats['total_income'], 0, ',', '.') ?></div>
        <div class="stat-card-subtitle">Bulan <?= date('F Y', strtotime($selected_month . '-01')) ?></div>
        <div class="stat-card-comparison">
            <span class="comparison-label">Total Keseluruhan:</span>
            <span class="comparison-value">Rp <?= number_format($overall_stats['total_income'], 0, ',', '.') ?></span>
        </div>
    </div>
    
    <div class="stat-card expense">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Pengeluaran</h3>
            <i data-feather="trending-down" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($stats['total_expense'], 0, ',', '.') ?></div>
        <div class="stat-card-subtitle">Bulan <?= date('F Y', strtotime($selected_month . '-01')) ?></div>
        <div class="stat-card-comparison">
            <span class="comparison-label">Total Keseluruhan:</span>
            <span class="comparison-value">Rp <?= number_format($overall_stats['total_expense'], 0, ',', '.') ?></span>
        </div>
    </div>
    
    <div class="stat-card balance <?= $stats['net_balance'] >= 0 ? 'positive' : 'negative' ?>">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Saldo Bersih</h3>
            <i data-feather="<?= $stats['net_balance'] >= 0 ? 'plus-circle' : 'minus-circle' ?>" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value">Rp <?= number_format($stats['net_balance'], 0, ',', '.') ?></div>
        <div class="stat-card-subtitle">Bulan <?= date('F Y', strtotime($selected_month . '-01')) ?></div>
        <div class="stat-card-comparison">
            <span class="comparison-label">Total Keseluruhan:</span>
            <span class="comparison-value">Rp <?= number_format($overall_stats['net_balance'], 0, ',', '.') ?></span>
        </div>
    </div>
    
    <div class="stat-card transactions">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total Transaksi</h3>
            <i data-feather="activity" class="stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?= number_format($stats['transaction_count']) ?></div>
        <div class="stat-card-subtitle">Bulan <?= date('F Y', strtotime($selected_month . '-01')) ?></div>
        <div class="stat-card-comparison">
            <span class="comparison-label">Total Keseluruhan:</span>
            <span class="comparison-value"><?= number_format($overall_stats['total_transactions']) ?></span>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="filter-section">
    <div class="filter-card">
        <div class="filter-header">
            <h3 class="filter-title">Filter Laporan Keuangan</h3>
            <div class="filter-subtitle">Pilih bulan dan cari transaksi</div>
        </div>
        
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="form-group">
                    <label class="form-label" for="month">Bulan</label>
                    <select class="form-input" name="month" id="month">
                        <?php 
                        $month_names = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                        
                        if (!empty($available_months)): 
                            foreach ($available_months as $month_data): 
                                $month_parts = explode('-', $month_data['month']);
                                $year = $month_parts[0];
                                $month_num = $month_parts[1];
                                $month_name = $month_names[$month_num] ?? $month_num;
                                $selected = ($month_data['month'] === $selected_month) ? 'selected' : '';
                        ?>
                            <option value="<?= $month_data['month'] ?>" <?= $selected ?>>
                                <?= $month_name ?> <?= $year ?>
                            </option>
                        <?php 
                            endforeach;
                        else: 
                        ?>
                            <option value="<?= $selected_month ?>"><?= date('F Y', strtotime($selected_month . '-01')) ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="search">Cari Transaksi</label>
                    <div class="search-input-group">
                        <i data-feather="search" class="search-icon"></i>
                        <input type="text" class="form-input" name="search" id="search" 
                               value="<?= htmlspecialchars($search_query) ?>"
                               placeholder="Cari berdasarkan keterangan atau jenis...">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="filter" width="16" height="16"></i>
                            Filter
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i data-feather="x" width="16" height="16"></i>
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Financial Report Table -->
<div class="content-section">
    <div class="section-header">
        <h2 class="section-title">Laporan Keuangan</h2>
        <div class="section-subtitle">
            <?php if (!empty($search_query)): ?>
                Hasil pencarian "<?= htmlspecialchars($search_query) ?>" untuk 
            <?php endif; ?>
            <?= date('F Y', strtotime($selected_month . '-01')) ?>
            (<?= count($transactions) ?> transaksi)
        </div>
    </div>
    
    <?php if (empty($transactions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-feather="file-text" width="48" height="48"></i>
            </div>
            <h3 class="empty-state-title">Tidak Ada Transaksi</h3>
            <p class="empty-state-text">
                <?php if (!empty($search_query)): ?>
                    Tidak ditemukan transaksi yang sesuai dengan pencarian "<?= htmlspecialchars($search_query) ?>".
                <?php else: ?>
                    Belum ada transaksi untuk bulan <?= date('F Y', strtotime($selected_month . '-01')) ?>.
                <?php endif; ?>
            </p>
            <?php if (!empty($search_query)): ?>
                <a href="?month=<?= urlencode($selected_month) ?>" class="btn btn-secondary">
                    <i data-feather="x" width="16" height="16"></i>
                    Hapus Pencarian
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="finance-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan Transaksi</th>
                        <th>Jenis</th>
                        <th>User</th>
                        <th class="text-right">Pemasukan</th>
                        <th class="text-right">Pengeluaran</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="transaction-row">
                            <td>
                                <div class="transaction-date">
                                    <div class="date-main"><?= date('d/m/Y', strtotime($transaction['created_at'])) ?></div>
                                    <div class="date-time"><?= date('H:i', strtotime($transaction['created_at'])) ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="transaction-description">
                                    <?= htmlspecialchars($transaction['description'] ?? 'Tidak ada keterangan') ?>
                                    <?php if (!empty($transaction['type'])): ?>
                                        <div class="transaction-category">
                                            <i data-feather="tag" width="12" height="12"></i>
                                            <?= htmlspecialchars($transaction['type']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="transaction-type <?= strtolower($transaction['type']) ?>">
                                    <i data-feather="<?= $transaction['amount_in'] > 0 ? 'trending-up' : 'trending-down' ?>" width="12" height="12"></i>
                                    <?= ucfirst($transaction['type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($transaction['user_name']): ?>
                                    <div class="user-info">
                                        <div class="user-name">
                                            <i data-feather="user" width="12" height="12"></i>
                                            <?= htmlspecialchars($transaction['user_name']) ?>
                                        </div>
                                        <div class="user-id">ID: <?= $transaction['user_id'] ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="user-info">
                                        <div class="user-name">
                                            <i data-feather="settings" width="12" height="12"></i>
                                            System
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if ($transaction['amount_in'] > 0): ?>
                                    <span class="amount income">
                                        <i data-feather="plus-circle" width="14" height="14"></i>
                                        +Rp <?= number_format($transaction['amount_in'], 0, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="amount-empty">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if ($transaction['amount_out'] > 0): ?>
                                    <span class="amount expense">
                                        <i data-feather="minus-circle" width="14" height="14"></i>
                                        -Rp <?= number_format($transaction['amount_out'], 0, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="amount-empty">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <span class="amount balance <?= $transaction['running_balance'] >= 0 ? 'positive' : 'negative' ?>">
                                    <i data-feather="<?= $transaction['running_balance'] >= 0 ? 'trending-up' : 'trending-down' ?>" width="14" height="14"></i>
                                    Rp <?= number_format($transaction['running_balance'], 0, ',', '.') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Summary Row -->
        <div class="table-summary">
            <div class="summary-row">
                <div class="summary-label">Total untuk <?= date('F Y', strtotime($selected_month . '-01')) ?>:</div>
                <div class="summary-values">
                    <div class="summary-item income">
                        <span class="summary-title">Pemasukan:</span>
                        <span class="summary-amount">Rp <?= number_format($stats['total_income'], 0, ',', '.') ?></span>
                    </div>
                    <div class="summary-item expense">
                        <span class="summary-title">Pengeluaran:</span>
                        <span class="summary-amount">Rp <?= number_format($stats['total_expense'], 0, ',', '.') ?></span>
                    </div>
                    <div class="summary-item balance <?= $stats['net_balance'] >= 0 ? 'positive' : 'negative' ?>">
                        <span class="summary-title">Saldo Bersih:</span>
                        <span class="summary-amount">Rp <?= number_format($stats['net_balance'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- All styles moved to separate CSS file: themes/modern/admin/pages/finance-management.css -->

<!-- JavaScript functionality is now in separate JS file: themes/modern/admin/pages/finance-management.js -->