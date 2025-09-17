<?php
/**
 * EPIC Hub Admin Members API
 * Handles member management operations with optimized performance
 */

require_once '../../bootstrap.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// CORS headers for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Authentication check
if (!epic_is_logged_in() || !epic_is_admin()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));
$action = end($path_parts);

// Route requests
switch ($action) {
    case 'check-duplicate':
        handleCheckDuplicate();
        break;
    case 'add':
        handleAddMember();
        break;
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found'
        ]);
        break;
}

/**
 * Check for duplicate email or phone
 */
function handleCheckDuplicate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        return;
    }
    
    try {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['whatsapp'] ?? '');
        
        if (empty($email) || empty($phone)) {
            echo json_encode([
                'success' => false,
                'message' => 'Email and phone are required'
            ]);
            return;
        }
        
        // Check for duplicates with specific checks
        $email_duplicate = db()->selectValue(
            "SELECT id FROM epic_users WHERE email = ? LIMIT 1",
            [$email]
        );
        
        $phone_duplicate = db()->selectValue(
            "SELECT id FROM epic_users WHERE phone = ? LIMIT 1",
            [$phone]
        );
        
        $has_duplicate = !empty($email_duplicate) || !empty($phone_duplicate);
        $message = 'No duplicates found';
        
        if ($email_duplicate && $phone_duplicate) {
            $message = 'Nomor WhatsApp atau email sudah digunakan';
        } elseif ($email_duplicate) {
            $message = 'Email sudah digunakan oleh member lain';
        } elseif ($phone_duplicate) {
            $message = 'Nomor WhatsApp sudah digunakan oleh member lain';
        }
        
        echo json_encode([
            'success' => true,
            'duplicate' => $has_duplicate,
            'message' => $message,
            'email_duplicate' => !empty($email_duplicate),
            'phone_duplicate' => !empty($phone_duplicate)
        ]);
        
    } catch (Exception $e) {
        error_log('Check duplicate error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred'
        ]);
    }
}

/**
 * Add new member
 */
