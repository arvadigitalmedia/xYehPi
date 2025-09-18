<?php
/**
 * EPIC Hub Zoom Integration Routes
 * URL routing untuk fitur Zoom Integration
 */

if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

/**
 * Add Zoom Integration routes to EPIC Hub
 * Note: Routes are now handled by the main admin routing system in admin.php
 * This function is kept for backward compatibility
 */
function epic_add_zoom_routes() {
    // Routes are now handled by epic_admin_route() in admin.php
    // Admin route: /admin/zoom-integration -> epic_admin_integrasi_zoom()
    // Member route: /member/zoom-events.php (direct file access)
    // API routes: handled by epic_api_route() in api.php
    
    // No action needed - routes are integrated into main routing system
    return true;
}

/**
 * Add Zoom Integration menu items
 */
function epic_add_zoom_menu_items() {
    // Add to admin menu
    if (function_exists('epic_is_admin') && epic_is_admin()) {
        // Menu items will be added via direct HTML in admin templates
        // This is a placeholder for future menu system integration
    }
    
    // Add to member menu
    if (function_exists('epic_is_logged_in') && epic_is_logged_in()) {
        // Menu items will be added via direct HTML in member templates
        // This is a placeholder for future menu system integration
    }
}

/**
 * Initialize Zoom Integration routes and menus
 */
function epic_init_zoom_integration() {
    // Add routes
    epic_add_zoom_routes();
    
    // Add menu items (simplified for EPIC Hub)
    epic_add_zoom_menu_items();
}

// Initialize zoom integration immediately
epic_init_zoom_integration();

/**
 * Helper function to check if Zoom Integration is installed
 */
function epic_zoom_is_installed() {
    global $epic_db;
    
    try {
        if (!$epic_db) {
            return false;
        }
        $stmt = $epic_db->prepare("SHOW TABLES LIKE 'epic_event_categories'");
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get user's accessible Zoom events
 */
function epic_get_user_zoom_events($user_id, $limit = 5) {
    if (!epic_zoom_is_installed()) {
        return [];
    }
    
    if (!function_exists('epic_get_user_access_level')) {
        return [];
    }
    
    require_once EPIC_PATH . '/core/zoom-integration.php';
    global $epic_zoom;
    
    if (!$epic_zoom) {
        return [];
    }
    
    $user_level = epic_get_user_access_level($user_id);
    return $epic_zoom->getEventsByUserLevel($user_level, $limit);
}

/**
 * Add Zoom events to member dashboard widget
 */
function epic_add_zoom_dashboard_widget() {
    if (!epic_is_logged_in() || !epic_zoom_is_installed()) {
        return;
    }
    
    $user = epic_get_current_user();
    $events = epic_get_user_zoom_events($user['id'], 3);
    
    if (empty($events)) {
        return;
    }
    
    echo '<div class="dashboard-widget zoom-events-widget">';
    echo '<div class="widget-header">';
    echo '<h3><i data-feather="video" width="20" height="20"></i> Event Zoom Mendatang</h3>';
    echo '<a href="' . epic_url('member/zoom-events') . '" class="widget-link">Lihat Semua</a>';
    echo '</div>';
    echo '<div class="widget-content">';
    
    foreach ($events as $event) {
        echo '<div class="event-item">';
        echo '<div class="event-category" style="color: ' . $event['category_color'] . '">';
        echo '<i data-feather="' . $event['category_icon'] . '" width="14" height="14"></i> ';
        echo htmlspecialchars($event['category_name']);
        echo '</div>';
        echo '<div class="event-title">' . htmlspecialchars($event['title']) . '</div>';
        echo '<div class="event-time">';
        echo '<i data-feather="calendar" width="14" height="14"></i> ';
        echo date('d M Y, H:i', strtotime($event['start_time']));
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
}

// Dashboard widget and styles will be integrated directly in templates
// These functions are available for manual integration if needed

/**
 * Add Zoom Integration styles to admin and member areas
 */
function epic_add_zoom_styles() {
    if (function_exists('epic_is_current_page') && 
        (epic_is_current_page('admin/zoom-integration') || epic_is_current_page('member/zoom-events'))) {
        echo '<style>';
        echo '.zoom-events-widget { margin-bottom: 1.5rem; }';
        echo '.widget-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }';
        echo '.widget-header h3 { margin: 0; display: flex; align-items: center; gap: 0.5rem; }';
        echo '.widget-link { color: var(--primary); text-decoration: none; font-size: 0.875rem; }';
        echo '.event-item { padding: 0.75rem 0; border-bottom: 1px solid var(--border); }';
        echo '.event-item:last-child { border-bottom: none; }';
        echo '.event-category { font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.25rem; }';
        echo '.event-title { font-weight: 500; color: var(--text); margin-bottom: 0.25rem; }';
        echo '.event-time { font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.25rem; }';
        echo '</style>';
    }
}

// Styles will be included directly in page templates