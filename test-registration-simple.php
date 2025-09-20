<?php
/**
 * Test Script: Verifikasi Registrasi Sederhana
 * Memastikan fungsi epic_register_user berfungsi dengan baik
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
            echo "   - Created At: " . $saved_user['created_at'] . "\n";
            
            // Test database integrity
            echo "\n--- Verifikasi Database ---\n";
            
            // Cek tabel epic_users
            $user_count = db()->selectOne("SELECT COUNT(*) as count FROM epic_users WHERE id = ?", [$user_id]);
            echo "epic_users: " . ($user_count['count'] > 0 ? '✅ Ada' : '❌ Tidak ada') . "\n";
            
            // Cleanup
            echo "\n--- Cleanup ---\n";
            try {
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
    
    // Cleanup jika ada error
    if (isset($user_id) && $user_id) {
        try {
            db()->query("DELETE FROM epic_users WHERE id = ?", [$user_id]);
            echo "🧹 Cleanup setelah error\n";
        } catch (Exception $cleanup_error) {
            // Silent cleanup
        }
    }
}

echo "\n=== TEST SELESAI ===\n";
?>