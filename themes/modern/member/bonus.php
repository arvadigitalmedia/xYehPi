<?php
/**
 * EPIC Hub Member Bonus Page
 * Halaman tracking komisi dan bonus untuk member area (EPIC & EPIS only)
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout system
require_once __DIR__ . '/components/page-layout.php';

$user = $user ?? epic_current_user();
$access_level = $access_level ?? epic_get_member_access_level($user);

// Get bonus/commission data (dummy data for now)
$transactions = [
    [
        'id' => 1,
        'type' => 'commission',
        'description' => 'Komisi Referral - John Doe',
        'amount' => 150000,
        'status' => 'completed',
        'date' => '2024-01-15 10:30:00',
        'reference' => 'REF-001',
        'from_user' => 'John Doe'
    ],
    [
        'id' => 2,
        'type' => 'bonus',
        'description' => 'Bonus Achievement - Target Bulanan',
        'amount' => 500000,
        'status' => 'completed',
        'date' => '2024-01-10 09:00:00',
        'reference' => 'BON-001',
        'from_user' => null
    ],
    [
        'id' => 3,
        'type' => 'commission',
        'description' => 'Komisi Referral - Jane Smith',
        'amount' => 75000,
        'status' => 'pending',
        'date' => '2024-01-12 14:20:00',
        'reference' => 'REF-002',
        'from_user' => 'Jane Smith'
    ],
    [
        'id' => 4,
        'type' => 'withdrawal',
        'description' => 'Penarikan ke Bank BCA',
        'amount' => -200000,
        'status' => 'completed',
        'date' => '2024-01-08 16:45:00',
        'reference' => 'WD-001',
        'from_user' => null
    ]
];

// Calculate statistics
$total_earned = array_sum(array_map(fn($t) => $t['amount'] > 0 ? $t['amount'] : 0, $transactions));
$total_withdrawn = abs(array_sum(array_map(fn($t) => $t['amount'] < 0 ? $t['amount'] : 0, $transactions)));
$current_balance = $total_earned - $total_withdrawn;
$pending_amount = array_sum(array_map(fn($t) => $t['status'] === 'pending' && $t['amount'] > 0 ? $t['amount'] : 0, $transactions));

$stats = [
    'current_balance' => $current_balance,
    'total_earned' => $total_earned,
    'total_withdrawn' => $total_withdrawn,
    'pending_amount' => $pending_amount,
    'this_month_earning' => 725000, // Dummy data
    'referral_count' => 12 // Dummy data
];
?>

<?php
// Data sudah disiapkan untuk bonus-content.php
// Include content
require_once __DIR__ . '/content/bonus-content.php';
?>

<!-- Quick Actions -->
<div class="quick-actions">
    <div class="action-card">
        <div class="action-icon">
            <i data-feather="users" width="24" height="24"></i>
        </div>
        <div class="action-content">
            <h4>Referral Program</h4>
            <p>Ajak teman dan dapatkan komisi hingga 30%</p>
            <a href="<?= epic_url('dashboard/member/prospects') ?>" class="action-link">
                Kelola Referral <i data-feather="arrow-right" width="14" height="14"></i>
            </a>
        </div>
    </div>
    
    <div class="action-card">
        <div class="action-icon">
            <i data-feather="target" width="24" height="24"></i>
        </div>
        <div class="action-content">
            <h4>Achievement Bonus</h4>
            <p>Capai target dan dapatkan bonus tambahan</p>
            <a href="#" class="action-link">
                Lihat Target <i data-feather="arrow-right" width="14" height="14"></i>
            </a>
        </div>
    </div>
    
    <div class="action-card">
        <div class="action-icon">
            <i data-feather="gift" width="24" height="24"></i>
        </div>
        <div class="action-content">
            <h4>Loyalty Rewards</h4>
            <p>Bonus khusus untuk member setia</p>
            <a href="#" class="action-link">
                Klaim Reward <i data-feather="arrow-right" width="14" height="14"></i>
            </a>
        </div>
    </div>
</div>



<!-- Withdraw Modal -->
<div class="modal" id="withdrawModal" style="display: none;">
    <div class="modal-overlay" onclick="closeWithdrawModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tarik Dana</h3>
            <button class="modal-close" onclick="closeWithdrawModal()">
                <i data-feather="x" width="20" height="20"></i>
            </button>
        </div>
        
        <form class="modal-body" onsubmit="processWithdraw(event)">
            <div class="withdraw-info">
                <div class="info-item">
                    <span class="info-label">Saldo Tersedia:</span>
                    <span class="info-value">Rp <?= number_format($current_balance, 0, ',', '.') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Minimal Penarikan:</span>
                    <span class="info-value">Rp 100.000</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Biaya Admin:</span>
                    <span class="info-value">Rp 5.000</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Jumlah Penarikan *</label>
                <input type="number" class="form-input" name="amount" min="100000" max="<?= $current_balance ?>" 
                       placeholder="Masukkan jumlah" required>
                <div class="form-help">Jumlah yang akan diterima = Jumlah penarikan - Biaya admin</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Bank Tujuan *</label>
                <select class="form-select" name="bank" required>
                    <option value="">Pilih Bank</option>
                    <option value="bca">BCA</option>
                    <option value="mandiri">Mandiri</option>
                    <option value="bni">BNI</option>
                    <option value="bri">BRI</option>
                    <option value="cimb">CIMB Niaga</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nomor Rekening *</label>
                <input type="text" class="form-input" name="account_number" 
                       placeholder="Masukkan nomor rekening" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nama Pemilik Rekening *</label>
                <input type="text" class="form-input" name="account_name" 
                       placeholder="Sesuai dengan rekening bank" required>
            </div>
            
            <div class="withdraw-summary">
                <div class="summary-item">
                    <span>Jumlah Penarikan:</span>
                    <span id="withdrawAmount">Rp 0</span>
                </div>
                <div class="summary-item">
                    <span>Biaya Admin:</span>
                    <span>Rp 5.000</span>
                </div>
                <div class="summary-item total">
                    <span>Yang Diterima:</span>
                    <span id="receivedAmount">Rp 0</span>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeWithdrawModal()">Batal</button>
                <button type="submit" class="btn btn-success">Proses Penarikan</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Page Header */
