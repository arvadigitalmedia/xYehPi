<?php
/**
 * Debug Script untuk Memeriksa Data Event Zoom
 * Script untuk troubleshooting masalah data event tidak muncul di tabel
 */

// Load bootstrap untuk koneksi database
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/zoom-integration.php';

// Set header untuk output yang bersih
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Debug Zoom Events Data</h1>";
echo "<hr>";

// Step 1: Cek koneksi database
echo "<h2>📊 Step 1: Database Connection</h2>";
try {
    global $epic_db;
    if (!$epic_db) {
        throw new Exception('Database connection tidak tersedia');
    }
    echo "✅ Database connection: <strong>OK</strong><br>";
} catch (Exception $e) {
    echo "❌ Database connection: <strong>FAILED</strong><br>";
    echo "Error: " . $e->getMessage() . "<br>";
    exit;
}

// Step 2: Cek tabel epic_zoom_events
echo "<h2>🗄️ Step 2: Table Structure</h2>";
try {
    $stmt = $epic_db->query("DESCRIBE epic_zoom_events");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "❌ Error checking table structure: " . $e->getMessage() . "<br>";
}

// Step 3: Cek jumlah total data
echo "<h2>📈 Step 3: Total Records Count</h2>";
try {
    $stmt = $epic_db->query("SELECT COUNT(*) as total FROM epic_zoom_events");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_events = $result['total'];
    
    echo "📊 Total events in database: <strong>$total_events</strong><br>";
    
    if ($total_events == 0) {
        echo "⚠️ <strong>MASALAH DITEMUKAN:</strong> Tidak ada data event di database!<br>";
        echo "💡 <strong>Kemungkinan penyebab:</strong><br>";
        echo "<ul>";
        echo "<li>Data tidak tersimpan saat form submission</li>";
        echo "<li>Error saat proses createEvent()</li>";
        echo "<li>Database transaction rollback</li>";
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "❌ Error counting records: " . $e->getMessage() . "<br>";
}

// Step 4: Tampilkan semua data events (jika ada)
echo "<h2>📋 Step 4: All Events Data</h2>";
try {
    $stmt = $epic_db->query("
        SELECT e.*, c.name as category_name 
        FROM epic_zoom_events e 
        LEFT JOIN epic_event_categories c ON e.category_id = c.id 
        ORDER BY e.created_at DESC
    ");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($events)) {
        echo "❌ <strong>Tidak ada data event ditemukan</strong><br>";
    } else {
        echo "✅ Ditemukan <strong>" . count($events) . "</strong> event(s):<br><br>";
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Title</th><th>Category</th><th>Start Time</th><th>Status</th><th>Created At</th>";
        echo "</tr>";
        
        foreach ($events as $event) {
            echo "<tr>";
            echo "<td>{$event['id']}</td>";
            echo "<td>" . htmlspecialchars($event['title']) . "</td>";
            echo "<td>" . htmlspecialchars($event['category_name'] ?? 'N/A') . "</td>";
            echo "<td>{$event['start_time']}</td>";
            echo "<td>{$event['status']}</td>";
            echo "<td>{$event['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "❌ Error fetching events: " . $e->getMessage() . "<br>";
}

// Step 5: Test getEvents() method
echo "<h2>🔧 Step 5: Test getEvents() Method</h2>";
try {
    $epic_zoom = new EpicZoomIntegration();
    $events_data = $epic_zoom->getEvents(1, 20);
    
    echo "📊 getEvents() result:<br>";
    echo "<ul>";
    echo "<li>Total events: <strong>" . ($events_data['total'] ?? 0) . "</strong></li>";
    echo "<li>Events returned: <strong>" . count($events_data['events'] ?? []) . "</strong></li>";
    echo "<li>Current page: <strong>" . ($events_data['page'] ?? 'N/A') . "</strong></li>";
    echo "<li>Total pages: <strong>" . ($events_data['total_pages'] ?? 'N/A') . "</strong></li>";
    echo "</ul>";
    
    if (!empty($events_data['events'])) {
        echo "✅ getEvents() method working correctly<br>";
        echo "<strong>Sample event data:</strong><br>";
        echo "<pre>" . print_r($events_data['events'][0], true) . "</pre>";
    } else {
        echo "❌ getEvents() method returns empty array<br>";
    }
} catch (Exception $e) {
    echo "❌ Error testing getEvents(): " . $e->getMessage() . "<br>";
}

// Step 6: Cek kategori events
echo "<h2>📁 Step 6: Event Categories</h2>";
try {
    $stmt = $epic_db->query("SELECT COUNT(*) as total FROM epic_event_categories");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_categories = $result['total'];
    
    echo "📊 Total categories: <strong>$total_categories</strong><br>";
    
    if ($total_categories == 0) {
        echo "⚠️ <strong>MASALAH:</strong> Tidak ada kategori event!<br>";
        echo "💡 Event memerlukan kategori untuk ditampilkan (JOIN dengan epic_event_categories)<br>";
    } else {
        $stmt = $epic_db->query("SELECT id, name FROM epic_event_categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "✅ Available categories:<br>";
        echo "<ul>";
        foreach ($categories as $category) {
            echo "<li>ID: {$category['id']} - {$category['name']}</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "❌ Error checking categories: " . $e->getMessage() . "<br>";
}

// Step 7: Cek recent database activity
echo "<h2>⏰ Step 7: Recent Database Activity</h2>";
try {
    // Cek event terbaru (dalam 1 jam terakhir)
    $stmt = $epic_db->query("
        SELECT COUNT(*) as recent_count 
        FROM epic_zoom_events 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $recent_events = $result['recent_count'];
    
    echo "📊 Events created in last hour: <strong>$recent_events</strong><br>";
    
    if ($recent_events > 0) {
        echo "✅ Ada aktivitas recent, data sedang tersimpan<br>";
        
        // Tampilkan event terbaru
        $stmt = $epic_db->query("
            SELECT id, title, created_at 
            FROM epic_zoom_events 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY created_at DESC
        ");
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<strong>Recent events:</strong><br>";
        echo "<ul>";
        foreach ($recent as $event) {
            echo "<li>ID: {$event['id']} - {$event['title']} (Created: {$event['created_at']})</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "❌ Error checking recent activity: " . $e->getMessage() . "<br>";
}

// Step 8: Diagnosis dan Rekomendasi
echo "<h2>🩺 Step 8: Diagnosis & Recommendations</h2>";

if ($total_events == 0) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px;'>";
    echo "<h3>⚠️ DIAGNOSIS: Data Event Tidak Tersimpan</h3>";
    echo "<p><strong>Kemungkinan penyebab:</strong></p>";
    echo "<ul>";
    echo "<li>Error saat form submission di halaman add-event</li>";
    echo "<li>Method createEvent() gagal execute</li>";
    echo "<li>Database transaction error</li>";
    echo "<li>Validation error yang tidak ter-handle</li>";
    echo "</ul>";
    echo "<p><strong>Rekomendasi:</strong></p>";
    echo "<ul>";
    echo "<li>Cek error log PHP</li>";
    echo "<li>Test manual form submission dengan debug</li>";
    echo "<li>Verifikasi method createEvent() di core/zoom-integration.php</li>";
    echo "</ul>";
    echo "</div>";
} elseif ($total_events > 0 && count($events_data['events'] ?? []) == 0) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px;'>";
    echo "<h3>⚠️ DIAGNOSIS: Data Ada Tapi Tidak Tampil</h3>";
    echo "<p><strong>Kemungkinan penyebab:</strong></p>";
    echo "<ul>";
    echo "<li>JOIN query dengan kategori gagal</li>";
    echo "<li>Filter atau WHERE condition terlalu ketat</li>";
    echo "<li>Pagination issue</li>";
    echo "</ul>";
    echo "<p><strong>Rekomendasi:</strong></p>";
    echo "<ul>";
    echo "<li>Cek apakah semua event memiliki category_id yang valid</li>";
    echo "<li>Test query getEvents() tanpa filter</li>";
    echo "<li>Verifikasi JOIN condition</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
    echo "<h3>✅ DIAGNOSIS: Data Normal</h3>";
    echo "<p>Data event tersimpan dan method getEvents() berfungsi dengan baik.</p>";
    echo "<p><strong>Kemungkinan masalah di frontend:</strong></p>";
    echo "<ul>";
    echo "<li>Cache browser</li>";
    echo "<li>JavaScript error</li>";
    echo "<li>Template rendering issue</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Debug selesai pada:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='/admin/zoom-integration'>🔗 Kembali ke Zoom Integration</a></p>";
?>