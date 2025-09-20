<?php
// Debug CSRF Token Generation
define('EPIC_ROOT', __DIR__);
require_once EPIC_ROOT . '/bootstrap.php';

echo "<h1>Debug CSRF Token</h1>";

// Check session
echo "<h2>Session Status</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "Session Started: " . (session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "<br>";

// Check if CSRF functions exist
echo "<h2>CSRF Functions</h2>";
echo "epic_generate_csrf_token exists: " . (function_exists('epic_generate_csrf_token') ? 'Yes' : 'No') . "<br>";
echo "epic_csrf_field exists: " . (function_exists('epic_csrf_field') ? 'Yes' : 'No') . "<br>";

// Try to generate CSRF token
echo "<h2>CSRF Token Generation</h2>";
try {
    require_once EPIC_ROOT . '/core/csrf-protection.php';
    
    echo "After requiring csrf-protection.php:<br>";
    echo "epic_generate_csrf_token exists: " . (function_exists('epic_generate_csrf_token') ? 'Yes' : 'No') . "<br>";
    echo "epic_csrf_field exists: " . (function_exists('epic_csrf_field') ? 'Yes' : 'No') . "<br>";
    
    if (function_exists('epic_generate_csrf_token')) {
        $token = epic_generate_csrf_token('register');
        echo "Generated Token: " . htmlspecialchars($token) . "<br>";
        
        if (function_exists('epic_csrf_field')) {
            echo "<h3>CSRF Field HTML:</h3>";
            echo "<pre>" . htmlspecialchars(epic_csrf_field('register')) . "</pre>";
        }
    }
    
    // Check session data
    echo "<h2>Session Data</h2>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>