.page-header {
    margin-bottom: 2rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: #64748b;
    font-size: 1rem;
    margin: 0;
}

/* Balance Overview */
.balance-overview {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.balance-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 1rem;
    position: relative;
    overflow: hidden;
}

.balance-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.balance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 1;
}

.balance-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    backdrop-filter: blur(10px);
}

.balance-content {
    position: relative;
    z-index: 1;
}

.balance-label {
    font-size: 0.875rem;
    opacity: 0.8;
    margin-bottom: 0.5rem;
}

.balance-amount {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.balance-note {
    font-size: 0.875rem;
    opacity: 0.9;
}

.balance-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.stat-item {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-item .stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    color: #6366f1;
    border-radius: 0.75rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #64748b;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.action-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    display: flex;
    gap: 1rem;
    transition: all 0.2s;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.action-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border-radius: 0.75rem;
    flex-shrink: 0;
}

.action-content h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
}

.action-content p {
    margin: 0 0 1rem 0;
    color: #64748b;
    font-size: 0.875rem;
    line-height: 1.5;
}

.action-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #6366f1;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.875rem;
    transition: color 0.2s;
}

.action-link:hover {
    color: #4f46e5;
}



/* Withdraw Modal */
.withdraw-info {
    background: #f8fafc;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e2e8f0;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.info-value {
    font-size: 0.875rem;
    color: #374151;
    font-weight: 600;
}

.withdraw-summary {
    background: #f0f9ff;
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 1.5rem 0;
    border: 1px solid #bae6fd;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    font-size: 0.875rem;
}

.summary-item.total {
    border-top: 1px solid #7dd3fc;
    margin-top: 0.5rem;
    padding-top: 1rem;
    font-weight: 600;
    font-size: 1rem;
    color: #0c4a6e;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .balance-overview {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .balance-stats {
        grid-template-columns: 1fr;
    }
    
    .balance-amount {
        font-size: 2rem;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .action-card {
        flex-direction: column;
        text-align: center;
    }
    
    .transaction-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .transaction-meta {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .filter-tabs {
        flex-wrap: wrap;
    }
}
</style>

<script>
// Withdraw modal functions
function openWithdrawModal() {
    document.getElementById('withdrawModal').style.display = 'flex';
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').style.display = 'none';
}

function processWithdraw(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const withdrawData = Object.fromEntries(formData);
    
    // Validate amount
    const amount = parseInt(withdrawData.amount);
    if (amount < 100000) {
        alert('Jumlah penarikan minimal Rp 100.000');
        return;
    }
    
    if (amount > <?= $current_balance ?>) {
        alert('Jumlah penarikan melebihi saldo tersedia');
        return;
    }
    
    // Here you would normally send data to server
    console.log('Processing withdrawal:', withdrawData);
    
    // Show success message
    alert('Permintaan penarikan berhasil disubmit! Dana akan diproses dalam 1-3 hari kerja.');
    
    // Close modal and reset form
    closeWithdrawModal();
    event.target.reset();
    
    // Refresh page (in real app, you'd update the balance)
    // location.reload();
}

function refreshBalance() {
    console.log('Refreshing balance...');
    // Implement balance refresh
}

// Update withdraw summary
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.querySelector('input[name="amount"]');
    
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const amount = parseInt(this.value) || 0;
            const adminFee = 5000;
            const received = Math.max(0, amount - adminFee);
            
            document.getElementById('withdrawAmount').textContent = 
                'Rp ' + amount.toLocaleString('id-ID');
            document.getElementById('receivedAmount').textContent = 
                'Rp ' + received.toLocaleString('id-ID');
        });
    }
});
</script>