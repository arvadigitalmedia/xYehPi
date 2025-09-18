<?php
/**
 * EPIC Hub Admin - Event Scheduling Management
 * Halaman admin untuk mengelola event scheduling tanpa integrasi Zoom
 */

if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/../themes/modern/admin/routing-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/event-scheduling');
$user = $init_result['user'];

// Load Event Scheduling core
require_once EPIC_PATH . '/core/event-scheduling.php';
global $epic_event_scheduling;

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'create_category':
            // Validate input
            if (empty($_POST['name'])) {
                echo json_encode(['success' => false, 'message' => 'Category name is required']);
                exit;
            }
            
            if (empty($_POST['access_levels']) || !is_array($_POST['access_levels'])) {
                echo json_encode(['success' => false, 'message' => 'At least one access level must be selected']);
                exit;
            }
            
            $result = $epic_event_scheduling->createEventCategory([
                'name' => epic_sanitize($_POST['name']),
                'description' => epic_sanitize($_POST['description']),
                'access_levels' => $_POST['access_levels'],
                'color' => epic_sanitize($_POST['color']),
                'icon' => epic_sanitize($_POST['icon']),
                'created_by' => epic_get_current_user_id()
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Category created successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create category. Category name may already exist.']);
            }
            exit;
            
        case 'update_category':
            // Validate input
            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'Category ID is required']);
                exit;
            }
            
            if (empty($_POST['name'])) {
                echo json_encode(['success' => false, 'message' => 'Category name is required']);
                exit;
            }
            
            if (empty($_POST['access_levels']) || !is_array($_POST['access_levels'])) {
                echo json_encode(['success' => false, 'message' => 'At least one access level must be selected']);
                exit;
            }
            
            $result = $epic_event_scheduling->updateEventCategory($_POST['id'], [
                'name' => epic_sanitize($_POST['name']),
                'description' => epic_sanitize($_POST['description']),
                'access_levels' => $_POST['access_levels'],
                'color' => epic_sanitize($_POST['color']),
                'icon' => epic_sanitize($_POST['icon']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update category. Category name may already exist.']);
            }
            exit;
            
        case 'delete_category':
            // Validate input
            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'Category ID is required']);
                exit;
            }
            
            $result = $epic_event_scheduling->deleteEventCategory($_POST['id']);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete category. Category may have associated events.']);
            }
            exit;
            
        case 'get_category':
            $category = $epic_event_scheduling->getEventCategory($_POST['id']);
            if ($category) {
                echo json_encode(['success' => true, 'category' => $category]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Category not found']);
            }
            exit;
            
        case 'create_event':
            // Validate input
            if (empty($_POST['title'])) {
                echo json_encode(['success' => false, 'message' => 'Event title is required']);
                exit;
            }
            
            if (empty($_POST['category_id'])) {
                echo json_encode(['success' => false, 'message' => 'Event category is required']);
                exit;
            }
            
            if (empty($_POST['start_time']) || empty($_POST['end_time'])) {
                echo json_encode(['success' => false, 'message' => 'Start time and end time are required']);
                exit;
            }
            
            if (empty($_POST['access_levels']) || !is_array($_POST['access_levels'])) {
                echo json_encode(['success' => false, 'message' => 'At least one access level must be selected']);
                exit;
            }
            
            // Validate date logic
            $start_time = strtotime($_POST['start_time']);
            $end_time = strtotime($_POST['end_time']);
            
            if ($start_time === false || $end_time === false) {
                echo json_encode(['success' => false, 'message' => 'Invalid date format']);
                exit;
            }
            
            if ($end_time <= $start_time) {
                echo json_encode(['success' => false, 'message' => 'End time must be after start time']);
                exit;
            }
            
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
                'status' => epic_sanitize($_POST['status']) ?: 'published',
                'event_url' => epic_sanitize($_POST['event_url']),
                'notes' => epic_sanitize($_POST['notes']),
                'created_by' => epic_get_current_user_id()
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Event created successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create event. Please check that the category exists and all required fields are filled correctly.']);
            }
            exit;
            
        case 'update_event':
            $result = $epic_event_scheduling->updateEvent($_POST['id'], [
                'category_id' => intval($_POST['category_id']),
                'title' => epic_sanitize($_POST['title']),
                'description' => epic_sanitize($_POST['description']),
                'location' => epic_sanitize($_POST['location']),
                'start_time' => epic_sanitize($_POST['start_time']),
                'end_time' => epic_sanitize($_POST['end_time']),
                'max_participants' => !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null,
                'registration_required' => isset($_POST['registration_required']) ? 1 : 0,
                'access_levels' => $_POST['access_levels'] ?? ['free'],
                'status' => epic_sanitize($_POST['status']),
                'event_url' => epic_sanitize($_POST['event_url']),
                'notes' => epic_sanitize($_POST['notes'])
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'delete_event':
            $result = $epic_event_scheduling->deleteEvent($_POST['id']);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'get_event':
            $event = $epic_event_scheduling->getEvent($_POST['id']);
            if ($event) {
                echo json_encode(['success' => true, 'event' => $event]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Event not found']);
            }
            exit;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            exit;
    }
}

// Get data for page
$categories = $epic_event_scheduling->getEventCategories();
$events_data = $epic_event_scheduling->getEvents(1, 20);
$events = $events_data['events'];
$total_pages = $events_data['total_pages'];

// Use admin layout system
require_once __DIR__ . '/../themes/modern/admin/layout-helper.php';

// Prepare layout data
$layout_data = [
    'page_title' => 'Event Scheduling - EPIC Hub Admin',
    'header_title' => 'Event Scheduling',
    'current_page' => 'manage',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => '#'],
        ['text' => 'Event Scheduling']
    ],
    'categories' => $categories,
    'events' => $events,
    'total_pages' => $total_pages
];

// Render admin page
epic_render_admin_page(__DIR__ . '/../themes/modern/admin/content/event-scheduling-content.php', $layout_data);
?>