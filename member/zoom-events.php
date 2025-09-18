<?php
/**
 * Direct access to Zoom Events Member Page
 * Redirect to proper member zoom events page
 */

// Include bootstrap
require_once '../bootstrap.php';

// Check member access
if (!epic_is_logged_in()) {
    epic_redirect('login');
}

// Include the actual member page
require_once EPIC_PATH . '/themes/modern/member/zoom-events.php';