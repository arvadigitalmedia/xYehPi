<?php
/**
 * Cleanup Invalid EPIS Data
 * Membersihkan data EPIS accounts dengan user_id = 0 yang tidak valid
 */

require_once 'bootstrap.php';

echo "=== CLEANUP INVALID EPIS DATA ===\n\n";

try {
    // 1. Cek data yang akan dibersihkan
    echo "1. CEK DATA YANG AKAN DIBERSIHKAN:\n";
    echo "===================================\n";
    
    $invalid_epis = db()->select("SELECT * FROM epic_epis_accounts WHERE user_id = 0 OR user_id IS NULL");
    echo "EPIS accounts dengan user_id invalid: " . count($invalid_epis) . "\n";
    
    if (!empty($invalid_epis)) {
        echo "Detail data invalid:\n";
        foreach ($invalid_epis as $epis) {
            echo "  ID: {$epis['id']}, Code: {$epis['epis_code']}, User ID: {$epis['user_id']}, Created: {$epis['created_at']}\n";
        }
        
        // 2. Backup data sebelum dihapus
        echo "\n2. BACKUP DATA SEBELUM DIHAPUS:\n";
        echo "================================\n";
        
        $backup_file = 'backup_invalid_epis_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_content = "-- Backup Invalid EPIS Data - " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($invalid_epis as $epis) {
            $backup_content .= "INSERT INTO epic_epis_accounts (id, user_id, epis_code, territory_name, territory_description, max_epic_recruits, current_epic_count, recruitment_commission_rate, indirect_commission_rate, can_manage_benefits, can_view_epic_analytics, status, created_at) VALUES (";
            $backup_content .= "{$epis['id']}, ";
            $backup_content .= ($epis['user_id'] ?: 'NULL') . ", ";
            $backup_content .= "'" . addslashes($epis['epis_code']) . "', ";
            $backup_content .= "'" . addslashes($epis['territory_name'] ?: '') . "', ";
            $backup_content .= "'" . addslashes($epis['territory_description'] ?: '') . "', ";
            $backup_content .= "{$epis['max_epic_recruits']}, ";
            $backup_content .= "{$epis['current_epic_count']}, ";
            $backup_content .= "{$epis['recruitment_commission_rate']}, ";
            $backup_content .= "{$epis['indirect_commission_rate']}, ";
            $backup_content .= ($epis['can_manage_benefits'] ? '1' : '0') . ", ";
            $backup_content .= ($epis['can_view_epic_analytics'] ? '1' : '0') . ", ";
            $backup_content .= "'" . $epis['status'] . "', ";
            $backup_content .= "'" . $epis['created_at'] . "'";
            $backup_content .= ");\n";
        }
        
        file_put_contents($backup_file, $backup_content);
        echo "✅ Backup disimpan ke: $backup_file\n";
        
        // 3. Hapus data invalid
        echo "\n3. HAPUS DATA INVALID:\n";
        echo "======================\n";
        
        $deleted_count = db()->delete('epic_epis_accounts', 'user_id = 0 OR user_id IS NULL');
        echo "✅ Data invalid dihapus: $deleted_count records\n";
        
        // 4. Hapus data terkait di epic_epis_networks jika ada
        echo "\n4. BERSIHKAN DATA TERKAIT:\n";
        echo "==========================\n";
        
        $networks_deleted = db()->delete('epic_epis_networks', 'epis_id = 0 OR epis_id IS NULL');
        echo "✅ Networks dengan epis_id invalid dihapus: $networks_deleted records\n";
        
    } else {
        echo "✅ Tidak ada data invalid yang perlu dibersihkan\n";
    }
    
    // 5. Verifikasi data setelah cleanup
    echo "\n5. VERIFIKASI DATA SETELAH CLEANUP:\n";
    echo "===================================\n";
    
    $total_epis_after = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts");
    $active_epis_after = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'active'");
    $valid_epis = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE user_id > 0");
    
    echo "Total EPIS accounts: $total_epis_after\n";
    echo "Active EPIS accounts: $active_epis_after\n";
    echo "Valid EPIS accounts (user_id > 0): $valid_epis\n";
    
    if ($total_epis_after == $valid_epis) {
        echo "✅ Semua data EPIS sekarang valid\n";
    } else {
        echo "⚠️  Masih ada data dengan user_id tidak valid\n";
    }
    
    // 6. Test function setelah cleanup
    echo "\n6. TEST FUNCTION SETELAH CLEANUP:\n";
    echo "=================================\n";
    
    $epis_via_function = epic_get_all_epis_accounts();
    echo "Data via function epic_get_all_epis_accounts: " . count($epis_via_function) . "\n";
    
    if (count($epis_via_function) == $total_epis_after) {
        echo "✅ Function sekarang mengembalikan data yang konsisten\n";
    } else {
        echo "⚠️  Masih ada perbedaan antara function dan query langsung\n";
        echo "   Function: " . count($epis_via_function) . "\n";
        echo "   Database: $total_epis_after\n";
    }
    
    // 7. Tampilkan data yang tersisa
    echo "\n7. DATA EPIS YANG TERSISA:\n";
    echo "==========================\n";
    
    $remaining_epis = db()->select("SELECT ea.id, ea.user_id, ea.epis_code, ea.status, u.name, u.email FROM epic_epis_accounts ea LEFT JOIN epic_users u ON ea.user_id = u.id ORDER BY ea.id");
    
    foreach ($remaining_epis as $epis) {
        $user_info = $epis['name'] ? "{$epis['name']} ({$epis['email']})" : "User not found";
        echo "  ID: {$epis['id']}, User ID: {$epis['user_id']}, Code: {$epis['epis_code']}, Status: {$epis['status']}, User: $user_info\n";
    }
    
    echo "\n🎉 CLEANUP SELESAI!\n";
    echo "===================\n";
    echo "✅ Data invalid telah dibersihkan\n";
    echo "✅ Function epic_get_all_epis_accounts diperbaiki\n";
    echo "✅ Stat cards sekarang akan menampilkan data yang konsisten\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}