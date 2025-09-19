<?php
require_once 'bootstrap.php';

echo "<h2>Test Sistem Monitoring</h2>";

try {
    echo "<h3>1. Inisialisasi Tabel Monitoring</h3>";
    epic_init_monitoring_tables();
    echo "<p>✅ Tabel monitoring berhasil diinisialisasi</p>";
    
    echo "<h3>2. Test Fungsi Record Registration Metrics</h3>";
    $result = epic_record_registration_metrics('success', 1.5);
    echo "<p>" . ($result ? "✅" : "❌") . " Record registration metrics: " . ($result ? "berhasil" : "gagal") . "</p>";
    
    echo "<h3>3. Test Fungsi Record Registration Error</h3>";
    $result = epic_record_registration_error('validation_error', 'Test error message', ['field' => 'email']);
    echo "<p>" . ($result ? "✅" : "❌") . " Record registration error: " . ($result ? "berhasil" : "gagal") . "</p>";
    
    echo "<h3>4. Test Fungsi Record Performance</h3>";
    $result = epic_record_performance('registration', 2.1, 1024, 5);
    echo "<p>" . ($result ? "✅" : "❌") . " Record performance: " . ($result ? "berhasil" : "gagal") . "</p>";
    
    echo "<h3>5. Test Fungsi Get Registration Success Rate</h3>";
    $rate = epic_get_registration_success_rate();
    echo "<p>" . ($rate !== null ? "✅" : "❌") . " Get registration success rate: " . ($rate !== null ? $rate . "%" : "gagal") . "</p>";
    
    echo "<h3>6. Test Fungsi Get Performance Metrics</h3>";
    $metrics = epic_get_performance_metrics();
    echo "<p>" . ($metrics !== null ? "✅" : "❌") . " Get performance metrics: " . ($metrics !== null ? "berhasil" : "gagal") . "</p>";
    if ($metrics) {
        echo "<pre>" . print_r($metrics, true) . "</pre>";
    }
    
    echo "<h3>7. Test Fungsi Cleanup Monitoring Data</h3>";
    $result = epic_cleanup_monitoring_data(90);
    echo "<p>" . ($result ? "✅" : "❌") . " Cleanup monitoring data: " . ($result ? "berhasil" : "gagal") . "</p>";
    
    echo "<h3>8. Verifikasi Data di Tabel</h3>";
    $tables = ['epi_registration_metrics', 'epi_registration_errors', 'epi_performance_logs'];
    foreach ($tables as $table) {
        $count = db()->selectOne("SELECT COUNT(*) as count FROM `$table`")['count'];
        echo "<p>📊 Tabel $table: $count record</p>";
    }
    
    echo "<hr><h3>✅ Semua Test Monitoring Berhasil!</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p style='color: red'>Stack trace:</p>";
    echo "<pre style='color: red'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>