<?php
/**
 * EPIC Hub Admin Edit Profile
 * Menggunakan layout global yang baru
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Include layout helper
require_once __DIR__ . '/layout-helper.php';

// Check admin access sudah dilakukan di layout helper
$user = epic_current_user();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        // Validate required fields
        if (empty($name)) {
            throw new Exception('Name is required.');
        }
        
        if (empty($email)) {
            throw new Exception('Email is required.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }
        
        // Check if email is already taken by another user
        $existing_user = db()->selectOne(
            "SELECT id FROM " . db()->table('users') . " WHERE email = ? AND id != ?",
            [$email, $user['id']]
        );
        
        if ($existing_user) {
            throw new Exception('Email is already taken by another user.');
        }
        
        // Validate phone number format for WhatsApp
        if (!empty($phone)) {
            // Remove any non-digit characters for validation
            $phone_digits = preg_replace('/\D/', '', $phone);
            
            // Check if phone number is valid (10-15 digits)
            if (strlen($phone_digits) < 10 || strlen($phone_digits) > 15) {
                throw new Exception('Nomor telepon harus antara 10-15 digit.');
            }
            
            // Check if starts with valid country code
            $valid_country_codes = ['62', '1', '44', '91', '86', '81', '33', '49', '39', '34'];
            $starts_with_valid_code = false;
            foreach ($valid_country_codes as $code) {
                if (strpos($phone_digits, $code) === 0) {
                    $starts_with_valid_code = true;
                    break;
                }
            }
            
            if (!$starts_with_valid_code) {
                throw new Exception('Nomor telepon harus dimulai dengan kode negara yang valid (contoh: 62 untuk Indonesia).');
            }
            
            // Store phone number with digits only
            $phone = $phone_digits;
        }
        
        // Handle profile photo upload
        $profile_photo = $user['profile_photo'];
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../../uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Validate file size (1MB = 1048576 bytes)
            if ($_FILES['profile_photo']['size'] > 1048576) {
                throw new Exception('Ukuran file terlalu besar. Maksimal 1MB.');
            }
            
            $file_extension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['profile_photo']['tmp_name']);
            finfo_close($finfo);
            
            $allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($mime_type, $allowed_mime_types)) {
                throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
            }
            
            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Format file tidak valid. Hanya JPG, PNG, dan GIF yang diperbolehkan.');
            }
            
            // Delete old profile photo if exists
            if (!empty($profile_photo)) {
                $old_photo_path = $upload_dir . $profile_photo;
                if (file_exists($old_photo_path)) {
                    unlink($old_photo_path);
                }
            }
            
            $profile_photo = 'profile_' . $user['id'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $profile_photo;
            
            if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
                throw new Exception('Failed to upload profile photo.');
            }
        }
        
        // Handle password change
        $update_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'profile_photo' => $profile_photo,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($_POST['new_password'])) {
            if (empty($_POST['current_password'])) {
                throw new Exception('Password saat ini diperlukan untuk mengubah password.');
            }
            
            if (!password_verify($_POST['current_password'], $user['password'])) {
                throw new Exception('Password saat ini tidak benar.');
            }
            
            $new_password = $_POST['new_password'];
            
            // Enhanced password validation
            if (strlen($new_password) < 8) {
                throw new Exception('Password baru harus minimal 8 karakter.');
            }
            
            if (!preg_match('/[A-Z]/', $new_password)) {
                throw new Exception('Password harus mengandung minimal 1 huruf besar (A-Z).');
            }
            
            if (!preg_match('/[a-z]/', $new_password)) {
                throw new Exception('Password harus mengandung minimal 1 huruf kecil (a-z).');
            }
            
            if (!preg_match('/[0-9]/', $new_password)) {
                throw new Exception('Password harus mengandung minimal 1 angka (0-9).');
            }
            
            if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $new_password)) {
                throw new Exception('Password harus mengandung minimal 1 simbol (!@#$%^&* dll).');
            }
            
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                throw new Exception('Password baru dan konfirmasi password tidak cocok.');
            }
            
            $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        // Update user data
        db()->update('users', $update_data, 'id = ?', [$user['id']]);
        
        // Update session data
        $_SESSION['user'] = array_merge($user, $update_data);
        
        // Log activity
        epic_log_activity($user['id'], 'update_profile', [
            'updated_fields' => array_keys($update_data)
        ]);
        
        $success = "Profile updated successfully!";
        
        // Refresh user data
        $user = epic_current_user();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log('Update profile error: ' . $e->getMessage());
    }
}

// Prepare data untuk layout
$layout_data = [
    'page_title' => 'Edit Profile - EPIC Hub Admin',
    'header_title' => 'Edit Profile',
    'current_page' => 'edit-profile',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Edit Profile']
    ],
    'content_file' => __DIR__ . '/content/edit-profile-content.php',
    // Pass variables ke content
    'success' => $success,
    'error' => $error,
    'user' => $user
];

// Render halaman dengan layout global
epic_render_admin_page($layout_data['content_file'], $layout_data);
?>