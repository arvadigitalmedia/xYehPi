<?php
/**
 * Test Script untuk Verifikasi Penyimpanan Data Event Zoom
 * Script ini akan melakukan simulasi penginputan data dummy dan verifikasi sistem
 */

// Load bootstrap untuk koneksi database
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/zoom-integration.php';

// Set header untuk output yang bersih
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🧪 Test Penyimpanan Data Event Zoom</h1>";
echo "<hr>";

// Step 1: Verifikasi koneksi database
echo "<h2>📋 Step 1: Verifikasi Koneksi Database</h2>";
try {
    global $epic_db;
    if (!$epic_db) {
        throw new Exception('Database connection tidak tersedia');
    }
    
    // Test koneksi dengan query sederhana
    $stmt = $epic_db->query("SELECT 1");
    if ($stmt) {
        echo "✅ Koneksi database: <strong>BERHASIL</strong><br>";
        echo "📊 Database driver: " . $epic_db->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";
    } else {
        throw new Exception('Query test gagal');
    }
} catch (Exception $e) {
    echo "❌ Koneksi database: <strong>GAGAL</strong><br>";
    echo "🚨 Error: " . $e->getMessage() . "<br>";
    exit;
}

// Step 2: Verifikasi tabel yang diperlukan
echo "<h2>🗄️ Step 2: Verifikasi Tabel Database</h2>";
$required_tables = [
    'epic_event_categories',
    'epic_zoom_events',
    'epic_zoom_settings'
];

foreach ($required_tables as $table) {
    try {
        $stmt = $epic_db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabel <strong>$table</strong>: ADA<br>";
        } else {
            echo "❌ Tabel <strong>$table</strong>: TIDAK ADA<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error checking table $table: " . $e->getMessage() . "<br>";
    }
}

// Step 3: Verifikasi kategori event (diperlukan untuk foreign key)
echo "<h2>📁 Step 3: Verifikasi Kategori Event</h2>";
try {
    $stmt = $epic_db->query("SELECT COUNT(*) as count FROM epic_event_categories");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $category_count = $result['count'];
    
    if ($category_count > 0) {
        echo "✅ Kategori tersedia: <strong>$category_count kategori</strong><br>";
        
        // Ambil kategori pertama untuk test
        $stmt = $epic_db->query("SELECT id, name FROM epic_event_categories LIMIT 1");
        $test_category = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "🎯 Kategori test: <strong>{$test_category['name']}</strong> (ID: {$test_category['id']})<br>";
    } else {
        echo "⚠️ Tidak ada kategori tersedia. Membuat kategori test...<br>";
        
        // Buat kategori test
        $stmt = $epic_db->prepare("
            INSERT INTO epic_event_categories (name, description, color, icon, access_levels, created_by) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            'Test Category',
            'Kategori untuk testing penyimpanan event',
            '#3B82F6',
            'folder',
            json_encode(['free', 'epic', 'epis']),
            1
        ]);
        
        if ($result) {
            $test_category = ['id' => $epic_db->lastInsertId(), 'name' => 'Test Category'];
            echo "✅ Kategori test berhasil dibuat (ID: {$test_category['id']})<br>";
        } else {
            echo "❌ Gagal membuat kategori test<br>";
            exit;
        }
    }
} catch (Exception $e) {
    echo "❌ Error verifikasi kategori: " . $e->getMessage() . "<br>";
    exit;
}

// Step 4: Inisialisasi class EpicZoomIntegration
echo "<h2>🔧 Step 4: Inisialisasi Zoom Integration Class</h2>";
try {
    $epic_zoom = new EpicZoomIntegration();
    echo "✅ Class EpicZoomIntegration: <strong>BERHASIL DIINISIALISASI</strong><br>";
} catch (Exception $e) {
    echo "❌ Error inisialisasi class: " . $e->getMessage() . "<br>";
    exit;
}

// Step 5: Persiapan data dummy untuk test
echo "<h2>📝 Step 5: Persiapan Data Dummy</h2>";
$dummy_data = [
    'category_id' => $test_category['id'],
    'title' => 'Test Event - ' . date('Y-m-d H:i:s'),
    'description' => 'Ini adalah event test untuk verifikasi sistem penyimpanan data. Event ini dibuat secara otomatis oleh script testing.',
    'start_time' => date('Y-m-d H:i:s', strtotime('+1 day')),
    'end_time' => date('Y-m-d H:i:s', strtotime('+1 day +2 hours')),
    'timezone' => 'Asia/Jakarta',
    'max_participants' => 50,
    'registration_required' => 1,
    'registration_deadline' => date('Y-m-d H:i:s', strtotime('+12 hours')),
    'status' => 'published',
    'created_by' => 1
];

echo "📋 <strong>Data Dummy yang akan disimpan:</strong><br>";
echo "<ul>";
foreach ($dummy_data as $key => $value) {
    echo "<li><strong>$key:</strong> $value</li>";
}
echo "</ul>";

// Step 6: Validasi data sebelum penyimpanan
echo "<h2>✅ Step 6: Validasi Data</h2>";
$validation_errors = [];

// Validasi field wajib
if (empty($dummy_data['category_id'])) {
    $validation_errors[] = 'Category ID tidak boleh kosong';
}
if (empty($dummy_data['title'])) {
    $validation_errors[] = 'Title tidak boleh kosong';
}
if (empty($dummy_data['start_time'])) {
    $validation_errors[] = 'Start time tidak boleh kosong';
}
if (empty($dummy_data['end_time'])) {
    $validation_errors[] = 'End time tidak boleh kosong';
}

