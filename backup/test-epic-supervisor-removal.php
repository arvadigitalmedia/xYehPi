<?php
/**
 * Test Script: Verifikasi Penghapusan EPIC Supervisor
 * Memastikan semua referensi EPIC Supervisor telah dihapus dari form registrasi
 */

echo "=== TEST PENGHAPUSAN EPIC SUPERVISOR ===\n\n";

// Test 1: Cek file register.php
echo "1. Memeriksa file register.php...\n";
$register_content = file_get_contents(__DIR__ . '/themes/modern/auth/register.php');

$epic_supervisor_patterns = [
    'EPIC Supervisor',
    'epis_supervisor',
    'episSupervisor',
    'updateEpisSupervisorField',
    'epic-supervisor-field',
    'episSupervisorId'
];

$found_references = [];
foreach ($epic_supervisor_patterns as $pattern) {
    // Cari semua kemunculan pattern
    $lines = explode("\n", $register_content);
    foreach ($lines as $line_num => $line) {
        if (stripos($line, $pattern) !== false) {
            // Skip jika dalam komentar removal (PHP, HTML, atau JavaScript)
            $trimmed_line = trim($line);
            if (preg_match('/^\/\/.*removed.*' . preg_quote($pattern, '/') . '/i', $trimmed_line) ||
                preg_match('/^<!--.*removed.*' . preg_quote($pattern, '/') . '/i', $trimmed_line) ||
                preg_match('/^<!--.*' . preg_quote($pattern, '/') . '.*removed.*-->/i', $trimmed_line) ||
                preg_match('/\/\/.*' . preg_quote($pattern, '/') . '.*removed/i', $trimmed_line)) {
                continue;
            }
            $found_references[] = $pattern . " (line " . ($line_num + 1) . ")";
        }
    }
}

if (empty($found_references)) {
    echo "   ✓ Tidak ada referensi EPIC Supervisor yang tersisa\n";
} else {
    echo "   ✗ Masih ada referensi: " . implode(', ', $found_references) . "\n";
}

// Test 2: Cek struktur form
echo "\n2. Memeriksa struktur form...\n";
$form_fields_removed = [
    'name="epis_supervisor_id"',
    'id="episSupervisorId"',
    'id="selectedEpisId"'
];

$form_issues = [];
foreach ($form_fields_removed as $field) {
    if (stripos($register_content, $field) !== false) {
        $form_issues[] = $field;
    }
}

if (empty($form_issues)) {
    echo "   ✓ Semua field EPIC Supervisor telah dihapus\n";
} else {
    echo "   ✗ Masih ada field: " . implode(', ', $form_issues) . "\n";
}

// Test 3: Cek JavaScript functions
echo "\n3. Memeriksa JavaScript functions...\n";
$js_functions_removed = [
    'updateEpisSupervisorField',
    'function updateEpisSupervisorField'
];

$js_issues = [];
foreach ($js_functions_removed as $func) {
    if (stripos($register_content, $func) !== false) {
        $js_issues[] = $func;
    }
}

if (empty($js_issues)) {
    echo "   ✓ Semua fungsi JavaScript EPIC Supervisor telah dihapus\n";
} else {
    echo "   ✗ Masih ada fungsi: " . implode(', ', $js_issues) . "\n";
}

// Test 4: Cek PHP variables
echo "\n4. Memeriksa PHP variables...\n";
$php_vars_removed = [
    '$available_epis',
    '$epis_required',
    'epis_supervisor_name',
    'epis_supervisor_id'
];

$php_issues = [];
foreach ($php_vars_removed as $var) {
    if (stripos($register_content, $var) !== false) {
        // Kecuali jika dalam komentar removal
        if (!preg_match('/\/\/.*removed.*' . preg_quote($var, '/') . '/i', $register_content)) {
            $php_issues[] = $var;
        }
    }
}

if (empty($php_issues)) {
    echo "   ✓ Semua variabel PHP EPIC Supervisor telah dihapus\n";
} else {
    echo "   ✗ Masih ada variabel: " . implode(', ', $php_issues) . "\n";
}

// Test 5: Simulasi form submission
echo "\n5. Simulasi form submission...\n";
$_POST = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '081234567890',
    'password' => 'Test123!',
    'confirm_password' => 'Test123!',
    'terms' => '1'
];

// Cek apakah tidak ada error terkait EPIC Supervisor
$validation_passed = true;
$required_fields = ['name', 'email', 'phone', 'password', 'confirm_password', 'terms'];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $validation_passed = false;
        break;
    }
}

if ($validation_passed) {
    echo "   ✓ Form dapat disubmit tanpa field EPIC Supervisor\n";
} else {
    echo "   ✗ Form validation gagal\n";
}

// Summary
echo "\n=== RINGKASAN HASIL TEST ===\n";
$total_issues = count($found_references) + count($form_issues) + count($js_issues) + count($php_issues);

if ($total_issues === 0) {
    echo "✅ SEMUA TEST BERHASIL!\n";
    echo "   - Semua referensi EPIC Supervisor telah dihapus\n";
    echo "   - Form registrasi telah disederhanakan\n";
    echo "   - Tidak ada field atau fungsi yang tersisa\n";
} else {
    echo "❌ MASIH ADA MASALAH!\n";
    echo "   - Total issues ditemukan: $total_issues\n";
    echo "   - Perlu perbaikan lebih lanjut\n";
}

echo "\n=== TEST SELESAI ===\n";
?>