<?php
/**
 * EPIS Monitoring Dashboard - Real-time System Health
 * Dashboard untuk monitoring kesehatan sistem EPIS secara real-time
 */

// Bootstrap sudah di-load melalui routing admin, cek konstanta
if (!defined('EPIC_INIT')) {
    require_once __DIR__ . '/../bootstrap.php';
}

// Security check - gunakan fungsi EPIC yang sudah ada
$user = epic_current_user();
if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
    if (function_exists('epic_route_403')) {
        epic_route_403();
    } else {
        http_response_code(403);
        echo '<h1>403 - Access Forbidden</h1>';
        exit;
    }
    return;
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_metrics') {
    header('Content-Type: application/json');
    echo json_encode(getSystemMetrics());
    exit;
}

function getSystemMetrics() {
    try {
        $db = db();
        
        // 1. EPIS Account Statistics
        $epis_stats = $db->selectOne("
            SELECT 
                COUNT(*) as total_accounts,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_accounts,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_accounts,
                COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended_accounts,
                SUM(current_epic_count) as total_epic_count,
                AVG(current_epic_count) as avg_epic_count,
                MAX(current_epic_count) as max_epic_count
            FROM epic_epis_accounts
        ");
        
        // 2. User Statistics
        $user_stats = $db->selectOne("
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                COUNT(CASE WHEN last_login_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as daily_active_users,
                COUNT(CASE WHEN last_login_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as weekly_active_users
            FROM epic_users
        ");
        
        // 3. Recent Activity (last 24 hours)
        $recent_activity = $db->selectOne("
            SELECT 
                COUNT(CASE WHEN action_type = 'registration' THEN 1 END) as new_registrations,
                COUNT(CASE WHEN action_type = 'activation' THEN 1 END) as new_activations,
                COUNT(CASE WHEN action_type = 'counter_update' THEN 1 END) as counter_updates
            FROM epic_monitoring_logs 
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        // 4. System Health Checks
        $health_checks = [];
        
        // Check for counter inconsistencies
        $counter_issues = $db->selectValue("
            SELECT COUNT(*) 
            FROM epic_epis_accounts 
            WHERE current_epic_count > max_epic_recruits OR current_epic_count < 0
        ");
        
        // Check for orphaned records
        $orphaned_users = $db->selectValue("
            SELECT COUNT(*) 
            FROM epic_users u 
            LEFT JOIN epic_epis_accounts e ON u.id = e.user_id 
            WHERE u.status = 'active' AND e.id IS NULL
        ");
        
        // Check for recent errors
        $recent_errors = $db->selectValue("
            SELECT COUNT(*) 
            FROM epic_monitoring_logs 
            WHERE level = 'error' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        
        $health_checks = [
            'counter_issues' => $counter_issues,
            'orphaned_users' => $orphaned_users,
            'recent_errors' => $recent_errors,
            'overall_status' => ($counter_issues == 0 && $orphaned_users == 0 && $recent_errors == 0) ? 'healthy' : 'warning'
        ];
        
        // 5. Performance Metrics
        $performance = [
            'avg_response_time' => rand(50, 200), // Placeholder - implement actual measurement
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'database_connections' => 1 // Placeholder
        ];
        
        // 6. Top Performing EPIS
        $top_epis = $db->select("
            SELECT epis_code, current_epic_count, max_epic_recruits,
                   ROUND((current_epic_count / max_epic_recruits) * 100, 2) as fill_percentage
            FROM epic_epis_accounts 
            WHERE status = 'active' AND max_epic_recruits > 0
            ORDER BY current_epic_count DESC 
            LIMIT 5
        ");
        
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'epis_stats' => $epis_stats,
            'user_stats' => $user_stats,
            'recent_activity' => $recent_activity,
            'health_checks' => $health_checks,
            'performance' => $performance,
            'top_epis' => $top_epis
        ];
        
    } catch (Exception $e) {
        return [
            'error' => 'Failed to fetch metrics: ' . $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Get initial metrics
$initial_metrics = getSystemMetrics();

// Prepare layout data
$layout_data = [
    'page_title' => 'EPIS Monitoring Dashboard - ' . epic_setting('site_name'),
    'header_title' => 'EPIS Monitoring Dashboard',
    'current_page' => 'epis-monitoring',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'EPIS Monitoring', 'url' => '#']
    ],
    'content_file' => __DIR__ . '/content/epis-monitoring-dashboard.php',
    'initial_metrics' => $initial_metrics,
    'additional_css' => [
        'themes/modern/admin/monitoring.css'
    ],
    'inline_css' => '
        /* Page Actions Bar */
        .page-actions-bar { 
            display: flex; 
            justify-content: flex-end; 
            margin-bottom: 2rem; 
            padding: 1rem 0; 
        }
        
        /* Monitoring Dashboard Grid */
        .monitoring-dashboard { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); 
            gap: 1.5rem; 
            margin-bottom: 2rem; 
        }
        
        /* Enhanced Card Styling with Gold-Black Theme */
        .monitoring-card { 
            background: linear-gradient(145deg, #0F0F14 0%, #15161C 100%);
            border: 2px solid #262732;
            border-radius: 16px; 
            padding: 1.75rem; 
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .monitoring-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #CFA84E 0%, #DDB966 50%, #E6CD8B 100%);
            opacity: 0.8;
        }
        
        .monitoring-card:hover {
            transform: translateY(-4px);
            border-color: #CFA84E;
            box-shadow: 0 8px 32px rgba(207, 168, 78, 0.15), 0 4px 20px rgba(0, 0, 0, 0.4);
        }
        
        /* Card Headers with Icons */
        .monitoring-card h3 { 
            margin: 0 0 1.25rem 0; 
            color: #E6CD8B; 
            font-size: 1.2rem; 
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .monitoring-card h3 i {
            width: 24px;
            height: 24px;
            color: #CFA84E;
            background: rgba(207, 168, 78, 0.1);
            border-radius: 8px;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Metrics Styling */
        .metric { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 0.75rem 0; 
            border-bottom: 1px solid rgba(38, 39, 50, 0.6);
            color: #D1D2D9;
        }
        
        .metric:last-child { 
            border-bottom: none; 
        }
        
        .metric-value { 
            font-weight: 700; 
            font-size: 1.1rem;
        }
        
        /* Status Colors - Enhanced */
        .status-healthy { 
            color: #34D399; 
            text-shadow: 0 0 8px rgba(52, 211, 153, 0.3);
        }
        
        .status-warning { 
            color: #FBBF24; 
            text-shadow: 0 0 8px rgba(251, 191, 36, 0.3);
        }
        
        .status-error { 
            color: #F87171; 
            text-shadow: 0 0 8px rgba(248, 113, 113, 0.3);
        }
        
        /* Refresh Indicator */
        .refresh-indicator { 
            display: none; 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            background: linear-gradient(135deg, #1C1D24, #23242C); 
            padding: 0.75rem 1.25rem; 
            border-radius: 12px; 
            color: #CFA84E; 
            border: 1px solid #262732;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }
        
        /* Last Updated */
        .last-updated { 
            text-align: center; 
            color: #9B9CA8; 
            font-size: 0.9rem; 
            margin-top: 1.5rem; 
            padding: 1rem;
            background: rgba(15, 15, 20, 0.5);
            border-radius: 8px;
            border: 1px solid rgba(38, 39, 50, 0.3);
        }
        
        /* Progress Bar - Gold Theme */
        .progress-bar { 
            width: 100%; 
            height: 10px; 
            background: #1D1D25; 
            border-radius: 6px; 
            overflow: hidden; 
            border: 1px solid #262732;
            margin-top: 0.5rem;
        }
        
        .progress-fill { 
            height: 100%; 
            background: linear-gradient(90deg, #CFA84E 0%, #DDB966 50%, #E6CD8B 100%); 
            transition: width 0.4s ease;
            box-shadow: 0 0 10px rgba(207, 168, 78, 0.4);
        }
        
        /* Alert Styling - Enhanced */
        .alert { 
            padding: 1.25rem; 
            border-radius: 12px; 
            margin-bottom: 1rem; 
            border: 2px solid;
            position: relative;
            overflow: hidden;
        }
        
        .alert::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }
        
        .alert-success { 
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(52, 211, 153, 0.05)); 
            border-color: #10B981; 
            color: #34D399; 
        }
        
        .alert-success::before {
            background: linear-gradient(90deg, #10B981, #34D399);
        }
        
        .alert-warning { 
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(251, 191, 36, 0.05)); 
            border-color: #F59E0B; 
            color: #FBBF24; 
        }
        
        .alert-warning::before {
            background: linear-gradient(90deg, #F59E0B, #FBBF24);
        }
        
        .alert-danger { 
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(248, 113, 113, 0.05)); 
            border-color: #EF4444; 
            color: #F87171; 
        }
        
        .alert-danger::before {
            background: linear-gradient(90deg, #EF4444, #F87171);
        }
        
        /* Button Styling */
        .btn-primary {
            background: linear-gradient(135deg, #CFA84E 0%, #DDB966 100%);
            color: #0B0B0F;
            border: 2px solid #CFA84E;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #DDB966 0%, #E6CD8B 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(207, 168, 78, 0.3);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .monitoring-dashboard {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .monitoring-card {
                padding: 1.25rem;
            }
            
            .page-actions-bar {
                justify-content: center;
            }
        }
    '
];

// Render admin page
epic_render_admin_page($layout_data['content_file'], $layout_data);