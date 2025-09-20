<?php
/**
 * Test Script - Verifikasi Tampilan Tabel EPIS Accounts
 * Memastikan semua kolom tampil dengan benar sesuai spesifikasi
 */

// Bypass security check
define('EPIC_DIRECT_ACCESS', true);
require_once 'bootstrap.php';

echo "<h2>Test Tampilan Tabel EPIS Accounts</h2>\n";

// Test query epic_get_all_epis_accounts
echo "<h3>1. Test Query epic_get_all_epis_accounts</h3>\n";
try {
    $epis_accounts = epic_get_all_epis_accounts();
    echo "<p>✅ Query berhasil dijalankan</p>\n";
    echo "<p>📊 Jumlah data: " . count($epis_accounts) . "</p>\n";
    
    if (!empty($epis_accounts)) {
        echo "<h4>Sample Data (Record Pertama):</h4>\n";
        $first_record = $epis_accounts[0];
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
        echo "<tr><th>Kolom</th><th>Nilai</th><th>Status</th></tr>\n";
        
        // Kolom yang dibutuhkan sesuai spesifikasi
        $required_columns = [
            'user_id' => 'ID',
            'name' => 'Nama',
            'email' => 'Email', 
            'phone' => 'Kontak (Phone)',
            'epis_code' => 'Kode EPIS',
            'territory_name' => 'Territory',
            'current_epic_count' => 'Network Size',
            'total_commissions' => 'Total Commissions',
            'status' => 'Status',
            'formatted_created_at' => 'Created (Formatted)',
            'created_at' => 'Created (Raw)'
        ];
        
        foreach ($required_columns as $column => $label) {
            $value = $first_record[$column] ?? 'NULL';
            $status = isset($first_record[$column]) ? '✅' : '❌';
            echo "<tr><td>{$label}</td><td>{$value}</td><td>{$status}</td></tr>\n";
        }
        echo "</table>\n";
        
    } else {
        echo "<p>⚠️ Tidak ada data EPIS Accounts</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>\n";
}

// Test struktur tabel yang diharapkan
echo "<h3>2. Verifikasi Struktur Kolom Tabel</h3>\n";
echo "<p>Kolom yang harus tampil di tabel:</p>\n";
echo "<ol>\n";
echo "<li>EPIS Account (ID, Nama, Email, Kode EPIS)</li>\n";
echo "<li>Kontak (Phone)</li>\n";
echo "<li>Territory</li>\n";
echo "<li>Network Size</li>\n";
echo "<li>Total Commissions</li>\n";
echo "<li>Status (Aktif/Inactive)</li>\n";
echo "<li>Created (Waktu pembuatan)</li>\n";
echo "<li>Action (Edit, Active/Nonaktifkan)</li>\n";
echo "</ol>\n";

// Test format data
echo "<h3>3. Test Format Data</h3>\n";
if (!empty($epis_accounts)) {
    $sample = $epis_accounts[0];
    
    echo "<h4>Format Total Commissions:</h4>\n";
    $total_commissions = $sample['total_commissions'] ?? 0;
    echo "<p>Raw: {$total_commissions}</p>\n";
    echo "<p>Formatted: Rp " . number_format($total_commissions, 0, ',', '.') . "</p>\n";
    
    echo "<h4>Format Status:</h4>\n";
    $status = $sample['status'] ?? 'unknown';
    $status_text = $status === 'active' ? 'Aktif' : 
                  ($status === 'suspended' ? 'Inactive' : ucfirst($status));
    echo "<p>Raw: {$status}</p>\n";
    echo "<p>Display: {$status_text}</p>\n";
    
    echo "<h4>Format Created Date:</h4>\n";
    $created_at = $sample['created_at'] ?? '';
    $formatted_created_at = $sample['formatted_created_at'] ?? '';
    echo "<p>Raw: {$created_at}</p>\n";
    echo "<p>Formatted: {$formatted_created_at}</p>\n";
    echo "<p>Fallback: " . date('d M Y H:i', strtotime($created_at)) . "</p>\n";
}

echo "<h3>4. Test URL Akses</h3>\n";
echo "<p>URL halaman: <a href='" . epic_url('admin/manage/epis') . "' target='_blank'>" . epic_url('admin/manage/epis') . "</a></p>\n";

echo "<h3>✅ Test Selesai</h3>\n";
echo "<p>Silakan buka URL di atas untuk melihat tampilan tabel yang sebenarnya.</p>\n";
?>