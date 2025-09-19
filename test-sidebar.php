<?php
// Test file untuk memverifikasi sidebar
session_start();

// Set session admin untuk testing
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['is_admin'] = true;

// Include core functions
require_once __DIR__ . '/core/bootstrap.php';

// Set current page untuk testing
$current_page = 'dashboard';
$current_url = '/admin/';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sidebar</title>
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/admin.css') ?>">
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="admin-layout">
        <!-- Test Sidebar Include -->
        <?php 
        echo "<!-- DEBUG: About to include sidebar -->\n";
        include __DIR__ . '/themes/modern/admin/components/sidebar.php'; 
        echo "<!-- DEBUG: Sidebar included -->\n";
        ?>
        
        <main class="admin-main">
            <div class="admin-content">
                <h1>Test Sidebar</h1>
                <p>Jika sidebar muncul di sebelah kiri, maka include berhasil.</p>
                <p>Jika tidak muncul, ada masalah dengan path atau CSS.</p>
            </div>
        </main>
    </div>
    
    <script>
        // Initialize Feather icons
        feather.replace();
        
        // Test toggle functions
        function toggleSubmenu(element) {
            const submenu = element.nextElementSibling;
            const arrow = element.querySelector('.sidebar-nav-arrow');
            
            if (submenu && submenu.classList.contains('sidebar-submenu')) {
                submenu.classList.toggle('show');
                element.classList.toggle('expanded');
            }
        }
        
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
            }
        }
    </script>
</body>
</html>