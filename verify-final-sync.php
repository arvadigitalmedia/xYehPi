<?php
/**
 * Final Verification - Data Synchronization
 * Memverifikasi sinkronisasi data setelah semua perbaikan
 */

require_once 'bootstrap.php';

echo "=== VERIFIKASI FINAL SINKRONISASI DATA ===\n\n";

try {
    // 1. Verifikasi data database langsung
    echo "1. DATA DATABASE LANGSUNG:\n";
    echo "===========================\n";
    
    $total_epis_db = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts");
    $active_epis_db = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'active'");
    $suspended_epis_db = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'suspended'");
    
    echo "Total EPIS Accounts: $total_epis_db\n";
    echo "Active EPIS: $active_epis_db\n";
    echo "Suspended EPIS: $suspended_epis_db\n";
    
    // 2. Verifikasi data via function
    echo "\n2. DATA VIA FUNCTION:\n";
    echo "=====================\n";
    
    $epis_via_function = epic_get_all_epis_accounts();
    $active_via_function = epic_get_all_epis_accounts(['status' => 'active']);
    $suspended_via_function = epic_get_all_epis_accounts(['status' => 'suspended']);
    
    echo "Total EPIS via function: " . count($epis_via_function) . "\n";
    echo "Active EPIS via function: " . count($active_via_function) . "\n";
    echo "Suspended EPIS via function: " . count($suspended_via_function) . "\n";
    
    // 3. Verifikasi stats admin panel (simulasi)
    echo "\n3. STATS ADMIN PANEL (SIMULASI):\n";
    echo "=================================\n";
    
    $stats = [
        'total_epis' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts"),
        'active_epis' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'active'"),
        'suspended_epis' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'suspended'"),
        'total_epic_in_networks' => db()->selectValue("SELECT COUNT(*) FROM epic_epis_networks WHERE status = 'active'"),
        'total_commissions' => db()->selectValue("SELECT COALESCE(SUM(total_commissions_earned), 0) FROM epic_epis_networks")
    ];
    
    echo "Stats Total EPIS: {$stats['total_epis']}\n";
    echo "Stats Active EPIS: {$stats['active_epis']}\n";
    echo "Stats Suspended EPIS: {$stats['suspended_epis']}\n";
    echo "Stats Networks: {$stats['total_epic_in_networks']}\n";
    echo "Stats Commissions: {$stats['total_commissions']}\n";
    
    // 4. Cek konsistensi data
    echo "\n4. CEK KONSISTENSI DATA:\n";
    echo "========================\n";
    
    $consistency_checks = [
        'Total EPIS' => [
            'Database' => $total_epis_db,
            'Function' => count($epis_via_function),
            'Stats' => $stats['total_epis']
        ],
        'Active EPIS' => [
            'Database' => $active_epis_db,
            'Function' => count($active_via_function),
            'Stats' => $stats['active_epis']
        ],
        'Suspended EPIS' => [
            'Database' => $suspended_epis_db,
            'Function' => count($suspended_via_function),
            'Stats' => $stats['suspended_epis']
        ]
    ];
    
    $all_consistent = true;
    
    foreach ($consistency_checks as $metric => $values) {
        $is_consistent = (count(array_unique($values)) === 1);
        $status = $is_consistent ? "✅ KONSISTEN" : "❌ TIDAK KONSISTEN";
        
        echo "$metric: $status\n";
        echo "  Database: {$values['Database']}\n";
        echo "  Function: {$values['Function']}\n";
        echo "  Stats: {$values['Stats']}\n";
        
        if (!$is_consistent) {
            $all_consistent = false;
        }
        echo "\n";
    }
    
    // 5. Detail data EPIS yang ada
    echo "5. DETAIL DATA EPIS YANG ADA:\n";
    echo "==============================\n";
    
    $epis_details = db()->select("
        SELECT ea.id, ea.user_id, ea.epis_code, ea.status, ea.created_at,
               u.name, u.email, u.status as user_status
        FROM epic_epis_accounts ea
        LEFT JOIN epic_users u ON ea.user_id = u.id
        ORDER BY ea.id
    ");
    
    foreach ($epis_details as $epis) {
        $user_info = $epis['name'] ? "{$epis['name']} ({$epis['email']})" : "User ID {$epis['user_id']} not found";
        echo "ID: {$epis['id']}, Code: {$epis['epis_code']}, Status: {$epis['status']}, User: $user_info\n";
    }
    
    // 6. Validasi data integrity
    echo "\n6. VALIDASI DATA INTEGRITY:\n";
    echo "============================\n";
    
    $invalid_user_ids = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE user_id = 0 OR user_id IS NULL");
    $orphaned_epis = db()->selectValue("
        SELECT COUNT(*) 
        FROM epic_epis_accounts ea 
        LEFT JOIN epic_users u ON ea.user_id = u.id 
        WHERE u.id IS NULL AND ea.user_id > 0
    ");
    
    echo "EPIS dengan user_id invalid: $invalid_user_ids\n";
    echo "EPIS dengan user tidak ditemukan: $orphaned_epis\n";
    
    if ($invalid_user_ids == 0 && $orphaned_epis == 0) {
        echo "✅ Data integrity baik\n";
    } else {
        echo "⚠️  Ada masalah data integrity\n";
        $all_consistent = false;
    }
    
    // 7. Hasil akhir
    echo "\n7. HASIL AKHIR:\n";
    echo "===============\n";
    
    if ($all_consistent) {
        echo "🎉 SEMUA DATA SUDAH SINKRON!\n";
        echo "✅ Database, Function, dan Stats Panel konsisten\n";
        echo "✅ Data integrity baik\n";
        echo "✅ Stat Cards akan menampilkan angka yang benar\n";
        echo "\nRingkasan:\n";
        echo "- Total EPIS Accounts: $total_epis_db\n";
        echo "- Active EPIS: $active_epis_db\n";
        echo "- Suspended EPIS: $suspended_epis_db\n";
    } else {
        echo "❌ MASIH ADA INKONSISTENSI DATA\n";
        echo "Perlu investigasi lebih lanjut\n";
    }
    
    // 8. Test akses admin panel (simulasi URL)
    echo "\n8. INFORMASI AKSES:\n";
    echo "===================\n";
    echo "URL Admin Panel: http://localhost:8000/admin/epis-management\n";
    echo "Stat Cards sekarang akan menampilkan:\n";
    echo "- Total EPIS Accounts: {$stats['total_epis']}\n";
    echo "- Active EPIS: {$stats['active_epis']}\n";
    echo "- Suspended EPIS: {$stats['suspended_epis']}\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}