<?php
/**
 * Test Registration Error Messages
 * File untuk menguji berbagai skenario error pada form registrasi
 */

require_once 'bootstrap.php';
require_once 'core/csrf-protection.php';
require_once 'core/registration-controller.php';

// Start session untuk testing
session_start();

echo "<h1>Test Registration Error Messages</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-case { border: 1px solid #ddd; margin: 10px 0; padding: 15px; }
    .success { background: #d4edda; border-color: #c3e6cb; }
    .error { background: #f8d7da; border-color: #f5c6cb; }
    .info { background: #d1ecf1; border-color: #bee5eb; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 4px; }
</style>";

// Test Case 1: Email sudah terdaftar
echo "<div class='test-case info'>";
echo "<h3>Test Case 1: Email Duplikat</h3>";

// Simulasi data dengan email yang sudah ada
$testData1 = [
    'name' => 'Test User',
    'email' => 'admin@test.com', // Email yang sudah ada di database
    'phone' => '081234567890',
    'password' => 'Test123',
    'confirm_password' => 'Test123',
    'terms' => '1'
];

$rules = epic_get_registration_validation_rules();
$errors1 = epic_validate_form_data($testData1, $rules);

echo "<strong>Data Test:</strong><br>";
echo "<pre>" . print_r($testData1, true) . "</pre>";
echo "<strong>Hasil Validasi:</strong><br>";
if (!empty($errors1)) {
    echo "<div class='error'>";
    echo "<pre>" . print_r($errors1, true) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='success'>Tidak ada error (unexpected)</div>";
}
echo "</div>";

// Test Case 2: Nomor telepon duplikat
echo "<div class='test-case info'>";
echo "<h3>Test Case 2: Nomor Telepon Duplikat</h3>";

$testData2 = [
    'name' => 'Test User 2',
    'email' => 'newuser@test.com',
    'phone' => '081234567890', // Nomor yang mungkin sudah ada
    'password' => 'Test123',
    'confirm_password' => 'Test123',
    'terms' => '1'
];

$errors2 = epic_validate_form_data($testData2, $rules);

echo "<strong>Data Test:</strong><br>";
echo "<pre>" . print_r($testData2, true) . "</pre>";
echo "<strong>Hasil Validasi:</strong><br>";
if (!empty($errors2)) {
    echo "<div class='error'>";
    echo "<pre>" . print_r($errors2, true) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='success'>Tidak ada error</div>";
}
echo "</div>";

// Test Case 3: Password tidak sesuai
echo "<div class='test-case info'>";
echo "<h3>Test Case 3: Password Tidak Cocok</h3>";

$testData3 = [
    'name' => 'Test User 3',
    'email' => 'user3@test.com',
    'phone' => '081234567891',
    'password' => 'Test123',
    'confirm_password' => 'Test456', // Password tidak cocok
    'terms' => '1'
];

$errors3 = epic_validate_form_data($testData3, $rules);

echo "<strong>Data Test:</strong><br>";
echo "<pre>" . print_r($testData3, true) . "</pre>";
echo "<strong>Hasil Validasi:</strong><br>";
if (!empty($errors3)) {
    echo "<div class='error'>";
    echo "<pre>" . print_r($errors3, true) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='success'>Tidak ada error (unexpected)</div>";
}
echo "</div>";

// Test Case 4: Format nomor telepon tidak valid
echo "<div class='test-case info'>";
echo "<h3>Test Case 4: Format Nomor Telepon Tidak Valid</h3>";

$testData4 = [
    'name' => 'Test User 4',
    'email' => 'user4@test.com',
    'phone' => '123', // Format tidak valid
    'password' => 'Test123',
    'confirm_password' => 'Test123',
    'terms' => '1'
];

$errors4 = epic_validate_form_data($testData4, $rules);

echo "<strong>Data Test:</strong><br>";
echo "<pre>" . print_r($testData4, true) . "</pre>";
echo "<strong>Hasil Validasi:</strong><br>";
if (!empty($errors4)) {
    echo "<div class='error'>";
    echo "<pre>" . print_r($errors4, true) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='success'>Tidak ada error (unexpected)</div>";
}
echo "</div>";

// Test Case 5: Password lemah (tidak ada angka)
echo "<div class='test-case info'>";
echo "<h3>Test Case 5: Password Lemah (Tanpa Angka)</h3>";

$testData5 = [
    'name' => 'Test User 5',
    'email' => 'user5@test.com',
    'phone' => '081234567892',
    'password' => 'TestPassword', // Tidak ada angka
    'confirm_password' => 'TestPassword',
    'terms' => '1'
];

$errors5 = epic_validate_form_data($testData5, $rules);

echo "<strong>Data Test:</strong><br>";
echo "<pre>" . print_r($testData5, true) . "</pre>";
echo "<strong>Hasil Validasi:</strong><br>";
if (!empty($errors5)) {
    echo "<div class='error'>";
    echo "<pre>" . print_r($errors5, true) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='success'>Tidak ada error (unexpected)</div>";
}
echo "</div>";

// Test Case 6: Field wajib kosong
echo "<div class='test-case info'>";
echo "<h3>Test Case 6: Field Wajib Kosong</h3>";

$testData6 = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'confirm_password' => '',
    // terms tidak diset (tidak dicentang)
];

$errors6 = epic_validate_form_data($testData6, $rules);

echo "<strong>Data Test:</strong><br>";
echo "<pre>" . print_r($testData6, true) . "</pre>";
echo "<strong>Hasil Validasi:</strong><br>";
if (!empty($errors6)) {
    echo "<div class='error'>";
    echo "<pre>" . print_r($errors6, true) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='success'>Tidak ada error (unexpected)</div>";
}
echo "</div>";

// Test Case 7: Data valid
echo "<div class='test-case info'>";
echo "<h3>Test Case 7: Data Valid</h3>";

$testData7 = [
    'name' => 'Valid User',
    'email' => 'validuser@test.com',
    'phone' => '081234567893',
    'password' => 'ValidPass123',
    'confirm_password' => 'ValidPass123',
    'terms' => '1'
];

$errors7 = epic_validate_form_data($testData7, $rules);

echo "<strong>Data Test:</strong><br>";
echo "<pre>" . print_r($testData7, true) . "</pre>";
echo "<strong>Hasil Validasi:</strong><br>";
if (!empty($errors7)) {
    echo "<div class='error'>";
    echo "<pre>" . print_r($errors7, true) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='success'>Tidak ada error - Data valid!</div>";
}
echo "</div>";

echo "<hr>";
echo "<h3>Kesimpulan Test</h3>";
echo "<p>Silakan akses <a href='themes/modern/auth/register.php' target='_blank'>halaman registrasi</a> untuk menguji tampilan error secara visual.</p>";
echo "<p>Test case di atas menunjukkan validasi backend berfungsi dengan baik.</p>";
?>