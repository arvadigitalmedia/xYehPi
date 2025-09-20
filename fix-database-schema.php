<?php
/**
 * Script untuk memperbaiki skema database Epic Hub
 * Menambahkan kolom dan tabel yang diperlukan
 */

require_once 'bootstrap.php';

echo "=== PERBAIKAN SKEMA DATABASE ===\n";
echo "Waktu: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = db();
    
    // 1. Cek dan perbaiki tabel epic_users
    echo "--- Memperbaiki tabel epic_users ---\n";
    
    // Cek apakah kolom email_verified ada
    $columns = $db->query("SHOW COLUMNS FROM epic_users LIKE 'email_verified'");
    if (empty($columns)) {
        $db->query("ALTER TABLE epic_users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER status");
        echo "✅ Kolom email_verified ditambahkan\n";
    } else {
        echo "✅ Kolom email_verified sudah ada\n";
    }
    
    // Cek apakah kolom marketing_consent ada
    $columns = $db->query("SHOW COLUMNS FROM epic_users LIKE 'marketing_consent'");
    if (empty($columns)) {
        $db->query("ALTER TABLE epic_users ADD COLUMN marketing_consent TINYINT(1) DEFAULT 0 AFTER email_verified");
        echo "✅ Kolom marketing_consent ditambahkan\n";
    } else {
        echo "✅ Kolom marketing_consent sudah ada\n";
    }
    
    // 2. Cek dan buat tabel epic_epi_error_logs
    echo "\n--- Memperbaiki tabel epic_epi_error_logs ---\n";
    
    $tables = $db->query("SHOW TABLES LIKE 'epic_epi_error_logs'");
    if (empty($tables)) {
        $db->query("
            CREATE TABLE epic_epi_error_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                correlation_id VARCHAR(36),
                level VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                context JSON,
                ip_address VARCHAR(45),
                user_agent TEXT,
                request_uri VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✅ Tabel epic_epi_error_logs dibuat\n";
    } else {
        echo "✅ Tabel epic_epi_error_logs sudah ada\n";
        
        // Cek kolom correlation_id
        $columns = $db->query("SHOW COLUMNS FROM epic_epi_error_logs LIKE 'correlation_id'");
        if (empty($columns)) {
            $db->query("ALTER TABLE epic_epi_error_logs ADD COLUMN correlation_id VARCHAR(36) AFTER id");
            echo "✅ Kolom correlation_id ditambahkan ke epic_epi_error_logs\n";
        }
    }
    
    // 3. Cek dan buat tabel epi_metrics
    echo "\n--- Memperbaiki tabel epi_metrics ---\n";
    
    $tables = $db->query("SHOW TABLES LIKE 'epi_metrics'");
    if (empty($tables)) {
        $db->query("
            CREATE TABLE epi_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_key VARCHAR(100) NOT NULL,
                value INT DEFAULT 0,
                date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_metric_date (metric_key, date)
            )
        ");
        echo "✅ Tabel epi_metrics dibuat\n";
    } else {
        echo "✅ Tabel epi_metrics sudah ada\n";
    }
    
    // 4. Test koneksi dan struktur
    echo "\n--- Test Struktur Database ---\n";
    
    // Test insert sederhana ke epic_users (tanpa id karena auto increment)
    $test_data = [
        'uuid' => 'test-' . uniqid(),
        'name' => 'Test User Schema',
        'email' => 'test_schema_' . time() . '@example.com',
        'password' => password_hash('test123', PASSWORD_DEFAULT),
        'phone' => '081234567890',
        'referral_code' => 'TEST' . rand(1000, 9999),
        'status' => 'pending',
        'role' => 'user',
        'email_verified' => 0,
        'marketing_consent' => 1
    ];
    
    try {
        $test_user_id = $db->insert('epic_users', $test_data);
        echo "✅ Test insert ke epic_users berhasil (ID: {$test_user_id})\n";
        
        // Cleanup test data
        $db->delete('epic_users', 'id = ?', [$test_user_id]);
        echo "✅ Test data dibersihkan\n";
    } catch (Exception $e) {
        echo "❌ Test insert ke epic_users gagal: " . $e->getMessage() . "\n";
    }
    
    // Test insert ke epi_metrics
    try {
        $metric_data = [
            'metric_key' => 'test_metric',
            'value' => 1,
            'date' => date('Y-m-d')
        ];
        // Insert langsung tanpa prefix karena tabel sudah bernama epi_metrics
        $stmt = $db->query("INSERT INTO epi_metrics (metric_key, value, date) VALUES (?, ?, ?)", 
                          [$metric_data['metric_key'], $metric_data['value'], $metric_data['date']]);
        $metric_id = $db->getConnection()->lastInsertId();
        echo "✅ Test insert ke epi_metrics berhasil (ID: {$metric_id})\n";
        
        // Cleanup test metric
        $db->query("DELETE FROM epi_metrics WHERE id = ?", [$metric_id]);
        echo "✅ Test metric dibersihkan\n";
    } catch (Exception $e) {
        echo "❌ Test epi_metrics gagal: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== PERBAIKAN SELESAI ===\n";
    echo "✅ Database schema sudah diperbaiki dan siap digunakan\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>