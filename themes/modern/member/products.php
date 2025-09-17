<?php
/**
 * EPIC Hub Member Products Page
 * Halaman produk untuk member dengan layout global dan integrasi LMS
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout helper and LMS integration
require_once __DIR__ . '/layout-helper.php';
require_once __DIR__ . '/../../../core/lms-integration.php';

// Get current user
$user = epic_current_user();
if (!$user) {
    epic_redirect('login');
}

// User access level
$access_level = $user['access_level'] ?? 'free';
$user_id = $user['id'];

// Get actual LMS products from database
$available_products = get_lms_products_for_member($access_level);

// Filter products based on access level and status
$locked_products = array_filter($available_products, function($product) use ($access_level) {
    return !in_array($access_level, $product['access_level']);
});

// Get user's purchased products
$purchased_products = get_user_purchased_products($user_id);

// Get user's learning statistics
$stats = get_user_learning_stats($user_id);

// Get user's progress for purchased products
$user_progress = get_user_products_progress($user_id, $purchased_products);
?>

<?php
// Data untuk breadcrumb yang akan digunakan di card
$breadcrumb_data = [
    ['text' => 'Dashboard', 'url' => epic_url('dashboard/member')],
    ['text' => 'Produk']
];

// Include content
require_once __DIR__ . '/content/products-content.php';
?>

<!-- Legacy content cleanup -->
<!-- Content moved to products-content.php -->


<!-- Content moved to products-content.php -->

<!-- Styles moved to products-content.php -->

<!-- Scripts moved to products-content.php -->