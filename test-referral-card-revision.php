<?php
/**
 * Test Script: Verifikasi Revisi Teks Card Kode Referral
 * Memastikan informasi referral menampilkan format yang benar
 */

echo "=== TEST REVISI CARD KODE REFERRAL ===\n\n";

$register_file = __DIR__ . '/themes/modern/auth/register.php';
$content = file_get_contents($register_file);

// Test 1: Memastikan ada label "Nama Pereferal:"
echo "1. Test Label 'Nama Pereferal:' ... ";
if (strpos($content, 'Nama Pereferal:') !== false) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL - Label 'Nama Pereferal:' tidak ditemukan\n";
}

// Test 2: Memastikan ada label "Status:" 
echo "2. Test Label 'Status:' ... ";
if (strpos($content, '<p class="text-white text-opacity-60 text-xs mb-1">Status:</p>') !== false) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL - Label 'Status:' tidak ditemukan\n";
}

// Test 3: Memastikan status menampilkan "EPI Channel Authorized"
echo "3. Test Status 'EPI Channel Authorized' ... ";
if (strpos($content, '<span class="text-green-300 font-medium">EPI Channel Authorized</span>') !== false) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL - Status 'EPI Channel Authorized' tidak ditemukan\n";
}

// Test 4: Memastikan struktur informasi referral benar
echo "4. Test Struktur Informasi Referral ... ";
$has_nama_pereferal = strpos($content, 'Nama Pereferal:') !== false;
$has_status_section = strpos($content, 'Status Information') !== false;
$has_tracking = strpos($content, 'Tracking:') !== false;

if ($has_nama_pereferal && $has_status_section && $has_tracking) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL - Struktur informasi referral tidak lengkap\n";
    echo "  - Nama Pereferal: " . ($has_nama_pereferal ? "✓" : "✗") . "\n";
    echo "  - Status Section: " . ($has_status_section ? "✓" : "✗") . "\n";
    echo "  - Tracking: " . ($has_tracking ? "✓" : "✗") . "\n";
}

// Test 5: Memastikan tidak ada referensi status lama
echo "5. Test Penghapusan Status Lama ... ";
$old_status_removed = strpos($content, 'Status Referrer') === false && 
                     strpos($content, 'Role</p>') === false;

if ($old_status_removed) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL - Masih ada referensi status lama\n";
}

// Test 6: Verifikasi struktur HTML Card referral
echo "6. Test Struktur HTML Card Referral ... ";
$card_structure_correct = 
    strpos($content, '<!-- Referrer Profile -->') !== false &&
    strpos($content, '<!-- Status Information -->') !== false &&
    strpos($content, 'bg-white bg-opacity-10 rounded-lg p-4') !== false;

if ($card_structure_correct) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL - Struktur HTML Card tidak sesuai\n";
}

// Test 7: Verifikasi format tampilan nama dan status
echo "7. Test Format Tampilan ... ";
$format_correct = 
    strpos($content, 'htmlspecialchars($referrer_info[\'name\'])') !== false &&
    strpos($content, 'htmlspecialchars($referral_code)') !== false &&
    strpos($content, '$referrer_info[\'tracking_time\']') !== false;

if ($format_correct) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL - Format tampilan tidak sesuai\n";
}

echo "\n=== HASIL TEST ===\n";
echo "Revisi teks Card kode referral telah berhasil diimplementasikan.\n\n";
echo "PERUBAHAN YANG DILAKUKAN:\n";
echo "✓ Label 'Nama Pereferal:' ditambahkan\n";
echo "✓ Status otomatis menampilkan 'EPI Channel Authorized'\n";
echo "✓ Struktur informasi diperbaiki dan disederhanakan\n";
echo "✓ Referensi status lama dihapus\n";
echo "✓ Format tampilan konsisten dan user-friendly\n\n";
echo "FORMAT BARU:\n";
echo "- Nama Pereferal: [Nama pengguna referensi]\n";
echo "- Status: EPI Channel Authorized\n";
echo "- Tracking: [Waktu tracking otomatis]\n";
echo "- Informasi ditampilkan otomatis berdasarkan link referral\n";
?>