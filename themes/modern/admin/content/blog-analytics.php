<?php
/**
 * Blog Analytics Dashboard
 * Comprehensive analytics for blog performance and referral tracking
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}
?>

<!-- Analytics Header -->
<div class="analytics-header">
    <div class="header-content">
        <div class="header-text">
            <h2 class="analytics-title">Blog Analytics</h2>
            <p class="analytics-subtitle">Track your blog performance, referrals, and revenue generation</p>
        </div>
        <div class="header-actions">
            <div class="date-range-selector">
                <select id="dateRange" class="form-select" onchange="updateAnalytics()">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                </select>
            </div>
            <button class="btn btn-secondary" onclick="exportAnalytics()">
                <i data-feather="download" width="16" height="16"></i>
                Export Data
            </button>
        </div>
    </div>
</div>

<!-- Key Metrics Overview -->
<div class="metrics-overview">
    <div class="metric-card primary">
        <div class="metric-icon">
            <i data-feather="eye" class="metric-icon-svg"></i>
        </div>
        <div class="metric-content">
            <div class="metric-value" id="totalViews">0</div>
            <div class="metric-label">Total Blog Views</div>
            <div class="metric-change positive">
                <i data-feather="trending-up" width="14" height="14"></i>
                <span>+12.5% from last period</span>
            </div>
        </div>
    </div>
    
    <div class="metric-card success">
        <div class="metric-icon">
            <i data-feather="users" class="metric-icon-svg"></i>
        </div>
        <div class="metric-content">
            <div class="metric-value" id="totalReferrals">0</div>
            <div class="metric-label">Referrals from Blog</div>
            <div class="metric-change positive">
                <i data-feather="trending-up" width="14" height="14"></i>
                <span>+8.3% from last period</span>
            </div>
        </div>
    </div>
    
    <div class="metric-card warning">
        <div class="metric-icon">
            <i data-feather="dollar-sign" class="metric-icon-svg"></i>
        </div>
        <div class="metric-content">
            <div class="metric-value" id="totalRevenue">Rp 0</div>
            <div class="metric-label">Revenue from Blog</div>
            <div class="metric-change positive">
                <i data-feather="trending-up" width="14" height="14"></i>
                <span>+15.7% from last period</span>
            </div>
        </div>
    </div>
    
    <div class="metric-card info">
        <div class="metric-icon">
            <i data-feather="target" class="metric-icon-svg"></i>
        </div>
        <div class="metric-content">
            <div class="metric-value" id="conversionRate">0%</div>
            <div class="metric-label">Conversion Rate</div>
            <div class="metric-change positive">
                <i data-feather="trending-up" width="14" height="14"></i>
                <span>+2.1% from last period</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-section">
    <!-- Monthly Views Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">
                <i data-feather="bar-chart-2" class="chart-icon"></i>
                Monthly Blog Views
            </h3>
            <div class="chart-actions">
                <button class="chart-btn active" data-chart="views">Views</button>
                <button class="chart-btn" data-chart="referrals">Referrals</button>
                <button class="chart-btn" data-chart="revenue">Revenue</button>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="monthlyChart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <!-- Category Performance -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">
                <i data-feather="pie-chart" class="chart-icon"></i>
                Category Performance
            </h3>
        </div>
        <div class="chart-body">
            <div class="category-stats">
                <?php if (!empty($analytics['category_performance'])): ?>
                    <?php foreach ($analytics['category_performance'] as $category): ?>
                        <div class="category-item">
                            <div class="category-info">
                                <div class="category-name"><?= htmlspecialchars($category['name'] ?: 'Uncategorized') ?></div>
                                <div class="category-stats-detail">
                                    <span class="stat"><?= $category['article_count'] ?> articles</span>
                                    <span class="stat"><?= number_format($category['total_views']) ?> views</span>
                                </div>
                            </div>
                            <div class="category-progress">
                                <?php 
                                $maxViews = max(array_column($analytics['category_performance'], 'total_views'));
                                $percentage = $maxViews > 0 ? ($category['total_views'] / $maxViews) * 100 : 0;
                                ?>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <div class="progress-value"><?= number_format($percentage, 1) ?>%</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i data-feather="folder" width="32" height="32"></i>
                        <p>No category data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Referral Sources Analysis -->
<div class="referral-analysis">
    <div class="analysis-card">
        <div class="card-header">
            <h3 class="card-title">
                <i data-feather="link" class="card-icon"></i>
                Referral Sources from Blog
            </h3>
            <div class="card-actions">
                <button class="btn btn-sm btn-secondary" onclick="refreshReferralData()">
                    <i data-feather="refresh-cw" width="14" height="14"></i>
                    Refresh
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <?php if (!empty($analytics['referral_sources'])): ?>
                <div class="referral-table">
                    <div class="table-header">
                        <div class="header-cell">Source</div>
                        <div class="header-cell">Referrals</div>
                        <div class="header-cell">Sales</div>
                        <div class="header-cell">Revenue</div>
                        <div class="header-cell">Conversion</div>
                    </div>
                    
                    <?php foreach ($analytics['referral_sources'] as $source): ?>
                        <?php 
                        $conversionRate = $source['referrals'] > 0 ? ($source['sales'] / $source['referrals']) * 100 : 0;
                        ?>
                        <div class="table-row">
                            <div class="table-cell">
                                <div class="source-info">
                                    <div class="source-name"><?= htmlspecialchars($source['source']) ?></div>
                                    <div class="source-type">Blog Traffic</div>
                                </div>
                            </div>
                            <div class="table-cell">
                                <div class="metric-value-small"><?= number_format($source['referrals']) ?></div>
                            </div>
                            <div class="table-cell">
                                <div class="metric-value-small"><?= number_format($source['sales'] ?? 0) ?></div>
                            </div>
                            <div class="table-cell">
                                <div class="metric-value-small">Rp <?= number_format($source['sales'] ?? 0, 0, ',', '.') ?></div>
                            </div>
                            <div class="table-cell">
                                <div class="conversion-rate <?= $conversionRate >= 5 ? 'high' : ($conversionRate >= 2 ? 'medium' : 'low') ?>">
                                    <?= number_format($conversionRate, 1) ?>%
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i data-feather="link-2" width="48" height="48"></i>
                    <h4>No Referral Data</h4>
                    <p>Start promoting your blog articles to generate referral traffic and track conversions.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Top Performing Articles -->
<div class="top-articles-section">
    <div class="section-header">
        <h3 class="section-title">
            <i data-feather="trending-up" class="section-icon"></i>
            Top Performing Articles
        </h3>
        <div class="section-actions">
            <div class="sort-options">
                <select id="sortBy" class="form-select" onchange="sortArticles()">
                    <option value="views">Sort by Views</option>
                    <option value="referrals">Sort by Referrals</option>
                    <option value="revenue">Sort by Revenue</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="articles-grid" id="articlesGrid">
        <!-- Articles will be loaded here via JavaScript -->
    </div>
</div>

<!-- Insights and Recommendations -->
<div class="insights-section">
    <div class="insights-card">
        <div class="card-header">
            <h3 class="card-title">
                <i data-feather="lightbulb" class="card-icon"></i>
                Insights & Recommendations
            </h3>
        </div>
        
        <div class="card-body">
            <div class="insights-grid">
                <div class="insight-item positive">
                    <div class="insight-icon">
                        <i data-feather="trending-up" width="20" height="20"></i>
                    </div>
                    <div class="insight-content">
                        <div class="insight-title">Growing Engagement</div>
                        <div class="insight-description">Your blog views increased by 12.5% this month. Keep creating quality content!</div>
                    </div>
                </div>
                
                <div class="insight-item warning">
                    <div class="insight-icon">
                        <i data-feather="alert-triangle" width="20" height="20"></i>
                    </div>
                    <div class="insight-content">
                        <div class="insight-title">Optimize SEO</div>
                        <div class="insight-description">Some articles are missing SEO descriptions. Add them to improve search visibility.</div>
                    </div>
                </div>
                
                <div class="insight-item info">
                    <div class="insight-icon">
                        <i data-feather="target" width="20" height="20"></i>
                    </div>
                    <div class="insight-content">
                        <div class="insight-title">Focus on High-Converting Topics</div>
                        <div class="insight-description">Articles about "affiliate marketing" generate 3x more referrals than other topics.</div>
                    </div>
                </div>
                
                <div class="insight-item success">
                    <div class="insight-icon">
                        <i data-feather="share-2" width="20" height="20"></i>
                    </div>
                    <div class="insight-content">
                        <div class="insight-title">Social Media Boost</div>
                        <div class="insight-description">Articles shared on social media get 40% more views. Consider adding social sharing buttons.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Blog Analytics Styles */
