<?php
// Prevent direct access
if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

echo "<h1>Test Reset Password Page</h1>";
echo "<p>Token: " . htmlspecialchars($_GET['token'] ?? 'No token') . "</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>EPIC_LOADED: " . (defined('EPIC_LOADED') ? 'Yes' : 'No') . "</p>";
echo "<p>TABLE_USERS: " . (defined('TABLE_USERS') ? TABLE_USERS : 'Not defined') . "</p>";
echo "<p>TABLE_USER_TOKENS: " . (defined('TABLE_USER_TOKENS') ? TABLE_USER_TOKENS : 'Not defined') . "</p>";

// Test database connection
try {
    $db = db();
    echo "<p>Database: Connected</p>";
} catch (Exception $e) {
    echo "<p>Database Error: " . $e->getMessage() . "</p>";
}
?>