<?php
/**
 * Test Profile Update Real Environment
 * Test update profile dengan nomor telepon existing di database
 */

require_once 'bootstrap.php';

echo "=== TEST PROFILE UPDATE REAL ENVIRONMENT ===\n\n";

try {
    // Koneksi database
    $pdo = new PDO("mysql:host=localhost;dbname=epic_hub", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connection established\n\n";
    
    // Cari user existing mana saja untuk testing
    echo "1. Mencari user existing...\n";
    $stmt = $pdo->prepare("SELECT id, name, email, phone FROM epic_users ORDER BY id ASC LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "   ERROR: Tidak ada user di database untuk testing!\n";
        echo "   Silakan buat user manual terlebih dahulu.\n";
        exit(1);
    }
    
    echo "   ✓ User ditemukan:\n";
    echo "     ID: {$user['id']}\n";
    echo "     Name: {$user['name']}\n";
    echo "     Email: {$user['email']}\n";
    echo "     Phone: {$user['phone']}\n\n";
    
    // Test cases untuk update nomor telepon
    $test_cases = [
        ['85860437327', '6285860437327', 'Nomor lokal tanpa kode negara'],
        ['081234567890', '6281234567890', 'Nomor dengan 0 di depan'],
        ['+62 858-6043-7327', '6285860437327', 'Nomor dengan format'],
        ['60123456789', '60123456789', 'Nomor Malaysia (valid)'],
    ];
    
    echo "2. Testing update nomor telepon...\n\n";
    
    foreach ($test_cases as $index => $test) {
        $input_phone = $test[0];
        $expected_phone = $test[1];
        $description = $test[2];
        
        echo "Test " . ($index + 1) . ": $description\n";
        echo "  Input: '$input_phone'\n";
        echo "  Expected: '$expected_phone'\n";
        
        try {
            // Simulasi validasi phone number (copy dari edit-profile.php)
            $phone = $input_phone;
            
            // Hapus semua karakter non-digit
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            // Cek panjang nomor
            if (strlen($phone) < 10 || strlen($phone) > 15) {
                throw new Exception('Nomor telepon harus terdiri dari 10-15 digit.');
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
                throw new Exception('Nomor telepon harus dimulai dengan kode negara yang valid (contoh: 62 untuk Indonesia).');
            }
            
            echo "  Processed: '$phone'\n";
            
            // Update database
            $stmt = $pdo->prepare("UPDATE epic_users SET phone = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$phone, $user['id']]);
            
            // Verifikasi update
            $stmt = $pdo->prepare("SELECT phone FROM epic_users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $updated_phone = $stmt->fetchColumn();
            
            echo "  Database: '$updated_phone'\n";
            
            if ($updated_phone === $expected_phone) {
                echo "  Status: ✓ PASS\n";
            } else {
                echo "  Status: ✗ FAIL (Expected: $expected_phone, Got: $updated_phone)\n";
            }
            
        } catch (Exception $e) {
            echo "  Error: " . $e->getMessage() . "\n";
            echo "  Status: ✗ FAIL\n";
        }
        
        echo "\n";
    }
    
    echo "3. Verifikasi final database...\n";
    $stmt = $pdo->prepare("SELECT id, name, email, phone, updated_at FROM epic_users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $final_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   Final user data:\n";
    echo "     ID: {$final_user['id']}\n";
    echo "     Name: {$final_user['name']}\n";
    echo "     Email: {$final_user['email']}\n";
    echo "     Phone: {$final_user['phone']}\n";
    echo "     Updated: {$final_user['updated_at']}\n";
    
    echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>