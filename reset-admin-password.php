<?php
require_once 'bootstrap.php';

// Reset admin password
$admin_email = 'arifin@emasperak.id';
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$result = db()->query(
    "UPDATE epic_users SET password = ? WHERE email = ? AND role IN ('admin', 'super_admin')",
    [$hashed_password, $admin_email]
);

if ($result) {
    echo "Password reset successfully!\n";
    echo "Email: " . $admin_email . "\n";
    echo "New Password: " . $new_password . "\n";
    echo "Login URL: http://localhost/test-bisnisemasperak/login\n";
} else {
    echo "Failed to reset password.\n";
}

// Show current admin info
$admin = db()->selectOne('SELECT * FROM epic_users WHERE email = ?', [$admin_email]);
if ($admin) {
    echo "\nCurrent admin info:\n";
    echo "ID: " . $admin['id'] . "\n";
    echo "Name: " . $admin['name'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Role: " . $admin['role'] . "\n";
    echo "Status: " . $admin['status'] . "\n";
}
?>