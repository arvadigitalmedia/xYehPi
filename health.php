<?php
/**
 * EPIC Hub Health Check Endpoint
 * Simple health monitoring for production
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$health = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'version' => '1.0.0',
    'checks' => []
];

try {
    // Check if bootstrap loads
    require_once __DIR__ . '/bootstrap.php';
    $health['checks']['bootstrap'] = 'ok';
    
    // Check database connection
    $db = db();
    $db->selectValue('SELECT 1');
    $health['checks']['database'] = 'ok';
    
    // Check core functions
    $required_functions = ['epic_url', 'epic_get_user', 'epic_setting'];
    foreach ($required_functions as $func) {
        if (!function_exists($func)) {
            throw new Exception("Missing function: {$func}");
        }
    }
    $health['checks']['functions'] = 'ok';
    
    // Check file permissions
    $dirs = ['uploads', 'cache', 'logs'];
    foreach ($dirs as $dir) {
        if (!is_writable(__DIR__ . '/' . $dir)) {
            $health['checks']['permissions'] = 'warning';
            $health['warnings'][] = "Directory not writable: {$dir}";
        }
    }
    if (!isset($health['checks']['permissions'])) {
        $health['checks']['permissions'] = 'ok';
    }
    
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['error'] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>