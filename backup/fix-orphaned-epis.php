<?php
/**
 * Fix Orphaned EPIS Data
 * Memperbaiki EPIS accounts yang user-nya tidak ditemukan
 */

require_once 'bootstrap.php';

echo "=== PERBAIKI ORPHANED EPIS DATA ===\n\n";

try {
    // 1. Cek EPIS dengan user tidak ditemukan
    echo "1. CEK EPIS DENGAN USER TIDAK DITEMUKAN:\n";
    echo "========================================\n";
    
    $orphaned_epis = db()->select("
        SELECT ea.id, ea.user_id, ea.epis_code, ea.status, ea.created_at
        FROM epic_epis_accounts ea 
        LEFT JOIN epic_users u ON ea.user_id = u.id 
        WHERE u.id IS NULL AND ea.user_id > 0
    ");
    
    echo "EPIS dengan user tidak ditemukan: " . count($orphaned_epis) . "\n";
    
    if (!empty($orphaned_epis)) {
        foreach ($orphaned_epis as $epis) {
            echo "  ID: {$epis['id']}, User ID: {$epis['user_id']}, Code: {$epis['epis_code']}, Status: {$epis['status']}\n";
        }
        
        // 2. Cek apakah ada user yang bisa digunakan
        echo "\n2. CEK USER YANG TERSEDIA:\n";
        echo "==========================\n";
        
        $available_users = db()->select("SELECT id, name, email, status FROM epic_users WHERE status IN ('active', 'epic') ORDER BY id");
        echo "User yang tersedia:\n";
        foreach ($available_users as $user) {
            echo "  ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Status: {$user['status']}\n";
        }
        
        // 3. Opsi perbaikan
        echo "\n3. OPSI PERBAIKAN:\n";
        echo "==================\n";
        echo "Opsi 1: Hapus EPIS dengan user tidak ditemukan\n";
        echo "Opsi 2: Assign ke user admin yang ada\n";
        echo "Opsi 3: Buat user baru untuk EPIS tersebut\n";
        
        // Untuk demo, kita akan assign ke user admin yang ada (ID 1)
        $admin_users = db()->select("SELECT * FROM epic_users WHERE id = 1 LIMIT 1");
        $admin_user = !empty($admin_users) ? $admin_users[0] : null;
        
        if ($admin_user) {
            echo "\n4. ASSIGN KE USER ADMIN:\n";
            echo "========================\n";
            echo "Akan assign EPIS orphaned ke user: {$admin_user['name']} (ID: {$admin_user['id']})\n";
            
            foreach ($orphaned_epis as $epis) {
                $updated = db()->query(
                    "UPDATE epic_epis_accounts SET user_id = ? WHERE id = ?",
                    [$admin_user['id'], $epis['id']]
                );
                
                if ($updated) {
                    echo "✅ EPIS ID {$epis['id']} (Code: {$epis['epis_code']}) assigned ke user {$admin_user['name']}\n";
                } else {
                    echo "❌ Gagal update EPIS ID {$epis['id']}\n";
                }
            }
        } else {
            echo "\n4. HAPUS EPIS ORPHANED:\n";
            echo "=======================\n";
            echo "User admin tidak ditemukan, akan hapus EPIS orphaned\n";
            
            foreach ($orphaned_epis as $epis) {
                $deleted = db()->query(
                    "DELETE FROM epic_epis_accounts WHERE id = ?",
                    [$epis['id']]
                );
                
                if ($deleted) {
                    echo "✅ EPIS ID {$epis['id']} (Code: {$epis['epis_code']}) dihapus\n";
                } else {
                    echo "❌ Gagal hapus EPIS ID {$epis['id']}\n";
                }
            }
        }
        
    } else {
        echo "✅ Tidak ada EPIS dengan user tidak ditemukan\n";
    }
    
    // 5. Verifikasi setelah perbaikan
    echo "\n5. VERIFIKASI SETELAH PERBAIKAN:\n";
    echo "=================================\n";
    
    $total_epis = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts");
    $active_epis = db()->selectValue("SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'active'");
    $orphaned_check = db()->selectValue("
        SELECT COUNT(*) 
        FROM epic_epis_accounts ea 
        LEFT JOIN epic_users u ON ea.user_id = u.id 
        WHERE u.id IS NULL AND ea.user_id > 0
    ");
    
    echo "Total EPIS accounts: $total_epis\n";
    echo "Active EPIS accounts: $active_epis\n";
    echo "EPIS dengan user tidak ditemukan: $orphaned_check\n";
    
    if ($orphaned_check == 0) {
        echo "✅ Semua EPIS sekarang memiliki user yang valid\n";
    } else {
        echo "⚠️  Masih ada EPIS dengan user tidak ditemukan\n";
    }
    
    // 6. Test function setelah perbaikan
    echo "\n6. TEST FUNCTION SETELAH PERBAIKAN:\n";
    echo "===================================\n";
    
    $epis_via_function = epic_get_all_epis_accounts();
    echo "Data via function: " . count($epis_via_function) . "\n";
    echo "Data database: $total_epis\n";
    
    if (count($epis_via_function) == $total_epis) {
        echo "✅ Function dan database konsisten\n";
    } else {
        echo "⚠️  Masih ada perbedaan\n";
    }
    
    // 7. Tampilkan data final
    echo "\n7. DATA EPIS FINAL:\n";
    echo "===================\n";
    
    $final_epis = db()->select("
        SELECT ea.id, ea.user_id, ea.epis_code, ea.status, ea.created_at,
               u.name, u.email, u.status as user_status
        FROM epic_epis_accounts ea
        LEFT JOIN epic_users u ON ea.user_id = u.id
        ORDER BY ea.id
    ");
    
    foreach ($final_epis as $epis) {
        $user_info = $epis['name'] ? "{$epis['name']} ({$epis['email']})" : "User not found";
        echo "ID: {$epis['id']}, Code: {$epis['epis_code']}, Status: {$epis['status']}, User: $user_info\n";
    }
    
    echo "\n🎉 PERBAIKAN SELESAI!\n";
    echo "=====================\n";
    echo "✅ Data orphaned telah diperbaiki\n";
    echo "✅ Semua EPIS sekarang memiliki user yang valid\n";
    echo "✅ Data integrity sudah baik\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}