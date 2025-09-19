<?php
session_start();
require_once 'bootstrap.php';

// Login admin otomatis
$admin_email = 'arifin@emasperak.id';
$admin = db()->selectOne('SELECT * FROM epic_users WHERE email = ?', [$admin_email]);

if ($admin) {
    // Set session
    $_SESSION['user_id'] = $admin['id'];
    $_SESSION['user_email'] = $admin['email'];
    $_SESSION['user_name'] = $admin['name'];
    $_SESSION['user_role'] = $admin['role'];
    $_SESSION['user_status'] = $admin['status'];
    
    echo "✓ Admin login berhasil!<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Email: " . $_SESSION['user_email'] . "<br>";
    echo "Name: " . $_SESSION['user_name'] . "<br>";
    echo "Role: " . $_SESSION['user_role'] . "<br>";
    echo "Status: " . $_SESSION['user_status'] . "<br><br>";
    
    // Test akses dashboard
    echo "<strong>Testing Dashboard Access:</strong><br>";
    echo "<a href='admin/dashboard.php' target='_blank'>→ Buka Dashboard Admin</a><br>";
    echo "<a href='admin/' target='_blank'>→ Buka Admin Index</a><br>";
    echo "<a href='debug-admin.php' target='_blank'>→ Debug Admin Status</a><br><br>";
    
    // Test current user function
    $current_user = epic_get_current_user();
    if ($current_user) {
        echo "✓ epic_get_current_user() berhasil<br>";
        echo "Current User Role: " . $current_user['role'] . "<br>";
    } else {
        echo "✗ epic_get_current_user() gagal<br>";
    }
    
} else {
    echo "✗ Admin tidak ditemukan!<br>";
}
?>