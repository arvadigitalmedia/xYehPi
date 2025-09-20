<?php
/**
 * Simple Blog Migration Script
 */

require_once __DIR__ . '/bootstrap.php';

echo "Running Simple Blog Migration...\n";

$sql = file_get_contents(__DIR__ . '/blog-tracking-simple.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$errors = 0;

foreach($statements as $stmt) {
    if(!empty($stmt) && !preg_match('/^\s*--/', $stmt)) {
        try {
            db()->query($stmt);
            echo "✓ OK: " . substr(str_replace(["\n", "\r"], ' ', $stmt), 0, 60) . "...\n";
            $success++;
        } catch(Exception $e) {
            echo "✗ ERROR: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
}

echo "\nMigration completed: {$success} success, {$errors} errors\n";

// Test tables
echo "\nTesting tables...\n";
$tables = ['epic_blog_article_stats', 'epic_blog_referral_tracking', 'epic_blog_social_shares'];
foreach($tables as $table) {
    try {
        $exists = db()->selectValue("SHOW TABLES LIKE '{$table}'");
        echo $exists ? "✓ {$table} exists\n" : "✗ {$table} missing\n";
    } catch(Exception $e) {
        echo "✗ {$table} error: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";
?>