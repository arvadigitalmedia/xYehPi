<?php
/**
 * Direct access to Zoom Integration Admin Page
 * Redirect to proper admin zoom integration page
 */

// Include bootstrap
require_once '../bootstrap.php';

// Check admin access
if (!epic_is_admin()) {
    epic_redirect('login');
}

// Include the actual admin page
require_once EPIC_PATH . '/themes/modern/admin/zoom-integration.php';