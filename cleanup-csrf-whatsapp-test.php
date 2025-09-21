<?php
/**
 * Cleanup Script - Hapus File Test CSRF WhatsApp
 */

echo "<h2>Cleanup Test Files</h2>";

$test_files = [
    'test-csrf-whatsapp-fix.php',
    'test-csrf-fix.php',
    'test-whatsapp-settings.php',
    'cleanup-csrf-test.php',
    'cleanup-csrf-whatsapp-test.php'
];

foreach ($test_files as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✓ Berhasil menghapus: $file<br>";
        } else {
            echo "✗ Gagal menghapus: $file<br>";
        }
    } else {
        echo "- File tidak ditemukan: $file<br>";
    }
}

echo "<hr>";
echo "<p><strong>Cleanup selesai!</strong></p>";
echo "<p>Perbaikan CSRF token untuk WhatsApp notification settings telah selesai.</p>";
echo "<p>Silakan test simpan pengaturan WhatsApp notification di halaman admin.</p>";
?>