<?php
/**
 * EPIC Hub Zoom Integration Migration Script
 * Menjalankan migrasi database untuk sistem event Zoom
 */

require_once __DIR__ . '/bootstrap.php';

// Cek apakah dijalankan dari command line atau browser dengan parameter
if (php_sapi_name() !== 'cli' && !isset($_GET['run'])) {
    die('Script ini hanya dapat dijalankan dari command line atau dengan parameter ?run=1');
}

echo "EPIC Hub Zoom Integration Migration\n";
echo "====================================\n\n";

try {
    // Baca file schema
    $schema_file = __DIR__ . '/zoom-integration-schema.sql';
    
    if (!file_exists($schema_file)) {
        throw new Exception('File schema tidak ditemukan: ' . $schema_file);
    }
    
    $sql_content = file_get_contents($schema_file);
    
    if (empty($sql_content)) {
        throw new Exception('File schema kosong atau tidak dapat dibaca');
    }
    
    echo "📁 Membaca file schema: zoom-integration-schema.sql\n";
    
    // Pisahkan SQL statements
    $statements = explode(';', $sql_content);
    $executed = 0;
    $errors = 0;
    
    echo "🔄 Memulai eksekusi migrasi...\n\n";
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Skip empty statements dan comments
        if (empty($statement) || 
            strpos($statement, '--') === 0 || 
            strpos($statement, '/*') === 0) {
            continue;
        }
        
        try {
            // Eksekusi statement
            db()->query($statement);
            $executed++;
            
            // Log statement yang berhasil (hanya untuk CREATE, INSERT, dll)
            if (preg_match('/^(CREATE|INSERT|ALTER|DROP)/i', $statement)) {
                $short_statement = substr($statement, 0, 50) . '...';
                echo "✅ Berhasil: {$short_statement}\n";
            }
            
        } catch (Exception $e) {
            $errors++;
            $short_statement = substr($statement, 0, 50) . '...';
            echo "❌ Error: {$short_statement}\n";
            echo "   Detail: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "📊 RINGKASAN MIGRASI\n";
    echo str_repeat('=', 50) . "\n";
    echo "✅ Berhasil dieksekusi: {$executed} statement\n";
    echo "❌ Error: {$errors} statement\n";
    
    if ($errors === 0) {
        echo "\n🎉 Migrasi Zoom Integration berhasil diselesaikan!\n";
        
        // Verifikasi tabel yang dibuat
        echo "\n🔍 Verifikasi tabel yang dibuat:\n";
        
        $tables_to_check = [
            'epic_event_categories',
            'epic_zoom_events', 
            'epic_event_registrations'
        ];
        
        foreach ($tables_to_check as $table) {
            try {
                $result = db()->selectOne("SELECT COUNT(*) as count FROM {$table}");
                echo "✅ Tabel {$table}: {$result['count']} record\n";
            } catch (Exception $e) {
                echo "❌ Tabel {$table}: Error - " . $e->getMessage() . "\n";
            }
        }
        
        // Test stored procedure
        echo "\n🧪 Testing stored procedure:\n";
        try {
            $result = db()->select("CALL GetEventsByUserLevel('epic', 5, 0)");
            echo "✅ Stored procedure GetEventsByUserLevel: " . count($result) . " hasil\n";
        } catch (Exception $e) {
            echo "❌ Stored procedure error: " . $e->getMessage() . "\n";
        }
        
        // Test function
        echo "\n🧪 Testing function:\n";
        try {
            $result = db()->selectOne("SELECT CanUserAccessEvent(1, 'epic') as can_access");
            echo "✅ Function CanUserAccessEvent: " . ($result['can_access'] ? 'TRUE' : 'FALSE') . "\n";
        } catch (Exception $e) {
            echo "❌ Function error: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "\n⚠️ Migrasi selesai dengan beberapa error. Silakan periksa log di atas.\n";
    }
    
} catch (Exception $e) {
    echo "\n💥 FATAL ERROR:\n";
    echo $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n📝 CATATAN PENTING:\n";
echo "- Pastikan untuk backup database sebelum menjalankan di production\n";
echo "- File ini dapat dihapus setelah migrasi berhasil\n";
echo "- Untuk rollback, gunakan script yang ada di bagian bawah schema file\n";
echo "\n✨ Migrasi selesai!\n";

?>