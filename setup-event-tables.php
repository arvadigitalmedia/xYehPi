<?php
/**
 * Setup Event Tables
 * Membuat tabel yang diperlukan untuk sistem event scheduling
 */

require_once 'bootstrap.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Event Tables</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>";

echo "<h1>Setup Event Tables</h1>";

try {
    $db = db();
    
    // Check and create epic_event_categories table
    echo "<h2>1. Setup epic_event_categories</h2>";
    $result = $db->query("SHOW TABLES LIKE 'epic_event_categories'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>✓ Tabel epic_event_categories sudah ada</p>";
    } else {
        echo "<p class='info'>Membuat tabel epic_event_categories...</p>";
        $sql = "CREATE TABLE `epic_event_categories` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `description` text,
            `access_levels` text,
            `color` varchar(7) DEFAULT '#007cba',
            `icon` varchar(50) DEFAULT 'calendar',
            `is_active` tinyint(1) DEFAULT 1,
            `created_by` bigint(20) unsigned DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `epic_event_categories_created_by_index` (`created_by`),
            KEY `epic_event_categories_is_active_index` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($db->query($sql)) {
            echo "<p class='success'>✓ Tabel epic_event_categories berhasil dibuat</p>";
            
            // Insert default categories
            $defaultCategories = [
                ['Webinar', 'Webinar dan presentasi online', '#2196F3'],
                ['Workshop', 'Workshop dan pelatihan', '#4CAF50'],
                ['Meeting', 'Meeting dan diskusi', '#FF9800'],
                ['Conference', 'Konferensi dan seminar', '#9C27B0']
            ];
            
            foreach ($defaultCategories as $cat) {
                $stmt = $db->prepare("INSERT INTO epic_event_categories (name, description, color, created_by) VALUES (?, ?, ?, 1)");
                $stmt->bind_param("sss", $cat[0], $cat[1], $cat[2]);
                $stmt->execute();
            }
            echo "<p class='success'>✓ Default categories berhasil ditambahkan</p>";
        } else {
            echo "<p class='error'>✗ Error membuat tabel: " . $db->error . "</p>";
        }
    }
    
    // Check and create epic_zoom_events table
    echo "<h2>2. Setup epic_zoom_events</h2>";
    $result = $db->query("SHOW TABLES LIKE 'epic_zoom_events'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>✓ Tabel epic_zoom_events sudah ada</p>";
    } else {
        echo "<p class='info'>Membuat tabel epic_zoom_events...</p>";
        $sql = "CREATE TABLE `epic_zoom_events` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `location` varchar(255) DEFAULT NULL,
            `category_id` bigint(20) unsigned DEFAULT NULL,
            `start_datetime` datetime NOT NULL,
            `end_datetime` datetime NOT NULL,
            `timezone` varchar(50) DEFAULT 'Asia/Jakarta',
            `access_level` enum('public','member','premium','private') DEFAULT 'public',
            `max_participants` int(11) DEFAULT NULL,
            `registration_required` tinyint(1) DEFAULT 0,
            `registration_deadline` datetime DEFAULT NULL,
            `status` enum('draft','scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
            `zoom_meeting_id` varchar(50) DEFAULT NULL,
            `zoom_join_url` text,
            `zoom_start_url` text,
            `zoom_password` varchar(50) DEFAULT NULL,
            `created_by` bigint(20) unsigned NOT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `epic_zoom_events_category_id_index` (`category_id`),
            KEY `epic_zoom_events_created_by_index` (`created_by`),
            KEY `epic_zoom_events_status_index` (`status`),
            KEY `epic_zoom_events_start_datetime_index` (`start_datetime`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($db->query($sql)) {
            echo "<p class='success'>✓ Tabel epic_zoom_events berhasil dibuat</p>";
        } else {
            echo "<p class='error'>✗ Error membuat tabel: " . $db->error . "</p>";
        }
    }
    
    // Add foreign key constraint if not exists
    echo "<h2>3. Setup Foreign Key Constraints</h2>";
    $result = $db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                         WHERE TABLE_NAME = 'epic_zoom_events' 
                         AND CONSTRAINT_TYPE = 'FOREIGN KEY' 
                         AND CONSTRAINT_NAME = 'epic_zoom_events_category_id_foreign'");
    
    if ($result && $result->num_rows == 0) {
        $sql = "ALTER TABLE `epic_zoom_events` 
                ADD CONSTRAINT `epic_zoom_events_category_id_foreign` 
                FOREIGN KEY (`category_id`) REFERENCES `epic_event_categories` (`id`) ON DELETE CASCADE";
        
        if ($db->query($sql)) {
            echo "<p class='success'>✓ Foreign key constraint berhasil ditambahkan</p>";
        } else {
            echo "<p class='error'>✗ Error menambahkan foreign key: " . $db->error . "</p>";
        }
    } else {
        echo "<p class='success'>✓ Foreign key constraint sudah ada</p>";
    }
    
    echo "<h2>4. Verifikasi Setup</h2>";
    $tables = ['epic_event_categories', 'epic_zoom_events'];
    foreach ($tables as $table) {
        $result = $db->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p class='success'>✓ Tabel $table: " . $row['count'] . " records</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='test-end-to-end.php'>→ Lanjut ke Test End-to-End</a></p>";
echo "</body></html>";
?>