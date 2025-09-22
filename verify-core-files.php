<?php
/**
 * Script untuk memverifikasi file core yang diperlukan
 * Jalankan di server untuk mengecek file yang hilang
 */

// Daftar file core yang wajib ada
$required_files = [
    'core/functions.php',
    'core/member-functions.php',
    'core/email-confirmation.php',
    'core/admin.php',
    'core/api.php',
    'core/dashboard.php',
    'core/order.php',
    'core/sponsor.php',
    'core/epis-functions.php',
    'core/csrf-protection.php',
    'core/rate-limiter.php',
    'config/database.php',
    'bootstrap.php'
];

echo "<h2>Verifikasi File Core - " . date('Y-m-d H:i:s') . "</h2>\n";
echo "<pre>\n";

$missing_files = [];
$existing_files = [];

foreach ($required_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "✅ FOUND: $file\n";
        $existing_files[] = $file;
    } else {
        echo "❌ MISSING: $file\n";
        $missing_files[] = $file;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "SUMMARY:\n";
echo "Total files checked: " . count($required_files) . "\n";
echo "Existing files: " . count($existing_files) . "\n";
echo "Missing files: " . count($missing_files) . "\n";

if (!empty($missing_files)) {
    echo "\n❌ FILES YANG HARUS DI-UPLOAD:\n";
    foreach ($missing_files as $file) {
        echo "- $file\n";
    }
    
    echo "\n📋 LANGKAH PERBAIKAN:\n";
    echo "1. Upload file yang hilang dari lokal ke server\n";
    echo "2. Pastikan struktur folder sama persis\n";
    echo "3. Cek permission file (644 untuk .php)\n";
    echo "4. Jalankan script ini lagi untuk verifikasi\n";
} else {
    echo "\n✅ SEMUA FILE CORE LENGKAP!\n";
}

echo "</pre>\n";

// Cek juga permission dan ukuran file penting
if (file_exists(__DIR__ . '/core/functions.php')) {
    $size = filesize(__DIR__ . '/core/functions.php');
    $perms = substr(sprintf('%o', fileperms(__DIR__ . '/core/functions.php')), -4);
    echo "<p><strong>core/functions.php:</strong> Size: {$size} bytes, Permissions: {$perms}</p>\n";
}

if (file_exists(__DIR__ . '/core/member-functions.php')) {
    $size = filesize(__DIR__ . '/core/member-functions.php');
    $perms = substr(sprintf('%o', fileperms(__DIR__ . '/core/member-functions.php')), -4);
    echo "<p><strong>core/member-functions.php:</strong> Size: {$size} bytes, Permissions: {$perms}</p>\n";
} else {
    echo "<p><strong>❌ core/member-functions.php TIDAK DITEMUKAN!</strong></p>\n";
}
?>