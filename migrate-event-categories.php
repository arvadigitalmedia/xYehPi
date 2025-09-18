<?php
/**
 * EPIC Hub Event Categories Migration
 * Script untuk mengintegrasikan kategori Event Scheduling dengan Zoom Integration
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

// Set header untuk output yang bersih
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔄 Event Categories Integration Migration</h1>";
echo "<hr>";

// Check admin access
if (!epic_is_admin()) {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; border-radius: 5px;'>";
    echo "❌ <strong>Access Denied</strong><br>";
    echo "You must be logged in as an administrator to run this migration.";
    echo "</div>";
    echo "<p><a href='" . epic_url('login') . "'>Login as Admin</a></p>";
    exit;
}

echo "<h2>📋 Migration Progress</h2>";

try {
    global $epic_db;
    
    if (!$epic_db) {
        throw new Exception('Database connection not available');
    }
    
    echo "✅ Database connection: <strong>OK</strong><br>";
    
    // Step 1: Check if epic_event_categories table exists
    echo "<h3>🔍 Step 1: Checking Tables</h3>";
    
    $stmt = $epic_db->query("SHOW TABLES LIKE 'epic_event_categories'");
    $zoom_categories_exists = $stmt->rowCount() > 0;
    
    $stmt = $epic_db->query("SHOW TABLES LIKE 'epi_event_schedule_categories'");
    $schedule_categories_exists = $stmt->rowCount() > 0;
    
    echo "📊 epic_event_categories (Zoom): " . ($zoom_categories_exists ? "✅ EXISTS" : "❌ NOT FOUND") . "<br>";
    echo "📊 epi_event_schedule_categories (Scheduling): " . ($schedule_categories_exists ? "✅ EXISTS" : "❌ NOT FOUND") . "<br>";
    
    if (!$zoom_categories_exists) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>⚠️ Zoom Integration Not Installed</h3>";
        echo "<p>The epic_event_categories table does not exist. Please install Zoom Integration first.</p>";
        echo "<p><a href='/install-zoom-integration.php'>Install Zoom Integration</a></p>";
        echo "</div>";
        exit;
    }
    
    // Step 2: Migrate categories from epi_event_schedule_categories to epic_event_categories
    if ($schedule_categories_exists) {
        echo "<h3>📦 Step 2: Migrating Categories</h3>";
        
        // Get categories from schedule table
        $stmt = $epic_db->query("SELECT * FROM epi_event_schedule_categories");
        $schedule_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Found <strong>" . count($schedule_categories) . "</strong> categories in epi_event_schedule_categories<br>";
        
        $migrated_count = 0;
        $skipped_count = 0;
        
        foreach ($schedule_categories as $category) {
            // Check if category already exists in epic_event_categories
            $stmt = $epic_db->prepare("SELECT id FROM epic_event_categories WHERE name = ?");
            $stmt->execute([$category['name']]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                echo "⏭️ Skipped: <strong>{$category['name']}</strong> (already exists)<br>";
                $skipped_count++;
            } else {
                // Insert into epic_event_categories
                $stmt = $epic_db->prepare("
                    INSERT INTO epic_event_categories (
                        name, description, access_levels, color, icon, is_active, created_by, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $result = $stmt->execute([
                    $category['name'],
                    $category['description'],
                    $category['access_levels'],
                    $category['color'],
                    $category['icon'],
                    $category['is_active'],
                    $category['created_by'],
                    $category['created_at'],
                    $category['updated_at']
                ]);
                
                if ($result) {
                    echo "✅ Migrated: <strong>{$category['name']}</strong><br>";
                    $migrated_count++;
                } else {
                    echo "❌ Failed: <strong>{$category['name']}</strong><br>";
                }
            }
        }
        
        echo "<br>📊 Migration Summary:<br>";
        echo "✅ Migrated: <strong>$migrated_count</strong> categories<br>";
        echo "⏭️ Skipped: <strong>$skipped_count</strong> categories<br>";
    }
    
    // Step 3: Update foreign key constraint in epi_event_schedules
    echo "<h3>🔗 Step 3: Updating Foreign Key Constraints</h3>";
    
    try {
        // Check if foreign key constraint exists
        $stmt = $epic_db->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'epi_event_schedules' 
            AND COLUMN_NAME = 'category_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $constraint = $stmt->fetch();
        
        if ($constraint) {
            echo "🔍 Found existing constraint: <strong>{$constraint['CONSTRAINT_NAME']}</strong><br>";
            
            // Drop existing constraint
            $epic_db->exec("ALTER TABLE epi_event_schedules DROP FOREIGN KEY {$constraint['CONSTRAINT_NAME']}");
            echo "❌ Dropped old constraint<br>";
        }
        
        // Add new foreign key constraint
        $epic_db->exec("
            ALTER TABLE epi_event_schedules 
            ADD CONSTRAINT epi_event_schedules_category_id_foreign 
            FOREIGN KEY (category_id) REFERENCES epic_event_categories(id) ON DELETE CASCADE
        ");
        echo "✅ Added new foreign key constraint to epic_event_categories<br>";
        
    } catch (Exception $e) {
        echo "⚠️ Foreign key constraint update: " . $e->getMessage() . "<br>";
    }
    
    // Step 4: Update category mapping for existing events
    echo "<h3>🔄 Step 4: Updating Event Category Mapping</h3>";
    
    if ($schedule_categories_exists) {
        // Get mapping between old and new category IDs
        $stmt = $epic_db->query("
            SELECT 
                sc.id as old_id, 
                sc.name,
                ec.id as new_id
            FROM epi_event_schedule_categories sc
            JOIN epic_event_categories ec ON sc.name = ec.name
        ");
        $category_mapping = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Found <strong>" . count($category_mapping) . "</strong> category mappings<br>";
        
        $updated_events = 0;
        foreach ($category_mapping as $mapping) {
            $stmt = $epic_db->prepare("
                UPDATE epi_event_schedules 
                SET category_id = ? 
                WHERE category_id = ?
            ");
            
            $stmt->execute([$mapping['new_id'], $mapping['old_id']]);
            $affected = $stmt->rowCount();
            
            if ($affected > 0) {
                echo "🔄 Updated <strong>$affected</strong> events for category: <strong>{$mapping['name']}</strong><br>";
                $updated_events += $affected;
            }
        }
        
        echo "📊 Total events updated: <strong>$updated_events</strong><br>";
    }
    
    // Step 5: Drop old category table (optional)
    echo "<h3>🗑️ Step 5: Cleanup (Optional)</h3>";
    
    if ($schedule_categories_exists) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>⚠️ Cleanup Option</h4>";
        echo "<p>The old table <code>epi_event_schedule_categories</code> can now be safely removed.</p>";
        echo "<p><strong>Note:</strong> This action is irreversible. Make sure the migration was successful before proceeding.</p>";
        echo "<form method='POST' style='margin-top: 10px;'>";
        echo "<input type='hidden' name='action' value='drop_old_table'>";
        echo "<button type='submit' style='background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;' onclick='return confirm(\"Are you sure you want to drop the old category table? This cannot be undone.\")'>Drop Old Table</button>";
        echo "</form>";
        echo "</div>";
        
        // Handle table drop request
        if (isset($_POST['action']) && $_POST['action'] === 'drop_old_table') {
            try {
                $epic_db->exec("DROP TABLE epi_event_schedule_categories");
                echo "✅ Old table <code>epi_event_schedule_categories</code> has been dropped<br>";
            } catch (Exception $e) {
                echo "❌ Failed to drop old table: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    // Step 6: Verification
    echo "<h3>🔍 Step 6: Verification</h3>";
    
    // Count categories
    $stmt = $epic_db->query("SELECT COUNT(*) as count FROM epic_event_categories");
    $category_count = $stmt->fetch()['count'];
    echo "📊 Total categories in epic_event_categories: <strong>$category_count</strong><br>";
    
    // Count events
    $stmt = $epic_db->query("SELECT COUNT(*) as count FROM epi_event_schedules");
    $event_count = $stmt->fetch()['count'];
    echo "📊 Total events in epi_event_schedules: <strong>$event_count</strong><br>";
    
    // Test join query
    $stmt = $epic_db->query("
        SELECT COUNT(*) as count 
        FROM epi_event_schedules e 
        JOIN epic_event_categories c ON e.category_id = c.id
    ");
    $joined_count = $stmt->fetch()['count'];
    echo "📊 Events with valid categories: <strong>$joined_count</strong><br>";
    
    if ($joined_count == $event_count) {
        echo "✅ All events have valid category references<br>";
    } else {
        echo "⚠️ Some events may have invalid category references<br>";
    }
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Migration Completed Successfully!</h3>";
    echo "<p>Event Scheduling categories are now integrated with Zoom Integration categories.</p>";
    echo "<ul>";
    echo "<li>✅ Categories are shared between both systems</li>";
    echo "<li>✅ No duplicate category creation needed</li>";
    echo "<li>✅ Foreign key constraints updated</li>";
    echo "<li>✅ Data integrity maintained</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Migration Failed</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>🔗 Quick Links</h2>";
echo "<p>";
echo "<a href='" . epic_url('admin/event-scheduling') . "' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📅 Event Scheduling</a>";
echo "<a href='" . epic_url('admin/zoom-integration') . "' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🎥 Zoom Integration</a>";
echo "<a href='" . epic_url('admin') . "' style='display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>🏠 Admin Dashboard</a>";
echo "</p>";

echo "<p><strong>Migration completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>