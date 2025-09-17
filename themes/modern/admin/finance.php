<?php
/**
 * EPIC Hub Admin Finance Management
 * Halaman finance dengan layout global admin
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include routing helper untuk error handling yang konsisten
require_once __DIR__ . '/routing-helper.php';

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Validate admin access dengan proper error handling
$user = epic_validate_admin_access('admin', 'admin/manage/finance');

// Validate system requirements
if (!epic_validate_system_requirements()) {
    epic_handle_403_error();
    exit;
}

$success = '';
$error = '';

// Get filter parameters
$selected_month = $_GET['month'] ?? date('Y-m');
$search_query = $_GET['search'] ?? '';

// Parse month for database queries
$month_start = $selected_month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));

// Build WHERE conditions
$where_conditions = [];
$params = [];

// Month filter
$where_conditions[] = "DATE(created_at) BETWEEN ? AND ?";
$params[] = $month_start;
$params[] = $month_end;

// Search filter
if (!empty($search_query)) {
    $where_conditions[] = "(description LIKE ? OR type LIKE ?)";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
}

$where_clause = implode(' AND ', $where_conditions);

// Get financial transactions (only lap_code=1 for financial reports like the reference)
$transactions = epic_safe_db_query(
    "SELECT t.*, u.name as user_name
     FROM " . TABLE_TRANSACTIONS . " t
     LEFT JOIN " . TABLE_USERS . " u ON t.user_id = u.id
     WHERE {$where_clause}
     ORDER BY t.created_at ASC",
    $params,
    'select'
);

// Get available months from transactions
$available_months = epic_safe_db_query(
    "SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as month
     FROM " . TABLE_TRANSACTIONS . "
     ORDER BY month DESC",
    [],
    'select'
);

// Calculate running balance
$running_balance = 0;
foreach ($transactions as &$transaction) {
    $amount_in = (float)($transaction['amount_in'] ?? 0);
    $amount_out = (float)($transaction['amount_out'] ?? 0);
    $running_balance += ($amount_in - $amount_out);
    $transaction['running_balance'] = $running_balance;
}
unset($transaction);

// Reverse array to show chronological order with correct running balance
$transactions = array_reverse($transactions);

// Recalculate running balance in chronological order
$running_balance = 0;
foreach ($transactions as &$transaction) {
    $amount_in = (float)($transaction['amount_in'] ?? 0);
    $amount_out = (float)($transaction['amount_out'] ?? 0);
    $running_balance += ($amount_in - $amount_out);
    $transaction['running_balance'] = $running_balance;
}
unset($transaction);

// Reverse back to show latest first
$transactions = array_reverse($transactions);

// Calculate statistics for the selected month
$stats = [
    'total_income' => 0,
    'total_expense' => 0,
    'net_balance' => 0,
    'transaction_count' => count($transactions)
];

foreach ($transactions as $transaction) {
    $amount_in = (float)($transaction['amount_in'] ?? 0);
    $amount_out = (float)($transaction['amount_out'] ?? 0);
    $stats['total_income'] += $amount_in;
    $stats['total_expense'] += $amount_out;
}

$stats['net_balance'] = $stats['total_income'] - $stats['total_expense'];

// Get overall statistics (all time)
$overall_stats = epic_safe_db_query(
    "SELECT 
        COALESCE(SUM(amount_in), 0) as total_income,
        COALESCE(SUM(amount_out), 0) as total_expense,
        COUNT(*) as total_transactions
     FROM " . TABLE_TRANSACTIONS,
    [],
    'selectOne'
) ?: ['total_income' => 0, 'total_expense' => 0, 'total_transactions' => 0];

$overall_stats['net_balance'] = $overall_stats['total_income'] - $overall_stats['total_expense'];

// Get monthly comparison data for charts
$monthly_data = epic_safe_db_query(
    "SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COALESCE(SUM(amount_in), 0) as income,
        COALESCE(SUM(amount_out), 0) as expense
     FROM " . TABLE_TRANSACTIONS . "
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY month
     ORDER BY month",
    [],
    'select'
);

// Prepare data untuk layout dengan struktur yang konsisten
$layout_data = [
    'page_title' => 'Finance Management - ' . epic_setting('site_name', 'EPIC Hub'),
    'header_title' => 'Finance Management',
    'current_page' => 'manage-finance',
    'body_class' => 'admin-body',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => epic_url('admin/manage')],
        ['text' => 'Finance']
    ],
    'page_actions' => [
        [
            'type' => 'button',
            'text' => 'Export Data',
            'icon' => 'download',
            'class' => 'btn-secondary',
            'onclick' => 'exportFinanceData()'
        ],
        [
            'type' => 'button',
            'text' => 'Add Transaction',
            'icon' => 'plus',
            'class' => 'btn-primary',
            'onclick' => 'showAddTransactionModal()'
        ]
    ],
    'content_file' => __DIR__ . '/content/finance-content.php',
    
    // Pass variables ke content dengan validasi
    'success' => $success,
    'error' => $error,
    'transactions' => $transactions ?? [],
    'stats' => $stats ?? [],
    'overall_stats' => $overall_stats ?? [],
    'monthly_data' => $monthly_data ?? [],
    'available_months' => $available_months ?? [],
    'selected_month' => $selected_month,
    'search_query' => $search_query,
    'user' => $user,
    
    // Additional CSS dan JS untuk halaman ini
    'additional_css' => [
        'themes/modern/admin/pages/finance-management.css'
    ],
    'additional_js' => [
        'themes/modern/admin/pages/finance-management.js'
    ]
];

// Render halaman dengan layout global yang konsisten
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>