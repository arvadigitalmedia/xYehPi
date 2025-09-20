<?php
/**
 * EPIC Hub Blog Migration Script
 * Run this script to create blog tracking tables
 */

// Include the bootstrap file
require_once __DIR__ . '/bootstrap.php';

echo "EPIC Hub Blog Migration Script\n";
echo "================================\n\n";

try {
    // Read the SQL file
    $sql_file = __DIR__ . '/blog-tracking-schema.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: {$sql_file}");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    if ($sql_content === false) {
        throw new Exception("Failed to read SQL file");
    }
    
    echo "Reading SQL file: {$sql_file}\n";
    
    // Split SQL statements (simple approach)
    $statements = array_filter(
        array_map('trim', 
            preg_split('/;\s*$/m', $sql_content)
        ),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^\s*--/', $stmt) && 
                   !preg_match('/^\s*\/\*/', $stmt) &&
                   !preg_match('/^\s*DELIMITER/', $stmt);
        }
    );
    
    echo "Found " . count($statements) . " SQL statements\n\n";
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            // Skip comments and empty statements
            if (preg_match('/^\s*(--|\/\*|#)/', $statement)) {
                continue;
            }
            
            echo "Executing statement " . ($index + 1) . "...\n";
            
            // Execute the statement
            $result = db()->query($statement);
            
            if ($result !== false) {
                $success_count++;
                echo "✓ Success\n";
            } else {
                $error_count++;
                echo "✗ Failed\n";
            }
            
        } catch (Exception $e) {
            $error_count++;
            echo "✗ Error: " . $e->getMessage() . "\n";
            
            // Continue with other statements even if one fails
            continue;
        }
    }
    
    echo "\n================================\n";
    echo "Migration Summary:\n";
    echo "✓ Successful: {$success_count}\n";
    echo "✗ Failed: {$error_count}\n";
    
    if ($error_count === 0) {
        echo "\n🎉 All migrations completed successfully!\n";
    } else {
        echo "\n⚠️  Some migrations failed. Please check the errors above.\n";
    }
    
    // Test if tables were created
    echo "\nTesting table creation...\n";
    
    $test_tables = [
        'epic_blog_article_stats',
        'epic_blog_referral_tracking',
        'epic_blog_social_shares'
    ];
    
    foreach ($test_tables as $table) {
        try {
            $exists = db()->selectValue(
                "SELECT COUNT(*) FROM information_schema.tables 
                 WHERE table_schema = DATABASE() AND table_name = ?",
                [$table]
            );
            
            if ($exists) {
                echo "✓ Table '{$table}' created successfully\n";
            } else {
                echo "✗ Table '{$table}' not found\n";
            }
        } catch (Exception $e) {
            echo "✗ Error checking table '{$table}': " . $e->getMessage() . "\n";
        }
    }
    
    // Test if view was created
    try {
        $view_exists = db()->selectValue(
            "SELECT COUNT(*) FROM information_schema.views 
             WHERE table_schema = DATABASE() AND table_name = 'epic_blog_analytics_summary'"
        );
        
        if ($view_exists) {
            echo "✓ View 'epic_blog_analytics_summary' created successfully\n";
        } else {
            echo "✗ View 'epic_blog_analytics_summary' not found\n";
        }
    } catch (Exception $e) {
        echo "✗ Error checking view: " . $e->getMessage() . "\n";
    }
    
    // Test stored procedure
    try {
        $proc_exists = db()->selectValue(
            "SELECT COUNT(*) FROM information_schema.routines 
             WHERE routine_schema = DATABASE() AND routine_name = 'UpdateArticleStats'"
        );
        
        if ($proc_exists) {
            echo "✓ Stored procedure 'UpdateArticleStats' created successfully\n";
        } else {
            echo "✗ Stored procedure 'UpdateArticleStats' not found\n";
        }
    } catch (Exception $e) {
        echo "✗ Error checking stored procedure: " . $e->getMessage() . "\n";
    }
    
    // Test function
    try {
        $func_exists = db()->selectValue(
            "SELECT COUNT(*) FROM information_schema.routines 
             WHERE routine_schema = DATABASE() AND routine_name = 'GetArticleConversionRate'"
        );
        
        if ($func_exists) {
            echo "✓ Function 'GetArticleConversionRate' created successfully\n";
        } else {
            echo "✗ Function 'GetArticleConversionRate' not found\n";
        }
    } catch (Exception $e) {
        echo "✗ Error checking function: " . $e->getMessage() . "\n";
    }
    
    echo "\n================================\n";
    echo "Blog tracking system is ready!\n";
    echo "\nNext steps:\n";
    echo "1. Test the blog admin interface at: " . epic_url('admin/blog') . "\n";
    echo "2. Create some test articles\n";
    echo "3. Test the public blog at: " . epic_url('blog') . "\n";
    echo "4. Test referral tracking with ?ref=REFERRAL_CODE\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "\nPlease check your database connection and try again.\n";
    exit(1);
}

echo "\n✅ Migration script completed.\n";
?>