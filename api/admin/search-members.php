<?php
/**
 * EPIC Hub Admin API - Search Members
 * API endpoint untuk pencarian member berdasarkan nama
 */

if (!defined('EPIC_INIT')) {
    require_once '../../bootstrap.php';
}

// Set JSON header
header('Content-Type: application/json');

// Check if user is admin
$current_user = epic_current_user();
if (!$current_user || !in_array($current_user['role'], ['admin', 'super_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get search query
$query = $_GET['q'] ?? '';
$query = trim($query);

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['members' => []]);
    exit;
}

try {
    // Search members by name or email
    $members = db()->select(
        "SELECT id, name, email, status, role, created_at 
         FROM " . db()->table('users') . " 
         WHERE role IN ('user', 'affiliate') 
         AND (name LIKE ? OR email LIKE ?)
         ORDER BY name ASC 
         LIMIT 20",
        [
            '%' . $query . '%',
            '%' . $query . '%'
        ]
    ) ?: [];
    
    // Format results
    $formatted_members = [];
    foreach ($members as $member) {
        $formatted_members[] = [
            'id' => $member['id'],
            'name' => $member['name'],
            'email' => $member['email'],
            'status' => $member['status'],
            'role' => $member['role'],
            'display_name' => $member['name'] . ' (' . ucfirst($member['status']) . ')',
            'created_at' => $member['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'members' => $formatted_members,
        'count' => count($formatted_members)
    ]);
    
} catch (Exception $e) {
    error_log('Search members error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
?>