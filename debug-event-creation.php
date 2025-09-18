<?php
/**
 * Debug Script untuk Event Creation
 * Script untuk troubleshooting masalah "Failed to create event"
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/event-scheduling.php';

// Set header untuk output yang bersih
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Debug Event Creation</h1>";
echo "<hr>";

// Check admin access
if (!epic_is_admin()) {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; border-radius: 5px;'>";
    echo "❌ <strong>Access Denied</strong><br>";
    echo "You must be logged in as an administrator to run this debug.";
    echo "</div>";
    echo "<p><a href='" . epic_url('login') . "'>Login as Admin</a></p>";
    exit;
}

echo "<h2>📋 Debug Progress</h2>";

try {
    global $epic_db;
    
    if (!$epic_db) {
        throw new Exception('Database connection not available');
    }
    
    echo "✅ Database connection: <strong>OK</strong><br>";
    
    // Step 1: Check table structure
    echo "<h3>🗄️ Step 1: Database Structure</h3>";
    
    // Check if tables exist
    $tables = ['epi_event_schedules', 'epic_event_categories'];
    foreach ($tables as $table) {
        $stmt = $epic_db->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo "📊 Table $table: " . ($exists ? "✅ EXISTS" : "❌ NOT FOUND") . "<br>";
        
        if (!$exists) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>❌ Missing Table</h3>";
            echo "<p>Table <code>$table</code> does not exist. Please run the installation scripts.</p>";
            echo "</div>";
            exit;
        }
    }
    
    // Check foreign key constraints
    echo "<h3>🔗 Step 2: Foreign Key Constraints</h3>";
    
    $stmt = $epic_db->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'epi_event_schedules' 
        AND COLUMN_NAME = 'category_id'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($constraints)) {
        echo "⚠️ No foreign key constraint found for category_id<br>";
    } else {
        foreach ($constraints as $constraint) {
            echo "✅ Foreign key: <strong>{$constraint['CONSTRAINT_NAME']}</strong><br>";
            echo "&nbsp;&nbsp;&nbsp;Column: {$constraint['COLUMN_NAME']} → {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']}<br>";
        }
    }
    
    // Step 3: Check categories
    echo "<h3>📁 Step 3: Available Categories</h3>";
    
    $stmt = $epic_db->query("SELECT id, name, is_active FROM epic_event_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Total categories: <strong>" . count($categories) . "</strong><br>";
    
    if (empty($categories)) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>⚠️ No Categories Found</h3>";
        echo "<p>No categories available. Events require a valid category.</p>";
        echo "<p><strong>Solution:</strong> Create at least one category first.</p>";
        echo "</div>";
    } else {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Active</th></tr>";
        foreach ($categories as $category) {
            $status = $category['is_active'] ? '✅ Active' : '❌ Inactive';
            echo "<tr><td>{$category['id']}</td><td>{$category['name']}</td><td>$status</td></tr>";
        }
        echo "</table>";
    }
    
    // Step 4: Test event creation
    echo "<h3>🧪 Step 4: Test Event Creation</h3>";
    
    if (!empty($categories)) {
        $first_category = $categories[0];
        
        echo "🔄 Testing event creation with category: <strong>{$first_category['name']}</strong> (ID: {$first_category['id']})<br>";
        
        $epic_event_scheduling = new EpicEventScheduling();
        
        $test_data = [
            'category_id' => $first_category['id'],
            'title' => 'Test Event - ' . date('Y-m-d H:i:s'),
            'description' => 'Test event untuk debugging masalah creation.',
            'location' => 'Online Test',
            'start_time' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_time' => date('Y-m-d H:i:s', strtotime('+1 day +2 hours')),
            'access_levels' => ['free'],
            'status' => 'published',
            'created_by' => epic_get_current_user_id() ?? 1
        ];
        
        echo "📋 Test data:<br>";
        echo "<ul>";
        foreach ($test_data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            echo "<li><strong>$key:</strong> $value</li>";
        }
        echo "</ul>";
        
        // Test creation
        echo "🔄 Attempting to create test event...<br>";
        
        $result = $epic_event_scheduling->createEvent($test_data);
        
        if ($result) {
            $event_id = $epic_db->lastInsertId();
            echo "✅ <strong>SUCCESS!</strong> Event created with ID: <strong>$event_id</strong><br>";
            
            // Verify the event was saved
            $stmt = $epic_db->prepare("SELECT * FROM epi_event_schedules WHERE id = ?");
            $stmt->execute([$event_id]);
            $saved_event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($saved_event) {
                echo "✅ Event verified in database<br>";
                echo "<strong>Saved event details:</strong><br>";
                echo "<ul>";
                echo "<li>ID: {$saved_event['id']}</li>";
                echo "<li>Title: {$saved_event['title']}</li>";
                echo "<li>Category ID: {$saved_event['category_id']}</li>";
                echo "<li>Status: {$saved_event['status']}</li>";
                echo "<li>Created: {$saved_event['created_at']}</li>";
                echo "</ul>";
            } else {
                echo "❌ Event not found after creation<br>";
            }
        } else {
            echo "❌ <strong>FAILED!</strong> Event creation failed<br>";
            
            // Get last error
            $error_info = $epic_db->errorInfo();
            if ($error_info[0] !== '00000') {
                echo "<strong>Database Error:</strong><br>";
                echo "<ul>";
                echo "<li>SQLSTATE: {$error_info[0]}</li>";
                echo "<li>Error Code: {$error_info[1]}</li>";
                echo "<li>Error Message: {$error_info[2]}</li>";
                echo "</ul>";
            }
        }
    }
    
    // Step 5: Check recent events
    echo "<h3>📊 Step 5: Recent Events</h3>";
    
    $stmt = $epic_db->query("
        SELECT e.*, c.name as category_name 
        FROM epi_event_schedules e 
        LEFT JOIN epic_event_categories c ON e.category_id = c.id 
        ORDER BY e.created_at DESC 
        LIMIT 5
    ");
    $recent_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Recent events (last 5): <strong>" . count($recent_events) . "</strong><br>";
    
    if (!empty($recent_events)) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Category</th><th>Status</th><th>Created</th></tr>";
        foreach ($recent_events as $event) {
            echo "<tr>";
            echo "<td>{$event['id']}</td>";
            echo "<td>" . htmlspecialchars($event['title']) . "</td>";
            echo "<td>" . htmlspecialchars($event['category_name'] ?? 'N/A') . "</td>";
            echo "<td>{$event['status']}</td>";
            echo "<td>{$event['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "📭 No events found in database<br>";
    }
    
    // Step 6: Test form data simulation
    echo "<h3>📝 Step 6: Form Data Simulation</h3>";
    
    if (!empty($categories)) {
        echo "🔄 Simulating form submission data...<br>";
        
        // Simulate typical form data
        $form_data = [
            'action' => 'create_event',
            'category_id' => $categories[0]['id'],
            'title' => 'Form Test Event',
            'description' => 'Event dari simulasi form submission',
            'location' => 'Online via Zoom',
            'start_time' => date('Y-m-d\TH:i', strtotime('+2 days')),
            'end_time' => date('Y-m-d\TH:i', strtotime('+2 days +3 hours')),
            'access_levels' => ['free', 'epic'],
            'status' => 'published',
            'registration_required' => '1',
            'event_url' => 'https://zoom.us/j/123456789',
            'notes' => 'Test notes dari admin'
        ];
        
        echo "📋 Simulated form data:<br>";
        echo "<ul>";
        foreach ($form_data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            echo "<li><strong>$key:</strong> $value</li>";
        }
        echo "</ul>";
        
        // Process like admin page would
        $processed_data = [
            'category_id' => intval($form_data['category_id']),
            'title' => $form_data['title'],
            'description' => $form_data['description'],
            'location' => $form_data['location'],
            'start_time' => str_replace('T', ' ', $form_data['start_time']) . ':00',
            'end_time' => str_replace('T', ' ', $form_data['end_time']) . ':00',
            'access_levels' => $form_data['access_levels'],
            'status' => $form_data['status'],
            'registration_required' => isset($form_data['registration_required']) ? 1 : 0,
            'event_url' => $form_data['event_url'],
            'notes' => $form_data['notes'],
            'created_by' => epic_get_current_user_id() ?? 1
        ];
        
        echo "🔄 Testing with processed form data...<br>";
        
        $result = $epic_event_scheduling->createEvent($processed_data);
        
        if ($result) {
            $event_id = $epic_db->lastInsertId();
            echo "✅ <strong>Form simulation SUCCESS!</strong> Event ID: <strong>$event_id</strong><br>";
        } else {
            echo "❌ <strong>Form simulation FAILED!</strong><br>";
            
            // Get detailed error
            $error_info = $epic_db->errorInfo();
            if ($error_info[0] !== '00000') {
                echo "<strong>Database Error Details:</strong><br>";
                echo "<pre>" . print_r($error_info, true) . "</pre>";
            }
        }
    }
    
    // Step 7: Recommendations
    echo "<h3>💡 Step 7: Recommendations</h3>";
    
    if (empty($categories)) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>🎯 Primary Issue: No Categories</h4>";
        echo "<p><strong>Problem:</strong> Events require a valid category, but no categories are available.</p>";
        echo "<p><strong>Solution:</strong></p>";
        echo "<ol>";
        echo "<li>Go to Event Scheduling → Categories tab</li>";
        echo "<li>Create at least one category</li>";
        echo "<li>Try creating an event again</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>✅ System Status: Good</h4>";
        echo "<p>Database connection and event creation are working properly.</p>";
        echo "<p><strong>If you're still experiencing issues:</strong></p>";
        echo "<ol>";
        echo "<li>Check browser console for JavaScript errors</li>";
        echo "<li>Verify all required form fields are filled</li>";
        echo "<li>Ensure start time is before end time</li>";
        echo "<li>Check that at least one access level is selected</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Debug Failed</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>🔗 Quick Links</h2>";
echo "<p>";
echo "<a href='" . epic_url('admin/event-scheduling') . "' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📅 Event Scheduling</a>";
echo "<a href='" . epic_url('admin/event-scheduling-add') . "' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>➕ Add Event</a>";
echo "<a href='" . epic_url('admin') . "' style='display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>🏠 Admin Dashboard</a>";
echo "</p>";

echo "<p><strong>Debug completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>