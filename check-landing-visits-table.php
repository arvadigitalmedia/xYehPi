<?php
/**
 * Check epic_landing_visits table structure
 */

require_once __DIR__ . '/bootstrap.php';

echo "Checking epic_landing_visits table structure...\n";
echo "===============================================\n\n";

try {
    $columns = db()->select("SHOW COLUMNS FROM epic_landing_visits");
    
    echo "Table columns:\n";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\nLooking for source-related columns...\n";
    $source_columns = [];
    foreach ($columns as $col) {
        if (stripos($col['Field'], 'source') !== false || 
            stripos($col['Field'], 'template') !== false ||
            stripos($col['Field'], 'page') !== false) {
            $source_columns[] = $col['Field'];
        }
    }
    
    if (!empty($source_columns)) {
        echo "Found source-related columns: " . implode(', ', $source_columns) . "\n";
    } else {
        echo "No source-related columns found\n";
    }
    
    // Check if we have article_id and article_slug columns (added by migration)
    $has_article_id = false;
    $has_article_slug = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'article_id') {
            $has_article_id = true;
        }
        if ($col['Field'] === 'article_slug') {
            $has_article_slug = true;
        }
    }
    
    echo "\nBlog tracking columns:\n";
    echo "- article_id: " . ($has_article_id ? "✓ EXISTS" : "✗ MISSING") . "\n";
    echo "- article_slug: " . ($has_article_slug ? "✓ EXISTS" : "✗ MISSING") . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Table structure check completed!\n";
?>