.analytics-header {
    background: linear-gradient(135deg, var(--surface-1), var(--surface-2));
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-xl);
    padding: var(--spacing-8);
    margin-bottom: var(--spacing-8);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.analytics-title {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-2) 0;
}

.analytics-subtitle {
    color: var(--ink-300);
    margin: 0;
}

.header-actions {
    display: flex;
    gap: var(--spacing-4);
    align-items: center;
}

/* Metrics Overview */
.metrics-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-6);
    margin-bottom: var(--spacing-8);
}

.metric-card {
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-xl);
    padding: var(--spacing-6);
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    position: relative;
    overflow: hidden;
    transition: all var(--transition-fast);
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.metric-card.primary::before {
    background: var(--primary);
}

.metric-card.success::before {
    background: var(--success);
}

.metric-card.warning::before {
    background: var(--warning);
}

.metric-card.info::before {
    background: var(--info);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.metric-card.primary .metric-icon {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.2));
    color: var(--primary);
}

.metric-card.success .metric-icon {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.2));
    color: var(--success);
}

.metric-card.warning .metric-icon {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.2));
    color: var(--warning);
}

.metric-card.info .metric-icon {
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(6, 182, 212, 0.2));
    color: var(--info);
}

.metric-content {
    flex: 1;
}

.metric-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-1);
}

