<?php
/**
 * Test Script untuk Membuat Event Zoom Manual
 * Script untuk testing proses create event dan troubleshooting
 */

// Load bootstrap untuk koneksi database
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/zoom-integration.php';

// Set header untuk output yang bersih
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🧪 Test Create Zoom Event</h1>";
echo "<hr>";

// Step 1: Inisialisasi
echo "<h2>🔧 Step 1: Initialization</h2>";
try {
    global $epic_db;
    if (!$epic_db) {
        throw new Exception('Database connection tidak tersedia');
    }
    
    $epic_zoom = new EpicZoomIntegration();
    echo "✅ Database connection: OK<br>";
    echo "✅ EpicZoomIntegration class: OK<br>";
} catch (Exception $e) {
    echo "❌ Initialization failed: " . $e->getMessage() . "<br>";
    exit;
}

// Step 2: Cek dan buat kategori jika perlu
echo "<h2>📁 Step 2: Check/Create Categories</h2>";
try {
    $stmt = $epic_db->query("SELECT COUNT(*) as count FROM epic_event_categories");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $category_count = $result['count'];
    
    if ($category_count == 0) {
        echo "⚠️ Tidak ada kategori, membuat kategori test...<br>";
        
        // Buat kategori test
        $stmt = $epic_db->prepare("
            INSERT INTO epic_event_categories (name, description, color, icon, access_levels, created_by) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            'Test Category',
            'Kategori untuk testing event',
            '#3B82F6',
            'folder',
            json_encode(['free', 'epic', 'epis']),
            1
        ]);
        
        if ($result) {
            $category_id = $epic_db->lastInsertId();
            echo "✅ Kategori test berhasil dibuat (ID: $category_id)<br>";
        } else {
            echo "❌ Gagal membuat kategori test<br>";
            exit;
        }
    } else {
        // Ambil kategori pertama
        $stmt = $epic_db->query("SELECT id, name FROM epic_event_categories LIMIT 1");
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        $category_id = $category['id'];
        echo "✅ Menggunakan kategori existing: {$category['name']} (ID: $category_id)<br>";
    }
} catch (Exception $e) {
    echo "❌ Error handling categories: " . $e->getMessage() . "<br>";
    exit;
}

// Step 3: Buat event test
echo "<h2>📝 Step 3: Create Test Event</h2>";
$test_event_data = [
    'category_id' => $category_id,
    'title' => 'Test Event - ' . date('Y-m-d H:i:s'),
    'description' => 'Event test untuk troubleshooting masalah tampilan data di tabel management.',
    'start_time' => date('Y-m-d H:i:s', strtotime('+1 day')),
    'end_time' => date('Y-m-d H:i:s', strtotime('+1 day +2 hours')),
    'timezone' => 'Asia/Jakarta',
    'max_participants' => 50,
    'registration_required' => 1,
    'status' => 'published',
    'created_by' => 1
];

echo "📋 Data event yang akan dibuat:<br>";
echo "<ul>";
foreach ($test_event_data as $key => $value) {
    echo "<li><strong>$key:</strong> $value</li>";
}
echo "</ul>";

