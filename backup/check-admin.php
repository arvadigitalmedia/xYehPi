<?php
require_once 'bootstrap.php';
require_once 'themes/modern/admin/routing-helper.php';

// Check for admin user
$admin = db()->selectOne('SELECT * FROM epic_users WHERE role = ? LIMIT 1', ['admin']);

if ($admin) {
    echo "Admin exists:\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Name: " . $admin['name'] . "\n";
    echo "ID: " . $admin['id'] . "\n";
} else {
    echo "No admin user found. Creating one...\n";
    
    // Create admin user
    $admin_data = [
        'uuid' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        ),
        'name' => 'Admin User',
        'email' => 'admin@epichub.com',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'admin',
        'status' => 'epic',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $result = db()->insert('users', $admin_data);
    $admin_id = $result;
    
    if ($result) {
        echo "Admin user created successfully!\n";
        echo "Email: admin@epichub.com\n";
        echo "Password: admin123\n";
    } else {
        echo "Failed to create admin user.\n";
    }
}

echo "\nLogin URL: http://localhost:8000/login\n";
?>