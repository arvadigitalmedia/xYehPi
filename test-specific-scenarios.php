<?php
/**
 * Test Spesifik untuk Skenario Draft dan Publikasi Event
 * Menguji masalah yang dilaporkan user
 */

// Bootstrap sistem
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Test Spesifik: Draft & Publikasi Event</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
.success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
.error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
.info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px; }
</style>";

// Inisialisasi
try {
    $epic_event_scheduling = new EpicEventScheduling();
    $categories = $epic_event_scheduling->getEventCategories();
    
    if (empty($categories)) {
        echo "<div class='error'>Tidak ada kategori tersedia. Test tidak bisa dilanjutkan.</div>";
        exit;
    }
    
    echo "<div class='info'>Menggunakan kategori: " . $categories[0]['name'] . " (ID: " . $categories[0]['id'] . ")</div>";
} catch (Exception $e) {
    echo "<div class='error'>Error inisialisasi: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

// Test 1: Draft dengan data minimal (yang sebelumnya gagal)
echo "<div class='test-section'>";
echo "<h2>Test 1: Simpan Draft (Data Minimal)</h2>";
echo "<p><strong>Skenario:</strong> Hanya title dan category, tanpa waktu</p>";

$draft_data = [
    'title' => 'Test Draft Event - ' . date('Y-m-d H:i:s'),
    'category_id' => $categories[0]['id'],
    'action' => 'save_draft'
];

echo "<div class='info'>Data yang dikirim:</div>";
echo "<pre>" . json_encode($draft_data, JSON_PRETTY_PRINT) . "</pre>";

try {
    $result = $epic_event_scheduling->createEvent($draft_data);
    
    if ($result['success']) {
        echo "<div class='success'>✓ BERHASIL: " . htmlspecialchars($result['message']) . "</div>";
        echo "<div class='info'>Event ID: " . $result['event_id'] . "</div>";
    } else {
        echo "<div class='error'>✗ GAGAL: " . htmlspecialchars($result['message']) . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 2: Publikasi dengan data lengkap (yang sebelumnya gagal)
echo "<div class='test-section'>";
echo "<h2>Test 2: Publikasi Event (Data Lengkap)</h2>";
echo "<p><strong>Skenario:</strong> Semua field diisi lengkap</p>";

$publish_data = [
    'title' => 'Test Publikasi Event - ' . date('Y-m-d H:i:s'),
    'description' => 'Deskripsi lengkap untuk event test publikasi',
    'category_id' => $categories[0]['id'],
    'start_time' => date('Y-m-d H:i:s', strtotime('+2 days')),
    'end_time' => date('Y-m-d H:i:s', strtotime('+2 days +3 hours')),
    'location' => 'Ruang Meeting Virtual',
    'max_participants' => 100,
    'registration_required' => 1,
    'registration_deadline' => date('Y-m-d H:i:s', strtotime('+1 day')),
    'timezone' => 'Asia/Jakarta',
    'action' => 'create_event',
    'created_by' => 1
];

echo "<div class='info'>Data yang dikirim:</div>";
echo "<pre>" . json_encode($publish_data, JSON_PRETTY_PRINT) . "</pre>";

try {
    $result = $epic_event_scheduling->createEvent($publish_data);
    
    if ($result['success']) {
        echo "<div class='success'>✓ BERHASIL: " . htmlspecialchars($result['message']) . "</div>";
        echo "<div class='info'>Event ID: " . $result['event_id'] . "</div>";
    } else {
        echo "<div class='error'>✗ GAGAL: " . htmlspecialchars($result['message']) . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 3: Draft dengan waktu parsial
echo "<div class='test-section'>";
echo "<h2>Test 3: Draft dengan Waktu Parsial</h2>";
echo "<p><strong>Skenario:</strong> Draft dengan hanya start_time</p>";

$partial_draft_data = [
    'title' => 'Test Draft Parsial - ' . date('Y-m-d H:i:s'),
    'description' => 'Draft dengan waktu parsial',
    'category_id' => $categories[0]['id'],
    'start_time' => date('Y-m-d H:i:s', strtotime('+3 days')),
    'location' => 'TBD',
    'action' => 'save_draft'
];

echo "<div class='info'>Data yang dikirim:</div>";
echo "<pre>" . json_encode($partial_draft_data, JSON_PRETTY_PRINT) . "</pre>";

try {
    $result = $epic_event_scheduling->createEvent($partial_draft_data);
    
    if ($result['success']) {
        echo "<div class='success'>✓ BERHASIL: " . htmlspecialchars($result['message']) . "</div>";
        echo "<div class='info'>Event ID: " . $result['event_id'] . "</div>";
    } else {
        echo "<div class='error'>✗ GAGAL: " . htmlspecialchars($result['message']) . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 4: Validasi error yang diharapkan
echo "<div class='test-section'>";
echo "<h2>Test 4: Validasi Error (Data Invalid)</h2>";
echo "<p><strong>Skenario:</strong> Data invalid untuk memastikan validasi berfungsi</p>";

$invalid_data = [
    'title' => '', // Title kosong
    'category_id' => 99999, // Kategori tidak ada
    'action' => 'create_event'
];

echo "<div class='info'>Data yang dikirim:</div>";
echo "<pre>" . json_encode($invalid_data, JSON_PRETTY_PRINT) . "</pre>";

try {
    $result = $epic_event_scheduling->createEvent($invalid_data);
    
    if (!$result['success']) {
        echo "<div class='success'>✓ VALIDASI BERFUNGSI: " . htmlspecialchars($result['message']) . "</div>";
    } else {
        echo "<div class='error'>✗ VALIDASI GAGAL: Data invalid diterima</div>";
    }
} catch (Exception $e) {
    echo "<div class='success'>✓ EXCEPTION HANDLING BERFUNGSI: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Ringkasan
echo "<div class='test-section info'>";
echo "<h2>Ringkasan Test</h2>";
echo "<p><strong>Test selesai!</strong> Periksa hasil di atas:</p>";
echo "<ul>";
echo "<li>Test 1 & 2 harus BERHASIL (ini yang sebelumnya gagal)</li>";
echo "<li>Test 3 harus BERHASIL (draft dengan data parsial)</li>";
echo "<li>Test 4 harus menunjukkan validasi berfungsi</li>";
echo "</ul>";
echo "<p><a href='admin/event-scheduling-add' target='_blank'>→ Test Manual di Form</a></p>";
echo "</div>";

// Cek log error terbaru
echo "<div class='test-section'>";
echo "<h2>Log Error Terbaru</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $logs = file_get_contents($error_log);
    $recent_logs = array_slice(explode("\n", $logs), -20);
    echo "<pre>" . htmlspecialchars(implode("\n", $recent_logs)) . "</pre>";
} else {
    echo "<div class='info'>Error log tidak ditemukan atau kosong</div>";
}
echo "</div>";
?>