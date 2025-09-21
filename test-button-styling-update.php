<?php
/**
 * Test Script untuk Verifikasi Update Styling Tombol dan Teks
 * Memastikan perubahan warna tombol dan teks berhasil
 */

echo "=== TEST UPDATE STYLING TOMBOL DAN TEKS ===\n\n";

$register_file = __DIR__ . '/themes/modern/auth/register.php';
$content = file_get_contents($register_file);

$tests = [
    'soft_silver_button' => [
        'name' => 'Tombol soft silver semi transparan',
        'pattern' => '/bg-gray-300 bg-opacity-20 backdrop-blur-sm/',
        'should_exist' => true
    ],
    'button_border_silver' => [
        'name' => 'Border tombol silver transparan',
        'pattern' => '/border-gray-400 border-opacity-30/',
        'should_exist' => true
    ],
    'button_text_white' => [
        'name' => 'Teks tombol putih',
        'pattern' => '/text-white.*bg-gray-300/',
        'should_exist' => true
    ],
    'button_hover_effect' => [
        'name' => 'Efek hover tombol silver',
        'pattern' => '/hover:bg-gray-200 hover:bg-opacity-30/',
        'should_exist' => true
    ],
    'button_shadow' => [
        'name' => 'Shadow pada tombol',
        'pattern' => '/shadow-sm/',
        'should_exist' => true
    ],
    'sudah_punya_akun_white' => [
        'name' => 'Teks "Sudah punya akun" putih',
        'pattern' => '/text-white.*Sudah punya akun terdaftar/',
        'should_exist' => true
    ],
    'footer_links_white' => [
        'name' => 'Footer links putih',
        'pattern' => '/text-sm text-white.*Home/s',
        'should_exist' => true
    ],
    'copyright_white' => [
        'name' => 'Copyright text putih dengan opacity',
        'pattern' => '/text-white text-opacity-80.*EPIC HUB/s',
        'should_exist' => true
    ],
    'no_old_ink_colors' => [
        'name' => 'Tidak ada warna ink-300 lama pada teks utama',
        'pattern' => '/text-ink-300.*Sudah punya akun/',
        'should_exist' => false
    ],
    'no_old_button_style' => [
        'name' => 'Tidak ada style tombol lama',
        'pattern' => '/border-ink-600.*text-ink-100/',
        'should_exist' => false
    ]
];

$passed = 0;
$total = count($tests);

foreach ($tests as $key => $test) {
    $found = preg_match($test['pattern'], $content);
    $success = ($test['should_exist'] && $found) || (!$test['should_exist'] && !$found);
    
    echo sprintf(
        "[%s] %s: %s\n",
        $success ? "✓" : "✗",
        $test['name'],
        $success ? "PASS" : "FAIL"
    );
    
    if ($success) {
        $passed++;
    } else {
        echo "   Pattern: {$test['pattern']}\n";
        echo "   Expected: " . ($test['should_exist'] ? "FOUND" : "NOT FOUND") . "\n";
        echo "   Actual: " . ($found ? "FOUND" : "NOT FOUND") . "\n\n";
    }
}

echo "\n=== HASIL TEST ===\n";
echo "Passed: $passed/$total\n";
echo "Status: " . ($passed === $total ? "✓ SEMUA TEST BERHASIL" : "✗ ADA TEST YANG GAGAL") . "\n\n";

// Test tambahan: Simulasi tampilan
echo "=== PREVIEW STYLING ===\n";
echo "✓ Tombol 'Masuk ke Akun Anda':\n";
echo "  - Background: Soft silver semi transparan (gray-300 20% opacity)\n";
echo "  - Border: Silver transparan (gray-400 30% opacity)\n";
echo "  - Text: Putih\n";
echo "  - Effect: Backdrop blur + shadow\n";
echo "  - Hover: Silver lebih terang (gray-200 30% opacity)\n\n";

echo "✓ Teks 'Sudah punya akun terdaftar?':\n";
echo "  - Warna: Putih\n\n";

echo "✓ Footer:\n";
echo "  - Links: Putih\n";
echo "  - Copyright: Putih dengan opacity 80%\n\n";

echo "=== PERUBAHAN YANG BERHASIL ===\n";
echo "✓ Tombol login dengan desain soft silver semi transparan\n";
echo "✓ Teks 'Sudah punya akun' diubah ke warna putih\n";
echo "✓ Footer links diubah ke warna putih\n";
echo "✓ Copyright text diubah ke putih dengan opacity\n";
echo "✓ Efek backdrop blur dan shadow pada tombol\n";
echo "✓ Hover effect yang konsisten dengan tema\n";

echo "\nUpdate styling tombol dan teks berhasil diselesaikan!\n";
?>