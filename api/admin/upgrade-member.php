<?php
/**
 * API Endpoint untuk Upgrade Member via AJAX
 * Endpoint: /api/admin/upgrade-member.php
 */

if (!defined('EPIC_INIT')) {
    require_once '../../bootstrap.php';
}

// Set JSON response header
header('Content-Type: application/json');

// Check if user is admin
$user = epic_current_user();
if (!$user || !epic_is_admin($user)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Hanya admin yang dapat melakukan upgrade member.'
    ]);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan. Gunakan POST request.'
    ]);
    exit;
}

// Get input data (support both JSON and form data)
$input = null;

// Try JSON input first
$json_input = json_decode(file_get_contents('php://input'), true);
if ($json_input && isset($json_input['member_id'])) {
    $input = $json_input;
} elseif (isset($_POST['member_id'])) {
    // Fallback to form data
    $input = $_POST;
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Member ID tidak ditemukan dalam request.'
    ]);
    exit;
}

// Validate input
if (!isset($input['member_id']) || !is_numeric($input['member_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Member ID tidak valid.'
    ]);
    exit;
}

$member_id = (int)$input['member_id'];

try {
    // Get member data before upgrade
    $member = db()->selectOne(
        "SELECT id, name, email, status, hierarchy_level, referral_code, epis_supervisor_id 
         FROM " . db()->table('users') . " 
         WHERE id = ?",
        [$member_id]
    );
    
    if (!$member) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Member tidak ditemukan.'
        ]);
        exit;
    }
    
    // Check if member is eligible for upgrade
    if ($member['status'] !== 'free' || $member['hierarchy_level'] != 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Member tidak eligible untuk upgrade. Hanya Free Account yang dapat diupgrade.'
        ]);
        exit;
    }
    
    // Perform upgrade using safe function
    $upgrade_result = epic_safe_upgrade_to_epic($member_id, $user['id']);
    
    if ($upgrade_result['success']) {
        // Get updated member data
        $updated_member = db()->selectOne(
            "SELECT id, name, email, status, hierarchy_level, created_at, updated_at 
             FROM " . db()->table('users') . " 
             WHERE id = ?",
            [$member_id]
        );
        
        // Prepare success response
        $response = [
            'success' => true,
            'message' => $upgrade_result['message'],
            'member' => [
                'id' => $updated_member['id'],
                'name' => $updated_member['name'],
                'email' => $updated_member['email'],
                'status' => $updated_member['status'],
                'hierarchy_level' => $updated_member['hierarchy_level'],
                'status_badge' => epic_get_status_badge($updated_member['status'], $updated_member['hierarchy_level']),
                'updated_at' => $updated_member['updated_at']
            ],
            'preserved_data' => [
                'referral' => $upgrade_result['referral_preserved'] ?? false,
                'supervisor' => $upgrade_result['supervisor_preserved'] ?? false
            ]
        ];
        
        echo json_encode($response);
        
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $upgrade_result['message']
        ]);
    }
    
} catch (Exception $e) {
    error_log("Upgrade Member API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
    ]);
}

/**
 * Helper function to get status badge HTML
 */
function epic_get_status_badge($status, $hierarchy_level) {
    $badge_class = '';
    $badge_text = '';
    
    switch ($status) {
        case 'free':
            $badge_class = 'badge-secondary';
            $badge_text = 'Free Account';
            break;
        case 'epic':
            $badge_class = 'badge-success';
            $badge_text = 'EPIC Account';
            break;
        case 'epis':
            $badge_class = 'badge-primary';
            $badge_text = 'EPIS Account';
            break;
        default:
            $badge_class = 'badge-secondary';
            $badge_text = ucfirst($status);
    }
    
    return "<span class=\"badge {$badge_class}\">{$badge_text}</span>";
}
?>