<?php
/**
 * EPIC Hub Admin Payout Management
 * Halaman payout dengan layout global admin
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Start output buffering to prevent header issues
ob_start();

try {
    // Include layout helper
    require_once __DIR__ . '/layout-helper.php';
    
    // Check admin access sudah dilakukan di layout helper
    $user = epic_current_user();
    
    if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
        epic_route_403();
        return;
    }

    $success = '';
    $error = '';
    
    // Handle payout processing
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payout'])) {
        $member_id = (int)($_POST['member_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        
        if ($member_id && $amount > 0) {
            try {
                // Get member commission balance
                $commission_balance = db()->selectValue(
                    "SELECT COALESCE(SUM(amount_in) - SUM(amount_out), 0) as balance 
                     FROM " . db()->table(TABLE_TRANSACTIONS) . " 
                     WHERE user_id = ? AND type = 'commission'",
                    [$member_id]
                ) ?: 0;
                
                if ($amount <= $commission_balance) {
                    // Process payout
                    db()->beginTransaction();
                    
                    // Create payout transaction
                    db()->insert(db()->table(TABLE_TRANSACTIONS), [
                        'user_id' => $member_id,
                        'type' => 'payout',
                        'amount_out' => $amount,
                        'status' => 'completed',
                        'description' => 'Commission payout processed by admin',
                        'created_at' => date('Y-m-d H:i:s'),
                        'processed_by' => $user['id']
                    ]);
                    
                    // Log activity
                    epic_log_activity($user['id'], 'payout_processed', "Processed payout of Rp " . number_format($amount) . " for member ID: " . $member_id);
                    
                    db()->commit();
                    $success = 'Payout berhasil diproses!';
                } else {
                    $error = 'Jumlah payout melebihi saldo komisi yang tersedia.';
                }
            } catch (Exception $e) {
                db()->rollback();
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        } else {
            $error = 'Data tidak valid.';
        }
    }

    // Get members with commission balance
    $members_with_commission = db()->select(
        "SELECT u.id, u.name, u.email, u.phone, u.additional_data,
                COALESCE(SUM(t.amount_in) - SUM(t.amount_out), 0) as commission_balance,
                COUNT(CASE WHEN t.type = 'payout' THEN 1 END) as total_payouts,
                MAX(CASE WHEN t.type = 'payout' THEN t.created_at END) as last_payout_date
         FROM " . db()->table(TABLE_USERS) . " u
         LEFT JOIN " . db()->table(TABLE_TRANSACTIONS) . " t ON u.id = t.user_id AND t.type IN ('commission', 'payout')
         WHERE u.role IN ('user', 'super_admin') AND u.status IN ('free', 'epic')
         GROUP BY u.id, u.name, u.email, u.phone, u.additional_data
         HAVING commission_balance > 0
         ORDER BY commission_balance DESC"
    ) ?: [];

    // Get payout statistics for charts
    $monthly_payouts = db()->select(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as payout_count,
                SUM(amount_out) as total_amount
         FROM " . db()->table(TABLE_TRANSACTIONS) . "
         WHERE type = 'payout' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
         GROUP BY month
         ORDER BY month"
    ) ?: [];
    
    // Get recent payouts
    $recent_payouts = db()->select(
        "SELECT t.*, u.name as member_name, u.email as member_email
         FROM " . db()->table(TABLE_TRANSACTIONS) . " t
         JOIN " . db()->table(TABLE_USERS) . " u ON t.user_id = u.id
         WHERE t.type = 'payout'
         ORDER BY t.created_at DESC
         LIMIT 10"
    ) ?: [];
    
    // Calculate statistics
    $total_members = count($members_with_commission);
    $total_commission_balance = array_sum(array_column($members_with_commission, 'commission_balance'));
    $total_payouts_this_month = db()->selectValue(
        "SELECT COALESCE(SUM(amount_out), 0) 
         FROM " . db()->table(TABLE_TRANSACTIONS) . " 
         WHERE type = 'payout' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')"
    ) ?: 0;

    // Prepare data untuk layout
    $layout_data = [
        'page_title' => 'Payout Management - EPIC Hub Admin',
        'header_title' => 'Payout Management',
        'current_page' => 'payout',
        'breadcrumb' => [
            ['text' => 'Admin', 'url' => epic_url('admin')],
            ['text' => 'Manage', 'url' => epic_url('admin')],
            ['text' => 'Payout']
        ],
        'content_file' => __DIR__ . '/content/payout-content.php',
        // Pass variables ke content
        'success' => $success,
        'error' => $error,
        'members_with_commission' => $members_with_commission,
        'monthly_payouts' => $monthly_payouts,
        'recent_payouts' => $recent_payouts,
        'total_members' => $total_members,
        'total_commission_balance' => $total_commission_balance,
        'total_payouts_this_month' => $total_payouts_this_month,
        'user' => $user
    ];

    // Render halaman dengan layout global
    epic_render_admin_page($layout_data['content_file'], $layout_data);
    
} catch (Exception $e) {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Log the error
    error_log('Payout page error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    // Show user-friendly error
    http_response_code(500);
    echo '<h1>Internal Server Error</h1>';
    echo '<p>Terjadi kesalahan pada halaman payout. Silakan coba lagi atau hubungi administrator.</p>';
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo '<hr>';
        echo '<h3>Debug Information:</h3>';
        echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . $e->getFile() . '</p>';
        echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
    }
}

// Flush output buffer
if (ob_get_level()) {
    ob_end_flush();
}
?>