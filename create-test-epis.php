<?php
/**
 * Script untuk membuat EPIS Supervisor test account
 */

require_once 'bootstrap.php';

echo "=== MEMBUAT EPIS SUPERVISOR TEST ===\n";
echo "Waktu: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Data EPIS supervisor
    $epis_data = [
        'name' => 'EPIS Supervisor Test',
        'email' => 'epis.test@example.com',
        'password' => 'epis123',
        'phone' => '081234567890',
        'status' => 'epis',
        'role' => 'user'
    ];
    
    // Cek apakah user sudah ada
    $existing_user = epic_get_user_by_email($epis_data['email']);
    
    if ($existing_user) {
        echo "✅ EPIS user sudah ada dengan ID: " . $existing_user['id'] . "\n";
        $user_id = $existing_user['id'];
    } else {
        // Buat user EPIS
        echo "--- Membuat User EPIS ---\n";
        $user_id = epic_create_user($epis_data);
        echo "✅ User EPIS dibuat dengan ID: {$user_id}\n";
    }
    
    // Cek apakah EPIS account sudah ada
    $existing_epis = db()->selectOne("SELECT id FROM epic_epis_accounts WHERE user_id = ?", [$user_id]);
    
    if ($existing_epis) {
        echo "✅ EPIS account sudah ada dengan ID: " . $existing_epis['id'] . "\n";
    } else {
        // Buat EPIS account
        echo "--- Membuat EPIS Account ---\n";
        $epis_account_data = [
            'user_id' => $user_id,
            'epis_code' => 'EPIS-TEST-001',
            'territory_name' => 'Test Territory',
            'max_epic_recruits' => 100,
            'current_epic_count' => 0,
            'recruitment_commission_rate' => 10.00,
            'indirect_commission_rate' => 5.00,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $epis_id = db()->insert('epic_epis_accounts', $epis_account_data);
        echo "✅ EPIS account dibuat dengan ID: {$epis_id}\n";
    }
    
    // Update user status menjadi epis
    db()->update('epic_users', [
        'status' => 'epis',
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$user_id]);
    
    echo "\n=== EPIS SUPERVISOR BERHASIL DIBUAT ===\n";
    echo "Email: " . $epis_data['email'] . "\n";
    echo "Password: " . $epis_data['password'] . "\n";
    echo "User ID: {$user_id}\n";
    echo "Status: EPIS Supervisor\n";
    echo "Territory: Test Territory\n";
    echo "Max Recruits: 100\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== SELESAI ===\n";
?>