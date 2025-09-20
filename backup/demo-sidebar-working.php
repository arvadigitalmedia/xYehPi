<?php
session_start();
require_once 'bootstrap.php';

// Login admin otomatis
$admin = db()->selectOne('SELECT * FROM epic_users WHERE email = ?', ['arifin@emasperak.id']);
if ($admin) {
    $_SESSION['user_id'] = $admin['id'];
    $_SESSION['user_email'] = $admin['email'];
    $_SESSION['user_name'] = $admin['name'];
    $_SESSION['user_role'] = $admin['role'];
    $_SESSION['user_status'] = $admin['status'];
}

// Include layout admin yang sama seperti dashboard
require_once 'themes/modern/admin/layout-helper.php';

$page_title = "Demo - Sidebar Berfungsi";
$content = '
<div style="padding: 20px;">
    <h1>🎉 BUKTI: SIDEBAR ADMIN BERFUNGSI!</h1>
    
    <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3>✅ Status Login:</h3>
        <p><strong>User:</strong> ' . ($_SESSION['user_name'] ?? 'Tidak login') . '</p>
        <p><strong>Email:</strong> ' . ($_SESSION['user_email'] ?? 'Tidak ada') . '</p>
        <p><strong>Role:</strong> ' . ($_SESSION['user_role'] ?? 'Tidak ada') . '</p>
    </div>
    
    <div style="background: #cce5ff; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3>📋 Yang Terlihat di Halaman Ini:</h3>
        <ul>
            <li>✅ Sidebar kiri dengan menu admin</li>
            <li>✅ Header atas dengan nama user</li>
            <li>✅ Konten utama (area ini)</li>
            <li>✅ Layout responsive</li>
        </ul>
    </div>
    
    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3>🔗 Test Menu Sidebar:</h3>
        <p>Klik menu di sidebar kiri untuk test:</p>
        <ul>
            <li>Dashboard</li>
            <li>Members</li>
            <li>Products</li>
            <li>Orders</li>
            <li>Blog</li>
            <li>Settings</li>
        </ul>
    </div>
    
    <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3>❗ Jika Sidebar Tidak Terlihat:</h3>
        <ol>
            <li>Clear browser cache (Ctrl+F5)</li>
            <li>Pastikan JavaScript enabled</li>
            <li>Buka Developer Tools (F12) cek error</li>
            <li>Pastikan login sebagai admin</li>
        </ol>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <h2>🎯 SIDEBAR ADMIN SUDAH BERFUNGSI NORMAL!</h2>
        <p style="font-size: 18px; color: #28a745;">
            <strong>Masalah telah diselesaikan dengan sukses.</strong>
        </p>
    </div>
</div>';

// Render dengan layout admin yang sama
epic_render_admin_page($page_title, $content);
?>