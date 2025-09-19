<?php
/**
 * Test End-to-End: Input → Database → Tampilan
 * Memverifikasi alur lengkap sistem event scheduling
 */

require_once 'bootstrap.php';
require_once 'core/event-scheduling.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test End-to-End Event Scheduling</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>";

echo "<h1>Test End-to-End Event Scheduling System</h1>";

// Initialize event scheduling system
global $epic_event_scheduling;
if (!isset($epic_event_scheduling)) {
    $epic_event_scheduling = new EpicEventScheduling();
}

// Test 1: Verifikasi koneksi database
echo "<div class='test-section'>";
echo "<h2>1. Test Koneksi Database</h2>";
try {
    $db = db();
    $connection = $db->getConnection();
    if ($connection) {
        echo "<p class='success'>✓ Koneksi database berhasil</p>";
    } else {
        echo "<p class='error'>✗ Koneksi database gagal</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error koneksi: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 2: Verifikasi struktur tabel
echo "<div class='test-section'>";
echo "<h2>2. Test Struktur Tabel</h2>";
try {
    $tables = ['epic_event_categories', 'epic_zoom_events', 'epic_users'];
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $result = $db->query($query);
        if ($result && $result->num_rows > 0) {
            echo "<p class='success'>✓ Tabel $table ada</p>";
        } else {
            echo "<p class='error'>✗ Tabel $table tidak ditemukan</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking tables: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 3: Test CRUD Operations
echo "<div class='test-section'>";
echo "<h2>3. Test CRUD Operations</h2>";

// Create test category
echo "<h3>3.1 Create Category</h3>";
try {
    $categoryData = [
        'name' => 'Test Category E2E',
        'description' => 'Test category untuk end-to-end testing',
        'color' => '#FF5722'
    ];
    
    $categoryId = $epic_event_scheduling->createEventCategory($categoryData);
    if ($categoryId) {
        echo "<p class='success'>✓ Category berhasil dibuat dengan ID: $categoryId</p>";
    } else {
        echo "<p class='error'>✗ Gagal membuat category</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error creating category: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Create test event
echo "<h3>3.2 Create Event</h3>";
try {
    $eventData = [
        'title' => 'Test Event E2E',
        'description' => 'Test event untuk end-to-end testing',
        'location' => 'Virtual Meeting Room',
        'category_id' => $categoryId ?? 1,
        'start_datetime' => date('Y-m-d H:i:s', strtotime('+1 day')),
        'end_datetime' => date('Y-m-d H:i:s', strtotime('+1 day +2 hours')),
        'access_level' => 'public',
        'max_participants' => 50,
        'created_by' => 1
    ];
    
    $eventId = $epic_event_scheduling->createEvent($eventData);
    if ($eventId) {
        echo "<p class='success'>✓ Event berhasil dibuat dengan ID: $eventId</p>";
    } else {
        echo "<p class='error'>✗ Gagal membuat event</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error creating event: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Read events
echo "<h3>3.3 Read Events</h3>";
try {
    $events = $epic_event_scheduling->getEvents(1, 5);
    if ($events && isset($events['data']) && count($events['data']) > 0) {
        echo "<p class='success'>✓ Berhasil mengambil " . count($events['data']) . " events</p>";
        echo "<p class='info'>Total events: " . ($events['total'] ?? 0) . "</p>";
    } else {
        echo "<p class='warning'>⚠ Tidak ada events ditemukan</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error reading events: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 4: Test Tampilan Data
echo "<div class='test-section'>";
echo "<h2>4. Test Tampilan Data</h2>";

if (isset($events) && $events && isset($events['data'])) {
    echo "<h3>4.1 Render Tabel Events</h3>";
    echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>ID</th><th>Title</th><th>Category</th><th>Location</th><th>Start Time</th><th>Status</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    foreach ($events['data'] as $event) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($event['id'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($event['title'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($event['category_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($event['location'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($event['start_datetime'] ?? '') . "</td>";
        echo "<td>";
        if (function_exists('epic_get_event_schedule_status_badge')) {
            echo epic_get_event_schedule_status_badge($event['status'] ?? 'scheduled');
        } else {
            echo htmlspecialchars($event['status'] ?? 'scheduled');
        }
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    echo "<p class='success'>✓ Tabel events berhasil dirender</p>";
} else {
    echo "<p class='warning'>⚠ Tidak ada data untuk ditampilkan</p>";
}
echo "</div>";

// Test 5: Test Helper Functions
echo "<div class='test-section'>";
echo "<h2>5. Test Helper Functions</h2>";

echo "<h3>5.1 Test Status Badge Function</h3>";
if (function_exists('epic_get_event_schedule_status_badge')) {
    $statuses = ['scheduled', 'ongoing', 'completed', 'cancelled'];
    foreach ($statuses as $status) {
        echo "<p>Status '$status': " . epic_get_event_schedule_status_badge($status) . "</p>";
    }
    echo "<p class='success'>✓ Status badge function bekerja</p>";
} else {
    echo "<p class='error'>✗ Function epic_get_event_schedule_status_badge tidak ditemukan</p>";
}

echo "<h3>5.2 Test Date Formatting</h3>";
$testDate = date('Y-m-d H:i:s');
echo "<p>Original: $testDate</p>";
echo "<p>Formatted: " . date('d/m/Y H:i', strtotime($testDate)) . "</p>";
echo "<p class='success'>✓ Date formatting bekerja</p>";
echo "</div>";

// Test 6: Test Performance
echo "<div class='test-section'>";
echo "<h2>6. Test Performance</h2>";

$startTime = microtime(true);
try {
    $events = $epic_event_scheduling->getEvents(1, 10);
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
    
    echo "<p class='info'>Query execution time: " . number_format($executionTime, 2) . " ms</p>";
    
    if ($executionTime < 100) {
        echo "<p class='success'>✓ Performance baik (< 100ms)</p>";
    } elseif ($executionTime < 500) {
        echo "<p class='warning'>⚠ Performance acceptable (< 500ms)</p>";
    } else {
        echo "<p class='error'>✗ Performance lambat (> 500ms)</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error testing performance: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 7: Cleanup (Optional)
echo "<div class='test-section'>";
echo "<h2>7. Cleanup Test Data</h2>";
try {
    if (isset($eventId) && $eventId) {
        $deleted = $epic_event_scheduling->deleteEvent($eventId);
        if ($deleted) {
            echo "<p class='success'>✓ Test event berhasil dihapus</p>";
        } else {
            echo "<p class='warning'>⚠ Test event tidak dapat dihapus</p>";
        }
    }
    
    if (isset($categoryId) && $categoryId) {
        $deleted = $epic_event_scheduling->deleteEventCategory($categoryId);
        if ($deleted) {
            echo "<p class='success'>✓ Test category berhasil dihapus</p>";
        } else {
            echo "<p class='warning'>⚠ Test category tidak dapat dihapus</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error cleanup: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>Summary</h2>";
echo "<p class='info'>Test end-to-end selesai. Periksa hasil di atas untuk memastikan semua komponen bekerja dengan baik.</p>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

echo "</body></html>";
?>