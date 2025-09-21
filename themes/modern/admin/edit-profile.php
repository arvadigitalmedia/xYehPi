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
            "SELECT id FROM " . db()->table('epic_users') . " WHERE email = ? AND id != ?",
            [$email, $user['id']]
        );
        
        if ($existing_user) {
            throw new Exception('Email is already taken by another user.');
        }
        
        // Validate and process WhatsApp phone number
        if (!empty($phone)) {
            // Remove all non-digit characters
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            // Check length (10-15 digits)
            if (strlen($phone) < 10 || strlen($phone) > 15) {
                throw new Exception('Nomor telepon harus terdiri dari 10-15 digit.');
            }
            
            // Check if already has valid country code
        $valid_country_codes = ['1', '7', '20', '27', '30', '31', '32', '33', '34', '36', '39', '40', '41', '43', '44', '45', '46', '47', '48', '49', '51', '52', '53', '54', '55', '56', '57', '58', '60', '61', '62', '63', '64', '65', '66', '81', '82', '84', '86', '90', '91', '92', '93', '94', '95', '98', '212', '213', '216', '218', '220', '221', '222', '223', '224', '225', '226', '227', '228', '229', '230', '231', '232', '233', '234', '235', '236', '237', '238', '239', '240', '241', '242', '243', '244', '245', '246', '248', '249', '250', '251', '252', '253', '254', '255', '256', '257', '258', '260', '261', '262', '263', '264', '265', '266', '267', '268', '269', '290', '291', '297', '298', '299', '350', '351', '352', '353', '354', '355', '356', '357', '358', '359', '370', '371', '372', '373', '374', '375', '376', '377', '378', '380', '381', '382', '383', '385', '386', '387', '389', '420', '421', '423', '500', '501', '502', '503', '504', '505', '506', '507', '508', '509', '590', '591', '592', '593', '594', '595', '596', '597', '598', '599', '670', '672', '673', '674', '675', '676', '677', '678', '679', '680', '681', '682', '683', '684', '685', '686', '687', '688', '689', '690', '691', '692', '850', '852', '853', '855', '856', '880', '886', '960', '961', '962', '963', '964', '965', '966', '967', '968', '970', '971', '972', '973', '974', '975', '976', '977', '992', '993', '994', '995', '996', '998'];
        $has_valid_country_code = false;
        foreach ($valid_country_codes as $code) {
            if (strpos($phone, $code) === 0) {
                $has_valid_country_code = true;
                break;
            }
        }
        
        // If doesn't have valid country code, try to auto-add Indonesia country code
        if (!$has_valid_country_code) {
            // Cek apakah ini nomor lokal Indonesia (dimulai dengan 0 atau 8)
            $is_indonesian_local = false;
            if (preg_match('/^[08]/', $phone)) {
                $is_indonesian_local = true;
            }
            
            // Cek pola yang jelas invalid (00 prefix atau 4+ digit country code)
            $invalid_patterns = ['/^00/', '/^[0-9]{4,}$/'];
            $has_invalid_pattern = false;
            foreach ($invalid_patterns as $pattern) {
                if (preg_match($pattern, $phone)) {
                    $has_invalid_pattern = true;
                    break;
                }
            }
            
            // Cek apakah dimulai dengan kode negara invalid (2-3 digit yang bukan valid)
            $possible_invalid_code = false;
            if (!$is_indonesian_local && preg_match('/^([0-9]{2,3})/', $phone, $matches)) {
                $potential_code = $matches[1];
                // Jika 2-3 digit pertama bukan kode negara valid, anggap invalid
                if (!in_array($potential_code, $valid_country_codes)) {
                    $possible_invalid_code = true;
                }
            }
            
            // Auto-add prefix Indonesia jika nomor lokal atau tidak ada pola invalid
            if ($is_indonesian_local || (!$has_invalid_pattern && !$possible_invalid_code)) {
                $phone = '62' . ltrim($phone, '0'); // Hapus 0 di depan jika ada
                $has_valid_country_code = true;
            }
        }
        
        // Final validation - hanya tolak jika benar-benar invalid
        if (!$has_valid_country_code) {
            throw new Exception('Nomor telepon harus dimulai dengan kode negara yang valid (contoh: 62 untuk Indonesia).');
        }
            
            // Store only digits
            $phone = $phone;
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
        db()->update('epic_users', $update_data, 'id = ?', [$user['id']]);
        
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