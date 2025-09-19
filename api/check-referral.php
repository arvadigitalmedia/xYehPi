<?php
/**
 * API Endpoint untuk validasi kode referral
 * Path: /api/check-referral
 */

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
require_once '../config.php';
require_once '../functions.php';
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
    
    // Validate referral code using existing function
    $referrer_info = epic_get_referrer_info($referral_code);
    
    if (!$referrer_info) {
        echo json_encode([
            'success' => false,
            'message' => 'Kode referral tidak valid atau tidak ditemukan'
        ]);
        exit;
    }
    
    // Check if referrer is active and eligible
    if (!isset($referrer_info['status']) || $referrer_info['status'] !== 'active') {
        echo json_encode([
            'success' => false,
            'message' => 'Akun referrer tidak aktif atau tidak memenuhi syarat'
        ]);
        exit;
    }
    
    // Return success response with referrer info
    echo json_encode([
        'success' => true,
        'message' => 'Kode referral valid',
        'referrer' => [
            'id' => $referrer_info['id'] ?? '',
            'name' => $referrer_info['name'] ?? '',
            'username' => $referrer_info['username'] ?? '',
            'email' => $referrer_info['email'] ?? '',
            'phone' => $referrer_info['phone'] ?? '',
            'referral_code' => $referral_code,
            'join_date' => $referrer_info['join_date'] ?? '',
            'total_referrals' => $referrer_info['total_referrals'] ?? 0,
            'status' => $referrer_info['status'] ?? 'active'
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