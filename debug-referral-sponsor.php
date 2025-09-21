<?php
/**
 * Debug script untuk memeriksa sistem referral dan sponsor
 * Khusus untuk user contact.bustanul@gmail.com dengan kode ADMIN001
 */

// Include required files
if (!defined('EPIC_LOADED')) define('EPIC_LOADED', true);
require_once 'config/config.php';
require_once 'bootstrap.php';

echo "<h1>Debug Sistem Referral & Sponsor</h1>";
echo "<h2>Target: contact.bustanul@gmail.com dengan kode ADMIN001</h2>";

// 1. Cek struktur tabel users
echo "<h3>1. Struktur Tabel Users</h3>";
try {
    $tables = db()->select("SHOW TABLES");
    echo "<strong>Tabel yang tersedia:</strong><br>";
    foreach ($tables as $table) {
        $table_name = array_values($table)[0];
        echo "- " . $table_name . "<br>";
    }
    
    // Cek struktur tabel users
    $user_table = db()->table('users');
    $columns = db()->select("DESCRIBE $user_table");
    echo "<br><strong>Struktur tabel $user_table:</strong><br>";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']}) - {$col['Null']} - {$col['Key']}<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// 2. Cek data user target
echo "<h3>2. Data User Target</h3>";
try {
    $user = db()->selectOne(
        "SELECT * FROM " . db()->table('users') . " WHERE email = ?",
        ['contact.bustanul@gmail.com']
    );
    
    if ($user) {
        echo "<strong>User ditemukan:</strong><br>";
        echo "<pre>" . print_r($user, true) . "</pre>";
    } else {
        echo "<strong>User tidak ditemukan!</strong><br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// 3. Cek data sponsor dengan kode ADMIN001
echo "<h3>3. Data Sponsor dengan Kode ADMIN001</h3>";
try {
    $sponsor = db()->selectOne(
        "SELECT * FROM " . db()->table('users') . " WHERE referral_code = ?",
        ['ADMIN001']
    );
    
    if ($sponsor) {
        echo "<strong>Sponsor ditemukan:</strong><br>";
        echo "<pre>" . print_r($sponsor, true) . "</pre>";
    } else {
        echo "<strong>Sponsor dengan kode ADMIN001 tidak ditemukan!</strong><br>";
        
        // Cek semua kode referral yang ada
        $all_codes = db()->select(
            "SELECT id, name, email, referral_code FROM " . db()->table('users') . " WHERE referral_code IS NOT NULL AND referral_code != ''"
        );
        echo "<br><strong>Semua kode referral yang tersedia:</strong><br>";
        foreach ($all_codes as $code) {
            echo "- ID: {$code['id']}, Name: {$code['name']}, Email: {$code['email']}, Code: {$code['referral_code']}<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// 4. Test API referral check
echo "<h3>4. Test API Referral Check</h3>";
try {
    // Simulasi API call
    $sponsor_api = db()->selectOne(
        "SELECT u.id, u.name, u.email, u.referral_code, u.status,
                supervisor.id as epis_supervisor_id,
                supervisor.name as epis_supervisor_name,
                supervisor.email as epis_supervisor_email
         FROM " . db()->table('users') . " u
         LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
         WHERE u.referral_code = ? AND u.status IN ('active', 'epic', 'epis')",
        ['ADMIN001']
    );
    
    if ($sponsor_api) {
        echo "<strong>API Response untuk ADMIN001:</strong><br>";
        echo "<pre>" . print_r($sponsor_api, true) . "</pre>";
    } else {
        echo "<strong>API tidak menemukan sponsor aktif dengan kode ADMIN001</strong><br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// 5. Cek relasi sponsor_id pada user target
echo "<h3>5. Analisis Relasi Sponsor</h3>";
if (isset($user) && $user) {
    echo "<strong>Data sponsor_id user:</strong> " . ($user['sponsor_id'] ?? 'NULL') . "<br>";
    echo "<strong>Data epis_supervisor_id user:</strong> " . ($user['epis_supervisor_id'] ?? 'NULL') . "<br>";
    
    if (!empty($user['sponsor_id'])) {
        $sponsor_detail = db()->selectOne(
            "SELECT id, name, email, referral_code FROM " . db()->table('users') . " WHERE id = ?",
            [$user['sponsor_id']]
        );
        
        if ($sponsor_detail) {
            echo "<strong>Detail Sponsor (dari sponsor_id):</strong><br>";
            echo "<pre>" . print_r($sponsor_detail, true) . "</pre>";
        }
    }
    
    if (!empty($user['epis_supervisor_id'])) {
        $supervisor_detail = db()->selectOne(
            "SELECT id, name, email, referral_code FROM " . db()->table('users') . " WHERE id = ?",
            [$user['epis_supervisor_id']]
        );
        
        if ($supervisor_detail) {
            echo "<strong>Detail EPIS Supervisor:</strong><br>";
            echo "<pre>" . print_r($supervisor_detail, true) . "</pre>";
        }
    }
}

// 6. Cek tabel sponsors jika ada
echo "<h3>6. Cek Tabel Sponsors (jika ada)</h3>";
try {
    $sponsors_table = db()->table('sponsors');
    $sponsor_record = db()->selectOne(
        "SELECT * FROM $sponsors_table WHERE user_id = ?",
        [$user['id'] ?? 0]
    );
    
    if ($sponsor_record) {
        echo "<strong>Record di tabel sponsors:</strong><br>";
        echo "<pre>" . print_r($sponsor_record, true) . "</pre>";
    } else {
        echo "<strong>Tidak ada record di tabel sponsors untuk user ini</strong><br>";
    }
} catch (Exception $e) {
    echo "Tabel sponsors tidak ada atau error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Kesimpulan & Rekomendasi</h3>";
echo "<p>Script ini akan membantu mengidentifikasi masalah pada sistem referral.</p>";
?>