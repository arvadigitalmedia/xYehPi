<?php
/**
 * Font Handler - Return empty font response
 * Prevents 404 errors for missing font files
 */

// Set proper headers for font files
header('Content-Type: font/woff2');
header('Cache-Control: public, max-age=31536000'); // 1 year
header('Access-Control-Allow-Origin: *');
header('Content-Length: 0');

// Return empty response
exit;
?>