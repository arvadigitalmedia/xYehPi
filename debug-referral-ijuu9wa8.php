<?php
require_once 'bootstrap.php';

echo "=== DEBUG REFERRAL CODE: IJUU9WA8 ===\n";

$referral_code = 'IJUU9WA8';

try {
    // 1. Cek apakah kode referral ada di database
    echo "\n1. CHECKING REFERRAL CODE IN DATABASE:\n";
    
    $stmt = $pdo->prepare("SELECT * FROM epi_users WHERE referral_code = ?");
    $stmt->execute([$referral_code]);
    $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($referrer) {
        echo "✅ Referral code found!\n";
        echo "   - User ID: " . $referrer['id'] . "\n";
        echo "   - Name: " . $referrer['name'] . "\n";
        echo "   - Email: " . $referrer['email'] . "\n";
        echo "   - Role: " . $referrer['role'] . "\n";
        echo "   - Status: " . $referrer['status'] . "\n";
        echo "   - Referral Code: " . $referrer['referral_code'] . "\n";
        echo "   - Created: " . $referrer['created_at'] . "\n";
    } else {
        echo "❌ Referral code NOT FOUND in database!\n";
        
        // Cek apakah ada kode yang mirip
        $stmt = $pdo->prepare("SELECT referral_code FROM epi_users WHERE referral_code LIKE ?");
        $stmt->execute(['%' . substr($referral_code, 0, 4) . '%']);
        $similar = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if ($similar) {
            echo "   Similar codes found: " . implode(', ', $similar) . "\n";
        }
    }
    
    // 2. Cek struktur tabel users
    echo "\n2. CHECKING TABLE STRUCTURE:\n";
    $stmt = $pdo->query("DESCRIBE epi_users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $has_referral_code = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'referral_code') {
            $has_referral_code = true;
            echo "✅ Column 'referral_code' exists\n";
            echo "   - Type: " . $column['Type'] . "\n";
            echo "   - Null: " . $column['Null'] . "\n";
            echo "   - Key: " . $column['Key'] . "\n";
            echo "   - Default: " . $column['Default'] . "\n";
            break;
        }
    }
    
    if (!$has_referral_code) {
        echo "❌ Column 'referral_code' NOT FOUND!\n";
    }
    
    // 3. Cek semua referral codes yang ada
    echo "\n3. ALL EXISTING REFERRAL CODES:\n";
    $stmt = $pdo->query("SELECT id, name, email, referral_code FROM epi_users WHERE referral_code IS NOT NULL AND referral_code != '' ORDER BY created_at DESC LIMIT 10");
    $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($codes) {
        foreach ($codes as $code) {
            echo "   - ID: {$code['id']}, Name: {$code['name']}, Code: {$code['referral_code']}\n";
        }
    } else {
        echo "   No referral codes found in database\n";
    }
    
    // 4. Test fungsi referral validation
    echo "\n4. TESTING REFERRAL VALIDATION FUNCTION:\n";
    
    // Include referral validator jika ada
    if (file_exists('core/referral-validator.php')) {
        require_once 'core/referral-validator.php';
        
        if (function_exists('epic_validate_referral_code')) {
            $validation_result = epic_validate_referral_code($referral_code);
            echo "   Function result: " . ($validation_result ? "VALID" : "INVALID") . "\n";
        } else {
            echo "   Function 'epic_validate_referral_code' not found\n";
        }
    } else {
        echo "   File 'core/referral-validator.php' not found\n";
    }
    
    // 5. Cek API endpoint referral
    echo "\n5. CHECKING REFERRAL API ENDPOINT:\n";
    
    if (file_exists('api/check-referral.php')) {
        echo "✅ API file exists: api/check-referral.php\n";
        
        // Simulasi request ke API
        $_GET['code'] = $referral_code;
        ob_start();
        include 'api/check-referral.php';
        $api_response = ob_get_clean();
        
        echo "   API Response: " . $api_response . "\n";
    } else {
        echo "❌ API file NOT FOUND: api/check-referral.php\n";
    }
    
    // 6. Cek form registrasi
    echo "\n6. CHECKING REGISTRATION FORM:\n";
    
    $registration_files = [
        'index.php',
        'register.php',
        'themes/modern/auth/register.php'
    ];
    
    foreach ($registration_files as $file) {
        if (file_exists($file)) {
            echo "✅ Found: $file\n";
            $content = file_get_contents($file);
            
            // Cek apakah ada handling referral code
            if (strpos($content, 'referral') !== false) {
                echo "   - Contains 'referral' keyword\n";
            }
            
            // Cek apakah ada EPIS Supervisor field
            if (strpos($content, 'epis_supervisor') !== false || strpos($content, 'supervisor') !== false) {
                echo "   - Contains 'supervisor' field\n";
            }
        }
    }
    
    echo "\n=== DEBUG COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}
?>