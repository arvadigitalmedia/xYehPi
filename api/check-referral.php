<?php
/**
 * API Endpoint untuk validasi kode referral
 * Path: /api/check-referral
 */

// Define constants to allow bootstrap access
if (!defined('EPIC_INIT')) define('EPIC_INIT', true);
if (!defined('EPIC_LOADED')) define('EPIC_LOADED', true);

// Set headers untuk JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Include required files
require_once '../config/config.php';
require_once '../bootstrap.php';
require_once '../core/rate-limiter.php';

try {
    // RATE LIMITING - Prevent abuse
    epic_check_referral_rate_limit();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['referral_code'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Kode referral tidak ditemukan'
        ]);
        exit;
    }
    
    $referral_code = trim($input['referral_code']);
    
    if (empty($referral_code)) {
        echo json_encode([
            'success' => false,
            'message' => 'Kode referral tidak boleh kosong'
        ]);
        exit;
    }
    
    // Query database untuk mendapatkan data sponsor
    $sponsor = db()->selectOne(
        "SELECT u.id, u.name, u.email, u.referral_code, u.status,
                supervisor.id as epis_supervisor_id,
                supervisor.name as epis_supervisor_name,
                supervisor.email as epis_supervisor_email
         FROM " . db()->table('users') . " u
         LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
         WHERE u.referral_code = ? AND u.status IN ('active', 'epic', 'epis')",
        [$referral_code]
    );
    
    if (!$sponsor) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Kode sponsor tidak ditemukan atau tidak aktif'
        ]);
        exit;
    }
    
    // Response sukses dengan data sponsor dan EPIS Supervisor
    echo json_encode([
        'success' => true,
        'message' => 'Sponsor ditemukan',
        'data' => [
            'sponsor' => [
                'id' => $sponsor['id'],
                'name' => $sponsor['name'],
                'email' => $sponsor['email'],
                'referral_code' => $sponsor['referral_code'],
                'status' => $sponsor['status']
            ],
            'epis_supervisor' => $sponsor['epis_supervisor_id'] ? [
                'id' => $sponsor['epis_supervisor_id'],
                'name' => $sponsor['epis_supervisor_name'],
                'email' => $sponsor['epis_supervisor_email']
            ] : null
        ]
    ]);
    
} catch (Exception $e) {
    // Log error for debugging
    error_log("Referral API Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
    ]);
}
?>