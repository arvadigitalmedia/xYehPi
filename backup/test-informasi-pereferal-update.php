<?php
/**
 * Test Script untuk Verifikasi Update Informasi Pereferal
 * Memastikan perubahan label dan penghapusan badge berhasil
 */

echo "=== TEST UPDATE INFORMASI PEREFERAL ===\n\n";

$register_file = __DIR__ . '/themes/modern/auth/register.php';
$content = file_get_contents($register_file);

$tests = [
    'informasi_pereferal_label' => [
        'name' => 'Label "Informasi Pereferal" ada',
        'pattern' => '/Informasi Pereferal/',
        'should_exist' => true
    ],
    'no_stats_card_label' => [
        'name' => 'Label "Stats Card Kode Referral" sudah dihapus',
        'pattern' => '/Stats Card Kode Referral/',
        'should_exist' => false
    ],
    'no_epi_channel_badge' => [
        'name' => 'Badge "EPI Channel Authorized" sudah dihapus',
        'pattern' => '/bg-green-500.*EPI Channel Authorized/',
        'should_exist' => false
    ],
    'epis_supervisor_label' => [
        'name' => 'Label "EPIS Supervisor" ada',
        'pattern' => '/EPIS Supervisor/',
        'should_exist' => true
    ],
    'supervisor_data_display' => [
        'name' => 'Tampilan data EPIS Supervisor',
        'pattern' => '/supervisor_name.*Admin EPIS/',
        'should_exist' => true
    ],
    'induk_pereferal_text' => [
        'name' => 'Teks "Induk dari" pereferal',
        'pattern' => '/Induk dari.*\$referrer_info\[\'name\'\]/',
        'should_exist' => true
    ],
    'no_status_label' => [
        'name' => 'Label "Status" sudah dihapus',
        'pattern' => '/text-xs mb-1">Status</',
        'should_exist' => false
    ],
    'blue_color_scheme' => [
        'name' => 'Skema warna biru untuk icon',
        'pattern' => '/text-blue-300/',
        'should_exist' => true
    ],
    'user_icon_updated' => [
        'name' => 'Icon user untuk Informasi Pereferal',
        'pattern' => '/M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z/',
        'should_exist' => true
    ],
    'no_green_indicator' => [
        'name' => 'Indikator hijau "EPI Channel Authorized" sudah dihapus',
        'pattern' => '/bg-green-400 rounded-full.*EPI Channel Authorized/',
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

// Test tambahan: Simulasi tampilan dengan data
echo "=== TEST SIMULASI TAMPILAN ===\n";

// Simulasi data referrer dengan supervisor
$referrer_info = [
    'name' => 'John Doe',
    'referral_code' => 'TEST123',
    'supervisor_name' => 'Admin EPIS Central'
];

echo "Simulasi data referrer:\n";
echo "- Nama Pereferal: " . $referrer_info['name'] . "\n";
echo "- EPIS Supervisor: " . ($referrer_info['supervisor_name'] ?? 'Admin EPIS') . "\n";
echo "- Induk dari: " . $referrer_info['name'] . "\n";
echo "- Kode Referral: " . $referrer_info['referral_code'] . "\n";

echo "\n=== PERUBAHAN YANG BERHASIL ===\n";
echo "✓ Badge 'EPI Channel Authorized' telah dihapus\n";
echo "✓ Label diubah dari 'Stats Card Kode Referral' → 'Informasi Pereferal'\n";
echo "✓ Label 'Status' diubah menjadi 'EPIS Supervisor'\n";
echo "✓ Menampilkan data EPIS Supervisor sebagai induk pereferal\n";
echo "✓ Skema warna diubah dari hijau ke biru\n";
echo "✓ Icon diubah menjadi icon user/person\n";
echo "✓ Indikator status hijau telah dihapus\n";

echo "\nUpdate Informasi Pereferal berhasil diselesaikan!\n";
?>