<?php
/**
 * EPIC Hub Admin Index
 * Main entry point for admin area
 */

// Include bootstrap
require_once '../bootstrap.php';

// Check if user is logged in
if (!epic_is_logged_in()) {
    epic_redirect('login');
    exit;
}

// Get current user
$user = epic_current_user();

// Check admin access
if (!epic_is_admin($user)) {
    epic_redirect('dashboard');
    exit;
}

// Route to admin dashboard
epic_admin_dashboard();