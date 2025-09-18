<?php
/**
 * EPIC Hub Zoom Integration Installer
 * Script untuk menginstall database schema dan setup menu
 */

require_once 'bootstrap.php';

// Check if user is admin
if (!epic_is_admin()) {
    die('Access denied. Admin privileges required.');
}

$success_messages = [];
$error_messages = [];

try {
    // Read and execute SQL schema
    $sql_file = __DIR__ . '/zoom-integration-schema.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception('Schema file not found: ' . $sql_file);
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
            continue;
        }
        
        try {
            $epic_db->exec($statement);
        } catch (PDOException $e) {
            // Ignore table already exists errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }
    
    $success_messages[] = 'Database schema berhasil diinstall';
    
    // Check if tables were created successfully
    $tables_to_check = [
        'epic_event_categories',
        'epic_zoom_events', 
        'epic_event_registrations'
    ];
    
    foreach ($tables_to_check as $table) {
        $stmt = $epic_db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            $success_messages[] = "Tabel {$table} berhasil dibuat";
        } else {
            $error_messages[] = "Tabel {$table} gagal dibuat";
        }
    }
    
    // Insert default data if not exists
    $stmt = $epic_db->prepare("SELECT COUNT(*) FROM epic_event_categories");
    $stmt->execute();
    $category_count = $stmt->fetchColumn();
    
    if ($category_count == 0) {
        $default_categories = [
            [
                'name' => 'EPI Insight',
                'description' => 'Event pembinaan via Zoom khusus untuk EPIC Account dan EPIS Account',
                'access_levels' => '["epic", "epis"]',
                'color' => '#10B981',
                'icon' => 'users',
                'created_by' => epic_get_current_user_id()
            ],
            [
                'name' => 'EPI Connect', 
                'description' => 'Event pembinaan via Zoom khusus untuk EPIS Account',
                'access_levels' => '["epis"]',
                'color' => '#F59E0B',
                'icon' => 'user-check',
                'created_by' => epic_get_current_user_id()
            ],
            [
                'name' => 'Webinar EPI',
                'description' => 'Event edukasi untuk Free Account dan semua level lainnya', 
                'access_levels' => '["free", "epic", "epis"]',
                'color' => '#3B82F6',
                'icon' => 'video',
                'created_by' => epic_get_current_user_id()
            ]
        ];
        
        $stmt = $epic_db->prepare("
            INSERT INTO epic_event_categories (name, description, access_levels, color, icon, created_by) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($default_categories as $category) {
            $stmt->execute([
                $category['name'],
                $category['description'],
                $category['access_levels'],
                $category['color'],
                $category['icon'],
                $category['created_by']
            ]);
        }
        
        $success_messages[] = 'Kategori event default berhasil ditambahkan';
    }
    
    // Add sample events
    $stmt = $epic_db->prepare("SELECT COUNT(*) FROM epic_zoom_events");
    $stmt->execute();
    $event_count = $stmt->fetchColumn();
    
    if ($event_count == 0) {
        // Get category IDs
        $stmt = $epic_db->prepare("SELECT id, name FROM epic_event_categories");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $category_ids = array_flip($categories);
        
        $sample_events = [
            [
                'category_id' => $category_ids['EPI Insight'] ?? 1,
                'title' => 'EPI Insight: Strategi Marketing Digital 2025',
                'description' => 'Pembahasan mendalam tentang strategi marketing digital terbaru untuk meningkatkan konversi dan ROI dalam bisnis online.',
                'start_time' => date('Y-m-d H:i:s', strtotime('+7 days 19:00')),
                'end_time' => date('Y-m-d H:i:s', strtotime('+7 days 21:00')),
                'status' => 'published',
                'created_by' => epic_get_current_user_id()
            ],
            [
                'category_id' => $category_ids['EPI Connect'] ?? 2,
                'title' => 'EPI Connect: Leadership untuk EPIS',
                'description' => 'Sesi khusus pengembangan kepemimpinan untuk para EPIS supervisor dalam mengelola tim dan mencapai target.',
                'start_time' => date('Y-m-d H:i:s', strtotime('+10 days 20:00')),
                'end_time' => date('Y-m-d H:i:s', strtotime('+10 days 22:00')),
                'status' => 'published',
                'created_by' => epic_get_current_user_id()
            ],
            [
                'category_id' => $category_ids['Webinar EPI'] ?? 3,
                'title' => 'Webinar EPI: Pengenalan Bisnis Online',
                'description' => 'Webinar gratis untuk pemula yang ingin memulai bisnis online dengan strategi yang tepat dan sustainable.',
                'start_time' => date('Y-m-d H:i:s', strtotime('+14 days 19:30')),
                'end_time' => date('Y-m-d H:i:s', strtotime('+14 days 21:00')),
                'status' => 'published',
                'created_by' => epic_get_current_user_id()
            ]
        ];
        
        $stmt = $epic_db->prepare("
            INSERT INTO epic_zoom_events (category_id, title, description, start_time, end_time, status, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sample_events as $event) {
            $stmt->execute([
                $event['category_id'],
                $event['title'],
                $event['description'],
                $event['start_time'],
                $event['end_time'],
                $event['status'],
                $event['created_by']
            ]);
        }
        
        $success_messages[] = 'Sample event berhasil ditambahkan';
    }
    
    $success_messages[] = 'Zoom Integration berhasil diinstall!';
    
} catch (Exception $e) {
    $error_messages[] = 'Error: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Zoom Integration - EPIC Hub</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1F2937;
            color: #F9FAFB;
            margin: 0;
            padding: 2rem;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #374151;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #3B82F6;
        }
        
        .subtitle {
            color: #9CA3AF;
        }
        
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid #10B981;
            color: #10B981;
        }
        
        .error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #EF4444;
            color: #EF4444;
        }
        
        .actions {
            text-align: center;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #3B82F6;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: #2563EB;
        }
        
        .btn-secondary {
            background: #6B7280;
        }
        
        .btn-secondary:hover {
            background: #4B5563;
        }
        
        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid #3B82F6;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .info-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #3B82F6;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
        }
        
        .info-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #4B5563;
        }
        
        .info-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Zoom Integration Installer</h1>
            <p class="subtitle">Setup database dan konfigurasi untuk fitur Zoom Integration</p>
        </div>
        
        <?php foreach ($success_messages as $message): ?>
            <div class="message success">
                ✅ <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
        
        <?php foreach ($error_messages as $message): ?>
            <div class="message error">
                ❌ <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($error_messages)): ?>
            <div class="info-box">
                <div class="info-title">Instalasi Berhasil! Langkah Selanjutnya:</div>
                <ul class="info-list">
                    <li><strong>Admin Panel:</strong> Akses menu Zoom Integration di admin panel untuk mengelola event</li>
                    <li><strong>Member Area:</strong> Member dapat melihat event sesuai level akses mereka</li>
                    <li><strong>Konfigurasi Zoom:</strong> Atur API credentials Zoom di tab Pengaturan</li>
                    <li><strong>Kategori Event:</strong> 3 kategori default telah dibuat (EPI Insight, EPI Connect, Webinar EPI)</li>
                    <li><strong>Sample Events:</strong> Beberapa sample event telah ditambahkan untuk testing</li>
                </ul>
            </div>
            
            <div class="actions">
                <a href="<?= epic_url('admin/zoom-integration') ?>" class="btn">Buka Admin Panel</a>
                <a href="<?= epic_url('member/zoom-events') ?>" class="btn btn-secondary">Lihat Member Area</a>
            </div>
        <?php else: ?>
            <div class="actions">
                <a href="<?= epic_url() ?>" class="btn btn-secondary">Kembali ke Dashboard</a>
                <a href="javascript:location.reload()" class="btn">Coba Lagi</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>