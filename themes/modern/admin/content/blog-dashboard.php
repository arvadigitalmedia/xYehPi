<?php
/**
 * Blog Dashboard Content
 * Main blog management interface with statistics and article listing
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Handle success/error messages
$success_message = $_GET['success'] ?? '';
$error_message = $_GET['error'] ?? '';
?>

<!-- Success/Error Messages -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible">
        <i data-feather="check-circle" class="alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">Success!</div>
            <div class="alert-message"><?= htmlspecialchars($success_message) ?></div>
        </div>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
            <i data-feather="x" width="16" height="16"></i>
        </button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-error alert-dismissible">
        <i data-feather="alert-circle" class="alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">Error!</div>
            <div class="alert-message"><?= htmlspecialchars($error_message) ?></div>
        </div>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
            <i data-feather="x" width="16" height="16"></i>
        </button>
    </div>
<?php endif; ?>

<!-- Blog Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i data-feather="file-text" class="stat-icon-svg"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_articles']) ?></div>
            <div class="stat-label">Total Articles</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon published">
            <i data-feather="eye" class="stat-icon-svg"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['published_articles']) ?></div>
            <div class="stat-label">Published</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon draft">
            <i data-feather="edit" class="stat-icon-svg"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['draft_articles']) ?></div>
            <div class="stat-label">Drafts</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon views">
            <i data-feather="trending-up" class="stat-icon-svg"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_views']) ?></div>
            <div class="stat-label">Total Views</div>
        </div>
    </div>
    
    <!-- Published Articles Table -->
    <div class="content-card full-width">
        <div class="card-header">
            <h3 class="card-title">
                <i data-feather="list" class="card-icon"></i>
                Published Articles
            </h3>
            <div class="card-actions">
                <a href="<?= epic_url('admin/blog?action=add') ?>" class="btn btn-sm btn-primary">
                    <i data-feather="plus" width="14" height="14"></i>
                    Add New
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <?php if (empty($published_articles)): ?>
                <div class="empty-state">
                    <i data-feather="file-text" width="48" height="48"></i>
                    <h4>No Published Articles</h4>
                    <p>Start creating and publishing articles to see them here.</p>
                    <a href="<?= epic_url('admin/blog?action=add') ?>" class="btn btn-primary">
                        Create First Article
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Judul Blog</th>
                                <th>Tanggal Publikasi</th>
                                <th>Nama Penulis</th>
                                <th>Status Publikasi</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($published_articles as $article): ?>
                                <tr>
                                    <td>
                                        <div class="article-title-cell">
                                            <h5 class="article-title">
                                                <a href="<?= epic_url('admin/blog?action=edit&id=' . $article['id']) ?>">
                                                    <?= htmlspecialchars($article['title']) ?>
                                                </a>
                                            </h5>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="date-text">
                                            <?= date('d M Y, H:i', strtotime($article['published_at'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="author-name">
                                            <?= htmlspecialchars($article['author_name'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-published">
                                            <i data-feather="check-circle" width="12" height="12"></i>
                                            Published
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?= epic_url('admin/blog?action=edit&id=' . $article['id']) ?>" 
                                               class="btn btn-sm btn-secondary" title="Edit">
                                                <i data-feather="edit" width="14" height="14"></i>
                                            </a>
                                            <a href="<?= epic_url('blog/' . $article['id']) ?>" 
                                               class="btn btn-sm btn-secondary" title="View" target="_blank">
                                                <i data-feather="external-link" width="14" height="14"></i>
                                            </a>
                                            <button onclick="deleteArticle(<?= $article['id'] ?>, '<?= htmlspecialchars($article['title']) ?>')" 
                                                    class="btn btn-sm btn-danger" title="Delete">
                                                <i data-feather="trash-2" width="14" height="14"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Referral Tracking Statistics -->
<div class="referral-stats-grid">
    <div class="referral-stat-card">
        <div class="referral-stat-header">
            <h3 class="referral-stat-title">
                <i data-feather="users" width="20" height="20"></i>
                Blog Referrals
            </h3>
        </div>
        <div class="referral-stat-content">
            <div class="referral-stat-value"><?= number_format($stats['total_referrals_from_blog']) ?></div>
            <div class="referral-stat-label">New members from blog articles</div>
        </div>
    </div>
    
    <div class="referral-stat-card">
        <div class="referral-stat-header">
            <h3 class="referral-stat-title">
                <i data-feather="dollar-sign" width="20" height="20"></i>
                Blog Sales
            </h3>
        </div>
        <div class="referral-stat-content">
            <div class="referral-stat-value">Rp <?= number_format($stats['total_sales_from_blog'], 0, ',', '.') ?></div>
            <div class="referral-stat-label">Revenue generated from blog traffic</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="<?= epic_url('admin/blog?action=add') ?>" class="btn btn-primary">
        <i data-feather="plus" width="16" height="16"></i>
        Add New Article
    </a>
    <a href="<?= epic_url('admin/blog?action=analytics') ?>" class="btn btn-secondary">
        <i data-feather="bar-chart-2" width="16" height="16"></i>
        View Analytics
    </a>
    <a href="<?= epic_url('admin/categories') ?>" class="btn btn-secondary">
        <i data-feather="folder" width="16" height="16"></i>
        Manage Categories
    </a>
</div>

<!-- Articles Management -->
<div class="content-grid">
    <!-- Recent Articles -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i data-feather="clock" class="card-icon"></i>
                Recent Articles
            </h3>
            <div class="card-actions">
                <a href="<?= epic_url('admin/articles') ?>" class="btn btn-sm btn-secondary">
                    View All
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <?php if (empty($recent_articles)): ?>
                <div class="empty-state">
                    <i data-feather="file-text" width="48" height="48"></i>
                    <h4>No Articles Yet</h4>
                    <p>Start creating your first blog article to engage your audience.</p>
                    <a href="<?= epic_url('admin/blog?action=add') ?>" class="btn btn-primary">
                        Create First Article
                    </a>
                </div>
            <?php else: ?>
                <div class="articles-list">
                    <?php foreach ($recent_articles as $article): ?>
                        <div class="article-item">
                            <div class="article-meta">
                                <div class="article-status">
                                    <span class="status-badge status-<?= $article['status'] ?>">
                                        <?= ucfirst($article['status']) ?>
                                    </span>
                                </div>
                                <div class="article-date">
                                    <?= date('M j, Y', strtotime($article['created_at'])) ?>
                                </div>
                            </div>
                            
                            <div class="article-content">
                                <h4 class="article-title">
                                    <a href="<?= epic_url('admin/blog?action=edit&id=' . $article['id']) ?>">
                                        <?= htmlspecialchars($article['title']) ?>
                                    </a>
                                </h4>
                                <div class="article-info">
                                    <span class="article-author">
                                        <i data-feather="user" width="14" height="14"></i>
                                        <?= htmlspecialchars($article['author_name']) ?>
                                    </span>
                                    <?php if ($article['category_name']): ?>
                                        <span class="article-category">
                                            <i data-feather="folder" width="14" height="14"></i>
                                            <?= htmlspecialchars($article['category_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="article-views">
                                        <i data-feather="eye" width="14" height="14"></i>
                                        <?= number_format($article['view_count']) ?> views
                                    </span>
                                </div>
                            </div>
                            
                            <div class="article-actions">
                                <a href="<?= epic_url('admin/blog?action=edit&id=' . $article['id']) ?>" class="btn btn-sm btn-secondary">
                                    <i data-feather="edit" width="14" height="14"></i>
                                    Edit
                                </a>
                                <?php if ($article['status'] === 'published'): ?>
                                    <a href="<?= epic_url('blog/' . $article['slug']) ?>" class="btn btn-sm btn-secondary" target="_blank">
                                        <i data-feather="external-link" width="14" height="14"></i>
                                        View
                                    </a>
                                <?php endif; ?>
                                <button onclick="deleteArticle(<?= $article['id'] ?>, '<?= htmlspecialchars($article['title']) ?>')" class="btn btn-sm btn-danger">
                                    <i data-feather="trash-2" width="14" height="14"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Top Performing Articles -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i data-feather="trending-up" class="card-icon"></i>
                Top Performing
            </h3>
        </div>
        
        <div class="card-body">
            <?php if (empty($top_articles)): ?>
                <div class="empty-state-small">
                    <i data-feather="bar-chart-2" width="32" height="32"></i>
                    <p>No published articles yet</p>
                </div>
            <?php else: ?>
                <div class="top-articles-list">
                    <?php foreach ($top_articles as $index => $article): ?>
                        <div class="top-article-item">
                            <div class="article-rank">
                                #<?= $index + 1 ?>
                            </div>
                            <div class="article-info">
                                <h5 class="article-title">
                                    <a href="<?= epic_url('admin/blog?action=edit&id=' . $article['id']) ?>">
                                        <?= htmlspecialchars($article['title']) ?>
                                    </a>
                                </h5>
                                <div class="article-stats">
                                    <span class="views">
                                        <i data-feather="eye" width="12" height="12"></i>
                                        <?= number_format($article['view_count']) ?>
                                    </span>
                                    <?php if ($article['category_name']): ?>
                                        <span class="category">
                                            <i data-feather="folder" width="12" height="12"></i>
                                            <?= htmlspecialchars($article['category_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Categories Overview -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i data-feather="folder" class="card-icon"></i>
                Categories
            </h3>
            <div class="card-actions">
                <a href="<?= epic_url('admin/categories') ?>" class="btn btn-sm btn-secondary">
                    Manage
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <?php if (empty($categories)): ?>
                <div class="empty-state-small">
                    <i data-feather="folder-plus" width="32" height="32"></i>
                    <p>No categories created</p>
                    <a href="<?= epic_url('admin/categories?action=add') ?>" class="btn btn-sm btn-primary">
                        Add Category
                    </a>
                </div>
            <?php else: ?>
                <div class="categories-list">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-item">
                            <div class="category-info">
                                <h5 class="category-name">
                                    <?= htmlspecialchars($category['name']) ?>
                                </h5>
                                <div class="category-count">
                                    <?= $category['article_count'] ?> articles
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Blog Dashboard Styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-6);
    margin-bottom: var(--spacing-8);
}

.stat-card {
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-xl);
    padding: var(--spacing-6);
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    transition: all var(--transition-fast);
}

.stat-card:hover {
    border-color: var(--gold-400);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-gold-subtle);
    color: var(--gold-400);
}

.stat-icon.published {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.2));
    color: var(--success);
}

.stat-icon.draft {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.2));
    color: var(--warning);
}

.stat-icon.views {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.2));
    color: var(--primary);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--ink-100);
    margin-bottom: var(--spacing-1);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
    font-weight: var(--font-weight-medium);
}

/* Referral Statistics */
.referral-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-6);
    margin-bottom: var(--spacing-8);
}

