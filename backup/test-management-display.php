<?php
/**
 * Test Management Display - Verifikasi tampilan data di halaman manajemen
 * Script untuk mengecek apakah data event ditampilkan dengan benar di halaman admin
 */

require_once 'bootstrap.php';
require_once EPIC_PATH . '/core/event-scheduling.php';

// Initialize event scheduling
global $epic_event_scheduling;

echo "<h1>Test Management Display System</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
    .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .event-preview { border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 5px; }
</style>";

// Test 1: Simulate page data loading
echo "<div class='test-section'>";
echo "<h2>Test 1: Simulate Admin Page Data Loading</h2>";
try {
    // Simulate the exact same data loading as admin page
    $categories = $epic_event_scheduling->getEventCategories();
    $events_data = $epic_event_scheduling->getEvents(1, 20);
    $events = $events_data['events'];
    
    echo "<div class='success'>✓ Data loaded successfully</div>";
    echo "<div class='info'>";
    echo "Categories: " . count($categories) . " items<br>";
    echo "Events: " . count($events) . " items<br>";
    echo "Total Events: " . $events_data['total'] . "<br>";
    echo "Total Pages: " . $events_data['total_pages'] . "<br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Data loading failed: " . $e->getMessage() . "</div>";
    exit;
}
echo "</div>";

// Test 2: Check event data structure
echo "<div class='test-section'>";
echo "<h2>Test 2: Event Data Structure Validation</h2>";
if (!empty($events)) {
    $sample_event = $events[0];
    $required_fields = ['id', 'title', 'description', 'category_id', 'start_time', 'end_time', 'status', 'access_levels'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($sample_event[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (empty($missing_fields)) {
        echo "<div class='success'>✓ All required fields present</div>";
        echo "<h3>Sample Event Data:</h3>";
        echo "<pre>" . json_encode($sample_event, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<div class='error'>✗ Missing fields: " . implode(', ', $missing_fields) . "</div>";
    }
} else {
    echo "<div class='warning'>⚠ No events found to validate</div>";
}
echo "</div>";

// Test 3: Test helper functions
echo "<div class='test-section'>";
echo "<h2>Test 3: Helper Functions Test</h2>";
try {
    // Test status badge function
    if (function_exists('epic_get_event_schedule_status_badge')) {
        echo "<div class='success'>✓ Status badge function available</div>";
        echo "<p>Draft badge: " . epic_get_event_schedule_status_badge('draft') . "</p>";
        echo "<p>Published badge: " . epic_get_event_schedule_status_badge('published') . "</p>";
    } else {
        echo "<div class='warning'>⚠ Status badge function not found</div>";
    }
    
    // Test access level formatting
    if (!empty($events)) {
        $sample_event = $events[0];
        $access_levels = json_decode($sample_event['access_levels'], true);
        if (is_array($access_levels)) {
            echo "<div class='success'>✓ Access levels properly formatted as JSON array</div>";
            echo "<p>Access levels: " . implode(', ', $access_levels) . "</p>";
        } else {
            echo "<div class='warning'>⚠ Access levels not in expected format</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Helper function test failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 4: Simulate table rendering
echo "<div class='test-section'>";
echo "<h2>Test 4: Table Rendering Simulation</h2>";
if (!empty($events)) {
    echo "<div class='success'>✓ Rendering event table</div>";
    echo "<table>";
    echo "<tr>";
    echo "<th>Event Title</th>";
    echo "<th>Description</th>";
    echo "<th>Category</th>";
    echo "<th>Access Level</th>";
    echo "<th>Date & Time</th>";
    echo "<th>Status</th>";
    echo "</tr>";
    
    foreach (array_slice($events, 0, 5) as $event) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($event['title']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($event['description'], 0, 50)) . "...</td>";
        echo "<td>" . htmlspecialchars($event['category_name'] ?? 'N/A') . "</td>";
        
        // Format access levels
        $access_levels = json_decode($event['access_levels'], true);
        if (is_array($access_levels)) {
            echo "<td>" . htmlspecialchars(implode(', ', $access_levels)) . "</td>";
        } else {
            echo "<td>" . htmlspecialchars($event['access_levels']) . "</td>";
        }
        
        // Format date
        $start_time = new DateTime($event['start_time']);
        $end_time = new DateTime($event['end_time']);
        echo "<td>" . $start_time->format('d M Y H:i') . " - " . $end_time->format('H:i') . "</td>";
        
        // Status badge
        if (function_exists('epic_get_event_schedule_status_badge')) {
            echo "<td>" . epic_get_event_schedule_status_badge($event['status']) . "</td>";
        } else {
            echo "<td>" . htmlspecialchars($event['status']) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='warning'>⚠ No events to render</div>";
}
echo "</div>";

// Test 5: Check for potential display issues
echo "<div class='test-section'>";
echo "<h2>Test 5: Display Issues Check</h2>";
$issues = [];

if (!empty($events)) {
    foreach ($events as $event) {
        // Check for empty titles
        if (empty(trim($event['title']))) {
            $issues[] = "Event ID {$event['id']} has empty title";
        }
        
        // Check for invalid dates
        if (!strtotime($event['start_time']) || !strtotime($event['end_time'])) {
            $issues[] = "Event ID {$event['id']} has invalid date format";
        }
        
        // Check for invalid JSON in access_levels
        $access_levels = json_decode($event['access_levels'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $issues[] = "Event ID {$event['id']} has invalid access_levels JSON";
        }
        
        // Check for missing category
        if (empty($event['category_name']) && !empty($event['category_id'])) {
            $issues[] = "Event ID {$event['id']} has missing category data";
        }
    }
}

if (empty($issues)) {
    echo "<div class='success'>✓ No display issues found</div>";
} else {
    echo "<div class='warning'>⚠ Found " . count($issues) . " potential issues:</div>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>" . htmlspecialchars($issue) . "</li>";
    }
    echo "</ul>";
}
echo "</div>";

// Test 6: Performance check
echo "<div class='test-section'>";
echo "<h2>Test 6: Performance Check</h2>";
$start_time = microtime(true);

// Simulate multiple page loads
for ($i = 1; $i <= 3; $i++) {
    $test_events = $epic_event_scheduling->getEvents($i, 10);
}

$end_time = microtime(true);
$execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds

if ($execution_time < 500) {
    echo "<div class='success'>✓ Good performance: {$execution_time}ms for 3 page loads</div>";
} elseif ($execution_time < 1000) {
    echo "<div class='warning'>⚠ Acceptable performance: {$execution_time}ms for 3 page loads</div>";
} else {
    echo "<div class='error'>✗ Slow performance: {$execution_time}ms for 3 page loads</div>";
}
echo "</div>";

echo "<div class='test-section info'>";
echo "<h2>Test Summary</h2>";
echo "<p><strong>Management display test completed!</strong></p>";
echo "<p>Data loading: " . (empty($events) ? "No events" : count($events) . " events loaded") . "</p>";
echo "<p>Issues found: " . count($issues) . "</p>";
echo "<p><a href='admin/event-scheduling' target='_blank'>→ Open Actual Management Page</a></p>";
echo "</div>";
?>