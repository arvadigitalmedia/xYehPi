<?php
/**
 * Test Komprehensif Event Scheduling Form
 * Menguji berbagai skenario data dan error handling
 */

// Bootstrap sistem
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Test Komprehensif Event Scheduling</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
.success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
.error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
.info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// Test 1: Koneksi Database
echo "<div class='test-section'>";
echo "<h2>Test 1: Koneksi Database</h2>";
try {
    $epic_event_scheduling = new EpicEventScheduling();
    echo "<div class='success'>✓ Koneksi database berhasil</div>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Koneksi database gagal: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
echo "</div>";

// Test 2: Load Kategori
echo "<div class='test-section'>";
echo "<h2>Test 2: Load Kategori Event</h2>";
try {
    $categories = $epic_event_scheduling->getEventCategories();
    if (empty($categories)) {
        echo "<div class='error'>✗ Tidak ada kategori event tersedia</div>";
    } else {
        echo "<div class='success'>✓ Berhasil load " . count($categories) . " kategori</div>";
        echo "<pre>" . print_r($categories, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Gagal load kategori: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 3: Validasi Data Draft
echo "<div class='test-section'>";
echo "<h2>Test 3: Simpan Draft (Data Minimal)</h2>";
$draft_data = [
    'title' => 'Test Event Draft',
    'category_id' => !empty($categories) ? $categories[0]['id'] : 1,
    'action' => 'save_draft'
];

try {
    $result = $epic_event_scheduling->createEvent($draft_data);
    if ($result['success']) {
        echo "<div class='success'>✓ Draft berhasil disimpan: " . htmlspecialchars($result['message']) . "</div>";
        echo "<div class='info'>Event ID: " . $result['event_id'] . "</div>";
    } else {
        echo "<div class='error'>✗ Draft gagal disimpan: " . htmlspecialchars($result['message']) . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error saat simpan draft: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 4: Validasi Data Lengkap
echo "<div class='test-section'>";
echo "<h2>Test 4: Publikasi Event (Data Lengkap)</h2>";
$full_data = [
    'title' => 'Test Event Lengkap',
    'description' => 'Deskripsi event test yang lengkap',
    'category_id' => !empty($categories) ? $categories[0]['id'] : 1,
    'start_time' => date('Y-m-d H:i:s', strtotime('+1 day')),
    'end_time' => date('Y-m-d H:i:s', strtotime('+1 day +2 hours')),
    'location' => 'Ruang Meeting A',
    'max_participants' => 50,
    'registration_required' => 1,
    'registration_deadline' => date('Y-m-d H:i:s', strtotime('+12 hours')),
    'access_levels' => ['member', 'premium'],
    'action' => 'create_event'
];

try {
    $result = $epic_event_scheduling->createEvent($full_data);
    if ($result['success']) {
        echo "<div class='success'>✓ Event berhasil dipublikasi: " . htmlspecialchars($result['message']) . "</div>";
        echo "<div class='info'>Event ID: " . $result['event_id'] . "</div>";
    } else {
        echo "<div class='error'>✗ Event gagal dipublikasi: " . htmlspecialchars($result['message']) . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error saat publikasi event: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 5: Validasi Error - Data Invalid
echo "<div class='test-section'>";
echo "<h2>Test 5: Validasi Error (Data Invalid)</h2>";
$invalid_data = [
    'title' => '', // Title kosong
    'category_id' => 99999, // Kategori tidak ada
    'start_time' => 'invalid-date',
    'action' => 'create_event'
];

try {
    $result = $epic_event_scheduling->createEvent($invalid_data);
    if (!$result['success']) {
        echo "<div class='success'>✓ Validasi error berfungsi: " . htmlspecialchars($result['message']) . "</div>";
    } else {
        echo "<div class='error'>✗ Validasi error tidak berfungsi - data invalid diterima</div>";
    }
} catch (Exception $e) {
    echo "<div class='success'>✓ Exception handling berfungsi: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 6: Test Form Rendering
echo "<div class='test-section'>";
echo "<h2>Test 6: Form Rendering</h2>";
try {
    // Simulasi data layout
    $layout_data = [
        'page_title' => 'Add New Event',
        'categories' => $categories,
        'error' => '',
        'success' => ''
    ];
    
    $template_path = __DIR__ . '/themes/modern/admin/content/event-scheduling-add-content.php';
    if (file_exists($template_path)) {
        echo "<div class='success'>✓ Template file ditemukan</div>";
        
        // Test include template
        ob_start();
        include $template_path;
        $template_output = ob_get_clean();
        
        if (!empty($template_output)) {
            echo "<div class='success'>✓ Template berhasil di-render (" . strlen($template_output) . " characters)</div>";
        } else {
            echo "<div class='error'>✗ Template kosong atau error</div>";
        }
    } else {
        echo "<div class='error'>✗ Template file tidak ditemukan: $template_path</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error rendering template: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Test 7: JavaScript Validation
echo "<div class='test-section'>";
echo "<h2>Test 7: JavaScript Form Validation</h2>";
echo "<div class='info'>Untuk test JavaScript, buka halaman admin/event-scheduling-add di browser dan periksa:</div>";
echo "<ul>";
echo "<li>Tombol 'Save Draft' dan 'Create Event' berfungsi</li>";
echo "<li>Validasi form client-side berjalan</li>";
echo "<li>Loading state ditampilkan saat submit</li>";
echo "<li>Alert success/error muncul setelah submit</li>";
echo "</ul>";
echo "</div>";

echo "<div class='test-section info'>";
echo "<h2>Ringkasan Test</h2>";
echo "<p><strong>Semua test telah selesai.</strong> Jika ada error di atas, silakan perbaiki sebelum melanjutkan.</p>";
echo "<p><a href='admin/event-scheduling-add' target='_blank'>→ Buka Form Event Scheduling</a></p>";
echo "</div>";
?>