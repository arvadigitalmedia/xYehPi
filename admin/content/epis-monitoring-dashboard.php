<?php
/**
 * EPIS Monitoring Dashboard Content
 * Content file untuk dashboard monitoring EPIS
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Get initial metrics from layout data
$initial_metrics = $initial_metrics ?? [];
?>

<!-- Page Actions -->
<div class="page-actions-bar">
    <button class="btn btn-primary" onclick="refreshMetrics()">
        <i data-feather="refresh-cw"></i>
        Refresh Data
    </button>
</div>

<!-- Refresh Indicator -->
<div class="refresh-indicator" id="refreshIndicator">
    <i data-feather="loader"></i> Updating...
</div>

<!-- Dashboard Content -->
<div class="monitoring-dashboard" id="dashboard">
    <!-- Content will be populated by JavaScript -->
</div>

<!-- Last Updated -->
<div class="last-updated" id="lastUpdated"></div>

<script>
    let metricsData = <?php echo json_encode($initial_metrics); ?>;
    
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
    
    function formatBytes(bytes) {
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        if (bytes === 0) return '0 Bytes';
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    function getStatusClass(status) {
        switch(status) {
            case 'healthy': return 'status-healthy';
            case 'warning': return 'status-warning';
            case 'error': return 'status-error';
            default: return '';
        }
    }
    
    function renderDashboard(data) {
        if (data.error) {
            document.getElementById('dashboard').innerHTML = `
                <div class="monitoring-card" style="grid-column: 1 / -1;">
                    <div class="alert alert-danger">
                        <i data-feather="alert-triangle"></i>
                        <strong>Error:</strong> ${data.error}
                    </div>
                </div>
            `;
            return;
        }
        
        const dashboard = document.getElementById('dashboard');
        dashboard.innerHTML = `
            <!-- EPIS Statistics -->
            <div class="monitoring-card">
                <h3><i data-feather="database"></i> EPIS Account Statistics</h3>
                <div class="metric">
                    <span><i data-feather="layers" style="width: 16px; height: 16px; margin-right: 8px;"></i>Total Accounts:</span>
                    <span class="metric-value">${formatNumber(data.epis_stats.total_accounts)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="check-circle" style="width: 16px; height: 16px; margin-right: 8px; color: #34D399;"></i>Active:</span>
                    <span class="metric-value status-healthy">${formatNumber(data.epis_stats.active_accounts)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="clock" style="width: 16px; height: 16px; margin-right: 8px; color: #FBBF24;"></i>Pending:</span>
                    <span class="metric-value status-warning">${formatNumber(data.epis_stats.pending_accounts)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="pause-circle" style="width: 16px; height: 16px; margin-right: 8px; color: #F87171;"></i>Suspended:</span>
                    <span class="metric-value status-error">${formatNumber(data.epis_stats.suspended_accounts)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="hash" style="width: 16px; height: 16px; margin-right: 8px;"></i>Total EPIC Count:</span>
                    <span class="metric-value">${formatNumber(data.epis_stats.total_epic_count)}</span>
                </div>
            </div>
            
            <!-- User Statistics -->
            <div class="monitoring-card">
                <h3><i data-feather="users"></i> User Statistics</h3>
                <div class="metric">
                    <span><i data-feather="user" style="width: 16px; height: 16px; margin-right: 8px;"></i>Total Users:</span>
                    <span class="metric-value">${formatNumber(data.user_stats.total_users)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="user-check" style="width: 16px; height: 16px; margin-right: 8px; color: #34D399;"></i>Active Users:</span>
                    <span class="metric-value status-healthy">${formatNumber(data.user_stats.active_users)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="sun" style="width: 16px; height: 16px; margin-right: 8px;"></i>Daily Active:</span>
                    <span class="metric-value">${formatNumber(data.user_stats.daily_active_users)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="calendar" style="width: 16px; height: 16px; margin-right: 8px;"></i>Weekly Active:</span>
                    <span class="metric-value">${formatNumber(data.user_stats.weekly_active_users)}</span>
                </div>
            </div>
            
            <!-- System Health -->
            <div class="monitoring-card">
                <h3><i data-feather="shield"></i> System Health</h3>
                <div class="alert ${data.health_checks.overall_status === 'healthy' ? 'alert-success' : 'alert-warning'}">
                    <i data-feather="${data.health_checks.overall_status === 'healthy' ? 'check-circle' : 'alert-triangle'}" style="width: 18px; height: 18px; margin-right: 8px;"></i>
                    <strong>Status:</strong> ${data.health_checks.overall_status.toUpperCase()}
                </div>
                <div class="metric">
                    <span><i data-feather="alert-circle" style="width: 16px; height: 16px; margin-right: 8px;"></i>Counter Issues:</span>
                    <span class="metric-value ${data.health_checks.counter_issues > 0 ? 'status-error' : 'status-healthy'}">${data.health_checks.counter_issues}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="user-x" style="width: 16px; height: 16px; margin-right: 8px;"></i>Orphaned Users:</span>
                    <span class="metric-value ${data.health_checks.orphaned_users > 0 ? 'status-warning' : 'status-healthy'}">${data.health_checks.orphaned_users}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="x-circle" style="width: 16px; height: 16px; margin-right: 8px;"></i>Recent Errors:</span>
                    <span class="metric-value ${data.health_checks.recent_errors > 0 ? 'status-error' : 'status-healthy'}">${data.health_checks.recent_errors}</span>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="monitoring-card">
                <h3><i data-feather="activity"></i> Recent Activity (24h)</h3>
                <div class="metric">
                    <span><i data-feather="user-plus" style="width: 16px; height: 16px; margin-right: 8px; color: #34D399;"></i>New Registrations:</span>
                    <span class="metric-value status-healthy">${formatNumber(data.recent_activity.new_registrations)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="zap" style="width: 16px; height: 16px; margin-right: 8px; color: #34D399;"></i>New Activations:</span>
                    <span class="metric-value status-healthy">${formatNumber(data.recent_activity.new_activations)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="trending-up" style="width: 16px; height: 16px; margin-right: 8px;"></i>Counter Updates:</span>
                    <span class="metric-value">${formatNumber(data.recent_activity.counter_updates)}</span>
                </div>
            </div>
            
            <!-- Performance -->
            <div class="monitoring-card">
                <h3><i data-feather="cpu"></i> Performance Metrics</h3>
                <div class="metric">
                    <span><i data-feather="hard-drive" style="width: 16px; height: 16px; margin-right: 8px;"></i>Memory Usage:</span>
                    <span class="metric-value">${formatBytes(data.performance.memory_usage)}</span>
                </div>
                <div class="metric">
                    <span><i data-feather="bar-chart" style="width: 16px; height: 16px; margin-right: 8px;"></i>Peak Memory:</span>
                    <span class="metric-value">${formatBytes(data.performance.peak_memory)}</span>
                </div>
            </div>
            
            <!-- Top EPIS -->
            <div class="monitoring-card">
                <h3><i data-feather="award"></i> Top Performing EPIS</h3>
                ${data.top_epis.map(epis => `
                    <div class="metric">
                        <div>
                            <i data-feather="star" style="width: 16px; height: 16px; margin-right: 8px; color: #CFA84E;"></i>
                            <strong>${epis.epis_code}</strong><br>
                            <small style="color: #9B9CA8; margin-left: 24px;">${epis.current_epic_count}/${epis.max_epic_recruits} (${epis.fill_percentage}%)</small>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${Math.min(epis.fill_percentage, 100)}%"></div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        
        document.getElementById('lastUpdated').innerHTML = `
            <i data-feather="clock"></i> Last updated: ${data.timestamp}
        `;
        
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
    
    function refreshMetrics() {
        document.getElementById('refreshIndicator').style.display = 'block';
        
        fetch('?action=get_metrics')
            .then(response => response.json())
            .then(data => {
                metricsData = data;
                renderDashboard(data);
                document.getElementById('refreshIndicator').style.display = 'none';
            })
            .catch(error => {
                console.error('Error fetching metrics:', error);
                document.getElementById('refreshIndicator').style.display = 'none';
                // Show error notification
                const dashboard = document.getElementById('dashboard');
                dashboard.innerHTML = `
                    <div class="monitoring-card" style="grid-column: 1 / -1;">
                        <div class="alert alert-danger">
                            <i data-feather="alert-triangle"></i>
                            <strong>Connection Error:</strong> Unable to fetch latest metrics. Please try again.
                        </div>
                    </div>
                `;
                // Re-initialize feather icons
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            });
    }
    
    // Initial render
    renderDashboard(metricsData);
    
    // Auto-refresh every 30 seconds
    setInterval(refreshMetrics, 30000);
    
    // Initialize feather icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>