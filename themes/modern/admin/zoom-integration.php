<?php
/**
 * EPIC Hub Admin - Zoom Integration Management
 * Halaman admin untuk mengelola event Zoom dan kategori
 */

if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/routing-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/zoom-integration');
$user = $init_result['user'];

// Load Zoom integration core
require_once EPIC_PATH . '/core/zoom-integration.php';
global $epic_zoom;

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'create_category':
            $result = $epic_zoom->createEventCategory([
                'name' => epic_sanitize($_POST['name']),
                'description' => epic_sanitize($_POST['description']),
                'access_levels' => $_POST['access_levels'],
                'color' => epic_sanitize($_POST['color']),
                'icon' => epic_sanitize($_POST['icon']),
                'created_by' => epic_get_current_user_id()
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'update_category':
            $result = $epic_zoom->updateEventCategory($_POST['id'], [
                'name' => epic_sanitize($_POST['name']),
                'description' => epic_sanitize($_POST['description']),
                'access_levels' => $_POST['access_levels'],
                'color' => epic_sanitize($_POST['color']),
                'icon' => epic_sanitize($_POST['icon']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'delete_category':
            $result = $epic_zoom->deleteEventCategory($_POST['id']);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'get_category':
            $category = $epic_zoom->getEventCategory($_POST['id']);
            if ($category) {
                echo json_encode(['success' => true, 'category' => $category]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Category not found']);
            }
            exit;
            
        case 'create_event':
            $result = $epic_zoom->createEvent([
                'category_id' => intval($_POST['category_id']),
                'title' => epic_sanitize($_POST['title']),
                'description' => epic_sanitize($_POST['description']),
                'start_time' => epic_sanitize($_POST['start_time']),
                'end_time' => epic_sanitize($_POST['end_time']),
                'max_participants' => !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null,
                'registration_required' => isset($_POST['registration_required']) ? 1 : 0,
                'created_by' => epic_get_current_user_id()
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'update_event':
            $result = $epic_zoom->updateEvent($_POST['id'], [
                'category_id' => intval($_POST['category_id']),
                'title' => epic_sanitize($_POST['title']),
                'description' => epic_sanitize($_POST['description']),
                'start_time' => epic_sanitize($_POST['start_time']),
                'end_time' => epic_sanitize($_POST['end_time']),
                'max_participants' => !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null,
                'registration_required' => isset($_POST['registration_required']) ? 1 : 0,
                'status' => epic_sanitize($_POST['status'])
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'delete_event':
            $result = $epic_zoom->deleteEvent($_POST['id']);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'save_zoom_settings':
            $result = $epic_zoom->saveZoomSettings([
                'zoom_api_key' => epic_sanitize($_POST['zoom_api_key']),
                'zoom_api_secret' => epic_sanitize($_POST['zoom_api_secret']),
                'zoom_account_id' => epic_sanitize($_POST['zoom_account_id'])
            ]);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'get_event':
            $event = $epic_zoom->getEvent($_POST['id']);
            echo json_encode($event);
            exit;
            
        case 'get_category':
            $category = $epic_zoom->getEventCategory($_POST['id']);
            echo json_encode($category);
            exit;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            exit;
    }
}

// Get data for page
$categories = $epic_zoom->getEventCategories();
$events_data = $epic_zoom->getEvents(1, 20);
$events = $events_data['events'];
$total_pages = $events_data['total_pages'];

// Use admin layout system
require_once __DIR__ . '/layout-helper.php';

// Prepare layout data
$layout_data = [
    'page_title' => 'Zoom Integration - EPIC Hub Admin',
    'header_title' => 'Zoom Integration',
    'current_page' => 'integrasi',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Integrasi', 'url' => '#'],
        ['text' => 'Zoom Integration']
    ],
    'categories' => $categories,
    'events' => $events,
    'total_pages' => $total_pages
];

// Render admin page
epic_render_admin_page(__DIR__ . '/content/zoom-integration-content.php', $layout_data);