function handleAddMember() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        return;
    }
    
    try {
        // Validate and sanitize input
        $data = validateMemberData($_POST);
        
        if (!$data['valid']) {
            echo json_encode([
                'success' => false,
                'message' => $data['message'],
                'errors' => $data['errors']
            ]);
            return;
        }
        
        // Check sponsor exists
        $sponsor = db()->selectOne(
            "SELECT id, name FROM epic_users WHERE id = ? OR referral_code = ?",
            [$data['sponsor_id'], $data['sponsor_id']]
        );
        
        if (!$sponsor) {
            echo json_encode([
                'success' => false,
                'message' => 'Sponsor ID tidak ditemukan'
            ]);
            return;
        }
        
        // Generate referral code
        $referral_code = generateReferralCode($data['full_name']);
        
        // Generate password
        $password = generateRandomPassword();
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Begin transaction for data consistency
        db()->beginTransaction();
        
        try {
            // Insert new member
            $member_id = db()->insert('epic_users', [
                'name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['whatsapp'],
                'password' => $hashed_password,
                'status' => $data['status'],
                'role' => $data['role'],
                'referral_code' => $referral_code,
                'sponsor_id' => $sponsor['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Log activity
            db()->insert('epic_activity_logs', [
                'user_id' => epic_get_current_user_id(),
                'action' => 'member_added',
                'description' => "Added new member: {$data['full_name']} (ID: {$member_id})",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            db()->commit();
            
            // Send welcome email (async)
            sendWelcomeEmail($data['email'], $data['full_name'], $password);
            
            echo json_encode([
                'success' => true,
                'message' => 'Member berhasil ditambahkan',
                'data' => [
                    'id' => $member_id,
                    'name' => $data['full_name'],
                    'email' => $data['email'],
                    'referral_code' => $referral_code
                ]
            ]);
            
        } catch (Exception $e) {
            db()->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log('Add member error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan saat menambahkan member'
        ]);
    }
}

/**
 * Validate member data
 */
function validateMemberData($post_data) {
    $errors = [];
    $data = [];
    
    // Sponsor ID
    $sponsor_id = trim($post_data['sponsor_id'] ?? '');
    if (empty($sponsor_id)) {
        $errors['sponsor_id'] = 'ID Sponsor wajib diisi';
    } elseif (strlen($sponsor_id) < 3) {
        $errors['sponsor_id'] = 'ID Sponsor minimal 3 karakter';
    } else {
        $data['sponsor_id'] = $sponsor_id;
    }
    
    // Full name
    $full_name = trim($post_data['full_name'] ?? '');
    if (empty($full_name)) {
        $errors['full_name'] = 'Nama lengkap wajib diisi';
    } elseif (strlen($full_name) < 2) {
        $errors['full_name'] = 'Nama lengkap minimal 2 karakter';
    } elseif (strlen($full_name) > 100) {
        $errors['full_name'] = 'Nama lengkap maksimal 100 karakter';
    } else {
        $data['full_name'] = $full_name;
    }
    
    // Email
    $email = trim($post_data['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email wajib diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    } else {
        $data['email'] = strtolower($email);
    }
    
    // WhatsApp
    $whatsapp = trim($post_data['whatsapp'] ?? '');
    if (empty($whatsapp)) {
        $errors['whatsapp'] = 'Nomor WhatsApp wajib diisi';
    } elseif (!preg_match('/^(\+?62|0)[0-9]{9,13}$/', $whatsapp)) {
        $errors['whatsapp'] = 'Format nomor WhatsApp tidak valid';
    } else {
        // Normalize phone number
        $whatsapp = preg_replace('/^0/', '62', $whatsapp);
        $whatsapp = preg_replace('/^\+/', '', $whatsapp);
        $data['whatsapp'] = $whatsapp;
    }
    
    // Status
    $status = trim($post_data['status'] ?? '');
    $allowed_statuses = ['pending', 'free', 'epic'];
    if (empty($status)) {
        $errors['status'] = 'Status member wajib dipilih';
    } elseif (!in_array($status, $allowed_statuses)) {
        $errors['status'] = 'Status member tidak valid';
    } else {
        $data['status'] = $status;
    }
    
    // Role
    $role = trim($post_data['role'] ?? '');
    $allowed_roles = ['user', 'super_admin'];
    if (empty($role)) {
        $errors['role'] = 'Role pengguna wajib dipilih';
    } elseif (!in_array($role, $allowed_roles)) {
        $errors['role'] = 'Role pengguna tidak valid';
    } else {
        $data['role'] = $role;
    }
    
    return [
        'valid' => empty($errors),
        'data' => $data,
        'errors' => $errors,
        'message' => empty($errors) ? 'Data valid' : 'Terdapat kesalahan pada data'
    ];
}

/**
 * Generate unique referral code
 */
function generateReferralCode($name) {
    // Create base from name
    $base = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
    if (strlen($base) < 3) {
        $base = str_pad($base, 3, 'X');
    }
    
    // Add random numbers
    $attempts = 0;
    do {
        $code = $base . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $exists = db()->selectValue(
            "SELECT id FROM epic_users WHERE referral_code = ?",
            [$code]
        );
        $attempts++;
    } while ($exists && $attempts < 10);
    
    // Fallback to timestamp-based code
    if ($exists) {
        $code = $base . substr(time(), -4);
    }
    
    return $code;
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    
    return $password;
}

/**
 * Send welcome email (async)
 */
function sendWelcomeEmail($email, $name, $password) {
    // This would typically be handled by a queue system
    // For now, we'll just log it
    error_log("Welcome email should be sent to: {$email} with password: {$password}");
    
    // TODO: Implement actual email sending
    // - Use PHPMailer or similar
    // - Queue for background processing
    // - Include login credentials and welcome message
}

?>