// Validasi format datetime
if (!strtotime($dummy_data['start_time'])) {
    $validation_errors[] = 'Format start_time tidak valid';
}
if (!strtotime($dummy_data['end_time'])) {
    $validation_errors[] = 'Format end_time tidak valid';
}

// Validasi logika waktu
if (strtotime($dummy_data['start_time']) >= strtotime($dummy_data['end_time'])) {
    $validation_errors[] = 'Start time harus lebih awal dari end time';
}

// Validasi kategori exists
try {
    $stmt = $epic_db->prepare("SELECT id FROM epic_event_categories WHERE id = ?");
    $stmt->execute([$dummy_data['category_id']]);
    if ($stmt->rowCount() == 0) {
        $validation_errors[] = 'Kategori dengan ID tersebut tidak ditemukan';
    }
} catch (Exception $e) {
    $validation_errors[] = 'Error validasi kategori: ' . $e->getMessage();
}

if (empty($validation_errors)) {
    echo "✅ Validasi data: <strong>SEMUA VALID</strong><br>";
} else {
    echo "❌ Validasi data: <strong>ADA ERROR</strong><br>";
    echo "<ul>";
    foreach ($validation_errors as $error) {
        echo "<li>🚨 $error</li>";
    }
    echo "</ul>";
    exit;
}

// Step 7: Test penyimpanan data
echo "<h2>💾 Step 7: Test Penyimpanan Data</h2>";
try {
    echo "🔄 Memulai proses penyimpanan...<br>";
    
    $result = $epic_zoom->createEvent($dummy_data);
    
    if ($result) {
        echo "✅ Penyimpanan data: <strong>BERHASIL</strong><br>";
        
        // Ambil ID event yang baru dibuat
        $event_id = $epic_db->lastInsertId();
        echo "🆔 Event ID: <strong>$event_id</strong><br>";
        
        // Step 8: Verifikasi data tersimpan
        echo "<h2>🔍 Step 8: Verifikasi Data Tersimpan</h2>";
        
        $stmt = $epic_db->prepare("
            SELECT e.*, c.name as category_name 
            FROM epic_zoom_events e 
            JOIN epic_event_categories c ON e.category_id = c.id 
            WHERE e.id = ?
        ");
        $stmt->execute([$event_id]);
        $saved_event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($saved_event) {
            echo "✅ Data berhasil ditemukan di database<br>";
            echo "📋 <strong>Data yang tersimpan:</strong><br>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            foreach ($saved_event as $key => $value) {
                echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
            }
            echo "</table>";
            
            // Verifikasi data sesuai input
            $verification_passed = true;
            $verification_errors = [];
            
            if ($saved_event['title'] !== $dummy_data['title']) {
                $verification_errors[] = "Title tidak sesuai: expected '{$dummy_data['title']}', got '{$saved_event['title']}'";
                $verification_passed = false;
            }
            
            if ($saved_event['category_id'] != $dummy_data['category_id']) {
                $verification_errors[] = "Category ID tidak sesuai: expected '{$dummy_data['category_id']}', got '{$saved_event['category_id']}'";
                $verification_passed = false;
            }
            
            if ($saved_event['status'] !== $dummy_data['status']) {
                $verification_errors[] = "Status tidak sesuai: expected '{$dummy_data['status']}', got '{$saved_event['status']}'";
                $verification_passed = false;
            }
            
            if ($verification_passed) {
                echo "<h2>🎉 Step 9: Hasil Akhir</h2>";
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h3>✅ SEMUA TEST BERHASIL!</h3>";
                echo "<p><strong>Sistem penyimpanan data event berfungsi 100% sempurna!</strong></p>";
                echo "<ul>";
                echo "<li>✅ Koneksi database: OK</li>";
                echo "<li>✅ Tabel database: OK</li>";
                echo "<li>✅ Validasi data: OK</li>";
                echo "<li>✅ Penyimpanan data: OK</li>";
                echo "<li>✅ Verifikasi data: OK</li>";
                echo "</ul>";
                echo "<p><strong>Event ID $event_id berhasil disimpan dan dapat diakses melalui sistem manajemen event.</strong></p>";
                echo "</div>";
            } else {
                echo "<h2>⚠️ Step 9: Ada Masalah Verifikasi</h2>";
                echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h3>⚠️ Data tersimpan tapi ada perbedaan:</h3>";
                echo "<ul>";
                foreach ($verification_errors as $error) {
                    echo "<li>🚨 $error</li>";
                }
                echo "</ul>";
                echo "</div>";
            }
            
        } else {
            echo "❌ Data tidak ditemukan di database setelah penyimpanan<br>";
        }
        
    } else {
        echo "❌ Penyimpanan data: <strong>GAGAL</strong><br>";
        echo "🚨 Method createEvent() mengembalikan false<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error saat penyimpanan: " . $e->getMessage() . "<br>";
    echo "📋 Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

// Step 10: Cleanup (opsional)
echo "<h2>🧹 Step 10: Cleanup</h2>";
echo "<p><em>Event test telah dibuat dengan ID: $event_id</em></p>";
echo "<p><em>Anda dapat menghapus event test ini melalui admin panel atau membiarkannya sebagai data test.</em></p>";

echo "<hr>";
echo "<p><strong>Test selesai pada:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='/admin/zoom-integration'>🔗 Lihat di Admin Panel</a></p>";
?>