<?php
/**
 * Test script untuk halaman admin member add
 * Simulasi login admin dan test form
 */

// Start session
session_start();

// Simulate admin login
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'super_admin';
$_SESSION['user_name'] = 'Admin Official';
$_SESSION['user_email'] = 'email@bisnisemasperak.com';
$_SESSION['logged_in'] = true;

// Redirect to admin member add page
header('Location: /admin/manage/member/add');
exit;
?>