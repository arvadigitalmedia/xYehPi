<?php
/**
 * Debug Stats Synchronization
 * Mengecek sinkronisasi data antara stat cards dengan data tabel sebenarnya
 */

require_once 'bootstrap.php';

echo "=== DEBUG STATS SYNCHRONIZATION ===\n\n";

try {
    // 1. Cek data langsung dari database
    echo "1. DATA LANGSUNG DARI DATABASE:\n";
    echo "================================\n";
    
    $total_epis_direct = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts");
    $active_epis_direct = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'active'");
    $suspended_epis_direct = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'suspended'");
    $terminated_epis_direct = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'terminated'");
    
    echo "Total EPIS Accounts: $total_epis_direct\n";
    echo "Active EPIS: $active_epis_direct\n";
    echo "Suspended EPIS: $suspended_epis_direct\n";
    echo "Terminated EPIS: $terminated_epis_direct\n";
    
    // 2. Cek data melalui function epic_get_all_epis_accounts
    echo "\n2. DATA MELALUI FUNCTION epic_get_all_epis_accounts:\n";
    echo "===================================================\n";
    
    if (function_exists('epic_get_all_epis_accounts')) {
        $all_epis = epic_get_all_epis_accounts();
        $total_via_function = count($all_epis);
        
        $active_via_function = 0;
        $suspended_via_function = 0;
        $terminated_via_function = 0;
        
        foreach ($all_epis as $epis) {
            switch ($epis['status']) {
                case 'active':
                    $active_via_function++;
                    break;
                case 'suspended':
                    $suspended_via_function++;
                    break;
                case 'terminated':
                    $terminated_via_function++;
                    break;
            }
        }
        
        echo "Total EPIS Accounts: $total_via_function\n";
        echo "Active EPIS: $active_via_function\n";
        echo "Suspended EPIS: $suspended_via_function\n";
        echo "Terminated EPIS: $terminated_via_function\n";
    } else {
        echo "❌ Function epic_get_all_epis_accounts tidak ditemukan\n";
    }
    
    // 3. Cek stats yang digunakan di admin panel
    echo "\n3. STATS YANG DIGUNAKAN DI ADMIN PANEL:\n";
    echo "======================================\n";
    
    $stats = [
        'total_epis' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts"),
        'active_epis' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'active'"),
        'suspended_epis' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'suspended'"),
        'total_epic_in_networks' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_networks WHERE status = 'active'"),
        'total_commissions' => db()->selectValue("SELECT COALESCE(SUM(total_commissions_earned), 0) FROM epic_epis_networks")
    ];
    
    echo "Stats Array:\n";
    foreach ($stats as $key => $value) {
        echo "  $key: $value\n";
    }
    
    // 4. Cek tabel epic_epis_networks (jika ada)
    echo "\n4. CEK TABEL epic_epis_networks:\n";
    echo "================================\n";
    
    $networks_exists = db()->selectValue("SHOW TABLES LIKE 'epic_epis_networks'");
    if ($networks_exists) {
        $networks_count = db()->selectValue("SELECT COUNT(*) FROM epic_epis_networks");
        $networks_active = db()->selectValue("SELECT COUNT(*) FROM epic_epis_networks WHERE status = 'active'");
        echo "Tabel epic_epis_networks ada\n";
        echo "Total records: $networks_count\n";
        echo "Active records: $networks_active\n";
        
        // Sample data
        $sample_networks = db()->select("SELECT * FROM epic_epis_networks LIMIT 3");
        echo "Sample data:\n";
        foreach ($sample_networks as $network) {
            echo "  ID: {$network['id']}, Status: {$network['status']}\n";
        }
    } else {
        echo "❌ Tabel epic_epis_networks tidak ada\n";
    }
    
    // 5. Detail data EPIS accounts
    echo "\n5. DETAIL DATA EPIS ACCOUNTS:\n";
    echo "=============================\n";
    
    $epis_details = db()->select("SELECT id, user_id, epis_code, status, created_at FROM epic_epis_accounts ORDER BY id");
    echo "Detail semua EPIS accounts:\n";
    foreach ($epis_details as $epis) {
        echo "  ID: {$epis['id']}, User ID: {$epis['user_id']}, Code: {$epis['epis_code']}, Status: {$epis['status']}, Created: {$epis['created_at']}\n";
    }
    
    // 6. Analisis perbedaan
    echo "\n6. ANALISIS PERBEDAAN:\n";
    echo "======================\n";
    
    if (function_exists('epic_get_all_epis_accounts')) {
        if ($total_epis_direct != $total_via_function) {
            echo "❌ PERBEDAAN DITEMUKAN!\n";
            echo "   Database langsung: $total_epis_direct\n";
            echo "   Via function: $total_via_function\n";
            echo "   Selisih: " . abs($total_epis_direct - $total_via_function) . "\n";
        } else {
            echo "✅ Total count konsisten\n";
        }
        
        if ($active_epis_direct != $active_via_function) {
            echo "❌ PERBEDAAN ACTIVE COUNT!\n";
            echo "   Database langsung: $active_epis_direct\n";
            echo "   Via function: $active_via_function\n";
            echo "   Selisih: " . abs($active_epis_direct - $active_via_function) . "\n";
        } else {
            echo "✅ Active count konsisten\n";
        }
    }
    
    // 7. Rekomendasi perbaikan
    echo "\n7. REKOMENDASI PERBAIKAN:\n";
    echo "=========================\n";
    
    if (!$networks_exists) {
        echo "⚠️  Tabel epic_epis_networks tidak ada, stats 'total_epic_in_networks' akan error\n";
        echo "   Solusi: Buat tabel atau ubah query stats\n";
    }
    
    if ($stats['total_epic_in_networks'] === null || $stats['total_epic_in_networks'] === false) {
        echo "⚠️  Query total_epic_in_networks menghasilkan null/false\n";
        echo "   Solusi: Perbaiki query atau berikan default value\n";
    }
    
    if ($stats['total_commissions'] === null || $stats['total_commissions'] === false) {
        echo "⚠️  Query total_commissions menghasilkan null/false\n";
        echo "   Solusi: Perbaiki query atau berikan default value\n";
    }
    
    echo "\n🎯 KESIMPULAN:\n";
    echo "==============\n";
    echo "Data yang ditampilkan di tabel seharusnya: $total_epis_direct total, $active_epis_direct active\n";
    echo "Pastikan stat cards menampilkan angka yang sama!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}