.referral-stat-card {
    background: linear-gradient(135deg, var(--surface-1), var(--surface-2));
    border: 1px solid var(--gold-600);
    border-radius: var(--radius-xl);
    padding: var(--spacing-6);
    position: relative;
    overflow: hidden;
}

.referral-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-gold);
}

.referral-stat-header {
    margin-bottom: var(--spacing-4);
}

.referral-stat-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-100);
    margin: 0;
}

.referral-stat-value {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-bold);
    color: var(--gold-400);
    margin-bottom: var(--spacing-2);
}

.referral-stat-label {
    font-size: var(--font-size-sm);
    color: var(--ink-300);
}

/* Quick Actions */
.quick-actions {
    display: flex;
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-8);
    flex-wrap: wrap;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: var(--spacing-6);
}

.content-card.full-width {
    grid-column: 1 / -1;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
    margin: -1px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--font-size-sm);
}

.data-table th,
.data-table td {
    padding: var(--spacing-3) var(--spacing-4);
    text-align: left;
    border-bottom: 1px solid var(--ink-700);
}

.data-table th {
    background: var(--surface-2);
    font-weight: var(--font-weight-semibold);
    color: var(--ink-200);
    font-size: var(--font-size-xs);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.data-table td {
    color: var(--ink-300);
}

.data-table tbody tr:hover {
    background: var(--surface-2);
}

.article-title-cell .article-title {
    margin: 0;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}

.article-title-cell .article-title a {
    color: var(--ink-100);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.article-title-cell .article-title a:hover {
    color: var(--gold-400);
}

.date-text {
    color: var(--ink-400);
    font-size: var(--font-size-xs);
}

.author-name {
    color: var(--ink-300);
    font-weight: var(--font-weight-medium);
}

.action-buttons {
     display: flex;
     gap: var(--spacing-2);
     align-items: center;
 }
 
 .status-badge {
     display: inline-flex;
     align-items: center;
     gap: var(--spacing-1);
     padding: var(--spacing-1) var(--spacing-2);
     border-radius: var(--radius-md);
     font-size: var(--font-size-xs);
     font-weight: var(--font-weight-medium);
     text-transform: uppercase;
     letter-spacing: 0.05em;
 }
 
 .status-badge.status-published {
     background: rgba(34, 197, 94, 0.1);
     color: #22c55e;
     border: 1px solid rgba(34, 197, 94, 0.2);
 }

.content-card {
    background: var(--surface-1);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
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

/* Articles List */
.articles-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

.article-item {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-4);
    padding: var(--spacing-4);
    border: 1px solid var(--ink-700);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
}

.article-item:hover {
    border-color: var(--gold-400);
    background: var(--surface-2);
}

.article-meta {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
    min-width: 100px;
}

.status-badge {
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-published {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
    border: 1px solid var(--success);
}

.status-draft {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning);
    border: 1px solid var(--warning);
}

.status-private {
    background: rgba(107, 114, 128, 0.1);
    color: var(--ink-300);
    border: 1px solid var(--ink-500);
}

.article-date {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

.article-content {
    flex: 1;
}

.article-title {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    margin: 0 0 var(--spacing-2) 0;
}

.article-title a {
    color: var(--ink-100);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.article-title a:hover {
    color: var(--gold-400);
}

.article-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

.article-info span {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
}

.article-actions {
    display: flex;
    gap: var(--spacing-2);
    flex-shrink: 0;
}

/* Top Articles */
.top-articles-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.top-article-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-3);
    border-radius: var(--radius-md);
    transition: background var(--transition-fast);
}

.top-article-item:hover {
    background: var(--surface-2);
}

.article-rank {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-full);
    background: var(--gradient-gold-subtle);
    color: var(--gold-400);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-sm);
    flex-shrink: 0;
}