try {
    echo "🔄 Memulai proses create event...<br>";
    
    $result = $epic_zoom->createEvent($test_event_data);
    
    if ($result) {
        $event_id = $epic_db->lastInsertId();
        echo "✅ Event berhasil dibuat dengan ID: <strong>$event_id</strong><br>";
        
        // Verifikasi data tersimpan
        $stmt = $epic_db->prepare("SELECT * FROM epic_zoom_events WHERE id = ?");
        $stmt->execute([$event_id]);
        $saved_event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($saved_event) {
            echo "✅ Data berhasil diverifikasi di database<br>";
            echo "<strong>Data tersimpan:</strong><br>";
            echo "<pre>" . print_r($saved_event, true) . "</pre>";
        } else {
            echo "❌ Data tidak ditemukan setelah insert<br>";
        }
    } else {
        echo "❌ Create event gagal<br>";
        
        // Cek error log
        $error_log = error_get_last();
        if ($error_log) {
            echo "<strong>Last error:</strong> " . $error_log['message'] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Exception saat create event: " . $e->getMessage() . "<br>";
    echo "<strong>Stack trace:</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Step 4: Test getEvents() method
echo "<h2>🔍 Step 4: Test getEvents() Method</h2>";
try {
    echo "🔄 Testing getEvents() method...<br>";
    
    $events_data = $epic_zoom->getEvents(1, 20);
    
    echo "📊 Result dari getEvents():<br>";
    echo "<ul>";
    echo "<li>Total events: <strong>" . ($events_data['total'] ?? 0) . "</strong></li>";
    echo "<li>Events returned: <strong>" . count($events_data['events'] ?? []) . "</strong></li>";
    echo "<li>Current page: <strong>" . ($events_data['page'] ?? 'N/A') . "</strong></li>";
    echo "<li>Total pages: <strong>" . ($events_data['total_pages'] ?? 'N/A') . "</strong></li>";
    echo "</ul>";
    
    if (!empty($events_data['events'])) {
        echo "✅ getEvents() method berfungsi dengan baik<br>";
        echo "<strong>Events yang ditemukan:</strong><br>";
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Title</th><th>Category</th><th>Start Time</th><th>Status</th>";
        echo "</tr>";
        
        foreach ($events_data['events'] as $event) {
            echo "<tr>";
            echo "<td>{$event['id']}</td>";
            echo "<td>" . htmlspecialchars($event['title']) . "</td>";
            echo "<td>" . htmlspecialchars($event['category_name'] ?? 'N/A') . "</td>";
            echo "<td>{$event['start_time']}</td>";
            echo "<td>{$event['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ getEvents() method mengembalikan array kosong<br>";
        
        // Debug query manual
        echo "<strong>🔍 Debug manual query:</strong><br>";
        $stmt = $epic_db->query("
            SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon
            FROM epic_zoom_events e
            JOIN epic_event_categories c ON e.category_id = c.id
            ORDER BY e.start_time DESC
            LIMIT 20
        ");
        $manual_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($manual_events)) {
            echo "✅ Manual query berhasil, ditemukan " . count($manual_events) . " events<br>";
            echo "💡 <strong>Kemungkinan masalah di method getEvents()</strong><br>";
        } else {
            echo "❌ Manual query juga kosong<br>";
            
            // Cek tanpa JOIN
            $stmt = $epic_db->query("SELECT * FROM epic_zoom_events ORDER BY start_time DESC LIMIT 20");
            $no_join_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($no_join_events)) {
                echo "✅ Events ada tapi JOIN dengan kategori gagal<br>";
                echo "💡 <strong>Masalah: category_id tidak valid atau kategori tidak ada</strong><br>";
            } else {
                echo "❌ Tidak ada events sama sekali di database<br>";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing getEvents(): " . $e->getMessage() . "<br>";
}

// Step 5: Diagnosis final
echo "<h2>🩺 Step 5: Final Diagnosis</h2>";

// Cek total events dan categories
try {
    $stmt = $epic_db->query("SELECT COUNT(*) as total FROM epic_zoom_events");
    $total_events = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $epic_db->query("SELECT COUNT(*) as total FROM epic_event_categories");
    $total_categories = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "📊 <strong>Database Summary:</strong><br>";
    echo "<ul>";
    echo "<li>Total events: <strong>$total_events</strong></li>";
    echo "<li>Total categories: <strong>$total_categories</strong></li>";
    echo "</ul>";
    
    if ($total_events > 0 && $total_categories > 0) {
        // Cek apakah ada events dengan category_id yang tidak valid
        $stmt = $epic_db->query("
            SELECT e.id, e.title, e.category_id, c.name as category_name
            FROM epic_zoom_events e
            LEFT JOIN epic_event_categories c ON e.category_id = c.id
            WHERE c.id IS NULL
        ");
        $orphan_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($orphan_events)) {
            echo "⚠️ <strong>MASALAH DITEMUKAN:</strong> Ada events dengan category_id tidak valid:<br>";
            foreach ($orphan_events as $event) {
                echo "- Event ID {$event['id']}: '{$event['title']}' (category_id: {$event['category_id']})<br>";
            }
            echo "💡 <strong>Solusi:</strong> Update category_id atau buat kategori yang sesuai<br>";
        } else {
            echo "✅ Semua events memiliki kategori yang valid<br>";
            echo "💡 <strong>Kemungkinan masalah di frontend atau caching</strong><br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error final diagnosis: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Test selesai pada:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='/admin/zoom-integration'>🔗 Kembali ke Zoom Integration</a></p>";
echo "<p><a href='/debug-zoom-events.php'>🔍 Lihat Debug Events</a></p>";
?>