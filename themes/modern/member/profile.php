<?php
/**
 * EPIC Hub Member Profile Page
 * Halaman edit profil untuk member area dengan fungsionalitas lengkap
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout system
require_once __DIR__ . '/components/page-layout.php';

$user = $user ?? epic_current_user();
$access_level = $access_level ?? epic_get_member_access_level($user);

// Get user profile data
$profile = db()->selectOne(
    "SELECT * FROM " . db()->table('user_profiles') . " WHERE user_id = ?",
    [$user['id']]
) ?: [];

// Handle success/error messages
$success_message = '';
$error_message = '';

if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $success_message = 'Profil berhasil diperbarui!';
}

if (isset($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}

// Calculate profile completion
$completion_fields = ['name', 'email', 'phone', 'avatar', 'bio'];
$completed_fields = 0;

if (!empty($user['name'])) $completed_fields++;
if (!empty($user['email'])) $completed_fields++;
if (!empty($user['phone'])) $completed_fields++;
if (!empty($user['avatar'])) $completed_fields++;
if (!empty($profile['bio'])) $completed_fields++;

$completion_percentage = round(($completed_fields / count($completion_fields)) * 100);

// Get additional profile data for social media (EPIC/EPIS only)
$social_data = [];
if (in_array($access_level, ['epic', 'epis'])) {
    $social_data = [
        'website' => $profile['website'] ?? '',
        'facebook' => $profile['facebook'] ?? '',
        'instagram' => $profile['instagram'] ?? '',
        'twitter' => $profile['twitter'] ?? '',
        'linkedin' => $profile['linkedin'] ?? ''
    ];
}
?>

<?php
// Render consistent page header
render_page_header([
    'title' => 'Edit Profil',
    'subtitle' => 'Kelola informasi profil dan pengaturan akun Anda',
    'breadcrumb' => [
        ['text' => 'Dashboard', 'url' => epic_url('dashboard/member')],
        ['text' => 'Edit Profil']
    ],
    'actions' => [
        [
            'text' => 'Kembali ke Dashboard',
            'url' => epic_url('dashboard/member'),
            'class' => 'btn-secondary',
            'icon' => 'arrow-left'
        ]
    ]
]);
?>

<?php
// Render consistent alert messages
$alerts = [];
if ($success_message) {
    $alerts[] = [
        'type' => 'success',
        'icon' => 'check-circle',
        'title' => 'Berhasil!',
        'message' => $success_message,
        'dismissible' => true
    ];
}
if ($error_message) {
    $alerts[] = [
        'type' => 'error',
        'icon' => 'alert-circle',
        'title' => 'Error!',
        'message' => $error_message,
        'dismissible' => true
    ];
}
render_alerts($alerts);

// Include content
require_once __DIR__ . '/content/profile-content.php';
?>