.top-article-item .article-info {
    flex: 1;
}

.top-article-item .article-title {
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-1);
}

.top-article-item .article-stats {
    display: flex;
    gap: var(--spacing-2);
    font-size: var(--font-size-xs);
    color: var(--ink-400);
}

.top-article-item .article-stats span {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
}

/* Categories */
.categories-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.category-item {
    padding: var(--spacing-3);
    border-radius: var(--radius-md);
    transition: background var(--transition-fast);
}

.category-item:hover {
    background: var(--surface-2);
}

.category-name {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--ink-100);
    margin: 0 0 var(--spacing-1) 0;
}

.category-count {
    font-size: var(--font-size-xs);
    color: var(--ink-400);
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
    margin: 0 0 var(--spacing-4) 0;
}

.empty-state-small {
    text-align: center;
    padding: var(--spacing-6);
    color: var(--ink-400);
}

.empty-state-small i {
    color: var(--ink-500);
    margin-bottom: var(--spacing-2);
}

.empty-state-small p {
    margin: 0;
    font-size: var(--font-size-sm);
}

/* Responsive */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .referral-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        flex-direction: column;
    }
    
    .article-item {
        flex-direction: column;
        align-items: stretch;
    }
    
    .article-actions {
        justify-content: flex-end;
    }
}
</style>

<script>
function deleteArticle(articleId, articleTitle) {
    if (confirm(`Are you sure you want to delete "${articleTitle}"? This action cannot be undone.`)) {
        window.location.href = `<?= epic_url('admin/blog') ?>?action=delete&id=${articleId}`;
    }
}

// Initialize feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>