.metric-label {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
    margin-bottom: var(--spacing-2);
}

.metric-change {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

.metric-change.positive {
    color: var(--success);
}

.metric-change.negative {
    color: var(--danger);
}

/* Charts Section */
.charts-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--spacing-6);
    margin-bottom: var(--spacing-8);
}

.chart-card {
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-700);
    background: var(--surface-2);
}

.chart-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0;
}

.chart-icon {
    color: var(--gold-400);
}

.chart-actions {
    display: flex;
    gap: var(--spacing-2);
}

.chart-btn {
    padding: var(--spacing-2) var(--spacing-3);
    border: 1px solid var(--ink-600);
    background: none;
    color: var(--ink-300);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.chart-btn:hover {
    border-color: var(--gold-400);
    color: var(--gold-400);
}

.chart-btn.active {
    background: var(--gold-400);
    border-color: var(--gold-400);
    color: var(--ink-900);
}

.chart-body {
    padding: var(--spacing-6);
}

/* Category Stats */
.category-stats {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

.category-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    background: var(--surface-2);
    border-radius: var(--radius-lg);
    border: 1px solid var(--ink-700);
}

.category-info {
    flex: 1;
}

.category-name {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-medium);
    color: var(--ink-100);
    margin-bottom: var(--spacing-1);
}

.category-stats-detail {
    display: flex;
    gap: var(--spacing-3);
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

.category-progress {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    min-width: 120px;
}

.progress-bar {
    flex: 1;
    height: 6px;
    background: var(--surface-3);
    border-radius: var(--radius-sm);
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--gradient-gold);
    border-radius: var(--radius-sm);
    transition: width var(--transition-normal);
}

.progress-value {
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    color: var(--ink-300);
    min-width: 35px;
    text-align: right;
}

/* Referral Analysis */
.referral-analysis {
    margin-bottom: var(--spacing-8);
}

.analysis-card {
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-6);
    border-bottom: 1px solid var(--ink-700);
    background: var(--surface-2);
}

.card-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0;
}

.card-icon {
    color: var(--gold-400);
}

.card-body {
    padding: var(--spacing-6);
}

/* Referral Table */
.referral-table {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.table-header {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr 1fr;
    gap: var(--spacing-4);
    padding: var(--spacing-3) var(--spacing-4);
    background: var(--surface-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-300);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr 1fr;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    background: var(--surface-2);
    border-radius: var(--radius-md);
    border: 1px solid var(--ink-700);
    transition: all var(--transition-fast);
}

.table-row:hover {
    border-color: var(--gold-400);
    background: var(--surface-3);
}

.table-cell {
    display: flex;
    align-items: center;
}

.source-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-1);
}

.source-name {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-medium);
    color: var(--ink-100);
}

.source-type {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

.metric-value-small {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
}

.conversion-rate {
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
}

.conversion-rate.high {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.conversion-rate.medium {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.conversion-rate.low {
    background: rgba(107, 114, 128, 0.1);
    color: var(--ink-400);
}

/* Top Articles Section */
.top-articles-section {
    margin-bottom: var(--spacing-8);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-6);
}

.section-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0;
}

.section-icon {
    color: var(--gold-400);
}

.section-actions {
    display: flex;
    gap: var(--spacing-4);
    align-items: center;
}

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-6);
}

/* Insights Section */
.insights-section {
    margin-bottom: var(--spacing-8);
}

.insights-card {
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-4);
}

