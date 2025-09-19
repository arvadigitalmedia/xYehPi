<?php
// Debug file untuk memeriksa session dan autentikasi admin
session_start();

// Include core functions
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Debug Admin Session</h1>";

echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Current User:</h2>";
$user = epic_current_user();
echo "<pre>";
print_r($user);
echo "</pre>";

echo "<h2>User Role Check:</h2>";
if ($user) {
    echo "User ID: " . $user['id'] . "<br>";
    echo "User Role: " . $user['role'] . "<br>";
    echo "Is Admin: " . (in_array($user['role'], ['admin', 'super_admin']) ? 'YES' : 'NO') . "<br>";
} else {
    echo "No user logged in<br>";
}

echo "<h2>Database Connection:</h2>";
try {
    $db_test = db()->selectValue("SELECT 1");
    echo "Database connection: OK<br>";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

echo "<h2>File Paths:</h2>";
echo "Current file: " . __FILE__ . "<br>";
echo "Layout file: " . __DIR__ . '/themes/modern/admin/layout.php' . "<br>";
echo "Layout exists: " . (file_exists(__DIR__ . '/themes/modern/admin/layout.php') ? 'YES' : 'NO') . "<br>";
echo "Sidebar file: " . __DIR__ . '/themes/modern/admin/components/sidebar.php' . "<br>";
echo "Sidebar exists: " . (file_exists(__DIR__ . '/themes/modern/admin/components/sidebar.php') ? 'YES' : 'NO') . "<br>";

echo "<h2>URL Functions:</h2>";
echo "epic_url('admin'): " . epic_url('admin') . "<br>";
echo "epic_url('admin/dashboard'): " . epic_url('admin/dashboard') . "<br>";

echo "<h2>Test Direct Dashboard Call:</h2>";
echo "<a href='" . epic_url('admin') . "' target='_blank'>Open Admin Dashboard</a><br>";

// Test direct include
echo "<h2>Test Direct Include:</h2>";
try {
    ob_start();
    include __DIR__ . '/themes/modern/admin/dashboard.php';
    $output = ob_get_clean();
    echo "Dashboard include: SUCCESS (length: " . strlen($output) . " chars)<br>";
    if (strlen($output) < 100) {
        echo "Output preview: " . htmlspecialchars(substr($output, 0, 200)) . "<br>";
    }
} catch (Exception $e) {
    echo "Dashboard include ERROR: " . $e->getMessage() . "<br>";
}
?>