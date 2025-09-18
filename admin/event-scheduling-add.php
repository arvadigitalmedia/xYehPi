<?php
/**
 * EPIC Hub Admin - Add New Event Scheduling
 * Halaman standalone untuk menambah event scheduling baru
 */

if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/../themes/modern/admin/routing-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/event-scheduling-add');
$user = $init_result['user'];

// Load Event Scheduling core
require_once EPIC_PATH . '/core/event-scheduling.php';
global $epic_event_scheduling;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_event') {
    // Validate input
    if (empty($_POST['title'])) {
        $error = 'Event title is required.';
    } elseif (empty($_POST['category_id'])) {
        $error = 'Event category is required.';
    } elseif (empty($_POST['start_time']) || empty($_POST['end_time'])) {
        $error = 'Start time and end time are required.';
    } elseif (empty($_POST['access_levels']) || !is_array($_POST['access_levels'])) {
        $error = 'At least one access level must be selected.';
    } else {
        // Validate date logic
        $start_time = strtotime($_POST['start_time']);
        $end_time = strtotime($_POST['end_time']);
        
        if ($start_time === false || $end_time === false) {
            $error = 'Invalid date format.';
        } elseif ($end_time <= $start_time) {
            $error = 'End time must be after start time.';
        } else {
            // Format datetime properly for database
            $start_time = epic_sanitize($_POST['start_time']);
            $end_time = epic_sanitize($_POST['end_time']);
            
            // Convert datetime-local format to MySQL datetime format
            if (strpos($start_time, 'T') !== false) {
                $start_time = str_replace('T', ' ', $start_time) . ':00';
            }
            if (strpos($end_time, 'T') !== false) {
                $end_time = str_replace('T', ' ', $end_time) . ':00';
            }
            
            $result = $epic_event_scheduling->createEvent([
                'category_id' => intval($_POST['category_id']),
                'title' => epic_sanitize($_POST['title']),
                'description' => epic_sanitize($_POST['description']),
                'location' => epic_sanitize($_POST['location']),
                'start_time' => $start_time,
                'end_time' => $end_time,
                'max_participants' => !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null,
                'registration_required' => isset($_POST['registration_required']) ? 1 : 0,
                'access_levels' => $_POST['access_levels'],
                'status' => epic_sanitize($_POST['status']) ?: 'draft',
                'event_url' => epic_sanitize($_POST['event_url']),
                'notes' => epic_sanitize($_POST['notes']),
                'created_by' => epic_get_current_user_id()
            ]);
            
            if ($result) {
                // Redirect back to event scheduling page with success message
                $success_type = ($_POST['status'] === 'draft') ? 'event_draft_saved' : 'event_created';
                header('Location: ' . epic_url('admin/event-scheduling?success=' . $success_type));
                exit;
            } else {
                $error = 'Failed to create event. Please check that the category exists and all required fields are filled correctly.';
            }
        }
    }
}

// Get categories for dropdown
$categories = $epic_event_scheduling->getEventCategories();

// Use admin layout system
require_once __DIR__ . '/../themes/modern/admin/layout-helper.php';

// Prepare layout data
$layout_data = [
    'page_title' => 'Add New Event - EPIC Hub Admin',
    'header_title' => 'Add New Event',
    'current_page' => 'manage',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => '#'],
        ['text' => 'Event Scheduling', 'url' => epic_url('admin/event-scheduling')],
        ['text' => 'Add New Event']
    ],
    'categories' => $categories,
    'error' => $error ?? null
];

// Render admin page
epic_render_admin_page(__DIR__ . '/../themes/modern/admin/content/event-scheduling-add-content.php', $layout_data);
?>