.insight-item {
    display: flex;
    gap: var(--spacing-3);
    padding: var(--spacing-4);
    background: var(--surface-2);
    border-radius: var(--radius-lg);
    border: 1px solid var(--ink-700);
    transition: all var(--transition-fast);
}

.insight-item:hover {
    border-color: var(--gold-400);
    transform: translateY(-2px);
}

.insight-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.insight-item.positive .insight-icon {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.insight-item.warning .insight-icon {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.insight-item.info .insight-icon {
    background: rgba(6, 182, 212, 0.1);
    color: var(--info);
}

.insight-item.success .insight-icon {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.insight-content {
    flex: 1;
}

.insight-title {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-1);
}

.insight-description {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
    line-height: 1.5;
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: var(--spacing-8);
    color: var(--ink-400);
}

.empty-state i {
    color: var(--ink-500);
    margin-bottom: var(--spacing-4);
}

.empty-state h4 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-200);
    margin: 0 0 var(--spacing-2) 0;
}

.empty-state p {
    margin: 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .header-content {
        flex-direction: column;
        gap: var(--spacing-4);
        text-align: center;
    }
    
    .table-header,
    .table-row {
        grid-template-columns: 1fr;
        gap: var(--spacing-2);
    }
    
    .table-cell {
        justify-content: space-between;
    }
}

@media (max-width: 768px) {
    .metrics-overview {
        grid-template-columns: 1fr;
    }
    
    .insights-grid {
        grid-template-columns: 1fr;
    }
    
    .articles-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Blog Analytics JavaScript
class BlogAnalytics {
    constructor() {
        this.currentChart = 'views';
        this.dateRange = 30;
        this.init();
    }
    
    init() {
        this.loadAnalyticsData();
        this.setupEventListeners();
        this.initializeChart();
    }
    
    setupEventListeners() {
        // Chart type buttons
        document.querySelectorAll('.chart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.currentChart = e.target.dataset.chart;
                this.updateChart();
            });
        });
    }
    
    loadAnalyticsData() {
        // Simulate loading analytics data
        const data = {
            totalViews: 15420,
            totalReferrals: 342,
            totalRevenue: 8750000,
            conversionRate: 2.2
        };
        
        // Update metrics
        document.getElementById('totalViews').textContent = data.totalViews.toLocaleString();
        document.getElementById('totalReferrals').textContent = data.totalReferrals.toLocaleString();
        document.getElementById('totalRevenue').textContent = 'Rp ' + data.totalRevenue.toLocaleString('id-ID');
        document.getElementById('conversionRate').textContent = data.conversionRate + '%';
    }
    
    initializeChart() {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        
        // Sample data for the last 12 months
        const labels = [];
        const data = [];
        
        for (let i = 11; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            labels.push(date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }));
            data.push(Math.floor(Math.random() * 2000) + 500);
        }
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Blog Views',
                    data: data,
                    borderColor: '#fbbf24',
                    backgroundColor: 'rgba(251, 191, 36, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#9ca3af'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#9ca3af'
                        }
                    }
                }
            }
        });
    }
    
    updateChart() {
        // Update chart based on selected type
        let label, color, bgColor;
        
        switch (this.currentChart) {
            case 'views':
                label = 'Blog Views';
                color = '#fbbf24';
                bgColor = 'rgba(251, 191, 36, 0.1)';
                break;
            case 'referrals':
                label = 'Referrals';
                color = '#10b981';
                bgColor = 'rgba(16, 185, 129, 0.1)';
                break;
            case 'revenue':
                label = 'Revenue (Rp)';
                color = '#3b82f6';
                bgColor = 'rgba(59, 130, 246, 0.1)';
                break;
        }
        
        this.chart.data.datasets[0].label = label;
        this.chart.data.datasets[0].borderColor = color;
        this.chart.data.datasets[0].backgroundColor = bgColor;
        this.chart.update();
    }
}

// Global functions
function updateAnalytics() {
    const dateRange = document.getElementById('dateRange').value;
    console.log('Updating analytics for', dateRange, 'days');
    // Implement date range filtering
}

function exportAnalytics() {
    console.log('Exporting analytics data...');
    // Implement export functionality
}

function refreshReferralData() {
    console.log('Refreshing referral data...');
    // Implement refresh functionality
}

function sortArticles() {
    const sortBy = document.getElementById('sortBy').value;
    console.log('Sorting articles by', sortBy);
    // Implement sorting functionality
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is available
    if (typeof Chart !== 'undefined') {
        window.blogAnalytics = new BlogAnalytics();
    } else {
        console.warn('Chart.js not loaded. Charts will not be displayed.');
    }
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>