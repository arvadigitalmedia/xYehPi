<?php
/**
 * Test Event Form Submission
 * Script untuk menguji form submission event scheduling secara langsung
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/event-scheduling.php';

// Set header untuk output yang bersih
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🧪 Test Event Form Submission</h1>";
echo "<hr>";

// Check admin access
if (!epic_is_admin()) {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; border-radius: 5px;'>";
    echo "❌ <strong>Access Denied</strong><br>";
    echo "You must be logged in as an administrator to run this test.";
    echo "</div>";
    echo "<p><a href='" . epic_url('login') . "'>Login as Admin</a></p>";
    exit;
}

echo "<h2>📋 Form Submission Test</h2>";

try {
    global $epic_db;
    
    if (!$epic_db) {
        throw new Exception('Database connection not available');
    }
    
    echo "✅ Database connection: <strong>OK</strong><br>";
    
    // Get available categories
    $stmt = $epic_db->query("SELECT id, name, is_active FROM epic_event_categories WHERE is_active = 1 ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Available categories: <strong>" . count($categories) . "</strong><br>";
    
    if (empty($categories)) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ No Categories Available</h3>";
        echo "<p>Cannot test event creation without categories. Please create at least one category first.</p>";
        echo "<p><a href='" . epic_url('admin/event-scheduling') . "'>Go to Event Scheduling</a></p>";
        echo "</div>";
        exit;
    }
    
    // Display available categories
    echo "<h3>📁 Available Categories:</h3>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>ID: {$category['id']} - {$category['name']} (" . ($category['is_active'] ? 'Active' : 'Inactive') . ")</li>";
    }
    echo "</ul>";
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h3>📝 Processing Form Submission</h3>";
        
        echo "<h4>🔍 Received Data:</h4>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th><th>Type</th></tr>";
        
        foreach ($_POST as $key => $value) {
            $display_value = is_array($value) ? json_encode($value) : htmlspecialchars($value);
            $type = is_array($value) ? 'array' : gettype($value);
            echo "<tr><td>$key</td><td>$display_value</td><td>$type</td></tr>";
        }
        echo "</table>";
        
        // Validate required fields
        echo "<h4>✅ Validation Results:</h4>";
        $validation_errors = [];
        
        if (empty($_POST['title'])) {
            $validation_errors[] = 'Title is required';
        }
        
        if (empty($_POST['category_id'])) {
            $validation_errors[] = 'Category is required';
        }
        
        if (empty($_POST['start_time'])) {
            $validation_errors[] = 'Start time is required';
        }
        
        if (empty($_POST['end_time'])) {
            $validation_errors[] = 'End time is required';
        }
        
        if (empty($_POST['access_levels']) || !is_array($_POST['access_levels'])) {
            $validation_errors[] = 'At least one access level must be selected';
        }
        
        // Date validation
        if (!empty($_POST['start_time']) && !empty($_POST['end_time'])) {
            $start_time = strtotime($_POST['start_time']);
            $end_time = strtotime($_POST['end_time']);
            
            if ($start_time === false || $end_time === false) {
                $validation_errors[] = 'Invalid date format';
            } elseif ($end_time <= $start_time) {
                $validation_errors[] = 'End time must be after start time';
            }
        }
        
        // Category validation
        if (!empty($_POST['category_id'])) {
            $stmt = $epic_db->prepare("SELECT id FROM epic_event_categories WHERE id = ? AND is_active = 1");
            $stmt->execute([$_POST['category_id']]);
            if (!$stmt->fetch()) {
                $validation_errors[] = 'Selected category does not exist or is inactive';
            }
        }
        
        if (empty($validation_errors)) {
            echo "✅ All validations passed<br>";
            
            // Try to create event
            echo "<h4>🚀 Creating Event:</h4>";
            
            $epic_event_scheduling = new EpicEventScheduling();
            
            $event_data = [
                'category_id' => intval($_POST['category_id']),
                'title' => epic_sanitize($_POST['title']),
                'description' => epic_sanitize($_POST['description'] ?? ''),
                'location' => epic_sanitize($_POST['location'] ?? ''),
                'start_time' => epic_sanitize($_POST['start_time']),
                'end_time' => epic_sanitize($_POST['end_time']),
                'max_participants' => !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null,
                'registration_required' => isset($_POST['registration_required']) ? 1 : 0,
                'access_levels' => $_POST['access_levels'],
                'status' => epic_sanitize($_POST['status'] ?? 'published'),
                'event_url' => epic_sanitize($_POST['event_url'] ?? ''),
                'notes' => epic_sanitize($_POST['notes'] ?? ''),
                'created_by' => epic_get_current_user_id() ?? 1
            ];
            
            echo "📋 Processed event data:<br>";
            echo "<pre>" . print_r($event_data, true) . "</pre>";
            
            $result = $epic_event_scheduling->createEvent($event_data);
            
            if ($result) {
                $event_id = $epic_db->lastInsertId();
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h3>🎉 SUCCESS!</h3>";
                echo "<p>Event created successfully with ID: <strong>$event_id</strong></p>";
                echo "<p><a href='" . epic_url('admin/event-scheduling') . "'>View in Event Management</a></p>";
                echo "</div>";
                
                // Verify the event was saved
                $stmt = $epic_db->prepare("SELECT * FROM epi_event_schedules WHERE id = ?");
                $stmt->execute([$event_id]);
                $saved_event = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($saved_event) {
                    echo "<h4>✅ Event Verification:</h4>";
                    echo "<ul>";
                    echo "<li>ID: {$saved_event['id']}</li>";
                    echo "<li>Title: {$saved_event['title']}</li>";
                    echo "<li>Category ID: {$saved_event['category_id']}</li>";
                    echo "<li>Status: {$saved_event['status']}</li>";
                    echo "<li>Created: {$saved_event['created_at']}</li>";
                    echo "</ul>";
                }
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h3>❌ FAILED!</h3>";
                echo "<p>Event creation failed. Check error logs for details.</p>";
                
                // Get database error info
                $error_info = $epic_db->errorInfo();
                if ($error_info[0] !== '00000') {
                    echo "<p><strong>Database Error:</strong></p>";
                    echo "<ul>";
                    echo "<li>SQLSTATE: {$error_info[0]}</li>";
                    echo "<li>Error Code: {$error_info[1]}</li>";
                    echo "<li>Error Message: {$error_info[2]}</li>";
                    echo "</ul>";
                }
                echo "</div>";
            }
        } else {
            echo "❌ Validation failed:<br>";
            echo "<ul>";
            foreach ($validation_errors as $error) {
                echo "<li style='color: red;'>$error</li>";
            }
            echo "</ul>";
        }
    }
    
    // Display test form
    echo "<h3>📝 Test Form</h3>";
    echo "<p>Fill out this form to test event creation:</p>";
    
    ?>
    
    <form method="POST" style="max-width: 600px; margin: 20px 0;">
        <div style="margin-bottom: 15px;">
            <label for="title" style="display: block; margin-bottom: 5px; font-weight: bold;">Event Title *</label>
            <input type="text" id="title" name="title" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?= htmlspecialchars($_POST['title'] ?? 'Test Event - ' . date('Y-m-d H:i:s')) ?>">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="category_id" style="display: block; margin-bottom: 5px; font-weight: bold;">Category *</label>
            <select id="category_id" name="category_id" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="description" style="display: block; margin-bottom: 5px; font-weight: bold;">Description</label>
            <textarea id="description" name="description" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($_POST['description'] ?? 'Test event description for debugging purposes.') ?></textarea>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="location" style="display: block; margin-bottom: 5px; font-weight: bold;">Location</label>
            <input type="text" id="location" name="location" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?= htmlspecialchars($_POST['location'] ?? 'Online Test') ?>">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label for="start_time" style="display: block; margin-bottom: 5px; font-weight: bold;">Start Time *</label>
                <input type="datetime-local" id="start_time" name="start_time" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?= $_POST['start_time'] ?? date('Y-m-d\TH:i', strtotime('+1 day')) ?>">
            </div>
            <div>
                <label for="end_time" style="display: block; margin-bottom: 5px; font-weight: bold;">End Time *</label>
                <input type="datetime-local" id="end_time" name="end_time" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?= $_POST['end_time'] ?? date('Y-m-d\TH:i', strtotime('+1 day +2 hours')) ?>">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Access Levels *</label>
            <div style="display: flex; gap: 15px;">
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" name="access_levels[]" value="free" <?= (isset($_POST['access_levels']) && in_array('free', $_POST['access_levels'])) ? 'checked' : 'checked' ?>>
                    Free Account
                </label>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" name="access_levels[]" value="epic" <?= (isset($_POST['access_levels']) && in_array('epic', $_POST['access_levels'])) ? 'checked' : '' ?>>
                    EPIC Account
                </label>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" name="access_levels[]" value="epis" <?= (isset($_POST['access_levels']) && in_array('epis', $_POST['access_levels'])) ? 'checked' : '' ?>>
                    EPIS Account
                </label>
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="status" style="display: block; margin-bottom: 5px; font-weight: bold;">Status</label>
            <select id="status" name="status" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="published" <?= (isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : 'selected' ?>>Published</option>
                <option value="draft" <?= (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="event_url" style="display: block; margin-bottom: 5px; font-weight: bold;">Event URL</label>
            <input type="url" id="event_url" name="event_url" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" value="<?= htmlspecialchars($_POST['event_url'] ?? 'https://zoom.us/j/123456789') ?>">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: flex; align-items: center; gap: 5px;">
                <input type="checkbox" name="registration_required" value="1" <?= (isset($_POST['registration_required'])) ? 'checked' : '' ?>>
                Require Registration
            </label>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="notes" style="display: block; margin-bottom: 5px; font-weight: bold;">Admin Notes</label>
            <textarea id="notes" name="notes" rows="2" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($_POST['notes'] ?? 'Test notes for debugging') ?></textarea>
        </div>
        
        <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">Create Test Event</button>
    </form>
    
    <?php
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Test Failed</h3>";
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
echo "<a href='/debug-event-creation.php' style='display: inline-block; padding: 10px 20px; background: #ffc107; color: black; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔍 Debug Tool</a>";
echo "<a href='" . epic_url('admin') . "' style='display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>🏠 Admin Dashboard</a>";
echo "</p>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>