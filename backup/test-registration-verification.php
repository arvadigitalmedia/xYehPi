<?php
/**
 * Test Script: Verifikasi Registrasi End-to-End (Simple)
 * Memastikan setiap pengguna yang registrasi berhasil tercatat di database
 */

require_once 'bootstrap.php';

echo "=== TEST REGISTRASI EPIC HUB ===\n";
echo "Waktu: " . date('Y-m-d H:i:s') . "\n\n";

// Test data sederhana
$test_email = 'testuser_' . time() . '@example.com';
$user_data = [
    'name' => 'Test User Registration',
    'email' => $test_email,
    'phone' => '081234567890',
    'password' => 'password123',
    'referral_code' => '',
    'marketing' => true
];

echo "--- Test Registrasi ---\n";
echo "Email: " . $test_email . "\n";

try {
    // Test registrasi
    $start_time = microtime(true);
    $user_id = epic_register_user($user_data);
    $end_time = microtime(true);
    
    $processing_time = round(($end_time - $start_time) * 1000, 2);
    
    if ($user_id) {
        echo "✅ BERHASIL - User ID: {$user_id}\n";
        echo "⏱️  Waktu proses: {$processing_time}ms\n";
        
        // Verifikasi data tersimpan di database
        $saved_user = epic_get_user($user_id);
        if ($saved_user) {
            echo "✅ Data tersimpan di database\n";
            echo "   - UUID: " . $saved_user['uuid'] . "\n";
            echo "   - Referral Code: " . $saved_user['referral_code'] . "\n";
            echo "   - Status: " . $saved_user['status'] . "\n";
            echo "   - Email Verified: " . ($saved_user['email_verified'] ? 'Ya' : 'Belum') . "\n";
            echo "   - Created At: " . $saved_user['created_at'] . "\n";
            
            // Cek email confirmation session
            if (isset($_SESSION['epic_email_confirmation'])) {
                $email_info = $_SESSION['epic_email_confirmation'];
                echo "   - Email konfirmasi: " . ($email_info['email_sent'] ? 'Terkirim' : 'Gagal') . "\n";
                if (!$email_info['email_sent']) {
                    echo "     Error: " . $email_info['message'] . "\n";
                }
            }
            
            // Test database integrity
            echo "\n--- Verifikasi Database ---\n";
            
            // Cek tabel epic_users
            $user_count = db()->selectOne("SELECT COUNT(*) as count FROM epic_users WHERE id = ?", [$user_id]);
            echo "epic_users: " . ($user_count['count'] > 0 ? '✅ Ada' : '❌ Tidak ada') . "\n";
            
            // Cek tabel epic_sponsors
            try {
                $sponsor_count = db()->selectOne("SELECT COUNT(*) as count FROM epic_sponsors WHERE user_id = ?", [$user_id]);
                echo "epic_sponsors: " . ($sponsor_count['count'] > 0 ? '✅ Ada' : '⚠️ Tidak ada') . "\n";
            } catch (Exception $e) {
                echo "epic_sponsors: ⚠️ Error atau tabel tidak ada\n";
            }
            
            // Cek tabel epic_activities
            try {
                $activity_count = db()->selectOne("SELECT COUNT(*) as count FROM epic_activities WHERE user_id = ?", [$user_id]);
                echo "epic_activities: " . ($activity_count['count'] > 0 ? '✅ Ada (' . $activity_count['count'] . ' record)' : '⚠️ Tidak ada') . "\n";
            } catch (Exception $e) {
                echo "epic_activities: ⚠️ Error atau tabel tidak ada\n";
            }
            
            // Test email confirmation token
            echo "\n--- Test Email Confirmation ---\n";
            try {
                $token = epic_generate_confirmation_token($user_id);
                echo "✅ Token generated: " . substr($token, 0, 10) . "...\n";
                
                $token_valid = epic_validate_confirmation_token($token);
                echo "✅ Token validation: " . ($token_valid ? 'Valid' : 'Invalid') . "\n";
            } catch (Exception $e) {
                echo "❌ Email confirmation error: " . $e->getMessage() . "\n";
            }
            
            // Cleanup
            echo "\n--- Cleanup ---\n";
            try {
                db()->query("DELETE FROM epic_activities WHERE user_id = ?", [$user_id]);
                db()->query("DELETE FROM epic_sponsors WHERE user_id = ?", [$user_id]);
                db()->query("DELETE FROM epic_users WHERE id = ?", [$user_id]);
                echo "✅ Test data cleaned up\n";
            } catch (Exception $e) {
                echo "⚠️ Cleanup error: " . $e->getMessage() . "\n";
            }
            
        } else {
            echo "❌ GAGAL - Data tidak ditemukan di database\n";
        }
    } else {
        echo "❌ GAGAL - User ID tidak dikembalikan\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST SELESAI ===\n";
?>