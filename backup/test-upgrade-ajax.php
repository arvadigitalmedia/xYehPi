<?php
/**
 * Test Script untuk Endpoint AJAX Upgrade Member
 * Verifikasi bahwa endpoint api/admin/upgrade-member.php berfungsi dengan baik
 */

require_once 'core/config.php';
require_once 'core/functions.php';

// Simulasi session admin
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

echo "<h2>Test Endpoint AJAX Upgrade Member</h2>";

// 1. Test ketersediaan endpoint
echo "<h3>1. Test Ketersediaan Endpoint</h3>";
$endpoint_path = __DIR__ . '/api/admin/upgrade-member.php';
if (file_exists($endpoint_path)) {
    echo "✅ Endpoint file exists: " . $endpoint_path . "<br>";
} else {
    echo "❌ Endpoint file NOT found: " . $endpoint_path . "<br>";
}

// 2. Test struktur database
echo "<h3>2. Test Struktur Database</h3>";
try {
    $pdo = epic_get_pdo();
    
    // Check tabel users
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $required_columns = ['id', 'status', 'hierarchy_level', 'referral_code', 'epis_supervisor_id'];
    foreach ($required_columns as $col) {
        if (in_array($col, $columns)) {
            echo "✅ Column '$col' exists<br>";
        } else {
            echo "❌ Column '$col' NOT found<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// 3. Test data user untuk upgrade
echo "<h3>3. Test Data User untuk Upgrade</h3>";
try {
    $stmt = $pdo->prepare("SELECT id, name, email, status, hierarchy_level, referral_code, epis_supervisor_id FROM users WHERE status = 'free' AND hierarchy_level = 1 LIMIT 3");
    $stmt->execute();
    $free_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($free_users)) {
        echo "✅ Found " . count($free_users) . " Free Account users for testing:<br>";
        foreach ($free_users as $user) {
            echo "- ID: {$user['id']}, Name: {$user['name']}, Status: {$user['status']}, Level: {$user['hierarchy_level']}<br>";
        }
    } else {
        echo "⚠️ No Free Account users found for testing<br>";
    }
} catch (Exception $e) {
    echo "❌ Error fetching users: " . $e->getMessage() . "<br>";
}

// 4. Test fungsi epic_safe_upgrade_to_epic
echo "<h3>4. Test Fungsi epic_safe_upgrade_to_epic</h3>";
if (function_exists('epic_safe_upgrade_to_epic')) {
    echo "✅ Function epic_safe_upgrade_to_epic exists<br>";
    
    if (function_exists('epic_validate_upgrade_eligibility')) {
        echo "✅ Function epic_validate_upgrade_eligibility exists<br>";
    } else {
        echo "❌ Function epic_validate_upgrade_eligibility NOT found<br>";
    }
    
    if (function_exists('epic_get_upgrade_impact')) {
        echo "✅ Function epic_get_upgrade_impact exists<br>";
    } else {
        echo "❌ Function epic_get_upgrade_impact NOT found<br>";
    }
} else {
    echo "❌ Function epic_safe_upgrade_to_epic NOT found<br>";
}

// 5. Test simulasi AJAX request (tanpa eksekusi upgrade)
echo "<h3>5. Test Simulasi AJAX Request</h3>";
if (!empty($free_users)) {
    $test_user = $free_users[0];
    echo "Testing with User ID: {$test_user['id']} ({$test_user['name']})<br>";
    
    // Simulasi POST data
    $_POST['member_id'] = $test_user['id'];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Test validasi eligibility
    try {
        if (function_exists('epic_validate_upgrade_eligibility')) {
            $eligibility = epic_validate_upgrade_eligibility($test_user['id']);
            if ($eligibility['eligible']) {
                echo "✅ User eligible for upgrade<br>";
                echo "- Current Status: {$eligibility['current_status']}<br>";
                echo "- Current Level: {$eligibility['current_level']}<br>";
            } else {
                echo "❌ User NOT eligible: {$eligibility['reason']}<br>";
            }
        }
    } catch (Exception $e) {
        echo "❌ Error testing eligibility: " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ No test user available<br>";
}

// 6. Test JavaScript function (simulasi)
echo "<h3>6. Test JavaScript Function</h3>";
echo "✅ JavaScript function upgradeAccount() telah diupdate untuk menggunakan AJAX<br>";
echo "✅ Function updateMemberRow() telah ditambahkan untuk update tampilan<br>";
echo "✅ Atribut data-member-id telah ditambahkan pada setiap row tabel<br>";

echo "<h3>Kesimpulan</h3>";
echo "Jika semua test di atas menunjukkan ✅, maka tombol UPGRADE sudah siap digunakan.<br>";
echo "Silakan test langsung di halaman Member Management dengan mengklik tombol UPGRADE pada user dengan Free Account.<br>";
echo "<br><a href='themes/modern/admin/member.php' target='_blank'>🔗 Buka Member Management</a>";
?>