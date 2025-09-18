<?php
/**
 * EPIC Hub Admin - Add New Zoom Event
 * Halaman standalone untuk menambah event Zoom baru
 */

if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

// Include routing helper for consistent error handling
require_once __DIR__ . '/../themes/modern/admin/routing-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/zoom-add-event');
$user = $init_result['user'];

// Load Zoom integration core
require_once EPIC_PATH . '/core/zoom-integration.php';
global $epic_zoom;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_event') {
    $result = $epic_zoom->createEvent([
        'category_id' => intval($_POST['category_id']),
        'title' => epic_sanitize($_POST['title']),
        'description' => epic_sanitize($_POST['description']),
        'start_time' => epic_sanitize($_POST['start_time']),
        'end_time' => epic_sanitize($_POST['end_time']),
        'max_participants' => !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null,
        'registration_required' => isset($_POST['registration_required']) ? 1 : 0,
        'status' => epic_sanitize($_POST['status']) ?: 'published',
        'created_by' => epic_get_current_user_id()
    ]);
    
    if ($result) {
        // Redirect back to zoom integration page with success message
        header('Location: ' . epic_url('admin/zoom-integration?success=event_created'));
        exit;
    } else {
        $error = 'Failed to create event. Please try again.';
    }
}

// Get categories for dropdown
$categories = $epic_zoom->getEventCategories();

// Use admin layout system
require_once __DIR__ . '/../themes/modern/admin/layout-helper.php';

// Prepare layout data
$layout_data = [
    'page_title' => 'Add New Zoom Event - EPIC Hub Admin',
    'header_title' => 'Add New Zoom Event',
    'current_page' => 'integrasi',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Integrasi', 'url' => '#'],
        ['text' => 'Zoom Integration', 'url' => epic_url('admin/zoom-integration')],
        ['text' => 'Add New Event']
    ],
    'categories' => $categories,
    'error' => $error ?? null
];

// Render admin page
epic_render_admin_page(__DIR__ . '/../themes/modern/admin/content/zoom-add-event-content.php', $layout_data);
?>