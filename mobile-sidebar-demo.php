<?php
/**
 * Demo Mobile Sidebar - Testing Responsive Admin Menu
 * Halaman untuk testing mobile sidebar functionality
 */

// Set session admin untuk testing
session_start();
$_SESSION['epic_user'] = [
    'id' => 8,
    'name' => 'Bustanul Arifin',
    'email' => 'arifin@emasperak.id',
    'role' => 'admin',
    'status' => 'epic'
];

// Include layout admin
define('EPIC_INIT', true);
define('EPIC_ROOT', __DIR__);

function epic_url($path = '') {
    return 'http://localhost/test-bisnisemasperak/' . ltrim($path, '/');
}

function epic_current_user() {
    return $_SESSION['epic_user'] ?? null;
}

function epic_setting($key) {
    $settings = [
        'site_name' => 'EPIC Hub',
        'site_logo' => 'logo.png'
    ];
    return $settings[$key] ?? null;
}

// Layout data
$data = [
    'page_title' => 'Mobile Sidebar Demo - EPIC Hub Admin',
    'current_page' => 'mobile-demo',
    'content' => '
        <div class="demo-container">
            <div class="demo-card">
                <div class="demo-header">
                    <h2>🔧 Mobile Sidebar Demo</h2>
                    <p>Testing responsive admin menu functionality</p>
                </div>
                
                <div class="demo-content">
                    <div class="demo-section">
                        <h3>📱 Cara Testing Mobile Sidebar:</h3>
                        <ol>
                            <li><strong>Buka Developer Tools</strong> (F12)</li>
                            <li><strong>Toggle Device Toolbar</strong> (Ctrl+Shift+M)</li>
                            <li><strong>Pilih device mobile</strong> (iPhone, Android, dll)</li>
                            <li><strong>Refresh halaman</strong> untuk melihat layout mobile</li>
                            <li><strong>Klik hamburger menu</strong> (☰) di kiri atas</li>
                            <li><strong>Sidebar akan slide dari kiri</strong></li>
                            <li><strong>Klik di luar sidebar</strong> untuk menutup</li>
                        </ol>
                    </div>
                    
                    <div class="demo-section">
                        <h3>✅ Fitur Mobile Sidebar:</h3>
                        <ul>
                            <li>✓ Hamburger menu button di header</li>
                            <li>✓ Sidebar slide animation</li>
                            <li>✓ Backdrop overlay</li>
                            <li>✓ Auto-close saat resize ke desktop</li>
                            <li>✓ Touch-friendly navigation</li>
                        </ul>
                    </div>
                    
                    <div class="demo-section">
                        <h3>🎯 Halaman yang Sudah Diperbaiki:</h3>
                        <div class="demo-links">
                            <a href="admin/" class="demo-link">📊 Admin Dashboard</a>
                            <a href="admin/edit-profile" class="demo-link">👤 Edit Profile</a>
                            <a href="admin/blog" class="demo-link">📝 Blog Management</a>
                            <a href="admin/member.php" class="demo-link">👥 Member Management</a>
                            <a href="admin/product.php" class="demo-link">🛍️ Product Management</a>
                        </div>
                    </div>
                    
                    <div class="demo-section">
                        <h3>📋 Status Implementation:</h3>
                        <div class="status-grid">
                            <div class="status-item completed">
                                <span class="status-icon">✅</span>
                                <span>CSS Responsive</span>
                            </div>
                            <div class="status-item completed">
                                <span class="status-icon">✅</span>
                                <span>Hamburger Menu</span>
                            </div>
                            <div class="status-item completed">
                                <span class="status-icon">✅</span>
                                <span>JavaScript Toggle</span>
                            </div>
                            <div class="status-item completed">
                                <span class="status-icon">✅</span>
                                <span>Mobile Testing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .demo-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .demo-card {
            background: var(--surface-2);
            border: 1px solid var(--ink-600);
            border-radius: var(--radius-xl);
            overflow: hidden;
        }
        
        .demo-header {
            background: var(--gradient-gold-subtle);
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid var(--ink-600);
        }
        
        .demo-header h2 {
            color: var(--gold-400);
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        
        .demo-header p {
            color: var(--ink-300);
            margin: 0;
        }
        
        .demo-content {
            padding: 30px;
        }
        
        .demo-section {
            margin-bottom: 30px;
        }
        
        .demo-section h3 {
            color: var(--gold-400);
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        
        .demo-section ol, .demo-section ul {
            color: var(--ink-200);
            line-height: 1.6;
            padding-left: 20px;
        }
        
        .demo-section li {
            margin-bottom: 8px;
        }
        
        .demo-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .demo-link {
            display: block;
            padding: 12px 16px;
            background: var(--surface-3);
            border: 1px solid var(--ink-600);
            border-radius: var(--radius-md);
            color: var(--ink-200);
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        
        .demo-link:hover {
            background: var(--surface-4);
            border-color: var(--gold-400);
            color: var(--gold-400);
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: var(--surface-3);
            border-radius: var(--radius-md);
            border: 1px solid var(--ink-600);
        }
        
        .status-item.completed {
            border-color: var(--success);
            background: rgba(16, 185, 129, 0.1);
        }
        
        .status-icon {
            font-size: 16px;
        }
        
        .status-item span:last-child {
            color: var(--ink-200);
            font-size: 14px;
        }
        
        .status-item.completed span:last-child {
            color: var(--success-light);
        }
        
        @media (max-width: 768px) {
            .demo-container {
                padding: 10px;
            }
            
            .demo-header {
                padding: 20px;
            }
            
            .demo-content {
                padding: 20px;
            }
            
            .demo-links {
                grid-template-columns: 1fr;
            }
            
            .status-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
    '
];

// Include layout
include __DIR__ . '/themes/modern/admin/layout.php';
?>