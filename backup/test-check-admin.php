<?php
require_once 'bootstrap.php';

echo "=== Checking Admin Users ===\n";
$users = db()->select('SELECT id, email, name, role FROM epic_users WHERE role IN ("admin", "super_admin") LIMIT 5');

if (empty($users)) {
    echo "No admin users found!\n";
    
    // Check all users
    echo "\n=== All Users ===\n";
    $all_users = db()->select('SELECT id, email, name, role FROM epic_users LIMIT 10');
    foreach($all_users as $user) {
        echo $user['id'] . ' | ' . $user['email'] . ' | ' . $user['name'] . ' | ' . $user['role'] . "\n";
    }
} else {
    foreach($users as $user) {
        echo $user['id'] . ' | ' . $user['email'] . ' | ' . $user['name'] . ' | ' . $user['role'] . "\n";
    }
}
?>