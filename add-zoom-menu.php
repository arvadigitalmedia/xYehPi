<?php
/**
 * Add Zoom Integration Menu to Admin Navbar
 * Script untuk menambahkan menu Zoom Integration ke navbar admin
 */

require_once 'bootstrap.php';

// Check if user is admin
if (!epic_is_admin()) {
    die('Access denied. Admin privileges required.');
}

$success = false;
$message = '';

try {
    // Check if admin navbar file exists
    $admin_navbar_files = [
        EPIC_PATH . '/themes/modern/admin/navbar.php',
        EPIC_PATH . '/themes/modern/admin/header.php',
        EPIC_PATH . '/themes/modern/admin/menu.php',
        EPIC_PATH . '/themes/modern/admin/sidebar.php'
    ];
    
    $navbar_file = null;
    foreach ($admin_navbar_files as $file) {
        if (file_exists($file)) {
            $navbar_file = $file;
            break;
        }
    }
    
    if (!$navbar_file) {
        // Create a simple admin menu file
        $admin_dir = EPIC_PATH . '/themes/modern/admin';
        if (!is_dir($admin_dir)) {
            mkdir($admin_dir, 0755, true);
        }
        
        $navbar_file = $admin_dir . '/menu.php';
        $menu_content = '<?php
/**
 * Admin Menu - EPIC Hub
 */

if (!defined("EPIC_LOADED")) {
    die("Direct access not allowed");
}

$admin_menu_items = [
    [
        "title" => "Dashboard",
        "url" => epic_url("admin"),
        "icon" => "home"
    ],
    [
        "title" => "Members",
        "url" => epic_url("admin/members"),
        "icon" => "users"
    ],
    [
        "title" => "Orders",
        "url" => epic_url("admin/orders"),
        "icon" => "shopping-cart"
    ],
    [
        "title" => "Integrations",
        "url" => "#",
        "icon" => "link",
        "submenu" => [
            [
                "title" => "Autoresponder",
                "url" => epic_url("admin/autoresponder"),
                "icon" => "mail"
            ],
            [
                "title" => "Zoom Integration",
                "url" => epic_url("admin/zoom-integration"),
                "icon" => "video"
            ]
        ]
    ],
    [
        "title" => "Settings",
        "url" => epic_url("admin/settings"),
        "icon" => "settings"
    ]
];

// Render menu
foreach ($admin_menu_items as $item) {
    echo "<li class=\"nav-item\">";
    
    if (isset($item["submenu"])) {
        echo "<a href=\"#\" class=\"nav-link dropdown-toggle\" data-toggle=\"dropdown\">";
        echo "<i data-feather=\"" . $item["icon"] . "\" width=\"16\" height=\"16\"></i> ";
        echo htmlspecialchars($item["title"]);
        echo "</a>";
        echo "<ul class=\"dropdown-menu\">";
        foreach ($item["submenu"] as $subitem) {
            echo "<li><a href=\"" . $subitem["url"] . "\" class=\"dropdown-item\">";
            echo "<i data-feather=\"" . $subitem["icon"] . "\" width=\"14\" height=\"14\"></i> ";
            echo htmlspecialchars($subitem["title"]);
            echo "</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<a href=\"" . $item["url"] . "\" class=\"nav-link\">";
        echo "<i data-feather=\"" . $item["icon"] . "\" width=\"16\" height=\"16\"></i> ";
        echo htmlspecialchars($item["title"]);
        echo "</a>";
    }
    
    echo "</li>";
}
?>';
        
        file_put_contents($navbar_file, $menu_content);
        $message = 'Admin menu file created with Zoom Integration menu';
        $success = true;
    } else {
        // Read existing navbar file
        $content = file_get_contents($navbar_file);
        
        // Check if Zoom Integration menu already exists
        if (strpos($content, 'zoom-integration') !== false) {
            $message = 'Zoom Integration menu already exists in navbar';
            $success = true;
        } else {
            // Add Zoom Integration menu
            $zoom_menu = '
                    <li class="nav-item">
                        <a href="<?= epic_url("admin/zoom-integration") ?>" class="nav-link">
                            <i data-feather="video" width="16" height="16"></i>
                            Zoom Integration
                        </a>
                    </li>';
            
            // Try to find a good place to insert the menu
            $insert_patterns = [
                '<!-- Integration Menu -->',
                '<!-- Admin Menu -->',
                '</ul>',
                '</nav>'
            ];
            
            $inserted = false;
            foreach ($insert_patterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $content = str_replace($pattern, $zoom_menu . "\n                    " . $pattern, $content);
                    $inserted = true;
                    break;
                }
            }
            
            if ($inserted) {
                file_put_contents($navbar_file, $content);
                $message = 'Zoom Integration menu added to navbar';
                $success = true;
            } else {
                $message = 'Could not find suitable location to insert menu. Please add manually.';
            }
        }
    }
    
} catch (Exception $e) {
    $message = 'Error: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Zoom Menu - EPIC Hub</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1F2937;
            color: #F9FAFB;
            margin: 0;
            padding: 2rem;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #374151;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #3B82F6;
        }
        
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid #10B981;
            color: #10B981;
        }
        
        .error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #EF4444;
            color: #EF4444;
        }
        
        .actions {
            text-align: center;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #3B82F6;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: #2563EB;
        }
        
        .btn-secondary {
            background: #6B7280;
        }
        
        .btn-secondary:hover {
            background: #4B5563;
        }
        
        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid #3B82F6;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .info-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #3B82F6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Add Zoom Integration Menu</h1>
        </div>
        
        <div class="message <?= $success ? 'success' : 'error' ?>">
            <?= $success ? '✅' : '❌' ?> <?= htmlspecialchars($message) ?>
        </div>
        
        <?php if ($success): ?>
            <div class="info-box">
                <div class="info-title">Menu Berhasil Ditambahkan!</div>
                <p>Menu Zoom Integration telah ditambahkan ke navbar admin. Anda sekarang dapat mengakses fitur Zoom Integration melalui menu admin.</p>
            </div>
        <?php endif; ?>
        
        <div class="actions">
            <?php if ($success): ?>
                <a href="<?= epic_url('admin/zoom-integration') ?>" class="btn">Buka Zoom Integration</a>
            <?php endif; ?>
            <a href="<?= epic_url('admin') ?>" class="btn btn-secondary">Kembali ke Admin</a>
        </div>
    </div>
</body>
</html>