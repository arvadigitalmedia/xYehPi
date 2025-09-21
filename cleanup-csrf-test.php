<?php
/**
 * Cleanup CSRF Test Files
 * Menghapus file test setelah verifikasi selesai
 */

echo "<h1>Cleanup CSRF Test Files</h1>";

$test_files = [
    'test-csrf-fix.php',
    'test-whatsapp-settings.php',
    'cleanup-csrf-test.php'
];

foreach ($test_files as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<p>✅ Deleted: $file</p>";
        } else {
            echo "<p>❌ Failed to delete: $file</p>";
        }
    } else {
        echo "<p>⚠️ File not found: $file</p>";
    }
}

echo "<h2>Cleanup Complete</h2>";
echo "<p>File test telah dibersihkan. Silakan test halaman WhatsApp notification settings melalui admin panel.</p>";
?>