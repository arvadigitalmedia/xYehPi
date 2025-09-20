<?php
/**
 * Database Status Checker - Quick Fix Checklist
 * Memeriksa status database dan tabel sebelum menerapkan perbaikan
 */

require_once 'bootstrap.php';

echo "=== DATABASE STATUS CHECKER ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Test koneksi database
    $pdo = db();
    echo "✅ Database connection: OK\n";
    
    // Cek tabel epic_users
    echo "\n--- EPIC_USERS TABLE ---\n";
    $result = $pdo->query("DESCRIBE epic_users");
    if ($result) {
        echo "✅ Table epic_users exists\n";
        echo "Columns:\n";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Field']} ({$row['Type']}) {$row['Key']}\n";
        }
        
        // Cek indexes yang ada
        $indexes = $pdo->query("SHOW INDEX FROM epic_users")->fetchAll(PDO::FETCH_ASSOC);
        echo "\nCurrent Indexes:\n";
        foreach ($indexes as $index) {
            echo "  - {$index['Key_name']} on {$index['Column_name']}\n";
        }
        
        // Cek jumlah data
        $count = $pdo->query("SELECT COUNT(*) FROM epic_users")->fetchColumn();
        echo "Total records: {$count}\n";
        
    } else {
        echo "❌ Table epic_users not found\n";
    }
    
    // Cek tabel epic_epis_accounts
    echo "\n--- EPIC_EPIS_ACCOUNTS TABLE ---\n";
    $result = $pdo->query("DESCRIBE epic_epis_accounts");
    if ($result) {
        echo "✅ Table epic_epis_accounts exists\n";
        echo "Columns:\n";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Field']} ({$row['Type']}) {$row['Key']}\n";
        }
        
        // Cek indexes yang ada
        $indexes = $pdo->query("SHOW INDEX FROM epic_epis_accounts")->fetchAll(PDO::FETCH_ASSOC);
        echo "\nCurrent Indexes:\n";
        foreach ($indexes as $index) {
            echo "  - {$index['Key_name']} on {$index['Column_name']}\n";
        }
        
        // Cek jumlah data
        $count = $pdo->query("SELECT COUNT(*) FROM epic_epis_accounts")->fetchColumn();
        echo "Total records: {$count}\n";
        
    } else {
        echo "❌ Table epic_epis_accounts not found\n";
    }
    
    // Cek duplicate emails
    echo "\n--- DUPLICATE CHECK ---\n";
    $duplicates = $pdo->query("
        SELECT email, COUNT(*) as count 
        FROM epic_users 
        GROUP BY email 
        HAVING COUNT(*) > 1
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "✅ No duplicate emails found\n";
    } else {
        echo "⚠️  Found duplicate emails:\n";
        foreach ($duplicates as $dup) {
            echo "  - {$dup['email']} ({$dup['count']} times)\n";
        }
    }
    
    // Cek duplicate referral codes
    $duplicates = $pdo->query("
        SELECT referral_code, COUNT(*) as count 
        FROM epic_users 
        WHERE referral_code IS NOT NULL AND referral_code != ''
        GROUP BY referral_code 
        HAVING COUNT(*) > 1
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "✅ No duplicate referral codes found\n";
    } else {
        echo "⚠️  Found duplicate referral codes:\n";
        foreach ($duplicates as $dup) {
            echo "  - {$dup['referral_code']} ({$dup['count']} times)\n";
        }
    }
    
    // Cek orphaned EPIS accounts
    echo "\n--- ORPHANED EPIS CHECK ---\n";
    $orphaned = $pdo->query("
        SELECT ea.id, ea.user_id 
        FROM epic_epis_accounts ea 
        LEFT JOIN epic_users eu ON ea.user_id = eu.id 
        WHERE eu.id IS NULL
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orphaned)) {
        echo "✅ No orphaned EPIS accounts found\n";
    } else {
        echo "⚠️  Found orphaned EPIS accounts:\n";
        foreach ($orphaned as $orphan) {
            echo "  - EPIS ID {$orphan['id']} references non-existent user {$orphan['user_id']}\n";
        }
    }
    
    echo "\n=== STATUS CHECK COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>