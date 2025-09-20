<?php
/**
 * Cleanup Test Files
 * Script untuk membersihkan file-file test setelah selesai testing
 */

// Security check
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    die('⚠️ Untuk keamanan, tambahkan ?confirm=yes di URL untuk menjalankan cleanup');
}

echo "<h2>🧹 Cleanup Test Files</h2>";

$test_files = [
    'check-table.php',
    'test-end-to-end.php', 
    'test-ui-consistency.php',
    'setup-event-tables.php',
    'test-event-display.php',
    'debug-hosting.php'
];

$deleted = [];
$not_found = [];

foreach ($test_files as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            $deleted[] = $file;
            echo "✅ Deleted: $file<br>";
        } else {
            echo "❌ Failed to delete: $file<br>";
        }
    } else {
        $not_found[] = $file;
        echo "ℹ️ Not found: $file<br>";
    }
}

echo "<hr>";
echo "<h3>📊 Summary</h3>";
echo "Deleted: " . count($deleted) . " files<br>";
echo "Not found: " . count($not_found) . " files<br>";

if (count($deleted) > 0) {
    echo "<h4>✅ Successfully Deleted:</h4>";
    echo "<ul>";
    foreach ($deleted as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
}

if (count($not_found) > 0) {
    echo "<h4>ℹ️ Files Not Found:</h4>";
    echo "<ul>";
    foreach ($not_found as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3>📋 Keep These Files:</h3>";
echo "<ul>";
echo "<li><strong>test-report.md</strong> - Dokumentasi hasil testing</li>";
echo "<li><strong>cleanup-test-files.php</strong> - Script cleanup ini (hapus manual jika perlu)</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>✅ Cleanup selesai!</strong></p>";
echo "<p>File test sudah dibersihkan. Sistem event scheduling siap untuk production.</p>";
echo "<p><a href='admin/event-scheduling.php' style='color: #007cba;'>→ Buka Event Management</a></p>";
?>