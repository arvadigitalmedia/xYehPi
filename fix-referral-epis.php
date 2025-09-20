<?php
define('EPIC_LOADED', true);
require_once 'config/database.php';

echo "=== FIX REFERRAL EPIS SUPERVISOR ===\n\n";

$referral_code = 'IJUU9WA8';

// 1. Cek data user saat ini
echo "1. Data user saat ini:\n";
$user = db()->selectOne(
    "SELECT id, name, email, referral_code, epis_supervisor_id, status, role 
     FROM epic_users 
     WHERE referral_code = ?",
    [$referral_code]
);

if (!$user) {
    echo "User dengan kode referral $referral_code tidak ditemukan!\n";
    exit;
}

echo "   ID: {$user['id']}\n";
echo "   Nama: {$user['name']}\n";
echo "   Email: {$user['email']}\n";
echo "   Kode Referral: {$user['referral_code']}\n";
echo "   EPIS Supervisor ID: " . ($user['epis_supervisor_id'] ?: 'NULL') . "\n";
echo "   Status: {$user['status']}\n";
echo "   Role: {$user['role']}\n\n";

// 2. Hapus EPIS Supervisor ID
echo "2. Menghapus EPIS Supervisor ID...\n";
$updated = db()->query(
    "UPDATE epic_users SET epis_supervisor_id = NULL WHERE id = ?",
    [$user['id']]
);

if ($updated) {
    echo "   ✓ EPIS Supervisor ID berhasil dihapus\n\n";
} else {
    echo "   ✗ Gagal menghapus EPIS Supervisor ID\n\n";
    exit;
}

// 3. Verifikasi perubahan
echo "3. Data user setelah perubahan:\n";
$user_updated = db()->selectOne(
    "SELECT id, name, email, referral_code, epis_supervisor_id, status, role 
     FROM epic_users 
     WHERE referral_code = ?",
    [$referral_code]
);

echo "   ID: {$user_updated['id']}\n";
echo "   Nama: {$user_updated['name']}\n";
echo "   Email: {$user_updated['email']}\n";
echo "   Kode Referral: {$user_updated['referral_code']}\n";
echo "   EPIS Supervisor ID: " . ($user_updated['epis_supervisor_id'] ?: 'NULL') . "\n";
echo "   Status: {$user_updated['status']}\n";
echo "   Role: {$user_updated['role']}\n\n";

// 4. Test fungsi epic_get_referrer_info
echo "4. Test fungsi epic_get_referrer_info:\n";
require_once 'core/functions.php';

$referrer_info = epic_get_referrer_info($referral_code);

if ($referrer_info) {
    echo "   ✓ Referrer ditemukan:\n";
    echo "   - Nama: {$referrer_info['name']}\n";
    echo "   - Email: {$referrer_info['email']}\n";
    echo "   - EPIS Supervisor ID: " . ($referrer_info['epis_supervisor_id'] ?: 'NULL') . "\n";
    echo "   - EPIS Supervisor Name: " . ($referrer_info['epis_supervisor_name'] ?: 'NULL') . "\n";
    echo "   - EPIS Code: " . ($referrer_info['epis_code'] ?: 'NULL') . "\n";
    echo "   - Territory: " . ($referrer_info['territory_name'] ?: 'NULL') . "\n";
} else {
    echo "   ✗ Referrer tidak ditemukan\n";
}

echo "\n=== SELESAI ===\n";
echo "Silakan test form registrasi dengan kode referral: $referral_code\n";
?>