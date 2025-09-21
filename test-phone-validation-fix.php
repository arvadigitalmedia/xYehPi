<?php
echo "=== PHONE VALIDATION FIX TEST ===\n\n";

// Test cases
$test_cases = [
    ['85860437327', '6285860437327', 'Nomor tanpa kode negara'],
    ['081234567890', '6281234567890', 'Nomor dengan 0 di depan'],
    ['6285860437327', '6285860437327', 'Nomor dengan kode negara 62'],
    ['60123456789', '60123456789', 'Nomor Malaysia'],
    ['+62 858-6043-7327', '6285860437327', 'Nomor dengan format'],
    ['(+62) 858 6043 7327', '6285860437327', 'Nomor dengan spasi dan kurung'],
    ['123', 'ERROR: Nomor telepon harus terdiri dari 10-15 digit.', 'Nomor terlalu pendek'],
    ['12345678901234567890', 'ERROR: Nomor telepon harus terdiri dari 10-15 digit.', 'Nomor terlalu panjang'],
    ['99123456789', 'ERROR: Nomor telepon harus dimulai dengan kode negara yang valid (contoh: 62 untuk Indonesia).', 'Kode negara tidak valid']
];

function validatePhone($phone) {
    // Hapus semua karakter non-digit
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Cek panjang nomor
    if (strlen($phone) < 10 || strlen($phone) > 15) {
        return "ERROR: Nomor telepon harus terdiri dari 10-15 digit.";
    }
    
    // Daftar kode negara yang valid
    $valid_country_codes = ['1', '7', '20', '27', '30', '31', '32', '33', '34', '36', '39', '40', '41', '43', '44', '45', '46', '47', '48', '49', '51', '52', '53', '54', '55', '56', '57', '58', '60', '61', '62', '63', '64', '65', '66', '81', '82', '84', '86', '90', '91', '92', '93', '94', '95', '98', '212', '213', '216', '218', '220', '221', '222', '223', '224', '225', '226', '227', '228', '229', '230', '231', '232', '233', '234', '235', '236', '237', '238', '239', '240', '241', '242', '243', '244', '245', '246', '248', '249', '250', '251', '252', '253', '254', '255', '256', '257', '258', '260', '261', '262', '263', '264', '265', '266', '267', '268', '269', '290', '291', '297', '298', '299', '350', '351', '352', '353', '354', '355', '356', '357', '358', '359', '370', '371', '372', '373', '374', '375', '376', '377', '378', '380', '381', '382', '383', '385', '386', '387', '389', '420', '421', '423', '500', '501', '502', '503', '504', '505', '506', '507', '508', '509', '590', '591', '592', '593', '594', '595', '596', '597', '598', '599', '670', '672', '673', '674', '675', '676', '677', '678', '679', '680', '681', '682', '683', '684', '685', '686', '687', '688', '689', '690', '691', '692', '850', '852', '853', '855', '856', '880', '886', '960', '961', '962', '963', '964', '965', '966', '967', '968', '970', '971', '972', '973', '974', '975', '976', '977', '992', '993', '994', '995', '996', '998'];
    
    // Cek apakah nomor sudah memiliki kode negara yang valid
    $has_valid_country_code = false;
    foreach ($valid_country_codes as $code) {
        if (strpos($phone, $code) === 0) {
            $has_valid_country_code = true;
            break;
        }
    }
    
    // Jika belum ada kode negara valid, cek apakah ini nomor lokal Indonesia
    if (!$has_valid_country_code) {
        // Cek apakah ini nomor lokal Indonesia (dimulai dengan 0 atau 8)
        $is_indonesian_local = false;
        if (preg_match('/^[08]/', $phone)) {
            $is_indonesian_local = true;
        }
        
        // Cek pola yang jelas invalid (00 prefix atau 4+ digit country code)
        $invalid_patterns = ['/^00/', '/^[0-9]{4,}$/'];
        $has_invalid_pattern = false;
        foreach ($invalid_patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                $has_invalid_pattern = true;
                break;
            }
        }
        
        // Cek apakah dimulai dengan kode negara invalid (2-3 digit yang bukan valid)
        $possible_invalid_code = false;
        if (!$is_indonesian_local && preg_match('/^([0-9]{2,3})/', $phone, $matches)) {
            $potential_code = $matches[1];
            // Jika 2-3 digit pertama bukan kode negara valid, anggap invalid
            if (!in_array($potential_code, $valid_country_codes)) {
                $possible_invalid_code = true;
            }
        }
        
        // Auto-add prefix Indonesia jika nomor lokal atau tidak ada pola invalid
        if ($is_indonesian_local || (!$has_invalid_pattern && !$possible_invalid_code)) {
            $phone = '62' . ltrim($phone, '0'); // Hapus 0 di depan jika ada
            $has_valid_country_code = true;
        }
    }
    
    // Validasi final - hanya tolak jika benar-benar invalid
    if (!$has_valid_country_code) {
        return "ERROR: Nomor telepon harus dimulai dengan kode negara yang valid (contoh: 62 untuk Indonesia).";
    }
    
    return $phone;
}

echo "Testing phone validation logic...\n\n";

$passed = 0;
$total = count($test_cases);

foreach ($test_cases as $index => $test) {
    $input = $test[0];
    $expected = $test[1];
    $description = $test[2];
    
    echo "Test " . ($index + 1) . ": $description\n";
    echo "  Input:    '$input'\n";
    echo "  Expected: '$expected'\n";
    
    try {
        $result = validatePhone($input);
        echo "  Result:   '$result'\n";
        
        if ($result === $expected) {
            echo "  Status:   ✓ PASS\n";
            $passed++;
        } else {
            echo "  Status:   ✗ FAIL\n";
        }
    } catch (Exception $e) {
        $result = "ERROR: " . $e->getMessage();
        echo "  Result:   '$result'\n";
        
        if (strpos($expected, 'ERROR') === 0) {
            echo "  Status:   ✓ PASS\n";
            $passed++;
        } else {
            echo "  Status:   ✗ FAIL\n";
        }
    }
    
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "Passed: $passed/$total tests\n";
echo "Success Rate: " . round(($passed / $total) * 100, 1) . "%\n";

if ($passed === $total) {
    echo "🎉 All tests passed!\n";
} else {
    echo "⚠️  Some tests failed. Please review the validation logic.\n";
}

echo "\n=== PHONE VALIDATION TEST COMPLETED ===\n";
?>