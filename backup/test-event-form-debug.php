<?php
/**
 * Debug Script untuk Event Scheduling Form
 * Test form submission tanpa autentikasi admin
 */

// Bootstrap sistem
require_once __DIR__ . '/bootstrap.php';

// Load Event Scheduling core
require_once EPIC_PATH . '/core/event-scheduling.php';

echo "<h1>Event Scheduling Form Debug</h1>";

// Test 1: Cek koneksi database
echo "<h2>1. Database Connection Test</h2>";
try {
    global $epic_db;
    if ($epic_db) {
        echo "✅ Database connection: OK<br>";
        
        // Test query
        $stmt = $epic_db->query("SELECT COUNT(*) as count FROM epic_event_categories");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Categories table accessible: " . $result['count'] . " categories found<br>";
        
        $stmt = $epic_db->query("SHOW TABLES LIKE 'epi_event_schedules'");
        if ($stmt->fetch()) {
            echo "✅ Event schedules table exists<br>";
        } else {
            echo "❌ Event schedules table NOT found<br>";
        }
    } else {
        echo "❌ Database connection: FAILED<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: Cek Event Scheduling Class
echo "<h2>2. Event Scheduling Class Test</h2>";
try {
    $epic_event_scheduling = new EpicEventScheduling();
    echo "✅ EpicEventScheduling class: OK<br>";
    
    // Test get categories
    $categories = $epic_event_scheduling->getEventCategories();
    echo "✅ Get categories: " . count($categories) . " categories loaded<br>";
    
    foreach ($categories as $cat) {
        echo "   - " . $cat['name'] . " (ID: " . $cat['id'] . ")<br>";
    }
} catch (Exception $e) {
    echo "❌ Event Scheduling class error: " . $e->getMessage() . "<br>";
}

// Test 3: Simulasi Form Submission
echo "<h2>3. Form Submission Simulation</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_submit'])) {
    echo "<h3>Processing Form Data:</h3>";
    
    // Debug POST data
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Validate input seperti di file asli
    $errors = [];
    
    if (empty($_POST['title'])) {
        $errors[] = 'Event title is required.';
    }
    if (empty($_POST['category_id'])) {
        $errors[] = 'Event category is required.';
    }
    if (empty($_POST['start_time']) || empty($_POST['end_time'])) {
        $errors[] = 'Start time and end time are required.';
    }
    if (empty($_POST['access_levels']) || !is_array($_POST['access_levels'])) {
        $errors[] = 'At least one access level must be selected.';
    }
    
    if (!empty($errors)) {
        echo "<h4>❌ Validation Errors:</h4>";
        foreach ($errors as $error) {
            echo "- " . $error . "<br>";
        }
    } else {
        echo "<h4>✅ Validation Passed</h4>";
        
        // Test date validation
        $start_time = strtotime($_POST['start_time']);
        $end_time = strtotime($_POST['end_time']);
        
        if ($start_time === false || $end_time === false) {
            echo "❌ Invalid date format<br>";
        } elseif ($end_time <= $start_time) {
            echo "❌ End time must be after start time<br>";
        } else {
            echo "✅ Date validation passed<br>";
            
            // Format datetime untuk database
            $start_time_formatted = $_POST['start_time'];
            $end_time_formatted = $_POST['end_time'];
            
            // Convert datetime-local format to MySQL datetime format
            if (strpos($start_time_formatted, 'T') !== false) {
                $start_time_formatted = str_replace('T', ' ', $start_time_formatted) . ':00';
            }
            if (strpos($end_time_formatted, 'T') !== false) {
                $end_time_formatted = str_replace('T', ' ', $end_time_formatted) . ':00';
            }
            
            echo "Formatted start time: " . $start_time_formatted . "<br>";
            echo "Formatted end time: " . $end_time_formatted . "<br>";
            
            // Test create event
            try {
                $event_data = [
                    'category_id' => intval($_POST['category_id']),
                    'title' => $_POST['title'],
                    'description' => $_POST['description'] ?? '',
                    'location' => $_POST['location'] ?? '',
                    'start_time' => $start_time_formatted,
                    'end_time' => $end_time_formatted,
                    'max_participants' => !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null,
                    'registration_required' => isset($_POST['registration_required']) ? 1 : 0,
                    'access_levels' => $_POST['access_levels'],
                    'status' => $_POST['status'] ?? 'draft',
                    'event_url' => $_POST['event_url'] ?? '',
                    'notes' => $_POST['notes'] ?? '',
                    'created_by' => 1 // Test user ID
                ];
                
                echo "<h4>Event Data to be Created:</h4>";
                echo "<pre>";
                print_r($event_data);
                echo "</pre>";
                
                $result = $epic_event_scheduling->createEvent($event_data);
                
                if ($result) {
                    echo "<h4>✅ Event Created Successfully!</h4>";
                    echo "Event ID: " . $epic_db->lastInsertId() . "<br>";
                } else {
                    echo "<h4>❌ Event Creation Failed</h4>";
                    $error_info = $epic_db->errorInfo();
                    echo "Database Error: " . $error_info[2] . "<br>";
                }
                
            } catch (Exception $e) {
                echo "<h4>❌ Exception during event creation:</h4>";
                echo $e->getMessage() . "<br>";
            }
        }
    }
}

// Test Form
echo "<h2>4. Test Form</h2>";
?>

<form method="POST" style="max-width: 600px; margin: 20px 0;">
    <input type="hidden" name="test_submit" value="1">
    
    <div style="margin-bottom: 15px;">
        <label>Event Title *</label><br>
        <input type="text" name="title" required style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Category *</label><br>
        <select name="category_id" required style="width: 100%; padding: 8px;">
            <option value="">Select Category</option>
            <?php
            if (isset($categories)) {
                foreach ($categories as $cat) {
                    echo "<option value='" . $cat['id'] . "'>" . htmlspecialchars($cat['name']) . "</option>";
                }
            }
            ?>
        </select>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Description</label><br>
        <textarea name="description" style="width: 100%; padding: 8px; height: 80px;"></textarea>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Location</label><br>
        <input type="text" name="location" style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Start Time *</label><br>
        <input type="datetime-local" name="start_time" required style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>End Time *</label><br>
        <input type="datetime-local" name="end_time" required style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Max Participants</label><br>
        <input type="number" name="max_participants" style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Access Levels *</label><br>
        <label><input type="checkbox" name="access_levels[]" value="free"> Free</label><br>
        <label><input type="checkbox" name="access_levels[]" value="premium"> Premium</label><br>
        <label><input type="checkbox" name="access_levels[]" value="vip"> VIP</label><br>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Status</label><br>
        <select name="status" style="width: 100%; padding: 8px;">
            <option value="draft">Draft</option>
            <option value="published">Published</option>
        </select>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Event URL</label><br>
        <input type="url" name="event_url" style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label><input type="checkbox" name="registration_required" value="1"> Registration Required</label>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>Notes</label><br>
        <textarea name="notes" style="width: 100%; padding: 8px; height: 60px;"></textarea>
    </div>
    
    <button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer;">
        Test Create Event
    </button>
</form>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3, h4 { color: #333; }
label { font-weight: bold; }
</style>