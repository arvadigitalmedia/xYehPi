<?php
/**
 * Test Event Display - Verifikasi tampilan data event
 * Script untuk mengecek apakah data event ditampilkan dengan benar
 */

require_once 'bootstrap.php';
require_once EPIC_PATH . '/core/event-scheduling.php';

// Initialize event scheduling
global $epic_event_scheduling;

echo "<h1>Test Event Display System</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// Test 1: Database Connection
echo "<div class='test-section'>";
echo "<h2>Test 1: Database Connection</h2>";
try {
    $pdo = db()->getConnection();
    echo "<div class='success'>✓ Database connection successful</div>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Database connection failed: " . $e->getMessage() . "</div>";
    exit;
}
echo "</div>";

// Test 2: Check if events exist in database
echo "<div class='test-section'>";
echo "<h2>Test 2: Raw Database Query</h2>";
try {
    $stmt = $pdo->prepare("
        SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon
        FROM epi_event_schedules e
        LEFT JOIN epic_event_categories c ON e.category_id = c.id
        ORDER BY e.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $raw_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($raw_events) {
        echo "<div class='success'>✓ Found " . count($raw_events) . " events in database</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Category</th><th>Status</th><th>Start Time</th><th>Access Levels</th></tr>";
        foreach ($raw_events as $event) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($event['id']) . "</td>";
            echo "<td>" . htmlspecialchars($event['title']) . "</td>";
            echo "<td>" . htmlspecialchars($event['category_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($event['status']) . "</td>";
            echo "<td>" . htmlspecialchars($event['start_time']) . "</td>";
            echo "<td>" . htmlspecialchars($event['access_levels']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>✗ No events found in database</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Database query failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 3: Test getEvents() function
echo "<div class='test-section'>";
echo "<h2>Test 3: getEvents() Function</h2>";
try {
    $events_data = $epic_event_scheduling->getEvents(1, 10);
    
    if ($events_data && isset($events_data['events'])) {
        $events = $events_data['events'];
        echo "<div class='success'>✓ getEvents() function works - Found " . count($events) . " events</div>";
        echo "<div class='info'>Total: " . $events_data['total'] . " | Page: " . $events_data['page'] . " | Total Pages: " . $events_data['total_pages'] . "</div>";
        
        if (!empty($events)) {
            echo "<h3>Event Data Structure:</h3>";
            echo "<pre>" . json_encode($events[0], JSON_PRETTY_PRINT) . "</pre>";
        }
    } else {
        echo "<div class='error'>✗ getEvents() function returned empty or invalid data</div>";
        echo "<pre>Result: " . json_encode($events_data) . "</pre>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ getEvents() function failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 4: Test getEventCategories() function
echo "<div class='test-section'>";
echo "<h2>Test 4: getEventCategories() Function</h2>";
try {
    $categories = $epic_event_scheduling->getEventCategories();
    
    if ($categories) {
        echo "<div class='success'>✓ getEventCategories() function works - Found " . count($categories) . " categories</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Color</th><th>Icon</th><th>Access Levels</th></tr>";
        foreach ($categories as $category) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($category['id']) . "</td>";
            echo "<td>" . htmlspecialchars($category['name']) . "</td>";
            echo "<td>" . htmlspecialchars($category['color']) . "</td>";
            echo "<td>" . htmlspecialchars($category['icon']) . "</td>";
            echo "<td>" . htmlspecialchars($category['access_levels']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>✗ getEventCategories() function returned empty data</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ getEventCategories() function failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 5: Test helper function for status badge
echo "<div class='test-section'>";
echo "<h2>Test 5: Helper Functions</h2>";
try {
    // Test status badge function
    if (function_exists('epic_get_event_schedule_status_badge')) {
        echo "<div class='success'>✓ epic_get_event_schedule_status_badge() function exists</div>";
        echo "<p>Draft: " . epic_get_event_schedule_status_badge('draft') . "</p>";
        echo "<p>Published: " . epic_get_event_schedule_status_badge('published') . "</p>";
    } else {
        echo "<div class='error'>✗ epic_get_event_schedule_status_badge() function not found</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Helper function test failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 6: Simulate page rendering
echo "<div class='test-section'>";
echo "<h2>Test 6: Page Rendering Simulation</h2>";
try {
    // Get data like the actual page does
    $categories = $epic_event_scheduling->getEventCategories();
    $events_data = $epic_event_scheduling->getEvents(1, 20);
    $events = $events_data['events'];
    $total_pages = $events_data['total_pages'];
    
    echo "<div class='info'>Data prepared for page rendering:</div>";
    echo "<ul>";
    echo "<li>Categories: " . count($categories) . " items</li>";
    echo "<li>Events: " . count($events) . " items</li>";
    echo "<li>Total Pages: " . $total_pages . "</li>";
    echo "</ul>";
    
    if (!empty($events)) {
        echo "<div class='success'>✓ Page data ready for rendering</div>";
        echo "<h3>Sample Event Display:</h3>";
        $sample_event = $events[0];
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<strong>" . htmlspecialchars($sample_event['title']) . "</strong><br>";
        echo "Category: " . htmlspecialchars($sample_event['category_name'] ?? 'N/A') . "<br>";
        echo "Status: " . htmlspecialchars($sample_event['status']) . "<br>";
        echo "Date: " . date('d M Y H:i', strtotime($sample_event['start_time'])) . "<br>";
        echo "Access: " . htmlspecialchars($sample_event['access_levels']) . "<br>";
        echo "</div>";
    } else {
        echo "<div class='error'>✗ No events available for rendering</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Page rendering simulation failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

echo "<div class='test-section info'>";
echo "<h2>Test Summary</h2>";
echo "<p><strong>Test completed!</strong> Check results above for any issues.</p>";
echo "<p><a href='admin/event-scheduling' target='_blank'>→ Open Event Management Page</a></p>";
echo "</div>";
?>