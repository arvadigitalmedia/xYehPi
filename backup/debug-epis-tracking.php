<?php
/**
 * Debug EPIS Tracking Function
 */

require_once 'bootstrap.php';
require_once 'core/sponsor.php';

$referral_code = '03KIPMLQ';

echo "<h2>🔍 Debug EPIS Tracking untuk Kode: $referral_code</h2>";

try {
    // Step 1: Get referrer by affiliate code
    echo "<h3>1. Cek epic_get_user_by_affiliate_code</h3>";
    $referrer = epic_get_user_by_affiliate_code($referral_code);
    if ($referrer) {
        echo "✅ Ditemukan via affiliate_code:<br><pre>" . print_r($referrer, true) . "</pre>";
    } else {
        echo "❌ Tidak ditemukan via affiliate_code<br>";
        
        // Fallback to referral_code
        echo "<h3>2. Fallback ke epic_get_user_by_referral_code</h3>";
        $referrer = epic_get_user_by_referral_code($referral_code);
        if ($referrer) {
            echo "✅ Ditemukan via referral_code:<br><pre>" . print_r($referrer, true) . "</pre>";
        } else {
            echo "❌ Tidak ditemukan via referral_code<br>";
            exit;
        }
    }
    
    // Step 2: Check EPIC status and EPIS supervisor
    echo "<h3>3. Cek Status EPIC dan EPIS Supervisor</h3>";
    echo "Status: " . $referrer['status'] . "<br>";
    echo "EPIS Supervisor ID: " . ($referrer['epis_supervisor_id'] ?? 'kosong') . "<br>";
    
    if ($referrer['status'] === 'epic' && !empty($referrer['epis_supervisor_id'])) {
        echo "✅ User adalah EPIC dengan EPIS supervisor<br>";
        
        // Step 3: Get EPIS supervisor
        echo "<h3>4. Cek epic_get_user untuk EPIS Supervisor</h3>";
        $epis_supervisor = epic_get_user($referrer['epis_supervisor_id']);
        if ($epis_supervisor) {
            echo "✅ EPIS Supervisor ditemukan:<br><pre>" . print_r($epis_supervisor, true) . "</pre>";
            
            if ($epis_supervisor['status'] === 'epis') {
                echo "✅ Status supervisor adalah EPIS<br>";
                
                // Step 4: Validate connection
                echo "<h3>5. Cek epic_validate_epic_epis_connection</h3>";
                $connection_validation = epic_validate_epic_epis_connection(
                    $referrer['id'], 
                    $referrer['epis_supervisor_id']
                );
                echo "Hasil validasi koneksi:<br><pre>" . print_r($connection_validation, true) . "</pre>";
                
                if ($connection_validation['valid']) {
                    echo "✅ Koneksi EPIC-EPIS valid<br>";
                    
                    // Step 5: Get EPIS account
                    echo "<h3>6. Cek epic_get_epis_account</h3>";
                    $epis_account = epic_get_epis_account($referrer['epis_supervisor_id']);
                    if ($epis_account) {
                        echo "✅ EPIS Account ditemukan:<br><pre>" . print_r($epis_account, true) . "</pre>";
                        
                        // Step 6: Check auto-assignment availability
                        echo "<h3>7. Cek Auto-Assignment Availability</h3>";
                        $auto_assignment_available = (
                            $epis_account && 
                            $epis_account['status'] === 'active' &&
                            ($epis_account['max_epic_recruits'] == 0 || 
                             $epis_account['current_epic_count'] < $epis_account['max_epic_recruits'])
                        );
                        echo "Auto-assignment available: " . ($auto_assignment_available ? 'YA' : 'TIDAK') . "<br>";
                        echo "EPIS Account Status: " . $epis_account['status'] . "<br>";
                        echo "Max EPIC Recruits: " . $epis_account['max_epic_recruits'] . "<br>";
                        echo "Current EPIC Count: " . $epis_account['current_epic_count'] . "<br>";
                        
                    } else {
                        echo "❌ EPIS Account tidak ditemukan<br>";
                    }
                } else {
                    echo "❌ Koneksi EPIC-EPIS tidak valid<br>";
                }
            } else {
                echo "❌ Status supervisor bukan EPIS: " . $epis_supervisor['status'] . "<br>";
            }
        } else {
            echo "❌ EPIS Supervisor tidak ditemukan<br>";
        }
    } else {
        echo "❌ User bukan EPIC atau tidak memiliki EPIS supervisor<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}