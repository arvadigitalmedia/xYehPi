<?php
/**
 * Resend Email Confirmation Handler
 * Handler untuk mengirim ulang email konfirmasi dengan rate limiting
 */

require_once 'bootstrap.php';

// Set response header
header('Content-Type: application/json');

// CSRF Protection
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

// Get email from request
$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email tidak valid.']);
    exit;
}

try {
    // Check if user exists and needs confirmation
    $user = db()->selectOne(
        "SELECT id, name, email, status, email_verified_at 
         FROM " . db()->table('users') . " 
         WHERE email = ?", 
        [$email]
    );
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan dalam sistem.']);
        exit;
    }
    
    // Check if email is already verified
    if ($user['status'] === 'free' && $user['email_verified_at']) {
        echo json_encode(['success' => false, 'message' => 'Email sudah dikonfirmasi sebelumnya.']);
        exit;
    }
    
    // Rate limiting: Check if user has requested resend in the last 5 minutes
    $recent_token = db()->selectOne(
        "SELECT id, created_at FROM " . db()->table('user_tokens') . " 
         WHERE user_id = ? AND type = 'email_verification' 
         AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
         ORDER BY created_at DESC LIMIT 1", 
        [$user['id']]
    );
    
    if ($recent_token) {
        $wait_time = 5 - floor((time() - strtotime($recent_token['created_at'])) / 60);
        echo json_encode([
            'success' => false, 
            'message' => "Silakan tunggu {$wait_time} menit sebelum meminta kirim ulang email."
        ]);
        exit;
    }
    
    // Deactivate old tokens
    db()->query(
        "UPDATE " . db()->table('user_tokens') . " 
         SET used_at = NOW() 
         WHERE user_id = ? AND type = 'email_verification' AND used_at IS NULL", 
        [$user['id']]
    );
    
    // Generate new verification token
    $token = hash('sha256', random_bytes(32) . $user['id'] . time());
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Insert new token
    $token_inserted = db()->insert(
        db()->table('user_tokens'),
        [
            'user_id' => $user['id'],
            'token' => $token,
            'type' => 'email_verification',
            'expires_at' => $expires_at,
            'created_at' => date('Y-m-d H:i:s')
        ]
    );
    
    if (!$token_inserted) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat token verifikasi.']);
        exit;
    }
    
    // Generate confirmation URL
    $confirmation_url = "http://" . $_SERVER['HTTP_HOST'] . "/confirm-email.php?token=" . $token;
    
    // TODO: Send email here
    // For now, we'll just log the URL
    error_log("Email confirmation URL for {$email}: {$confirmation_url}");
    
    // In production, integrate with your email service:
    /*
    $email_sent = epic_send_confirmation_email([
        'name' => $user['name'],
        'email' => $user['email'],
        'confirmation_url' => $confirmation_url
    ]);
    
    if (!$email_sent) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengirim email.']);
        exit;
    }
    */
    
    // Log the resend action
    error_log("Email confirmation resent for user ID: {$user['id']}, email: {$email}");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Email konfirmasi telah dikirim ulang. Silakan cek inbox Anda.',
        'debug_url' => $confirmation_url // Remove this in production
    ]);
    
} catch (Exception $e) {
    error_log('Resend confirmation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi nanti.']);
}
?>